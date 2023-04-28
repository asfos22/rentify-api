<?php

namespace App\Repositories\NotificationToken;

use App\Repositories\Auth\Auth;
use App\Repositories\Model;
use App\Repositories\DateTime;
use App\Repositories\User\User;

/**
 *
 * @author Foster Asante <asantefoster22@gmail.com>
 *
 */
class NotificationToken
{

    /**
     *
     * @var string
     */
    private $id;

    /**
     * @var
     */
    private $user_id;

    /**
     * @var
     */
    private $device_number;

    /**
     * @var
     */
    private $ip;


    /**
     * @var
     */
    private $client;


    /**
     * @var
     */
    private $platform;


    /**
     * @var
     */
    private $created_at;

    /**
     * @var
     */
    private $updated_at;
    /**
     * @var
     */
    private $deleted_at;

    /**
     *
     * @var string
     */
    private $push_token;

    /**
     *
     * @var string
     */
    private $ref_code;
    /**
     * @var  versionRelease
     */
    private $version_release;

    /**
     * @var
     */
    private $model_name;

    /**
     *
     * @var bool
     */
    private $is_device_physical;

    /**
     * Tells if device is physical or emulator
     * @var bool
     */
    private $push_enabled;

    /**
     * @var
     */

    private $auth;

    /**
     * 
     */
    private $user;

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
     * Returns id
     * @return string|NULL
     */
    public function getID(): ?string
    {
        return $this->id;
    }

    /**
     * Sets id
     * @param string $id
     */
    public function setID(?string $id)
    {
        $this->id = $id;
    }


    /**
     * Returns user id
     * @return Auth|null $auth
     */
    public function getAuth(): ?Auth
    {
        return $this->auth;
    }

    /**
     * Sets user
     * @param Auth|null $auth
     */
    public function setAuth(?Auth $auth)
    {
        $this->auth = $auth;
    }


    /**
     * Returns device number
     * @return string|NULL
     */
    public function getDeviceNumber(): ?string
    {
        return $this->device_number;
    }

    /**
     * Sets device number
     * @param string $deviceNumber
     */
    public function setDeviceNumberID(?string $deviceNumber)
    {
        $this->device_number = $deviceNumber;
    }


    /**
     * Returns datetime at created at
     *
     * @return DateTime|NULL
     */
    public function getCreatedAt(): ?DateTime
    {
        return $this->created_at;
    }

    /**
     * Sets datetime at created at
     *
     * @param DateTime $datetime
     */
    public function setCreatedAt(?DateTime $datetime)
    {
        $this->created_at = $datetime;
    }


    /**
     * Returns datetime at created at
     *
     * @return DateTime|NULL
     */
    public function getUpdatedAt(): ?DateTime
    {
        return $this->updated_at;
    }

    /**
     * Sets datetime at created at
     *
     * @param DateTime $datetime
     */
    public function setUpdatedAt(?DateTime $datetime)
    {
        $this->updated_at = $datetime;
    }


    /**
     * Returns datetime at created at
     *
     * @return DateTime|NULL
     */
    public function getDeletedAt(): ?DateTime
    {
        return $this->deleted_at;
    }

    /**
     * Sets datetime at created at
     *
     * @param DateTime $datetime
     */
    public function setDeletedAt(?DateTime $datetime)
    {
        $this->deleted_at = $datetime;
    }


    /**
     * Returns IP for access token
     * @return string|NULL
     */
    public function getIp(): ?string
    {
        return $this->ip;
    }

    /**
     * Sets IP for access token
     * @param string $ip
     */
    public function setIp(?string $ip)
    {
        $this->ip = $ip;
    }


    /**
     * Returns IP Client for access token
     * @return string|NULL
     */
    public function getClient(): ?string
    {
        return $this->client;
    }

    /**
     * Sets IP Client for access token
     * @param string $client
     */
    public function setClient(?string $client)
    {
        $this->client = $client;
    }

    /**
     * Returns platform for access token
     * @return string|NULL
     */
    public function getPlatform(): ?string
    {
        return $this->platform;
    }

    /**
     * Sets IP platform for access token
     * @param string $platform
     */
    public function setPlatform(?string $platform)
    {
        $this->platform = $platform;
    }

    /**
     * Returns device model name
     * @return string|NULL
     */
    public function getModelName(): ?string
    {
        return $this->model_name;
    }


    /**
     * Sets device model name
     * @param string|null $modelName
     */
    public function setModelName(?string $modelName)
    {
        $this->model_name = $modelName;
    }


    /**
     * Returns ref_code for fcm
     * @return string|NULL
     */
    public function getRefCode(): ?string
    {
        return $this->ref_code;
    }


    /**
     * Sets ref code for fcm
     * @param string $refCode
     */
    public function setRefCode(?string $refCode)
    {
        $this->ref_code = $refCode;
    }


    /**
     * Returns setVersionRelease fcm
     * @return string|NULL
     */
    public function getVersionRelease(): ?string
    {
        return $this->version_release;
    }


    /**
     * Sets ref code for fcm
     * @param string|null $versionRelease
     */
    public function setVersionRelease(?string $versionRelease)
    {
        $this->version_release = $versionRelease;
    }


    public function isPhysicalDevice()
    {
        return $this->is_device_physical;
    }

    /**
     * Set if device s physical or not
     * @param boolean $isDevicePhysical
     */
    public function setIsPhysicalDevice($isDevicePhysical)
    {
        $this->is_device_physical = $isDevicePhysical;
    }


     /**
     * Returns user 
     * @return User|null $user
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Sets user
     * @param User|null $user
     */
    public function setUser(?User $user)
    {
        $this->user = $user;
    }



    /**
     * @return array
     */
    protected function toJson()
    {
        $content = get_object_vars($this);
        unset($content['ip'], $content['client'], $content['push_token']);

        return $content;
    }
}

