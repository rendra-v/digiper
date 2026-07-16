@extends('layout')

@section('title', 'Rekap Keseluruhan 2')

@section('content')
<div class="min-h-screen">

    {{-- ─── Header Page ─── --}}
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="text-4xl font-semibold tracking-tight mb-3">Rekap Keseluruhan 2</h2>
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

    @if(!$error && isset($rekap) && count($rekap['rows']) > 0)
        @php
            $jenisList = $rekap['jenis_list'];
            $rows      = $rekap['rows'];
        @endphp

        {{-- ─── Title ─── --}}
        <div class="mb-5 text-center">
            <p class="text-sm font-bold uppercase tracking-wide text-neutral-900 dark:text-neutral-100">
                REKAPITULASI BIAYA PENYELESAIAN PERKARA YANG DIPUTUS
            </p>
            <p class="text-sm font-bold uppercase tracking-wide text-neutral-800 dark:text-neutral-200 mt-0.5">
                YANG USIANYA KURANG DARI 120 HARI SEJAK REGISTER PERKARA MASUK
            </p>
            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Distribusi Biaya Per Peruntukan</p>
        </div>

        {{-- ─── Table ─── --}}
        <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                @php
                    $thBase = 'border border-neutral-300 dark:border-neutral-600 text-xs font-bold text-neutral-800 dark:text-neutral-200 text-center align-middle whitespace-nowrap px-2 py-2';
                @endphp
                <table class="w-full text-xs border-collapse" style="min-width: 1600px;">
                    <thead>
                        {{-- Baris 1: NO | label | PERUNTUKAN | PPh | Jenis Perkara ... | TOTAL --}}
                        <tr class="bg-sky-100 dark:bg-sky-900/40">
                            <th rowspan="2" class="{{ $thBase }}">NO</th>
                            <th rowspan="2" class="{{ $thBase }}"></th>
                            <th rowspan="2" class="{{ $thBase }} text-left px-3">PERUNTUKAN</th>
                            <th rowspan="2" class="{{ $thBase }}">PPh</th>
                            @foreach($jenisList as $jenis)
                                <th colspan="3" class="{{ $thBase }}">{{ $jenis['label'] }}</th>
                            @endforeach
                            <th rowspan="2" class="{{ $thBase }}">TOTAL</th>
                        </tr>
                        {{-- Baris 2: BIAYA | JML | SUB TOTAL per jenis --}}
                        <tr class="bg-sky-50 dark:bg-sky-950/40">
                            @foreach($jenisList as $jenis)
                                <th class="{{ $thBase }}">BIAYA</th>
                                <th class="{{ $thBase }}">JML</th>
                                <th class="{{ $thBase }}">SUB TOTAL</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php $prevNo = null; @endphp
                        @foreach($rows as $row)
                            @php
                                $isFirst15 = ($row['no'] !== $prevNo);
                                $prevNo    = $row['no'];
                                $trBg      = 'hover:bg-blue-50/30 dark:hover:bg-neutral-800/30';
                            @endphp
                            <tr class="border-b border-neutral-200 dark:border-neutral-800 {{ $trBg }}">
                                {{-- NO --}}
                                <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-1 text-center text-xs">
                                    @if($isFirst15) {{ $row['no'] }} @endif
                                </td>
                                {{-- label_no --}}
                                <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-1 text-center text-xs">
                                    {{ $row['label_no'] }}
                                </td>
                                {{-- PERUNTUKAN --}}
                                <td class="border border-neutral-200 dark:border-neutral-700 px-3 py-1 text-left text-xs">
                                    {{ $row['peruntukan'] }}
                                </td>
                                {{-- PPh pool --}}
                                <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-1 text-center text-xs text-neutral-500">
                                    {{ $row['pph_pool'] }}%
                                </td>
                                {{-- Per jenis: BIAYA | JML | SUB TOTAL --}}
                                @foreach($jenisList as $jenis)
                                    @php $j = $row['per_jenis'][$jenis['key']]; @endphp
                                    <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1 text-right text-xs">
                                        {{ $j['biaya'] > 0 ? number_format($j['biaya'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1 text-right text-xs">
                                        {{ $j['jumlah'] > 0 ? number_format($j['jumlah'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1 text-right text-xs">
                                        {{ $j['sub_total'] > 0 ? number_format($j['sub_total'], 0, ',', '.') : '-' }}
                                    </td>
                                @endforeach
                                {{-- TOTAL baris --}}
                                <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1 text-right text-xs font-semibold">
                                    {{ $row['total'] > 0 ? number_format($row['total'], 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                        @endforeach

                        {{-- Baris JUMLAH --}}
                        <tr class="bg-neutral-100 dark:bg-neutral-800/60 border-b border-neutral-200 dark:border-neutral-800 font-bold">
                            <td colspan="4" class="border border-neutral-200 dark:border-neutral-700 px-3 py-1.5 text-xs font-bold text-neutral-900 dark:text-neutral-100">
                                JUMLAH
                            </td>
                            @foreach($jenisList as $jenis)
                                <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-right text-xs font-bold" colspan="2"></td>
                                <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-right text-xs font-bold">
                                    {{ $rekap['jumlah_jenis'][$jenis['key']] > 0 ? number_format($rekap['jumlah_jenis'][$jenis['key']], 0, ',', '.') : '-' }}
                                </td>
                            @endforeach
                            <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-right text-xs font-bold">
                                {{ $rekap['jumlah_grand'] > 0 ? number_format($rekap['jumlah_grand'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
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
