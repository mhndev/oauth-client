<?php
namespace mhndev\oauthClient\Objects;

use mhndev\oauthClient\exceptions\InvalidArgumentException;
use mhndev\phpStd\Collection;

/**
 * Class Identifiers
 * @package mhndev\oauthClient\Objects
 */
class Identifiers extends Collection
{

    /**
     * Identifiers constructor.
     * @param array $items
     */
    public function __construct($items = [])
    {
        foreach ($items as $item){
            if(! $item instanceof Identifier){
                throw new InvalidArgumentException(sprintf(
                    'all items should be instance of %s given : %s',
                    Identifier::class,
                    is_object($item) ? get_class($item) : gettype($item)
                ));
            }
        }

        parent::__construct($items);
    }

    /**
     * @return integer
     */
    public function countVerifiedIdentifiers()
    {
        return $this->getVerifiedIdentifiers()->count();
    }



    /**
     * @return integer
     */
    public function countVerifiedEmailIdentifiers()
    {
        $count = 0;

        /** @var Identifier $item */
        foreach ($this->items as $item){
            if($item->getVerified() && $item->getType() == Identifier::EMAIL){
                $count ++;
            }
        }

        return $count;
    }


    /**
     * @return integer
     */
    public function countVerifiedMobileIdentifiers()
    {
        return $this->countVerifiedSpecificIdentifiers(Identifier::MOBILE);
    }

    
    /**
     * @param $identifier_type
     * @return int
     */
    protected function countVerifiedSpecificIdentifiers($identifier_type)
    {
        return $this->getVerifiedSpecificIdentifiers($identifier_type)->count();
    }


    /**
     * @param $identifier_type
     * @return static
     */
    protected function getVerifiedSpecificIdentifiers($identifier_type)
    {
        $verified = [];

        /** @var Identifier $item */
        foreach ($this->items as $item){
            if($item->getVerified() && $item->getType() == $identifier_type){
                $verified[] = $item;
            }
        }

        return new static($verified);
    }


    /**
     * @return boolean
     */
    public function hasEmailIdentifier()
    {
        return ($this->getMobileIdentifiers()->count() > 0 );
    }

    /**
     * @return Identifiers
     */
    public function getMobileIdentifiers()
    {
        $mobileIdentifiers = [];

        /** @var Identifier $item */
        foreach ($this->items as $item){
            if($item->getType() == Identifier::MOBILE){
                $mobileIdentifiers[] = $item;
            }
        }

        return new static($mobileIdentifiers);
    }


    /**
     * @return Identifiers
     */
    public function getEmailIdentifiers()
    {
        $mobileIdentifiers = [];

        /** @var Identifier $item */
        foreach ($this->items as $item){
            if($item->getType() == Identifier::EMAIL){
                $mobileIdentifiers[] = $item;
            }
        }

        return new static($mobileIdentifiers);
    }


    /**
     * @return boolean
     */
    public function hasMobileIdentifier()
    {
        return ($this->getMobileIdentifiers()->count() > 0 );
    }


    /**
     * @return boolean
     */
    public function hasVerifiedEmailIdentifier()
    {
        return ($this->getVerifiedEmailIdentifiers()->count() > 0 );
    }

    /**
     * @return boolean
     */
    public function hasVerifiedMobileIdentifier()
    {
        return ($this->getVerifiedMobileIdentifiers()->count() > 0 );
    }


    /**
     * @return Identifiers
     */
    public function getVerifiedIdentifiers()
    {
        $verifiedIdentifiers = [];

        /** @var Identifier $item */
        foreach ($this->items as $item){
            if($item->getVerified()){
                $verifiedIdentifiers[] = $item;
            }
        }

        return new static($verifiedIdentifiers);
    }


    /**
     * @return Identifiers
     */
    public function getVerifiedEmailIdentifiers()
    {
        return $this->getVerifiedSpecificIdentifiers(Identifier::EMAIL);
    }


    /**
     * @return Identifiers
     */
    public function getVerifiedMobileIdentifiers()
    {
        return $this->getVerifiedSpecificIdentifiers(Identifier::MOBILE);
    }

    
    /**
     * @return Identifier | null
     */
    public function getFirstEmailVerifiedIdentifier()
    {
        $result = $this->getVerifiedEmailIdentifiers();
        
        if($result->count() > 0 ){
            return $result->first();
        }
        
        return null;
    }

    
    /**
     * @return Identifier
     */
    public function getLastEmailVerifiedIdentifier()
    {
        $result = $this->getVerifiedEmailIdentifiers();

        if($result->count() > 0 ){
            return $result->last();
        }

        return null;
    }


    /**
     * @return Identifier
     */
    public function getLastMobileVerifiedIdentifier()
    {
        $result = $this->getVerifiedMobileIdentifiers();

        if($result->count() > 0 ){
            return $result->last();
        }

        return null;
    }

    /**
     * @return Identifier
     */
    public function getFirstMobileVerifiedIdentifier()
    {
        $result = $this->getVerifiedMobileIdentifiers();

        if($result->count() > 0 ){
            return $result->first();
        }

        return null;
    }

}
