<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\Ingredient;
use App\Models\Unit;

class ReceipeHppController extends Controller
{
    public function index()
    {
        $recipes = Recipe::with('ingredients.ingredient', 'yieldUnit')
            ->where('is_active', true)
            ->get();
        
        $units = Unit::all();
        $ingredients = Ingredient::where('is_active', true)->orderBy('name')->get();

        return view('dashboard.receipehpp.index', [
            'recipes' => $recipes,
            'units' => $units,
            'ingredients' => $ingredients,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string',
            'yield_quantity' => 'required|numeric|min:0.01',
            'yield_unit_id' => 'required|exists:units,id',
            'selling_price' => 'required|numeric|min:0',
            'overhead_per_batch' => 'nullable|numeric|min:0',
            'image_url' => 'nullable|string',
            'ingredients' => 'nullable|array',
        ]);

        // Generate unique code from recipe name
        $code = $this->generateUniqueCode($validated['name']);

        $recipe = Recipe::create([
            'code' => $code,
            'name' => $validated['name'],
            'yield_quantity' => $validated['yield_quantity'],
            'yield_unit_id' => $validated['yield_unit_id'],
            'selling_price' => $validated['selling_price'],
            'overhead_per_batch' => $validated['overhead_per_batch'] ?? 0,
            'image_url' => $validated['image_url'],
            'is_active' => true,
        ]);

        return redirect()->route('recipes.index')
            ->with('success', 'Resep berhasil ditambahkan');
    }

    /**
     * Generate unique code from recipe name
     */
    private function generateUniqueCode($name): string
    {
        // Remove spaces and take first 3 characters
        $baseCode = strtoupper(substr(str_replace(' ', '', $name), 0, 3));
        
        $code = $baseCode;
        $counter = 1;
        
        // If code already exists, append number
        while (Recipe::where('code', $code)->exists()) {
            $code = $baseCode . $counter;
            $counter++;
        }
        
        return $code;
    }

    /**
     * Show recipe data for edit modal (JSON response)
     */
    public function show(Recipe $recipe)
    {
        $recipe->load('ingredients.ingredient', 'yieldUnit');

        return response()->json([
            'id' => $recipe->id,
            'name' => $recipe->name,
            'category' => $recipe->category,
            'yield_quantity' => $recipe->yield_quantity,
            'yield_unit_id' => $recipe->yield_unit_id,
            'selling_price' => $recipe->selling_price,
            'overhead_per_batch' => $recipe->overhead_per_batch,
            'image_url' => $recipe->image_url,
            'ingredients' => $recipe->ingredients->map(function ($item) {
                return [
                    'id' => $item->id,
                    'ingredient_id' => $item->ingredient_id,
                    'ingredient_name' => $item->ingredient->name,
                    'unit_id' => $item->unit_id,
                    'quantity' => $item->quantity,
                ];
            })->toArray(),
        ]);
    }

    public function update(Recipe $recipe, Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string',
            'yield_quantity' => 'required|numeric|min:0.01',
            'yield_unit_id' => 'required|exists:units,id',
            'selling_price' => 'required|numeric|min:0',
            'overhead_per_batch' => 'nullable|numeric|min:0',
            'image_url' => 'nullable|string',
            'ingredients' => 'nullable|array',
        ]);

        $recipe->update([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'yield_quantity' => $validated['yield_quantity'],
            'yield_unit_id' => $validated['yield_unit_id'],
            'selling_price' => $validated['selling_price'],
            'overhead_per_batch' => $validated['overhead_per_batch'] ?? 0,
            'image_url' => $validated['image_url'],
        ]);

        return redirect()->route('recipes.index')
            ->with('success', 'Resep berhasil diperbarui');
    }

    public function delete(Recipe $recipe)
    {
        $recipe->update(['is_active' => false]);

        return redirect()->route('recipes.index')
               ->with('success', 'Resep berhasil dihapus!');
    }
}
