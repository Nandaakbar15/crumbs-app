<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HPPCalculation extends Model
{
    protected $table = 'hpp_calculations';

    protected $fillable = [
        'production_batch_id',
        'product_id',
        'total_material_cost',
        'quantity_produced',
        'unit_id',
        'hpp_per_unit',
        'selling_price',
        'gross_margin',
        'margin_percentage',
        'notes',
        'calculated_date',
    ];

    protected $casts = [
        'total_material_cost' => 'decimal:2',
        'quantity_produced' => 'decimal:4',
        'hpp_per_unit' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'gross_margin' => 'decimal:2',
        'margin_percentage' => 'decimal:2',
        'calculated_date' => 'date',
    ];

    public function productionBatch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
