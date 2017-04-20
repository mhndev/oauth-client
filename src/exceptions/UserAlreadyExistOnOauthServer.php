<?php
namespace mhndev\oauthClient\exceptions;

use mhndev\oauthClient\Objects\Identifier;
use mhndev\oauthClient\Objects\User;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Class UserAlreadyExistOnOauthServer
 * @package mhndev\oauthClient\exceptions
 */
class UserAlreadyExistOnOauthServer extends \Exception
{

    /**
     * @var User
     */
    private $user;

    /**
     * @var Identifier
     */
    private $identifier;

    /**
     * UserAlreadyExistOnOauthServer constructor.
     * @param string $message
     * @param User $user
     * @param Identifier $identifier
     * @param ResponseInterface $response
     * @param Throwable|null $previous
     */
    public function __construct(
        $message = "",
        User $user,
        Identifier $identifier,
        ResponseInterface $response,
        Throwable $previous = null
    )
    {

        $code = $response->getStatusCode();
        $this->user = $user;
        $this->identifier = $identifier;
        parent::__construct($message, $code, $previous);
    }


    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }


    /**
     * @return Identifier
     */
    public function getDuplicateIdentifier()
    {
        return $this->identifier;
    }



}
