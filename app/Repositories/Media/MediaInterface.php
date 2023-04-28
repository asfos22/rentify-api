<?php

namespace App\Repositories\Media;

use Illuminate\Http\Request;


interface MediaInterface
{
    /**
     * Create media property media
     * @param Request $request
     * @param int $propertyID
     * @param int $userID
     * @return Media
     */

    public function createPropertyMedia(Request $request, int $propertyID, int $userID);


}
