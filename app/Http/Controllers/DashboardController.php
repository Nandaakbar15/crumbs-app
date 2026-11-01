<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\ProductionBatch;
use App\Models\WasteLog;
use App\Models\Product;
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
            ->get()
            ->filter(fn($ingredient) => $ingredient->isCritical())
            ->take(5);

        // Get low stocks
        $lowStocks = Ingredient::where('is_active', true)
            ->get()
            ->filter(fn($ingredient) => $ingredient->isLow() && !$ingredient->isCritical())
            ->take(5);

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

        return view('dashboard.index', [
            'activeBatches' => $activeBatches,
            'activeBatchesList' => $activeBatchesList,
            'criticalStocks' => $criticalStocks,
            'lowStocks' => $lowStocks,
            'totalMaterialCost' => $totalMaterialCost,
            'totalWasteAmount' => $totalWasteAmount,
            'wasteQuantity' => $wasteQuantity,
            'topMarginProducts' => $topMarginProducts,
        ]);
    }
}
