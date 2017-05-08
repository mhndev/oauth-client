<?php
namespace mhndev\oauthClient\Objects;

use mhndev\oauthClient\interfaces\object\iToken;
use mhndev\phpStd\ObjectBuilder;

/**
 * Class Token
 * @package mhndev\digipeyk\services\oauth2\Objects
 */
class Token implements iToken
{

    use ObjectBuilder {
        fromOptions as parentFromOptions;
    }


    /**
     * @var string
     */
    protected $type;


    /**
     * @var string
     */
    protected $access_token;

    /**
     * @var integer second
     */
    protected $expires_in;

    /**
     * @var \DateTime
     */
    protected $expires_at;

    /**
     * @var string
     */
    protected $refresh_token;


    const SCHEMA_Basic  = 'Basic';
    const SCHEMA_Bearer = 'Bearer';
    const SCHEMA_Digest = 'Digest';
    const SCHEMA_HOBA   = 'HOBA';
    const SCHEMA_Mutual = 'Mutual';
    const SCHEMA_AWS    = 'AWS4-HMAC-SHA256';


    public static $validated_schemas = [
        self::SCHEMA_Basic,
        self::SCHEMA_Bearer,
        self::SCHEMA_Digest,
        self::SCHEMA_HOBA,
        self::SCHEMA_Mutual,
        self::SCHEMA_AWS
    ];


    /**
     * Token constructor.
     * @param string $access_token
     * @param string $type
     * @param null | string  $refresh_token
     * @param null | integer $expires_in
     */
    public function __construct(
        $access_token = null,
        $type = self::SCHEMA_Basic,
        $refresh_token = null,
        $expires_in = null
    )
    {
        $this->access_token = $access_token;
        $this->type = $type;
        $this->expires_in = $expires_in;
        $this->refresh_token = $refresh_token;
        $this->expires_at = (new \DateTime())->setTimestamp($expires_in + time() );
    }


    /**
     * @param array $options
     * @return static
     */
    public static function fromOptions(array $options)
    {
        $object = self::parentFromOptions($options);

        $object->setExpiresAt(
            (new \DateTime())->setTimestamp($object->getExpiresIn() + time() )
        );

        return $object;
    }


    /**
     * @param array $options
     * @return Token
     */
    public static function fromArray(array $options)
    {
        return self::fromOptions($options);
    }


    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->access_token;
    }

    /**
     * @return int|null
     */
    public function getExpiresIn()
    {
        return $this->expires_in;
    }


    /**
     * @return \DateTime
     */
    public function getExpiresAt()
    {
        return $this->expires_at;
    }


    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'type'         => $this->type,
            'access_token' => $this->access_token,
            'expires_in'   => $this->getExpiresIn()
        ];
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return $this->type. ' '. $this->access_token;
    }


    /**
     * @return string | null
     */
    function getRefreshToken()
    {
        return $this->refresh_token;
    }


    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param string $access_token
     * @return $this
     */
    public function setAccessToken($access_token)
    {
        $this->access_token = $access_token;

        return $this;
    }

    /**
     * @param int $expires_in
     * @return $this
     */
    public function setExpiresIn($expires_in)
    {
        $this->expires_in = $expires_in;

        return $this;
    }

    /**
     * @param \DateTime $expires_at
     * @return $this
     */
    public function setExpiresAt($expires_at)
    {
        $this->expires_at = $expires_at;

        return $this;
    }

    /**
     * @param string $refresh_token
     * @return $this
     */
    public function setRefreshToken($refresh_token)
    {
        $this->refresh_token = $refresh_token;

        return $this;
    }

}
