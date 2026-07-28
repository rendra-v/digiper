<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Sheet Cek — Rekap Penyerahan Honorarium</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --ink:       #1a1a2e;
            --accent:    #1e3a5f;
            --accent2:   #2e5999;
            --border:    #b0bdd6;
            --bg-alt:    #f4f6fb;
            --gold:      #c8a84b;
        }

        html, body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            color: var(--ink);
            background: #e8ecf3;
            line-height: 1.4;
        }

        /* ── Action bar (screen only) ────────────────────────────────── */
        .action-bar {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 999;
            background: #f3f4f6;
            border-bottom: 1px solid #d1d5db;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .action-bar .brand {
            color: #111;
            font-size: 13px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .action-bar .hint { color: #555; font-family: Arial, sans-serif; font-size: 8.5pt; }
        .action-bar .btn-group { display: flex; gap: 10px; align-items: center; }

        .btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 14px; border-radius: 6px;
            font-family: Arial, sans-serif; font-size: 12px; font-weight: 600;
            cursor: pointer; border: 1px solid rgba(0,0,0,.2); transition: filter .15s; text-decoration: none;
        }
        .btn:hover { filter: brightness(0.95); }
        .btn-print { background: #f4a418; color: #000; border: none; padding: 6px 18px; }
        .btn-back  { background: #fff; color: #333; }

        /* ── Page wrapper ──────────────────────────────────────────────── */
        .pages {
            margin-top: 72px;
            padding: 24px 16px 48px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* ── Paper ─────────────────────────────────────────────────────── */
        .page {
            width: 330mm; /* F4 landscape width for screen preview */
            background: #fff;
            box-shadow: 0 4px 32px rgba(0,0,0,.18);
            border-radius: 3px;
            padding: 16mm 14mm 12mm;
        }

        /* ── Judul ────────────────────────────────────────────────────────── */
        .doc-title {
            text-align: center;
            margin-bottom: 8px;
            margin-top: 0;
            line-height: 1.45;
        }
        .doc-title .t1 { font-size: 14pt; font-weight: 700; text-transform: uppercase; color: #111; }
        .doc-title .t2 { font-size: 14pt; font-weight: 700; color: #111; }

        /* ── Tabel ─────────────────────────────────────────────────────── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            table-layout: auto;
            color: #111;
        }
        th, td {
            border: 0.8px solid #555;
            padding: 5px 7px;
            vertical-align: middle;
            word-wrap: break-word;
        }
        thead th {
            background: #d9d9d9;
            font-weight: 700;
            text-align: center;
        }
        .td-right { text-align: right; white-space: nowrap; }
        .td-center { text-align: center; }
        .td-left { text-align: left; }

        /* ── Footer / Tanda Tangan ─────────────────────────────────────── */
        .footer-wrap {
            margin-top: 16px;
            padding-top: 10px;
            border-top: 1px solid var(--border);
        }
        .footer-date { text-align: right; font-size: 11pt; margin-bottom: 12px; color: #4a4a6a; }
        .ttd-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 16px;
            font-size: 11pt;
        }
        .ttd-label { font-weight: 700; text-transform: uppercase; line-height: 1.4; }
        .ttd-space { height: 5cm; margin: 6px 0 4px; }
        .ttd-name  { font-weight: 700; text-decoration: underline; }
        .ttd-item  { text-align: center; }

        /* ── PRINT ──────────────────────────────────────────────────────── */
        @@page {
            margin: 0;
            size: 330mm 215.9mm;
            /* Hapus header/footer bawaan browser */
            @top-left   { content: ''; }
            @top-center { content: ''; }
            @top-right  { content: ''; }
            @bottom-left   { content: ''; }
            @bottom-center { content: ''; }
            @bottom-right  { content: ''; }
        }
        @media print {
            html { background: #fff; }
            body { background: #fff; font-size: 9.5pt; padding: 20mm 15mm 10mm; }
            .action-bar { display: none !important; }
            .pages { margin-top: 0; padding: 0; background: none; }
            .page {
                width: 100%;
                box-shadow: none;
                border-radius: 0;
                padding: 0;
            }
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
            table { font-size: 7pt; width: 96%; margin: 0 auto; }
            thead th, tbody td { padding: 3px 4px; }
            tbody tr { break-inside: avoid; page-break-inside: avoid; }
            .td-right { white-space: nowrap; }
            .footer-wrap { break-inside: avoid; page-break-inside: avoid; }
            .ttd-grid { break-inside: avoid; page-break-inside: avoid; }
            .ttd-space { height: 5cm; }
        }
    </style>
</head>
<body>

{{-- Action Bar --}}
<div class="action-bar">
    <div class="brand">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f4a418" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
        </svg>
        Cetak Sheet Cek — Rekap Penyerahan Honorarium
    </div>
    <span class="hint">F4 Landscape · Ukuran kertas diatur otomatis</span>
    <div class="btn-group">
        <a href="{{ route('sheet-cek') }}" class="btn btn-back">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            Kembali
        </a>
        <button class="btn btn-print" onclick="window.print()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Cetak / Simpan PDF
        </button>
    </div>
</div>

<div class="pages">

@if(isset($error) && $error)
    <div style="margin:40px auto;max-width:480px;background:#fff8e6;border:1px solid #f0c040;border-radius:8px;padding:24px;text-align:center;font-family:Arial,sans-serif;">
        <h3 style="color:#b45309;margin-bottom:8px;">⚠ Gagal memuat data</h3>
        <p style="color:#78350f;font-size:9pt;">{{ $error }}</p>
    </div>

@elseif(empty($groups))
    <div style="margin:40px auto;max-width:480px;background:#fff8e6;border:1px solid #f0c040;border-radius:8px;padding:24px;text-align:center;font-family:Arial,sans-serif;">
        <h3 style="color:#b45309;margin-bottom:8px;">Tidak ada data</h3>
        <p style="color:#78350f;font-size:9pt;">Sheet "cek" tidak ditemukan atau kosong.</p>
    </div>

@else
<div class="page">

    {{-- Judul --}}
    <div class="doc-title">
        <div class="t1">REKAP TOTAL PENYERAHAN KE MASING-MASING PANMUD</div>
        <div class="t2">Honorarium Biaya Penyelesaian Perkara</div>
        @if(Session::has('excel_period') && Session::get('excel_period') !== '')
            <div class="t2" style="font-weight:normal;margin-top:2px;font-size:10pt;">Periode: {{ strtoupper(Session::get('excel_period')) }}</div>
        @endif
    </div>

    {{-- Tabel --}}
    <table>
        <thead>
            <tr>
                <th>NO</th>
                <th>PERKARA</th>
                <th>JENIS PERKARA</th>
                <th>JUMLAH</th>
                <th>BIAYA PERKARA</th>
                <th>JUMLAH BIAYA</th>
                <th>KETERANGAN</th>
                <th>TIM</th>
                <th>5 MAJELIS</th>
                <th>KEPANITERAAN</th>
                <th>PEMILAH</th>
                <th>TOTAL BRUTO</th>
                <th>POT. PAJAK</th>
                <th>TOTAL NETTO</th>
                <th>SUBTOTAL PERKARA</th>
                <th>TOTAL KELOMPOK</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groups as $group)
                @php
                    $groupTotal = 0;
                    $totalGroupRows = 0;
                    foreach($group['sub_groups'] as $sg) {
                        $p15 = round($sg['total_m_15'] * 0.15);
                        $b15 = $sg['total_m_15'] - $p15;
                        $p5  = round($sg['total_m_5'] * 0.05);
                        $b5  = $sg['total_m_5'] - $p5;
                        $groupTotal += ($b15 + $b5);
                        $totalGroupRows += ($sg['label'] ? 4 : 3);
                    }
                @endphp
                @foreach($group['sub_groups'] as $index => $sg)
                    @php
                        $pajak15   = round($sg['total_m_15'] * 0.15);
                        $bersih15  = $sg['total_m_15'] - $pajak15;
                        $pajak5    = round($sg['total_m_5'] * 0.05);
                        $bersih5   = $sg['total_m_5'] - $pajak5;
                        $subGroupTotal = $bersih15 + $bersih5;

                        $totBiaya   = $sg['total_15'] + $sg['total_5'];
                        $totTim     = $sg['tim_15'] + $sg['tim_5'];
                        $totMajelis = $sg['majelis5_15'] + $sg['majelis5_5'];
                        $totKepan   = $sg['kepaniteraan_15'] + $sg['kepaniteraan_5'];
                        $totPemilah = $sg['pemilah_15'] + $sg['pemilah_5'];
                        $totBruto   = $sg['total_m_15'] + $sg['total_m_5'];
                        $totPajak   = $pajak15 + $pajak5;
                    @endphp

                    @if($sg['label'])
                    <tr style="background:#e9e9e9;font-weight:bold;">
                        @if($index === 0)
                        <td class="td-center" rowspan="{{ $totalGroupRows }}">{{ $group['no'] }}</td>
                        <td class="td-left" rowspan="{{ $totalGroupRows }}">{{ $group['perkara'] }}</td>
                        @endif
                        <td class="td-left" colspan="13" style="background:#d9d9d9;">{{ $sg['label'] }} — {{ $sg['jenis'] }}</td>
                        @if($index === 0)
                        <td class="td-right" rowspan="{{ $totalGroupRows }}">{{ $groupTotal > 0 ? number_format($groupTotal, 0, ',', '.') : '-' }}</td>
                        @endif
                    </tr>
                    @endif

                    {{-- Baris Total Sub-Group --}}
                    <tr style="background:#f3f4f6;font-weight:bold;">
                        @if($index === 0 && !$sg['label'])
                        <td class="td-center" rowspan="{{ $totalGroupRows }}">{{ $group['no'] }}</td>
                        <td class="td-left" rowspan="{{ $totalGroupRows }}">{{ $group['perkara'] }}</td>
                        @endif
                        <td class="td-left" rowspan="3">{{ $sg['jenis'] }}</td>
                        <td class="td-right" rowspan="3">{{ $sg['jumlah'] > 0 ? number_format($sg['jumlah'], 0, ',', '.') : '-' }}</td>
                        <td class="td-right">{{ $sg['biaya_total'] > 0 ? number_format($sg['biaya_total'], 0, ',', '.') : '-' }}</td>
                        <td class="td-right">{{ $totBiaya > 0 ? number_format($totBiaya, 0, ',', '.') : '-' }}</td>
                        <td class="td-center" style="background:#d9d9d9;">TOTAL</td>
                        <td class="td-right">{{ $totTim > 0 ? number_format($totTim, 0, ',', '.') : '-' }}</td>
                        <td class="td-right">{{ $totMajelis > 0 ? number_format($totMajelis, 0, ',', '.') : '-' }}</td>
                        <td class="td-right">{{ $totKepan > 0 ? number_format($totKepan, 0, ',', '.') : '-' }}</td>
                        <td class="td-right">{{ $totPemilah > 0 ? number_format($totPemilah, 0, ',', '.') : '-' }}</td>
                        <td class="td-right">{{ $totBruto > 0 ? number_format($totBruto, 0, ',', '.') : '-' }}</td>
                        <td class="td-right">{{ $totPajak > 0 ? number_format($totPajak, 0, ',', '.') : '-' }}</td>
                        <td class="td-right">{{ $subGroupTotal > 0 ? number_format($subGroupTotal, 0, ',', '.') : '-' }}</td>
                        <td class="td-right" rowspan="3">{{ $subGroupTotal > 0 ? number_format($subGroupTotal, 0, ',', '.') : '-' }}</td>
                        @if($index === 0 && !$sg['label'])
                        <td class="td-right" rowspan="{{ $totalGroupRows }}">{{ $groupTotal > 0 ? number_format($groupTotal, 0, ',', '.') : '-' }}</td>
                        @endif
                    </tr>
                    {{-- Baris PPH 15% --}}
                    <tr>
                        <td class="td-right">{{ $sg['biaya_15'] > 0 ? number_format($sg['biaya_15'], 0, ',', '.') : '-' }}</td>
                        <td class="td-right">{{ $sg['total_15'] > 0 ? number_format($sg['total_15'], 0, ',', '.') : '-' }}</td>
                        <td class="td-center" style="background:#e9e9e9;font-weight:bold;">PAJAK 15 %</td>
                        <td class="td-right">{{ $sg['tim_15'] > 0 ? number_format($sg['tim_15'], 0, ',', '.') : '-' }}</td>
                        <td class="td-right">{{ $sg['majelis5_15'] > 0 ? number_format($sg['majelis5_15'], 0, ',', '.') : '-' }}</td>
                        <td class="td-right">{{ $sg['kepaniteraan_15'] > 0 ? number_format($sg['kepaniteraan_15'], 0, ',', '.') : '-' }}</td>
                        <td class="td-right">{{ $sg['pemilah_15'] > 0 ? number_format($sg['pemilah_15'], 0, ',', '.') : '-' }}</td>
                        <td class="td-right">{{ $sg['total_m_15'] > 0 ? number_format($sg['total_m_15'], 0, ',', '.') : '-' }}</td>
                        <td class="td-right">{{ $pajak15 > 0 ? number_format($pajak15, 0, ',', '.') : '-' }}</td>
                        <td class="td-right">{{ $bersih15 > 0 ? number_format($bersih15, 0, ',', '.') : '-' }}</td>
                    </tr>
                    {{-- Baris PPH 5% --}}
                    <tr>
                        <td class="td-right">{{ $sg['biaya_5'] > 0 ? number_format($sg['biaya_5'], 0, ',', '.') : '-' }}</td>
                        <td class="td-right">{{ $sg['total_5'] > 0 ? number_format($sg['total_5'], 0, ',', '.') : '-' }}</td>
                        <td class="td-center" style="background:#e9e9e9;font-weight:bold;">PAJAK 5 %</td>
                        <td class="td-right">{{ $sg['tim_5'] > 0 ? number_format($sg['tim_5'], 0, ',', '.') : '-' }}</td>
                        <td class="td-right">{{ $sg['majelis5_5'] > 0 ? number_format($sg['majelis5_5'], 0, ',', '.') : '-' }}</td>
                        <td class="td-right">{{ $sg['kepaniteraan_5'] > 0 ? number_format($sg['kepaniteraan_5'], 0, ',', '.') : '-' }}</td>
                        <td class="td-right">{{ $sg['pemilah_5'] > 0 ? number_format($sg['pemilah_5'], 0, ',', '.') : '-' }}</td>
                        <td class="td-right">{{ $sg['total_m_5'] > 0 ? number_format($sg['total_m_5'], 0, ',', '.') : '-' }}</td>
                        <td class="td-right">{{ $pajak5 > 0 ? number_format($pajak5, 0, ',', '.') : '-' }}</td>
                        <td class="td-right">{{ $bersih5 > 0 ? number_format($bersih5, 0, ',', '.') : '-' }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    {{-- Footer / Tanda Tangan --}}
    <div class="footer-wrap" style="width:96%;margin:16px auto 0;">
        <div class="ttd-grid" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;font-size:11pt;">
            <div class="ttd-item">
                <div class="ttd-label">Petugas Pembuat Komitmen<br>Biaya Proses</div>
                <div class="ttd-space"></div>
                <div class="ttd-name">ST. KRIS NUGROHO, S.H., M.H.</div>
            </div>
            <div class="ttd-item">
                <div class="ttd-label">Mengetahui,<br>Kuasa Pengelola Biaya Proses</div>
                <div class="ttd-space"></div>
                <div class="ttd-name">ASEP NURSOBAH, S.Ag., M.H.</div>
            </div>
            <div class="ttd-item">
                <div class="ttd-label">Bendahara Biaya Proses</div>
                <div class="ttd-space"></div>
                <div class="ttd-name">FARIDA, S.H.</div>
            </div>
        </div>
    </div>

</div>{{-- /.page --}}
@endif

</div>{{-- /.pages --}}
</body>
</html>
