<?php

namespace App\Repositories\Permission;

use App\Repositories\Auth\Auth;

/**
 * @author Foster Asante <asantefoster22@gmail.com>
 */
interface PermissionRepositoryInterface
{

    /**
     * @param Auth $auth
     * @return mixed
     */
    public function findPermission(Auth $auth): Auth;

    /**
     * @param String $token
     * @return Auth
     */
    public function findByToken(String $token): ?Auth;


}
