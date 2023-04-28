<?php
declare(strict_types = 1);
namespace App\Repositories\Auth;

use App\Repositories\Model;
use App\Repositories\DateTime;

/**
 *
 * @author Foste Asante <asantefoster22@gmail.com>
 *        
 */
class Token extends Model
{

    /**
     *
     * @var string
     */
    private $secret;

    /**
     *
     * @var DateTime
     */
    private $expires_at;

    /**
     *
     * @var string
     */
    private $ip;

    /**
     *
     * @var string
     */
    private $client;

    /**
     *
     * @var string
     */
    private $push_token;

    /**
     *
     * @var bool
     */
    private $push_enabled;

    /**
     * Tells if push notification has been enabled for this token
     *
     * @return boolean
     */
    public function isPushEnabled()
    {
        return $this->push_enabled;
    }

    /**
     * Enables or disables push notification for this token
     *
     * @param boolean $push_enabled
     */
    public function setPushEnabled($push_enabled)
    {
        $this->push_enabled = $push_enabled;
    }

    /**
     * Return push notification token associated with this token
     *
     * @return string
     */
    public function getPushToken(): ?string
    {
        return $this->push_token;
    }

    /**
     * Sets push notification token associated with this token
     *
     * @param string $push_token
     */
    public function setPushToken(?string $push_token): void
    {
        $this->push_token = $push_token;
    }

    /**
     * Returns access token
     *
     * @return string|NULL
     */
    public function getSecret(): ?string
    {
        return $this->secret;
    }

    /**
     * Sets access token
     *
     * @param string $token
     */
    public function setSecret(?string $token)
    {
        $this->secret = $token;
    }

    /**
     * Returns datetime at which token expires
     *
     * @return DateTime|NULL
     */
    public function getExpiresAt(): ?DateTime
    {
        return $this->expires_at;
    }

    /**
     * Sets datetime at which access token expires
     *
     * @param DateTime $datetime
     */
    public function setExpiresAt(?DateTime $datetime)
    {
        $this->expires_at = $datetime;
    }

    /**
     * Returns IP for access token
     *
     * @return string|NULL
     */
    public function getIp(): ?string
    {
        return $this->ip;
    }

    /**
     * Sets IP for access token
     *
     * @param string $ip
     */
    public function setIp(?string $ip)
    {
        $this->ip = $ip;
    }

    /**
     * Returns client on which token was created
     *
     * @return string|NULL
     */
    public function getClient(): ?string
    {
        return $this->client;
    }

    /**
     * Sets client on which token was created
     *
     * @param string $client
     */
    public function setClient(?string $client)
    {
        $this->client = $client;
    }

    /**
     * Checks if a token has expired
     *
     * @return bool
     */
    public function hasExpired(): bool
    {
        if ($this->expires_at instanceof DateTime) {
            return $this->expires_at <= new DateTime();
        }

        return true;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Rentify\Api\Repositories\Model::toJson()
     */
    protected function toJson()
    {
        $content = get_object_vars($this);
        unset($content['ip'], $content['client'], $content['push_token']);

        return $content;
    }
}

