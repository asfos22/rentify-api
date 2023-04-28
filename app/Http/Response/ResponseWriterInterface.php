<?php
declare(strict_types = 1);
namespace App\Http\Response;

use Psr\Http\Message\ResponseInterface;

/**
 *
 * @author Asante Foster <asantefoster22@gmail.com>
 *        
 */
interface ResponseWriterInterface
{

    /**
     * Writes a response to given base response object
     * @param ResponseInterface $response
     * @param mixed $data
     * @param int $code
     * @param string $message
     * @param array $headers
     * @return ResponseInterface
     */
    public function write(ResponseInterface $response, $data = null, int $code = 200, string $message = 'OK', array $headers = [] ): ResponseInterface;

    /**
     * Writes a collection of data to the given response object
     * @param ResponseInterface $response
     * @param iterable $data
     * @param int $total
     * @param int $offset
     * @param int $code
     * @param string $message
     * @param array $headers
     * @return ResponseInterface
     */
    public function writeCollection(ResponseInterface $response, iterable $data = [], int $total = 0, int $offset = 0, int $code = 200, string $message = 'OK', array $headers = []): ResponseInterface;
    
    /**
     * Writes an error response to the given response object
     * @param ResponseInterface $response
     * @param mixed $data
     * @param int $code
     * @param string $message
     * @param array $headers
     * @return ResponseInterface
     */
    public function writeError(ResponseInterface $response, $data = null, int $code = 500, string $message = 'Internal Server Error', array $headers = []): ResponseInterface;
}

