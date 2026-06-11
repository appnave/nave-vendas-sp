<?php

namespace BildVitta\SpVendas\Console\Commands\Messages\Resources;

use BildVitta\SpVendas\Models\Worker;
use Throwable;

trait LogHelper
{
    /**
     * @param Throwable $exception
     * @param mixed $message
     * @return void
     * @throws Throwable
     */
    private function logError(Throwable $exception, mixed $message): void
    {
        try {
            $worker = new Worker();
            $worker->type = 'rabbitmq.worker.error';
            $worker->payload = [
                'message' => $message
            ];
            $worker->status = 'error';
            $worker->error = [
                'message' => $exception->getMessage(),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
                'trace'   => $exception->getTraceAsString(),
            ];
            $worker->schedule = now();
            $worker->save();
        } catch (Throwable $throwable) {
            throw $exception;
        }
    }
}
