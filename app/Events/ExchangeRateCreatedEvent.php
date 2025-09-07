<?php

namespace App\Events;

use App\Models\ExchangeRate;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use MongoDB\Laravel\Eloquent\Model;

class ExchangeRateCreatedEvent
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public readonly ExchangeRate $exchangeRate)
    {
    }
}
