<?php
declare(strict_types = 1);
namespace App\Services\TaskRunner;

interface TaskRunnerInterface
{

    /**
     * Runs a given task
     *
     * @param callable $task
     */
    public function run(callable $task): void;
}

