<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShoppingList;
use App\Models\ProductionBatch;
use App\Models\Ingredient;
use App\Models\Unit;
use App\Models\Supplier;

class DaftarBelanjaController extends Controller
{
    public function index()
    {
        // Get all shopping lists with their items
        $shoppingLists = ShoppingList::with(['productionBatch.recipe', 'items.ingredient', 'items.unit', 'items.supplier'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get active batches for creating new shopping list
        $productionBatches = ProductionBatch::with('recipe', 'producedUnit')
            ->where('status', 'planned')
            ->orderBy('production_date', 'desc')
            ->get();

        $ingredients = Ingredient::where('is_active', true)->get();
        $units = Unit::all();
        $suppliers = Supplier::all();

        return view('dashboard.daftarbelanja.index', [
            'shoppingLists' => $shoppingLists,
            'productionBatches' => $productionBatches,
            'ingredients' => $ingredients,
            'units' => $units,
            'suppliers' => $suppliers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'production_batch_id' => 'required|exists:production_batches,id',
            'items' => 'required|array|min:1',
            'items.*.ingredient_id' => 'required|exists:ingredients,id',
            'items.*.quantity_needed' => 'required|numeric|min:0.01',
            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.estimated_price_per_unit' => 'required|numeric|min:0',
            'items.*.supplier_id' => 'nullable|exists:suppliers,id',
            'items.*.notes' => 'nullable|string',
        ]);

        // Create shopping list
        $shoppingList = ShoppingList::create([
            'shopping_list_code' => ShoppingList::generateShoppingListCode(),
            'production_batch_id' => $validated['production_batch_id'],
            'total_estimated_cost' => 0,
            'status' => 'draft',
        ]);

        // Create shopping list items
        $totalCost = 0;
        foreach ($validated['items'] as $item) {
            $itemTotalCost = $item['quantity_needed'] * $item['estimated_price_per_unit'];
            $totalCost += $itemTotalCost;

            $shoppingList->items()->create([
                'ingredient_id' => $item['ingredient_id'],
                'quantity_needed' => $item['quantity_needed'],
                'unit_id' => $item['unit_id'],
                'estimated_price_per_unit' => $item['estimated_price_per_unit'],
                'total_estimated_cost' => $itemTotalCost,
                'supplier_id' => $item['supplier_id'],
                'notes' => $item['notes'],
            ]);
        }

        // Update shopping list total cost
        $shoppingList->update(['total_estimated_cost' => $totalCost]);

        return redirect()->route('shopping-lists.index')
            ->with('success', 'Daftar belanja berhasil dibuat');
    }
}
