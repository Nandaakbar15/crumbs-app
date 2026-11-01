<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUMBS - Pastry & Bakery Inventory System</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-[#FAF8F3] text-[#3E2723]">
    <!-- Top Navigation Bar -->
    <nav class="bg-[#8B5A2B] text-white shadow-md sticky top-0 z-50">
        <div class="max-w-full mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <!-- Logo & Brand -->
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.jpeg') }}" alt="2pm CRUMBS Logo" class="h-10 w-auto rounded-lg">
                    <div>
                        <h1 class="text-xl font-bold">2pm CRUMBS</h1>
                        <span class="text-xs text-[#FFD7A8]">INVENTARIS & PRODUKSI DAPUS</span>
                    </div>
                </div>

                <!-- Navigation Menu -->
                <div class="hidden lg:flex items-center gap-1">
                    <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-lg text-sm font-medium hover:bg-[#A0522D] transition">
                        📊 Ringkasan
                    </a>
                    <a href="{{ route('ingredients.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium hover:bg-[#A0522D] transition">
                        📦 Stok Bahan
                    </a>
                    <a href="{{ route('recipes.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium hover:bg-[#A0522D] transition">
                        📖 Katalog Resep
                    </a>
                    <a href="{{ route('batches.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium hover:bg-[#A0522D] transition">
                        🍪 Batch & HPP
                    </a>
                    <a href="{{ route('shopping-lists.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium hover:bg-[#A0522D] transition">
                        📋 Daftar Belanja
                    </a>
                    <a href="{{ route('waste-loss.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium hover:bg-[#A0522D] transition">
                        ⚠️ Waste & Loss
                    </a>
                    <a href="{{ route('laporan-penjualan.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium hover:bg-[#A0522D] transition">
                        📊 Laporan
                    </a>
                </div>

                <!-- Right Section: ID, Language, User -->
                <div class="flex items-center gap-4">
                    <span class="hidden sm:inline px-2 py-1 rounded-lg bg-[#D4A574] text-xs font-semibold">ID</span>
                    <span class="hidden sm:inline text-xs text-[#FFD7A8]">EN</span>
                    
                    <!-- CTA Button -->
                    <a href="{{ route('batches.index') }}" class="px-3 py-2 rounded-full bg-[#D4A574] text-white text-sm font-semibold hover:bg-opacity-90 transition">
                        🍪 Catat Batch
                    </a>

                    <!-- User Menu -->
                    <div class="relative user-menu">
                        <button class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-[#A0522D] transition cursor-pointer">
                            <span class="text-lg">👤</span>
                            <span class="text-sm font-medium hidden sm:inline">{{ auth()->user()->name ?? 'User' }}</span>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div class="absolute right-0 top-full mt-1 w-48 bg-white text-[#3E2723] rounded-lg border border-[#E8DCC8] shadow-md opacity-0 invisible user-dropdown transition z-50" style="transition: opacity 0.2s, visibility 0.2s;">
                            <div class="p-3 border-b border-[#E8DCC8]">
                                <p class="text-xs text-[#8B6F47]">Logged in as</p>
                                <p class="font-semibold text-[#3E2723]">{{ auth()->user()->name }}</p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}" class="p-2">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-[#8B6F47] hover:bg-[#E8DCC8] hover:bg-opacity-20 rounded transition">
                                    🚪 Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1">
        <div class="max-w-full mx-auto p-6 lg:p-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-[#6B9E77] bg-opacity-10 border border-[#6B9E77] text-[#3E2723] rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 bg-[#C85A54] bg-opacity-10 border border-[#C85A54] text-[#3E2723] rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Toast Notifications (optional) -->
    <div id="toast-container" class="fixed bottom-4 right-4 space-y-2 z-50"></div>

    @vite('resources/js/app.js')

    <script>
        // User menu dropdown handler
        document.addEventListener('DOMContentLoaded', function() {
            const userMenu = document.querySelector('.user-menu');
            const userDropdown = document.querySelector('.user-dropdown');
            
            if (userMenu && userDropdown) {
                userMenu.addEventListener('mouseenter', function() {
                    userDropdown.classList.remove('opacity-0', 'invisible');
                    userDropdown.classList.add('opacity-100', 'visible');
                });
                
                userMenu.addEventListener('mouseleave', function() {
                    userDropdown.classList.add('opacity-0', 'invisible');
                    userDropdown.classList.remove('opacity-100', 'visible');
                });
            }
        });
    </script>
</body>
</html>
