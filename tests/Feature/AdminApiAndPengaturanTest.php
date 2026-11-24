<?php

namespace Tests\Feature;

use App\Models\KelompokTani;
use App\Models\Komoditas;
use App\Models\Lahan;
use App\Models\Panen;
use App\Models\Pengguna;
use App\Models\TransaksiPenjualan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminApiAndPengaturanTest extends TestCase
{
    use RefreshDatabase;

    protected Pengguna $pengurus;
    protected KelompokTani $poktan;
    protected Pengguna $petani;
    protected Komoditas $komoditasPadi;
    protected Panen $panen;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->poktan = KelompokTani::create([
            'id_kelompok' => 'POKTAN-API-01',
            'nama_kelompok' => 'Poktan Maju Jaya Admin',
            'alamat' => 'Jl. Pertanian No. 10, Jember',
            'status' => 'Aktif',
        ]);

        $this->pengurus = Pengguna::create([
            'id_pengguna' => 'PGR-API-01',
            'nama' => 'Bpk. Ketua Poktan Admin',
            'username' => 'ketua_admin',
            'password' => Hash::make('password123'),
            'role' => 'Pengurus',
            'jabatan' => 'Ketua',
            'id_kelompok' => $this->poktan->id_kelompok,
            'status' => 'Aktif',
        ]);

        $this->petani = Pengguna::create([
            'id_pengguna' => 'PET-API-01',
            'nama' => 'Petani Joko Admin',
            'username' => 'petani_joko',
            'password' => Hash::make('password123'),
            'role' => 'Anggota',
            'id_kelompok' => $this->poktan->id_kelompok,
            'status' => 'Aktif',
        ]);

        $this->komoditasPadi = Komoditas::create([
            'id_komoditas' => 'KMD-API-01',
            'nama_komoditas' => 'Padi Ciherang Super',
            'kategori' => 'Tanaman Pangan',
        ]);

        $lahan = Lahan::create([
            'id_lahan' => 'LHN-API-01',
            'id_pengguna' => $this->petani->id_pengguna,
            'id_kelompok' => $this->poktan->id_kelompok,
            'id_komoditas' => $this->komoditasPadi->id_komoditas,
            'nama_lahan' => 'Petak Timur 1',
            'luas' => 1.5,
            'alamat_lahan' => 'Blok Sawah Lor RT 02',
            'status_verifikasi' => 'terverifikasi',
        ]);

        $this->panen = Panen::create([
            'id_panen' => 'PNN-API-01',
            'kode_panen' => 'PNN-2026-001',
            'id_pengguna' => $this->petani->id_pengguna,
            'id_lahan' => $lahan->id_lahan,
            'id_kelompok' => $this->poktan->id_kelompok,
            'komoditas_nama' => 'Padi Ciherang Super',
            'varietas' => 'Ciherang',
            'berat_kg' => 4500,
            'tanggal_panen' => Carbon::now()->toDateString(),
            'musim_tanam' => 'Musim Rendeng 2026',
            'status' => 'menunggu',
        ]);

        TransaksiPenjualan::create([
            'id_penjualan' => 'TRX-API-01',
            'kode_transaksi' => 'TRX-API-01',
            'id_pengguna' => $this->petani->id_pengguna,
            'id_komoditas' => $this->komoditasPadi->id_komoditas,
            'nama_produk' => 'Beras Ciherang 500kg',
            'nama_pembeli' => 'Toko Subur',
            'jumlah' => 500,
            'satuan' => 'kg',
            'harga_satuan' => 14000,
            'total_harga' => 7000000,
            'total_dibayar' => 7000000,
            'status_pembayaran' => 'lunas',
            'status_penyerahan' => 'diserahkan',
            'tanggal_transaksi' => Carbon::now()->toDateString(),
            'metode_pembayaran' => 'transfer_bri',
        ]);
    }

    /**
     * Test endpoint API Admin Dashboard.
     */
    public function test_api_admin_dashboard(): void
    {
        $response = $this->actingAs($this->pengurus, 'sanctum')->getJson(route('api.admin.dashboard'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'total_anggota' => 1,
                'total_lahan' => 1,
                'panen_menunggu_verifikasi' => 1,
                'total_transaksi' => 1,
            ],
        ]);
    }

    /**
     * Test endpoint API Admin Panen & Statistik.
     */
    public function test_api_admin_panen_dan_statistik(): void
    {
        // 1. List panen
        $resList = $this->actingAs($this->pengurus, 'sanctum')->getJson(route('api.admin.panen'));
        $resList->assertStatus(200);
        $resList->assertJsonStructure(['success', 'data' => ['data']]);

        // 2. Statistik panen
        $resStat = $this->actingAs($this->pengurus, 'sanctum')->getJson(route('api.admin.panen.statistik'));
        $resStat->assertStatus(200);
        $resStat->assertJson([
            'success' => true,
            'data' => [
                'total' => 1,
                'menunggu' => 1,
                'disetujui' => 0,
                'ditolak' => 0,
            ],
        ]);

        // 3. Detail panen
        $resDetail = $this->actingAs($this->pengurus, 'sanctum')->getJson(route('api.admin.panen.detail', $this->panen->id_panen));
        $resDetail->assertStatus(200);
        $resDetail->assertJson([
            'success' => true,
            'data' => [
                'id_panen' => $this->panen->id_panen,
                'kode_panen' => 'PNN-2026-001',
            ],
        ]);

        // 4. Update status panen menjadi disetujui
        $resApprove = $this->actingAs($this->pengurus, 'sanctum')->patchJson(route('api.admin.panen.update-status', $this->panen->id_panen), [
            'status' => 'disetujui',
        ]);
        $resApprove->assertStatus(200);
        $this->assertEquals('disetujui', $this->panen->fresh()->status);

        // 5. Update status panen menjadi ditolak tanpa alasan harus gagal
        $resRejectFail = $this->actingAs($this->pengurus, 'sanctum')->patchJson(route('api.admin.panen.update-status', $this->panen->id_panen), [
            'status' => 'ditolak',
        ]);
        $resRejectFail->assertStatus(422);

        // 6. Update status panen menjadi ditolak dengan alasan
        $resRejectSuccess = $this->actingAs($this->pengurus, 'sanctum')->patchJson(route('api.admin.panen.update-status', $this->panen->id_panen), [
            'status' => 'ditolak',
            'catatan_verifikasi' => 'Timbangan tidak sesuai hasil ubinan poktan',
        ]);
        $resRejectSuccess->assertStatus(200);
        $this->assertEquals('ditolak', $this->panen->fresh()->status);
        $this->assertEquals('Timbangan tidak sesuai hasil ubinan poktan', $this->panen->fresh()->catatan_verifikasi);
    }

    /**
     * Test endpoint API Admin Anggota, Lahan, Transaksi.
     */
    public function test_api_admin_anggota_lahan_transaksi(): void
    {
        $resAnggota = $this->actingAs($this->pengurus, 'sanctum')->getJson(route('api.admin.anggota'));
        $resAnggota->assertStatus(200);
        $resAnggota->assertJsonPath('data.total', 1);

        $resLahan = $this->actingAs($this->pengurus, 'sanctum')->getJson(route('api.admin.lahan'));
        $resLahan->assertStatus(200);
        $resLahan->assertJsonPath('data.total', 1);

        $resTrx = $this->actingAs($this->pengurus, 'sanctum')->getJson(route('api.admin.transaksi'));
        $resTrx->assertStatus(200);
        $resTrx->assertJsonPath('data.total', 1);
    }

    /**
     * Test web page Pengaturan Pengurus.
     */
    public function test_web_pengaturan_page_dan_update(): void
    {
        // Buka halaman pengaturan
        $response = $this->actingAs($this->pengurus)->get(route('pengurus.pengaturan'));
        $response->assertStatus(200);
        $response->assertSee('Pengaturan Sistem');
        $response->assertSee('Profil Admin / Pengurus');
        $response->assertSee('Data Kelembagaan Kelompok Tani');

        // Update profil
        $resProfil = $this->actingAs($this->pengurus)->put(route('pengurus.pengaturan.profil'), [
            'nama' => 'Bpk. Sutrisno Baru',
            'username' => 'ketua_admin_baru',
            'email' => 'ketua.baru@pawontani.id',
            'no_telepon' => '081299998888',
            'alamat' => 'Jl. Mawar No. 12',
        ]);
        $resProfil->assertRedirect(route('pengurus.pengaturan'));
        $this->assertDatabaseHas('pengguna', [
            'id_pengguna' => $this->pengurus->id_pengguna,
            'nama' => 'Bpk. Sutrisno Baru',
            'username' => 'ketua_admin_baru',
        ]);

        // Update data kelompok
        $resKelompok = $this->actingAs($this->pengurus)->put(route('pengurus.pengaturan.kelompok'), [
            'nama_kelompok' => 'Poktan Maju Jaya Admin Updated',
            'alamat' => 'Sekretariat Balai Tani No. 5',
        ]);
        $resKelompok->assertRedirect(route('pengurus.pengaturan'));
        $this->assertDatabaseHas('kelompok_tani', [
            'id_kelompok' => $this->poktan->id_kelompok,
            'nama_kelompok' => 'Poktan Maju Jaya Admin Updated',
        ]);
    }
}
