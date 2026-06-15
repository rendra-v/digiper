<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Honorarium - Digiper</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            background: white;
            color: #000;
        }

        /* ── Print toolbar (tidak dicetak) ── */
        .print-toolbar {
            background: #1d4ed8;
            color: white;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: sans-serif;
            font-size: 13px;
        }
        .print-toolbar span { flex: 1; font-weight: 600; }
        .btn-print {
            background: white; color: #1d4ed8;
            border: none; padding: 6px 18px;
            border-radius: 6px; font-weight: 700;
            cursor: pointer; font-size: 13px;
        }
        .btn-close {
            background: transparent; color: white;
            border: 1px solid rgba(255,255,255,0.5);
            padding: 6px 14px; border-radius: 6px;
            cursor: pointer; font-size: 13px;
        }

        /* ── Halaman cetak ── */
        .page {
            padding: 14mm 12mm 14mm 18mm;
        }
        .page-break { page-break-after: always; }

        /* ── Judul dokumen ── */
        .doc-title {
            text-align: center;
            margin-bottom: 10px;
        }
        .doc-title p {
            font-weight: bold;
            font-size: 10.5pt;
            line-height: 1.6;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        /* ── Label sheet ── */
        .sheet-meta {
            font-size: 8pt;
            color: #555;
            margin-bottom: 6px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 4px;
        }

        /* ── Tabel ── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-top: 4px;
        }

        thead th {
            background-color: #dbeafe;
            border: 1px solid #93c5fd;
            padding: 5px 6px;
            text-align: center;
            font-weight: bold;
            font-size: 8pt;
            text-transform: uppercase;
            vertical-align: middle;
            white-space: nowrap;
        }

        tbody td {
            border: 1px solid #d1d5db;
            padding: 4px 6px;
            vertical-align: middle;
        }

        /* Kolom NO */
        td.col-no, th.col-no { text-align: center; width: 28px; }

        /* Kolom angka (kanan) */
        td.col-num { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }

        /* Kolom tengah */
        td.col-center, th.col-center { text-align: center; }

        /* Kolom teks (kiri) */
        td.col-text { text-align: left; }

        /* Baris sub-total / jumlah */
        tr.summary-row td {
            font-weight: bold;
            background-color: #f3f4f6;
        }

        /* Zebra striping */
        tbody tr:nth-child(even):not(.summary-row) td {
            background-color: #f9fafb;
        }

        /* ── Footer info ── */
        .page-footer {
            margin-top: 8px;
            font-size: 8pt;
            color: #6b7280;
            text-align: right;
        }

        @media print {
            .print-toolbar { display: none !important; }
            body { margin: 0; }
            .page { padding: 10mm 10mm 10mm 15mm; }
            thead { display: table-header-group; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>

{{-- ── Toolbar (tidak ikut cetak) ── --}}
<div class="print-toolbar">
    <span>🖨️ &nbsp;Honorarium Biaya Perkara — Mode Print</span>
    <button class="btn-print" onclick="window.print()">Cetak / Simpan PDF</button>
    <button class="btn-close" onclick="window.close()">✕ Tutup</button>
</div>

@if($error)
    <div class="page">
        <p style="color:red; font-family:sans-serif;">{{ $error }}</p>
    </div>
@elseif(count($sheets) === 0)
    <div class="page">
        <p style="font-family:sans-serif;">Tidak ada data honorarium ditemukan dalam file.</p>
    </div>
@else
    @foreach($sheets as $sheetIdx => $sheet)
    <div class="page {{ $sheetIdx > 0 ? 'page-break' : '' }}">

        {{-- Judul --}}
        @php
            $titleParts = array_filter(array_map('trim', explode("\n", $sheet['title'])));
        @endphp
        <div class="doc-title">
            @if(count($titleParts))
                @foreach($titleParts as $line)
                    <p>{{ $line }}</p>
                @endforeach
            @else
                <p>HONORARIUM BIAYA PENYELESAIAN PERKARA</p>
            @endif
        </div>

        <p class="sheet-meta">Sheet: <strong>{{ $sheet['sheetName'] }}</strong></p>

        {{-- Tabel --}}
        <table>
            <thead>
                <tr>
                    @foreach($sheet['headers'] as $colIdx => $headerName)
                        @php
                            $upper    = strtoupper(trim($headerName));
                            $isNo     = in_array($upper, ['NO','NO.','NOMOR']);
                            $isCenter = str_contains($upper,'PERKARA') && str_contains($upper,'JUMLAH');
                        @endphp
                        <th class="{{ $isNo ? 'col-no' : ($isCenter ? 'col-center' : '') }}">
                            {{ $headerName }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($sheet['rows'] as $row)
                    @php
                        $firstKey  = array_key_first($row);
                        $firstVal  = trim((string)($row[$firstKey] ?? ''));
                        $isSummary = !is_numeric($firstVal) && $firstVal !== '' && strtoupper($firstVal) !== 'NO';
                    @endphp
                    <tr class="{{ $isSummary ? 'summary-row' : '' }}">
                        @foreach($sheet['headers'] as $colIdx => $headerName)
                            @php
                                $val   = $row[$headerName] ?? '';
                                $upper = strtoupper(trim($headerName));

                                $isNumericCol = in_array($upper, ['BIAYA','JUMLAH BIAYA','PPH 15%','PPH 5%','NETTO','PPH','PAJAK','TOTAL'])
                                    || str_contains($upper, 'BIAYA')
                                    || str_contains($upper, 'NETTO')
                                    || str_contains($upper, 'PPH');
                                $isNoCol  = in_array($upper, ['NO','NO.','NOMOR']);
                                $isJmlCol = str_contains($upper,'PERKARA') && str_contains($upper,'JUMLAH');

                                $displayVal = $val;
                                $stripped   = str_replace(['.', ',', ' ', 'Rp'], '', $val);
                                if ($isNumericCol && $stripped !== '' && is_numeric($stripped)) {
                                    $num = (float) $stripped;
                                    $displayVal = $num != 0 ? 'Rp ' . number_format($num, 0, ',', '.') : '-';
                                }

                                if ($isNoCol)       $tdClass = 'col-no';
                                elseif ($isNumericCol) $tdClass = 'col-num';
                                elseif ($isJmlCol)  $tdClass = 'col-center';
                                else                $tdClass = 'col-text';
                            @endphp
                            <td class="{{ $tdClass }}">{{ $displayVal }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p class="page-footer">{{ count($sheet['rows']) }} baris &nbsp;|&nbsp; {{ $sheet['sheetName'] }}</p>

    </div>
    @endforeach
@endif

</body>
</html>
