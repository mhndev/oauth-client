<?php
namespace mhndev\oauthClient\handlers;

use mhndev\oauthClient\exceptions\ConnectOAuthServerException;
use mhndev\oauthClient\exceptions\InvalidIdentifierType;
use mhndev\oauthClient\exceptions\OAuthServerBadResponseException;
use mhndev\oauthClient\exceptions\OAuthServerUnhandledError;
use mhndev\oauthClient\exceptions\TokenInvalidOrExpiredException;
use mhndev\oauthClient\interfaces\entity\iToken;
use mhndev\oauthClient\interfaces\handler\iHandler;
use mhndev\valueObjects\implementations\Token;

/**
 * Class MockHandler
 * @package mhndev\oauthClient\handlers
 */
class MockHandler implements iHandler
{

    /**
     *
     * This method get a token instance and output token info which includes :
     *
     * 1 - token scopes
     * 2 - user object (if token is related to an user and not a client)
     *
     * @param Token $token
     * @param array $options
     * @return array
     * @throws \Exception
     */
    public function getTokenInfo(Token $token, array $options = [])
    {
        if(empty($options['token_owner'])){
            $options['token_owner'] = 'user';
        }

        if($options['token_owner'] == 'client'){
            $result = [
                'scopes' => [
                    '*'
                ],
                'user' => null
            ];

        } elseif($options['token_owner'] == 'user' ) {

            $result = [
                'scopes' => [
                    '*'
                ],
                'user' => [
                    'id' => 12,
                    'name' => 'majid',
                    'created_at' => '2017-04-04 10:54:55',
                    'updated_at' => '2017-04-04 10:54:55',
                    'identifiers' => [
                        [
                            'id' => 5,
                            'type' => 'email',
                            'value' => 'majid8911303@gmail.com',
                            'verified' => true
                        ],
                        [
                            'id' => 5,
                            'type' => 'mobile',
                            'value' => '09124971706',
                            'verified' => null
                        ],

                    ]

                ]
            ];
        }

        else {
            throw new \Exception(
                'invalid token owner, token owner just can be client or user.'
            );
        }

        return [
            'status' => 'OK',
            'result' => $result
        ];

    }

    /**
     *
     * This method register new user to oauth server
     *
     * @param string $name
     * @param string $password
     * @param array $identifiers
     * @param iToken $token
     * @return array
     * @internal param array $identifiers
     */
    public function register($name, $password, array $identifiers, iToken $token)
    {
        $result = [
            'status' => 'OK',
            'result' => [
                'id' => 12,
                'name' => $name,
                'created_at' => '2017-04-08 15:50:08',
                'updated_at' => '2017-04-08 15:50:08',
                'identifiers' => [
                    $identifiers
                ]
            ]
        ];

        return $result;
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
     * @return array
     * @throws InvalidIdentifierType
     * @throws \Exception
     */
    public function getWhois($identifier_type, $identifier_value, iToken $token)
    {
        $result = [
              "id" => 2,
              "name" => "مجید",
              "created_at" => "2017-04-04 10:54:55",
              "updated_at" => "2017-04-08 15:45:50",
              "identifiers" => [
                [
                  "id" => 3,
                  "type" => $identifier_type,
                  "value" => $identifier_value,
                  "verified" => true
                ],
                [
                  "id"=> 4,
                  "type"=> "mobile",
                  "value"=> "09134107672",
                  "verified"=> true
                ],
                [
                  "id"=> 9,
                  "type"=> "email",
                  "value"=> "hamid@gmail.com",
                  "verified"=> null
                ]
              ]
            ];

        return $result;

    }

    /**
     * Get a list of users given their ids.
     *
     * @param array $userIds
     * @param iToken $token     users.read scope is required
     *
     * @throws TokenInvalidOrExpiredException
     * @throws OAuthServerUnhandledError
     *
     * @return array
     */
    public function getUsers(array $userIds, iToken $token)
    {
        return [];
    }

    /**
     * @param $client_id
     * @param $client_secret
     * @param array $scopes
     * @return array
     * @throws ConnectOAuthServerException
     * @throws OAuthServerBadResponseException
     * @throws \Exception
     */
    public function getClientTokenFromOAuthServer($client_id, $client_secret, array $scopes = [])
    {
        $result = [
          "access_token"=> "45a2a110259a6a7cb6b481eacca2fa5f5aa67e61",
          "token_type"=> "Bearer",
          "expires_in"=> 3600,
          "client_id"=> $client_id
        ];

        return $result;
    }

}
