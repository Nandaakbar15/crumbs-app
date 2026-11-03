<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ingredient extends Model
{
    protected $fillable = [
        'code',
        'name',
        'category',
        'base_unit_id',
        'current_stock',
        'current_unit_id',
        'minimum_threshold',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'current_stock' => 'decimal:4',
        'minimum_threshold' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    public function currentUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'current_unit_id');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(IngredientPrice::class);
    }

    public function recipeIngredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    public function batchLogs(): HasMany
    {
        return $this->hasMany(BatchIngredientLog::class);
    }

    public function wasteLogs(): HasMany
    {
        return $this->hasMany(WasteLog::class);
    }

    public function shoppingListItems(): HasMany
    {
        return $this->hasMany(ShoppingListItem::class);
    }

    /**
     * Get the latest price for this ingredient
     */
    public function getLatestPrice()
    {
        return $this->prices()->where('is_active', true)->latest('created_at')->first();
    }

    /**
     * Check if ingredient stock is critical
     */
    public function isCritical(): bool
    {
        return $this->current_stock < $this->minimum_threshold;
    }

    /**
     * Check if ingredient stock is low
     */
    public function isLow(): bool
    {
        return $this->current_stock < ($this->minimum_threshold * 1.5);
    }

    /**
     * Format a stock value (in base unit) into a human-readable string.
     * Always displays in the base unit — no automatic promotion to kg/liter.
     * Returns ['value' => float, 'unit' => string]
     */
    public function formatStock(?float $baseQty = null): array
    {
        $qty      = $baseQty ?? (float) $this->current_stock;
        $baseCode = $this->baseUnit?->code ?? 'unit';

        return ['value' => $qty, 'unit' => $baseCode];
    }

    /**
     * Format the minimum_threshold the same way as formatStock()
     */
    public function formatThreshold(): array
    {
        return $this->formatStock((float) $this->minimum_threshold);
    }
}
