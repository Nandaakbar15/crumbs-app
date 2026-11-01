<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionBatch extends Model
{
    protected $fillable = [
        'batch_code',
        'recipe_id',
        'batch_number',
        'produced_quantity',
        'produced_unit_id',
        'production_date',
        'production_notes',
        'status',
        'completed_at',
        'quantity_sold',
        'revenue',
        'recorded_date',
    ];

    protected $casts = [
        'batch_number' => 'integer',
        'produced_quantity' => 'decimal:4',
        'production_date' => 'date',
        'completed_at' => 'datetime',
        'status' => 'string',
        'quantity_sold' => 'decimal:4',
        'revenue' => 'decimal:2',
        'recorded_date' => 'datetime',
    ];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function producedUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'produced_unit_id');
    }

    public function ingredientLogs(): HasMany
    {
        return $this->hasMany(BatchIngredientLog::class);
    }

    public function wasteLogs(): HasMany
    {
        return $this->hasMany(WasteLog::class);
    }

    public function shoppingList(): HasMany
    {
        return $this->hasMany(ShoppingList::class);
    }

    public function hppCalculations(): HasMany
    {
        return $this->hasMany(HPPCalculation::class);
    }

    /**
     * Generate batch code
     */
    public static function generateBatchCode(): string
    {
        $date = date('Ymd');
        $latestBatch = self::where('batch_code', 'like', 'BATCH' . $date . '%')->latest()->first();
        $sequence = $latestBatch ? intval(substr($latestBatch->batch_code, -3)) + 1 : 1;
        return 'BATCH' . $date . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get total material cost for this batch
     */
    public function getTotalMaterialCost(): float
    {
        $totalCost = 0;
        foreach ($this->ingredientLogs as $log) {
            $latestPrice = $log->ingredient->getLatestPrice();
            if ($latestPrice) {
                $totalCost += ($log->quantity_used * $latestPrice->price_per_unit);
            }
        }
        return $totalCost;
    }

    /**
     * Get total waste cost for this batch
     */
    public function getTotalWasteCost(): float
    {
        $totalCost = 0;
        foreach ($this->wasteLogs as $waste) {
            if ($waste->ingredient) {
                $latestPrice = $waste->ingredient->getLatestPrice();
                if ($latestPrice) {
                    $totalCost += ($waste->quantity * $latestPrice->price_per_unit);
                }
            }
        }
        return $totalCost;
    }

    /**
     * Record sale for this batch
     */
    public function recordSale(float $quantitySold, ?string $recordedDate = null): void
    {
        $this->quantity_sold = $quantitySold;
        $this->revenue = $this->calculateRevenue($quantitySold);
        $this->recorded_date = $recordedDate ? Carbon::parse($recordedDate) : now();
        $this->save();
    }

    /**
     * Calculate revenue for this batch
     */
    public function calculateRevenue(float $quantitySold): float
    {
        return $quantitySold * $this->recipe->selling_price;
    }

    /**
     * Calculate total HPP for this batch (based on produced quantity)
     */
    public function calculateTotalHPP(): float
    {
        $recipe = $this->recipe;
        $hppPerUnit = $recipe->getLatestHPP()->hpp_per_unit;
        return $this->produced_quantity * $hppPerUnit;
    }

    /**
     * Calculate gross profit (revenue - total hpp)
     */
    public function calculateGrossProfit(): float
    {
        return $this->revenue - $this->calculateTotalHPP();
    }

    /**
     * Calculate profit margin percentage
     */
    public function calculateMargin(): float
    {
        if ($this->revenue <= 0) {
            return 0;
        }
        return ($this->calculateGrossProfit() / $this->revenue) * 100;
    }

    /**
     * Get the unsold quantity
     */
    public function getUnsoldQuantity(): float
    {
        return $this->produced_quantity - $this->quantity_sold;
    }
}
