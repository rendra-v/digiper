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

    @if(!$error && isset($rekap))
        {{-- ─── Title ─── --}}
        <div class="mb-2 text-center">
            <p class="text-sm font-bold uppercase tracking-wide text-neutral-900 dark:text-neutral-100">
                REKAPITULASI BIAYA PENYELESAIAN PERKARA YANG DIPUTUS
            </p>
            <p class="text-sm font-bold uppercase tracking-wide text-neutral-800 dark:text-neutral-200 mt-0.5">
                YANG USIANYA KURANG DARI 120 HARI SEJAK REGISTER PERKARA MASUK
            </p>
        </div>

        {{-- ─── Table ─── --}}

        <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
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
                    <thead>
                        <tr class="bg-sky-100 dark:bg-sky-900/40 border-b border-neutral-200 dark:border-neutral-800 text-center font-bold text-neutral-800 dark:text-neutral-200">
                            <td rowspan="3" class="border border-neutral-200 dark:border-neutral-700 px-2 py-1.5 align-middle">NO.</td>
                            <td rowspan="3" class="border border-neutral-200 dark:border-neutral-700 px-2 py-1.5 align-middle">JENIS PERKARA</td>
                            <td rowspan="3" class="border border-neutral-200 dark:border-neutral-700 px-2 py-1.5 align-middle">KLASIFIKASI</td>
                            <td colspan="10" class="border border-neutral-200 dark:border-neutral-700 px-2 py-1.5">JUMLAH PERKARA</td>
                            <td rowspan="3" class="border border-neutral-200 dark:border-neutral-700 px-2 py-1.5 align-middle">TOTAL JML MINUTASI TEPAT WAKTU (120 HARI)</td>
                        </tr>
                        <tr class="bg-sky-100 dark:bg-sky-900/40 border-b border-neutral-200 dark:border-neutral-800 text-center font-bold text-neutral-800 dark:text-neutral-200">
                            <td colspan="5" class="border border-neutral-200 dark:border-neutral-700 px-2 py-1.5">KASASI</td>
                            <td colspan="5" class="border border-neutral-200 dark:border-neutral-700 px-2 py-1.5">PENINJAUAN KEMBALI</td>
                        </tr>
                        <tr class="bg-sky-100 dark:bg-sky-900/40 border-b border-neutral-200 dark:border-neutral-800 text-center font-bold text-neutral-800 dark:text-neutral-200 text-[10px] leading-tight">
                            <td class="border border-neutral-200 dark:border-neutral-700 px-1 py-1">SISA S.D TH<br>LALU</td>
                            <td class="border border-neutral-200 dark:border-neutral-700 px-1 py-1">MASUK TH<br>INI</td>
                            <td class="border border-neutral-200 dark:border-neutral-700 px-1 py-1">PUTUS</td>
                            <td class="border border-neutral-200 dark:border-neutral-700 px-1 py-1">BELUM PUTUS</td>
                            <td class="border border-neutral-200 dark:border-neutral-700 px-1 py-1">JML MINUT TEPAT WAKTU (120 HARI)</td>
                            <td class="border border-neutral-200 dark:border-neutral-700 px-1 py-1">SISA S.D TH<br>LALU</td>
                            <td class="border border-neutral-200 dark:border-neutral-700 px-1 py-1">MASUK TH<br>INI</td>
                            <td class="border border-neutral-200 dark:border-neutral-700 px-1 py-1">PUTUS</td>
                            <td class="border border-neutral-200 dark:border-neutral-700 px-1 py-1">BELUM PUTUS</td>
                            <td class="border border-neutral-200 dark:border-neutral-700 px-1 py-1">JML MINUT TEPAT WAKTU (120 HARI)</td>
                        </tr>
                        <tr class="bg-sky-100 dark:bg-sky-900/40 border-b border-neutral-200 dark:border-neutral-800 text-center text-[10px] text-neutral-600 dark:text-neutral-400">
                            @for($i = 1; $i <= 14; $i++)
                                <td class="border border-neutral-200 dark:border-neutral-700 px-1 py-0.5">{{ $i }}</td>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rekap['rows'] as $row)
                            @php
                                $trBg = $row['is_category'] 
                                    ? 'bg-cyan-50 dark:bg-cyan-900/20 font-bold text-neutral-900 dark:text-neutral-100'
                                    : 'hover:bg-blue-50/30 dark:hover:bg-neutral-800/30 text-neutral-800 dark:text-neutral-200';
                            @endphp
                            <tr class="border-b border-neutral-200 dark:border-neutral-800 {{ $trBg }}">
                                <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-1.5 text-center">{{ $row['no'] }}</td>
                                <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-1.5 text-left">{{ $row['perkara'] }}</td>
                                <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-1.5 text-left">{{ $row['klasifikasi'] }}</td>
                                
                                {{-- Kasasi --}}
                                <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-right">{{ $row['kasasi']['sisa'] > 0 ? number_format($row['kasasi']['sisa'], 0, ',', '.') : '-' }}</td>
                                <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-right">{{ $row['kasasi']['masuk'] > 0 ? number_format($row['kasasi']['masuk'], 0, ',', '.') : '-' }}</td>
                                <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-right">{{ $row['kasasi']['putus'] > 0 ? number_format($row['kasasi']['putus'], 0, ',', '.') : '-' }}</td>
                                <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-right">{{ $row['kasasi']['blm'] > 0 ? number_format($row['kasasi']['blm'], 0, ',', '.') : '-' }}</td>
                                <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-right">{{ $row['kasasi']['minut'] > 0 ? number_format($row['kasasi']['minut'], 0, ',', '.') : '-' }}</td>

                                {{-- PK --}}
                                <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-right">{{ $row['pk']['sisa'] > 0 ? number_format($row['pk']['sisa'], 0, ',', '.') : '-' }}</td>
                                <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-right">{{ $row['pk']['masuk'] > 0 ? number_format($row['pk']['masuk'], 0, ',', '.') : '-' }}</td>
                                <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-right">{{ $row['pk']['putus'] > 0 ? number_format($row['pk']['putus'], 0, ',', '.') : '-' }}</td>
                                <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-right">{{ $row['pk']['blm'] > 0 ? number_format($row['pk']['blm'], 0, ',', '.') : '-' }}</td>
                                <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-right">{{ $row['pk']['minut'] > 0 ? number_format($row['pk']['minut'], 0, ',', '.') : '-' }}</td>

                                {{-- Total --}}
                                <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-right font-bold">{{ $row['total_minut'] > 0 ? number_format($row['total_minut'], 0, ',', '.') : '-' }}</td>
                            </tr>
                        @endforeach
                        
                        {{-- Baris JUMLAH --}}
                        @php $t = $rekap['total']; @endphp
                        <tr class="bg-neutral-100 dark:bg-neutral-800/60 border-b border-neutral-200 dark:border-neutral-800 font-bold text-neutral-900 dark:text-neutral-100">
                            <td colspan="3" class="border border-neutral-200 dark:border-neutral-700 px-2 py-1.5 text-center tracking-wider">TOTAL</td>
                            <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-right">{{ $t['kasasi']['sisa'] > 0 ? number_format($t['kasasi']['sisa'], 0, ',', '.') : '-' }}</td>
                            <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-right">{{ $t['kasasi']['masuk'] > 0 ? number_format($t['kasasi']['masuk'], 0, ',', '.') : '-' }}</td>
                            <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-right">{{ $t['kasasi']['putus'] > 0 ? number_format($t['kasasi']['putus'], 0, ',', '.') : '-' }}</td>
                            <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-right">{{ $t['kasasi']['blm'] > 0 ? number_format($t['kasasi']['blm'], 0, ',', '.') : '-' }}</td>
                            <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-right">{{ $t['kasasi']['minut'] > 0 ? number_format($t['kasasi']['minut'], 0, ',', '.') : '-' }}</td>
                            <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-right">{{ $t['pk']['sisa'] > 0 ? number_format($t['pk']['sisa'], 0, ',', '.') : '-' }}</td>
                            <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-right">{{ $t['pk']['masuk'] > 0 ? number_format($t['pk']['masuk'], 0, ',', '.') : '-' }}</td>
                            <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-right">{{ $t['pk']['putus'] > 0 ? number_format($t['pk']['putus'], 0, ',', '.') : '-' }}</td>
                            <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-right">{{ $t['pk']['blm'] > 0 ? number_format($t['pk']['blm'], 0, ',', '.') : '-' }}</td>
                            <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-right">{{ $t['pk']['minut'] > 0 ? number_format($t['pk']['minut'], 0, ',', '.') : '-' }}</td>
                            <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-right font-bold">{{ $t['total_minut'] > 0 ? number_format($t['total_minut'], 0, ',', '.') : '-' }}</td>
                        </tr>
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
                            ASEP NURSOBAH, S.Ag., M.H.
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
                            ST. KRIS NUGROHO, S.H., M.H.
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
                            FARIDA,SH
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
                        Dr. SUDHARMAWATININGSIH, S.H., M.Hum.
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
