<?php

namespace App\Repositories\Media;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;
use Illuminate\Support\Str;

class MediaRepository implements MediaInterface
{


    /**
     * Create media property media
     * @param Request $request
     * @param int $propertyID
     * @param int $userID
     * @return \App\Repositories\Media\Media|array
     */
    public function createPropertyMedia(Request $request, int $propertyID, int $userID)
    {


        $media = array();
        if ($request->hasFile('files')) {

            foreach ($request->file('files') as $image) {

                $token = Str::random(32);
                $name = sha1(date('YmdHis') . $token);
                // $save_name = $name . '.' . $image->getClientOriginalExtension();
                $resize_name = strtoupper($name . $token) . '.' . $image->getClientOriginalExtension();
                $image->move(public_path() . '/upload/user/properties/houses/media/images/', $resize_name);

                $media[] = [
                    'name' => $resize_name,
                    'house_id' => $propertyID,
                    'user_id' => $userID,
                    'created_at' => Carbon::now()
                ];

            }

            DB::table('media')->insert($media);

        }


        return $media;
    }
}
