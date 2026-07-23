<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print – Rekap Keseluruhan</title>
    <style>
        @page {
            size: 330mm 215.9mm;
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

        .recap-table td,
        .recap-table th {
            border: 0.6px solid #555;
            padding: 1.5px 2px;
            vertical-align: middle;
            word-break: break-word;
            font-size: 7px;
            line-height: 1.2;
        }

        /* Header rows */
        .recap-table .hdr {
            background: #cce5ff;
            font-weight: 700;
            text-align: center;
            font-size: 6.5px;
            text-transform: uppercase;
        }

        /* Category / group label rows */
        .recap-table .cat { background: #dbeafe; font-weight: 700; }

        /* Sub-total per kelompok */
        .recap-table .subtot { background: #f1f5f9; font-weight: 700; }

        /* Grand total */
        .recap-table .gtot { background: #bfdbfe; font-weight: 700; }

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
        @php $pejabat = config('tarif.pejabat'); @endphp

        {{-- Title --}}
        <div class="doc-title">
            <div class="t1">REKAPITULASI BIAYA PENYELESAIAN PERKARA YANG DIPUTUS</div>
            <div class="t2">YANG USIANYA KURANG DARI 120 HARI SEJAK REGISTER PERKARA MASUK</div>
        </div>

        <table class="recap-table">
            <colgroup>
                <col style="width:3%">      {{-- No --}}
                <col style="width:14%">     {{-- Jenis Perkara --}}
                <col style="width:10%">     {{-- Klasifikasi --}}
                <col style="width:6%">      {{-- Kasasi: Jumlah --}}
                <col style="width:9%">      {{-- Kasasi: Biaya --}}
                <col style="width:11%">     {{-- Kasasi: Total --}}
                <col style="width:6%">      {{-- PK: Jumlah --}}
                <col style="width:9%">      {{-- PK: Biaya --}}
                <col style="width:11%">     {{-- PK: Total --}}
                <col style="width:11%">     {{-- Grand Total --}}
            </colgroup>
            <thead>
                <tr class="hdr">
                    <th rowspan="2" class="c">No</th>
                    <th rowspan="2" class="c">Jenis Perkara</th>
                    <th rowspan="2" class="c">Klasifikasi</th>
                    <th colspan="3" class="c">KASASI</th>
                    <th colspan="3" class="c">PENINJAUAN KEMBALI (PK)</th>
                    <th rowspan="2" class="c">Grand Total (Rp)</th>
                </tr>
                <tr class="hdr">
                    <th class="c">Jumlah</th>
                    <th class="c">Biaya (Rp)</th>
                    <th class="c">Jumlah Biaya (Rp)</th>
                    <th class="c">Jumlah</th>
                    <th class="c">Biaya (Rp)</th>
                    <th class="c">Jumlah Biaya (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($groups as $group)
                    @php $rowCount = count($group['rows']); @endphp

                    @foreach($group['rows'] as $i => $row)
                        <tr>
                            @if($i === 0)
                                <td rowspan="{{ $rowCount }}" class="c b cat">{{ $group['no'] }}</td>
                                <td rowspan="{{ $rowCount }}" class="l b cat">{{ $group['label'] }}</td>
                            @endif
                            <td class="l">{{ $row['label'] }}</td>
                            {{-- Kasasi --}}
                            <td class="r">{{ $row['kasasi_jumlah'] > 0 ? number_format($row['kasasi_jumlah'], 0, ',', '.') : '-' }}</td>
                            <td class="r">{{ $row['kasasi_biaya'] > 0 ? number_format($row['kasasi_biaya'], 0, ',', '.') : '-' }}</td>
                            <td class="r">{{ $row['kasasi_total'] > 0 ? number_format($row['kasasi_total'], 0, ',', '.') : '-' }}</td>
                            {{-- PK --}}
                            <td class="r">{{ $row['pk_jumlah'] > 0 ? number_format($row['pk_jumlah'], 0, ',', '.') : '-' }}</td>
                            <td class="r">{{ $row['pk_biaya'] > 0 ? number_format($row['pk_biaya'], 0, ',', '.') : '-' }}</td>
                            <td class="r">{{ $row['pk_total'] > 0 ? number_format($row['pk_total'], 0, ',', '.') : '-' }}</td>
                            {{-- Grand Total per row --}}
                            <td class="r">{{ $row['grand_total'] > 0 ? number_format($row['grand_total'], 0, ',', '.') : '-' }}</td>
                        </tr>
                    @endforeach

                    {{-- Sub-total per kelompok --}}
                    <tr class="subtot">
                        <td colspan="3" class="l">Total {{ $group['label'] }}</td>
                        <td class="r">{{ $group['kasasiJml'] > 0 ? number_format($group['kasasiJml'], 0, ',', '.') : '-' }}</td>
                        <td class="c">—</td>
                        <td class="r">{{ $group['kasasiTotal'] > 0 ? number_format($group['kasasiTotal'], 0, ',', '.') : '-' }}</td>
                        <td class="r">{{ $group['pkJml'] > 0 ? number_format($group['pkJml'], 0, ',', '.') : '-' }}</td>
                        <td class="c">—</td>
                        <td class="r">{{ $group['pkTotal'] > 0 ? number_format($group['pkTotal'], 0, ',', '.') : '-' }}</td>
                        <td class="r">{{ $group['grand'] > 0 ? number_format($group['grand'], 0, ',', '.') : '-' }}</td>
                    </tr>
                @endforeach

                {{-- Grand Total --}}
                @if($final_total)
                <tr class="gtot">
                    <td colspan="3" class="l">JUMLAH TOTAL KESELURUHAN</td>
                    <td class="r">{{ $final_total['kasasiJml'] > 0 ? number_format($final_total['kasasiJml'], 0, ',', '.') : '-' }}</td>
                    <td class="c">—</td>
                    <td class="r">{{ $final_total['kasasiTotal'] > 0 ? number_format($final_total['kasasiTotal'], 0, ',', '.') : '-' }}</td>
                    <td class="r">{{ $final_total['pkJml'] > 0 ? number_format($final_total['pkJml'], 0, ',', '.') : '-' }}</td>
                    <td class="c">—</td>
                    <td class="r">{{ $final_total['pkTotal'] > 0 ? number_format($final_total['pkTotal'], 0, ',', '.') : '-' }}</td>
                    <td class="r">{{ $final_total['grand'] > 0 ? number_format($final_total['grand'], 0, ',', '.') : '-' }}</td>
                </tr>
                @endif
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
                    <div class="sig-name">{{ $pejabat['kuasa_pengelola'] ?? 'ASEP NURSOBAH, S.Ag., M.H.' }}</div>
                </div>

                <div class="sig-col center">
                    <div class="sig-label">Petugas Pembuat Komitmen<br>Biaya Proses</div>
                    <div class="sig-space"></div>
                    <div class="sig-name">{{ $pejabat['ppk'] ?? 'ST. KRIS NUGROHO, S.H., M.H.' }}</div>
                </div>

                <div class="sig-col right">
                    <div class="sig-label">Bendahara Biaya Proses</div>
                    <div class="sig-space"></div>
                    <div class="sig-name">{{ $pejabat['bendahara'] ?? 'FARIDA, S.H.' }}</div>
                </div>

            </div>

            {{-- Mengetahui --}}
            <div class="sig-bottom">
                <div class="sig-label">Mengetahui,</div>
                <div class="sig-label">Panitera MA-RI</div>
                <div class="sig-name">Dr. SUDHARMAWATININGSIH, S.H., M.Hum.</div>
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
