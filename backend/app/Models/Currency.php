<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'is_active',
        'decimal_places'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'decimal_places' => 'integer'
    ];

    /**
     * Get exchange rates where this currency is the base
     */
    public function baseRates(): HasMany
    {
        return $this->hasMany(ExchangeRate::class, 'base_currency', 'code');
    }

    /**
     * Get exchange rates where this currency is the target
     */
    public function targetRates(): HasMany
    {
        return $this->hasMany(ExchangeRate::class, 'target_currency', 'code');
    }

    /**
     * Get all exchange rates for this currency
     */
    public function exchangeRates(): HasMany
    {
        return $this->baseRates()->orWhere('target_currency', $this->code);
    }

    /**
     * Scope for active currencies
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get formatted name with symbol
     */
    public function getFormattedNameAttribute(): string
    {
        if ($this->symbol) {
            return "{$this->name} ({$this->symbol})";
        }
        return $this->name;
    }
}
