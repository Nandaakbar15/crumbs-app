@extends('dashboard.layouts.main')

@section('content')
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-[#3E2723] mb-2">Waste & Loss</h1>
        <p class="text-[#8B6F47]">Catat bahan-tumpah, gosong, kadaluarsa, atau rusak saat stok digital sesuai stok ril.</p>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Input Form -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl border border-[#E8DCC8] p-6 shadow-sm">
                <h2 class="text-lg font-bold text-[#3E2723] mb-4">Catat Waste</h2>

                <form id="wasteForm" action="{{ route('waste-loss.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Bahan -->
                    <div>
                        <label class="block text-sm font-medium text-[#3E2723] mb-2">Bahan</label>
                        <select name="ingredient_id" required class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                            <option value="">-- Pilih bahan --</option>
                            @foreach ($ingredients as $ingredient)
                                <option value="{{ $ingredient->id }}">{{ $ingredient->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Jumlah & Satuan -->
                    <div>
                        <label class="block text-sm font-medium text-[#3E2723] mb-2">Jumlah</label>
                        <div class="flex gap-2">
                            <input type="number" name="quantity" placeholder="0" step="0.01" required class="flex-1 px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                            <select name="unit_id" required class="px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->code }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Jenis Pembuangan (Hidden, defaulted to 'waste') -->
                    <input type="hidden" name="waste_type" value="waste">
                    <input type="hidden" name="recorded_date" id="recordedDate" value="{{ date('Y-m-d') }}">

                    <!-- Pembuangan -->
                    <div>
                        <label class="block text-sm font-medium text-[#3E2723] mb-2">Pembuangan</label>
                        <select name="reason" required class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                            <option value="">-- Pilih alasan --</option>
                            <option value="Tumpah/Jatuh di mangkuk">Tumpah/Jatuh di mangkuk</option>
                            <option value="Gosong/Terlalu matang">Gosong/Terlalu matang</option>
                            <option value="Kadaluarsa">Kadaluarsa</option>
                            <option value="Rusak">Rusak</option>
                            <option value="Testing/Quality Check">Testing/Quality Check</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <!-- Catatan Kerugian -->
                    <div>
                        <label class="block text-xs font-medium text-[#D4A574] uppercase mb-2">Estimasi Kerugian Bulan Ini</label>
                        @php
                            $totalBulanIni = ($wasteLogs->sum(fn($w) => $w->getWasteCost())) + ($lossLogs->sum(fn($l) => $l->getWasteCost()));
                        @endphp
                        <p class="text-2xl font-bold text-[#3E2723] mb-3">Rp {{ number_format($totalBulanIni, 0, ',', '.') }}</p>
                    </div>

                    <!-- Catat Button -->
                    <button type="submit" class="w-full px-4 py-2 rounded-lg bg-[#D4A574] text-white font-medium hover:bg-opacity-90 transition">
                        🗒️ Catat Waste
                    </button>

                    <!-- Total Breakdown -->
                    <div class="bg-[#FAF8F3] rounded-lg p-4 mt-6">
                        <p class="text-xs uppercase font-semibold text-[#3E2723] mb-3">TOTAL KERUGIAN</p>
                        @php
                            $totalWasteCost = $wasteByReason->sum('total_cost');
                            $totalLossCost  = $lossByReason->sum('total_cost');
                            $totalKerugian  = $totalWasteCost + $totalLossCost;
                        @endphp
                        <p class="text-3xl font-bold text-[#3E2723] mb-3">Rp {{ number_format($totalKerugian, 0, ',', '.') }}</p>

                        @if ($wasteByReason->count() > 0 || $lossByReason->count() > 0)
                            <div class="space-y-2 text-sm">
                                @foreach ($wasteByReason as $item)
                                    <div class="flex justify-between items-center">
                                        <span class="text-[#8B6F47]">{{ $item['reason'] }}</span>
                                        <span class="text-[#D4A574] font-semibold">Rp {{ number_format($item['total_cost'], 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                                @foreach ($lossByReason as $item)
                                    <div class="flex justify-between items-center">
                                        <span class="text-[#8B6F47]">{{ $item['reason'] }}</span>
                                        <span class="text-[#C85A54] font-semibold">Rp {{ number_format($item['total_cost'], 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-[#8B6F47]">Belum ada kerugian bulan ini.</p>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Column: Waste & Loss Logs -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Log Waste Section -->
            <div class="bg-white rounded-xl border border-[#E8DCC8] shadow-sm overflow-hidden">
                <div class="bg-[#FAF8F3] border-b border-[#E8DCC8] px-6 py-3">
                    <h3 class="font-bold text-[#3E2723]">Log Waste</h3>
                </div>

                @if ($wasteLogs->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-[#FAF8F3] border-b border-[#E8DCC8]">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-[#3E2723]">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-[#3E2723]">Bahan</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-[#3E2723]">Jumlah</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-[#3E2723]">Pembuangan</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-[#3E2723]">Kerugian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($wasteLogs as $waste)
                                    <tr class="border-b border-[#E8DCC8] hover:bg-[#FAF8F3] transition">
                                        <td class="px-6 py-3 text-sm text-[#8B6F47]">
                                            {{ date('d M, H:i', strtotime($waste->recorded_date . ' ' . $waste->created_at->format('H:i:s'))) }}
                                        </td>
                                        <td class="px-6 py-3">
                                            <p class="font-medium text-[#3E2723]">{{ $waste->ingredient->name }}</p>
                                            <p class="text-xs text-[#8B6F47]">{{ $waste->ingredient->baseUnit->code ?? 'unit' }}</p>
                                        </td>
                                        <td class="px-6 py-3">
                                            <p class="font-medium text-[#3E2723]">{{ number_format($waste->quantity, 2) }} {{ $waste->unit->code }}</p>
                                        </td>
                                        <td class="px-6 py-3">
                                            <p class="text-sm text-[#8B6F47]">{{ $waste->reason }}</p>
                                        </td>
                                        <td class="px-6 py-3">
                                            <p class="font-semibold text-[#D4A574]">Rp {{ number_format($waste->getWasteCost(), 0, ',', '.') }}</p>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-8 text-center text-[#8B6F47]">
                        <p>Belum ada waste tercatat bulan ini</p>
                    </div>
                @endif
            </div>

            <!-- Log Loss Section -->
            <div class="bg-white rounded-xl border border-[#E8DCC8] shadow-sm overflow-hidden">
                <div class="bg-[#FAF8F3] border-b border-[#E8DCC8] px-6 py-3">
                    <h3 class="font-bold text-[#3E2723]">Log Loss</h3>
                </div>

                @if ($lossLogs->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-[#FAF8F3] border-b border-[#E8DCC8]">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-[#3E2723]">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-[#3E2723]">Bahan</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-[#3E2723]">Jumlah</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-[#3E2723]">Kerugian</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-[#3E2723]">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lossLogs as $loss)
                                    <tr class="border-b border-[#E8DCC8] hover:bg-[#FAF8F3] transition">
                                        <td class="px-6 py-3 text-sm text-[#8B6F47]">
                                            {{ date('d M, H:i', strtotime($loss->recorded_date . ' ' . $loss->created_at->format('H:i:s'))) }}
                                        </td>
                                        <td class="px-6 py-3">
                                            <p class="font-medium text-[#3E2723]">{{ $loss->ingredient->name }}</p>
                                        </td>
                                        <td class="px-6 py-3">
                                            <p class="font-medium text-[#3E2723]">{{ number_format($loss->quantity, 2) }} {{ $loss->unit->code }}</p>
                                        </td>
                                        <td class="px-6 py-3">
                                            <p class="font-semibold text-[#D4A574]">Rp {{ number_format($loss->getWasteCost(), 0, ',', '.') }}</p>
                                        </td>
                                        <td class="px-6 py-3 text-sm text-[#8B6F47]">
                                            {{ $loss->reason }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-8 text-center text-[#8B6F47]">
                        <p>Belum ada loss tercatat bulan ini</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        // Update estimated loss when inputs change
        document.getElementById('wasteForm').addEventListener('change', function() {
            const ingredientSelect = document.querySelector('select[name="ingredient_id"]');
            const quantityInput = document.querySelector('input[name="quantity"]');
            
            if (ingredientSelect.value && quantityInput.value) {
                // This would need AJAX to fetch actual price from backend
                // For now, we'll show a placeholder
                console.log('Calculate loss: ' + ingredientSelect.value + ' x ' + quantityInput.value);
            }
        });

        // Handle form submission
        document.getElementById('wasteForm').addEventListener('submit', function(e) {
            // The form will submit normally to the backend
        });
    </script>
@endsection
