<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Currency;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            [
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => '$',
                'is_active' => true,
                'decimal_places' => 2
            ],
            [
                'code' => 'EUR',
                'name' => 'Euro',
                'symbol' => '€',
                'is_active' => true,
                'decimal_places' => 2
            ],
            [
                'code' => 'GBP',
                'name' => 'British Pound',
                'symbol' => '£',
                'is_active' => true,
                'decimal_places' => 2
            ],
            [
                'code' => 'JPY',
                'name' => 'Japanese Yen',
                'symbol' => '¥',
                'is_active' => true,
                'decimal_places' => 0
            ],
            [
                'code' => 'AUD',
                'name' => 'Australian Dollar',
                'symbol' => 'A$',
                'is_active' => true,
                'decimal_places' => 2
            ],
            [
                'code' => 'CAD',
                'name' => 'Canadian Dollar',
                'symbol' => 'C$',
                'is_active' => true,
                'decimal_places' => 2
            ],
            [
                'code' => 'CHF',
                'name' => 'Swiss Franc',
                'symbol' => 'CHF',
                'is_active' => true,
                'decimal_places' => 2
            ],
            [
                'code' => 'CNY',
                'name' => 'Chinese Yuan',
                'symbol' => '¥',
                'is_active' => true,
                'decimal_places' => 2
            ],
            [
                'code' => 'SEK',
                'name' => 'Swedish Krona',
                'symbol' => 'kr',
                'is_active' => true,
                'decimal_places' => 2
            ],
            [
                'code' => 'NZD',
                'name' => 'New Zealand Dollar',
                'symbol' => 'NZ$',
                'is_active' => true,
                'decimal_places' => 2
            ],
            [
                'code' => 'MXN',
                'name' => 'Mexican Peso',
                'symbol' => '$',
                'is_active' => true,
                'decimal_places' => 2
            ],
            [
                'code' => 'SGD',
                'name' => 'Singapore Dollar',
                'symbol' => 'S$',
                'is_active' => true,
                'decimal_places' => 2
            ],
            [
                'code' => 'HKD',
                'name' => 'Hong Kong Dollar',
                'symbol' => 'HK$',
                'is_active' => true,
                'decimal_places' => 2
            ],
            [
                'code' => 'NOK',
                'name' => 'Norwegian Krone',
                'symbol' => 'kr',
                'is_active' => true,
                'decimal_places' => 2
            ],
            [
                'code' => 'MYR',
                'name' => 'Malaysian Ringgit',
                'symbol' => 'RM',
                'is_active' => true,
                'decimal_places' => 2
            ],
            [
                'code' => 'PHP',
                'name' => 'Philippine Peso',
                'symbol' => '₱',
                'is_active' => true,
                'decimal_places' => 2
            ],
            [
                'code' => 'THB',
                'name' => 'Thai Baht',
                'symbol' => '฿',
                'is_active' => true,
                'decimal_places' => 2
            ],
            [
                'code' => 'INR',
                'name' => 'Indian Rupee',
                'symbol' => '₹',
                'is_active' => true,
                'decimal_places' => 2
            ],
            [
                'code' => 'KRW',
                'name' => 'South Korean Won',
                'symbol' => '₩',
                'is_active' => true,
                'decimal_places' => 0
            ],
            [
                'code' => 'HUF',
                'name' => 'Hungarian Forint',
                'symbol' => 'Ft',
                'is_active' => true,
                'decimal_places' => 0
            ]
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['code' => $currency['code']],
                $currency
            );
        }
    }
}
