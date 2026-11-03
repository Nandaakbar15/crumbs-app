@extends('dashboard.layouts.main')

@section('content')
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-[#3E2723] mb-2">Stok Bahan</h1>
        <p class="text-[#8B6F47]">Semua stok dan harga bahan diatur dalam satuan dasar (gram, ml, pcs) dan diskonversi otomatis.</p>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            <strong class="font-bold">Gagal menyimpan bahan:</strong>
            <ul class="mt-1 list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
            {{ session('success') }}
        </div>
    @endif

    <!-- Search and Filters -->
    <div class="bg-white rounded-xl border border-[#E8DCC8] p-6 shadow-sm mb-6">
        <div class="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between mb-6">
            <!-- Search Bar -->
            <div class="relative flex-1">
                <svg class="w-5 h-5 absolute left-3 top-3 text-[#D4A574]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input type="text" id="searchInput" placeholder="Cari bahan atau supplier..." class="w-full pl-10 pr-4 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
            </div>

            <!-- Add Button -->
            <button onclick="openAddModal()" class="px-4 py-2 rounded-lg bg-[#D4A574] text-white font-semibold hover:bg-opacity-90 transition flex items-center gap-2 whitespace-nowrap">
                <span>+</span>
                <span>Tambah Bahan</span>
            </button>
        </div>

        <!-- Filter Pills -->
        @php
            $kritisCount  = $ingredients->filter(fn($i) => $i->isCritical())->count();
            $menipisCount = $ingredients->filter(fn($i) => $i->isLow() && !$i->isCritical())->count();
            $amanCount    = $ingredients->filter(fn($i) => !$i->isLow())->count();
        @endphp
        <div class="flex gap-3 flex-wrap">
            <button class="px-3 py-1 rounded-full bg-[#D4A574] text-white text-sm font-medium">Semua</button>
            <button class="px-3 py-1 rounded-full bg-[#F5DEB3] text-[#8B5A2B] text-sm font-medium hover:bg-opacity-80 transition">Kritis {{ $kritisCount }}</button>
            <button class="px-3 py-1 rounded-full bg-[#F5DEB3] text-[#8B5A2B] text-sm font-medium hover:bg-opacity-80 transition">Menipis {{ $menipisCount }}</button>
            <button class="px-3 py-1 rounded-full bg-[#F5DEB3] text-[#8B5A2B] text-sm font-medium hover:bg-opacity-80 transition">Aman {{ $amanCount }}</button>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-[#E8DCC8] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-[#FAF8F3] border-b border-[#E8DCC8]">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#3E2723]">Bahan</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#3E2723]">Kategori</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#3E2723]">Stok</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#3E2723]">Batas Min</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#3E2723]">Harga Beli</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#3E2723]">Biaya / satuan</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#3E2723]">Status</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#3E2723]">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ingredients as $ingredient)
                        <tr class="border-b border-[#E8DCC8] hover:bg-[#FAF8F3] transition">
                            <td class="px-6 py-4">
                                <p class="font-medium text-[#3E2723]">{{ $ingredient->name }}</p>
                                <p class="text-xs text-[#8B6F47]">{{ $ingredient->baseUnit->code ?? 'unit' }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm text-[#8B6F47]">{{ $ingredient->category ?? '-' }}</td>
                            <td class="px-6 py-4">
                                @php $stock = $ingredient->formatStock(); @endphp
                                <p class="font-medium text-[#3E2723]">{{ number_format($stock['value'], 0, ',', '.') }}</p>
                                <p class="text-xs text-[#8B6F47]">{{ $stock['unit'] }}</p>
                            </td>
                            <td class="px-6 py-4">
                                @php $threshold = $ingredient->formatThreshold(); @endphp
                                <p class="font-medium text-[#3E2723]">{{ number_format($threshold['value'], 0, ',', '.') }}</p>
                                <p class="text-xs text-[#8B6F47]">{{ $threshold['unit'] }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-[#3E2723]">Rp {{ number_format($ingredient->getLatestPrice()?->price_per_unit ?? 0, 0, ',', '.') }}</p>
                                <p class="text-xs text-[#8B6F47]">per {{ $ingredient->baseUnit->code ?? 'unit' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-[#D4A574]">Rp {{ number_format(($ingredient->getLatestPrice()?->price_per_unit ?? 0) / max($ingredient->current_stock, 1), 0, ',', '.') }}</p>
                            </td>
                            <td class="px-6 py-4">
                                @if ($ingredient->isCritical())
                                    <span class="px-2 py-1 rounded-full bg-[#C85A54] bg-opacity-20 text-white text-xs font-semibold">🔴 KRITIS</span>
                                @elseif ($ingredient->isLow())
                                    <span class="px-2 py-1 rounded-full bg-[#F4C430] bg-opacity-20 text-white text-xs font-semibold">⚠️ MENIPIS</span>
                                @else
                                    <span class="px-2 py-1 rounded-full bg-[#6B9E77] bg-opacity-20 text-white text-xs font-semibold">✓ AMAN</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button onclick="openRestockModal({{ $ingredient->id }}, '{{ addslashes($ingredient->name) }}', {{ (float)$ingredient->current_stock }}, '{{ $ingredient->currentUnit->code ?? 'unit' }}', {{ $ingredient->currentUnit->id ?? 1 }}, {{ $ingredient->getLatestPrice()?->price_per_unit ?? 0 }})" class="rounded-lg shadow-lg px-4 py-2 bg-slate-300 hover:bg-slate-700 text-white">Restock</button>
                                    <button onclick="openHistoryPanel({{ $ingredient->id }})" class="rounded-lg shadow-lg px-4 py-2 bg-sky-300 hover:bg-sky-500 text-white">Riwayat</button>
                                    <button onclick="openEditModal({{ $ingredient->id }})" class="text-sm text-[#D4A574] hover:underline font-medium">Edit</button>
                                    <form action="/ingredients/{{ $ingredient->id }}" method="post">
                                        @csrf
                                        @METHOD('DELETE')
                                        <button type="submit" class="rounded-lg shadow-lg bg-red-500 hover:bg-red-700 text-white px-4 py-2">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-[#8B6F47]">
                                <p>Belum ada bahan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div id="modalContent" class="bg-white rounded-xl max-w-2xl w-full p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-[#3E2723]" id="modalTitle">Tambah Bahan</h2>
                <button type="button" onclick="closeAddModal()" class="text-[#8B6F47] hover:text-[#3E2723] text-2xl leading-none">&times;</button>
            </div>

            <form id="ingredientForm" action="{{ route('ingredients.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" id="ingredientId" name="ingredient_id" value="">
                <input type="hidden" id="formMethod" name="_method" value="POST">

                <!-- Nama Bahan -->
                <div>
                    <label class="block text-sm font-medium text-[#3E2723] mb-2">Nama bahan</label>
                    <input type="text" name="name" placeholder="Gula Pasir" required class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                </div>

                <!-- Row: Kategori -->
                <div>
                    <label class="block text-sm font-medium text-[#3E2723] mb-2">Kategori</label>
                    <select name="category" class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                        <option value="">-- Pilih Kategori --</option>
                        <option value="Bahan Baku">Bahan Baku</option>
                        <option value="Topping">Topping</option>
                        <option value="Perisa">Perisa</option>
                        <option value="Pewarna">Pewarna</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <!-- Satuan Dasar -->
                <div>
                    <label class="block text-sm font-medium text-[#3E2723] mb-2">Satuan dasar (stok & resep)</label>
                    <div class="flex items-center gap-2">
                        <input type="number" name="purchase_quantity" id="purchaseQty" value="1" min="1" step="1" required
                            class="w-20 flex-shrink-0 px-2 py-2 border border-[#D4A574] rounded-lg focus:outline-none focus:border-[#D4A574] text-center text-sm font-semibold text-[#3E2723]">
                        <div class="flex gap-1 flex-wrap">
                            <button type="button" onclick="selectUnit('g', this)" class="px-3 py-2 rounded-lg border-2 border-[#D4A574] bg-[#D4A574] text-white text-sm font-medium whitespace-nowrap">g</button>
                            <button type="button" onclick="selectUnit('ml', this)" class="px-3 py-2 rounded-lg border-2 border-[#E8DCC8] text-[#8B6F47] text-sm font-medium hover:bg-[#F5DEB3] transition whitespace-nowrap">ml</button>
                            <button type="button" onclick="selectUnit('pcs', this)" class="px-3 py-2 rounded-lg border-2 border-[#E8DCC8] text-[#8B6F47] text-sm font-medium hover:bg-[#F5DEB3] transition whitespace-nowrap">pcs</button>
                            <button type="button" onclick="selectUnit('bh', this)" class="px-3 py-2 rounded-lg border-2 border-[#E8DCC8] text-[#8B6F47] text-sm font-medium hover:bg-[#F5DEB3] transition whitespace-nowrap">bh</button>
                            <button type="button" onclick="selectUnit('btr', this)" class="px-3 py-2 rounded-lg border-2 border-[#E8DCC8] text-[#8B6F47] text-sm font-medium hover:bg-[#F5DEB3] transition whitespace-nowrap">btr</button>
                        </div>
                    </div>
                    <p class="text-xs text-[#8B6F47] mt-1">Jumlah per pembelian · Contoh: 250 g / Rp 13.500</p>
                    <input type="hidden" name="base_unit_id" id="baseUnitId" value="{{ $units->firstWhere('code', 'gram')?->id ?? 1 }}">
                </div>

                <!-- Row: Harga Beli & Satuan Beli -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-[#3E2723] mb-2">Harga beli (Rp)</label>
                        <input type="number" name="price_per_unit" placeholder="13500" step="1" required class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                        <p class="text-xs text-[#8B6F47] mt-1">Harga untuk <span id="priceHint">1 g</span></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[#3E2723] mb-2">Satuan beli</label>
                        <select name="current_unit_id" required class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->code }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Row: Stok Saat Ini & Satuan -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-[#3E2723] mb-2">Stok saat ini</label>
                        <input type="number" name="current_stock" placeholder="0" step="1" required class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[#3E2723] mb-2">Satuan</label>
                        <input type="text" id="selectedUnit" name="stock_unit_label" placeholder="gram"
                            class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg bg-[#FAF8F3] focus:outline-none text-sm text-[#8B6F47]" readonly>
                    </div>
                </div>

                <!-- Row: Batas Minimum & Satuan -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-[#3E2723] mb-2">Batas minimum (safety stock)</label>
                        <input type="number" name="minimum_threshold" placeholder="0" step="1" required class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[#3E2723] mb-2">Satuan</label>
                        <input type="text" id="selectedUnitMin" placeholder="gram"
                            class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg bg-[#FAF8F3] focus:outline-none text-sm text-[#8B6F47]" readonly>
                        <p class="text-xs text-[#8B6F47] mt-1">(satuan dasar)</p>
                    </div>
                </div>

                <!-- Metode Bayar -->
                <div>
                    <label class="block text-sm font-medium text-[#3E2723] mb-2">Metode Bayar <span class="text-xs text-[#8B6F47]">(opsional)</span></label>
                    <select name="supplier_name" class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                        <option value="">-- Pilih Metode --</option>
                        <option value="Cash">Cash</option>
                        <option value="Cashless">Cashless</option>
                    </select>
                </div>

                <!-- Supplier / Tempat Beli -->
                <div>
                    <label class="block text-sm font-medium text-[#3E2723] mb-2">Supplier / Tempat Beli <span class="text-xs text-[#8B6F47]">(opsional)</span></label>
                    <input type="text" name="store_name" placeholder="Nama toko / supplier..." class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                </div>

                <!-- Buttons -->
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="closeAddModal()" class="flex-1 px-4 py-2 border border-[#E8DCC8] rounded-lg text-[#8B6F47] font-medium hover:bg-[#FAF8F3] transition">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-[#D4A574] text-white rounded-lg font-medium hover:bg-opacity-90 transition">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Restock Modal -->
    <div id="restockModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-md w-full p-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-1">
                <h2 class="text-xl font-bold text-[#3E2723]">Restock Bahan</h2>
                <button type="button" onclick="closeRestockModal()" class="text-[#8B6F47] hover:text-[#3E2723] text-2xl leading-none">&times;</button>
            </div>
            <p id="restockIngredientName" class="text-sm text-[#8B6F47] mb-5"></p>

            <!-- Tab: Stok Masuk / Stok Keluar -->
            <div class="flex rounded-lg overflow-hidden border border-[#E8DCC8] mb-5">
                <button id="tabIn" type="button" onclick="switchRestockTab('in')"
                    class="flex-1 py-2 text-sm font-semibold transition bg-[#3E2723] text-white">
                    + Stok Masuk
                </button>
                <button id="tabOut" type="button" onclick="switchRestockTab('out')"
                    class="flex-1 py-2 text-sm font-semibold transition bg-white text-[#8B6F47]">
                    − Stok Keluar
                </button>
            </div>

            <form id="restockForm" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="type" id="restockType" value="in">

                <!-- Jumlah + Satuan -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label id="restockQtyLabel" class="block text-sm font-medium text-[#3E2723] mb-2">Jumlah masuk</label>
                        <input type="number" name="quantity" id="restockQty" placeholder="0" step="0.01" min="0.01" required
                            class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[#3E2723] mb-2">Satuan</label>
                        <select name="unit_id" id="restockUnitId" class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->code }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Preview stok sebelum/sesudah -->
                <div id="restockPreview" class="bg-[#FAF8F3] border border-[#E8DCC8] rounded-lg px-4 py-3 text-sm">
                    <span id="previewBefore" class="text-[#3E2723] font-medium">0</span>
                    <span class="text-[#8B6F47] mx-1">→</span>
                    <span id="previewAfter" class="text-[#3E2723] font-bold">0</span>
                    <br>
                    <span id="previewDelta" class="text-[#6B9E77] text-xs">+0 (Stok setelah)</span>
                </div>

                <!-- Harga baru (hanya tampil saat Stok Masuk) -->
                <div id="restockPriceSection">
                    <label class="block text-sm font-medium text-[#3E2723] mb-2">Harga baru per satuan beli / kg (Rp)</label>
                    <input type="number" name="price_per_unit" id="restockPrice" placeholder="0" step="0.01" min="0"
                        class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                </div>

                <!-- Metode Bayar (hanya saat Stok Masuk) -->
                <div id="restockSupplierSection">
                    <label class="block text-sm font-medium text-[#3E2723] mb-2">Metode Bayar <span class="text-xs text-[#8B6F47]">(opsional)</span></label>
                    <select name="supplier_name" id="restockSupplier"
                        class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                        <option value="">-- Pilih Metode --</option>
                        <option value="Cash">Cash</option>
                        <option value="Cashless">Cashless</option>
                    </select>
                    <div class="mt-3">
                        <label class="block text-sm font-medium text-[#3E2723] mb-2">Supplier / Tempat Beli <span class="text-xs text-[#8B6F47]">(opsional)</span></label>
                        <input type="text" name="store_name" id="restockStoreName" placeholder="Nama toko / supplier..."
                            class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                    </div>
                </div>

                <!-- Catatan -->
                <div>
                    <label class="block text-sm font-medium text-[#3E2723] mb-2">Catatan <span class="text-xs text-[#8B6F47]">(opsional)</span></label>
                    <input type="text" name="notes" id="restockNotes" placeholder="mis. dipakai untuk tester..."
                        class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                </div>

                <!-- Buttons -->
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeRestockModal()" class="flex-1 px-4 py-2 border border-[#E8DCC8] rounded-lg text-[#8B6F47] font-medium hover:bg-[#FAF8F3] transition">Batal</button>
                    <button type="submit" id="restockSubmitBtn" class="flex-1 px-4 py-2 bg-[#3E2723] text-white rounded-lg font-medium hover:bg-opacity-90 transition">Stok Masuk</button>
                </div>
            </form>
        </div>
    </div>

    <!-- History Panel (slide-in from right) -->
    <div id="historyOverlay" class="hidden fixed inset-0 bg-black bg-opacity-30 z-40" onclick="closeHistoryPanel()"></div>
    <div id="historyPanel" class="fixed top-0 right-0 h-full w-full max-w-sm bg-white shadow-2xl z-50 transform translate-x-full transition-transform duration-300 flex flex-col">
        <!-- Header -->
        <div class="px-6 py-5 border-b border-[#E8DCC8]">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-lg font-bold text-[#3E2723]">Riwayat Keluar-Masuk</h2>
                    <p id="historySubtitle" class="text-sm text-[#8B6F47] mt-0.5"></p>
                </div>
                <button onclick="closeHistoryPanel()" class="text-[#8B6F47] hover:text-[#3E2723] text-2xl leading-none ml-4">&times;</button>
            </div>

            <!-- Summary cards -->
            <div class="grid grid-cols-2 gap-3 mt-4">
                <div class="bg-[#F0FFF4] border border-[#6B9E77] rounded-lg px-4 py-2">
                    <p class="text-xs text-[#6B9E77] font-semibold uppercase tracking-wide">Total Masuk</p>
                    <p id="historyTotalIn" class="text-[#3E2723] font-bold mt-0.5">+0</p>
                </div>
                <div class="bg-[#FFF5F5] border border-[#C85A54] rounded-lg px-4 py-2">
                    <p class="text-xs text-[#C85A54] font-semibold uppercase tracking-wide">Total Keluar</p>
                    <p id="historyTotalOut" class="text-[#3E2723] font-bold mt-0.5">-0</p>
                </div>
            </div>
        </div>

        <!-- Body: entries list -->
        <div class="flex-1 overflow-y-auto px-6 py-4">
            <div id="historyLoading" class="text-center py-12 text-[#8B6F47]">
                <svg class="animate-spin w-6 h-6 mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Memuat riwayat...
            </div>
            <div id="historyEntries" class="hidden space-y-1"></div>
            <div id="historyEmpty" class="hidden text-center py-12 text-[#8B6F47] text-sm">Belum ada riwayat</div>
        </div>
    </div>

    <script>
        function selectUnit(unit, btn) {
            const allButtons = btn.parentElement.querySelectorAll('button');
            allButtons.forEach(b => {
                b.classList.remove('border-[#D4A574]', 'bg-[#D4A574]', 'text-white');
                b.classList.add('border-[#E8DCC8]', 'text-[#8B6F47]', 'hover:bg-[#F5DEB3]');
            });
            btn.classList.remove('border-[#E8DCC8]', 'text-[#8B6F47]', 'hover:bg-[#F5DEB3]');
            btn.classList.add('border-[#D4A574]', 'bg-[#D4A574]', 'text-white');
            
            document.getElementById('selectedUnit').value = unit;
            if (document.getElementById('selectedUnitMin')) {
                document.getElementById('selectedUnitMin').value = unit;
            }
            // Update price hint
            const qty = document.getElementById('purchaseQty')?.value || 1;
            if (document.getElementById('priceHint')) {
                document.getElementById('priceHint').textContent = `${qty} ${unit}`;
            }
            // Unit IDs match the seeder: gram=1, kg=2, ml=3, liter=4, pcs=5, bh=7, btr=8
            const unitMap = {
                'g':   {{ $units->firstWhere('code', 'gram')?->id ?? 1 }},
                'kg':  {{ $units->firstWhere('code', 'kg')?->id ?? 2 }},
                'ml':  {{ $units->firstWhere('code', 'ml')?->id ?? 3 }},
                'pcs': {{ $units->firstWhere('code', 'pcs')?->id ?? 5 }},
                'bh':  {{ $units->firstWhere('code', 'bh')?->id ?? 7 }},
                'btr': {{ $units->firstWhere('code', 'btr')?->id ?? 8 }},
            };
            document.getElementById('baseUnitId').value = unitMap[unit] || {{ $units->firstWhere('code', 'gram')?->id ?? 1 }};
        }

        function openAddModal() {
            const form = document.getElementById('ingredientForm');
            form.reset();
            form.action = "{{ route('ingredients.store') }}";
            document.getElementById('ingredientId').value = '';
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('modalTitle').textContent = 'Tambah Bahan';
            document.getElementById('baseUnitId').value = '{{ $units->firstWhere('code', 'gram')?->id ?? 1 }}';
            document.getElementById('selectedUnit').value = 'g';
            if (document.getElementById('selectedUnitMin')) {
                document.getElementById('selectedUnitMin').value = 'g';
            }
            if (document.getElementById('purchaseQty')) {
                document.getElementById('purchaseQty').value = 1;
            }
            if (document.getElementById('priceHint')) {
                document.getElementById('priceHint').textContent = '1 g';
            }

            // Reset unit buttons
            const unitButtons = document.querySelectorAll('button[onclick*="selectUnit"]');
            unitButtons.forEach((btn, idx) => {
                btn.classList.remove('border-[#D4A574]', 'bg-[#D4A574]', 'text-white');
                btn.classList.add('border-[#E8DCC8]', 'text-[#8B6F47]');
                if (idx === 0) {
                    btn.classList.add('border-[#D4A574]', 'bg-[#D4A574]', 'text-white');
                    btn.classList.remove('border-[#E8DCC8]', 'text-[#8B6F47]');
                }
            });

            document.getElementById('addModal').classList.remove('hidden');
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
        }

        function openEditModal(id) {
            fetch(`/ingredients/${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('ingredientId').value = data.id;
                    document.getElementById('formMethod').value = 'PUT';
                    document.getElementById('modalTitle').textContent = 'Edit Bahan';
                    document.querySelector('input[name="name"]').value = data.name;

                    // Populate category dropdown
                    const catSelect = document.querySelector('select[name="category"]');
                    if (catSelect) catSelect.value = data.category ?? '';
                    document.querySelector('input[name="price_per_unit"]').value = Math.round(parseFloat(data.price_per_unit));
                    // Restore purchase_quantity (jumlah per pembelian)
                    if (document.getElementById('purchaseQty')) {
                        document.getElementById('purchaseQty').value = Math.round(parseFloat(data.purchase_quantity ?? 1));
                    }
                    document.querySelector('input[name="current_stock"]').value = Math.round(parseFloat(data.current_stock));
                    document.querySelector('input[name="minimum_threshold"]').value = Math.round(parseFloat(data.minimum_threshold));
                    document.querySelector('select[name="current_unit_id"]').value = data.current_unit_id;

                    if (document.querySelector('select[name="supplier_name"]')) {
                        document.querySelector('select[name="supplier_name"]').value = data.supplier_name ?? '';
                    }
                    if (document.querySelector('input[name="store_name"]')) {
                        document.querySelector('input[name="store_name"]').value = data.store_name ?? '';
                    }

                    const unitMap = {
                        {{ $units->firstWhere('code', 'gram')?->id ?? 1 }}: 'g',
                        {{ $units->firstWhere('code', 'kg')?->id ?? 2 }}:   'kg',
                        {{ $units->firstWhere('code', 'ml')?->id ?? 3 }}:   'ml',
                        {{ $units->firstWhere('code', 'pcs')?->id ?? 5 }}:  'pcs',
                        {{ $units->firstWhere('code', 'bh')?->id ?? 7 }}:   'bh',
                        {{ $units->firstWhere('code', 'btr')?->id ?? 8 }}:  'btr',
                    };
                    const unitCode = unitMap[data.base_unit_id] || 'g';
                    document.getElementById('baseUnitId').value = data.base_unit_id;
                    document.getElementById('selectedUnit').value = unitCode;
                    if (document.getElementById('selectedUnitMin')) {
                        document.getElementById('selectedUnitMin').value = unitCode;
                    }

                    const unitButtons = document.querySelectorAll('button[onclick*="selectUnit"]');
                    unitButtons.forEach(btn => {
                        btn.classList.remove('border-[#D4A574]', 'bg-[#D4A574]', 'text-white');
                        btn.classList.add('border-[#E8DCC8]', 'text-[#8B6F47]');
                        if (btn.textContent.trim() === unitCode) {
                            btn.classList.add('border-[#D4A574]', 'bg-[#D4A574]', 'text-white');
                            btn.classList.remove('border-[#E8DCC8]', 'text-[#8B6F47]');
                        }
                    });

                    const form = document.getElementById('ingredientForm');
                    form.action = `/ingredients/${data.id}`;

                    // Update price hint with restored values
                    if (document.getElementById('priceHint')) {
                        const qty  = document.getElementById('purchaseQty')?.value || 1;
                        const unit = document.getElementById('selectedUnit')?.value || 'g';
                        document.getElementById('priceHint').textContent = `${qty} ${unit}`;
                    }

                    document.getElementById('addModal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Gagal memuat data bahan');
                });
        }

        // Search
        document.getElementById('searchInput').addEventListener('keyup', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(searchTerm) ? '' : 'none';
            });
        });

        // Close when clicking backdrop
        document.getElementById('addModal').addEventListener('click', function(e) {
            if (e.target === this) closeAddModal();
        });

        // ── Restock Modal ──────────────────────────────────────────────────────

        // Unit conversion factors (server-rendered)
        const unitConversionMap = {
            @foreach ($units as $unit)
            {{ $unit->id }}: { code: '{{ $unit->code }}', factor: {{ (float) $unit->conversion_factor }} },
            @endforeach
        };

        let _restockCurrentStock = 0;
        let _restockUnitCode = '';
        let _restockBaseUnitId = null;

        function openRestockModal(id, name, currentStock, unitCode, unitId, lastPrice) {
            _restockCurrentStock = parseFloat(currentStock) || 0;
            _restockUnitCode = unitCode;
            _restockBaseUnitId = unitId;

            document.getElementById('restockIngredientName').textContent = name;
            document.getElementById('restockForm').action = `/ingredients/${id}/restock`;

            // Pre-select unit
            const unitSelect = document.getElementById('restockUnitId');
            if (unitId) unitSelect.value = unitId;

            // Pre-fill last price
            document.getElementById('restockPrice').value = lastPrice > 0 ? lastPrice : '';

            // Reset to "stok masuk" tab
            switchRestockTab('in');

            // Reset fields
            document.getElementById('restockQty').value = '';
            document.getElementById('restockSupplier').value = '';
            if (document.getElementById('restockStoreName')) {
                document.getElementById('restockStoreName').value = '';
            }
            document.getElementById('restockNotes').value = '';
            updateRestockPreview();

            document.getElementById('restockModal').classList.remove('hidden');
        }

        function closeRestockModal() {
            document.getElementById('restockModal').classList.add('hidden');
        }

        function switchRestockTab(type) {
            document.getElementById('restockType').value = type;

            const tabIn  = document.getElementById('tabIn');
            const tabOut = document.getElementById('tabOut');
            const priceSection    = document.getElementById('restockPriceSection');
            const supplierSection = document.getElementById('restockSupplierSection');
            const submitBtn       = document.getElementById('restockSubmitBtn');
            const qtyLabel        = document.getElementById('restockQtyLabel');

            if (type === 'in') {
                tabIn.classList.replace('bg-white', 'bg-[#3E2723]');
                tabIn.classList.replace('text-[#8B6F47]', 'text-white');
                tabOut.classList.replace('bg-[#3E2723]', 'bg-white');
                tabOut.classList.replace('text-white', 'text-[#8B6F47]');
                priceSection.classList.remove('hidden');
                supplierSection.classList.remove('hidden');
                submitBtn.textContent = 'Stok Masuk';
                submitBtn.className = 'flex-1 px-4 py-2 bg-[#3E2723] text-white rounded-lg font-medium hover:bg-opacity-90 transition';
                qtyLabel.textContent = 'Jumlah masuk';
            } else {
                tabOut.classList.replace('bg-white', 'bg-[#C85A54]');
                tabOut.classList.replace('text-[#8B6F47]', 'text-white');
                tabIn.classList.replace('bg-[#3E2723]', 'bg-white');
                tabIn.classList.replace('text-white', 'text-[#8B6F47]');
                priceSection.classList.add('hidden');
                supplierSection.classList.add('hidden');
                submitBtn.textContent = 'Stok Keluar';
                submitBtn.className = 'flex-1 px-4 py-2 bg-[#C85A54] text-white rounded-lg font-medium hover:bg-opacity-90 transition';
                qtyLabel.textContent = 'Jumlah keluar';
            }

            document.getElementById('restockQty').value = '';
            updateRestockPreview();
        }

        function updateRestockPreview() {
            const type       = document.getElementById('restockType').value;
            const qty        = parseFloat(document.getElementById('restockQty').value) || 0;
            const selectedUnitId = parseInt(document.getElementById('restockUnitId').value);

            // Get conversion factor for the selected input unit
            const unitInfo   = unitConversionMap[selectedUnitId] || { code: _restockUnitCode, factor: 1 };
            const qtyInBase  = qty * unitInfo.factor;
            const baseUnit   = unitConversionMap[_restockBaseUnitId] || { code: _restockUnitCode };

            const after = type === 'in'
                ? _restockCurrentStock + qtyInBase
                : Math.max(0, _restockCurrentStock - qtyInBase);

            document.getElementById('previewBefore').textContent = `${_restockCurrentStock} ${baseUnit.code}`;
            document.getElementById('previewAfter').textContent  = `${Math.round(after)} ${baseUnit.code}`;

            const deltaEl = document.getElementById('previewDelta');
            // Show both input qty and converted base qty if different
            const conversionNote = (unitInfo.factor !== 1)
                ? ` (${qty} ${unitInfo.code} = ${Math.round(qtyInBase)} ${baseUnit.code})`
                : '';

            if (type === 'in') {
                deltaEl.textContent = `+${Math.round(qtyInBase)} ${baseUnit.code}${conversionNote}`;
                deltaEl.className = 'text-[#6B9E77] text-xs';
            } else {
                deltaEl.textContent = `−${Math.round(qtyInBase)} ${baseUnit.code}${conversionNote}`;
                deltaEl.className = 'text-[#C85A54] text-xs';
            }
        }

        document.getElementById('restockQty').addEventListener('input', updateRestockPreview);
        document.getElementById('restockUnitId').addEventListener('change', updateRestockPreview);

        // Update price hint when purchase qty changes
        document.getElementById('purchaseQty')?.addEventListener('input', function() {
            const qty  = this.value || 1;
            const unit = document.getElementById('selectedUnit')?.value || 'g';
            if (document.getElementById('priceHint')) {
                document.getElementById('priceHint').textContent = `${qty} ${unit}`;
            }
        });

        // Close restock modal on backdrop click
        document.getElementById('restockModal').addEventListener('click', function(e) {
            if (e.target === this) closeRestockModal();
        });

        // ── History Panel ───────────────────────────────────────────────────────

        function openHistoryPanel(id) {
            // Show overlay and panel
            document.getElementById('historyOverlay').classList.remove('hidden');
            const panel = document.getElementById('historyPanel');
            panel.classList.remove('translate-x-full');
            panel.classList.add('translate-x-0');

            // Reset state
            document.getElementById('historyLoading').classList.remove('hidden');
            document.getElementById('historyEntries').classList.add('hidden');
            document.getElementById('historyEmpty').classList.add('hidden');
            document.getElementById('historySubtitle').textContent = '';
            document.getElementById('historyTotalIn').textContent  = '...';
            document.getElementById('historyTotalOut').textContent = '...';

            fetch(`/ingredients/${id}/history`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('historyLoading').classList.add('hidden');
                    document.getElementById('historySubtitle').textContent =
                        `${data.name} · Stok: ${data.current_stock} ${data.unit}`;
                    document.getElementById('historyTotalIn').textContent =
                        `+${data.total_in} ${data.unit}`;
                    document.getElementById('historyTotalOut').textContent =
                        `-${data.total_out} ${data.unit}`;

                    const container = document.getElementById('historyEntries');
                    container.innerHTML = '';

                    if (!data.entries || data.entries.length === 0) {
                        document.getElementById('historyEmpty').classList.remove('hidden');
                        return;
                    }

                    data.entries.forEach(entry => {
                        const isIn     = entry.type === 'in';
                        const qtySign  = isIn ? `+${entry.quantity}` : `-${entry.quantity}`;
                        const qtyColor = isIn ? 'text-[#6B9E77]' : 'text-[#C85A54]';
                        const badge    = isIn
                            ? `<span class="text-xs px-2 py-0.5 rounded-full bg-[#F0FFF4] text-[#6B9E77] font-semibold">Masuk</span>`
                            : `<span class="text-xs px-2 py-0.5 rounded-full bg-[#FFF5F5] text-[#C85A54] font-semibold">Keluar</span>`;

                        const priceHtml = entry.price > 0
                            ? `<span class="text-xs text-[#8B6F47]">Rp ${Number(entry.price).toLocaleString('id-ID')} / ${entry.unit}</span>`
                            : '';

                        const supplierHtml = entry.supplier_name && entry.supplier_name !== '-'
                            ? `<span class="text-xs text-[#8B6F47]">· 💳 ${entry.supplier_name}</span>`
                            : '';

                        const notesHtml = entry.notes
                            ? `<p class="text-xs text-[#8B6F47] italic mt-0.5">${entry.notes}</p>`
                            : '';

                        container.insertAdjacentHTML('beforeend', `
                            <div class="py-3 border-b border-[#F0EDE6] last:border-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            ${badge}
                                            <span class="text-xs text-[#8B6F47]">${entry.date}</span>
                                        </div>
                                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                                            ${priceHtml}
                                            ${supplierHtml}
                                        </div>
                                        ${notesHtml}
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <p class="font-bold ${qtyColor}">${qtySign} ${entry.unit}</p>
                                        <p class="text-xs text-[#8B6F47]">→ ${Number(entry.balance).toFixed(2)} ${entry.unit}</p>
                                    </div>
                                </div>
                            </div>
                        `);
                    });

                    container.classList.remove('hidden');
                })
                .catch(err => {
                    console.error(err);
                    document.getElementById('historyLoading').classList.add('hidden');
                    document.getElementById('historyEmpty').textContent = 'Gagal memuat riwayat.';
                    document.getElementById('historyEmpty').classList.remove('hidden');
                });
        }

        function closeHistoryPanel() {
            document.getElementById('historyOverlay').classList.add('hidden');
            const panel = document.getElementById('historyPanel');
            panel.classList.add('translate-x-full');
            panel.classList.remove('translate-x-0');
        }
    </script>
@endsection

