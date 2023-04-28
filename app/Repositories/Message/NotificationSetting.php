<?php
declare(strict_types = 1);
namespace App\Repositories\Message;

use App\Repositories\Model;

class NotificationSetting extends Model
{

    /**
     *
     * @var bool
     */
    private $push;

    /**
     *
     * @var bool
     */
    private $sms;

    /**
     *
     * @var bool
     */
    private $email;

    /**
     * Inidicates whether push notification is enabled
     *
     * @return boolean
     */
    public function isPushEnabled(): bool
    {
        return $this->push ?? false;
    }

    /**
     * Inidicates whether sms notification is enabled
     *
     * @return boolean
     */
    public function isSmsEnabled(): bool
    {
        return $this->sms ?? false;
    }

    /**
     * Inidicates whether email notification is enabled
     *
     * @return boolean
     */
    public function isEmailEnabled(): bool
    {
        return $this->email ?? false;
    }

    /**
     *
     * @param boolean $push
     */
    public function setPush(?bool $push): void
    {
        $this->push = $push;
    }

    /**
     *
     * @param boolean $sms
     */
    public function setSms(?bool $sms): void
    {
        $this->sms = $sms;
    }

    /**
     *
     * @param boolean $email
     */
    public function setEmail(?bool $email): void
    {
        $this->email = $email;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Model::toJson()
     */
    protected function toJson()
    {
        return get_object_vars($this);
    }
}

