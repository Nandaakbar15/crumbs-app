<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WasteLog extends Model
{
    protected $fillable = [
        'ingredient_id',
        'production_batch_id',
        'waste_type',
        'quantity',
        'unit_id',
        'reason',
        'recorded_date',
        'recorded_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'waste_type' => 'string',
        'recorded_date' => 'date',
    ];

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function productionBatch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get waste cost based on latest ingredient price
     */
    public function getWasteCost(): float
    {
        if ($this->ingredient) {
            $latestPrice = $this->ingredient->getLatestPrice();
            if ($latestPrice) {
                return $this->quantity * $latestPrice->price_per_unit;
            }
        }
        return 0;
    }
}
