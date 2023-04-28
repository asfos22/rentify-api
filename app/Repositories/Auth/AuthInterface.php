<?php

namespace App\Services\Api;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;


interface AuthInterface
{
    /**
     * @param Request $request
     * @return mixed
     */

    public function verifyAccount(Request $request);

    /**
     * @param Request $request
     * @return mixed
     */
    public function createAccount(Request $request):void;

    /**
     * @param Request $request
     * @param int $accountID
     * @return mixed
     */

    public function createPasswordReset(Request $request, int $accountID);

    /**
     * @param Request $request
     * @param int $accountID
     * @return mixed
     */

    public function createPasswordChange(Request $request, int $accountID);


}
