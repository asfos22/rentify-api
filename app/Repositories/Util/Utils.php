<?php

/**
 * generic functions
 * @author Foster Asante <asantefoster22@gmail.com>
 */

namespace App\Repositories\Util;

use Illuminate\Support\Facades\Hash;

Class Utils
{
    /**
     * Calculate avg function
     * @param array $list
     * @return float|int
     */
    public function toCalculateAvg($list = [])
    {
        $sum = array_filter($list);
        return array_sum($sum) / count($sum);

    }

    /**
     * @param String $password
     * @return string
     */
    public function createPassword(String $password)
    {

        return Hash::make($password);
    }


    /**
     * @param int $length
     * @return string
     * @throws \Exception
     */
    public function generateToken(int $length)
    {

        /*$token1 = bin2hex(openssl_random_pseudo_bytes(16));
       # or in php7
       $token = bin2hex(random_bytes(4));
       print($token1);
       print("\n");
       print($token."\n");
       print(bin2hex(random_bytes(2)).'-'.bin2hex(random_bytes(2)));*/


        return bin2hex(random_bytes($length));
    }

    /**
     * Validate email 
     * @param string email
     */
     public  function validateEmail(string $email) {
        return (preg_match("/(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)/", $email) || !preg_match("/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/", $email)) ? false : true;
       }

}