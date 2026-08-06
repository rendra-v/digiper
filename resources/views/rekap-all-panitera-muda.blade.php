@extends('layout')

@section('title', 'Rekap All Panitera Muda')

@section('content')
@php
    function rp_all(int $v): string {
        return 'Rp&nbsp;' . number_format($v, 0, ',', '.');
    }
@endphp

<div class="min-h-screen">

    {{-- ─── Header ─── --}}
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="text-4xl font-semibold tracking-tight mb-3">Rekap All Panitera Muda</h2>
            <p class="text-neutral-500 dark:text-neutral-400">
                Ringkasan rekapitulasi honorarium biaya penyelesaian perkara seluruh Panitera Muda
                @if($fileName)
                    — <span class="font-medium text-neutral-700 dark:text-neutral-300">{{ $fileName }}</span>
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="relative">
                <select onchange="if(this.value) window.location.href = this.value"
                    class="appearance-none rounded-lg border border-emerald-300 dark:border-emerald-700 bg-white dark:bg-neutral-950 px-4 py-2.5 pr-10 text-sm font-medium text-neutral-900 dark:text-neutral-100 shadow-sm focus:outline-none cursor-pointer">
                    <option value="" disabled selected>Lihat halaman lain</option>
                    <option value="{{ route('dashboard') }}">🏠&nbsp; Dashboard</option>
                    <option value="{{ route('data-print') }}">🖨️&nbsp; Data Print</option>
                    <option value="{{ route('honorarium') }}">💰&nbsp; Honorarium</option>
                    <option value="{{ route('rekap-kepaniteraan-tim') }}">📋&nbsp; Rekap Kepaniteraan &amp; Tim</option>
                    <option value="{{ route('rekap-panitera-muda') }}">📋&nbsp; Rekap Panitera Muda</option>
                    <option value="{{ route('rekap-keseluruhan') }}">📊&nbsp; Rekap Keseluruhan</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-emerald-600 dark:text-emerald-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </div>
            </div>

            <a href="{{ route('dashboard') }}"
               class="px-4 py-2.5 text-sm bg-neutral-200 dark:bg-neutral-800 hover:bg-neutral-300 dark:hover:bg-neutral-700 text-neutral-900 dark:text-neutral-100 rounded-lg transition-colors duration-200">
                Dashboard
            </a>

            @if(!$error && !empty($rows))
            <a href="{{ route('rekap-all-panitera-muda.print') }}" target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 9V2h12v7"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18H5a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 14h8v8H8z"/>
                </svg>
                Cetak Laporan
            </a>
            @endif
        </div>
    </div>

    @if($error)
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/50 rounded-lg">
            <p class="text-sm font-medium text-red-700 dark:text-red-400">⚠ {{ $error }}</p>
        </div>
    @elseif(empty($rows))
        <div class="p-8 text-center text-neutral-400 dark:text-neutral-500 text-sm">
            Tidak ada data. Silakan upload file Excel terlebih dahulu.
        </div>
    @else

        <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl overflow-hidden shadow-sm">

            {{-- Table Title --}}
            <div class="px-6 py-5 border-b border-neutral-100 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-800/40">
                <h3 class="text-base font-bold text-neutral-800 dark:text-neutral-100 uppercase tracking-wide">
                    REKAPITULASI HONORARIUM BIAYA PENYELESAIAN PERKARA PADA MASING - MASING PANITERA MUDA
                </h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                    Periode Laporan: {{ $period ?: '-' }}
                </p>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-neutral-100 dark:bg-neutral-800 text-xs uppercase tracking-wide text-neutral-600 dark:text-neutral-300">
                            <th class="px-6 py-4 text-center w-12">No</th>
                            <th class="px-6 py-4 text-left">Panitera Muda</th>
                            <th class="px-6 py-4 text-center">Periode</th>
                            <th class="px-6 py-4 text-right">Jumlah Biaya</th>
                            <th class="px-6 py-4 text-right">PPH 15%</th>
                            <th class="px-6 py-4 text-right">PPH 5%</th>
                            <th class="px-6 py-4 text-right">Netto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                        @foreach($rows as $row)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                            <td class="px-6 py-4 text-center text-neutral-500 dark:text-neutral-400">{{ $row['no'] }}</td>
                            <td class="px-6 py-4 font-semibold text-neutral-800 dark:text-neutral-100">{{ $row['nama'] }}</td>
                            <td class="px-6 py-4 text-center text-neutral-600 dark:text-neutral-300">{{ $period ?: '-' }}</td>
                            <td class="px-6 py-4 text-right font-mono text-neutral-700 dark:text-neutral-300">
                                {!! $row['jumlah_biaya'] > 0 ? rp_all($row['jumlah_biaya']) : '<span class="text-neutral-400">Rp&nbsp;-</span>' !!}
                            </td>
                            <td class="px-6 py-4 text-right font-mono text-neutral-700 dark:text-neutral-300">
                                {!! $row['pph15'] > 0 ? rp_all($row['pph15']) : '<span class="text-neutral-400">Rp&nbsp;-</span>' !!}
                            </td>
                            <td class="px-6 py-4 text-right font-mono text-neutral-700 dark:text-neutral-300">
                                {!! $row['pph5'] > 0 ? rp_all($row['pph5']) : '<span class="text-neutral-400">Rp&nbsp;-</span>' !!}
                            </td>
                            <td class="px-6 py-4 text-right font-mono text-neutral-700 dark:text-neutral-300">
                                {!! $row['netto'] > 0 ? rp_all($row['netto']) : '<span class="text-neutral-400">Rp&nbsp;-</span>' !!}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-neutral-100 dark:bg-neutral-800 font-bold text-sm">
                            <td class="px-6 py-4 text-center" colspan="3">TOTAL</td>
                            <td class="px-6 py-4 text-right font-mono">
                                {!! $total['jumlah_biaya'] > 0 ? rp_all($total['jumlah_biaya']) : 'Rp&nbsp;-' !!}
                            </td>
                            <td class="px-6 py-4 text-right font-mono">
                                {!! $total['pph15'] > 0 ? rp_all($total['pph15']) : 'Rp&nbsp;-' !!}
                            </td>
                            <td class="px-6 py-4 text-right font-mono">
                                {!! $total['pph5'] > 0 ? rp_all($total['pph5']) : 'Rp&nbsp;-' !!}
                            </td>
                            <td class="px-6 py-4 text-right font-mono">
                                {!! $total['netto'] > 0 ? rp_all($total['netto']) : 'Rp&nbsp;-' !!}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    @endif
</div>
@endsection
