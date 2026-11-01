@extends('dashboard.layouts.main')

@section('content')
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h1 class="text-3xl font-bold text-[#3E2723] mb-2">Ringkasan Belanja</h1>
                <p class="text-[#8B6F47]">Target stok bulanan, kebutuhan resep + jumlah batch + safety stock.</p>
            </div>
            <div class="flex gap-3">
                <button onclick="shareViaWhatsapp()" class="px-4 py-2 rounded-lg border-2 border-[#D4A574] text-[#D4A574] font-medium hover:bg-[#F5DEB3] transition flex items-center gap-2 whitespace-nowrap">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.67-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.076 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421-7.403h-.004a9.87 9.87 0 00-5.031 1.378c-3.055 2.036-5.044 5.46-5.044 9.033 0 1.798.487 3.558 1.388 5.09L2.254 22l5.592-1.33c1.447.776 3.074 1.19 4.722 1.19 5.514 0 10-4.486 10-10S17.486 2.5 11.972 2.5c-2.821 0-5.46.856-7.738 2.465"/></svg>
                    Kirim untuk Whatsapp
                </button>
                <button onclick="printList()" class="px-4 py-2 rounded-lg bg-[#8B5A2B] text-white font-medium hover:bg-opacity-90 transition">
                    Cetak
                </button>
            </div>
        </div>
    </div>

    <!-- Batch Selection & Summary -->
    <div class="bg-white rounded-xl border border-[#E8DCC8] p-6 shadow-sm mb-6">
        <div class="flex items-end gap-4 mb-6">
            <!-- Batch Selection -->
            <div class="flex-1">
                <label class="block text-sm font-medium text-[#3E2723] mb-2">BATCH PERSIAPAN BELANJA</label>
                <div class="flex gap-2">
                    <select id="batchSelect" class="flex-1 px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]" onchange="loadBatchItems()">
                        <option value="">-- Pilih batch --</option>
                        @foreach ($productionBatches as $batch)
                            <option value="{{ $batch->id }}">
                                {{ $batch->productionBatch->batch_code ?? $batch->batch_code }} - {{ $batch->recipe->name }}
                            </option>
                        @endforeach
                    </select>
                    <button onclick="openCreateModal()" class="px-4 py-2 rounded-lg bg-[#D4A574] text-white font-medium hover:bg-opacity-90 transition">+</button>
                    <button class="px-4 py-2 rounded-lg border-2 border-[#D4A574] text-[#D4A574] font-medium">−</button>
                    <button class="px-4 py-2 rounded-lg border-2 border-[#D4A574] text-[#D4A574] font-medium">🔄</button>
                </div>
            </div>

            <!-- Total Estimate Box -->
            <div class="bg-[#3E2723] rounded-lg p-4 text-white min-w-[320px]">
                <p class="text-sm uppercase tracking-wide mb-2">TOTAL ESTIMASI BELANJA</p>
                <p class="text-4xl font-bold mb-2" id="totalEstimate">Rp 0</p>
                <p class="text-xs opacity-90">
                    <span id="totalItems" class="font-semibold">0 item</span> per dibeli · 
                    <span id="totalBought" class="font-semibold">0 contang</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Shopping Lists -->
    @if ($shoppingLists->count() > 0)
        @foreach ($shoppingLists as $list)
            <div class="bg-white rounded-xl border border-[#E8DCC8] shadow-sm mb-6 overflow-hidden">
                <!-- List Header -->
                <div class="bg-[#FAF8F3] border-b border-[#E8DCC8] px-6 py-3 flex items-center justify-between">
                    <div class="flex-1">
                        <p class="font-semibold text-[#3E2723]">{{ $list->shopping_list_code }}</p>
                        <p class="text-xs text-[#8B6F47]">{{ $list->productionBatch->recipe->name ?? 'N/A' }} • {{ $list->productionBatch->batch_code ?? 'N/A' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-[#3E2723]">Rp {{ number_format($list->total_estimated_cost, 0, ',', '.') }}</p>
                        <span class="inline-block px-2 py-1 rounded text-xs font-semibold 
                            @if ($list->status === 'pending') bg-[#F4C430] bg-opacity-20 text-[#F4C430]
                            @elseif ($list->status === 'completed') bg-[#6B9E77] bg-opacity-20 text-[#6B9E77]
                            @else bg-[#C85A54] bg-opacity-20 text-[#C85A54]
                            @endif
                        ">
                            {{ ucfirst($list->status) }}
                        </span>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-[#FAF8F3] border-b border-[#E8DCC8]">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-[#3E2723]">Bahan</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-[#3E2723]">Supplier / Tempat Beli</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-[#3E2723]">Stok</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-[#3E2723]">Kebutuhan</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-[#3E2723]">Beli</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-[#3E2723]">Harga</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-[#3E2723]">Estimasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($list->items as $item)
                                <tr class="border-b border-[#E8DCC8] hover:bg-[#FAF8F3] transition">
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-[#3E2723]">{{ $item->ingredient->name }}</p>
                                        <p class="text-xs text-[#8B6F47]">{{ $item->ingredient->baseUnit->code ?? 'unit' }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-[#8B6F47]">
                                        {{ $item->supplier->name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-[#3E2723]">{{ number_format($item->ingredient->current_stock, 0) }}</p>
                                        <p class="text-xs text-[#8B6F47]">{{ $item->ingredient->currentUnit->code ?? 'unit' }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-[#3E2723]">{{ number_format($item->quantity_needed, 2) }}</p>
                                        <p class="text-xs text-[#8B6F47]">{{ $item->unit->code ?? 'unit' }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="checkbox" class="w-4 h-4 rounded border-[#D4A574] text-[#D4A574] focus:ring-0">
                                    </td>
                                    <td class="px-6 py-4 text-sm text-[#D4A574] font-medium">
                                        Rp {{ number_format($item->estimated_price_per_unit, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-[#3E2723]">Rp {{ number_format($item->total_estimated_cost, 0, ',', '.') }}</p>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    @else
        <!-- Empty State -->
        <div class="bg-white rounded-xl border border-[#E8DCC8] p-12 shadow-sm text-center">
            <p class="text-lg text-[#8B6F47] mb-2">Stok cukup untuk target itu. Tidak ada yang perlu dibeli!</p>
            <p class="text-sm text-[#D4A574]">Silakan buat daftar belanja baru untuk batch produksi yang lain.</p>
        </div>
    @endif

    <!-- Create Shopping List Modal -->
    <style>
        @keyframes slideDownFrom {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInOverlay {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .modal-overlay-show {
            animation: fadeInOverlay 0.3s ease-out;
        }

        .modal-content-show {
            animation: slideDownFrom 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .modal-overlay-hide {
            animation: fadeInOverlay 0.3s ease-out reverse;
        }

        .modal-content-hide {
            animation: slideDownFrom 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) reverse;
        }
    </style>

    <div id="createModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 modal-overlay">
        <div class="bg-white rounded-xl max-w-4xl w-full p-6 modal-content max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-[#3E2723]">Buat Daftar Belanja Baru</h2>
                <button onclick="closeCreateModal()" class="text-[#8B6F47] hover:text-[#3E2723]">✕</button>
            </div>

            <form id="shoppingListForm" action="{{ route('shopping-lists.store') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Batch Selection -->
                <div>
                    <label class="block text-sm font-medium text-[#3E2723] mb-2">Pilih Batch Produksi</label>
                    <select name="production_batch_id" required class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                        <option value="">-- Pilih batch --</option>
                        @foreach ($productionBatches as $batch)
                            <option value="{{ $batch->id }}">
                                {{ $batch->batch_code }} - {{ $batch->recipe->name }} ({{ number_format($batch->produced_quantity) }} {{ $batch->producedUnit->code ?? 'pcs' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Items Section -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-sm font-medium text-[#3E2723]">ITEM BELANJA</label>
                        <button type="button" onclick="addShoppingItem()" class="text-xs text-[#D4A574] hover:underline font-semibold">
                            + Tambah item
                        </button>
                    </div>

                    <!-- Items Table -->
                    <div class="border border-[#E8DCC8] rounded-lg overflow-hidden">
                        <table class="w-full">
                            <thead class="bg-[#FAF8F3] border-b border-[#E8DCC8]">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-[#3E2723]">Bahan</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-[#3E2723]">Jumlah Kebutuhan</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-[#3E2723]">Satuan</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-[#3E2723]">Harga Perkiraan</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-[#3E2723]">Supplier</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-[#3E2723]">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="shoppingItems">
                                <!-- Items will be added here -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="closeCreateModal()" class="flex-1 px-4 py-2 border border-[#E8DCC8] rounded-lg text-[#8B6F47] font-medium hover:bg-[#FAF8F3] transition">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-[#D4A574] text-white rounded-lg font-medium hover:bg-opacity-90 transition">Simpan Daftar Belanja</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let itemCount = 0;

        function openCreateModal() {
            itemCount = 0;
            document.getElementById('shoppingItems').innerHTML = '';
            addShoppingItem();

            const modal = document.getElementById('createModal');
            const overlay = modal.querySelector('.modal-overlay');
            const content = modal.querySelector('.modal-content');
            
            modal.classList.remove('hidden');
            overlay.classList.add('modal-overlay-show');
            content.classList.add('modal-content-show');
        }

        function closeCreateModal() {
            const modal = document.getElementById('createModal');
            const overlay = modal.querySelector('.modal-overlay');
            const content = modal.querySelector('.modal-content');
            
            overlay.classList.remove('modal-overlay-show');
            overlay.classList.add('modal-overlay-hide');
            content.classList.remove('modal-content-show');
            content.classList.add('modal-content-hide');
            
            setTimeout(() => {
                modal.classList.add('hidden');
                overlay.classList.remove('modal-overlay-hide');
                content.classList.remove('modal-content-hide');
            }, 300);
        }

        function addShoppingItem() {
            itemCount++;
            const row = `
                <tr>
                    <td class="px-3 py-2 border-b border-[#E8DCC8]">
                        <select name="items[${itemCount}][ingredient_id]" class="w-full px-2 py-1 border border-[#E8DCC8] rounded text-sm" required>
                            <option value="">-- Pilih bahan --</option>
                            @foreach ($ingredients as $ingredient)
                                <option value="{{ $ingredient->id }}">{{ $ingredient->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="px-3 py-2 border-b border-[#E8DCC8]">
                        <input type="number" name="items[${itemCount}][quantity_needed]" placeholder="0" step="0.01" class="w-full px-2 py-1 border border-[#E8DCC8] rounded text-sm" required>
                    </td>
                    <td class="px-3 py-2 border-b border-[#E8DCC8]">
                        <select name="items[${itemCount}][unit_id]" class="w-full px-2 py-1 border border-[#E8DCC8] rounded text-sm" required>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->code }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="px-3 py-2 border-b border-[#E8DCC8]">
                        <input type="number" name="items[${itemCount}][estimated_price_per_unit]" placeholder="0" step="0.01" class="w-full px-2 py-1 border border-[#E8DCC8] rounded text-sm" required>
                    </td>
                    <td class="px-3 py-2 border-b border-[#E8DCC8]">
                        <select name="items[${itemCount}][supplier_id]" class="w-full px-2 py-1 border border-[#E8DCC8] rounded text-sm">
                            <option value="">-- Pilih supplier --</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="px-3 py-2 border-b border-[#E8DCC8] text-center">
                        <button type="button" onclick="removeShoppingItem(this)" class="text-red-500 hover:text-red-700 text-sm">✕</button>
                    </td>
                </tr>
            `;
            document.getElementById('shoppingItems').insertAdjacentHTML('beforeend', row);
        }

        function removeShoppingItem(btn) {
            btn.closest('tr').remove();
        }

        function loadBatchItems() {
            // Placeholder for loading batch items
            alert('Feature coming soon');
        }

        function shareViaWhatsapp() {
            // Get current shopping list totals
            const totalEstimate = document.getElementById('totalEstimate')?.textContent || 'Rp 0';
            const totalItems = document.getElementById('totalItems')?.textContent || '0 item';
            const totalBought = document.getElementById('totalBought')?.textContent || '0 contang';
            
            // Create WhatsApp message
            const message = `*Ringkasan Belanja*%0A%0A📋 Total Estimasi: ${totalEstimate}%0A📦 ${totalItems}%0A✓ ${totalBought}%0A%0AUntuk detail lengkap, silakan buka aplikasi.`;
            
            // Open WhatsApp with pre-filled message
            const whatsappURL = `https://wa.me/?text=${message}`;
            window.open(whatsappURL, '_blank');
        }

        function printList() {
            window.print();
        }

        function updateSummary() {
            // Calculate totals from all shopping lists
            let totalCost = 0;
            let totalItems = 0;
            let totalBought = 0;

            // Sum from all visible shopping list items
            document.querySelectorAll('tbody tr').forEach(row => {
                const estimasiCell = row.querySelector('td:last-child');
                if (estimasiCell) {
                    const estimasiText = estimasiCell.textContent.replace('Rp ', '').replace(/\./g, '');
                    totalCost += parseInt(estimasiText) || 0;
                    totalItems++;
                }

                // Check if checkbox is checked
                const checkbox = row.querySelector('input[type="checkbox"]');
                if (checkbox && checkbox.checked) {
                    totalBought++;
                }
            });

            // Update display
            document.getElementById('totalEstimate').textContent = 'Rp ' + totalCost.toLocaleString('id-ID');
            document.getElementById('totalItems').textContent = totalItems + ' item';
            document.getElementById('totalBought').textContent = totalBought + ' contang';
        }

        // Update summary on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateSummary();
            
            // Add event listener to checkboxes
            document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                checkbox.addEventListener('change', updateSummary);
            });
        });

        // Close modal when clicking outside
        const createModal = document.getElementById('createModal');
        if (createModal) {
            createModal.addEventListener('click', function(e) {
                if (e.target === this || e.target.classList.contains('modal-overlay')) {
                    closeCreateModal();
                }
            });
        }
    </script>
@endsection
