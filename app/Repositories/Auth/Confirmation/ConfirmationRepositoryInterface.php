<?php

namespace App\Repositories\Auth\Confirmation;


use App\Repositories\Auth\Auth;
use App\Repositories\User\User;
use Illuminate\Http\Request;


interface ConfirmationRepositoryInterface
{
    /**
     * @param String $verificationCode
     * @return mixed
     */

    public function confirmAccountByToken(String $verificationCode):?Auth;

    /**
     * @param id
     */
    public function activate(int $id):?int;

    /**
     * @param string verificationCode
     */

    public function findById(String $verificationCode):?Auth;

    /**
     * @param string verificationCode
     */

    public function findbyByEmailOrPhone(String $verificationCode):?Auth;

    /**
     * @param string verificationCode
     */
    public function findByPhone(String $verificationCode):?Auth;

    /**
     * Find by reset code
     * @param string resetCode
     */
    
    public function findByResetCode(String $resetCode):?Auth;

  
}
