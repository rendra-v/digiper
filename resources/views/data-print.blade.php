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
                <!-- Category Selector + Nav Dropdown -->
                <div class="border-b border-neutral-200 dark:border-neutral-800 p-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">

                        {{-- ── Kiri: dua dropdown berdampingan ── --}}
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end w-full lg:flex-1">

                            {{-- Dropdown 1: Pilih Kategori Perkara --}}
                            <div class="flex-1 min-w-0">
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
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-neutral-500 dark:text-neutral-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 9 6 6 6-6" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            {{-- Divider vertikal --}}
                            <div class="hidden sm:block self-stretch w-px bg-neutral-200 dark:bg-neutral-700 mb-0.5"></div>

                            {{-- Dropdown 2: Navigasi ke Halaman Lain --}}
                            <div class="sm:w-60">
                                <label for="nav-page-select"
                                    class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Lihat halaman lain
                                </label>
                                <div class="relative">
                                    <select id="nav-page-select"
                                        onchange="if(this.value) window.location.href = this.value"
                                        class="w-full appearance-none rounded-lg border border-emerald-300 dark:border-emerald-700 bg-white dark:bg-neutral-950 px-4 py-3 pr-11 text-sm font-medium text-neutral-900 dark:text-neutral-100 shadow-sm transition-colors duration-200 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 cursor-pointer">
                                        <option value="" disabled selected>— Pilih halaman —</option>
                                        <option value="{{ route('sheet-cek') }}">📋&nbsp; Lihat Sheet Cek</option>
                                        <option value="{{ route('rekap-keseluruhan') }}">📊&nbsp; Rekap Keseluruhan</option>
                                        <option value="{{ route('honorarium') }}">💰&nbsp; Honorarium Biaya</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-emerald-600 dark:text-emerald-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- ── Kanan: Print PDF ── --}}
                        <a href="{{ route('data-print.print') }}" target="_blank" rel="noopener"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition-colors duration-200 hover:bg-blue-700 flex-shrink-0">
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
                <div class="p-6">
                    @foreach ($categories as $idx => $category)
                        <div x-show="activeCategory === {{ $idx }}" class="space-y-4">
                            <div>
                                <h3 class="text-xl font-semibold mb-1">{{ $category['title'] ?? 'N/A' }}</h3>
                                <p class="text-sm text-neutral-500 dark:text-neutral-400">Total perkara: <span
                                        class="font-bold text-neutral-700 dark:text-neutral-300">{{ $category['count'] ?? 0 }}</span>
                                </p>
                            </div>

                            @php
                                $excludedColumns = [
                                    'U', 'V', 'QTY', 'P1', 'P2', 'P3', 'P4', 'P5', 'PP', 'cek bulan', 'cek umur', 
                                    'panmud', 'Jenis Perkara', 'Jenis Permohonan', 'klasifikasi', 'MJELIS', 
                                    'AK', 'AL', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AJ',
                                ];

                                $visibleColumns = collect($category['columns'] ?? [])
                                    ->filter(function ($colName) use ($excludedColumns) {
                                        if (!$colName || $colName === 'No') return false;
                                        // Kolom berisi formula Excel (=R1744 dll)
                                        if (str_starts_with($colName, '=')) return false;
                                        // Kolom berisi huruf Excel saja (A–Z, AA–AZ) atau angka saja (1, 2, dst.)
                                        if (preg_match('/^[A-Z]{1,3}$/', $colName)) return false;
                                        if (is_numeric($colName)) return false;
                                        if (in_array($colName, $excludedColumns, true)) return false;
                                        return true;
                                    })
                                    ->values();
                            @endphp

                            <!-- Table -->
                            @if ($category['count'] > 0 && isset($category['data']) && count($category['data']) > 0)
                                <div class="overflow-x-auto rounded-lg border border-neutral-200 dark:border-neutral-800">
                                    <table class="w-full text-sm">
                                        <thead class="bg-neutral-50 dark:bg-neutral-800/50 sticky top-0">
                                            <tr class="border-b border-neutral-200 dark:border-neutral-800">
                                                <th class="px-4 py-2.5 text-center font-semibold text-neutral-700 dark:text-neutral-300 text-xs w-10">No</th>
                                                @foreach ($visibleColumns as $colName)
                                                    <th class="px-4 py-2.5 text-left font-semibold text-neutral-700 dark:text-neutral-300 text-xs uppercase tracking-wide whitespace-nowrap">
                                                        {{ $colName }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $displayNum = 0; @endphp
                                            @forelse($category['data'] as $rowIdx => $row)
                                                @php
                                                    // ── Filter 1: skip jika kolom No bukan angka ──
                                                    $noVal = trim((string)($row['No'] ?? ''));
                                                    if ($noVal !== '' && !is_numeric($noVal)) {
                                                        // Ini kemungkinan section header atau baris judul
                                                        continue;
                                                    }

                                                    // ── Filter 2: skip jika semua kolom visible kosong / '-' ──
                                                    $meaningfulCount = 0;
                                                    foreach ($visibleColumns as $colName) {
                                                        $v = trim((string)($row[$colName] ?? ''));
                                                        if ($v !== '' && $v !== '-') $meaningfulCount++;
                                                    }
                                                    if ($meaningfulCount === 0 && trim($noVal) === '') continue;

                                                    $displayNum++;
                                                @endphp
                                                <tr class="border-b border-neutral-200 dark:border-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors duration-150
                                                    {{ $displayNum % 2 === 0 ? 'bg-neutral-50/40 dark:bg-neutral-800/10' : '' }}">
                                                    <td class="px-4 py-2.5 text-xs text-center text-neutral-500 dark:text-neutral-400 font-medium w-10">
                                                        {{ $displayNum }}</td>
                                                    @foreach ($visibleColumns as $colName)
                                                        <td class="px-4 py-2.5 text-xs text-neutral-900 dark:text-neutral-100 max-w-[220px] truncate"
                                                            title="{{ $row[$colName] ?? '' }}">
                                                            {{ $row[$colName] !== null && $row[$colName] !== '' ? $row[$colName] : '-' }}
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
                                        @if (count($category['data']) < $category['count'])
                                            <tbody class="bg-amber-50 dark:bg-amber-950/20">
                                                <tr>
                                                    <td colspan="{{ $visibleColumns->count() + 1 }}" class="px-4 py-2.5 text-center text-xs text-amber-700 dark:text-amber-300">
                                                        ℹ️ Menampilkan {{ count($category['data']) }} dari {{ $category['count'] }} data ({{ round((count($category['data']) / $category['count']) * 100) }}%)
                                                    </td>
                                                </tr>
                                            </tbody>
                                        @endif
                                        @if (isset($category['total']) && $category['total'] !== null)
                                            <tfoot class="bg-neutral-100 dark:bg-neutral-800/70 border-t-2 border-neutral-300 dark:border-neutral-700">
                                                <tr>
                                                    <td class="px-4 py-2.5 text-xs font-bold text-neutral-700 dark:text-neutral-300 text-center">TOTAL</td>
                                                    @php $isFirstColumn = true; @endphp
                                                    @foreach ($visibleColumns as $colName)
                                                        <td class="px-4 py-2.5 text-xs font-bold text-neutral-900 dark:text-neutral-100">
                                                            @if ($isFirstColumn)
                                                                {{ $category['total'] }}
                                                                @php $isFirstColumn = false; @endphp
                                                            @else
                                                                —
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            </tfoot>
                                        @endif
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-12 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg border border-neutral-200 dark:border-neutral-800">
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
                <p class="text-neutral-400 dark:text-neutral-500 text-sm mt-2">Harap upload file Excel terlebih dahulu di Dashboard</p>
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
