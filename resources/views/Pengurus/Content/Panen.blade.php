@extends('Pengurus.Layout._layout')

@section('title', 'Data Panen')

@section('content')
<div class="space-y-6">

  {{-- ==================== 1. HERO BANNER ==================== --}}
  <div class="relative overflow-hidden p-6 sm:p-7 min-h-[130px] flex items-center justify-between" 
       style="background: linear-gradient(135deg, #D6EDCF 0%, #E3F2DC 45%, #EEF7EB 75%, #F7FAF5 100%); border: 1px solid #CAE3BF; border-radius: 20px; box-shadow: 0 4px 20px -2px rgba(77, 152, 48, 0.06);">
    
    <!-- Left: Title & Subtitle -->
    <div class="relative z-20 flex items-center gap-4 max-w-sm sm:max-w-md">
      <!-- Clean Leaf Circle Badge (matching reference) -->
      <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-full flex items-center justify-center shrink-0 shadow-2xs"
           style="background-color: #E0F2D8; border: 2px solid #2E7D1D; color: #2E7D1D;">
        <i data-lucide="leaf" class="w-7 h-7 stroke-[2.2]"></i>
      </div>
      <div>
        <h1 class="text-2xl sm:text-[26px] font-bold tracking-tight" style="color: #172B10;">
          Data Panen
        </h1>
        <p class="text-xs sm:text-[13.5px] mt-1 font-medium" style="color: #4A6338;">
          Kelola hasil panen dari lahan Anda dengan mudah dan terintegrasi.
        </p>
      </div>
    </div>

    <!-- Right: High-Res Chili Photo spanning 90% of the banner as a rich panoramic background -->
    <div class="absolute right-0 top-0 bottom-0 w-[85%] sm:w-[90%] overflow-hidden pointer-events-none z-10"
         style="border-top-right-radius: 20px; border-bottom-right-radius: 20px;">
      <!-- Smooth Gradient Overlay for seamless text legibility -->
      <div class="absolute inset-0 z-10" 
           style="background: linear-gradient(to right, #D6EDCF 0%, rgba(214, 237, 207, 0.98) 22%, rgba(220, 240, 215, 0.82) 38%, rgba(230, 245, 226, 0.35) 65%, transparent 90%);"></div>
      <img src="{{ asset('images/panen/banner-chili-hd.jpg') }}" 
           alt="Hasil Panen Cabai" 
           class="w-full h-full object-cover object-[center_35%]">
    </div>

  </div>

  {{-- ==================== 2. STAT CARDS ROW (4 CARDS) ==================== --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">

    <!-- Card 1: Total Panen -->
    <div class="p-5 flex flex-col justify-between"
         style="background-color: #FFFFFF; border-radius: 20px; box-shadow: 0 4px 16px rgba(77, 152, 48, 0.05);">
      <div>
        <span class="text-xs sm:text-[13px] font-semibold" style="color: #4A6338;">Total Panen</span>
      </div>
      <div class="mt-3">
        <span class="text-2xl sm:text-[28px] font-extrabold tracking-tight block" style="color: #172B10;">620 Kg</span>
        <span class="text-xs font-semibold inline-flex items-center gap-0.5 mt-1" style="color: #2E7D1D;">
          &uarr; 12%
        </span>
      </div>
    </div>

    <!-- Card 2: Panen Bulan Ini -->
    <div class="p-5 flex flex-col justify-between"
         style="background-color: #FFFFFF; border-radius: 20px; box-shadow: 0 4px 16px rgba(77, 152, 48, 0.05);">
      <div>
        <span class="text-xs sm:text-[13px] font-semibold" style="color: #4A6338;">Panen Bulan Ini</span>
      </div>
      <div class="mt-3">
        <span class="text-2xl sm:text-[28px] font-extrabold tracking-tight block" style="color: #172B10;">280 Kg</span>
        <span class="text-xs font-semibold inline-flex items-center gap-0.5 mt-1" style="color: #2E7D1D;">
          &uarr; 8%
        </span>
      </div>
    </div>

    <!-- Card 3: Stok Tersedia -->
    <div class="p-5 flex flex-col justify-between"
         style="background-color: #FFFFFF; border-radius: 20px; box-shadow: 0 4px 16px rgba(37, 99, 235, 0.05);">
      <div>
        <span class="text-xs sm:text-[13px] font-semibold" style="color: #38588A;">Stok Tersedia</span>
      </div>
      <div class="mt-3">
        <span class="text-2xl sm:text-[28px] font-extrabold tracking-tight block" style="color: #172B10;">430 Kg</span>
        <span class="text-xs font-semibold inline-flex items-center gap-0.5 mt-1" style="color: #2563EB;">
          Siap Didistribusikan
        </span>
      </div>
    </div>

    <!-- Card 4: Total Terjual -->
    <div class="p-5 flex flex-col justify-between"
         style="background-color: #FFFFFF; border-radius: 20px; box-shadow: 0 4px 16px rgba(224, 122, 21, 0.05);">
      <div>
        <span class="text-xs sm:text-[13px] font-semibold" style="color: #845722;">Total Terjual</span>
      </div>
      <div class="mt-3">
        <span class="text-2xl sm:text-[26px] font-extrabold tracking-tight block" style="color: #172B10;">190 Kg</span>
        <span class="text-xs font-semibold inline-flex items-center gap-0.5 mt-1" style="color: #E07A15;">
          &uarr; 15%
        </span>
      </div>
    </div>

  </div>

  {{-- ==================== 3. ACTIONS & FILTERS BAR ==================== --}}
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 pt-1">
    
    <!-- Left: Tambah Data Panen Button -->
    <div>
    </div>

    <!-- Right: Filters (Search, Date, Land) -->
    <div class="flex flex-1 items-center gap-2.5">
      <!-- Search Komoditas Input -->
      <div class="relative flex-1 max-w-2xl">
        <i data-lucide="search" class="w-4 h-4 text-[#8FA77B] absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
        <input type="text" id="filter-search" oninput="applyFilters()"
          placeholder="Cari komoditas..."
          class="h-10 pl-9 pr-3.5 rounded-xl bg-white text-xs sm:text-sm text-slate-800 placeholder-[#9AB880] focus:outline-none transition-all w-full shadow-2xs"
          style="border: 1px solid #D5E5CB;">
      </div>

      <!-- Date Filter Dropdown / Picker -->
      <div class="relative">
        <input type="date" id="date-native-picker" onchange="onDateSelected(this.value)" class="absolute inset-0 opacity-0 pointer-events-none">
        <button type="button" onclick="document.getElementById('date-native-picker').showPicker ? document.getElementById('date-native-picker').showPicker() : document.getElementById('date-native-picker').click()"
          class="h-10 px-3.5 rounded-xl bg-white text-xs sm:text-sm font-medium text-[#4A6338] flex items-center gap-2 hover:bg-[#F9FCF6] shadow-2xs transition-colors cursor-pointer"
          style="border: 1px solid #D5E5CB;">
          <i data-lucide="calendar" class="w-4 h-4 text-[#4D9830]"></i>
          <span id="date-label">Pilih Tanggal</span>
          <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#8FA77B]"></i>
        </button>
      </div>

      <!-- Land Filter Dropdown -->
      <div class="relative">
        <select id="filter-lahan" onchange="applyFilters()"
          class="h-10 pl-3.5 pr-9 rounded-xl bg-white text-xs sm:text-sm font-medium text-[#4A6338] appearance-none hover:bg-[#F9FCF6] focus:outline-none shadow-2xs transition-colors cursor-pointer"
          style="border: 1px solid #D5E5CB;">
          <option value="">Semua Lahan</option>
          <option value="Lahan 1">Lahan 1</option>
          <option value="Lahan 2">Lahan 2</option>
          <option value="Lahan 3">Lahan 3</option>
        </select>
        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#8FA77B] absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
      </div>

    </div>

  </div>

  {{-- ==================== 4. RIWAYAT PANEN TABLE CARD ==================== --}}
  <div class="overflow-hidden bg-white shadow-xs"
       style="border: 1px solid #E4F0D6; border-radius: 20px; box-shadow: 0 4px 20px rgba(77, 152, 48, 0.04);">
    
    <!-- Table Card Header -->
    <div class="px-6 py-4 flex items-center gap-2.5" style="border-bottom: 1px solid #EAF2E3;">
      <i data-lucide="leaf" class="w-5 h-5 text-[#2E7D1D]"></i>
      <h2 class="text-base font-bold" style="color: #172B10;">Riwayat Panen</h2>
    </div>

    <!-- Table Responsive Wrapper -->
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse text-xs sm:text-sm">
        
        <!-- Table Head with solid soft green background (matching reference) -->
        <thead style="background-color: #EAF5E3;">
          <tr style="border-bottom: 1px solid #DDEED1;">
            <th class="w-14 px-6 py-3.5 text-xs font-semibold" style="color: #2D451E;">No</th>
            <th class="px-6 py-3.5 text-xs font-semibold" style="color: #2D451E;">Komoditas</th>
            <th class="px-6 py-3.5 text-xs font-semibold" style="color: #2D451E;">Lahan</th>
            <th class="px-6 py-3.5 text-xs font-semibold" style="color: #2D451E;">Tanggal Panen</th>
            <th class="px-6 py-3.5 text-xs font-semibold" style="color: #2D451E;">Hasil Panen</th>
            <th class="px-6 py-3.5 text-xs font-semibold" style="color: #2D451E;">Kualitas</th>
            <th class="px-6 py-3.5 text-xs font-semibold" style="color: #2D451E;">Status</th>
          </tr>
        </thead>

        <!-- Table Body -->
        <tbody id="panen-tbody" class="divide-y divide-[#F0F6EA]">
          @foreach($panenList as $index => $item)
            <tr class="hover:bg-[#F9FCF6] transition-colors panen-row" 
                data-id="{{ $item['id'] }}"
                data-komoditas="{{ strtolower($item['komoditas']) }}" 
                data-lahan="{{ $item['lahan'] }}" 
                data-tanggal="{{ $item['tanggal_panen'] }}">
              
              <!-- No -->
              <td class="px-6 py-4 font-medium text-[#4A6338] row-index">{{ $index + 1 }}</td>
              
              <!-- Komoditas (Clean HD Image + Name) -->
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-xl overflow-hidden shrink-0 flex items-center justify-center bg-white p-1" style="border: 1px solid #E2EED9; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                    <img src="{{ asset($item['gambar']) }}" alt="{{ $item['komoditas'] }}" class="w-full h-full object-contain">
                  </div>
                  <span class="font-bold text-[#172B10]">{{ $item['komoditas'] }}</span>
                </div>
              </td>

              <!-- Lahan -->
              <td class="px-6 py-4 text-[#4A6338]">{{ $item['lahan'] }}</td>

              <!-- Tanggal Panen -->
              <td class="px-6 py-4 text-[#4A6338] whitespace-nowrap">{{ $item['tanggal_panen'] }}</td>

              <!-- Hasil Panen -->
              <td class="px-6 py-4 font-semibold text-[#172B10]">{{ $item['hasil_panen'] }}</td>

              <!-- Kualitas -->
              <td class="px-6 py-4 font-semibold text-[#172B10]">{{ $item['kualitas'] }}</td>

              <!-- Status Badge (Matching reference design) -->
              <td class="px-6 py-4">
                @if($item['status'] === 'Tersedia')
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                        style="background-color: #DCFCE7; color: #15803D;">
                    Tersedia
                  </span>
                @elseif($item['status'] === 'Terjual Sebagian')
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                        style="background-color: #DBEAFE; color: #1D4ED8;">
                    Terjual Sebagian
                  </span>
                @else
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                        style="background-color: #E2E8F0; color: #475569;">
                    Habis Terjual
                  </span>
                @endif
              </td>

            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <!-- Table Footer / Pagination -->
    <div class="px-6 py-4 flex items-center justify-between" style="border-top: 1px solid #EAF2E3;">
      <span id="pagination-info" class="text-xs font-medium text-[#5A7342]">
        Menampilkan 1–{{ count($panenList) }} dari {{ count($panenList) }} data
      </span>

      <!-- Pagination Buttons -->
      <div class="flex items-center gap-1.5">
        <button type="button" disabled
          class="w-8 h-8 rounded-lg bg-white text-[#9AB880] text-xs flex items-center justify-center disabled:opacity-40 cursor-not-allowed"
          style="border: 1px solid #E0ECD5;">
          <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
        </button>

        <button type="button"
          class="w-8 h-8 rounded-lg text-white text-xs font-bold flex items-center justify-center shadow-2xs"
          style="background-color: #2E7D1D;">
          1
        </button>

        <button type="button" disabled
          class="w-8 h-8 rounded-lg bg-white text-[#9AB880] text-xs flex items-center justify-center disabled:opacity-40 cursor-not-allowed"
          style="border: 1px solid #E0ECD5;">
          <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
        </button>
      </div>
    </div>

  </div>

</div>

{{-- ==================== MODAL: TAMBAH DATA PANEN ==================== --}}
<div id="modal-tambah-panen" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/45 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl max-w-lg w-full border border-[#DFECD6] shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
    
    <!-- Modal Header -->
    <div class="px-6 py-4 border-b border-[#EAF2E3] flex items-center justify-between bg-[#F8FAF4]">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center shadow-2xs" style="background-color: #DCF2D2; color: #2E7D1D;">
          <i data-lucide="plus" class="w-4 h-4 stroke-[2.5]"></i>
        </div>
        <h3 id="modal-panen-title" class="text-base font-bold text-[#172B10]">Tambah Data Panen</h3>
      </div>
      <button type="button" onclick="closePanenModal()" class="text-[#8FA77B] hover:text-[#1A2D10] p-1.5 rounded-lg hover:bg-slate-100 transition-colors cursor-pointer">
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>
    </div>

    <!-- Modal Form -->
    <form id="form-panen" onsubmit="handleSavePanen(event)" class="p-6 space-y-4">
      <input type="hidden" id="panen-edit-id" value="">

      <!-- Komoditas -->
      <div>
        <label class="block text-xs font-semibold text-[#172B10] mb-1.5">Komoditas <span class="text-red-500">*</span></label>
        <select id="panen-komoditas" required class="w-full h-10 px-3.5 rounded-xl border border-[#D5E5CB] text-xs sm:text-sm text-slate-800 focus:outline-none focus:border-[#4D9830] focus:ring-1 focus:ring-[#4D9830]">
          <option value="Cabai">Cabai</option>
          <option value="Tomat">Tomat</option>
          <option value="Jagung">Jagung</option>
          <option value="Bayam">Bayam</option>
          <option value="Padi">Padi</option>
        </select>
      </div>

      <!-- Lahan -->
      <div>
        <label class="block text-xs font-semibold text-[#172B10] mb-1.5">Lahan <span class="text-red-500">*</span></label>
        <select id="panen-lahan" required class="w-full h-10 px-3.5 rounded-xl border border-[#D5E5CB] text-xs sm:text-sm text-slate-800 focus:outline-none focus:border-[#4D9830] focus:ring-1 focus:ring-[#4D9830]">
          <option value="Lahan 1">Lahan 1</option>
          <option value="Lahan 2">Lahan 2</option>
          <option value="Lahan 3">Lahan 3</option>
        </select>
      </div>

      <!-- Tanggal Panen & Hasil Panen (Kg) -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
        <div>
          <label class="block text-xs font-semibold text-[#172B10] mb-1.5">Tanggal Panen <span class="text-red-500">*</span></label>
          <input type="date" id="panen-tanggal" required value="{{ date('Y-m-d') }}"
            class="w-full h-10 px-3.5 rounded-xl border border-[#D5E5CB] text-xs sm:text-sm text-slate-800 focus:outline-none focus:border-[#4D9830] focus:ring-1 focus:ring-[#4D9830]">
        </div>
        <div>
          <label class="block text-xs font-semibold text-[#172B10] mb-1.5">Hasil Panen (Kg) <span class="text-red-500">*</span></label>
          <input type="number" id="panen-hasil" min="1" step="any" placeholder="Contoh: 100" required
            class="w-full h-10 px-3.5 rounded-xl border border-[#D5E5CB] text-xs sm:text-sm text-slate-800 focus:outline-none focus:border-[#4D9830] focus:ring-1 focus:ring-[#4D9830]">
        </div>
      </div>

      <!-- Kualitas & Status -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
        <div>
          <label class="block text-xs font-semibold text-[#172B10] mb-1.5">Kualitas <span class="text-red-500">*</span></label>
          <select id="panen-kualitas" required class="w-full h-10 px-3.5 rounded-xl border border-[#D5E5CB] text-xs sm:text-sm text-slate-800 focus:outline-none focus:border-[#4D9830] focus:ring-1 focus:ring-[#4D9830]">
            <option value="A">Grade A (Sangat Baik)</option>
            <option value="B">Grade B (Baik)</option>
            <option value="C">Grade C (Standar)</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-[#172B10] mb-1.5">Status <span class="text-red-500">*</span></label>
          <select id="panen-status" required class="w-full h-10 px-3.5 rounded-xl border border-[#D5E5CB] text-xs sm:text-sm text-slate-800 focus:outline-none focus:border-[#4D9830] focus:ring-1 focus:ring-[#4D9830]">
            <option value="Tersedia">Tersedia</option>
            <option value="Terjual Sebagian">Terjual Sebagian</option>
            <option value="Habis Terjual">Habis Terjual</option>
          </select>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="pt-3.5 border-t border-[#EAF2E3] flex items-center justify-end gap-2.5">
        <button type="button" onclick="closePanenModal()"
          class="px-4 py-2.5 rounded-xl border border-[#D5E5CB] text-xs sm:text-sm font-semibold text-[#4A6338] hover:bg-[#F9FCF6] transition-colors cursor-pointer">
          Batal
        </button>
        <button type="submit"
          class="px-5 py-2.5 rounded-xl text-white text-xs sm:text-sm font-semibold shadow-xs hover:shadow transition-all cursor-pointer"
          style="background-color: #2E7D1D;">
          Simpan Data Panen
        </button>
      </div>
    </form>

  </div>
</div>

{{-- ==================== MODAL: LIHAT DETAIL PANEN ==================== --}}
<div id="modal-detail-panen" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/45 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl max-w-md w-full border border-[#DFECD6] shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
    <div class="px-6 py-4 border-b border-[#EAF2E3] flex items-center justify-between bg-[#F8FAF4]">
      <div class="flex items-center gap-2">
        <i data-lucide="eye" class="w-4 h-4 text-[#2E7D1D]"></i>
        <h3 class="text-base font-bold text-[#172B10]">Detail Hasil Panen</h3>
      </div>
      <button type="button" onclick="closeDetailModal()" class="text-[#8FA77B] hover:text-[#1A2D10] p-1.5 rounded-lg hover:bg-slate-100 transition-colors cursor-pointer">
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>
    </div>
    <div id="detail-panen-content" class="p-6 space-y-3.5 text-xs sm:text-sm">
      <!-- Dynamic populated -->
    </div>
    <div class="px-6 py-3 border-t border-[#EAF2E3] bg-[#F8FAF4] flex justify-end">
      <button type="button" onclick="closeDetailModal()" class="px-4 py-2 rounded-xl text-white text-xs font-semibold cursor-pointer" style="background-color: #2E7D1D;">
        Tutup
      </button>
    </div>
  </div>
</div>

@push('scripts')
<script>
  // Initial Javascript dataset for client-side interactions
  let panenData = @json($panenList);

  const imagesMap = {
    'Cabai': '{{ asset("images/panen/cabai.jpg") }}',
    'Tomat': '{{ asset("images/panen/tomat.jpg") }}',
    'Jagung': '{{ asset("images/panen/jagung.jpg") }}',
    'Bayam': '{{ asset("images/panen/bayam.jpg") }}',
    'Padi': '{{ asset("images/panen/jagung.jpg") }}'
  };

  function renderTable() {
    const tbody = document.getElementById('panen-tbody');
    const searchVal = (document.getElementById('filter-search')?.value || '').toLowerCase().trim();
    const lahanVal = document.getElementById('filter-lahan')?.value || '';
    const dateVal = document.getElementById('date-native-picker')?.value || '';

    tbody.innerHTML = '';
    let visibleCount = 0;

    panenData.forEach((item, index) => {
      const matchSearch = !searchVal || item.komoditas.toLowerCase().includes(searchVal);
      const matchLahan = !lahanVal || item.lahan === lahanVal;
      
      let matchDate = true;
      if (dateVal) {
        // Date match check
      }

      if (matchSearch && matchLahan && matchDate) {
        visibleCount++;
        const row = document.createElement('tr');
        row.className = 'hover:bg-[#F9FCF6] transition-colors panen-row';
        row.setAttribute('data-id', item.id);

        let badgeHtml = `
          <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold" style="background-color: #DCFCE7; color: #15803D;">
            Tersedia
          </span>
        `;
        if (item.status === 'Terjual Sebagian') {
          badgeHtml = `
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold" style="background-color: #DBEAFE; color: #1D4ED8;">
              Terjual Sebagian
            </span>
          `;
        } else if (item.status === 'Habis Terjual') {
          badgeHtml = `
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold" style="background-color: #E2E8F0; color: #475569;">
              Habis Terjual
            </span>
          `;
        }

        const imgSrc = item.gambar.startsWith('http') ? item.gambar : '{{ asset("") }}' + item.gambar;

        row.innerHTML = `
          <td class="px-6 py-4 font-medium text-[#4A6338]">${visibleCount}</td>
          <td class="px-6 py-4">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-xl overflow-hidden shrink-0 flex items-center justify-center bg-white p-1" style="border: 1px solid #E2EED9; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <img src="${imgSrc}" alt="${item.komoditas}" class="w-full h-full object-contain">
              </div>
              <span class="font-bold text-[#172B10]">${item.komoditas}</span>
            </div>
          </td>
          <td class="px-6 py-4 text-[#4A6338]">${item.lahan}</td>
          <td class="px-6 py-4 text-[#4A6338] whitespace-nowrap">${item.tanggal_panen}</td>
          <td class="px-6 py-4 font-semibold text-[#172B10]">${item.hasil_panen}</td>
          <td class="px-6 py-4 font-semibold text-[#172B10]">${item.kualitas}</td>
          <td class="px-6 py-4">
            ${badgeHtml}
          </td>
        `;
        tbody.appendChild(row);
      }
    });

    if (visibleCount === 0) {
      const emptyRow = document.createElement('tr');
      emptyRow.innerHTML = `
        <td colspan="8" class="text-center py-8 text-xs text-[#9AB880]">
          Tidak ada data hasil panen yang sesuai dengan pencarian atau filter.
        </td>
      `;
      tbody.appendChild(emptyRow);
    }

    document.getElementById('pagination-info').innerText = `Menampilkan 1–${visibleCount} dari ${visibleCount} data`;
    if (window.lucide) window.lucide.createIcons();
  }

  function applyFilters() {
    renderTable();
  }

  function onDateSelected(val) {
    if (val) {
      const parts = val.split('-');
      const d = new Date(parts[0], parts[1] - 1, parts[2]);
      const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      document.getElementById('date-label').innerText = `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
    } else {
      document.getElementById('date-label').innerText = 'Pilih Tanggal';
    }
    applyFilters();
  }

  function openTambahPanenModal() {
    document.getElementById('modal-panen-title').innerText = 'Tambah Data Panen';
    document.getElementById('panen-edit-id').value = '';
    document.getElementById('form-panen').reset();
    document.getElementById('modal-tambah-panen').classList.remove('hidden');
    if (window.lucide) window.lucide.createIcons();
  }

  function closePanenModal() {
    document.getElementById('modal-tambah-panen').classList.add('hidden');
  }

  function handleSavePanen(e) {
    e.preventDefault();
    const editId = document.getElementById('panen-edit-id').value;
    const komoditas = document.getElementById('panen-komoditas').value;
    const lahan = document.getElementById('panen-lahan').value;
    const tanggal = document.getElementById('panen-tanggal').value;
    const hasil = document.getElementById('panen-hasil').value;
    const kualitas = document.getElementById('panen-kualitas').value;
    const status = document.getElementById('panen-status').value;

    let formattedDate = tanggal;
    if (tanggal) {
      const parts = tanggal.split('-');
      const d = new Date(parts[0], parts[1] - 1, parts[2]);
      const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      formattedDate = `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
    }

    const defaultImg = imagesMap[komoditas] || '{{ asset("images/panen/cabai.jpg") }}';

    if (editId) {
      // Update
      const item = panenData.find(p => p.id == editId);
      if (item) {
        item.komoditas = komoditas;
        item.lahan = lahan;
        item.tanggal_panen = formattedDate;
        item.hasil_panen = `${hasil} Kg`;
        item.kualitas = kualitas;
        item.status = status;
        if (imagesMap[komoditas]) item.gambar = imagesMap[komoditas];
      }
    } else {
      // Create
      const newId = Date.now();
      panenData.unshift({
        id: newId,
        komoditas: komoditas,
        gambar: defaultImg,
        lahan: lahan,
        tanggal_panen: formattedDate,
        hasil_panen: `${hasil} Kg`,
        jumlah_kg: parseFloat(hasil),
        kualitas: kualitas,
        status: status,
      });
    }

    closePanenModal();
    renderTable();
  }

  function viewPanen(id) {
    const item = panenData.find(p => p.id == id);
    if (!item) return;

    const content = document.getElementById('detail-panen-content');
    const imgSrc = item.gambar.startsWith('http') ? item.gambar : '{{ asset("") }}' + item.gambar;

    content.innerHTML = `
      <div class="relative overflow-hidden rounded-xl border border-[#DFECD6] bg-[#F5F8F1]" style="min-height: 140px;">
        <img src="${imgSrc}" alt="${item.komoditas}" class="absolute inset-0 w-full h-full object-cover opacity-70 pointer-events-none">
        <div class="relative z-10 p-4 flex items-center gap-3">
          <div class="w-12 h-12 rounded-xl overflow-hidden bg-white/80 p-1 border border-[#DFECD6] shrink-0 backdrop-blur-sm">
            <img src="${imgSrc}" class="w-full h-full object-contain">
          </div>
          <div>
            <h4 class="font-bold text-base text-[#172B10]">${item.komoditas}</h4>
            <span class="text-xs text-[#527041] font-medium">${item.lahan}</span>
          </div>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-3 pt-2">
        <div class="p-3 bg-slate-50 rounded-xl">
          <span class="text-[11px] text-[#88A675] font-semibold block uppercase">Tanggal Panen</span>
          <span class="font-semibold text-sm text-[#172B10] mt-0.5 block">${item.tanggal_panen}</span>
        </div>
        <div class="p-3 bg-slate-50 rounded-xl">
          <span class="text-[11px] text-[#88A675] font-semibold block uppercase">Hasil Panen</span>
          <span class="font-semibold text-sm text-[#172B10] mt-0.5 block">${item.hasil_panen}</span>
        </div>
        <div class="p-3 bg-slate-50 rounded-xl">
          <span class="text-[11px] text-[#88A675] font-semibold block uppercase">Kualitas</span>
          <span class="font-semibold text-sm text-[#172B10] mt-0.5 block">Grade ${item.kualitas}</span>
        </div>
        <div class="p-3 bg-slate-50 rounded-xl">
          <span class="text-[11px] text-[#88A675] font-semibold block uppercase">Status Stok</span>
          <span class="font-semibold text-sm text-[#172B10] mt-0.5 block">${item.status}</span>
        </div>
      </div>
    `;

    document.getElementById('modal-detail-panen').classList.remove('hidden');
    if (window.lucide) window.lucide.createIcons();
  }

  function closeDetailModal() {
    document.getElementById('modal-detail-panen').classList.add('hidden');
  }

  function editPanen(id) {
    const item = panenData.find(p => p.id == id);
    if (!item) return;

    document.getElementById('modal-panen-title').innerText = 'Ubah Data Panen';
    document.getElementById('panen-edit-id').value = item.id;
    document.getElementById('panen-komoditas').value = item.komoditas;
    document.getElementById('panen-lahan').value = item.lahan;
    document.getElementById('panen-hasil').value = parseFloat(item.hasil_panen.replace(/[^0-9.]/g, '')) || '';
    document.getElementById('panen-kualitas').value = item.kualitas;
    document.getElementById('panen-status').value = item.status;

    document.getElementById('modal-tambah-panen').classList.remove('hidden');
    if (window.lucide) window.lucide.createIcons();
  }

  function deletePanen(id) {
    if (confirm('Apakah Anda yakin ingin menghapus data panen ini?')) {
      panenData = panenData.filter(p => p.id != id);
      renderTable();
    }
  }

  // Close modals on ESC
  window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closePanenModal();
      closeDetailModal();
    }
  });
</script>
@endpush
@endsection
