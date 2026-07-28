<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Rekap Keseluruhan</title>
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
            line-height: 1.35;
            margin-bottom: 8px;
            margin-top: 0;
        }

        .title-line-1 {
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .title-line-2 {
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .title-line-3 {
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            margin-top: 1px;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 9px;
            line-height: 1.35;
        }

        .report-table td,
        .report-table th {
            border: 1px solid #444;
            padding: 4px 6px;
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
            margin-top: 14px;
            width: 96%;
            margin-left: auto;
            margin-right: auto;
        }

        .signature-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 16px;
            align-items: start;
        }

        .signature-block {
            min-height: 5cm;
            font-size: 10px;
            line-height: 1.4;
            text-align: center;
            font-weight: 700;
            text-transform: uppercase;
        }

        .signature-name {
            margin-top: 5cm;
            font-size: 10px;
            font-weight: 700;
            text-decoration: underline;
        }

        .signature-date {
            text-align: right;
            font-size: 10px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .signature-centered {
            margin-top: 10px;
            text-align: center;
            font-size: 10px;
            font-weight: 700;
            line-height: 1.4;
            text-transform: uppercase;
        }

        .signature-centered .name {
            margin-top: 5cm;
            font-weight: 700;
            text-decoration: underline;
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
            body { padding: 20mm 15mm 10mm; }
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
            tr { break-inside: avoid; page-break-inside: avoid; }
            .signature-area { break-inside: avoid; page-break-inside: avoid; }
            .signature-centered { break-inside: avoid; page-break-inside: avoid; }
            table { width: 96%; margin-left: auto; margin-right: auto; }
        }
    </style>
</head>

<body style="padding: 20mm 15mm 10mm;">
    <div class="page">
        @if ($error)
            <div class="notice">
                {{ $error }}
            </div>
        @else
            @php
                $rows = collect($report['rows'] ?? [])->keyBy('number');

                $getCellValue = function (int $rowNumber, string $reference, string $default = '-') use (
                    $rows,
                ): string {
                    $row = $rows->get($rowNumber);
                    if (!$row) {
                        return $default;
                    }

                    $cell = collect($row['cells'] ?? [])->firstWhere('reference', $reference);

                    return $cell['value'] ?: $default;
                };

                $line1 = $getCellValue(2, 'A2', 'REKAPITULASI BIAYA PENYELESAIAN PERKARA YANG DIPUTUS PADA BULAN');
                $line2 = $getCellValue(3, 'A3', 'YANG USIANYA KURANG DARI 120 HARI SEJAK REGISTER PERKARA MASUK');
                $line3 = $reportLabel ?? 'PERKARA ELEKTRONIK';
                $recapDate = $recapDate ?: (
                    \Illuminate\Support\Facades\Session::get('excel_tgl_rekap_keseluruhan')
                    ?: ('Jakarta, ' . date('d') . ' ' . ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][(int)date('m')] . ' ' . date('Y'))
                );
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
                                    } elseif (
                                        in_array(
                                            substr($cell['reference'], 0, 1),
                                            ['A', 'C', 'D', 'E', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N'],
                                            true,
                                        )
                                    ) {
                                        $cellClasses[] = 'center';
                                    } else {
                                        $cellClasses[] = 'left';
                                    }

                                    if ($value === '') {
                                        $cellClasses[] = 'light';
                                    }
                                @endphp
                                <td rowspan="{{ $cell['rowspan'] }}" colspan="{{ $cell['colspan'] }}"
                                    class="{{ implode(' ', $cellClasses) }}">
                                    {{ $value }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="signature-area">
                <div class="signature-date">{{ $recapDate }}</div>
                <div class="signature-row">
                    <div class="signature-block">
                        <div>PETUGAS PEMBUAT KOMITMEN<br>BIAYA PROSES</div>
                        <div class="signature-name">
                            {{ $getCellValue(40, 'F40', 'ST. KRIS NUGROHO, S.H., M.H.') }}
                        </div>
                    </div>

                    <div class="signature-block">
                        <div>MENGETAHUI,<br>KUASA PENGELOLA BIAYA PROSES</div>
                        <div class="signature-name">
                            {{ $getCellValue(40, 'B40', 'ASEP NURSOBAH, S.Ag., M.H.') }}
                        </div>
                    </div>

                    <div class="signature-block">
                        <div>BENDAHARA BIAYA PROSES</div>
                        <div class="signature-name">
                            {{ $getCellValue(40, 'L40', 'FARIDA, S.H.') }}
                        </div>
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
