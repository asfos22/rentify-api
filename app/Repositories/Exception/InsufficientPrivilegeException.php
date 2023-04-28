<?php
declare(strict_types = 1);
namespace Repository\Exception;

/**
 *
 * @author Foster Asante <asantefoster22@gmail.com>
 *        
 */
class InsufficientPrivilegeException extends AccessControlException
{

    public function __construct(string $message = 'You do not have the required permission to perform requested operation.', int $code = 403)
    {
        parent::__construct($message, $code);
    }
}

