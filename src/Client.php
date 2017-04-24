<?php
namespace mhndev\oauthClient;

use mhndev\oauthClient\entity\common\Token as EntityToken;
use mhndev\oauthClient\exceptions\ConnectOAuthServerException;
use mhndev\oauthClient\exceptions\InvalidIdentifierType;
use mhndev\oauthClient\exceptions\InvalidTokenException;
use mhndev\oauthClient\exceptions\OAuthServerBadResponseException;
use mhndev\oauthClient\interfaces\entity\iToken;
use mhndev\oauthClient\interfaces\handler\iHandler;
use mhndev\oauthClient\interfaces\iOAuthClient;
use mhndev\oauthClient\Objects\TokenInfo;
use mhndev\oauthClient\Objects\User;
use mhndev\valueObjects\implementations\Token as TokenValueObject;

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
     * @param TokenValueObject $token
     * @return TokenInfo
     * @throws InvalidTokenException
     */
    public function getTokenInfo(TokenValueObject $token)
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
     * @throws ConnectOAuthServerException
     * @throws OAuthServerBadResponseException
     * @throws \Exception
     *
     * @return iToken
     */
    public function getClientToken($client_id, $client_secret)
    {
        return $this->getNewClientToken($client_id, $client_secret);
    }

    /**
     * @param $client_id
     * @param $client_secret
     * @return iToken
     */
    protected function getNewClientToken($client_id, $client_secret)
    {
        $arrayToken = $this->handler->getClientTokenFromOAuthServer(
            $client_id,
            $client_secret
        );

        $arrayToken['client_id'] = $client_id;
        $arrayToken['credentials'] = $arrayToken['access_token'];
        $arrayToken['type'] = $arrayToken['token_type'];
        unset($arrayToken['token_type'], $arrayToken['access_token']);
        $arrayToken['client_secret'] = $client_secret;

        $token = EntityToken::fromArray($arrayToken);

        return $token;
    }

    /**
     *
     * This method register new user to oauth server
     *
     * @param string $name
     * @param string $password
     * @param array $identifiers
     * @param iToken $token
     * @return User
     * @internal param array $identifiers
     */
    public function register($name, $password, array $identifiers, iToken $token)
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
     * @param iToken|null $token
     * @return TokenInfo
     * @throws InvalidIdentifierType
     * @throws \Exception
     */
    public function getWhois($identifier_type, $identifier_value, iToken $token)
    {
        $arrayWhois = $this->handler->getWhois($identifier_type, $identifier_value, $token);

        return TokenInfo::fromArray($arrayWhois);
    }

}
