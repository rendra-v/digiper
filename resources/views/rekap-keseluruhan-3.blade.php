@extends('layout')

@section('title', 'Rekap Keseluruhan 3')

@section('content')
<div class="min-h-screen">

    {{-- ─── Header ─── --}}
    <div class="mb-6 flex items-start justify-between">
        <div>
            <h2 class="text-3xl font-semibold tracking-tight mb-2">Rekap Keseluruhan 3</h2>
            <p class="text-neutral-500 dark:text-neutral-400 text-sm">
                Distribusi Honor / Insentif Personil per Jabatan × Jenis Perkara
                @if($fileName)
                    — <span class="font-medium text-neutral-700 dark:text-neutral-300">{{ $fileName }}</span>
                @endif
            </p>
        </div>
        <div class="flex flex-col items-end gap-2">
            <div class="flex items-end gap-2">
                <div>
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">Halaman lain</label>
                    <div class="relative">
                        <select onchange="if(this.value) window.location.href = this.value"
                            class="appearance-none rounded-lg border border-emerald-300 dark:border-emerald-700 bg-white dark:bg-neutral-950 px-3 py-2 pr-8 text-sm font-medium text-neutral-900 dark:text-neutral-100 focus:outline-none cursor-pointer">
                            <option value="" disabled selected>— Pilih —</option>
                            <option value="{{ route('data-print') }}">📄 Data Print</option>
                            <option value="{{ route('sheet-cek') }}">📋 Sheet Cek</option>
                            <option value="{{ route('rekap-keseluruhan') }}">📊 Rekap 1</option>
                            <option value="{{ route('rekap-keseluruhan-2') }}">📊 Rekap 2</option>
                            <option value="{{ route('honorarium') }}">💰 Honorarium</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-2 flex items-center text-emerald-600">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>
                <a href="{{ route('dashboard') }}"
                   class="px-3 py-2 text-sm bg-neutral-200 dark:bg-neutral-800 hover:bg-neutral-300 dark:hover:bg-neutral-700 rounded-lg transition-colors">
                    Dashboard
                </a>
                @if(!$error && !empty($columns))
                <a href="{{ route('rekap-keseluruhan-3.print') }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 9V2h12v7M6 18H5a2 2 0 01-2-2v-3a2 2 0 012-2h14a2 2 0 012 2v3a2 2 0 01-2 2h-1M8 14h8v8H8z"/></svg>
                    Print PDF
                </a>
                @endif
            </div>
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

        {{-- ─── Legend Pajak ─── --}}
        <div class="mb-4 flex flex-wrap gap-3 text-[10px]">
            <span class="px-2.5 py-1 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 font-medium">PPh 15% — Jabatan Umum</span>
            <span class="px-2.5 py-1 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 font-medium">PPh 5% — Operator/Pengetik</span>
            <span class="px-2.5 py-1 rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300 font-medium">Mixed — Rumpun A (5%) + Rumpun B (15%)</span>
        </div>

        {{-- ─── Tabel ─── --}}
        <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl shadow-sm overflow-hidden mb-8">
            <div class="overflow-x-auto">
                <table class="w-full text-[9px] border-collapse whitespace-nowrap">
                    {{-- header row 1 --}}
                    <thead>
                        <tr class="bg-indigo-100 dark:bg-indigo-900/40">
                            <th rowspan="2" class="border border-neutral-300 dark:border-neutral-600 px-2 py-1.5 text-center font-bold min-w-[28px]">NO</th>
                            <th rowspan="2" class="border border-neutral-300 dark:border-neutral-600 px-2 py-1.5 text-left font-bold min-w-[180px] max-w-[200px] whitespace-normal">JABATAN</th>
                            <th rowspan="2" class="border border-neutral-300 dark:border-neutral-600 px-1.5 py-1.5 text-center font-bold min-w-[36px]">%</th>
                            @foreach($columns as $col)
                            <th colspan="3" class="border border-neutral-300 dark:border-neutral-600 px-1 py-1.5 text-center font-bold min-w-[150px]">
                                {{ $col['label'] }}
                            </th>
                            @endforeach
                            <th rowspan="2" class="border border-neutral-300 dark:border-neutral-600 px-2 py-1.5 text-center font-bold min-w-[90px] bg-green-50 dark:bg-green-900/20">BRUTO</th>
                            <th rowspan="2" class="border border-neutral-300 dark:border-neutral-600 px-2 py-1.5 text-center font-bold min-w-[80px] bg-red-50 dark:bg-red-900/20">PPh 15%</th>
                            <th rowspan="2" class="border border-neutral-300 dark:border-neutral-600 px-2 py-1.5 text-center font-bold min-w-[80px] bg-orange-50 dark:bg-orange-900/20">PPh 5%</th>
                            <th rowspan="2" class="border border-neutral-300 dark:border-neutral-600 px-2 py-1.5 text-center font-bold min-w-[90px] bg-emerald-100 dark:bg-emerald-900/30">NETTO</th>
                        </tr>
                        <tr class="bg-indigo-50 dark:bg-indigo-900/20">
                            @foreach($columns as $col)
                            <th class="border border-neutral-300 dark:border-neutral-600 px-1.5 py-1 text-center font-bold min-w-[55px]">BIAYA</th>
                            <th class="border border-neutral-300 dark:border-neutral-600 px-1.5 py-1 text-center font-bold min-w-[32px]">JML</th>
                            <th class="border border-neutral-300 dark:border-neutral-600 px-1.5 py-1 text-center font-bold min-w-[65px]">SUB TOTAL</th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                    @foreach($rows as $i => $row)
                        @php
                            $taxBadge = match($row['pajak']) {
                                'pph5'  => 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300',
                                'mixed' => 'bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300',
                                default => '',
                            };
                        @endphp
                        <tr class="border-b border-neutral-100 dark:border-neutral-800 hover:bg-indigo-50/20 dark:hover:bg-neutral-800/20">
                            <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-center text-neutral-500">{{ $i + 1 }}</td>
                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-1.5 text-neutral-800 dark:text-neutral-200 max-w-[200px] whitespace-normal leading-tight">
                                {{ $row['label'] }}
                                @if($row['pajak'] !== 'pph15')
                                <span class="ml-1 px-1 py-0.5 rounded text-[8px] font-medium {{ $taxBadge }}">
                                    {{ $row['pajak'] === 'pph5' ? 'PPh 5%' : 'Mixed' }}
                                </span>
                                @endif
                            </td>
                            <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-center text-neutral-600 dark:text-neutral-400">
                                {{ number_format($row['persen'] * 100, 1, ',', '.') }}%
                            </td>
                            @foreach($columns as $col)
                                @php $cell = $row['cells'][$col['key']] ?? ['biaya'=>0,'jml'=>0,'sub_total'=>0] @endphp
                                <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-center text-neutral-700 dark:text-neutral-300">
                                    {{ $cell['biaya'] > 0 ? number_format($cell['biaya'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-center text-neutral-700 dark:text-neutral-300">
                                    {{ $cell['jml'] > 0 ? number_format($cell['jml'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-center text-neutral-700 dark:text-neutral-300">
                                    {{ $cell['sub_total'] > 0 ? number_format($cell['sub_total'], 0, ',', '.') : '-' }}
                                </td>
                            @endforeach
                            <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-center font-medium text-green-800 dark:text-green-300 bg-green-50/40 dark:bg-green-900/10">
                                {{ $row['bruto'] > 0 ? number_format($row['bruto'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-center text-red-700 dark:text-red-400 bg-red-50/30 dark:bg-red-900/10">
                                {{ $row['pph15'] > 0 ? number_format($row['pph15'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-center text-orange-700 dark:text-orange-400 bg-orange-50/30 dark:bg-orange-900/10">
                                {{ $row['pph5'] > 0 ? number_format($row['pph5'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="border border-neutral-200 dark:border-neutral-700 px-1.5 py-1.5 text-center font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50/40 dark:bg-emerald-900/10">
                                {{ $row['netto'] > 0 ? number_format($row['netto'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                    @endforeach

                    {{-- ─── TOTAL ROW ─── --}}
                    <tr class="bg-indigo-100 dark:bg-indigo-900/40 font-bold border-t-2 border-neutral-400 dark:border-neutral-600">
                        <td colspan="3" class="border border-neutral-300 dark:border-neutral-600 px-2 py-2 text-center uppercase tracking-wide text-[9px]">TOTAL</td>
                        @foreach($columns as $col)
                        <td class="border border-neutral-300 dark:border-neutral-600 px-1.5 py-2 text-center text-neutral-500">-</td>
                        <td class="border border-neutral-300 dark:border-neutral-600 px-1.5 py-2 text-center text-neutral-500">-</td>
                        <td class="border border-neutral-300 dark:border-neutral-600 px-1.5 py-2 text-center">
                            {{ isset($col_grand_total[$col['key']]) && $col_grand_total[$col['key']] > 0
                               ? number_format($col_grand_total[$col['key']], 0, ',', '.') : '-' }}
                        </td>
                        @endforeach
                        <td class="border border-neutral-300 dark:border-neutral-600 px-1.5 py-2 text-center text-green-800 dark:text-green-300">
                            {{ $grand_bruto > 0 ? number_format($grand_bruto, 0, ',', '.') : '-' }}
                        </td>
                        <td class="border border-neutral-300 dark:border-neutral-600 px-1.5 py-2 text-center text-red-700 dark:text-red-400">
                            {{ $grand_pph15 > 0 ? number_format($grand_pph15, 0, ',', '.') : '-' }}
                        </td>
                        <td class="border border-neutral-300 dark:border-neutral-600 px-1.5 py-2 text-center text-orange-700 dark:text-orange-400">
                            {{ $grand_pph5 > 0 ? number_format($grand_pph5, 0, ',', '.') : '-' }}
                        </td>
                        <td class="border border-neutral-300 dark:border-neutral-600 px-1.5 py-2 text-center text-emerald-700 dark:text-emerald-400">
                            {{ $grand_netto > 0 ? number_format($grand_netto, 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ─── Ringkasan Keuangan ─── --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            @foreach([
                ['label' => 'Total Bruto',  'value' => $grand_bruto,  'color' => 'green'],
                ['label' => 'PPh 15%',      'value' => $grand_pph15,  'color' => 'red'],
                ['label' => 'PPh 5%',       'value' => $grand_pph5,   'color' => 'orange'],
                ['label' => 'Total Netto',  'value' => $grand_netto,  'color' => 'emerald'],
            ] as $card)
            <div class="bg-{{ $card['color'] }}-50 dark:bg-{{ $card['color'] }}-900/20 border border-{{ $card['color'] }}-200 dark:border-{{ $card['color'] }}-800 rounded-xl p-4">
                <p class="text-xs text-{{ $card['color'] }}-600 dark:text-{{ $card['color'] }}-400 font-medium mb-1">{{ $card['label'] }}</p>
                <p class="text-lg font-bold text-{{ $card['color'] }}-800 dark:text-{{ $card['color'] }}-300">
                    Rp {{ number_format($card['value'], 0, ',', '.') }}
                </p>
            </div>
            @endforeach
        </div>

    @else
        <div class="text-center py-24">
            <svg class="w-16 h-16 text-neutral-300 dark:text-neutral-700 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-neutral-500 dark:text-neutral-400 text-lg font-medium">Belum ada data</p>
            <p class="text-neutral-400 dark:text-neutral-500 text-sm mt-2">Harap upload file Excel yang mengandung sheet "Data Print"</p>
            <a href="{{ route('dashboard') }}"
               class="inline-block mt-6 px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors text-sm">
                Ke Dashboard
            </a>
        </div>
    @endif

</div>
@endsection
