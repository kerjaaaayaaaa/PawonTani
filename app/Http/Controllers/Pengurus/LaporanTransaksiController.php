<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Models\Komoditas;
use App\Models\Pengguna;
use App\Models\TransaksiPenjualan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanTransaksiController extends Controller
{
    /**
     * Menampilkan dashboard Laporan Transaksi Penjualan Kelompok Tani.
     */
    public function index(Request $request): View
    {
        /** @var \App\Models\Pengguna $pengurus */
        $pengurus = $request->user();

        abort_unless(
            $pengurus && $pengurus->status === 'Aktif' && $pengurus->role === 'Pengurus' && !empty($pengurus->id_kelompok),
            403,
            'Akses ditolak. Anda harus menjadi Pengurus aktif yang terdaftar dalam kelompok tani.'
        );

        $kelompok = $pengurus->kelompokTani;

        // Base query transaksi khusus anggota & pengurus dalam kelompok tani yang sama
        $baseQuery = TransaksiPenjualan::whereHas('penjual', function ($q) use ($pengurus) {
            $q->where('id_kelompok', $pengurus->id_kelompok);
        });

        // Query terfilter
        $query = clone $baseQuery;
        $this->applyFilters($query, $request);

        // Hitung Metrik Ringkasan KPI dari query terfilter
        $metricsQuery = clone $query;
        $activeMetricsQuery = (clone $query)->where('status_pembayaran', '!=', 'dibatalkan');

        $totalTransaksi = (clone $metricsQuery)->count();
        $totalNilaiTransaksi = (float) (clone $activeMetricsQuery)->sum('total_harga');
        $totalTerbayar = (float) (clone $activeMetricsQuery)->sum('total_dibayar');
        $totalSisaPiutang = max(0.0, $totalNilaiTransaksi - $totalTerbayar);
        $totalVolumeKg = (float) (clone $activeMetricsQuery)->sum('jumlah');

        $totalLunasCount = (clone $metricsQuery)->where('status_pembayaran', 'lunas')->count();
        $totalDpTempoCount = (clone $metricsQuery)->whereIn('status_pembayaran', ['dp', 'tempo'])->count();
        $totalBelumBayarCount = (clone $metricsQuery)->where('status_pembayaran', 'belum_dibayar')->count();
        $totalBatalCount = (clone $metricsQuery)->where('status_pembayaran', 'dibatalkan')->count();

        $persenLunas = $totalTransaksi > 0 ? round(($totalLunasCount / $totalTransaksi) * 100, 1) : 0;
        $persenTerbayar = $totalNilaiTransaksi > 0 ? round(($totalTerbayar / $totalNilaiTransaksi) * 100, 1) : 0;

        $stats = [
            'total_transaksi' => $totalTransaksi,
            'total_omzet' => $totalNilaiTransaksi,
            'total_terbayar' => $totalTerbayar,
            'total_piutang' => $totalSisaPiutang,
            'total_volume_kg' => $totalVolumeKg,
            'lunas_count' => $totalLunasCount,
            'dp_tempo_count' => $totalDpTempoCount,
            'belum_bayar_count' => $totalBelumBayarCount,
            'batal_count' => $totalBatalCount,
            'persen_lunas' => $persenLunas,
            'persen_terbayar' => $persenTerbayar,
        ];

        // Tren Penjualan 6 Bulan Terakhir untuk Chart
        $chartTren = $this->getMonthlyTrend($pengurus->id_kelompok);

        // Sebaran Komoditas Terlaris untuk Chart / Visualisasi
        $chartKomoditas = $this->getCommodityDistribution($pengurus->id_kelompok);

        // Data transaksi untuk tabel dengan eager loading
        $transaksis = $query->with([
            'penjual:id_pengguna,nama,no_telepon,foto_profil',
            'komoditas:id_komoditas,nama_komoditas',
            'produk:id_produk,nama_produk,foto_produk',
            'riwayatPembayaran',
        ])
        ->orderByDesc('tanggal_transaksi')
        ->orderByDesc('created_at')
        ->paginate(10)
        ->withQueryString();

        // Data master untuk filter dropdown
        $komoditasList = Komoditas::orderBy('nama_komoditas')->get(['id_komoditas', 'nama_komoditas']);
        $petaniList = Pengguna::where('id_kelompok', $pengurus->id_kelompok)
            ->where('status', 'Aktif')
            ->orderBy('nama')
            ->get(['id_pengguna', 'nama', 'role']);

        return view('Pengurus.Content.Laporan.Transaksi', compact(
            'kelompok',
            'pengurus',
            'stats',
            'transaksis',
            'chartTren',
            'chartKomoditas',
            'komoditasList',
            'petaniList'
        ));
    }

    /**
     * Detail transaksi untuk modal / inspect via AJAX.
     */
    public function show(Request $request, string $id)
    {
        /** @var \App\Models\Pengguna $pengurus */
        $pengurus = $request->user();

        abort_unless(
            $pengurus && $pengurus->status === 'Aktif' && $pengurus->role === 'Pengurus' && !empty($pengurus->id_kelompok),
            403
        );

        $trx = TransaksiPenjualan::with([
            'penjual:id_pengguna,nama,no_telepon,email,alamat,id_kelompok',
            'komoditas:id_komoditas,nama_komoditas',
            'produk:id_produk,nama_produk,foto_produk',
            'riwayatPembayaran',
        ])->where('id_penjualan', $id)->firstOrFail();

        // Pastikan transaksi milik anggota dari kelompok tani pengurus
        if ($trx->penjual?->id_kelompok !== $pengurus->id_kelompok) {
            abort(403, 'Akses ditolak. Transaksi berada di luar kewenangan kelompok tani Anda.');
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $trx,
            ]);
        }

        return redirect()->route('pengurus.laporan', ['q' => $trx->kode_transaksi]);
    }

    /**
     * Catat transaksi penjualan baru oleh Pengurus Kelompok Tani.
     */
    public function store(Request $request)
    {
        /** @var \App\Models\Pengguna $pengurus */
        $pengurus = $request->user();

        abort_unless(
            $pengurus && $pengurus->status === 'Aktif' && $pengurus->role === 'Pengurus' && !empty($pengurus->id_kelompok),
            403
        );

        $validated = $request->validate([
            'id_pengguna' => 'required|exists:pengguna,id_pengguna',
            'nama_produk' => 'required|string|max:150',
            'id_komoditas' => 'nullable|exists:komoditas,id_komoditas',
            'nama_pembeli' => 'required|string|max:150',
            'no_telepon_pembeli' => 'nullable|string|max:30',
            'jumlah' => 'required|numeric|min:0.01',
            'satuan' => 'nullable|string|max:30',
            'harga_satuan' => 'required|numeric|min:1',
            'nominal_dibayar' => 'nullable|numeric|min:0',
            'status_pembayaran' => 'nullable|string|in:lunas,dp,tempo,belum_dibayar',
            'status_penyerahan' => 'nullable|string|in:diserahkan,belum_diserahkan',
            'metode_pembayaran' => 'required|string|max:50',
            'tanggal_transaksi' => 'required|date',
            'catatan' => 'nullable|string|max:500',
        ]);

        // Pastikan petani adalah anggota kelompok tani pengurus
        $petani = Pengguna::where('id_pengguna', $validated['id_pengguna'])
            ->where('id_kelompok', $pengurus->id_kelompok)
            ->firstOrFail();

        $jumlah = (float) $validated['jumlah'];
        $hargaSatuan = (float) $validated['harga_satuan'];
        $totalHarga = $jumlah * $hargaSatuan;
        $nominalDibayar = min($totalHarga, (float) ($validated['nominal_dibayar'] ?? 0));

        if ($nominalDibayar >= $totalHarga) {
            $statusBayar = 'lunas';
        } elseif ($nominalDibayar > 0) {
            $statusBayar = 'dp';
        } else {
            $statusBayar = $validated['status_pembayaran'] ?? 'belum_dibayar';
        }

        $trx = TransaksiPenjualan::create([
            'id_pengguna' => $petani->id_pengguna,
            'id_komoditas' => $validated['id_komoditas'] ?? null,
            'nama_produk' => $validated['nama_produk'],
            'nama_pembeli' => $validated['nama_pembeli'],
            'no_telepon_pembeli' => $validated['no_telepon_pembeli'] ?? null,
            'jumlah' => $jumlah,
            'satuan' => $validated['satuan'] ?: 'kg',
            'harga_satuan' => $hargaSatuan,
            'total_harga' => $totalHarga,
            'total_dibayar' => $nominalDibayar,
            'status_pembayaran' => $statusBayar,
            'status_penyerahan' => $validated['status_penyerahan'] ?? 'belum_diserahkan',
            'tanggal_transaksi' => $validated['tanggal_transaksi'],
            'waktu_transaksi' => now()->format('H:i'),
            'metode_pembayaran' => $validated['metode_pembayaran'],
            'catatan' => $validated['catatan'] ?? null,
            'tanggal_diserahkan' => ($validated['status_penyerahan'] ?? '') === 'diserahkan' ? now() : null,
        ]);

        if ($nominalDibayar > 0) {
            \App\Models\RiwayatPembayaranPenjualan::create([
                'id_pembayaran' => 'PAY-' . $trx->kode_transaksi . '-1',
                'id_penjualan' => $trx->id_penjualan,
                'nominal' => $nominalDibayar,
                'tanggal_pembayaran' => $validated['tanggal_transaksi'],
                'keterangan' => $statusBayar === 'lunas' ? 'Pembayaran Lunas' : 'Pembayaran Uang Muka (DP)',
                'status_verifikasi' => 'terverifikasi',
            ]);
        }

        return redirect()->back()->with('success', 'Transaksi penjualan ' . $trx->kode_transaksi . ' berhasil dicatat.');
    }

    /**
     * Unduh Laporan Transaksi ke format CSV (Excel Friendly UTF-8 BOM).
     */
    public function export(Request $request): StreamedResponse
    {
        /** @var \App\Models\Pengguna $pengurus */
        $pengurus = $request->user();

        abort_unless(
            $pengurus && $pengurus->status === 'Aktif' && $pengurus->role === 'Pengurus' && !empty($pengurus->id_kelompok),
            403
        );

        $query = TransaksiPenjualan::whereHas('penjual', function ($q) use ($pengurus) {
            $q->where('id_kelompok', $pengurus->id_kelompok);
        })->with(['penjual', 'komoditas', 'produk']);

        $this->applyFilters($query, $request);

        $items = $query->orderByDesc('tanggal_transaksi')->get();

        $timestamp = now()->format('Ymd_His');
        $namaKelompokClean = preg_replace('/[^A-Za-z0-9_-]/', '_', $pengurus->kelompokTani?->nama_kelompok ?? 'Poktan');
        $filename = "Laporan_Transaksi_{$namaKelompokClean}_{$timestamp}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($items) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM untuk Microsoft Excel agar aksen & karakter terbaca rapi
            fwrite($handle, "\xEF\xBB\xBF");

            // Header Kolom
            fputcsv($handle, [
                'No',
                'Kode Transaksi',
                'Tanggal Transaksi',
                'Waktu',
                'Nama Petani (Penjual)',
                'Nama Pembeli',
                'No. Telepon Pembeli',
                'Nama Produk',
                'Komoditas',
                'Volume',
                'Satuan',
                'Harga Satuan (Rp)',
                'Total Nilai (Rp)',
                'Total Terbayar (Rp)',
                'Sisa Piutang (Rp)',
                'Status Pembayaran',
                'Status Penyerahan',
                'Metode Pembayaran',
                'Tanggal Diserahkan',
                'Catatan',
            ], ';');

            $no = 1;
            foreach ($items as $trx) {
                fputcsv($handle, [
                    $no++,
                    $trx->kode_transaksi,
                    $trx->tanggal_transaksi ? Carbon::parse($trx->tanggal_transaksi)->format('d/m/Y') : '-',
                    $trx->waktu_transaksi ?: ($trx->created_at ? $trx->created_at->format('H:i') : '-'),
                    $trx->penjual?->nama ?? 'Anggota Poktan',
                    $trx->nama_pembeli ?: 'Pelanggan',
                    $trx->no_telepon_pembeli ?: '-',
                    $trx->nama_produk,
                    $trx->komoditas?->nama_komoditas ?: '-',
                    number_format((float) $trx->jumlah, 2, ',', '.'),
                    $trx->satuan ?: 'kg',
                    number_format((float) $trx->harga_satuan, 0, ',', '.'),
                    number_format((float) $trx->total_harga, 0, ',', '.'),
                    number_format((float) $trx->total_dibayar, 0, ',', '.'),
                    number_format((float) $trx->sisa_pembayaran, 0, ',', '.'),
                    ucwords(str_replace('_', ' ', $trx->status_pembayaran)),
                    ucwords(str_replace('_', ' ', $trx->status_penyerahan)),
                    ucwords(str_replace('_', ' ', $trx->metode_pembayaran)),
                    $trx->tanggal_diserahkan ? Carbon::parse($trx->tanggal_diserahkan)->format('d/m/Y H:i') : '-',
                    $trx->catatan ?: '-',
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Halaman Cetak (Print View) Laporan Resmi Kelompok Tani.
     */
    public function print(Request $request): View
    {
        /** @var \App\Models\Pengguna $pengurus */
        $pengurus = $request->user();

        abort_unless(
            $pengurus && $pengurus->status === 'Aktif' && $pengurus->role === 'Pengurus' && !empty($pengurus->id_kelompok),
            403
        );

        $kelompok = $pengurus->kelompokTani;

        $query = TransaksiPenjualan::whereHas('penjual', function ($q) use ($pengurus) {
            $q->where('id_kelompok', $pengurus->id_kelompok);
        })->with(['penjual', 'komoditas', 'produk']);

        $this->applyFilters($query, $request);

        $transaksis = $query->orderByDesc('tanggal_transaksi')->get();

        $activeTrx = $transaksis->where('status_pembayaran', '!=', 'dibatalkan');
        $summary = [
            'total_transaksi' => $transaksis->count(),
            'total_omzet' => (float) $activeTrx->sum('total_harga'),
            'total_terbayar' => (float) $activeTrx->sum('total_dibayar'),
            'total_piutang' => max(0.0, (float) $activeTrx->sum('total_harga') - (float) $activeTrx->sum('total_dibayar')),
            'total_volume_kg' => (float) $activeTrx->sum('jumlah'),
            'lunas_count' => $transaksis->where('status_pembayaran', 'lunas')->count(),
            'pending_count' => $transaksis->whereIn('status_pembayaran', ['dp', 'tempo', 'belum_dibayar'])->count(),
            'batal_count' => $transaksis->where('status_pembayaran', 'dibatalkan')->count(),
        ];

        return view('Pengurus.Content.Laporan.PrintTransaksi', compact(
            'kelompok',
            'pengurus',
            'transaksis',
            'summary'
        ));
    }

    /**
     * Terapkan filter pencarian pada query transaksi.
     */
    private function applyFilters($query, Request $request): void
    {
        // 1. Rentang Tanggal
        if ($dari = $request->query('tanggal_dari')) {
            $query->whereDate('tanggal_transaksi', '>=', $dari);
        }
        if ($sampai = $request->query('tanggal_sampai')) {
            $query->whereDate('tanggal_transaksi', '<=', $sampai);
        }

        // 2. Shortcut Preset Tanggal
        if ($preset = $request->query('preset_waktu')) {
            $now = Carbon::now();
            match ($preset) {
                'hari_ini' => $query->whereDate('tanggal_transaksi', $now->toDateString()),
                'minggu_ini' => $query->whereBetween('tanggal_transaksi', [$now->startOfWeek()->toDateString(), $now->endOfWeek()->toDateString()]),
                'bulan_ini' => $query->whereBetween('tanggal_transaksi', [$now->startOfMonth()->toDateString(), $now->endOfMonth()->toDateString()]),
                'tahun_ini' => $query->whereYear('tanggal_transaksi', $now->year),
                '30_hari' => $query->whereDate('tanggal_transaksi', '>=', $now->subDays(30)->toDateString()),
                default => null,
            };
        }

        // 3. Status Pembayaran
        if ($statusBayar = $request->query('status_pembayaran')) {
            $query->where('status_pembayaran', strtolower($statusBayar));
        }

        // 4. Status Penyerahan
        if ($statusSerah = $request->query('status_penyerahan')) {
            $query->where('status_penyerahan', strtolower($statusSerah));
        }

        // 5. Komoditas
        if ($idKomoditas = $request->query('id_komoditas')) {
            $query->where('id_komoditas', $idKomoditas);
        }

        // 6. Petani / Penjual
        if ($idPetani = $request->query('id_pengguna')) {
            $query->where('id_pengguna', $idPetani);
        }

        // 7. Metode Pembayaran
        if ($metode = $request->query('metode_pembayaran')) {
            $query->where('metode_pembayaran', strtolower($metode));
        }

        // 8. Pencarian Teks
        if ($search = trim((string) $request->query('q'))) {
            $likeOp = DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(function ($q) use ($search, $likeOp) {
                $q->where('kode_transaksi', $likeOp, "%{$search}%")
                  ->orWhere('nama_produk', $likeOp, "%{$search}%")
                  ->orWhere('nama_pembeli', $likeOp, "%{$search}%")
                  ->orWhere('no_telepon_pembeli', $likeOp, "%{$search}%")
                  ->orWhereHas('penjual', fn ($uq) => $uq->where('nama', $likeOp, "%{$search}%"));
            });
        }
    }

    /**
     * Ambil tren omzet bulanan 6 bulan terakhir untuk grafik.
     */
    private function getMonthlyTrend(string $idKelompok): array
    {
        $months = [];
        $omzet = [];
        $transaksiCount = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth()->toDateString();
            $monthEnd = $date->copy()->endOfMonth()->toDateString();
            $label = $date->locale('id')->isoFormat('MMM Y');

            $months[] = $label;

            $monthQuery = TransaksiPenjualan::whereHas('penjual', function ($q) use ($idKelompok) {
                $q->where('id_kelompok', $idKelompok);
            })
            ->whereBetween('tanggal_transaksi', [$monthStart, $monthEnd]);

            $totalBulanIni = (float) (clone $monthQuery)->where('status_pembayaran', '!=', 'dibatalkan')->sum('total_harga');
            $countBulanIni = (clone $monthQuery)->count();

            $omzet[] = $totalBulanIni;
            $transaksiCount[] = $countBulanIni;
        }

        return [
            'labels' => $months,
            'omzet' => $omzet,
            'transaksi' => $transaksiCount,
        ];
    }

    /**
     * Ambil sebaran penjualan berdasarkan komoditas untuk kelompok tani.
     */
    private function getCommodityDistribution(string $idKelompok): array
    {
        $data = TransaksiPenjualan::whereHas('penjual', function ($q) use ($idKelompok) {
            $q->where('id_kelompok', $idKelompok);
        })
        ->where('status_pembayaran', '!=', 'dibatalkan')
        ->select('id_komoditas', 'nama_produk', DB::raw('SUM(total_harga) as total_nominal'), DB::raw('SUM(jumlah) as total_volume'), DB::raw('COUNT(*) as total_count'))
        ->groupBy('id_komoditas', 'nama_produk')
        ->with('komoditas:id_komoditas,nama_komoditas')
        ->orderByDesc('total_nominal')
        ->take(5)
        ->get();

        $labels = [];
        $values = [];
        $volumes = [];

        foreach ($data as $item) {
            $label = $item->komoditas?->nama_komoditas ?: ($item->nama_produk ?: 'Hasil Tani');
            $labels[] = $label;
            $values[] = (float) $item->total_nominal;
            $volumes[] = (float) $item->total_volume;
        }

        if (empty($labels)) {
            $labels = ['Belum Ada Transaksi'];
            $values = [0];
            $volumes = [0];
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'volumes' => $volumes,
        ];
    }
}
