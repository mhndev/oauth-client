<?php
namespace mhndev\oauthClient\interfaces\repository;

use mhndev\oauthClient\interfaces\entity\iToken;

/**
 * Interface iTokenRepository
 * @package mhndev\digipeyk\services\oauth2\interfaces
 */
interface iTokenRepository
{

    /**
     * @param $client_id
     * @return iToken
     */
    function findByClientId($client_id);


    /**
     * @param iToken $token
     * @return mixed
     */
    function writeOrUpdate(iToken $token);

}
