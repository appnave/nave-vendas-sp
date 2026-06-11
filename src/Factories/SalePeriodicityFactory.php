<?php

namespace BildVitta\SpVendas\Factories;

use BildVitta\SpVendas\Models\Sale;
use BildVitta\SpVendas\Models\SalePeriodicity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class SalePeriodicityFactory.
 *
 * @package BildVitta\SpVendas\Factories
 */
class SalePeriodicityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SalePeriodicity::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        /** @var Sale $sale */
        $sale = Sale::inRandomOrder()->first();

        return [
            'uuid'              => fake()->uuid(),
            'sale_id'           => $sale,
            'periodicity'       => fake()->randomKey(SalePeriodicity::PERIODICITY_LIST),
            'installments'      => fake()->numberBetween(1, 40),
            'installment_price' => fake()->randomFloat(2, 100, 10000),
            'due_at'            => fake()->date(),
        ];
    }
}
