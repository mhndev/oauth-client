<?php
namespace mhndev\oauthClient\interfaces\handler;

use mhndev\digipeyk\exceptions\AccessDeniedException;
use mhndev\oauthClient\exceptions\ConnectOAuthServerException;
use mhndev\oauthClient\exceptions\InvalidIdentifierType;
use mhndev\oauthClient\exceptions\OAuthServerUnhandledError;
use mhndev\oauthClient\exceptions\TokenInvalidOrExpiredException;

/**
 * Interface iHandler
 * @package mhndev\oauthClient\interfaces\handler
 */
interface iHandler
{
    /**
     *
     * This method get a token instance and output token info which includes :
     *
     * 1 - token scopes
     * 2 - user object (if token is related to an user and not a client)
     *
     * @param string $token
     * @return array
     */
    public function getTokenInfo($token);


    /**
     * @param $client_id
     * @param $client_secret
     * @param array $scopes
     * @return mixed
     */
    public function getClientTokenFromOAuthServer($client_id, $client_secret, array $scopes = []);

    /**
     * This method register new user to oauth server
     *
     * @param string $name
     * @param string $password
     * @param array $identifiers
     * @param mixed (string or an object which implements __toString) $token
     * @return array
     * @throws \Exception
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
     * @param mixed (string or an object which implements __toString) $token
     *
     * @return array

     * @throws InvalidIdentifierType
     * @throws \Exception
     */
    public function getWhois($identifier_type, $identifier_value, $token);

    /**
     * Get a list of users given their ids.
     *
     * @param array $userIds
     * @param mixed $token  (string or an object which implements __toString)   users.read scope is required
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
     * @return mixed
     * @throws AccessDeniedException
     * @throws ConnectOAuthServerException
     * @throws \Exception
     */
    public function addIdentifier($token, $identifier_value, $identifier_type);


    /**
     * @param $token
     * @param $identifier_value
     * @param $identifier_type
     * @return mixed
     * @throws TokenInvalidOrExpiredException
     * @throws ConnectOAuthServerException
     * @throws \Exception
     */
    public function removeIdentifier($token, $identifier_value, $identifier_type);


    /**
     * @param $token
     * @param $identifier_value
     * @param $identifier_type
     * @return mixed
     * @throws TokenInvalidOrExpiredException
     * @throws ConnectOAuthServerException
     * @throws \Exception
     */
    public function verifyIdentifier($token, $identifier_value, $identifier_type);


}
