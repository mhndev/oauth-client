<?php
namespace mhndev\oauthClient\interfaces\object;

/**
 * Interface iUserToken
 * @package mhndev\oauthClient\interfaces\object
 */
interface iUserToken
{
    /**
     * @return int
     */
    function getId();

    /**
     * @param int $id
     */
    function setId(int $id);

    /**
     * @return string
     */
    function getAccessToken();

    /**
     * @return string | null
     */
    function getRefreshToken();

    /**
     * @return int
     */
    function getUserId();

    /**
     * @return int
     */
    function getExpiresAt();


    /**
     * @return string
     */
    function __toString();

    /**
     * @return array
     */
    function toArray();
}
