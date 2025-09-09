<?php

namespace App\Services\ExchangeRateService;

use App\Models\ExchangeRate;
use App\ValueObjects\SingleCurrencyExchangeRateVOInterface;
use App\ValueObjects\SwopSingleCurrencyExchangeRateVO;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class SwopService implements ExchangeRateServiceInterface
{
    protected string $apiUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.swop.api_key');
        $this->apiUrl = config('services.swop.base_url');
    }

    public function exchangeSingleCurrency(ExchangeRate $exchangeRate): SingleCurrencyExchangeRateVOInterface
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'ApiKey ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->get($this->apiUrl . '/rates/' . $exchangeRate->from_currency . '/' . $exchangeRate->to_currency);

            if ($response->failed()) {
                throw new Exception('API request failed: ' . $response->body());
            }

            $responseAsArray = json_decode($response->body(), true);
            return new SwopSingleCurrencyExchangeRateVO($responseAsArray['quote'], $responseAsArray['date']);
        } catch (Throwable $e) {
            throw new Exception('Error fetching conversion: ' . $e->getMessage());
        }
    }

    public function getCacheKey(ExchangeRate $exchangeRate): string
    {
        return sprintf(
            'exchange_rate:%s:%s',
            $exchangeRate->from_currency,
            $exchangeRate->to_currency,
        );
    }

    public function getCachedValue(ExchangeRate $exchangeRate): ?ExchangeRate
    {
        $cacheKey = $this->getCacheKey($exchangeRate);
        if (Cache::has($cacheKey)) {
            $cachedValue = Cache::get($cacheKey);
            $exchangeRate->result = (int) $cachedValue * $exchangeRate->amount;
            return $exchangeRate;
        } else {
            return null;
        }
    }
}
