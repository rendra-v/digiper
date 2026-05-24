@extends('layout')

@section('title', 'Data Print')

@section('content')
<div x-data="dataPrintApp()" class="min-h-screen">
    <!-- Header Section -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="text-4xl font-semibold tracking-tight mb-3">Data Print Perkara</h2>
            <p class="text-neutral-500 dark:text-neutral-400">
                @if($fileName)
                    File: <span class="font-medium text-neutral-700 dark:text-neutral-300">{{ $fileName }}</span>
                @else
                    Rekap Keseluruhan Perkara Putus Bulan Desember 2025 SD Februari 2026
                @endif
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('dashboard') }}" class="px-4 py-2 text-sm bg-neutral-200 dark:bg-neutral-800 hover:bg-neutral-300 dark:hover:bg-neutral-700 text-neutral-900 dark:text-neutral-100 rounded-lg transition-colors duration-200">
                Kembali
            </a>
            <a href="{{ route('sheet-cek') }}" class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 font-medium">
                Lihat Sheet Cek →
            </a>
        </div>
    </div>

    @if($error)
        <!-- Error Message -->
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/50 rounded-lg">
            <div class="flex">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-red-700 dark:text-red-400">Ada masalah</p>
                    <p class="text-sm text-red-600 dark:text-red-300 mt-1">{{ $error }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(count($categories) > 0 && !$error)
        <!-- Categories Tabs -->
        <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-lg overflow-hidden">
            <!-- Tab Navigation -->
            <div class="border-b border-neutral-200 dark:border-neutral-800 overflow-x-auto">
                <div class="flex">
                    @foreach($categories as $idx => $category)
                        <button
                            @click="activeCategory = {{ $idx }}"
                            :class="activeCategory === {{ $idx }} ? 'border-b-2 border-blue-600 text-blue-600 dark:text-blue-400' : 'border-b-2 border-transparent text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-200'"
                            class="px-4 py-4 text-sm font-medium transition-colors duration-200 whitespace-nowrap"
                        >
                            {{ substr($category['title'], 0, 20) }}... <span class="ml-2 text-xs bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 px-2 py-1 rounded">{{ $category['count'] ?? 0 }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Tab Content -->
            <div class="p-8">
                @foreach($categories as $idx => $category)
                    <div x-show="activeCategory === {{ $idx }}" class="space-y-6">
                        <div>
                            <h3 class="text-2xl font-semibold mb-2">{{ $category['title'] ?? 'N/A' }}</h3>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">Total perkara: <span class="font-bold text-neutral-700 dark:text-neutral-300">{{ $category['count'] ?? 0 }}</span></p>
                        </div>

                        <!-- Table -->
                        @if($category['count'] > 0 && isset($category['data']) && count($category['data']) > 0)
                            <div class="overflow-x-auto rounded-lg border border-neutral-200 dark:border-neutral-800">
                                <table class="w-full text-sm">
                                    <thead class="bg-neutral-50 dark:bg-neutral-800/50">
                                        <tr class="border-b border-neutral-200 dark:border-neutral-800">
                                            <th class="px-6 py-3 text-left font-semibold text-neutral-700 dark:text-neutral-300 text-xs">No</th>
                                            @if(isset($category['columns']))
                                                @foreach($category['columns'] as $colName)
                                                    @if($colName && $colName !== 'No')
                                                        <th class="px-6 py-3 text-left font-semibold text-neutral-700 dark:text-neutral-300 text-xs uppercase tracking-wide">{{ $colName }}</th>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($category['data'] as $rowIdx => $row)
                                            <tr class="border-b border-neutral-200 dark:border-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors duration-150">
                                                <td class="px-6 py-3 text-sm text-neutral-600 dark:text-neutral-400 font-medium">{{ $rowIdx + 1 }}</td>
                                                @if(isset($category['columns']))
                                                    @foreach($category['columns'] as $colName)
                                                        @if($colName && $colName !== 'No')
                                                            <td class="px-6 py-3 text-sm text-neutral-900 dark:text-neutral-100 truncate" title="{{ $row[$colName] ?? '-' }}">
                                                                {{ $row[$colName] ?? '-' }}
                                                            </td>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ (isset($category['columns']) ? count($category['columns']) : 0) + 1 }}" class="px-6 py-8 text-center text-neutral-500 dark:text-neutral-400">
                                                    Tidak ada data untuk kategori ini
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    @if(isset($category['total']) && $category['total'] !== null)
                                    <tfoot class="bg-neutral-100 dark:bg-neutral-800/70 border-t-2 border-neutral-300 dark:border-neutral-700">
                                        <tr>
                                            <td class="px-6 py-3 text-sm font-bold text-neutral-700 dark:text-neutral-300">TOTAL</td>
                                            @if(isset($category['columns']))
                                                @php $isFirstColumn = true; @endphp
                                                @foreach($category['columns'] as $i => $colName)
                                                    @if($colName && $colName !== 'No')
                                                        <td class="px-6 py-3 text-sm font-bold text-neutral-900 dark:text-neutral-100">
                                                            @if($isFirstColumn)
                                                                {{ $category['total'] }}
                                                                @php $isFirstColumn = false; @endphp
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                    @endif
                                                @endforeach
                                            @endif
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
            <svg class="w-16 h-16 text-neutral-300 dark:text-neutral-700 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-neutral-500 dark:text-neutral-400 text-lg font-medium">Belum ada data</p>
            <p class="text-neutral-400 dark:text-neutral-500 text-sm mt-2">Harap upload file Excel terlebih dahulu di Dashboard</p>
            <a href="{{ route('dashboard') }}" class="inline-block mt-6 px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200">
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
