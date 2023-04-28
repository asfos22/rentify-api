<?php
declare(strict_types=1);

namespace App\Repositories\Auth;


use App\Repositories\Model;
use App\Repositories\User\User;
use App\Repositories\Permission\Permission;

/**
 *
 * @author  Foster Asante <asantefoster22@gmail.com>
 *
 */
final class Auth extends Model
{

    /**
     *
     * @var string
     */
    private $username;

    /**
     *
     * @var string
     */
    private $password;

    /**
     *
     * @var string
     */
    private $reset_code;

    /**
     *
     * @var string
     */
    private $confirmation_code;

    /**
     *
     * @var User
     */
    private $user;

    /**
     *
     * @var Token
     */
    private $token;

    /**
     *
     * @var Permission[]
     */
    private $permissions = [];

    /**
     * Returns username
     *
     * @return string|NULL
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * Sets username
     *
     * @param string $name
     */
    public function setUsername(?string $name)
    {
        $this->username = $name;
    }

    /**
     * Returns password
     *
     * @return string|NULL
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Sets password
     *
     * @param string $password
     */
    public function setPassword(?string $password)
    {
        $this->password = $password;
    }

    /**
     * Returns reset code
     *
     * @return string|NULL
     */
    public function getResetCode(): ?string
    {
        return $this->reset_code;
    }

    /**
     * Sets reset code
     *
     * @param string $code
     */
    public function setResetCode(?string $code)
    {
        $this->reset_code = $code;
    }

    /**
     * Returns confirmation code
     *
     * @return string|NULL
     */
    public function getConfirmationCode(): ?string
    {
        return $this->confirmation_code;
    }

    /**
     * Sets confirmation code
     *
     * @param string $code
     */
    public function setConfirmationCode(?string $code)
    {
        $this->confirmation_code = $code;
    }

    /**
     * Returns user associated with this auth instance
     *
     * @return User|NULL
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Sets user associated with this auth instances
     *
     * @param User $user
     */
    public function setUser(?User $user)
    {
        $this->user = $user;
    }

    /**
     * Returns token associated with this auth instance
     *
     * @return Token|NULL
     */
    public function getToken(): ?Token
    {
        return $this->token;
    }

    /**
     * Sets token associated with this auth instances
     *
     * @param Token $token
     */
    public function setToken(?Token $token)
    {
        $this->token = $token;
    }

    /**
     * Returns permissions associated with this auth
     *
     * @return Permission[]
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * Sets permissions for this auth
     *
     * @param Permission ...$permissions
     */
    public function setPermissions(Permission ...$permissions)
    {
        $this->permissions = $permissions;
    }

    /**
     * Checks if this auth has a given permission
     *
     * @param string $permCode
     * @return bool
     */
    public function hasPermission(string $permCode): bool
    {
        foreach ($this->permissions as $permission) {
            if (0 == strcasecmp($permission->getCode(), $permCode)) {
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
        $content = get_object_vars($this);
        unset($content['password'], $content['confirmation_code'], $content['reset_code']);
        return $content;
    }
}

