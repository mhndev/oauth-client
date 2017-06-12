<?php
namespace mhndev\oauthClient\handlers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use mhndev\oauthClient\exceptions\ClientNotFoundException;
use mhndev\oauthClient\exceptions\ConnectOAuthServerException;
use mhndev\oauthClient\exceptions\IdentifierNotFoundOnOauthServer;
use mhndev\oauthClient\exceptions\InvalidIdentifierException;
use mhndev\oauthClient\exceptions\InvalidIdentifierType;
use mhndev\oauthClient\exceptions\InvalidTokenException;
use mhndev\oauthClient\exceptions\NonRemovableIdentifierException;
use mhndev\oauthClient\exceptions\OAuthServerBadResponseException;
use mhndev\oauthClient\exceptions\OAuthServerConnectionException;
use mhndev\oauthClient\exceptions\OAuthServerUnhandledError;
use mhndev\oauthClient\exceptions\TokenInvalidOrExpiredException;
use mhndev\oauthClient\exceptions\UserAlreadyExistOnOauthServer;
use mhndev\oauthClient\exceptions\ValidationException;
use mhndev\oauthClient\interfaces\handler\iHandler;
use mhndev\oauthClient\interfaces\object\iToken;
use mhndev\oauthClient\Objects\Identifier;
use mhndev\oauthClient\Objects\User;
use Psr\Http\Message\ResponseInterface;

/**
 * This Handler is Guzzle handler and send actual http requests to the oauth server
 *
 * This Handler throw following Exceptions :
 *
 *  1) GuzzleHttp\Exception\ServerException :
 *      This exception is thrown when oauth server has response status code
 *      greater than or equal to 500, which means oauth server has faced and error
 *      (un handled error)
 *
 *  2) GuzzleHttp\Exception\ClientException
 *      This exception is thrown when oauth server return response with status code
 *      ( 400 <= statusCode < 500)
 *      for example 422 which means un processable entity and is for validation exception
 *      and 404 is used when user not found on oauth server
 *      and 403 is used when access denied for a token
 *
 *  3) GuzzleHttp\Exception\ConnectException
 *      This exception is thrown when oauth client cannot connect to oauth server
 *      maybe network issue, proxy issue , ... or whatever
 *
 *
 * Class GuzzleHandler
 * @package mhndev\oauthClient\handlers
 */
class GuzzleHandler implements iHandler
{


    /**
     * @var Client
     */
    protected $httpClient;


    /**
     * @var string
     */
    protected $serverUrl;


    /**
     * GuzzleHandler constructor.
     *
     * @param Client $client
     * @param $serverUrl
     */
    public function __construct(Client $client, $serverUrl)
    {
        $this->httpClient = $client;
        $this->serverUrl = $serverUrl;
    }


    /**
     *
     * This method get a token instance and output token info which includes :
     *
     * 1 - token scopes
     * 2 - user object (if token is related to an user and not a client)
     *
     * @param string $token
     * @return ResponseInterface
     * @throws OAuthServerConnectionException
     * @throws TokenInvalidOrExpiredException
     * @throws \Exception
     */
    public function getTokenInfo($token)
    {
        try {
            $response = $this->httpClient->get($this->endpoint(__FUNCTION__), [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => (string)$token,
                ],
            ]);

        } catch (ClientException $e) {

            // provided token is not valid or has expired
            if ($e->getCode() == 401) {
                throw new TokenInvalidOrExpiredException(sprintf(
                    'your provided token is : %s which is expired or invalid.',
                    (string)$token
                ));
            } else {
                /*
                 * do nothing, here is when there is an error which I never thought of and should
                 * handled in someway
                 */
                throw $e;
            }


        } catch (ConnectException $e) {

            throw new OAuthServerConnectionException($e->getMessage(), $e->getCode());

        } catch (\Exception $e) {
            /*
             * do nothing, here is when there is an error which I never thought of and should
             * handled in someway
             */

            throw $e;
        }

        return $this->getResult($response);
    }


    /**
     * @param $client_id
     * @param $client_secret
     * @param array $scopes
     * @return mixed
     * @throws ClientNotFoundException
     * @throws ConnectOAuthServerException
     * @throws OAuthServerBadResponseException
     * @throws \Exception
     */
    public function getClientTokenFromOAuthServer($client_id, $client_secret, array $scopes = [])
    {
        $uri = $this->endpoint(__FUNCTION__);

        $json = [
            'grant_type' => 'client_credentials',
            'client_id' => $client_id,
            'client_secret' => $client_secret
        ];

        if (!empty($scopes)) {
            $json['scope'] = implode(' ', $scopes);
        }

        $options = [
            'headers' => [
                'Accept' => 'application/json'
            ],
            'json' => $json
        ];

        try {
            $response = $this->httpClient->post($uri, $options);

        } catch (ConnectException $e) {

            throw new ConnectOAuthServerException(
                sprintf(
                    'could not establish connection to oauth server (%s)',
                    $this->serverUrl
                )
            );

        } catch (ClientException $e) {

            if ($e->getCode() == 401) {
                throw new ClientNotFoundException(sprintf(
                    'client with id %s is not valid by oauth server',
                    $client_id
                ));
            }

            throw new OAuthServerBadResponseException($e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }


        return $this->getResult($response);
    }


    /**
     * This method register new user to oauth server
     *
     * @param string $name
     * @param string $password
     * @param array $identifiers
     * @param iToken $token
     * @return array
     * @throws \Exception
     */
    public function register($name, $password, array $identifiers, $token)
    {
        try {
            $response = $this->httpClient->post($this->endpoint(__FUNCTION__), [
                'headers' => ['Accept' => 'application/json'],
                'json' => array_merge($identifiers, [
                    'name' => $name,
                    'password' => $password
                ])
            ]);

        } catch (ClientException $e) {

            // un processable entity (usually validation error)
            if ($e->getCode() == 422) {

                $this->ifUserAlreadyExistThrowException(
                    $e->getResponse(),
                    $identifiers,
                    (string)$token
                );

                throw $this->getValidationException($e->getResponse());
            } else {
                throw $e;
            }

        } catch (\Exception $e) {
            //do nothing
            throw $e;
        }

        return $this->getResult($response);
    }


    /**
     * @param ResponseInterface $registerEndpointResponse
     * @param array $identifiers
     * @param string $token
     * @throws UserAlreadyExistOnOauthServer
     */
    protected function ifUserAlreadyExistThrowException(
        ResponseInterface $registerEndpointResponse,
        array $identifiers,
        $token
    )
    {
        $responseBody = $this->getResult($registerEndpointResponse);

        // check whether error is user already exist in database or not
        if (!empty($responseBody['error']['info']['failed'])) {

            foreach ($responseBody['error']['info']['failed'] as $failed_rules) {

                foreach ($failed_rules as $key => $value) {

                    if (is_array($value) && $key = 'UniqueIdentifier') {

                        $user = User::fromArray(
                            $this->getWhois($value[0], $identifiers[$value[0]], (string)$token)
                        );

                        throw new UserAlreadyExistOnOauthServer(
                            'user already exist',
                            $user,
                            Identifier::fromArray([
                                'type' => $value[0],
                                'value' => $identifiers[$value[0]],
                                'verified' => true
                            ]),
                            $registerEndpointResponse
                        );

                    }
                }
            }

        }
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
     *
     * @return array
     * @throws InvalidIdentifierType
     * @throws \Exception
     */
    public function getWhois($identifier_type, $identifier_value, $token)
    {
        $uri = $this->endpoint(__FUNCTION__);

        $headers = [
            'Accept' => 'application/json',
            'Authorization' => (string)$token,
        ];

        $query = Identifier::toArray($identifier_type, $identifier_value);


        $options = ['headers' => $headers, 'query' => $query];

        try {
            $response = $this->httpClient->get($uri, $options);

        }
        catch (ClientException $e) {

            if ($e->getCode() == 404) {

                throw new IdentifierNotFoundOnOauthServer(sprintf(
                    'identifier with %s = %s', $identifier_type, $identifier_value
                ));
            }

            if ($e->getCode() == 401) {
                throw new TokenInvalidOrExpiredException(sprintf(
                    'client token is not valid'
                ));
            }

            if ($e->getCode() >= 500) {
                throw new OAuthServerUnhandledError(sprintf(
                    'oauth server unhandled exception'
                ));
            }

            else{
                throw $e;
            }


        }
        catch (\Exception $e) {
            throw $e;
        }

        return $this->getResult($response);

    }


    /**
     * Get a list of users given their ids.
     *
     * @param array $userIds
     * @param mixed $token users.read scope is required
     *
     * @param bool $returnIdentifiers
     * @return array
     * @throws OAuthServerUnhandledError
     * @throws TokenInvalidOrExpiredException
     */
    public function getUsers(array $userIds, $token, $returnIdentifiers = true)
    {
        $uri = $this->serverUrl . '/api/getUsers';
        $options = [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => (string)$token,
            ],
            'query' => [
                'ids' => $userIds,
            ],
        ];
        if ($returnIdentifiers) {
            $options['query']['identifiers'] = 1;
        }

        try {
            $response = $this->httpClient->get($uri, $options);
        }

        catch (ClientException $e) {

            if ($e->getCode() == 401) {
                throw new TokenInvalidOrExpiredException(sprintf(
                    'client token is not valid'
                ));
            }

            if ($e->getCode() >= 500) {
                throw new OAuthServerUnhandledError(sprintf(
                    'oauth server unhandled exception'
                ));
            }

            else{
                throw $e;
            }
        }

        return $this->getResult($response);
    }


    /**
     * @param $token
     * @param $identifier_value
     * @param $identifier_type
     * @return mixed
     * @throws TokenInvalidOrExpiredException
     * @throws ConnectOAuthServerException
     * @throws \Exception
     */
    public function addIdentifier($token, $identifier_value, $identifier_type)
    {

        $uri = $this->endpoint(__FUNCTION__);
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => (string)$token,
            'Accept-Language' => 'fa'
        ];

        $json = [
            $identifier_type => $identifier_value
        ];

        $options = [
            'headers' => $headers,
            'json'    => $json
        ];

        try {
            $response = $this->httpClient->post($uri, $options);
        }
        catch (ConnectException $e) {
            throw new ConnectOAuthServerException(
                sprintf(
                    'could not establish connection to oauth server (%s)',
                    $this->serverUrl
                )
            );

        }
        catch (ClientException $e) {

            if ($e->getCode() == 401) {
                throw new TokenInvalidOrExpiredException();
            }

            if ($e->getCode() == 422) {

                throw $this->getValidationException($e->getResponse());
            }

            else{
                throw $e;
            }
        }
        catch (\Exception $e) {
            throw $e;
        }

        return $this->getResult($response);

    }



    /**
     * @param $token
     * @param $identifier_value
     * @param $identifier_type
     * @return mixed
     * @throws TokenInvalidOrExpiredException
     * @throws ConnectOAuthServerException
     * @throws \Exception
     */
    public function removeIdentifier($token, $identifier_value, $identifier_type)
    {
        $uri = $this->endpoint(__FUNCTION__);

        $headers = [
            'Accept' => 'application/json',
            'Authorization' => (string)$token,
            'Accept-Language' => 'fa'
        ];

        $json = [ $identifier_type => $identifier_value];

        $options = ['headers' => $headers, 'json' => $json];


        try {
            $response = $this->httpClient->post($uri, $options);
        }
        catch (ConnectException $e) {

            throw new ConnectOAuthServerException(
                sprintf(
                    'could not establish connection to oauth server (%s)',
                    $this->serverUrl
                )
            );

        }
        catch (ClientException $e) {

            if ($e->getCode() == 401) {
                throw new TokenInvalidOrExpiredException();
            }

            if ($e->getCode() == 422) {
                throw $this->getValidationException($e->getResponse());
            }
            else{
                throw $e;
            }

        }
        catch (\Exception $e) {
            throw $e;
        }


        $result = $this->getResult($response)['result'];

        if (!$result){
            throw new NonRemovableIdentifierException();
        }

        return $result;
    }




    /**
     * @param $token
     * @param $identifier_value
     * @param $identifier_type
     * @return mixed
     * @throws TokenInvalidOrExpiredException
     * @throws ConnectOAuthServerException
     * @throws \Exception
     */
    public function verifyIdentifier($token, $identifier_value, $identifier_type)
    {
        $uri = $this->endpoint(__FUNCTION__);

        $headers = [
            'Accept' => 'application/json',
            'Accept-Language' => 'fa'
        ];

        $json = [
            $identifier_type => $identifier_value,
            'token'          => $token
        ];

        $options = [
            'headers' => $headers,
            'json'    => $json
        ];

        try {
            $response = $this->httpClient->post($uri, $options);
        }
        catch (ConnectException $e) {
            throw new ConnectOAuthServerException(
                sprintf(
                    'could not establish connection to oauth server (%s)',
                    $this->serverUrl
                )
            );

        }
        catch (ClientException $e) {

            if ($e->getCode() == 401) {
                throw new InvalidTokenException();
            }

            if ($e->getCode() == 404) {
                throw new InvalidIdentifierException();
            }

            if ($e->getCode() == 422) {

                throw $this->getValidationException($e->getResponse());
            }
            else{
                throw $e;
            }

        }
        catch (\Exception $e) {
            throw $e;
        }


        $result = $this->getResult($response);

        return $result;

    }

    /**
     * @param $token
     * @param $identifier_key
     * @param $identifier_value
     * @return mixed
     * @throws ConnectOAuthServerException
     * @throws InvalidTokenException
     * @throws \Exception
     */
    public function searchForUser($token, $identifier_key, $identifier_value)
    {
        $uri = $this->endpoint(__FUNCTION__);

        $headers = [
            'Accept' => 'application/json',
            'Accept-Language' => 'fa',
            'Authorization'  => (string)$token
        ];
        $query = [$identifier_key => $identifier_value];

        $options = [
            'headers' => $headers,
            'query'    => $query
        ];

        try {
            $response = $this->httpClient->get($uri, $options);

        } catch (ConnectException $e) {
            throw new ConnectOAuthServerException(
                sprintf(
                    'could not establish connection to oauth server (%s)',
                    $this->serverUrl
                )
            );

        } catch (ClientException $e) {

            if ($e->getCode() == 401) {
                throw new InvalidTokenException();
            }
            if ($e->getCode() == 422) {

                $this->throwValidationException($e->getResponse());
            }
        } catch (\Exception $e) {
            throw $e;
        }


        return $this->getResult($response);
    }


    /**
     * @param ResponseInterface $response
     * @param bool $returnArray
     * @return mixed
     */
    protected function getResult(ResponseInterface $response, $returnArray = true)
    {
        return json_decode($response->getBody()->getContents(), $returnArray);
    }


    /**
     * @param $method
     * @return string
     */
    protected function endpoint($method)
    {
        switch ($method) {

            case 'getClientTokenFromOAuthServer':
                return $this->serverUrl . '/api/auth/token';
                break;

            case 'getTokenInfo':
                return $this->serverUrl . '/api/getTokenInfo';
                break;

            case 'getWhois':
                return $this->serverUrl . '/api/whois';
                break;

            case 'register':
                return $this->serverUrl . '/api/registerOrGetUser';
                break;

            case 'getUsers':
                return $this->serverUrl . '/api/getUsers';
                break;

            case 'addIdentifier':
                return $this->serverUrl . '/api/addIdentifier';
                break;

            case 'removeIdentifier':
                return $this->serverUrl . '/api/removeUserIdentifier';
                break;

            case 'verifyIdentifier':
                return $this->serverUrl . '/api/verifyIdentifier';
                break;
            case 'searchForUser':
                return $this->serverUrl . '/api/searchForUser';
                break;
        }


    }


    /**
     * @param ResponseInterface $response
     * @return  ValidationException
     */
    protected function getValidationException(ResponseInterface $response)

    {
        $responseBody = $this->getResult($response);

        return new ValidationException(
            $responseBody['error']['message'],
            422,
            $responseBody['error']['info']['messages'],
            $responseBody['error']['info']['failed']
        );

    }

    /**
     * @param $token
     * @param $identifier_key
     * @param $identifier_value
     * @return mixed
     */
    public function unverifyIdentifier($token, $identifier_key, $identifier_value)
    {
        // TODO: Implement unverifyIdentifier() method.
    }
}
