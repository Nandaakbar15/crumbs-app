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
    public function index(Request $request)
    {
        $selectedBatchId = $request->query('batch_id');

        // Get shopping lists, filtered by batch if selected
        $query = ShoppingList::with(['productionBatch.recipe', 'items.ingredient', 'items.unit', 'items.supplier'])
            ->orderBy('created_at', 'desc');

        if ($selectedBatchId) {
            $query->where('production_batch_id', $selectedBatchId);
        }

        $shoppingLists = $query->get();

        // Get all batches that have a shopping list (for the filter dropdown)
        $batchesWithLists = ProductionBatch::with('recipe')
            ->whereHas('shoppingList')
            ->orderBy('production_date', 'desc')
            ->get();

        // Get active batches for creating new shopping list
        $productionBatches = ProductionBatch::with('recipe', 'producedUnit')
            ->orderBy('production_date', 'desc')
            ->get();

        $ingredients = Ingredient::where('is_active', true)->get();
        $units = Unit::all();
        $suppliers = Supplier::all();

        return view('dashboard.daftarbelanja.index', [
            'shoppingLists'     => $shoppingLists,
            'batchesWithLists'  => $batchesWithLists,
            'productionBatches' => $productionBatches,
            'selectedBatchId'   => $selectedBatchId,
            'ingredients'       => $ingredients,
            'units'             => $units,
            'suppliers'         => $suppliers,
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
                'supplier_id' => $item['supplier_id'] ?? null,
                'notes'       => $item['notes'] ?? null,
            ]);
        }

        // Update shopping list total cost
        $shoppingList->update(['total_estimated_cost' => $totalCost]);

        return redirect()->route('shopping-lists.index')
            ->with('success', 'Daftar belanja berhasil dibuat');
    }
}
