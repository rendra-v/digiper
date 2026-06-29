@extends('layout')

@section('title', 'Rekap Keseluruhan')

@section('content')
<div class="min-h-screen">

    {{-- ─── Header Page ─── --}}
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="text-4xl font-semibold tracking-tight mb-3">Rekap Keseluruhan</h2>
            <p class="text-neutral-500 dark:text-neutral-400">
                Rekapitulasi biaya penyelesaian perkara – Distribusi per peruntukan
                @if($fileName)
                    — <span class="font-medium text-neutral-700 dark:text-neutral-300">{{ $fileName }}</span>
                @endif
            </p>
        </div>
        <div class="flex items-end gap-3">

            {{-- Dropdown: Lihat Halaman Lain --}}
            <div>
                <label for="nav-page-select-rekap2" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    Lihat halaman lain
                </label>
                <div class="relative">
                    <select id="nav-page-select-rekap2"
                        onchange="if(this.value) window.location.href = this.value"
                        class="appearance-none rounded-lg border border-emerald-300 dark:border-emerald-700 bg-white dark:bg-neutral-950 px-4 py-2.5 pr-10 text-sm font-medium text-neutral-900 dark:text-neutral-100 shadow-sm transition-colors duration-200 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 cursor-pointer">
                        <option value="" disabled selected>— Pilih halaman —</option>
                        <option value="{{ route('data-print') }}">📄&nbsp; Data Print Perkara</option>
                        <option value="{{ route('sheet-cek') }}">📋&nbsp; Sheet Cek</option>
                        <option value="{{ route('rekap-keseluruhan') }}">📊&nbsp; Rekap Keseluruhan 1</option>
                        <option value="{{ route('rekap-keseluruhan-3') }}">📋&nbsp; Rekap Keseluruhan 3</option>
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
            <a href="{{ route('rekap-keseluruhan-2.print') }}"
               target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 9V2h12v7"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18H5a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 14h8v8H8z"/>
                </svg>
                Print PDF
            </a>
            <a href="{{ route('rekap-keseluruhan-3') }}"
               class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors duration-200 shadow-sm">
                Selanjutnya
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>

    {{-- ─── Error ─── --}}
    @if($error)
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/50 rounded-lg">
            <p class="text-sm text-red-700 dark:text-red-400">{{ $error }}</p>
        </div>
    @endif

    @if(!$error && isset($report) && count($report['rows']) > 0)
        @php
            /**
             * Tabel kanan atas dari Excel – area Q4:AY38
             *
             * STRUKTUR KOLOM (relatif dari Q=1):
             *   Pos 1  = Q  = NO               (rowspan 3)
             *   Pos 2  = R  = (no label)
             *   Pos 3  = S  = PERUNTUKAN        (rowspan 3)
             *   Pos 4  = T  = %                 (rowspan 3)
             *   Pos 5  = U  = KASASI PDT... BIAYA
             *   Pos 6  = V  = JML
             *   Pos 7  = W  = SUB TOTAL
             *   Pos 8  = X  = KASASI TUN... BIAYA
             *   ... dst tiap kelompok 3 kolom
             *   Pos 35 = AY = TOTAL             (rowspan 3)
             *
             * BARIS:
             *   Row 4-5  = header level 1 (merged)
             *   Row 6    = sub-header (BIAYA|JML|SUB TOTAL per kelompok)
             *   Row 7    = (merged dari baris 6, hasil rowspan)
             *   Row 8-38 = data
             *
             * Header baris: 4, 5, 6 (row 7 adalah covered dari merge row 6)
             */
            $HEADER_START = 4;
            $HEADER_END   = 6;  // baris 7 adalah covered cell dari rowspan baris 6
            $DATA_END     = 38;

            // Mapping kolom Excel (letter) ke index numerik relatif (mulai Q=17)
            $startColNum = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString('Q');

            // Kolom-kolom yang termasuk "numerik" untuk diformat (kolom U ke AX, yaitu index 21-50)
            $numericStartCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString('U');
            $numericEndCol   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString('AX');
            $totalCol        = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString('AY');

            // Kolom yang alignment-nya teks (kiri/center)
            // Q=17(NO,center), R=18(hidden label,left), S=19(PERUNTUKAN,left), T=20(%,center)
            $colQ = 17; $colR = 18; $colS = 19; $colT = 20;

            // Baris yang dianggap "jumlah/total" untuk styling bold + bg
            $isJumlahRow = function(int $rowNum, array $cells) {
                // Baris 31 di Excel (JUMLAH) - tidak ada di output karena Q51:Q53 di baris berbeda
                // Cek apakah peruntukan mengandung JUMLAH
                foreach ($cells as $cell) {
                    $colLtr = preg_replace('/\d+/', '', $cell['reference']);
                    $colNum = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($colLtr);
                    if ($colNum === 19 && stripos($cell['value'], 'JUMLAH') !== false) return true;
                }
                return false;
            };

            // Baris yang merupakan "100%" total akhir
            $is100Row = function(int $rowNum, array $cells) {
                foreach ($cells as $cell) {
                    $colLtr = preg_replace('/\d+/', '', $cell['reference']);
                    $colNum = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($colLtr);
                    if ($colNum === 20 && $cell['value'] === '100%') return true;
                }
                return false;
            };
        @endphp

        {{-- ─── Title ─── --}}
        <div class="mb-5 text-center">
            <p class="text-sm font-bold uppercase tracking-wide text-neutral-900 dark:text-neutral-100">
                {{ $title1 ?: 'REKAPITULASI BIAYA PENYELESAIAN PERKARA YANG DIPUTUS' }}
            </p>
            <p class="text-sm font-bold uppercase tracking-wide text-neutral-800 dark:text-neutral-200 mt-0.5">
                {{ $title2 ?: 'YANG USIANYA KURANG DARI 120 HARI SEJAK REGISTER PERKARA MASUK' }}
            </p>
            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Distribusi Biaya Per Peruntukan</p>
        </div>

        {{-- ─── Table ─── --}}
        <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs border-collapse" style="min-width: 2200px;">
                    <colgroup>
                        {{-- Q: NO --}}            <col style="width: 35px;">
                        {{-- R: (label no) --}}    <col style="width: 25px;">
                        {{-- S: PERUNTUKAN --}}    <col style="width: 300px;">
                        {{-- T: % --}}             <col style="width: 45px;">
                        {{-- KASASI PDT,PDTSUS,AG: BIAYA|JML|SUB TOTAL --}}
                        <col style="width: 80px;"><col style="width: 55px;"><col style="width: 110px;">
                        {{-- KASASI TUN: BIAYA|JML|SUB TOTAL --}}
                        <col style="width: 80px;"><col style="width: 45px;"><col style="width: 90px;">
                        {{-- KASASI NIAGA: BIAYA|JML|SUB TOTAL --}}
                        <col style="width: 90px;"><col style="width: 40px;"><col style="width: 90px;">
                        {{-- PK: BIAYA|JML|SUB TOTAL --}}
                        <col style="width: 90px;"><col style="width: 45px;"><col style="width: 105px;">
                        {{-- P-HUM: BIAYA|JML|SUB TOTAL --}}
                        <col style="width: 80px;"><col style="width: 40px;"><col style="width: 85px;">
                        {{-- PK-PAJAK: BIAYA|JML|SUB TOTAL --}}
                        <col style="width: 90px;"><col style="width: 55px;"><col style="width: 120px;">
                        {{-- PK-PDT KHUSUS: BIAYA|JML|SUB TOTAL --}}
                        <col style="width: 90px;"><col style="width: 40px;"><col style="width: 90px;">
                        {{-- PK-AGAMA: BIAYA|JML|SUB TOTAL --}}
                        <col style="width: 90px;"><col style="width: 40px;"><col style="width: 95px;">
                        {{-- PK-TUN: BIAYA|JML|SUB TOTAL --}}
                        <col style="width: 90px;"><col style="width: 40px;"><col style="width: 95px;">
                        {{-- PK NIAGA: BIAYA|JML|SUB TOTAL --}}
                        <col style="width: 90px;"><col style="width: 40px;"><col style="width: 95px;">
                        {{-- AY: TOTAL --}}         <col style="width: 130px;">
                    </colgroup>
                    <tbody>
                        @foreach($report['rows'] as $row)
                            @php
                                $rowNum = $row['number'];
                                $cells  = $row['cells'];

                                $isHeaderRow  = ($rowNum >= $HEADER_START && $rowNum <= $HEADER_END);
                                $isJumlah     = !$isHeaderRow && $isJumlahRow($rowNum, $cells);
                                $isTotal100   = !$isHeaderRow && !$isJumlah && $is100Row($rowNum, $cells);

                                // Warna baris
                                if ($isHeaderRow) {
                                    $trBg = 'bg-sky-100 dark:bg-sky-900/40';
                                } elseif ($isJumlah) {
                                    $trBg = 'bg-neutral-100 dark:bg-neutral-800/60';
                                } elseif ($isTotal100) {
                                    $trBg = 'bg-amber-50 dark:bg-amber-900/20';
                                } else {
                                    $trBg = 'hover:bg-blue-50/30 dark:hover:bg-neutral-800/30';
                                }
                            @endphp
                            <tr class="border-b border-neutral-200 dark:border-neutral-800 {{ $trBg }}">
                                @foreach($cells as $cell)
                                    @php
                                        $val    = $cell['value'];
                                        $colLtr = preg_replace('/\d+/', '', $cell['reference']);
                                        $colNum = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($colLtr);

                                        // ── Alignment ──
                                        if ($isHeaderRow) {
                                            $align = 'text-center';
                                        } elseif ($colNum === $colQ) {
                                            // NO: center
                                            $align = 'text-center';
                                        } elseif ($colNum === $colR) {
                                            // label (a., b., dst): center
                                            $align = 'text-center';
                                        } elseif ($colNum === $colS) {
                                            // PERUNTUKAN: left
                                            $align = 'text-left';
                                        } elseif ($colNum === $colT) {
                                            // %: center
                                            $align = 'text-center';
                                        } else {
                                            // Kolom numerik: right
                                            $align = 'text-right';
                                        }

                                        // ── Format nilai numerik ──
                                        $display = $val;
                                        // Kolom numerik: U–AX dan AY (total)
                                        $isNumericCol = ($colNum >= $numericStartCol && $colNum <= $totalCol);

                                        if (!$isHeaderRow && $isNumericCol) {
                                            // Jika nilai sudah berformat (misal "1,912" atau "19,120,000")
                                            // konversi ke numerik dulu lalu format ulang
                                            $numericVal = str_replace([',', '.'], '', $val);
                                            if (is_numeric($numericVal) && (int)$numericVal !== 0) {
                                                $display = number_format((float)$numericVal, 0, ',', '.');
                                            } elseif ($val === '-' || $val === '') {
                                                $display = '-';
                                            } elseif (is_numeric($val) && (float)$val == 0) {
                                                $display = '-';
                                            }
                                        }

                                        // ── CSS classes ──
                                        $tdClass = 'border border-neutral-200 dark:border-neutral-700 px-1.5 py-1 text-xs ' . $align;

                                        if ($isHeaderRow) {
                                            $tdClass .= ' font-bold text-neutral-800 dark:text-neutral-200';
                                        } elseif ($isJumlah || $isTotal100) {
                                            $tdClass .= ' font-bold text-neutral-900 dark:text-neutral-100';
                                        } else {
                                            $tdClass .= ' text-neutral-800 dark:text-neutral-200';
                                        }
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


    @else
        {{-- Empty state --}}
        <div class="text-center py-24">
            <svg class="w-16 h-16 text-neutral-300 dark:text-neutral-700 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-neutral-500 dark:text-neutral-400 text-lg font-medium">Belum ada data</p>
            <p class="text-neutral-400 dark:text-neutral-500 text-sm mt-2">Harap upload file Excel terlebih dahulu di Dashboard</p>
            <a href="{{ route('dashboard') }}"
               class="inline-block mt-6 px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200">
                Ke Dashboard
            </a>
        </div>
    @endif

</div>
@endsection
