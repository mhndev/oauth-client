<?php
namespace mhndev\oauthClient;

use mhndev\oauthClient\exceptions\ModelNotFoundException;
use mhndev\oauthClient\interfaces\entity\iToken;
use mhndev\oauthClient\interfaces\handler\iHandler;
use mhndev\oauthClient\interfaces\iOAuthClient;
use mhndev\oauthClient\interfaces\repository\iTokenRepository;

/**
 * This Client class handles token
 * for himself to it persist tokens for future requests
 *
 *
 * Class ClientTokenHandle
 * @package mhndev\oauthClient
 */
class ClientTokenHandle extends Client implements iOAuthClient
{


    /**
     * @var iTokenRepository
     */
    protected $tokenRepository;


    /**
     * ClientTokenHandle constructor.
     *
     * @param iHandler $handler
     * @param iTokenRepository $tokenRepository
     */
    public function __construct(iHandler $handler, iTokenRepository $tokenRepository)
    {
        parent::__construct($handler);

        $this->tokenRepository = $tokenRepository;
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
    public function getClientToken($client_id, $client_secret, array $scopes = [])
    {
        try{
            $token = $this->tokenRepository->findByClientId($client_id);

            if($token->getExpiresAt() <= new \DateTime() ){
                $token = $this->getNewClientToken($client_id, $client_secret);
            }
        }
        catch (ModelNotFoundException $e) {
            $token = $this->getNewClientToken($client_id, $client_secret);
        }

        return $token;
    }

    public function getNewClientToken($client_id, $client_secret, array $scopes = [])
    {
        $token = parent::getNewClientToken($client_id, $client_secret, $scopes);
        $this->tokenRepository->writeOrUpdate($token);
        return $token;
    }

}
