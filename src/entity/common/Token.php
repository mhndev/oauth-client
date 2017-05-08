<?php
namespace mhndev\oauthClient\entity\common;

use mhndev\oauthClient\interfaces\entity\iToken;

/**
 * Class Token
 * @package mhndev\digipeyk\services\oauth2\entity\common
 */
class Token implements iToken
{

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $credentials;

    /**
     * @var string
     */
    protected $client_id;

    /**
     * @var string
     */
    protected $client_secret;

    /**
     * @var integer
     */
    protected $expires_in;

    /**
     * @var \DateTime
     */
    protected $expires_at;

    /**
     * Token constructor.
     * @param string $type
     * @param string $credentials
     * @param string $client_id
     * @param string $client_secret
     * @param integer $expires_in
     * @param \DateTime $expires_at
     */
    public function __construct(
        $type,
        $credentials,
        $client_id,
        $client_secret,
        $expires_in = null,
        \DateTime $expires_at = null
    )
    {
        $this->type = $type;
        $this->credentials = $credentials;
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;

        if(!empty($expires_in)){
            $this->expires_in = $expires_in;
        }

        if(!empty($expires_at)){
            $this->expires_at = $expires_at;
        }

        if(!empty($expires_in) && empty($expires_at)){
            $this->expires_at = (new \DateTime())->setTimestamp((int)$this->expires_in + time());
        }


    }


    /**
     * @param array $token
     * @return static
     */
    public static function fromArray(array $token)
    {
        return new static(
            $token['type'],
            $token['credentials'],
            $token['client_id'],
            $token['client_secret'],
            !empty($token['expires_in']) ? $token['expires_in'] : null,
            !empty($token['expires_at']) ? $token['expires_at'] : null
        );
    }


    /**
     * @return string
     */
    function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * @return string
     */
    function getClientId()
    {
        return $this->client_id;
    }

    /**
     * @return string
     */
    function getClientSecret()
    {
        return $this->client_secret;
    }

    /**
     * @return \DateTime
     */
    function getExpiresAt()
    {
        return $this->expires_at;
    }

    /**
     * @return int
     */
    public function getExpiresIn()
    {
        return $this->expires_in;
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return $this->type.' '.$this->credentials;
    }

    /**
     * @return string
     */
    function getAccessToken()
    {
        return $this->credentials;
    }
}
