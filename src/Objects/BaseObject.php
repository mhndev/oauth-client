<?php
namespace mhndev\oauthClient\Objects;

/**
 * Class BaseObject
 * @package mhndev\digipeyk\services\oauth2\Objects
 */
class BaseObject
{


    /**
     * proxy cal to fromOptions
     * for sugar syntax
     *
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data)
    {
        return self::fromOptions($data);
    }


    /**
     * @param $name
     * @param $value
     */
    function __set($name, $value)
    {
        $this->$name = $value;
    }


    /**
     * @param $name
     */
    function __unset($name)
    {
        unset($this->$name);
    }

    /**
     * @param array $options
     * @return static
     */
    static function fromOptions(array $options)
    {
        $instance = new static();

        foreach ($options as $key => $value){

            if(method_exists($instance, $method = 'set'.ucfirst($key) )){
                $instance->{$method}($value);
            }else{
                $instance->$key = $value;
            }
        }

        return $instance;
    }

    /**
     * @param array $options
     * @return $this
     */
    function buildByOptions(array $options)
    {
        foreach ($options as $key => $value){
            $this->$key = $value;
        }

        return $this;
    }


}
