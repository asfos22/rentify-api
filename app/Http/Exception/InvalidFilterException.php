<?php
declare(strict_types = 1);
namespace Rentify\Api\Http\Exception;

/**
 *
 * @author Asante Foster <asantefoster22@gmail.com>
 *
 */
class InvalidFilterException extends \Exception 
{
    public function __construct(string $message = 'Invalid filter format.', int $code = 400, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

