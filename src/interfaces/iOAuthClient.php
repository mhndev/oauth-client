<?php
namespace mhndev\oauthClient\interfaces;

use mhndev\oauthClient\exceptions\ConnectOAuthServerException;
use mhndev\oauthClient\exceptions\InvalidIdentifierType;
use mhndev\oauthClient\exceptions\OAuthServerBadResponseException;
use mhndev\oauthClient\exceptions\UserAlreadyExistOnOauthServer;
use mhndev\oauthClient\interfaces\entity\iToken;
use mhndev\oauthClient\Objects\TokenInfo;

/**
 * Interface iClient
 * @package mhndev\oauthClient\interfaces
 */
interface iOAuthClient
{

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
    public function getClientToken($client_id, $client_secret);


    /**
     *
     * This method register new user to oauth server
     *
     * @param string $name
     * @param string $password
     * @param array $identifiers
     * @return static
     * @throws UserAlreadyExistOnOauthServer
     */
    public function register($name, $password, array $identifiers);


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
    public function getWhois($identifier_type, $identifier_value, iToken $token);
}
