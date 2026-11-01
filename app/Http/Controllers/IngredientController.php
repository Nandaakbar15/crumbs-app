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
            'supplier_id' => 'nullable|exists:suppliers,id',
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
            'supplier_id' => $validated['supplier_id'],
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
            'supplier_id' => $latestPrice?->supplier_id,
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
            'supplier_id' => 'nullable|numeric',
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
                'supplier_id' => $validated['supplier_id'],
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
}
