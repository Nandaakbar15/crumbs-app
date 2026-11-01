@extends('dashboard.layouts.main')

@section('page-title', '')

@section('content')
    <!-- Hero Section -->
    <div class="mb-8 rounded-2xl bg-gradient-to-r from-[#8B5A2B] to-[#A0522D] p-8 text-white relative overflow-hidden">
        <!-- Decorative pattern -->
        <div class="absolute right-0 top-0 opacity-10 text-8xl">🥐</div>
        
        <div class="relative z-10 max-w-2xl">
            <p class="text-xs font-semibold text-[#FFD7A8] mb-2 uppercase tracking-widest">2PM CRUMBS - INVENTARIS & PRODUKSI DAPUS</p>
            <h1 class="text-4xl font-bold mb-3">Selamat datang di dapur</h1>
            <p class="text-[#F5DEB3] mb-6">Pantau stok, biaya produksi, dan margin dalam satu layar</p>
            <div class="flex gap-3">
                <a href="{{ route('batches.index') }}" class="px-4 py-2 rounded-full bg-[#D4A574] text-white text-sm font-semibold hover:bg-opacity-90 transition flex items-center gap-2">
                    <span>🍪</span>
                    <span>Catat Batch</span>
                </a>
                <a href="{{ route('shopping-lists.index') }}" class="px-4 py-2 rounded-full border border-[#FFD7A8] text-[#FFD7A8] text-sm font-semibold hover:bg-white hover:bg-opacity-10 transition flex items-center gap-2">
                    <span>→</span>
                    <span>Lihat daftar belanja</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Actions Menu -->
    <div class="mb-8 bg-white rounded-xl shadow-sm p-6 border border-[#E8DCC8]">
        <div class="flex gap-3 overflow-x-auto pb-2">
            <button class="px-4 py-2 rounded-lg bg-[#D4A574] text-white text-sm font-medium whitespace-nowrap hover:bg-opacity-90 transition flex items-center gap-2">
                <span>�</span>
                <span>Isi stok & harga bahan</span>
            </button>
            <button class="px-4 py-2 rounded-lg bg-[#F5DEB3] text-[#8B5A2B] text-sm font-medium whitespace-nowrap hover:bg-opacity-80 transition flex items-center gap-2">
                <span>🍳</span>
                <span>Buat resep produksi</span>
            </button>
            <button class="px-4 py-2 rounded-lg bg-[#F5DEB3] text-[#8B5A2B] text-sm font-medium whitespace-nowrap hover:bg-opacity-80 transition flex items-center gap-2">
                <span>🍪</span>
                <span>Catat batch produksi</span>
            </button>
            <button class="px-4 py-2 rounded-lg bg-[#F5DEB3] text-[#8B5A2B] text-sm font-medium whitespace-nowrap hover:bg-opacity-80 transition flex items-center gap-2">
                <span>🔍</span>
                <span>Isi journal tersuai</span>
            </button>
            <button class="px-4 py-2 rounded-lg bg-[#F5DEB3] text-[#8B5A2B] text-sm font-medium whitespace-nowrap hover:bg-opacity-80 transition flex items-center gap-2">
                <span>�</span>
                <span>Lihat laporan</span>
            </button>
        </div>
    </div>

    <!-- Summary Cards - 4 Columns -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Nilai Stok -->
        <div class="bg-white rounded-xl border border-[#E8DCC8] p-6 shadow-sm hover:shadow-md transition">
            <div class="flex items-start justify-between mb-3">
                <p class="text-xs font-medium text-[#8B6F47] uppercase tracking-wide">NILAI STOK</p>
                <span class="text-lg">📦</span>
            </div>
            <h3 class="text-2xl font-bold text-[#3E2723] mb-1">Rp 0</h3>
            <p class="text-xs text-[#8B6F47]">0 bahan</p>
        </div>

        <!-- Bahan Menipis -->
        <div class="bg-white rounded-xl border border-[#E8DCC8] p-6 shadow-sm hover:shadow-md transition">
            <div class="flex items-start justify-between mb-3">
                <p class="text-xs font-medium text-[#8B6F47] uppercase tracking-wide">BAHAN MENIPIS</p>
                <span class="text-lg">⚠️</span>
            </div>
            <h3 class="text-2xl font-bold text-[#3E2723] mb-1">0</h3>
            <p class="text-xs text-[#8B6F47]">0 Kritis - 0 rendah</p>
        </div>

        <!-- Batch Bulan Ini -->
        <div class="bg-white rounded-xl border border-[#E8DCC8] p-6 shadow-sm hover:shadow-md transition">
            <div class="flex items-start justify-between mb-3">
                <p class="text-xs font-medium text-[#8B6F47] uppercase tracking-wide">BATCH BULAN INI</p>
                <span class="text-lg">🍪</span>
            </div>
            <h3 class="text-2xl font-bold text-[#3E2723] mb-1">0</h3>
            <p class="text-xs text-[#8B6F47]">pcs diproduski</p>
        </div>

        <!-- Waste Bulan Ini -->
        <div class="bg-white rounded-xl border border-[#E8DCC8] p-6 shadow-sm hover:shadow-md transition">
            <div class="flex items-start justify-between mb-3">
                <p class="text-xs font-medium text-[#8B6F47] uppercase tracking-wide">WASTE BULAN INI</p>
                <span class="text-lg">⚠️</span>
            </div>
            <h3 class="text-2xl font-bold text-[#3E2723] mb-1">Rp 0</h3>
            <p class="text-xs text-[#8B6F47]">Waste & Loss</p>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- HPP vs Harga Jual per pcs -->
        <div class="bg-white rounded-xl border border-[#E8DCC8] p-6 shadow-sm">
            <h2 class="text-lg font-bold text-[#3E2723] mb-6">HPP vs Harga Jual per pcs</h2>
            <div class="space-y-4">
                @if ($topMarginProducts->count() > 0)
                    @foreach ($topMarginProducts->take(3) as $product)
                        <div class="flex items-center justify-between pb-4 border-b border-[#E8DCC8] last:border-0">
                            <div>
                                <p class="font-medium text-[#3E2723]">{{ $product->name }}</p>
                                <p class="text-xs text-[#8B6F47]">HPP: Rp {{ number_format($product->getLatestHPP()?->hpp_per_unit ?? 0, 0, ',', '.') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-[#3E2723]">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</p>
                                <p class="text-xs text-[#6B9E77] font-medium">{{ $product->getLatestHPP()?->margin_percentage ?? 0 }}%</p>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-center py-8 text-[#8B6F47]">Tidak ada produk</p>
                @endif
            </div>
        </div>

        <!-- Peringatan Stok -->
        <div class="bg-white rounded-xl border border-[#E8DCC8] p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-[#3E2723]">Peringatan Stok</h2>
                <a href="{{ route('ingredients.index') }}" class="text-xs text-[#D4A574] hover:underline font-semibold">Lihat daftar belanja</a>
            </div>
            <div class="space-y-3">
                @if ($criticalStocks->count() > 0 || $lowStocks->count() > 0)
                    @forelse ($criticalStocks as $stock)
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-[#C85A54] bg-opacity-10 border border-[#C85A54]">
                            <span class="text-lg">🔴</span>
                            <div class="flex-1 text-sm">
                                <p class="font-medium text-[#3E2723]">{{ $stock->name }}</p>
                                <p class="text-xs text-[#8B6F47]">Stok: {{ $stock->current_stock }} {{ $stock->currentUnit->code ?? 'unit' }}</p>
                            </div>
                        </div>
                    @empty
                        @forelse ($lowStocks as $stock)
                            <div class="flex items-center gap-3 p-3 rounded-lg bg-[#F4C430] bg-opacity-10 border border-[#F4C430]">
                                <span class="text-lg">⚠️</span>
                                <div class="flex-1 text-sm">
                                    <p class="font-medium text-[#3E2723]">{{ $stock->name }}</p>
                                    <p class="text-xs text-[#8B6F47]">Stok: {{ $stock->current_stock }} {{ $stock->currentUnit->code ?? 'unit' }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 rounded-lg bg-[#6B9E77] bg-opacity-10 border border-[#6B9E77]">
                                <p class="text-sm text-white font-medium">✓ Semua bahan dalam stok aman</p>
                            </div>
                        @endforelse
                    @endforelse
                @else
                    <div class="p-4 rounded-lg bg-[#6B9E77] bg-opacity-10 border border-[#6B9E77]">
                        <p class="text-sm text-[#6B9E77] font-medium">✓ Semua bahan dalam stok aman</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Batch Terakhir (Recent Batches) -->
    <div class="bg-white rounded-xl border border-[#E8DCC8] p-6 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-bold text-[#3E2723]">Batch Terakhir</h2>
            <a href="{{ route('batches.index') }}" class="text-xs text-[#D4A574] hover:underline font-semibold">Riwayat Produksi →</a>
        </div>
        
        @if ($activeBatches->count() > 0)
            <div class="overflow-x-auto pb-2">
                <div class="flex gap-4 min-w-min">
                    @foreach ($activeBatches as $batch)
                        <div class="flex-shrink-0 w-48 bg-gradient-to-br from-[#FAF8F3] to-[#F5DEB3] rounded-lg border border-[#E8DCC8] p-4 hover:shadow-md transition">
                            <div class="text-xs text-[#8B6F47] mb-1 font-medium">{{ $batch->production_date->format('d M d/m/y') }}</div>
                            <h3 class="font-bold text-[#3E2723] text-sm mb-3 line-clamp-2">{{ $batch->recipe->name ?? 'Unknown Recipe' }}</h3>
                            <div class="space-y-2 text-xs">
                                <div class="flex justify-between">
                                    <span class="text-[#8B6F47]">1x</span>
                                    <span class="font-semibold text-[#3E2723]">{{ $batch->quantity ?? 24 }} pcs</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-[#8B6F47]">Rp</span>
                                    <span class="font-semibold text-[#D4A574]">{{ number_format($batch->getTotalMaterialCost() ?? 0, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="text-center py-8 text-[#8B6F47]">
                <p>Belum ada batch yang diproses</p>
            </div>
        @endif
    </div>
@endsection