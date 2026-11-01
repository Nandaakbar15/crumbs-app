<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'code',
        'name',
        'recipe_id',
        'selling_price',
        'quantity_per_unit',
        'unit_id',
        'description',
        'is_active',
    ];

    protected $casts = [
        'selling_price' => 'decimal:2',
        'quantity_per_unit' => 'integer',
        'is_active' => 'boolean',
    ];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function hppCalculations(): HasMany
    {
        return $this->hasMany(HPPCalculation::class);
    }

    /**
     * Get latest HPP calculation for this product
     */
    public function getLatestHPP()
    {
        return $this->hppCalculations()->latest('calculated_date')->first();
    }
}
