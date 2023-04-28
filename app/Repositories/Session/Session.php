<?php

namespace App\Repositories\Session;

use App\Repositories\DateTime;

/**
 *
 * @author Foste Asante <asantefoster22@gmail.com>
 *
 */
class Session
{
    /**
     * @var  string
     */
    private $name;

    /**
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
    private $id;


    /**
     * Returns session  name
     *
     * @return string|NULL
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set session  name
     *
     * @param string|null $name
     */

    public function setName(?string $name)
    {
        $this->name = $name;
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
     * Sets datetime at which access session expires
     *
     * @param DateTime $datetime
     */
    public function setExpiresAt(?DateTime $datetime)
    {
        $this->expires_at = $datetime;
    }

    /**
     * Returns ID of user
     *
     * @return string|NULL
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Sets ID of user
     *
     * @param string $id
     */
    public function setID(?string $id)
    {
        $this->id = $id;
    }


    /**
     * Checks if a token has expired
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


}

