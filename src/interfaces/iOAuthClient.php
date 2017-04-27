<?php
namespace mhndev\oauthClient\interfaces;

use mhndev\oauthClient\exceptions\ConnectOAuthServerException;
use mhndev\oauthClient\exceptions\InvalidIdentifierType;
use mhndev\oauthClient\exceptions\InvalidTokenException;
use mhndev\oauthClient\exceptions\OAuthServerBadResponseException;
use mhndev\oauthClient\interfaces\entity\iToken;
use mhndev\oauthClient\Objects\TokenInfo;
use mhndev\oauthClient\Objects\User;
use mhndev\valueObjects\implementations\Token;

/**
 * Interface iClient
 * @package mhndev\oauthClient\interfaces
 */
interface iOAuthClient
{


    /**
     *
     * This method get a token instance and output token info which includes :
     *
     * 1 - token scopes
     * 2 - user object (if token is related to an user and not a client)
     *
     * @param Token $token
     * @return TokenInfo
     * @throws InvalidTokenException
     */
    public function getTokenInfo(Token $token);


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
    public function getClientToken($client_id, $client_secret, array $scopes = []);


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
    public function register($name, $password, array $identifiers, iToken $token);


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
