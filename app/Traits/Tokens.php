<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait Tokens
{
    /**
     * @param $item
     * @return string
     */
    public function token($item)
    {
        return Str::upper(sha1($item) . Str::random(16));//Str::upper(Str::random(32));
    }


    // Function to generate OTP
function generateIntegerOTP($n) {   
    // Take a generator string which consist of all numeric digits
    $generator = "1357902468";

    $result = "";
  
    for ($i = 1; $i <= $n; $i++) {
        $result .= substr($generator, (rand()%(strlen($generator))), 1);
    }
    return $result;
}
}
  