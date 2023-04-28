<?php
namespace App\Http\Response;

use Psr\Http\Message\ResponseInterface;
/**
 * @author Asante Foster <asantefoster22@gmail.com>       
 */

class JsonResponseWriter implements ResponseWriterInterface
{

    /**
     *
     * @var array[string]string
     */
    private $headers = [];

    public function __construct(array $headers = [])
    {
        $this->headers = $headers;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Http\Response\ResponseWriterInterface::write()
     */
    public function write(ResponseInterface $response, $data = null, int $code = 200, string $message = 'OK', array $headers = []): ResponseInterface
    {
        $modifiedReponse = $response->withStatus($code);

        foreach ($headers as $name => $value) {
            $modifiedReponse = $modifiedReponse->withHeader($name, $value);
        }

        $payload = array(
            'code' => $code,
            'message' => $message,
            'payload' => $data
        );

        $modifiedReponse->getBody()->write(json_encode($payload));

        return $modifiedReponse->withHeader('Access-Control-Allow-Origin', '*')->withHeader('Content-Type', 'application/json;charset=utf-8');
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Http\Response\ResponseWriterInterface::writeCollection()
     */
    public function writeCollection(ResponseInterface $response, iterable $data = [], int $total = 0, int $offset = 0, int $code = 200, string $message = 'OK', array $headers = []): ResponseInterface
    {
        $calOffset = $offset + count($data);

        $collection = array(
            'items' => $data,
            'total' => $total,
            'offset' => $calOffset > $total ? $total : $calOffset
        );

        return $this->write($response, $collection, $code, $message, $headers);
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Http\Response\ResponseWriterInterface::writeError()
     */
    public function writeError(ResponseInterface $response, $data = null, int $code = 500, string $message = 'Internal Server Error', array $headers = []): ResponseInterface
    {
        return $this->write($response, $data, $code, $message, $headers);
    }
}

