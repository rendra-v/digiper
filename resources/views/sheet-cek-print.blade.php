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
        .action-bar .hint { color: rgba(255,255,255,.6); font-family: Arial, sans-serif; font-size: 8.5pt; }
        .action-bar .btn-group { display: flex; gap: 10px; align-items: center; }

        .btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 18px; border-radius: 7px;
            font-family: Arial, sans-serif; font-size: 9pt; font-weight: 600;
            cursor: pointer; border: none; transition: filter .15s; text-decoration: none;
        }
        .btn:hover { filter: brightness(1.12); }
        .btn-print { background: #f4a418; color: #1a1a2e; }
        .btn-back  { background: rgba(255,255,255,.18); color: #fff; border: 1px solid rgba(255,255,255,.35); }

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
            width: 297mm; /* landscape A4 width for screen preview */
            background: #fff;
            box-shadow: 0 4px 32px rgba(0,0,0,.18);
            border-radius: 3px;
            padding: 16mm 14mm 12mm;
        }

        /* ── Kop Surat ──────────────────────────────────────────────────── */
        .kop {
            display: flex;
            align-items: center;
            gap: 14px;
            border-bottom: 3px double var(--accent);
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .kop-logo {
            width: 58px; height: 58px; flex-shrink: 0;
        }
        .kop-text { flex: 1; text-align: center; }
        .kop-text .instansi  { font-size: 9.5pt; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--accent); }
        .kop-text .sub       { font-size: 8.5pt; color: #4a4a6a; }

        /* ── Judul ─────────────────────────────────────────────────────── */
        .doc-title {
            background: linear-gradient(90deg, var(--accent) 0%, var(--accent2) 100%);
            color: #fff;
            text-align: center;
            padding: 8px 12px;
            margin-bottom: 12px;
            border-radius: 2px;
        }
        .doc-title .t1 { font-size: 9.5pt; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; }
        .doc-title .t2 { font-size: 8.5pt; color: rgba(255,255,255,.85); margin-top: 2px; }
        .doc-title .t3 { font-size: 8pt; color: var(--gold); margin-top: 2px; font-weight: 600; }

        /* ── Tabel ─────────────────────────────────────────────────────── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            table-layout: auto;
        }
        thead tr { background: var(--accent); color: #fff; }
        thead th {
            border: 1px solid var(--accent);
            padding: 4px 5px;
            font-weight: 700;
            text-align: center;
            white-space: normal;
            word-break: break-word;
        }
        tbody tr:nth-child(even) { background: var(--bg-alt); }
        tbody td {
            border: 1px solid var(--border);
            padding: 3px 5px;
            vertical-align: middle;
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
        .footer-date { text-align: right; font-size: 8.5pt; margin-bottom: 12px; color: #4a4a6a; }
        .ttd-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 16px;
            font-size: 8pt;
        }
        .ttd-label { font-weight: 700; text-transform: uppercase; color: var(--accent); line-height: 1.4; }
        .ttd-space { height: 48px; border-bottom: 1px dashed var(--border); margin: 8px 0 4px; }
        .ttd-name  { color: #4a4a6a; font-style: italic; }
        .ttd-center { text-align: center; }
        .ttd-right  { text-align: right; }

        /* ── PRINT ──────────────────────────────────────────────────────── */
        @page {
            size: A4 landscape;
            margin: 10mm 12mm 10mm;
        }
        @media print {
            html, body { background: #fff; font-size: 8pt; }
            .action-bar { display: none !important; }
            .pages { margin-top: 0; padding: 0; background: none; }
            .page {
                width: 100%;
                box-shadow: none;
                border-radius: 0;
                padding: 0;
            }
            table { font-size: 7.5pt; }
            thead th, tbody td { padding: 2px 4px; }
            .td-right { white-space: nowrap; }
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
    <span class="hint">A4 Landscape · Gunakan "Save as PDF" di dialog cetak browser</span>
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

@elseif(empty($data))
    <div style="margin:40px auto;max-width:480px;background:#fff8e6;border:1px solid #f0c040;border-radius:8px;padding:24px;text-align:center;font-family:Arial,sans-serif;">
        <h3 style="color:#b45309;margin-bottom:8px;">Tidak ada data</h3>
        <p style="color:#78350f;font-size:9pt;">Sheet "cek" tidak ditemukan atau kosong.</p>
    </div>

@else
<div class="page">

    {{-- Kop Surat --}}
    <div class="kop">
        <svg class="kop-logo" viewBox="0 0 80 80" xmlns="http://www.w3.org/2000/svg">
            <circle cx="40" cy="40" r="38" fill="#1e3a5f" stroke="#c8a84b" stroke-width="2.5"/>
            <text x="40" y="46" text-anchor="middle" font-size="22" font-family="serif" fill="#c8a84b" font-weight="bold">⚖</text>
        </svg>
        <div class="kop-text">
            <div class="instansi">Mahkamah Agung Republik Indonesia</div>
            <div class="sub">Pengadilan Tinggi / Pengadilan Negeri</div>
            @if(Session::has('excel_period') && Session::get('excel_period') !== '')
                <div class="sub" style="color:#1e3a5f;font-weight:600;">Periode: {{ strtoupper(Session::get('excel_period')) }}</div>
            @elseif(Session::has('excel_file_name'))
                <div class="sub">{{ Session::get('excel_file_name') }}</div>
            @endif
        </div>
    </div>

    {{-- Judul --}}
    <div class="doc-title">
        <div class="t1">Rekap Total Penyerahan ke Masing-Masing Panmud</div>
        <div class="t2">Honorarium Biaya Penyelesaian Perkara</div>
    </div>

    {{-- Tabel --}}
    <table>
        <thead>
            <tr>
                @foreach($headers as $colLetter => $headerName)
                    <th>{{ $headerName }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php
                $numericHeaders = ['JUMLAH', 'BIAYA', 'TIM', '5 MAJELIS', 'KEPANITERAAN', 'PEMILAH', 'Total',
                                   'PAJAK', 'TOTAL', 'TOTAL_1', 'TOTAL_2', 'TOTAL_3',
                                   'Penyerahan', 'Honorarium', 'Biaya', 'Bersih'];
            @endphp
            @foreach($data as $row)
                <tr>
                    @foreach($headers as $colLetter => $headerName)
                        @php
                            $key      = $colToKey[$colLetter] ?? $headerName;
                            $value    = $row[$key] ?? null;
                            $rowspan  = $row['_rowspans'][$key] ?? 1;
                            $isNumeric = in_array($headerName, $numericHeaders) || in_array($key, $numericHeaders);
                        @endphp

                        @if($value === 'SKIP_OR_NULL')
                            @continue
                        @endif

                        <td class="{{ $isNumeric ? 'td-right' : (in_array($headerName, ['NO', 'NO.']) ? 'td-center' : 'td-left') }}"
                            @if($rowspan > 1) rowspan="{{ $rowspan }}" @endif>
                            @if(is_numeric($value))
                                @if((float)$value === 0.0)
                                    -
                                @else
                                    {{ number_format((float)$value, 0, ',', '.') }}
                                @endif
                            @else
                                {{ $value ?? '-' }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Footer / Tanda Tangan --}}
    @if(isset($footer) && count($footer) > 0)
        @php
            $fullText = '';
            foreach($footer as $fRow) {
                foreach($fRow as $k => $v) {
                    if($k !== '_rowspans' && $k !== '_original_row' && $v && $v !== 'SKIP_OR_NULL') {
                        $fullText .= ' ' . $v;
                    }
                }
            }
            $date = '';
            if (preg_match('/(Jakarta,\s*\d{1,2}\s+[A-Z][a-z]+\s+\d{4})/', $fullText, $m)) $date = $m[1];
            $bendaharaName = preg_match('/FARIDA,\s*SH/', $fullText) ? 'FARIDA, S.H.' : 'FARIDA, S.H.';
            $mengetahuiName = preg_match('/ASEP NURSOBAH/', $fullText) ? 'ASEP NURSOBAH, S.Ag., M.H.' : 'ASEP NURSOBAH, S.Ag., M.H.';
            $ppkName = preg_match('/KRIS NUGROHO/', $fullText) ? 'ST. KRIS NUGROHO, S.H., M.H.' : 'ST. KRIS NUGROHO, S.H., M.H.';
        @endphp
        <div class="footer-wrap">
            @if($date)
                <div class="footer-date">{{ $date }}</div>
            @endif
            <div class="ttd-grid">
                <div>
                    <div class="ttd-label">Bendahara Biaya Proses</div>
                    <div class="ttd-space"></div>
                    <div class="ttd-name">{{ $bendaharaName }}</div>
                </div>
                <div class="ttd-center">
                    <div class="ttd-label">Mengetahui,<br>Kuasa Pengelola Biaya Proses</div>
                    <div class="ttd-space"></div>
                    <div class="ttd-name">{{ $mengetahuiName }}</div>
                </div>
                <div class="ttd-right">
                    <div class="ttd-label">Petugas Pembuat Komitmen<br>Biaya Proses</div>
                    <div class="ttd-space"></div>
                    <div class="ttd-name">{{ $ppkName }}</div>
                </div>
            </div>
        </div>
    @endif

</div>{{-- /.page --}}
@endif

</div>{{-- /.pages --}}
</body>
</html>
