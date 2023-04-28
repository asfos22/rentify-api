<?php

namespace App\Repositories\Tokens;

use App\Repositories\Auth\Auth as AuthAuth;
use App\Repositories\User\User;
use Auth;
use Illuminate\Http\Request;

interface TokenInterface
{
    /**
     * @param Request $request
     * @param int $userID
     * @return mixed
     */

    public function verifyAccountToken(Request $request, int $userID);

    /**
     * @param Request $request
     * @param AuthAuth auth
     * @return mixed
     */
    public function createAccountToken(Request $request, AuthAuth $auth);

    /**
     * @param string $token
     * @return mixed
     */
    public function destroyAccountToken(string $token);

    /**
      *\App\Repositories\Tokens\TokenRepository::updateAccountToken()
     * @param Request $request
     * @param AuthAuth $auth
     * @return mixed
     */
    public function updateAccountToken(Request $request, AuthAuth $auth);

}
