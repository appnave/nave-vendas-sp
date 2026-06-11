<?php

namespace BildVitta\SpVendas\Factories;

use BildVitta\SpCrm\Models\Customer;
use BildVitta\SpProduto\Models\RealEstateDevelopment;
use BildVitta\SpVendas\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class SaleFactory.
 *
 * @package BildVitta\SpVendas\Factories
 */
class SaleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Sale::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        /** @var RealEstateDevelopment $realEstateDevelopment */
        $realEstateDevelopment = RealEstateDevelopment::has('unities')->inRandomOrder()->first();

        /** @var Customer $crm_customer */
        $crm_customer = Customer::inRandomOrder()->first();

        return [
            'uuid' => fake()->uuid(),

            'real_estate_development_id'        => $realEstateDevelopment,
            'unit_id'                           => $realEstateDevelopment->unities()->inRandomOrder()->first(),
            'user_hub_seller_id'                => config('hub.model_user')::inRandomOrder()->first(),
            'user_hub_manager_id'               => config('hub.model_user')::inRandomOrder()->first(),
            'user_hub_supervisor_id'            => config('hub.model_user')::inRandomOrder()->first(),
            'crm_customer_id'                   => $crm_customer,
            'blueprint_id'                      => $realEstateDevelopment->blueprints()->inRandomOrder()->first(),
            'proposal_model_id'                 => $realEstateDevelopment->proposal_models()->inRandomOrder()->first(),
            'buying_option_id'                  => $realEstateDevelopment->buying_options()->inRandomOrder()->first(),
            'hub_company_real_estate_agency_id' => config('hub.model_company')::inRandomOrder()->first(),
            'justified_user_id'                 => config('hub.model_user')::inRandomOrder()->first(),

            'contract_ref_uuid'      => fake()->uuid(),
            'concretized'            => fake()->boolean(),
            'special_needs'          => fake()->boolean(),
            'input'                  => fake()->randomFloat(2, 10000, 50000),
            'price_total'            => fake()->randomFloat(2, 10000, 50000),
            'commission_manager'     => fake()->randomFloat(2, 10000, 50000),
            'commission_supervisor'  => fake()->randomFloat(2, 10000, 50000),
            'commission_seller'      => fake()->randomFloat(2, 10000, 50000),
            'commission_real_estate' => fake()->randomFloat(2, 10000, 50000),
            'commission_option'      => fake()->randomElement(Sale::COMMISSION_OPTIONS),
            'is_insurance'           => fake()->boolean(),
            'justified'              => fake()->text(200),
            'justified_at'           => fake()->dateTime(),
            'customer_justified'     => null,
            'customer_justified_at'  => fake()->dateTime(),
            'made_at'                => fake()->date(),
            'status'                 => fake()->randomElement(Sale::STATUS),
            'signed_contract_at'     => fake()->date(),
            'bill_paid_at'           => fake()->date(),
            'made_by'                => config('hub.model_user')::inRandomOrder()->first(),
        ];
    }
}
