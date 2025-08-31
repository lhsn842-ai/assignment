<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\HasOne;

class ExchangeRate extends Model
{
    protected $table = 'exchange_rates';
    protected $primaryKey = '_id';
    protected $casts = [
        '_id' => 'string',
    ];

    protected $fillable = [
        'id',
        'user_id',
        'from_currency',
        'to_currency',
        'amount',
        'result',
        'status',
        'attempts',
    ];

    /** @return HasOne<User> */
    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
}
