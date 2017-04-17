<?php
namespace mhndev\oauthClient;

use GuzzleHttp\Exception\ClientException;
use mhndev\oauthClient\exceptions\ConnectOAuthServerException;
use mhndev\oauthClient\exceptions\IdentifierNotFoundOnOauthServer;
use mhndev\oauthClient\exceptions\InvalidArgumentException;
use mhndev\oauthClient\exceptions\InvalidToken;
use mhndev\oauthClient\exceptions\InvalidIdentifierType;
use mhndev\oauthClient\exceptions\ModelNotFoundException;
use mhndev\oauthClient\exceptions\OAuthServerBadResponseException;
use mhndev\oauthClient\exceptions\OAuthServerUnhandledError;
use mhndev\oauthClient\exceptions\UserAlreadyExistOnOauthServer;
use mhndev\oauthClient\interfaces\entity\iToken;
use mhndev\oauthClient\interfaces\iOAuthClient;
use mhndev\oauthClient\Objects\TokenInfo;
use mhndev\oauthClient\Objects\User;
use mhndev\valueObjects\implementations\Token;

/**
 * Class Client
 * @package mhndev\digipeyk\services\oauth2
 */
class Client extends aClient implements iOAuthClient
{

    /**
     * @var iToken
     */
    protected $token = null;

    /**
     * @param Token $token
     * @return TokenInfo
     * @throws InvalidToken
     */
    public function getTokenInfo(Token $token)
    {
        try{
            $response = $this->client->get($this->endpoint(__FUNCTION__), [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => $token->__toString(),
                ],
            ]);
        }

        catch (ClientException $e){
            if($e->getCode() == 401){
                throw new InvalidToken('token is not valid.');
            }
        }

        catch (\Exception $e){
            var_dump($e->getMessage());
            die();
        }

        return TokenInfo::fromArray($this->getResult($response)['result']);
    }

    /**
     *
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
        try{
            $token = $this->getValidTokenForClientFromDataSource($client_id);
        }
        catch (ModelNotFoundException $e){
            $token = $this->getClientTokenFromOAuthServer($client_id, $client_secret);

            //write to database
            $this->tokenRepository->writeOrUpdate($token);
        }

        return $this->token = $token;
    }


    /**
     * @param string $name
     * @param string $password
     * @param array $identifiers
     * @return static
     * @throws UserAlreadyExistOnOauthServer
     */
    public function register($name, $password, array $identifiers)
    {
        try{
            $response = $this->client->post($this->endpoint(__FUNCTION__), [
                'headers' => [ 'Accept' => 'application/json' ],
                'json'    => array_merge($identifiers, [
                    'name'     => $name,
                    'password' => $password
                ])
            ]);
        }

        catch (ClientException $e){

            if($e->getCode() == 422){
                $responseBody = $this->getResult($e->getResponse());

                $user = User::fromArray($responseBody['error']['user']);

                if($responseBody['error']['error_codes'] == 'userAlreadyExist'){
                    throw new UserAlreadyExistOnOauthServer(
                        'user already exist',
                        $user,
                        $e->getResponse()
                    );
                }

                throw new InvalidArgumentException(
                    json_encode($responseBody['error']['errors'])
                );
            }

        }

        catch (\Exception $e){
            var_dump($e->getMessage());
            die();
        }


        return User::fromArray($this->getResult($response));
    }


    /**
     * @param string $identifier_type
     * @param string $identifier_value
     * @param iToken|null $token
     * @return TokenInfo
     * @throws InvalidIdentifierType
     * @throws \Exception
     */
    public function getWhois($identifier_type, $identifier_value, iToken $token)
    {
        $this->checkIdentifierIsValid($identifier_type);

        $uri = $this->endpoint(__FUNCTION__);
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => $token->__toString(),
        ];

        $query = $this->createIdentifierArray($identifier_type, $identifier_value);

        try{
            $response = $this->client->get($uri, [
                'headers' => $headers,
                'query' => $query
            ]);
        }
        catch (ClientException $e){

            if($e->getCode() == 404){
                throw new IdentifierNotFoundOnOauthServer(sprintf(
                    'identifier with %s = %s', $identifier_type, $identifier_value
                ));
            }

            if($e->getCode() == 401){
                throw new InvalidToken(sprintf(
                    'client token is not valid'
                ));
            }

            if($e->getCode() == 500){
                throw new OAuthServerUnhandledError(sprintf(
                    'oauth server unhandled exception'
                ));
            }
        }


        return TokenInfo::fromArray($this->getResult($response));

    }





}
