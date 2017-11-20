<?php
namespace mhndev\oauthClient\interfaces;

use mhndev\oauthClient\exceptions\InvalidIdentifierType;
use mhndev\oauthClient\exceptions\OAuthServerUnhandledError;
use mhndev\oauthClient\exceptions\TokenInvalidOrExpiredException;
use mhndev\oauthClient\interfaces\entity\iToken;
use mhndev\oauthClient\Objects\Identifier;
use mhndev\oauthClient\Objects\TokenInfo;
use mhndev\oauthClient\Objects\User;

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
     * @param string $token
     * @return TokenInfo
     */
    public function getTokenInfo($token);


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
     * @return \mhndev\oauthClient\interfaces\entity\iToken
     */
    public function getClientToken($client_id, $client_secret, array $scopes = []);


    /**
     * @param int $userId
     * @param string $username
     * @param string|null $client_id
     * @param string|null $password
     * @param string $grant_type
     * @return iToken
     */
    public function getUserToken(
        int    $userId,
        string $username,
        string $client_id = null,
        string $password = null,
        string $grant_type = 'password'
    );


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
    public function register($name, $password, array $identifiers, $token);


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
    public function getWhois($identifier_type, $identifier_value, $token);

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
    public function getUsers(array $userIds, $token);



    /**
     * @param $token
     * @param $identifier_value
     * @param $identifier_type
     * @return Identifier
     */
    public function addIdentifier($token, $identifier_value, $identifier_type);


    /**
     * @param $token
     * @param $identifier_value
     * @param $identifier_type
     * @return true
     */
    public function removeIdentifier($token, $identifier_value, $identifier_type);

    /**
     * @param $token
     * @param $identifier_value
     * @param $identifier_type
     * @param $sessionChallenge
     * @param $client_id
     * @return boolean
     */
    public function verifyIdentifier(
        $token,
        $identifier_value,
        $identifier_type,
        $sessionChallenge,
        $client_id
    );

    /**
     * @param $token
     * @param $user_id
     * @return true
     */
    public function unverifyIdentifier($token, $user_id);

    /**
     * @param $token
     * @param $identifier_key
     * @param $identifier_value
     * @return mixed
     */
    public function searchForUser($token, $identifier_key, $identifier_value);

    /**
     * @param string $token
     * @param string $identifierKey
     * @param string $identifierValue
     * @param integer $userId
     * @return mixed
     */
    public function verifyIdentifierByAdmin(
        $token,
        $identifierKey,
        $identifierValue,
        $userId
    );

}
