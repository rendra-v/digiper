<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print – Rekap Keseluruhan</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 7mm 8mm;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 7.5px;
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
            margin-bottom: 4px;
            line-height: 1.45;
        }
        .doc-title .t1 { font-size: 8.5px; font-weight: 700; text-transform: uppercase; }
        .doc-title .t2 { font-size: 8px;   font-weight: 700; text-transform: uppercase; }

        /* ── Table ─── */
        .recap-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .recap-table td {
            border: 0.6px solid #555;
            padding: 1.5px 2px;
            vertical-align: middle;
            word-break: break-word;
            font-size: 7px;
            line-height: 1.2;
        }

        /* Header rows (baris 4–8 dari Excel) */
        .recap-table .hdr {
            background: #cce5ff;
            font-weight: 700;
            text-align: center;
            font-size: 6.5px;
            text-transform: uppercase;
        }

        /* Category rows (I, II, III, IV...) */
        .recap-table .cat { background: #dbeafe; font-weight: 700; }

        /* Total / Jumlah rows */
        .recap-table .tot { background: #f1f5f9; font-weight: 700; }

        /* Alignment helpers */
        .c { text-align: center; }
        .l { text-align: left; }
        .r { text-align: right; }
        .b { font-weight: 700; }

        /* ── Signature ─── */
        .sig-wrap { margin-top: 7px; }

        .sig-date {
            text-align: right;
            font-size: 8px;
            font-weight: 700;
            margin-bottom: 3px;
        }

        .sig-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
        }

        .sig-col {
            font-size: 8px;
            line-height: 1.3;
            display: flex;
            flex-direction: column;
        }
        .sig-col.left   { align-items: flex-start; text-align: left; }
        .sig-col.center { align-items: center;      text-align: center; }
        .sig-col.right  { align-items: flex-end;    text-align: right; }

        .sig-label  { font-weight: 700; text-transform: uppercase; font-size: 7.5px; line-height: 1.35; }
        .sig-space  { height: 30px; }
        .sig-name   { font-weight: 700; text-decoration: underline; font-size: 8px; }

        .sig-bottom {
            margin-top: 7px;
            text-align: center;
            font-size: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .sig-bottom .sig-name { margin-top: 30px; }

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
        <span style="font-weight:600; color:#1f2937;">🖨️ Preview – Rekap Keseluruhan</span>
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
            $rows = collect($report['rows'] ?? [])->keyBy('number');
            $getCellVal = function (int $rn, string $ref, string $def = '') use ($rows) {
                $row  = $rows->get($rn);
                $cell = $row ? collect($row['cells'] ?? [])->firstWhere('reference', $ref) : null;
                $v    = $cell['value'] ?? '';
                return ($v !== '' && $v !== null) ? $v : $def;
            };

            // Baris 4–8  : header Excel (dengan merged cells)
            // Baris 9–34 : data tabel
            // Baris 35+  : area tanda tangan (tidak dirender di tabel)
            $HEADER_END = 8;
            $DATA_END   = 34;

            $romanNumerals = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII'];
        @endphp

        {{-- Title --}}
        <div class="doc-title">
            <div class="t1">{{ $title1 ?: 'REKAPITULASI BIAYA PENYELESAIAN PERKARA YANG DIPUTUS PADA BULAN DESEMBER 2025 S/D FEBRUARI 2026' }}</div>
            <div class="t2">{{ $title2 ?: 'YANG USIANYA KURANG DARI 120 HARI SEJAK REGISTER PERKARA MASUK' }}</div>
        </div>

        {{--
            Tabel: render baris 4–34 langsung dari data Excel.
            Rowspan/colspan sudah diekstrak dari merged cells Excel
            oleh buildRekapKeseluruhanReport().
            Tidak ada thead hardcoded — struktur merge dari Excel yang berlaku.
        --}}
        <table class="recap-table">
            <colgroup>
                {{-- A: No --}}              <col style="width:3%;">
                {{-- B: Jenis Perkara --}}   <col style="width:15%;">
                {{-- C: Klasifikasi --}}     <col style="width:7%;">
                {{-- D–H: KASASI (5) --}}
                <col style="width:5%;">
                <col style="width:5%;">
                <col style="width:5%;">
                <col style="width:7.5%;">
                <col style="width:5.5%;">
                {{-- I–M: PK (5) --}}
                <col style="width:5%;">
                <col style="width:5%;">
                <col style="width:5%;">
                <col style="width:7.5%;">
                <col style="width:5.5%;">
                {{-- N: Total --}}           <col style="width:7%;">
            </colgroup>
            <tbody>
                @foreach($report['rows'] as $row)
                    @php
                        $rowNum = $row['number'];
                        if ($rowNum < 4 || $rowNum > $DATA_END) continue;

                        $isHeaderRow = ($rowNum <= $HEADER_END);

                        // Skip baris kosong berdasarkan raw value (dari controller)
                        if (!$isHeaderRow && !($row['hasData'] ?? true)) continue;

                        $firstCell = collect($row['cells'])->first();
                        $firstVal  = trim($firstCell['value'] ?? '');

                        $isCat = !$isHeaderRow && in_array($firstVal, $romanNumerals);
                        $isTot = !$isHeaderRow
                            && (stripos($firstVal, 'TOTAL') !== false
                             || stripos($firstVal, 'JUMLAH') !== false);

                        $trClass = $isHeaderRow ? 'hdr' : ($isCat ? 'cat' : ($isTot ? 'tot' : ''));
                    @endphp
                    <tr class="{{ $trClass }}">
                        @foreach($row['cells'] as $cell)
                            @php
                                $val    = $cell['value'];
                                $colLtr = preg_replace('/\d+/', '', $cell['reference']);
                                $colNum = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($colLtr);

                                // Alignment
                                if ($isHeaderRow)     $align = 'c';
                                elseif ($colNum === 1) $align = 'c';
                                elseif ($colNum === 2) $align = 'l';
                                elseif ($colNum === 3) $align = 'c';
                                else                   $align = 'r';

                                // Format number
                                $display = $val;
                                if (!$isHeaderRow && is_numeric($val) && (float)$val != 0 && $colNum >= 4) {
                                    $display = number_format((float)$val, 0, ',', '.');
                                }
                                if (!$isHeaderRow && $colNum >= 4
                                    && ($display === '' || $display === null
                                        || (string)$display === '0' || (float)($val ?? 0) == 0)
                                ) {
                                    $display = '-';
                                }

                                $boldClass = ($isHeaderRow || $isCat || $isTot) ? ' b' : '';
                            @endphp
                            <td rowspan="{{ $cell['rowspan'] }}"
                                colspan="{{ $cell['colspan'] }}"
                                class="{{ $align }}{{ $boldClass }}">{{ $display }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- ══ Signature Area ══ --}}
        <div class="sig-wrap">

            {{-- Tanggal kanan --}}
            <div class="sig-date">{{ $recapDate ?: 'Jakarta, 05 Maret 2026' }}</div>

            {{-- 3 kolom --}}
            <div class="sig-row">

                <div class="sig-col left">
                    <div class="sig-label">Kuasa Pengelola Biaya Proses</div>
                    <div class="sig-space"></div>
                    <div class="sig-name">{{ $getCellVal(40, 'B40', 'ASEP NURSOBAH, S.Ag., M.H.') }}</div>
                </div>

                <div class="sig-col center">
                    <div class="sig-label">Petugas Pembuat Komitmen<br>Biaya Proses</div>
                    <div class="sig-space"></div>
                    <div class="sig-name">{{ $getCellVal(40, 'F40', 'ST. KRIS NUGROHO, S.H., M.H.') }}</div>
                </div>

                <div class="sig-col right">
                    <div class="sig-label">Bendahara Biaya Proses</div>
                    <div class="sig-space"></div>
                    <div class="sig-name">{{ $getCellVal(40, 'L40', 'FARIDA,SH') }}</div>
                </div>

            </div>

            {{-- Mengetahui --}}
            <div class="sig-bottom">
                <div class="sig-label">Mengetahui,</div>
                <div class="sig-label">Panitera MA-RI</div>
                <div class="sig-name">{{ $getCellVal(49, 'F49', 'Dr. SUDHARMAWATININGSIH, S.H., M.Hum.') }}</div>
            </div>

        </div>
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
