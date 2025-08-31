<?php

namespace Tests\Unit\App\Services;

use App\Models\ExchangeRate;
use App\Services\ExchangeRateService\SwopService;
use App\ValueObjects\SwopSingleCurrencyExchangeRateVO;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SwopServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure API key is set for tests
        putenv('SWOP_API_KEY=test-api-key');
    }

    /** @test */
    public function test_returns_a_vo_on_successful_api_response(): void
    {
        $exchangeRate = new ExchangeRate([
            'from_currency' => 'USD',
            'to_currency' => 'EUR',
            'amount' => 100,
        ]);

        $fakeResponse = [
            'quote' => 0.92,
            'date' => '2025-08-31',
        ];

        Http::fake([
            'https://swop.cx/rest/rates/USD/EUR' => Http::response($fakeResponse, 200),
        ]);

        $service = new SwopService();
        $result = $service->exchangeSingleCurrency($exchangeRate);

        $this->assertInstanceOf(SwopSingleCurrencyExchangeRateVO::class, $result);
        $this->assertEquals(0.92, $result->getQuote());
        $this->assertEquals('2025-08-31', $result->getDate());
    }

    /** @test */
    public function test_throws_exception_on_failed_api_response(): void
    {
        $exchangeRate = new ExchangeRate([
            'from_currency' => 'USD',
            'to_currency' => 'EUR',
            'amount' => 100,
        ]);

        Http::fake([
            'https://swop.cx/rest/rates/USD/EUR' => Http::response('Unauthorized', 401),
        ]);

        $service = new SwopService();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('API request failed: Unauthorized');

        $service->exchangeSingleCurrency($exchangeRate);
    }

    /** @test */
    public function test_throws_exception_on_invalid_json(): void
    {
        $exchangeRate = new ExchangeRate([
            'from_currency' => 'USD',
            'to_currency' => 'EUR',
            'amount' => 100,
        ]);

        Http::fake([
            'https://swop.cx/rest/rates/USD/EUR' => Http::response('not-a-json', 200),
        ]);

        $service = new SwopService();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Error fetching conversion:/');

        $service->exchangeSingleCurrency($exchangeRate);
    }
}
