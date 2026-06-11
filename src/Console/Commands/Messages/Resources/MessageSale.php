<?php

namespace BildVitta\SpVendas\Console\Commands\Messages\Resources;

use BildVitta\SpVendas\Models\Sale;
use BildVitta\SpVendas\Models\SaleAccessory;
use BildVitta\SpVendas\Models\SalePeriodicity;
use PhpAmqpLib\Message\AMQPMessage;
use stdClass;
use Throwable;

class MessageSale
{
    use LogHelper;

    /**
     * @var string
     */
    public const UPDATED = 'sales.updated';

    /**
     * @var string
     */
    public const CREATED = 'sales.created';

    /**
     * @var stdClass $sale
     */
    protected stdClass $sale;

    /**
     * @var string $modelUser
     */
    protected string $modelUser;

    /**
     * @var string $modelCompany
     */
    protected string $modelCompany;

    /**
     * @param AMQPMessage $message
     * @return void
     * @throws Throwable
     */
    public function process(AMQPMessage $message): void
    {
        $message->ack();
        $customer = null;
        $messageBody = null;
        try {
            $messageBody = $message->getBody();
            $sale = json_decode($messageBody);
            $properties = $message->get_properties();
            $operation = $properties['type'];
            switch ($operation) {
                case self::CREATED:
                case self::UPDATED:
                    $this->updateOrCreate($sale);
                    break;
                default:
                    break;
            }
        } catch (Throwable $exception) {
            $this->logError($exception, $messageBody);
        }
    }

    /**
     * @param stdClass $sale
     * @return void
     */
    private function updateOrCreate(stdClass $sale): void
    {
        $this->sale = $sale;
        $this->modelUser = config('hub.model_user');
        $this->modelCompany = config('hub.model_company');

        $saleObj = $this->syncSale();

        $this->syncPeriodicities($saleObj);
        $this->syncAccessories($saleObj);
    }

    /**
     * @return Sale $sale
     */
    private function syncSale(): Sale
    {
        $data = [
            'uuid' => $this->sale->uuid,

            'external_code'          => $this->sale->external_code,
            'contract_ref_uuid'      => $this->sale->contract_ref_uuid,
            'concretized'            => $this->sale->concretized,
            'special_needs'          => $this->sale->special_needs,
            'input'                  => $this->sale->input,
            'price_total'            => $this->sale->price_total,
            'is_insurance'           => $this->sale->is_insurance,
            'commission_option'      => $this->sale->commission_option,
            'commission_manager'     => $this->sale->commission_manager,
            'commission_supervisor'  => $this->sale->commission_supervisor,
            'commission_seller'      => $this->sale->commission_seller,
            'commission_real_estate' => $this->sale->commission_real_estate,
            'justified'              => $this->sale->justified,
            'customer_justified'     => $this->sale->customer_justified,
            'customer_justified_at'  => $this->sale->customer_justified_at,
            'justified_at'           => $this->sale->justified_at,
            'made_at'                => $this->sale->made_at,
            'made_by'                => $this->sale->made_by,
            'status'                 => $this->sale->status,
            'signed_contract_at'     => $this->sale->signed_contract_at,
            'bill_paid_at'           => $this->sale->bill_paid_at,


            'real_estate_development_id' => optional(
                config('sp-produto.model_real_estate_development')::withTrashed()
                ->select('id')
                ->firstWhere('uuid', $this->sale->real_estate_development)
            )->id,
            'blueprint_id' => optional(
                config('sp-produto.model_blueprint')::withTrashed()
                ->select('id')
                ->firstWhere('uuid', $this->sale->blueprint)
            )->id,
            'proposal_model_id' => optional(
                config('sp-produto.model_proposal_model')::withTrashed()
                ->select('id')
                ->firstWhere('uuid', $this->sale->proposal_model)
            )->id,
            'buying_option_id' => optional(
                config('sp-produto.model_buying_option')::withTrashed()
                ->select('id')
                ->firstWhere('uuid', $this->sale->buying_option)
            )->id,
            'unit_id' => optional(
                config('sp-produto.model_unit')::withTrashed()
                ->select('id')
                ->firstWhere('uuid', $this->sale->unit)
            )->id,
            'crm_customer_id' => optional(
                config('sp-crm.model_customer')::withTrashed()
                ->select('id')
                ->firstWhere('uuid', $this->sale->crm_customer)
            )->id,
            'user_hub_seller_id' => optional(
                config('hub.model_user')::withTrashed()
                ->select('id')
                ->firstWhere('hub_uuid', $this->sale->user_hub_seller)
            )->id,
            'user_hub_manager_id' => optional(
                config('hub.model_user')::withTrashed()
                ->select('id')
                ->firstWhere('hub_uuid', $this->sale->user_hub_manager)
            )->id,
            'user_hub_supervisor_id' => optional(
                config('hub.model_user')::withTrashed()
                ->select('id')
                ->firstWhere('hub_uuid', $this->sale->user_hub_supervisor)
            )->id,
            'justified_user_id' => optional(
                config('hub.model_user')::withTrashed()
                ->select('id')
                ->firstWhere('hub_uuid', $this->sale->justified_user)
            )->id,
            'hub_company_real_estate_agency_id' => optional(
                config('hub.model_company')::withTrashed()
                ->select('id')
                ->firstWhere('uuid', $this->sale->hub_company_real_estate_agency)
            )->id,

            'created_at' => $this->sale->created_at,
            'updated_at' => $this->sale->updated_at,
            'deleted_at' => $this->sale->deleted_at,
        ];

        return Sale::updateOrCreate(['uuid' => $this->sale->uuid], $data);
    }

    /**
     * @param string $param
     * @param string $model
     * @param string $uuid
     * @return int|null
     */
    private function syncRelated(string $param, string $model, string $uuid = 'uuid'): int|null
    {
        $obj = $model::firstWhere($uuid, $this->sale->{$param});
        return $obj?->id;
    }

    /**
     * @param Sale $sale
     * @return void
     */
    private function syncPeriodicities(Sale $sale): void
    {
        foreach ($this->sale->periodicities as $periodicity) {
            $data = [
                'uuid' => $periodicity->uuid,

                'sale_id'            => $sale->id,
                'periodicity'        => $periodicity->periodicity,
                'installments'       => $periodicity->installments,
                'installment_price'  => $periodicity->installment_price,
                'installment_amount' => $periodicity->installment_amount,
                'payment_method'     => $periodicity->payment_method,
                'due_at'             => $periodicity->due_at,

                'created_at' => $periodicity->created_at,
                'updated_at' => $periodicity->updated_at,
                'deleted_at' => $periodicity->deleted_at,
            ];

            SalePeriodicity::updateOrCreate(['uuid' => $periodicity->uuid], $data);
        }
    }

    /**
     * @param Sale $sale
     * @return void
     */
    private function syncAccessories(Sale $sale): void
    {
        foreach ($this->sale->accessories as $accessory) {
            $data = [
                'uuid' => $accessory->uuid,

                'sale_id'               => $sale->id,
                'accessory_category_id' => optional(
                    config('sp-produto.model_accessory_category')::withTrashed()
                    ->select('id')
                    ->firstWhere('uuid', $accessory->category?->uuid)
                )->id,
                'accessory_id' => optional(
                    config('sp-produto.model_real_estate_development_accessory')::withTrashed()
                    ->select('id')
                    ->firstWhere('uuid', $accessory->accessory?->uuid)
                )->id,

                'created_at' => $accessory->created_at,
                'updated_at' => $accessory->updated_at,
                'deleted_at' => $accessory->deleted_at,
            ];

            SaleAccessory::updateOrCreate(['uuid' => $accessory->uuid], $data);
        }
    }
}
