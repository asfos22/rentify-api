<?php
declare(strict_types = 1);
namespace App\Services\TaskRunner;

use Psr\Log\LoggerInterface;
use Exception;

/**
 * Service for running tasks after script execution.
 * This is basicaaly a call to PHP's native 'register_shutdown_function' function
 *
 * @author Asante Foster
 *        
 */
class AfterShutdownTaskRunner implements TaskRunnerInterface
{

    /**
     *
     * @var LoggerInterface
     */
    private $logger;

    /*public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }*/

    /**
     *
     * {@inheritdoc}
     * @see \Api\Services\TaskRunner\TaskRunnerInterface::run()
     */
    public function run(callable $task): void
    {
        register_shutdown_function(function () use ($task) {
            if (! headers_sent()) {
                header("Connection: close");
                $size = ob_get_length();
                header("Content-Length: $size");
                ob_end_flush();
                flush();
            }

            try {
                call_user_func($task);
            } catch (Exception $e) {
                $this->logger->error($e->getMessage(), array(
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ));
            }
        });
    }
}

