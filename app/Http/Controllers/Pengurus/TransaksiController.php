<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Models\Komoditas;
use App\Models\Pengguna;
use App\Models\RiwayatPembayaranPenjualan;
use App\Models\TransaksiPenjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransaksiController extends Controller
{
    /**
     * Menampilkan daftar transaksi penjualan anggota kelompok tani.
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

        // Base query transaksi scoped ke kelompok tani pengurus (BOLA protection)
        $baseQuery = TransaksiPenjualan::whereHas('penjual', function ($q) use ($pengurus) {
            $q->where('id_kelompok', $pengurus->id_kelompok);
        });

        // Summary metrics untuk 4 kartu atas
        $metricsBase = clone $baseQuery;
        $activeMetrics = (clone $baseQuery)->where('status_pembayaran', '!=', 'dibatalkan');

        $totalTransaksi = (clone $metricsBase)->count();
        $totalOmzet = (float) (clone $activeMetrics)->sum('total_harga');
        $totalTerbayar = (float) (clone $activeMetrics)->sum('total_dibayar');
        $totalPiutang = max(0.0, $totalOmzet - $totalTerbayar);
        $totalVolumeKg = (float) (clone $activeMetrics)->sum('jumlah');

        $lunasCount = (clone $metricsBase)->where('status_pembayaran', 'lunas')->count();
        $dpCount = (clone $metricsBase)->where('status_pembayaran', 'dp')->count();
        $tempoCount = (clone $metricsBase)->where('status_pembayaran', 'tempo')->count();
        $belumBayarCount = (clone $metricsBase)->where('status_pembayaran', 'belum_dibayar')->count();
        $batalCount = (clone $metricsBase)->where('status_pembayaran', 'dibatalkan')->count();

        $stats = [
            'total' => $totalTransaksi,
            'total_omzet' => $totalOmzet,
            'total_terbayar' => $totalTerbayar,
            'total_piutang' => $totalPiutang,
            'total_volume_kg' => $totalVolumeKg,
            'total_volume_ton' => round($totalVolumeKg / 1000.0, 2),
            'lunas' => $lunasCount,
            'dp' => $dpCount,
            'tempo' => $tempoCount,
            'belum_bayar' => $belumBayarCount,
            'dibatalkan' => $batalCount,
            'piutang_count' => $dpCount + $tempoCount + $belumBayarCount,
        ];

        // Query transaksi dengan filter
        $query = (clone $baseQuery)->with([
            'penjual:id_pengguna,nama,no_telepon,foto_profil,id_kelompok',
            'komoditas:id_komoditas,nama_komoditas',
            'produk:id_produk,nama_produk,foto_produk',
            'riwayatPembayaran',
        ]);

        // Filter tab status pembayaran (misal: 'lunas', 'piutang', 'batal')
        if ($tab = $request->query('tab')) {
            if ($tab === 'lunas') {
                $query->where('status_pembayaran', 'lunas');
            } elseif ($tab === 'piutang') {
                $query->whereIn('status_pembayaran', ['dp', 'tempo', 'belum_dibayar']);
            } elseif ($tab === 'batal') {
                $query->where('status_pembayaran', 'dibatalkan');
            }
        }

        // Filter status pembayaran dropdown
        if ($status = $request->query('status')) {
            $query->where('status_pembayaran', strtolower($status));
        }

        // Filter komoditas
        if ($idKomoditas = $request->query('komoditas')) {
            $query->where('id_komoditas', $idKomoditas);
        }

        // Filter petani
        if ($idPetani = $request->query('petani')) {
            $query->where('id_pengguna', $idPetani);
        }

        // Pencarian teks
        if ($search = trim((string) $request->query('q'))) {
            $like = DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(function ($q) use ($search, $like) {
                $q->where('kode_transaksi', $like, "%{$search}%")
                  ->orWhere('nama_produk', $like, "%{$search}%")
                  ->orWhere('nama_pembeli', $like, "%{$search}%")
                  ->orWhere('no_telepon_pembeli', $like, "%{$search}%")
                  ->orWhereHas('penjual', fn ($uq) => $uq->where('nama', $like, "%{$search}%"));
            });
        }

        $transaksis = $query->orderByDesc('tanggal_transaksi')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $komoditasList = Komoditas::orderBy('nama_komoditas')->get(['id_komoditas', 'nama_komoditas']);
        $petaniList = Pengguna::where('id_kelompok', $pengurus->id_kelompok)
            ->where('status', 'Aktif')
            ->orderBy('nama')
            ->get(['id_pengguna', 'nama', 'role']);

        return view('Pengurus.Content.Transaksi.Index', compact(
            'kelompok',
            'pengurus',
            'stats',
            'transaksis',
            'komoditasList',
            'petaniList'
        ));
    }

    /**
     * Catat transaksi penjualan baru oleh Pengurus.
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
            RiwayatPembayaranPenjualan::create([
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
}
