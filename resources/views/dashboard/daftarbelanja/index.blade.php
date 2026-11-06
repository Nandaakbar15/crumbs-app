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

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if (session('info'))
        <div class="bg-blue-50 border border-blue-300 text-blue-700 px-4 py-3 rounded-lg mb-6 text-sm">
            {{ session('info') }}
        </div>
    @endif

    @php
        $criticalCount = \App\Models\Ingredient::where('is_active', true)->get()->filter(fn($i) => $i->isCritical())->count();
        $lowCount      = \App\Models\Ingredient::where('is_active', true)->get()->filter(fn($i) => $i->isLow() && !$i->isCritical())->count();
    @endphp

    @if ($criticalCount > 0 || $lowCount > 0)
        <div class="bg-[#FFF5F5] border border-[#C85A54] rounded-xl px-5 py-4 mb-6 flex items-center justify-between gap-4">
            <div>
                <p class="font-semibold text-[#C85A54] text-sm">
                    ⚠️ Ada
                    @if ($criticalCount > 0)
                        <span class="font-bold">{{ $criticalCount }} bahan kritis</span>
                    @endif
                    @if ($criticalCount > 0 && $lowCount > 0) dan @endif
                    @if ($lowCount > 0)
                        <span class="font-bold">{{ $lowCount }} bahan menipis</span>
                    @endif
                </p>
                <p class="text-xs text-[#8B6F47] mt-0.5">Buat daftar belanja otomatis dari bahan-bahan ini beserta resep yang memakainya.</p>
            </div>
            <form action="{{ route('shopping-lists.from-critical') }}" method="POST">
                @csrf
                <button type="submit"
                    class="px-4 py-2 rounded-lg bg-[#C85A54] text-white text-sm font-semibold hover:bg-opacity-90 transition whitespace-nowrap">
                    🛒 Buat dari Stok Kritis
                </button>
            </form>
        </div>
    @endif

    <!-- Batch Selection & Summary -->
    <div class="bg-white rounded-xl border border-[#E8DCC8] p-6 shadow-sm mb-6">
        <div class="flex items-end gap-4 mb-6">
            <!-- Batch Selection -->
            <div class="flex-1">
                <label class="block text-sm font-medium text-[#3E2723] mb-2">BATCH PERSIAPAN BELANJA</label>
                <div class="flex gap-2">
                    <select id="batchSelect" class="flex-1 px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]" onchange="filterByBatch(this.value)">
                        <option value="">-- Tampilkan semua --</option>
                        @foreach ($batchesWithLists as $batch)
                            <option value="{{ $batch->id }}" {{ $selectedBatchId == $batch->id ? 'selected' : '' }}>
                                {{ $batch->batch_code }} - {{ $batch->recipe->name ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                    <button onclick="openCreateModal()" title="Buat daftar belanja baru" class="px-4 py-2 rounded-lg bg-[#D4A574] text-white font-medium hover:bg-opacity-90 transition">+</button>
                    <button onclick="resetFilter()" title="Tampilkan semua" class="px-4 py-2 rounded-lg border-2 border-[#D4A574] text-[#D4A574] font-medium hover:bg-[#F5DEB3] transition">🔄</button>
                </div>
                @if ($selectedBatchId)
                    <p class="text-xs text-[#D4A574] mt-1">Menampilkan daftar belanja untuk batch yang dipilih. <a href="{{ route('shopping-lists.index') }}" class="underline">Tampilkan semua</a></p>
                @endif
            </div>

            <!-- Total Estimate Box -->
            <div class="bg-[#3E2723] rounded-lg p-4 text-white min-w-[320px]">
                <p class="text-sm uppercase tracking-wide mb-2">TOTAL ESTIMASI BELANJA</p>
                <p class="text-4xl font-bold mb-2" id="totalEstimate">Rp {{ number_format($shoppingLists->sum('total_estimated_cost'), 0, ',', '.') }}</p>
                <p class="text-xs opacity-90">
                    <span id="totalItems" class="font-semibold">{{ $shoppingLists->sum(fn($l) => $l->items->count()) }} item</span> per dibeli ·
                    <span id="totalBought" class="font-semibold">0 contang</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Shopping Lists -->
    @if ($shoppingLists->count() > 0)
        @foreach ($shoppingLists as $list)
            <div class="bg-white rounded-xl border border-[#E8DCC8] shadow-sm mb-6 overflow-hidden" data-list-id="{{ $list->id }}" data-item-count="{{ $list->items->count() }}" data-total-cost="{{ $list->total_estimated_cost }}">
                <!-- List Header -->
                <div class="bg-[#FAF8F3] border-b border-[#E8DCC8] px-6 py-3 flex items-center justify-between">
                    <div class="flex-1">
                        <p class="font-semibold text-[#3E2723]">{{ $list->shopping_list_code }}</p>
                        @if ($list->productionBatch)
                            <p class="text-xs text-[#8B6F47]">{{ $list->productionBatch->recipe->name ?? 'N/A' }} • {{ $list->productionBatch->batch_code ?? 'N/A' }}</p>
                        @else
                            <p class="text-xs text-[#C85A54] font-medium">🛒 Dari Stok Kritis</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <p class="font-bold text-[#3E2723]">Rp {{ number_format($list->total_estimated_cost, 0, ',', '.') }}</p>
                            <span class="inline-block px-2 py-1 rounded text-xs font-semibold 
                                @if ($list->status === 'pending') bg-[#F4C430] bg-opacity-20 text-[#F4C430]
                                @elseif ($list->status === 'completed') bg-[#6B9E77] bg-opacity-20 text-[#6B9E77]
                                @else bg-[#C85A54] bg-opacity-20 text-white
                                @endif
                            ">
                                {{ ucfirst($list->status) }}
                            </span>
                        </div>
                        <form action="{{ route('shopping-lists.destroy', $list) }}" method="POST"
                            onsubmit="return confirm('Hapus daftar belanja ' + '{{ $list->shopping_list_code }}' + '?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="p-2 rounded-lg text-[#C85A54] hover:bg-[#FFF5F5] transition"
                                title="Hapus daftar belanja">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-[#FAF8F3] border-b border-[#E8DCC8]">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-[#3E2723]">Bahan</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-[#3E2723]">Resep Terkait</th>
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
                                    <td class="px-6 py-4 text-xs text-[#8B6F47]">
                                        @if ($item->notes && str_starts_with($item->notes, 'Dipakai di:'))
                                            <span class="text-[#D4A574]">{{ str_replace('Dipakai di: ', '', $item->notes) }}</span>
                                        @elseif ($item->notes)
                                            {{ $item->notes }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-[#8B6F47]">
                                        {{ $item->ingredient->getLatestPrice()?->store_name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-[#3E2723]">{{ number_format($item->ingredient->current_stock, 0) }}</p>
                                        <p class="text-xs text-[#8B6F47]">{{ $item->ingredient->currentUnit->code ?? 'unit' }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $latestPr  = $item->ingredient->getLatestPrice();
                                            $buyQty    = (int) ($latestPr?->purchase_qty_input ?? 1);
                                            $buyPrice  = (float) ($latestPr?->purchase_price_input ?? $latestPr?->price_per_unit ?? 0);
                                            $buyUnit   = $item->ingredient->baseUnit?->code ?? 'unit';
                                        @endphp
                                        <p class="font-medium text-[#3E2723]">{{ number_format($item->quantity_needed, 0) }} {{ $buyUnit }}</p>
                                        <p class="text-xs text-[#8B6F47]">per {{ $buyQty }} {{ $buyUnit }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="checkbox" class="w-4 h-4 rounded border-[#D4A574] text-[#D4A574] focus:ring-0 item-checkbox" data-list="{{ $list->id }}">
                                    </td>
                                    <td class="px-6 py-4 text-sm text-[#D4A574] font-medium">
                                        Rp {{ number_format($buyPrice, 0, ',', '.') }} / {{ $buyQty }} {{ $buyUnit }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $latPr2    = $item->ingredient->getLatestPrice();
                                            $buyQty2   = max(1, (int) ($latPr2?->purchase_qty_input ?? 1));
                                            $buyPrice2 = (float) ($latPr2?->purchase_price_input ?? $latPr2?->price_per_unit ?? 0);
                                            $qtyNeeded2 = (float) $item->quantity_needed;
                                            $packs2     = $buyQty2 > 0 ? ceil($qtyNeeded2 / $buyQty2) : 1;
                                            $realCost   = $packs2 * $buyPrice2;
                                        @endphp
                                        <p class="font-medium text-[#3E2723]">Rp {{ number_format($realCost, 0, ',', '.') }}</p>
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
    <div id="createModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-4xl w-full p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-[#3E2723]">Buat Daftar Belanja Baru</h2>
                <button type="button" onclick="closeCreateModal()" class="text-[#8B6F47] hover:text-[#3E2723] text-2xl leading-none">&times;</button>
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
            document.getElementById('createModal').classList.remove('hidden');
        }

        function closeCreateModal() {
            document.getElementById('createModal').classList.add('hidden');
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

        function filterByBatch(batchId) {
            if (batchId) {
                window.location.href = `{{ route('shopping-lists.index') }}?batch_id=${batchId}`;
            } else {
                window.location.href = `{{ route('shopping-lists.index') }}`;
            }
        }

        function resetFilter() {
            window.location.href = `{{ route('shopping-lists.index') }}`;
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
            let totalBought = 0;
            document.querySelectorAll('.item-checkbox').forEach(cb => {
                if (cb.checked) totalBought++;
            });
            document.getElementById('totalBought').textContent = totalBought + ' contang';
        }

        function checkListCompletion(listId) {
            const card = document.querySelector('[data-list-id="' + listId + '"]');
            if (!card) return;
            const checkboxes = card.querySelectorAll('.item-checkbox');
            if (checkboxes.length === 0) return;
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            if (allChecked) {
                card.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.97)';
                setTimeout(() => {
                    card.style.display = 'none';
                    // Recalculate total from visible cards
                    recalculateTotalEstimate();
                }, 400);
            } else {
                card.style.display = '';
                card.style.opacity = '1';
                card.style.transform = '';
                recalculateTotalEstimate();
            }
        }

        function recalculateTotalEstimate() {
            // Sum total_estimated_cost from all visible shopping list cards
            let total = 0;
            let itemCount = 0;
            document.querySelectorAll('[data-list-id]').forEach(card => {
                if (card.style.display !== 'none') {
                    const cost = parseFloat(card.dataset.totalCost || 0);
                    total += cost;
                    itemCount += card.querySelectorAll('.item-checkbox').length;
                }
            });
            const formatted = 'Rp ' + total.toLocaleString('id-ID', { maximumFractionDigits: 0 });
            document.getElementById('totalEstimate').textContent = formatted;
            document.getElementById('totalItems').textContent = itemCount + ' item';
        }

        // Update summary on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateSummary();

            // Add event listener to checkboxes
            document.querySelectorAll('.item-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateSummary();
                    checkListCompletion(this.dataset.list);
                });
            });
        });

        // Close modal when clicking backdrop
        document.getElementById('createModal').addEventListener('click', function(e) {
            if (e.target === this) closeCreateModal();
        });
    </script>
@endsection
