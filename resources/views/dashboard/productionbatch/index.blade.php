@extends('dashboard.layouts.main')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column: Input Form -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl border border-[#E8DCC8] p-6 shadow-sm">
                <h2 class="text-xl font-bold text-[#3E2723] mb-6">Input Batch Produksi</h2>
                <p class="text-sm text-[#8B6F47] mb-6">Pilih resep dan jumlah batch, stok bahan otomatis terpotong.</p>

                <form action="{{ route('batches.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Resep Selection -->
                    <div>
                        <label class="block text-sm font-medium text-[#3E2723] mb-2">Resep</label>
                        <select name="recipe_id" id="recipeSelect" required class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                            <option value="">-- Pilih Resep --</option>
                            @foreach ($recipes as $recipe)
                                <option value="{{ $recipe->id }}" data-yield="{{ $recipe->yield_quantity }}">{{ $recipe->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Batch Quantity Pills -->
                    <div>
                        <label class="block text-sm font-medium text-[#3E2723] mb-3">Catatan (opsional)</label>
                        <div class="flex gap-2 flex-wrap">
                            <button type="button" onclick="setMultiplier(0.5)" class="px-3 py-2 rounded-lg border-2 border-[#E8DCC8] text-[#8B6F47] font-medium hover:bg-[#F5DEB3] transition">0.5x</button>
                            <button type="button" onclick="setMultiplier(1)" class="px-3 py-2 rounded-lg border-2 border-[#D4A574] bg-[#D4A574] text-white font-medium">1x</button>
                            <button type="button" onclick="setMultiplier(2)" class="px-3 py-2 rounded-lg border-2 border-[#E8DCC8] text-[#8B6F47] font-medium hover:bg-[#F5DEB3] transition">2x</button>
                            <button type="button" onclick="setMultiplier(4)" class="px-3 py-2 rounded-lg border-2 border-[#E8DCC8] text-[#8B6F47] font-medium hover:bg-[#F5DEB3] transition">4x</button>
                        </div>
                    </div>

                    <!-- Catatan -->
                    <div>
                        <label class="block text-sm font-medium text-[#3E2723] mb-2">Catatan (opsional)</label>
                        <textarea name="notes" placeholder="..." rows="3" class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]"></textarea>
                    </div>

                    <!-- Jumlah Batch -->
                    <div>
                        <label class="block text-sm font-medium text-[#3E2723] mb-2">Jumlah batch</label>
                        <input type="number" name="batch_number" value="1" min="1" required class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                    </div>

                    <!-- Production Date -->
                    <div>
                        <label class="block text-sm font-medium text-[#3E2723] mb-2">Tanggal produksi</label>
                        <input type="date" name="production_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                    </div>

                    <!-- Quantity Input -->
                    <div>
                        <label class="block text-sm font-medium text-[#3E2723] mb-2">Jumlah produk</label>
                        <input type="number" name="quantity" id="quantityInput" placeholder="0" step="0.01" required class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full px-4 py-3 rounded-lg bg-[#6B9E77] text-white font-semibold hover:bg-opacity-90 transition">
                        Simpan Batch
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Column: Production History -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border border-[#E8DCC8] p-6 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-[#3E2723]">Riwayat Produksi & Penjualan</h2>
                    <button onclick="openReportModal()" class="text-xs text-[#D4A574] hover:underline font-semibold">Buat Laporan →</button>
                </div>

                <div class="space-y-4">
                    @forelse ($recentBatches as $batch)
                        <div class="border-b border-[#E8DCC8] pb-4 last:border-0">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <h3 class="font-semibold text-[#3E2723]">{{ $batch->recipe->name ?? 'Unknown Recipe' }}</h3>
                                    <p class="text-xs text-[#8B6F47]">{{ $batch->production_date->format('d/m/Y') }} : {{ $batch->batch_code }}</p>
                                </div>
                                <span class="px-3 py-1 rounded-lg bg-[#D4A574] text-white text-xs font-semibold">HPP: Rp {{ number_format($batch->calculateTotalHPP() ?? 0, 0, ',', '.') }}</span>
                            </div>

                            <div class="flex items-center gap-3 flex-wrap text-sm">
                                <span class="text-[#3E2723] font-medium">Terjual:</span>
                                <input type="number" id="qty_{{ $batch->id }}" value="{{ $batch->quantity_sold ?? 0 }}" step="0.01" class="w-20 px-2 py-1 border border-[#E8DCC8] rounded text-center" />
                                <span class="text-[#8B6F47]">dari {{ $batch->produced_quantity }} pcs</span>
                                
                                <button onclick="recordSale({{ $batch->id }})" class="px-3 py-1 rounded-lg bg-[#6B9E77] text-white text-xs font-semibold hover:opacity-90 transition">Simpan</button>

                                @if($batch->quantity_sold > 0)
                                    <div class="ml-auto text-right">
                                        <p class="text-[#3E2723] font-semibold">Penjualan: Rp {{ number_format($batch->revenue ?? 0, 0, ',', '.') }}</p>
                                        <p class="text-[#8B6F47] text-xs">Laba: Rp {{ number_format($batch->calculateGrossProfit() ?? 0, 0, ',', '.') }} ({{ number_format($batch->calculateMargin() ?? 0, 1) }}%)</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-[#8B6F47]">
                            <p>Belum ada batch produksi</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Report Modal -->
    <div id="reportModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-8 w-full max-w-2xl max-h-screen overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-[#3E2723]">Buat Laporan Penjualan</h2>
                <button onclick="closeReportModal()" class="text-[#8B6F47] hover:text-[#3E2723]">&times;</button>
            </div>

            <form id="reportForm" class="space-y-6">
                @csrf
                
                <!-- Date Range -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-[#3E2723] mb-2">Dari Tanggal</label>
                        <input type="date" id="startDate" name="start_date" required class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[#3E2723] mb-2">Sampai Tanggal</label>
                        <input type="date" id="endDate" name="end_date" required class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                    </div>
                </div>

                <!-- Report Type -->
                <div>
                    <label class="block text-sm font-medium text-[#3E2723] mb-3">Tipe Laporan</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2">
                            <input type="radio" name="report_type" value="weekly" checked class="w-4 h-4">
                            <span class="text-[#3E2723]">Mingguan (Selasa - Senin)</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="report_type" value="monthly" class="w-4 h-4">
                            <span class="text-[#3E2723]">Bulanan</span>
                        </label>
                    </div>
                </div>

                <!-- Metrics Selection -->
                <div>
                    <label class="block text-sm font-medium text-[#3E2723] mb-3">Pilih Metrik (minimal 1)</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="metrics[]" value="revenue" checked class="w-4 h-4">
                            <span class="text-[#3E2723]">Pendapatan</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="metrics[]" value="total_hpp" checked class="w-4 h-4">
                            <span class="text-[#3E2723]">Total HPP</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="metrics[]" value="gross_profit" checked class="w-4 h-4">
                            <span class="text-[#3E2723]">Laba Kotor</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="metrics[]" value="margin" class="w-4 h-4">
                            <span class="text-[#3E2723]">Margin %</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="w-full px-4 py-3 rounded-lg bg-[#6B9E77] text-white font-semibold hover:opacity-90 transition">
                    Buat Laporan
                </button>
            </form>
        </div>
    </div>

    <!-- Report Display Modal -->
    <div id="reportDisplayModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-8 w-full max-w-4xl max-h-screen overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-[#3E2723]" id="reportTitle">Laporan Penjualan</h2>
                <div class="flex gap-2">
                    <button onclick="printReport()" class="px-4 py-2 rounded-lg bg-[#D4A574] text-white font-semibold hover:opacity-90 transition">Cetak</button>
                    <button onclick="closeReportDisplayModal()" class="text-[#8B6F47] hover:text-[#3E2723] text-2xl">&times;</button>
                </div>
            </div>

            <div id="reportContent" class="space-y-6">
                <!-- Report will be populated here by JavaScript -->
            </div>

            <div class="mt-8 pt-6 border-t border-[#E8DCC8]">
                <h3 class="font-bold text-[#3E2723] mb-4">Ringkasan</h3>
                <div id="reportSummary" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- Summary will be populated here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentMultiplier = 1;

        function setMultiplier(multiplier) {
            currentMultiplier = multiplier;
            
            // Update button styles
            document.querySelectorAll('[onclick*="setMultiplier"]').forEach(btn => {
                btn.classList.remove('border-[#D4A574]', 'bg-[#D4A574]', 'text-white');
                btn.classList.add('border-[#E8DCC8]', 'text-[#8B6F47]');
            });
            
            // Highlight selected
            event.target.classList.remove('border-[#E8DCC8]', 'text-[#8B6F47]');
            event.target.classList.add('border-[#D4A574]', 'bg-[#D4A574]', 'text-white');
            
            // Update quantity if recipe is selected
            const recipeSelect = document.getElementById('recipeSelect');
            if (recipeSelect.value) {
                const yieldQuantity = parseFloat(recipeSelect.options[recipeSelect.selectedIndex].dataset.yield) || 1;
                document.getElementById('quantityInput').value = (yieldQuantity * multiplier).toFixed(2);
            }
        }

        // Initialize multiplier
        document.getElementById('recipeSelect').addEventListener('change', function() {
            if (this.value) {
                const yieldQuantity = parseFloat(this.options[this.selectedIndex].dataset.yield) || 1;
                document.getElementById('quantityInput').value = (yieldQuantity * currentMultiplier).toFixed(2);
            }
        });

        // Initialize on page load
        window.addEventListener('load', function() {
            setMultiplier(1);
        });

        // Sales Recording Function
        function recordSale(batchId) {
            const quantity = document.getElementById(`qty_${batchId}`).value;
            
            if (!quantity || quantity < 0) {
                alert('Masukkan jumlah yang valid');
                return;
            }

            fetch(`/batches/${batchId}/record-sale`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    quantity_sold: parseFloat(quantity),
                    recorded_date: new Date().toISOString().split('T')[0]
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Penjualan berhasil dicatat');
                    location.reload();
                } else {
                    alert('Gagal mencatat penjualan: ' + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error: ' + err.message);
            });
        }

        // Report Modal Functions
        function openReportModal() {
            document.getElementById('reportModal').classList.remove('hidden');
            // Set default date range to last 30 days
            const today = new Date();
            const thirtyDaysAgo = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);
            document.getElementById('startDate').value = thirtyDaysAgo.toISOString().split('T')[0];
            document.getElementById('endDate').value = today.toISOString().split('T')[0];
        }

        function closeReportModal() {
            document.getElementById('reportModal').classList.add('hidden');
        }

        function closeReportDisplayModal() {
            document.getElementById('reportDisplayModal').classList.add('hidden');
        }

        // Report Form Submission
        document.getElementById('reportForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const metrics = formData.getAll('metrics[]');

            if (metrics.length === 0) {
                alert('Pilih minimal satu metrik');
                return;
            }

            const data = {
                start_date: document.getElementById('startDate').value,
                end_date: document.getElementById('endDate').value,
                report_type: document.querySelector('input[name="report_type"]:checked').value,
                metrics: metrics
            };

            fetch('{{ route('batches.generate-report') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    displayReport(data);
                    closeReportModal();
                    document.getElementById('reportDisplayModal').classList.remove('hidden');
                } else {
                    alert('Gagal membuat laporan');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error: ' + err.message);
            });
        });

        function displayReport(data) {
            const reportType = data.report_type === 'weekly' ? 'Mingguan' : 'Bulanan';
            document.getElementById('reportTitle').textContent = `Laporan Penjualan ${reportType}`;

            let html = '';
            data.data.forEach(period => {
                html += `
                    <div class="border border-[#E8DCC8] rounded-lg p-4">
                        <h3 class="font-bold text-[#3E2723] mb-3">${period.period}</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-[#E8DCC8]">
                                        <th class="text-left px-2 py-2 text-[#3E2723] font-semibold">Resep</th>
                                        <th class="text-right px-2 py-2 text-[#3E2723] font-semibold">Terjual</th>
                                        ${data.metrics.includes('revenue') ? '<th class="text-right px-2 py-2 text-[#3E2723] font-semibold">Pendapatan</th>' : ''}
                                        ${data.metrics.includes('total_hpp') ? '<th class="text-right px-2 py-2 text-[#3E2723] font-semibold">HPP</th>' : ''}
                                        ${data.metrics.includes('gross_profit') ? '<th class="text-right px-2 py-2 text-[#3E2723] font-semibold">Laba Kotor</th>' : ''}
                                        ${data.metrics.includes('margin') ? '<th class="text-right px-2 py-2 text-[#3E2723] font-semibold">Margin %</th>' : ''}
                                    </tr>
                                </thead>
                                <tbody>
                `;

                period.batches.forEach(batch => {
                    html += `
                        <tr class="border-b border-[#E8DCC8]">
                            <td class="px-2 py-2 text-[#3E2723]">${batch.recipe_name}</td>
                            <td class="text-right px-2 py-2 text-[#3E2723]">${parseFloat(batch.quantity_sold).toFixed(2)}</td>
                            ${data.metrics.includes('revenue') ? `<td class="text-right px-2 py-2 text-[#3E2723]">Rp ${parseFloat(batch.revenue).toLocaleString('id-ID', {maximumFractionDigits: 0})}</td>` : ''}
                            ${data.metrics.includes('total_hpp') ? `<td class="text-right px-2 py-2 text-[#3E2723]">Rp ${parseFloat(batch.total_hpp).toLocaleString('id-ID', {maximumFractionDigits: 0})}</td>` : ''}
                            ${data.metrics.includes('gross_profit') ? `<td class="text-right px-2 py-2 text-[#3E2723]">Rp ${parseFloat(batch.gross_profit).toLocaleString('id-ID', {maximumFractionDigits: 0})}</td>` : ''}
                            ${data.metrics.includes('margin') ? `<td class="text-right px-2 py-2 text-[#3E2723]">${parseFloat(batch.margin).toFixed(2)}%</td>` : ''}
                        </tr>
                    `;
                });

                html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
            });

            document.getElementById('reportContent').innerHTML = html;

            // Display summary
            let summaryHtml = '';
            if (data.summary.revenue) {
                summaryHtml += `
                    <div class="p-4 bg-[#FAF8F3] rounded-lg border border-[#E8DCC8]">
                        <p class="text-[#8B6F47] text-sm">Pendapatan Total</p>
                        <p class="text-2xl font-bold text-[#3E2723]">Rp ${parseFloat(data.summary.revenue).toLocaleString('id-ID', {maximumFractionDigits: 0})}</p>
                    </div>
                `;
            }
            if (data.summary.total_hpp) {
                summaryHtml += `
                    <div class="p-4 bg-[#FAF8F3] rounded-lg border border-[#E8DCC8]">
                        <p class="text-[#8B6F47] text-sm">Total HPP</p>
                        <p class="text-2xl font-bold text-[#3E2723]">Rp ${parseFloat(data.summary.total_hpp).toLocaleString('id-ID', {maximumFractionDigits: 0})}</p>
                    </div>
                `;
            }
            if (data.summary.gross_profit) {
                summaryHtml += `
                    <div class="p-4 bg-[#FAF8F3] rounded-lg border border-[#E8DCC8]">
                        <p class="text-[#8B6F47] text-sm">Laba Kotor</p>
                        <p class="text-2xl font-bold text-[#3E2723]">Rp ${parseFloat(data.summary.gross_profit).toLocaleString('id-ID', {maximumFractionDigits: 0})}</p>
                    </div>
                `;
            }
            if (data.summary.margin) {
                summaryHtml += `
                    <div class="p-4 bg-[#FAF8F3] rounded-lg border border-[#E8DCC8]">
                        <p class="text-[#8B6F47] text-sm">Margin Rata-rata</p>
                        <p class="text-2xl font-bold text-[#3E2723]">${parseFloat(data.summary.margin).toFixed(2)}%</p>
                    </div>
                `;
            }

            document.getElementById('reportSummary').innerHTML = summaryHtml;
        }

        function printReport() {
            window.print();
        }
    </script>

    <style media="print">
        @media print {
            body * {
                visibility: hidden;
            }
            #reportDisplayModal {
                position: static;
                background: white;
            }
            #reportDisplayModal * {
                visibility: visible;
            }
            #reportDisplayModal .flex.justify-between {
                display: none;
            }
        }
    </style>
@endsection
