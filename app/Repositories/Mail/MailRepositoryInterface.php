<?php

namespace App\Repositories\Mail;

use App\Repositories\Message\Message;

interface MailRepositoryInterface
{
    /**
     * @param String $name
     * @param String $email
     * @param String $verificationCode
     * @return mixed
     */
    public function mailAccountVerification(String $name, String $email, String $verificationCode);

    /**
     * @param String $accountID
     * @return mixed
     */
    public function mailAccountPasswordReset(String $accountID): String;

    /**
     * @param Mail $mail
     * @return mixed
     */
    //public function mailMessage(Mail $mail):?Message;


}
