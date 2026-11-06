<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\Unit;
use App\Models\Supplier;
use Illuminate\View\View;

class IngredientController extends Controller
{
    public function index(): View
    {
        $ingredients = Ingredient::where('is_active', true)
            ->with(['baseUnit', 'currentUnit', 'prices'])
            ->get();

        $units = Unit::all();
        $suppliers = Supplier::all();

        return view('dashboard.ingredients.index', [
            'ingredients' => $ingredients,
            'units' => $units,
            'suppliers' => $suppliers,
        ]);
    }

    public function store()
    {
        $validated = request()->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string',
            'base_unit_id' => 'required|exists:units,id',
            'current_unit_id' => 'required|exists:units,id',
            'purchase_quantity' => 'nullable|numeric|min:1',
            'price_per_unit' => 'required|numeric|min:0',
            'current_stock' => 'required|numeric|min:0',
            'minimum_threshold' => 'required|numeric|min:0',
            'store_name' => 'nullable|string|max:255',
        ]);

        $baseUnit  = Unit::findOrFail($validated['base_unit_id']);
        $inputUnit = Unit::findOrFail($validated['current_unit_id']);

        // purchase_quantity: jumlah per pembelian (misal 250 gram per Rp13.500)
        // Normalize price to per-base-unit price
        $purchaseQty = (float) ($validated['purchase_quantity'] ?? 1);
        $pricePerBase = $purchaseQty > 0
            ? (float) $validated['price_per_unit'] / $purchaseQty
            : (float) $validated['price_per_unit'];

        // Convert stock & threshold to the ingredient's base unit
        $stockInBase     = $inputUnit->toBaseUnit((float) $validated['current_stock']);
        $thresholdInBase = $inputUnit->toBaseUnit((float) $validated['minimum_threshold']);

        // Generate unique code from name
        $code = $this->generateUniqueCode($validated['name']);

        $ingredient = Ingredient::create([
            'code'               => $code,
            'name'               => $validated['name'],
            'category'           => $validated['category'] ?? null,
            'base_unit_id'       => $validated['base_unit_id'],
            'current_unit_id'    => $validated['base_unit_id'], // always store in base unit
            'current_stock'      => $stockInBase,
            'minimum_threshold'  => $thresholdInBase,
            'is_active'          => true,
        ]);

        // Create price record — store normalized price per base unit + original input values
        $ingredient->prices()->create([
            'price_per_unit'       => $pricePerBase,
            'purchase_price_input' => (float) $validated['price_per_unit'],
            'purchase_qty_input'   => $purchaseQty,
            'unit_id'              => $validated['base_unit_id'],
            'supplier_name'        => $validated['supplier_name'] ?? null,
            'store_name'           => $validated['store_name'] ?? null,
            'purchase_date'        => now()->toDateString(),
            'quantity_purchased'   => $validated['current_stock'],
            'total_cost'           => $pricePerBase * $stockInBase,
            'is_active'            => true,
        ]);

        return redirect()->route('ingredients.index')
            ->with('success', 'Bahan berhasil ditambahkan');
    }

    /**
     * Generate unique code from ingredient name
     */
    private function generateUniqueCode($name): string
    {
        // Remove spaces and take first 3 characters
        $baseCode = strtoupper(substr(str_replace(' ', '', $name), 0, 3));
        
        $code = $baseCode;
        $counter = 1;
        
        // If code already exists, append number
        while (Ingredient::where('code', $code)->exists()) {
            $code = $baseCode . $counter;
            $counter++;
        }
        
        return $code;
    }

    /**
     * Show ingredient data for edit modal (JSON response)
     */
    public function show(Ingredient $ingredient)
    {
        $ingredient->load('baseUnit', 'currentUnit', 'prices');
        
        $latestPrice = $ingredient->getLatestPrice();

        return response()->json([
            'id'               => $ingredient->id,
            'name'             => $ingredient->name,
            'category'         => $ingredient->category ?? '',
            'base_unit_id'     => $ingredient->base_unit_id,
            'current_unit_id'  => $ingredient->current_unit_id,
            'current_stock'    => $ingredient->current_stock,
            'minimum_threshold'=> $ingredient->minimum_threshold,
            // Return the original input values so the form shows what the user typed
            'price_per_unit'   => $latestPrice?->purchase_price_input ?? $latestPrice?->price_per_unit ?? 0,
            'purchase_quantity'=> $latestPrice?->purchase_qty_input ?? 1,
            'supplier_name'    => $latestPrice?->supplier_name ?? '',
            'store_name'       => $latestPrice?->store_name ?? '',
            'base_unit_code'   => $ingredient->baseUnit?->code ?? 'g',
            'current_unit_code'=> $ingredient->currentUnit?->code ?? 'g',
        ]);
    }

    public function update(Ingredient $ingredient)
    {
        $validated = request()->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string',
            'base_unit_id' => 'required|exists:units,id',
            'current_unit_id' => 'required|exists:units,id',
            'purchase_quantity' => 'nullable|numeric|min:1',
            'current_stock' => 'required|numeric|min:0',
            'minimum_threshold' => 'required|numeric|min:0',
            'price_per_unit' => 'required|numeric|min:0',
            'store_name' => 'nullable|string|max:255',
        ]);

        $inputUnit = Unit::findOrFail($validated['current_unit_id']);

        // Normalize price to per-base-unit
        $purchaseQty  = (float) ($validated['purchase_quantity'] ?? 1);
        $pricePerBase = $purchaseQty > 0
            ? (float) $validated['price_per_unit'] / $purchaseQty
            : (float) $validated['price_per_unit'];

        // Convert to base unit before saving
        $stockInBase     = $inputUnit->toBaseUnit((float) $validated['current_stock']);
        $thresholdInBase = $inputUnit->toBaseUnit((float) $validated['minimum_threshold']);

        $ingredient->update([
            'name'              => $validated['name'],
            'category'          => $validated['category'] ?? null,
            'base_unit_id'      => $validated['base_unit_id'],
            'current_unit_id'   => $validated['base_unit_id'], // always base
            'current_stock'     => $stockInBase,
            'minimum_threshold' => $thresholdInBase,
        ]);

        // Update or create price record with normalized per-base-unit price + original input values
        $ingredient->prices()->updateOrCreate(
            ['is_active' => true],
            [
                'price_per_unit'       => $pricePerBase,
                'purchase_price_input' => (float) $validated['price_per_unit'],
                'purchase_qty_input'   => $purchaseQty,
                'unit_id'              => $validated['base_unit_id'],
                'supplier_name'        => $validated['supplier_name'] ?? null,
                'store_name'           => $validated['store_name'] ?? null,
                'purchase_date'        => now()->toDateString(),
                'quantity_purchased'   => $validated['current_stock'],
                'total_cost'           => $pricePerBase * $stockInBase,
            ]
        );

        return redirect()->route('ingredients.index')
            ->with('success', 'Bahan berhasil diperbarui');
    }

    public function destroy(Ingredient $ingredient)
    {
        $ingredient->update(['is_active' => false]);

        return redirect()->route('ingredients.index')
            ->with('success', 'Bahan berhasil dihapus');
    }

    public function history(Ingredient $ingredient)
    {
        $ingredient->load(['currentUnit', 'prices.unit']);

        $prices = $ingredient->prices()
            ->with('unit')
            ->orderByDesc('created_at')
            ->get();

        // Saldo awal = stok pertama kali ditambahkan (dari price record pertama)
        $entries = [];
        $runningBalance = 0;

        // Build chronological entries from price records
        foreach ($prices->reverse()->values() as $price) {
            $qty    = (float) $price->quantity_purchased;
            $isIn   = $price->is_active || $price->quantity_purchased > 0;
            $runningBalance += $qty;

            $entries[] = [
                'date'          => $price->created_at->format('d M, H:i'),
                'type'          => 'in',
                'quantity'      => $qty,
                'unit'          => $price->unit?->code ?? $ingredient->currentUnit?->code ?? 'unit',
                'balance'       => $runningBalance,
                'price'         => (float) $price->price_per_unit,
                'supplier_name' => $price->supplier_name ?? '-',
                'notes'         => $price->notes ?? '',
            ];
        }

        // Reverse so newest is first
        $entries = array_reverse($entries);

        $totalIn  = collect($entries)->where('type', 'in')->sum('quantity');
        $totalOut = 0; // future: track waste/batch logs

        return response()->json([
            'id'           => $ingredient->id,
            'name'         => $ingredient->name,
            'current_stock'=> (float) $ingredient->current_stock,
            'unit'         => $ingredient->currentUnit?->code ?? 'unit',
            'total_in'     => $totalIn,
            'total_out'    => $totalOut,
            'entries'      => $entries,
        ]);
    }

    public function restock(Ingredient $ingredient)
    {
        $validated = request()->validate([
            'type' => 'required|in:in,out',
            'quantity' => 'required|numeric|min:0.01',
            'unit_id' => 'required|exists:units,id',
            'price_per_unit' => 'nullable|numeric|min:0',
            'store_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        $inputUnit = Unit::findOrFail($validated['unit_id']);

        // Always work in base unit
        $quantityInBase = $inputUnit->toBaseUnit((float) $validated['quantity']);

        if ($validated['type'] === 'in') {
            $ingredient->increment('current_stock', $quantityInBase);

            if (!empty($validated['price_per_unit'])) {
                $ingredient->prices()->where('is_active', true)->update(['is_active' => false]);

                $ingredient->prices()->create([
                    'price_per_unit'     => $validated['price_per_unit'],
                    'unit_id'            => $validated['unit_id'],
                    'supplier_name'      => $validated['supplier_name'] ?? null,
                    'store_name'         => $validated['store_name'] ?? null,
                    'purchase_date'      => now()->toDateString(),
                    'quantity_purchased' => $validated['quantity'], // original input qty
                    'total_cost'         => $validated['price_per_unit'] * $validated['quantity'],
                    'notes'              => $validated['notes'] ?? null,
                    'is_active'          => true,
                ]);
            }
        } else {
            $newStock = max(0, $ingredient->current_stock - $quantityInBase);
            $ingredient->update(['current_stock' => $newStock]);
        }

        $baseCode = $ingredient->baseUnit?->code ?? 'unit';

        return redirect()->route('ingredients.index')
            ->with('success', $validated['type'] === 'in'
                ? "Restock {$ingredient->name}: +{$quantityInBase} {$baseCode}."
                : "Stok keluar {$ingredient->name}: -{$quantityInBase} {$baseCode}.");
    }
}
