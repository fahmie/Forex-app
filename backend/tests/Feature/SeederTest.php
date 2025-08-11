<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_currency_seeder_creates_currencies()
    {
        $this->artisan('db:seed', ['--class' => 'CurrencySeeder']);

        $currencies = Currency::all();

        $this->assertGreaterThan(0, $currencies->count());

        // Check if major currencies exist
        $this->assertTrue(Currency::where('code', 'USD')->exists());
        $this->assertTrue(Currency::where('code', 'EUR')->exists());
        $this->assertTrue(Currency::where('code', 'GBP')->exists());
        $this->assertTrue(Currency::where('code', 'JPY')->exists());
    }

    public function test_exchange_rate_seeder_creates_rates()
    {
        // First seed currencies
        $this->artisan('db:seed', ['--class' => 'CurrencySeeder']);

        // Then seed exchange rates
        $this->artisan('db:seed', ['--class' => 'ExchangeRateSeeder']);

        $rates = ExchangeRate::all();

        $this->assertGreaterThan(0, $rates->count());

        // Check if rates are created for today
        $todayRates = ExchangeRate::whereDate('rate_date', today())->get();
        $this->assertGreaterThan(0, $todayRates->count());
    }

    public function test_database_seeder_runs_all_seeders()
    {
        $this->artisan('db:seed');

        $currencies = Currency::all();
        $rates = ExchangeRate::all();

        $this->assertGreaterThan(0, $currencies->count());
        $this->assertGreaterThan(0, $rates->count());
    }

    public function test_currencies_have_correct_structure()
    {
        $this->artisan('db:seed', ['--class' => 'CurrencySeeder']);

        $currency = Currency::where('code', 'USD')->first();

        $this->assertNotNull($currency);
        $this->assertEquals('US Dollar', $currency->name);
        $this->assertEquals('$', $currency->symbol);
        $this->assertTrue($currency->is_active);
        $this->assertEquals(2, $currency->decimal_places);
    }

    public function test_exchange_rates_have_correct_structure()
    {
        $this->artisan('db:seed', ['--class' => 'CurrencySeeder']);
        $this->artisan('db:seed', ['--class' => 'ExchangeRateSeeder']);

        $rate = ExchangeRate::first();

        $this->assertNotNull($rate);
        $this->assertNotNull($rate->base_currency);
        $this->assertNotNull($rate->target_currency);
        $this->assertNotNull($rate->rate);
        $this->assertNotNull($rate->rate_date);
        $this->assertEquals('seeder', $rate->source);
    }
}
