<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipe extends Model
{
    protected $fillable = [
        'code',
        'name',
        'category',
        'description',
        'yield_quantity',
        'yield_unit_id',
        'selling_price',
        'overhead_per_batch',
        'image_url',
        'estimated_production_time',
        'difficulty_level',
        'is_active',
    ];

    protected $casts = [
        'yield_quantity' => 'decimal:4',
        'selling_price' => 'decimal:2',
        'overhead_per_batch' => 'decimal:2',
        'difficulty_level' => 'string',
        'is_active' => 'boolean',
    ];

    public function yieldUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'yield_unit_id');
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    public function productionBatches(): HasMany
    {
        return $this->hasMany(ProductionBatch::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Calculate total material cost for this recipe
     */
    public function getTotalMaterialCost(): float
    {
        $totalCost = 0;

        foreach ($this->ingredients as $recipeIngredient) {
            $latestPrice = $recipeIngredient->ingredient->getLatestPrice();
            if ($latestPrice) {
                $totalCost += ($recipeIngredient->quantity * $latestPrice->price_per_unit);
            }
        }

        return $totalCost;
    }

    /**
     * Get latest HPP calculation
     */
    public function getLatestHPP()
    {
        $totalCost = $this->getTotalMaterialCost() + ($this->overhead_per_batch ?? 0);
        $hppPerUnit = $this->yield_quantity > 0 ? $totalCost / $this->yield_quantity : 0;
        $margin = $this->selling_price > 0 ? (($this->selling_price - $hppPerUnit) / $this->selling_price * 100) : 0;

        return (object) [
            'hpp_per_batch' => $totalCost,
            'hpp_per_unit' => $hppPerUnit,
            'margin_percentage' => $margin,
        ];
    }

    /**
     * Calculate total material cost for this recipe (legacy method name)
     */
    public function calculateTotalMaterialCost(): float
    {
        return $this->getTotalMaterialCost();
    }
}

