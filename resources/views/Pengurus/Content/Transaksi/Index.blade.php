@extends('Pengurus.Layout._layout')

@section('title', 'Transaksi Penjualan')

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
        <span>Terjadi kesalahan saat mencatat transaksi:</span>
      </div>
      <ul class="list-disc list-inside pl-6 text-red-700 space-y-0.5">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- ==================== 1. TOP HEADER & ACTION BUTTONS ==================== --}}
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-2xl sm:text-[28px] font-extrabold text-[#1A2D10] tracking-tight">
        Kelola Transaksi Penjualan
      </h1>
      <p class="text-xs sm:text-sm text-[#9AB880] mt-1 font-medium">
        Pantau catatan penjualan hasil panen anggota {{ $kelompok?->nama_kelompok ?? 'Kelompok Tani' }}.
      </p>
    </div>

    <!-- Action Buttons & Tabs -->
    <div class="flex flex-wrap items-center gap-2.5 shrink-0">
      @php
        $curTab = request('tab');
      @endphp
      <a href="{{ route('pengurus.penjualan') }}"
        class="rounded-xl border px-3.5 py-2.5 text-xs font-semibold transition-all {{ empty($curTab) ? 'border-[#398D32] bg-[#EAF5E5] text-[#3D8024]' : 'border-[#C5DFB0] bg-white text-[#4A6030] hover:bg-[#F5F8F1]' }}">
        Semua ({{ $stats['total'] }})
      </a>
      <a href="{{ route('pengurus.penjualan', ['tab' => 'lunas']) }}"
        class="rounded-xl border px-3.5 py-2.5 text-xs font-semibold transition-all {{ $curTab === 'lunas' ? 'border-[#398D32] bg-[#EAF5E5] text-[#3D8024]' : 'border-[#C5DFB0] bg-white text-[#4A6030] hover:bg-[#F5F8F1]' }}">
        Lunas ({{ $stats['lunas'] }})
      </a>
      <a href="{{ route('pengurus.penjualan', ['tab' => 'piutang']) }}"
        class="rounded-xl border px-3.5 py-2.5 text-xs font-semibold transition-all {{ $curTab === 'piutang' ? 'border-[#398D32] bg-[#EAF5E5] text-[#3D8024]' : 'border-[#C5DFB0] bg-white text-[#4A6030] hover:bg-[#F5F8F1]' }}">
        Piutang / DP ({{ $stats['piutang_count'] }})
      </a>

      <!-- Unduh Data (Export CSV) -->
      <a href="{{ route('pengurus.laporan.transaksi.export', request()->query()) }}"
        class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-white text-xs sm:text-sm font-semibold text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10] hover:border-[#9AB880] transition-all shadow-2xs cursor-pointer"
        title="Unduh Data CSV">
        <i data-lucide="download" class="w-4 h-4 text-[#398D32]"></i>
        <span>Unduh Data</span>
      </a>

      <!-- Tambah / Catat Transaksi Button -->
      <button type="button" onclick="openModalTambahTrx()"
        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#398D32] hover:bg-[#3D8024] active:scale-[0.99] text-white text-xs sm:text-sm font-bold shadow-sm shadow-[#398D32]/20 hover:shadow-md hover:shadow-[#398D32]/30 transition-all cursor-pointer">
        <i data-lucide="plus" class="w-4 h-4 stroke-[2.5]"></i>
        <span>Catat Transaksi</span>
      </button>

      <!-- Segarkan -->
      <a href="{{ route('pengurus.penjualan') }}"
        class="p-2.5 rounded-xl border border-[#C5DFB0] bg-white text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10] transition-colors"
        title="Muat Ulang">
        <i data-lucide="refresh-cw" class="w-4 h-4 text-[#398D32]"></i>
      </a>
    </div>
  </div>

  {{-- ==================== 2. SUMMARY METRICS CARDS (IDENTIK DENGAN SCREENSHOT) ==================== --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

    <!-- Card 1: Total Transaksi -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-[#EAF5E5] text-[#398D32] flex items-center justify-center shrink-0">
        <i data-lucide="shopping-bag" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Total Transaksi</span>
        <span class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ $stats['total'] }}</span>
        <span class="text-[11px] text-[#4A6030] block mt-0.5 font-medium">Volume: {{ number_format($stats['total_volume_ton'], 2, ',', '.') }} Ton</span>
      </div>
    </div>

    <!-- Card 2: Total Omzet Penjualan -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
        <i data-lucide="wallet" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Total Omzet</span>
        <span class="text-2xl font-extrabold text-emerald-800 tracking-tight">Rp {{ number_format($stats['total_omzet'], 0, ',', '.') }}</span>
        <span class="text-[11px] text-emerald-700 block mt-0.5 font-medium">Realisasi hasil panen poktan</span>
      </div>
    </div>

    <!-- Card 3: Transaksi Lunas -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center shrink-0">
        <i data-lucide="check-circle-2" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-teal-700 uppercase tracking-wider block">Transaksi Lunas</span>
        <span class="text-2xl font-extrabold text-teal-900 tracking-tight">{{ $stats['lunas'] }}</span>
        <span class="text-[11px] text-teal-800 block mt-0.5 font-semibold">● Pembayaran selesai</span>
      </div>
    </div>

    <!-- Card 4: Piutang & DP -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-amber-50 text-[#F4A020] flex items-center justify-center shrink-0">
        <i data-lucide="clock" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-amber-700 uppercase tracking-wider block">Piutang &amp; DP</span>
        <span class="text-2xl font-extrabold text-amber-900 tracking-tight">Rp {{ number_format($stats['total_piutang'], 0, ',', '.') }}</span>
        <span class="text-[11px] text-amber-800 block mt-0.5 font-medium">{{ $stats['piutang_count'] }} transaksi belum lunas</span>
      </div>
    </div>

  </div>

  {{-- ==================== 3. FILTER & SEARCH BAR (IDENTIK DENGAN SCREENSHOT) ==================== --}}
  <div class="p-4 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs">
    <form method="GET" action="{{ route('pengurus.penjualan') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">

      <!-- Search Input -->
      <div class="lg:col-span-5 relative">
        <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-[#9AB880]"></i>
        <input type="text"
               name="q"
               value="{{ request('q') }}"
               placeholder="Cari kode transaksi, petani, produk, atau pembeli..."
               class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-xs sm:text-sm text-[#1A2D10] placeholder-[#9AB880] focus:outline-none focus:border-[#398D32] focus:bg-white transition-all">
      </div>

      <!-- Filter Status Pembayaran -->
      <div class="lg:col-span-3">
        <select name="status" class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-xs sm:text-sm text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white transition-all">
          <option value="">-- Semua Status Pembayaran --</option>
          <option value="lunas" {{ request('status') === 'lunas' ? 'selected' : '' }}>Lunas</option>
          <option value="dp" {{ request('status') === 'dp' ? 'selected' : '' }}>Uang Muka (DP)</option>
          <option value="tempo" {{ request('status') === 'tempo' ? 'selected' : '' }}>Tempo</option>
          <option value="belum_dibayar" {{ request('status') === 'belum_dibayar' ? 'selected' : '' }}>Belum Dibayar</option>
          <option value="dibatalkan" {{ request('status') === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
        </select>
      </div>

      <!-- Filter Komoditas -->
      <div class="lg:col-span-2">
        <select name="komoditas" class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-xs sm:text-sm text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white transition-all">
          <option value="">-- Semua Komoditas --</option>
          @foreach($komoditasList as $kmdt)
            <option value="{{ $kmdt->id_komoditas }}" {{ request('komoditas') == $kmdt->id_komoditas ? 'selected' : '' }}>
              {{ $kmdt->nama_komoditas }}
            </option>
          @endforeach
        </select>
      </div>

      <!-- Action Buttons -->
      <div class="lg:col-span-2 flex items-center gap-2">
        <button type="submit" class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-[#398D32] text-white text-xs sm:text-sm font-semibold hover:bg-[#3D7826] transition-colors shadow-xs cursor-pointer">
          <i data-lucide="filter" class="w-3.5 h-3.5"></i>
          <span>Filter</span>
        </button>
        @if(request()->hasAny(['q', 'status', 'komoditas', 'tab', 'petani']))
          <a href="{{ route('pengurus.penjualan') }}" class="p-2.5 rounded-xl border border-[#C5DFB0] text-[#4A6030] hover:text-[#1A2D10] hover:bg-[#F5F8F1] transition-colors" title="Reset Filter">
            <i data-lucide="x" class="w-4 h-4"></i>
          </a>
        @endif
      </div>

    </form>
  </div>

  {{-- ==================== 4. DATA TABLE ==================== --}}
  <div class="rounded-[20px] bg-white border border-[#E4F0D6] shadow-2xs overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs sm:text-sm border-collapse">
        <thead>
          <tr class="bg-[#F5F8F1] border-b border-[#E4F0D6] text-[#4A6030] font-bold uppercase tracking-wider text-[11px]">
            <th class="py-3.5 px-4">Kode &amp; Tanggal</th>
            <th class="py-3.5 px-4">Petani Anggota</th>
            <th class="py-3.5 px-4">Pembeli</th>
            <th class="py-3.5 px-4">Produk &amp; Komoditas</th>
            <th class="py-3.5 px-4 text-right">Volume</th>
            <th class="py-3.5 px-4 text-right">Total Nilai</th>
            <th class="py-3.5 px-4 text-center">Status Pembayaran</th>
            <th class="py-3.5 px-4 text-center w-28">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#E4F0D6] text-[#1A2D10]">
          @forelse($transaksis as $trx)
            @php
              $badgeBayar = match($trx->status_pembayaran) {
                'lunas' => ['bg' => 'bg-[#EAF5E5] text-[#2E5A1C] border-[#D1DFC4]', 'label' => 'Lunas', 'icon' => 'check-circle-2'],
                'dp' => ['bg' => 'bg-amber-50 text-amber-800 border-amber-200', 'label' => 'Uang Muka (DP)', 'icon' => 'clock'],
                'tempo' => ['bg' => 'bg-blue-50 text-blue-800 border-blue-200', 'label' => 'Tempo Aktif', 'icon' => 'calendar-clock'],
                'dibatalkan' => ['bg' => 'bg-rose-50 text-rose-800 border-rose-200', 'label' => 'Dibatalkan', 'icon' => 'x-circle'],
                default => ['bg' => 'bg-slate-100 text-slate-700 border-slate-200', 'label' => 'Belum Bayar', 'icon' => 'alert-circle'],
              };
            @endphp
            <tr class="hover:bg-[#F5F8F1]/60 transition-colors">
              {{-- Kode & Tanggal --}}
              <td class="py-4 px-4 align-top">
                <div class="font-bold text-[#1A2D10] font-mono">{{ $trx->kode_transaksi }}</div>
                <div class="text-[11px] text-[#9AB880] mt-0.5 flex items-center gap-1 font-medium">
                  <i data-lucide="calendar" class="w-3 h-3 text-[#398D32]"></i>
                  <span>{{ $trx->tanggal_transaksi ? \Carbon\Carbon::parse($trx->tanggal_transaksi)->locale('id')->isoFormat('d MMM Y') : '-' }}</span>
                </div>
              </td>

              {{-- Petani --}}
              <td class="py-4 px-4 align-top">
                <div class="font-semibold text-[#1A2D10]">{{ $trx->penjual?->nama ?? 'Anggota Poktan' }}</div>
                <div class="text-[11px] text-[#9AB880] mt-0.5 flex items-center gap-1">
                  <i data-lucide="phone" class="w-3 h-3 text-[#398D32]"></i>
                  <span>{{ $trx->penjual?->no_telepon ?? '-' }}</span>
                </div>
              </td>

              {{-- Pembeli --}}
              <td class="py-4 px-4 align-top">
                <div class="font-semibold text-[#1A2D10]">{{ $trx->nama_pembeli ?: 'Pelanggan' }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">
                  {{ $trx->no_telepon_pembeli ?: '-' }}
                </div>
              </td>

              {{-- Produk & Komoditas --}}
              <td class="py-4 px-4 align-top">
                <div class="font-bold text-[#1A2D10]">{{ $trx->nama_produk }}</div>
                <div class="text-[11px] text-[#398D32] font-medium mt-0.5">
                  {{ $trx->komoditas?->nama_komoditas ?? 'Hasil Tani' }}
                </div>
              </td>

              {{-- Volume --}}
              <td class="py-4 px-4 text-right align-top">
                <div class="font-extrabold text-[#1A2D10]">
                  {{ number_format((float) $trx->jumlah, 0, ',', '.') }} {{ $trx->satuan ?: 'kg' }}
                </div>
                <div class="text-[11px] text-slate-400 mt-0.5">
                  @ Rp {{ number_format((float) $trx->harga_satuan, 0, ',', '.') }}
                </div>
              </td>

              {{-- Total Nilai --}}
              <td class="py-4 px-4 text-right align-top">
                <div class="font-extrabold text-[#1A2D10]">
                  Rp {{ number_format((float) $trx->total_harga, 0, ',', '.') }}
                </div>
                <div class="text-[11px] font-semibold {{ $trx->sisa_pembayaran > 0 ? 'text-amber-600' : 'text-emerald-700' }} mt-0.5">
                  Terbayar: Rp {{ number_format((float) $trx->total_dibayar, 0, ',', '.') }}
                </div>
              </td>

              {{-- Status Pembayaran --}}
              <td class="py-4 px-4 text-center align-top">
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

              {{-- Aksi --}}
              <td class="py-4 px-4 text-center align-top">
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
              <td colspan="8" class="py-12 text-center">
                <div class="w-14 h-14 rounded-2xl bg-[#EAF5E5] text-[#398D32] flex items-center justify-center mx-auto mb-3">
                  <i data-lucide="inbox" class="w-7 h-7"></i>
                </div>
                <h3 class="text-sm font-bold text-[#1A2D10]">Belum Ada Catatan Transaksi</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                  Belum ada transaksi penjualan yang dicatat. Tekan tombol Catat Transaksi untuk menambahkan transaksi baru.
                </p>
                <div class="mt-4">
                  <button type="button" onclick="openModalTambahTrx()" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#398D32] text-white text-xs font-semibold hover:bg-[#3D7826] transition-colors">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Catat Transaksi Sekarang</span>
                  </button>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($transaksis->hasPages())
      <div class="p-4 border-t border-[#E4F0D6] bg-[#F5F8F1]/40 flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="text-xs text-slate-500 font-medium">
          Menampilkan {{ $transaksis->firstItem() }} - {{ $transaksis->lastItem() }} dari {{ $transaksis->total() }} transaksi
        </div>
        <div>
          {{ $transaksis->links() }}
        </div>
      </div>
    @endif
  </div>

</div>

{{-- ==================== 5. MODAL DETAIL TRANSAKSI ==================== --}}
<div id="modalDetailTrx" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="bg-white rounded-[24px] border border-[#E4F0D6] shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
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
      <button type="button" onclick="closeDetailModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 cursor-pointer">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>

    <div class="p-5 sm:p-6 overflow-y-auto space-y-4 text-xs sm:text-sm">
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

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="p-4 rounded-xl border border-[#E4F0D6] bg-white space-y-2">
          <div class="flex items-center gap-2 text-[#398D32] font-bold text-xs uppercase tracking-wider">
            <i data-lucide="user" class="w-3.5 h-3.5"></i>
            <span>Petani Penjual</span>
          </div>
          <div id="modalNamaPenjual" class="text-sm font-extrabold text-[#1A2D10]">-</div>
          <div class="text-xs text-slate-500">
            <div>Telp: <span id="modalTelpPenjual" class="font-medium text-slate-800">-</span></div>
          </div>
        </div>

        <div class="p-4 rounded-xl border border-[#E4F0D6] bg-white space-y-2">
          <div class="flex items-center gap-2 text-[#398D32] font-bold text-xs uppercase tracking-wider">
            <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
            <span>Informasi Pembeli</span>
          </div>
          <div id="modalNamaPembeli" class="text-sm font-extrabold text-[#1A2D10]">-</div>
          <div class="text-xs text-slate-500">
            <div>Telp: <span id="modalTelpPembeli" class="font-medium text-slate-800">-</span></div>
          </div>
        </div>
      </div>

      <div class="rounded-xl border border-[#E4F0D6] p-4 space-y-2 bg-[#FBFDF9]">
        <div class="flex justify-between items-center">
          <div>
            <div id="modalNamaProduk" class="font-bold text-[#1A2D10] text-sm">-</div>
            <div id="modalKomoditas" class="text-xs text-[#398D32]">-</div>
          </div>
          <div class="text-right">
            <span id="modalVolume" class="font-extrabold text-sm text-[#1A2D10]">-</span>
            <div class="text-[11px] text-slate-400">@ <span id="modalHargaSatuan">Rp 0</span></div>
          </div>
        </div>
        <div class="pt-2 border-t border-[#E4F0D6] flex justify-between font-extrabold text-[#1A2D10]">
          <span>Total Nilai:</span>
          <span id="modalTotalNilai">Rp 0</span>
        </div>
      </div>

      <div id="modalRiwayatBayarList" class="space-y-2"></div>
    </div>

    <div class="p-4 sm:p-5 border-t border-[#E4F0D6] bg-white flex justify-end">
      <button type="button" onclick="closeDetailModal()" class="px-4 py-2.5 rounded-xl border border-[#C5DFB0] text-xs font-bold text-[#4A6030] hover:bg-[#F5F8F1]">
        Tutup
      </button>
    </div>
  </div>
</div>

{{-- ==================== 6. MODAL CATAT TRANSAKSI BARU ==================== --}}
<div id="modalTambahTrx" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="bg-white rounded-[24px] border border-[#E4F0D6] shadow-xl w-full max-w-2xl max-h-[92vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
    <div class="p-5 sm:p-6 border-b border-[#E4F0D6] flex items-center justify-between bg-[#F5F8F1]/60">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-[#398D32] text-white flex items-center justify-center shrink-0">
          <i data-lucide="plus-circle" class="w-5 h-5"></i>
        </div>
        <div>
          <h3 class="text-base sm:text-lg font-extrabold text-[#1A2D10]">Catat Transaksi Penjualan</h3>
          <p class="text-xs text-[#4A6030] font-medium mt-0.5">{{ $kelompok?->nama_kelompok ?? 'Kelompok Tani' }}</p>
        </div>
      </div>
      <button type="button" onclick="closeModalTambahTrx()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 cursor-pointer">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>

    <form method="POST" action="{{ route('pengurus.penjualan.store') }}" class="flex flex-col flex-1 overflow-hidden">
      @csrf
      <div class="p-5 sm:p-6 overflow-y-auto space-y-4 text-xs sm:text-sm flex-1">
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
            <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">Komoditas</label>
            <select name="id_komoditas" class="w-full px-3 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">
              <option value="">-- Pilih Komoditas --</option>
              @foreach($komoditasList as $kmdt)
                <option value="{{ $kmdt->id_komoditas }}">{{ $kmdt->nama_komoditas }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div>
          <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">
            Nama Produk / Hasil Panen <span class="text-rose-500">*</span>
          </label>
          <input type="text" name="nama_produk" required placeholder="Contoh: Beras Ciherang 50kg"
            class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
          <div>
            <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">
              Nama Pembeli <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="nama_pembeli" required placeholder="Nama pembeli / toko"
              class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">
          </div>
          <div>
            <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">No. Telepon Pembeli</label>
            <input type="text" name="no_telepon_pembeli" placeholder="08xxxxxxxxxx"
              class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">
          </div>
        </div>

        <div class="grid grid-cols-3 gap-3">
          <div>
            <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">Volume <span class="text-rose-500">*</span></label>
            <input type="number" step="any" min="0.01" id="trxJumlah" name="jumlah" required placeholder="100"
              class="w-full px-3 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">
          </div>
          <div>
            <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">Satuan</label>
            <input type="text" name="satuan" value="kg" class="w-full px-3 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">
          </div>
          <div>
            <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">Harga / Satuan <span class="text-rose-500">*</span></label>
            <input type="number" min="1" id="trxHarga" name="harga_satuan" required placeholder="14000"
              class="w-full px-3 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">
          </div>
        </div>

        <div class="p-3 rounded-xl bg-[#EAF5E5] border border-[#C5DFB0] flex items-center justify-between text-xs font-semibold text-[#1A2D10]">
          <span>Total Nilai Transaksi:</span>
          <span id="trxPreviewTotal" class="text-sm font-extrabold text-[#398D32]">Rp 0</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
          <div>
            <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">Dibayar di Muka (Rp)</label>
            <input type="number" min="0" name="nominal_dibayar" placeholder="0 jika belum bayar"
              class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">
          </div>
          <div>
            <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">Metode Bayar <span class="text-rose-500">*</span></label>
            <select name="metode_pembayaran" required class="w-full px-3 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">
              <option value="transfer_bri">Transfer BRI</option>
              <option value="transfer">Transfer Bank Lainnya</option>
              <option value="tunai">Tunai / Cash</option>
              <option value="tempo">Tempo / Piutang</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
          <div>
            <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">Tanggal Transaksi <span class="text-rose-500">*</span></label>
            <input type="date" name="tanggal_transaksi" required value="{{ date('Y-m-d') }}"
              class="w-full px-3 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">
          </div>
          <div>
            <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">Status Penyerahan <span class="text-rose-500">*</span></label>
            <select name="status_penyerahan" required class="w-full px-3 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">
              <option value="diserahkan">Sudah Diserahkan</option>
              <option value="belum_diserahkan">Belum Diserahkan</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">Catatan</label>
          <textarea name="catatan" rows="2" placeholder="Catatan transaksi..."
            class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F5F8F1]/50 text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm"></textarea>
        </div>
      </div>

      <div class="p-4 sm:p-5 border-t border-[#E4F0D6] bg-[#F5F8F1]/40 flex justify-end gap-2.5">
        <button type="button" onclick="closeModalTambahTrx()" class="px-4 py-2.5 rounded-xl border border-[#C5DFB0] text-xs font-bold text-[#4A6030] hover:bg-white">
          Batal
        </button>
        <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#398D32] hover:bg-[#3D8024] text-white text-xs sm:text-sm font-bold shadow-xs">
          Simpan Transaksi
        </button>
      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
function openDetailModal(idPenjualan) {
  const modal = document.getElementById('modalDetailTrx');
  if (!modal) return;
  modal.classList.remove('hidden');

  fetch("{{ url('pengurus/laporan/transaksi') }}/" + idPenjualan, {
    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
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

    document.getElementById('modalNamaPembeli').innerText = trx.nama_pembeli || 'Pelanggan';
    document.getElementById('modalTelpPembeli').innerText = trx.no_telepon_pembeli || '-';

    document.getElementById('modalNamaProduk').innerText = trx.nama_produk || '-';
    document.getElementById('modalKomoditas').innerText = trx.komoditas ? trx.komoditas.nama_komoditas : 'Komoditas';
    document.getElementById('modalVolume').innerText = new Intl.NumberFormat('id-ID').format(trx.jumlah) + ' ' + (trx.satuan || 'kg');
    document.getElementById('modalHargaSatuan').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(trx.harga_satuan);
    document.getElementById('modalTotalNilai').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(trx.total_harga);

    if (window.lucide) window.lucide.createIcons();
  });
}

function closeDetailModal() {
  const modal = document.getElementById('modalDetailTrx');
  if (modal) modal.classList.add('hidden');
}

function openModalTambahTrx() {
  const modal = document.getElementById('modalTambahTrx');
  if (modal) modal.classList.remove('hidden');
  if (window.lucide) window.lucide.createIcons();
}

function closeModalTambahTrx() {
  const modal = document.getElementById('modalTambahTrx');
  if (modal) modal.classList.add('hidden');
}

const inputJml = document.getElementById('trxJumlah');
const inputHrg = document.getElementById('trxHarga');
const previewTot = document.getElementById('trxPreviewTotal');

function updateLiveTotal() {
  if (!inputJml || !inputHrg || !previewTot) return;
  const total = (parseFloat(inputJml.value) || 0) * (parseFloat(inputHrg.value) || 0);
  previewTot.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
}

if (inputJml && inputHrg) {
  inputJml.addEventListener('input', updateLiveTotal);
  inputHrg.addEventListener('input', updateLiveTotal);
}

document.addEventListener('DOMContentLoaded', function () {
  if (window.lucide) window.lucide.createIcons();
});
</script>
@endpush
