<?php
namespace App\Http\Controllers\Auth;

use App\Http\AccessControl\AccessManagerInterface;
use App\Http\Controllers\Controller;
use App\Http\Exception\AccessControlException;
use App\Repositories\Auth\AuthRepositoryInterface;
use App\Repositories\Auth\Confirmation\ConfirmationRepositoryInterface;
use App\Repositories\Message\Message;
use App\Repositories\Message\MessagingServiceInterface;
use App\Repositories\System\Event\SystemEvent;
use App\Repositories\Tokens\TokenInterface;
use App\Repositories\Tokens\TokenRepository;
use App\Repositories\User\User;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\Util\Crypto as UtilCrypto;
use App\Repositories\Util\Utils;
use App\Repositories\Validation\ForgotPasswordChangeValidator;
use App\Repositories\Validation\PasswordChangeValidator;
use App\Repositories\Validation\PasswordConfirmValidator;
use App\Repositories\Validation\PasswordResetValidator;
use App\Services\Messaging\Message as MessagingMessage;
use App\Services\Messaging\MessageComposeInterface;
use App\Services\Messaging\MessageInterface as MessageMessageInterface;
use App\Services\Messaging\MessageUserInterface;
use App\Services\Messaging\MessengerInterface;
use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;


/**
 *
 * @author Foster Asante <asantefoster22@gmail.com>
 *
 */
class PasswordController extends Controller
{
    /**
     * @var AuthRepositoryInterface
     */
    private $authRepositoryInterface;
    /**
     * @var PasswordConfirmValidator
     */

    private $passwordConfirmValidator;

    /**
     * @var PasswordChangeValidator
     */

    private $passwordChangeValidator;

    /**
     *
     */
    private $forgotPasswordChangeValidator;

    /**
     * @var PasswordResetValidator
     */
    private $passwordResetValidator;

    /**
     * @var
     */

    private $changePassword;

    /**
     * @var
     */

    private $forgotPassword;

    /**
     * @var
     */
    private $accessManager;
    /**
     * @var
     */
    private $messenger;

    /**
     * @var
     */
    private $messageUserInterface;

    /**
     * @var
     */
    private $userRepositoryInterface;
    /**
     * @var
     */
    private $confirmationRepositoryInterface;

    /**
     * @var  TokenInterface 
     */
    private  $tokenInterface;

    /**
     * @var  MessageComposeInterface 
     */
    private  $messageComposeInterface;

    /**
     * @var
     */
    //private $responseWriter;

    /**
     * PasswordController constructor.
     * @param PasswordConfirmValidator $passwordConfirmValidator
     * @param PasswordChangeValidator $passwordChangeValidator
     * @param PasswordResetValidator $passwordResetValidator
     * @param ForgotPasswordChanageValidator $forgotPasswordChanageValidator
     * @param AuthRepositoryInterface $authRepositoryInterface
     * @param AccessManagerInterface $accessManager
     * @param TokenRepositoryInterface   $tokenRepositoryInterface 
     * @param  MessageComposeInterface   $messageComposeInterface 
     */

    public function __construct(

        PasswordConfirmValidator $passwordConfirmValidator,
        PasswordChangeValidator $passwordChangeValidator,
        PasswordResetValidator $passwordResetValidator,
        ForgotPasswordChangeValidator $forgotPasswordChangeValidator,
        AuthRepositoryInterface $authRepositoryInterface,
        AccessManagerInterface $accessManager,
        MessageUserInterface $messageUserInterface,
        MessagingServiceInterface $messenger,
        // ResponseWriterInterface $responseWriterInterface,
        UserRepositoryInterface $userRepositoryInterface,
        ConfirmationRepositoryInterface $confirmationRepositoryInterface,
        TokenInterface  $tokenRepositoryInterface,
        MessageComposeInterface   $messageComposeInterface 
        //TokenRepositoryInterface  $tokenRepositoryInterface 
        
    ) {
        $this->authRepositoryInterface = $authRepositoryInterface;

        $this->passwordConfirmValidator = $passwordConfirmValidator;

        $this->passwordChangeValidator = $passwordChangeValidator;

        $this->forgotPasswordChangeValidator = $forgotPasswordChangeValidator;

        $this->passwordResetValidator = $passwordResetValidator;

        $this->accessManager = $accessManager;

        $this->messageUserInterface = $messageUserInterface;

        $this->messenger = $messenger;

        $this->userRepositoryInterface = $userRepositoryInterface;

        $this->confirmationRepositoryInterface = $confirmationRepositoryInterface;

        $this->tokenInterface = $tokenRepositoryInterface;

        $this->messageComposeInterface = $messageComposeInterface;  

        // $this->responseWriter = $responseWriterInterface;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|mixed
     * @throws \Illuminate\Validation\ValidationException
     */

    public function postChangePassword(Request $request)
    {

        $this->changePassword = $this->passwordChangeValidator->validate();
        $auth = $this->authRepositoryInterface->enforce($request);

        $this->authRepositoryInterface->resetPassword($auth, $request['current'], $request['new']);

        $this->changePassword = $this->authRepositoryInterface->changePassword($auth, $request['current'], $request['new'], $token);

        return $this->changePassword;
    }

    /**
     * Change forgotten password
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|mixed
     * @throws \Illuminate\Validation\ValidationException
     */

    public function postForgotChangePassword(Request $request)
    {
        $this->forgotPasswordChangeValidator->validate();

        $auth = $this->authRepositoryInterface->enforce($request);

        $token = $request['token'];
        $verificationCode = $request['code'];
        $password = $request['password'];

        $crypto = new UtilCrypto();
        $utils = new Utils();
        $keys = Config::get('custom.encryption_key');

        // $encrypt =  $crypto->encryptAesGcm("asantefoster22@gmail.com", $keys['confirm-encryption-key'], "base64");//str_encryptaesgcm("mysecretText", "myPassword", "base64"); // return a base64 encrypted string, you can also choose hex or null as encoding.
        $decryptEmail = $crypto->decryptAesGcm($token, $keys['forgot-confirm-encryption-key'], "base64");

        if ($decryptEmail == false || $utils->validateEmail($decryptEmail) == false) {

            throw new AccessControlException('Sorry we couldnot verify account', 402);

        }

        $findByEmail = $this->userRepositoryInterface->findByEmail($decryptEmail);

        if (!$findByEmail->getEmail()) {

            throw new AccessControlException('Sorry we couldnot verify account', 402);

        }

        $findByResetCode = $this->confirmationRepositoryInterface->findByResetCode($verificationCode);

        if (!$findByResetCode->getResetCode()) {

            throw new AccessControlException('Sorry we couldnot verify account', 402);
        }
        $resetPassword = $this->authRepositoryInterface->resetPassword($auth, $password);

        $this->authRepositoryInterface->activateResetCode($resetPassword);

       //$user = $this->userRepositoryInterface->find($auth->getUser()->getId());

        $this->tokenInterface->updateAccountToken($request, $auth);

        $find = $this->authRepositoryInterface->find($resetPassword);

        return response()->json(
            [
                "message" => "OK",
                "code" => 200,
                "payload" => [

                    "name" => $find->getUser()->getName(),
                    "email" => $find->getUser()->getEmail(),
                    "phone_number" => $find->getUser()->getPhone(),
                    "secret" =>  $crypto->encryptAesGcm($find->getToken()->getSecret(), $keys['auth-token-encryption-key'], "base64"),          
                   // "secret" => $fecthfindByAuthToken->getToken()->getSecret(),
                    "role" => $find->getUser()->getRole()->getName(),
                    "id" => $find->getUser()->getId(),

                ],

            ]);
    }

    /**
     * @param Request $request
     * @return mixed|object
     * @throws \Illuminate\Validation\ValidationException
     */

    public function postResetForgottenPassword(Request $request)
    {
       
        $this->forgotPassword = $this->passwordResetValidator->validate();
        $fecthForgottenPassword = $this->authRepositoryInterface->resetForgottenPassword($request['email']);

        $notifications = [];
        
        $fullName = explode(' ', $fecthForgottenPassword->getUser()->getName());
        $nameLast = array_pop($fullName);

        $crypto = new UtilCrypto();

        $keys = Config::get('custom.encryption_key');

        /**
         * A base64 encrypted string, you can also choose hex or null as encoding.
         */

        $encrypt = $crypto->encryptAesGcm($fecthForgottenPassword->getUser()->getEmail(), $keys['forgot-confirm-encryption-key'], "base64");

        //$decrypt = $crypto->decryptAesGcm($encrypt, $keys['confirm-encryption-key'], "base64");

        $content = <<<MESSAGE
                      Dear %s, \nYour verification code is below:\n\n %s\n\n
                      We've received a request to set a new password for this Rentify account: %s.\n\n
                      <a style="background-color: #0096FF;color: white; border-radius: 8px;border: none;padding: 15px 25px;text-decoration: none;"  href="%s%s" class="button">Set password</a> \n\n\n
                      If you didn't request this email,you can safely ignore this email.\n\n  Sincerely.\nRentify Support
                     MESSAGE;
        //--
        $messagingMessage = new MessagingMessage();
        $recipient = $this->messageUserInterface;
        $recipient->setId($fecthForgottenPassword->getUser()->getId());
        $recipient->setName($fecthForgottenPassword->getUser()->getName());
        $recipient->setPhone($fecthForgottenPassword->getUser()->getPhone());
        $recipient->setEmail($fecthForgottenPassword->getUser()->getEmail());
        $recipient->setSubject(sprintf("Set your new Rentify password", $fecthForgottenPassword->getResetCode() /*$auth->getConfirmationCode()*/));
        $recipient->setContent(sprintf($content, $nameLast, $fecthForgottenPassword->getResetCode(), $fecthForgottenPassword->getUser()->getEmail() /*$auth->getConfirmationCode()*/, 'https://www.w3docs.com/verify/', $encrypt));

        $notifications[] = $fecthForgottenPassword->getUser()->getId();

        $messagingMessage->setRecipients($recipient);
        $messagingMessage->setSender($recipient);

        $messagingMessage->setRecipient($recipient);
        $this->messageUserInterface->setIds(...$notifications);

       $this->messageComposeInterface->composeMessage( 
            $messagingMessage,
            $this->messageUserInterface,
            MessengerInterface::CHANNEL_SMS,
            MessengerInterface::CHANNEL_PUSH,
            MessengerInterface::CHANNEL_EMAIL
          );

           /* $this->composeMessage( 
            $messagingMessage,
                                //
             MessengerInterface::CHANNEL_SMS,
            // MessengerInterface::CHANNEL_PUSH,
             MessengerInterface::CHANNEL_EMAIL
                );*/
        return response()->json(
            [
                "code" => 200,
                "message" => sprintf("We sent a recovery link to you at %s", $fecthForgottenPassword->getUser()->getEmail()),
                "payload" => [
                   /* $encrypt*/
                ]]
        );
    }

    /**
     * Compose message [SMS, PUSH , EMAIL]
     * @param Message
     * @param User
     */
    public function composeMessage( MessagingMessage $messagingMessage, string ...$channels ): ?Message
    {

        $message = new Message();
        $receiver = new User();
        $receiver->setId($messagingMessage->getRecipient()->getId());
        $message->setReceiver($receiver);

        $messagingMessage->setData(true);

        //-- set channels
        $messagingMessage->setChannels(...$channels);

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

/**namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use App\Repositories\Auth\AuthRepositoryInterface;
use App\Repositories\Validation\PasswordChangeValidator;
use App\Repositories\Validation\PasswordConfirmValidator;
use App\Repositories\Validation\PasswordResetValidator;
use Illuminate\Http\Request;
use Validator;

/
 *
 * @author Foster Asante <asantefoster22@Rentifygh.com>
 *
 *
class PasswordController extends Controller
{
/*
 * @var AuthRepositoryInterface
 *
private $authRepositoryInterface;
/**
 * @var PasswordConfirmValidator
 *

private $passwordConfirmValidator;

/**
 * @var PasswordChangeValidator
 *

private $passwordChangeValidator;

/*
 * @var PasswordResetValidator
 *
private $passwordResetValidator;

private $changePassword;

public function __construct(

PasswordConfirmValidator $passwordConfirmValidator,
PasswordChangeValidator $passwordChangeValidator,
PasswordResetValidator $passwordResetValidator,
AuthRepositoryInterface $authRepositoryInterface

)
{
$this->authRepositoryInterface = $authRepositoryInterface;

//$this->passwordConfirmValidator = $passwordConfirmValidator;

$this->passwordChangeValidator = $passwordChangeValidator;

$this->passwordResetValidator = $passwordResetValidator;

}

 * @param Request $request
 * @return \Illuminate\Http\JsonResponse|mixed
 * @throws \Illuminate\Validation\ValidationException

public function postChangePassword(Request $request)
{

$this->changePassword = $this->passwordChangeValidator->validate();
$auth = $this->authRepositoryInterface->enforce($request);

$this->authRepositoryInterface->resetPassword($auth, $request['current'], $request['new']);

return $this->changePassword;
}

 * @param Request $request
 * @return string

public function postResetForgottenPassword(Request $request)
{
return "password reset";
}

}*/
