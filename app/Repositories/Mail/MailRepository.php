<?php

namespace App\Repositories\Mail;

use App\Mail\SignupEmail;
use App\Repositories\Message\Message;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

/**
 * @author Foster Asante <asantefoster22@gmail.com>
 */
class MailRepository implements MailRepositoryInterface
{

    /**
     * @param String $name
     * @param String $email
     * @param String $verificationCode
     * @return mixed|void
     */
    public function mailAccountVerification(String $name, String $email, String $verificationCode)
    {
        // account verification code

        $data = [
            'name' => $name,
            'verification_code' => $verificationCode
        ];
        Mail::to($email)->send(new SignupEmail($data));
    }

    /**
     * @param String $accountID
     * @return mixed
     */
    public function mailAccountPasswordReset(String $accountID): String
    {
        // TODO: Implement mailAccountPasswordReset() method.
    }



  /* public function mailMessage(Mail $mail):?Message
   {

    foreach ($data as $key => $value) {
        $input['email'] = $value->email;
        $input['name'] = $value->name;
        \Mail::send('mail.Test_mail', [], function($message) use($input){
            $message->to($input['email'], $input['name'])
                ->subject($input['subject']);
        });
    }
   }*/

   

}