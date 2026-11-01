<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Recipe;
use App\Models\ProductionBatch;
use App\Models\RecipeIngredient;
use App\Models\Unit;

class ProductionBatchController extends Controller
{
    public function index()
    {
        $recipes = Recipe::where('is_active', true)->with('yieldUnit', 'ingredients.ingredient')->get();
        $units = Unit::all();
        $recentBatches = ProductionBatch::with('recipe', 'producedUnit')
            ->orderBy('production_date', 'desc')
            ->take(10)
            ->get();

        return view('dashboard.productionbatch.index', [
            'recipes' => $recipes,
            'units' => $units,
            'recentBatches' => $recentBatches,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipe_id' => 'required|exists:recipes,id',
            'batch_number' => 'required|integer|min:1',
            'quantity' => 'required|numeric|min:0.01',
            'production_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $recipe = Recipe::findOrFail($validated['recipe_id']);

        $batch = ProductionBatch::create([
            'batch_code' => ProductionBatch::generateBatchCode(),
            'recipe_id' => $validated['recipe_id'],
            'batch_number' => $validated['batch_number'],
            'produced_quantity' => $validated['quantity'],
            'produced_unit_id' => $recipe->yield_unit_id,
            'production_date' => $validated['production_date'],
            'production_notes' => $validated['notes'] ?? null,
            'status' => 'planned',
        ]);

        return redirect()->route('batches.index')
            ->with('success', 'Batch produksi berhasil dibuat');
    }

    /**
     * Record sales for a batch
     */
    public function recordSale(Request $request, ProductionBatch $batch)
    {
        $validated = $request->validate([
            'quantity_sold' => 'required|numeric|min:0',
            'recorded_date' => 'nullable|date',
        ]);

        $batch->recordSale(
            $validated['quantity_sold'],
            $validated['recorded_date'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Penjualan berhasil dicatat',
            'revenue' => $batch->revenue,
            'quantity_sold' => $batch->quantity_sold,
            'unsold' => $batch->getUnsoldQuantity(),
            'total_hpp' => $batch->calculateTotalHPP(),
            'gross_profit' => $batch->calculateGrossProfit(),
            'margin' => $batch->calculateMargin(),
        ]);
    }

    /**
     * Generate sales report
     */
    public function generateReport(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'metrics' => 'required|array|min:1',
            'metrics.*' => 'in:revenue,total_hpp,gross_profit,margin',
            'report_type' => 'required|in:weekly,monthly',
        ]);

        $startDate = Carbon::parse($validated['start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['end_date'])->endOfDay();

        $batches = ProductionBatch::with('recipe')
            ->whereBetween('recorded_date', [$startDate, $endDate])
            ->where('quantity_sold', '>', 0)
            ->get();

        $reportData = [];

        if ($validated['report_type'] === 'weekly') {
            $reportData = $this->generateWeeklyReport($batches, $startDate, $endDate);
        } elseif ($validated['report_type'] === 'monthly') {
            $reportData = $this->generateMonthlyReport($batches, $startDate, $endDate);
        }

        return response()->json([
            'success' => true,
            'report_type' => $validated['report_type'],
            'metrics' => $validated['metrics'],
            'data' => $reportData,
            'summary' => $this->calculateReportSummary($reportData, $validated['metrics']),
        ]);
    }

    /**
     * Generate weekly report (weeks start on Tuesday)
     */
    private function generateWeeklyReport($batches, $startDate, $endDate)
    {
        $weeks = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            // Calculate Tuesday of this week
            // If current day is before Tuesday (0=Sunday, 1=Monday, 2=Tuesday), go back to previous Tuesday
            // If current day is Tuesday or later, use this Tuesday
            $dayOfWeek = $current->dayOfWeek; // 0=Sunday, 1=Monday, 2=Tuesday
            
            if ($dayOfWeek < 2) {
                // Before Tuesday, go back to last Tuesday
                $tuesday = $current->copy()->subDays($dayOfWeek + 6);
            } else {
                // Tuesday or later, go back to this Tuesday
                $tuesday = $current->copy()->subDays($dayOfWeek - 2);
            }

            $weekEnd = $tuesday->copy()->addDays(6)->endOfDay(); // Monday end

            $weekBatches = $batches->filter(function ($batch) use ($tuesday, $weekEnd) {
                $recordDate = Carbon::parse($batch->recorded_date ?? now());
                return $recordDate >= $tuesday && $recordDate <= $weekEnd;
            });

            if ($weekBatches->count() > 0) {
                $weeks[] = [
                    'period' => $tuesday->format('d M Y') . ' - ' . $weekEnd->format('d M Y'),
                    'start_date' => $tuesday->format('Y-m-d'),
                    'end_date' => $weekEnd->format('Y-m-d'),
                    'batches' => $weekBatches->map(function ($batch) {
                        return [
                            'batch_code' => $batch->batch_code,
                            'recipe_name' => $batch->recipe->name,
                            'quantity_sold' => $batch->quantity_sold,
                            'revenue' => $batch->revenue,
                            'total_hpp' => $batch->calculateTotalHPP(),
                            'gross_profit' => $batch->calculateGrossProfit(),
                            'margin' => $batch->calculateMargin(),
                        ];
                    })->values(),
                ];
            }

            $current = $tuesday->copy()->addWeek();
        }

        return $weeks;
    }

    /**
     * Generate monthly report
     */
    private function generateMonthlyReport($batches, $startDate, $endDate)
    {
        $months = [];
        $current = $startDate->copy()->startOfMonth();

        while ($current <= $endDate) {
            $monthEnd = $current->copy()->endOfMonth();

            $monthBatches = $batches->filter(function ($batch) use ($current, $monthEnd) {
                $recordDate = Carbon::parse($batch->recorded_date ?? now());
                return $recordDate >= $current && $recordDate <= $monthEnd;
            });

            if ($monthBatches->count() > 0) {
                $months[] = [
                    'period' => $current->format('F Y'),
                    'start_date' => $current->format('Y-m-d'),
                    'end_date' => $monthEnd->format('Y-m-d'),
                    'batches' => $monthBatches->map(function ($batch) {
                        return [
                            'batch_code' => $batch->batch_code,
                            'recipe_name' => $batch->recipe->name,
                            'quantity_sold' => $batch->quantity_sold,
                            'revenue' => $batch->revenue,
                            'total_hpp' => $batch->calculateTotalHPP(),
                            'gross_profit' => $batch->calculateGrossProfit(),
                            'margin' => $batch->calculateMargin(),
                        ];
                    })->values(),
                ];
            }

            $current->addMonth();
        }

        return $months;
    }

    /**
     * Calculate summary for report
     */
    private function calculateReportSummary($reportData, $metrics)
    {
        $summary = [];

        foreach ($metrics as $metric) {
            $total = 0;

            foreach ($reportData as $period) {
                foreach ($period['batches'] as $batch) {
                    switch ($metric) {
                        case 'revenue':
                            $total += $batch['revenue'];
                            break;
                        case 'total_hpp':
                            $total += $batch['total_hpp'];
                            break;
                        case 'gross_profit':
                            $total += $batch['gross_profit'];
                            break;
                        case 'margin':
                            // Average margin
                            $total += $batch['margin'];
                            break;
                    }
                }
            }

            // For margin, calculate average
            if ($metric === 'margin' && count($reportData) > 0) {
                $totalBatches = array_sum(array_map(fn($p) => count($p['batches']), $reportData));
                $total = $totalBatches > 0 ? $total / $totalBatches : 0;
            }

            $summary[$metric] = $total;
        }

        return $summary;
    }
}
