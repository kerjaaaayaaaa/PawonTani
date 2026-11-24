<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cetak Laporan Transaksi - {{ $kelompok?->nama_kelompok ?? 'PawonTani' }}</title>
  <link rel="icon" type="image/png" href="{{ asset('images/Logo.png') }}">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest" defer></script>

  <style>
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      color: #1A2D10;
      background-color: #F5F8F1;
      margin: 0;
      padding: 24px;
    }

    .report-card {
      max-width: 1020px;
      margin: 0 auto;
      background: #ffffff;
      padding: 32px 40px;
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
      border: 1px solid #E4F0D6;
    }

    /* Print Controls */
    .print-controls {
      max-width: 1020px;
      margin: 0 auto 16px auto;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .btn-print {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background-color: #398D32;
      color: white;
      padding: 10px 20px;
      border-radius: 10px;
      font-weight: 700;
      font-size: 14px;
      border: none;
      cursor: pointer;
      transition: background 0.2s;
    }
    .btn-print:hover {
      background-color: #3D7826;
    }

    .btn-back {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background-color: white;
      color: #4A6030;
      padding: 10px 16px;
      border-radius: 10px;
      font-weight: 600;
      font-size: 13px;
      border: 1px solid #C5DFB0;
      text-decoration: none;
    }
    .btn-back:hover {
      background-color: #F5F8F1;
    }

    /* Kop Surat */
    .kop-header {
      display: flex;
      align-items: center;
      gap: 24px;
      padding-bottom: 20px;
      border-bottom: 3px double #398D32;
      margin-bottom: 24px;
    }

    .kop-logo {
      height: 64px;
      width: auto;
      object-contain: contain;
    }

    .kop-info h1 {
      margin: 0;
      font-size: 20px;
      font-weight: 800;
      color: #1A2D10;
      letter-spacing: -0.3px;
      text-transform: uppercase;
    }

    .kop-info h2 {
      margin: 4px 0 0 0;
      font-size: 14px;
      font-weight: 600;
      color: #398D32;
    }

    .kop-info p {
      margin: 4px 0 0 0;
      font-size: 11px;
      color: #64748b;
    }

    /* Meta & Title */
    .document-title {
      text-align: center;
      margin-bottom: 24px;
    }

    .document-title h3 {
      margin: 0;
      font-size: 16px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #1A2D10;
    }

    .document-title p {
      margin: 4px 0 0 0;
      font-size: 12px;
      color: #4A6030;
    }

    /* Summary KPI Box */
    .summary-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 12px;
      margin-bottom: 24px;
    }

    .summary-item {
      padding: 12px 14px;
      border-radius: 10px;
      background-color: #F5F8F1;
      border: 1px solid #E4F0D6;
    }

    .summary-label {
      font-size: 10px;
      font-weight: 700;
      color: #9AB880;
      text-transform: uppercase;
      margin-bottom: 2px;
    }

    .summary-val {
      font-size: 15px;
      font-weight: 800;
      color: #1A2D10;
    }

    /* Table */
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 11px;
      margin-bottom: 28px;
    }

    th {
      background-color: #EAF5E5;
      color: #1A2D10;
      font-weight: 700;
      text-transform: uppercase;
      font-size: 10px;
      letter-spacing: 0.3px;
      border: 1px solid #C5DFB0;
      padding: 8px 10px;
      text-align: left;
    }

    td {
      border: 1px solid #E4F0D6;
      padding: 7px 10px;
      vertical-align: middle;
    }

    tr:nth-child(even) {
      background-color: #FBFDF9;
    }

    .text-right { text-align: right; }
    .text-center { text-align: center; }

    .badge {
      display: inline-block;
      padding: 2px 8px;
      border-radius: 9999px;
      font-size: 9px;
      font-weight: 700;
      text-transform: uppercase;
    }
    .badge-lunas { background: #EAF5E5; color: #2E5A1C; border: 1px solid #C5DFB0; }
    .badge-dp { background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A; }
    .badge-tempo { background: #DBEAFE; color: #1E40AF; border: 1px solid #BFDBFE; }
    .badge-batal { background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; }

    /* Signatures */
    .sign-section {
      display: flex;
      justify-content: space-between;
      margin-top: 40px;
      page-break-inside: avoid;
    }

    .sign-box {
      text-align: center;
      width: 240px;
    }

    .sign-date {
      font-size: 11px;
      color: #64748b;
      margin-bottom: 6px;
    }

    .sign-role {
      font-size: 12px;
      font-weight: 700;
      color: #1A2D10;
    }

    .sign-space {
      height: 60px;
    }

    .sign-name {
      font-size: 12px;
      font-weight: 800;
      color: #1A2D10;
      text-decoration: underline;
    }

    .sign-id {
      font-size: 10px;
      color: #64748b;
    }

    /* Print Optimization */
    @media print {
      body {
        background: white;
        padding: 0;
      }
      .print-controls {
        display: none !important;
      }
      .report-card {
        border: none;
        box-shadow: none;
        padding: 0;
        margin: 0;
        max-width: 100%;
      }
      @page {
        size: A4 portrait;
        margin: 1.5cm;
      }
    }
  </style>
</head>
<body>

  <!-- Tombol Aksi Layar -->
  <div class="print-controls">
    <a href="{{ route('pengurus.laporan', request()->query()) }}" class="btn-back">
      <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
      <span>Kembali ke Laporan</span>
    </a>
    <button onclick="window.print()" class="btn-print">
      <i data-lucide="printer" style="width: 18px; height: 18px;"></i>
      <span>Cetak Dokumen Sekarang</span>
    </button>
  </div>

  <div class="report-card">

    <!-- 1. KOP SURAT POKTAN -->
    <div class="kop-header">
      <img src="{{ asset('images/Logo2.png') }}" alt="PawonTani Logo" class="kop-logo">
      <div class="kop-info">
        <h1>{{ $kelompok?->nama_kelompok ?? 'KELOMPOK TANI BINAAN' }}</h1>
        <h2>SISTEM MANAJEMEN OPERASIONAL &amp; DISTRIBUSI HASIL TANI PAWONTANI</h2>
        <p>{{ $kelompok?->alamat ?? ($pengurus->alamat ?? 'Wilayah Kerja Pertanian Jawa Timur') }} • Pengurus Poktan: {{ $pengurus->nama }}</p>
      </div>
    </div>

    <!-- 2. JUDUL DOKUMEN -->
    <div class="document-title">
      <h3>Laporan Rekapitulasi Transaksi Penjualan Hasil Tani</h3>
      <p>Dicetak pada: {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y - HH:mm') }} WIB</p>
    </div>

    <!-- 3. RINGKASAN METRIK FINANSIAL -->
    <div class="summary-grid">
      <div class="summary-item">
        <div class="summary-label">Total Omzet Penjualan</div>
        <div class="summary-val">Rp {{ number_format($summary['total_omzet'], 0, ',', '.') }}</div>
      </div>
      <div class="summary-item">
        <div class="summary-label">Kas Terbayar (Masuk)</div>
        <div class="summary-val" style="color: #2E5A1C;">Rp {{ number_format($summary['total_terbayar'], 0, ',', '.') }}</div>
      </div>
      <div class="summary-item">
        <div class="summary-label">Sisa Piutang Berjalan</div>
        <div class="summary-val" style="color: #92400E;">Rp {{ number_format($summary['total_piutang'], 0, ',', '.') }}</div>
      </div>
      <div class="summary-item">
        <div class="summary-label">Total Volume Terjual</div>
        <div class="summary-val">{{ number_format($summary['total_volume_kg'], 0, ',', '.') }} Kg</div>
      </div>
    </div>

    <!-- 4. TABEL TRANSAKSI -->
    <table>
      <thead>
        <tr>
          <th class="text-center" style="width: 30px;">No</th>
          <th>Kode &amp; Tanggal</th>
          <th>Petani (Penjual)</th>
          <th>Pembeli</th>
          <th>Produk &amp; Komoditas</th>
          <th class="text-right">Volume</th>
          <th class="text-right">Harga</th>
          <th class="text-right">Total (Rp)</th>
          <th class="text-center">Status</th>
          <th class="text-center">Metode</th>
        </tr>
      </thead>
      <tbody>
        @forelse($transaksis as $idx => $trx)
          @php
            $badgeClass = match($trx->status_pembayaran) {
              'lunas' => 'badge-lunas',
              'dp' => 'badge-dp',
              'tempo' => 'badge-tempo',
              'dibatalkan' => 'badge-batal',
              default => '',
            };
          @endphp
          <tr>
            <td class="text-center">{{ $idx + 1 }}</td>
            <td>
              <strong>{{ $trx->kode_transaksi }}</strong><br>
              <span style="color: #64748b; font-size: 10px;">{{ $trx->tanggal_transaksi ? \Carbon\Carbon::parse($trx->tanggal_transaksi)->format('d/m/Y') : '-' }}</span>
            </td>
            <td>{{ $trx->penjual?->nama ?? 'Anggota Poktan' }}</td>
            <td>{{ $trx->nama_pembeli ?: 'Pelanggan' }}</td>
            <td>
              <strong>{{ $trx->nama_produk }}</strong><br>
              <span style="color: #398D32; font-size: 10px;">{{ $trx->komoditas?->nama_komoditas ?: '-' }}</span>
            </td>
            <td class="text-right">
              <strong>{{ number_format((float) $trx->jumlah, 0, ',', '.') }}</strong> {{ $trx->satuan ?: 'kg' }}
            </td>
            <td class="text-right">
              Rp {{ number_format((float) $trx->harga_satuan, 0, ',', '.') }}
            </td>
            <td class="text-right">
              <strong>Rp {{ number_format((float) $trx->total_harga, 0, ',', '.') }}</strong>
            </td>
            <td class="text-center">
              <span class="badge {{ $badgeClass }}">{{ $trx->status_label }}</span>
            </td>
            <td class="text-center" style="font-size: 10px;">
              {{ ucwords(str_replace('_', ' ', $trx->metode_pembayaran)) }}
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="10" class="text-center" style="padding: 24px; color: #64748b;">
              Tidak ada data transaksi yang sesuai filter untuk dicetak.
            </td>
          </tr>
        @endforelse
      </tbody>
      @if($transaksis->count() > 0)
        <tfoot>
          <tr style="background-color: #EAF5E5; font-weight: 800;">
            <td colspan="5" class="text-right">TOTAL KESELURUHAN:</td>
            <td class="text-right">{{ number_format($summary['total_volume_kg'], 0, ',', '.') }} Kg</td>
            <td></td>
            <td class="text-right">Rp {{ number_format($summary['total_omzet'], 0, ',', '.') }}</td>
            <td colspan="2" class="text-center" style="font-size: 10px;">{{ $summary['lunas_count'] }} LUNAS / {{ $summary['total_transaksi'] }} TRX</td>
          </tr>
        </tfoot>
      @endif
    </table>

    <!-- 5. TANDA TANGAN RESMI -->
    <div class="sign-section">
      <div class="sign-box">
        <div class="sign-date">&nbsp;</div>
        <div class="sign-role">Petugas Penyuluh Pertanian (PPL)</div>
        <div class="sign-space"></div>
        <div class="sign-name">_________________________</div>
        <div class="sign-id">NIP. ........................................</div>
      </div>

      <div class="sign-box">
        <div class="sign-date">{{ $kelompok?->alamat ? explode(',', $kelompok->alamat)[0] : 'Jember' }}, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}</div>
        <div class="sign-role">Ketua / Pengurus Kelompok Tani</div>
        <div class="sign-space"></div>
        <div class="sign-name">{{ $pengurus->nama }}</div>
        <div class="sign-id">ID Pengurus: {{ $pengurus->id_pengguna }}</div>
      </div>
    </div>

  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      if (window.lucide) {
        window.lucide.createIcons();
      }
    });
  </script>
</body>
</html>
