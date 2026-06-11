<?php

namespace BildVitta\SpVendas\Console\Commands\DataImport\Sales\Jobs;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class SaleImportJob extends BaseJob
{
    public function process(): void
    {
        $currentTable = $this->worker->payload->tables[$this->worker->payload->table_index];

        switch ($currentTable) {
            case 'sales':
                $this->syncSales();
                break;
            case 'sale_accessories':
                $this->syncSaleAccessories();
                break;
            case 'sale_periodicities':
                $this->syncSalePeriodicities();
                break;
            default:
                $this->fail('Invalid current table');
                return;
                break;
        }

        $this->dispatchNextJob();
    }

    //

    protected function dispatchNextJob(): void
    {
        $payload = $this->worker->payload;
        $nextOffset = $payload->offset + $payload->limit;

        if ($nextOffset < $payload->total) {
            $payload->offset = $nextOffset;

            $this->worker->update(['payload' => $payload]);

            self::dispatch($this->worker->id);
        } else {
            $nextTableIndex = $payload->table_index + 1;

            if (isset($payload->tables[$nextTableIndex])) {
                $payload->table_index = $nextTableIndex;
                $payload->offset = 0;
                $payload->total = null;
                $this->worker->update(['payload' => $payload]);
                self::dispatch($this->worker->id);
            } else {
                unset($payload->table_index);
                unset($payload->offset);
                unset($payload->total);
                $this->worker->update([
                    'payload' => $payload,
                    'status'  => 'finished'
                ]);
            }
        }
    }

    //

    protected function syncSales(): void
    {
        $query = DB::connection('vendas')->table('sales as s')
            ->leftJoin('produto_real_estate_developments as red', 's.real_estate_development_id', '=', 'red.id')
            ->leftJoin('produto_units as u', 's.unit_id', '=', 'u.id')
            ->leftJoin('users as u_seller', 's.user_hub_seller_id', '=', 'u_seller.id')
            ->leftJoin('users as u_manager', 's.user_hub_manager_id', '=', 'u_manager.id')
            ->leftJoin('users as u_supervisor', 's.user_hub_supervisor_id', '=', 'u_supervisor.id')
            ->leftJoin('crm_customers as c', 's.crm_customer_id', '=', 'c.id')
            ->leftJoin('produto_blueprints as b', 's.blueprint_id', '=', 'b.id')
            ->leftJoin('produto_proposal_models as pm', 's.proposal_model_id', '=', 'pm.id')
            ->leftJoin('produto_buying_options as bo', 's.buying_options_id', '=', 'bo.id')
            ->leftJoin('hub_companies as hc', 's.hub_company_real_estate_agency_id', '=', 'hc.id')
            ->leftJoin('users as u_justified', 's.justified_user_id', '=', 'u_justified.id')
            ->select([
                's.*',
                'red.uuid as real_estate_development_uuid',
                'u.uuid as unit_uuid',
                'u_seller.hub_uuid as user_hub_seller_uuid',
                'u_manager.hub_uuid as user_hub_manager_uuid',
                'u_supervisor.hub_uuid as user_hub_supervisor_uuid',
                'c.uuid as crm_customer_uuid',
                'b.uuid as blueprint_uuid',
                'pm.uuid as proposal_model_uuid',
                'bo.uuid as buying_option_uuid',
                'hc.uuid as hub_company_real_estate_agency_uuid',
                'u_justified.hub_uuid as justified_user_uuid',
            ]);

        if (is_null($this->worker->payload->total)) {
            $payload = $this->worker->payload;
            $payload->total = $query->count();
            $this->worker->update(['payload' => $payload]);
        }

        if ($this->worker->payload->total > 0) {
            $query->limit($this->worker->payload->limit)->offset($this->worker->payload->offset);
            $query->get()->each(function ($item) {
                $item->real_estate_development_id = optional(
                    config('sp-produto.model_real_estate_development')::withTrashed()
                    ->select('id')
                    ->firstWhere('uuid', $item->real_estate_development_uuid)
                )->id;

                $item->unit_id = optional(
                    config('sp-produto.model_unit')::withTrashed()
                    ->select('id')
                    ->firstWhere('uuid', $item->unit_uuid)
                )->id;

                $item->crm_customer_id = optional(
                    config('sp-crm.model_customer')::withTrashed()
                    ->select('id')
                    ->firstWhere('uuid', $item->crm_customer_uuid)
                )->id;

                $item->blueprint_id = optional(
                    config('sp-produto.model_blueprint')::withTrashed()
                    ->select('id')
                    ->firstWhere('uuid', $item->blueprint_uuid)
                )->id;

                $item->proposal_model_id = optional(
                    config('sp-produto.model_proposal_model')::withTrashed()
                    ->select('id')
                    ->firstWhere('uuid', $item->proposal_model_uuid)
                )->id;

                $item->buying_option_id = optional(
                    config('sp-produto.model_buying_option')::withTrashed()
                    ->select('id')
                    ->firstWhere('uuid', $item->buying_option_uuid)
                )->id;

                $item->hub_company_real_estate_agency_id = optional(
                    config('hub.model_company')::withTrashed()
                    ->select('id')
                    ->firstWhere('uuid', $item->hub_company_real_estate_agency_uuid)
                )->id;

                $item->user_hub_seller_id = optional(
                    config('hub.model_user')::withTrashed()
                    ->select('id')
                    ->firstWhere('hub_uuid', $item->user_hub_seller_uuid)
                )->id;

                $item->user_hub_manager_id = optional(
                    config('hub.model_user')::withTrashed()
                    ->select('id')
                    ->firstWhere('hub_uuid', $item->user_hub_manager_uuid)
                )->id;

                $item->user_hub_supervisor_id = optional(
                    config('hub.model_user')::withTrashed()
                    ->select('id')
                    ->firstWhere('hub_uuid', $item->user_hub_supervisor_uuid)
                )->id;

                $item->justified_user_id = optional(
                    config('hub.model_user')::withTrashed()
                    ->select('id')
                    ->firstWhere('hub_uuid', $item->justified_user_uuid)
                )->id;

                $dates = [
                    'customer_justified_at',
                    'signed_contract_at',
                    'bill_paid_at',
                    'justified_at',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ];
                foreach ($dates as $date) {
                    if (!empty($item->{$date})) {
                        $item->{$date} = Carbon::parse($item->{$date})->greaterThan('0001-01-01 23:59:59') ? $item->{$date} : null;
                    }
                }

                config('sp-vendas.model_sale')::withTrashed()
                    ->firstOrNew(['uuid' => $item->uuid])
                    ->fill(collect($item)->toArray())
                    ->save();
            });
        }
    }

    protected function syncSaleAccessories(): void
    {
        $query = DB::connection('vendas')->table('sale_accessories as sa')
            ->leftJoin('sales as s', 'sa.sale_id', '=', 's.id')
            ->leftJoin('produto_real_estate_development_accessories as reda', 'sa.accessory_id', '=', 'reda.id')
            ->leftJoin('produto_accessory_categories as ac', 'sa.accessory_category_id', '=', 'ac.id')
            ->select([
                'sa.*',
                's.uuid as sale_uuid',
                'reda.uuid as real_estate_development_accessory_uuid',
                'ac.uuid as accessory_category_uuid',
            ]);

        if (is_null($this->worker->payload->total)) {
            $payload = $this->worker->payload;
            $payload->total = $query->count();
            $this->worker->update(['payload' => $payload]);
        }

        if ($this->worker->payload->total > 0) {
            $query->limit($this->worker->payload->limit)->offset($this->worker->payload->offset);
            $query->get()->each(function ($item) {

                $item->sale_id = optional(
                    config('sp-vendas.model_sale')::withTrashed()
                    ->select('id')
                    ->firstWhere('uuid', $item->sale_uuid)
                )->id;

                $item->accessory_id = optional(
                    config('sp-produto.model_real_estate_development_accessory')::withTrashed()
                    ->select('id')
                    ->firstWhere('uuid', $item->real_estate_development_accessory_uuid)
                )->id;

                $item->accessory_category_uuid = optional(
                    config('sp-produto.model_accessory_category')::withTrashed()
                    ->select('id')
                    ->firstWhere('uuid', $item->accessory_category_uuid)
                )->id;

                $dates = [
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ];
                foreach ($dates as $date) {
                    if (!empty($item->{$date})) {
                        $item->{$date} = Carbon::parse($item->{$date})->greaterThan('0001-01-01 23:59:59') ? $item->{$date} : null;
                    }
                }

                config('sp-vendas.model_sale_accessory')::withTrashed()
                    ->firstOrNew(['uuid' => $item->uuid])
                    ->fill(collect($item)->toArray())
                    ->save();
            });
        }
    }

    protected function syncSalePeriodicities(): void
    {
        $query = DB::connection('vendas')->table('sale_periodicities as sp')
            ->leftJoin('sales as s', 'sp.sale_id', '=', 's.id')
            ->select([
                'sp.*',
                's.uuid as sale_uuid',
            ]);
        if (is_null($this->worker->payload->total)) {
            $payload = $this->worker->payload;
            $payload->total = $query->count();
            $this->worker->update(['payload' => $payload]);
        }

        if ($this->worker->payload->total > 0) {
            $query->limit($this->worker->payload->limit)->offset($this->worker->payload->offset);
            $query->get()->each(function ($item) {

                $item->sale_id = optional(
                    config('sp-vendas.model_sale')::withTrashed()
                    ->select('id')
                    ->firstWhere('uuid', $item->sale_uuid)
                )->id;

                $dates = [
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ];
                foreach ($dates as $date) {
                    if (!empty($item->{$date})) {
                        $item->{$date} = Carbon::parse($item->{$date})->greaterThan('0001-01-01 23:59:59') ? $item->{$date} : null;
                    }
                }

                config('sp-vendas.model_sale_periodicity')::withTrashed()
                    ->firstOrNew(['uuid' => $item->uuid])
                    ->fill(collect($item)->toArray())
                    ->save();
            });
        }
    }
}
