<?php
namespace mhndev\oauthClient;

use mhndev\oauthClient\exceptions\InvalidIdentifierType;
use mhndev\oauthClient\exceptions\ModelNotFoundException;
use mhndev\oauthClient\exceptions\OAuthServerUnhandledError;
use mhndev\oauthClient\exceptions\TokenInvalidOrExpiredException;
use mhndev\oauthClient\interfaces\handler\iHandler;
use mhndev\oauthClient\interfaces\iOAuthClient;
use mhndev\oauthClient\interfaces\object\iToken;
use mhndev\oauthClient\interfaces\object\iUserToken;
use mhndev\oauthClient\interfaces\repository\iTokenRepository;
use mhndev\oauthClient\interfaces\repository\iUserTokenRepository;
use mhndev\oauthClient\Objects\User;
use mhndev\oauthClient\entity\common\Token as TokenEntity;

/**
 * This Client class handles token
 * for himself to it persist tokens for future requests
 *
 *
 * Class ClientTokenHandle
 * @package mhndev\oauthClient
 */
class ClientTokenHandle extends Client implements iOAuthClient
{


    /**
     * @var iTokenRepository
     */
    protected $tokenRepository;


    /**
     * @var iUserTokenRepository
     */
    protected $userTokenRepository;


    /**
     * ClientTokenHandle constructor.
     *
     * @param iHandler $handler
     * @param iTokenRepository $tokenRepository
     * @param iUserTokenRepository $userTokenRepository
     */
    public function __construct(
        iHandler $handler,
        iTokenRepository $tokenRepository,
        iUserTokenRepository $userTokenRepository
    )
    {
        parent::__construct($handler);

        $this->tokenRepository = $tokenRepository;
        $this->userTokenRepository = $userTokenRepository;
    }


    /**
     * @param TokenEntity $token
     * @return iToken
     */
    private function refreshToken(TokenEntity $token)
    {
        return $this->getNewClientToken(
            $token->getClientId(),
            $token->getClientSecret()
        );

    }


    /**
     * This method checks if there is any token for specified client_id in table
     * or not, if there is any it also checks token expired_at field
     * if token has expired it would issue new token to oauth server
     * then it should persist new token to database and also return the token
     *
     *
     * @param string $client_id
     * @param string $client_secret
     *
     * @param array $scopes
     * @return iToken
     */
    public function getClientToken($client_id, $client_secret, array $scopes = [])
    {
        try {
            $token = $this->tokenRepository->findByClientId($client_id);

            if ($token->getExpiresAt() <= new \DateTime()) {
                $token = $this->getNewClientToken($client_id, $client_secret);
            }
        } catch (ModelNotFoundException $e) {
            $token = $this->getNewClientToken($client_id, $client_secret);
        }

        return $token;
    }


    /**
     * This method checks if there is any token for specified username in table
     * or not, if there is any it also checks token expired_at field
     * if token has expired it would issue new token to oauth server
     * then it should persist new token to database and also return the token
     *
     *
     * @param int $userId
     * @param string $username
     * @param string $client_id
     * @param string $password
     * @param string $grant_type
     * @return iUserToken
     */
    public function getUserToken(
        int $userId,
        string $username = null,
        string $client_id = null,
        string $password = null,
        string $grant_type = 'password'
    )
    {
        try {
            $userToken = $this->userTokenRepository->findByUserId($userId);
            if ($userToken->getExpiresAt() <= time()) {
                $id = $userToken->getId();
                $userToken = $this->getNewUserToken($username, $password,$client_id, $grant_type);
                $userToken->setUserId($userId)->setId($id);

                $userToken = $this->userTokenRepository->update($userToken);
            }
        } catch (\Exception $e) {
            $userToken = $this->getNewUserToken($username, $password,$client_id, $grant_type);
            $userToken->setUserId($userId);
            $userToken = $this->userTokenRepository->insert($userToken);
        }

        return $userToken;
    }



    /**
     * get new token for a client with specified client_id & client_secret from oauth server
     *
     * @param $client_id
     * @param $client_secret
     * @param array $scopes
     * @return iToken
     */
    public function getNewClientToken($client_id, $client_secret, array $scopes = [])
    {
        $token = parent::getNewClientToken($client_id, $client_secret, $scopes);

        $tokenEntityAsArray = array_merge(
            $token->toArray(),
            [
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'credentials' => $token->getAccessToken()
            ]
        );

        unset($tokenEntityAsArray['access_token']);


        $this->tokenRepository->writeOrUpdate(TokenEntity::fromArray($tokenEntityAsArray));

        return $token;
    }


    /**
     *
     * This method register new user to oauth server
     *
     * @param string $name
     * @param string $password
     * @param array $identifiers
     * @param string $token
     * @return User
     * @throws TokenInvalidOrExpiredException
     * @internal param array $identifiers
     */
    public function register($name, $password, array $identifiers, $token)
    {
        try {
            $arrayUser = $this->handler->register($name, $password, $identifiers, $token)['result'];
        } catch (TokenInvalidOrExpiredException $e) {

            if ($token instanceof TokenEntity) {

                $refreshedAccessToken = $this->refreshToken($token);
                $arrayUser = $this->handler->register(
                    $name,
                    $password,
                    $identifiers,
                    $refreshedAccessToken
                )['result'];

            } else {
                throw $e;
            }
        }

        return User::fromArray($arrayUser);
    }


    /**
     *
     * This method get an user identifier like email or mobile
     * and check token data relates to who ?
     * consider this method should be called with client token (credentials)
     *
     * @param string $identifier_type
     * @param string $identifier_value
     * @param string $token
     * @return User
     * @throws InvalidIdentifierType
     * @throws \Exception
     */
    public function getWhois($identifier_type, $identifier_value, $token)
    {
        try {
            $arrayWhois = $this->handler->getWhois($identifier_type, $identifier_value, $token);
        } catch (TokenInvalidOrExpiredException $e) {

            if ($token instanceof TokenEntity) {

                $refreshedAccessToken = $this->refreshToken($token);

                $arrayWhois = $this->handler->getWhois(
                    $identifier_type,
                    $identifier_value,
                    $refreshedAccessToken
                );

            } else {
                throw $e;
            }
        }

        return User::fromArray($arrayWhois);
    }

    /**
     * Get a list of users given their ids.
     *
     * @param array $userIds
     * @param mixed $token users.read scope is required
     *
     * @throws TokenInvalidOrExpiredException
     * @throws OAuthServerUnhandledError
     *
     * @return array
     */
    public function getUsers(array $userIds, $token)
    {
        try {
            $users = $this->handler->getUsers($userIds, $token);
        } catch (TokenInvalidOrExpiredException $e) {

            $refreshedAccessToken = $this->refreshToken($token);

            $users = $this->handler->getUsers($userIds, $refreshedAccessToken);
        }

        $func = function ($user) {
            return User::fromArray($user);
        };

        return array_map($func, $users);

    }



}
