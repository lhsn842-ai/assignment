<?php

namespace App\Events;

use App\Models\ExchangeRate;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class ExchangeRateResultReadyEvent implements ShouldBroadcast
{
    use SerializesModels;

    public function __construct(public ExchangeRate $exchangeRate) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('user.' . $this->exchangeRate->user_id);
    }

    public function broadcastAs(): string
    {
        return 'ExchangeRateResultReadyEvent';
    }

    public function broadcastWith(): array
    {
        return [
            'exchangeRateId' => $this->exchangeRate->id,
            'result' => $this->exchangeRate->result,
        ];
    }
}
