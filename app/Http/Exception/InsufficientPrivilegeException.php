<?php
declare(strict_types = 1);
namespace App\Http\Exception;

/**
 *
 * @author Asante Foster <asantefoster22@gmail.com>
 */
class InsufficientPrivilegeException extends AccessControlException
{

    public function __construct(string $message = 'You do not have the needed permission for requested operation.', int $code = 403)
    {
        parent::__construct($message, $code);
    }
}

