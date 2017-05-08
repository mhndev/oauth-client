<?php
namespace mhndev\oauthClient\interfaces\object;

/**
 * Interface iToken
 * @package mhndev\oauthClient\interfaces\object
 */
interface iToken
{
    /**
     * @return string
     */
    function getType();

    /**
     * @return string
     */
    function getAccessToken();

    /**
     * @return string | null
     */
    function getRefreshToken();

    /**
     * @return \DateTime
     */
    function getExpiresAt();

    /**
     * @return integer
     */
    function getExpiresIn();

    /**
     * @return string
     */
    function __toString();

    /**
     * @return array
     */
    function toArray();
}
