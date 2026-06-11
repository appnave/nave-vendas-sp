<?php

namespace BildVitta\SpVendas\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Sale.
 *
 * @package BildVitta\SpVendas\Models
 */
class Sale extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS = [
        'permutation' => 'Permutante',      // 10 - Permutante - Reserva para permutante

        'simulation'  => 'Simulação',        // 15 - Interesse - Unidade selecionada
        'in_approval' => 'Em aprovação',    // 15 - Interesse - Unidade selecionada
        'reproved'    => 'Recusada',           // 15 - Interesse - Unidade selecionada
        'processing'  => 'Processando',      // 15 - Interesse - Unidade selecionada
        'failed'      => 'Falhou',               // 15 - Interesse - Unidade selecionada

        'pre_sold'   => 'Pré-Venda',          // 30 - Pré-venda - Proposta aprovada
        'commercial' => 'Comercial',        // 35 - Comercial - Impressão do contrato
        'legal'      => 'Jurídico',              // 40 - Bild Jurídico / Vitta assinado - Validação comercial do contrato
        'credit'     => 'Crédito',              // 45 - Crédito imobiliário/Repasse - Validação jurídica do contrato
        'sold'       => 'Vendida',                // 50 - Vendido - Venda validada
        'distracted' => 'Distrato',         // 55 - Venda distratada - Venda distratada
        'canceled'   => 'Cancelada',          // 60 - Venda cancelada - Venda cancelada
    ];

    public const COMMISSION_OPTIONS = [
        'sales_team'           => 'Equipe de Vendas',
        'external_real_estate' => 'Imobiliária Externa'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('sp-vendas.table_prefix') . 'sales';
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',

        'external_code',
        'contract_ref_uuid',
        'concretized',
        'special_needs',
        'input',
        'price_total',
        'is_insurance',
        'commission_option',
        'commission_manager',
        'commission_supervisor',
        'commission_seller',
        'commission_real_estate',
        'justified',
        'customer_justified',
        'customer_justified_at',
        'justified_at',
        'made_at',
        'made_by',
        'status',
        'signed_contract_at',
        'bill_paid_at',

        'real_estate_development_id',
        'blueprint_id',
        'proposal_model_id',
        'buying_option_id',
        'unit_id',
        'crm_customer_id',
        'user_hub_seller_id',
        'user_hub_manager_id',
        'user_hub_supervisor_id',
        'justified_user_id',
        'hub_company_real_estate_agency_id',

        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [

    ];

    /**
     * @return BelongsTo
     */
    public function real_estate_development(): BelongsTo
    {
        return $this->belongsTo(config('sp-produto.model_real_estate_development'), 'real_estate_development_id');
    }

    /**
     * @return BelongsTo
     */
    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(config('sp-produto.model_blueprint'), 'blueprint_id');
    }

    /**
     * @return BelongsTo
     */
    public function proposal_model(): BelongsTo
    {
        return $this->belongsTo(config('sp-produto.model_proposal_model'), 'proposal_model_id');
    }

    /**
     * @return BelongsTo
     */
    public function buying_option(): BelongsTo
    {
        return $this->belongsTo(config('sp-produto.model_buying_option'), 'buying_option_id');
    }

    /**
     * @return BelongsTo
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(config('sp-produto.model_unit'), 'unit_id');
    }

    /**
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(config('sp-crm.model_customer'), 'crm_customer_id');
    }

    /**
     * @return BelongsTo
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(config('hub.model_user'), 'user_hub_seller_id');
    }

    /**
     * @return BelongsTo
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(config('hub.model_user'), 'user_hub_manager_id');
    }

    /**
     * @return BelongsTo
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(config('hub.model_user'), 'user_hub_supervisor_id');
    }

    /**
     * @return BelongsTo
     */
    public function justified_user(): BelongsTo
    {
        return $this->belongsTo(config('hub.model_user'), 'justified_user_id');
    }

    /**
     * @return BelongsTo
     */
    public function hub_company_real_estate_agency(): BelongsTo
    {
        return $this->belongsTo(app(config('hub.model_company')), 'hub_company_real_estate_agency_id');
    }

    /**
     * @return HasMany
     */
    public function accessories(): HasMany
    {
        return $this->hasMany(SaleAccessory::class, 'sale_id');
    }

    /**
     * @return HasMany
     */
    public function periodicities(): HasMany
    {
        return $this->hasMany(SalePeriodicity::class, 'sale_id');
    }
}
