@extends('Pengurus.Layout._layout')

@section('title', 'Pengaturan')

@section('content')
<div class="space-y-6 max-w-5xl">

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
      <button type="button" onclick="document.getElementById('flash-banner').remove()" class="p-1 text-[#4A6030] hover:text-[#1A2D10] rounded-lg cursor-pointer">
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>
    </div>
  @endif

  @if($errors->any())
    <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs sm:text-sm space-y-1">
      <div class="flex items-center gap-2 font-bold text-red-900">
        <i data-lucide="alert-circle" class="w-4 h-4 text-red-600"></i>
        <span>Terdapat kesalahan input:</span>
      </div>
      <ul class="list-disc list-inside pl-6 text-red-700">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- ==================== TOP HEADER ==================== --}}
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-2xl sm:text-[28px] font-extrabold text-[#1A2D10] tracking-tight">
        Pengaturan Sistem
      </h1>
      <p class="text-xs sm:text-sm text-[#9AB880] mt-1 font-medium">
        Kelola profil admin, data kelembagaan kelompok tani, dan preferensi keamanan.
      </p>
    </div>
  </div>

  {{-- ==================== FORM CARDS ==================== --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- KIRI: PROFIL PENGURUS --}}
    <div class="lg:col-span-2 space-y-6">

      {{-- Card 1: Data Diri Admin --}}
      <div class="p-6 rounded-[22px] bg-white border border-[#E4F0D6] shadow-2xs space-y-5">
        <div class="flex items-center gap-3 pb-4 border-b border-[#E4F0D6]">
          <div class="w-10 h-10 rounded-xl bg-[#EAF5E5] text-[#398D32] flex items-center justify-center shrink-0">
            <i data-lucide="user-cog" class="w-5 h-5"></i>
          </div>
          <div>
            <h2 class="text-base font-extrabold text-[#1A2D10]">Profil Admin / Pengurus</h2>
            <p class="text-xs text-slate-500 font-medium">Informasi akun pengelola sistem PawonTani</p>
          </div>
        </div>

        <form method="POST" action="{{ route('pengurus.pengaturan.profil') }}" enctype="multipart/form-data" class="space-y-4 text-xs sm:text-sm">
          @csrf
          @method('PUT')

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">Nama Lengkap</label>
              <input type="text" name="nama" value="{{ old('nama', $user->nama) }}" required
                class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F8FAFC] text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">
            </div>

            <div>
              <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">Username Login</label>
              <input type="text" name="username" value="{{ old('username', $user->username) }}" required
                class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F8FAFC] text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">Email</label>
              <input type="email" name="email" value="{{ old('email', $user->email) }}"
                class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F8FAFC] text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">
            </div>

            <div>
              <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">No. Telepon / WhatsApp</label>
              <input type="text" name="no_telepon" value="{{ old('no_telepon', $user->no_telepon) }}"
                class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F8FAFC] text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">
            </div>
          </div>

          <div>
            <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">Alamat Domisili</label>
            <textarea name="alamat" rows="2"
              class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F8FAFC] text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">{{ old('alamat', $user->alamat) }}</textarea>
          </div>

          <div class="pt-2 flex justify-end">
            <button type="submit"
              class="px-5 py-2.5 rounded-xl bg-[#398D32] hover:bg-[#2d7027] text-white text-xs sm:text-sm font-bold shadow-xs transition-colors cursor-pointer flex items-center gap-2">
              <i data-lucide="save" class="w-4 h-4"></i>
              <span>Simpan Perubahan Profil</span>
            </button>
          </div>
        </form>
      </div>

      {{-- Card 2: Kelompok Tani Info --}}
      <div class="p-6 rounded-[22px] bg-white border border-[#E4F0D6] shadow-2xs space-y-5">
        <div class="flex items-center gap-3 pb-4 border-b border-[#E4F0D6]">
          <div class="w-10 h-10 rounded-xl bg-[#EAF5E5] text-[#398D32] flex items-center justify-center shrink-0">
            <i data-lucide="building-2" class="w-5 h-5"></i>
          </div>
          <div>
            <h2 class="text-base font-extrabold text-[#1A2D10]">Data Kelembagaan Kelompok Tani</h2>
            <p class="text-xs text-slate-500 font-medium">Informasi identitas poktan yang Anda kelola</p>
          </div>
        </div>

        <form method="POST" action="{{ route('pengurus.pengaturan.kelompok') }}" class="space-y-4 text-xs sm:text-sm">
          @csrf
          @method('PUT')

          <div>
            <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">Nama Kelompok Tani</label>
            <input type="text" name="nama_kelompok" value="{{ old('nama_kelompok', $kelompok?->nama_kelompok) }}" required
              class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F8FAFC] text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">
          </div>

          <div>
            <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">Alamat Sekretariat / Domisili</label>
            <textarea name="alamat" rows="2" required
              class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F8FAFC] text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">{{ old('alamat', $kelompok?->alamat) }}</textarea>
          </div>

          <div class="pt-2 flex justify-end">
            <button type="submit"
              class="px-5 py-2.5 rounded-xl bg-[#398D32] hover:bg-[#2d7027] text-white text-xs sm:text-sm font-bold shadow-xs transition-colors cursor-pointer flex items-center gap-2">
              <i data-lucide="save" class="w-4 h-4"></i>
              <span>Simpan Data Kelompok</span>
            </button>
          </div>
        </form>
      </div>

    </div>

    {{-- KANAN: KEAMANAN & GANTI KATA SANDI --}}
    <div class="space-y-6">

      {{-- Card Keamanan Akun --}}
      <div class="p-6 rounded-[22px] bg-white border border-[#E4F0D6] shadow-2xs space-y-5">
        <div class="flex items-center gap-3 pb-4 border-b border-[#E4F0D6]">
          <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
            <i data-lucide="shield-check" class="w-5 h-5"></i>
          </div>
          <div>
            <h2 class="text-base font-extrabold text-[#1A2D10]">Keamanan Akun</h2>
            <p class="text-xs text-slate-500 font-medium">Ubah kata sandi login admin</p>
          </div>
        </div>

        <form method="POST" action="{{ route('pengurus.pengaturan.password') }}" class="space-y-4 text-xs sm:text-sm">
          @csrf
          @method('PUT')

          <div>
            <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">Kata Sandi Saat Ini</label>
            <input type="password" name="current_password" required placeholder="••••••••"
              class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F8FAFC] text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">
          </div>

          <div>
            <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">Kata Sandi Baru (Min. 8 Karakter)</label>
            <input type="password" name="new_password" required placeholder="••••••••"
              class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F8FAFC] text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">
          </div>

          <div>
            <label class="block text-[11px] font-bold text-[#9AB880] uppercase tracking-wider mb-1.5">Konfirmasi Kata Sandi Baru</label>
            <input type="password" name="new_password_confirmation" required placeholder="••••••••"
              class="w-full px-3.5 py-2.5 rounded-xl border border-[#C5DFB0] bg-[#F8FAFC] text-[#1A2D10] focus:outline-none focus:border-[#398D32] focus:bg-white text-xs sm:text-sm">
          </div>

          <div class="pt-2">
            <button type="submit"
              class="w-full py-2.5 rounded-xl bg-[#398D32] hover:bg-[#2d7027] text-white text-xs sm:text-sm font-bold shadow-xs transition-colors cursor-pointer flex items-center justify-center gap-2">
              <i data-lucide="key" class="w-4 h-4"></i>
              <span>Perbarui Kata Sandi</span>
            </button>
          </div>
        </form>
      </div>

      {{-- Card Info Status --}}
      <div class="p-5 rounded-[22px] bg-[#EAF5E5] border border-[#B8D99B] text-xs space-y-2">
        <div class="flex items-center gap-2 font-bold text-[#24351B]">
          <i data-lucide="info" class="w-4 h-4 text-[#398D32]"></i>
          <span>Informasi Otoritas</span>
        </div>
        <p class="text-[#2E5A1C] leading-relaxed font-medium">
          Anda bertindak sebagai <strong>{{ $user->jabatan ?? 'Pengurus / Ketua' }}</strong> di <strong>{{ $kelompok?->nama_kelompok ?? 'Kelompok Tani' }}</strong>. Segala aktivitas pengelolaan data tersimpan dalam audit log sistem.
        </p>
      </div>

    </div>

  </div>

</div>
@endsection
