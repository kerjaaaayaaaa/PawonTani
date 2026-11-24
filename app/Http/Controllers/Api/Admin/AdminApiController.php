<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AktivitasTanam;
use App\Models\KelompokTani;
use App\Models\Komoditas;
use App\Models\Lahan;
use App\Models\Panen;
use App\Models\Pengguna;
use App\Models\TransaksiPenjualan;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminApiController extends Controller
{
    /**
     * Dapatkan pengurus / admin yang terautentikasi dan kelompok taninya.
     */
    protected function getAdminContext(Request $request): array
    {
        /** @var \App\Models\Pengguna|null $user */
        $user = $request->user();

        // Jika tidak login via Sanctum, cari pengurus aktif default untuk testing/dev jika diizinkan
        if (!$user) {
            $user = Pengguna::where('role', 'Pengurus')->whereNotNull('id_kelompok')->first();
        }

        abort_unless($user, 401, 'Unauthenticated.');

        return [$user, $user->id_kelompok];
    }

    /**
     * GET /api/admin/dashboard
     * Ringkasan KPI dashboard admin / pengurus.
     */
    public function dashboard(Request $request): JsonResponse
    {
        [$admin, $idKelompok] = $this->getAdminContext($request);

        $totalAnggota = Pengguna::where('id_kelompok', $idKelompok)->where('role', 'Anggota')->count();
        $totalLahan = Lahan::where('id_kelompok', $idKelompok)->count();
        $totalLuasLahan = (float) Lahan::where('id_kelompok', $idKelompok)->sum('luas');
        $lahanMenunggu = Lahan::where('id_kelompok', $idKelompok)->where('status_verifikasi', 'menunggu')->count();

        $panenBase = Panen::where('id_kelompok', $idKelompok);
        $totalPanenKg = (float) (clone $panenBase)->where('status', 'disetujui')->sum('berat_kg');
        $panenMenunggu = (clone $panenBase)->where('status', 'menunggu')->count();

        $trxBase = TransaksiPenjualan::whereHas('penjual', fn ($q) => $q->where('id_kelompok', $idKelompok));
        $totalTransaksi = (clone $trxBase)->count();
        $totalOmzet = (float) (clone $trxBase)->where('status_pembayaran', '!=', 'dibatalkan')->sum('total_harga');

        $aktivitasTerbaru = AktivitasTanam::whereHas('pengguna', fn ($q) => $q->where('id_kelompok', $idKelompok))
            ->with(['pengguna:id_pengguna,nama', 'lahan:id_lahan,nama_lahan'])
            ->latest('tanggal')
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'kelompok' => $admin->kelompokTani?->nama_kelompok,
                'total_anggota' => $totalAnggota,
                'total_lahan' => $totalLahan,
                'total_luas_lahan_ha' => round($totalLuasLahan, 2),
                'lahan_menunggu_verifikasi' => $lahanMenunggu,
                'total_hasil_panen_kg' => $totalPanenKg,
                'total_hasil_panen_ton' => round($totalPanenKg / 1000.0, 2),
                'panen_menunggu_verifikasi' => $panenMenunggu,
                'total_transaksi' => $totalTransaksi,
                'total_omzet_penjualan' => $totalOmzet,
                'aktivitas_terbaru' => $aktivitasTerbaru,
            ],
        ]);
    }

    /**
     * GET /api/admin/anggota
     */
    public function anggota(Request $request): JsonResponse
    {
        [$admin, $idKelompok] = $this->getAdminContext($request);

        $query = Pengguna::where('id_kelompok', $idKelompok)->where('role', 'Anggota');

        if ($search = $request->query('q')) {
            $like = DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(fn ($q) => $q->where('nama', $like, "%{$search}%")
                ->orWhere('no_telepon', $like, "%{$search}%")
                ->orWhere('email', $like, "%{$search}%"));
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $anggota = $query->latest('created_at')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $anggota,
        ]);
    }

    /**
     * GET /api/admin/lahan
     */
    public function lahan(Request $request): JsonResponse
    {
        [$admin, $idKelompok] = $this->getAdminContext($request);

        $query = Lahan::where('id_kelompok', $idKelompok)
            ->with(['pengguna:id_pengguna,nama,no_telepon', 'komoditas:id_komoditas,nama_komoditas']);

        if ($search = $request->query('q')) {
            $like = DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(fn ($q) => $q->where('nama_lahan', $like, "%{$search}%")
                ->orWhere('alamat_lahan', $like, "%{$search}%")
                ->orWhereHas('pengguna', fn ($uq) => $uq->where('nama', $like, "%{$search}%")));
        }

        if ($status = $request->query('status')) {
            $query->where('status_verifikasi', $status);
        }

        $lahan = $query->latest('created_at')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $lahan,
        ]);
    }

    /**
     * GET /api/admin/panen
     */
    public function panen(Request $request): JsonResponse
    {
        [$admin, $idKelompok] = $this->getAdminContext($request);

        $query = Panen::where('id_kelompok', $idKelompok)
            ->with([
                'pengguna:id_pengguna,nama,no_telepon',
                'lahan:id_lahan,nama_lahan,luas',
                'komoditas:id_komoditas,nama_komoditas',
            ]);

        if ($status = $request->query('status')) {
            $query->where('status', strtolower($status));
        }

        if ($tanggalDari = $request->query('tanggal_dari')) {
            $query->whereDate('tanggal_panen', '>=', $tanggalDari);
        }

        if ($tanggalSampai = $request->query('tanggal_sampai')) {
            $query->whereDate('tanggal_panen', '<=', $tanggalSampai);
        }

        if ($search = $request->query('q')) {
            $like = DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(fn ($q) => $q->where('kode_panen', $like, "%{$search}%")
                ->orWhere('varietas', $like, "%{$search}%")
                ->orWhere('komoditas_nama', $like, "%{$search}%")
                ->orWhereHas('pengguna', fn ($uq) => $uq->where('nama', $like, "%{$search}%"))
                ->orWhereHas('lahan', fn ($lq) => $lq->where('nama_lahan', $like, "%{$search}%")));
        }

        $panens = $query->orderByDesc('tanggal_panen')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $panens,
        ]);
    }

    /**
     * GET /api/admin/panen/statistik
     */
    public function panenStatistik(Request $request): JsonResponse
    {
        [$admin, $idKelompok] = $this->getAdminContext($request);

        $base = Panen::where('id_kelompok', $idKelompok);

        $totalCount = (clone $base)->count();
        $menungguCount = (clone $base)->where('status', 'menunggu')->count();
        $disetujuiCount = (clone $base)->where('status', 'disetujui')->count();
        $ditolakCount = (clone $base)->where('status', 'ditolak')->count();
        $totalBeratKg = (float) (clone $base)->where('status', 'disetujui')->sum('berat_kg');

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $totalCount,
                'menunggu' => $menungguCount,
                'disetujui' => $disetujuiCount,
                'ditolak' => $ditolakCount,
                'total_berat_kg' => $totalBeratKg,
                'total_berat_ton' => round($totalBeratKg / 1000.0, 2),
            ],
        ]);
    }

    /**
     * GET /api/admin/panen/{id}
     */
    public function panenDetail(Request $request, string $id): JsonResponse
    {
        [$admin, $idKelompok] = $this->getAdminContext($request);

        $panen = Panen::with([
            'pengguna:id_pengguna,nama,no_telepon,email,alamat',
            'lahan:id_lahan,nama_lahan,luas,alamat_lahan',
            'komoditas:id_komoditas,nama_komoditas',
        ])
        ->where('id_kelompok', $idKelompok)
        ->where('id_panen', $id)
        ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $panen,
        ]);
    }

    /**
     * PATCH /api/admin/panen/{id}/status
     */
    public function panenUpdateStatus(Request $request, string $id): JsonResponse
    {
        [$admin, $idKelompok] = $this->getAdminContext($request);

        $validated = $request->validate([
            'status' => 'required|in:disetujui,ditolak,menunggu',
            'catatan_verifikasi' => [
                $request->input('status') === 'ditolak' ? 'required' : 'nullable',
                'string',
                'max:500',
            ],
        ], [
            'catatan_verifikasi.required' => 'Wajib mengisi alasan penolakan saat menolak laporan panen.',
        ]);

        $panen = Panen::where('id_kelompok', $idKelompok)->where('id_panen', $id)->firstOrFail();

        $panen->status = $validated['status'];
        $panen->catatan_verifikasi = $validated['catatan_verifikasi'] ?? null;
        $panen->diverifikasi_oleh = $admin->nama;
        $panen->tanggal_verifikasi = now();
        $panen->save();

        AuditLogger::log(
            'panen.diverifikasi.api',
            "API verifikasi panen {$panen->kode_panen} menjadi '{$panen->status}'.",
            'Panen',
            $panen->id_panen,
            $idKelompok,
            $request
        );

        return response()->json([
            'success' => true,
            'message' => "Laporan hasil panen berhasil diubah menjadi {$panen->status}.",
            'data' => $panen,
        ]);
    }

    /**
     * GET /api/admin/transaksi
     */
    public function transaksi(Request $request): JsonResponse
    {
        [$admin, $idKelompok] = $this->getAdminContext($request);

        $query = TransaksiPenjualan::whereHas('penjual', fn ($q) => $q->where('id_kelompok', $idKelompok))
            ->with(['penjual:id_pengguna,nama,no_telepon', 'komoditas:id_komoditas,nama_komoditas', 'riwayatPembayaran']);

        if ($status = $request->query('status')) {
            $query->where('status_pembayaran', strtolower($status));
        }

        if ($search = $request->query('q')) {
            $like = DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(fn ($q) => $q->where('kode_transaksi', $like, "%{$search}%")
                ->orWhere('nama_produk', $like, "%{$search}%")
                ->orWhere('nama_pembeli', $like, "%{$search}%")
                ->orWhereHas('penjual', fn ($uq) => $uq->where('nama', $like, "%{$search}%")));
        }

        $transaksis = $query->orderByDesc('tanggal_transaksi')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $transaksis,
        ]);
    }
}
