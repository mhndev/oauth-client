<?php
namespace mhndev\oauthClient;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use mhndev\oauthClient\entity\common\Token;
use mhndev\oauthClient\exceptions\ConnectOAuthServerException;
use mhndev\oauthClient\exceptions\InvalidIdentifierType;
use mhndev\oauthClient\exceptions\ModelNotFoundException;
use mhndev\oauthClient\exceptions\OAuthServerBadResponseException;
use mhndev\oauthClient\interfaces\entity\iToken;
use mhndev\oauthClient\interfaces\repository\iTokenRepository;
use mhndev\oauthClient\Objects\Identifier;
use mhndev\valueObjects\implementations\MobilePhone;
use Psr\Http\Message\ResponseInterface;

/**
 * Class aClient
 * @package mhndev\digipeyk\services\oauth2
 */
abstract class aClient
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $serverUrl;


    /**
     * @var iTokenRepository
     */
    protected $tokenRepository;

    /**
     * Client constructor.
     * @param \GuzzleHttp\Client $client
     * @param string $serverUrl
     * @param iTokenRepository $tokenRepository
     */
    public function __construct(
        \GuzzleHttp\Client $client,
        $serverUrl,
        iTokenRepository $tokenRepository
    )
    {
        $this->client = $client;
        $this->serverUrl = $serverUrl;
        $this->tokenRepository = $tokenRepository;
    }


    /**
     * @param $client_id
     * @param $client_secret
     *
     * @throws ConnectOAuthServerException
     * @throws OAuthServerBadResponseException
     * @throws \Exception
     *
     * @return iToken
     */
    protected function getClientTokenFromOAuthServer($client_id, $client_secret)
    {
        $uri = $this->endpoint(__FUNCTION__);

        $options = [
            'headers' => [
                'Accept' => 'application/json'
            ],
            'json' => [
                'grant_type'    => 'client_credentials',
                'client_id'     => $client_id,
                'client_secret' => $client_secret
            ]
        ];

        try{
            $response = $this->client->post($uri, $options);

        }catch(ConnectException $e){

            throw new ConnectOAuthServerException(
                sprintf(
                    'could not establish connection to oauth server (%s)',
                    $this->serverUrl
                )
            );

        }catch (ClientException $e){

            throw new OAuthServerBadResponseException($e->getMessage());
        }

        catch (\Exception $e){
            throw $e;
        }

        $tokenArray = $this->getResult($response);

        return Token::fromArray([
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'type'          => $tokenArray['token_type'],
            'credentials'   => $tokenArray['access_token'],
            'expires_in'    => $tokenArray['expires_in']
        ]);
    }


    /**
     * @param $client_id
     *
     * @throws ModelNotFoundException
     *
     * @return iToken
     */
    protected function getValidTokenForClientFromDataSource($client_id)
    {
        $token = $this->tokenRepository->findByClientId($client_id);

        if($token->getExpiresAt() < (new \DateTime() )){
            throw new ModelNotFoundException('token expired.');
        }

        return $token;
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
     * @param $identifier_type
     * @throws InvalidIdentifierType
     */
    protected function checkIdentifierIsValid($identifier_type)
    {
        if(! in_array($identifier_type, Identifier::$valid_identifier_types)){
            throw new InvalidIdentifierType(
                sprintf(
                    'valid identifiers are : %s, given : %s',
                    implode(' , ', Identifier::$valid_identifier_types),
                    $identifier_type
                )
            );
        }

    }


    /**
     * @param string $identifier_type
     * @param string $identifier_value
     * @return array
     * @throws \Exception
     */
    protected function createIdentifierArray($identifier_type, $identifier_value)
    {
        if($identifier_type == Identifier::EMAIL){
            $result = [ $identifier_type => $identifier_value ];
        }
        elseif ($identifier_type == Identifier::MOBILE){
            $result = [ $identifier_type => (new MobilePhone($identifier_value))->toArray() ];
        }
        else{
            throw new \Exception('????????');
        }

        return $result;
    }

    /**
     * @param $method
     * @return string
     */
    protected function endpoint($method)
    {
        switch ($method){

            case 'getClientTokenFromOAuthServer':
                return $this->serverUrl.'/auth/token';
                break;

            case 'getTokenInfo':
                return $this->serverUrl.'/api/getTokenInfo';
                break;

            case 'getWhois':
                return $this->serverUrl.'/api/whois';
                break;

            case 'register':
                return $this->serverUrl.'/api/registerUser';
                break;
        }

    }


}
