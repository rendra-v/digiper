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
        <div class="flex gap-3">
            <a href="{{ route('data-print') }}" class="px-4 py-2 text-sm bg-neutral-200 dark:bg-neutral-800 hover:bg-neutral-300 dark:hover:bg-neutral-700 text-neutral-900 dark:text-neutral-100 rounded-lg transition-colors duration-200">
                ← Kembali
            </a>
            <a href="{{ route('dashboard') }}" class="px-4 py-2 text-sm text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100 rounded-lg transition-colors duration-200">
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

    @if(count($data) > 0)
        <!-- Table Section -->
        <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-lg overflow-hidden">
            <!-- Table Header -->
            <div class="px-8 py-6 border-b border-neutral-200 dark:border-neutral-800">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold">Data Sheet Cek</h3>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">{{ count($data) }} baris data</p>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-neutral-50 dark:bg-neutral-800/50">
                        <tr class="border-b border-neutral-200 dark:border-neutral-800">
                            @foreach($headers as $headerKey => $headerName)
                                <th class="px-8 py-4 text-left font-semibold text-neutral-700 dark:text-neutral-300 uppercase tracking-wide text-xs">{{ $headerName }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $idx => $row)
                            <tr class="border-b border-neutral-200 dark:border-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors duration-150">
                                @foreach($headers as $colLetter => $headerName)
                                    @php 
                                        $key = $colToKey[$colLetter] ?? $headerName;
                                        $value = $row[$key] ?? null;
                                        $rowspan = $row['_rowspans'][$key] ?? 1;
                                    @endphp
                                    
                                    @if($value === 'SKIP_OR_NULL')
                                        @continue
                                    @endif
                                    
                                    <td class="px-8 py-4 text-sm text-neutral-900 dark:text-neutral-100 border-r border-neutral-100 dark:border-neutral-800"
                                        @if($rowspan > 1) rowspan="{{ $rowspan }}" @endif>
                                        @if(is_numeric($value))
                                            @if((float)$value === 0.0)
                                                -
                                            @else
                                                {{ number_format((float)$value, 0, ',', '.') }}
                                            @endif
                                        @else
                                            {{ $value ?? '-' }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($headers) + 1 }}" class="px-8 py-8 text-center text-neutral-500 dark:text-neutral-400">
                                    Tidak ada data
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Info -->
            <div class="px-8 py-4 border-t border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-800/50">
                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                    Menampilkan <strong>{{ count($data) }}</strong> dari <strong>{{ count($data) }}</strong> baris
                </p>
            </div>
        </div>

        <!-- Professional Footer / Signatures -->
        @if(isset($footer) && count($footer) > 0)
            <div class="mt-16 mb-24 px-8">
                @php
                    $fullText = '';
                    foreach($footer as $fRow) {
                        foreach($fRow as $k => $v) {
                            if($k !== '_rowspans' && $k !== '_original_row' && $v && $v !== 'SKIP_OR_NULL') {
                                $fullText .= ' ' . $v;
                            }
                        }
                    }

                    // Extract Date
                    $date = '';
                    if (preg_match('/(Jakarta,\s*\d{1,2}\s+[A-Z][a-z]+\s+\d{4})/', $fullText, $matches)) {
                        $date = $matches[1];
                    }

                    // Extract People
                    // Bendahara
                    $bendaharaName = '';
                    if (preg_match('/FARIDA,\s*SH/', $fullText, $m)) $bendaharaName = 'FARIDA, S.H.';
                    
                    // Mengetahui
                    $mengetahuiName = '';
                    if (preg_match('/ASEP NURSOBAH,\s*S\.Ag\.,\s*M\.H\./', $fullText, $m)) $mengetahuiName = 'ASEP NURSOBAH, S.Ag., M.H.';
                    
                    // PPK
                    $ppkName = '';
                    if (preg_match('/ST\.\s*KRIS NUGROHO,\s*S\.H\.,\s*M\.H\./', $fullText, $m)) $ppkName = 'ST. KRIS NUGROHO, S.H., M.H.';
                @endphp

                <div class="flex flex-col items-end mb-12">
                    <p class="text-sm font-medium text-neutral-800 dark:text-neutral-200">{{ $date ?: 'Jakarta, 05 Maret 2026' }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-12 text-center">
                    <!-- Column 1: Bendahara -->
                    <div class="flex flex-col justify-between h-48">
                        <p class="text-xs font-bold uppercase tracking-widest text-neutral-500 dark:text-neutral-500">Bendahara Biaya Proses</p>
                        <div class="mt-auto">
                            <p class="text-sm font-bold text-neutral-900 dark:text-neutral-100 underline decoration-2 underline-offset-4">{{ $bendaharaName ?: 'FARIDA, S.H.' }}</p>
                        </div>
                    </div>

                    <!-- Column 2: Mengetahui -->
                    <div class="flex flex-col justify-between h-48">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-neutral-500 dark:text-neutral-500 mb-1">Mengetahui</p>
                            <p class="text-xs font-medium text-neutral-600 dark:text-neutral-400">Kuasa Pengelola Biaya Proses</p>
                        </div>
                        <div class="mt-auto">
                            <p class="text-sm font-bold text-neutral-900 dark:text-neutral-100 underline decoration-2 underline-offset-4">{{ $mengetahuiName ?: 'ASEP NURSOBAH, S.Ag., M.H.' }}</p>
                        </div>
                    </div>

                    <!-- Column 3: PPK -->
                    <div class="flex flex-col justify-between h-48">
                        <p class="text-xs font-bold uppercase tracking-widest text-neutral-500 dark:text-neutral-500">Petugas Pembuat Komitmen Biaya Proses</p>
                        <div class="mt-auto">
                            <p class="text-sm font-bold text-neutral-900 dark:text-neutral-100 underline decoration-2 underline-offset-4">{{ $ppkName ?: 'ST. KRIS NUGROHO, S.H., M.H.' }}</p>
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
            <p class="text-neutral-500 dark:text-neutral-400 text-lg font-medium">Sheet "cek" tidak ditemukan</p>
            <p class="text-neutral-400 dark:text-neutral-500 text-sm mt-2">Pastikan file Excel berisi sheet bernama "cek"</p>
        </div>
    @endif
</div>
@endsection
