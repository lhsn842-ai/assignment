<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ExchangeRateResultReadyEvent implements ShouldBroadcast
{
    public function __construct(
        public string $exchangeRateId,
        public string $userId,
        public ?float $result = null
    ) {}

    public function broadcastOn(): Channel
    {
        $channel = 'user.' . $this->userId;
        return new Channel($channel);
    }
    
    public function broadcastAs(): string
    {
        return 'ExchangeRateResultReadyEvent';
    }

    public function broadcastWith(): array
    {
        return [
            'exchangeRateId' => $this->exchangeRateId,
            'result' => $this->result,
        ];
    }
}
