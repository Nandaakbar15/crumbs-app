<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    protected $fillable = [
        'code',
        'name',
        'category',
        'conversion_factor',
        'base_unit',
    ];

    protected $casts = [
        'category' => 'string',
        'conversion_factor' => 'decimal:4',
    ];

    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class, 'base_unit_id');
    }

    /**
     * Convert a quantity FROM this unit TO the ingredient's base unit.
     * e.g. 2 kg -> 2000 gram (factor=1000), 1 dozen -> 12 pcs (factor=12)
     */
    public function toBaseUnit(float $quantity): float
    {
        return $quantity * (float) $this->conversion_factor;
    }

    /**
     * Convert a quantity FROM the base unit TO this unit.
     * e.g. 2000 gram -> 2 kg
     */
    public function fromBaseUnit(float $quantity): float
    {
        $factor = (float) $this->conversion_factor;
        return $factor > 0 ? $quantity / $factor : $quantity;
    }

    /**
     * Whether this unit is the same "category family" as another unit.
     * Prevents converting kg to ml, for example.
     */
    public function isSameCategory(Unit $other): bool
    {
        return $this->category === $other->category;
    }

    public function recipeIngredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    public function ingredientPrices(): HasMany
    {
        return $this->hasMany(IngredientPrice::class);
    }

    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class, 'yield_unit_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function productionBatches(): HasMany
    {
        return $this->hasMany(ProductionBatch::class, 'produced_unit_id');
    }

    public function batchIngredientLogs(): HasMany
    {
        return $this->hasMany(BatchIngredientLog::class);
    }

    public function wasteLogs(): HasMany
    {
        return $this->hasMany(WasteLog::class);
    }

    public function hppCalculations(): HasMany
    {
        return $this->hasMany(HPPCalculation::class);
    }
}
