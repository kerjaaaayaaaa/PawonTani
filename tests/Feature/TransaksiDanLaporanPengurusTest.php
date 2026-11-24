<?php

namespace Tests\Feature;

use App\Models\KelompokTani;
use App\Models\Komoditas;
use App\Models\Pengguna;
use App\Models\Produk;
use App\Models\TransaksiPenjualan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TransaksiDanLaporanPengurusTest extends TestCase
{
    use RefreshDatabase;

    protected Pengguna $pengurus;
    protected KelompokTani $poktan;
    protected Pengguna $petani;
    protected Komoditas $komoditasPadi;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->poktan = KelompokTani::create([
            'id_kelompok' => 'POKTAN-TEST-02',
            'nama_kelompok' => 'Poktan Maju Bersama Test',
            'alamat' => 'Desa Sumbersari, Jember',
            'status' => 'Aktif',
        ]);

        $this->pengurus = Pengguna::create([
            'id_pengguna' => 'PGR-TEST-02',
            'nama' => 'Bpk. Sutrisno, S.P.',
            'username' => 'sutrisno_test',
            'password' => Hash::make('password123'),
            'role' => 'Pengurus',
            'jabatan' => 'Ketua',
            'id_kelompok' => $this->poktan->id_kelompok,
            'status' => 'Aktif',
        ]);

        $this->petani = Pengguna::create([
            'id_pengguna' => 'PET-TEST-02',
            'nama' => 'Pak Sukardi',
            'username' => 'sukardi_test',
            'password' => Hash::make('password123'),
            'role' => 'Anggota',
            'id_kelompok' => $this->poktan->id_kelompok,
            'status' => 'Aktif',
        ]);

        $this->komoditasPadi = Komoditas::create([
            'id_komoditas' => 'KMD-TEST-02',
            'nama_komoditas' => 'Padi Sawah',
            'kategori' => 'Tanaman Pangan',
        ]);

        TransaksiPenjualan::create([
            'id_penjualan' => 'TRX-SAMPLE-01',
            'kode_transaksi' => 'TRX-SAMPLE-01',
            'id_pengguna' => $this->petani->id_pengguna,
            'id_komoditas' => $this->komoditasPadi->id_komoditas,
            'nama_produk' => 'Gabah Kering Panen 1 Ton',
            'nama_pembeli' => 'UD Berkah Jaya',
            'jumlah' => 1000,
            'satuan' => 'kg',
            'harga_satuan' => 7000,
            'total_harga' => 7000000,
            'total_dibayar' => 7000000,
            'status_pembayaran' => 'lunas',
            'status_penyerahan' => 'diserahkan',
            'tanggal_transaksi' => Carbon::now()->toDateString(),
            'metode_pembayaran' => 'transfer_bri',
        ]);
    }

    /**
     * Test halaman Transaksi terbuka dan memuat komponen sesuai desain mockup.
     */
    public function test_halaman_transaksi_terbuka_dengan_benar(): void
    {
        $response = $this->actingAs($this->pengurus)->get(route('pengurus.penjualan'));

        $response->assertStatus(200);
        $response->assertSee('Kelola Transaksi Penjualan');
        $response->assertSee('Total Transaksi');
        $response->assertSee('Total Omzet');
        $response->assertSee('Transaksi Lunas');
        $response->assertSee('Piutang &amp; DP', false);
        $response->assertSee('TRX-SAMPLE-01');
        $response->assertSee('Gabah Kering Panen 1 Ton');
        $response->assertSee('UD Berkah Jaya');
    }

    /**
     * Test pencatatan transaksi baru melalui route pengurus.penjualan.store.
     */
    public function test_pengurus_dapat_mencatat_transaksi_dari_halaman_transaksi(): void
    {
        $payload = [
            'id_pengguna' => $this->petani->id_pengguna,
            'id_komoditas' => $this->komoditasPadi->id_komoditas,
            'nama_produk' => 'Jagung Pipil Kering 500kg',
            'nama_pembeli' => 'Koperasi Peternak Jaya',
            'no_telepon_pembeli' => '081234567899',
            'jumlah' => 500,
            'satuan' => 'kg',
            'harga_satuan' => 5500,
            'nominal_dibayar' => 2000000,
            'metode_pembayaran' => 'transfer',
            'tanggal_transaksi' => Carbon::now()->toDateString(),
            'status_penyerahan' => 'belum_diserahkan',
            'catatan' => 'Uang muka diterima via transfer bank',
        ];

        $response = $this->actingAs($this->pengurus)
            ->post(route('pengurus.penjualan.store'), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('transaksi_penjualan', [
            'nama_produk' => 'Jagung Pipil Kering 500kg',
            'total_harga' => 2750000,
            'total_dibayar' => 2000000,
            'status_pembayaran' => 'dp',
        ]);
    }

    /**
     * Test halaman Laporan terbuka dengan Hero Welcome Banner dan grafik.
     */
    public function test_halaman_laporan_terbuka_dengan_benar(): void
    {
        $response = $this->actingAs($this->pengurus)->get(route('pengurus.laporan'));

        $response->assertStatus(200);
        $response->assertSee('Laporan Transaksi Penjualan Hasil Tani');
        $response->assertSee('Laporan Keuangan &amp; Penjualan', false);
        $response->assertSee('Tren Perputaran Omzet');
        $response->assertSee('Komoditas Terlaris');
    }
}
