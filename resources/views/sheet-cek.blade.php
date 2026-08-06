@extends('layout')

@section('title', 'Sheet Cek')

@section('content')
<div class="min-h-screen">
    <!-- Header Section -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="text-4xl font-semibold tracking-tight mb-3">Sheet Cek</h2>
            <p class="text-neutral-500 dark:text-neutral-400">Verifikasi dan pengecekan data perkara</p>
        </div>
        <div class="flex items-end gap-3">

            {{-- Dropdown: Lihat Halaman Lain --}}
            <div>
                <label for="nav-page-select-cek" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    Lihat halaman lain
                </label>
                <div class="relative flex items-center gap-2">
                    <div class="relative">
                        <select id="nav-page-select-cek"
                            onchange="if(this.value) window.location.href = this.value"
                            class="appearance-none rounded-lg border border-emerald-300 dark:border-emerald-700 bg-white dark:bg-neutral-950 px-4 py-2.5 pr-10 text-sm font-medium text-neutral-900 dark:text-neutral-100 shadow-sm transition-colors duration-200 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 cursor-pointer">
                            <option value="" disabled selected>Pilih halaman</option>
                            <option value="{{ route('data-print') }}">📄&nbsp; Data Print Perkara</option>
                            <option value="{{ route('rekap-keseluruhan') }}">📊&nbsp; Rekap Keseluruhan</option>
                            <option value="{{ route('rekap-kepaniteraan-tim') }}">📋&nbsp; Rekap Kepaniteraan &amp; Tim</option>
                            <option value="{{ route('rekap-panitera-muda') }}">📋&nbsp; Rekap Panitera Muda</option>
                            <option value="{{ route('rekap-all-panitera-muda') }}">📊&nbsp; Rekap All Panitera Muda</option>
                            <option value="{{ route('rekap-keseluruhan-2') }}">📈&nbsp; Rekap Keseluruhan 2</option>
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
            </div>

            @if(isset($groups) && count($groups) > 0)
            <a href="{{ route('sheet-cek.print') }}" target="_blank" class="px-4 py-2.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 font-medium shadow-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <polyline points="6 9 6 2 18 2 18 9"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
                    <rect x="6" y="14" width="12" height="8"/>
                </svg>
                Cetak PDF
            </a>
            @endif
            <a href="{{ route('dashboard') }}" class="px-4 py-2.5 text-sm bg-neutral-200 dark:bg-neutral-800 hover:bg-neutral-300 dark:hover:bg-neutral-700 text-neutral-900 dark:text-neutral-100 rounded-lg transition-colors duration-200">
                Dashboard
            </a>
        </div>
    </div>

    @if($error)
        <!-- Error Message -->
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/50 rounded-lg">
            <p class="text-sm text-red-700 dark:text-red-400">{{ $error }}</p>
        </div>
    @endif

    @if(count($groups) > 0)
        <!-- Table Section -->
        <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-lg overflow-hidden">
            <!-- Title Header from Excel -->
            <div class="px-8 py-6 bg-neutral-50 dark:bg-neutral-800/30 border-b border-neutral-200 dark:border-neutral-800">
                <div class="space-y-2">
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-neutral-100">REKAP TOTAL PENYERAHAN KE MASING - MASING PANMUD</h3>
                    <p class="text-base font-semibold text-neutral-700 dark:text-neutral-300">HONORARIUM BIAYA PENYELESAIAN PERKARA</p>
                    @if(Session::has('excel_period') && Session::get('excel_period') !== '')
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">PERIODE: {{ strtoupper(Session::get('excel_period')) }}</p>
                    @elseif(Session::has('excel_file_name'))
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">File: {{ Session::get('excel_file_name') }}</p>
                    @endif
                </div>
            </div>

                                                <!-- Table Header Info -->
            <div class="px-8 py-4 border-b border-neutral-200 dark:border-neutral-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Jika data kosong, mohon kembali ke Dashboard dan upload ulang file Excel (sesi mungkin telah berakhir).</p>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-[11px] border-collapse">
                    <thead class="bg-neutral-300 dark:bg-neutral-700 sticky top-0">
                        <tr class="border-b border-neutral-400 dark:border-neutral-600">
                            <th class="px-1.5 py-3 text-center font-bold text-neutral-800 dark:text-neutral-200 border-r border-neutral-400 dark:border-neutral-600">NO</th>
                            <th class="px-1.5 py-3 text-center font-bold text-neutral-800 dark:text-neutral-200 border-r border-neutral-400 dark:border-neutral-600">PERKARA</th>
                            <th class="px-1.5 py-3 text-center font-bold text-neutral-800 dark:text-neutral-200 border-r border-neutral-400 dark:border-neutral-600">JENIS PERKARA</th>
                            <th class="px-1.5 py-3 text-center font-bold text-neutral-800 dark:text-neutral-200 border-r border-neutral-400 dark:border-neutral-600">JUMLAH</th>
                            <th class="px-1.5 py-3 text-center font-bold text-neutral-800 dark:text-neutral-200 border-r border-neutral-400 dark:border-neutral-600">BIAYA PERKARA</th>
                            <th class="px-1.5 py-3 text-center font-bold text-neutral-800 dark:text-neutral-200 border-r border-neutral-400 dark:border-neutral-600">JUMLAH BIAYA</th>
                            <th class="px-1.5 py-3 text-center font-bold text-neutral-800 dark:text-neutral-200 border-r border-neutral-400 dark:border-neutral-600">KETERANGAN</th>
                            <th class="px-1.5 py-3 text-center font-bold text-neutral-800 dark:text-neutral-200 border-r border-neutral-400 dark:border-neutral-600">TIM</th>
                            <th class="px-1.5 py-3 text-center font-bold text-neutral-800 dark:text-neutral-200 border-r border-neutral-400 dark:border-neutral-600">5 MAJELIS</th>
                            <th class="px-1.5 py-3 text-center font-bold text-neutral-800 dark:text-neutral-200 border-r border-neutral-400 dark:border-neutral-600">KEPANITERAAN</th>
                            <th class="px-1.5 py-3 text-center font-bold text-neutral-800 dark:text-neutral-200 border-r border-neutral-400 dark:border-neutral-600">PEMILAH</th>
                            <th class="px-1.5 py-3 text-center font-bold text-neutral-800 dark:text-neutral-200 border-r border-neutral-400 dark:border-neutral-600">TOTAL BRUTO</th>
                            <th class="px-1.5 py-3 text-center font-bold text-neutral-800 dark:text-neutral-200 border-r border-neutral-400 dark:border-neutral-600">POT. PAJAK</th>
                            <th class="px-1.5 py-3 text-center font-bold text-neutral-800 dark:text-neutral-200 border-r border-neutral-400 dark:border-neutral-600">TOTAL NETTO</th>
                            <th class="px-1.5 py-3 text-center font-bold text-neutral-800 dark:text-neutral-200 border-r border-neutral-400 dark:border-neutral-600">SUBTOTAL PERKARA</th>
                            <th class="px-1.5 py-3 text-center font-bold text-neutral-800 dark:text-neutral-200 border-r border-neutral-400 dark:border-neutral-600">TOTAL KELOMPOK</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($groups) && count($groups) > 0)
                            @foreach($groups as $group)
                                @php
                                    $groupTotal = 0;
                                    $totalGroupRows = 0;
                                    foreach($group['sub_groups'] as $sg) {
                                        $p15 = round($sg['total_m_15'] * 0.15);
                                        $b15 = $sg['total_m_15'] - $p15;
                                        $p5 = round($sg['total_m_5'] * 0.05);
                                        $b5 = $sg['total_m_5'] - $p5;
                                        $groupTotal += ($b15 + $b5);
                                        $totalGroupRows += ($sg['label'] ? 4 : 3);
                                    }
                                @endphp
                                @foreach($group['sub_groups'] as $index => $sg)
                                    @php
                                        $pajak15 = round($sg['total_m_15'] * 0.15);
                                        $bersih15 = $sg['total_m_15'] - $pajak15;
                                        
                                        $pajak5 = round($sg['total_m_5'] * 0.05);
                                        $bersih5 = $sg['total_m_5'] - $pajak5;
                                        
                                        $subGroupTotal = $bersih15 + $bersih5;

                                        $totBiaya   = $sg['total_15'] + $sg['total_5'];
                                        $totTim     = $sg['tim_15'] + $sg['tim_5'];
                                        $totMajelis = $sg['majelis5_15'] + $sg['majelis5_5'];
                                        $totKepan   = $sg['kepaniteraan_15'] + $sg['kepaniteraan_5'];
                                        $totPemilah = $sg['pemilah_15'] + $sg['pemilah_5'];
                                        $totBruto   = $sg['total_m_15'] + $sg['total_m_5'];
                                        $totPajak   = $pajak15 + $pajak5;
                                    @endphp
                                    
                                    @if($sg['label'])
                                    <tr class="bg-neutral-200 dark:bg-neutral-700 font-bold border-b border-neutral-400 dark:border-neutral-600">
                                        @if($index === 0)
                                        <td class="px-1.5 py-2 border-r border-neutral-400 dark:border-neutral-700 text-center align-middle" rowspan="{{ $totalGroupRows }}">{{ $group['no'] }}</td>
                                        <td class="px-1.5 py-2 border-r border-neutral-400 dark:border-neutral-700 align-middle" rowspan="{{ $totalGroupRows }}">{{ $group['perkara'] }}</td>
                                        @endif
                                        <td colspan="13" class="px-1.5 py-2 border-r border-neutral-400 dark:border-neutral-700 font-bold bg-neutral-300 dark:bg-neutral-600">{{ $sg['label'] }} — {{ $sg['jenis'] }}</td>
                                        @if($index === 0)
                                        <td class="px-1.5 py-2 text-right border-r border-neutral-400 dark:border-neutral-700 align-middle bg-white dark:bg-neutral-900 font-bold" rowspan="{{ $totalGroupRows }}">{{ $groupTotal > 0 ? number_format($groupTotal, 0, ',', '.') : '-' }}</td>
                                        @endif
                                    </tr>
                                    @endif
                                
                                    <tr class="border-b border-neutral-400 dark:border-neutral-800 bg-neutral-100 dark:bg-neutral-800/60 font-bold">
                                        @if($index === 0 && !$sg['label'])
                                        <td class="px-1.5 py-2 text-center border-r border-neutral-400 dark:border-neutral-700 align-middle" rowspan="{{ $totalGroupRows }}">
                                            {{ $group['no'] }}
                                        </td>
                                        <td class="px-1.5 py-2 border-r border-neutral-400 dark:border-neutral-700 text-neutral-800 dark:text-neutral-200 align-middle" rowspan="{{ $totalGroupRows }}">
                                            {{ $group['perkara'] }}
                                        </td>
                                        @endif
                                        <td class="px-1.5 py-2 border-r border-neutral-400 dark:border-neutral-700 text-neutral-800 dark:text-neutral-200 align-middle" rowspan="3">{{ $sg['jenis'] }}</td>
                                        <td class="px-1.5 py-2 text-right border-r border-neutral-400 dark:border-neutral-700 align-middle" rowspan="3">{{ $sg['jumlah'] > 0 ? number_format($sg['jumlah'], 0, ',', '.') : '-' }}</td>
                                        <td class="px-1.5 py-2 text-right border-r border-neutral-400 dark:border-neutral-700">{{ $sg['biaya_total'] > 0 ? number_format($sg['biaya_total'], 0, ',', '.') : '-' }}</td>
                                        <td class="px-1.5 py-2 text-right border-r border-neutral-400 dark:border-neutral-700">{{ $totBiaya > 0 ? number_format($totBiaya, 0, ',', '.') : '-' }}</td>
                                        <td class="px-1.5 py-2 text-center border-r border-neutral-400 dark:border-neutral-700 bg-neutral-200 dark:bg-neutral-700">TOTAL</td>
                                        <td class="px-1.5 py-2 text-right border-r border-neutral-400 dark:border-neutral-700">{{ $totTim > 0 ? number_format($totTim, 0, ',', '.') : '-' }}</td>
                                        <td class="px-1.5 py-2 text-right border-r border-neutral-400 dark:border-neutral-700">{{ $totMajelis > 0 ? number_format($totMajelis, 0, ',', '.') : '-' }}</td>
                                        <td class="px-1.5 py-2 text-right border-r border-neutral-400 dark:border-neutral-700">{{ $totKepan > 0 ? number_format($totKepan, 0, ',', '.') : '-' }}</td>
                                        <td class="px-1.5 py-2 text-right border-r border-neutral-400 dark:border-neutral-700">{{ $totPemilah > 0 ? number_format($totPemilah, 0, ',', '.') : '-' }}</td>
                                        <td class="px-1.5 py-2 text-right border-r border-neutral-400 dark:border-neutral-700">{{ $totBruto > 0 ? number_format($totBruto, 0, ',', '.') : '-' }}</td>
                                        <td class="px-1.5 py-2 text-right border-r border-neutral-400 dark:border-neutral-700">{{ $totPajak > 0 ? number_format($totPajak, 0, ',', '.') : '-' }}</td>
                                        <td class="px-1.5 py-2 text-right border-r border-neutral-400 dark:border-neutral-700">{{ $subGroupTotal > 0 ? number_format($subGroupTotal, 0, ',', '.') : '-' }}</td>
                                        <td class="px-1.5 py-2 text-right border-r border-neutral-400 dark:border-neutral-700 align-middle" rowspan="3">{{ $subGroupTotal > 0 ? number_format($subGroupTotal, 0, ',', '.') : '-' }}</td>
                                        @if($index === 0 && !$sg['label'])
                                        <td class="px-1.5 py-2 text-right border-r border-neutral-400 dark:border-neutral-700 align-middle bg-white dark:bg-neutral-900" rowspan="{{ $totalGroupRows }}">{{ $groupTotal > 0 ? number_format($groupTotal, 0, ',', '.') : '-' }}</td>
                                        @endif
                                    </tr>
                                    
                                    {{-- Baris PPH 15% --}}
                                    <tr class="border-b border-neutral-400 dark:border-neutral-800 bg-white dark:bg-neutral-900 font-medium">
                                        <td class="px-1.5 py-2 text-right border-r border-neutral-400 dark:border-neutral-700 font-bold">{{ $sg['biaya_15'] > 0 ? number_format($sg['biaya_15'], 0, ',', '.') : '-' }}</td>
                                        <td class="px-1.5 py-2 text-right border-r border-neutral-400 dark:border-neutral-700 font-bold">{{ $sg['total_15'] > 0 ? number_format($sg['total_15'], 0, ',', '.') : '-' }}</td>
                                        <td class="px-1.5 py-2 text-center border-r border-neutral-400 dark:border-neutral-700 bg-neutral-300 dark:bg-neutral-700 text-black dark:text-white font-bold">PAJAK 15 %</td>
                                        <td class="px-1.5 py-2 text-right border-r border-neutral-400 dark:border-neutral-700">{{ $sg['tim_15'] > 0 ? number_format($sg['tim_15'], 0, ',', '.') : '-' }}</td>
                                        <td class="px-1.5 py-2 text-right border-r border-neutral-400 dark:border-neutral-700">{{ $sg['majelis5_15'] > 0 ? number_format($sg['majelis5_15'], 0, ',', '.') : '-' }}</td>
                                        <td class="px-1.5 py-2 text-right border-r border-neutral-400 dark:border-neutral-700">{{ $sg['kepaniteraan_15'] > 0 ? number_format($sg['kepaniteraan_15'], 0, ',', '.') : '-' }}</td>
                                        <td class="px-1.5 py-2 text-right border-r border-neutral-400 dark:border-neutral-700">{{ $sg['pemilah_15'] > 0 ? number_format($sg['pemilah_15'], 0, ',', '.') : '-' }}</td>
                                        <td class="px-1.5 py-2 text-right border-r border-neutral-400 dark:border-neutral-700">{{ $sg['total_m_15'] > 0 ? number_format($sg['total_m_15'], 0, ',', '.') : '-' }}</td>
                                        <td class="px-1.5 py-2 text-right border-r border-neutral-400 dark:border-neutral-700">{{ $pajak15 > 0 ? number_format($pajak15, 0, ',', '.') : '-' }}</td>
                                        <td class="px-1.5 py-2 text-right border-r border-neutral-400 dark:border-neutral-700">{{ $bersih15 > 0 ? number_format($bersih15, 0, ',', '.') : '-' }}</td>
                                    </tr>
                                    
                                    {{-- Baris PPH 5% --}}
                                    <tr class="border-b border-neutral-400 dark:border-neutral-800 bg-white dark:bg-neutral-900 font-medium">
                                        <td class="px-1.5 py-2 text-right border-r border-neutral-400 dark:border-neutral-700 font-bold">{{ $sg['biaya_5'] > 0 ? number_format($sg['biaya_5'], 0, ',', '.') : '-' }}</td>
                                        <td class="px-1.5 py-2 text-right border-r border-neutral-400 dark:border-neutral-700 font-bold">{{ $sg['total_5'] > 0 ? number_format($sg['total_5'], 0, ',', '.') : '-' }}</td>
                                        <td class="px-1.5 py-2 text-center border-r border-neutral-400 dark:border-neutral-700 bg-neutral-300 dark:bg-neutral-700 text-black dark:text-white font-bold">PAJAK 5 %</td>
                                        <td class="px-1.5 py-2 text-right border-r border-neutral-400 dark:border-neutral-700">{{ $sg['tim_5'] > 0 ? number_format($sg['tim_5'], 0, ',', '.') : '-' }}</td>
                                        <td class="px-1.5 py-2 text-right border-r border-neutral-400 dark:border-neutral-700">{{ $sg['majelis5_5'] > 0 ? number_format($sg['majelis5_5'], 0, ',', '.') : '-' }}</td>
                                        <td class="px-1.5 py-2 text-right border-r border-neutral-400 dark:border-neutral-700">{{ $sg['kepaniteraan_5'] > 0 ? number_format($sg['kepaniteraan_5'], 0, ',', '.') : '-' }}</td>
                                        <td class="px-1.5 py-2 text-right border-r border-neutral-400 dark:border-neutral-700">{{ $sg['pemilah_5'] > 0 ? number_format($sg['pemilah_5'], 0, ',', '.') : '-' }}</td>
                                        <td class="px-1.5 py-2 text-right border-r border-neutral-400 dark:border-neutral-700">{{ $sg['total_m_5'] > 0 ? number_format($sg['total_m_5'], 0, ',', '.') : '-' }}</td>
                                        <td class="px-1.5 py-2 text-right border-r border-neutral-400 dark:border-neutral-700">{{ $pajak5 > 0 ? number_format($pajak5, 0, ',', '.') : '-' }}</td>
                                        <td class="px-1.5 py-2 text-right border-r border-neutral-400 dark:border-neutral-700">{{ $bersih5 > 0 ? number_format($bersih5, 0, ',', '.') : '-' }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @else
                            <tr>
                                <td colspan="16" class="px-8 py-8 text-center text-red-500 font-bold bg-red-50 dark:bg-red-900/10">
                                    DATA KOSONG: Sesi Excel Anda sudah berakhir. Silakan kembali ke Dashboard dan upload ulang file Excel Anda.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Professional Footer / Signatures -->
        @if(isset($footer) && count($footer) > 0)
            <div class="mt-16 mb-24 px-8">
                @php
                    $dpMonths = [1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'];
                    $tglRekap = \Illuminate\Support\Facades\Session::get('excel_tgl_rekap_keseluruhan')
                        ?: ('Jakarta, ' . date('d') . ' ' . $dpMonths[(int)date('m')] . ' ' . date('Y'));
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center pt-8">
                    <!-- Column 1: PPK (Kiri) -->
                    <div class="flex flex-col justify-between h-44">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-neutral-600 dark:text-neutral-400">PETUGAS PEMBUAT KOMITMEN</p>
                            <p class="text-xs font-bold uppercase tracking-wider text-neutral-600 dark:text-neutral-400">BIAYA PROSES</p>
                        </div>
                        <div class="mt-auto">
                            <p class="text-sm font-bold text-neutral-900 dark:text-neutral-100 uppercase">ST. KRIS NUGROHO, S.H., M.H.</p>
                        </div>
                    </div>

                    <!-- Column 2: Mengetahui (Tengah) -->
                    <div class="flex flex-col justify-between h-44">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-neutral-600 dark:text-neutral-400">MENGETAHUI,</p>
                            <p class="text-xs font-bold uppercase tracking-wider text-neutral-600 dark:text-neutral-400">KUASA PENGELOLA BIAYA PROSES</p>
                        </div>
                        <div class="mt-auto">
                            <p class="text-sm font-bold text-neutral-900 dark:text-neutral-100 uppercase">ASEP NURSOBAH, S.Ag., M.H.</p>
                        </div>
                    </div>

                    <!-- Column 3: Bendahara (Kanan) -->
                    <div class="flex flex-col justify-between h-44">
                        <div>
                            <p class="text-sm font-semibold text-neutral-800 dark:text-neutral-200 mb-1.5">{{ $tglRekap }}</p>
                            <p class="text-xs font-bold uppercase tracking-wider text-neutral-600 dark:text-neutral-400">BENDAHARA BIAYA PROSES</p>
                        </div>
                        <div class="mt-auto">
                            <p class="text-sm font-bold text-neutral-900 dark:text-neutral-100 uppercase">FARIDA, SH</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @else
        <!-- Empty State -->
        <div class="text-center py-24">
            <svg class="w-16 h-16 text-neutral-300 dark:text-neutral-700 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-neutral-500 dark:text-neutral-400 text-lg font-medium">Data belum tersedia</p>
            <p class="text-neutral-400 dark:text-neutral-500 text-sm mt-2">Silakan upload file Excel yang mengandung sheet "Data Print" terlebih dahulu</p>
            <a href="{{ route('dashboard') }}" class="mt-6 inline-block px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors">
                Ke Dashboard
            </a>
        </div>
    @endif
</div>
@endsection
