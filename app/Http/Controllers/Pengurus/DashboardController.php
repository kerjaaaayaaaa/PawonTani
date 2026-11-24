<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Models\AktivitasTanam;
use App\Models\Komoditas;
use App\Models\Lahan;
use App\Models\Panen;
use App\Models\Pengguna;
use App\Models\TransaksiPenjualan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        /** @var \App\Models\Pengguna|null $pengurus */
        $pengurus = Auth::user();
        $idKelompok = $pengurus?->id_kelompok;
        $kelompok = $pengurus?->kelompokTani;

        // 1. STATISTIK KPI UTAMA
        $totalAnggota = 0;
        $totalPetakLahan = 0;
        $totalLuasLahan = 0.0;
        $lahanTerverifikasi = 0;
        $lahanMenunggu = 0;
        $lahanPerbaikan = 0;
        $totalAktivitasBulanIni = 0;
        $totalHasilPanenKg = 0.0;
        $totalHasilPanenTon = 0.0;
        $panenMenunggu = 0;
        $totalTransaksi = 0;
        $totalOmzet = 0.0;
        $totalLaporanMenunggu = 0;

        $lahanMenungguTinjauan = collect();
        $aktivitasTerkini = collect();
        $sebaranKomoditas = collect();
        $grafikProduksiPanen = ['labels' => [], 'data' => []];

        if ($idKelompok) {
            // Total Anggota
            $totalAnggota = Pengguna::where('role', 'Anggota')
                ->where('id_kelompok', $idKelompok)
                ->count();

            // Total Petak & Luas Lahan
            $lahanQuery = Lahan::where(function ($q) use ($idKelompok) {
                $q->where('id_kelompok', $idKelompok)
                  ->orWhereHas('pengguna', fn ($uq) => $uq->where('id_kelompok', $idKelompok));
            });

            $totalPetakLahan = (clone $lahanQuery)->count();
            $totalLuasLahan = (float) (clone $lahanQuery)->sum('luas');

            // Status Verifikasi Lahan
            $lahanTerverifikasi = (clone $lahanQuery)->where('status_verifikasi', 'terverifikasi')->count();
            $lahanMenunggu = (clone $lahanQuery)->whereIn('status_verifikasi', ['menunggu', 'menunggu_verifikasi'])->count();
            $lahanPerbaikan = (clone $lahanQuery)->where('status_verifikasi', 'perlu_perbaikan')->count();

            // Total Hasil Panen & Panen Menunggu
            $panenBase = Panen::where('id_kelompok', $idKelompok);
            $totalHasilPanenKg = (float) (clone $panenBase)->where('status', 'disetujui')->sum('berat_kg');
            $totalHasilPanenTon = round($totalHasilPanenKg / 1000.0, 2);
            $panenMenunggu = (clone $panenBase)->where('status', 'menunggu')->count();

            // Total Transaksi & Omzet
            $trxBase = TransaksiPenjualan::whereHas('penjual', fn ($q) => $q->where('id_kelompok', $idKelompok));
            $totalTransaksi = (clone $trxBase)->count();
            $totalOmzet = (float) (clone $trxBase)->where('status_pembayaran', '!=', 'dibatalkan')->sum('total_harga');

            // Total Laporan Menunggu (Lahan + Panen)
            $totalLaporanMenunggu = $lahanMenunggu + $panenMenunggu;

            // Aktivitas 30 Hari Terakhir
            $totalAktivitasBulanIni = AktivitasTanam::where(function ($q) use ($idKelompok) {
                    $q->where('id_kelompok', $idKelompok)
                      ->orWhereHas('pengguna', fn ($uq) => $uq->where('id_kelompok', $idKelompok));
                })
                ->where('tanggal', '>=', Carbon::now()->subDays(30))
                ->count();

            // 2. DAFTAR LAHAN MENUNGGU VERIFIKASI / TINJAUAN (5 Terbaru)
            $lahanMenungguTinjauan = (clone $lahanQuery)
                ->with(['pengguna', 'komoditas'])
                ->whereIn('status_verifikasi', ['menunggu', 'menunggu_verifikasi', 'perlu_perbaikan'])
                ->latest('created_at')
                ->take(5)
                ->get();

            // 3. CATATAN AKTIVITAS TANAM PETANI TERKINI (5 Aktivitas Terakhir)
            $aktivitasTerkini = AktivitasTanam::with(['pengguna', 'lahan'])
                ->where(function ($q) use ($idKelompok) {
                    $q->where('id_kelompok', $idKelompok)
                      ->orWhereHas('pengguna', fn ($uq) => $uq->where('id_kelompok', $idKelompok));
                })
                ->orderByDesc('tanggal')
                ->orderByDesc('created_at')
                ->take(5)
                ->get();

            // 4. SEBARAN KOMODITAS POKTAN
            $sebaranKomoditas = Komoditas::whereHas('lahan', function ($q) use ($idKelompok) {
                    $q->where(function ($sq) use ($idKelompok) {
                        $sq->where('id_kelompok', $idKelompok)
                          ->orWhereHas('pengguna', fn ($uq) => $uq->where('id_kelompok', $idKelompok));
                    });
                })
                ->withCount(['lahan' => function ($q) use ($idKelompok) {
                    $q->where(function ($sq) use ($idKelompok) {
                        $sq->where('id_kelompok', $idKelompok)
                          ->orWhereHas('pengguna', fn ($uq) => $uq->where('id_kelompok', $idKelompok));
                    });
                }])
                ->withSum(['lahan' => function ($q) use ($idKelompok) {
                    $q->where(function ($sq) use ($idKelompok) {
                        $sq->where('id_kelompok', $idKelompok)
                          ->orWhereHas('pengguna', fn ($uq) => $uq->where('id_kelompok', $idKelompok));
                    });
                }], 'luas')
                ->orderByDesc('lahan_sum_luas')
                ->take(5)
                ->get();

            // 5. GRAFIK PRODUKSI PANEN 6 BULAN TERAKHIR
            $months = [];
            $dataPanen = [];
            for ($i = 5; $i >= 0; $i--) {
                $d = Carbon::now()->subMonths($i);
                $mStart = $d->copy()->startOfMonth()->toDateString();
                $mEnd = $d->copy()->endOfMonth()->toDateString();
                $months[] = $d->locale('id')->isoFormat('MMM Y');

                $ton = (float) Panen::where('id_kelompok', $idKelompok)
                    ->where('status', 'disetujui')
                    ->whereBetween('tanggal_panen', [$mStart, $mEnd])
                    ->sum('berat_kg') / 1000.0;
                $dataPanen[] = round($ton, 2);
            }
            $grafikProduksiPanen = [
                'labels' => $months,
                'data' => $dataPanen,
            ];
        }

        // Sapaan Waktu Indonesia
        $hour = (int) date('H');
        if ($hour >= 5 && $hour < 11) {
            $sapaanWaktu = 'Selamat Pagi';
        } elseif ($hour >= 11 && $hour < 15) {
            $sapaanWaktu = 'Selamat Siang';
        } elseif ($hour >= 15 && $hour < 18) {
            $sapaanWaktu = 'Selamat Sore';
        } else {
            $sapaanWaktu = 'Selamat Malam';
        }

        return view('Pengurus.Content.Dashboard', compact(
            'pengurus',
            'kelompok',
            'sapaanWaktu',
            'totalAnggota',
            'totalPetakLahan',
            'totalLuasLahan',
            'lahanTerverifikasi',
            'lahanMenunggu',
            'lahanPerbaikan',
            'totalHasilPanenKg',
            'totalHasilPanenTon',
            'panenMenunggu',
            'totalTransaksi',
            'totalOmzet',
            'totalLaporanMenunggu',
            'totalAktivitasBulanIni',
            'lahanMenungguTinjauan',
            'aktivitasTerkini',
            'sebaranKomoditas',
            'grafikProduksiPanen'
        ));
    }
}
