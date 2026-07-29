<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Data Print</title>
    <style>
        @page {
            margin: 2cm 1.2cm 1.2cm 1.2cm;
            size: 330mm 215.9mm;
            /* Hapus header/footer bawaan browser */
            @top-left   { content: ''; }
            @top-center { content: ''; }
            @top-right  { content: ''; }
            @bottom-left   { content: ''; }
            @bottom-center { content: ''; }
            @bottom-right  { content: ''; }
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
            body { padding: 0; margin: 0; }
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
            tr { break-inside: avoid; page-break-inside: avoid; }
            .cat-title, .cat-subtitle { break-after: avoid; page-break-after: avoid; }
            .page-group { break-after: page !important; page-break-after: always !important; }
            .cat-section { break-after: page !important; page-break-after: always !important; }
            .page-group:last-child, .cat-section:last-child { break-after: avoid !important; page-break-after: avoid !important; }
            .sig-block { break-inside: avoid !important; page-break-inside: avoid !important; }
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
            'U', 'V', 'QTY', 'P1', 'P2', 'P3', 'P4', 'P5', 'PP', 'cek bulan', 'cek umur', 
            'panmud', 'Jenis Perkara', 'Jenis Permohonan', 'klasifikasi', 'Klasifikasi', 'MJELIS', 
            'AK', 'AL', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AJ',
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

            {{-- Judul Utama & Subtitle --}}
            <div class="cat-title">{{ $category['title'] ?? '' }}</div>
            <div class="cat-subtitle">
                {{ $fileName ?? '' }}
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
                                $lowerCol = strtolower($colName);
                                $isName = preg_match('/nama p[1-5]/', $lowerCol) || 
                                          str_contains($lowerCol, 'nama panitera pengganti') || 
                                          str_contains($lowerCol, 'hakim pemilah');
                                
                                $cls = 'td-c';
                                if ($isName) {
                                    $cls = 'td-l';
                                }
                            @endphp
                            <td class="{{ $cls }}">{{ $val !== null && $val !== '' ? $val : '-' }}</td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>

        @if($loop->last)
            @php
                $dpPrintMonths = [1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'];
                $dpPrintDate = \Illuminate\Support\Facades\Session::get('excel_tgl_data_laporan')
                    ?: ('Jakarta, ' . date('d') . ' ' . $dpPrintMonths[(int)date('m')] . ' ' . date('Y'));
                $dpPrintJabatan = 'Panitera Muda ' . ucwords(strtolower($category['title'] ?? ''));
                $catSlug = \Str::slug($category['title'] ?? ('cat_' . $catKey));
                $dpPrintKey = 'ttd_dp_' . $catSlug;
                $defaultPrintTtdName = $category['ttd_name'] ?? '';
                if (empty($defaultPrintTtdName)) {
                    $opCfg = config('tarif.operator_kamar.' . ($category['title'] ?? '')) ?? config('tarif.operator_kamar.*');
                    $defaultPrintTtdName = $opCfg['nama'] ?? '';
                }
            @endphp
            <div class="sig-block" style="margin-top: 24px; display: flex; justify-content: flex-end; page-break-inside: avoid; break-inside: avoid;">
                <div style="text-align: center; min-width: 180px;">
                <p style="font-size: 9px; font-weight: 600;">{{ $dpPrintDate }}</p>
                <p style="font-size: 9px; font-weight: 700; margin-top: 3px;">Mengetahui,</p>
                <p style="font-size: 9px; font-weight: 700;">{{ $dpPrintJabatan }}</p>
                <div style="height: 2.5cm;"></div>
                <div data-ttd-key="{{ $dpPrintKey }}" style="font-size: 11px; font-weight: 700;">{{ $defaultPrintTtdName }}</div>
                </div>
            </div>
        @endif

        </div>
        @endforeach

    @endforeach
@endif
<script>
    window.addEventListener('load', function () {
        document.querySelectorAll('[data-ttd-key]').forEach(function (el) {
            var stored = localStorage.getItem(el.getAttribute('data-ttd-key'));
            if (stored !== null && stored !== '') el.textContent = stored;
        });
    });
</script>

</body>
</html>
