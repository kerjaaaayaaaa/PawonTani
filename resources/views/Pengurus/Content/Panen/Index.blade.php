@extends('Pengurus.Layout._layout')

@section('title', 'Kelola Panen')

@section('content')
<div class="space-y-6">

  {{-- ==================== FLASH NOTIFICATIONS ==================== --}}
  @if(session('success'))
    <div id="flash-banner" class="flex items-center justify-between p-4 rounded-2xl bg-[#EAF5E5] border border-[#B8D99B] text-[#1A2D10] text-xs sm:text-sm shadow-xs animate-in fade-in duration-200">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-xl bg-[#398D32] text-white flex items-center justify-center shrink-0">
          <i data-lucide="check" class="w-4 h-4 stroke-[3]"></i>
        </div>
        <div>
          <span class="font-bold">Berhasil!</span>
          <span class="text-[#24351B] ml-1">{{ session('success') }}</span>
        </div>
      </div>
      <button type="button" onclick="document.getElementById('flash-banner').remove()" class="p-1 text-[#4A6030] hover:text-[#1A2D10] rounded-lg cursor-pointer" aria-label="Tutup">
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

  {{-- ==================== 1. TOP HEADER ==================== --}}
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-2xl sm:text-[28px] font-extrabold text-[#1A2D10] tracking-tight">
        Kelola Panen
      </h1>
      <p class="text-xs sm:text-sm text-[#9AB880] mt-1 font-medium">
        Pantau, verifikasi, dan kelola catatan hasil panen anggota {{ $kelompok?->nama_kelompok ?? 'Kelompok Tani' }}.
      </p>
    </div>

    <div class="flex items-center gap-2.5 shrink-0">
      <a href="{{ route('pengurus.panen') }}"
        class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-white text-xs sm:text-sm font-semibold text-[#4A6030] hover:bg-[#F5F8F1] hover:text-[#1A2D10] hover:border-[#9AB880] transition-all shadow-2xs cursor-pointer"
        title="Muat Ulang Halaman">
        <i data-lucide="refresh-cw" class="w-4 h-4 text-[#398D32]"></i>
        <span>Segarkan</span>
      </a>
    </div>
  </div>

  {{-- ==================== 2. SUMMARY METRICS CARDS ==================== --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

    <!-- Card 1: Total Catatan Panen -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-[#EAF5E5] text-[#398D32] flex items-center justify-center shrink-0">
        <i data-lucide="wheat" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-[#9AB880] uppercase tracking-wider block">Total Panen</span>
        <span class="text-2xl font-extrabold text-[#1A2D10] tracking-tight">{{ $stats['total'] ?? 0 }}</span>
        <span class="text-[11px] text-[#4A6030] block mt-0.5 font-medium">Total volume: {{ number_format($stats['total_berat_ton'] ?? 0, 2, ',', '.') }} Ton</span>
      </div>
    </div>

    <!-- Card 2: Menunggu Verifikasi -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
        <i data-lucide="clock" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-amber-700 uppercase tracking-wider block">Menunggu</span>
        <span class="text-2xl font-extrabold text-amber-900 tracking-tight">{{ $stats['menunggu'] ?? 0 }}</span>
        <span class="text-[11px] text-amber-800 block mt-0.5 font-medium">Perlu ditinjau pengurus</span>
      </div>
    </div>

    <!-- Card 3: Disetujui -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
        <i data-lucide="check-circle-2" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wider block">Disetujui</span>
        <span class="text-2xl font-extrabold text-emerald-900 tracking-tight">{{ $stats['disetujui'] ?? 0 }}</span>
        <span class="text-[11px] text-emerald-800 block mt-0.5 font-medium">Validasi poktan selesai</span>
      </div>
    </div>

    <!-- Card 4: Ditolak -->
    <div class="p-5 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs hover:shadow-xs transition-shadow flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
        <i data-lucide="x-circle" class="w-6 h-6"></i>
      </div>
      <div>
        <span class="text-xs font-semibold text-rose-700 uppercase tracking-wider block">Ditolak</span>
        <span class="text-2xl font-extrabold text-rose-900 tracking-tight">{{ $stats['ditolak'] ?? 0 }}</span>
        <span class="text-[11px] text-rose-800 block mt-0.5 font-medium">Data ditolak / tidak sesuai</span>
      </div>
    </div>

  </div>

  {{-- ==================== 3. FILTER & SEARCH BAR ==================== --}}
  <div class="p-4 rounded-[18px] bg-white border border-[#E4F0D6] shadow-2xs">
    <form method="GET" action="{{ route('pengurus.panen') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">

      <!-- Search Input: Kode Panen, Petani, Varietas, Lahan -->
      <div class="lg:col-span-4 relative">
        <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">Pencarian Data</label>
        <div class="relative">
          <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-[#9AB880]"></i>
          <input type="text"
                 name="q"
                 value="{{ request('q') }}"
                 placeholder="Cari kode panen, petani, varietas, atau lahan..."
                 class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F8FAFC] text-xs sm:text-sm text-[#1A2D10] placeholder-[#9AB880] focus:outline-none focus:border-[#398D32] focus:bg-white transition-all">
        </div>
      </div>

      <!-- Filter Status -->
      <div class="lg:col-span-2">
        <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">Status</label>
        <select name="status" class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F8FAFC] text-xs sm:text-sm text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white transition-all">
          <option value="">Semua Status</option>
          <option value="menunggu" {{ request('status') === 'menunggu' ? 'selected' : '' }}>Menunggu Verifikasi</option>
          <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
          <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
        </select>
      </div>

      <!-- Filter Tanggal Dari -->
      <div class="lg:col-span-2">
        <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">Tanggal Dari</label>
        <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}"
          class="w-full px-3 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F8FAFC] text-xs sm:text-sm text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white transition-all">
      </div>

      <!-- Filter Tanggal Sampai -->
      <div class="lg:col-span-2">
        <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">Tanggal Sampai</label>
        <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}"
          class="w-full px-3 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F8FAFC] text-xs sm:text-sm text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white transition-all">
      </div>

      <!-- Buttons -->
      <div class="lg:col-span-2 flex items-center gap-2">
        <button type="submit" class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-[#398D32] text-white text-xs sm:text-sm font-semibold hover:bg-[#2d7027] transition-colors shadow-xs cursor-pointer">
          <i data-lucide="filter" class="w-3.5 h-3.5"></i>
          <span>Filter</span>
        </button>
        @if(request()->hasAny(['q', 'status', 'komoditas', 'tanggal_dari', 'tanggal_sampai']))
          <a href="{{ route('pengurus.panen') }}" class="p-2.5 rounded-xl border border-[#C5DFB0] text-[#4A6030] hover:text-[#1A2D10] hover:bg-[#F5F8F1] transition-colors" title="Reset Filter">
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
          <tr class="bg-[#F8FAFC] border-b border-[#E4F0D6] text-[#4A6030] font-bold uppercase tracking-wider text-[11px]">
            <th class="py-3.5 px-4">Kode Panen</th>
            <th class="py-3.5 px-4">Nama Petani</th>
            <th class="py-3.5 px-4">Lahan</th>
            <th class="py-3.5 px-4">Komoditas</th>
            <th class="py-3.5 px-4">Varietas</th>
            <th class="py-3.5 px-4">Tanggal Panen</th>
            <th class="py-3.5 px-4 text-right">Luas Panen</th>
            <th class="py-3.5 px-4 text-right">Hasil Panen</th>
            <th class="py-3.5 px-4 text-center">Status</th>
            <th class="py-3.5 px-4 text-center w-36">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#E4F0D6] text-[#1A2D10]">
          @forelse($panens as $item)
            @php
              $statusBadge = match($item->status) {
                'disetujui' => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'label' => 'DISETUJUI', 'icon' => 'check-circle-2'],
                'ditolak' => ['bg' => 'bg-rose-50 text-rose-700 border-rose-200', 'label' => 'DITOLAK', 'icon' => 'x-circle'],
                default => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'label' => 'MENUNGGU', 'icon' => 'clock'],
              };
              $beratTon = round(((float)$item->berat_kg) / 1000.0, 2);
            @endphp
            <tr class="hover:bg-[#F8FAFC]/80 transition-colors">
              {{-- Kode Panen --}}
              <td class="py-4 px-4 align-top">
                <span class="font-bold text-[#1A2D10] font-mono">{{ $item->kode_panen }}</span>
              </td>

              {{-- Nama Petani --}}
              <td class="py-4 px-4 align-top">
                <div class="font-semibold text-[#1A2D10]">{{ $item->pengguna?->nama ?? 'Anggota Poktan' }}</div>
                <div class="text-[11px] text-[#9AB880] mt-0.5">{{ $item->pengguna?->no_telepon ?? '-' }}</div>
              </td>

              {{-- Lahan --}}
              <td class="py-4 px-4 align-top">
                <div class="font-semibold text-[#1A2D10]">{{ $item->lahan?->nama_lahan ?? 'Petak Lahan' }}</div>
                <div class="text-[11px] text-slate-500">{{ $item->lahan?->alamat_lahan ?? '-' }}</div>
              </td>

              {{-- Komoditas --}}
              <td class="py-4 px-4 align-top">
                <span class="font-semibold text-[#398D32]">{{ $item->komoditas?->nama_komoditas ?? ($item->komoditas_nama ?? 'Padi') }}</span>
              </td>

              {{-- Varietas --}}
              <td class="py-4 px-4 align-top">
                <span class="font-medium text-slate-700">{{ $item->varietas ?? '-' }}</span>
              </td>

              {{-- Tanggal Panen --}}
              <td class="py-4 px-4 align-top">
                <div class="text-xs font-semibold text-[#1A2D10]">
                  {{ $item->tanggal_panen ? $item->tanggal_panen->translatedFormat('d M Y') : '-' }}
                </div>
              </td>

              {{-- Luas Panen --}}
              <td class="py-4 px-4 align-top text-right">
                <span class="font-semibold text-slate-700">
                  {{ $item->lahan?->luas ? number_format($item->lahan->luas, 2) . ' Ha' : ($item->luas_panen ? number_format($item->luas_panen, 2) . ' Ha' : '-') }}
                </span>
              </td>

              {{-- Jumlah Hasil Panen --}}
              <td class="py-4 px-4 align-top text-right">
                <div class="font-extrabold text-[#1A2D10] text-sm">{{ number_format((float)$item->berat_kg, 0, ',', '.') }} kg</div>
                <div class="text-[11px] text-[#398D32] font-semibold">≈ {{ $beratTon }} Ton</div>
              </td>

              {{-- Status --}}
              <td class="py-4 px-4 align-top text-center">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold border {{ $statusBadge['bg'] }}">
                  <i data-lucide="{{ $statusBadge['icon'] }}" class="w-3 h-3"></i>
                  <span>{{ $statusBadge['label'] }}</span>
                </span>
                @if($item->status === 'ditolak' && $item->catatan_verifikasi)
                  <div class="text-[10px] text-rose-700 mt-1 italic max-w-[130px] truncate mx-auto" title="{{ $item->catatan_verifikasi }}">
                    "{{ $item->catatan_verifikasi }}"
                  </div>
                @endif
              </td>

              {{-- Aksi: Detail, Setujui, Tolak --}}
              <td class="py-4 px-4 align-top text-center">
                <div class="inline-flex items-center gap-1.5 flex-wrap justify-center">
                  {{-- Tombol Detail --}}
                  <a href="{{ route('pengurus.panen.show', $item->id_panen) }}"
                     class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-[#EAF5E5] text-[#398D32] hover:bg-[#398D32] hover:text-white text-xs font-semibold transition-all"
                     title="Lihat Detail & Foto Panen">
                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                    <span>Detail</span>
                  </a>

                  @if($item->status === 'menunggu')
                    {{-- Tombol Setujui --}}
                    <form method="POST" action="{{ route('pengurus.panen.verifikasi', $item->id_panen) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin MENYETUJUI hasil panen ini?')">
                      @csrf
                      <input type="hidden" name="status" value="disetujui">
                      <button type="submit" class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all cursor-pointer" title="Setujui Laporan">
                        <i data-lucide="check" class="w-3.5 h-3.5 stroke-[3]"></i>
                      </button>
                    </form>

                    {{-- Tombol Tolak --}}
                    <button type="button"
                      onclick="openModalTolak('{{ $item->id_panen }}', '{{ $item->kode_panen }}')"
                      class="p-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-all cursor-pointer" title="Tolak Laporan">
                      <i data-lucide="x" class="w-3.5 h-3.5 stroke-[3]"></i>
                    </button>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="10" class="py-12 px-4 text-center">
                <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                  <div class="w-14 h-14 rounded-2xl bg-[#F8FAFC] text-[#9AB880] flex items-center justify-center mb-3">
                    <i data-lucide="inbox" class="w-7 h-7"></i>
                  </div>
                  <h3 class="text-sm sm:text-base font-bold text-[#1A2D10]">Belum Ada Catatan Hasil Panen</h3>
                  <p class="text-xs text-[#9AB880] mt-1 text-center">
                    Anggota kelompok tani Anda belum mengirimkan pencatatan panen dari aplikasi mobile atau filter pencarian tidak menemukan hasil.
                  </p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination Footer --}}
    @if($panens->hasPages())
      <div class="p-4 bg-[#F8FAFC] border-t border-[#E4F0D6]">
        {{ $panens->links() }}
      </div>
    @endif
  </div>

</div>

{{-- ==================== MODAL TOLAK PANEN DENGAN ALASAN ==================== --}}
<div id="modalTolakPanen" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="bg-white rounded-[22px] border border-[#E4F0D6] shadow-xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in-95 duration-200">
    <div class="p-5 border-b border-[#E4F0D6] flex items-center justify-between bg-rose-50/60">
      <div class="flex items-center gap-2.5 text-rose-800">
        <i data-lucide="alert-octagon" class="w-5 h-5 text-rose-600"></i>
        <h3 class="text-base font-extrabold text-[#1A2D10]">Tolak Laporan Panen</h3>
      </div>
      <button type="button" onclick="closeModalTolak()" class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg">
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>
    </div>

    <form id="formTolakPanen" method="POST" action="" class="p-5 space-y-4">
      @csrf
      <input type="hidden" name="status" value="ditolak">

      <p class="text-xs text-slate-600 leading-relaxed">
        Anda akan menolak laporan hasil panen <strong id="tolakKodePanen" class="text-rose-700 font-mono">TRX-PANEN</strong>. Sesuai aturan, <strong>alasan penolakan wajib diisi</strong> agar petani mengetahui perbaikan yang diperlukan.
      </p>

      <div>
        <label class="block text-xs font-bold text-[#1A2D10] uppercase mb-1.5">
          Alasan Penolakan <span class="text-rose-500">*</span>
        </label>
        <textarea name="catatan_verifikasi" required rows="3"
          placeholder="Contoh: Berat panen tidak sesuai timbangan di lapangan, silakan timbang ulang bersama pengurus."
          class="w-full p-3 rounded-xl border border-rose-300 bg-rose-50/20 text-xs sm:text-sm text-[#1A2D10] placeholder-slate-400 focus:outline-none focus:border-rose-500 focus:bg-white transition-all"></textarea>
      </div>

      <div class="flex justify-end gap-2.5 pt-2">
        <button type="button" onclick="closeModalTolak()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-100">
          Batal
        </button>
        <button type="submit" class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs">
          Konfirmasi Tolak Panen
        </button>
      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
function openModalTolak(idPanen, kodePanen) {
  const modal = document.getElementById('modalTolakPanen');
  const form = document.getElementById('formTolakPanen');
  const kodeEl = document.getElementById('tolakKodePanen');
  if (!modal || !form) return;

  form.action = "{{ url('pengurus/panen') }}/" + idPanen + "/verifikasi";
  kodeEl.innerText = kodePanen;
  modal.classList.remove('hidden');
  if (window.lucide) window.lucide.createIcons();
}

function closeModalTolak() {
  const modal = document.getElementById('modalTolakPanen');
  if (modal) modal.classList.add('hidden');
}
</script>
@endpush
