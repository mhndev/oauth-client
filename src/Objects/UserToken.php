<?php
/*
 * This file is part of the Digipeyk Software.
 *
 * (c) Majid Abdolhosseini <majid@mhndev.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace mhndev\oauthClient\Objects;

use mhndev\oauthClient\interfaces\object\iUserToken;

class UserToken implements iUserToken
{


    /**
     * BaseEntityUserToken constructor.
     * @param $id
     * @param $access_token
     * @param $refresh_token
     * @param $user_id
     * @param $expires_in
     * @param $expires_at
     */
    public function __construct($id, $access_token, $refresh_token, $user_id, $expires_in = null, $expires_at = null)
    {
        $this->id = $id;
        $this->access_token = $access_token;
        $this->refresh_token = $refresh_token;
        $this->user_id = $user_id;
        if ($expires_at){
            $this->expires_at = $expires_at;
        }
        if ($expires_in){
            $this->expires_at = time() + $expires_in;
        }
    }

    /**
     * @var int|null
     */
    protected $id;

    /**
     * @var string
     */
    protected $access_token;

    /**
     * @var string
     */
    protected $refresh_token;

    /**
     * @var int
     */
    protected $expires_at;

    /**
     * @var int
     */
    protected $user_id;

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id = null)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->access_token;
    }

    /**
     * @param string $access_token
     */
    public function setAccessToken(string $access_token)
    {
        $this->access_token = $access_token;
    }

    /**
     * @return string
     */
    public function getRefreshToken(): string
    {
        return $this->refresh_token;
    }

    /**
     * @param string $refresh_token
     */
    public function setRefreshToken(string $refresh_token)
    {
        $this->refresh_token = $refresh_token;
    }

    /**
     * @return int
     */
    public function getExpiresAt(): int
    {
        return $this->expires_at;
    }

    /**
     * @param int $expires_at
     */
    public function setExpiresAt(int $expires_at)
    {
        $this->expires_at = $expires_at;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     * @return $this
     */
    public function setUserId(int $user_id)
    {
        $this->user_id = $user_id;

        return $this;
    }

    /**
     * @return string
     */
    function __toString()
    {
        return $this->getAccessToken();
    }

    /**
     * @return array
     */
    function toArray()
    {
        return [
            'id' => $this->getId(),
            'access_token' => $this->getAccessToken(),
            'refresh_token' => $this->getRefreshToken(),
            'user_id' => $this->getUserId(),
            'expires_at' => $this->getExpiresAt(),
        ];
    }

    /**
     * @param $array
     * @return static
     */
    public static function fromOptions($array)
    {
        $id = !empty($array['id']) ? $array['id'] : null;
        $userId = !empty($array['user_id']) ? $array['user_id'] : null;

        return new static(
            $id,
            $array['access_token'],
            $array['refresh_token'],
            $userId,
            null,
            $array['expires_at']
        );
    }

    /**
     * @param $array
     * @return static
     */
    public static function fromOauthArray($array)
    {
        $id = !empty($array['id']) ? $array['id'] : null;
        $userId = !empty($array['user_id']) ? $array['user_id'] : null;

        return new static(
            $id,
            $array['access_token'],
            $array['refresh_token'],
            $userId,
            $array['expires_in']
        );
    }
}
