<?php
namespace mhndev\oauthClient\interfaces\entity;

/**
 * Interface iToken
 * @package mhndev\digipeyk\services\oauth2\interfaces\entity
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
    function getClientId();

    /**
     * @return string
     */
    function getClientSecret();

    /**
     * @return mixed
     */
    function __toString();

}
