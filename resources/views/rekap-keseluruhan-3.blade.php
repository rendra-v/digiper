@extends('layout')

@section('title', 'Rekap Keseluruhan 3')

@section('content')
<div class="min-h-screen">

    {{-- ─── Header Page ─── --}}
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="text-4xl font-semibold tracking-tight mb-3">Rekap Keseluruhan</h2>
            <p class="text-neutral-500 dark:text-neutral-400">
                Rekapitulasi Honorarium Biaya Perkara – Bruto, PPh &amp; Netto
                @if($fileName)
                    — <span class="font-medium text-neutral-700 dark:text-neutral-300">{{ $fileName }}</span>
                @endif
            </p>
        </div>
        <div class="flex flex-col items-end gap-2">
            <div class="flex items-end gap-3">
                {{-- Dropdown: Lihat Halaman Lain --}}
                <div>
                    <label for="nav-page-select-rekap3" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                        Lihat halaman lain
                    </label>
                    <div class="relative">
                        <select id="nav-page-select-rekap3"
                            onchange="if(this.value) window.location.href = this.value"
                            class="appearance-none rounded-lg border border-emerald-300 dark:border-emerald-700 bg-white dark:bg-neutral-950 px-4 py-2.5 pr-10 text-sm font-medium text-neutral-900 dark:text-neutral-100 shadow-sm transition-colors duration-200 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 cursor-pointer">
                            <option value="" disabled selected>— Pilih halaman —</option>
                            <option value="{{ route('data-print') }}">📄&nbsp; Data Print Perkara</option>
                            <option value="{{ route('sheet-cek') }}">📋&nbsp; Sheet Cek</option>
                            <option value="{{ route('rekap-keseluruhan') }}">📊&nbsp; Rekap Keseluruhan 1</option>
                            <option value="{{ route('rekap-keseluruhan-2') }}">📈&nbsp; Rekap Keseluruhan 2</option>
                            <option value="{{ route('honorarium') }}">💰&nbsp; Honorarium Biaya</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-emerald-600 dark:text-emerald-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </div>
                    </div>
                </div>

                <a href="{{ route('dashboard') }}"
                   class="px-4 py-2.5 text-sm bg-neutral-200 dark:bg-neutral-800 hover:bg-neutral-300 dark:hover:bg-neutral-700 text-neutral-900 dark:text-neutral-100 rounded-lg transition-colors duration-200">
                    Dashboard
                </a>
                <a href="{{ route('rekap-keseluruhan-3.print') }}"
                   target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 9V2h12v7"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18H5a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 14h8v8H8z"/>
                    </svg>
                    Print PDF
                </a>
            </div>
            {{-- Navigasi Sebelumnya --}}
            <a href="{{ route('rekap-keseluruhan-2') }}"
               class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold bg-neutral-600 hover:bg-neutral-700 text-white rounded-lg transition-colors duration-200 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Sebelumnya
            </a>
        </div>
    </div>

    {{-- ─── Breadcrumb ─── --}}
    <div class="mb-6 flex items-center gap-2 text-sm">
        <a href="{{ route('rekap-keseluruhan') }}" class="px-3 py-1.5 rounded-md bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors">
            1 · Rekap Biaya
        </a>
        <svg class="w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('rekap-keseluruhan-2') }}" class="px-3 py-1.5 rounded-md bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors">
            2 · Distribusi Peruntukan
        </a>
        <svg class="w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="px-3 py-1.5 rounded-md bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 font-semibold">
            3 · Honorarium Perkara
        </span>
    </div>

    {{-- ─── Error ─── --}}
    @if($error)
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/50 rounded-lg">
            <p class="text-sm text-red-700 dark:text-red-400">{{ $error }}</p>
        </div>
    @endif

    @if(!$error && isset($report) && count($report['rows']) > 0)
        @php
            $headerRow  = $report['headerRow'] ?? null;
            $startColIdx = $report['startColIdx'] ?? 1;
            // Kolom ke-2 dalam tabel (relatif ke startColIdx) biasanya adalah Nama/Jabatan
            $colNoAbs   = $startColIdx;       // Kolom pertama = NO
            $colNamaAbs = $startColIdx + 1;   // Kolom kedua  = Nama/Jabatan

            $isHeaderRowFn = function(int $rowNum) use ($headerRow): bool {
                if ($headerRow === null) return false;
                // Extend to headerRow+1 untuk menangkap baris subheader BIAYA|JML|SUB TOTAL
                return $rowNum >= ($headerRow - 4) && $rowNum <= ($headerRow + 1);
            };

            // Cek apakah baris punya minimal satu sel tidak kosong
            $rowHasContentFn = function(array $cells): bool {
                foreach ($cells as $cell) {
                    if (trim($cell['value']) !== '') return true;
                }
                return false;
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
        @endphp

        {{-- ─── Title ─── --}}
        <div class="mb-4 text-center">
            <p class="text-sm font-bold uppercase tracking-wide text-neutral-900 dark:text-neutral-100">
                {{ $title1 ?: 'REKAPITULASI BIAYA PENYELESAIAN PERKARA YANG DIPUTUS' }}
            </p>
            <p class="text-sm font-bold uppercase tracking-wide text-neutral-800 dark:text-neutral-200 mt-0.5">
                {{ $title2 ?: 'YANG USIANYA KURANG DARI 120 HARI SEJAK REGISTER PERKARA MASUK' }}
            </p>
            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Rincian Honorarium Perkara – Bruto, PPh &amp; Netto</p>
        </div>

        {{-- ─── Table ─── --}}
        <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                @php
                    // ── Pre-separation: pisahkan baris header dan baris data ──
                    $theadRows = [];
                    $tbodyRows = [];
                    foreach ($report['rows'] as $_row) {
                        $_isHdr = $isHeaderRowFn($_row['number']);
                        if ($_isHdr) {
                            if ($rowHasContentFn($_row['cells'])) {
                                $theadRows[] = $_row;
                            }
                        } else {
                            if ($_row['hasData'] ?? true) {
                                $tbodyRows[] = $_row;
                            }
                        }
                    }

                    // ── Filter $tbodyRows: buang baris yang hanya berisi keyword subheader ──
                    // Ini menangani kasus di mana baris BIAYA/JML/SUB TOTAL "lolos" ke tbody
                    // (misalnya karena ada 2 baris subheader di Excel, atau isHeaderRowFn terlalu sempit).
                    $_tbodySubhdrKeywords = ['BIAYA','JML','SUB TOTAL','BRUTO','NETTO','PPH','PPh','TOTAL'];
                    $_isSubhdrOnlyRow = function(array $cells) use ($_tbodySubhdrKeywords): bool {
                        $nonEmptyCount = 0;
                        $subhdrCount   = 0;
                        foreach ($cells as $cell) {
                            $v = trim($cell['value']);
                            if ($v === '' || $v === '-' || $v === '0') continue;
                            $nonEmptyCount++;
                            $upper = strtoupper($v);
                            foreach ($_tbodySubhdrKeywords as $_kw) {
                                if (str_contains($upper, strtoupper($_kw))) {
                                    $subhdrCount++;
                                    break;
                                }
                            }
                        }
                        // Baris dianggap "subheader only" jika semua sel non-kosong adalah keyword subheader
                        return $nonEmptyCount > 0 && $subhdrCount === $nonEmptyCount;
                    };
                    $tbodyRows = array_values(array_filter(
                        $tbodyRows,
                        function ($_row) use ($_isSubhdrOnlyRow) {
                            return !$_isSubhdrOnlyRow($_row['cells']);
                        }
                    ));

                    // ── Ekstrak nama kategori dari theadRows[0] untuk thead statis ──
                    // Ambil semua sel dengan colspan >= 3 (= nama grup perkara / TOTAL)
                    $_categories = []; // [['label' => string, 'isTotal' => bool]]
                    if (count($theadRows) >= 1) {
                        foreach ($theadRows[0]['cells'] as $_c) {
                            if (($_c['colspan'] ?? 1) >= 3) {
                                $isTotal = strtoupper(trim($_c['value'])) === 'TOTAL'
                                    || (($_c['colspan'] ?? 1) >= 4);
                                $_categories[] = [
                                    'label'   => $_c['value'],
                                    'colspan' => $_c['colspan'],
                                    'isTotal' => $isTotal,
                                ];
                            }
                        }
                    }

                    // Fallback: jika tidak ditemukan dari theadRows, gunakan nama default
                    if (empty($_categories)) {
                        $_categories = [
                            ['label' => 'KASASI PDT, PDTSUS, AG (Rp400.000)', 'colspan' => 3, 'isTotal' => false],
                            ['label' => 'KASASI TUN (Rp400.000)',              'colspan' => 3, 'isTotal' => false],
                            ['label' => 'KASASI NIAGA (Rp5.000.000)',          'colspan' => 3, 'isTotal' => false],
                            ['label' => 'PK PDT (Rp2.000.000)',                'colspan' => 3, 'isTotal' => false],
                            ['label' => 'P - HUM/KHS (TUN) (Rp1.000.000)',    'colspan' => 3, 'isTotal' => false],
                            ['label' => 'PK - PAJAK (Rp2.000.000)',            'colspan' => 3, 'isTotal' => false],
                            ['label' => 'PK - PDT KHUSUS (Rp2.000.000)',       'colspan' => 3, 'isTotal' => false],
                            ['label' => 'PK - AGAMA (Rp2.000.000)',            'colspan' => 3, 'isTotal' => false],
                            ['label' => 'PK - TUN (Rp2.000.000)',              'colspan' => 3, 'isTotal' => false],
                            ['label' => 'PK NIAGA (Rp10.000.000)',             'colspan' => 3, 'isTotal' => false],
                            ['label' => 'TOTAL',                               'colspan' => 4, 'isTotal' => true],
                        ];
                    }

                    $_thBase = 'border border-neutral-300 dark:border-neutral-600 text-xs font-bold text-neutral-800 dark:text-neutral-200 text-center align-middle whitespace-nowrap';
                @endphp
                <table class="w-full text-xs border-collapse">

                    {{-- ▸ HEADER STATIS: identik dengan struktur Excel referensi --}}
                    <thead>
                        {{-- Baris 1: NO | PERUNTUKAN | % | Nama Kategori × N | TOTAL --}}
                        <tr class="bg-sky-100 dark:bg-sky-900/40 border-b border-neutral-300 dark:border-neutral-700">
                            <th rowspan="2" class="{{ $_thBase }} px-2 py-2">NO</th>
                            <th rowspan="2" class="{{ $_thBase }} px-2 py-2">PERUNTUKAN</th>
                            <th rowspan="2" class="{{ $_thBase }} px-2 py-2">%</th>
                            @foreach($_categories as $_cat)
                                <th colspan="{{ $_cat['colspan'] }}"
                                    class="{{ $_thBase }} px-2 py-2">{{ $_cat['label'] }}</th>
                            @endforeach
                        </tr>
                        {{-- Baris 2: BIAYA|JML|SUB TOTAL per kategori, BRUTO|PPh15%|PPh5%|NETTO untuk TOTAL --}}
                        <tr class="bg-sky-50 dark:bg-sky-950/40 border-b border-neutral-300 dark:border-neutral-700">
                            @foreach($_categories as $_cat)
                                @if($_cat['isTotal'])
                                    <th class="{{ $_thBase }} px-1.5 py-1">BRUTO</th>
                                    <th class="{{ $_thBase }} px-1.5 py-1">PPh 15%</th>
                                    <th class="{{ $_thBase }} px-1.5 py-1">PPh 5%</th>
                                    <th class="{{ $_thBase }} px-1.5 py-1">NETTO</th>
                                @else
                                    <th class="{{ $_thBase }} px-1.5 py-1">BIAYA</th>
                                    <th class="{{ $_thBase }} px-1.5 py-1">JML</th>
                                    <th class="{{ $_thBase }} px-1.5 py-1">SUB TOTAL</th>
                                @endif
                            @endforeach
                        </tr>
                    </thead>

                    {{-- ▸ BODY: baris data --}}
                    <tbody>
                        @foreach($tbodyRows as $row)
                            @php
                                $cells   = $row['cells'];
                                $isTotal = $isTotalRowFn($cells);
                                $trBg    = $isTotal
                                    ? 'bg-neutral-100 dark:bg-neutral-800/60'
                                    : 'hover:bg-blue-50/30 dark:hover:bg-neutral-800/30';
                            @endphp
                            <tr class="border-b border-neutral-200 dark:border-neutral-800 {{ $trBg }}">
                                @foreach($cells as $cell)
                                    @php
                                        $val    = $cell['value'];
                                        $colLtr = preg_replace('/\d+/', '', $cell['reference']);
                                        $colNum = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($colLtr);

                                        // Alignment: NO=center, Nama/Peruntukan=left, numerik=right
                                        if ($colNum === $colNoAbs) {
                                            $align = 'text-center';
                                        } elseif ($colNum === $colNamaAbs) {
                                            $align = 'text-left';
                                        } else {
                                            $align = 'text-right';
                                        }

                                        // Format angka pada kolom numerik (kolom ke-3 dan seterusnya)
                                        $display   = $val;
                                        $isNumeric = ($colNum > $colNamaAbs);
                                        if ($isNumeric) {
                                            $stripped = str_replace([',', '.'], '', $val);
                                            if (is_numeric($stripped) && (float) $stripped != 0) {
                                                $display = number_format((float) $stripped, 0, ',', '.');
                                            } elseif ($val === '' || $val === '0' || (is_numeric($val) && (float) $val == 0)) {
                                                $display = '-';
                                            }
                                        }

                                        $tdClass  = 'border border-neutral-200 dark:border-neutral-700 px-1.5 py-1 text-xs '.$align;
                                        $tdClass .= $isTotal
                                            ? ' font-bold text-neutral-900 dark:text-neutral-100'
                                            : ' text-neutral-800 dark:text-neutral-200';
                                    @endphp
                                    <td rowspan="{{ $cell['rowspan'] }}"
                                        colspan="{{ $cell['colspan'] }}"
                                        class="{{ $tdClass }}">{{ $display }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>

        {{-- ─── Signature Block ─── --}}
        <div class="mt-12 mb-16">
            <div class="flex justify-end mb-8">
                <p class="text-sm font-medium text-neutral-800 dark:text-neutral-200">
                    {{ $recapDate ?: 'Jakarta, 05 Maret 2026' }}
                </p>
            </div>
            <div class="grid grid-cols-3 gap-8">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-neutral-700 dark:text-neutral-300">Kuasa Pengelola Biaya Proses</p>
                    <div class="mt-20 border-t-2 border-neutral-400 dark:border-neutral-600 pt-1">
                        <p class="text-xs text-neutral-500">&nbsp;</p>
                    </div>
                </div>
                <div class="text-center">
                    <p class="text-xs font-bold uppercase tracking-wide text-neutral-700 dark:text-neutral-300">Petugas Pembuat Komitmen<br>Biaya Proses</p>
                    <div class="mt-20 border-t-2 border-neutral-400 dark:border-neutral-600 pt-1">
                        <p class="text-xs text-neutral-500">&nbsp;</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs font-bold uppercase tracking-wide text-neutral-700 dark:text-neutral-300">Bendahara Biaya Proses</p>
                    <div class="mt-20 border-t-2 border-neutral-400 dark:border-neutral-600 pt-1">
                        <p class="text-xs text-neutral-500">&nbsp;</p>
                    </div>
                </div>
            </div>
            <div class="mt-14 flex flex-col items-center text-center">
                <p class="text-xs font-bold uppercase tracking-wide text-neutral-700 dark:text-neutral-300">Mengetahui,</p>
                <p class="text-xs font-bold uppercase tracking-wide text-neutral-700 dark:text-neutral-300 mt-0.5">Panitera MA-RI</p>
                <div class="mt-20 w-48 border-t-2 border-neutral-400 dark:border-neutral-600 pt-1 mx-auto">
                    <p class="text-xs text-neutral-500">&nbsp;</p>
                </div>
            </div>
        </div>

    @else
        {{-- ─── Empty State ─── --}}
        <div class="text-center py-24">
            <svg class="w-16 h-16 text-neutral-300 dark:text-neutral-700 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            @if(! $error)
                <p class="text-neutral-500 dark:text-neutral-400 text-lg font-medium">Tabel honorarium tidak ditemukan</p>
                <p class="text-neutral-400 dark:text-neutral-500 text-sm mt-2">
                    Pastikan sheet "Rekap Keseluruhan" memiliki tabel dengan kolom PEJABATAN / BRUTO / NETTO di bawah area rekap utama.
                </p>
            @endif
            <div class="flex gap-3 justify-center mt-6">
                <a href="{{ route('rekap-keseluruhan-2') }}"
                   class="inline-flex items-center gap-2 px-5 py-2 bg-neutral-600 hover:bg-neutral-700 text-white rounded-lg transition-colors duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Kembali
                </a>
                <a href="{{ route('dashboard') }}"
                   class="inline-block px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200">
                    Ke Dashboard
                </a>
            </div>
        </div>
    @endif

</div>
@endsection
