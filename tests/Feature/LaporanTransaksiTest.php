<?php

namespace Tests\Feature;

use App\Models\KelompokTani;
use App\Models\Komoditas;
use App\Models\Pengguna;
use App\Models\Produk;
use App\Models\RiwayatPembayaranPenjualan;
use App\Models\TransaksiPenjualan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LaporanTransaksiTest extends TestCase
{
    use RefreshDatabase;

    protected Pengguna $pengurus;
    protected KelompokTani $poktan;
    protected Pengguna $petani;
    protected Komoditas $komoditasPadi;
    protected TransaksiPenjualan $trxLunas;
    protected TransaksiPenjualan $trxDp;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        // 1. Buat Poktan
        $this->poktan = KelompokTani::create([
            'id_kelompok' => 'POKTAN-TEST-01',
            'nama_kelompok' => 'Poktan Sumber Makmur Test',
            'alamat' => 'Desa Sumbersari, Jember',
            'status' => 'Aktif',
        ]);

        // 2. Buat Pengurus
        $this->pengurus = Pengguna::create([
            'id_pengguna' => 'PGR-TEST-01',
            'nama' => 'Pengurus Test Poktan',
            'username' => 'pengurus_test',
            'password' => Hash::make('password123'),
            'role' => 'Pengurus',
            'jabatan' => 'Ketua',
            'id_kelompok' => $this->poktan->id_kelompok,
            'status' => 'Aktif',
        ]);

        // 3. Buat Petani Anggota dalam kelompok yang sama
        $this->petani = Pengguna::create([
            'id_pengguna' => 'PET-TEST-01',
            'nama' => 'Petani Budi Test',
            'username' => 'petani_budi',
            'password' => Hash::make('password123'),
            'role' => 'Anggota',
            'id_kelompok' => $this->poktan->id_kelompok,
            'status' => 'Aktif',
        ]);

        // 4. Buat Komoditas
        $this->komoditasPadi = Komoditas::create([
            'id_komoditas' => 'KMD-PADI-01',
            'nama_komoditas' => 'Padi Ciherang',
            'kategori' => 'Tanaman Pangan',
        ]);

        // 5. Buat Produk
        $produk = Produk::create([
            'id_produk' => 'PRD-TEST-01',
            'id_pengguna' => $this->petani->id_pengguna,
            'id_komoditas' => $this->komoditasPadi->id_komoditas,
            'nama_produk' => 'Beras Ciherang Premium Test',
            'harga' => 14000,
            'satuan' => 'kg',
            'stok_fisik' => 1000,
            'status' => 'aktif',
        ]);

        // 6. Buat Transaksi Lunas
        $this->trxLunas = TransaksiPenjualan::create([
            'id_penjualan' => 'TRX-TEST-LUNAS-01',
            'kode_transaksi' => 'TRX-TEST-LUNAS-01',
            'id_pengguna' => $this->petani->id_pengguna,
            'id_produk' => $produk->id_produk,
            'id_komoditas' => $this->komoditasPadi->id_komoditas,
            'nama_produk' => 'Beras Ciherang Premium Test',
            'nama_pembeli' => 'Toko Subur Jaya',
            'no_telepon_pembeli' => '081234567890',
            'jumlah' => 100,
            'satuan' => 'kg',
            'harga_satuan' => 14000,
            'total_harga' => 1400000,
            'total_dibayar' => 1400000,
            'status_pembayaran' => 'lunas',
            'status_penyerahan' => 'diserahkan',
            'tanggal_transaksi' => Carbon::now()->toDateString(),
            'metode_pembayaran' => 'transfer_bri',
        ]);

        // 7. Buat Transaksi DP
        $this->trxDp = TransaksiPenjualan::create([
            'id_penjualan' => 'TRX-TEST-DP-02',
            'kode_transaksi' => 'TRX-TEST-DP-02',
            'id_pengguna' => $this->petani->id_pengguna,
            'id_produk' => $produk->id_produk,
            'id_komoditas' => $this->komoditasPadi->id_komoditas,
            'nama_produk' => 'Beras Ciherang Premium Test',
            'nama_pembeli' => 'Bpk. Ahmad Fauzi',
            'no_telepon_pembeli' => '085566778899',
            'jumlah' => 200,
            'satuan' => 'kg',
            'harga_satuan' => 14000,
            'total_harga' => 2800000,
            'total_dibayar' => 1000000,
            'status_pembayaran' => 'dp',
            'status_penyerahan' => 'belum_diserahkan',
            'tanggal_transaksi' => Carbon::now()->subDays(3)->toDateString(),
            'metode_pembayaran' => 'transfer',
        ]);

        RiwayatPembayaranPenjualan::create([
            'id_pembayaran' => 'PAY-TEST-01',
            'id_penjualan' => $this->trxDp->id_penjualan,
            'nominal' => 1000000,
            'tanggal_pembayaran' => Carbon::now()->subDays(3)->toDateString(),
            'keterangan' => 'DP Tahap 1',
            'status_verifikasi' => 'terverifikasi',
        ]);
    }

    /**
     * Test tamu yang belum login dialihkan ke login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('pengurus.laporan'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Test pengguna pengurus dapat melihat halaman laporan transaksi.
     */
    public function test_pengurus_can_view_laporan_transaksi_page(): void
    {
        $response = $this->actingAs($this->pengurus)->get(route('pengurus.laporan'));

        $response->assertStatus(200);
        $response->assertSee('Laporan Transaksi Penjualan Hasil Tani');
        $response->assertSee('Total Nilai Transaksi');
        $response->assertSee('TRX-TEST-LUNAS-01');
        $response->assertSee('TRX-TEST-DP-02');
        $response->assertSee('Toko Subur Jaya');
        $response->assertSee('Bpk. Ahmad Fauzi');
    }

    /**
     * Test filter status pembayaran lunas.
     */
    public function test_filter_by_status_pembayaran(): void
    {
        $response = $this->actingAs($this->pengurus)->get(route('pengurus.laporan', ['status_pembayaran' => 'lunas']));

        $response->assertStatus(200);
        $response->assertSee('TRX-TEST-LUNAS-01');
        $response->assertDontSee('TRX-TEST-DP-02');
    }

    /**
     * Test pencarian teks kata kunci.
     */
    public function test_search_by_keyword(): void
    {
        $response = $this->actingAs($this->pengurus)->get(route('pengurus.laporan', ['q' => 'Ahmad Fauzi']));

        $response->assertStatus(200);
        $response->assertSee('TRX-TEST-DP-02');
        $response->assertDontSee('TRX-TEST-LUNAS-01');
    }

    /**
     * Test unduh export CSV laporan transaksi.
     */
    public function test_export_csv_downloads_valid_csv(): void
    {
        $response = $this->actingAs($this->pengurus)->get(route('pengurus.laporan.transaksi.export'));

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'text/csv'));
        $this->assertTrue(str_contains($response->headers->get('content-disposition'), 'attachment; filename='));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Kode Transaksi', $content);
        $this->assertStringContainsString('TRX-TEST-LUNAS-01', $content);
        $this->assertStringContainsString('TRX-TEST-DP-02', $content);
    }

    /**
     * Test cetak (print view) laporan transaksi.
     */
    public function test_print_view_loads_clean_printable_report(): void
    {
        $response = $this->actingAs($this->pengurus)->get(route('pengurus.laporan.transaksi.print'));

        $response->assertStatus(200);
        $response->assertSee('Laporan Rekapitulasi Transaksi Penjualan Hasil Tani');
        $response->assertSee('KOP SURAT');
        $response->assertSee('TRX-TEST-LUNAS-01');
        $response->assertSee('Poktan Sumber Makmur Test');
    }

    /**
     * Test AJAX detail transaksi untuk modal.
     */
    public function test_ajax_detail_transaksi(): void
    {
        $response = $this->actingAs($this->pengurus)
            ->getJson(route('pengurus.laporan.transaksi.show', $this->trxDp->id_penjualan));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'id_penjualan' => $this->trxDp->id_penjualan,
                'kode_transaksi' => 'TRX-TEST-DP-02',
                'nama_pembeli' => 'Bpk. Ahmad Fauzi',
                'status_pembayaran' => 'dp',
            ],
        ]);
        $response->assertJsonCount(1, 'data.riwayat_pembayaran');
    }

    /**
     * Test pengurus dapat mencatat transaksi penjualan baru.
     */
    public function test_pengurus_can_store_new_sale_transaction(): void
    {
        $payload = [
            'id_pengguna' => $this->petani->id_pengguna,
            'id_komoditas' => $this->komoditasPadi->id_komoditas,
            'nama_produk' => 'Beras Pandan Wangi Super Baru',
            'nama_pembeli' => 'Resto Sedap Malam',
            'no_telepon_pembeli' => '081233445566',
            'jumlah' => 250,
            'satuan' => 'kg',
            'harga_satuan' => 15000,
            'nominal_dibayar' => 3750000,
            'metode_pembayaran' => 'transfer_bri',
            'tanggal_transaksi' => Carbon::now()->toDateString(),
            'status_penyerahan' => 'diserahkan',
            'catatan' => 'Pesanan rutin mingguan',
        ];

        $response = $this->actingAs($this->pengurus)
            ->post(route('pengurus.laporan.transaksi.store'), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('transaksi_penjualan', [
            'id_pengguna' => $this->petani->id_pengguna,
            'nama_produk' => 'Beras Pandan Wangi Super Baru',
            'nama_pembeli' => 'Resto Sedap Malam',
            'jumlah' => 250,
            'total_harga' => 3750000,
            'status_pembayaran' => 'lunas',
        ]);
    }
}
