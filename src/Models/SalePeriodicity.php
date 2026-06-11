<?php

namespace BildVitta\SpVendas\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class SalePeriodicity.
 *
 * @package BildVitta\SpVendas\Models
 */
class SalePeriodicity extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    public const PERIODICITY_LIST = [
        // produto
        'financing'          => 'Financiamento',
        'fgts'               => 'FGTS',
        'subsidy'            => 'Subsídio',
        'down_payment'       => 'Entrada',
        'intermediate'       => 'Intermediária',
        'post_construction'  => 'Pós-obra',
        'monthly'            => 'Mensal',
        'bimonthly'          => 'Bimestral',
        'quarterly'          => 'Trimestral',
        'semiannual'         => 'Semestral',
        'yearly'             => 'Anual',
        'conclusion_balance' => 'Saldo Conclusão',

        //vendas
        'signal'                           => 'Sinal',
        'periodicity'                      => 'Periodicidade',
        'final'                            => 'Final',
        'vehicle_exchange'                 => 'Dação em pagamento - Veículo',
        'real_estate_development_exchange' => 'Dação em pagamento - Imóvel',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('sp-vendas.table_prefix') . 'sale_periodicities';
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',

        'sale_id',
        'periodicity',
        'installments',
        'installment_price',
        'installment_amount',
        'payment_method',
        'due_at',


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
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }
}
