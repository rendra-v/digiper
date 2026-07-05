@extends('layout')

@section('title', 'Honorarium Biaya Perkara')

@section('content')
<div x-data="honorariumApp()" class="min-h-screen">

    {{-- ─── Header ─── --}}
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="text-4xl font-semibold tracking-tight mb-3">Honorarium Biaya Perkara</h2>
            <p class="text-neutral-500 dark:text-neutral-400">
                Daftar honorarium biaya penyelesaian perkara per kamar
                @if($fileName)
                    — <span class="font-medium text-neutral-700 dark:text-neutral-300">{{ $fileName }}</span>
                @endif
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('dashboard') }}"
               class="px-4 py-2 text-sm bg-neutral-200 dark:bg-neutral-800 hover:bg-neutral-300 dark:hover:bg-neutral-700 text-neutral-900 dark:text-neutral-100 rounded-lg transition-colors duration-200">
                Dashboard
            </a>
        </div>
    </div>

    {{-- ─── Error ─── --}}
    @if($error)
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/50 rounded-lg">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm text-red-700 dark:text-red-400">{{ $error }}</p>
            </div>
        </div>
    @endif

    @if(!$error && count($sheets) > 0)

        {{-- ─── Card Utama ─── --}}
        <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl overflow-hidden shadow-sm">

            {{-- ── Toolbar: Tab Sheet + Navigasi + Filter ── --}}
            <div class="border-b border-neutral-200 dark:border-neutral-800 p-4 space-y-4">

                {{-- Baris 1: Sheet selector + Nav + Print --}}
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end w-full lg:flex-1">

                        {{-- Tab pilih sheet --}}
                        @if(count($sheets) > 1)
                        <div class="flex-1 min-w-0">

                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Pilih sheet honorarium
                            </label>
                            <div class="relative">
                                <select x-model.number="activeSheet"
                                    @change="filterKuitansi = 0"
                                    class="w-full appearance-none rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-950 px-4 py-2.5 pr-11 text-sm font-medium text-neutral-900 dark:text-neutral-100 shadow-sm transition-colors duration-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                                    @foreach($sheets as $idx => $sheet)
                                        <option value="{{ $idx }}">{{ $sheet['sheetName'] }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-neutral-500 dark:text-neutral-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 9 6 6 6-6"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="hidden sm:block self-stretch w-px bg-neutral-200 dark:bg-neutral-700 mb-0.5"></div>
                        @endif

                        {{-- Dropdown: Lihat Halaman Lain --}}
                        <div class="{{ count($sheets) > 1 ? 'sm:w-56' : 'flex-1 max-w-xs' }}">
                            <label for="nav-page-select-honor"
                                class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Lihat halaman lain
                            </label>
                            <div class="relative">
                                <select id="nav-page-select-honor"
                                    onchange="if(this.value) window.location.href = this.value"
                                    class="w-full appearance-none rounded-lg border border-emerald-300 dark:border-emerald-700 bg-white dark:bg-neutral-950 px-4 py-2.5 pr-11 text-sm font-medium text-neutral-900 dark:text-neutral-100 shadow-sm transition-colors duration-200 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 cursor-pointer">
                                    <option value="" disabled selected>— Pilih halaman —</option>
                                    <option value="{{ route('data-print') }}">📄&nbsp; Data Print Perkara</option>
                                    <option value="{{ route('sheet-cek') }}">📋&nbsp; Sheet Cek</option>
                                    <option value="{{ route('rekap-keseluruhan') }}">📊&nbsp; Rekap Keseluruhan</option>
                                    <option value="{{ route('rekap-keseluruhan-2') }}">📈&nbsp; Rekap Keseluruhan 2</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-emerald-600 dark:text-emerald-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Print PDF --}}
                    <a href="{{ route('honorarium.print') }}" target="_blank" rel="noopener"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors duration-200 hover:bg-blue-700 flex-shrink-0">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 9V2h12v7"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18H5a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 14h8v8H8z"/>
                        </svg>
                        Print PDF
                    </a>

                </div>

                {{-- ─── Baris 2: Filter Kuitansi ─── --}}
                <div class="flex flex-col sm:flex-row sm:items-center gap-3">

                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-neutral-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                        </svg>
                        <span class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Filter Kuitansi:</span>
                    </div>

                    <div class="flex flex-wrap gap-2">

                        {{-- Tombol: Semua --}}
                        <button type="button"
                            @click="filterKuitansi = 0"
                            :class="filterKuitansi === 0
                                ? 'bg-neutral-800 dark:bg-neutral-100 text-white dark:text-neutral-900 ring-2 ring-neutral-800 dark:ring-neutral-100'
                                : 'bg-white dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 border border-neutral-300 dark:border-neutral-600 hover:bg-neutral-100 dark:hover:bg-neutral-700'"
                            class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-semibold transition-all duration-150 cursor-pointer">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            Semua Data
                        </button>

                        {{-- Tombol: Kuitansi Tim (Kode 1) --}}
                        <button type="button"
                            @click="filterKuitansi = 1"
                            title="Hakim Agung · Panmud/Askor · Asisten · Operator"
                            :class="filterKuitansi === 1
                                ? 'bg-blue-600 text-white ring-2 ring-blue-600'
                                : 'bg-white dark:bg-neutral-800 text-blue-700 dark:text-blue-300 border border-blue-300 dark:border-blue-700 hover:bg-blue-50 dark:hover:bg-blue-950/30'"
                            class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-semibold transition-all duration-150 cursor-pointer">
                            <span class="inline-flex items-center justify-center w-4 h-4 rounded-full text-[10px] font-bold"
                                :class="filterKuitansi === 1 ? 'bg-white/30 text-white' : 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300'">
                                1
                            </span>
                            Kuitansi Tim
                        </button>

                        {{-- Tombol: Honor Kepaniteraan (Kode 2) --}}
                        <button type="button"
                            @click="filterKuitansi = 2"
                            title="Panitera Pengganti · Juru Sita · dan jabatan kepaniteraan lainnya"
                            :class="filterKuitansi === 2
                                ? 'bg-emerald-600 text-white ring-2 ring-emerald-600'
                                : 'bg-white dark:bg-neutral-800 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700 hover:bg-emerald-50 dark:hover:bg-emerald-950/30'"
                            class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-semibold transition-all duration-150 cursor-pointer">
                            <span class="inline-flex items-center justify-center w-4 h-4 rounded-full text-[10px] font-bold"
                                :class="filterKuitansi === 2 ? 'bg-white/30 text-white' : 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300'">
                                2
                            </span>
                            Kuitansi Kepaniteraan
                        </button>

                        {{-- Divider --}}
                        <span class="self-center w-px h-5 bg-neutral-300 dark:bg-neutral-600 mx-1"></span>

                        {{-- Tombol: Operator --}}
                        <button type="button"
                            @click="filterKuitansi = 'operator'"
                            title="Filter hanya Operator"
                            :class="filterKuitansi === 'operator'
                                ? 'bg-violet-600 text-white ring-2 ring-violet-600'
                                : 'bg-white dark:bg-neutral-800 text-violet-700 dark:text-violet-300 border border-violet-300 dark:border-violet-700 hover:bg-violet-50 dark:hover:bg-violet-950/30'"
                            class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-semibold transition-all duration-150 cursor-pointer">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Operator
                        </button>

                        {{-- Tombol: Panmud --}}
                        <button type="button"
                            @click="filterKuitansi = 'panmud'"
                            title="Filter hanya Panmud / Askor / Panitera Muda"
                            :class="filterKuitansi === 'panmud'
                                ? 'bg-orange-500 text-white ring-2 ring-orange-500'
                                : 'bg-white dark:bg-neutral-800 text-orange-700 dark:text-orange-300 border border-orange-300 dark:border-orange-700 hover:bg-orange-50 dark:hover:bg-orange-950/30'"
                            class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-semibold transition-all duration-150 cursor-pointer">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Panmud
                        </button>

                    </div>

                    {{-- Info jumlah baris aktif --}}
                    @foreach($sheets as $idx => $sheet)
                    @php
                        $cT    = count(array_filter($sheet['rows'], fn($r) => ($r['_kode_kuitansi'] ?? 0) === 1));
                        $cP    = count(array_filter($sheet['rows'], fn($r) => ($r['_kode_kuitansi'] ?? 0) === 2));
                        $cA    = $cT + $cP;
                        $cOpr  = count(array_filter($sheet['rows'], fn($r) => ($r['_jabatan_sub'] ?? '') === 'operator'));
                        $cPanm = count(array_filter($sheet['rows'], fn($r) => ($r['_jabatan_sub'] ?? '') === 'panmud'));
                    @endphp
                    <div x-show="activeSheet === {{ $idx }}" x-cloak class="ml-auto">
                        <span class="text-xs text-neutral-500 dark:text-neutral-400">
                            <span x-show="filterKuitansi === 0">Semua: <strong class="text-neutral-700 dark:text-neutral-200">{{ $cA }}</strong> baris</span>
                            <span x-show="filterKuitansi === 1" x-cloak>Tim: <strong class="text-blue-700 dark:text-blue-300">{{ $cT }}</strong> baris</span>
                            <span x-show="filterKuitansi === 2" x-cloak>Kepaniteraan: <strong class="text-emerald-700 dark:text-emerald-300">{{ $cP }}</strong> baris</span>
                            <span x-show="filterKuitansi === 'operator'" x-cloak>Operator: <strong class="text-violet-700 dark:text-violet-300">{{ $cOpr }}</strong> baris</span>
                            <span x-show="filterKuitansi === 'panmud'" x-cloak>Panmud: <strong class="text-orange-700 dark:text-orange-300">{{ $cPanm }}</strong> baris</span>
                        </span>
                    </div>
                    @endforeach

                </div>

                {{-- Legend kode --}}
                <div class="flex flex-wrap gap-3 pt-2 border-t border-neutral-100 dark:border-neutral-800">
                    <span class="text-xs text-neutral-400 dark:text-neutral-500 self-center">Kode:</span>
                    {{-- Kode 1: Tim --}}
                    <div class="flex items-start gap-1.5">
                        <span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-blue-100 dark:bg-blue-900/50 text-[10px] font-bold text-blue-700 dark:text-blue-300 mt-0.5 flex-shrink-0">1</span>
                        <div class="text-xs text-blue-700 dark:text-blue-300">
                            <span class="font-semibold">Tim</span>
                            <span class="text-blue-500 dark:text-blue-400"> — </span>
                            <span class="inline-flex flex-wrap gap-1">
                                <span class="px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-[10px] font-medium">Hakim Agung</span>
                                <span class="px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-[10px] font-medium">Panmud / Askor</span>
                                <span class="px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-[10px] font-medium">Asisten</span>
                                <span class="px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-[10px] font-medium">Operator</span>
                            </span>
                        </div>
                    </div>
                    {{-- Kode 2: Kepaniteraan --}}
                    <div class="flex items-center gap-1.5">
                        <span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-[10px] font-bold text-emerald-700 dark:text-emerald-300 flex-shrink-0">2</span>
                        <span class="text-xs text-emerald-700 dark:text-emerald-300">
                            <span class="font-semibold">Kepaniteraan</span>
                            <span class="text-emerald-500 dark:text-emerald-400"> — semua jabatan selain Tim</span>
                        </span>
                    </div>
                </div>

            </div>

            {{-- ── Konten per Sheet ── --}}
            @foreach($sheets as $idx => $sheet)
            @php
                // Hitung jumlah Tim/Kepaniteraan untuk counter di JS
                $cntTimSheet  = count(array_filter($sheet['rows'], fn($r) => ($r['_kode_kuitansi'] ?? 0) === 1));
                $cntPaneSheet = count(array_filter($sheet['rows'], fn($r) => ($r['_kode_kuitansi'] ?? 0) === 2));
                $cntAllSheet  = $cntTimSheet + $cntPaneSheet;
            @endphp
            <div x-show="activeSheet === {{ $idx }}" x-cloak>

                {{-- Judul dari Excel --}}
                <div class="px-6 py-5 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/20 dark:to-indigo-950/20 border-b border-neutral-200 dark:border-neutral-800">
                    @php $titleParts = array_filter(array_map('trim', explode("\n", $sheet['title']))); @endphp
                    @if(count($titleParts))
                        @foreach($titleParts as $line)
                            <p class="text-sm font-bold text-center uppercase tracking-wide text-neutral-900 dark:text-neutral-100 leading-relaxed">
                                {{ $line }}
                            </p>
                        @endforeach
                    @else
                        <p class="text-sm font-bold text-center uppercase tracking-wide text-neutral-900 dark:text-neutral-100">
                            HONORARIUM BIAYA PENYELESAIAN PERKARA
                        </p>
                    @endif
                </div>

                {{-- Tabel dengan Filter Reaktif --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border-collapse" style="min-width: 700px;">
                        <thead class="bg-blue-600 text-white sticky top-0 z-10 shadow-sm">
                            <tr>
                                {{-- Kolom badge kode --}}
                                <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wide border-r border-blue-500/40 w-10 min-w-[48px]">
                                    Kode
                                </th>
                                @foreach($sheet['headers'] as $colIdx => $headerName)
                                    @php
                                        $upper       = strtoupper(trim($headerName));
                                        $isWide      = in_array($upper, ['NAMA', 'NAMA LENGKAP', 'JABATAN', 'URAIAN']);
                                        $isExtraWide = str_contains($upper, 'NAMA') && str_contains($upper, 'PERKARA');
                                        $isNarrow    = in_array($upper, ['NO', 'NO.', 'NOMOR']);
                                        $isTtd       = str_contains($upper, 'TANDA') || str_contains($upper, 'TTD');
                                        $isNumHdr    = in_array($upper, ['BIAYA','JUMLAH BIAYA','PPH 15%','PPH 5%','NETTO','PPH','PAJAK','TOTAL'])
                                                       || str_contains($upper, 'BIAYA')
                                                       || str_contains($upper, 'NETTO')
                                                       || str_contains($upper, 'PPH');
                                    @endphp
                                    <th class="px-3 py-3 text-center text-xs font-bold uppercase tracking-wide border-r border-blue-500/40 last:border-r-0 whitespace-nowrap
                                        {{ $isNarrow    ? 'w-10 min-w-[40px]'   : '' }}
                                        {{ $isExtraWide ? 'min-w-[200px]'        : '' }}
                                        {{ $isWide && !$isExtraWide ? 'min-w-[150px]' : '' }}
                                        {{ $isTtd       ? 'min-w-[90px]'         : '' }}
                                        {{ $isNumHdr    ? 'min-w-[130px]'        : '' }}
                                        {{ !$isNarrow && !$isExtraWide && !$isWide && !$isTtd && !$isNumHdr ? 'min-w-[100px]' : '' }}">
                                        {{ $headerName }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                            @php $rowNum = 0; $lastSectionTitle = null; @endphp
                            @foreach($sheet['rows'] as $rIdx => $row)
                                @php
                                    $kode         = $row['_kode_kuitansi'] ?? 0;
                                    $jabSub       = $row['_jabatan_sub'] ?? '';
                                    $sectionTitle = $row['_section_title'] ?? '';
                                    $firstKey     = collect(array_keys($row))->first(fn($k) => !str_starts_with($k, '_'));
                                    $firstVal     = trim((string)($row[$firstKey] ?? ''));
                                    $isSummary    = !is_numeric($firstVal) && $firstVal !== '' && strtoupper($firstVal) !== 'NO';
                                    if (!$isSummary) $rowNum++;

                                    // Skip baris kosong (selain field internal _kode_kuitansi)
                                    $rowIsEmpty = collect($row)->every(function($v, $k) {
                                        if (str_starts_with((string)$k, '_')) return true;
                                        $s = trim((string)($v ?? ''));
                                        return $s === '' || $s === '0' || (is_numeric($s) && (float)$s == 0);
                                    });

                                    // Tampilkan separator section jika judul berubah
                                    $showSectionSeparator = $sectionTitle !== '' && $sectionTitle !== $lastSectionTitle;
                                    if ($showSectionSeparator) $lastSectionTitle = $sectionTitle;
                                @endphp
                                @if($rowIsEmpty) @continue @endif

                                {{-- Section separator row --}}
                                @if($showSectionSeparator)
                                <tr class="bg-gradient-to-r from-blue-600 to-blue-700 dark:from-blue-700 dark:to-blue-800" data-kode="0" data-sub="">
                                    <td class="px-3 py-2 text-center" colspan="1">
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-white/20 text-white text-[9px] font-bold">§</span>
                                    </td>
                                    <td class="px-3 py-2 text-xs font-bold text-white uppercase tracking-wide" colspan="{{ count($sheet['headers']) }}">
                                        {{ $sectionTitle }}
                                    </td>
                                </tr>
                                @endif

                                {{--
                                    x-show: filter oleh kode (1/2) atau sub-jabatan ('operator'/'panmud').
                                    data-kode: kode kuitansi (0=summary, 1=tim, 2=kepaniteraan)
                                    data-sub : sub-jabatan ('hakim','panmud','asisten','operator','')
                                --}}
                                <tr
                                    data-kode="{{ $isSummary ? 0 : $kode }}"
                                    data-sub="{{ $isSummary ? '' : $jabSub }}"
                                    x-show="
                                        filterKuitansi === 0
                                        || $el.dataset.kode == '0'
                                        || (filterKuitansi === 1 && $el.dataset.kode == '1')
                                        || (filterKuitansi === 2 && $el.dataset.kode == '2')
                                        || (filterKuitansi === 'operator' && $el.dataset.sub === 'operator')
                                        || (filterKuitansi === 'panmud'   && $el.dataset.sub === 'panmud')
                                    "
                                    x-transition:enter="transition-opacity duration-150"
                                    x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100"
                                    class="transition-colors duration-100
                                        {{ $isSummary
                                            ? 'bg-blue-50/80 dark:bg-blue-950/20'
                                            : ($rowNum % 2 === 0
                                                ? 'bg-neutral-50/80 hover:bg-blue-50/50 dark:bg-neutral-800/20 dark:hover:bg-blue-900/10'
                                                : 'bg-white dark:bg-neutral-900 hover:bg-blue-50/50 dark:hover:bg-blue-900/10') }}">

                                    {{-- Badge kode kuitansi + sub-jabatan --}}
                                    <td class="px-2 py-2.5 text-center align-middle border-r border-neutral-100 dark:border-neutral-800/50 w-14 min-w-[56px]">
                                        @if(!$isSummary && $kode > 0)
                                            @if($kode === 1)
                                                {{-- Tim: tampilkan badge sub-jabatan --}}
                                                @if($jabSub === 'hakim')
                                                    <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 whitespace-nowrap" title="Tim – Hakim">Hakim</span>
                                                @elseif($jabSub === 'panmud')
                                                    <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-orange-100 dark:bg-orange-900/50 text-orange-700 dark:text-orange-300 whitespace-nowrap" title="Tim – Panmud/Askor">Panmud</span>
                                                @elseif($jabSub === 'operator')
                                                    <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-violet-100 dark:bg-violet-900/50 text-violet-700 dark:text-violet-300 whitespace-nowrap" title="Tim – Operator">Operator</span>
                                                @elseif($jabSub === 'asisten')
                                                    <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-sky-100 dark:bg-sky-900/50 text-sky-700 dark:text-sky-300 whitespace-nowrap" title="Tim – Asisten">Asisten</span>
                                                @else
                                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/50 text-xs font-bold text-blue-700 dark:text-blue-300" title="Kuitansi Tim">1</span>
                                                @endif
                                            @else
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-xs font-bold text-emerald-700 dark:text-emerald-300" title="Kuitansi Kepaniteraan">2</span>
                                            @endif
                                        @endif
                                    </td>

                                    @foreach($sheet['headers'] as $colIdx => $headerName)
                                        @php
                                            $val   = $row[$headerName] ?? '';
                                            $upper = strtoupper(trim($headerName));

                                            $isNumericCol = in_array($upper, ['BIAYA','JUMLAH BIAYA','PPH 15%','PPH 5%','NETTO','PPH','PAJAK','TOTAL'])
                                                || str_contains($upper, 'BIAYA')
                                                || str_contains($upper, 'NETTO')
                                                || str_contains($upper, 'PPH');
                                            $isNoCol   = in_array($upper, ['NO','NO.','NOMOR']);
                                            $isJmlCol  = str_contains($upper,'PERKARA') && str_contains($upper,'JUMLAH');
                                            $isTtdCol  = str_contains($upper,'TANDA') || str_contains($upper,'TTD');
                                            $isWideCol = in_array($upper, ['NAMA', 'NAMA LENGKAP', 'JABATAN', 'URAIAN'])
                                                || (str_contains($upper, 'NAMA') && str_contains($upper, 'PERKARA'));

                                            // Format angka
                                            $displayVal = $val;
                                            $stripped   = str_replace(['.', ',', ' ', 'Rp'], '', $val);
                                            if ($isNumericCol && $stripped !== '' && is_numeric($stripped)) {
                                                $num        = (float) $stripped;
                                                $displayVal = $num != 0 ? 'Rp '.number_format($num, 0, ',', '.') : '-';
                                            } elseif ($val === '' || $val === null) {
                                                $displayVal = $isTtdCol ? '' : '';
                                            }
                                        @endphp
                                        <td class="px-3 py-2.5 text-xs border-r border-neutral-100 dark:border-neutral-800/50 last:border-r-0 align-middle
                                            {{ $isNoCol      ? 'text-center text-neutral-400 dark:text-neutral-500 w-10 min-w-[40px]' : '' }}
                                            {{ $isNumericCol ? 'text-right tabular-nums font-medium whitespace-nowrap min-w-[130px]'    : '' }}
                                            {{ $isJmlCol     ? 'text-center font-semibold'                                              : '' }}
                                            {{ $isTtdCol     ? 'text-center min-w-[90px]'                                               : '' }}
                                            {{ $isWideCol    ? 'text-left min-w-[150px] leading-snug'                                   : '' }}
                                            {{ !$isNoCol && !$isNumericCol && !$isJmlCol && !$isTtdCol && !$isWideCol ? 'text-left'     : '' }}
                                            {{ $isSummary
                                                ? 'font-semibold text-blue-800 dark:text-blue-200'
                                                : 'text-neutral-800 dark:text-neutral-200' }}">
                                            {{ $displayVal }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                    </tbody>
                    </table>
                </div>

                @if(!empty($sheet['footerBlocks']))
                    @php
                        $blockMap = [];
                        foreach ($sheet['footerBlocks'] as $blk) {
                            $blockMap[$blk['position']] = $blk['lines'];
                        }

                        $leftFooterLines = $blockMap['left'] ?? [];
                        $centerFooterLines = $blockMap['center'] ?? [];
                        $rightFooterLines = $blockMap['right'] ?? [];
                        $footerLineCount = max(count($leftFooterLines), count($centerFooterLines), count($rightFooterLines));

                        $footerStats = [
                            'sheet' => $sheet['sheetName'],
                            'total' => count($sheet['rows']),
                            'tim' => count(array_filter($sheet['rows'], fn($r) => ($r['_kode_kuitansi'] ?? 0) === 1)),
                            'pane' => count(array_filter($sheet['rows'], fn($r) => ($r['_kode_kuitansi'] ?? 0) === 2)),
                        ];
                    @endphp
                    <div class="mt-4 overflow-hidden rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow-sm">
                        <div class="border-b border-neutral-200 dark:border-neutral-800 px-5 py-4 bg-gradient-to-r from-neutral-50 to-white dark:from-neutral-900 dark:to-neutral-950">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-neutral-400 dark:text-neutral-500">Tanda Tangan</p>
                            <p class="mt-1 text-base font-semibold text-neutral-900 dark:text-neutral-100">Per Kolom</p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full table-fixed border-collapse text-sm">
                                <colgroup>
                                    <col class="w-1/3">
                                    <col class="w-1/3">
                                    <col class="w-1/3">
                                </colgroup>
                                <tbody>
                                    @for($lineIndex = 0; $lineIndex < $footerLineCount; $lineIndex++)
                                        <tr class="align-top bg-white dark:bg-neutral-900">
                                            <td class="px-5 py-3 border-t border-r border-neutral-200 dark:border-neutral-800 text-left text-sm text-neutral-700 dark:text-neutral-300">
                                                @php $line = $leftFooterLines[$lineIndex] ?? ''; @endphp
                                                @if($line !== '')
                                                    @php
                                                        $isDate  = preg_match('/\d{1,2}\s+\w+\s+\d{4}/', $line);
                                                        $isTitle = strtoupper($line) === $line && strlen(trim($line)) > 3;
                                                        $isName  = preg_match('/,\s*(S\.H|M\.H|S\.E|M\.M|S\.Ag|M\.Ag)/i', $line);
                                                    @endphp
                                                    <p class="leading-snug {{ $isDate ? 'text-neutral-400 dark:text-neutral-500 text-right' : ($isTitle ? 'font-bold uppercase tracking-wide text-neutral-900 dark:text-neutral-100' : ($isName ? 'font-semibold underline underline-offset-2 decoration-neutral-400' : '')) }}">{{ $line }}</p>
                                                @endif
                                            </td>
                                            <td class="px-5 py-3 border-t border-r border-neutral-200 dark:border-neutral-800 text-center text-sm text-neutral-700 dark:text-neutral-300">
                                                @php $line = $centerFooterLines[$lineIndex] ?? ''; @endphp
                                                @if($line !== '')
                                                    @php
                                                        $isTitle = strtoupper($line) === $line && strlen(trim($line)) > 3;
                                                        $isName  = preg_match('/,\s*(S\.H|M\.H|S\.E|M\.M|S\.Ag|M\.Ag)/i', $line);
                                                    @endphp
                                                    <p class="leading-snug {{ $isTitle ? 'font-bold uppercase tracking-wide text-neutral-900 dark:text-neutral-100' : ($isName ? 'font-semibold underline underline-offset-2 decoration-neutral-400' : '') }}">{{ $line }}</p>
                                                @endif
                                            </td>
                                            <td class="px-5 py-3 border-t border-neutral-200 dark:border-neutral-800 text-right text-sm text-neutral-700 dark:text-neutral-300">
                                                @php $line = $rightFooterLines[$lineIndex] ?? ''; @endphp
                                                @if($line !== '')
                                                    @php
                                                        $isDate  = preg_match('/\d{1,2}\s+\w+\s+\d{4}/', $line);
                                                        $isTitle = strtoupper($line) === $line && strlen(trim($line)) > 3;
                                                        $isName  = preg_match('/,\s*(S\.H|M\.H|S\.E|M\.M|S\.Ag|M\.Ag)/i', $line);
                                                    @endphp
                                                    <p class="leading-snug {{ $isDate ? 'text-neutral-400 dark:text-neutral-500' : ($isTitle ? 'font-bold uppercase tracking-wide text-neutral-900 dark:text-neutral-100' : ($isName ? 'font-semibold underline underline-offset-2 decoration-neutral-400' : '')) }}">{{ $line }}</p>
                                                @endif
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>

                        <div class="border-t border-neutral-200 dark:border-neutral-800 p-4 bg-neutral-50/70 dark:bg-neutral-950/40">
                            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4 text-xs text-neutral-500 dark:text-neutral-400">
                                <div class="rounded-lg border border-neutral-200 dark:border-neutral-800 px-3 py-2 bg-white dark:bg-neutral-900">
                                    <span class="block uppercase tracking-[0.22em] text-[10px] text-neutral-400 dark:text-neutral-500">Sheet</span>
                                    <strong class="text-neutral-700 dark:text-neutral-200">{{ $footerStats['sheet'] }}</strong>
                                </div>
                                <div class="rounded-lg border border-neutral-200 dark:border-neutral-800 px-3 py-2 bg-white dark:bg-neutral-900">
                                    <span class="block uppercase tracking-[0.22em] text-[10px] text-neutral-400 dark:text-neutral-500">Total Baris</span>
                                    <strong class="text-neutral-700 dark:text-neutral-200">{{ $footerStats['total'] }}</strong>
                                </div>
                                <div class="rounded-lg border border-blue-200 dark:border-blue-900 px-3 py-2 bg-blue-50 dark:bg-blue-950/30">
                                    <span class="block uppercase tracking-[0.22em] text-[10px] text-blue-500 dark:text-blue-400">Tim</span>
                                    <strong class="text-blue-700 dark:text-blue-300">{{ $footerStats['tim'] }}</strong>
                                </div>
                                <div class="rounded-lg border border-emerald-200 dark:border-emerald-900 px-3 py-2 bg-emerald-50 dark:bg-emerald-950/30">
                                    <span class="block uppercase tracking-[0.22em] text-[10px] text-emerald-500 dark:text-emerald-400">Kepaniteraan</span>
                                    <strong class="text-emerald-700 dark:text-emerald-300">{{ $footerStats['pane'] }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
            @endforeach

        </div>{{-- end card --}}

    @elseif(!$error)
        {{-- ─── Empty State ─── --}}
        <div class="text-center py-24">
            <svg class="w-16 h-16 text-neutral-300 dark:text-neutral-700 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-neutral-500 dark:text-neutral-400 text-lg font-medium">Belum ada data honorarium</p>
            <p class="text-neutral-400 dark:text-neutral-500 text-sm mt-2">Harap upload file Excel terlebih dahulu di Dashboard</p>
            <a href="{{ route('dashboard') }}"
               class="inline-block mt-6 px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200">
                Ke Dashboard
            </a>
        </div>
    @endif

</div>

<script>
function honorariumApp() {
    return {
        activeSheet:    {{ $activeSheet ?? 0 }},
        filterKuitansi: 0,  // 0 = Semua, 1 = Tim, 2 = Kepaniteraan
    };
}
</script>
@endsection
