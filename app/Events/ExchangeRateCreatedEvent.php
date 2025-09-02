<?php

namespace App\Events;

use App\Models\ExchangeRate;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use MongoDB\Laravel\Eloquent\Model;

class ExchangeRateCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public readonly ExchangeRate $exchangeRate)
    {
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('user.' . $this->exchangeRate->user_id);
    }

    public function broadcastWith(): array
    {
        return [
            'id'            => $this->exchangeRate->id,
            'from_currency' => $this->exchangeRate->from_currency,
            'to_currency'   => $this->exchangeRate->to_currency,
            'amount'        => $this->exchangeRate->amount,
            'result'        => $this->exchangeRate->result,
            'status'        => $this->exchangeRate->status,
            'created_at'    => $this->exchangeRate->created_at,
            'updated_at'    => $this->exchangeRate->updated_at,
        ];
    }
}
