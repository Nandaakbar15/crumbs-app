<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\ProductionBatch;
use App\Models\Recipe;

class LaporanPenjualanController extends Controller
{
    /**
     * Display sales report
     */
    public function index(Request $request)
    {
        // Default: dari 7 hari lalu sampai hari ini (mencakup minggu berjalan)
        $today = Carbon::now();

        // Cari Selasa terakhir (start of current Tuesday-based week)
        $weekStart = $today->copy()->startOfWeek(Carbon::TUESDAY)->startOfDay();
        // Jika hari ini sebelum Selasa (Sun/Mon), ambil Selasa minggu lalu
        if ($weekStart->gt($today)) {
            $weekStart->subWeek();
        }
        $weekEnd = $weekStart->copy()->addDays(6)->endOfDay(); // Senin malam

        $startDate = $request->get('start_date', $weekStart->format('Y-m-d'));
        $endDate   = $request->get('end_date',   $weekEnd->format('Y-m-d'));
        $reportType = $request->get('report_type', 'weekly');

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // Get batches with sales in the period
        $batches = ProductionBatch::with('recipe')
            ->whereBetween('recorded_date', [$start, $end])
            ->where('quantity_sold', '>', 0)
            ->orderBy('recorded_date', 'asc')
            ->get();

        // Group data based on report type
        if ($reportType === 'monthly') {
            $groupedData = $this->groupByMonth($batches);
        } else {
            $groupedData = $this->groupByWeek($batches);
        }

        // Calculate summary
        $summary = $this->calculateSummary($batches);

        // Get per-product summary
        $perProduct = $this->getPerProductSummary($batches);

        return view('dashboard.laporanpenjualan.index', [
            'groupedData' => $groupedData,
            'summary' => $summary,
            'perProduct' => $perProduct,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'reportType' => $reportType,
            'batches' => $batches,
        ]);
    }

    /**
     * Group batches by week (Tuesday-based)
     */
    private function groupByWeek($batches)
    {
        $grouped = [];

        foreach ($batches as $batch) {
            $recordDate = Carbon::parse($batch->recorded_date);

            // Cari Selasa terakhir dari tanggal ini
            $tuesday = $recordDate->copy()->startOfWeek(Carbon::TUESDAY)->startOfDay();
            if ($tuesday->gt($recordDate)) {
                $tuesday->subWeek();
            }

            $monday = $tuesday->copy()->addDays(6)->endOfDay();
            $weekKey    = $tuesday->format('Y-m-d');
            $weekPeriod = $tuesday->format('d M Y') . ' - ' . $monday->format('d M Y');

            if (!isset($grouped[$weekKey])) {
                $grouped[$weekKey] = [
                    'period'     => $weekPeriod,
                    'start_date' => $tuesday->format('Y-m-d'),
                    'end_date'   => $monday->format('Y-m-d'),
                    'batches'    => [],
                ];
            }

            $grouped[$weekKey]['batches'][] = $batch;
        }

        return $grouped;
    }

    /**
     * Group batches by month
     */
    private function groupByMonth($batches)
    {
        $grouped = [];
        
        foreach ($batches as $batch) {
            $recordDate = Carbon::parse($batch->recorded_date);
            $monthKey = $recordDate->format('Y-m');
            $monthPeriod = $recordDate->format('F Y');
            $monthEnd = $recordDate->copy()->endOfMonth();
            
            if (!isset($grouped[$monthKey])) {
                $grouped[$monthKey] = [
                    'period' => $monthPeriod,
                    'start_date' => $recordDate->copy()->startOfMonth()->format('Y-m-d'),
                    'end_date' => $monthEnd->format('Y-m-d'),
                    'batches' => [],
                ];
            }
            
            $grouped[$monthKey]['batches'][] = $batch;
        }
        
        return $grouped;
    }

    /**
     * Calculate overall summary
     */
    private function calculateSummary($batches)
    {
        $totalRevenue = 0;
        $totalHPP = 0;
        $totalProfit = 0;
        $totalSold = 0;
        $totalUnsold = 0;

        foreach ($batches as $batch) {
            $totalRevenue += $batch->revenue;
            $totalHPP += $batch->calculateTotalHPP();
            $totalProfit += $batch->calculateGrossProfit();
            $totalSold += $batch->quantity_sold;
            $totalUnsold += $batch->getUnsoldQuantity();
        }

        $avgMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

        return [
            'total_revenue' => $totalRevenue,
            'total_hpp' => $totalHPP,
            'total_profit' => $totalProfit,
            'total_sold' => $totalSold,
            'total_unsold' => $totalUnsold,
            'avg_margin' => $avgMargin,
        ];
    }

    /**
     * Get per-product summary
     */
    private function getPerProductSummary($batches)
    {
        $products = [];

        foreach ($batches as $batch) {
            $recipeName = $batch->recipe->name ?? 'Unknown';
            
            if (!isset($products[$recipeName])) {
                $products[$recipeName] = [
                    'recipe_name' => $recipeName,
                    'recipe_id' => $batch->recipe_id,
                    'quantity_sold' => 0,
                    'quantity_unsold' => 0,
                    'revenue' => 0,
                    'total_hpp' => 0,
                    'gross_profit' => 0,
                    'batches_count' => 0,
                ];
            }
            
            $products[$recipeName]['quantity_sold'] += $batch->quantity_sold;
            $products[$recipeName]['quantity_unsold'] += $batch->getUnsoldQuantity();
            $products[$recipeName]['revenue'] += $batch->revenue;
            $products[$recipeName]['total_hpp'] += $batch->calculateTotalHPP();
            $products[$recipeName]['gross_profit'] += $batch->calculateGrossProfit();
            $products[$recipeName]['batches_count']++;
        }

        // Calculate margin for each product
        foreach ($products as $key => $product) {
            if ($product['revenue'] > 0) {
                $products[$key]['margin'] = ($product['gross_profit'] / $product['revenue']) * 100;
            } else {
                $products[$key]['margin'] = 0;
            }
        }

        return array_values($products);
    }
}
