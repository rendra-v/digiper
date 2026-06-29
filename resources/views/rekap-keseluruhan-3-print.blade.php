<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak – Rekap Keseluruhan 3</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, sans-serif;
            font-size: 8pt;
            color: #000;
            background: #fff;
        }
        .page-header {
            text-align: center;
            margin-bottom: 10px;
        }
        .page-header h1 {
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .page-header p {
            font-size: 8pt;
            margin-top: 2px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7pt;
        }
        th, td {
            border: 1px solid #000;
            padding: 2px 4px;
            vertical-align: middle;
        }
        th {
            background-color: #d0e8f0;
            font-weight: bold;
            text-align: center;
        }
        td.text-left   { text-align: left; }
        td.text-center { text-align: center; }
        td.text-right  { text-align: right; }
        td.total-row {
            font-weight: bold;
            background-color: #f0f0f0;
        }
        .footer-area {
            margin-top: 20px;
        }
        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-top: 8px;
        }
        .signature-block {
            min-height: 80px;
        }
        .signature-block p {
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .signature-name {
            margin-top: 60px;
            border-top: 2px solid #000;
            padding-top: 2px;
            font-size: 7.5pt;
            font-weight: bold;
        }
        .mengetahui {
            margin-top: 16px;
            text-align: center;
        }
        .date-line {
            text-align: right;
            margin-bottom: 10px;
            font-size: 8pt;
        }
        @media print {
            body { margin: 0; }
            @page { margin: 10mm; size: A3 landscape; }
        }
    </style>
</head>
<body>

    <div class="page-header">
        <h1>{{ $title1 ?: 'REKAPITULASI BIAYA PENYELESAIAN PERKARA YANG DIPUTUS' }}</h1>
        <p>{{ $title2 ?: 'YANG USIANYA KURANG DARI 120 HARI SEJAK REGISTER PERKARA MASUK' }}</p>
        <p>Rincian Honorarium Perkara – Bruto, PPh &amp; Netto</p>
    </div>

    @if($error)
        <p style="color:red; text-align:center;">{{ $error }}</p>
    @elseif(isset($report) && count($report['rows']) > 0)
        @php
            $headerRow  = $report['headerRow'] ?? null;
            $numericStartColIdx = 3;

            $isHeaderRowFn = function(int $rowNum) use ($headerRow): bool {
                if ($headerRow === null) return false;
                return $rowNum >= ($headerRow - 4) && $rowNum <= $headerRow;
            };

            $isTotalRowFn = function(array $cells): bool {
                foreach ($cells as $cell) {
                    $upper = strtoupper(trim($cell['value']));
                    if (str_contains($upper, 'JUMLAH') || str_contains($upper, 'TOTAL')) {
                        return true;
                    }
                }
                return false;
            };

            $isFooterRowFn = function(array $cells): bool {
                $text = '';
                foreach ($cells as $cell) {
                    $text .= ' ' . strtolower($cell['value']);
                }
                return str_contains($text, 'jakarta') ||
                       str_contains($text, 'mengetahui') ||
                       str_contains($text, 'kuasa pengelola') ||
                       str_contains($text, 'bendahara') ||
                       str_contains($text, 'panitera');
            };

            $tableRows  = [];
            $footerRows = [];
            foreach ($report['rows'] as $row) {
                if ($isFooterRowFn($row['cells'])) {
                    $footerRows[] = $row;
                } else {
                    $tableRows[] = $row;
                }
            }
        @endphp

        <table>
            <tbody>
                @foreach($tableRows as $row)
                    @php
                        $rowNum   = $row['number'];
                        $cells    = $row['cells'];
                        $isHeader = $isHeaderRowFn($rowNum);

                        // Skip baris kosong berdasarkan raw value (dari controller)
                        if (!$isHeader && !($row['hasData'] ?? true)) continue;

                        $isTotal  = !$isHeader && $isTotalRowFn($cells);
                    @endphp
                    <tr>
                        @foreach($cells as $cell)
                            @php
                                $val    = $cell['value'];
                                $colLtr = preg_replace('/\d+/', '', $cell['reference']);
                                $colNum = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($colLtr);

                                if ($isHeader) {
                                    $align = 'text-center';
                                } elseif ($colNum === 1) {
                                    $align = 'text-center';
                                } elseif ($colNum === 2) {
                                    $align = 'text-left';
                                } else {
                                    $align = 'text-right';
                                }

                                $display   = $val;
                                $isNumeric = $colNum >= $numericStartColIdx && !$isHeader;
                                if ($isNumeric && is_numeric(str_replace([',', '.'], '', $val))) {
                                    $numVal = (float) str_replace([',', '.'], '', $val);
                                    $display = $numVal != 0 ? number_format($numVal, 0, ',', '.') : '-';
                                } elseif ($isNumeric && ($val === '' || $val === null)) {
                                    $display = '-';
                                }

                                $tdClass = $align;
                                if ($isTotal) $tdClass .= ' total-row';
                            @endphp
                            @if($isHeader)
                                <th rowspan="{{ $cell['rowspan'] }}" colspan="{{ $cell['colspan'] }}">{{ $display }}</th>
                            @else
                                <td rowspan="{{ $cell['rowspan'] }}" colspan="{{ $cell['colspan'] }}" class="{{ $tdClass }}">{{ $display }}</td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer-area">
            <div class="date-line">{{ $recapDate ?: 'Jakarta, 05 Maret 2026' }}</div>
            <div class="signature-grid">
                <div class="signature-block">
                    <p>Kuasa Pengelola Biaya Proses</p>
                    <div class="signature-name">&nbsp;</div>
                </div>
                <div class="signature-block" style="text-align:center;">
                    <p>Petugas Pembuat Komitmen<br>Biaya Proses</p>
                    <div class="signature-name">&nbsp;</div>
                </div>
                <div class="signature-block" style="text-align:right;">
                    <p>Bendahara Biaya Proses</p>
                    <div class="signature-name">&nbsp;</div>
                </div>
            </div>
            <div class="mengetahui">
                <p style="font-size:7pt; font-weight:bold; text-transform:uppercase;">Mengetahui,</p>
                <p style="font-size:7pt; font-weight:bold; text-transform:uppercase; margin-top:2px;">Panitera MA-RI</p>
                <div class="signature-name" style="width:200px; margin: 60px auto 0;">&nbsp;</div>
            </div>
        </div>
    @else
        <p style="text-align:center; padding:40px;">Tabel honorarium tidak ditemukan dalam file Excel.</p>
    @endif

    <script>window.onload = function() { window.print(); }</script>
</body>
</html>
