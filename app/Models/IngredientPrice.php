<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IngredientPrice extends Model
{
    protected $fillable = [
        'ingredient_id',
        'supplier_id',
        'price_per_unit',
        'unit_id',
        'purchase_date',
        'quantity_purchased',
        'total_cost',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'price_per_unit' => 'decimal:2',
        'quantity_purchased' => 'decimal:4',
        'total_cost' => 'decimal:2',
        'purchase_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
