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
