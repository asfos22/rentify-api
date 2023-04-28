<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Exception\AccessControlException;
use App\Repositories\Auth\Auth;
use App\Repositories\Auth\AuthRepositoryInterface;
use App\Repositories\Util\Crypto as UtilCrypto;
use App\Repositories\Validation\LoginValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

//use Session;

/**
 * @author Foster Asante <asantefoster22@gmail.com>
 */
class LoginController extends Controller
{

    /**
     * @var AuthRepositoryInterface
     */
    private $authRepositoryInterface;

    /**
     * @var LoginValidator
     */

    private $loginValidator;

    /**
     * @var
     */
    private $verifyAccount;

    public function __construct(

        LoginValidator $loginValidator,
        AuthRepositoryInterface $authRepositoryInterface

    ) {
        $this->authRepositoryInterface = $authRepositoryInterface;
        $this->loginValidator = $loginValidator;

    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function accountLogin(Request $request)
    {

        $crypto = new UtilCrypto();

        $keys = Config::get('custom.encryption_key');

        $this->verifyAccount = $this->loginValidator->validate();

        $email = $request['email'];
        $password = $request['password'];

        $verifyAccount = $this->verifyAccount = $this->authRepositoryInterface->verifyAccount($request, $email, $password);

        if (!$verifyAccount->getUser()->isActivated()) {

            throw new AccessControlException(sprintf('Please activate your account. We have sent you verification email %s ', $verifyAccount->getUser()->getEmail()), 422);
        }

        if ($verifyAccount->getUser()->isBlocked()) {

            throw new AccessControlException('Account has been blocked', 400);
        }
        

        return response()->json(
            [
                "message" => "OK",
                "code" => 200,
                "payload" => [

                    "name" => $verifyAccount->getUser()->getName(),
                    "email" => $verifyAccount->getUser()->getEmail(),
                    "phone_number" => $verifyAccount->getUser()->getPhone(),
                    "secret" => $crypto->encryptAesGcm($verifyAccount->getToken()->getSecret(), $keys['auth-token-encryption-key'], "base64"),
                    "role" => $verifyAccount->getUser()->getRole()->getName(),
                    "id" => $verifyAccount->getUser()->getId(),

                ],

            ]);
    }

    /**
     * Responses to request to logout
     * @param Request $request
     * @return ResponseInterface
     */
    public function deleteLogout(Request $request)
    {
        $auth = $this->accessManager->enforce($request);

        $this->accessManager->forget($auth);

        return response()->json(
            [
                "code" => 200,
                "message" => "OK",
                "payload" => [
                ]]
        );
    }

}
