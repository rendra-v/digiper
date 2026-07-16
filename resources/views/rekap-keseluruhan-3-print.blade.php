<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print – Rekap Keseluruhan Halaman 3</title>
    <style>
        @page { size: A4 landscape; margin: 7mm 6mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 6.5px; color: #111; background: #fff; }
        .no-print { font-family: Arial, Helvetica, sans-serif; font-size: 13px; background: #f3f4f6; border-bottom: 1px solid #d1d5db; padding: 10px 16px; display: flex; align-items: center; justify-content: space-between; }
        @media print { .no-print { display: none !important; } }
        .doc-title { text-align: center; margin-bottom: 3px; line-height: 1.4; }
        .doc-title .t1 { font-size: 8px; font-weight: 700; text-transform: uppercase; }
        .doc-title .t2 { font-size: 7.5px; font-weight: 700; text-transform: uppercase; }
        .doc-title .t3 { font-size: 7px; font-weight: 400; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        td, th { border: 0.6px solid #555; padding: 1px 2px; vertical-align: middle; font-size: 5.8px; line-height: 1.15; }
        .hdr { background: #cce5ff; font-weight: 700; text-align: center; font-size: 5.5px; text-transform: uppercase; }
        .tot { background: #f1f5f9; font-weight: 700; }
        .c { text-align: center; } .l { text-align: left; } .r { text-align: right; }
        .b { font-weight: 700; } .netto { color: #166534; font-weight: 700; }
        .notice { margin: 30px auto; max-width: 600px; padding: 16px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px; }
    </style>
</head>
<body>

    <div class="no-print">
        <span style="font-weight:600; color:#1f2937;">🖨️ Preview – Rekap Keseluruhan 3 (Honorarium Bruto/PPh/Netto)</span>
        <div style="display:flex; gap:10px;">
            <button onclick="window.print()" style="padding:7px 22px; background:#2563eb; color:#fff; border:none; border-radius:6px; font-weight:600; cursor:pointer; font-size:13px;">Print / Save PDF</button>
            <button onclick="window.close()" style="padding:7px 16px; background:#e5e7eb; color:#374151; border:none; border-radius:6px; cursor:pointer; font-size:13px;">Tutup</button>
        </div>
    </div>

    @if($error)
        <div class="notice">{{ $error }}</div>
    @elseif(!isset($rekap) || !$rekap)
        <div class="notice">Belum ada data. Silakan buka halaman Rekap Keseluruhan 3 terlebih dahulu.</div>
    @else
        @php
            $jenisList = $rekap['jenis_list'];
            $rows      = $rekap['rows'];
        @endphp

        <div class="doc-title">
            <div class="t1">REKAPITULASI BIAYA PENYELESAIAN PERKARA YANG DIPUTUS</div>
            <div class="t2">YANG USIANYA KURANG DARI 120 HARI SEJAK REGISTER PERKARA MASUK</div>
            <div class="t3">Rincian Honorarium Perkara – Bruto, PPh &amp; Netto</div>
        </div>

        <table>
            <thead>
                <tr class="hdr">
                    <th rowspan="2">NO</th>
                    <th rowspan="2"></th>
                    <th rowspan="2" class="l">PERUNTUKAN</th>
                    <th rowspan="2">PPh</th>
                    @foreach($jenisList as $jenis)
                        <th colspan="3">{{ $jenis['label'] }}</th>
                    @endforeach
                    <th colspan="4">TOTAL</th>
                </tr>
                <tr class="hdr">
                    @foreach($jenisList as $jenis)
                        <th>BIAYA</th><th>JML</th><th>SUB TOTAL</th>
                    @endforeach
                    <th>BRUTO</th><th>PPh 15%</th><th>PPh 5%</th><th>NETTO</th>
                </tr>
            </thead>
            <tbody>
                @php $prevNo = null; @endphp
                @foreach($rows as $row)
                    <tr>
                        <td class="c">@if($row['no'] !== $prevNo){{ $row['no'] }}@endif</td>
                        <td class="c">{{ $row['label_no'] }}</td>
                        <td class="l">{{ $row['peruntukan'] }}</td>
                        <td class="c">{{ $row['pph_pool'] }}%</td>
                        @foreach($jenisList as $jenis)
                            @php $j = $row['per_jenis'][$jenis['key']]; @endphp
                            <td class="r">{{ $j['biaya'] > 0 ? number_format($j['biaya'], 0, ',', '.') : '-' }}</td>
                            <td class="r">{{ $j['jumlah'] > 0 ? number_format($j['jumlah'], 0, ',', '.') : '-' }}</td>
                            <td class="r">{{ $j['sub_total'] > 0 ? number_format($j['sub_total'], 0, ',', '.') : '-' }}</td>
                        @endforeach
                        <td class="r b">{{ $row['bruto'] > 0 ? number_format($row['bruto'], 0, ',', '.') : '-' }}</td>
                        <td class="r">{{ $row['pph15'] > 0 ? number_format($row['pph15'], 0, ',', '.') : '-' }}</td>
                        <td class="r">{{ $row['pph5'] > 0 ? number_format($row['pph5'], 0, ',', '.') : '-' }}</td>
                        <td class="r netto">{{ $row['netto'] > 0 ? number_format($row['netto'], 0, ',', '.') : '-' }}</td>
                    </tr>
                    @php $prevNo = $row['no']; @endphp
                @endforeach
                <tr class="tot">
                    <td colspan="4" class="b">JUMLAH</td>
                    @foreach($jenisList as $jenis)
                        <td colspan="2"></td>
                        <td class="r b">{{ $rekap['jumlah_jenis'][$jenis['key']] > 0 ? number_format($rekap['jumlah_jenis'][$jenis['key']], 0, ',', '.') : '-' }}</td>
                    @endforeach
                    <td class="r b">{{ $rekap['jumlah_bruto'] > 0 ? number_format($rekap['jumlah_bruto'], 0, ',', '.') : '-' }}</td>
                    <td class="r b">{{ $rekap['jumlah_pph15'] > 0 ? number_format($rekap['jumlah_pph15'], 0, ',', '.') : '-' }}</td>
                    <td class="r b">{{ $rekap['jumlah_pph5'] > 0 ? number_format($rekap['jumlah_pph5'], 0, ',', '.') : '-' }}</td>
                    <td class="r netto">{{ $rekap['jumlah_netto'] > 0 ? number_format($rekap['jumlah_netto'], 0, ',', '.') : '-' }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    <script>
        @if(!$error && isset($rekap) && $rekap)
        window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 400); });
        @endif
    </script>
</body>
</html>
