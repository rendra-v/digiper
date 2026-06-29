@extends('layout')

@section('title', 'Rekap Keseluruhan')

@section('content')
<div class="min-h-screen">

    {{-- ─── Header Page ─── --}}
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="text-4xl font-semibold tracking-tight mb-3">Rekap Keseluruhan</h2>
            <p class="text-neutral-500 dark:text-neutral-400">
                Rekapitulasi biaya penyelesaian perkara
                @if($fileName)
                    — <span class="font-medium text-neutral-700 dark:text-neutral-300">{{ $fileName }}</span>
                @endif
            </p>
        </div>
        <div class="flex flex-col items-end gap-2">
            <div class="flex items-end gap-3">

                {{-- Dropdown: Lihat Halaman Lain --}}
                <div>
                    <label for="nav-page-select-rekap" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                        Lihat halaman lain
                    </label>
                    <div class="relative">
                        <select id="nav-page-select-rekap"
                            onchange="if(this.value) window.location.href = this.value"
                            class="appearance-none rounded-lg border border-emerald-300 dark:border-emerald-700 bg-white dark:bg-neutral-950 px-4 py-2.5 pr-10 text-sm font-medium text-neutral-900 dark:text-neutral-100 shadow-sm transition-colors duration-200 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 cursor-pointer">
                            <option value="" disabled selected>— Pilih halaman —</option>
                            <option value="{{ route('data-print') }}">📄&nbsp; Data Print Perkara</option>
                            <option value="{{ route('sheet-cek') }}">📋&nbsp; Sheet Cek</option>
                            <option value="{{ route('rekap-keseluruhan-2') }}">📈&nbsp; Rekap Keseluruhan 2</option>
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
                <a href="{{ route('rekap-keseluruhan.print') }}"
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
            <a href="{{ route('rekap-keseluruhan-2') }}"
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

    @if(!$error && isset($report))
        @php
            $rows = collect($report['rows'])->keyBy('number');
            $getCellVal = function (int $rowNum, string $ref, string $default = '') use ($rows) {
                $row  = $rows->get($rowNum);
                $cell = $row ? collect($row['cells'] ?? [])->firstWhere('reference', $ref) : null;
                $v    = $cell['value'] ?? '';
                return ($v !== '' && $v !== null) ? $v : $default;
            };

            // Baris header Excel (dengan merged cells) ada di baris 4–8
            // Baris data ada di 9–34
            // Baris footer/ttd ada di 35+  → tidak dirender di tabel
            $HEADER_END  = 8;   // baris terakhir header Excel
            $DATA_END    = 34;  // baris terakhir data tabel

            $romanNumerals = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII'];
        @endphp

        {{-- ─── Title ─── --}}
        <div class="mb-2 text-center">
            <p class="text-sm font-bold uppercase tracking-wide text-neutral-900 dark:text-neutral-100">
                {{ $title1 ?: 'REKAPITULASI BIAYA PENYELESAIAN PERKARA YANG DIPUTUS' }}
            </p>
            <p class="text-sm font-bold uppercase tracking-wide text-neutral-800 dark:text-neutral-200 mt-0.5">
                {{ $title2 ?: 'YANG USIANYA KURANG DARI 120 HARI SEJAK REGISTER PERKARA MASUK' }}
            </p>
        </div>

        {{-- ─── Table ─── --}}

        <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                {{--
                    Pendekatan: render SEMUA baris Excel 4–34 langsung dari data report.
                    Merged cells (rowspan/colspan) sudah diekstrak oleh buildRekapKeseluruhanReport().
                    Baris 4–HEADER_END diperlakukan sebagai header (warna biru, bold, center).
                    Baris (HEADER_END+1)–DATA_END adalah baris data.
                    Tidak ada <thead> hardcoded — biarkan Excel yang menentukan struktur merge.
                --}}
                <table class="w-full text-xs border-collapse" style="min-width: 1080px;">
                    <colgroup>
                        {{-- Col A: No --}}
                        <col style="width: 3%;">
                        {{-- Col B: Jenis Perkara --}}
                        <col style="width: 15%;">
                        {{-- Col C: Klasifikasi --}}
                        <col style="width: 7%;">
                        {{-- Col D-H: KASASI (5 kolom) --}}
                        <col style="width: 5%;">
                        <col style="width: 5%;">
                        <col style="width: 5%;">
                        <col style="width: 7.5%;">
                        <col style="width: 5.5%;">
                        {{-- Col I-M: PENINJAUAN KEMBALI (5 kolom) --}}
                        <col style="width: 5%;">
                        <col style="width: 5%;">
                        <col style="width: 5%;">
                        <col style="width: 7.5%;">
                        <col style="width: 5.5%;">
                        {{-- Col N: Total --}}
                        <col style="width: 7%;">
                    </colgroup>
                    <tbody>
                        @foreach($report['rows'] as $row)
                            @php
                                $rowNum = $row['number'];

                                // Skip baris judul (1-3) dan baris ttd (35+)
                                if ($rowNum < 4 || $rowNum > $DATA_END) continue;

                                $isHeaderRow = ($rowNum <= $HEADER_END);

                                // Skip baris kosong berdasarkan raw value (dari controller)
                                if (!$isHeaderRow && !($row['hasData'] ?? true)) continue;

                                $firstCell = collect($row['cells'])->first();
                                $firstVal  = trim($firstCell['value'] ?? '');

                                $isCategoryRow = !$isHeaderRow && in_array($firstVal, $romanNumerals);
                                $isTotalRow    = !$isHeaderRow
                                    && (stripos($firstVal, 'TOTAL') !== false
                                     || stripos($firstVal, 'JUMLAH') !== false);

                                // Warna baris
                                if ($isHeaderRow) {
                                    $trBg = 'bg-sky-100 dark:bg-sky-900/40';
                                } elseif ($isCategoryRow) {
                                    $trBg = 'bg-cyan-50 dark:bg-cyan-900/20';
                                } elseif ($isTotalRow) {
                                    $trBg = 'bg-neutral-100 dark:bg-neutral-800/60';
                                } else {
                                    $trBg = 'hover:bg-blue-50/30 dark:hover:bg-neutral-800/30';
                                }
                            @endphp
                            <tr class="border-b border-neutral-200 dark:border-neutral-800 {{ $trBg }}">
                                @foreach($row['cells'] as $cell)
                                    @php
                                        $val    = $cell['value'];
                                        $colLtr = preg_replace('/\d+/', '', $cell['reference']);
                                        $colNum = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($colLtr);

                                        // ── Alignment ──
                                        if ($isHeaderRow) {
                                            $align = 'text-center';
                                        } elseif ($colNum === 1) {
                                            $align = 'text-center';
                                        } elseif ($colNum === 2) {
                                            $align = 'text-left';
                                        } elseif ($colNum === 3) {
                                            $align = 'text-center';
                                        } else {
                                            $align = 'text-right';
                                        }

                                        // ── Format nilai ──
                                        $display = $val;
                                        if (!$isHeaderRow && is_numeric($val) && (float)$val != 0 && $colNum >= 4) {
                                            $display = number_format((float)$val, 0, ',', '.');
                                        }
                                        // Tampilkan '-' untuk angka 0 / kosong di kolom numerik (bukan header)
                                        if (!$isHeaderRow
                                            && $colNum >= 4
                                            && ($display === '' || $display === null || (string)$display === '0' || (float)($val ?? 0) == 0)
                                        ) {
                                            $display = '-';
                                        }

                                        // ── CSS classes ──
                                        $tdClass = 'border border-neutral-200 dark:border-neutral-700 px-2 py-1.5 text-xs ' . $align;

                                        if ($isHeaderRow) {
                                            $tdClass .= ' font-bold text-neutral-800 dark:text-neutral-200';
                                        } elseif ($isCategoryRow || $isTotalRow) {
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

        {{-- ══════════════════════════════════════════════════
             SIGNATURE AREA — format dokumen resmi MA
             ══════════════════════════════════════════════════ --}}
        <div class="mt-12 mb-16">

            {{-- Tanggal → rata kanan --}}
            <div class="flex justify-end mb-8">
                <p class="text-sm font-medium text-neutral-800 dark:text-neutral-200">
                    {{ $recapDate ?: 'Jakarta, 05 Maret 2026' }}
                </p>
            </div>

            {{-- 3 kolom tanda tangan --}}
            <div class="grid grid-cols-3">

                {{-- Kiri --}}
                <div class="flex flex-col items-start">
                    <p class="text-xs font-bold uppercase tracking-wide text-neutral-700 dark:text-neutral-300 leading-snug">
                        Kuasa Pengelola Biaya Proses
                    </p>
                    <div class="mt-20">
                        <p class="text-sm font-bold text-neutral-900 dark:text-neutral-100 underline underline-offset-4 decoration-2">
                            {{ $getCellVal(40, 'B40', 'ASEP NURSOBAH, S.Ag., M.H.') }}
                        </p>
                    </div>
                </div>

                {{-- Tengah --}}
                <div class="flex flex-col items-center text-center">
                    <p class="text-xs font-bold uppercase tracking-wide text-neutral-700 dark:text-neutral-300 leading-snug">
                        Petugas Pembuat Komitmen<br>Biaya Proses
                    </p>
                    <div class="mt-20">
                        <p class="text-sm font-bold text-neutral-900 dark:text-neutral-100 underline underline-offset-4 decoration-2">
                            {{ $getCellVal(40, 'F40', 'ST. KRIS NUGROHO, S.H., M.H.') }}
                        </p>
                    </div>
                </div>

                {{-- Kanan --}}
                <div class="flex flex-col items-end text-right">
                    <p class="text-xs font-bold uppercase tracking-wide text-neutral-700 dark:text-neutral-300 leading-snug">
                        Bendahara Biaya Proses
                    </p>
                    <div class="mt-20">
                        <p class="text-sm font-bold text-neutral-900 dark:text-neutral-100 underline underline-offset-4 decoration-2">
                            {{ $getCellVal(40, 'L40', 'FARIDA,SH') }}
                        </p>
                    </div>
                </div>

            </div>

            {{-- Mengetahui — Panitera MA-RI (tengah) --}}
            <div class="mt-14 flex flex-col items-center text-center">
                <p class="text-xs font-bold uppercase tracking-wide text-neutral-700 dark:text-neutral-300">
                    Mengetahui,
                </p>
                <p class="text-xs font-bold uppercase tracking-wide text-neutral-700 dark:text-neutral-300 mt-0.5">
                    Panitera MA-RI
                </p>
                <div class="mt-20">
                    <p class="text-sm font-bold text-neutral-900 dark:text-neutral-100 underline underline-offset-4 decoration-2">
                        {{ $getCellVal(49, 'F49', 'Dr. SUDHARMAWATININGSIH, S.H., M.Hum.') }}
                    </p>
                </div>
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
