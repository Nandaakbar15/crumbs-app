<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WasteLog;
use App\Models\Ingredient;
use App\Models\Unit;
use App\Models\ProductionBatch;
use Carbon\Carbon;

class WasteLossController extends Controller
{
    public function index()
    {
        // Get waste logs grouped by type for current month
        $currentMonth = Carbon::now()->startOfMonth();
        
        // Waste logs (pembuangan/sampah)
        $wasteLogs = WasteLog::where('waste_type', 'waste')
            ->where('recorded_date', '>=', $currentMonth)
            ->with(['ingredient', 'unit', 'productionBatch'])
            ->orderBy('recorded_date', 'desc')
            ->get();

        // Loss logs (kerugian/rusak)
        $lossLogs = WasteLog::where('waste_type', 'loss')
            ->where('recorded_date', '>=', $currentMonth)
            ->with(['ingredient', 'unit', 'productionBatch'])
            ->orderBy('recorded_date', 'desc')
            ->get();

        // Calculate totals by reason
        $wasteByReason = $wasteLogs->groupBy('reason')->map(function ($items) {
            return [
                'reason' => $items->first()->reason,
                'total_cost' => $items->sum(fn($item) => $item->getWasteCost()),
                'count' => $items->count(),
            ];
        });

        $lossByReason = $lossLogs->groupBy('reason')->map(function ($items) {
            return [
                'reason' => $items->first()->reason,
                'total_cost' => $items->sum(fn($item) => $item->getWasteCost()),
                'count' => $items->count(),
            ];
        });

        // Supporting data
        $ingredients = Ingredient::where('is_active', true)->get();
        $units = Unit::all();
        $productionBatches = ProductionBatch::with('recipe')->where('status', 'in_progress')->get();

        return view('dashboard.waste_loss.index', [
            'wasteLogs' => $wasteLogs,
            'lossLogs' => $lossLogs,
            'wasteByReason' => $wasteByReason,
            'lossByReason' => $lossByReason,
            'ingredients' => $ingredients,
            'units' => $units,
            'productionBatches' => $productionBatches,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ingredient_id' => 'required|exists:ingredients,id',
            'quantity' => 'required|numeric|min:0.01',
            'unit_id' => 'required|exists:units,id',
            'waste_type' => 'required|in:waste,loss',
            'reason' => 'required|string|max:255',
            'recorded_date' => 'required|date',
            'production_batch_id' => 'nullable|exists:production_batches,id',
        ]);

        WasteLog::create([
            'ingredient_id' => $validated['ingredient_id'],
            'production_batch_id' => $validated['production_batch_id'],
            'waste_type' => $validated['waste_type'],
            'quantity' => $validated['quantity'],
            'unit_id' => $validated['unit_id'],
            'reason' => $validated['reason'],
            'recorded_date' => $validated['recorded_date'],
            'recorded_by' => auth()->id(),
        ]);

        return redirect()->route('waste-loss.index')
            ->with('success', 'Waste/Loss berhasil dicatat');
    }
}
