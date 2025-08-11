<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExchangeRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'base_currency',
        'target_currency',
        'rate',
        'rate_date',
        'fetched_at',
        'source'
    ];

    protected $casts = [
        'rate' => 'decimal:6',
        'rate_date' => 'date',
        'fetched_at' => 'datetime'
    ];

    /**
     * Get the base currency
     */
    public function baseCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'base_currency', 'code');
    }

    /**
     * Get the target currency
     */
    public function targetCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'target_currency', 'code');
    }

    /**
     * Scope for rates on a specific date
     */
    public function scopeForDate($query, $date)
    {
        return $query->where('rate_date', $date);
    }

    /**
     * Scope for a specific currency pair
     */
    public function scopeForPair($query, $base, $target)
    {
        return $query->where('base_currency', $base)
            ->where('target_currency', $target);
    }

    /**
     * Get the inverse rate (1/rate)
     */
    public function getInverseRateAttribute(): float
    {
        return $this->rate > 0 ? 1 / $this->rate : 0;
    }
}
