<?php

namespace Database\Seeders;

use App\Models\Komoditas;
use App\Models\Pengguna;
use App\Models\Produk;
use App\Models\RiwayatPembayaranPenjualan;
use App\Models\TransaksiPenjualan;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TransaksiPenjualanSeeder extends Seeder
{
    public function run(): void
    {
        // Cari pengurus poktan 1
        $pengurus = Pengguna::where('role', 'Pengurus')->whereNotNull('id_kelompok')->first();
        if (!$pengurus) {
            return;
        }

        $idKelompok = $pengurus->id_kelompok;

        // Pastikan ada beberapa anggota petani di kelompok tani ini
        $petani1 = Pengguna::firstOrCreate(
            ['username' => 'petani_pak_bejo'],
            [
                'id_pengguna' => 'PET-001',
                'nama' => 'Pak Bejo Sugiono',
                'nik' => '3509012304800001',
                'password' => Hash::make('password123'),
                'email' => 'bejo@pawontani.id',
                'no_telepon' => '081234567891',
                'alamat' => 'Dusun Krajan RT 01/RW 02, Sumbersari, Jember',
                'role' => 'Anggota',
                'jabatan' => 'Anggota',
                'id_kelompok' => $idKelompok,
                'status' => 'Aktif',
            ]
        );

        $petani2 = Pengguna::firstOrCreate(
            ['username' => 'petani_pak_kardi'],
            [
                'id_pengguna' => 'PET-002',
                'nama' => 'Pak Sukardi',
                'nik' => '3509011508770002',
                'password' => Hash::make('password123'),
                'email' => 'sukardi@pawontani.id',
                'no_telepon' => '082234567892',
                'alamat' => 'Dusun Kebonsari RT 03/RW 01, Sumbersari, Jember',
                'role' => 'Anggota',
                'jabatan' => 'Anggota',
                'id_kelompok' => $idKelompok,
                'status' => 'Aktif',
            ]
        );

        // Ambil komoditas
        $likeOp = \Illuminate\Support\Facades\DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
        $padi = Komoditas::where('nama_komoditas', $likeOp, '%padi%')->first() ?? Komoditas::first();
        $jagung = Komoditas::where('nama_komoditas', $likeOp, '%jagung%')->first() ?? Komoditas::skip(1)->first() ?? $padi;

        // Buat beberapa produk demo
        $produk1 = Produk::firstOrCreate(
            ['id_produk' => 'PRD-BERAS-ORGANIK'],
            [
                'id_pengguna' => $petani1->id_pengguna,
                'id_komoditas' => $padi?->id_komoditas,
                'nama_produk' => 'Beras Organik Pandan Wangi Super',
                'deskripsi' => 'Beras organik bersertifikat, pulen dan harum bebas pestisida kimia.',
                'jenis_harga' => 'tetap',
                'harga' => 15000,
                'satuan' => 'kg',
                'stok_fisik' => 1500,
                'stok_ditahan' => 200,
                'status' => 'aktif',
            ]
        );

        $produk2 = Produk::firstOrCreate(
            ['id_produk' => 'PRD-GABAH-KERING'],
            [
                'id_pengguna' => $petani1->id_pengguna,
                'id_komoditas' => $padi?->id_komoditas,
                'nama_produk' => 'Gabah Kering Panen (GKP) Varietas Ciherang',
                'deskripsi' => 'Gabah kering panen kualitas premium siap giling.',
                'jenis_harga' => 'tetap',
                'harga' => 7200,
                'satuan' => 'kg',
                'stok_fisik' => 5000,
                'stok_ditahan' => 0,
                'status' => 'aktif',
            ]
        );

        $produk3 = Produk::firstOrCreate(
            ['id_produk' => 'PRD-JAGUNG-PIPIL'],
            [
                'id_pengguna' => $petani2->id_pengguna,
                'id_komoditas' => $jagung?->id_komoditas,
                'nama_produk' => 'Jagung Pipil Kering Hibrida Bisi-18',
                'deskripsi' => 'Jagung pipil kering dengan kadar air di bawah 14%, cocok untuk pakan & industri.',
                'jenis_harga' => 'tetap',
                'harga' => 5500,
                'satuan' => 'kg',
                'stok_fisik' => 3000,
                'stok_ditahan' => 500,
                'status' => 'aktif',
            ]
        );

        // Buat riwayat transaksi dengan beragam tanggal, status, dan nominal
        $dataTransaksi = [
            [
                'kode_transaksi' => 'TRX-2026-001',
                'id_pengguna' => $petani1->id_pengguna,
                'id_produk' => $produk1->id_produk,
                'id_komoditas' => $padi?->id_komoditas,
                'nama_produk' => 'Beras Organik Pandan Wangi Super',
                'nama_pembeli' => 'Toko Sembako Berkah Jaya',
                'no_telepon_pembeli' => '081399887766',
                'jumlah' => 500,
                'satuan' => 'kg',
                'harga_satuan' => 15000,
                'total_harga' => 7500000,
                'total_dibayar' => 7500000,
                'status_pembayaran' => 'lunas',
                'status_penyerahan' => 'diserahkan',
                'tanggal_transaksi' => Carbon::now()->subDays(2)->toDateString(),
                'waktu_transaksi' => '09:30',
                'metode_pembayaran' => 'transfer_bri',
                'catatan' => 'Pembayaran lunas via transfer BRI, pesanan diantar ke gudang toko.',
                'tanggal_diserahkan' => Carbon::now()->subDays(1),
            ],
            [
                'kode_transaksi' => 'TRX-2026-002',
                'id_pengguna' => $petani1->id_pengguna,
                'id_produk' => $produk2->id_produk,
                'id_komoditas' => $padi?->id_komoditas,
                'nama_produk' => 'Gabah Kering Panen (GKP) Varietas Ciherang',
                'nama_pembeli' => 'UD Penggilingan Padi Makmur',
                'no_telepon_pembeli' => '082155443322',
                'jumlah' => 2000,
                'satuan' => 'kg',
                'harga_satuan' => 7200,
                'total_harga' => 14400000,
                'total_dibayar' => 7000000,
                'status_pembayaran' => 'dp',
                'status_penyerahan' => 'belum_diserahkan',
                'tanggal_transaksi' => Carbon::now()->subDays(5)->toDateString(),
                'waktu_transaksi' => '14:15',
                'metode_pembayaran' => 'transfer_bri',
                'catatan' => 'Uang Muka 50% diterima. Pelunasan saat muat truk akhir pekan.',
                'tanggal_diserahkan' => null,
            ],
            [
                'kode_transaksi' => 'TRX-2026-003',
                'id_pengguna' => $petani2->id_pengguna,
                'id_produk' => $produk3->id_produk,
                'id_komoditas' => $jagung?->id_komoditas,
                'nama_produk' => 'Jagung Pipil Kering Hibrida Bisi-18',
                'nama_pembeli' => 'Koperasi Peternak Unggas Sejahtera',
                'no_telepon_pembeli' => '085211223344',
                'jumlah' => 1500,
                'satuan' => 'kg',
                'harga_satuan' => 5500,
                'total_harga' => 8250000,
                'total_dibayar' => 8250000,
                'status_pembayaran' => 'lunas',
                'status_penyerahan' => 'diserahkan',
                'tanggal_transaksi' => Carbon::now()->subDays(10)->toDateString(),
                'waktu_transaksi' => '11:00',
                'metode_pembayaran' => 'tunai',
                'catatan' => 'Transaksi tunai di balai kelompok tani, pengambilan mandiri.',
                'tanggal_diserahkan' => Carbon::now()->subDays(10),
            ],
            [
                'kode_transaksi' => 'TRX-2026-004',
                'id_pengguna' => $petani2->id_pengguna,
                'id_produk' => $produk3->id_produk,
                'id_komoditas' => $jagung?->id_komoditas,
                'nama_produk' => 'Jagung Pipil Kering Hibrida Bisi-18',
                'nama_pembeli' => 'Bpk. Hendra (Pengepul Lokal)',
                'no_telepon_pembeli' => '087788990011',
                'jumlah' => 800,
                'satuan' => 'kg',
                'harga_satuan' => 5400,
                'total_harga' => 4320000,
                'total_dibayar' => 0,
                'status_pembayaran' => 'tempo',
                'status_penyerahan' => 'diserahkan',
                'tanggal_transaksi' => Carbon::now()->subDays(14)->toDateString(),
                'waktu_transaksi' => '16:45',
                'metode_pembayaran' => 'tempo',
                'catatan' => 'Sistem tempo 2 minggu, jatuh tempo tanggal ' . Carbon::now()->toDateString() . '.',
                'tanggal_diserahkan' => Carbon::now()->subDays(14),
            ],
            [
                'kode_transaksi' => 'TRX-2026-005',
                'id_pengguna' => $petani1->id_pengguna,
                'id_produk' => $produk1->id_produk,
                'id_komoditas' => $padi?->id_komoditas,
                'nama_produk' => 'Beras Organik Pandan Wangi Super',
                'nama_pembeli' => 'Restoran Sari Rasa',
                'no_telepon_pembeli' => '081298765411',
                'jumlah' => 300,
                'satuan' => 'kg',
                'harga_satuan' => 15000,
                'total_harga' => 4500000,
                'total_dibayar' => 4500000,
                'status_pembayaran' => 'lunas',
                'status_penyerahan' => 'diserahkan',
                'tanggal_transaksi' => Carbon::now()->subDays(20)->toDateString(),
                'waktu_transaksi' => '10:20',
                'metode_pembayaran' => 'transfer_bri',
                'catatan' => 'Langganan tetap bulanan beras organik poktan.',
                'tanggal_diserahkan' => Carbon::now()->subDays(19),
            ],
            [
                'kode_transaksi' => 'TRX-2026-006',
                'id_pengguna' => $petani1->id_pengguna,
                'id_produk' => $produk2->id_produk,
                'id_komoditas' => $padi?->id_komoditas,
                'nama_produk' => 'Gabah Kering Panen (GKP) Varietas Ciherang',
                'nama_pembeli' => 'CV Lumbung Padi Nusantara',
                'no_telepon_pembeli' => '081122334455',
                'jumlah' => 3500,
                'satuan' => 'kg',
                'harga_satuan' => 7100,
                'total_harga' => 24850000,
                'total_dibayar' => 24850000,
                'status_pembayaran' => 'lunas',
                'status_penyerahan' => 'diserahkan',
                'tanggal_transaksi' => Carbon::now()->subMonths(1)->subDays(3)->toDateString(),
                'waktu_transaksi' => '13:00',
                'metode_pembayaran' => 'transfer_bri',
                'catatan' => 'Penjualan partai besar pasca panen raya serentak poktan.',
                'tanggal_diserahkan' => Carbon::now()->subMonths(1)->subDays(2),
            ],
            [
                'kode_transaksi' => 'TRX-2026-007',
                'id_pengguna' => $petani2->id_pengguna,
                'id_produk' => $produk3->id_produk,
                'id_komoditas' => $jagung?->id_komoditas,
                'nama_produk' => 'Jagung Pipil Kering Hibrida Bisi-18',
                'nama_pembeli' => 'Toko Pakan Ternak Barokah',
                'no_telepon_pembeli' => '085677889900',
                'jumlah' => 1200,
                'satuan' => 'kg',
                'harga_satuan' => 5400,
                'total_harga' => 6480000,
                'total_dibayar' => 6480000,
                'status_pembayaran' => 'lunas',
                'status_penyerahan' => 'diserahkan',
                'tanggal_transaksi' => Carbon::now()->subMonths(2)->subDays(5)->toDateString(),
                'waktu_transaksi' => '08:45',
                'metode_pembayaran' => 'transfer',
                'catatan' => 'Pengiriman gelombang pertama.',
                'tanggal_diserahkan' => Carbon::now()->subMonths(2)->subDays(4),
            ],
            [
                'kode_transaksi' => 'TRX-2026-008',
                'id_pengguna' => $petani1->id_pengguna,
                'id_produk' => $produk1->id_produk,
                'id_komoditas' => $padi?->id_komoditas,
                'nama_produk' => 'Beras Organik Pandan Wangi Super',
                'nama_pembeli' => 'Ibu Rahmawati (Konsumen Langsung)',
                'no_telepon_pembeli' => '089912345678',
                'jumlah' => 50,
                'satuan' => 'kg',
                'harga_satuan' => 16000,
                'total_harga' => 800000,
                'total_dibayar' => 0,
                'status_pembayaran' => 'dibatalkan',
                'status_penyerahan' => 'belum_diserahkan',
                'tanggal_transaksi' => Carbon::now()->subDays(7)->toDateString(),
                'waktu_transaksi' => '15:10',
                'metode_pembayaran' => 'tunai',
                'catatan' => 'Dibatalkan karena pembeli memesan ulang varietas lain.',
                'alasan_batal' => 'Pembeli meminta ganti jenis komoditas',
                'tanggal_dibatalkan' => Carbon::now()->subDays(6),
            ],
        ];

        foreach ($dataTransaksi as $item) {
            $trx = TransaksiPenjualan::updateOrCreate(
                ['kode_transaksi' => $item['kode_transaksi']],
                array_merge($item, [
                    'id_penjualan' => $item['kode_transaksi'],
                ])
            );

            // Tambahkan riwayat pembayaran jika ada pembayaran
            if ($trx->total_dibayar > 0) {
                RiwayatPembayaranPenjualan::updateOrCreate(
                    ['id_pembayaran' => 'PAY-' . $trx->kode_transaksi . '-1'],
                    [
                        'id_penjualan' => $trx->id_penjualan,
                        'nominal' => $trx->status_pembayaran === 'dp' ? $trx->total_dibayar : $trx->total_harga,
                        'tanggal_pembayaran' => $trx->tanggal_transaksi,
                        'keterangan' => $trx->status_pembayaran === 'dp' ? 'Pembayaran Uang Muka (DP)' : 'Pembayaran Lunas',
                        'status_verifikasi' => 'terverifikasi',
                    ]
                );
            }
        }
    }
}
