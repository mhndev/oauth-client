<?php

namespace mhndev\oauthClient\Objects;

/**
 * Class User
 * @package mhndev\oauthClient\Objects
 */
class User extends BaseObject
{

    /**
     * @var mixed
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var \DateTime
     */
    protected $created_at;

    /**
     * @var \DateTime
     */
    protected $updated_at;

    /**
     * @var array
     */
    protected $identifiers;


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * @return array of Identifier object
     */
    public function getIdentifiers()
    {
        return $this->identifiers;
    }


    /**
     * @param array $identifiers
     * @return $this
     */
    public function setIdentifiers(array $identifiers)
    {
        $ar = [];

        foreach ($identifiers as $identifier){

            $ar[] = Identifier::fromArray($identifier);
        }

        $this->identifiers = $ar;

        return $this;
    }


}
