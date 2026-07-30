@extends('layout')

@section('title', 'Honorarium Biaya Perkara')

@section('content')
<div x-data="honorariumApp()" class="min-h-screen">

    {{-- ─── Header ─── --}}
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="text-4xl font-semibold tracking-tight mb-3">Honorarium Per Kamar</h2>
            <p class="text-neutral-500 dark:text-neutral-400">
                Dokumen honorarium biaya penyelesaian perkara per jenis kamar
                @if($fileName)
                    — <span class="font-medium text-neutral-700 dark:text-neutral-300">{{ $fileName }}</span>
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Dropdown Navigasi Halaman Lain --}}
            <div class="relative w-56">
                <select onchange="if(this.value) window.location.href = this.value"
                    class="w-full appearance-none rounded-lg border border-emerald-300 dark:border-emerald-700 bg-white dark:bg-neutral-950 px-3.5 py-2.5 pr-10 text-sm font-medium text-neutral-900 dark:text-neutral-100 shadow-sm transition-colors duration-200 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 cursor-pointer">
                    <option value="" disabled selected>Lihat halaman lain</option>
                    <option value="{{ route('dashboard') }}">🏠&nbsp; Dashboard</option>
                    <option value="{{ route('data-print') }}">🖨️&nbsp; Data Print</option>
                    <option value="{{ route('sheet-cek') }}">📋&nbsp; Lihat Sheet Cek</option>
                    <option value="{{ route('rekap-keseluruhan') }}">📊&nbsp; Rekap Keseluruhan</option>
                    <option value="{{ route('periode-laporan') }}">📅&nbsp; Periode Laporan</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-emerald-600 dark:text-emerald-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </div>
            </div>

            @if(!$error && (count($sheets) > 0 || !empty($timData) || !empty($kepaniteraanData) || !empty($opStafData)))
                {{-- Dropdown Cetak --}}

                <div class="relative" x-data="{ printOpen: false }" @click.outside="printOpen = false">
                    <button @click="printOpen = !printOpen"
                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 font-medium shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <polyline points="6 9 6 2 18 2 18 9"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
                            <rect x="6" y="14" width="12" height="8"/>
                        </svg>
                        Cetak
                        <svg class="w-3.5 h-3.5 transition-transform duration-150" :class="printOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m6 9 6 6 6-6"/>
                        </svg>
                    </button>

                    {{-- Dropdown menu --}}
                    <div x-show="printOpen"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-64 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-xl z-50 overflow-hidden"
                         style="display:none;">

                        {{-- ── Grup: Honorarium ── --}}
                        <div class="px-4 py-2 bg-neutral-50 dark:bg-neutral-800 border-b border-neutral-200 dark:border-neutral-700">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-neutral-400 dark:text-neutral-500">Honorarium</p>
                        </div>

                        {{-- Semua Honorarium --}}
                        <a href="{{ route('honorarium.print') }}" target="_blank" rel="noopener"
                           class="flex items-center gap-3 px-4 py-2.5 text-sm font-semibold text-blue-700 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/40 transition-colors border-b border-neutral-100 dark:border-neutral-800">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>
                            </svg>
                            Semua Honorarium
                        </a>

                        {{-- Per sheet Honorarium --}}
                        @foreach($sheets as $si => $sheet)
                        <a href="{{ route('honorarium.print') }}?sheet={{ $si }}" target="_blank" rel="noopener"
                           class="flex items-center gap-3 pl-8 pr-4 py-2.5 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors border-b border-neutral-100 dark:border-neutral-800">
                            <svg class="w-3.5 h-3.5 flex-shrink-0 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6"/>
                            </svg>
                            {{ $sheet['sheetName'] }}
                            <span class="ml-auto text-xs text-neutral-400">{{ count($sheet['blocks']) }} dok</span>
                        </a>
                        @endforeach

                        {{-- Computed Data Prints --}}
                        @if(!empty($kepaniteraanData) && empty(array_filter($sheets, fn($s) => $s['sheetName'] === 'Kepaniteraan')))
                        <a href="{{ route('honorarium.print') }}?computed=kepaniteraan" target="_blank" rel="noopener"
                           class="flex items-center gap-3 pl-8 pr-4 py-2.5 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors border-b border-neutral-100 dark:border-neutral-800">
                            <svg class="w-3.5 h-3.5 flex-shrink-0 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6"/>
                            </svg>
                            Kepaniteraan
                        </a>
                        @endif
                        @if(!empty($timData) && empty(array_filter($sheets, fn($s) => $s['sheetName'] === 'TIM')))
                        <a href="{{ route('honorarium.print') }}?computed=tim" target="_blank" rel="noopener"
                           class="flex items-center gap-3 pl-8 pr-4 py-2.5 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors border-b border-neutral-100 dark:border-neutral-800">
                            <svg class="w-3.5 h-3.5 flex-shrink-0 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6"/>
                            </svg>
                            TIM
                        </a>
                        @endif
                        @if(!empty($opStafData) && empty(array_filter($sheets, fn($s) => $s['sheetName'] === 'OP - STAF')))
                        <a href="{{ route('honorarium.print') }}?computed=op-staf" target="_blank" rel="noopener"
                           class="flex items-center gap-3 pl-8 pr-4 py-2.5 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors border-b border-neutral-100 dark:border-neutral-800">
                            <svg class="w-3.5 h-3.5 flex-shrink-0 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6"/>
                            </svg>
                            OP - STAF
                        </a>
                        @endif

                        {{-- ── Grup: Rekap Keseluruhan ── --}}
                        <div class="px-4 py-2 bg-neutral-50 dark:bg-neutral-800 border-t border-b border-neutral-200 dark:border-neutral-700">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-neutral-400 dark:text-neutral-500">Rekap Keseluruhan</p>
                        </div>

                        <a href="{{ route('rekap-keseluruhan.print') }}" target="_blank" rel="noopener"
                           class="flex items-center gap-3 px-4 py-2.5 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors border-b border-neutral-100 dark:border-neutral-800">
                            <svg class="w-4 h-4 flex-shrink-0 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Rekap Keseluruhan 1
                        </a>
                        <a href="{{ route('rekap-keseluruhan-2.print') }}" target="_blank" rel="noopener"
                           class="flex items-center gap-3 px-4 py-2.5 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors border-b border-neutral-100 dark:border-neutral-800">
                            <svg class="w-4 h-4 flex-shrink-0 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Rekap Keseluruhan 2
                        </a>
                        <a href="{{ route('rekap-keseluruhan-3.print') }}" target="_blank" rel="noopener"
                           class="flex items-center gap-3 px-4 py-2.5 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors border-b border-neutral-100 dark:border-neutral-800">
                            <svg class="w-4 h-4 flex-shrink-0 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Rekap Keseluruhan 3
                        </a>

                        {{-- ── Grup: Lainnya ── --}}
                        <div class="px-4 py-2 bg-neutral-50 dark:bg-neutral-800 border-t border-b border-neutral-200 dark:border-neutral-700">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-neutral-400 dark:text-neutral-500">Lainnya</p>
                        </div>
                        <a href="{{ route('sheet-cek.print') }}" target="_blank" rel="noopener"
                           class="flex items-center gap-3 px-4 py-2.5 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                            <svg class="w-4 h-4 flex-shrink-0 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            Sheet Cek
                        </a>
                        <a href="{{ route('data-print.print') }}" target="_blank" rel="noopener"
                           class="flex items-center gap-3 px-4 py-2.5 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors border-t border-neutral-100 dark:border-neutral-800">
                            <svg class="w-4 h-4 flex-shrink-0 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                            Data Print
                        </a>

                    </div>
                </div>
            @endif

            <a href="{{ route('dashboard') }}"
               class="px-4 py-2.5 text-sm bg-neutral-200 dark:bg-neutral-800 hover:bg-neutral-300 dark:hover:bg-neutral-700 text-neutral-900 dark:text-neutral-100 rounded-lg transition-colors duration-200">
                Dashboard
            </a>
        </div>

    </div>

    {{-- ─── Error ─── --}}
    @if($error)
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/50 rounded-xl">
            <p class="text-sm text-red-700 dark:text-red-400">{{ $error }}</p>
        </div>
    @endif

    @if(!$error && count($sheets) > 0)

        {{-- ─── Sheet Tabs ─── --}}
        <div class="flex gap-2 mb-6 flex-wrap">
            @foreach($sheets as $si => $sheet)
                <button @click="activeSheet = {{ $si }}"
                        :class="activeSheet === {{ $si }}
                            ? 'bg-blue-600 text-white shadow-sm'
                            : 'bg-white dark:bg-neutral-900 text-neutral-700 dark:text-neutral-300 border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800'"
                        class="px-5 py-2.5 rounded-lg text-sm font-medium transition-all duration-200">
                    {{ $sheet['sheetName'] }}
                    <span class="ml-2 text-xs opacity-70">({{ count($sheet['blocks']) }} dok)</span>
                </button>
            @endforeach
        </div>

        {{-- ─── Sheets Content ─── --}}
        @foreach($sheets as $si => $sheet)
        <div x-show="activeSheet === {{ $si }}" x-cloak>

            @if((strtoupper(trim($sheet['sheetName'])) === 'TIM' || str_contains(strtoupper($sheet['sheetName']), 'TIM')) && !empty($timData))
                {{-- TIM: filter kategori + computed blocks dari Data Print --}}
                @php
                    $timVisibleCount = count($timData);
                @endphp
                <div x-data="{ timCat: '' }">

                {{-- ── Tombol Kembali ── --}}
                <div class="mb-3 flex justify-end">
                    <a href="{{ route('data-print') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-600 rounded-lg shadow-sm hover:shadow-md hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-all duration-200">
                        <svg class="w-3.5 h-3.5 text-neutral-700 dark:text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        <span class="text-neutral-900 dark:text-white font-semibold">Kembali</span>
                    </a>
                </div>

                {{-- ── Dropdown ── --}}
                <div class="mb-6 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-4">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-3">
                        Pilih Kategori Perkara
                    </label>
                    <div class="flex items-center gap-3">
                        <div class="relative flex-1">
                            <select x-model="timCat"
                                    class="w-full appearance-none rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-950 px-4 py-2.5 pr-10 text-sm text-neutral-900 dark:text-neutral-100 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-colors cursor-pointer">
                                <option value="">— Semua Kategori ({{ $timVisibleCount }}) —</option>
                                @foreach($timData as $ti => $tBlock)
                                <option value="{{ $ti }}">{{ $tBlock['label'] }} ({{ number_format($tBlock['jumlah_perkara'], 0, ',', '.') }} Perkara)</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-neutral-500">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/>
                                </svg>
                            </div>
                        </div>
                        <a :href="timCat !== '' ? '{{ route('honorarium.print') }}?computed=tim&cat=' + timCat : '{{ route('honorarium.print') }}?computed=tim'"
                           target="_blank" rel="noopener"
                           class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition-colors shadow-sm shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <polyline points="6 9 6 2 18 2 18 9"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
                                <rect x="6" y="14" width="12" height="8"/>
                            </svg>
                            <span x-text="timCat !== '' ? '🖨️ Cetak Kategori Ini' : '🖨️ Cetak Semua TIM'"></span>
                        </a>
                    </div>
                </div>

                @foreach($timData as $ti => $block)
                @if(count($block['rows']) >= 1)
                <div x-show="timCat === '' || timCat === '{{ $ti }}'" class="mb-8 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl shadow-sm">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/30 dark:to-indigo-950/30 border-b border-blue-100 dark:border-blue-900/50 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="text-center flex-1">
                                <p class="text-sm font-bold uppercase tracking-wide text-neutral-900 dark:text-neutral-100">
                                    HONORARIUM BIAYA PENYELESAIAN PERKARA {{ $block['label'] }}
                                </p>
                                <p class="text-sm font-bold text-blue-700 dark:text-blue-400 mt-1">
                                    Sebanyak {{ number_format($block['jumlah_perkara'], 0, ',', '.') }} Perkara
                                </p>
                            </div>
                            <a href="{{ route('honorarium.print') }}?computed=tim&cat={{ $ti }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition-colors shadow-sm shrink-0">
                                🖨️ Cetak Kategori Ini
                            </a>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-100 dark:bg-slate-800/60 border-b-2 border-slate-300 dark:border-slate-600">
                                    <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center w-8">NO</th>
                                    <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[160px]">NAMA</th>
                                    <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[180px]">JABATAN</th>
                                    <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[90px]">JUMLAH PERKARA</th>
                                    <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[90px]">BIAYA</th>
                                    <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-right min-w-[100px]">JUMLAH BIAYA</th>
                                    <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-right min-w-[90px]">PPH 15%</th>
                                    <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[90px]">PPH 5%</th>
                                    <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[100px]">NETTO</th>
                                    <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[100px]">TANDA TANGAN</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $rowNum = 0; @endphp
                                @foreach($block['rows'] as $row)
                                    @php $rowNum++; $bg = $rowNum % 2 === 0 ? 'bg-slate-50/50 dark:bg-slate-800/20' : ''; @endphp
                                    <tr class="border-b border-neutral-100 dark:border-neutral-800 hover:bg-blue-50/40 dark:hover:bg-blue-950/20 transition-colors {{ $bg }}">
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center text-neutral-800 dark:text-neutral-200">{{ $row['no'] }}</td>
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-1 py-1 font-medium"
                                            x-data="{ editing: false, val: localStorage.getItem('hon_tim_{{ $ti }}_{{ $loop->index }}') ?? @js($row['nama']) }"
                                            x-init="$watch('val', v => localStorage.setItem('hon_tim_{{ $ti }}_{{ $loop->index }}', v))"
                                            @dblclick="orig=val; editing=true" title="Klik 2x untuk edit nama">
                                            <span x-show="!editing" x-text="val !== '' ? val : '— Belum diisi —'" :class="val === '' ? 'text-neutral-400 dark:text-neutral-600 font-normal not-italic text-xs tracking-wide' : ''" class="cursor-text block px-1 py-1 text-center text-neutral-800 dark:text-neutral-200"></span>
                                            <div x-show="editing" x-cloak class="flex items-center gap-1">
                                                <input x-model="val" type="text"
                                                       x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                                                       @keyup.enter="editing=false" @keyup.escape="val=orig; editing=false"
                                                       class="flex-1 border border-blue-400 rounded px-1 py-0.5 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 min-w-0 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100">
                                                <button @click="editing=false" class="text-green-500 hover:text-green-700 font-bold text-sm shrink-0">✓</button>
                                                <button @click="val=orig; editing=false" class="text-red-400 hover:text-red-600 font-bold text-sm shrink-0">✗</button>
                                            </div>
                                        </td>
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center text-neutral-700 dark:text-neutral-300">{{ $row['jabatan'] }}</td>
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center text-neutral-800 dark:text-neutral-200">{{ number_format($row['jumlah_perkara'], 0, ',', '.') }}</td>
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center text-neutral-800 dark:text-neutral-200">Rp {{ number_format($row['biaya'], 0, ',', '.') }}</td>
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-right text-neutral-800 dark:text-neutral-200">Rp {{ number_format($row['jumlah_biaya'], 0, ',', '.') }}</td>
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-right text-neutral-800 dark:text-neutral-200">{{ $row['pph15'] > 0 ? 'Rp ' . number_format($row['pph15'], 0, ',', '.') : '-' }}</td>
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center text-neutral-800 dark:text-neutral-200">{{ $row['pph5'] > 0 ? 'Rp ' . number_format($row['pph5'], 0, ',', '.') : '-' }}</td>
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center text-neutral-800 dark:text-neutral-200">Rp {{ number_format($row['netto'], 0, ',', '.') }}</td>
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2"></td>
                                    </tr>
                                @endforeach
                                <tr class="bg-slate-100 dark:bg-slate-700/40 border-t-2 border-slate-400 dark:border-slate-500 font-bold">
                                    <td colspan="5" class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 text-neutral-900 dark:text-neutral-100 text-right">JUMLAH</td>
                                    <td class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 text-right text-neutral-900 dark:text-neutral-100">Rp {{ number_format($block['total']['jumlah_biaya'], 0, ',', '.') }}</td>
                                    <td class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 text-right text-neutral-900 dark:text-neutral-100">{{ $block['total']['pph15'] > 0 ? 'Rp ' . number_format($block['total']['pph15'], 0, ',', '.') : '-' }}</td>
                                    <td class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 text-right text-neutral-900 dark:text-neutral-100">{{ $block['total']['pph5'] > 0 ? 'Rp ' . number_format($block['total']['pph5'], 0, ',', '.') : '-' }}</td>
                                    <td class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 text-right text-neutral-900 dark:text-neutral-100">Rp {{ number_format($block['total']['netto'], 0, ',', '.') }}</td>
                                    <td class="border border-slate-300 dark:border-slate-600 px-2 py-2.5"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    {{-- TTD Section --}}
                    @php
                        $ttdDate = \Illuminate\Support\Facades\Session::get('excel_tgl_kwitansi')
                            ?: ('Jakarta, ' . \Carbon\Carbon::now()->translatedFormat('d F Y'));
                        $pejabat = config('tarif.pejabat');
                    @endphp
                    <div class="px-4 pt-4 pb-2 border-t border-neutral-200 dark:border-neutral-700">
                        <div class="grid grid-cols-3 gap-4 text-xs">
                            {{-- Kiri --}}
                            <div class="flex flex-col">
                                <div class="font-bold uppercase text-neutral-700 dark:text-neutral-300 text-center leading-tight mb-1">Petugas Pembuat Komitmen<br>Biaya Proses</div>
                                <div class="h-12"></div>
                                <div x-data="{ editing: false, val: localStorage.getItem('ttd_petugas') ?? @js($pejabat['petugas_pembuat'] ?? 'ST. KRIS NUGROHO, S.H., M.H.') }"
                                     x-init="$watch('val', v => localStorage.setItem('ttd_petugas', v))"
                                     @dblclick="orig=val; editing=true" title="Klik 2x untuk edit">
                                    <span x-show="!editing" x-text="val !== '' ? val : '— Belum diisi —'" :class="val === '' ? 'text-neutral-400 dark:text-neutral-600 font-normal not-italic text-xs tracking-wide' : ''" class="cursor-text block font-bold text-neutral-900 dark:text-neutral-100 text-center"></span>
                                    <div x-show="editing" x-cloak class="flex items-center gap-1">
                                        <input x-model="val" type="text"
                                               x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                                               @keyup.enter="editing=false" @keyup.escape="val=orig; editing=false"
                                               class="flex-1 border border-blue-400 rounded px-1 py-0.5 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 min-w-0 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100">
                                        <button @click="editing=false" class="text-green-500 hover:text-green-700 font-bold shrink-0">✓</button>
                                        <button @click="val=orig; editing=false" class="text-red-400 hover:text-red-600 font-bold shrink-0">✗</button>
                                    </div>
                                </div>
                            </div>
                            {{-- Tengah --}}
                            <div class="flex flex-col items-center">
                                <div class="font-bold uppercase text-neutral-700 dark:text-neutral-300 text-center leading-tight mb-1">Mengetahui,<br>Kuasa Pengelola Biaya Proses</div>
                                <div class="h-12"></div>
                                <div x-data="{ editing: false, val: localStorage.getItem('ttd_kuasa') ?? @js($pejabat['kuasa_pengelola'] ?? 'ASEP NURSOBAH, S.Ag., M.H.') }"
                                     x-init="$watch('val', v => localStorage.setItem('ttd_kuasa', v))"
                                     @dblclick="orig=val; editing=true" title="Klik 2x untuk edit">
                                    <span x-show="!editing" x-text="val !== '' ? val : '— Belum diisi —'" :class="val === '' ? 'text-neutral-400 dark:text-neutral-600 font-normal not-italic text-xs tracking-wide' : ''" class="cursor-text block font-bold text-neutral-900 dark:text-neutral-100 text-center"></span>
                                    <div x-show="editing" x-cloak class="flex items-center gap-1">
                                        <input x-model="val" type="text"
                                               x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                                               @keyup.enter="editing=false" @keyup.escape="val=orig; editing=false"
                                               class="flex-1 border border-blue-400 rounded px-1 py-0.5 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 min-w-0 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100">
                                        <button @click="editing=false" class="text-green-500 hover:text-green-700 font-bold shrink-0">✓</button>
                                        <button @click="val=orig; editing=false" class="text-red-400 hover:text-red-600 font-bold shrink-0">✗</button>
                                    </div>
                                </div>
                            </div>
                            {{-- Kanan --}}
                            <div class="flex flex-col items-end">
                                <div class="text-right font-semibold text-neutral-600 dark:text-neutral-400 mb-1">{{ $ttdDate }}<br><span class="font-bold uppercase text-neutral-700 dark:text-neutral-300">Bendahara Biaya Proses</span></div>
                                <div class="h-12"></div>
                                <div x-data="{ editing: false, val: localStorage.getItem('ttd_bendahara') ?? @js($pejabat['bendahara'] ?? 'FARIDA,SH') }"
                                     x-init="$watch('val', v => localStorage.setItem('ttd_bendahara', v))"
                                     @dblclick="orig=val; editing=true" title="Klik 2x untuk edit">
                                    <span x-show="!editing" x-text="val !== '' ? val : '— Belum diisi —'" :class="val === '' ? 'text-neutral-400 dark:text-neutral-600 font-normal not-italic text-xs tracking-wide' : ''" class="cursor-text block font-bold text-neutral-900 dark:text-neutral-100 text-right"></span>
                                    <div x-show="editing" x-cloak class="flex items-center gap-1 justify-end">
                                        <input x-model="val" type="text"
                                               x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                                               @keyup.enter="editing=false" @keyup.escape="val=orig; editing=false"
                                               class="flex-1 border border-blue-400 rounded px-1 py-0.5 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 min-w-0 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100">
                                        <button @click="editing=false" class="text-green-500 hover:text-green-700 font-bold shrink-0">✓</button>
                                        <button @click="val=orig; editing=false" class="text-red-400 hover:text-red-600 font-bold shrink-0">✗</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-neutral-400 mt-2 text-center">Klik 2x pada nama untuk mengedit</p>
                    </div>
                </div>
                @endif
                @endforeach

                </div>{{-- /x-data timCat --}}


            @elseif((strtoupper(trim($sheet['sheetName'])) === 'KEPANITERAAN' || str_contains(strtoupper($sheet['sheetName']), 'KEPANITERAAN')) && !empty($kepaniteraanData))
                {{-- KEPANITERAAN: tampilkan computed blocks dari Data Print --}}
                @php $kepVisibleCount = count(array_filter($kepaniteraanData, fn($b) => count($b['rows']) >= 1)); @endphp
                <div x-data="{ kepBlock: '' }">

                {{-- ── Tombol Kembali ── --}}
                <div class="mb-3 flex justify-end">
                    <a href="{{ route('data-print') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-600 rounded-lg shadow-sm hover:shadow-md hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-all duration-200">
                        <svg class="w-3.5 h-3.5 text-neutral-700 dark:text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        <span class="text-neutral-900 dark:text-white font-semibold">Kembali</span>
                    </a>
                </div>

                {{-- ── Dropdown ── --}}
                <div class="mb-6 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-4">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-3">
                        Pilih Kategori Perkara
                    </label>
                    <div class="flex items-center gap-3">
                        <div class="relative flex-1">
                            <select x-model="kepBlock"
                                    class="w-full appearance-none rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-950 px-4 py-2.5 pr-10 text-sm text-neutral-900 dark:text-neutral-100 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-colors cursor-pointer">
                                <option value="">— Semua Kategori ({{ $kepVisibleCount }}) —</option>
                                @foreach($kepaniteraanData as $ki => $kb)
                                @if(count($kb['rows']) >= 1)
                                <option value="{{ $ki }}">{{ $kb['title'] }} — {{ number_format($kb['jml_perkara'], 0, ',', '.') }} Perkara</option>
                                @endif
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-neutral-500">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/>
                                </svg>
                            </div>
                        </div>
                        <a :href="kepBlock !== '' ? '{{ route('honorarium.print') }}?computed=kepaniteraan&cat=' + kepBlock : '{{ route('honorarium.print') }}?computed=kepaniteraan'"
                           target="_blank" rel="noopener"
                           class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition-colors shadow-sm shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <polyline points="6 9 6 2 18 2 18 9"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
                                <rect x="6" y="14" width="12" height="8"/>
                            </svg>
                            <span x-text="kepBlock !== '' ? '🖨️ Cetak Kategori Ini' : '🖨️ Cetak Semua Kepaniteraan'"></span>
                        </a>
                    </div>
                </div>

                @foreach($kepaniteraanData as $ki => $block)
                @if(count($block['rows']) >= 1)
                <div class="mb-8 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl shadow-sm"
                     x-show="kepBlock === '' || kepBlock === '{{ $ki }}'"
                     x-cloak>
                    {{-- Header --}}
                    <div class="bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-950/30 dark:to-teal-950/30 border-b border-emerald-100 dark:border-emerald-900/50 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="text-center flex-1">
                                <p class="text-sm font-bold uppercase tracking-wide text-neutral-900 dark:text-neutral-100">
                                    HONORARIUM BIAYA PENYELESAIAN PERKARA {{ $block['title'] }}
                                </p>
                                <p class="text-sm font-bold text-emerald-700 dark:text-emerald-400 mt-1">
                                    Sebanyak {{ number_format($block['jml_perkara'], 0, ',', '.') }} Perkara
                                </p>
                            </div>
                            <a href="{{ route('honorarium.print') }}?computed=kepaniteraan&cat={{ $ki }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition-colors shadow-sm shrink-0">
                                🖨️ Cetak Kategori Ini
                            </a>
                        </div>
                    </div>

                    {{-- Tabel --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-100 dark:bg-slate-800/60 border-b-2 border-slate-300 dark:border-slate-600">
                                    <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center w-8">NO</th>
                                    <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[180px]">NAMA</th>
                                    <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[200px]">JABATAN</th>
                                    <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[90px]">JUMLAH PERKARA</th>
                                    <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[90px]">BIAYA</th>
                                    <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-right min-w-[110px]">JUMLAH BIAYA</th>
                                    <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-right min-w-[90px]">PPH 15%</th>
                                    <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[90px]">PPH 5%</th>
                                    <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[110px]">NETTO</th>
                                    <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[100px]">TANDA TANGAN</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $rn = 0; @endphp
                                @foreach($block['rows'] as $row)
                                    @php $rn++; $bg = $rn % 2 === 0 ? 'bg-slate-50/50 dark:bg-slate-800/20' : ''; @endphp
                                    <tr class="border-b border-neutral-100 dark:border-neutral-800 hover:bg-emerald-50/30 dark:hover:bg-neutral-800/30 transition-colors {{ $bg }}">
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center text-neutral-600">{{ $row['no'] }}</td>
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-1 py-1 font-medium"
                                            x-data="{ editing: false, val: localStorage.getItem('hon_kep_{{ $ki }}_{{ $loop->index }}') ?? @js($row['nama']) }"
                                            x-init="$watch('val', v => localStorage.setItem('hon_kep_{{ $ki }}_{{ $loop->index }}', v))"
                                            @dblclick="orig=val; editing=true" title="Klik 2x untuk edit nama">
                                            <span x-show="!editing" x-text="val !== '' ? val : '— Belum diisi —'" :class="val === '' ? 'text-neutral-400 dark:text-neutral-600 font-normal not-italic text-xs tracking-wide' : ''" class="cursor-text block px-1 py-1 text-center text-neutral-900 dark:text-neutral-100"></span>
                                            <div x-show="editing" x-cloak class="flex items-center gap-1">
                                                <input x-model="val" type="text"
                                                       x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                                                       @keyup.enter="editing=false" @keyup.escape="val=orig; editing=false"
                                                       class="flex-1 border border-blue-400 rounded px-1 py-0.5 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 min-w-0 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100">
                                                <button @click="editing=false" class="text-green-500 hover:text-green-700 font-bold text-sm shrink-0">✓</button>
                                                <button @click="val=orig; editing=false" class="text-red-400 hover:text-red-600 font-bold text-sm shrink-0">✗</button>
                                            </div>
                                        </td>
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center text-neutral-700 dark:text-neutral-300">{{ $row['jabatan'] }}</td>
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center text-neutral-800 dark:text-neutral-200">{{ number_format($row['jml_perkara'], 0, ',', '.') }}</td>
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center text-neutral-800 dark:text-neutral-200">Rp {{ number_format($row['biaya'], 0, ',', '.') }}</td>
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-right text-neutral-800 dark:text-neutral-200">Rp {{ number_format($row['jml_biaya'], 0, ',', '.') }}</td>
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-right text-neutral-800 dark:text-neutral-200">{{ $row['pph15'] > 0 ? 'Rp ' . number_format($row['pph15'], 0, ',', '.') : '-' }}</td>
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center text-neutral-800 dark:text-neutral-200">{{ $row['pph5'] > 0 ? 'Rp ' . number_format($row['pph5'], 0, ',', '.') : '-' }}</td>
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center font-medium text-neutral-900 dark:text-neutral-100">Rp {{ number_format($row['netto'], 0, ',', '.') }}</td>
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2"></td>
                                    </tr>
                                @endforeach
                                {{-- Total row --}}
                                <tr class="bg-emerald-50 dark:bg-emerald-900/20 border-t-2 border-emerald-400 dark:border-emerald-700 font-bold">
                                    <td colspan="5" class="border border-emerald-200 dark:border-emerald-800 px-2 py-2.5 text-right text-neutral-900 dark:text-neutral-100">TOTAL</td>
                                    <td class="border border-emerald-200 dark:border-emerald-800 px-2 py-2.5 text-right text-neutral-900 dark:text-neutral-100">Rp {{ number_format($block['total']['jml_biaya'], 0, ',', '.') }}</td>
                                    <td class="border border-emerald-200 dark:border-emerald-800 px-2 py-2.5 text-right text-neutral-900 dark:text-neutral-100">{{ $block['total']['pph15'] > 0 ? 'Rp ' . number_format($block['total']['pph15'], 0, ',', '.') : '-' }}</td>
                                    <td class="border border-emerald-200 dark:border-emerald-800 px-2 py-2.5 text-right text-neutral-900 dark:text-neutral-100">{{ $block['total']['pph5'] > 0 ? 'Rp ' . number_format($block['total']['pph5'], 0, ',', '.') : '-' }}</td>
                                    <td class="border border-emerald-200 dark:border-emerald-800 px-2 py-2.5 text-right text-neutral-900 dark:text-neutral-100">Rp {{ number_format($block['total']['netto'], 0, ',', '.') }}</td>
                                    <td class="border border-emerald-200 dark:border-emerald-800 px-2 py-2.5"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    {{-- TTD Section --}}
                    @php
                        $ttdDate = \Illuminate\Support\Facades\Session::get('excel_tgl_kwitansi')
                            ?: ('Jakarta, ' . \Carbon\Carbon::now()->translatedFormat('d F Y'));
                        $pejabat = config('tarif.pejabat');
                    @endphp
                    <div class="px-4 pt-4 pb-2 border-t border-neutral-200 dark:border-neutral-700">
                        <div class="grid grid-cols-3 gap-4 text-xs">
                            {{-- Kiri --}}
                            <div class="flex flex-col">
                                <div class="font-bold uppercase text-neutral-700 dark:text-neutral-300 text-center leading-tight mb-1">Petugas Pembuat Komitmen<br>Biaya Proses</div>
                                <div class="h-12"></div>
                                <div x-data="{ editing: false, val: localStorage.getItem('ttd_petugas') ?? @js($pejabat['petugas_pembuat'] ?? 'ST. KRIS NUGROHO, S.H., M.H.') }"
                                     x-init="$watch('val', v => localStorage.setItem('ttd_petugas', v))"
                                     @dblclick="orig=val; editing=true" title="Klik 2x untuk edit">
                                    <span x-show="!editing" x-text="val !== '' ? val : '— Belum diisi —'" :class="val === '' ? 'text-neutral-400 dark:text-neutral-600 font-normal not-italic text-xs tracking-wide' : ''" class="cursor-text block font-bold text-neutral-900 dark:text-neutral-100 text-center"></span>
                                    <div x-show="editing" x-cloak class="flex items-center gap-1">
                                        <input x-model="val" type="text"
                                               x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                                               @keyup.enter="editing=false" @keyup.escape="val=orig; editing=false"
                                               class="flex-1 border border-blue-400 rounded px-1 py-0.5 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 min-w-0 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100">
                                        <button @click="editing=false" class="text-green-500 hover:text-green-700 font-bold shrink-0">✓</button>
                                        <button @click="val=orig; editing=false" class="text-red-400 hover:text-red-600 font-bold shrink-0">✗</button>
                                    </div>
                                </div>
                            </div>
                            {{-- Tengah --}}
                            <div class="flex flex-col items-center">
                                <div class="font-bold uppercase text-neutral-700 dark:text-neutral-300 text-center leading-tight mb-1">Mengetahui,<br>Kuasa Pengelola Biaya Proses</div>
                                <div class="h-12"></div>
                                <div x-data="{ editing: false, val: localStorage.getItem('ttd_kuasa') ?? @js($pejabat['kuasa_pengelola'] ?? 'ASEP NURSOBAH, S.Ag., M.H.') }"
                                     x-init="$watch('val', v => localStorage.setItem('ttd_kuasa', v))"
                                     @dblclick="orig=val; editing=true" title="Klik 2x untuk edit">
                                    <span x-show="!editing" x-text="val !== '' ? val : '— Belum diisi —'" :class="val === '' ? 'text-neutral-400 dark:text-neutral-600 font-normal not-italic text-xs tracking-wide' : ''" class="cursor-text block font-bold text-neutral-900 dark:text-neutral-100 text-center"></span>
                                    <div x-show="editing" x-cloak class="flex items-center gap-1">
                                        <input x-model="val" type="text"
                                               x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                                               @keyup.enter="editing=false" @keyup.escape="val=orig; editing=false"
                                               class="flex-1 border border-blue-400 rounded px-1 py-0.5 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 min-w-0 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100">
                                        <button @click="editing=false" class="text-green-500 hover:text-green-700 font-bold shrink-0">✓</button>
                                        <button @click="val=orig; editing=false" class="text-red-400 hover:text-red-600 font-bold shrink-0">✗</button>
                                    </div>
                                </div>
                            </div>
                            {{-- Kanan --}}
                            <div class="flex flex-col items-end">
                                <div class="text-right font-semibold text-neutral-600 dark:text-neutral-400 mb-1">{{ $ttdDate }}<br><span class="font-bold uppercase text-neutral-700 dark:text-neutral-300">Bendahara Biaya Proses</span></div>
                                <div class="h-12"></div>
                                <div x-data="{ editing: false, val: localStorage.getItem('ttd_bendahara') ?? @js($pejabat['bendahara'] ?? 'FARIDA,SH') }"
                                     x-init="$watch('val', v => localStorage.setItem('ttd_bendahara', v))"
                                     @dblclick="orig=val; editing=true" title="Klik 2x untuk edit">
                                    <span x-show="!editing" x-text="val !== '' ? val : '— Belum diisi —'" :class="val === '' ? 'text-neutral-400 dark:text-neutral-600 font-normal not-italic text-xs tracking-wide' : ''" class="cursor-text block font-bold text-neutral-900 dark:text-neutral-100 text-right"></span>
                                    <div x-show="editing" x-cloak class="flex items-center gap-1 justify-end">
                                        <input x-model="val" type="text"
                                               x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                                               @keyup.enter="editing=false" @keyup.escape="val=orig; editing=false"
                                               class="flex-1 border border-blue-400 rounded px-1 py-0.5 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 min-w-0 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100">
                                        <button @click="editing=false" class="text-green-500 hover:text-green-700 font-bold shrink-0">✓</button>
                                        <button @click="val=orig; editing=false" class="text-red-400 hover:text-red-600 font-bold shrink-0">✗</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-neutral-400 mt-2 text-center">Klik 2x pada nama untuk mengedit</p>
                    </div>
                </div>
                @endif
                @endforeach
                </div>{{-- /x-data kepBlock --}}

            @else
                {{-- Kepaniteraan / OP-STAF / TIM tanpa computed data: tampilkan Excel blocks --}}


                {{-- Block filter: dropdown pilih dokumen --}}
            <div class="mb-6 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-4">
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-3">
                    Pilih Dokumen — {{ $sheet['sheetName'] }}
                </label>
                <div class="flex items-center gap-3">
                    <div class="relative flex-1 max-w-xl">
                        <select
                            @change="activeBlock[{{ $si }}] = $event.target.value === 'null' ? null : parseInt($event.target.value)"
                            :value="activeBlock[{{ $si }}] === null ? 'null' : activeBlock[{{ $si }}]"
                            class="w-full appearance-none rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-950 px-4 py-2.5 pr-10 text-sm text-neutral-900 dark:text-neutral-100 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-colors duration-200 cursor-pointer">
                            <option value="null">— Semua Dokumen ({{ count($sheet['blocks']) }}) —</option>
                            @foreach($sheet['blocks'] as $bi => $block)
                                @php
                                    $shortTitle = preg_replace('/^HONORARIUM BIAYA PENYELESAIAN PERKARA\s*/i', '', $block['title1']);
                                    // Ambil jumlah perkara dari title3
                                    preg_match('/Sebanyak\s*([\d]+)\s*Perkara/i', $block['title3'], $m);
                                    $jmlPerkara = isset($m[1]) ? ' — ' . $m[1] . ' Perkara' : '';
                                @endphp
                                <option value="{{ $bi }}">{{ $shortTitle }}{{ $jmlPerkara }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-neutral-500 dark:text-neutral-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/>
                            </svg>
                        </div>
                    </div>
                    <a :href="activeBlock[{{ $si }}] !== null ? '{{ route('honorarium.print') }}?sheet={{ $si }}&block=' + activeBlock[{{ $si }}] : '{{ route('honorarium.print') }}?sheet={{ $si }}'"
                       target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition-colors shadow-sm shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <polyline points="6 9 6 2 18 2 18 9"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
                            <rect x="6" y="14" width="12" height="8"/>
                        </svg>
                        <span x-text="activeBlock[{{ $si }}] !== null ? '🖨️ Cetak Kategori Ini' : '🖨️ Cetak Semua {{ $sheet['sheetName'] }}'"></span>
                    </a>
                </div>
            </div>

            {{-- Dokumen per blok --}}
            @foreach($sheet['blocks'] as $bi => $block)
            @if(count($block['rows']) >= 1)
            <div x-show="activeBlock[{{ $si }}] === null || activeBlock[{{ $si }}] === {{ $bi }}"
                 class="mb-8 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl shadow-sm overflow-hidden">

                {{-- Judul dokumen --}}
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/30 dark:to-indigo-950/30 border-b border-blue-100 dark:border-blue-900/50 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="text-center flex-1">
                            <p class="text-sm font-bold uppercase tracking-wide text-neutral-900 dark:text-neutral-100">
                                {{ $block['title1'] }}
                            </p>
                            <p class="text-xs font-semibold uppercase tracking-wide text-neutral-700 dark:text-neutral-300 mt-1">
                                {{ $block['title2'] }}
                            </p>
                            <p class="text-sm font-bold text-blue-700 dark:text-blue-400 mt-1.5">
                                {{ $block['title3'] }}
                            </p>
                        </div>
                        <a href="{{ route('honorarium.print') }}?sheet={{ $si }}&block={{ $bi }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition-colors shadow-sm shrink-0">
                            🖨️ Cetak Kategori Ini
                        </a>
                    </div>
                </div>

                {{-- Tabel data --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-xs border-collapse">
                        {{-- Header --}}
                        <thead>
                            <tr class="bg-slate-100 dark:bg-slate-800/60 border-b-2 border-slate-300 dark:border-slate-600">
                                @foreach($block['headers'] as $colIdx => $hdr)
                                    @php
                                        $hdrUp = strtoupper(trim($hdr ?? ''));
                                        $isNo   = $hdrUp === 'NO' || $hdrUp === 'NO.';
                                        $isNama = strpos($hdrUp, 'NAMA') === 0;
                                        $isJab  = strpos($hdrUp, 'JABATAN') !== false || strpos($hdrUp, 'NAMA OPERATOR') !== false;
                                        $isNum  = !$isNo && !$isNama && !$isJab;
                                        $thAlign = $isNum ? 'text-center' : ($isNo ? 'text-center' : 'text-left');
                                        $thWidth = $isNo ? 'w-8' : ($isNama || $isJab ? 'min-w-[140px]' : 'min-w-[90px]');
                                    @endphp
                                    <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 {{ $thAlign }} {{ $thWidth }} whitespace-nowrap">
                                        {{ $hdr ?? '' }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        {{-- Body --}}
                        <tbody>
                            @php $rowNum = 0; @endphp
                            @foreach($block['rows'] as $ri => $row)
                                @php
                                    $rowNum++;
                                    $isEven = $rowNum % 2 === 0;
                                    $bgClass = $isEven ? 'bg-slate-50/50 dark:bg-slate-800/20' : '';

                                    $namaVal = $row[2] ?? '';
                                    $jabVal  = $row[3] ?? '';

                                    // Skip baris yang nama kosong, jabatan ada tapi semua nilai numerik kosong
                                    // (artefak merged-cell Excel — hanya menampilkan jabatan berulang tanpa data)
                                    if ($namaVal === '') {
                                        $hasValue = false;
                                        foreach ($row as $idx => $v) {
                                            if ($idx <= 3) continue; // skip NO, NAMA, JABATAN
                                            $stripped = str_replace([',', '.', ' '], '', preg_replace('/^Rp\s*/i', '', (string)$v));
                                            if ($stripped !== '' && $stripped !== '0' && $stripped !== '-' && is_numeric($stripped) && (float)$stripped != 0) {
                                                $hasValue = true;
                                                break;
                                            }
                                        }
                                        if (!$hasValue) continue;
                                    }

                                    $isDataOnlyRow = ($namaVal === '' && $jabVal === '');
                                @endphp
                                <tr class="border-b border-neutral-100 dark:border-neutral-800 hover:bg-blue-50/40 dark:hover:bg-blue-950/20 transition-colors {{ $bgClass }} {{ $isDataOnlyRow ? 'opacity-70' : '' }}">
                                    @foreach($block['headers'] as $colIdx => $hdr)
                                        @php
                                            $val    = $row[$colIdx] ?? '';
                                            $hdrUp  = strtoupper(trim($hdr ?? ''));
                                            $isNo   = $hdrUp === 'NO' || $hdrUp === 'NO.';
                                            $isNama = strpos($hdrUp, 'NAMA') === 0;
                                            $isJab  = strpos($hdrUp, 'JABATAN') !== false || strpos($hdrUp, 'NAMA OPERATOR') !== false;
                                            $isNum  = !$isNo && !$isNama && !$isJab;

                                            // Format angka (JUMLAH PERKARA = count, bukan currency)
                                            $isCount = str_contains(strtoupper($hdrUp), 'JUMLAH PERKARA');
                                            if ($isNum && !$isCount && $val !== '' && $val !== '-') {
                                                $stripped = str_replace([',', '.', ' '], '', preg_replace('/^Rp\s*/i', '', $val));
                                                if (is_numeric($stripped) && (float)$stripped != 0) {
                                                    $val = 'Rp ' . number_format((float)$stripped, 0, ',', '.');
                                                } elseif ($val === '0' || $val === 'Rp -' || $val === 'Rp 0') {
                                                    $val = 'Rp -';
                                                }
                                            }

                                            $tdAlign = $isNo ? 'text-center' : ($isNum ? 'text-right' : 'text-left');
                                            $tdFont  = ($isNo || $isNama) ? 'font-medium' : '';
                                        @endphp
                                        @if($isNama)
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-1 py-1 {{ $tdFont }}"
                                            x-data="{ editing: false, val: @js((string)($val ?? '')) }"
                                            @dblclick="orig=val; editing=true" title="Klik 2x untuk edit nama">
                                            <span x-show="!editing" x-text="val !== '' ? val : '— Belum diisi —'" :class="val === '' ? 'text-neutral-400 dark:text-neutral-600 font-normal not-italic text-xs tracking-wide' : ''" class="cursor-text block px-1 py-1 text-neutral-800 dark:text-neutral-200 min-h-[1rem]"></span>
                                            <div x-show="editing" x-cloak class="flex items-center gap-1">
                                                <input x-model="val" type="text"
                                                       x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                                                       @keyup.enter="editing=false" @keyup.escape="val=orig; editing=false"
                                                       class="flex-1 border border-blue-400 rounded px-1 py-0.5 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 min-w-0 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100">
                                                <button @click="editing=false" class="text-green-500 hover:text-green-700 font-bold text-sm shrink-0">✓</button>
                                                <button @click="val=orig; editing=false" class="text-red-400 hover:text-red-600 font-bold text-sm shrink-0">✗</button>
                                            </div>
                                        </td>
                                        @else
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-neutral-800 dark:text-neutral-200 {{ $tdAlign }} {{ $tdFont }}">
                                            {{ $val }}
                                        </td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach

                            {{-- Total row --}}
                            @if($block['totalRow'])
                                <tr class="bg-slate-100 dark:bg-slate-700/40 border-t-2 border-slate-400 dark:border-slate-500 font-bold">
                                    @foreach($block['headers'] as $colIdx => $hdr)
                                        @php
                                            $val   = $block['totalRow'][$colIdx] ?? '';
                                            $hdrUp = strtoupper(trim($hdr ?? ''));
                                            $isNo  = $hdrUp === 'NO' || $hdrUp === 'NO.';
                                            $isNama = strpos($hdrUp, 'NAMA') === 0;
                                            $isJab  = strpos($hdrUp, 'JABATAN') !== false;
                                            $isNum  = !$isNo && !$isNama && !$isJab;

                                            $isCount = str_contains(strtoupper($hdrUp), 'JUMLAH PERKARA');
                                            if ($isNum && !$isCount && $val !== '' && $val !== '-') {
                                                $stripped = str_replace([',', '.', ' '], '', preg_replace('/^Rp\s*/i', '', $val));
                                                if (is_numeric($stripped) && (float)$stripped != 0) {
                                                    $val = 'Rp ' . number_format((float)$stripped, 0, ',', '.');
                                                }
                                            }
                                            $tdAlign = $isNo ? 'text-center' : ($isNum ? 'text-right' : 'text-left');
                                        @endphp
                                        <td class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 text-neutral-900 dark:text-neutral-100 {{ $tdAlign }}">
                                            {{ $val }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- Footer / Tanda Tangan --}}
                <div class="px-6 py-5 border-t border-neutral-100 dark:border-neutral-800">
                    @php $footerInfo = $block['footerInfo']; @endphp

                    @if($footerInfo['date'])
                        <div class="flex justify-end mb-6">
                            <p class="text-xs text-neutral-600 dark:text-neutral-400 font-medium">{{ $footerInfo['date'] }}</p>
                        </div>
                    @endif

                    <div class="grid grid-cols-3 gap-6 text-xs">
                        {{-- Kiri: Petugas Pembuat Komitmen --}}
                        <div>
                            @if($footerInfo['left'])
                                @foreach(explode("\n", $footerInfo['left']) as $line)
                                    <p class="font-semibold uppercase text-neutral-700 dark:text-neutral-300 leading-relaxed">{{ $line }}</p>
                                @endforeach
                            @else
                                <p class="font-semibold uppercase text-neutral-700 dark:text-neutral-300">Petugas Pembuat Komitmen<br>Biaya Proses</p>
                            @endif
                            <div class="mt-14 border-t border-dashed border-neutral-400 dark:border-neutral-600 pt-2">
                                <p class="text-neutral-500">{{ $footerInfo['left_name'] ?? '' }}</p>
                            </div>
                        </div>

                        {{-- Tengah: Mengetahui --}}
                        <div class="text-center">
                            @if($footerInfo['center'])
                                @foreach(explode("\n", $footerInfo['center']) as $line)
                                    <p class="font-semibold uppercase text-neutral-700 dark:text-neutral-300 leading-relaxed">{{ $line }}</p>
                                @endforeach
                            @else
                                <p class="font-semibold uppercase text-neutral-700 dark:text-neutral-300">Mengetahui,<br>Kuasa Pengelola Biaya Proses</p>
                            @endif
                            <div class="mt-14 border-t border-dashed border-neutral-400 dark:border-neutral-600 pt-2">
                                <p class="text-neutral-500">{{ $footerInfo['center_name'] ?? '' }}</p>
                            </div>
                        </div>

                        {{-- Kanan: Bendahara --}}
                        <div class="text-right">
                            @if($footerInfo['right'])
                                @foreach(explode("\n", $footerInfo['right']) as $line)
                                    <p class="font-semibold uppercase text-neutral-700 dark:text-neutral-300 leading-relaxed">{{ $line }}</p>
                                @endforeach
                            @else
                                <p class="font-semibold uppercase text-neutral-700 dark:text-neutral-300">Bendahara Biaya Proses</p>
                            @endif
                            <div class="mt-14 border-t border-dashed border-neutral-400 dark:border-neutral-600 pt-2">
                                <p class="text-neutral-500">{{ $footerInfo['right_name'] ?? '' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            @endif
            @endforeach

            @endif {{-- @if($sheet['sheetName'] === 'TIM' && !empty($timData)) --}}

        </div>
        @endforeach

    @else
        {{-- ─── Fallback: tidak ada Excel → tampilkan computed TIM ─── --}}
        <div x-data="{ active: 0, kepBlock: '' }">
            <div class="flex gap-2 mb-6 flex-wrap">
                @php $staticTabs = ['Kepaniteraan', 'TIM', 'OP - STAF']; @endphp
                @foreach($staticTabs as $i => $label)
                    <button @click="active = {{ $i }}"
                            :class="active === {{ $i }}
                                ? 'bg-blue-600 text-white shadow-sm'
                                : 'bg-white dark:bg-neutral-900 text-neutral-700 dark:text-neutral-300 border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800'"
                            class="px-5 py-2.5 rounded-lg text-sm font-medium transition-all duration-200">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            {{-- Kepaniteraan Tab --}}
            <div x-show="active === 0" x-cloak>
                @if(!empty($kepaniteraanData))

                    {{-- ── Tombol Kembali ── --}}
                    <div class="mb-3 flex justify-end">
                        <a href="{{ route('data-print') }}"
                           class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-600 rounded-lg shadow-sm hover:shadow-md hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-all duration-200">
                            <svg class="w-3.5 h-3.5 text-neutral-700 dark:text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            <span class="text-neutral-900 dark:text-white font-semibold">Kembali</span>
                        </a>
                    </div>

                    {{-- ── Dropdown ── --}}
                    <div class="mb-6 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-4">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-3">
                            Pilih Kategori Perkara
                        </label>
                        <div class="relative">
                            <select x-model="kepBlock"
                                    class="w-full appearance-none rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-950 px-4 py-2.5 pr-10 text-sm text-neutral-900 dark:text-neutral-100 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-colors cursor-pointer">
                                <option value="">— Semua Kategori ({{ count(array_filter($kepaniteraanData, fn($b) => count($b['rows']) >= 11)) }}) —</option>
                                @foreach($kepaniteraanData as $ki => $kb)
                                @if(count($kb['rows']) >= 11)
                                <option value="{{ $ki }}">{{ $kb['title'] }} — {{ number_format($kb['jml_perkara'], 0, ',', '.') }} Perkara</option>
                                @endif
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-neutral-500">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    @foreach($kepaniteraanData as $ki => $block)
                    @if(count($block['rows']) >= 11)
                    <div class="mb-8 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl shadow-sm overflow-hidden"
                         x-show="kepBlock === '' || kepBlock === '{{ $ki }}'">

                        <div class="bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-950/30 dark:to-teal-950/30 border-b border-emerald-100 dark:border-emerald-900/50 px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="text-center flex-1">
                                    <p class="text-sm font-bold uppercase tracking-wide text-neutral-900 dark:text-neutral-100">
                                        HONORARIUM BIAYA PENYELESAIAN PERKARA {{ $block['title'] }}
                                    </p>
                                    <p class="text-sm font-bold text-emerald-700 dark:text-emerald-400 mt-1">
                                        Sebanyak {{ number_format($block['jml_perkara'], 0, ',', '.') }} Perkara
                                    </p>
                                </div>

                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs border-collapse">
                                <thead>
                                    <tr class="bg-slate-100 dark:bg-slate-800/60 border-b-2 border-slate-300 dark:border-slate-600">
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center w-8">NO</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[180px]">NAMA</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[200px]">JABATAN</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[90px]">JUMLAH PERKARA</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[90px]">BIAYA</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-right min-w-[110px]">JUMLAH BIAYA</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-right min-w-[90px]">PPH 15%</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[90px]">PPH 5%</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[110px]">NETTO</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[100px]">TANDA TANGAN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $rn = 0; @endphp
                                    @foreach($block['rows'] as $row)
                                        @php $rn++; $bg = $rn % 2 === 0 ? 'bg-slate-50/50 dark:bg-slate-800/20' : ''; @endphp
                                        <tr class="border-b border-neutral-100 dark:border-neutral-800 hover:bg-emerald-50/30 dark:hover:bg-neutral-800/30 transition-colors {{ $bg }}">
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center text-neutral-600">{{ $row['no'] }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-1 py-1 font-medium"
                                                x-data="{ editing: false, val: localStorage.getItem('hon_kep_{{ $ki }}_{{ $loop->index }}') ?? @js($row['nama']) }"
                                                x-init="$watch('val', v => localStorage.setItem('hon_kep_{{ $ki }}_{{ $loop->index }}', v))"
                                                @dblclick="orig=val; editing=true" title="Klik 2x untuk edit nama">
                                                <span x-show="!editing" x-text="val !== '' ? val : '— Belum diisi —'" :class="val === '' ? 'text-neutral-400 dark:text-neutral-600 font-normal not-italic text-xs tracking-wide' : ''" class="cursor-text block px-1 py-1 text-center text-neutral-900 dark:text-neutral-100"></span>
                                                <div x-show="editing" x-cloak class="flex items-center gap-1">
                                                    <input x-model="val" type="text"
                                                           x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                                                           @keyup.enter="editing=false" @keyup.escape="val=orig; editing=false"
                                                           class="flex-1 border border-blue-400 rounded px-1 py-0.5 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 min-w-0 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100">
                                                    <button @click="editing=false" class="text-green-500 hover:text-green-700 font-bold text-sm shrink-0">✓</button>
                                                    <button @click="val=orig; editing=false" class="text-red-400 hover:text-red-600 font-bold text-sm shrink-0">✗</button>
                                                </div>
                                            </td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center text-neutral-700 dark:text-neutral-300">{{ $row['jabatan'] }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center">{{ number_format($row['jml_perkara'], 0, ',', '.') }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center">Rp {{ number_format($row['biaya'], 0, ',', '.') }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-right">Rp {{ number_format($row['jml_biaya'], 0, ',', '.') }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-right">{{ $row['pph15'] > 0 ? 'Rp ' . number_format($row['pph15'], 0, ',', '.') : '-' }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center">{{ $row['pph5'] > 0 ? 'Rp ' . number_format($row['pph5'], 0, ',', '.') : '-' }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center font-medium text-neutral-900 dark:text-neutral-100">Rp {{ number_format($row['netto'], 0, ',', '.') }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2"></td>
                                        </tr>
                                    @endforeach
                                    <tr class="bg-emerald-50 dark:bg-emerald-900/20 border-t-2 border-emerald-400 dark:border-emerald-700 font-bold">
                                        <td colspan="5" class="border border-emerald-200 dark:border-emerald-800 px-2 py-2.5 text-right text-neutral-900 dark:text-neutral-100">TOTAL</td>
                                        <td class="border border-emerald-200 dark:border-emerald-800 px-2 py-2.5 text-right text-neutral-900 dark:text-neutral-100">Rp {{ number_format($block['total']['jml_biaya'], 0, ',', '.') }}</td>
                                        <td class="border border-emerald-200 dark:border-emerald-800 px-2 py-2.5 text-right text-neutral-900 dark:text-neutral-100">{{ $block['total']['pph15'] > 0 ? 'Rp ' . number_format($block['total']['pph15'], 0, ',', '.') : '-' }}</td>
                                        <td class="border border-emerald-200 dark:border-emerald-800 px-2 py-2.5 text-right text-neutral-900 dark:text-neutral-100">{{ $block['total']['pph5'] > 0 ? 'Rp ' . number_format($block['total']['pph5'], 0, ',', '.') : '-' }}</td>
                                        <td class="border border-emerald-200 dark:border-emerald-800 px-2 py-2.5 text-right text-neutral-900 dark:text-neutral-100">Rp {{ number_format($block['total']['netto'], 0, ',', '.') }}</td>
                                        <td class="border border-emerald-200 dark:border-emerald-800 px-2 py-2.5"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        {{-- TTD Section Kepaniteraan --}}
                        @php
                            $ttdDateKep = \Illuminate\Support\Facades\Session::get('excel_tgl_kwitansi')
                                ?: ('Jakarta, ' . \Carbon\Carbon::now()->translatedFormat('d F Y'));
                            $pejabatKep = config('tarif.pejabat');
                        @endphp
                        <div class="px-4 pt-4 pb-2 border-t border-neutral-200 dark:border-neutral-700">
                            <div class="grid grid-cols-3 gap-4 text-xs">
                                {{-- Kiri --}}
                                <div class="flex flex-col">
                                    <div class="font-bold uppercase text-neutral-700 dark:text-neutral-300 text-center leading-tight mb-1">Petugas Pembuat Komitmen<br>Biaya Proses</div>
                                    <div class="h-12"></div>
                                    <div x-data="{ editing: false, val: localStorage.getItem('ttd_petugas') ?? @js($pejabatKep['petugas_pembuat'] ?? 'ST. KRIS NUGROHO, S.H., M.H.') }"
                                         x-init="$watch('val', v => localStorage.setItem('ttd_petugas', v))"
                                         @dblclick="orig=val; editing=true" title="Klik 2x untuk edit">
                                        <span x-show="!editing" x-text="val !== '' ? val : '— Belum diisi —'" :class="val === '' ? 'text-neutral-400 dark:text-neutral-600 font-normal not-italic text-xs tracking-wide' : ''" class="cursor-text block font-bold text-neutral-900 dark:text-neutral-100 text-center"></span>
                                        <div x-show="editing" x-cloak class="flex items-center gap-1">
                                            <input x-model="val" type="text"
                                                   x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                                                   @keyup.enter="editing=false" @keyup.escape="val=orig; editing=false"
                                                   class="flex-1 border border-blue-400 rounded px-1 py-0.5 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 min-w-0 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100">
                                            <button @click="editing=false" class="text-green-500 hover:text-green-700 font-bold shrink-0">✓</button>
                                            <button @click="val=orig; editing=false" class="text-red-400 hover:text-red-600 font-bold shrink-0">✗</button>
                                        </div>
                                    </div>
                                </div>
                                {{-- Tengah --}}
                                <div class="flex flex-col items-center">
                                    <div class="font-bold uppercase text-neutral-700 dark:text-neutral-300 text-center leading-tight mb-1">Mengetahui,<br>Kuasa Pengelola Biaya Proses</div>
                                    <div class="h-12"></div>
                                    <div x-data="{ editing: false, val: localStorage.getItem('ttd_kuasa') ?? @js($pejabatKep['kuasa_pengelola'] ?? 'ASEP NURSOBAH, S.Ag., M.H.') }"
                                         x-init="$watch('val', v => localStorage.setItem('ttd_kuasa', v))"
                                         @dblclick="orig=val; editing=true" title="Klik 2x untuk edit">
                                        <span x-show="!editing" x-text="val !== '' ? val : '— Belum diisi —'" :class="val === '' ? 'text-neutral-400 dark:text-neutral-600 font-normal not-italic text-xs tracking-wide' : ''" class="cursor-text block font-bold text-neutral-900 dark:text-neutral-100 text-center"></span>
                                        <div x-show="editing" x-cloak class="flex items-center gap-1">
                                            <input x-model="val" type="text"
                                                   x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                                                   @keyup.enter="editing=false" @keyup.escape="val=orig; editing=false"
                                                   class="flex-1 border border-blue-400 rounded px-1 py-0.5 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 min-w-0 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100">
                                            <button @click="editing=false" class="text-green-500 hover:text-green-700 font-bold shrink-0">✓</button>
                                            <button @click="val=orig; editing=false" class="text-red-400 hover:text-red-600 font-bold shrink-0">✗</button>
                                        </div>
                                    </div>
                                </div>
                                {{-- Kanan --}}
                                <div class="flex flex-col items-end">
                                    <div class="text-right font-semibold text-neutral-600 dark:text-neutral-400 mb-1">{{ $ttdDateKep }}<br><span class="font-bold uppercase text-neutral-700 dark:text-neutral-300">Bendahara Biaya Proses</span></div>
                                    <div class="h-12"></div>
                                    <div x-data="{ editing: false, val: localStorage.getItem('ttd_bendahara') ?? @js($pejabatKep['bendahara'] ?? 'FARIDA, S.H.') }"
                                         x-init="$watch('val', v => localStorage.setItem('ttd_bendahara', v))"
                                         @dblclick="orig=val; editing=true" title="Klik 2x untuk edit">
                                        <span x-show="!editing" x-text="val !== '' ? val : '— Belum diisi —'" :class="val === '' ? 'text-neutral-400 dark:text-neutral-600 font-normal not-italic text-xs tracking-wide' : ''" class="cursor-text block font-bold text-neutral-900 dark:text-neutral-100 text-right"></span>
                                        <div x-show="editing" x-cloak class="flex items-center gap-1 justify-end">
                                            <input x-model="val" type="text"
                                                   x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                                                   @keyup.enter="editing=false" @keyup.escape="val=orig; editing=false"
                                                   class="flex-1 border border-blue-400 rounded px-1 py-0.5 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 min-w-0 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100">
                                            <button @click="editing=false" class="text-green-500 hover:text-green-700 font-bold shrink-0">✓</button>
                                            <button @click="val=orig; editing=false" class="text-red-400 hover:text-red-600 font-bold shrink-0">✗</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                @else
                    <p class="text-sm text-neutral-500 text-center py-8">Tidak ada data honorarium Kepaniteraan.</p>
                @endif
            </div>

            {{-- TIM Tab --}}
            <div x-show="active === 1" x-cloak>
                @if(!empty($timData))
                <div x-data="{ timCat3: '' }">

                {{-- ── Tombol Kembali ── --}}
                <div class="mb-3 flex justify-end">
                    <a href="{{ route('data-print') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-600 rounded-lg shadow-sm hover:shadow-md hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-all duration-200">
                        <svg class="w-3.5 h-3.5 text-neutral-700 dark:text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        <span class="text-neutral-900 dark:text-white font-semibold">Kembali</span>
                    </a>
                </div>

                {{-- ── Dropdown ── --}}
                <div class="mb-6 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-4">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-3">
                        Pilih Kategori Perkara
                    </label>
                    <div class="relative">
                        <select x-model="timCat3"
                                class="w-full appearance-none rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-950 px-4 py-2.5 pr-10 text-sm text-neutral-900 dark:text-neutral-100 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-colors cursor-pointer">
                            <option value="">— Semua Kategori ({{ count($timData) }}) —</option>
                            @foreach($timData as $ti3 => $tBlock3)
                            <option value="{{ $ti3 }}">{{ $tBlock3['label'] }} ({{ number_format($tBlock3['jumlah_perkara'], 0, ',', '.') }} Perkara)</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-neutral-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/>
                            </svg>
                        </div>
                    </div>
                </div>

                    @foreach($timData as $ti3 => $block)
                    @if(count($block['rows']) >= 1)
                    <div x-show="timCat3 === '' || timCat3 === '{{ $ti3 }}'" class="mb-8 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl shadow-sm overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/30 dark:to-indigo-950/30 border-b border-blue-100 dark:border-blue-900/50 px-6 py-5 text-center">
                            <p class="text-sm font-bold uppercase tracking-wide text-neutral-900 dark:text-neutral-100">
                                HONORARIUM BIAYA PENYELESAIAN PERKARA {{ $block['label'] }}
                            </p>
                            <p class="text-sm font-bold text-blue-700 dark:text-blue-400 mt-1.5">
                                Sebanyak {{ number_format($block['jumlah_perkara'], 0, ',', '.') }} Perkara
                            </p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs border-collapse">
                                <thead>
                                    <tr class="bg-slate-100 dark:bg-slate-800/60 border-b-2 border-slate-300 dark:border-slate-600">
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center w-8">NO</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[160px]">NAMA</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[180px]">JABATAN</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[90px]">JUMLAH PERKARA</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[90px]">BIAYA</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-right min-w-[100px]">JUMLAH BIAYA</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-right min-w-[90px]">PPH 15%</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[90px]">PPH 5%</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[100px]">NETTO</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[100px]">TANDA TANGAN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $rowNum = 0; @endphp
                                    @foreach($block['rows'] as $row)
                                        @php $rowNum++; $bg = $rowNum % 2 === 0 ? 'bg-slate-50/50 dark:bg-slate-800/20' : ''; @endphp
                                        <tr class="border-b border-neutral-100 dark:border-neutral-800 hover:bg-blue-50/40 dark:hover:bg-blue-950/20 transition-colors {{ $bg }}">
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center">{{ $row['no'] }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-1 py-1 font-medium"
                                                x-data="{ editing: false, val: localStorage.getItem('hon_tim_{{ $ti3 }}_{{ $loop->index }}') ?? @js($row['nama']) }"
                                                x-init="$watch('val', v => localStorage.setItem('hon_tim_{{ $ti3 }}_{{ $loop->index }}', v))"
                                                @dblclick="orig=val; editing=true" title="Klik 2x untuk edit nama">
                                                <span x-show="!editing" x-text="val !== '' ? val : '— Belum diisi —'" :class="val === '' ? 'text-neutral-400 dark:text-neutral-600 font-normal not-italic text-xs tracking-wide' : ''" class="cursor-text block px-1 py-1 text-center"></span>
                                                <div x-show="editing" x-cloak class="flex items-center gap-1">
                                                    <input x-model="val" type="text"
                                                           x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                                                           @keyup.enter="editing=false" @keyup.escape="val=orig; editing=false"
                                                           class="flex-1 border border-blue-400 rounded px-1 py-0.5 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 min-w-0 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100">
                                                    <button @click="editing=false" class="text-green-500 hover:text-green-700 font-bold text-sm shrink-0">✓</button>
                                                    <button @click="val=orig; editing=false" class="text-red-400 hover:text-red-600 font-bold text-sm shrink-0">✗</button>
                                                </div>
                                            </td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center">{{ $row['jabatan'] }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center">{{ number_format($row['jumlah_perkara'], 0, ',', '.') }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center">Rp {{ number_format($row['biaya'], 0, ',', '.') }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-right">Rp {{ number_format($row['jumlah_biaya'], 0, ',', '.') }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-right">{{ $row['pph15'] > 0 ? 'Rp ' . number_format($row['pph15'], 0, ',', '.') : '-' }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center">{{ $row['pph5'] > 0 ? 'Rp ' . number_format($row['pph5'], 0, ',', '.') : '-' }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center">Rp {{ number_format($row['netto'], 0, ',', '.') }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2"></td>
                                        </tr>
                                    @endforeach
                                    <tr class="bg-slate-100 dark:bg-slate-700/40 border-t-2 border-slate-400 font-bold">
                                        <td colspan="5" class="border border-slate-300 px-2 py-2.5 text-right">JUMLAH</td>
                                        <td class="border border-slate-300 px-2 py-2.5 text-right">Rp {{ number_format($block['total']['jumlah_biaya'], 0, ',', '.') }}</td>
                                        <td class="border border-slate-300 px-2 py-2.5 text-right">{{ $block['total']['pph15'] > 0 ? 'Rp ' . number_format($block['total']['pph15'], 0, ',', '.') : '-' }}</td>
                                        <td class="border border-slate-300 px-2 py-2.5 text-right">{{ $block['total']['pph5'] > 0 ? 'Rp ' . number_format($block['total']['pph5'], 0, ',', '.') : '-' }}</td>
                                        <td class="border border-slate-300 px-2 py-2.5 text-right">Rp {{ number_format($block['total']['netto'], 0, ',', '.') }}</td>
                                        <td class="border border-slate-300 px-2 py-2.5"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        {{-- TTD Section TIM --}}
                        @php
                            $ttdDateTim3 = \Illuminate\Support\Facades\Session::get('excel_tgl_kwitansi')
                                ?: ('Jakarta, ' . \Carbon\Carbon::now()->translatedFormat('d F Y'));
                            $pejabatTim3 = config('tarif.pejabat');
                        @endphp
                        <div class="px-4 pt-4 pb-2 border-t border-neutral-200 dark:border-neutral-700">
                            <div class="grid grid-cols-3 gap-4 text-xs">
                                {{-- Kiri --}}
                                <div class="flex flex-col">
                                    <div class="font-bold uppercase text-neutral-700 dark:text-neutral-300 text-center leading-tight mb-1">Petugas Pembuat Komitmen<br>Biaya Proses</div>
                                    <div class="h-12"></div>
                                    <div x-data="{ editing: false, val: localStorage.getItem('ttd_petugas') ?? @js($pejabatTim3['petugas_pembuat'] ?? 'ST. KRIS NUGROHO, S.H., M.H.') }"
                                         x-init="$watch('val', v => localStorage.setItem('ttd_petugas', v))"
                                         @dblclick="orig=val; editing=true" title="Klik 2x untuk edit">
                                        <span x-show="!editing" x-text="val !== '' ? val : '— Belum diisi —'" :class="val === '' ? 'text-neutral-400 dark:text-neutral-600 font-normal not-italic text-xs tracking-wide' : ''" class="cursor-text block font-bold text-neutral-900 dark:text-neutral-100 text-center"></span>
                                        <div x-show="editing" x-cloak class="flex items-center gap-1">
                                            <input x-model="val" type="text"
                                                   x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                                                   @keyup.enter="editing=false" @keyup.escape="val=orig; editing=false"
                                                   class="flex-1 border border-blue-400 rounded px-1 py-0.5 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 min-w-0 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100">
                                            <button @click="editing=false" class="text-green-500 hover:text-green-700 font-bold shrink-0">✓</button>
                                            <button @click="val=orig; editing=false" class="text-red-400 hover:text-red-600 font-bold shrink-0">✗</button>
                                        </div>
                                    </div>
                                </div>
                                {{-- Tengah --}}
                                <div class="flex flex-col items-center">
                                    <div class="font-bold uppercase text-neutral-700 dark:text-neutral-300 text-center leading-tight mb-1">Mengetahui,<br>Kuasa Pengelola Biaya Proses</div>
                                    <div class="h-12"></div>
                                    <div x-data="{ editing: false, val: localStorage.getItem('ttd_kuasa') ?? @js($pejabatTim3['kuasa_pengelola'] ?? 'ASEP NURSOBAH, S.Ag., M.H.') }"
                                         x-init="$watch('val', v => localStorage.setItem('ttd_kuasa', v))"
                                         @dblclick="orig=val; editing=true" title="Klik 2x untuk edit">
                                        <span x-show="!editing" x-text="val !== '' ? val : '— Belum diisi —'" :class="val === '' ? 'text-neutral-400 dark:text-neutral-600 font-normal not-italic text-xs tracking-wide' : ''" class="cursor-text block font-bold text-neutral-900 dark:text-neutral-100 text-center"></span>
                                        <div x-show="editing" x-cloak class="flex items-center gap-1">
                                            <input x-model="val" type="text"
                                                   x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                                                   @keyup.enter="editing=false" @keyup.escape="val=orig; editing=false"
                                                   class="flex-1 border border-blue-400 rounded px-1 py-0.5 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 min-w-0 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100">
                                            <button @click="editing=false" class="text-green-500 hover:text-green-700 font-bold shrink-0">✓</button>
                                            <button @click="val=orig; editing=false" class="text-red-400 hover:text-red-600 font-bold shrink-0">✗</button>
                                        </div>
                                    </div>
                                </div>
                                {{-- Kanan --}}
                                <div class="flex flex-col items-end">
                                    <div class="text-right font-semibold text-neutral-600 dark:text-neutral-400 mb-1">{{ $ttdDateTim3 }}<br><span class="font-bold uppercase text-neutral-700 dark:text-neutral-300">Bendahara Biaya Proses</span></div>
                                    <div class="h-12"></div>
                                    <div x-data="{ editing: false, val: localStorage.getItem('ttd_bendahara') ?? @js($pejabatTim3['bendahara'] ?? 'FARIDA, S.H.') }"
                                         x-init="$watch('val', v => localStorage.setItem('ttd_bendahara', v))"
                                         @dblclick="orig=val; editing=true" title="Klik 2x untuk edit">
                                        <span x-show="!editing" x-text="val !== '' ? val : '— Belum diisi —'" :class="val === '' ? 'text-neutral-400 dark:text-neutral-600 font-normal not-italic text-xs tracking-wide' : ''" class="cursor-text block font-bold text-neutral-900 dark:text-neutral-100 text-right"></span>
                                        <div x-show="editing" x-cloak class="flex items-center gap-1 justify-end">
                                            <input x-model="val" type="text"
                                                   x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                                                   @keyup.enter="editing=false" @keyup.escape="val=orig; editing=false"
                                                   class="flex-1 border border-blue-400 rounded px-1 py-0.5 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 min-w-0 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100">
                                            <button @click="editing=false" class="text-green-500 hover:text-green-700 font-bold shrink-0">✓</button>
                                            <button @click="val=orig; editing=false" class="text-red-400 hover:text-red-600 font-bold shrink-0">✗</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- ── Tabel 5 ANGGOTA (jika ada perkara 5 majelis) ── --}}
                    @if(!empty($block['block_5_anggota']))
                    @php $b5 = $block['block_5_anggota']; @endphp
                    <div x-show="timCat3 === '' || timCat3 === '{{ $ti3 }}'" class="mb-8 bg-white dark:bg-neutral-900 border border-amber-300 dark:border-amber-800/60 rounded-xl shadow-sm overflow-hidden">
                        <div class="bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-950/40 dark:to-orange-950/30 border-b border-amber-200 dark:border-amber-900/50 px-6 py-4 text-center">
                            <p class="text-sm font-bold uppercase tracking-wide text-neutral-900 dark:text-neutral-100">
                                {{ $b5['title'] }}
                            </p>
                            <p class="text-xs font-semibold text-neutral-600 dark:text-neutral-400 mt-0.5 uppercase">
                                {{ $b5['subtitle'] }}
                            </p>
                            <p class="text-xs font-bold text-amber-700 dark:text-amber-400 mt-1">
                                {{ $b5['kamar_info'] }}
                            </p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs border-collapse">
                                <thead>
                                    <tr class="bg-slate-100 dark:bg-slate-800/60 border-b-2 border-slate-300 dark:border-slate-600">
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center w-8">NO</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-left min-w-[200px]">NAMA</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-left min-w-[180px]">JABATAN</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[90px]">JUMLAH PERKARA</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-right min-w-[90px]">BIAYA</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-right min-w-[100px]">JUMLAH BIAYA</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-right min-w-[90px]">PPH 15%</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-right min-w-[90px]">PPH 5%</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-right min-w-[100px]">NETTO</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[100px]">TANDA TANGAN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $rowNum5 = 0; @endphp
                                    @foreach($b5['rows'] as $r5)
                                        @php $rowNum5++; $bg5 = $rowNum5 % 2 === 0 ? 'bg-amber-50/30 dark:bg-amber-950/10' : ''; @endphp
                                        <tr class="border-b border-neutral-100 dark:border-neutral-800 hover:bg-amber-50/50 dark:hover:bg-amber-950/20 transition-colors {{ $bg5 }}">
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center">{{ $r5['no'] }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-1 py-1 font-medium"
                                                x-data="{ editing: false, val: localStorage.getItem('hon_tim5_{{ $ti3 }}_{{ $loop->index }}') ?? @js($r5['nama']) }"
                                                x-init="$watch('val', v => localStorage.setItem('hon_tim5_{{ $ti3 }}_{{ $loop->index }}', v))"
                                                @dblclick="orig=val; editing=true" title="Klik 2x untuk edit nama">
                                                <span x-show="!editing" x-text="val !== '' ? val : '— Belum diisi —'" :class="val === '' ? 'text-neutral-400 dark:text-neutral-600 font-normal not-italic text-xs tracking-wide' : ''" class="cursor-text block px-1 py-1"></span>
                                                <div x-show="editing" x-cloak class="flex items-center gap-1">
                                                    <input x-model="val" type="text"
                                                           x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                                                           @keyup.enter="editing=false" @keyup.escape="val=orig; editing=false"
                                                           class="flex-1 border border-amber-400 rounded px-1 py-0.5 text-xs focus:outline-none focus:ring-1 focus:ring-amber-500 min-w-0 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100">
                                                    <button @click="editing=false" class="text-green-500 hover:text-green-700 font-bold text-sm shrink-0">✓</button>
                                                    <button @click="val=orig; editing=false" class="text-red-400 hover:text-red-600 font-bold text-sm shrink-0">✗</button>
                                                </div>
                                            </td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2">{{ $r5['jabatan'] }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center">{{ number_format($r5['jumlah_perkara'], 0, ',', '.') }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-right">Rp {{ number_format($r5['biaya'], 0, ',', '.') }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-right">Rp {{ number_format($r5['jumlah_biaya'], 0, ',', '.') }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-right">{{ $r5['pph15'] > 0 ? 'Rp ' . number_format($r5['pph15'], 0, ',', '.') : '-' }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-right">-</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-right font-medium text-neutral-900 dark:text-neutral-100">Rp {{ number_format($r5['netto'], 0, ',', '.') }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2"></td>
                                        </tr>
                                    @endforeach
                                    <tr class="bg-amber-50 dark:bg-amber-900/20 border-t-2 border-amber-400 font-bold">
                                        <td colspan="5" class="border border-amber-200 dark:border-amber-800 px-2 py-2.5 text-right text-neutral-900 dark:text-neutral-100">TOTAL</td>
                                        <td class="border border-amber-200 dark:border-amber-800 px-2 py-2.5 text-right text-neutral-900 dark:text-neutral-100">Rp {{ number_format($b5['total']['jumlah_biaya'], 0, ',', '.') }}</td>
                                        <td class="border border-amber-200 dark:border-amber-800 px-2 py-2.5 text-right text-neutral-900 dark:text-neutral-100">{{ $b5['total']['pph15'] > 0 ? 'Rp ' . number_format($b5['total']['pph15'], 0, ',', '.') : '-' }}</td>
                                        <td class="border border-amber-200 dark:border-amber-800 px-2 py-2.5 text-right text-neutral-900 dark:text-neutral-100">-</td>
                                        <td class="border border-amber-200 dark:border-amber-800 px-2 py-2.5 text-right text-neutral-900 dark:text-neutral-100">Rp {{ number_format($b5['total']['netto'], 0, ',', '.') }}</td>
                                        <td class="border border-amber-200 dark:border-amber-800 px-2 py-2.5"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        {{-- TTD Section TIM block_5_anggota --}}
                        @php
                            $ttdDateTim5 = \Illuminate\Support\Facades\Session::get('excel_tgl_kwitansi')
                                ?: ('Jakarta, ' . \Carbon\Carbon::now()->translatedFormat('d F Y'));
                            $pejabatTim5 = config('tarif.pejabat');
                        @endphp
                        <div class="px-4 pt-4 pb-2 border-t border-neutral-200 dark:border-neutral-700">
                            <div class="grid grid-cols-3 gap-4 text-xs">
                                <div class="flex flex-col">
                                    <div class="font-bold uppercase text-neutral-700 dark:text-neutral-300 text-center leading-tight mb-1">Petugas Pembuat Komitmen<br>Biaya Proses</div>
                                    <div class="h-12"></div>
                                    <div x-data="{ editing: false, val: localStorage.getItem('ttd_petugas') ?? @js($pejabatTim5['petugas_pembuat'] ?? 'ST. KRIS NUGROHO, S.H., M.H.') }"
                                         x-init="$watch('val', v => localStorage.setItem('ttd_petugas', v))"
                                         @dblclick="orig=val; editing=true" title="Klik 2x untuk edit">
                                        <span x-show="!editing" x-text="val !== '' ? val : '— Belum diisi —'" :class="val === '' ? 'text-neutral-400 dark:text-neutral-600 font-normal not-italic text-xs tracking-wide' : ''" class="cursor-text block font-bold text-neutral-900 dark:text-neutral-100 text-center"></span>
                                        <div x-show="editing" x-cloak class="flex items-center gap-1">
                                            <input x-model="val" type="text"
                                                   x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                                                   @keyup.enter="editing=false" @keyup.escape="val=orig; editing=false"
                                                   class="flex-1 border border-blue-400 rounded px-1 py-0.5 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 min-w-0 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100">
                                            <button @click="editing=false" class="text-green-500 hover:text-green-700 font-bold shrink-0">✓</button>
                                            <button @click="val=orig; editing=false" class="text-red-400 hover:text-red-600 font-bold shrink-0">✗</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col items-center">
                                    <div class="font-bold uppercase text-neutral-700 dark:text-neutral-300 text-center leading-tight mb-1">Mengetahui,<br>Kuasa Pengelola Biaya Proses</div>
                                    <div class="h-12"></div>
                                    <div x-data="{ editing: false, val: localStorage.getItem('ttd_kuasa') ?? @js($pejabatTim5['kuasa_pengelola'] ?? 'ASEP NURSOBAH, S.Ag., M.H.') }"
                                         x-init="$watch('val', v => localStorage.setItem('ttd_kuasa', v))"
                                         @dblclick="orig=val; editing=true" title="Klik 2x untuk edit">
                                        <span x-show="!editing" x-text="val !== '' ? val : '— Belum diisi —'" :class="val === '' ? 'text-neutral-400 dark:text-neutral-600 font-normal not-italic text-xs tracking-wide' : ''" class="cursor-text block font-bold text-neutral-900 dark:text-neutral-100 text-center"></span>
                                        <div x-show="editing" x-cloak class="flex items-center gap-1">
                                            <input x-model="val" type="text"
                                                   x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                                                   @keyup.enter="editing=false" @keyup.escape="val=orig; editing=false"
                                                   class="flex-1 border border-blue-400 rounded px-1 py-0.5 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 min-w-0 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100">
                                            <button @click="editing=false" class="text-green-500 hover:text-green-700 font-bold shrink-0">✓</button>
                                            <button @click="val=orig; editing=false" class="text-red-400 hover:text-red-600 font-bold shrink-0">✗</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end">
                                    <div class="text-right font-semibold text-neutral-600 dark:text-neutral-400 mb-1">{{ $ttdDateTim5 }}<br><span class="font-bold uppercase text-neutral-700 dark:text-neutral-300">Bendahara Biaya Proses</span></div>
                                    <div class="h-12"></div>
                                    <div x-data="{ editing: false, val: localStorage.getItem('ttd_bendahara') ?? @js($pejabatTim5['bendahara'] ?? 'FARIDA, S.H.') }"
                                         x-init="$watch('val', v => localStorage.setItem('ttd_bendahara', v))"
                                         @dblclick="orig=val; editing=true" title="Klik 2x untuk edit">
                                        <span x-show="!editing" x-text="val !== '' ? val : '— Belum diisi —'" :class="val === '' ? 'text-neutral-400 dark:text-neutral-600 font-normal not-italic text-xs tracking-wide' : ''" class="cursor-text block font-bold text-neutral-900 dark:text-neutral-100 text-right"></span>
                                        <div x-show="editing" x-cloak class="flex items-center gap-1 justify-end">
                                            <input x-model="val" type="text"
                                                   x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                                                   @keyup.enter="editing=false" @keyup.escape="val=orig; editing=false"
                                                   class="flex-1 border border-blue-400 rounded px-1 py-0.5 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 min-w-0 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100">
                                            <button @click="editing=false" class="text-green-500 hover:text-green-700 font-bold shrink-0">✓</button>
                                            <button @click="val=orig; editing=false" class="text-red-400 hover:text-red-600 font-bold shrink-0">✗</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach

                </div>{{-- /x-data timCat3 --}}
                @else
                    <p class="text-sm text-neutral-500 text-center py-8">Tidak ada data honorarium TIM.</p>
                @endif
            </div>

            {{-- OP - STAF Tab --}}
            <div x-show="active === 2" x-cloak x-data="{ opBlock: '' }">
                @if(!empty($opStafData))

                    {{-- ── Tombol Kembali ── --}}
                    <div class="mb-3 flex justify-end">
                        <a href="{{ route('data-print') }}"
                           class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-600 rounded-lg shadow-sm hover:shadow-md hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-all duration-200">
                            <svg class="w-3.5 h-3.5 text-neutral-700 dark:text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            <span class="text-neutral-900 dark:text-white font-semibold">Kembali</span>
                        </a>
                    </div>

                    {{-- ── Dropdown ── --}}
                    <div class="mb-6 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-4">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-3">
                            Pilih Kategori Perkara
                        </label>
                        <div class="flex items-center gap-3">
                            <div class="relative flex-1">
                                <select x-model="opBlock"
                                        class="w-full appearance-none rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-950 px-4 py-2.5 pr-10 text-sm text-neutral-900 dark:text-neutral-100 shadow-sm focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20 transition-colors cursor-pointer">
                                    <option value="">— Semua Kategori ({{ count($opStafData) }}) —</option>
                                    @foreach($opStafData as $oi => $ob)
                                        <option value="{{ $oi }}">{{ $ob['title'] }} — {{ number_format($ob['total']['jml'], 0, ',', '.') }} Perkara</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-neutral-500">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/>
                                    </svg>
                                </div>
                            </div>
                            <a :href="opBlock !== '' ? '{{ route('honorarium.print') }}?computed=op-staf&cat=' + opBlock : '{{ route('honorarium.print') }}?computed=op-staf'"
                               target="_blank" rel="noopener"
                               class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition-colors shadow-sm shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <polyline points="6 9 6 2 18 2 18 9"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
                                    <rect x="6" y="14" width="12" height="8"/>
                                </svg>
                                <span x-text="opBlock !== '' ? '🖨️ Cetak Kategori Ini' : '🖨️ Cetak Semua OP-STAF'"></span>
                            </a>
                        </div>
                    </div>

                    @foreach($opStafData as $oi => $block)
                    <div class="mb-8 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl shadow-sm overflow-hidden"
                         x-show="opBlock === '' || opBlock === '{{ $oi }}'">

                        {{-- Header --}}
                        <div class="bg-gradient-to-r from-orange-50 to-amber-50 dark:from-orange-950/30 dark:to-amber-950/30 border-b border-orange-100 dark:border-orange-900/50 px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="text-center flex-1">
                                    <p class="text-xs font-semibold uppercase text-neutral-500 dark:text-neutral-400 tracking-wider">HONORARIUM BIAYA PENYELESAIAN PERKARA</p>
                                    <p class="text-sm font-bold uppercase tracking-wide text-neutral-900 dark:text-neutral-100 mt-0.5">
                                        {{ $block['title'] }}
                                    </p>
                                    <p class="text-xs text-orange-600 dark:text-orange-400 mt-1">
                                        Sebanyak {{ number_format($block['total_perkara'] ?? $block['total']['jml'], 0, ',', '.') }} Perkara — Yang Diterima Operator
                                    </p>
                                </div>
                                <a href="{{ route('honorarium.print') }}?computed=op-staf&cat={{ $oi }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition-colors shadow-sm shrink-0">
                                    🖨️ Cetak Kategori Ini
                                </a>
                            </div>
                        </div>

                        {{-- Tabel --}}
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs border-collapse">
                                <thead>
                                    <tr class="bg-slate-100 dark:bg-slate-800/60 border-b-2 border-slate-300 dark:border-slate-600">
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center w-8">NO</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[200px]">NAMA OPERATOR</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[100px]">JABATAN</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[80px]">JUMLAH PERKARA</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[80px]">BIAYA</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-right min-w-[110px]">JUMLAH BIAYA</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[90px]">PPH 5%</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[110px]">NETTO</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[100px]">TANDA TANGAN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $rn = 0; @endphp
                                    @foreach($block['rows'] as $row)
                                        @php $rn++; $bg = $rn % 2 === 0 ? 'bg-slate-50/50 dark:bg-slate-800/20' : ''; @endphp
                                        <tr class="border-b border-neutral-100 dark:border-neutral-800 hover:bg-orange-50/30 dark:hover:bg-neutral-800/30 transition-colors {{ $bg }}">
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center text-neutral-600">{{ $row['no'] }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-1 py-1 font-medium"
                                                x-data="{ editing: false, val: localStorage.getItem('hon_op_{{ $oi }}_{{ $loop->index }}') ?? '' }"
                                                x-init="$watch('val', v => localStorage.setItem('hon_op_{{ $oi }}_{{ $loop->index }}', v))"
                                                @dblclick="orig=val; editing=true" title="Klik 2x untuk edit nama">
                                                <span x-show="!editing" x-text="val !== '' ? val : '— Belum diisi —'" :class="val === '' ? 'text-neutral-400 dark:text-neutral-600 font-normal not-italic text-xs tracking-wide' : ''" class="cursor-text block px-1 py-1 min-h-[1.5rem]"></span>
                                                <div x-show="editing" x-cloak class="flex items-center gap-1">
                                                    <input x-model="val" type="text"
                                                           x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                                                           @keyup.enter="editing=false" @keyup.escape="val=orig; editing=false"
                                                           class="flex-1 border border-orange-400 rounded px-1 py-0.5 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 min-w-0 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100">
                                                    <button @click="editing=false" class="text-green-500 hover:text-green-700 font-bold text-sm shrink-0">✓</button>
                                                    <button @click="val=orig; editing=false" class="text-red-400 hover:text-red-600 font-bold text-sm shrink-0">✗</button>
                                                </div>
                                            </td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center text-neutral-600 dark:text-neutral-400">OPERATOR/PENGETIK</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center text-neutral-800 dark:text-neutral-200">{{ number_format($row['jml'], 0, ',', '.') }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center text-neutral-800 dark:text-neutral-200">Rp {{ number_format($row['tarif'], 0, ',', '.') }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-right text-neutral-800 dark:text-neutral-200">Rp {{ number_format($row['bruto'], 0, ',', '.') }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center text-neutral-800 dark:text-neutral-200">Rp {{ number_format($row['pph5'], 0, ',', '.') }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center font-medium text-neutral-900 dark:text-neutral-100">Rp {{ number_format($row['netto'], 0, ',', '.') }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2"></td>
                                        </tr>
                                    @endforeach
                                    {{-- Total row --}}
                                    <tr class="bg-orange-50 dark:bg-orange-900/20 border-t-2 border-orange-400 dark:border-orange-700 font-bold">
                                        <td colspan="3" class="border border-orange-200 dark:border-orange-800 px-2 py-2.5 text-right text-neutral-900 dark:text-neutral-100">TOTAL KESELURUHAN</td>
                                        <td class="border border-orange-200 dark:border-orange-800 px-2 py-2.5 text-center text-neutral-900 dark:text-neutral-100">{{ number_format($block['total']['jml'], 0, ',', '.') }}</td>
                                        <td class="border border-orange-200 dark:border-orange-800 px-2 py-2.5"></td>
                                        <td class="border border-orange-200 dark:border-orange-800 px-2 py-2.5 text-right text-neutral-900 dark:text-neutral-100">Rp {{ number_format($block['total']['bruto'], 0, ',', '.') }}</td>
                                        <td class="border border-orange-200 dark:border-orange-800 px-2 py-2.5 text-right text-neutral-900 dark:text-neutral-100">Rp {{ number_format($block['total']['pph5'], 0, ',', '.') }}</td>
                                        <td class="border border-orange-200 dark:border-orange-800 px-2 py-2.5 text-right text-neutral-900 dark:text-neutral-100">Rp {{ number_format($block['total']['netto'], 0, ',', '.') }}</td>
                                        <td class="border border-orange-200 dark:border-orange-800 px-2 py-2.5"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        {{-- TTD Section OP - STAF: hanya operator kamar di kanan --}}
                        @php
                            $ttdDateOp = \Illuminate\Support\Facades\Session::get('excel_tgl_kwitansi')
                                ?: ('Jakarta, ' . \Carbon\Carbon::now()->translatedFormat('d F Y'));
                            $pejabatOp = config('tarif.pejabat');
                        @endphp
                        <div class="px-4 pt-4 pb-2 border-t border-neutral-200 dark:border-neutral-700">
                            <div class="flex flex-col items-end text-xs">
                                <div class="text-right font-semibold text-neutral-600 dark:text-neutral-400 mb-1">{{ $ttdDateOp }}</div>
                                <div class="font-bold uppercase text-neutral-700 dark:text-neutral-300 text-right mb-1">Operator Kamar Perdata</div>
                                <div class="h-12"></div>
                                <div x-data="{ editing: false, val: localStorage.getItem('ttd_op_mulki') ?? @js($pejabatOp['operator_kamar'] ?? 'Mulki Ardiansyah, S.Kom.') }"
                                     x-init="$watch('val', v => localStorage.setItem('ttd_op_mulki', v))"
                                     @dblclick="orig=val; editing=true" title="Klik 2x untuk edit">
                                    <span x-show="!editing" x-text="val !== '' ? val : '— Belum diisi —'" :class="val === '' ? 'text-neutral-400 dark:text-neutral-600 font-normal not-italic text-xs tracking-wide' : ''" class="cursor-text block font-bold text-neutral-900 dark:text-neutral-100 text-right"></span>
                                    <div x-show="editing" x-cloak class="flex items-center gap-1 justify-end">
                                        <input x-model="val" type="text"
                                               x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                                               @keyup.enter="editing=false" @keyup.escape="val=orig; editing=false"
                                               class="border border-orange-400 rounded px-1 py-0.5 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100">
                                        <button @click="editing=false" class="text-green-500 hover:text-green-700 font-bold shrink-0">✓</button>
                                        <button @click="val=orig; editing=false" class="text-red-400 hover:text-red-600 font-bold shrink-0">✗</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach

                @else
                    <p class="text-sm text-neutral-500 text-center py-8">Tidak ada data honorarium OP - STAF.</p>
                @endif
            </div>

        </div>
    @endif

</div>

@endsection

@section('scripts')
<script>
function honorariumApp() {
    return {
        activeSheet: 0,
        activeBlock: {},

        init() {
            // Inisialisasi activeBlock untuk setiap sheet → null = tampil semua
            @foreach($sheets as $si => $sheet)
            this.activeBlock[{{ $si }}] = null;
            @endforeach
        }
    };
}
</script>
@endsection
