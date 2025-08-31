<?php

namespace App\Services\ExchangeRateService;

use App\Models\ExchangeRate;
use App\ValueObjects\SingleCurrencyExchangeRateVOInterface;

interface ExchangeRateServiceInterface
{
    public function exchangeSingleCurrency(ExchangeRate $exchangeRate): SingleCurrencyExchangeRateVOInterface;
}
