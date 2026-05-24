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
                            <th class="px-8 py-4 text-left font-semibold text-neutral-700 dark:text-neutral-300 uppercase tracking-wide">No</th>
                            @foreach($headers as $headerKey => $headerName)
                                <th class="px-8 py-4 text-left font-semibold text-neutral-700 dark:text-neutral-300 uppercase tracking-wide text-xs">{{ $headerName }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $idx => $row)
                            <tr class="border-b border-neutral-200 dark:border-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors duration-150">
                                <td class="px-8 py-4 text-sm text-neutral-600 dark:text-neutral-400 font-medium">{{ $idx + 1 }}</td>
                                @foreach($headers as $headerKey => $headerName)
                                    <td class="px-8 py-4 text-sm text-neutral-900 dark:text-neutral-100">
                                        {{ $row[$headerName] ?? '-' }}
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
