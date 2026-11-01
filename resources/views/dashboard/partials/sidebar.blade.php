<aside class="w-56 bg-white border-r border-[#E8DCC8] shadow-sm">
            <!-- Logo -->
            <div class="flex items-center gap-3 p-6 border-b border-[#E8DCC8]">
                <img src="{{ asset('images/logo.jpeg') }}" alt="" srcset="">
            </div>

            <!-- Navigation Menu -->
            <nav class="p-4 space-y-2">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-[#D4A574] bg-opacity-20 text-[#3E2723] font-medium hover:bg-opacity-30 transition">
                    <span class="text-xl">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('ingredients.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-[#8B6F47] font-medium hover:bg-[#E8DCC8] hover:bg-opacity-30 transition">
                    <span class="text-xl">📦</span>
                    <span>Manajemen Stok</span>
                </a>
                <a href="{{ route('recipes.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-[#8B6F47] font-medium hover:bg-[#E8DCC8] hover:bg-opacity-30 transition">
                    <span class="text-xl">📖</span>
                    <span>Katalog Resep</span>
                </a>
                <a href="{{ route('batches.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-[#8B6F47] font-medium hover:bg-[#E8DCC8] hover:bg-opacity-30 transition">
                    <span class="text-xl">🍪</span>
                    <span>Batch Produksi</span>
                </a>
                <a href="{{ route('shopping-lists.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-[#8B6F47] font-medium hover:bg-[#E8DCC8] hover:bg-opacity-30 transition">
                    <span class="text-xl">🛒</span>
                    <span>Shopping List</span>
                </a>
                <a href="{{ route('waste-loss.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-[#8B6F47] font-medium hover:bg-[#E8DCC8] hover:bg-opacity-30 transition">
                    <span class="text-xl">⚠️</span>
                    <span>Waste Tracker</span>
                </a>
                <a href="{{ route('laporan-penjualan.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-[#8B6F47] font-medium hover:bg-[#E8DCC8] hover:bg-opacity-30 transition">
                    <span class="text-xl">📊</span>
                    <span>Laporan Penjualan</span>
                </a>
                <a href="{{ route('analytics.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-[#8B6F47] font-medium hover:bg-[#E8DCC8] hover:bg-opacity-30 transition">
                    <span class="text-xl">📈</span>
                    <span>Analytics</span>
                </a>
            </nav>

            <!-- Bottom Settings -->
            <div class="absolute bottom-0 w-56 p-4 border-t border-[#E8DCC8]">
                <button class="flex items-center gap-3 w-full px-4 py-2 rounded-lg text-[#8B6F47] hover:bg-[#E8DCC8] hover:bg-opacity-20 transition">
                    <span class="text-xl">⚙️</span>
                    <span class="text-sm">Pengaturan</span>
                </button>
            </div>
        </aside>