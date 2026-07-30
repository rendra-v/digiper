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
                            <option value="" disabled selected>Pilih halaman</option>
                            <option value="{{ route('data-print') }}">📄&nbsp; Data Print Perkara</option>
                            <option value="{{ route('sheet-cek') }}">📋&nbsp; Sheet Cek</option>
                            <option value="{{ route('rekap-keseluruhan-2') }}">📈&nbsp; Rekap Keseluruhan 2</option>
                            <option value="{{ route('rekap-keseluruhan-3') }}">📋&nbsp; Rekap Keseluruhan 3</option>
                            <option value="{{ route('honorarium') }}">💰&nbsp; Honorarium Biaya</option>
                                <option value="{{ route('periode-laporan') }}">📅&nbsp; Periode Laporan</option>
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
                @if(!$error && !empty($groups))
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
                @endif
            </div>
            @if(!$error && !empty($groups))
            <a href="{{ route('rekap-keseluruhan-2') }}"
               class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors duration-200 shadow-sm">
                Selanjutnya
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            @endif
        </div>
    </div>

    {{-- ─── Error ─── --}}
    @if($error)
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/50 rounded-lg">
            <p class="text-sm text-red-700 dark:text-red-400">{{ $error }}</p>
        </div>
    @endif

    @if(!$error && !empty($groups))

        {{-- ─── Title ─── --}}
        <div class="mb-4 text-center">
            <p class="text-sm font-bold uppercase tracking-wide text-neutral-900 dark:text-neutral-100">
                REKAPITULASI BIAYA PENYELESAIAN PERKARA YANG DIPUTUS
            </p>
            <p class="text-sm font-bold uppercase tracking-wide text-neutral-800 dark:text-neutral-200 mt-0.5">
                YANG USIANYA KURANG DARI 120 HARI SEJAK REGISTER PERKARA MASUK
            </p>
            @php $laporan_periode = \Illuminate\Support\Facades\Session::get('excel_laporan_periode'); @endphp
            @if($laporan_periode)
            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Periode: {{ strtoupper($laporan_periode) }}</p>
            @endif
        </div>

        {{-- ─── Table ─── --}}
        <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl shadow-sm overflow-hidden mb-10">
            <div class="overflow-x-auto">
                <table class="w-full text-xs border-collapse" style="min-width: 960px;">
                    <colgroup>
                        <col style="width:3%">      {{-- No --}}
                        <col style="width:14%">     {{-- Jenis Perkara --}}
                        <col style="width:10%">     {{-- Klasifikasi --}}
                        <col style="width:6%">      {{-- Kasasi: Jumlah --}}
                        <col style="width:9%">      {{-- Kasasi: Biaya (Rp) --}}
                        <col style="width:11%">     {{-- Kasasi: Total --}}
                        <col style="width:6%">      {{-- PK: Jumlah --}}
                        <col style="width:9%">      {{-- PK: Biaya (Rp) --}}
                        <col style="width:11%">     {{-- PK: Total --}}
                        <col style="width:11%">     {{-- Grand Total --}}
                    </colgroup>
                    <thead>
                        {{-- Baris header 1 --}}
                        <tr class="bg-sky-100 dark:bg-sky-900/40 border-b border-neutral-300 dark:border-neutral-700">
                            <th rowspan="2" class="border border-neutral-300 dark:border-neutral-600 px-2 py-2 text-center font-bold text-neutral-800 dark:text-neutral-200 align-middle">No</th>
                            <th rowspan="2" class="border border-neutral-300 dark:border-neutral-600 px-2 py-2 text-center font-bold text-neutral-800 dark:text-neutral-200 align-middle">Jenis Perkara</th>
                            <th rowspan="2" class="border border-neutral-300 dark:border-neutral-600 px-2 py-2 text-center font-bold text-neutral-800 dark:text-neutral-200 align-middle">Klasifikasi</th>
                            <th colspan="3" class="border border-neutral-300 dark:border-neutral-600 px-2 py-2 text-center font-bold text-neutral-800 dark:text-neutral-200">KASASI</th>
                            <th colspan="3" class="border border-neutral-300 dark:border-neutral-600 px-2 py-2 text-center font-bold text-neutral-800 dark:text-neutral-200">PENINJAUAN KEMBALI (PK)</th>
                            <th rowspan="2" class="border border-neutral-300 dark:border-neutral-600 px-2 py-2 text-center font-bold text-neutral-800 dark:text-neutral-200 align-middle">Grand Total (Rp)</th>
                        </tr>
                        {{-- Baris header 2 --}}
                        <tr class="bg-sky-100 dark:bg-sky-900/40 border-b border-neutral-300 dark:border-neutral-700">
                            <th class="border border-neutral-300 dark:border-neutral-600 px-2 py-2 text-center font-bold text-neutral-700 dark:text-neutral-300">Jumlah Perkara</th>
                            <th class="border border-neutral-300 dark:border-neutral-600 px-2 py-2 text-center font-bold text-neutral-700 dark:text-neutral-300">Biaya (Rp)</th>
                            <th class="border border-neutral-300 dark:border-neutral-600 px-2 py-2 text-center font-bold text-neutral-700 dark:text-neutral-300">Jumlah Biaya (Rp)</th>
                            <th class="border border-neutral-300 dark:border-neutral-600 px-2 py-2 text-center font-bold text-neutral-700 dark:text-neutral-300">Jumlah Perkara</th>
                            <th class="border border-neutral-300 dark:border-neutral-600 px-2 py-2 text-center font-bold text-neutral-700 dark:text-neutral-300">Biaya (Rp)</th>
                            <th class="border border-neutral-300 dark:border-neutral-600 px-2 py-2 text-center font-bold text-neutral-700 dark:text-neutral-300">Jumlah Biaya (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $fmt = fn($v) => $v > 0 ? number_format($v, 0, ',', '.') : '-';
                            $fmtN = fn($v) => $v > 0 ? number_format($v, 0, ',', '.') : '-';
                        @endphp

                        @foreach($groups as $group)
                            @php $rowCount = count($group['rows']); @endphp

                            @foreach($group['rows'] as $i => $row)
                                <tr class="border-b border-neutral-200 dark:border-neutral-800 hover:bg-blue-50/30 dark:hover:bg-neutral-800/30">
                                    @if($i === 0)
                                        {{-- No & Jenis Perkara hanya muncul di baris pertama, span seluruh data rows --}}
                                        <td rowspan="{{ $rowCount }}"
                                            class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center font-bold text-neutral-900 dark:text-neutral-100 align-middle bg-cyan-50 dark:bg-cyan-900/20">
                                            {{ $group['no'] }}
                                        </td>
                                        <td rowspan="{{ $rowCount }}"
                                            class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center font-bold text-neutral-900 dark:text-neutral-100 align-middle bg-cyan-50 dark:bg-cyan-900/20">
                                            {{ $group['label'] }}
                                        </td>
                                    @endif
                                    <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center text-neutral-800 dark:text-neutral-200">
                                        {{ $row['label'] }}
                                    </td>
                                    {{-- Kasasi --}}
                                    <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center text-neutral-800 dark:text-neutral-200">
                                        {{ $row['kasasi_jumlah'] > 0 ? number_format($row['kasasi_jumlah'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center text-neutral-800 dark:text-neutral-200">
                                        {{ $row['kasasi_biaya'] > 0 ? number_format($row['kasasi_biaya'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center text-neutral-800 dark:text-neutral-200">
                                        {{ $row['kasasi_total'] > 0 ? number_format($row['kasasi_total'], 0, ',', '.') : '-' }}
                                    </td>
                                    {{-- PK --}}
                                    <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center text-neutral-800 dark:text-neutral-200">
                                        {{ $row['pk_jumlah'] > 0 ? number_format($row['pk_jumlah'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center text-neutral-800 dark:text-neutral-200">
                                        {{ $row['pk_biaya'] > 0 ? number_format($row['pk_biaya'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center text-neutral-800 dark:text-neutral-200">
                                        {{ $row['pk_total'] > 0 ? number_format($row['pk_total'], 0, ',', '.') : '-' }}
                                    </td>
                                    {{-- Grand Total per row --}}
                                    <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center text-neutral-800 dark:text-neutral-200">
                                        {{ $row['grand_total'] > 0 ? number_format($row['grand_total'], 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                            @endforeach

                            {{-- Baris total per kelompok --}}
                            <tr class="border-b border-neutral-300 dark:border-neutral-700 bg-neutral-100 dark:bg-neutral-800/60 font-bold">
                                <td colspan="3" class="border border-neutral-300 dark:border-neutral-600 px-2 py-2 text-left text-neutral-900 dark:text-neutral-100">
                                    Total {{ $group['label'] }}
                                </td>
                                <td class="border border-neutral-300 dark:border-neutral-600 px-2 py-2 text-center text-neutral-900 dark:text-neutral-100">
                                    {{ $group['kasasiJml'] > 0 ? number_format($group['kasasiJml'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="border border-neutral-300 dark:border-neutral-600 px-2 py-2 text-center text-neutral-400">—</td>
                                <td class="border border-neutral-300 dark:border-neutral-600 px-2 py-2 text-center text-neutral-900 dark:text-neutral-100">
                                    {{ $group['kasasiTotal'] > 0 ? number_format($group['kasasiTotal'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="border border-neutral-300 dark:border-neutral-600 px-2 py-2 text-center text-neutral-900 dark:text-neutral-100">
                                    {{ $group['pkJml'] > 0 ? number_format($group['pkJml'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="border border-neutral-300 dark:border-neutral-600 px-2 py-2 text-center text-neutral-400">—</td>
                                <td class="border border-neutral-300 dark:border-neutral-600 px-2 py-2 text-center text-neutral-900 dark:text-neutral-100">
                                    {{ $group['pkTotal'] > 0 ? number_format($group['pkTotal'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="border border-neutral-300 dark:border-neutral-600 px-2 py-2 text-center text-neutral-900 dark:text-neutral-100">
                                    {{ $group['grand'] > 0 ? number_format($group['grand'], 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                        @endforeach

                        {{-- Baris Grand Total --}}
                        @if($final_total)
                        <tr class="bg-blue-100 dark:bg-blue-900/40 font-bold border-t-2 border-neutral-400 dark:border-neutral-600">
                            <td colspan="3" class="border border-neutral-300 dark:border-neutral-600 px-2 py-2.5 text-left text-neutral-900 dark:text-neutral-100 uppercase tracking-wide">
                                JUMLAH TOTAL KESELURUHAN
                            </td>
                            <td class="border border-neutral-300 dark:border-neutral-600 px-2 py-2.5 text-center text-neutral-400">—</td>
                            <td class="border border-neutral-300 dark:border-neutral-600 px-2 py-2.5 text-center text-neutral-400">—</td>
                            <td class="border border-neutral-300 dark:border-neutral-600 px-2 py-2.5 text-center text-neutral-900 dark:text-neutral-100">
                                {{ $final_total['kasasiTotal'] > 0 ? number_format($final_total['kasasiTotal'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="border border-neutral-300 dark:border-neutral-600 px-2 py-2.5 text-center text-neutral-400">—</td>
                            <td class="border border-neutral-300 dark:border-neutral-600 px-2 py-2.5 text-center text-neutral-400">—</td>
                            <td class="border border-neutral-300 dark:border-neutral-600 px-2 py-2.5 text-center text-neutral-900 dark:text-neutral-100">
                                {{ $final_total['pkTotal'] > 0 ? number_format($final_total['pkTotal'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="border border-neutral-300 dark:border-neutral-600 px-2 py-2.5 text-center text-neutral-900 dark:text-neutral-100">
                                {{ $final_total['grand'] > 0 ? number_format($final_total['grand'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                        <tr class="bg-blue-100 dark:bg-blue-900/40 font-bold">
                            <td colspan="3" class="border border-neutral-300 dark:border-neutral-600 px-2 py-2.5 text-left text-neutral-900 dark:text-neutral-100">
                                Jumlah Total Perkara
                            </td>
                            <td colspan="3" class="border border-neutral-300 dark:border-neutral-600 px-2 py-2.5 text-center text-neutral-900 dark:text-neutral-100">
                                {{ $final_total['kasasiJml'] > 0 ? number_format($final_total['kasasiJml'], 0, ',', '.') : '-' }}
                            </td>
                            <td colspan="3" class="border border-neutral-300 dark:border-neutral-600 px-2 py-2.5 text-center text-neutral-900 dark:text-neutral-100">
                                {{ $final_total['pkJml'] > 0 ? number_format($final_total['pkJml'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="border border-neutral-300 dark:border-neutral-600 px-2 py-2.5 text-center text-neutral-900 dark:text-neutral-100 text-lg font-black">
                                {{ ($final_total['kasasiJml'] + $final_total['pkJml']) > 0 ? number_format($final_total['kasasiJml'] + $final_total['pkJml'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ══ Signature Area ══ --}}
        @php
            $pejabat = config('tarif.pejabat');
            $months = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            $dateNow = \Illuminate\Support\Facades\Session::get('excel_tgl_rekap_keseluruhan')
                ?: ('Jakarta, ' . date('d') . ' ' . $months[(int)date('m')] . ' ' . date('Y'));
        @endphp
        <div class="mt-12 mb-16">
            <div class="flex justify-end mb-8">
                <p class="text-sm font-medium text-neutral-800 dark:text-neutral-200">
                    {{ $dateNow }}
                </p>
            </div>

            <div class="grid grid-cols-3">
                <div class="flex flex-col items-center text-center">
                    <p class="text-xs font-bold uppercase tracking-wide text-neutral-700 dark:text-neutral-300 leading-snug">
                        Kuasa Pengelola Biaya Proses
                    </p>
                    <div class="mt-20"
                         x-data="{ editing: false, val: localStorage.getItem('ttd_kuasa') ?? @js($pejabat['kuasa_pengelola'] ?? 'ASEP NURSOBAH, S.Ag., M.H.') }"
                         x-init="$watch('val', v => localStorage.setItem('ttd_kuasa', v))"
                         @dblclick="orig=val; editing=true" title="Klik 2x untuk edit">
                        <span x-show="!editing" x-text="val !== '' ? val : '— Belum diisi —'" :class="val === '' ? 'text-neutral-400 dark:text-neutral-600 font-normal text-xs tracking-wide' : ''" class="cursor-text block text-sm font-bold text-neutral-900 dark:text-neutral-100 text-center"></span>
                        <div x-show="editing" x-cloak class="flex items-center gap-1 justify-center">
                            <input x-model="val" type="text"
                                   x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                                   @keyup.enter="editing=false" @keyup.escape="val=orig; editing=false"
                                   class="flex-1 border border-blue-400 rounded px-1 py-0.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 min-w-0 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 text-center">
                            <button @click="editing=false" class="text-green-500 hover:text-green-700 font-bold shrink-0">✓</button>
                            <button @click="val=orig; editing=false" class="text-red-400 hover:text-red-600 font-bold shrink-0">✗</button>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col items-center text-center">
                    <p class="text-xs font-bold uppercase tracking-wide text-neutral-700 dark:text-neutral-300 leading-snug">
                        Petugas Pembuat Komitmen<br>Biaya Proses
                    </p>
                    <div class="mt-20"
                         x-data="{ editing: false, val: localStorage.getItem('ttd_petugas') ?? @js($pejabat['ppk'] ?? $pejabat['petugas_pembuat'] ?? 'ST. KRIS NUGROHO, S.H., M.H.') }"
                         x-init="$watch('val', v => localStorage.setItem('ttd_petugas', v))"
                         @dblclick="orig=val; editing=true" title="Klik 2x untuk edit">
                        <span x-show="!editing" x-text="val !== '' ? val : '— Belum diisi —'" :class="val === '' ? 'text-neutral-400 dark:text-neutral-600 font-normal text-xs tracking-wide' : ''" class="cursor-text block text-sm font-bold text-neutral-900 dark:text-neutral-100 text-center"></span>
                        <div x-show="editing" x-cloak class="flex items-center gap-1 justify-center">
                            <input x-model="val" type="text"
                                   x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                                   @keyup.enter="editing=false" @keyup.escape="val=orig; editing=false"
                                   class="flex-1 border border-blue-400 rounded px-1 py-0.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 min-w-0 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 text-center">
                            <button @click="editing=false" class="text-green-500 hover:text-green-700 font-bold shrink-0">✓</button>
                            <button @click="val=orig; editing=false" class="text-red-400 hover:text-red-600 font-bold shrink-0">✗</button>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col items-center text-center">
                    <p class="text-xs font-bold uppercase tracking-wide text-neutral-700 dark:text-neutral-300 leading-snug">
                        Bendahara Biaya Proses
                    </p>
                    <div class="mt-20"
                         x-data="{ editing: false, val: localStorage.getItem('ttd_bendahara') ?? @js($pejabat['bendahara'] ?? 'FARIDA, S.H.') }"
                         x-init="$watch('val', v => localStorage.setItem('ttd_bendahara', v))"
                         @dblclick="orig=val; editing=true" title="Klik 2x untuk edit">
                        <span x-show="!editing" x-text="val !== '' ? val : '— Belum diisi —'" :class="val === '' ? 'text-neutral-400 dark:text-neutral-600 font-normal text-xs tracking-wide' : ''" class="cursor-text block text-sm font-bold text-neutral-900 dark:text-neutral-100 text-center"></span>
                        <div x-show="editing" x-cloak class="flex items-center gap-1 justify-center">
                            <input x-model="val" type="text"
                                   x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                                   @keyup.enter="editing=false" @keyup.escape="val=orig; editing=false"
                                   class="flex-1 border border-blue-400 rounded px-1 py-0.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 min-w-0 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 text-center">
                            <button @click="editing=false" class="text-green-500 hover:text-green-700 font-bold shrink-0">✓</button>
                            <button @click="val=orig; editing=false" class="text-red-400 hover:text-red-600 font-bold shrink-0">✗</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-14 flex flex-col items-center text-center">
                <p class="text-xs font-bold uppercase tracking-wide text-neutral-700 dark:text-neutral-300">Mengetahui,</p>
                <p class="text-xs font-bold uppercase tracking-wide text-neutral-700 dark:text-neutral-300 mt-0.5">Panitera MA-RI</p>
                <div class="mt-20"
                     x-data="{ editing: false, val: localStorage.getItem('ttd_panitera') ?? 'Dr. SUDHARMAWATININGSIH, S.H., M.Hum.' }"
                     x-init="$watch('val', v => localStorage.setItem('ttd_panitera', v))"
                     @dblclick="orig=val; editing=true" title="Klik 2x untuk edit">
                    <span x-show="!editing" x-text="val !== '' ? val : '— Belum diisi —'" :class="val === '' ? 'text-neutral-400 dark:text-neutral-600 font-normal text-xs tracking-wide' : ''" class="cursor-text block text-sm font-bold text-neutral-900 dark:text-neutral-100 text-center"></span>
                    <div x-show="editing" x-cloak class="flex items-center gap-1">
                        <input x-model="val" type="text"
                               x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                               @keyup.enter="editing=false" @keyup.escape="val=orig; editing=false"
                               class="flex-1 border border-blue-400 rounded px-1 py-0.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 min-w-0 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100">
                        <button @click="editing=false" class="text-green-500 hover:text-green-700 font-bold shrink-0">✓</button>
                        <button @click="val=orig; editing=false" class="text-red-400 hover:text-red-600 font-bold shrink-0">✗</button>
                    </div>
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
            <p class="text-neutral-400 dark:text-neutral-500 text-sm mt-2">Harap upload file Excel yang mengandung sheet "Data Print" terlebih dahulu</p>
            <a href="{{ route('dashboard') }}"
               class="inline-block mt-6 px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200">
                Ke Dashboard
            </a>
        </div>
    @endif

</div>
@endsection
