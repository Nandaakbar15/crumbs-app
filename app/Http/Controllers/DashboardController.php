<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\ProductionBatch;
use App\Models\WasteLog;
use App\Models\Product;
use App\Models\Recipe;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = now()->toDateString();

        // Get active batches (untuk hari ini)
        $activeBatchesToday = ProductionBatch::where('status', 'in_progress')
            ->where('production_date', $today)
            ->get();

        // Get recent batches (untuk display batch terakhir)
        $activeBatches = ProductionBatch::orderBy('production_date', 'desc')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        $activeBatchesList = $activeBatchesToday->take(5);

        // Get critical stocks
        $criticalStocks = Ingredient::where('is_active', true)
            ->with(['baseUnit', 'currentUnit'])
            ->get()
            ->filter(fn($ingredient) => $ingredient->isCritical())
            ->take(5);

        // Get low stocks
        $lowStocks = Ingredient::where('is_active', true)
            ->with(['baseUnit', 'currentUnit'])
            ->get()
            ->filter(fn($ingredient) => $ingredient->isLow() && !$ingredient->isCritical())
            ->take(5);

        // Get safe stocks
        $safeStocks = Ingredient::where('is_active', true)
            ->with(['baseUnit', 'currentUnit'])
            ->get()
            ->filter(fn($ingredient) => !$ingredient->isLow())
            ->take(5);

        // Total ingredient counts by status
        $allIngredients = Ingredient::where('is_active', true)->with(['baseUnit', 'currentUnit', 'prices'])->get();
        $kritisCount  = $allIngredients->filter(fn($i) => $i->isCritical())->count();
        $menipisCount = $allIngredients->filter(fn($i) => $i->isLow() && !$i->isCritical())->count();

        // Calculate totals
        $totalMaterialCost = $activeBatchesToday->sum(fn($batch) => $batch->getTotalMaterialCost());
        
        // Waste calculation
        $totalWasteAmount = WasteLog::whereDate('recorded_date', $today)
            ->get()
            ->sum(fn($waste) => $waste->getWasteCost());
        
        $wasteQuantity = WasteLog::whereDate('recorded_date', $today)->sum('quantity');

        // Get top margin products
        $topMarginProducts = Product::with('hppCalculations')
            ->where('is_active', true)
            ->orderBy('selling_price', 'desc')
            ->take(3)
            ->get();

        // Get recipes with HPP data for chart
        $hppChartData = Recipe::with(['ingredients.ingredient.prices', 'yieldUnit'])
            ->where('is_active', true)
            ->get()
            ->map(function ($recipe) {
                $hpp = $recipe->getLatestHPP();
                return [
                    'name'          => $recipe->name,
                    'hpp_per_unit'  => round((float) $hpp->hpp_per_unit, 2),
                    'selling_price' => round((float) $recipe->selling_price, 2),
                    'margin'        => round((float) $hpp->margin_percentage, 1),
                ];
            })
            ->filter(fn($r) => $r['selling_price'] > 0)
            ->values();

        return view('dashboard.index', [
            'activeBatches' => $activeBatches,
            'activeBatchesList' => $activeBatchesList,
            'criticalStocks' => $criticalStocks,
            'lowStocks' => $lowStocks,
            'safeStocks' => $safeStocks,
            'kritisCount' => $kritisCount,
            'menipisCount' => $menipisCount,
            'totalMaterialCost' => $totalMaterialCost,
            'totalWasteAmount' => $totalWasteAmount,
            'wasteQuantity' => $wasteQuantity,
            'topMarginProducts' => $topMarginProducts,
            'hppChartData' => $hppChartData,
        ]);
    }
}
