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
        <div class="flex gap-3 flex-wrap">
            <button class="px-3 py-1 rounded-full bg-[#D4A574] text-white text-sm font-medium">Semua</button>
            <button class="px-3 py-1 rounded-full bg-[#F5DEB3] text-[#8B5A2B] text-sm font-medium hover:bg-opacity-80 transition">Kritis 0</button>
            <button class="px-3 py-1 rounded-full bg-[#F5DEB3] text-[#8B5A2B] text-sm font-medium hover:bg-opacity-80 transition">Menipis 0</button>
            <button class="px-3 py-1 rounded-full bg-[#F5DEB3] text-[#8B5A2B] text-sm font-medium hover:bg-opacity-80 transition">Aman 0</button>
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
                        <th class="px-6 py-4 text-left text-sm font-semibold text-[#3E2723]">Nilai</th>
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
                            <td class="px-6 py-4 text-sm text-[#8B6F47]">-</td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-[#3E2723]">{{ number_format($ingredient->current_stock, 0) }}</p>
                                <p class="text-xs text-[#8B6F47]">{{ $ingredient->currentUnit->code ?? 'unit' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-[#3E2723]">{{ number_format($ingredient->minimum_threshold, 0) }}</p>
                                <p class="text-xs text-[#8B6F47]">{{ $ingredient->currentUnit->code ?? 'unit' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-[#3E2723]">Rp {{ number_format($ingredient->getLatestPrice()?->price_per_unit ?? 0, 0, ',', '.') }}</p>
                                <p class="text-xs text-[#8B6F47]">per {{ $ingredient->baseUnit->code ?? 'unit' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-[#D4A574]">Rp {{ number_format(($ingredient->getLatestPrice()?->price_per_unit ?? 0) / max($ingredient->current_stock, 1), 0, ',', '.') }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-[#3E2723]">Rp {{ number_format(($ingredient->getLatestPrice()?->price_per_unit ?? 0) * $ingredient->current_stock, 0, ',', '.') }}</p>
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
                            <td colspan="9" class="px-6 py-8 text-center text-[#8B6F47]">
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

                <!-- Row: Kategori & Satuan Dasar -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-[#3E2723] mb-2">Kategori</label>
                        <input type="text" name="category" placeholder="Umum" class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[#3E2723] mb-2">Satuan dasar (stok & resep)</label>
                        <div class="flex items-center gap-2">
                            <input type="number" placeholder="1" min="1" class="w-16 flex-shrink-0 px-2 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574] text-center" readonly value="1">
                            <div class="flex gap-1 flex-shrink-0">
                                <button type="button" onclick="selectUnit('g', this)" class="px-3 py-2 rounded-lg border-2 border-[#D4A574] bg-[#D4A574] text-white text-sm font-medium whitespace-nowrap">g</button>
                                <button type="button" onclick="selectUnit('ml', this)" class="px-3 py-2 rounded-lg border-2 border-[#E8DCC8] text-[#8B6F47] text-sm font-medium hover:bg-[#F5DEB3] transition whitespace-nowrap">ml</button>
                                <button type="button" onclick="selectUnit('pcs', this)" class="px-3 py-2 rounded-lg border-2 border-[#E8DCC8] text-[#8B6F47] text-sm font-medium hover:bg-[#F5DEB3] transition whitespace-nowrap">pcs</button>
                            </div>
                        </div>
                        <input type="hidden" name="base_unit_id" id="baseUnitId" value="1">
                    </div>
                </div>

                <!-- Row: Harga Beli & Satuan Beli -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-[#3E2723] mb-2">Harga beli per satuan beli (Rp)</label>
                        <input type="number" name="price_per_unit" placeholder="17500" step="0.01" required class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
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
                        <input type="number" name="current_stock" placeholder="0" step="0.01" required class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[#3E2723] mb-2">Satuan</label>
                        <select class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]" disabled>
                            <option id="selectedUnit">gram</option>
                        </select>
                    </div>
                </div>

                <!-- Row: Batas Minimum & Satuan -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-[#3E2723] mb-2">Batas minimum (safety stock)</label>
                        <input type="number" name="minimum_threshold" placeholder="0" step="0.01" required class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[#3E2723] mb-2">Satuan</label>
                        <select class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]" disabled>
                            <option>gram</option>
                        </select>
                    </div>
                </div>

                <!-- Supplier -->
                <div>
                    <label class="block text-sm font-medium text-[#3E2723] mb-2">Supplier / tempat beli <span class="text-xs text-[#8B6F47]">(opsional)</span></label>
                    <select name="supplier_id" class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                        <option value="">-- Pilih Supplier --</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Buttons -->
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="closeAddModal()" class="flex-1 px-4 py-2 border border-[#E8DCC8] rounded-lg text-[#8B6F47] font-medium hover:bg-[#FAF8F3] transition">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-[#D4A574] text-white rounded-lg font-medium hover:bg-opacity-90 transition">Simpan</button>
                </div>
            </form>
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
            
            document.getElementById('selectedUnit').textContent = unit;
            // Unit IDs match the seeder: gram=1, ml=3, pcs=5
            const unitMap = { 'g': {{ $units->firstWhere('code', 'gram')?->id ?? 1 }}, 'ml': {{ $units->firstWhere('code', 'ml')?->id ?? 3 }}, 'pcs': {{ $units->firstWhere('code', 'pcs')?->id ?? 5 }} };
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
            document.getElementById('selectedUnit').textContent = 'g';

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
                    document.querySelector('input[name="price_per_unit"]').value = data.price_per_unit;
                    document.querySelector('input[name="current_stock"]').value = data.current_stock;
                    document.querySelector('input[name="minimum_threshold"]').value = data.minimum_threshold;
                    document.querySelector('select[name="current_unit_id"]').value = data.current_unit_id;

                    if (document.querySelector('select[name="supplier_id"]')) {
                        document.querySelector('select[name="supplier_id"]').value = data.supplier_id ?? '';
                    }

                    const unitMap = { {{ $units->firstWhere('code', 'gram')?->id ?? 1 }}: 'g', {{ $units->firstWhere('code', 'ml')?->id ?? 3 }}: 'ml', {{ $units->firstWhere('code', 'pcs')?->id ?? 5 }}: 'pcs' };
                    const unitCode = unitMap[data.base_unit_id] || 'g';
                    document.getElementById('baseUnitId').value = data.base_unit_id;
                    document.getElementById('selectedUnit').textContent = unitCode;

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
    </script>
@endsection

