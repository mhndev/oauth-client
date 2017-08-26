<?php
namespace mhndev\oauthClient\interfaces\repository;

use mhndev\oauthClient\interfaces\entity\iToken;

/**
 * Interface iUserTokenRepository
 * @package mhndev\oauthClient\interfaces\repository
 */
interface iUserTokenRepository
{

    /**
     * @param string $username
     * @return iToken
     */
    function findByUsername(string $username);


    /**
     * @param string $username
     * @param iToken $token
     * @return mixed
     */
    function writeOrUpdate(string $username, iToken $token);

}
