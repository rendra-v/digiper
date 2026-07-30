@extends('layout')

@section('title', 'Rekap Keseluruhan 2')

@section('content')
<div class="min-h-screen">

    {{-- ─── Header ─── --}}
    <div class="mb-6 flex items-start justify-between">
        <div>
            <h2 class="text-3xl font-semibold tracking-tight mb-2">Rekap Keseluruhan 2</h2>
            <p class="text-neutral-500 dark:text-neutral-400 text-sm">
                Distribusi Biaya Penyelesaian Perkara per PERUNTUKAN
                @if($fileName)
                    — <span class="font-medium text-neutral-700 dark:text-neutral-300">{{ $fileName }}</span>
                @endif
            </p>
        </div>
        <div class="flex flex-col items-end gap-2">
            <div class="flex items-end gap-2">
                <div>
                    <label for="nav-rekap2" class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">Halaman lain</label>
                    <div class="relative">
                        <select id="nav-rekap2" onchange="if(this.value) window.location.href = this.value"
                            class="appearance-none rounded-lg border border-emerald-300 dark:border-emerald-700 bg-white dark:bg-neutral-950 px-3 py-2 pr-8 text-sm font-medium text-neutral-900 dark:text-neutral-100 focus:outline-none cursor-pointer">
                            <option value="" disabled selected>Pilih halaman</option>
                            <option value="{{ route('data-print') }}">📄 Data Print</option>
                            <option value="{{ route('sheet-cek') }}">📋 Sheet Cek</option>
                            <option value="{{ route('rekap-keseluruhan') }}">📊 Rekap 1</option>
                            <option value="{{ route('rekap-keseluruhan-3') }}">📋 Rekap 3</option>
                            <option value="{{ route('honorarium') }}">💰 Honorarium</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-2 flex items-center text-emerald-600">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>
                <a href="{{ route('dashboard') }}"
                   class="px-3 py-2 text-sm bg-neutral-200 dark:bg-neutral-800 hover:bg-neutral-300 dark:hover:bg-neutral-700 text-neutral-900 dark:text-neutral-100 rounded-lg transition-colors">
                    Dashboard
                </a>
                @if(!$error && !empty($columns))
                <a href="{{ route('rekap-keseluruhan-2.print') }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 9V2h12v7M6 18H5a2 2 0 01-2-2v-3a2 2 0 012-2h14a2 2 0 012 2v3a2 2 0 01-2 2h-1M8 14h8v8H8z"/></svg>
                    Print PDF
                </a>
                @endif
            </div>
            @if(!$error && !empty($columns))
            <a href="{{ route('rekap-keseluruhan-3') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors shadow-sm">
                Selanjutnya <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @endif
        </div>
    </div>

    @if($error)
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/50 rounded-lg">
            <p class="text-sm text-red-700 dark:text-red-400">{{ $error }}</p>
        </div>
    @endif

    @if(!$error && !empty($columns))

        @if($recapDate)
        <p class="text-xs text-neutral-500 dark:text-neutral-400 text-center mb-3">Periode: {{ strtoupper($recapDate) }}</p>
        @endif

        {{-- ─── Tabel Distribusi Biaya ─── --}}
        <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl shadow-sm overflow-hidden mb-8">
            <div class="overflow-x-auto">
                <table class="w-full text-[10px] border-collapse whitespace-nowrap">
                    {{-- ── Header baris 1 ── --}}
                    <thead>
                        <tr class="bg-blue-100 dark:bg-blue-900/40">
                            <th rowspan="2" class="border border-neutral-300 dark:border-neutral-600 px-2 py-1.5 text-center font-bold text-neutral-800 dark:text-neutral-200 min-w-[28px]">NO</th>
                            <th rowspan="2" class="border border-neutral-300 dark:border-neutral-600 px-2 py-1.5 text-center font-bold text-neutral-800 dark:text-neutral-200 min-w-[200px] max-w-[220px] whitespace-normal">PERUNTUKAN</th>
                            <th rowspan="2" class="border border-neutral-300 dark:border-neutral-600 px-1.5 py-1.5 text-center font-bold text-neutral-800 dark:text-neutral-200 min-w-[32px]">%</th>
                            @foreach($columns as $col)
                            <th colspan="3" class="border border-neutral-300 dark:border-neutral-600 px-1 py-1.5 text-center font-bold text-neutral-800 dark:text-neutral-200 min-w-[160px]">
                                {{ $col['label'] }}<br>
                                <span class="font-normal text-neutral-500 dark:text-neutral-400">({{ $col['rate_label'] }})</span>
                            </th>
                            @endforeach
                            <th rowspan="2" class="border border-neutral-300 dark:border-neutral-600 px-2 py-1.5 text-center font-bold text-neutral-800 dark:text-neutral-200 min-w-[100px]">TOTAL</th>
                        </tr>
                        <tr class="bg-blue-50 dark:bg-blue-900/20">
                            @foreach($columns as $col)
                            <th class="border border-neutral-300 dark:border-neutral-600 px-1.5 py-1 text-center font-bold text-neutral-700 dark:text-neutral-300 min-w-[60px]">BIAYA</th>
                            <th class="border border-neutral-300 dark:border-neutral-600 px-1.5 py-1 text-center font-bold text-neutral-700 dark:text-neutral-300 min-w-[36px]">JML</th>
                            <th class="border border-neutral-300 dark:border-neutral-600 px-1.5 py-1 text-center font-bold text-neutral-700 dark:text-neutral-300 min-w-[70px]">SUB TOTAL</th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                    @foreach($rows as $row)
                        @php
                            $isHeader  = $row['type'] === 'header';
                            $isJmlOnly = $row['type'] === 'jml_only';
                            $isData    = $row['type'] === 'data';
                            $rowTotal  = $row_totals[$row['key']] ?? 0;
                            $colCount  = count($columns);
                        @endphp

                        @if($isHeader)
                        <tr class="bg-gray-100 dark:bg-neutral-800/60">
                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-1 text-center text-neutral-600 dark:text-neutral-400">{{ $row['no'] }}</td>
                            <td colspan="{{ $colCount * 3 + 2 }}" class="border border-neutral-200 dark:border-neutral-700 px-2 py-1 font-bold text-neutral-800 dark:text-neutral-200">
                                {{ $row['label'] }}
                            </td>
                        </tr>

                        @elseif($isJmlOnly)
                        <tr class="border-b border-neutral-100 dark:border-neutral-800 hover:bg-neutral-50/50 dark:hover:bg-neutral-800/20">
                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-1 text-center text-neutral-500 dark:text-neutral-400">{{ $row['no'] }}</td>
                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-1 text-neutral-700 dark:text-neutral-300">{{ $row['label'] }}</td>
                            <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1 text-center text-neutral-400">{{ $row['persen'] }}</td>
                            @foreach($columns as $col)
                                @php $cell = $cells[$row['key']][$col['key']] ?? null @endphp
                                <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1 text-center text-neutral-400">-</td>
                                <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1 text-center text-neutral-700 dark:text-neutral-300">
                                    {{ $cell ? number_format($cell['jml'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1 text-center text-neutral-400">-</td>
                            @endforeach
                            <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1 text-center text-neutral-400">-</td>
                        </tr>

                        @else {{-- data --}}
                        <tr class="border-b border-neutral-100 dark:border-neutral-800 hover:bg-blue-50/20 dark:hover:bg-neutral-800/20">
                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-1 text-center text-neutral-600 dark:text-neutral-400">{{ $row['no'] }}</td>
                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-1 text-neutral-800 dark:text-neutral-200 max-w-[220px] whitespace-normal leading-tight">{{ $row['label'] }}</td>
                            <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1 text-center text-neutral-600 dark:text-neutral-400">{{ $row['persen'] }}</td>
                            @foreach($columns as $col)
                                @php $cell = $cells[$row['key']][$col['key']] ?? null @endphp
                                <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1 text-center text-neutral-700 dark:text-neutral-300">
                                    {{ ($cell && $cell['biaya'] > 0) ? number_format($cell['biaya'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1 text-center text-neutral-700 dark:text-neutral-300">
                                    {{ $cell ? number_format($cell['jml'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1 text-center text-neutral-700 dark:text-neutral-300">
                                    {{ ($cell && $cell['sub_total'] > 0) ? number_format($cell['sub_total'], 0, ',', '.') : '-' }}
                                </td>
                            @endforeach
                            <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1 text-center font-medium text-neutral-800 dark:text-neutral-200">
                                {{ $rowTotal > 0 ? number_format($rowTotal, 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                        @endif
                    @endforeach

                    {{-- ─── TOTAL ROW ─── --}}
                    <tr class="bg-blue-100 dark:bg-blue-900/40 font-bold border-t-2 border-neutral-400 dark:border-neutral-600">
                        <td colspan="2" class="border border-neutral-300 dark:border-neutral-600 px-2 py-2 text-left text-neutral-900 dark:text-neutral-100 uppercase tracking-wide text-[10px]"></td>
                        <td class="border border-neutral-300 dark:border-neutral-600 px-1.5 py-2 text-center text-neutral-900 dark:text-neutral-100">100%</td>
                        @foreach($columns as $col)
                        <td class="border border-neutral-300 dark:border-neutral-600 px-1.5 py-2 text-center text-neutral-900 dark:text-neutral-100">
                            {{ number_format($col['base_rate'], 0, ',', '.') }}
                        </td>
                        <td class="border border-neutral-300 dark:border-neutral-600 px-1.5 py-2 text-center text-neutral-500">-</td>
                        <td class="border border-neutral-300 dark:border-neutral-600 px-1.5 py-2 text-center text-neutral-500">-</td>
                        @endforeach
                        <td class="border border-neutral-300 dark:border-neutral-600 px-1.5 py-2 text-center text-emerald-700 dark:text-emerald-400">
                            {{ $grand_total > 0 ? number_format($grand_total, 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ─── TTD Section ─── --}}
        @php
            $months = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            $dateNow = \Illuminate\Support\Facades\Session::get('excel_tgl_rekap_keseluruhan')
                ?: ('Jakarta, ' . date('d') . ' ' . $months[(int)date('m')] . ' ' . date('Y'));
            $pejabat = config('tarif.pejabat');
        @endphp
        <div class="mt-12 mb-16 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-6 shadow-sm">
            <div class="flex justify-end mb-8">
                <p class="text-sm font-medium text-neutral-800 dark:text-neutral-200">
                    {{ $dateNow }}
                </p>
            </div>

            <div class="grid grid-cols-3 gap-6">
                <div class="flex flex-col items-start">
                    <p class="text-xs font-bold uppercase tracking-wide text-neutral-700 dark:text-neutral-300 leading-snug">
                        Petugas Pembuat Komitmen<br>Biaya Proses
                    </p>
                    <div class="mt-20"
                         x-data="{ editing: false, val: localStorage.getItem('ttd_petugas') ?? @js($pejabat['ppk'] ?? $pejabat['petugas_pembuat'] ?? 'ST. KRIS NUGROHO, S.H., M.H.') }"
                         x-init="$watch('val', v => localStorage.setItem('ttd_petugas', v))"
                         @dblclick="orig=val; editing=true" title="Klik 2x untuk edit">
                        <span x-show="!editing" x-text="val !== '' ? val : '— Belum diisi —'" :class="val === '' ? 'text-neutral-400 dark:text-neutral-600 font-normal text-xs tracking-wide' : ''" class="cursor-text block text-sm font-bold text-neutral-900 dark:text-neutral-100"></span>
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

                <div class="flex flex-col items-center text-center">
                    <p class="text-xs font-bold uppercase tracking-wide text-neutral-700 dark:text-neutral-300 leading-snug">
                        Mengetahui,<br>Kuasa Pengelola Biaya Proses
                    </p>
                    <div class="mt-20"
                         x-data="{ editing: false, val: localStorage.getItem('ttd_kuasa') ?? @js($pejabat['kuasa_pengelola'] ?? 'ASEP NURSOBAH, S.Ag., M.H.') }"
                         x-init="$watch('val', v => localStorage.setItem('ttd_kuasa', v))"
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

                <div class="flex flex-col items-end text-right">
                    <p class="text-xs font-bold uppercase tracking-wide text-neutral-700 dark:text-neutral-300 leading-snug">
                        Bendahara Biaya Proses
                    </p>
                    <div class="mt-20"
                         x-data="{ editing: false, val: localStorage.getItem('ttd_bendahara') ?? @js($pejabat['bendahara'] ?? 'FARIDA, S.H.') }"
                         x-init="$watch('val', v => localStorage.setItem('ttd_bendahara', v))"
                         @dblclick="orig=val; editing=true" title="Klik 2x untuk edit">
                        <span x-show="!editing" x-text="val !== '' ? val : '— Belum diisi —'" :class="val === '' ? 'text-neutral-400 dark:text-neutral-600 font-normal text-xs tracking-wide' : ''" class="cursor-text block text-sm font-bold text-neutral-900 dark:text-neutral-100 text-right"></span>
                        <div x-show="editing" x-cloak class="flex items-center gap-1 justify-end">
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
        </div>

    @else
        <div class="text-center py-24">
            <svg class="w-16 h-16 text-neutral-300 dark:text-neutral-700 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-neutral-500 dark:text-neutral-400 text-lg font-medium">Belum ada data</p>
            <p class="text-neutral-400 dark:text-neutral-500 text-sm mt-2">Harap upload file Excel yang mengandung sheet "Data Print" terlebih dahulu</p>
            <a href="{{ route('dashboard') }}"
               class="inline-block mt-6 px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors text-sm">
                Ke Dashboard
            </a>
        </div>
    @endif

</div>
@endsection
