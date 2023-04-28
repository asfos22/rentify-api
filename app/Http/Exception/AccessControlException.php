<?php
namespace App\Http\Exception;

use Exception;

/**
 *
 * @author Asante Foster <asantefoster22@gmail.com>
 *        
 */
class AccessControlException extends Exception  //
{

    public function __construct(string $message = "PERMISSION_DENIED",int $code = 403, Exception $previous = null) //string $status = "PERMISSION_DENIED"
    {
        parent::__construct($message, $code, $previous);
    }
}

