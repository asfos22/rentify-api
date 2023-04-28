<?php
declare(strict_types=1);

namespace App\Http\Exception;

/**
 *
 * @author Asante Foster <asantefoster22@gmail.com>
 *
 */
class ResourceNotFoundException extends \Exception
{

    public function __construct(string $message = 'Requested resource not found.', int $code = 404, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

