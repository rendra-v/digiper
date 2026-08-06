<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak – Rekap Panitera Muda</title>
    <style>
        @page {
            margin: 1.5cm 1.2cm 1.2cm 1.2cm;
            size: A4 landscape;
            @top-left   { content: ''; }
            @top-center { content: ''; }
            @top-right  { content: ''; }
            @bottom-left   { content: ''; }
            @bottom-center { content: ''; }
            @bottom-right  { content: ''; }
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            color: #111;
            background: #fff;
        }

        /* ── Toolbar (screen only) ── */
        .no-print {
            font-family: Arial, sans-serif;
            font-size: 13px;
            background: #f3f4f6;
            color: #111;
            border-bottom: 1px solid #d1d5db;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* ── Block wrapper (1 per halaman) ── */
        .block-page {
            break-after: page;
            page-break-after: always;
            margin-bottom: 2cm;
        }
        .block-page:last-child {
            break-after: avoid;
            page-break-after: avoid;
            margin-bottom: 0;
        }

        /* ── Judul ── */
        .blk-title {
            text-align: center;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            line-height: 1.5;
            margin-bottom: 2px;
        }
        .blk-subtitle {
            text-align: center;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        /* ── Tabel ── */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 0.8px solid #555;
            padding: 4px 6px;
            font-size: 9px;
            vertical-align: middle;
        }
        thead tr { background: #d9d9d9; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        thead th { font-weight: 700; text-align: center; }
        .td-no  { text-align: center; width: 28px; }
        .td-l   { text-align: left; }
        .td-c   { text-align: center; }
        .td-r   { text-align: right; }
        .td-bold{ font-weight: 700; }
        .row-total td { font-weight: 700; background: #f5f5f5; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        /* Rp cell split: Rp kiri, angka kanan */
        .rp-cell { display: flex; justify-content: space-between; gap: 4px; }
        .rp-label { white-space: nowrap; }
        .rp-val { text-align: right; white-space: nowrap; }

        /* ── Tanda Tangan ── */
        .sig-wrap {
            margin-top: 16px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .sig-col { text-align: center; min-width: 160px; font-size: 9px; }
        .sig-col .sig-label { font-weight: 700; }
        .sig-col .sig-space { height: 2.2cm; }
        .sig-col .sig-name  { font-weight: 700; font-size: 10px; }
        .sig-col .sig-under { text-decoration: underline; font-weight: 700; }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
            .block-page { break-after: page !important; page-break-after: always !important; }
            .block-page:last-child { break-after: avoid !important; page-break-after: avoid !important; }
        }
    </style>
</head>
<body>

{{-- Toolbar (screen only) --}}
<div class="no-print">
    <span>🖨️ Rekap Panitera Muda
        @if($fileName) — {{ $fileName }} @endif
    </span>
    <div style="display:flex; gap:8px;">
        <button onclick="window.print()"
            style="padding:6px 18px; background:#2563eb; color:#fff; border:none; border-radius:6px; font-weight:600; cursor:pointer;">
            Cetak / Simpan PDF
        </button>
        <button onclick="window.close()"
            style="padding:6px 14px; background:#fff; color:#333; border:1px solid rgba(0,0,0,.2); border-radius:6px; cursor:pointer; font-weight:600;">
            Tutup
        </button>
    </div>
</div>

@if($error)
    <div style="margin:30px auto; max-width:400px; padding:12px; border:1px solid #ddd; border-radius:6px; font-size:12px;">
        ⚠ {{ $error }}
    </div>
@elseif(empty($tables))
    <div style="margin:30px auto; max-width:400px; padding:12px; border:1px solid #ddd; border-radius:6px; font-size:12px;">
        Tidak ada data. Silakan upload file Excel terlebih dahulu.
    </div>
@else

@foreach($tables as $kamar => $tbls)
@foreach($tbls as $block)
@php
    $jml      = $block['jml_perkara'];
    $isLast   = $loop->parent->last && $loop->last;

    // Format Rp helper
    $rp = fn($v) => $v > 0 ? number_format($v, 0, ',', '.') : '-';
@endphp
<div class="block-page">

    {{-- Judul --}}
    <div class="blk-title">{{ $block['title'] }}</div>
    <div class="blk-subtitle">{{ $block['subtitle'] }} ( Sebanyak {{ number_format($jml, 0, ',', '.') }} Perkara )</div>

    {{-- Tabel --}}
    <table>
        <thead>
            <tr>
                <th class="td-no">NO</th>
                <th class="td-l" style="width:22%;">NAMA</th>
                <th class="td-c" style="width:18%;">PERIODE</th>
                <th class="td-c" colspan="2">JUMLAH BIAYA</th>
                <th class="td-c" colspan="2">PPH 15%</th>
                <th class="td-c" colspan="2">PPH 5%</th>
                <th class="td-c" colspan="2">NETTO</th>
            </tr>
        </thead>
        <tbody>
            @foreach($block['rows'] as $row)
            <tr>
                <td class="td-no">{{ $row['no'] }}</td>
                <td class="td-l td-bold">{{ $row['nama'] }}</td>
                <td class="td-c">{{ $period ?: '-' }}</td>
                <td class="td-c" style="width:22px;">Rp</td>
                <td class="td-r">{{ $rp($row['jumlah_biaya']) }}</td>
                <td class="td-c" style="width:22px;">Rp</td>
                <td class="td-r">{{ $rp($row['pph15']) }}</td>
                <td class="td-c" style="width:22px;">Rp</td>
                <td class="td-r">{{ $rp($row['pph5']) }}</td>
                <td class="td-c" style="width:22px;">Rp</td>
                <td class="td-r">{{ $rp($row['netto']) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="row-total">
                <td class="td-no"></td>
                <td class="td-c" colspan="2">TOTAL</td>
                <td class="td-c">Rp</td>
                <td class="td-r">{{ $rp($block['total']['jumlah_biaya']) }}</td>
                <td class="td-c">Rp</td>
                <td class="td-r">{{ $rp($block['total']['pph15']) }}</td>
                <td class="td-c">Rp</td>
                <td class="td-r">{{ $rp($block['total']['pph5']) }}</td>
                <td class="td-c">Rp</td>
                <td class="td-r">{{ $rp($block['total']['netto']) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Tanda Tangan --}}
    @php
        $tglTtd = $period ?: ('Jakarta, ' . \Illuminate\Support\Facades\Session::get('excel_tgl_data_laporan', date('d F Y')));
    @endphp
    <div class="sig-wrap">
        {{-- Kiri: Petugas Pembuat Komitmen --}}
        <div class="sig-col">
            <p class="sig-label">PETUGAS PEMBUAT KOMITMEN</p>
            <p class="sig-label">BIAYA PROSES</p>
            <div class="sig-space"></div>
            <p class="sig-under">{{ $pejabat['petugas_pembuat'] ?? '' }}</p>
        </div>

        {{-- Tengah: Kuasa Pengelola --}}
        <div class="sig-col">
            <p>{{ $tglTtd }}</p>
            <p class="sig-label" style="margin-top:4px;">MENGETAHUI,</p>
            <p class="sig-label">KUASA PENGELOLA BIAYA PROSES</p>
            <div class="sig-space"></div>
            <p class="sig-under">{{ $pejabat['kuasa_pengelola'] ?? '' }}</p>
        </div>

        {{-- Kanan: Bendahara --}}
        <div class="sig-col">
            <p>{{ $tglTtd }}</p>
            <p class="sig-label" style="margin-top:4px;">BENDAHARA BIAYA PROSES</p>
            <div class="sig-space"></div>
            <p class="sig-under">{{ $pejabat['bendahara'] ?? '' }}</p>
        </div>
    </div>

</div>
@endforeach
@endforeach

@endif
</body>
</html>
