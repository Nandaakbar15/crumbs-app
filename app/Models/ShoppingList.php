<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShoppingList extends Model
{
    protected $fillable = [
        'shopping_list_code',
        'production_batch_id',
        'total_estimated_cost',
        'status',
        'purchased_date',
    ];

    protected $casts = [
        'total_estimated_cost' => 'decimal:2',
        'status' => 'string',
        'purchased_date' => 'date',
    ];

    public function productionBatch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShoppingListItem::class);
    }

    /**
     * Generate shopping list code
     */
    public static function generateShoppingListCode(): string
    {
        $date = date('Ymd');
        $latestList = self::where('shopping_list_code', 'like', 'SL' . $date . '%')->latest()->first();
        $sequence = $latestList ? intval(substr($latestList->shopping_list_code, -3)) + 1 : 1;
        return 'SL' . $date . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Recalculate total estimated cost
     */
    public function recalculateTotal(): void
    {
        $this->total_estimated_cost = $this->items->sum('total_estimated_cost');
        $this->save();
    }
}
