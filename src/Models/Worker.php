<?php

namespace BildVitta\SpVendas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class Worker extends Model
{
    use SoftDeletes;

    /**
     * @var string
     */
    public const STATUS_IN_PROGRESS = 'in_progress';

    /**
     * @var string
     */
    public const STATUS_CREATED = 'created';

    /**
     * @var string
     */
    public const STATUS_ERROR = 'error';

    /**
     * @var string
     */
    public const STATUS_FINISHED = 'finished';

    /**
     * @var string
     */
    protected $table = 'workers';

    /**
     * @var string
     */
    protected const KEY_UUID = 'uuid';

    /**
     * @var string
     */
    protected static string $keyUuid = self::KEY_UUID;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'uuid',
        'type',
        'payload',
        'status',
        'attempts',
        'error',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'payload'  => 'object',
        'error'    => 'object',
        'attempts' => 'int',
        'schedule' => 'datetime'
    ];

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    public static function boot(): void
    {
        parent::boot();

        self::creating(function ($model) {
            if (collect($model->getFillable())->filter(fn (string $columnName) => $columnName === static::$keyUuid)->isNotEmpty()) {
                $model->uuid = (string) Uuid::uuid4();
            }
        });
    }
}
