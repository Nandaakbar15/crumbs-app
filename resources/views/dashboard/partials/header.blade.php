<header class="bg-white border-b border-[#E8DCC8] shadow-sm sticky top-0 z-40">
                <div class="flex items-center justify-between px-8 py-4">
                    <h2 class="text-2xl font-bold text-[#3E2723]">@yield('page-title', 'Dashboard')</h2>
                    <div class="flex items-center gap-4">
                        <button class="relative p-2 text-[#D4A574] hover:bg-[#E8DCC8] hover:bg-opacity-30 rounded-lg transition">
                            <span class="text-2xl">🔔</span>
                            <span class="absolute top-1 right-1 w-2 h-2 bg-[#C85A54] rounded-full"></span>
                        </button>
                        <div class="flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-[#E8DCC8] hover:bg-opacity-30 transition relative user-menu">
                            <span class="text-2xl">👤</span>
                            <span class="text-sm font-medium cursor-pointer">{{ auth()->user()->name ?? 'User' }}</span>
                            
                            <!-- Dropdown Menu -->
                            <div class="absolute right-0 top-full mt-2 w-48 bg-white rounded-lg border border-[#E8DCC8] shadow-md opacity-0 invisible user-dropdown transition z-50" style="transition: opacity 0.2s, visibility 0.2s;">
                                <div class="p-2 border-b border-[#E8DCC8]">
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
            </header>