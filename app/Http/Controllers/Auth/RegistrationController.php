<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use App\Repositories\Auth\Auth;
use App\Repositories\Auth\AuthRepositoryInterface;
use App\Repositories\Auth\Verification\VerificationRepositoryInterface;
use App\Repositories\Message\Message;
use App\Repositories\Role\Role;
use App\Repositories\Role\RoleRepositoryInterface;
use App\Repositories\System\Event\SystemEvent;
use App\Repositories\User\User;
use App\Repositories\Validation\RegistrationValidator;
use App\Services\Messaging\MessengerInterface;
use App\Services\Messaging\Message as MessagingMessage;
use App\Services\Messaging\MessageInterface as MessageMessageInterface;
use App\Services\Messaging\MessageUserInterface;
use App\Repositories\Message\MessagingServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use App\Repositories\Util\Crypto as UtilCrypto;



/**
 * @author Foster Asante <asantefoster22@gmail.com>
 */
class RegistrationController extends Controller
{

    /**
     * @var AuthRepositoryInterface
     */
    private $authRepositoryInterface;

    /**
     * @var RegistrationValidator
     */

    private $registrationValidator;
    /**
     * @var VerificationRepositoryInterface
     */
    private $verificationRepository;

    /**
     * @var
     */
    private $createAccount;

    /**
     * @var RoleRepositoryInterface
     */
    private $roleRepository;

    /**
     * @var  role
     */
    private $role;


    private $auth;

    private $messenger;

    private $messageUserInterface;

    private $fetchVerificationCode;

    public function __construct(

        RegistrationValidator $registrationValidator,

        AuthRepositoryInterface $authRepositoryInterface,

        VerificationRepositoryInterface $verificationRepository,

        RoleRepositoryInterface $roleRepository,

        MessageUserInterface $messageUserInterface,

        MessagingServiceInterface $messenger,

    )
    {
        $this->registrationValidator = $registrationValidator;
        $this->authRepositoryInterface = $authRepositoryInterface;
        $this->verificationRepository = $verificationRepository;
        $this->roleRepository = $roleRepository;
        $this->messageUserInterface =$messageUserInterface;
        $this->messenger =$messenger;

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function postAccountRegistration(Request $request)
    {


        $name = $request->name;
        $email = $request->email;
        $phoneNumber = $request->phone_number;
        $country = $request->country;
        $password = $request->password;
        $role = $request->role;

        $this->role = new Role();
        $user = new User();

        $this->createAccount = $this->registrationValidator->validate();

        $this->role = $this->roleRepository->findRoleByIDName($role);

    
       $this->createAccount = $this->authRepositoryInterface->createAccount($name, $email, $phoneNumber, $country, $this->role);
        
       if (!empty($this->createAccount)) {

            $this->auth = new Auth();
            
            $this->auth->setPassword($password);
            $user->setId($this->createAccount->getId());
            $user->setName($name);
            $user->setPhone($phoneNumber);
            $user->setEmail($email);
            $this->auth->setUser($user);

           $this->fetchVerificationCode = $this->verificationRepository->createAccountVerification($this->auth, true);
       }
       
        $notifications = [];
        
        $auth =$this->authRepositoryInterface->find($this->fetchVerificationCode);

        $fullName = explode(' ', $this->createAccount->getName());
        $nameLast = array_pop($fullName);
    
        $crypto = new UtilCrypto();

        $keys = Config::get('custom.encryption_key');

        /**
         * A base64 encrypted string, you can also choose hex or null as encoding.
         */
       
        $encrypt =  $crypto->encryptAesGcm($this->createAccount->getEmail(), $keys['confirm-encryption-key'], "base64");
    
        //$decrypt = $crypto->decryptAesGcm($encrypt, $keys['confirm-encryption-key'], "base64");

        /*if($decrypt==true){
            dump($encrypt);
            dump("Confirmation");
            dump($decrypt);
        }*/
        $content = <<<MESSAGE
                    Dear %s, \nYour confirmation code is below:\n\n %s\n\n 
                    To confirm your email address. Please click this link\n\n
                    <a style="background-color: #0096FF;color: white; border-radius: 8px;border: none;padding: 15px 25px;text-decoration: none;"  href="%s%s" class="button">Confirm</a> \n\n\n
                    If you didn't request this email, there's nothing to worry about - you can safely ignore it.\n\n  Sincerely.\nRentify Support
                    MESSAGE;

        //--
        $messagingMessage = new MessagingMessage();
        $recipient = $this->messageUserInterface;
        $recipient->setId($this->createAccount->getId());
        $recipient->setName($user->getName());
        $recipient->setPhone($user->getPhone());
        $recipient->setEmail($this->createAccount->getEmail());
        $recipient->setSubject(sprintf("Rentify confirmation code" ,$auth->getConfirmationCode()));
        $recipient->setContent(sprintf($content, $nameLast,$auth->getConfirmationCode(), 'https://www.renseek.com/verify/',$encrypt));

        $notifications[] = $this->createAccount->getId();

        $messagingMessage->setRecipients($recipient);

        $messagingMessage->setRecipient($recipient);
        $this->messageUserInterface->setIds(...$notifications);

        $this->composeMessage($messagingMessage);

        return response()->json([

                "code" => 200,
                "message" => sprintf("%s,thank you for signing up for Rentify,we have sent you an email to %s%s",$this->auth->getUser()->getName(),$this->auth->getUser()->getEmail(),' for verification'),  //$this->auth->getUser()->getName() . ' thanks for signing up for Rentify,we have sent you an email to ' . $this->auth->getUser()->getEmail() . ' for verification',
                "payload" => []
            ]

        );
    }

     /**
     * Compose message
     * @param Message
     * @param User
     */
    public function composeMessage(MessagingMessage $messagingMessage): ?Message
    {
        $message = new Message();
        $receiver = new User();
        $receiver->setId($messagingMessage->getRecipient()->getId());
        $message->setReceiver($receiver);


        $messagingMessage->setData(true);
        $messagingMessage->setChannels( //
            MessengerInterface::CHANNEL_SMS,
           // MessengerInterface::CHANNEL_PUSH,
            MessengerInterface::CHANNEL_EMAIL
        );

        //----
        $messagingMessage->setEvent(SystemEvent::EVENT_AUTH_ACCOUNT_VERIFICATION);
        $messagingMessage->setState(MessageMessageInterface::STATE_SUCCESS);

        //----

        $sender = $this->messageUserInterface;

        $messagingMessage->setSender($sender);

        $this->messenger->send($messagingMessage);
        return $message;
        
    }


}

