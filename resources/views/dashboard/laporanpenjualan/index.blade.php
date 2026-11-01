@extends('dashboard.layouts.main')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-[#3E2723]">Laporan Penjualan</h1>
                <p class="text-[#8B6F47] mt-1">Pendapatan, HPP, laba kotor, dan margin dihitung otomatis dari batch produksi.</p>
            </div>
            <div class="flex gap-2">
                <button onclick="exportExcel()" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-[#6B9E77] text-white font-semibold hover:opacity-90 transition">
                    <span>📥</span>
                    <span>Unduh Excel</span>
                </button>
                <button onclick="printReport()" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-[#D4A574] text-white font-semibold hover:opacity-90 transition">
                    <span>🖨️</span>
                    <span>Cetak</span>
                </button>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="bg-white rounded-xl border border-[#E8DCC8] p-6 shadow-sm">
            <form method="GET" action="{{ route('laporan-penjualan.index') }}" class="flex gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-[#3E2723] mb-2">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                </div>

                <div class="flex-1">
                    <label class="block text-sm font-medium text-[#3E2723] mb-2">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                </div>

                <div class="flex-1">
                    <label class="block text-sm font-medium text-[#3E2723] mb-2">Tipe Laporan</label>
                    <select name="report_type" class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                        <option value="weekly" {{ $reportType === 'weekly' ? 'selected' : '' }}>Mingguan</option>
                        <option value="monthly" {{ $reportType === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                    </select>
                </div>

                <button type="submit" class="px-4 py-2 rounded-lg bg-[#6B9E77] text-white font-semibold hover:opacity-90 transition">
                    Tampilkan
                </button>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <div class="bg-white rounded-lg border border-[#E8DCC8] p-4 shadow-sm">
                <p class="text-[#8B6F47] text-sm font-medium">PENDAPATAN</p>
                <p class="text-2xl font-bold text-[#3E2723] mt-2">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</p>
                <p class="text-xs text-[#8B6F47] mt-1">{{ $summary['total_sold'] }} pcs dijual</p>
            </div>

            <div class="bg-white rounded-lg border border-[#E8DCC8] p-4 shadow-sm">
                <p class="text-[#8B6F47] text-sm font-medium">TOTAL HPP</p>
                <p class="text-2xl font-bold text-[#3E2723] mt-2">Rp {{ number_format($summary['total_hpp'], 0, ',', '.') }}</p>
                <p class="text-xs text-[#8B6F47] mt-1">Biaya produksi</p>
            </div>

            <div class="bg-white rounded-lg border border-[#E8DCC8] p-4 shadow-sm">
                <p class="text-[#8B6F47] text-sm font-medium">LABA KOTOR</p>
                <p class="text-2xl font-bold text-[#3E2723] mt-2">Rp {{ number_format($summary['total_profit'], 0, ',', '.') }}</p>
                <p class="text-xs text-[#8B6F47] mt-1">Profit {{ number_format($summary['avg_margin'], 1) }}%</p>
            </div>

            <div class="bg-white rounded-lg border border-[#E8DCC8] p-4 shadow-sm">
                <p class="text-[#8B6F47] text-sm font-medium">MARGIN</p>
                <p class="text-2xl font-bold text-[#3E2723] mt-2">{{ number_format($summary['avg_margin'], 1) }}%</p>
                <p class="text-xs text-[#8B6F47] mt-1">Margin rata-rata</p>
            </div>

            <div class="bg-white rounded-lg border border-[#E8DCC8] p-4 shadow-sm">
                <p class="text-[#8B6F47] text-sm font-medium">SISA STOK</p>
                <p class="text-2xl font-bold text-[#3E2723] mt-2">{{ $summary['total_unsold'] }} pcs</p>
                <p class="text-xs text-[#8B6F47] mt-1">Tidak terjual</p>
            </div>
        </div>

        <!-- Period Breakdown -->
        <div class="bg-white rounded-xl border border-[#E8DCC8] p-6 shadow-sm">
            <h2 class="text-lg font-bold text-[#3E2723] mb-4">
                {{ $reportType === 'weekly' ? 'Laporan Mingguan' : 'Laporan Bulanan' }}
            </h2>

            @forelse($groupedData as $period)
                <div class="mb-6 pb-6 border-b border-[#E8DCC8] last:border-0">
                    <h3 class="font-semibold text-[#3E2723] mb-3">{{ $period['period'] }}</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-[#FAF8F3]">
                                <tr>
                                    <th class="px-3 py-2 text-left text-[#3E2723] font-semibold">Batch</th>
                                    <th class="px-3 py-2 text-left text-[#3E2723] font-semibold">Resep</th>
                                    <th class="px-3 py-2 text-right text-[#3E2723] font-semibold">Pcs Dijual</th>
                                    <th class="px-3 py-2 text-right text-[#3E2723] font-semibold">Pcs Tersisa</th>
                                    <th class="px-3 py-2 text-right text-[#3E2723] font-semibold">Pendapatan</th>
                                    <th class="px-3 py-2 text-right text-[#3E2723] font-semibold">Total HPP</th>
                                    <th class="px-3 py-2 text-right text-[#3E2723] font-semibold">Laba Kotor</th>
                                    <th class="px-3 py-2 text-right text-[#3E2723] font-semibold">Margin</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $periodRevenue = 0;
                                    $periodHPP = 0;
                                    $periodProfit = 0;
                                @endphp
                                
                                @foreach($period['batches'] as $batch)
                                    @php
                                        $hpp = $batch->calculateTotalHPP();
                                        $profit = $batch->calculateGrossProfit();
                                        $margin = $batch->calculateMargin();
                                        $unsold = $batch->getUnsoldQuantity();
                                        
                                        $periodRevenue += $batch->revenue;
                                        $periodHPP += $hpp;
                                        $periodProfit += $profit;
                                    @endphp
                                    <tr class="border-b border-[#E8DCC8] hover:bg-[#FAF8F3] transition">
                                        <td class="px-3 py-2 text-[#3E2723]">{{ $batch->batch_code }}</td>
                                        <td class="px-3 py-2 text-[#3E2723]">{{ $batch->recipe->name }}</td>
                                        <td class="px-3 py-2 text-right text-[#3E2723]">{{ $batch->quantity_sold }}</td>
                                        <td class="px-3 py-2 text-right text-[#8B6F47]">{{ $unsold }}</td>
                                        <td class="px-3 py-2 text-right text-[#6B9E77] font-medium">Rp {{ number_format($batch->revenue, 0, ',', '.') }}</td>
                                        <td class="px-3 py-2 text-right text-[#3E2723]">Rp {{ number_format($hpp, 0, ',', '.') }}</td>
                                        <td class="px-3 py-2 text-right font-medium" style="color: {{ $profit >= 0 ? '#6B9E77' : '#D84545' }}">
                                            Rp {{ number_format($profit, 0, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-2 text-right text-[#3E2723] font-medium">{{ number_format($margin, 1) }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-[#E8DCC8] bg-opacity-30">
                                <tr class="border-t border-[#E8DCC8]">
                                    <td colspan="4" class="px-3 py-2 text-[#3E2723] font-semibold">Subtotal {{ $period['period'] }}</td>
                                    <td class="px-3 py-2 text-right text-[#6B9E77] font-bold">Rp {{ number_format($periodRevenue, 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-right text-[#3E2723] font-bold">Rp {{ number_format($periodHPP, 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-right font-bold" style="color: {{ $periodProfit >= 0 ? '#6B9E77' : '#D84545' }}">
                                        Rp {{ number_format($periodProfit, 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 text-right text-[#3E2723] font-bold">
                                        {{ $periodRevenue > 0 ? number_format(($periodProfit / $periodRevenue) * 100, 1) : '0' }}%
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 text-[#8B6F47]">
                    <p class="text-lg">Tidak ada data penjualan untuk periode ini</p>
                </div>
            @endforelse
        </div>

        <!-- Per Product Summary -->
        @if(count($perProduct) > 0)
            <div class="bg-white rounded-xl border border-[#E8DCC8] p-6 shadow-sm">
                <h2 class="text-lg font-bold text-[#3E2723] mb-4">Per Produk</h2>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-[#FAF8F3]">
                            <tr>
                                <th class="px-3 py-2 text-left text-[#3E2723] font-semibold">Produk</th>
                                <th class="px-3 py-2 text-right text-[#3E2723] font-semibold">Pcs Dijual</th>
                                <th class="px-3 py-2 text-right text-[#3E2723] font-semibold">Pcs Tersisa</th>
                                <th class="px-3 py-2 text-right text-[#3E2723] font-semibold">Pendapatan</th>
                                <th class="px-3 py-2 text-right text-[#3E2723] font-semibold">Total HPP</th>
                                <th class="px-3 py-2 text-right text-[#3E2723] font-semibold">Laba Kotor</th>
                                <th class="px-3 py-2 text-right text-[#3E2723] font-semibold">Margin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($perProduct as $product)
                                <tr class="border-b border-[#E8DCC8] hover:bg-[#FAF8F3] transition">
                                    <td class="px-3 py-2 text-[#3E2723] font-medium">{{ $product['recipe_name'] }}</td>
                                    <td class="px-3 py-2 text-right text-[#3E2723]">{{ $product['quantity_sold'] }}</td>
                                    <td class="px-3 py-2 text-right text-[#8B6F47]">{{ $product['quantity_unsold'] }}</td>
                                    <td class="px-3 py-2 text-right text-[#6B9E77] font-medium">Rp {{ number_format($product['revenue'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-right text-[#3E2723]">Rp {{ number_format($product['total_hpp'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-right font-medium" style="color: {{ $product['gross_profit'] >= 0 ? '#6B9E77' : '#D84545' }}">
                                        Rp {{ number_format($product['gross_profit'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 text-right text-[#3E2723] font-medium">{{ number_format($product['margin'], 1) }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <script>
        function printReport() {
            window.print();
        }

        function exportExcel() {
            // Temporary: Show alert (will implement Excel export in Phase 2)
            alert('Fitur unduh Excel akan segera tersedia. Gunakan cetak untuk menyimpan sebagai PDF.');
        }
    </script>

    <style media="print">
        @media print {
            button {
                display: none;
            }
            .flex.items-center.justify-between > div:last-child {
                display: none;
            }
        }
    </style>
@endsection
