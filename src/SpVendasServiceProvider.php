<?php

namespace BildVitta\SpVendas;

use BildVitta\SpVendas\Console\Commands\DataImport\Sales\SaleImportCommand;
use BildVitta\SpVendas\Console\Commands\Messages\SalesWorkerCommand;
use BildVitta\SpVendas\Console\InstallSp;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

/**
 * Class SpVendasServiceProvider.
 *
 * @package BildVitta\SpVendas
 */
class SpVendasServiceProvider extends PackageServiceProvider
{
    /**
     * @var string $seeder
     */
    protected string $seeder = 'SpVendasSeeder';

    /**
     * @param  Package  $package
     *
     * @return void
     */
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('sp-vendas')
            ->hasConfigFile(['sp-vendas'])
            ->hasMigrations([
                'create_sp_vendas_sales_table', // must be before other sale tables
                'create_sp_vendas_sale_accessories_table',
                'create_sp_vendas_sale_periodicities_table',
            ])
            ->runsMigrations();

        $package
            ->name('sp-vendas')
            ->hasCommands([
                InstallSp::class,
                SalesWorkerCommand::class,
                SaleImportCommand::class,
            ]);

        $this->publishes([
            $package->basePath("/../database/seeders/{$this->seeder}.php.stub")
            => database_path("seeders/{$this->seeder}.php")
        ], 'seeders');
    }
}
