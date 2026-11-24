@extends('Pengurus.Layout._layout')

@section('title', 'Laporan Transaksi Penjualan')

@section('content')
<div class="space-y-6">

  {{-- ==================== FLASH NOTIFICATIONS ==================== --}}
  @if(session('success'))
    <div id="flash-banner" class="flex items-center justify-between p-4 rounded-2xl bg-[#EAF5E5] border border-[#C5DFB0] text-[#1A2D10] text-xs sm:text-sm shadow-xs animate-in fade-in duration-200">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-xl bg-[#398D32] text-white flex items-center justify-center shrink-0">
          <i data-lucide="check" class="w-4 h-4 stroke-[3]"></i>
        </div>
        <div>
          <span class="font-bold">Berhasil!</span>
          <span class="text-[#4A6030] ml-1">{{ session('success') }}</span>
        </div>
      </div>
      <button type="button" onclick="document.getElementById('flash-banner').remove()" class="p-1 text-[#4A6030] hover:text-[#1A2D10] rounded-lg hover:bg-[#dff0d0] cursor-pointer" aria-label="Tutup">
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>
    </div>
  @endif

  @if($errors->any())
    <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs sm:text-sm space-y-1 animate-in fade-in duration-200">
      <div class="flex items-center gap-2 font-bold text-red-900">
        <i data-lucide="alert-circle" class="w-4 h-4 text-red-600"></i>
        <span>Terjadi kesalahan:</span>
      </div>
      <ul class="list-disc list-inside pl-6 text-red-700 space-y-0.5">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- ==================== 1. HERO BANNER LAPORAN ==================== --}}
  <div class="p-6 sm:p-8 rounded-[22px] bg-gradient-to-r from-[#2B571B] via-[#398D32] to-[#6EB844] text-white shadow-sm relative overflow-hidden">
    <div class="absolute -right-8 -bottom-8 w-60 h-60 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
    <div class="absolute right-1/4 -top-12 w-40 h-40 rounded-full bg-emerald-300/10 blur-xl pointer-events-none"></div>

    <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
      <div class="max-w-2xl space-y-2.5">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 text-white text-xs font-semibold backdrop-blur-xs border border-white/20">
          <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-yellow-300"></i>
          <span>Laporan Keuangan &amp; Penjualan</span>
          <span class="text-white/40">•</span>
          <span class="text-emerald-100 font-medium">{{ $kelompok?->nama_kelompok ?? 'Kelompok Tani' }}</span>
        </div>

        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
          Laporan Transaksi Penjualan Hasil Tani
        </h1>

        <p class="text-xs sm:text-sm text-emerald-50 leading-relaxed font-medium">
          Pantau perputaran omzet penjualan, riwayat pembayaran bertahap (DP &amp; Pelunasan), piutang aktif, serta distribusi komoditas unggulan anggota kelompok tani.
        </p>
      </div>

      <!-- Tombol Aksi Cepat: Cetak, Ekspor & Tambah Transaksi -->
      <div class="flex flex-wrap items-center gap-2.5 self-start lg:self-center shrink-0">
        <button type="button" onclick="openModalTambahTrx()"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-[#1A2D10] text-xs sm:text-sm font-bold shadow-xs hover:bg-[#F5F8F1] hover:shadow-md transition-all cursor-pointer">
          <i data-lucide="plus-circle" class="w-4 h-4 text-[#398D32]"></i>
          <span>+ Catat Transaksi</span>
        </button>

        <a href="{{ route('pengurus.laporan.transaksi.print', request()->query()) }}" target="_blank"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#1A2D10]/40 hover:bg-[#1A2D10]/60 border border-white/25 text-white text-xs sm:text-sm font-bold backdrop-blur-xs transition-all">
          <i data-lucide="printer" class="w-4 h-4 text-emerald-300"></i>
          <span>Cetak Laporan</span>
        </a>

        <a href="{{ route('pengurus.laporan.transaksi.export', request()->query()) }}"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#1A2D10]/40 hover:bg-[#1A2D10]/60 border border-white/25 text-white text-xs sm:text-sm font-bold backdrop-blur-xs transition-all">
          <i data-lucide="download" class="w-4 h-4 text-emerald-300"></i>
          <span>Unduh Data</span>
        </a>
      </div>
    </div>
  </div>

  {{-- ==================== 2. GRID 5 KARTU KPI RINGKASAN ==================== --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">

    <!-- Card 1: Total Omzet -->
    <div class="p-5 rounded-[20px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between">
      <div class="flex items-center justify-between mb-3">
        <span class="text-[11px] font-bold text-[#9AB880] uppercase tracking-wider">Total Nilai Transaksi</span>
        <div class="w-10 h-10 rounded-xl bg-[#EAF5E5] text-[#398D32] flex items-center justify-center">
          <i data-lucide="wallet" class="w-5 h-5"></i>
        </div>
      </div>
      <div>
        <div class="text-xl sm:text-2xl font-extrabold text-[#1A2D10] tracking-tight">
          Rp {{ number_format($stats['total_omzet'], 0, ',', '.') }}
        </div>
        <div class="mt-1 flex items-center gap-1.5 text-xs text-[#4A6030]">
          <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
          <span class="font-semibold">{{ $stats['total_transaksi'] }} Transaksi Terdata</span>
        </div>
      </div>
    </div>

    <!-- Card 2: Sudah Terbayar (Kas Diterima) -->
    <div class="p-5 rounded-[20px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between">
      <div class="flex items-center justify-between mb-3">
        <span class="text-[11px] font-bold text-[#9AB880] uppercase tracking-wider">Pembayaran Diterima</span>
        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
          <i data-lucide="check-circle-2" class="w-5 h-5"></i>
        </div>
      </div>
      <div>
        <div class="text-xl sm:text-2xl font-extrabold text-emerald-700 tracking-tight">
          Rp {{ number_format($stats['total_terbayar'], 0, ',', '.') }}
        </div>
        <div class="mt-1 text-xs text-[#4A6030] font-medium">
          Tingkat realisasi: <span class="font-bold text-[#1A2D10]">{{ $stats['persen_terbayar'] }}%</span>
        </div>
      </div>
    </div>

    <!-- Card 3: Sisa Piutang / Tagihan Berjalan -->
    <div class="p-5 rounded-[20px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between">
      <div class="flex items-center justify-between mb-3">
        <span class="text-[11px] font-bold text-[#9AB880] uppercase tracking-wider">Sisa Piutang / DP</span>
        <div class="w-10 h-10 rounded-xl {{ $stats['total_piutang'] > 0 ? 'bg-amber-50 text-amber-600' : 'bg-slate-50 text-slate-400' }} flex items-center justify-center">
          <i data-lucide="clock-4" class="w-5 h-5"></i>
        </div>
      </div>
      <div>
        <div class="text-xl sm:text-2xl font-extrabold {{ $stats['total_piutang'] > 0 ? 'text-amber-700' : 'text-slate-700' }} tracking-tight">
          Rp {{ number_format($stats['total_piutang'], 0, ',', '.') }}
        </div>
        <div class="mt-1 text-xs {{ $stats['total_piutang'] > 0 ? 'text-amber-600 font-semibold' : 'text-slate-500' }}">
          {{ $stats['dp_tempo_count'] + $stats['belum_bayar_count'] }} transaksi belum lunas
        </div>
      </div>
    </div>

    <!-- Card 4: Volume Produk Terjual -->
    <div class="p-5 rounded-[20px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between">
      <div class="flex items-center justify-between mb-3">
        <span class="text-[11px] font-bold text-[#9AB880] uppercase tracking-wider">Volume Terjual</span>
        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
          <i data-lucide="package-check" class="w-5 h-5"></i>
        </div>
      </div>
      <div>
        <div class="text-xl sm:text-2xl font-extrabold text-[#1A2D10] tracking-tight">
          {{ number_format($stats['total_volume_kg'] >= 1000 ? $stats['total_volume_kg'] / 1000 : $stats['total_volume_kg'], 2, ',', '.') }}
          <span class="text-sm font-semibold text-slate-500">{{ $stats['total_volume_kg'] >= 1000 ? 'Ton' : 'Kg' }}</span>
        </div>
        <div class="mt-1 text-xs text-slate-500 font-medium">
          Setara {{ number_format($stats['total_volume_kg'], 0, ',', '.') }} Kg total
        </div>
      </div>
    </div>

    <!-- Card 5: Status Transaksi Selesai -->
    <div class="p-5 rounded-[20px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between">
      <div class="flex items-center justify-between mb-3">
        <span class="text-[11px] font-bold text-[#9AB880] uppercase tracking-wider">Efektivitas Lunas</span>
        <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
          <i data-lucide="badge-percent" class="w-5 h-5"></i>
        </div>
      </div>
      <div>
        <div class="text-xl sm:text-2xl font-extrabold text-[#1A2D10] tracking-tight">
          {{ $stats['lunas_count'] }} <span class="text-sm font-semibold text-slate-400">/ {{ $stats['total_transaksi'] }}</span>
        </div>
        <div class="mt-1 flex items-center gap-1.5 text-xs text-[#4A6030]">
          <span class="inline-block w-2 h-2 rounded-full bg-[#398D32]"></span>
          <span class="font-semibold">{{ $stats['persen_lunas'] }}% Transaksi Lunas</span>
        </div>
      </div>
    </div>

  </div>

  {{-- ==================== 3. SECTION GRAFIK VISUALISASI ==================== --}}
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">

    <!-- Grafik Tren Omzet Penjualan (8 Kolom) -->
    <div class="lg:col-span-8 bg-white rounded-[22px] border border-[#E4F0D6] shadow-2xs p-5 sm:p-6 flex flex-col justify-between">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 pb-4 border-b border-[#E4F0D6]">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-xl bg-[#EAF5E5] text-[#398D32] flex items-center justify-center shrink-0">
            <i data-lucide="trending-up" class="w-4 h-4"></i>
          </div>
          <div>
            <h2 class="text-base font-extrabold text-[#1A2D10]">Tren Perputaran Omzet 6 Bulan Terakhir</h2>
            <p class="text-xs text-slate-500 font-medium">Realisasi perputaran hasil panen anggota kelompok tani</p>
          </div>
        </div>
        <div class="flex items-center gap-3 text-xs font-semibold text-[#4A6030]">
          <span class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-[#398D32]"></span>
            <span>Omzet (Rp)</span>
          </span>
          <span class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-[#9AB880]"></span>
            <span>Jumlah Transaksi</span>
          </span>
        </div>
      </div>

      <!-- Canvas Chart Tren -->
      <div class="relative w-full h-64 sm:h-72">
        <canvas id="chartTrenOmzet"></canvas>
      </div>
    </div>

    <!-- Grafik Komposisi Komoditas Terlaris (4 Kolom) -->
    <div class="lg:col-span-4 bg-white rounded-[22px] border border-[#E4F0D6] shadow-2xs p-5 sm:p-6 flex flex-col justify-between">
      <div class="flex items-center gap-2.5 mb-5 pb-4 border-b border-[#E4F0D6]">
        <div class="w-8 h-8 rounded-xl bg-[#EAF5E5] text-[#398D32] flex items-center justify-center shrink-0">
          <i data-lucide="pie-chart" class="w-4 h-4"></i>
        </div>
        <div>
          <h2 class="text-base font-extrabold text-[#1A2D10]">Komoditas Terlaris</h2>
          <p class="text-xs text-slate-500 font-medium">Berdasarkan akumulasi nominal omzet</p>
        </div>
      </div>

      <!-- Canvas Chart Komposisi -->
      <div class="relative w-full h-56 flex items-center justify-center">
        <canvas id="chartKomoditas"></canvas>
      </div>

      <!-- Legend Custom -->
      <div class="mt-4 pt-3 border-t border-[#E4F0D6] space-y-2 text-xs">
        @php
          $palette = ['#2B571B', '#398D32', '#6EB844', '#9AB880', '#C5DFB0'];
        @endphp
        @foreach($chartKomoditas['labels'] as $idx => $label)
          <div class="flex items-center justify-between text-slate-600">
            <span class="flex items-center gap-2 truncate max-w-[180px]">
              <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $palette[$idx % count($palette)] }}"></span>
              <span class="truncate font-medium">{{ $label }}</span>
            </span>
            <span class="font-bold text-[#1A2D10]">
              Rp {{ number_format($chartKomoditas['values'][$idx] ?? 0, 0, ',', '.') }}
            </span>
          </div>
        @endforeach
      </div>
    </div>

  </div>

  {{-- ==================== 4. FILTER & PENCARIAN TINGKAT LANJUT ==================== --}}
  <div class="p-5 rounded-[20px] bg-white border border-[#E4F0D6] shadow-2xs space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-[#E4F0D6]/80">
      <div class="flex items-center gap-2">
        <i data-lucide="sliders-horizontal" class="w-4 h-4 text-[#398D32]"></i>
        <span class="text-sm font-extrabold text-[#1A2D10]">Filter &amp; Penyesuaian Data</span>
      </div>

      <!-- Shortcut Presets Waktu -->
      <div class="flex items-center gap-1.5 flex-wrap">
        <span class="text-[11px] font-semibold text-[#9AB880] mr-1">Rentang:</span>
        @php
          $curPreset = request('preset_waktu');
        @endphp
        <a href="{{ route('pengurus.laporan', array_merge(request()->except(['preset_waktu', 'tanggal_dari', 'tanggal_sampai']), ['preset_waktu' => 'semua'])) }}"
          class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-colors {{ empty($curPreset) || $curPreset === 'semua' ? 'bg-[#EAF5E5] text-[#398D32] border border-[#C5DFB0]' : 'text-slate-600 hover:bg-[#F5F8F1]' }}">
          Semua
        </a>
        <a href="{{ route('pengurus.laporan', array_merge(request()->except(['preset_waktu', 'tanggal_dari', 'tanggal_sampai']), ['preset_waktu' => 'bulan_ini'])) }}"
          class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-colors {{ $curPreset === 'bulan_ini' ? 'bg-[#EAF5E5] text-[#398D32] border border-[#C5DFB0]' : 'text-slate-600 hover:bg-[#F5F8F1]' }}">
          Bulan Ini
        </a>
        <a href="{{ route('pengurus.laporan', array_merge(request()->except(['preset_waktu', 'tanggal_dari', 'tanggal_sampai']), ['preset_waktu' => '30_hari'])) }}"
          class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-colors {{ $curPreset === '30_hari' ? 'bg-[#EAF5E5] text-[#398D32] border border-[#C5DFB0]' : 'text-slate-600 hover:bg-[#F5F8F1]' }}">
          30 Hari
        </a>
        <a href="{{ route('pengurus.laporan', array_merge(request()->except(['preset_waktu', 'tanggal_dari', 'tanggal_sampai']), ['preset_waktu' => 'tahun_ini'])) }}"
          class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-colors {{ $curPreset === 'tahun_ini' ? 'bg-[#EAF5E5] text-[#398D32] border border-[#C5DFB0]' : 'text-slate-600 hover:bg-[#F5F8F1]' }}">
          Tahun Ini
        </a>
      </div>
    </div>

    <form method="GET" action="{{ route('pengurus.laporan') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">

      <!-- Pencarian Bebas -->
      <div class="lg:col-span-4">
        <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">Kata Kunci Pencarian</label>
        <div class="relative">
          <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-[#9AB880]"></i>
          <input type="text"
                 name="q"
                 value="{{ request('q') }}"
                 placeholder="Cari kode transaksi, produk, pembeli..."
                 class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-xs sm:text-sm text-[#1A2D10] placeholder-[#9AB880] focus:outline-none focus:border-[#398D32] focus:bg-white transition-all">
        </div>
      </div>

      <!-- Tanggal Dari -->
      <div class="lg:col-span-2">
        <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">Tanggal Dari</label>
        <input type="date"
               name="tanggal_dari"
               value="{{ request('tanggal_dari') }}"
               class="w-full px-3 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-xs sm:text-sm text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white transition-all">
      </div>

      <!-- Tanggal Sampai -->
      <div class="lg:col-span-2">
        <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">Tanggal Sampai</label>
        <input type="date"
               name="tanggal_sampai"
               value="{{ request('tanggal_sampai') }}"
               class="w-full px-3 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-xs sm:text-sm text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white transition-all">
      </div>

      <!-- Status Pembayaran -->
      <div class="lg:col-span-2">
        <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">Status Bayar</label>
        <select name="status_pembayaran" class="w-full px-3 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-xs sm:text-sm text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white transition-all">
          <option value="">Semua Status</option>
          <option value="lunas" {{ request('status_pembayaran') === 'lunas' ? 'selected' : '' }}>Lunas</option>
          <option value="dp" {{ request('status_pembayaran') === 'dp' ? 'selected' : '' }}>Uang Muka (DP)</option>
          <option value="tempo" {{ request('status_pembayaran') === 'tempo' ? 'selected' : '' }}>Tempo</option>
          <option value="belum_dibayar" {{ request('status_pembayaran') === 'belum_dibayar' ? 'selected' : '' }}>Belum Dibayar</option>
          <option value="dibatalkan" {{ request('status_pembayaran') === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
        </select>
      </div>

      <!-- Komoditas -->
      <div class="lg:col-span-2">
        <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">Komoditas</label>
        <select name="id_komoditas" class="w-full px-3 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-xs sm:text-sm text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white transition-all">
          <option value="">Semua Komoditas</option>
          @foreach($komoditasList as $kmdt)
            <option value="{{ $kmdt->id_komoditas }}" {{ request('id_komoditas') == $kmdt->id_komoditas ? 'selected' : '' }}>
              {{ $kmdt->nama_komoditas }}
            </option>
          @endforeach
        </select>
      </div>

      <!-- Petani Anggota -->
      <div class="lg:col-span-3">
        <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">Petani (Penjual)</label>
        <select name="id_pengguna" class="w-full px-3 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-xs sm:text-sm text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white transition-all">
          <option value="">Semua Petani Poktan</option>
          @foreach($petaniList as $ptn)
            <option value="{{ $ptn->id_pengguna }}" {{ request('id_pengguna') == $ptn->id_pengguna ? 'selected' : '' }}>
              {{ $ptn->nama }} ({{ $ptn->role }})
            </option>
          @endforeach
        </select>
      </div>

      <!-- Status Penyerahan -->
      <div class="lg:col-span-3">
        <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">Status Penyerahan</label>
        <select name="status_penyerahan" class="w-full px-3 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-xs sm:text-sm text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white transition-all">
          <option value="">Semua Penyerahan</option>
          <option value="diserahkan" {{ request('status_penyerahan') === 'diserahkan' ? 'selected' : '' }}>Sudah Diserahkan</option>
          <option value="belum_diserahkan" {{ request('status_penyerahan') === 'belum_diserahkan' ? 'selected' : '' }}>Belum Diserahkan</option>
        </select>
      </div>

      <!-- Metode Bayar -->
      <div class="lg:col-span-3">
        <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">Metode Bayar</label>
        <select name="metode_pembayaran" class="w-full px-3 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-xs sm:text-sm text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white transition-all">
          <option value="">Semua Metode</option>
          <option value="transfer_bri" {{ request('metode_pembayaran') === 'transfer_bri' ? 'selected' : '' }}>Transfer BRI</option>
          <option value="transfer" {{ request('metode_pembayaran') === 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
          <option value="tunai" {{ request('metode_pembayaran') === 'tunai' ? 'selected' : '' }}>Tunai / Cash</option>
          <option value="tempo" {{ request('metode_pembayaran') === 'tempo' ? 'selected' : '' }}>Tempo</option>
        </select>
      </div>

      <!-- Action Buttons -->
      <div class="lg:col-span-3 flex items-center gap-2">
        <button type="submit"
          class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#398D32] text-white text-xs sm:text-sm font-bold hover:bg-[#3D7826] transition-colors shadow-xs cursor-pointer">
          <i data-lucide="filter" class="w-4 h-4"></i>
          <span>Terapkan Filter</span>
        </button>

        @if(request()->hasAny(['q', 'tanggal_dari', 'tanggal_sampai', 'status_pembayaran', 'id_komoditas', 'id_pengguna', 'status_penyerahan', 'metode_pembayaran', 'preset_waktu']))
          <a href="{{ route('pengurus.laporan') }}"
            class="p-2.5 rounded-xl border border-[#C5DFB0] text-[#4A6030] hover:text-[#1A2D10] hover:bg-[#F5F8F1] transition-colors"
            title="Reset Seluruh Filter">
            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
          </a>
        @endif
      </div>

    </form>
  </div>

  {{-- ==================== 5. TABEL DATA LAPORAN TRANSAKSI ==================== --}}
  <div class="rounded-[20px] bg-white border border-[#E4F0D6] shadow-2xs overflow-hidden">
    <div class="p-5 border-b border-[#E4F0D6] flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white">
      <div>
        <h3 class="text-base font-extrabold text-[#1A2D10]">Rincian Transaksi Kelompok Tani</h3>
        <p class="text-xs text-slate-500 font-medium">Menampilkan {{ $transaksis->firstItem() ?? 0 }} - {{ $transaksis->lastItem() ?? 0 }} dari total {{ $transaksis->total() }} catatan</p>
      </div>

      <div class="flex items-center gap-2 text-xs font-semibold text-[#4A6030]">
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#EAF5E5] border border-[#C5DFB0]">
          <i data-lucide="check-check" class="w-3.5 h-3.5 text-[#398D32]"></i>
          <span>{{ $stats['lunas_count'] }} Transaksi Lunas</span>
        </span>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs sm:text-sm border-collapse">
        <thead>
          <tr class="bg-[#F5F8F1] border-b border-[#E4F0D6] text-[#4A6030] font-bold uppercase tracking-wider text-[11px]">
            <th class="py-3.5 px-4 w-12 text-center">No</th>
            <th class="py-3.5 px-4">Kode &amp; Tanggal</th>
            <th class="py-3.5 px-4">Petani (Penjual)</th>
            <th class="py-3.5 px-4">Pembeli</th>
            <th class="py-3.5 px-4">Produk / Komoditas</th>
            <th class="py-3.5 px-4 text-right">Volume</th>
            <th class="py-3.5 px-4 text-right">Total Nilai</th>
            <th class="py-3.5 px-4 text-center">Status Pembayaran</th>
            <th class="py-3.5 px-4 text-center">Penyerahan</th>
            <th class="py-3.5 px-4 text-center w-28">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#E4F0D6] text-[#1A2D10]">
          @forelse($transaksis as $idx => $trx)
            @php
              $badgeBayar = match($trx->status_pembayaran) {
                'lunas' => ['bg' => 'bg-[#EAF5E5] text-[#2E5A1C] border-[#D1DFC4]', 'label' => 'Lunas', 'icon' => 'check-circle-2'],
                'dp' => ['bg' => 'bg-amber-50 text-amber-800 border-amber-200', 'label' => 'Uang Muka (DP)', 'icon' => 'clock'],
                'tempo' => ['bg' => 'bg-blue-50 text-blue-800 border-blue-200', 'label' => 'Tempo Aktif', 'icon' => 'calendar-clock'],
                'dibatalkan' => ['bg' => 'bg-rose-50 text-rose-800 border-rose-200', 'label' => 'Dibatalkan', 'icon' => 'x-circle'],
                default => ['bg' => 'bg-slate-100 text-slate-700 border-slate-200', 'label' => 'Belum Bayar', 'icon' => 'alert-circle'],
              };

              $badgeSerah = match($trx->status_penyerahan) {
                'diserahkan' => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'label' => 'Diserahkan', 'icon' => 'check'],
                default => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'label' => 'Menunggu', 'icon' => 'package'],
              };
            @endphp
            <tr class="hover:bg-[#F5F8F1]/60 transition-colors">
              {{-- No Urut --}}
              <td class="py-4 px-4 text-center text-slate-400 font-semibold align-middle">
                {{ $transaksis->firstItem() + $idx }}
              </td>

              {{-- Kode & Tanggal --}}
              <td class="py-4 px-4 align-middle">
                <div class="font-bold text-[#1A2D10] font-mono text-xs sm:text-sm">{{ $trx->kode_transaksi }}</div>
                <div class="text-[11px] text-[#9AB880] mt-0.5 flex items-center gap-1 font-medium">
                  <i data-lucide="calendar" class="w-3 h-3 text-[#398D32]"></i>
                  <span>{{ $trx->tanggal_transaksi ? \Carbon\Carbon::parse($trx->tanggal_transaksi)->locale('id')->isoFormat('D MMM Y') : '-' }}</span>
                  <span class="text-slate-300">•</span>
                  <span>{{ $trx->waktu_transaksi ?: ($trx->created_at ? $trx->created_at->format('H:i') : '') }}</span>
                </div>
              </td>

              {{-- Petani Penjual --}}
              <td class="py-4 px-4 align-middle">
                <div class="font-semibold text-[#1A2D10]">{{ $trx->penjual?->nama ?? 'Anggota Poktan' }}</div>
                <div class="text-[11px] text-[#9AB880] mt-0.5 flex items-center gap-1">
                  <i data-lucide="phone" class="w-3 h-3 text-[#398D32]"></i>
                  <span>{{ $trx->penjual?->no_telepon ?? '-' }}</span>
                </div>
              </td>

              {{-- Pembeli --}}
              <td class="py-4 px-4 align-middle">
                <div class="font-semibold text-[#1A2D10]">{{ $trx->nama_pembeli ?: 'Pelanggan' }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1">
                  <span>{{ $trx->no_telepon_pembeli ?: '-' }}</span>
                </div>
              </td>

              {{-- Produk & Komoditas --}}
              <td class="py-4 px-4 align-middle">
                <div class="font-bold text-[#1A2D10]">{{ $trx->nama_produk }}</div>
                <div class="text-[11px] text-[#398D32] mt-0.5 font-medium flex items-center gap-1">
                  <i data-lucide="tag" class="w-3 h-3"></i>
                  <span>{{ $trx->komoditas?->nama_komoditas ?? 'Hasil Tani' }}</span>
                </div>
              </td>

              {{-- Volume --}}
              <td class="py-4 px-4 text-right align-middle">
                <div class="font-extrabold text-[#1A2D10]">
                  {{ number_format((float) $trx->jumlah, 0, ',', '.') }}
                  <span class="text-xs font-semibold text-slate-500">{{ $trx->satuan ?: 'kg' }}</span>
                </div>
                <div class="text-[11px] text-slate-400 mt-0.5">
                  @ Rp {{ number_format((float) $trx->harga_satuan, 0, ',', '.') }}
                </div>
              </td>

              {{-- Total Nilai & Terbayar --}}
              <td class="py-4 px-4 text-right align-middle">
                <div class="font-extrabold text-[#1A2D10]">
                  Rp {{ number_format((float) $trx->total_harga, 0, ',', '.') }}
                </div>
                <div class="text-[11px] font-semibold {{ $trx->sisa_pembayaran > 0 ? 'text-amber-600' : 'text-emerald-700' }} mt-0.5">
                  Terbayar: Rp {{ number_format((float) $trx->total_dibayar, 0, ',', '.') }}
                </div>
              </td>

              {{-- Status Pembayaran --}}
              <td class="py-4 px-4 text-center align-middle">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border {{ $badgeBayar['bg'] }}">
                  <i data-lucide="{{ $badgeBayar['icon'] }}" class="w-3.5 h-3.5"></i>
                  <span>{{ $badgeBayar['label'] }}</span>
                </span>
                @if($trx->sisa_pembayaran > 0 && $trx->status_pembayaran !== 'dibatalkan')
                  <div class="text-[10px] text-amber-700 font-bold mt-1">
                    Sisa: Rp {{ number_format($trx->sisa_pembayaran, 0, ',', '.') }}
                  </div>
                @endif
              </td>

              {{-- Status Penyerahan --}}
              <td class="py-4 px-4 text-center align-middle">
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $badgeSerah['bg'] }}">
                  <i data-lucide="{{ $badgeSerah['icon'] }}" class="w-3 h-3"></i>
                  <span>{{ $badgeSerah['label'] }}</span>
                </span>
              </td>

              {{-- Aksi --}}
              <td class="py-4 px-4 text-center align-middle">
                <button type="button"
                  onclick="openDetailModal('{{ $trx->id_penjualan }}')"
                  class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl border border-[#C5DFB0] bg-white text-xs font-bold text-[#4A6030] hover:bg-[#EAF5E5] hover:text-[#1A2D10] hover:border-[#9AB880] transition-colors shadow-2xs cursor-pointer">
                  <i data-lucide="eye" class="w-3.5 h-3.5 text-[#398D32]"></i>
                  <span>Rincian</span>
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="10" class="py-12 text-center">
                <div class="w-14 h-14 rounded-2xl bg-[#EAF5E5] text-[#398D32] flex items-center justify-center mx-auto mb-3">
                  <i data-lucide="inbox" class="w-7 h-7"></i>
                </div>
                <h3 class="text-sm font-bold text-[#1A2D10]">Tidak Ada Data Transaksi</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                  Tidak ditemukan data transaksi yang sesuai dengan filter atau kata kunci yang Anda masukkan.
                </p>
                <div class="mt-4">
                  <a href="{{ route('pengurus.laporan') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#398D32] text-white text-xs font-semibold hover:bg-[#3D7826] transition-colors">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    <span>Reset Filter</span>
                  </a>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination Bar --}}
    @if($transaksis->hasPages())
      <div class="p-4 border-t border-[#E4F0D6] bg-[#F5F8F1]/40 flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="text-xs text-slate-500 font-medium">
          Halaman {{ $transaksis->currentPage() }} dari {{ $transaksis->lastPage() }}
        </div>
        <div>
          {{ $transaksis->links() }}
        </div>
      </div>
    @endif
  </div>

</div>

{{-- ==================== 6. MODAL DETAIL TRANSAKSI ==================== --}}
<div id="modalDetailTrx" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="bg-white rounded-[24px] border border-[#E4F0D6] shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
    
    <!-- Modal Header -->
    <div class="p-5 sm:p-6 border-b border-[#E4F0D6] flex items-center justify-between bg-[#F5F8F1]/50">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-[#EAF5E5] text-[#398D32] flex items-center justify-center shrink-0">
          <i data-lucide="receipt" class="w-5 h-5"></i>
        </div>
        <div>
          <h3 class="text-base sm:text-lg font-extrabold text-[#1A2D10]">Rincian Transaksi Penjualan</h3>
          <p id="modalKodeTrx" class="text-xs font-mono font-bold text-[#398D32] mt-0.5">TRX-XXXX</p>
        </div>
      </div>
      <button type="button" onclick="closeDetailModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 cursor-pointer" aria-label="Tutup Modal">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>

    <!-- Modal Body Content -->
    <div class="p-5 sm:p-6 overflow-y-auto space-y-5 text-xs sm:text-sm">
      
      <!-- Box Ringkasan Status -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="p-3 rounded-xl bg-[#F5F8F1] border border-[#E4F0D6]">
          <span class="text-[10px] font-bold uppercase tracking-wider text-[#9AB880] block">Status Bayar</span>
          <span id="modalStatusBayar" class="font-extrabold text-[#1A2D10] mt-0.5 block">-</span>
        </div>
        <div class="p-3 rounded-xl bg-[#F5F8F1] border border-[#E4F0D6]">
          <span class="text-[10px] font-bold uppercase tracking-wider text-[#9AB880] block">Penyerahan</span>
          <span id="modalStatusSerah" class="font-extrabold text-[#1A2D10] mt-0.5 block">-</span>
        </div>
        <div class="p-3 rounded-xl bg-[#F5F8F1] border border-[#E4F0D6]">
          <span class="text-[10px] font-bold uppercase tracking-wider text-[#9AB880] block">Metode</span>
          <span id="modalMetode" class="font-bold text-[#398D32] mt-0.5 block">-</span>
        </div>
        <div class="p-3 rounded-xl bg-[#F5F8F1] border border-[#E4F0D6]">
          <span class="text-[10px] font-bold uppercase tracking-wider text-[#9AB880] block">Tanggal</span>
          <span id="modalTanggal" class="font-bold text-[#1A2D10] mt-0.5 block">-</span>
        </div>
      </div>

      <!-- Info Pihak Terlibat -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Info Penjual / Petani -->
        <div class="p-4 rounded-xl border border-[#E4F0D6] bg-white space-y-2">
          <div class="flex items-center gap-2 text-[#398D32] font-bold text-xs uppercase tracking-wider">
            <i data-lucide="user" class="w-3.5 h-3.5"></i>
            <span>Petani Penjual</span>
          </div>
          <div id="modalNamaPenjual" class="text-sm font-extrabold text-[#1A2D10]">-</div>
          <div class="text-xs text-slate-500 space-y-0.5">
            <div>Telp: <span id="modalTelpPenjual" class="font-medium text-slate-800">-</span></div>
            <div>Alamat: <span id="modalAlamatPenjual" class="font-medium text-slate-800">-</span></div>
          </div>
        </div>

        <!-- Info Pembeli -->
        <div class="p-4 rounded-xl border border-[#E4F0D6] bg-white space-y-2">
          <div class="flex items-center gap-2 text-[#398D32] font-bold text-xs uppercase tracking-wider">
            <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
            <span>Informasi Pembeli</span>
          </div>
          <div id="modalNamaPembeli" class="text-sm font-extrabold text-[#1A2D10]">-</div>
          <div class="text-xs text-slate-500 space-y-0.5">
            <div>Telp: <span id="modalTelpPembeli" class="font-medium text-slate-800">-</span></div>
            <div>Tgl Diserahkan: <span id="modalTglSerah" class="font-medium text-slate-800">-</span></div>
          </div>
        </div>
      </div>

      <!-- Detail Produk & Finansial -->
      <div class="rounded-xl border border-[#E4F0D6] overflow-hidden">
        <div class="bg-[#F5F8F1] px-4 py-2.5 font-bold text-xs text-[#4A6030] uppercase tracking-wider border-b border-[#E4F0D6]">
          Rincian Komoditas &amp; Pembayaran
        </div>
        <div class="p-4 space-y-3">
          <div class="flex items-center justify-between">
            <div>
              <div id="modalNamaProduk" class="font-bold text-[#1A2D10] text-sm">-</div>
              <div id="modalKomoditas" class="text-xs text-[#398D32] font-medium">-</div>
            </div>
            <div class="text-right">
              <span id="modalVolume" class="font-extrabold text-sm text-[#1A2D10]">-</span>
              <div class="text-[11px] text-slate-400">@ <span id="modalHargaSatuan">Rp 0</span></div>
            </div>
          </div>

          <div class="pt-3 border-t border-[#E4F0D6] space-y-1.5 text-xs">
            <div class="flex justify-between text-slate-600">
              <span>Total Nilai Transaksi:</span>
              <span id="modalTotalNilai" class="font-extrabold text-sm text-[#1A2D10]">Rp 0</span>
            </div>
            <div class="flex justify-between text-emerald-700">
              <span>Nominal Sudah Terbayar:</span>
              <span id="modalTerbayar" class="font-bold text-sm">Rp 0</span>
            </div>
            <div class="flex justify-between text-amber-700 font-bold">
              <span>Sisa Piutang / Kurang Bayar:</span>
              <span id="modalSisaPiutang" class="text-sm">Rp 0</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Riwayat Pembayaran Bertahap -->
      <div class="space-y-2">
        <div class="flex items-center gap-2 text-xs font-bold text-[#1A2D10] uppercase tracking-wider">
          <i data-lucide="history" class="w-3.5 h-3.5 text-[#398D32]"></i>
          <span>Riwayat Tahapan Pembayaran</span>
        </div>
        <div id="modalRiwayatBayarList" class="space-y-2">
          <!-- Diisi dinamis via JS -->
        </div>
      </div>

      <!-- Catatan Transaksi -->
      <div class="p-3.5 rounded-xl bg-[#F5F8F1] border border-[#E4F0D6] text-xs">
        <span class="font-bold text-[#4A6030] block mb-0.5">Catatan:</span>
        <p id="modalCatatan" class="text-slate-600 italic">-</p>
      </div>

    </div>

    <!-- Modal Footer -->
    <div class="p-4 sm:p-5 border-t border-[#E4F0D6] bg-white flex justify-end gap-2.5">
      <button type="button" onclick="closeDetailModal()" class="px-4 py-2.5 rounded-xl border border-[#C5DFB0] text-xs font-bold text-[#4A6030] hover:bg-[#F5F8F1] transition-colors cursor-pointer">
        Tutup
      </button>
    </div>

  </div>
</div>

{{-- ==================== 7. MODAL CATAT TRANSAKSI BARU ==================== --}}
<div id="modalTambahTrx" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="bg-white rounded-[24px] border border-[#E4F0D6] shadow-xl w-full max-w-2xl max-h-[92vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
    
    <!-- Modal Header -->
    <div class="p-5 sm:p-6 border-b border-[#E4F0D6] flex items-center justify-between bg-[#F5F8F1]/60">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-[#398D32] text-white flex items-center justify-center shrink-0">
          <i data-lucide="plus-circle" class="w-5 h-5"></i>
        </div>
        <div>
          <h3 class="text-base sm:text-lg font-extrabold text-[#1A2D10]">Catat Transaksi Penjualan</h3>
          <p class="text-xs text-[#4A6030] font-medium mt-0.5">Kelompok Tani {{ $kelompok?->nama_kelompok ?? 'Maju Bersama' }}</p>
        </div>
      </div>
      <button type="button" onclick="closeModalTambahTrx()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 cursor-pointer" aria-label="Tutup Modal">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>

    <!-- Modal Form Body -->
    <form method="POST" action="{{ route('pengurus.laporan.transaksi.store') }}" class="flex flex-col flex-1 overflow-hidden">
      @csrf
      <div class="p-5 sm:p-6 overflow-y-auto space-y-4 text-xs sm:text-sm flex-1">

        <!-- Baris 1: Petani Penjual & Komoditas -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
          <div>
            <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">
              Petani Anggota (Penjual) <span class="text-rose-500">*</span>
            </label>
            <select name="id_pengguna" required class="w-full px-3 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">
              <option value="">-- Pilih Anggota Poktan --</option>
              @foreach($petaniList as $ptn)
                <option value="{{ $ptn->id_pengguna }}">{{ $ptn->nama }} ({{ $ptn->role }})</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">
              Komoditas Terkait
            </label>
            <select name="id_komoditas" class="w-full px-3 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">
              <option value="">-- Pilih Komoditas --</option>
              @foreach($komoditasList as $kmdt)
                <option value="{{ $kmdt->id_komoditas }}">{{ $kmdt->nama_komoditas }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <!-- Baris 2: Nama Produk -->
        <div>
          <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">
            Nama Produk / Hasil Panen <span class="text-rose-500">*</span>
          </label>
          <input type="text" name="nama_produk" required placeholder="Contoh: Beras Organik Pandan Wangi 50kg"
            class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">
        </div>

        <!-- Baris 3: Informasi Pembeli -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
          <div>
            <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">
              Nama Pembeli / Pengepul <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="nama_pembeli" required placeholder="Nama pembeli / toko / CV"
              class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">
          </div>
          <div>
            <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">
              No. Telepon / WhatsApp Pembeli
            </label>
            <input type="text" name="no_telepon_pembeli" placeholder="08xxxxxxxxxx"
              class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">
          </div>
        </div>

        <!-- Baris 4: Kuantitas, Satuan & Harga Satuan -->
        <div class="grid grid-cols-3 gap-3">
          <div class="col-span-1">
            <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">
              Volume / Qty <span class="text-rose-500">*</span>
            </label>
            <input type="number" step="any" min="0.01" id="inputJumlah" name="jumlah" required placeholder="100"
              class="w-full px-3 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">
          </div>
          <div class="col-span-1">
            <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">
              Satuan
            </label>
            <input type="text" name="satuan" value="kg" placeholder="kg"
              class="w-full px-3 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">
          </div>
          <div class="col-span-1">
            <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">
              Harga / Satuan (Rp) <span class="text-rose-500">*</span>
            </label>
            <input type="number" min="1" id="inputHarga" name="harga_satuan" required placeholder="14000"
              class="w-full px-3 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">
          </div>
        </div>

        <!-- Estimasi Total Otomatis -->
        <div class="p-3 rounded-xl bg-[#EAF5E5] border border-[#C5DFB0] flex items-center justify-between text-xs font-semibold text-[#1A2D10]">
          <span>Estimasi Total Nilai Transaksi:</span>
          <span id="previewTotalHarga" class="text-sm font-extrabold text-[#398D32]">Rp 0</span>
        </div>

        <!-- Baris 5: Pembayaran & Metode -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
          <div>
            <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">
              Nominal Dibayar di Muka (Rp)
            </label>
            <input type="number" min="0" name="nominal_dibayar" placeholder="Masukkan 0 jika belum dibayar"
              class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">
            <span class="text-[10px] text-slate-500 mt-1 block">Jika sama dengan total nilai, transaksi otomatis tercatat Lunas.</span>
          </div>

          <div>
            <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">
              Metode Pembayaran <span class="text-rose-500">*</span>
            </label>
            <select name="metode_pembayaran" required class="w-full px-3 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">
              <option value="transfer_bri">Transfer BRI</option>
              <option value="transfer">Transfer Bank Lainnya</option>
              <option value="tunai">Tunai / Cash</option>
              <option value="tempo">Tempo / Piutang</option>
            </select>
          </div>
        </div>

        <!-- Baris 6: Tanggal Transaksi & Status Penyerahan -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
          <div>
            <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">
              Tanggal Transaksi <span class="text-rose-500">*</span>
            </label>
            <input type="date" name="tanggal_transaksi" required value="{{ date('Y-m-d') }}"
              class="w-full px-3 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">
          </div>

          <div>
            <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">
              Status Penyerahan Hasil Tani <span class="text-rose-500">*</span>
            </label>
            <select name="status_penyerahan" required class="w-full px-3 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">
              <option value="diserahkan">Sudah Diserahkan ke Pembeli</option>
              <option value="belum_diserahkan">Belum Diserahkan (Masih di Gudang/Petani)</option>
            </select>
          </div>
        </div>

        <!-- Catatan Tambahan -->
        <div>
          <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">
            Catatan Tambahan
          </label>
          <textarea name="catatan" rows="2" placeholder="Keterangan pengiriman, nomor resi, perjanjian tempo, dll."
            class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm"></textarea>
        </div>

      </div>

      <!-- Modal Form Footer -->
      <div class="p-4 sm:p-5 border-t border-[#E4F0D6] bg-[#F5F8F1]/40 flex justify-end gap-2.5">
        <button type="button" onclick="closeModalTambahTrx()" class="px-4 py-2.5 rounded-xl border border-[#C5DFB0] text-xs font-bold text-[#4A6030] hover:bg-white transition-colors cursor-pointer">
          Batal
        </button>
        <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#398D32] hover:bg-[#3D7826] text-white text-xs sm:text-sm font-bold shadow-xs transition-colors cursor-pointer flex items-center gap-2">
          <i data-lucide="check" class="w-4 h-4"></i>
          <span>Simpan Transaksi</span>
        </button>
      </div>
    </form>

  </div>
</div>

@endsection

@push('scripts')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // 1. Data dari Controller untuk Chart
  const trenData = @json($chartTren);
  const komoditasData = @json($chartKomoditas);

  // 2. Render Chart Tren Omzet (Line / Area Chart dengan tema Hijau PawonTani)
  const ctxTren = document.getElementById('chartTrenOmzet');
  if (ctxTren) {
    new Chart(ctxTren, {
      type: 'line',
      data: {
        labels: trenData.labels,
        datasets: [
          {
            label: 'Omzet Penjualan (Rp)',
            data: trenData.omzet,
            borderColor: '#398D32',
            backgroundColor: 'rgba(77, 152, 48, 0.12)',
            borderWidth: 2.5,
            fill: true,
            tension: 0.35,
            pointBackgroundColor: '#2B571B',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 4.5,
            pointHoverRadius: 6.5,
            yAxisID: 'y',
          },
          {
            label: 'Jumlah Transaksi',
            data: trenData.transaksi,
            borderColor: '#9AB880',
            backgroundColor: 'transparent',
            borderWidth: 2,
            borderDash: [5, 5],
            tension: 0.3,
            pointBackgroundColor: '#9AB880',
            pointBorderColor: '#ffffff',
            pointRadius: 3.5,
            yAxisID: 'y1',
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            backgroundColor: '#1A2D10',
            titleFont: { family: 'Plus Jakarta Sans', size: 12, weight: 'bold' },
            bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
            padding: 10,
            cornerRadius: 10,
            callbacks: {
              label: function(context) {
                if (context.datasetIndex === 0) {
                  return ' Omzet: Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                }
                return ' ' + context.parsed.y + ' Transaksi';
              }
            }
          }
        },
        scales: {
          x: {
            grid: { color: 'rgba(228, 240, 214, 0.4)' },
            ticks: {
              color: '#4A6030',
              font: { family: 'Plus Jakarta Sans', size: 11 }
            }
          },
          y: {
            type: 'linear',
            display: true,
            position: 'left',
            grid: { color: 'rgba(228, 240, 214, 0.6)' },
            ticks: {
              color: '#4A6030',
              font: { family: 'Plus Jakarta Sans', size: 11 },
              callback: function(value) {
                if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                if (value >= 1000) return 'Rp ' + (value / 1000).toFixed(0) + 'K';
                return 'Rp ' + value;
              }
            }
          },
          y1: {
            type: 'linear',
            display: false,
            position: 'right',
            grid: { drawOnChartArea: false },
          }
        }
      }
    });
  }

  // 3. Render Chart Komoditas Terlaris (Doughnut Chart)
  const ctxKomoditas = document.getElementById('chartKomoditas');
  if (ctxKomoditas) {
    const palette = ['#2B571B', '#398D32', '#6EB844', '#9AB880', '#C5DFB0'];
    new Chart(ctxKomoditas, {
      type: 'doughnut',
      data: {
        labels: komoditasData.labels,
        datasets: [{
          data: komoditasData.values,
          backgroundColor: palette.slice(0, komoditasData.labels.length),
          borderColor: '#ffffff',
          borderWidth: 2,
          hoverOffset: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#1A2D10',
            titleFont: { family: 'Plus Jakarta Sans', size: 12, weight: 'bold' },
            bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
            padding: 10,
            cornerRadius: 10,
            callbacks: {
              label: function(context) {
                const val = context.parsed;
                return ' Rp ' + new Intl.NumberFormat('id-ID').format(val);
              }
            }
          }
        }
      }
    });
  }

  // Re-init lucide icons inside modal if needed
  if (window.lucide) {
    window.lucide.createIcons();
  }
});

// Modal Logic
function openDetailModal(idPenjualan) {
  const modal = document.getElementById('modalDetailTrx');
  if (!modal) return;

  modal.classList.remove('hidden');

  // Fetch data detail via AJAX
  fetch("{{ url('pengurus/laporan/transaksi') }}/" + idPenjualan, {
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(res => res.json())
  .then(res => {
    if (!res.success || !res.data) return;
    const trx = res.data;

    document.getElementById('modalKodeTrx').innerText = trx.kode_transaksi;
    document.getElementById('modalStatusBayar').innerText = (trx.status_pembayaran || '').toUpperCase().replace('_', ' ');
    document.getElementById('modalStatusSerah').innerText = (trx.status_penyerahan || '').toUpperCase().replace('_', ' ');
    document.getElementById('modalMetode').innerText = (trx.metode_pembayaran || '').toUpperCase().replace('_', ' ');
    document.getElementById('modalTanggal').innerText = trx.tanggal_transaksi || '-';

    document.getElementById('modalNamaPenjual').innerText = trx.penjual ? trx.penjual.nama : 'Anggota Poktan';
    document.getElementById('modalTelpPenjual').innerText = trx.penjual ? (trx.penjual.no_telepon || '-') : '-';
    document.getElementById('modalAlamatPenjual').innerText = trx.penjual ? (trx.penjual.alamat || '-') : '-';

    document.getElementById('modalNamaPembeli').innerText = trx.nama_pembeli || 'Pelanggan';
    document.getElementById('modalTelpPembeli').innerText = trx.no_telepon_pembeli || '-';
    document.getElementById('modalTglSerah').innerText = trx.tanggal_diserahkan || 'Belum Diserahkan';

    document.getElementById('modalNamaProduk').innerText = trx.nama_produk || '-';
    document.getElementById('modalKomoditas').innerText = trx.komoditas ? trx.komoditas.nama_komoditas : 'Komoditas Pertanian';
    document.getElementById('modalVolume').innerText = new Intl.NumberFormat('id-ID').format(trx.jumlah) + ' ' + (trx.satuan || 'kg');
    document.getElementById('modalHargaSatuan').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(trx.harga_satuan);

    const totalHarga = parseFloat(trx.total_harga) || 0;
    const totalDibayar = parseFloat(trx.total_dibayar) || 0;
    const sisa = Math.max(0, totalHarga - totalDibayar);

    document.getElementById('modalTotalNilai').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalHarga);
    document.getElementById('modalTerbayar').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalDibayar);
    document.getElementById('modalSisaPiutang').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(sisa);

    document.getElementById('modalCatatan').innerText = trx.catatan || (trx.alasan_batal ? 'Dibatalkan: ' + trx.alasan_batal : 'Tidak ada catatan khusus.');

    // Render riwayat pembayaran
    const riwayatList = document.getElementById('modalRiwayatBayarList');
    riwayatList.innerHTML = '';
    if (trx.riwayat_pembayaran && trx.riwayat_pembayaran.length > 0) {
      trx.riwayat_pembayaran.forEach((pay, index) => {
        const itemHtml = `
          <div class="p-3 rounded-xl bg-[#F5F8F1] border border-[#E4F0D6] flex items-center justify-between text-xs">
            <div>
              <span class="font-bold text-[#1A2D10]">Pembayaran #${index + 1} - ${pay.keterangan || 'Angsuran / DP'}</span>
              <span class="text-[11px] text-slate-500 block">${pay.tanggal_pembayaran || '-'}</span>
            </div>
            <div class="text-right">
              <span class="font-extrabold text-[#398D32] text-sm">Rp ${new Intl.NumberFormat('id-ID').format(pay.nominal)}</span>
              <span class="text-[10px] text-emerald-700 font-semibold block capitalize">${pay.status_verifikasi}</span>
            </div>
          </div>
        `;
        riwayatList.insertAdjacentHTML('beforeend', itemHtml);
      });
    } else {
      riwayatList.innerHTML = '<div class="p-3 rounded-xl bg-[#F5F8F1] text-xs text-slate-500 text-center italic">Belum ada catatan riwayat pembayaran bertahap.</div>';
    }

    if (window.lucide) {
      window.lucide.createIcons();
    }
  })
  .catch(err => {
    console.error('Gagal mengambil data transaksi:', err);
  });
}

function closeDetailModal() {
  const modal = document.getElementById('modalDetailTrx');
  if (modal) {
    modal.classList.add('hidden');
  }
}

// Modal Tambah Transaksi Logic
function openModalTambahTrx() {
  const modal = document.getElementById('modalTambahTrx');
  if (!modal) return;
  modal.classList.remove('hidden');
  if (window.lucide) {
    window.lucide.createIcons();
  }
}

function closeModalTambahTrx() {
  const modal = document.getElementById('modalTambahTrx');
  if (modal) {
    modal.classList.add('hidden');
  }
}

// Live calculation total estimasi transaksi baru
const inputJml = document.getElementById('inputJumlah');
const inputHrg = document.getElementById('inputHarga');
const previewTot = document.getElementById('previewTotalHarga');

function updateLiveTotal() {
  if (!inputJml || !inputHrg || !previewTot) return;
  const j = parseFloat(inputJml.value) || 0;
  const h = parseFloat(inputHrg.value) || 0;
  const total = j * h;
  previewTot.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
}

if (inputJml && inputHrg) {
  inputJml.addEventListener('input', updateLiveTotal);
  inputHrg.addEventListener('input', updateLiveTotal);
}
</script>
@endpush
