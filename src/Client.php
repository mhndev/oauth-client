<?php
namespace mhndev\oauthClient;

use mhndev\oauthClient\exceptions\InvalidIdentifierType;
use mhndev\oauthClient\exceptions\OAuthServerUnhandledError;
use mhndev\oauthClient\exceptions\TokenInvalidOrExpiredException;
use mhndev\oauthClient\interfaces\handler\iHandler;
use mhndev\oauthClient\interfaces\iOAuthClient;
use mhndev\oauthClient\interfaces\object\iToken;
use mhndev\oauthClient\interfaces\object\iUserToken;
use mhndev\oauthClient\Objects\Identifier;
use mhndev\oauthClient\Objects\Token;
use mhndev\oauthClient\Objects\TokenInfo;
use mhndev\oauthClient\Objects\User;
use mhndev\oauthClient\objects\UserToken;

/**
 * Class Client
 * @package mhndev\digipeyk\services\oauth2
 */
class Client implements iOAuthClient
{

    /**
     * @var iHandler
     */
    protected $handler;

    /**
     * Client constructor.
     * @param iHandler $handler
     */
    public function __construct(iHandler $handler)
    {
        $this->handler = $handler;
    }


    /**
     *
     * This method get a token instance and output token info which includes :
     *
     * 1 - token scopes
     * 2 - user object (if token is related to an user and not a client)
     *
     * @param string $token
     * @return TokenInfo
     */
    public function getTokenInfo($token)
    {
        $arrayTokenInfo = $this->handler->getTokenInfo($token);

        return TokenInfo::fromArray($arrayTokenInfo['result']);
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
    public function getClientToken($client_id, $client_secret, array $scopes =[])
    {
        return $this->getNewClientToken($client_id, $client_secret, $scopes);
    }

    /**
     * @param $client_id
     * @param $client_secret
     * @param array $scopes
     * @return iToken
     */
    public function getNewClientToken($client_id, $client_secret, array $scopes  =[])
    {
        $arrayToken = $this->handler->getClientTokenFromOAuthServer(
            $client_id,
            $client_secret
        );

        $arrayToken['type'] = $arrayToken['token_type'];
        unset($arrayToken['token_type']);

        $token = Token::fromOptions($arrayToken);

        return $token;
    }


    /**
     * @param $username
     * @param $password
     * @param $client_id
     * @param string $grant_type
     * @return iUserToken
     */
    public function getNewUserToken(
        string $username,
        string $password,
        string $client_id,
        string $grant_type = 'password'
    )
    {
        $arrayToken = $this->handler->getUserTokenFromOAuthServer(
            $username,
            $password,
            $client_id,
            $grant_type
        );


        $token = UserToken::fromOptions($arrayToken);

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
     * @internal param array $identifiers
     */
    public function register($name, $password, array $identifiers, $token)
    {
        $arrayUser = $this->handler->register($name, $password, $identifiers, $token)['result'];

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
        $arrayWhois = $this->handler->getWhois('id', $identifier_value, $token);

        return User::fromArray($arrayWhois);
    }

    /**
     * Get a list of users given their ids.
     *
     * @param array $userIds
     * @param string $token     users.read scope is required
     *
     * @throws TokenInvalidOrExpiredException
     * @throws OAuthServerUnhandledError
     *
     * @return array
     */
    public function getUsers(array $userIds, $token)
    {
        $users = $this->handler->getUsers($userIds, $token);

        return array_map(function ($user) {
            return User::fromArray($user);
        }, $users);
    }


    /**
     * @param $token
     * @param $identifier_value
     * @param $identifier_type
     * @return Identifier
     */
    public function addIdentifier($token, $identifier_value, $identifier_type)
    {
        $identifierArray = $this->handler->addIdentifier($token, $identifier_value, $identifier_type);

       return Identifier::fromArray($identifierArray);

    }


    /**
     * @param $token
     * @param $identifier_value
     * @param $identifier_type
     * @return true
     */
    public function removeIdentifier($token, $identifier_value, $identifier_type)
    {
        return $this->handler->removeIdentifier($token, $identifier_value, $identifier_type);

    }

    /**
     * @param $token
     * @param $identifier_value
     * @param $identifier_type
     * @param $sessionChallenge
     * @param $client_id
     * @return true
     */
    public function verifyIdentifier($token, $identifier_value, $identifier_type, $sessionChallenge, $client_id)
    {
        return $this->handler->verifyIdentifier($token, $identifier_value, $identifier_type, $sessionChallenge, $client_id);    }


    /**
     * @param $token
     * @param $user_id
     * @return true
     */
    public function unverifyIdentifier($token, $user_id)
    {
        return $this->handler->unverifyIdentifier($token, $user_id);
    }

    /**
     * @param $token
     * @param $identifier_key
     * @param $identifier_value
     * @return mixed
     */
    public function searchForUser($token, $identifier_key, $identifier_value)
    {
        $users = $this->handler->searchForUser($token, $identifier_key, $identifier_value)['result'];

        return array_map(function ($user) {
            return User::fromArray($user);
        }, $users);
    }

    /**
     * @param string $token
     * @param string $identifierKey
     * @param string $identifierValue
     * @param integer $userId
     * @return mixed
     */
    public function verifyIdentifierByAdmin($token, $identifierKey, $identifierValue, $userId)
    {
        return $this->handler->verifyIdentifierByAdmin($token, $identifierKey, $identifierValue, $userId);
    }

    /**
     * @param int $userId
     * @param string $username
     * @param string|null $client_id
     * @param string|null $password
     * @param string $grant_type
     * @return interfaces\entity\iToken
     */
    public function getUserToken(
        int $userId,
        string $username = null,
        string $client_id = null,
        string $password = null,
        string $grant_type = 'password'
    )
    {
        // TODO: Implement getUserToken() method.
    }
}
