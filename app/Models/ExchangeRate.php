<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\HasOne;

/**
 * @property string $id
 * @property string $user_id
 * @property string $from_currency
 * @property string $to_currency
 * @property float $amount
 * @property float|null $result
 * @property string $status
 * @property int $attempts
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ExchangeRate extends Model
{
    /** HasFactory<ExchangeRateFactory> */
    use HasFactory;

    protected $table = 'exchange_rates';
    protected $primaryKey = '_id';
    protected $casts = [
        '_id' => 'string',
    ];

    public $timestamps = true;
    protected $dates = [
        'created_at',
        'updated_at',
    ];
    protected $fillable = [
        'user_id',
        'from_currency',
        'to_currency',
        'amount',
        'result',
        'status',
        'attempts',
        'created_at',
        'updated_at',
    ];

    /** @return HasOne<User, ExchangeRate> */
    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
}
