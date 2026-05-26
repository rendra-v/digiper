<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Rekap Keseluruhan</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 7mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #111;
            background: #fff;
        }

        .page {
            width: 100%;
        }

        .title {
            text-align: center;
            line-height: 1.08;
            margin-bottom: 6px;
        }

        .title-line-1 {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .title-line-2 {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .title-line-3 {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            margin-top: 1px;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 7px;
            line-height: 1.02;
        }

        .report-table td,
        .report-table th {
            border: 1px solid #444;
            padding: 1px 2px;
            vertical-align: middle;
            word-break: break-word;
        }

        .report-table .center {
            text-align: center;
        }

        .report-table .left {
            text-align: left;
        }

        .report-table .right {
            text-align: right;
        }

        .report-table .bold {
            font-weight: 700;
        }

        .report-table .header-row td,
        .report-table .header-row th {
            font-weight: 700;
            text-align: center;
        }

        .report-table .main-head td,
        .report-table .main-head th {
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
            text-align: center;
            letter-spacing: 0.02em;
        }

        .report-table .sub-head td,
        .report-table .sub-head th {
            font-weight: 700;
            text-align: center;
        }

        .report-table .row-label {
            font-weight: 700;
        }

        .report-table .light {
            background: #f7f7f7;
        }

        .signature-area {
            margin-top: 10px;
            width: 100%;
        }

        .signature-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
            align-items: start;
        }

        .signature-block {
            min-height: 78px;
            font-size: 8px;
            line-height: 1.15;
            text-align: center;
            white-space: pre-line;
        }

        .signature-block.left {
            text-align: left;
        }

        .signature-block.right {
            text-align: right;
        }

        .signature-name {
            margin-top: 28px;
            font-size: 8px;
        }

        .signature-note {
            margin-top: 18px;
        }

        .signature-centered {
            margin-top: 14px;
            text-align: center;
            font-size: 8px;
            line-height: 1.15;
        }

        .signature-centered .name {
            margin-top: 28px;
        }

        .notice {
            margin: 40px auto;
            max-width: 720px;
            padding: 16px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #fafafa;
            font-size: 14px;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        @if ($error)
            <div class="notice">
                {{ $error }}
            </div>
        @else
            @php
                $rows = collect($report['rows'] ?? [])->keyBy('number');

                $getCellValue = function (int $rowNumber, string $reference, string $default = '-') use ($rows): string {
                    $row = $rows->get($rowNumber);
                    if (! $row) {
                        return $default;
                    }

                    $cell = collect($row['cells'] ?? [])->firstWhere('reference', $reference);

                    return $cell['value'] ?: $default;
                };

                $line1 = $getCellValue(2, 'A2', 'REKAPITULASI BIAYA PENYELESAIAN PERKARA YANG DIPUTUS PADA BULAN');
                $line2 = $getCellValue(3, 'A3', 'YANG USIANYA KURANG DARI 120 HARI SEJAK REGISTER PERKARA MASUK');
                $line3 = $reportLabel ?? 'PERKARA ELEKTRONIK';
                $recapDate = $recapDate ?? '';
            @endphp

            <div class="title">
                <div class="title-line-1">{{ $line1 }}</div>
                <div class="title-line-2">{{ $line2 }}</div>
                <div class="title-line-3">{{ $line3 }}</div>
            </div>

            <table class="report-table">
                <colgroup>
                    <col style="width: 3.2%;">
                    <col style="width: 20.5%;">
                    <col style="width: 8.5%;">
                    <col style="width: 6.8%;">
                    <col style="width: 7.0%;">
                    <col style="width: 7.0%;">
                    <col style="width: 9.3%;">
                    <col style="width: 7.3%;">
                    <col style="width: 6.8%;">
                    <col style="width: 5.5%;">
                    <col style="width: 6.5%;">
                    <col style="width: 8.8%;">
                    <col style="width: 7.5%;">
                    <col style="width: 8.3%;">
                </colgroup>
                <tbody>
                    @foreach ($report['rows'] as $row)
                        @if ($row['number'] < 4 || $row['number'] > 34)
                            @continue
                        @endif

                        @php
                            $rowNumber = $row['number'];
                            $rowClass = $rowNumber <= 8 ? 'header-row light' : 'body-row';
                        @endphp
                        <tr class="{{ $rowClass }}">
                            @foreach ($row['cells'] as $cell)
                                @php
                                    $value = $cell['value'];
                                    $cellClasses = [];

                                    if ($rowNumber <= 8) {
                                        $cellClasses[] = 'center';
                                        $cellClasses[] = 'bold';
                                    } elseif (in_array(substr($cell['reference'], 0, 1), ['A', 'C', 'D', 'E', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N'], true)) {
                                        $cellClasses[] = 'center';
                                    } else {
                                        $cellClasses[] = 'left';
                                    }

                                    if ($value === '') {
                                        $cellClasses[] = 'light';
                                    }
                                @endphp
                                <td
                                    rowspan="{{ $cell['rowspan'] }}"
                                    colspan="{{ $cell['colspan'] }}"
                                    class="{{ implode(' ', $cellClasses) }}"
                                >
                                    {{ $value }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="signature-area">
                <div class="signature-row">
                    <div class="signature-block left">
                        {{ $getCellValue(36, 'B36', 'KUASA PENGELOLA BIAYA PROSES') }}
                        <div class="signature-name">
                            {{ $getCellValue(40, 'B40', 'ASEP NURSOBAH, S.Ag., M.H.') }}
                        </div>
                    </div>

                    <div class="signature-block">
                        {{ $getCellValue(36, 'F36', 'PETUGAS PEMBUAT KOMITMEN') }}
                        <br>{{ $getCellValue(37, 'F37', 'BIAYA PROSES') }}
                        <div class="signature-name">
                            {{ $getCellValue(40, 'F40', 'ST. KRIS NUGROHO, S.H., M.H.') }}
                        </div>
                    </div>

                    <div class="signature-block right">
                        {{ $recapDate !== '' ? $recapDate : 'Jakarta, 05 Maret 2026' }}
                        <br>{{ $getCellValue(36, 'L36', 'BENDAHARA BIAYA PROSES') }}
                        <div class="signature-name">
                            {{ $getCellValue(40, 'L40', 'FARIDA,SH') }}
                        </div>
                    </div>
                </div>

                <div class="signature-centered">
                    {{ $getCellValue(43, 'F43', 'MENGETAHUI,') }}<br>
                    {{ $getCellValue(44, 'F44', 'PANITERA MA-RI') }}
                    <div class="name">
                        {{ $getCellValue(49, 'F49', 'Dr. SUDHARMAWATININGSIH, S.H., M.Hum.') }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script>
        window.addEventListener('load', () => {
            if (!{{ $error ? 'true' : 'false' }}) {
                setTimeout(() => {
                    window.print();
                }, 250);
            }
        });
    </script>
</body>
</html>
