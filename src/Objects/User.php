<?php
namespace mhndev\oauthClient\Objects;

use mhndev\oauthClient\exceptions\InvalidArgumentException;
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
     * @var string
     */
    protected $avatar_url;

    /**
     * @var \DateTime
     */
    protected $created_at;

    /**
     * @var \DateTime
     */
    protected $updated_at;

    /**
     * @var string
     */
    protected $session_challenge;

    /**
     * @var Identifiers
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
     * @return Identifiers of Identifier object
     */
    public function getIdentifiers()
    {
        return $this->identifiers;
    }


    /**
     * @param array | Identifiers $identifiers
     * @return $this
     */
    public function setIdentifiers($identifiers)
    {
        if($identifiers instanceof Identifiers){
            $this->identifiers = $identifiers;
        }
        elseif (is_array($identifiers)){

            $ar = [];

            foreach ($identifiers as $identifier) {
                $ar[] = Identifier::fromArray($identifier);
            }

            $this->identifiers = new Identifiers($ar);
        }
        else{
            throw new InvalidArgumentException(sprintf(
                'just array or %s are acceptable, given : %s',
                Identifiers::class,
                is_object($identifiers) ? get_class($identifiers) : gettype($identifiers)
            ));
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getAvatarUrl()
    {
        return $this->avatar_url;
    }

    /**
     * @return string
     */
    public function getSessionChallenge()
    {
        return $this->session_challenge;
    }

    /**
     * @param $sessionChallenge
     * @internal param string $session_challenge
     */
    public function setSessionChallenge($sessionChallenge)
    {
        $this->session_challenge = $sessionChallenge;
    }


}
