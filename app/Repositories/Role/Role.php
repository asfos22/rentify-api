<?php
declare(strict_types=1);

namespace App\Repositories\Role;

use App\Repositories\Model;
use App\Repositories\Permission\Permission;

/**
 *
 * @author Asante Foster <asantefoster22@gmail.com>
 *
 */
class Role extends Model
{

    /**
     * Predefined administrator role
     *
     * @var string
     */
    const ADMINISTRATOR = 'ADMINISTRATOR';

    /**
     * Predefined host role
     * @var string
     */
    const HOST = 'HOST';

    /**
     *
     * @var string
     */
    private $name;

    /**
     *
     * @var string
     */
    private $code;

    /**
     *
     * @var int
     */
    private $rank;

    /**
     *
     * @var Permission[]
     */
    private $permissions;

    /**
     *
     * @var Role
     */
    private $parent;

    /**
     *
     * @var bool
     */
    private $self_register;

    /**
     * Returns role name
     *
     * @return string|NULL
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Sets role name
     *
     * @param string $name
     */
    public function setName(?string $name)
    {
        $this->name = $name;
    }

    /**
     * Returns role code
     *
     * @return string|NULL
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * Sets role code
     *
     * @param string $code
     */
    public function setCode(?string $code)
    {
        $this->code = $code;
    }

    /**
     * Returns account rank
     *
     * @return int|NULL
     */
    public function getRank(): ?int
    {
        return $this->rank;
    }

    /**
     * Sets account rank
     *
     * @param int $rank
     */
    public function setRank(?int $rank)
    {
        $this->rank = $rank;
    }

    /**
     *
     * @return Permission[]
     */
    public function getPermissions(): array
    {
        return is_array($this->permissions) ? $this->permissions : [];
    }

    /**
     * Sets role permissions
     *
     * @param Permission ...$permissions
     */
    public function setPermissions(Permission ...$permissions)
    {
        $this->permissions = $permissions;
    }

    /**
     * Returns parent role
     *
     * @return Role|NULL
     */
    public function getParent(): ?Role
    {
        return $this->parent;
    }

    /**
     * Sets parent role
     *
     * @param Role $role
     */
    public function setParent(?Role $role)
    {
        $this->parent = $role;
    }

    /**
     * Tells whether a role can be self registered
     *
     * @return bool|NULL
     */
    public function isSelfRegistrable(): ?bool
    {
        return $this->self_register;
    }

    /**
     * Sets self registration status
     *
     * @param bool $status
     */
    public function setSelfRegister(?bool $status)
    {
        $this->self_register = $status;
    }

    /**
     * Remove permission
     */
    public function removePermissions()
    {
        $this->permissions = null;
    }

    /**
     * Checks if a role has a given permission
     *
     * @param string $code
     * @return bool
     */
    public function hasPermission(string $code): bool
    {
        foreach ($this->getPermissions() as $permission) {
            if (0 == strcasecmp($permission->getCode(), $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Rentify\Api\Repositories\Model::toJson()
     */
    protected function toJson()
    {
        return get_object_vars($this);
    }
}

