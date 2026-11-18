<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PanenController extends Controller
{
    /**
     * Display the Panen management page.
     */
    public function index(Request $request)
    {
        $pengurus = Auth::user();

        // Data riwayat panen (sesuai tampilan desain)
        $panenList = [
            [
                'id' => 1,
                'komoditas' => 'Cabai',
                'gambar' => 'images/panen/cabai.jpg',
                'lahan' => 'Lahan 1',
                'tanggal_panen' => '15 Nov 2026',
                'hasil_panen' => '100 Kg',
                'jumlah_kg' => 100,
                'kualitas' => 'A',
                'status' => 'Tersedia',
            ],
            [
                'id' => 2,
                'komoditas' => 'Tomat',
                'gambar' => 'images/panen/tomat.jpg',
                'lahan' => 'Lahan 2',
                'tanggal_panen' => '14 Nov 2026',
                'hasil_panen' => '80 Kg',
                'jumlah_kg' => 80,
                'kualitas' => 'A',
                'status' => 'Terjual Sebagian',
            ],
            [
                'id' => 3,
                'komoditas' => 'Jagung',
                'gambar' => 'images/panen/jagung.jpg',
                'lahan' => 'Lahan 1',
                'tanggal_panen' => '12 Nov 2026',
                'hasil_panen' => '200 Kg',
                'jumlah_kg' => 200,
                'kualitas' => 'B',
                'status' => 'Habis Terjual',
            ],
            [
                'id' => 4,
                'komoditas' => 'Bayam',
                'gambar' => 'images/panen/bayam.jpg',
                'lahan' => 'Lahan 3',
                'tanggal_panen' => '10 Nov 2026',
                'hasil_panen' => '50 Kg',
                'jumlah_kg' => 50,
                'kualitas' => 'A',
                'status' => 'Tersedia',
            ],
        ];

        // Ringkasan metrik statistik (sesuai tampilan desain)
        $stats = [
            'total_panen' => '620 Kg',
            'growth_total' => '↑ 12%',
            'panen_bulan_ini' => '280 Kg',
            'growth_bulan_ini' => '↑ 8%',
            'stok_tersedia' => '430 Kg',
            'total_terjual' => '190 Kg',
            'growth_terjual' => '↑ 15%',
        ];

        return view('Pengurus.Content.Panen', compact('panenList', 'stats', 'pengurus'));
    }
}
