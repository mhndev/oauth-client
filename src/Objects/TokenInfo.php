<?php
namespace mhndev\oauthClient\Objects;

/**
 * Class TokenInfo
 * @package mhndev\oauthClient\Objects
 */
class TokenInfo extends BaseObject
{

    const TOKEN_USER = 'user';
    const TOKEN_CLIENT = 'client';

    /**
     * @var User
     */
    protected $user;

    /**
     * @var array
     */
    protected $scopes;

    /**
     * @var
     */
    protected $type;


    /**
     * @return array
     */
    public function getScopes()
    {
        return $this->scopes;
    }


    /**
     * @param  $userData
     * @return $this
     */
    function setUser($userData)
    {
        if(empty($userData)){
            $this->user = null;
            $this->type = self::TOKEN_CLIENT;
        }
        elseif (is_array($userData)){
            $this->type = self::TOKEN_USER;
            $this->user = User::fromArray($userData);
        }

        return $this;
    }


    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }


    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

}
