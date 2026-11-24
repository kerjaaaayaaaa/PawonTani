<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Models\KelompokTani;
use App\Models\Pengguna;
use App\Services\AuditLogger;
use App\Services\CloudinaryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PengaturanController extends Controller
{
    /**
     * Menampilkan halaman Pengaturan Admin / Pengurus.
     */
    public function index(Request $request): View
    {
        /** @var Pengguna $user */
        $user = Auth::user();
        $user->load('kelompokTani');
        $kelompok = $user->kelompokTani;

        return view('Pengurus.Content.Pengaturan', compact('user', 'kelompok'));
    }

    /**
     * Memperbarui profil pengurus.
     */
    public function updateProfil(Request $request, CloudinaryService $cloudinary): RedirectResponse
    {
        /** @var Pengguna $user */
        $user = Auth::user();

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'alpha_dash',
                Rule::unique('pengguna', 'username')->ignore($user->id_pengguna, 'id_pengguna'),
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('pengguna', 'email')->ignore($user->id_pengguna, 'id_pengguna'),
            ],
            'no_telepon' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'foto_profil' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if ($request->hasFile('foto_profil')) {
            $foto = $request->file('foto_profil');
            $upload = $cloudinary->uploadPhoto($foto, 'pawontani/profil');
            $validated['foto_profil'] = $upload['secure_url'] ?? $upload['url'] ?? null;
        }

        $user->update($validated);

        return redirect()->route('pengurus.pengaturan')->with('success', 'Profil admin berhasil diperbarui.');
    }

    /**
     * Mengubah kata sandi pengurus.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var Pengguna $user */
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'new_password.min' => 'Kata sandi baru minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'Kata sandi saat ini salah.']);
        }

        $user->update([
            'password' => Hash::make($request->input('new_password')),
        ]);

        return redirect()->route('pengurus.pengaturan')->with('success', 'Kata sandi berhasil diperbarui.');
    }

    /**
     * Memperbarui data kelembagaan Kelompok Tani.
     */
    public function updateKelompok(Request $request): RedirectResponse
    {
        /** @var Pengguna $user */
        $user = Auth::user();

        $kelompok = $user->kelompokTani;
        abort_unless($kelompok, 404, 'Kelompok tani tidak ditemukan.');

        $validated = $request->validate([
            'nama_kelompok' => ['required', 'string', 'max:150', Rule::unique('kelompok_tani', 'nama_kelompok')->ignore($kelompok->id_kelompok, 'id_kelompok')],
            'alamat' => ['required', 'string', 'max:500'],
        ]);

        $kelompok->update($validated);

        return redirect()->route('pengurus.pengaturan')->with('success', 'Informasi kelompok tani berhasil diperbarui.');
    }
}
