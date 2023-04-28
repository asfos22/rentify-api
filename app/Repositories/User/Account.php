<?php
declare(strict_types = 1);
namespace App\Repositories\User;

use App\Repositories\Model;
use App\Repositories\Role\Role;


class Account extends Model
{

    /**
     *
     * @var string
     */
    private $name;

    /**
     *
     * @var string
     */
    private $phone;

    /**
     *
     * @var string
     */
    private $email;

    /**
     *
     * @var bool
     */
    private $blocked;

    /**
     *
     * @var bool
     */
    private $parent_blocked;

    /**
     *
     * @var bool
     */
    private $activated;

    /**
     *
     * @var Role
     */
    private $role;

    /**
     *
     * @return string|NULL
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     *
     * @param string $name
     */
    public function setName(?string $name)
    {
        $this->name = $name;
    }

    /**
     *
     * @return string|NULL
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     *
     * @param string $phone
     */
    public function setPhone(?string $phone)
    {
        $this->phone = $phone;
    }

    /**
     *
     * @return string|NULL
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     *
     * @param string $email
     */
    public function setEmail(?string $email)
    {
        $this->email = $email;
    }

    /**
     * Sets a user's blocked status
     *
     * @param bool $blocked
     */
    public function setBlocked(?bool $blocked = true)
    {
        $this->blocked = $blocked;
    }

    /**
     * Checks whether current user is blocked
     *
     * @return bool
     */
    public function isBlocked(): ?bool
    {
        return $this->blocked;
    }

    /**
     * Sets a user's parent blocked status
     *
     * @param bool $blocked
     */
    public function setParentBlocked(?bool $blocked = true)
    {
        $this->parent_blocked = $blocked;
    }

    /**
     * Checks whether current user is blocked by parent account
     *
     * @return bool
     */
    public function isParentBlocked(): ?bool
    {
        return $this->parent_blocked;
    }

    /**
     * Sets a user's activation status
     *
     * @param bool $blocked
     */
    public function setActivated(?bool $activated = true)
    {
        $this->activated = $activated;
    }

    /**
     * Checks whether current user is activated
     *
     * @return bool
     */
    public function isActivated(): ?bool
    {
        return $this->activated;
    }

    /**
     *
     * @return Role
     */
    public function getRole(): ?Role
    {
        return $this->role;
    }

    /**
     *
     * @param Role ...$roles
     */
    public function setRole(Role $role)
    {
        $this->role = $role;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Rentify\Api\Repositories\Model::toJson()
     */
    public function toJson($options = 0)
    {
        $content = get_object_vars($this);
        unset($content['push_tokens']);

        return $content;
    }
}

