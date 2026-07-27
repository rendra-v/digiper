<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Data Print</title>
    <style>
        @page {
            margin: 0;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 7px;
            color: #111;
            background: #fff;
        }

        /* ── toolbar (screen only) ── */
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

        /* ── Judul ── */
        .cat-title {
            text-align: center;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 4px;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
        }
        .cat-subtitle {
            text-align: center;
            font-size: 9px;
            margin-bottom: 4px;
        }

        /* ── Tabel ── */
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }
        th, td {
            border: 0.8px solid #555;
            padding: 4px 6px;
            font-size: 9px;
            line-height: 1.35;
            vertical-align: middle;
        }
        thead tr { background: #d9d9d9; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        thead th { font-weight: 700; text-align: center; }
        .td-no  { text-align: center; width: 22px; }
        .td-l   { text-align: left; }
        .td-c   { text-align: center; }
        .td-r   { text-align: right; }

        /* ── Page break rules ── */
        .page-group {
            break-after: page;
            page-break-after: always;
        }
        .page-group:last-child {
            break-after: avoid;
            page-break-after: avoid;
        }
        .cat-section {
            break-after: page;
            page-break-after: always;
        }
        .cat-section:last-child {
            break-after: avoid;
            page-break-after: avoid;
        }

        /* ── Notice ── */
        .notice {
            margin: 30px auto; max-width: 400px; padding: 12px;
            border: 1px solid #ddd; border-radius: 6px; font-size: 12px;
        }

        @media print {
            .no-print { display: none !important; }
            body { padding: 10mm 12mm; }
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
            tr { break-inside: avoid; page-break-inside: avoid; }
            .cat-title, .cat-subtitle { break-after: avoid; page-break-after: avoid; }
        }
    </style>
</head>
<body>

{{-- Toolbar (screen only) --}}
<div class="no-print">
    <span>🖨️ Cetak Data Print
        @if($fileName) — {{ $fileName }} @endif
        @if($catFilter !== null) — Filter: {{ $catFilter }} @endif
    </span>
    <div style="display:flex; gap:8px;">
        <button onclick="window.print()"
            style="padding:6px 18px; background:#f4a418; color:#000; border:none; border-radius:6px; font-weight:600; cursor:pointer;">
            Cetak / Simpan PDF
        </button>
        <button onclick="window.close()"
            style="padding:6px 14px; background:#fff; color:#333; border:1px solid rgba(0,0,0,.2); border-radius:6px; cursor:pointer; font-weight:600;">
            Tutup
        </button>
    </div>
</div>

@if($error)
    <div class="notice">⚠ {{ $error }}</div>

@elseif(empty($categories))
    <div class="notice">Tidak ada data. Silakan upload file Excel terlebih dahulu.</div>

@else
    @php
        $excludedColumns = [
            'TANGGAL PERKARA MASUK', 'TANGGAL PERKARA MASUK 2', 'TANGGAL PERKARA MASUK 3',
            'No', 'no', 'NO',
        ];
        $rowsPerPage = 60; // baris per halaman
    @endphp

    @foreach($categories as $catKey => $category)
        @php
            $visibleColumns = collect($category['columns'] ?? [])
                ->filter(function ($colName) use ($excludedColumns) {
                    if (!$colName || $colName === 'No') return false;
                    if (str_starts_with($colName, '=')) return false;
                    if (preg_match('/^[A-Z]{1,3}$/', $colName)) return false;
                    if (is_numeric($colName)) return false;
                    if (in_array($colName, $excludedColumns, true)) return false;
                    return true;
                })
                ->values();

            // Filter baris valid
            $validRows = [];
            foreach (($category['data'] ?? []) as $row) {
                $noVal = trim((string)($row['No'] ?? ''));
                if ($noVal !== '' && !is_numeric($noVal)) continue;
                $meaningfulCount = 0;
                foreach ($visibleColumns as $colName) {
                    $v = trim((string)($row[$colName] ?? ''));
                    if ($v !== '' && $v !== '-') $meaningfulCount++;
                }
                if ($meaningfulCount === 0 && trim($noVal) === '') continue;
                $validRows[] = $row;
            }

            $rowChunks = array_chunk($validRows, $rowsPerPage);
            $totalChunks = count($rowChunks);
        @endphp

        @if(count($validRows) === 0)
            @continue
        @endif

        @foreach($rowChunks as $ci => $chunk)
        <div class="{{ ($loop->last && $loop->parent->last) ? 'cat-section' : 'page-group' }}">

            {{-- Judul --}}
            <div class="cat-title">
                DATA PRINT — {{ strtoupper($catKey) }}
            </div>
            <div class="cat-subtitle">
                {{ $fileName ?? '' }}
                @if($totalChunks > 1) — Halaman {{ $ci + 1 }} dari {{ $totalChunks }} @endif
                ({{ count($validRows) }} data)
            </div>

            {{-- Tabel --}}
            <table>
                <thead>
                    <tr>
                        <th class="td-no">No</th>
                        @foreach($visibleColumns as $colName)
                            <th>{{ $colName }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($chunk as $rowNum => $row)
                    <tr>
                        <td class="td-no">{{ ($ci * $rowsPerPage) + $rowNum + 1 }}</td>
                        @foreach($visibleColumns as $colName)
                            @php
                                $val = (string)($row[$colName] ?? '');
                                if (str_starts_with($val, '#VALUE') || str_starts_with($val, '#REF') || str_starts_with($val, '#N/A') || str_starts_with($val, '#')) {
                                    $val = '-';
                                }
                                $isDate = str_contains(strtolower($colName), 'tanggal') || str_contains(strtolower($colName), 'putus');
                                $cls = 'td-l';
                                if (is_numeric(str_replace([',', '.'], '', $val)) && !$isDate && $val !== '') $cls = 'td-r';
                                if ($isDate) $cls = 'td-c';
                            @endphp
                            <td class="{{ $cls }}">{{ $val !== null && $val !== '' ? $val : '-' }}</td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
        @endforeach

    @endforeach
@endif

</body>
</html>
