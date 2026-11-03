@extends('dashboard.layouts.main')

@section('content')
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-[#3E2723] mb-2">Katalog Resep & HPP</h1>
        <p class="text-[#8B6F47]">Food cost dihitung otomatis dari harga bahan terbaru.</p>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-xl border border-[#E8DCC8] p-6 shadow-sm mb-6">
        <div class="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between mb-6">
            <!-- Search Bar -->
            <div class="relative flex-1">
                <svg class="w-5 h-5 absolute left-3 top-3 text-[#D4A574]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input type="text" id="searchInput" placeholder="Cari resep..." class="w-full pl-10 pr-4 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
            </div>

            <!-- Add Button -->
            <button onclick="openAddModal()" class="px-4 py-2 rounded-lg bg-[#D4A574] text-white font-semibold hover:bg-opacity-90 transition flex items-center gap-2 whitespace-nowrap">
                <span>+</span>
                <span>Tambah Resep</span>
            </button>
        </div>
    </div>

    <!-- Empty State or Recipe List -->
    @if ($recipes->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($recipes as $recipe)
                <div class="bg-white rounded-xl border border-[#E8DCC8] shadow-sm hover:shadow-md transition overflow-hidden">
                    <!-- Image Section -->
                    <div class="h-32 bg-gradient-to-br from-[#F5DEB3] to-[#E8DCC8] flex items-center justify-center relative">
                        @if ($recipe->image_url)
                            <img src="{{ $recipe->image_url }}" alt="{{ $recipe->name }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-5xl">🥐</span>
                        @endif
                    </div>

                    <!-- Content Section -->
                    <div class="p-4">
                        <!-- Category & Yield -->
                        <div class="flex items-start justify-between mb-2">
                            <p class="text-xs font-semibold text-[#D4A574] uppercase tracking-wide">
                                {{ strtoupper($recipe->category ?? 'Pastry') }} / {{ number_format($recipe->yield_quantity) }} {{ $recipe->yieldUnit->code ?? 'PCS' }}
                            </p>
                        </div>

                        <!-- Recipe Name -->
                        <h3 class="text-lg font-bold text-[#3E2723] mb-3">{{ $recipe->name }}</h3>

                        <!-- Stats Row -->
                        <div class="space-y-2 mb-4 pb-4 border-b border-[#E8DCC8]">
                            <!-- HPP per PCS -->
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-[#8B6F47] font-medium">HPP / PCS</span>
                                @php
                                    $hppPerPcs = ($recipe->getTotalMaterialCost() ?? 0) / max($recipe->yield_quantity, 1);
                                @endphp
                                <span class="font-bold text-[#3E2723]">Rp {{ number_format($hppPerPcs, 0, ',', '.') }}</span>
                            </div>

                            <!-- Harga Jual per PCS -->
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-[#8B6F47] font-medium">HARGA JUAL PCS</span>
                                <span class="font-bold text-[#D4A574]">Rp {{ number_format($recipe->selling_price, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2">
                            <button onclick="openEditModal({{ $recipe->id }})" class="flex-1 px-3 py-2 rounded-lg bg-[#D4A574] text-white text-sm font-medium hover:bg-opacity-90 transition">
                                Edit
                            </button>
                            <button onclick="viewRecipe({{ $recipe->id }})" class="flex-1 px-3 py-2 rounded-lg border border-[#E8DCC8] text-[#8B6F47] text-sm font-medium hover:bg-[#FAF8F3] transition">
                                Lihat
                            </button>
                            <form action="/recipes/{{ $recipe->id }}" method="post">
                                @csrf
                                @METHOD('DELETE')
                                <button class="rounded-lg shadow-lg text-white bg-red-500 hover:bg-red-700 px-4 py-2">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-xl border border-[#E8DCC8] p-12 shadow-sm text-center">
            <p class="text-lg text-[#8B6F47] mb-4">Belum ada resep</p>
            <p class="text-sm text-[#D4A574]">Klik "Tambah Resep" untuk membuat resep pertama</p>
        </div>
    @endif

    <!-- Add/Edit Modal -->

    <div id="addModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-4xl w-full p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-[#3E2723]" id="modalTitle">Tambah Resep</h2>
                <button type="button" onclick="closeAddModal()" class="text-[#8B6F47] hover:text-[#3E2723] text-2xl leading-none">&times;</button>
            </div>

            <form id="recipeForm" action="{{ route('recipes.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" id="recipeId" name="recipe_id" value="">
                <input type="hidden" id="formMethod" name="_method" value="POST">

                <!-- Two Column Layout -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Nama Produk -->
                    <div>
                        <label class="block text-sm font-medium text-[#3E2723] mb-1">Nama produk</label>
                        <input type="text" name="name" placeholder="Croissant" required class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                    </div>

                    <!-- Kategori -->
                    <div>
                        <label class="block text-sm font-medium text-[#3E2723] mb-1">Kategori</label>
                        <select name="category" class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                            <option value="">Pastry</option>
                            <option value="Bread">Bread</option>
                            <option value="Cake">Cake</option>
                            <option value="Cookie">Cookie</option>
                        </select>
                    </div>
                </div>

                <!-- Hasil per Batch -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-[#3E2723] mb-1">Hasil per batch (pcs)</label>
                        <input type="number" name="yield_quantity" placeholder="12" step="1" required class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[#3E2723] mb-1">&nbsp;</label>
                        <select name="yield_unit_id" required class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->code }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Overhead hidden — selalu 0, field dihilangkan -->
                <input type="hidden" name="overhead_per_batch" value="0">

                <!-- Harga jual -->
                <div>
                    <label class="block text-sm font-medium text-[#3E2723] mb-1">Harga jual per pcs (Rp)</label>
                    <input type="number" name="selling_price" placeholder="25000" step="1" required class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                </div>

                <!-- URL Gambar -->
                <div>
                    <label class="block text-sm font-medium text-[#3E2723] mb-1">URL gambar (opsional)</label>
                    <input type="text" name="image_url" placeholder="https://..." class="w-full px-3 py-2 border border-[#E8DCC8] rounded-lg focus:outline-none focus:border-[#D4A574]">
                </div>

                <!-- Bahan Section -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-sm font-medium text-[#3E2723]">BAHAN</label>
                        <button type="button" onclick="addIngredient()" class="text-xs text-[#D4A574] hover:underline font-semibold">
                            + Tambah bahan
                        </button>
                    </div>

                    <!-- Ingredients Table -->
                    <div class="border border-[#E8DCC8] rounded-lg overflow-hidden">
                        <table class="w-full">
                            <thead class="bg-[#FAF8F3] border-b border-[#E8DCC8]">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-[#3E2723]">Pilih bahan</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-[#3E2723]">Satuan</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-[#3E2723]">Jumlah</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-[#3E2723]">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="ingredientRows">
                                <!-- Rows will be added here -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- HPP Summary -->
                <div class="bg-[#FAF8F3] rounded-lg p-4 grid grid-cols-3 gap-4">
                    <div>
                        <p class="text-xs text-[#8B6F47] uppercase tracking-wide mb-1">HPP / BATCH</p>
                        <p class="text-lg font-bold text-[#3E2723]" id="hppBatch">Rp 0</p>
                    </div>
                    <div>
                        <p class="text-xs text-[#8B6F47] uppercase tracking-wide mb-1">HPP / PCS</p>
                        <p class="text-lg font-bold text-[#3E2723]" id="hppPcs">Rp 0</p>
                    </div>
                    <div>
                        <p class="text-xs text-[#8B6F47] uppercase tracking-wide mb-1">MARGIN</p>
                        <p class="text-lg font-bold text-[#6B9E77]" id="margin">0%</p>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="closeAddModal()" class="flex-1 px-4 py-2 border border-[#E8DCC8] rounded-lg text-[#8B6F47] font-medium hover:bg-[#FAF8F3] transition">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-[#D4A574] text-white rounded-lg font-medium hover:bg-opacity-90 transition">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Detail Panel (slide-in from right) -->
    <div id="detailOverlay" class="hidden fixed inset-0 bg-black bg-opacity-30 z-40" onclick="closeDetailPanel()"></div>
    <div id="detailPanel" class="fixed top-0 right-0 h-full w-full max-w-sm bg-white shadow-2xl z-50 transform translate-x-full transition-transform duration-300 flex flex-col">

        <!-- Header tetap -->
        <div class="px-6 py-5 border-b border-[#E8DCC8] flex items-center justify-between flex-shrink-0">
            <h2 class="text-lg font-bold text-[#3E2723]">Detail Resep</h2>
            <button onclick="closeDetailPanel()" class="text-[#8B6F47] hover:text-[#3E2723] text-2xl leading-none">&times;</button>
        </div>

        <!-- Scroll area -->
        <div class="flex-1 overflow-y-auto">

            <!-- Loading state -->
            <div id="detailLoading" class="flex flex-col items-center justify-center py-16 text-[#8B6F47]">
                <svg class="animate-spin w-6 h-6 mb-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Memuat detail...
            </div>

            <!-- Content -->
            <div id="detailContent" class="hidden">

                <!-- Gambar -->
                <div id="detailImage" class="h-44 bg-gradient-to-br from-[#F5DEB3] to-[#E8DCC8] flex items-center justify-center overflow-hidden flex-shrink-0"></div>

                <div class="px-6 py-5 space-y-5">

                    <!-- Nama & Kategori -->
                    <div>
                        <p id="detailCategory" class="text-xs font-semibold text-[#D4A574] uppercase tracking-wide mb-1"></p>
                        <h3 id="detailName" class="text-2xl font-bold text-[#3E2723]"></h3>
                    </div>

                    <!-- HPP & Harga ringkas -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-[#FAF8F3] rounded-lg px-4 py-3 border border-[#E8DCC8]">
                            <p class="text-xs text-[#8B6F47] font-semibold uppercase tracking-wide mb-1">Harga Jual / pcs</p>
                            <p id="detailSellingPrice" class="text-lg font-bold text-[#D4A574]"></p>
                        </div>
                        <div class="bg-[#FAF8F3] rounded-lg px-4 py-3 border border-[#E8DCC8]">
                            <p class="text-xs text-[#8B6F47] font-semibold uppercase tracking-wide mb-1">Overhead / batch</p>
                            <p id="detailOverhead" class="text-lg font-bold text-[#3E2723]"></p>
                        </div>
                    </div>

                    <!-- Divider -->
                    <hr class="border-[#E8DCC8]">

                    <!-- Daftar Bahan -->
                    <div>
                        <h4 class="text-sm font-bold text-[#3E2723] uppercase tracking-wide mb-3">Bahan-bahan</h4>

                        <div id="detailIngredientTable">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-[#E8DCC8]">
                                        <th class="pb-2 text-left text-xs font-semibold text-[#8B6F47]">Bahan</th>
                                        <th class="pb-2 text-right text-xs font-semibold text-[#8B6F47]">Jumlah</th>
                                        <th class="pb-2 text-right text-xs font-semibold text-[#8B6F47]">Satuan</th>
                                    </tr>
                                </thead>
                                <tbody id="detailIngredients"></tbody>
                            </table>
                        </div>

                        <p id="detailNoIngredients" class="hidden text-sm text-[#8B6F47] italic">Belum ada bahan ditambahkan</p>
                    </div>

                </div>
            </div>
        </div>

        <!-- Footer dengan tombol Edit -->
        <div class="px-6 py-4 border-t border-[#E8DCC8] flex-shrink-0">
            <button id="detailEditBtn" class="w-full px-4 py-2 bg-[#D4A574] text-white rounded-lg font-medium hover:bg-opacity-90 transition">
                Edit Resep
            </button>
        </div>
    </div>

    <script>
        let ingredientCount = 0;

        function openAddModal() {
            // Reset form for new recipe
            const form = document.getElementById('recipeForm');
            form.reset();
            form.action = "{{ route('recipes.store') }}";
            
            document.getElementById('recipeId').value = '';
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('modalTitle').textContent = 'Tambah Resep';
            
            ingredientCount = 0;
            document.getElementById('ingredientRows').innerHTML = '';
            addIngredient();

            document.getElementById('addModal').classList.remove('hidden');

            // Calculate HPP after modal is shown
            setTimeout(() => calculateHPP(), 100);
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
        }

        function addIngredient() {
            ingredientCount++;
            const row = `
                <tr>
                    <td class="px-3 py-2 border-b border-[#E8DCC8]">
                        <select name="ingredients[${ingredientCount}][ingredient_id]" class="w-full px-2 py-1 border border-[#E8DCC8] rounded text-sm ingredient-price" onchange="calculateHPP()">
                            <option value="">-- Pilih bahan --</option>
                            @foreach ($ingredients as $ingredient)
                            <option value="{{ $ingredient->id }}">{{ $ingredient->name }} ({{ $ingredient->baseUnit->code ?? 'unit' }})</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="px-3 py-2 border-b border-[#E8DCC8]">
                        <select name="ingredients[${ingredientCount}][unit_id]" class="w-full px-2 py-1 border border-[#E8DCC8] rounded text-sm">
                            @foreach ($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->code }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="px-3 py-2 border-b border-[#E8DCC8]">
                        <input type="number" name="ingredients[${ingredientCount}][quantity]" placeholder="0" step="1" class="w-full px-2 py-1 border border-[#E8DCC8] rounded text-sm ingredient-quantity" onchange="calculateHPP()" oninput="calculateHPP()">
                    </td>
                    <td class="px-3 py-2 border-b border-[#E8DCC8] text-center">
                        <button type="button" onclick="removeIngredient(this)" class="text-red-500 hover:text-red-700 text-sm">✕</button>
                    </td>
                </tr>
            `;
            document.getElementById('ingredientRows').insertAdjacentHTML('beforeend', row);
        }

        function removeIngredient(btn) {
            btn.closest('tr').remove();
            calculateHPP();
        }

        function calculateHPP() {
            // Get values from form safely
            const sellingPriceInput = document.querySelector('input[name="selling_price"]');
            const yieldQuantityInput = document.querySelector('input[name="yield_quantity"]');

            // If form fields don't exist, return early
            if (!sellingPriceInput || !yieldQuantityInput) {
                return;
            }

            const overhead = 0; // overhead dihilangkan dari form
            const sellingPrice = parseFloat(sellingPriceInput.value) || 0;
            const yield_quantity = parseFloat(yieldQuantityInput.value) || 1;

            // Calculate total ingredient cost (simplified - assuming each ingredient row has a cost)
            let totalIngredientCost = 0;
            document.querySelectorAll('input.ingredient-quantity').forEach(input => {
                // For now, we'll just show 0 since we need to fetch ingredient prices
                // This would need to be enhanced with AJAX calls to get actual prices
            });

            // Calculate HPP per batch
            const hppBatch = totalIngredientCost + overhead;
            
            // Calculate HPP per pcs
            const hppPcs = yield_quantity > 0 ? hppBatch / yield_quantity : 0;
            
            // Calculate margin
            const margin = sellingPrice > 0 ? ((sellingPrice - hppPcs) / sellingPrice * 100) : 0;

            // Update display
            const hppBatchEl = document.getElementById('hppBatch');
            const hppPcsEl = document.getElementById('hppPcs');
            const marginEl = document.getElementById('margin');

            if (hppBatchEl) hppBatchEl.textContent = 'Rp ' + formatCurrency(hppBatch);
            if (hppPcsEl) hppPcsEl.textContent = 'Rp ' + formatCurrency(hppPcs);
            if (marginEl) marginEl.textContent = margin.toFixed(1) + '%';
        }

        function formatCurrency(value) {
            return Math.round(value).toLocaleString('id-ID');
        }

        function openEditModal(id) {
            // Fetch recipe data
            fetch(`/recipes/${id}`)
                .then(response => response.json())
                .then(data => {
                    // Populate form with recipe data
                    document.getElementById('recipeId').value = data.id;
                    document.getElementById('formMethod').value = 'PUT';
                    document.getElementById('modalTitle').textContent = 'Edit Resep';
                    document.querySelector('input[name="name"]').value = data.name;
                    document.querySelector('select[name="category"]').value = data.category || '';
                    document.querySelector('input[name="yield_quantity"]').value = data.yield_quantity;
                    document.querySelector('select[name="yield_unit_id"]').value = data.yield_unit_id;
                    document.querySelector('input[name="selling_price"]').value = data.selling_price;
                    document.querySelector('input[name="image_url"]').value = data.image_url || '';

                    // Update form action for edit
                    const form = document.getElementById('recipeForm');
                    form.action = `/recipes/${data.id}`;

                    // Populate ingredients
                    ingredientCount = 0;
                    document.getElementById('ingredientRows').innerHTML = '';
                    
                    if (data.ingredients && data.ingredients.length > 0) {
                        data.ingredients.forEach(ing => {
                            ingredientCount++;
                            const row = `
                                <tr>
                                    <td class="px-3 py-2 border-b border-[#E8DCC8]">
                                        <select name="ingredients[${ingredientCount}][ingredient_id]" class="w-full px-2 py-1 border border-[#E8DCC8] rounded text-sm ingredient-price" onchange="calculateHPP()">
                                            <option value="">-- Pilih bahan --</option>
                                            <option value="${ing.ingredient_id}" selected>${ing.ingredient_name}</option>
                                        </select>
                                    </td>
                                    <td class="px-3 py-2 border-b border-[#E8DCC8]">
                                        <select name="ingredients[${ingredientCount}][unit_id]" class="w-full px-2 py-1 border border-[#E8DCC8] rounded text-sm">
                                            <option value="${ing.unit_id}" selected>gram</option>
                                        </select>
                                    </td>
                                    <td class="px-3 py-2 border-b border-[#E8DCC8]">
                                        <input type="number" name="ingredients[${ingredientCount}][quantity]" placeholder="0" step="1" value="${ing.quantity}" class="w-full px-2 py-1 border border-[#E8DCC8] rounded text-sm ingredient-quantity" onchange="calculateHPP()" oninput="calculateHPP()">
                                    </td>
                                    <td class="px-3 py-2 border-b border-[#E8DCC8] text-center">
                                        <button type="button" onclick="removeIngredient(this)" class="text-red-500 hover:text-red-700 text-sm">✕</button>
                                    </td>
                                </tr>
                            `;
                            document.getElementById('ingredientRows').insertAdjacentHTML('beforeend', row);
                        });
                    } else {
                        addIngredient();
                    }

                    // Trigger modal
                    document.getElementById('addModal').classList.remove('hidden');

                    // Calculate HPP
                    calculateHPP();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Gagal memuat data resep');
                });
        }

        function viewRecipe(id) {
            // Tampilkan panel dan mulai loading
            document.getElementById('detailOverlay').classList.remove('hidden');
            const panel = document.getElementById('detailPanel');
            panel.classList.remove('translate-x-full');
            panel.classList.add('translate-x-0');

            // Reset state
            document.getElementById('detailLoading').classList.remove('hidden');
            document.getElementById('detailContent').classList.add('hidden');

            fetch(`/recipes/${id}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('detailLoading').classList.add('hidden');

                    // Header
                    const imgEl = document.getElementById('detailImage');
                    if (data.image_url) {
                        imgEl.innerHTML = `<img src="${data.image_url}" alt="${data.name}" class="w-full h-full object-cover">`;
                    } else {
                        imgEl.innerHTML = `<span class="text-5xl">🥐</span>`;
                    }

                    document.getElementById('detailCategory').textContent =
                        (data.category || 'Pastry').toUpperCase() + ' / ' + parseFloat(data.yield_quantity) + ' PCS';
                    document.getElementById('detailName').textContent = data.name;

                    // HPP & Harga
                    document.getElementById('detailSellingPrice').textContent =
                        'Rp ' + Number(data.selling_price).toLocaleString('id-ID');
                    document.getElementById('detailOverhead').textContent =
                        'Rp ' + Number(data.overhead_per_batch || 0).toLocaleString('id-ID');

                    // Bahan-bahan
                    const tbody = document.getElementById('detailIngredients');
                    tbody.innerHTML = '';
                    if (data.ingredients && data.ingredients.length > 0) {
                        data.ingredients.forEach(ing => {
                            tbody.insertAdjacentHTML('beforeend', `
                                <tr class="border-b border-[#F0EDE6] last:border-0">
                                    <td class="py-2 text-sm text-[#3E2723] font-medium">${ing.ingredient_name}</td>
                                    <td class="py-2 text-sm text-right text-[#8B6F47]">${parseFloat(ing.quantity).toLocaleString('id-ID')}</td>
                                    <td class="py-2 text-sm text-right text-[#8B6F47]">gram</td>
                                </tr>
                            `);
                        });
                        document.getElementById('detailNoIngredients').classList.add('hidden');
                        document.getElementById('detailIngredientTable').classList.remove('hidden');
                    } else {
                        document.getElementById('detailNoIngredients').classList.remove('hidden');
                        document.getElementById('detailIngredientTable').classList.add('hidden');
                    }

                    // Tombol Edit
                    document.getElementById('detailEditBtn').onclick = () => {
                        closeDetailPanel();
                        openEditModal(id);
                    };

                    document.getElementById('detailContent').classList.remove('hidden');
                })
                .catch(err => {
                    console.error(err);
                    document.getElementById('detailLoading').textContent = 'Gagal memuat detail resep.';
                });
        }

        function closeDetailPanel() {
            document.getElementById('detailOverlay').classList.add('hidden');
            const panel = document.getElementById('detailPanel');
            panel.classList.add('translate-x-full');
            panel.classList.remove('translate-x-0');
        }

        // Add event listeners to all input fields for real-time calculation
        document.addEventListener('DOMContentLoaded', function() {
            // Listen for changes on selling price and yield
            const sellingPriceInput = document.querySelector('input[name="selling_price"]');
            const yieldQuantityInput = document.querySelector('input[name="yield_quantity"]');

            if (sellingPriceInput) sellingPriceInput.addEventListener('input', calculateHPP);
            if (yieldQuantityInput) yieldQuantityInput.addEventListener('input', calculateHPP);

            // Don't call calculateHPP on page load - only when modal is opened
        });

        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.grid > div');
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Close modal when clicking outside
        const addModal = document.getElementById('addModal');
        if (addModal) {
            addModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeAddModal();
                }
            });
        }
    </script>
@endsection
