<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Honorarium — {{ $fileName ?? 'Dokumen' }}</title>
    <style>
        /* ── Reset & Base ─────────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --ink:        #1a1a2e;
            --ink-light:  #4a4a6a;
            --accent:     #1e3a5f;
            --accent-mid: #2e5999;
            --border:     #b0bdd6;
            --bg-head:    #1e3a5f;
            --bg-alt:     #f4f6fb;
            --gold:       #c8a84b;
            --page-w:     297mm;  /* landscape A4 width */
        }

        html, body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            color: var(--ink);
            background: #e8ecf3;
            line-height: 1.45;
            overflow-x: auto;
        }

        /* ── Screen: action bar ───────────────────────────────────────── */
        .action-bar {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 999;
            background: linear-gradient(135deg, #1e3a5f 0%, #2e5999 100%);
            box-shadow: 0 2px 12px rgba(0,0,0,.35);
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .action-bar .brand {
            color: #fff;
            font-size: 13pt;
            font-weight: 700;
            letter-spacing: .03em;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .action-bar .brand svg { flex-shrink: 0; }
        .action-bar .hint {
            color: rgba(255,255,255,.65);
            font-family: Arial, sans-serif;
            font-size: 8.5pt;
        }
        .action-bar .btn-group { display: flex; gap: 10px; align-items: center; }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border-radius: 7px;
            font-family: Arial, sans-serif;
            font-size: 9pt;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: filter .15s;
            text-decoration: none;
        }
        .btn:hover { filter: brightness(1.12); }
        .btn-print {
            background: #f4a418;
            color: #1a1a2e;
        }
        .btn-back {
            background: rgba(255,255,255,.18);
            color: #fff;
            border: 1px solid rgba(255,255,255,.35);
        }

        /* ── Screen: page wrapper ─────────────────────────────────────── */
        .pages {
            margin-top: 72px; /* below fixed bar */
            padding: 24px 16px 48px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 28px;
        }

        /* ── Paper page ───────────────────────────────────────────────── */
        .page {
            width: var(--page-w);
            min-height: 210mm;  /* landscape A4 height */
            background: #fff;
            box-shadow: 0 4px 32px rgba(0,0,0,.18), 0 1px 6px rgba(0,0,0,.08);
            border-radius: 3px;
            padding: 14mm 16mm 12mm;
            position: relative;
            overflow: hidden;
        }

        /* ── Kop Surat (header) ───────────────────────────────────────── */
        .kop {
            display: flex;
            align-items: center;
            gap: 14px;
            border-bottom: 3px double var(--accent);
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .kop-logo {
            width: 62px; height: 62px;
            flex-shrink: 0;
        }
        .kop-text { flex: 1; text-align: center; }
        .kop-text .instansi {
            font-size: 9.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: var(--accent);
        }
        .kop-text .sub-instansi {
            font-size: 8.5pt;
            color: var(--ink-light);
        }
        .kop-text .kota {
            font-size: 8pt;
            color: var(--ink-light);
        }

        /* ── Judul dokumen ────────────────────────────────────────────── */
        .doc-title-wrap {
            background: linear-gradient(90deg, var(--bg-head) 0%, #2e5999 100%);
            color: #fff;
            text-align: center;
            padding: 9px 12px;
            margin: 0 0 12px;   /* sejajar dengan tabel — tanpa negative margin */
            border-radius: 2px;
        }
        .doc-title-wrap .t1 {
            font-size: 9.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
        }
        .doc-title-wrap .t2 {
            font-size: 8.5pt;
            font-weight: 600;
            color: rgba(255,255,255,.85);
            margin-top: 2px;
        }
        .doc-title-wrap .t3 {
            font-size: 8pt;
            color: var(--gold);
            margin-top: 3px;
            font-weight: 600;
        }

        /* ── Tabel ────────────────────────────────────────────────────── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            table-layout: auto;
        }
        thead tr {
            background: var(--accent);
            color: #fff;
        }
        thead th {
            border: 1px solid var(--accent);
            padding: 6px 8px;
            font-weight: 700;
            text-align: center;
            white-space: nowrap;
        }
        tbody tr:nth-child(even) { background: var(--bg-alt); }
        tbody tr:hover { background: #e3eaf7; }
        tbody td {
            border: 1px solid var(--border);
            padding: 5px 8px;
            vertical-align: middle;
        }
        .td-no    { text-align: center; width: 28px;  white-space: nowrap; }
        .td-nama  { text-align: left;   min-width: 130px; }
        .td-jab   { text-align: left;   min-width: 150px; }
        .td-num   { text-align: right;  white-space: nowrap; min-width: 72px; }
        .td-count { text-align: center; white-space: nowrap; min-width: 72px; }

        tr.row-total {
            background: #d0daea !important;
            font-weight: 700;
            border-top: 2px solid var(--accent);
        }
        tr.row-total td { border-color: var(--accent-mid); }

        /* ── Footer / Tanda Tangan ────────────────────────────────────── */
        .footer-wrap {
            margin-top: 14px;
            padding-top: 10px;
            border-top: 1px solid var(--border);
        }
        .footer-date {
            text-align: right;
            font-size: 8.5pt;
            margin-bottom: 10px;
            color: var(--ink-light);
        }
        .ttd-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 12px;
            font-size: 8pt;
        }
        .ttd-item { }
        .ttd-item.center { text-align: center; }
        .ttd-item.right  { text-align: right;  }
        .ttd-label {
            font-weight: 700;
            text-transform: uppercase;
            color: var(--accent);
            line-height: 1.4;
        }
        .ttd-space {
            height: 48px;
            border-bottom: 1px dashed var(--border);
            margin: 8px 0 4px;
        }
        .ttd-name { color: var(--ink-light); font-style: italic; }

        /* ── Page break separator ─────────────────────────────────────── */
        .page-label {
            font-family: Arial, sans-serif;
            font-size: 7.5pt;
            color: #999;
            text-align: center;
            letter-spacing: .08em;
        }

        /* ── Error / empty state ──────────────────────────────────────── */
        .notice {
            margin: 40px auto;
            max-width: 520px;
            background: #fff8e6;
            border: 1px solid #f0c040;
            border-radius: 8px;
            padding: 24px;
            text-align: center;
            font-family: Arial, sans-serif;
        }
        .notice h3 { color: #b45309; margin-bottom: 8px; }
        .notice p  { color: #78350f; font-size: 9pt; }

        /* ══════════════════════════════════════════════════════════════
           PRINT STYLES — override everything for clean paper output
        ══════════════════════════════════════════════════════════════ */
        @page {
            size: A4 landscape;
            margin: 10mm 12mm 10mm;
        }
        @media print {
            html, body { background: #fff; font-size: 8pt; }
            .action-bar { display: none !important; }
            .kop         { display: none !important; }  /* Sembunyikan kop surat di PDF */
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

            table {
                font-size: 8pt;
                width: 100% !important;
                table-layout: auto;
            }
            thead th {
                padding: 5px 8px;
                white-space: nowrap;
            }
            tbody td {
                padding: 4px 8px;
            }
            .td-nama, .td-jab {
                white-space: normal;
                word-break: break-word;
            }
            .td-num {
                white-space: nowrap;
                text-align: right;
            }
            thead { display: table-header-group; }
            tbody tr:hover { background: inherit; }
            .page-label { display: none; }
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
            <polyline points="10 9 9 9 8 9"/>
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

    @elseif(empty($sheets))
        <div class="notice">
            <h3>Tidak ada data</h3>
            <p>Tidak ditemukan data honorarium yang bisa dicetak. Pastikan file Excel sudah diupload dan memiliki data honorarium.</p>
        </div>

    @else
        @foreach($sheets as $sheetIdx => $sheet)
            @foreach($sheet['blocks'] as $blockIdx => $block)

            {{-- Satu blok = satu halaman --}}
            <div class="page">



                {{-- Judul Dokumen --}}
                <div class="doc-title-wrap">
                    <div class="t1">{{ $block['title1'] ?? 'HONORARIUM BIAYA PENYELESAIAN PERKARA' }}</div>
                    @if(!empty($block['title2']))
                        <div class="t2">{{ $block['title2'] }}</div>
                    @endif
                    @if(!empty($block['title3']))
                        <div class="t3">{{ $block['title3'] }}</div>
                    @endif
                </div>

                {{-- Tabel Data --}}
                <table>
                    <thead>
                        <tr>
                            @foreach($block['headers'] as $colIdx => $hdr)
                                @php
                                    $hdrUp  = strtoupper(trim($hdr ?? ''));
                                    $isNo   = in_array($hdrUp, ['NO', 'NO.']);
                                    $isNama = str_starts_with($hdrUp, 'NAMA');
                                    $isJab  = str_contains($hdrUp, 'JABATAN') || str_contains($hdrUp, 'NAMA OPERATOR');
                                    $isCount = str_contains($hdrUp, 'JUMLAH PERKARA');
                                    $isNum  = !$isNo && !$isNama && !$isJab && !$isCount;
                                    $cls    = $isNo ? 'td-no' : ($isNama ? 'td-nama' : ($isJab ? 'td-jab' : ($isCount ? 'td-count' : 'td-num')));
                                @endphp
                                <th class="{{ $cls }}">{{ $hdr ?? '' }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php $rowNum = 0; @endphp
                        @foreach($block['rows'] as $row)
                            @php $rowNum++; @endphp
                            <tr>
                                @foreach($block['headers'] as $colIdx => $hdr)
                                    @php
                                        $val    = $row[$colIdx] ?? '';
                                        $hdrUp  = strtoupper(trim($hdr ?? ''));
                                        $isNo   = in_array($hdrUp, ['NO', 'NO.']);
                                        $isNama = str_starts_with($hdrUp, 'NAMA');
                                        $isJab  = str_contains($hdrUp, 'JABATAN') || str_contains($hdrUp, 'NAMA OPERATOR');
                                        $isNum  = !$isNo && !$isNama && !$isJab;

                                        $isCount = str_contains($hdrUp, 'JUMLAH PERKARA');
                                        if ($isNum && !$isCount && $val !== '' && $val !== '-') {
                                            $stripped = str_replace([',', '.', ' '], '', preg_replace('/^Rp\s*/i', '', $val));
                                            if (is_numeric($stripped) && (float)$stripped != 0) {
                                                $val = 'Rp ' . number_format((float)$stripped, 0, ',', '.');
                                            } elseif (in_array($val, ['0', 'Rp -', 'Rp 0'])) {
                                                $val = 'Rp -';
                                            }
                                        }
                                        $cls = $isNo ? 'td-no' : ($isNama ? 'td-nama' : ($isJab ? 'td-jab' : ($isCount ? 'td-count' : 'td-num')));
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
                                        $val    = $block['totalRow'][$colIdx] ?? '';
                                        $hdrUp  = strtoupper(trim($hdr ?? ''));
                                        $isNo   = in_array($hdrUp, ['NO', 'NO.']);
                                        $isNama = str_starts_with($hdrUp, 'NAMA');
                                        $isJab  = str_contains($hdrUp, 'JABATAN');
                                        $isNum  = !$isNo && !$isNama && !$isJab;

                                        $isCount = str_contains($hdrUp, 'JUMLAH PERKARA');
                                        if ($isNum && !$isCount && $val !== '' && $val !== '-') {
                                            $stripped = str_replace([',', '.', ' '], '', preg_replace('/^Rp\s*/i', '', $val));
                                            if (is_numeric($stripped) && (float)$stripped != 0) {
                                                $val = 'Rp ' . number_format((float)$stripped, 0, ',', '.');
                                            }
                                        }
                                        $cls = $isNo ? 'td-no' : ($isNama ? 'td-nama' : ($isJab ? 'td-jab' : ($isCount ? 'td-count' : 'td-num')));
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
