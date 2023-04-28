<?php

namespace App\Repositories\Auth;

/**
 * @author Foster Asante <foster@Rentifygh.com>
 */
interface ConfirmationRepositoryInterface
{
    /**
     * @param String $phone
     * @param String $password
     * @return mixed
     */


    public function verifyAccount(String $phone, String $password);


    /**
     * @param String $phone
     * @param String $password
     * @return mixed
     */
    public function findAccount(String $phone, String $password);

    /**
     * @param String $name
     * @param String $phone
     * @param String $password
     * @param String $country
     * @param String $role
     * @return mixed
     */

    public function createAccount(String $name, String $phone, String $password, String $country, String $role);


    // public function findAccount(String $phoneNumber, String $password);


    /**
     * @param String $verificationCode
     * @return mixed
     */

    public function confirmAccount(String $verificationCode);

    /**
     * @param String $phone
     * @return mixed
     */
    public function resetForgottenPassword(String $phone);

    /**
     * @param String $current
     * @param String $new
     * @param String $token
     * @return mixed
     */

    public function changePassword(String $current, String $new, String $token);


}
