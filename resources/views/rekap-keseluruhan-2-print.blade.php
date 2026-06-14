<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print – Rekap Keseluruhan Halaman 2</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 7mm 6mm;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 6.5px;
            color: #111;
            background: #fff;
        }

        /* ── Screen-only toolbar ─── */
        .no-print {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            background: #f3f4f6;
            border-bottom: 1px solid #d1d5db;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        @media print {
            .no-print { display: none !important; }
        }

        /* ── Title ─── */
        .doc-title {
            text-align: center;
            margin-bottom: 3px;
            line-height: 1.4;
        }
        .doc-title .t1 { font-size: 8px; font-weight: 700; text-transform: uppercase; }
        .doc-title .t2 { font-size: 7.5px; font-weight: 700; text-transform: uppercase; }
        .doc-title .t3 { font-size: 7px; font-weight: 400; color: #555; }

        /* ── Table ─── */
        .recap-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .recap-table td {
            border: 0.6px solid #555;
            padding: 1px 2px;
            vertical-align: middle;
            word-break: break-word;
            font-size: 6px;
            line-height: 1.15;
        }

        /* Header rows (baris 4–6 dari Excel) */
        .recap-table .hdr {
            background: #cce5ff;
            font-weight: 700;
            text-align: center;
            font-size: 5.5px;
            text-transform: uppercase;
        }

        /* Baris JUMLAH */
        .recap-table .tot { background: #f1f5f9; font-weight: 700; }

        /* Baris 100% */
        .recap-table .total100 { background: #fef9c3; font-weight: 700; }

        /* Alignment helpers */
        .c { text-align: center; }
        .l { text-align: left; }
        .r { text-align: right; }
        .b { font-weight: 700; }

        /* ── Error ─── */
        .notice {
            margin: 30px auto;
            max-width: 600px;
            padding: 16px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 13px;
            background: #fafafa;
        }
    </style>
</head>
<body>

    {{-- Toolbar (screen only) --}}
    <div class="no-print">
        <span style="font-weight:600; color:#1f2937;">🖨️ Preview – Rekap Keseluruhan Halaman 2 (Distribusi Biaya Per Peruntukan)</span>
        <div style="display:flex; gap:10px;">
            <button onclick="window.print()"
                    style="padding:7px 22px; background:#2563eb; color:#fff; border:none; border-radius:6px; font-weight:600; cursor:pointer; font-size:13px;">
                Print / Save PDF
            </button>
            <button onclick="window.close()"
                    style="padding:7px 16px; background:#e5e7eb; color:#374151; border:none; border-radius:6px; cursor:pointer; font-size:13px;">
                Tutup
            </button>
        </div>
    </div>

    @if($error)
        <div class="notice">{{ $error }}</div>
    @else
        @php
            /**
             * Kolom mapping (Q=17 s.d. AY=51)
             * Q=17 NO, R=18 label, S=19 PERUNTUKAN, T=20 %
             * U=21..W=23  KASASI PDT (BIAYA|JML|SUB TOTAL)
             * X=24..Z=26  KASASI TUN
             * AA=27..AC=29 KASASI NIAGA
             * AD=30..AF=32 PK
             * AG=33..AI=35 P-HUM
             * AJ=36..AL=38 PK-PAJAK
             * AM=39..AO=41 PK-PDT KHUSUS
             * AP=42..AR=44 PK-AGAMA
             * AS=45..AU=47 PK-TUN
             * AV=48..AX=50 PK NIAGA
             * AY=51        TOTAL
             */
            $HEADER_START = 4;
            $HEADER_END   = 6;

            $colQ = 17; $colR = 18; $colS = 19; $colT = 20;
            $numericStartCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString('U');
            $totalCol        = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString('AY');

            $isJumlahRow = function(int $rowNum, array $cells) {
                foreach ($cells as $cell) {
                    $colLtr = preg_replace('/\d+/', '', $cell['reference']);
                    $colNum = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($colLtr);
                    if ($colNum === 19 && stripos($cell['value'], 'JUMLAH') !== false) return true;
                }
                return false;
            };

            $is100Row = function(int $rowNum, array $cells) {
                foreach ($cells as $cell) {
                    $colLtr = preg_replace('/\d+/', '', $cell['reference']);
                    $colNum = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($colLtr);
                    if ($colNum === 20 && $cell['value'] === '100%') return true;
                }
                return false;
            };
        @endphp

        {{-- Title --}}
        <div class="doc-title">
            <div class="t1">{{ $title1 ?: 'REKAPITULASI BIAYA PENYELESAIAN PERKARA YANG DIPUTUS' }}</div>
            <div class="t2">{{ $title2 ?: 'YANG USIANYA KURANG DARI 120 HARI SEJAK REGISTER PERKARA MASUK' }}</div>
            <div class="t3">Distribusi Biaya Per Peruntukan</div>
        </div>

        <table class="recap-table">
            <colgroup>
                {{-- Q: NO --}}            <col style="width:22pt;">
                {{-- R: label --}}         <col style="width:14pt;">
                {{-- S: PERUNTUKAN --}}    <col style="width:130pt;">
                {{-- T: % --}}             <col style="width:22pt;">
                {{-- KASASI PDT: BIAYA|JML|SUB TOTAL --}}
                <col style="width:38pt;"><col style="width:22pt;"><col style="width:50pt;">
                {{-- KASASI TUN: BIAYA|JML|SUB TOTAL --}}
                <col style="width:38pt;"><col style="width:18pt;"><col style="width:42pt;">
                {{-- KASASI NIAGA: BIAYA|JML|SUB TOTAL --}}
                <col style="width:40pt;"><col style="width:16pt;"><col style="width:40pt;">
                {{-- PK: BIAYA|JML|SUB TOTAL --}}
                <col style="width:40pt;"><col style="width:18pt;"><col style="width:48pt;">
                {{-- P-HUM: BIAYA|JML|SUB TOTAL --}}
                <col style="width:36pt;"><col style="width:16pt;"><col style="width:36pt;">
                {{-- PK-PAJAK: BIAYA|JML|SUB TOTAL --}}
                <col style="width:40pt;"><col style="width:22pt;"><col style="width:54pt;">
                {{-- PK-PDT KHUSUS: BIAYA|JML|SUB TOTAL --}}
                <col style="width:40pt;"><col style="width:16pt;"><col style="width:40pt;">
                {{-- PK-AGAMA: BIAYA|JML|SUB TOTAL --}}
                <col style="width:40pt;"><col style="width:16pt;"><col style="width:42pt;">
                {{-- PK-TUN: BIAYA|JML|SUB TOTAL --}}
                <col style="width:40pt;"><col style="width:16pt;"><col style="width:42pt;">
                {{-- PK NIAGA: BIAYA|JML|SUB TOTAL --}}
                <col style="width:40pt;"><col style="width:16pt;"><col style="width:42pt;">
                {{-- AY: TOTAL --}}         <col style="width:56pt;">
            </colgroup>
            <tbody>
                @foreach($report['rows'] as $row)
                    @php
                        $rowNum = $row['number'];
                        $cells  = $row['cells'];

                        $isHeaderRow = ($rowNum >= $HEADER_START && $rowNum <= $HEADER_END);
                        $isJumlah    = !$isHeaderRow && $isJumlahRow($rowNum, $cells);
                        $isTotal100  = !$isHeaderRow && !$isJumlah && $is100Row($rowNum, $cells);

                        $trClass = $isHeaderRow ? 'hdr' : ($isJumlah ? 'tot' : ($isTotal100 ? 'total100' : ''));
                    @endphp
                    <tr class="{{ $trClass }}">
                        @foreach($cells as $cell)
                            @php
                                $val    = $cell['value'];
                                $colLtr = preg_replace('/\d+/', '', $cell['reference']);
                                $colNum = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($colLtr);

                                // Alignment
                                if ($isHeaderRow)          $align = 'c';
                                elseif ($colNum === $colQ) $align = 'c';
                                elseif ($colNum === $colR) $align = 'c';
                                elseif ($colNum === $colS) $align = 'l';
                                elseif ($colNum === $colT) $align = 'c';
                                else                       $align = 'r';

                                // Format angka
                                $display = $val;
                                $isNumericCol = ($colNum >= $numericStartCol && $colNum <= $totalCol);

                                if (!$isHeaderRow && $isNumericCol) {
                                    $numericVal = str_replace([',', '.'], '', $val);
                                    if (is_numeric($numericVal) && (int)$numericVal !== 0) {
                                        $display = number_format((float)$numericVal, 0, ',', '.');
                                    } elseif ($val === '-' || $val === '') {
                                        $display = '-';
                                    } elseif (is_numeric($val) && (float)$val == 0) {
                                        $display = '-';
                                    }
                                }

                                $boldClass = ($isHeaderRow || $isJumlah || $isTotal100) ? ' b' : '';
                            @endphp
                            <td rowspan="{{ $cell['rowspan'] }}"
                                colspan="{{ $cell['colspan'] }}"
                                class="{{ $align }}{{ $boldClass }}">{{ $display }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

    @endif

    <script>
        @if(!$error)
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 400);
        });
        @endif
    </script>
</body>
</html>
