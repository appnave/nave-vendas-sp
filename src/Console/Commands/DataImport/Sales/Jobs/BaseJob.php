<?php

namespace BildVitta\SpVendas\Console\Commands\DataImport\Sales\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use BildVitta\SpVendas\Models\Worker;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class BaseJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 3300;

    /**
     * @var int
     */
    public $retryAfter = 60;

    /**
     * @var Worker
     */
    protected $worker;

    /**
     * @param int $workerId
     */
    public function __construct(int $workerId)
    {
        $this->worker = Worker::find($workerId);
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->worker) {
            $this->fail('Invalid worker');
            return;
        }

        config([
            'database.connections.vendas' => [
                'driver'         => 'mysql',
                'host'           => config('sp-vendas.db.host'),
                'port'           => config('sp-vendas.db.port'),
                'database'       => config('sp-vendas.db.database'),
                'username'       => config('sp-vendas.db.username'),
                'password'       => config('sp-vendas.db.password'),
                'unix_socket'    => env('DB_SOCKET', ''),
                'charset'        => 'utf8mb4',
                'collation'      => 'utf8mb4_unicode_ci',
                'prefix'         => '',
                'prefix_indexes' => true,
                'strict'         => true,
                'engine'         => null,
                'options'        => [],
            ]
        ]);

        $this->worker->update(['status' => 'in_progress']);

        $this->process();
    }

    /**
     * Process the job.
     *
     * @return void
     */
    abstract public function process(): void;

    /**
     * Set worker status as finished.
     *
     * @return void
     */
    public function finish(): void
    {
        $this->worker->update(['status' => 'finished']);
    }

    /**
     * Handle a job failure.
     *
     * @param Throwable $exception
     *
     * @return void
     */
    public function failed(Throwable $exception)
    {
        Log::error($exception);

        $this->worker->update([
            'status' => 'error',
            'error'  => json_encode([
                'message' => $exception->getMessage(),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
                'trace'   => $exception->getTraceAsString(),
            ]),
        ]);
    }
}
