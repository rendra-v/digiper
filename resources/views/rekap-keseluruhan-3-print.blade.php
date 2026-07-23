<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Print – Rekap Keseluruhan 3</title>
    <style>
        @page {
            size: landscape;
            margin: 8mm 10mm;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #111;
            background: #fff;
        }

        /* ── toolbar ── */
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
        @media print { .no-print { display: none !important; } }

        /* ── title ── */
        .doc-title {
            text-align: center;
            margin-bottom: 3px;
        }
        .doc-title .t1 { font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .doc-title .t2 { font-size: 9px; color: #555; }

        /* ── table ── */
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th, td {
            border: 0.8px solid #555;
            padding: 3px 4px;
            font-size: 9px;
            line-height: 1.35;
            overflow: hidden;
        }
        .hdr  { background: #d9d9d9; font-weight: 700; text-align: center; }
        .hdr2 { background: #e9e9e9; font-weight: 700; text-align: center; }
        .tot  { background: #d9d9d9; font-weight: 700; }
        .c  { text-align: center; }
        .l  { text-align: left; }
        .r  { text-align: right; }
        .b  { font-weight: 700; }
        .g  { background: #e8f5e9; }
        .rd { background: #fce4e4; }
        .or { background: #fff3e0; }
        .em { background: #e8f5e9; font-weight: 700; }

        .notice { margin: 30px auto; max-width: 400px; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 12px; }
        .period { text-align: right; font-size: 9px; font-weight: 700; margin-top: 5px; }
    </style>
</head>
<body>

    {{-- Toolbar --}}
    <div class="no-print">
        <span style="font-weight:600; color:#1f2937;">🖨️ Print – Rekap Keseluruhan 3 (Distribusi Honor Personil)</span>
        <div style="display:flex; gap:8px;">
            <button onclick="window.print()"
                style="padding:6px 18px; background:#4f46e5; color:#fff; border:none; border-radius:6px; font-weight:600; cursor:pointer;">
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
            // Split kolom jadi 2 halaman
            $half = (int) ceil(count($columns) / 2);
            $colChunks = array_chunk((array) $columns, $half);

        @endphp


            @foreach($colChunks as $chunkIdx => $colChunk)
            <div style="{{ $chunkIdx > 0 ? 'page-break-before: always;' : '' }}">

            <div class="doc-title">
                <div class="t1">REKAPITULASI DISTRIBUSI HONOR/INSENTIF PERSONIL PENYELESAIAN PERKARA</div>
                @if($recapDate)
                <div class="t2">{{ strtoupper($recapDate) }} &mdash; Halaman {{ $chunkIdx + 1 }} dari {{ count($colChunks) }}</div>
                @endif
            </div>

            <table>
            <colgroup>
                <col style="width:1.5%">   {{-- NO --}}
                <col style="width:10%">    {{-- JABATAN --}}
                <col style="width:1.5%">   {{-- % --}}
                @foreach($colChunk as $col)
                <col style="width:4%">     {{-- BIAYA --}}
                <col style="width:2.5%">   {{-- JML --}}
                <col style="width:4.5%">   {{-- SUB TOTAL --}}
                @endforeach
                <col style="width:5%">     {{-- BRUTO --}}
                <col style="width:4%">     {{-- PPh 15% --}}
                <col style="width:4%">     {{-- PPh 5% --}}
                <col style="width:5.5%">   {{-- NETTO --}}
            </colgroup>

            <thead>
                {{-- row 1 --}}
                <tr class="hdr">
                    <th rowspan="2" class="c">NO</th>
                    <th rowspan="2" class="c">JABATAN</th>
                    <th rowspan="2" class="c">%</th>
                    @foreach($colChunk as $col)
                    <th colspan="3" class="c">{{ $col['label'] }}</th>
                    @endforeach
                    <th rowspan="2" class="c g">BRUTO</th>
                    <th rowspan="2" class="c rd">PPh 15%</th>
                    <th rowspan="2" class="c or">PPh 5%</th>
                    <th rowspan="2" class="c em">NETTO</th>
                </tr>
                {{-- row 2 --}}
                <tr class="hdr2">
                    @foreach($colChunk as $col)
                    <th class="c">BIAYA</th>
                    <th class="c">JML</th>
                    <th class="c">SUB TOTAL</th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
            @foreach($rows as $i => $row)
            <tr>
                <td class="c">{{ $i + 1 }}</td>
                <td class="l">{{ $row['label'] }}</td>
                <td class="c">{{ number_format($row['persen'] * 100, 1, ',', '.') }}%</td>
                @foreach($colChunk as $col)
                    @php $cell = $row['cells'][$col['key']] ?? ['biaya'=>0,'jml'=>0,'sub_total'=>0] @endphp
                    <td class="r">{{ $cell['biaya'] > 0 ? number_format($cell['biaya'], 0, ',', '.') : '-' }}</td>
                    <td class="r">{{ $cell['jml'] > 0 ? number_format($cell['jml'], 0, ',', '.') : '-' }}</td>
                    <td class="r">{{ $cell['sub_total'] > 0 ? number_format($cell['sub_total'], 0, ',', '.') : '-' }}</td>
                @endforeach
                <td class="r b g">{{ $row['bruto'] > 0 ? number_format($row['bruto'], 0, ',', '.') : '-' }}</td>
                <td class="r rd">{{ $row['pph15'] > 0 ? number_format($row['pph15'], 0, ',', '.') : '-' }}</td>
                <td class="r or">{{ $row['pph5'] > 0 ? number_format($row['pph5'], 0, ',', '.') : '-' }}</td>
                <td class="r b em">{{ $row['netto'] > 0 ? number_format($row['netto'], 0, ',', '.') : '-' }}</td>
            </tr>
            @endforeach

            {{-- TOTAL --}}
            <tr class="tot">
                <td colspan="3" class="c b">TOTAL</td>
                @foreach($colChunk as $col)
                <td class="c" style="color:#aaa">-</td>
                <td class="c" style="color:#aaa">-</td>
                <td class="r b">
                    {{ isset($col_grand_total[$col['key']]) && $col_grand_total[$col['key']] > 0
                       ? number_format($col_grand_total[$col['key']], 0, ',', '.') : '-' }}
                </td>
                @endforeach
                <td class="r b g">{{ $grand_bruto > 0 ? number_format($grand_bruto, 0, ',', '.') : '-' }}</td>
                <td class="r b rd">{{ $grand_pph15 > 0 ? number_format($grand_pph15, 0, ',', '.') : '-' }}</td>
                <td class="r b or">{{ $grand_pph5 > 0 ? number_format($grand_pph5, 0, ',', '.') : '-' }}</td>
                <td class="r b em">{{ $grand_netto > 0 ? number_format($grand_netto, 0, ',', '.') : '-' }}</td>
            </tr>
            </tbody>
            </table>

            </div>{{-- end chunk div --}}
            @endforeach {{-- colChunks --}}

        @if($recapDate)
        <div class="period">{{ $recapDate }}</div>
        @endif

        <script>
            window.addEventListener('load', function () {
                setTimeout(function () { window.print(); }, 400);
            });
        </script>

    @endif
</body>
</html>
