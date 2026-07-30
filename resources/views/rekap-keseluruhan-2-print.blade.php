<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Print – Rekap Keseluruhan 2</title>
    <style>
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
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #111;
            background: #fff;
        }

        /* ── toolbar (screen only) ── */
        .no-print {
            font-family: Arial, sans-serif;
            font-size: 13px;
            background: #f3f4f6;
            border-bottom: 1px solid #d1d5db;
            padding: 8px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 20mm 12mm 10mm; }
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
            tr { break-inside: avoid; page-break-inside: avoid; }
            .tot { break-inside: avoid; page-break-inside: avoid; }
        }

        /* ── title ── */
        .doc-title {
            text-align: center;
            margin-bottom: 6px;
            margin-top: 0;
        }
        .doc-title .t1 { font-size: 14px; font-weight: 700; text-transform: uppercase; }
        .doc-title .t2 { font-size: 14px; font-weight: 700; text-transform: uppercase; }

        /* ── table ── */
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th, td {
            border: 0.8px solid #555;
            padding: 5px 7px;
            font-size: 10px;
            line-height: 1.4;
            overflow: hidden;
        }
        .hdr  { background: #d9d9d9; font-weight: 700; text-align: center; }
        .hdr2 { background: #e9e9e9; font-weight: 700; text-align: center; }
        .sec  { background: #f0f0f0; font-weight: 700; }
        .tot  { background: #d9d9d9; font-weight: 700; }
        .c    { text-align: center; }
        .l    { text-align: left; }
        .r    { text-align: right; }
        .b    { font-weight: 700; }
        .muted { color: #aaa; text-align: center; }
        .nowrap { white-space: nowrap; text-align: center; }

        /* notice */
        .notice {
            margin: 30px auto;
            max-width: 400px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 12px;
        }

        /* period */
        .period {
            text-align: right;
            font-size: 9px;
            font-weight: 700;
            margin-top: 5px;
        }

        /* signature */
        .sig-wrap   { break-inside: avoid; page-break-inside: avoid; margin-top: 15px; }
        .sig-date   { text-align: right; font-size: 10px; font-weight: 700; margin-bottom: 8px; }
        .sig-row    { display: flex; justify-content: space-between; gap: 16px; margin-bottom: 12px; }
        .sig-col    { flex: 1; text-align: center; }
        .sig-label  { font-weight: 700; text-transform: uppercase; font-size: 10px; line-height: 1.35; }
        .sig-space  { height: 2cm; }
        .sig-name   { font-weight: 700; font-size: 11px; }
    </style>
</head>
<body>

    {{-- Toolbar (screen only) --}}
    <div class="no-print">
        <span style="font-weight:600; color:#1f2937;">🖨️ Print – Rekap Keseluruhan 2 (Distribusi Biaya)</span>
        <span style="font-size:11px;color:#6b7280;">📄 Ukuran kertas: <b>F4 Landscape</b></span>
        <div style="display:flex; gap:8px;">
            <button onclick="window.print()"
                style="padding:6px 18px; background:#2563eb; color:#fff; border:none; border-radius:6px; font-weight:600; cursor:pointer;">
                Print / Save PDF
            </button>
            <button onclick="window.close()"
                style="padding:6px 14px; background:#e5e7eb; color:#374151; border:none; border-radius:6px; cursor:pointer;">
                Tutup
            </button>
        </div>
    </div>

    @if($error)
        <div class="notice">{{ $error }}</div>
    @elseif(empty($columns))
        <div class="notice">Belum ada data. Silakan upload file Excel terlebih dahulu.</div>
    @else


        @php
            $colW_no    = '3%';
            $colW_label = '11%';
            $colW_pct   = '4%';
            $colW_biaya = '5.5%';
            $colW_jml   = '4%';
            $colW_sub   = '6%';
            $colW_total = '7%';

            // Split kolom jadi 2 halaman
            $half = (int) ceil(count($columns) / 2);
            $colChunks = array_chunk((array) $columns, $half);
        @endphp

        @foreach($colChunks as $chunkIdx => $colChunk)
        {{-- Halaman {{ $chunkIdx + 1 }} dari {{ count($colChunks) }} --}}
        <div style="{{ $chunkIdx > 0 ? 'page-break-before: always;' : '' }}">

        <div class="doc-title">
            <div class="t1">REKAPITULASI BIAYA PENYELESAIAN PERKARA YANG DIPUTUS YANG USIANYA KURANG DARI 120 HARI SEJAK REGISTER PERKARA MASUK</div>

        </div>

        <table>
            {{-- Colgroup --}}
            <colgroup>
                <col style="width:{{ $colW_no }}">
                <col style="width:{{ $colW_label }}">
                <col style="width:{{ $colW_pct }}">
                @foreach($colChunk as $col)
                <col style="width:{{ $colW_biaya }}">
                <col style="width:{{ $colW_jml }}">
                <col style="width:{{ $colW_sub }}">
                @endforeach
                <col style="width:{{ $colW_total }}">
            </colgroup>

            {{-- Header --}}

            <thead>
                <tr class="hdr">
                    <th rowspan="2" class="c">NO</th>
                    <th rowspan="2" class="c">PERUNTUKAN</th>
                    <th rowspan="2" class="c">%</th>
                    @foreach($colChunk as $col)
                    <th colspan="3" class="c">{{ $col['label'] }}<br>({{ $col['rate_label'] }})</th>
                    @endforeach
                    <th rowspan="2" class="c">TOTAL</th>
                </tr>
                <tr class="hdr2">
                    @foreach($colChunk as $col)
                    <th class="c">BIAYA</th>
                    <th class="c">JML</th>
                    <th class="c">SUB TOTAL</th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
            @foreach($rows as $row)
                @php
                    $isHeader  = $row['type'] === 'header';
                    $isJmlOnly = $row['type'] === 'jml_only';
                    $colCount  = count($colChunk);
                    // Hitung row total hanya dari kolom di chunk ini
                    $chunkRowTotal = 0;
                    if (!$isHeader) {
                        foreach ($colChunk as $col) {
                            $cell = $cells[$row['key']][$col['key']] ?? null;
                            $chunkRowTotal += $cell ? ($cell['sub_total'] ?? 0) : 0;
                        }
                    }
                @endphp

                @if($isHeader)
                <tr class="sec">
                    <td class="c">{{ $row['no'] }}</td>
                    <td colspan="{{ $colCount * 3 + 2 }}" class="l b">{{ $row['label'] }}</td>
                </tr>

                @elseif($isJmlOnly)
                <tr>
                    <td class="c">{{ $row['no'] }}</td>
                    <td class="l">{{ $row['label'] }}</td>
                    <td class="c nowrap">{{ $row['persen'] }}</td>
                    @foreach($colChunk as $col)
                        @php $cell = $cells[$row['key']][$col['key']] ?? null @endphp
                        <td class="muted">-</td>
                        <td class="c">{{ $cell ? number_format($cell['jml'], 0, ',', '.') : '-' }}</td>
                        <td class="muted">-</td>
                    @endforeach
                    <td class="muted">-</td>
                </tr>

                @else
                <tr>
                    <td class="c">{{ $row['no'] }}</td>
                    <td class="l">{{ $row['label'] }}</td>
                    <td class="c nowrap">{{ $row['persen'] }}</td>
                    @foreach($colChunk as $col)
                        @php $cell = $cells[$row['key']][$col['key']] ?? null @endphp
                        <td class="c">{{ ($cell && $cell['biaya'] > 0) ? number_format($cell['biaya'], 0, ',', '.') : '-' }}</td>
                        <td class="c">{{ $cell ? number_format($cell['jml'], 0, ',', '.') : '-' }}</td>
                        <td class="c">{{ ($cell && $cell['sub_total'] > 0) ? number_format($cell['sub_total'], 0, ',', '.') : '-' }}</td>
                    @endforeach
                    <td class="c b">{{ $chunkRowTotal > 0 ? number_format($chunkRowTotal, 0, ',', '.') : '-' }}</td>
                </tr>
                @endif
            @endforeach

            {{-- Total row --}}
            <tr class="tot">
                <td colspan="2" class="c b"></td>
                <td class="c nowrap b">100%</td>
                @foreach($colChunk as $col)
                <td class="c b">{{ number_format($col['base_rate'], 0, ',', '.') }}</td>
                <td class="muted">-</td>
                <td class="muted">-</td>
                @endforeach
                @php
                    $chunkGrandTotal = array_sum(array_map(fn($c) => $row_totals[$c['key']] ?? 0, $colChunk));
                @endphp
                <td class="c b">{{ $chunkGrandTotal > 0 ? number_format($chunkGrandTotal, 0, ',', '.') : '-' }}</td>
            </tr>
            </tbody>
        </table>

        </div>{{-- end chunk div --}}
        @endforeach {{-- colChunks --}}

        {{-- ══ Signature Area ══ --}}
        <div class="sig-wrap">
            @php
                $months = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ];
                $dateNow = \Illuminate\Support\Facades\Session::get('excel_tgl_rekap_keseluruhan')
                    ?: ('Jakarta, ' . date('d') . ' ' . $months[(int)date('m')] . ' ' . date('Y'));
                $pejabat = config('tarif.pejabat');
            @endphp
            <div class="sig-date">{{ $dateNow }}</div>
            <div class="sig-row">
                <div class="sig-col">
                    <div class="sig-label">Petugas Pembuat Komitmen<br>Biaya Proses</div>
                    <div class="sig-space"></div>
                    <div class="sig-name" data-ttd-key="ttd_petugas">{{ $pejabat['ppk'] ?? $pejabat['petugas_pembuat'] ?? 'ST. KRIS NUGROHO, S.H., M.H.' }}</div>
                </div>

                <div class="sig-col">
                    <div class="sig-label">Mengetahui,<br>Kuasa Pengelola Biaya Proses</div>
                    <div class="sig-space"></div>
                    <div class="sig-name" data-ttd-key="ttd_kuasa">{{ $pejabat['kuasa_pengelola'] ?? 'ASEP NURSOBAH, S.Ag., M.H.' }}</div>
                </div>

                <div class="sig-col">
                    <div class="sig-label">Bendahara Biaya Proses</div>
                    <div class="sig-space"></div>
                    <div class="sig-name" data-ttd-key="ttd_bendahara">{{ $pejabat['bendahara'] ?? 'FARIDA, S.H.' }}</div>
                </div>
            </div>
        </div>

        <script>
            window.addEventListener('load', function () {
                document.querySelectorAll('[data-ttd-key]').forEach(function(el) {
                    var stored = localStorage.getItem(el.getAttribute('data-ttd-key'));
                    if (stored !== null && stored !== '') el.textContent = stored;
                });
                setTimeout(function () { window.print(); }, 400);
            });
        </script>
</body>
</html>
