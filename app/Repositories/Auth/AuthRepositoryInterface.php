<?php

namespace App\Repositories\Auth;


//use App\Models\User;

use App\Models\User;
use App\Repositories\Role\Role;
use App\Repositories\User\User as RUser;
use Illuminate\Http\Request;

/**
 * @author Foster Asante <asantefoster22@gmail.com>
 */
interface AuthRepositoryInterface
{
    /**
     * @param Request $request
     * @return Auth
     */
    public function enforce(Request $request): Auth;


    /**
     * @param Request $request
     * @return Auth
     */
    public function enforceHeader(Request $request): Auth;


    /**
     * 
     * @param Request $request
     * @return mixed
     */

     public function  verifyAccount(Request $request, string $email, string $password);
    //public function verifyAccount(Request $request);


    /**
     * @param String $name
     * @param String $email
     * @param String $phone
     * @param String $countryName
     * @param Role $role
     * @return User
     */

    public function createAccount(String $name, String $email, String $phone ,String $countryName='',  Role $role = null): RUser;


    // public function findAccount(String $phoneNumber, String $password);

    /**
     * Resets an existing auth's password
     * @param Auth $auth
     * @param string $old_password
     * @param string $new_password
     * @return int Number of affected account
     */
    public function resetPassword(Auth $auth, string $password): int;


    /**
     * @param String $verificationCode
     * @return mixed
     */

    public function confirmAccount(String $verificationCode);

    /**
     * @param String $email
     * @return mixed
     */
    public function resetForgottenPassword(String $email);

    /**
     * Change an existing auth's password
     * @param String $current
     * @param String $new
     * @param String $token
     * @return mixed
     */

    public function changePassword(Auth $auth,String $current, String $new, String $token);

    /**
     * @param int $id
     * @return Auth|null
     */
    public function find(int $id): ?Auth;


    /**
     * @param int $id
     * @return Auth|null
     */
    public function  findByUserId(int $id): ?Auth;
    /**
     * @param int $id
     * @return int|null
     */
    public function activate(int $id): int;

    /**
     * activate reset code
     * @param int id
     */
    public function  activateResetCode(int $id): int;

   
}
