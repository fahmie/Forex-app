<?php

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExchangeRate>
 */
class ExchangeRateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $baseCurrency = Currency::inRandomOrder()->first() ?? Currency::factory()->create();
        $targetCurrency = Currency::where('code', '!=', $baseCurrency->code)->inRandomOrder()->first() ?? Currency::factory()->create();

        return [
            'base_currency' => $baseCurrency->code,
            'target_currency' => $targetCurrency->code,
            'rate' => $this->faker->randomFloat(6, 0.0001, 1000.0),
            'rate_date' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'fetched_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'source' => $this->faker->randomElement(['api', 'manual', 'seeder', 'system']),
        ];
    }

    /**
     * Indicate that the exchange rate is for today.
     */
    public function today(): static
    {
        return $this->state(fn(array $attributes) => [
            'rate_date' => Carbon::today()->toDateString(),
            'fetched_at' => Carbon::now(),
        ]);
    }

    /**
     * Indicate that the exchange rate is for a specific date.
     */
    public function forDate(string $date): static
    {
        return $this->state(fn(array $attributes) => [
            'rate_date' => $date,
            'fetched_at' => Carbon::parse($date),
        ]);
    }

    /**
     * Indicate that the exchange rate is from a specific source.
     */
    public function fromSource(string $source): static
    {
        return $this->state(fn(array $attributes) => [
            'source' => $source,
        ]);
    }
}
