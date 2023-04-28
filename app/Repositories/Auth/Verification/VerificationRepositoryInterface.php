<?php

namespace App\Repositories\Auth\Verification;


use App\Repositories\Auth\Auth;
use App\Repositories\User\User;
use Illuminate\Http\Request;


interface VerificationRepositoryInterface
{
    /**
     * @param Request $request
     * @return mixed
     */

    public function verifyAccountAuth(Request $request);

    /**
     * Create auth account and send verification email||SMS
     * @param Auth $auth
     * @param $isSendNotification =false
     * @return mixed
     */
    public function createAccountVerification(Auth $auth, bool $isSendNotification =false);
    

    /**
     *@param string email
     *@param string verificationCode
     */
    public function verifyAccountByVerification(String $email, String $verificationCode): ?Auth;
 
    

    /**
     *@param string email
     *@param string verificationCode
     */
    public function verifyForgotAccountByVerification(String $email, String $verificationCode): ?Auth;
 
    /**
     * activate account reset 
     * @param $id
     */
    public function activate(int $id): int;

    /**
     * activate forgot account reset 
     * @param $id
     * @param $resetCode
     */

    public function  activateForgotPassword(int $id, string $resetCode): int;

}
