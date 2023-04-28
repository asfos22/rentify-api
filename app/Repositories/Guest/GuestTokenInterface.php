<?php

namespace App\Repositories\Guest;

use Auth;
use Illuminate\Http\Request;


interface GuestTokenInterface
{


    /**
     * @param GuestToken $guestToken
     * @return mixed
     */

    public function fetchGuestToken(GuestToken $guestToken): ?array;


    /**
     * Get subscriber by token by id
     * @param GuestToken $guestToken
     * @return mixed
     */

    public function fetchGuestTokenByID(GuestToken $guestToken): ?array;


    /**
     * Get subscriber by  device and token
     * @param GuestToken $guestToken
     * @return mixed
     */

    public function fetchGuestTokenByDeviceIDToken(GuestToken $guestToken): ?array;


    /**
     * @param GuestToken $guestToken
     * @return mixed
     */

    public function verifyGuestToken(GuestToken $guestToken);

    /**
     * @param Request $request
     * @return mixed
     */
    public function createGuestToken(Request $request): ?GuestToken;

    /**
     * @param GuestToken $guestToken
     * @return mixed
     */
    public function destroyGuestTokenByID(GuestToken $guestToken);

    /**
     * @param GuestToken $guestToken
     * @return mixed
     */
    public function updateGuestTokenByID(GuestToken $guestToken);


}
