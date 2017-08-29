<?php
namespace mhndev\oauthClient\interfaces\repository;

use mhndev\oauthClient\interfaces\object\iUserToken;

/**
 * Interface iUserTokenRepository
 * @package mhndev\oauthClient\interfaces\repository
 */
interface iUserTokenRepository
{

    /**
     * @param int $userId
     * @return iUserToken
     */
    function findByUserId(int $userId);


    /**
     * @param int $userId
     * @param iUserToken $token
     * @return mixed
     */
    function writeOrUpdate(int $userId, iUserToken $token);

    /**
     * @param iUserToken $token
     * @return iUserToken
     */
    function insert(iUserToken $token);

    /**
     * @param iUserToken $token
     * @return iUserToken
     */
    function update(iUserToken $token);

}
