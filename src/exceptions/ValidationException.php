<?php
namespace mhndev\oauthClient\exceptions;

use Throwable;

/**
 * Class ValidationException
 * @package mhndev\oauthClient\exceptions
 */
class ValidationException extends \Exception
{

    /**
     * @var array
     */
    protected $failed_messages;

    /**
     * @var array
     */
    protected $failed_rules;


    /**
     * Construct the exception. Note: The message is NOT binary safe.
     * @link http://php.net/manual/en/exception.construct.php
     * @param string $message [optional] The Exception message to throw.
     * @param int $code [optional] The Exception code.
     * @param array $failed_messages
     * @param array $failed_rules
     * @param Throwable $previous [optional] The previous throwable used for the exception chaining.
     * @since 5.1.0
     */
    public function __construct(
        $message = "",
        $code = 0,
        array $failed_messages = [],
        array $failed_rules = [],
        Throwable $previous = null
    )
    {
        parent::__construct($message, $code, $previous);

        $this->failed_rules = $failed_rules;
        $this->failed_messages = $failed_messages;
    }


    /**
     * @return array
     */
    public function getFailedRules()
    {
        return $this->failed_rules;
    }


    /**
     * @return array
     */
    public function getFailedMessages()
    {
        return $this->failed_messages;
    }

}
