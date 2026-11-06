<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
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

    public function storeFromCritical()
    {
        // Ambil semua bahan aktif yang kritis atau menipis
        $criticalIngredients = Ingredient::where('is_active', true)
            ->with([
                'baseUnit',
                'prices' => fn($q) => $q->where('is_active', true)->latest(),
                'recipeIngredients.recipe',
            ])
            ->get()
            ->filter(fn($i) => $i->isCritical());

        if ($criticalIngredients->isEmpty()) {
            return redirect()->route('shopping-lists.index')
                ->with('info', 'Tidak ada bahan kritis saat ini.');
        }

        // Kumpulkan nama-nama resep yang menggunakan bahan-bahan ini
        $usedInRecipes = $criticalIngredients
            ->flatMap(fn($i) => $i->recipeIngredients->pluck('recipe.name'))
            ->filter()
            ->unique()
            ->values()
            ->implode(', ');

        $code = ShoppingList::generateShoppingListCode();

        $shoppingList = ShoppingList::create([
            'shopping_list_code'   => $code,
            'production_batch_id'  => null,
            'total_estimated_cost' => 0,
            'status'               => 'draft',
        ]);

        $totalCost = 0;

        foreach ($criticalIngredients as $ingredient) {
            $latestPrice = $ingredient->prices->first();

            // Satuan & harga beli asli (misal: 1000 gram / Rp 18.000)
            $purchaseQty   = (float) ($latestPrice?->purchase_qty_input   ?? 1);   // 1000 gram
            $purchasePrice = (float) ($latestPrice?->purchase_price_input ?? $latestPrice?->price_per_unit ?? 0); // Rp 18.000

            // Jumlah yang dibutuhkan (deficit)
            $deficit   = max(0, (float) $ingredient->minimum_threshold - (float) $ingredient->current_stock);
            $qtyNeeded = $deficit > 0 ? $deficit : (float) $ingredient->minimum_threshold;

            // Hitung berapa pack yang perlu dibeli (ceil — beli satuan penuh)
            // Contoh: butuh 50g, satuan beli 1000g → beli 1 pack (1000g)
            $packsNeeded   = $purchaseQty > 0 ? ceil($qtyNeeded / $purchaseQty) : 1;
            $qtyToBuy      = $packsNeeded * $purchaseQty;   // gram yang benar-benar dibeli
            $itemCost      = $packsNeeded * $purchasePrice; // total biaya pembelian

            $totalCost += $itemCost;

            // Kumpulkan nama resep untuk notes item ini
            $recipeNames = $ingredient->recipeIngredients
                ->pluck('recipe.name')
                ->filter()
                ->unique()
                ->implode(', ');

            $shoppingList->items()->create([
                'ingredient_id'            => $ingredient->id,
                'quantity_needed'          => $qtyToBuy,      // qty yang benar-benar dibeli (dalam gram)
                'unit_id'                  => $ingredient->base_unit_id,
                'estimated_price_per_unit' => $purchasePrice, // harga per pack
                'total_estimated_cost'     => $itemCost,
                'supplier_id'              => null,
                'notes'                    => collect(array_filter([
                    $recipeNames ? "Dipakai di: {$recipeNames}" : null,
                    $latestPrice?->store_name ? "Beli di: {$latestPrice->store_name}" : null,
                    "Beli {$packsNeeded}× {$purchaseQty} gram",
                ]))->implode(' · ') ?: null,
            ]);
        }

        $shoppingList->update(['total_estimated_cost' => $totalCost]);

        return redirect()->route('shopping-lists.index')
            ->with('success', "Daftar belanja stok kritis berhasil dibuat ({$criticalIngredients->count()} bahan)" .
                ($usedInRecipes ? " — Resep terkait: {$usedInRecipes}" : ''));
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

    public function destroy(ShoppingList $shoppingList)
    {
        $shoppingList->delete();

        return redirect()->route('shopping-lists.index')
            ->with('success', "Daftar belanja {$shoppingList->shopping_list_code} berhasil dihapus.");
    }
}
