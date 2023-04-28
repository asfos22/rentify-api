<?php

namespace App\Repositories\Util;

use Exception;

class Crypto
{

     //-- encrypt
    public function encryptAesGcm($plaintext, $password, $encoding = null)
    {
       
        try {
            if ($plaintext != null && $password != null) {
                $keysalt = openssl_random_pseudo_bytes(16);
                $key = hash_pbkdf2("sha512", $password, $keysalt, 20000, 32, true);
                $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length("aes-256-gcm"));
                $tag = "";
                $encryptedstring = openssl_encrypt($plaintext, "aes-256-gcm", $key, OPENSSL_RAW_DATA, $iv, $tag, "", 16);
                return $encoding == "hex" ? bin2hex($keysalt . $iv . $encryptedstring . $tag) : ($encoding == "base64" ? base64_encode($keysalt . $iv . $encryptedstring . $tag) : $keysalt . $iv . $encryptedstring . $tag);
            }
        } catch (Exception $e) {

        }

    }
       
    // -- decrypt
    public function decryptAesGcm($encryptedstring, $password, $encoding = null)
    {

        try {
            if ($encryptedstring != null && $password != null) {
                $encryptedstring = $encoding == "hex" ? hex2bin($encryptedstring) : ($encoding == "base64" ? base64_decode($encryptedstring) : $encryptedstring);
                $keysalt = substr($encryptedstring, 0, 16);
                $key = hash_pbkdf2("sha512", $password, $keysalt, 20000, 32, true);
                $ivlength = openssl_cipher_iv_length("aes-256-gcm");
                $iv = substr($encryptedstring, 16, $ivlength);
                $tag = substr($encryptedstring, -16);
                return openssl_decrypt(substr($encryptedstring, 16 + $ivlength, -16), "aes-256-gcm", $key, OPENSSL_RAW_DATA, $iv, $tag);
            }
        } catch (Exception $e) {

        }

    }
}
