<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Honorarium — {{ $fileName ?? 'Dokumen' }}</title>
    <style>
        /* ── Reset ─────────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #000;
            background: #e8ecf3;
            line-height: 1.3;
        }

        /* ── Action Bar (screen only) ─────────────────────────── */
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
        .action-bar .hint {
            color: #555;
            font-size: 8.5pt;
        }
        .action-bar .btn-group { display: flex; gap: 10px; align-items: center; }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 6px;
            font-family: Arial, sans-serif;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid rgba(0,0,0,.2);
            transition: filter .15s;
            text-decoration: none;
        }
        .btn:hover { filter: brightness(0.95); }
        .btn-print { background: #f4a418; color: #000; border: none; padding: 6px 18px; }
        .btn-back  { background: #fff; color: #333; }

        /* ── Screen: page wrapper ─────────────────────────────── */
        .pages {
            margin-top: 72px;
            padding: 24px 16px 48px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 28px;
        }

        /* ── Paper page (screen preview) ─────────────────────── */
        .page {
            width: 330mm;
            min-height: 215.9mm;
            background: #fff;
            box-shadow: 0 4px 32px rgba(0,0,0,.18), 0 1px 6px rgba(0,0,0,.08);
            border-radius: 3px;
            padding: 12mm 14mm 10mm;
            position: relative;
        }

        /* ── Judul dokumen ────────────────────────────────────── */
        .doc-title-wrap {
            text-align: center;
            margin-bottom: 8px;
        }
        .doc-title-wrap .title-main {
            font-size: 11pt;
            font-weight: 700;
            text-transform: uppercase;
            line-height: 1.3;
        }
        .doc-title-wrap .title-sub {
            font-size: 11pt;
            font-weight: 700;
            text-transform: uppercase;
            line-height: 1.3;
        }
        .doc-title-wrap .title-info {
            font-size: 11pt;
            font-weight: 400;
            margin-top: 2px;
        }

        /* ── Tabel ────────────────────────────────────────────── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11pt;
            font-family: Arial, Helvetica, sans-serif;
            table-layout: auto;
            margin-top: 6px;
        }
        thead tr {
            background: #d9d9d9;
        }
        thead th {
            border: 1px solid #000;
            padding: 5px 6px;
            font-weight: 700;
            text-align: center;
            font-size: 11pt;
        }
        tbody td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: middle;
            font-size: 11pt;
        }
        tbody tr:nth-child(even) { background: #f5f5f5; }

        .td-no    { text-align: center; width: 30px;  white-space: nowrap; }
        .td-nama  { text-align: left;   min-width: 140px; }
        .td-jab   { text-align: left;   min-width: 160px; }
        .td-num   { text-align: right;  white-space: nowrap; min-width: 80px; }
        .td-count { text-align: center; white-space: nowrap; min-width: 70px; }
        .td-ttd   { text-align: center; min-width: 80px; }

        tr.row-total {
            background: #d9d9d9 !important;
            font-weight: 700;
        }
        tr.row-total td { border: 1px solid #000; }

        /* ── Footer / Tanda Tangan ────────────────────────────── */
        .footer-wrap {
            margin-top: 16px;
        }
        .footer-date {
            text-align: right;
            font-size: 11pt;
            margin-bottom: 8px;
        }
        .ttd-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 8px;
            font-size: 11pt;
        }
        .ttd-item { }
        .ttd-item.center { text-align: center; }
        .ttd-item.right  { text-align: right;  }
        .ttd-label {
            font-weight: 700;
            text-transform: uppercase;
            line-height: 1.4;
        }
        .ttd-space {
            height: 50px;
            margin: 6px 0 4px;
        }
        .ttd-name { }

        /* ── Notice / Error ───────────────────────────────────── */
        .notice {
            margin: 40px auto;
            max-width: 520px;
            background: #fff8e6;
            border: 1px solid #f0c040;
            border-radius: 8px;
            padding: 24px;
            text-align: center;
        }
        .notice h3 { color: #b45309; margin-bottom: 8px; }
        .notice p  { color: #78350f; font-size: 9pt; }

        /* ══════════════════════════════════════════════════════
           PRINT STYLES — bersih hitam-putih, persis seperti contoh
        ══════════════════════════════════════════════════════ */
        @page {
            size: 330mm 215.9mm;
            margin: 10mm 12mm 10mm 12mm;
        }
        @media print {
            html, body {
                background: #fff;
                font-size: 11pt;
                font-family: Arial, Helvetica, sans-serif;
                color: #000;
            }
            .action-bar { display: none !important; }
            .pages { margin-top: 0; padding: 0; gap: 0; background: none; }

            .page {
                width: 100%;
                min-height: auto;
                box-shadow: none;
                border-radius: 0;
                padding: 0;
                page-break-after: always;
                break-after: page;
            }
            .page:last-child { page-break-after: avoid; break-after: avoid; }

            /* Tabel hitam-putih bersih */
            table { font-size: 11pt; width: 100% !important; table-layout: auto; }
            thead tr { background: #d9d9d9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            thead th { border: 1px solid #000; padding: 4px 5px; font-weight: 700; }
            tbody td { border: 1px solid #000; padding: 3px 5px; }
            tbody tr:nth-child(even) { background: #f0f0f0 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            tr.row-total { background: #d9d9d9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

            thead { display: table-header-group; }
            tbody tr:hover { background: inherit; }

            /* Judul */
            .doc-title-wrap .title-main,
            .doc-title-wrap .title-sub,
            .doc-title-wrap .title-info {
                font-size: 11pt;
            }

            /* Footer */
            .footer-wrap { margin-top: 10px; }
            .footer-date { font-size: 11pt; }
            .ttd-grid { font-size: 11pt; }
            .ttd-space { height: 46px; }
        }
    </style>
</head>
<body>

{{-- ── Action Bar (screen only) ──────────────────────────────────────── --}}
<div class="action-bar" aria-label="Print controls">
    <div class="brand">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f4a418" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
        </svg>
        Cetak Honorarium
        @if($fileName)
            <span style="font-weight:400;opacity:.7;font-size:10pt">— {{ $fileName }}</span>
        @endif
    </div>
    <span class="hint">Pratinjau dokumen siap cetak &middot; Gunakan PDF printer untuk simpan sebagai PDF</span>
    <div class="btn-group">
        <a href="{{ route('honorarium') }}" class="btn btn-back">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            Kembali
        </a>
        <button class="btn btn-print" onclick="window.print()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Cetak / Simpan PDF
        </button>
    </div>
</div>

{{-- ── Content ─────────────────────────────────────────────────────────── --}}
<div class="pages">

    @if($error)
        <div class="notice">
            <h3>⚠ Gagal memuat dokumen</h3>
            <p>{{ $error }}</p>
        </div>

    @elseif($computedType === 'op-staf')
        @foreach($opStafData as $oi => $block)
            @if(count($block['rows']) >= 1)
            <div class="page">
                <div class="doc-title-wrap">
                    <div class="title-main">HONORARIUM BIAYA PENYELESAIAN PERKARA {{ $block['title'] }}</div>
                    <div class="title-sub">(SEBAGAI OPERATOR / STAF)</div>
                    <div class="title-info">Sebanyak {{ number_format($block['total_perkara'], 0, ',', '.') }} Perkara</div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th class="td-no">NO</th>
                            <th class="td-nama">NAMA OPERATOR / STAF</th>
                            <th class="td-jab">JABATAN</th>
                            <th class="td-count">JUMLAH PERKARA</th>
                            <th class="td-num">BIAYA</th>
                            <th class="td-num">JUMLAH BIAYA</th>
                            <th class="td-num">PPH 15%</th>
                            <th class="td-num">PPH 5%</th>
                            <th class="td-num">NETTO</th>
                            <th class="td-ttd">TANDA TANGAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($block['rows'] as $row)
                            <tr>
                                <td class="td-no">{{ $row['no'] }}</td>
                                <td class="td-nama">{{ $row['nama'] }}</td>
                                <td class="td-jab">PANITERA PENGGANTI</td>
                                <td class="td-count">{{ number_format($row['jml'], 0, ',', '.') }}</td>
                                <td class="td-num">Rp {{ number_format($row['tarif'], 0, ',', '.') }}</td>
                                <td class="td-num">Rp {{ number_format($row['bruto'], 0, ',', '.') }}</td>
                                <td class="td-num">-</td>
                                <td class="td-num">{{ $row['pph5'] > 0 ? 'Rp ' . number_format($row['pph5'], 0, ',', '.') : '-' }}</td>
                                <td class="td-num">Rp {{ number_format($row['netto'], 0, ',', '.') }}</td>
                                <td class="td-ttd"></td>
                            </tr>
                        @endforeach
                        <tr class="row-total">
                            <td colspan="5" class="td-center" style="text-align: right; padding-right: 15px;">TOTAL</td>
                            <td class="td-num">Rp {{ number_format($block['total']['bruto'], 0, ',', '.') }}</td>
                            <td class="td-num">-</td>
                            <td class="td-num">{{ $block['total']['pph5'] > 0 ? 'Rp ' . number_format($block['total']['pph5'], 0, ',', '.') : '-' }}</td>
                            <td class="td-num">Rp {{ number_format($block['total']['netto'], 0, ',', '.') }}</td>
                            <td class="td-ttd"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @endif
        @endforeach

    @elseif($computedType === 'tim')
        @foreach($timData as $ti => $block)
            @if(count($block['rows']) >= 1)
            <div class="page">
                <div class="doc-title-wrap">
                    <div class="title-main">HONORARIUM BIAYA PENYELESAIAN PERKARA {{ $block['label'] }}</div>
                    <div class="title-sub">Sebanyak {{ number_format($block['jumlah_perkara'], 0, ',', '.') }} Perkara</div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th class="td-no">NO</th>
                            <th class="td-nama">NAMA</th>
                            <th class="td-jab">JABATAN</th>
                            <th class="td-count">JUMLAH PERKARA</th>
                            <th class="td-num">BIAYA</th>
                            <th class="td-num">JUMLAH BIAYA</th>
                            <th class="td-num">PPH 15%</th>
                            <th class="td-num">PPH 5%</th>
                            <th class="td-num">NETTO</th>
                            <th class="td-ttd">TANDA TANGAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($block['rows'] as $row)
                            <tr>
                                <td class="td-no">{{ $row['no'] }}</td>
                                <td class="td-nama">{{ $row['nama'] }}</td>
                                <td class="td-jab">{{ $row['jabatan'] }}</td>
                                <td class="td-count">{{ $row['jumlah_perkara'] > 0 ? number_format($row['jumlah_perkara'], 0, ',', '.') : '-' }}</td>
                                <td class="td-num">{{ $row['biaya'] > 0 ? 'Rp ' . number_format($row['biaya'], 0, ',', '.') : '-' }}</td>
                                <td class="td-num">{{ $row['jumlah_biaya'] > 0 ? 'Rp ' . number_format($row['jumlah_biaya'], 0, ',', '.') : '-' }}</td>
                                <td class="td-num">{{ $row['pph15'] > 0 ? 'Rp ' . number_format($row['pph15'], 0, ',', '.') : '-' }}</td>
                                <td class="td-num">{{ $row['pph5'] > 0 ? 'Rp ' . number_format($row['pph5'], 0, ',', '.') : '-' }}</td>
                                <td class="td-num">{{ $row['netto'] > 0 ? 'Rp ' . number_format($row['netto'], 0, ',', '.') : '-' }}</td>
                                <td class="td-ttd"></td>
                            </tr>
                        @endforeach
                        @if($block['totalRow'])
                            <tr class="row-total">
                                <td colspan="3" class="td-center" style="text-align: right; padding-right: 15px;">TOTAL</td>
                                <td class="td-count">{{ $block['totalRow']['jumlah_perkara'] > 0 ? number_format($block['totalRow']['jumlah_perkara'], 0, ',', '.') : '-' }}</td>
                                <td class="td-num"></td>
                                <td class="td-num">{{ $block['totalRow']['jumlah_biaya'] > 0 ? 'Rp ' . number_format($block['totalRow']['jumlah_biaya'], 0, ',', '.') : '-' }}</td>
                                <td class="td-num">{{ $block['totalRow']['pph15'] > 0 ? 'Rp ' . number_format($block['totalRow']['pph15'], 0, ',', '.') : '-' }}</td>
                                <td class="td-num">{{ $block['totalRow']['pph5'] > 0 ? 'Rp ' . number_format($block['totalRow']['pph5'], 0, ',', '.') : '-' }}</td>
                                <td class="td-num">{{ $block['totalRow']['netto'] > 0 ? 'Rp ' . number_format($block['totalRow']['netto'], 0, ',', '.') : '-' }}</td>
                                <td class="td-ttd"></td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            @endif
        @endforeach

    @elseif($computedType === 'kepaniteraan')
        @foreach($kepaniteraanData as $ki => $block)
            @if(count($block['rows']) >= 1)
            <div class="page">
                <div class="doc-title-wrap">
                    <div class="title-main">HONORARIUM BIAYA PENYELESAIAN PERKARA {{ $block['title'] }}</div>
                    <div class="title-sub">Sebanyak {{ number_format($block['jml_perkara'], 0, ',', '.') }} Perkara</div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th class="td-no">NO</th>
                            <th class="td-nama">NAMA</th>
                            <th class="td-jab">JABATAN</th>
                            <th class="td-count">JUMLAH PERKARA</th>
                            <th class="td-num">BIAYA</th>
                            <th class="td-num">JUMLAH BIAYA</th>
                            <th class="td-num">PPH 15%</th>
                            <th class="td-num">PPH 5%</th>
                            <th class="td-num">NETTO</th>
                            <th class="td-ttd">TANDA TANGAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($block['rows'] as $row)
                            <tr>
                                <td class="td-no">{{ $row['no'] }}</td>
                                <td class="td-nama">{{ $row['nama'] }}</td>
                                <td class="td-jab">{{ $row['jabatan'] }}</td>
                                <td class="td-count">{{ number_format($row['jml_perkara'], 0, ',', '.') }}</td>
                                <td class="td-num">Rp {{ number_format($row['biaya'], 0, ',', '.') }}</td>
                                <td class="td-num">Rp {{ number_format($row['jml_biaya'], 0, ',', '.') }}</td>
                                <td class="td-num">{{ $row['pph15'] > 0 ? 'Rp ' . number_format($row['pph15'], 0, ',', '.') : '-' }}</td>
                                <td class="td-num">{{ $row['pph5'] > 0 ? 'Rp ' . number_format($row['pph5'], 0, ',', '.') : '-' }}</td>
                                <td class="td-num">Rp {{ number_format($row['netto'], 0, ',', '.') }}</td>
                                <td class="td-ttd"></td>
                            </tr>
                        @endforeach
                        <tr class="row-total">
                            <td colspan="5" class="td-center" style="text-align: right; padding-right: 15px;">TOTAL</td>
                            <td class="td-num">Rp {{ number_format($block['total']['jml_biaya'], 0, ',', '.') }}</td>
                            <td class="td-num">{{ $block['total']['pph15'] > 0 ? 'Rp ' . number_format($block['total']['pph15'], 0, ',', '.') : '-' }}</td>
                            <td class="td-num">{{ $block['total']['pph5'] > 0 ? 'Rp ' . number_format($block['total']['pph5'], 0, ',', '.') : '-' }}</td>
                            <td class="td-num">Rp {{ number_format($block['total']['netto'], 0, ',', '.') }}</td>
                            <td class="td-ttd"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @endif
        @endforeach

    @elseif(empty($sheets))
        <div class="notice">
            <h3>Tidak ada data</h3>
            <p>Tidak ditemukan data honorarium yang bisa dicetak.</p>
        </div>

    @else
        @foreach($sheets as $sheetIdx => $sheet)
            @foreach($sheet['blocks'] as $blockIdx => $block)

            {{-- Satu blok = satu halaman cetak --}}
            <div class="page">

                {{-- Judul --}}
                <div class="doc-title-wrap">
                    @if(!empty($block['title1']))
                        <div class="title-main">{{ $block['title1'] }}</div>
                    @endif
                    @if(!empty($block['title2']))
                        <div class="title-sub">{{ $block['title2'] }}</div>
                    @endif
                    @if(!empty($block['title3']))
                        <div class="title-info">{{ $block['title3'] }}</div>
                    @endif
                </div>

                {{-- Tabel Data --}}
                <table>
                    <thead>
                        <tr>
                            @foreach($block['headers'] as $colIdx => $hdr)
                                @php
                                    $hdrUp   = strtoupper(trim($hdr ?? ''));
                                    $isNo    = in_array($hdrUp, ['NO', 'NO.']);
                                    $isNama  = str_starts_with($hdrUp, 'NAMA');
                                    $isJab   = str_contains($hdrUp, 'JABATAN') || str_contains($hdrUp, 'NAMA OPERATOR');
                                    $isCount = str_contains($hdrUp, 'JUMLAH PERKARA');
                                    $isTtd   = str_contains($hdrUp, 'TANDA TANGAN');
                                    $cls     = $isNo ? 'td-no' : ($isNama ? 'td-nama' : ($isJab ? 'td-jab' : ($isCount ? 'td-count' : ($isTtd ? 'td-ttd' : 'td-num'))));
                                @endphp
                                <th class="{{ $cls }}">{{ $hdr ?? '' }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($block['rows'] as $row)
                            <tr>
                                @foreach($block['headers'] as $colIdx => $hdr)
                                    @php
                                        $val     = $row[$colIdx] ?? '';
                                        $hdrUp   = strtoupper(trim($hdr ?? ''));
                                        $isNo    = in_array($hdrUp, ['NO', 'NO.']);
                                        $isNama  = str_starts_with($hdrUp, 'NAMA');
                                        $isJab   = str_contains($hdrUp, 'JABATAN') || str_contains($hdrUp, 'NAMA OPERATOR');
                                        $isCount = str_contains($hdrUp, 'JUMLAH PERKARA');
                                        $isTtd   = str_contains($hdrUp, 'TANDA TANGAN');
                                        $isNum   = !$isNo && !$isNama && !$isJab && !$isCount && !$isTtd;

                                        // Format rupiah untuk kolom angka (bukan jumlah perkara)
                                        if ($isNum && $val !== '' && $val !== '-') {
                                            $stripped = str_replace([',', '.', ' '], '', preg_replace('/^Rp\s*/i', '', (string)$val));
                                            if (is_numeric($stripped) && (float)$stripped != 0) {
                                                $val = 'Rp ' . number_format((float)$stripped, 0, ',', '.');
                                            } elseif (in_array($val, ['0', 'Rp -', 'Rp 0'])) {
                                                $val = '-';
                                            }
                                        }
                                        $cls = $isNo ? 'td-no' : ($isNama ? 'td-nama' : ($isJab ? 'td-jab' : ($isCount ? 'td-count' : ($isTtd ? 'td-ttd' : 'td-num'))));
                                    @endphp
                                    <td class="{{ $cls }}">{{ $val }}</td>
                                @endforeach
                            </tr>
                        @endforeach

                        {{-- Baris Total --}}
                        @if($block['totalRow'])
                            <tr class="row-total">
                                @foreach($block['headers'] as $colIdx => $hdr)
                                    @php
                                        $val     = $block['totalRow'][$colIdx] ?? '';
                                        $hdrUp   = strtoupper(trim($hdr ?? ''));
                                        $isNo    = in_array($hdrUp, ['NO', 'NO.']);
                                        $isNama  = str_starts_with($hdrUp, 'NAMA');
                                        $isJab   = str_contains($hdrUp, 'JABATAN');
                                        $isCount = str_contains($hdrUp, 'JUMLAH PERKARA');
                                        $isTtd   = str_contains($hdrUp, 'TANDA TANGAN');
                                        $isNum   = !$isNo && !$isNama && !$isJab && !$isCount && !$isTtd;

                                        if ($isNum && $val !== '' && $val !== '-') {
                                            $stripped = str_replace([',', '.', ' '], '', preg_replace('/^Rp\s*/i', '', (string)$val));
                                            if (is_numeric($stripped) && (float)$stripped != 0) {
                                                $val = 'Rp ' . number_format((float)$stripped, 0, ',', '.');
                                            }
                                        }
                                        $cls = $isNo ? 'td-no' : ($isNama ? 'td-nama' : ($isJab ? 'td-jab' : ($isCount ? 'td-count' : ($isTtd ? 'td-ttd' : 'td-num'))));
                                    @endphp
                                    <td class="{{ $cls }}">{{ $val }}</td>
                                @endforeach
                            </tr>
                        @endif
                    </tbody>
                </table>

                {{-- Footer / Tanda Tangan --}}
                @php $fi = $block['footerInfo']; @endphp
                <div class="footer-wrap">
                    @if($fi['date'])
                        <div class="footer-date">{{ $fi['date'] }}</div>
                    @endif
                    <div class="ttd-grid">
                        {{-- Kiri --}}
                        <div class="ttd-item">
                            @if($fi['left'])
                                @foreach(explode("\n", $fi['left']) as $line)
                                    <div class="ttd-label">{{ $line }}</div>
                                @endforeach
                            @else
                                <div class="ttd-label">Petugas Pembuat Komitmen<br>Biaya Proses</div>
                            @endif
                            <div class="ttd-space"></div>
                            <div class="ttd-name">{{ $fi['left_name'] ?? '' }}</div>
                        </div>

                        {{-- Tengah --}}
                        <div class="ttd-item center">
                            @if($fi['center'])
                                @foreach(explode("\n", $fi['center']) as $line)
                                    <div class="ttd-label">{{ $line }}</div>
                                @endforeach
                            @else
                                <div class="ttd-label">Mengetahui,<br>Kuasa Pengelola Biaya Proses</div>
                            @endif
                            <div class="ttd-space"></div>
                            <div class="ttd-name">{{ $fi['center_name'] ?? '' }}</div>
                        </div>

                        {{-- Kanan --}}
                        <div class="ttd-item right">
                            @if($fi['right'])
                                @foreach(explode("\n", $fi['right']) as $line)
                                    <div class="ttd-label">{{ $line }}</div>
                                @endforeach
                            @else
                                <div class="ttd-label">Bendahara Biaya Proses</div>
                            @endif
                            <div class="ttd-space"></div>
                            <div class="ttd-name">{{ $fi['right_name'] ?? '' }}</div>
                        </div>
                    </div>
                </div>

            </div>{{-- /.page --}}

            @endforeach {{-- blocks --}}
        @endforeach {{-- sheets --}}
    @endif

</div>{{-- /.pages --}}

</body>
</html>
