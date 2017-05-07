<?php
namespace mhndev\oauthClient\interfaces\entity;

/**
 * Interface iToken
 * @package mhndev\digipeyk\services\oauth2\interfaces\entity
 */
interface iToken extends \mhndev\oauthClient\interfaces\object\iToken
{

    /**
     * @return string
     */
    function getType();

    /**
     * @return string
     */
    function getCredentials();

    /**
     * @return string
     */
    function getClientId();

    /**
     * @return string
     */
    function getClientSecret();

    /**
     * @return \DateTime
     */
    function getExpiresAt();
}
