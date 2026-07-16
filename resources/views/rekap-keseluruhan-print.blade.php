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
        {{-- Title --}}
        <div class="doc-title">
            <div class="t1">REKAPITULASI BIAYA PENYELESAIAN PERKARA YANG DIPUTUS</div>
            <div class="t2">YANG USIANYA KURANG DARI 120 HARI SEJAK REGISTER PERKARA MASUK</div>
        </div>

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
            <thead>
                <tr class="hdr">
                    <td rowspan="3">NO.</td>
                    <td rowspan="3">JENIS PERKARA</td>
                    <td rowspan="3">KLASIFIKASI</td>
                    <td colspan="10">JUMLAH PERKARA</td>
                    <td rowspan="3">TOTAL JML MINUTASI TEPAT WAKTU (120 HARI)</td>
                </tr>
                <tr class="hdr">
                    <td colspan="5">KASASI</td>
                    <td colspan="5">PENINJAUAN KEMBALI</td>
                </tr>
                <tr class="hdr" style="font-size: 5.5px;">
                    <td>SISA S.D TH<br>LALU</td>
                    <td>MASUK TH<br>INI</td>
                    <td>PUTUS</td>
                    <td>BELUM PUTUS</td>
                    <td>JML MINUT TEPAT WAKTU (120 HARI)</td>
                    <td>SISA S.D TH<br>LALU</td>
                    <td>MASUK TH<br>INI</td>
                    <td>PUTUS</td>
                    <td>BELUM PUTUS</td>
                    <td>JML MINUT TEPAT WAKTU (120 HARI)</td>
                </tr>
                <tr class="hdr" style="color: #666; font-size: 5px;">
                    @for($i = 1; $i <= 14; $i++)
                        <td>{{ $i }}</td>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @foreach($rekap['rows'] as $row)
                    @php
                        $trClass = $row['is_category'] ? 'cat' : '';
                    @endphp
                    <tr class="{{ $trClass }}">
                        <td class="c">{{ $row['no'] }}</td>
                        <td class="l">{{ $row['perkara'] }}</td>
                        <td class="l">{{ $row['klasifikasi'] }}</td>
                        
                        {{-- Kasasi --}}
                        <td class="r">{{ $row['kasasi']['sisa'] > 0 ? number_format($row['kasasi']['sisa'], 0, ',', '.') : '-' }}</td>
                        <td class="r">{{ $row['kasasi']['masuk'] > 0 ? number_format($row['kasasi']['masuk'], 0, ',', '.') : '-' }}</td>
                        <td class="r">{{ $row['kasasi']['putus'] > 0 ? number_format($row['kasasi']['putus'], 0, ',', '.') : '-' }}</td>
                        <td class="r">{{ $row['kasasi']['blm'] > 0 ? number_format($row['kasasi']['blm'], 0, ',', '.') : '-' }}</td>
                        <td class="r">{{ $row['kasasi']['minut'] > 0 ? number_format($row['kasasi']['minut'], 0, ',', '.') : '-' }}</td>

                        {{-- PK --}}
                        <td class="r">{{ $row['pk']['sisa'] > 0 ? number_format($row['pk']['sisa'], 0, ',', '.') : '-' }}</td>
                        <td class="r">{{ $row['pk']['masuk'] > 0 ? number_format($row['pk']['masuk'], 0, ',', '.') : '-' }}</td>
                        <td class="r">{{ $row['pk']['putus'] > 0 ? number_format($row['pk']['putus'], 0, ',', '.') : '-' }}</td>
                        <td class="r">{{ $row['pk']['blm'] > 0 ? number_format($row['pk']['blm'], 0, ',', '.') : '-' }}</td>
                        <td class="r">{{ $row['pk']['minut'] > 0 ? number_format($row['pk']['minut'], 0, ',', '.') : '-' }}</td>

                        {{-- Total --}}
                        <td class="r b">{{ $row['total_minut'] > 0 ? number_format($row['total_minut'], 0, ',', '.') : '-' }}</td>
                    </tr>
                @endforeach
                
                {{-- Baris JUMLAH --}}
                @php $t = $rekap['total']; @endphp
                <tr class="tot">
                    <td colspan="3" class="c" style="letter-spacing: 1px;">TOTAL</td>
                    <td class="r">{{ $t['kasasi']['sisa'] > 0 ? number_format($t['kasasi']['sisa'], 0, ',', '.') : '-' }}</td>
                    <td class="r">{{ $t['kasasi']['masuk'] > 0 ? number_format($t['kasasi']['masuk'], 0, ',', '.') : '-' }}</td>
                    <td class="r">{{ $t['kasasi']['putus'] > 0 ? number_format($t['kasasi']['putus'], 0, ',', '.') : '-' }}</td>
                    <td class="r">{{ $t['kasasi']['blm'] > 0 ? number_format($t['kasasi']['blm'], 0, ',', '.') : '-' }}</td>
                    <td class="r">{{ $t['kasasi']['minut'] > 0 ? number_format($t['kasasi']['minut'], 0, ',', '.') : '-' }}</td>
                    <td class="r">{{ $t['pk']['sisa'] > 0 ? number_format($t['pk']['sisa'], 0, ',', '.') : '-' }}</td>
                    <td class="r">{{ $t['pk']['masuk'] > 0 ? number_format($t['pk']['masuk'], 0, ',', '.') : '-' }}</td>
                    <td class="r">{{ $t['pk']['putus'] > 0 ? number_format($t['pk']['putus'], 0, ',', '.') : '-' }}</td>
                    <td class="r">{{ $t['pk']['blm'] > 0 ? number_format($t['pk']['blm'], 0, ',', '.') : '-' }}</td>
                    <td class="r">{{ $t['pk']['minut'] > 0 ? number_format($t['pk']['minut'], 0, ',', '.') : '-' }}</td>
                    <td class="r b">{{ $t['total_minut'] > 0 ? number_format($t['total_minut'], 0, ',', '.') : '-' }}</td>
                </tr>
            </tbody>
        </table>

        {{-- ── Tanda Tangan ── --}}
        <div class="sig-wrap">
            <div class="sig-date">
                {{ $recapDate ?: 'Jakarta, 05 Maret 2026' }}
            </div>

            <div class="sig-row">
                <div class="sig-col left">
                    <div class="sig-label">Kuasa Pengelola Biaya Proses</div>
                    <div class="sig-space"></div>
                    <div class="sig-name">ASEP NURSOBAH, S.Ag., M.H.</div>
                </div>
                <div class="sig-col center">
                    <div class="sig-label">Petugas Pembuat Komitmen<br>Biaya Proses</div>
                    <div class="sig-space"></div>
                    <div class="sig-name">ST. KRIS NUGROHO, S.H., M.H.</div>
                </div>
                <div class="sig-col right">
                    <div class="sig-label">Bendahara Biaya Proses</div>
                    <div class="sig-space"></div>
                    <div class="sig-name">FARIDA,SH</div>
                </div>
            </div>

            <div class="sig-bottom">
                <div class="sig-label">Mengetahui,<br>Panitera MA-RI</div>
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
