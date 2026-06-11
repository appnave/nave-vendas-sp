<?php

namespace BildVitta\SpVendas\Console\Commands\DataImport\Sales;

use BildVitta\SpVendas\Console\Commands\DataImport\Sales\Jobs\SaleImportJob;
use BildVitta\SpVendas\Models\Worker;
use Illuminate\Console\Command;

class SaleImportCommand extends Command
{
    /**
     * @var string
     */
    public const WORKER_TYPE = 'sp-vendas.dataimport.sales';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dataimport:vendas_sales {--select=500} {--offset=0} {--tables=sales,sale_accessories,sale_periodicities}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Call init sync sales in database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting import');

        $selectLimit = 500;
        if ($optionSelect = $this->option('select')) {
            $selectLimit = (int) $optionSelect;
        }

        $offset = 0;
        if ($optionOffset = $this->option('offset')) {
            $offset = (int) $optionOffset;
        }

        $tableIndex = 0;
        $tables = explode(',', $this->option('tables'));

        $worker = new Worker();
        $worker->type = self::WORKER_TYPE;
        $worker->status = 'created';
        $worker->schedule = now();
        $worker->payload = [
            'limit'       => $selectLimit,
            'offset'      => $offset,
            'total'       => null,
            'table_index' => $tableIndex,
            'tables'      => $tables,
        ];
        $worker->save();

        SaleImportJob::dispatch($worker->id);

        $this->info('Worker type: ' . self::WORKER_TYPE);
        $this->info('Job started, command execution ended');

        return 0;
    }
}
