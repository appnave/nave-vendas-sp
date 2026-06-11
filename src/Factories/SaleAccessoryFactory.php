<?php

namespace BildVitta\SpVendas\Factories;

use BildVitta\SpProduto\Models\RealEstateDevelopment\Accessory;
use BildVitta\SpVendas\Models\Sale;
use BildVitta\SpVendas\Models\SaleAccessory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class SaleAccessoryFactory.
 *
 * @package BildVitta\SpVendas\Factories
 */
class SaleAccessoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SaleAccessory::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        /** @var Sale $sale */
        $sale = Sale::inRandomOrder()->first();

        /** @var Accessory $accessory */
        $accessory = $sale->real_estate_development->accessories->first();

        return [
            'uuid'                  => fake()->uuid(),
            'sale_id'               => $sale,
            'accessory_id'          => $accessory->accessory,
            'accessory_category_id' => $accessory->category,
        ];
    }
}
