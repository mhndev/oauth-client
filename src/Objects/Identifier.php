<?php

namespace mhndev\oauthClient\Objects;

use mhndev\oauthClient\exceptions\InvalidIdentifierType;
use mhndev\valueObjects\implementations\MobilePhone;

/**
 * Class Identifier
 * @package mhndev\oauthClient\Objects
 */
class Identifier extends BaseObject
{

    const EMAIL ='email';

    const MOBILE = 'mobile';

    public static $valid_identifier_types = [ self::EMAIL, self::MOBILE ];

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
     * @param $identifier_type
     * @throws InvalidIdentifierType
     */
    public static function isValid($identifier_type)
    {
        if(! in_array($identifier_type, Identifier::$valid_identifier_types)){
            throw new InvalidIdentifierType(
                sprintf(
                    'valid identifiers are : %s, given : %s',
                    implode(' , ', Identifier::$valid_identifier_types),
                    $identifier_type
                )
            );
        }

    }


    /**
     * @param string $identifier_type
     * @param string $identifier_value
     * @return array
     * @throws \Exception
     */
    public static function toArray($identifier_type, $identifier_value)
    {
        self::isValid($identifier_type);

        if($identifier_type == Identifier::EMAIL){
            $result = [ $identifier_type => $identifier_value ];
        }

        //  ($identifier_type == Identifier::MOBILE)
        else {
            // mobile string with zero
            $msz = (new MobilePhone($identifier_value))->format(MobilePhone::WithZero);

            $result = [$identifier_type => $msz];
        }

        return $result;
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
