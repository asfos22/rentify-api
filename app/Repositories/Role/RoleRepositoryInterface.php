<?php

namespace App\Repositories\Role;

use App\Repositories\Auth\Auth;


/**
 *
 * @author Foster Asante  <asantefoster22@gmail.com>
 *
 */
interface RoleRepositoryInterface
{

    /**
     * @param String $name
     * @return Auth|null
     */
    public function findRoleByIDName(String $name): ?Role;

}
