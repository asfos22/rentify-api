<?php

namespace App\Repositories\Util;


class MaskEmail
{


    /**
     * @param String $email
     * @param int $charShownFront
     * @param int $charShownBack
     * @return string
     */
    function maskEmail(String $email, int $charShownFront = 1, int $charShownBack = 1)
    {

        $mail_parts = explode('@', $email);
        $username = $mail_parts[0];
        $len = strlen($username);

        if ($len < $charShownFront or $len < $charShownBack) {
            return implode('@', $mail_parts);
        }

        //Logic: show asterisk in middle, but also show the last character before @
        $mail_parts[0] = substr($username, 0, $charShownFront)
            . str_repeat('*', $len - $charShownFront - $charShownBack)
            . substr($username, $len - $charShownBack, $charShownBack);

        return implode('@', $mail_parts);
    }


}