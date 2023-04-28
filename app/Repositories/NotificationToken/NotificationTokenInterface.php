<?php

namespace App\Repositories\NotificationToken;

use Auth;
use Illuminate\Http\Request;


interface NotificationTokenInterface
{
    /**
     * @param Request $request
     * @param int $id
     * @return NotificationToken|null
     */
    public function findToken(Request $request, int $id): ?NotificationToken;

    /**
     * @param Request $request
     * @param int $userID
     * @return mixed
     */
    public function createToken(Request $request, int $userID);

    /**
     * @param Request $request
     * @param int $userID
     * @return mixed
     */
    public function destroyToken(Request $request, int $userID);

    /**
     * @param Request $request
     * @param int $accountID
     * @return mixed
     */
    public function changeToken(Request $request, int $accountID);

}

