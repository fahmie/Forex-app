<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_currency()
    {
        $currency = Currency::create([
            'code' => 'TEST',
            'name' => 'Test Currency',
            'symbol' => 'T',
            'is_active' => true,
            'decimal_places' => 2
        ]);

        $this->assertDatabaseHas('currencies', [
            'code' => 'TEST',
            'name' => 'Test Currency'
        ]);
    }

    public function test_currency_code_is_unique()
    {
        Currency::create([
            'code' => 'TEST',
            'name' => 'Test Currency',
            'symbol' => 'T',
            'is_active' => true,
            'decimal_places' => 2
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Currency::create([
            'code' => 'TEST',
            'name' => 'Another Test Currency',
            'symbol' => 'A',
            'is_active' => true,
            'decimal_places' => 2
        ]);
    }

    public function test_active_scope_returns_only_active_currencies()
    {
        Currency::create([
            'code' => 'ACTIVE',
            'name' => 'Active Currency',
            'symbol' => 'A',
            'is_active' => true,
            'decimal_places' => 2
        ]);

        Currency::create([
            'code' => 'INACTIVE',
            'name' => 'Inactive Currency',
            'symbol' => 'I',
            'is_active' => false,
            'decimal_places' => 2
        ]);

        $activeCurrencies = Currency::active()->get();

        $this->assertEquals(1, $activeCurrencies->count());
        $this->assertEquals('ACTIVE', $activeCurrencies->first()->code);
    }

    public function test_formatted_name_attribute()
    {
        $currency = Currency::create([
            'code' => 'TEST',
            'name' => 'Test Currency',
            'symbol' => 'T',
            'is_active' => true,
            'decimal_places' => 2
        ]);

        $this->assertEquals('Test Currency (T)', $currency->formatted_name);
    }

    public function test_formatted_name_without_symbol()
    {
        $currency = Currency::create([
            'code' => 'TEST',
            'name' => 'Test Currency',
            'symbol' => null,
            'is_active' => true,
            'decimal_places' => 2
        ]);

        $this->assertEquals('Test Currency', $currency->formatted_name);
    }
}
