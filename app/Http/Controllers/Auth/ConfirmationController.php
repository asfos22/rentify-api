<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Exception\AccessControlException;
use App\Repositories\Auth\AuthRepositoryInterface;
use App\Repositories\Auth\Confirmation\ConfirmationRepositoryInterface;
use App\Repositories\Auth\Verification\VerificationRepositoryInterface;
use App\Repositories\Tokens\TokenInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\Util\Crypto as UtilCrypto;
use App\Repositories\Util\MaskEmail;
use App\Repositories\Validation\ConfirmationValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Validator;

/**
 * @author Foster Asante <asantefoster22@gmail.com>
 *
 */
class ConfirmationController extends Controller
{

    /**
     * @var AuthRepositoryInterface
     */
    private $authRepositoryInterface;

    /**
     * @var ConfirmationRepositoryInterface
     */
    private $confirmationRepository;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var VerificationRepositoryInterface
     */
    private $verificationRepositoryInterface;

    /**
     * @var MaskEmail
     */
    private $maskEmail;

    private $validator;

    private $tokenInterface;

    public function __construct(

        AuthRepositoryInterface $authRepositoryInterface,

        ConfirmationRepositoryInterface $confirmationRepository,

        UserRepositoryInterface $userRepository,

        MaskEmail $maskEmail,

        ConfirmationValidator $confirmationValidator,

        VerificationRepositoryInterface $verificationRepositoryInterface,

        TokenInterface $tokenInterface

    ) {
        $this->authRepositoryInterface = $authRepositoryInterface;

        $this->confirmationRepository = $confirmationRepository;

        $this->userRepository = $userRepository;

        $this->maskEmail = $maskEmail;

        $this->validator = $confirmationValidator;

        $this->verificationRepositoryInterface = $verificationRepositoryInterface;

        $this->tokenInterface = $tokenInterface;

    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */

    /*  public function getConfirm()
    {

    $activation = Session::get('not-active');

    $maskEmail = $this->maskEmail->maskEmail($activation['email'], 1, 1);

    //return $activation;

    if (!is_null($maskEmail) && $activation['code'] == 403) {

    // return view('auth/auth-login');

    return view('auth/auth-confirm')->with('message', $activation['message'] ?? '')->with('data', $maskEmail ?? '');

    }

    return redirect()->intended('auth/login');

    }*/

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|mixed
     * @throws \Exception
     */

    public function postAccountConfirm(Request $request)
    {

        /* $crypto = new UtilCrypto();

        $keys = Config::get('custom.encryption_key');

        $encrypt =  $crypto->encryptAesGcm("asantefoster22@gmail.com", $keys['confirm-encryption-key'], "base64");//str_encryptaesgcm("mysecretText", "myPassword", "base64"); // return a base64 encrypted string, you can also choose hex or null as encoding.
        $decrypt = $crypto->decryptAesGcm($encrypt, $keys['confirm-encryption-key'], "base64");

        if($decrypt==true){
        dump($encrypt);
        dump("Confirmation");
        dump($decrypt);
        }
        exit();*/

        $token = $request['token'];
        $verificationCode = $request['code'];

        $this->validator->validate();

        try {

            $crypto = new UtilCrypto();

            $keys = Config::get('custom.encryption_key');

            // $encrypt =  $crypto->encryptAesGcm("asantefoster22@gmail.com", $keys['confirm-encryption-key'], "base64");//str_encryptaesgcm("mysecretText", "myPassword", "base64"); // return a base64 encrypted string, you can also choose hex or null as encoding.
             $decryptEmail = $crypto->decryptAesGcm($token, $keys['confirm-encryption-key'], "base64");

            if ($decryptEmail == false) {

                throw new AccessControlException('Sorry we couldnot verify account', 402);

            }
           
            // $auth = $this->authRepositoryInterface->find($fecthVerifyAccountByVerification ->getId());

            $verifyAccountByVerification = $this->verificationRepositoryInterface->verifyAccountByVerification($decryptEmail, $verificationCode);


            if (!$verifyAccountByVerification) {

                throw new AccessControlException('Sorry we couldnot verify account', 402);

            }

            $auth = $this->authRepositoryInterface->find($verifyAccountByVerification->getId());

            if ($auth) {

               $auth = $this->tokenInterface->createAccountToken($request, $auth);

               $this->authRepositoryInterface->activate($verifyAccountByVerification->getId());
            }

            // $fetchFindByUserId = $this->authRepositoryInterface->findByUserId($auth->getUser()->getId());

            $auth = $this->authRepositoryInterface->find($verifyAccountByVerification->getId());

            return response()->json(
                [
                    "message" => "OK",
                    "code" => 200,
                    "payload" => [

                        "name" => $auth->getUser()->getName(),
                        "email" => $auth->getUser()->getEmail(),
                        "phone_number" => $auth->getUser()->getPhone(),
                        "secret" =>  $crypto->encryptAesGcm($auth->getToken()->getSecret(), $keys['auth-token-encryption-key'], "base64"),          
                        //"secret" => $auth->getToken()->getSecret(),
                        "role" => $auth->getUser()->getRole()->getName(),
                        "id" => $auth->getUser()->getId(),

                    ],

                ]);

        } catch (\Exception$e) {

            if (!($e instanceof AccessControlException)) {
                throw $e;
            }

            $errors = array(
                'token' => $e->getMessage(),
            );

            return response()->json(
                [
                    "code" => 422,
                    "message" => "OK",
                    "token" => $errors['token'],
                ]);
        }
    }

    /**
     * Confirmation  of forgiven account password
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|mixed
     * @throws \Exception
     */

    public function postForgotAccountConfirm(Request $request)
    {

        $token = $request['token'];
        $verificationCode = $request['code'];

        $this->validator->validate();

        try {

            $crypto = new UtilCrypto();

            $keys = Config::get('custom.encryption_key');

            // $encrypt =  $crypto->encryptAesGcm("asantefoster22@gmail.com", $keys['confirm-encryption-key'], "base64");//str_encryptaesgcm("mysecretText", "myPassword", "base64"); // return a base64 encrypted string, you can also choose hex or null as encoding.
            $decryptEmail = $crypto->decryptAesGcm($token, $keys['forgot-confirm-encryption-key'], "base64");

            if ($decryptEmail == false) {

                throw new AccessControlException('Sorry we couldnot verify account', 402);

            }
            /*dump($verificationCode);
            dump($token);
            dump($decryptEmail);
            exit();*/

            // $auth = $this->authRepositoryInterface->find($fecthVerifyAccountByVerification ->getId());

            $fecthVerifyAccountByVerification = $this->verificationRepositoryInterface->verifyForgotAccountByVerification($decryptEmail, $verificationCode);

            if ($fecthVerifyAccountByVerification->getResetCode() == null) {

                throw new AccessControlException('Sorry we couldnot verify account', 402);

            }

            $auth = $this->authRepositoryInterface->find($fecthVerifyAccountByVerification->getId());

            $fetchFindByUserId = $this->authRepositoryInterface->findByUserId($auth->getUser()->getId());

            /*if($fetchFindByUserId==null){

             $fetchAccountTokenId = $this->tokenInterface->createAccountToken($request, $auth);

             }*/

            if ($fetchFindByUserId != null) {

                $fetchAccountTokenId = $this->tokenInterface->updateAccountToken($request, $auth);

            }

            $user = $this->userRepository->find($auth->getUser()->getId());

            // $fecthfindByAuthToken =  $this->authRepositoryInterface->findByAuthTokenId($fetchAccountTokenId);
            $fecthfindByAuthToken = $this->authRepositoryInterface->find($fetchAccountTokenId);

            return response()->json(
                [
                    "message" => "OK",
                    "code" => 200,
                    "payload" => [

                        "code" => $fecthfindByAuthToken->getResetCode(),
                        "auth_token" => $crypto->encryptAesGcm($user->getEmail(), $keys['forgot-confirm-encryption-key'], "base64"),
                        "secret" =>  $crypto->encryptAesGcm($fecthfindByAuthToken->getToken()->getSecret(), $keys['auth-token-encryption-key'], "base64"),
                    ],

                ]);

        } catch (\Exception$e) {

            if (!($e instanceof AccessControlException)) {
                throw $e;
            }

            $errors = array(
                'token' => $e->getMessage(),
            );

            return response()->json(
                [
                    "code" => 422,
                    "message" => "OK",
                    "token" => $errors['token'],
                ]);
        }
    }

}
