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
            'price_per_unit' => 'required|numeric|min:0',
            'current_stock' => 'required|numeric|min:0',
            'minimum_threshold' => 'required|numeric|min:0',
            'supplier_name' => 'nullable|string|max:255',
        ]);

        // Generate unique code from name
        $code = $this->generateUniqueCode($validated['name']);

        $ingredient = Ingredient::create([
            'code' => $code,
            'name' => $validated['name'],
            'base_unit_id' => $validated['base_unit_id'],
            'current_unit_id' => $validated['current_unit_id'],
            'current_stock' => $validated['current_stock'],
            'minimum_threshold' => $validated['minimum_threshold'],
            'is_active' => true,
        ]);

        // Create price record with unit_id
        $ingredient->prices()->create([
            'price_per_unit' => $validated['price_per_unit'],
            'unit_id' => $validated['current_unit_id'],
            'supplier_name' => $validated['supplier_name'] ?? null,
            'purchase_date' => now()->toDateString(),
            'quantity_purchased' => $validated['current_stock'],
            'total_cost' => $validated['price_per_unit'] * $validated['current_stock'],
            'is_active' => true,
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
            'id' => $ingredient->id,
            'name' => $ingredient->name,
            'base_unit_id' => $ingredient->base_unit_id,
            'current_unit_id' => $ingredient->current_unit_id,
            'current_stock' => $ingredient->current_stock,
            'minimum_threshold' => $ingredient->minimum_threshold,
            'price_per_unit' => $latestPrice?->price_per_unit ?? 0,
            'supplier_name' => $latestPrice?->supplier_name ?? '',
            'base_unit_code' => $ingredient->baseUnit?->code ?? 'g',
            'current_unit_code' => $ingredient->currentUnit?->code ?? 'g',
        ]);
    }

    public function update(Ingredient $ingredient)
    {
        $validated = request()->validate([
            'name' => 'required|string|max:255',
            'base_unit_id' => 'required|exists:units,id',
            'current_unit_id' => 'required|exists:units,id',
            'current_stock' => 'required|numeric|min:0',
            'minimum_threshold' => 'required|numeric|min:0',
            'price_per_unit' => 'required|numeric|min:0',
            'supplier_name' => 'nullable|string|max:255',
        ]);

        $ingredient->update([
            'name' => $validated['name'],
            'base_unit_id' => $validated['base_unit_id'],
            'current_unit_id' => $validated['current_unit_id'],
            'current_stock' => $validated['current_stock'],
            'minimum_threshold' => $validated['minimum_threshold'],
        ]);

        // Update or create price record
        $ingredient->prices()->updateOrCreate(
            ['is_active' => true],
            [
                'price_per_unit' => $validated['price_per_unit'],
                'unit_id' => $validated['current_unit_id'],
                'supplier_name' => $validated['supplier_name'] ?? null,
                'purchase_date' => now()->toDateString(),
                'quantity_purchased' => $validated['current_stock'],
                'total_cost' => $validated['price_per_unit'] * $validated['current_stock'],
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
            'supplier_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validated['type'] === 'in') {
            $ingredient->increment('current_stock', $validated['quantity']);

            // Create a new price record if price is provided
            if (!empty($validated['price_per_unit'])) {
                // Deactivate old active price
                $ingredient->prices()->where('is_active', true)->update(['is_active' => false]);

                $ingredient->prices()->create([
                    'price_per_unit' => $validated['price_per_unit'],
                    'unit_id' => $validated['unit_id'],
                    'supplier_name' => $validated['supplier_name'] ?? null,
                    'purchase_date' => now()->toDateString(),
                    'quantity_purchased' => $validated['quantity'],
                    'total_cost' => $validated['price_per_unit'] * $validated['quantity'],
                    'notes' => $validated['notes'] ?? null,
                    'is_active' => true,
                ]);
            }
        } else {
            // Stok keluar — kurangi stok, minimal 0
            $newStock = max(0, $ingredient->current_stock - $validated['quantity']);
            $ingredient->update(['current_stock' => $newStock]);
        }

        return redirect()->route('ingredients.index')
            ->with('success', $validated['type'] === 'in'
                ? "Restock {$ingredient->name} berhasil dicatat."
                : "Stok keluar {$ingredient->name} berhasil dicatat.");
    }
}
