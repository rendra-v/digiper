@extends('layout')

@section('title', 'Data Print')

@section('content')
    <div x-data="dataPrintApp()" class="min-h-screen">
        <!-- Header Section -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h2 class="text-4xl font-semibold tracking-tight mb-3">Data Print Perkara</h2>
                <p class="text-neutral-500 dark:text-neutral-400">
                    @if ($fileName)
                        File: <span class="font-medium text-neutral-700 dark:text-neutral-300">{{ $fileName }}</span>
                    @else
                        Rekap Keseluruhan Perkara Putus Bulan Desember 2025 SD Februari 2026
                    @endif
                </p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('dashboard') }}"
                    class="px-4 py-2 text-sm bg-neutral-200 dark:bg-neutral-800 hover:bg-neutral-300 dark:hover:bg-neutral-700 text-neutral-900 dark:text-neutral-100 rounded-lg transition-colors duration-200">
                    Kembali
                </a>
                <a href="{{ route('sheet-cek') }}"
                    class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 font-medium">
                    Lihat Sheet Cek →
                </a>
            </div>
        </div>

        @if ($error)
            <!-- Error Message -->
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/50 rounded-lg">
                <div class="flex">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 mr-3 flex-shrink-0" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-red-700 dark:text-red-400">Ada masalah</p>
                        <p class="text-sm text-red-600 dark:text-red-300 mt-1">{{ $error }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (count($categories) > 0 && !$error)
            <!-- Categories Tabs -->
            <div
                class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-lg overflow-hidden">
                <!-- Category Selector -->
                <div class="border-b border-neutral-200 dark:border-neutral-800 p-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div class="w-full lg:max-w-xl">
                            <label for="category-select"
                                class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Pilih kategori perkara
                            </label>
                            <div class="relative">
                                <select id="category-select" x-model.number="activeCategory"
                                    class="w-full appearance-none rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-950 px-4 py-3 pr-11 text-sm font-medium text-neutral-900 dark:text-neutral-100 shadow-sm transition-colors duration-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                                    @foreach ($categories as $idx => $category)
                                        <option value="{{ $idx }}">
                                            {{ $category['title'] ?? 'N/A' }} ({{ $category['count'] ?? 0 }})
                                        </option>
                                    @endforeach
                                </select>
                                <div
                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-neutral-500 dark:text-neutral-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 9 6 6 6-6" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('data-print.print') }}" target="_blank" rel="noopener"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition-colors duration-200 hover:bg-blue-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 9V2h12v7" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18H5a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 14h8v8H8z" />
                            </svg>
                            Print PDF
                        </a>
                    </div>
                </div>

                <!-- Tab Content -->
                <div class="p-8">
                    @foreach ($categories as $idx => $category)
                        <div x-show="activeCategory === {{ $idx }}" class="space-y-6">
                            <div>
                                <h3 class="text-2xl font-semibold mb-2">{{ $category['title'] ?? 'N/A' }}</h3>
                                <p class="text-sm text-neutral-500 dark:text-neutral-400">Total perkara: <span
                                        class="font-bold text-neutral-700 dark:text-neutral-300">{{ $category['count'] ?? 0 }}</span>
                                </p>
                            </div>

                            @php
                                $excludedColumns = [
                                    'U',
                                    'V',
                                    'QTY',
                                    'P1',
                                    'P2',
                                    'P3',
                                    'P4',
                                    'P5',
                                    'PP',
                                    'cek bulan',
                                    'cek umur',
                                    'panmud',
                                    'Jenis Perkara',
                                    'Jenis Permohonan',
                                    'klasifikasi',
                                    'MJELIS',
                                    'AK',
                                    'AL',
                                    'X',
                                    'Y',
                                    'Z',
                                    'AA',
                                    'AB',
                                    'AC',
                                    'AD',
                                    '=R1744',
                                    '=H1744',
                                    '=C1744',
                                    '=D1744',
                                    '=S1744',
                                    'AJ',
                                ];

                                $visibleColumns = collect($category['columns'] ?? [])
                                    ->filter(fn ($colName) => $colName && $colName !== 'No' && ! in_array($colName, $excludedColumns, true))
                                    ->values();
                            @endphp

                            <!-- Table -->
                            @if ($category['count'] > 0 && isset($category['data']) && count($category['data']) > 0)
                                <div class="overflow-x-auto rounded-lg border border-neutral-200 dark:border-neutral-800">
                                    <table class="w-full text-sm">
                                        <thead class="bg-neutral-50 dark:bg-neutral-800/50">
                                            <tr class="border-b border-neutral-200 dark:border-neutral-800">
                                                <th
                                                    class="px-6 py-3 text-left font-semibold text-neutral-700 dark:text-neutral-300 text-xs">
                                                    No</th>
                                                @foreach ($visibleColumns as $colName)
                                                    <th
                                                        class="px-6 py-3 text-left font-semibold text-neutral-700 dark:text-neutral-300 text-xs uppercase tracking-wide">
                                                        {{ $colName }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($category['data'] as $rowIdx => $row)
                                                <tr
                                                    class="border-b border-neutral-200 dark:border-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors duration-150">
                                                    <td
                                                        class="px-6 py-3 text-sm text-neutral-600 dark:text-neutral-400 font-medium">
                                                        {{ $rowIdx + 1 }}</td>
                                                    @foreach ($visibleColumns as $colName)
                                                        <td class="px-6 py-3 text-sm text-neutral-900 dark:text-neutral-100 truncate"
                                                            title="{{ $row[$colName] ?? '-' }}">
                                                            {{ $row[$colName] ?? '-' }}
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ $visibleColumns->count() + 1 }}"
                                                        class="px-6 py-8 text-center text-neutral-500 dark:text-neutral-400">
                                                        Tidak ada data untuk kategori ini
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        @if (isset($category['total']) && $category['total'] !== null)
                                            <tfoot
                                                class="bg-neutral-100 dark:bg-neutral-800/70 border-t-2 border-neutral-300 dark:border-neutral-700">
                                                <tr>
                                                    <td
                                                        class="px-6 py-3 text-sm font-bold text-neutral-700 dark:text-neutral-300">
                                                        TOTAL</td>
                                                    @php $isFirstColumn = true; @endphp
                                                    @foreach ($visibleColumns as $colName)
                                                        <td
                                                            class="px-6 py-3 text-sm font-bold text-neutral-900 dark:text-neutral-100">
                                                            @if ($isFirstColumn)
                                                                {{ $category['total'] }}
                                                                @php $isFirstColumn = false; @endphp
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            </tfoot>
                                        @endif
                                    </table>
                                </div>
                            @else
                                <div
                                    class="text-center py-12 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg border border-neutral-200 dark:border-neutral-800">
                                    <p class="text-neutral-500 dark:text-neutral-400">Belum ada data untuk kategori ini</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif(!$error)
            <!-- Empty State -->
            <div class="text-center py-24">
                <svg class="w-16 h-16 text-neutral-300 dark:text-neutral-700 mx-auto mb-6" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-neutral-500 dark:text-neutral-400 text-lg font-medium">Belum ada data</p>
                <p class="text-neutral-400 dark:text-neutral-500 text-sm mt-2">Harap upload file Excel terlebih dahulu di
                    Dashboard</p>
                <a href="{{ route('dashboard') }}"
                    class="inline-block mt-6 px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200">
                    Ke Dashboard
                </a>
            </div>
        @endif
    </div>

    <script>
        function dataPrintApp() {
            return {
                activeCategory: 0,
            }
        }
    </script>
@endsection
