<?php

namespace Tests\Unit;

use App\Models\CurrencyRate;
use App\Services\CurrencyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencyServiceTest extends TestCase
{
    use RefreshDatabase;

    private CurrencyService $currencyService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->currencyService = new CurrencyService;
    }

    public function test_returns_one_for_same_currency(): void
    {
        $rate = $this->currencyService->getRate('USD', 'USD');

        $this->assertEquals(1.0, $rate);
    }

    public function test_returns_cached_rate(): void
    {
        CurrencyRate::create([
            'from_currency' => 'USD',
            'to_currency' => 'EUR',
            'rate' => 0.85,
            'fetched_at' => now(),
        ]);

        $rate = $this->currencyService->getRate('USD', 'EUR');

        $this->assertEquals(0.85, $rate);
    }

    public function test_converts_amount(): void
    {
        CurrencyRate::create([
            'from_currency' => 'USD',
            'to_currency' => 'EUR',
            'rate' => 0.85,
            'fetched_at' => now(),
        ]);

        $result = $this->currencyService->convert(100, 'USD', 'EUR');

        $this->assertEquals(85.00, $result);
    }
}
