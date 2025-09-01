<?php

namespace Database\Factories;

use App\Models\ExchangeRate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ExchangeRateFactory extends Factory
{
    protected $model = ExchangeRate::class;

    public function definition()
    {
        return [
            '_id' => (string) Str::uuid(),
            'user_id' => (string) Str::uuid(),
            'from_currency' => $this->faker->currencyCode(),
            'to_currency' => $this->faker->currencyCode(),
            'amount' => $this->faker->randomFloat(2, 1, 1000),
            'result' => null,
            'status' => 'pending',
            'attempts' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
