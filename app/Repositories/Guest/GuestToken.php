<?php

namespace App\Repositories\Guest;

use App\Repositories\Model;
use App\Repositories\DateTime;

/**
 *
 * @author Foster Asante <asantefoster22@gmail.com>
 *
 */
class GuestToken extends Model
{

    /**
     * @var string
     */
    private $secret;

    /**
     * @var $model_name
     */
    private $model_name;

    /**
     * @var fcm token
     */
    private $token;


    /**
     * @var $device_id
     */
    private $device_id;


    /**
     *
     * @var $version_release
     */
    private $version_release;


    /**
     * @var string
     */
    private $ip;

    /**
     *
     * @var string
     */
    private $client;


    /**
     * @var bool
     */
    private $is_physical_device;

    /**
     * Check if device is physical or not
     *
     * @return boolean
     */
    public function isPhysicalDevice()
    {
        return $this->is_physical_device;
    }

    /**
     * True or false physical device
     *
     * @param boolean $is_physical
     */
    public function setPhysicalDevice($is_physical)
    {
        $this->is_physical_device = $is_physical;
    }

    /**
     * Return model name
     *
     * @return string
     */
    public function getModelName(): ?string
    {
        return $this->model_name;
    }

    /** Sets model name
     * @param String|null $model_name
     * @return String
     */
    public function setModelName(?string $model_name)
    {
        $this->model_name = $model_name;
    }


    /**
     * Return device id
     *
     * @return string
     */
    public function getDeviceID(): ?string
    {
        return $this->device_id;
    }

    /** Sets device id
     * @param String|null $device_id
     * @return String
     */
    public function setDeviceID(?string $device_id)
    {
        $this->device_id = $device_id;
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
     * Returns FCM token
     *
     * @return string|NULL
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * Sets FCM token
     *
     * @param string $token
     */
    public function setToken(?string $token)
    {
        $this->token = $token;
    }


    /**
     * Return version release
     *
     * @return string
     */
    public function getVersionRelease(): ?String
    {
        return $this->version_release;
    }

    /** Sets version release
     * @param String|null $version_release
     * @return String
     */
    public function setVersionRelease(?String $version_release)
    {
        $this->version_release = $version_release;
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
    public function setIp(?String $ip)
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

    /**Checks if a token has expired
     * @return bool
     * @throws \Exception
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
     * @see App\Repositories\Model::toJson()
     */
    protected function toJson()
    {
        $content = get_object_vars($this);
        unset($content['ip']);

        return $content;
    }
}

