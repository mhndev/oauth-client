<?php

namespace mhndev\oauthClient\Objects;

/**
 * Class Identifier
 * @package mhndev\oauthClient\Objects
 */
class Identifier extends BaseObject
{

    const EMAIL ='email';

    const MOBILE = 'mobile';

    public static $valid_identifier_types = [self::EMAIL, self::MOBILE];

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var bool
     */
    protected $verified;

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
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return null|true
     */
    public function getVerified()
    {
        return $this->verified;
    }

}
