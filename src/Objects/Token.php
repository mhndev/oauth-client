<?php
namespace mhndev\oauthClient\Objects;

use mhndev\digipeyk\services\oauth2\exceptions\InvalidArgumentException;
use MongoDB\Database;

/**
 * Class Token
 * @package mhndev\digipeyk\services\oauth2\Objects
 */
class Token
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
     * @var integer second
     */
    protected $expires_in;

    /**
     * @var \DateTime
     */
    protected $expires_at;


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
     * @param mixed $credentials
     * @param string $type
     * @param null | integer $expires_in
     */
    public function __construct($credentials, $type = self::SCHEMA_Basic, $expires_in = null)
    {

        if(is_string($credentials)){
            $this->type = $type;
            $this->credentials = $credentials;
            $this->expires_in = $expires_in;

            if(!empty($this->expires_in)){
                $this->expires_at = new \DateTime(time() + $this->expires_in);
            }
        }

        elseif (is_array($credentials)){
            if(empty($credentials['type'] || empty($credentials['credentials']))){

                throw new InvalidArgumentException(sprintf(
                    'input array should contain type and credentials keys'
                ));
            }

            else{
                $this->type = $credentials['type'];
                $this->credentials = $credentials['credentials'];
                $this->expires_in = !empty($credentials['expires_in']) ? $credentials['expires_in'] : null;

                if(!empty($this->expires_in)){
                    $this->expires_at = new \DateTime(time() + $this->expires_in);
                }
            }
        }

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
    public function getCredentials()
    {
        return $this->credentials;
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
            'type' => $this->type,
            'credentials' => $this->credentials
        ];
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return $this->type. ' '. $this->credentials;
    }


}
