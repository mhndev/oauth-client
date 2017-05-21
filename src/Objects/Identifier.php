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

    const ID = 'id';

    const OAUTH_IDENTIFIER = 'oauthIdentifier';

    public static $valid_identifier_types = [ self::EMAIL, self::MOBILE, self::ID, self::OAUTH_IDENTIFIER ];

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
        $result = [];

        if($identifier_type == self::EMAIL){
            $result = [ $identifier_type => $identifier_value ];
        }
        //  ($identifier_type == Identifier::MOBILE)
        elseif ($identifier_type == self::MOBILE) {
            // mobile string with zero
            if ($identifier_value instanceof MobilePhone){
                $msz = $identifier_value->format(MobilePhone::WithZero);
            }
            else{
                $msz = (new MobilePhone($identifier_value))->format(MobilePhone::WithZero);
            }

            $result = [$identifier_type => $msz];

        }
        elseif($identifier_type == self::ID){
            $result = [$identifier_type => $identifier_value];
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
