<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShoppingListItem extends Model
{
    protected $fillable = [
        'shopping_list_id',
        'ingredient_id',
        'quantity_needed',
        'unit_id',
        'estimated_price_per_unit',
        'total_estimated_cost',
        'supplier_id',
        'notes',
    ];

    protected $casts = [
        'quantity_needed' => 'decimal:4',
        'estimated_price_per_unit' => 'decimal:2',
        'total_estimated_cost' => 'decimal:2',
    ];

    public function shoppingList(): BelongsTo
    {
        return $this->belongsTo(ShoppingList::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
