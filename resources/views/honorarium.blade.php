@extends('layout')

@section('title', 'Honorarium Biaya Perkara')

@section('content')
<div x-data="honorariumApp()" class="min-h-screen">

    {{-- ─── Header ─── --}}
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="text-4xl font-semibold tracking-tight mb-3">Honorarium Biaya Perkara</h2>
            <p class="text-neutral-500 dark:text-neutral-400">
                Daftar honorarium biaya penyelesaian perkara per kamar
                @if($fileName)
                    — <span class="font-medium text-neutral-700 dark:text-neutral-300">{{ $fileName }}</span>
                @endif
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('dashboard') }}"
               class="px-4 py-2 text-sm bg-neutral-200 dark:bg-neutral-800 hover:bg-neutral-300 dark:hover:bg-neutral-700 text-neutral-900 dark:text-neutral-100 rounded-lg transition-colors duration-200">
                Dashboard
            </a>
        </div>
    </div>

    {{-- ─── Error ─── --}}
    @if($error)
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/50 rounded-lg">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm text-red-700 dark:text-red-400">{{ $error }}</p>
            </div>
        </div>
    @endif

    @if(!$error && count($sheets) > 0)

        {{-- ─── Card Utama ─── --}}
        <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-lg overflow-hidden">

            {{-- ── Toolbar: Tab Sheet + Dropdown Nav ── --}}
            <div class="border-b border-neutral-200 dark:border-neutral-800 p-4">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">

                    {{-- Kiri: Tab sheet (jika > 1) + Dropdown navigasi --}}
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end w-full lg:flex-1">

                        {{-- Tab pilih sheet (hanya muncul kalau ada > 1 sheet) --}}
                        @if(count($sheets) > 1)
                        <div class="flex-1 min-w-0">
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Pilih sheet honorarium
                            </label>
                            <div class="relative">
                                <select x-model.number="activeSheet"
                                    class="w-full appearance-none rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-950 px-4 py-3 pr-11 text-sm font-medium text-neutral-900 dark:text-neutral-100 shadow-sm transition-colors duration-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                                    @foreach($sheets as $idx => $sheet)
                                        <option value="{{ $idx }}">{{ $sheet['sheetName'] }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-neutral-500 dark:text-neutral-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 9 6 6 6-6"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        {{-- Divider vertikal --}}
                        <div class="hidden sm:block self-stretch w-px bg-neutral-200 dark:bg-neutral-700 mb-0.5"></div>
                        @endif

                        {{-- Dropdown: Lihat Halaman Lain --}}
                        <div class="{{ count($sheets) > 1 ? 'sm:w-60' : 'flex-1 max-w-xs' }}">
                            <label for="nav-page-select-honor"
                                class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Lihat halaman lain
                            </label>
                            <div class="relative">
                                <select id="nav-page-select-honor"
                                    onchange="if(this.value) window.location.href = this.value"
                                    class="w-full appearance-none rounded-lg border border-emerald-300 dark:border-emerald-700 bg-white dark:bg-neutral-950 px-4 py-3 pr-11 text-sm font-medium text-neutral-900 dark:text-neutral-100 shadow-sm transition-colors duration-200 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 cursor-pointer">
                                    <option value="" disabled selected>— Pilih halaman —</option>
                                    <option value="{{ route('data-print') }}">📄&nbsp; Data Print Perkara</option>
                                    <option value="{{ route('sheet-cek') }}">📋&nbsp; Sheet Cek</option>
                                    <option value="{{ route('rekap-keseluruhan') }}">📊&nbsp; Rekap Keseluruhan</option>
                                    <option value="{{ route('rekap-keseluruhan-2') }}">📈&nbsp; Rekap Keseluruhan 2</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-emerald-600 dark:text-emerald-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Kanan: Print PDF --}}
                    <a href="{{ route('honorarium.print') }}" target="_blank" rel="noopener"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition-colors duration-200 hover:bg-blue-700 flex-shrink-0">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 9V2h12v7"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18H5a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 14h8v8H8z"/>
                        </svg>
                        Print PDF
                    </a>

                </div>
            </div>

            {{-- ── Konten per Sheet ── --}}
            @foreach($sheets as $idx => $sheet)
            <div x-show="activeSheet === {{ $idx }}" x-cloak>

                {{-- Judul dari Excel --}}
                <div class="px-6 py-5 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/20 dark:to-indigo-950/20 border-b border-neutral-200 dark:border-neutral-800">
                    @php $titleParts = array_filter(array_map('trim', explode("\n", $sheet['title']))); @endphp
                    @if(count($titleParts))
                        @foreach($titleParts as $line)
                            <p class="text-sm font-bold text-center uppercase tracking-wide text-neutral-900 dark:text-neutral-100 leading-relaxed">
                                {{ $line }}
                            </p>
                        @endforeach
                    @else
                        <p class="text-sm font-bold text-center uppercase tracking-wide text-neutral-900 dark:text-neutral-100">
                            HONORARIUM BIAYA PENYELESAIAN PERKARA
                        </p>
                    @endif
                </div>

                {{-- Info row count --}}
                <div class="px-6 py-3 border-b border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-800/30">
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">
                        <span class="font-semibold text-neutral-700 dark:text-neutral-300">{{ count($sheet['rows']) }}</span> baris data
                        &nbsp;·&nbsp; Sheet: <span class="font-semibold text-neutral-700 dark:text-neutral-300">{{ $sheet['sheetName'] }}</span>
                    </p>
                </div>

                {{-- Tabel --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse">
                        <thead class="bg-blue-600 text-white sticky top-0">
                            <tr>
                                @foreach($sheet['headers'] as $colIdx => $headerName)
                                    @php
                                        $upper = strtoupper(trim($headerName));
                                        $isWide   = in_array($upper, ['NAMA', 'NAMA LENGKAP', 'JABATAN']);
                                        $isNarrow = in_array($upper, ['NO', 'NO.', 'NOMOR']);
                                        $isTtd    = str_contains($upper, 'TANDA') || str_contains($upper, 'TTD');
                                    @endphp
                                    <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wide border-r border-blue-500/50 last:border-r-0
                                        {{ $isNarrow ? 'w-10' : ($isWide ? 'min-w-[160px]' : ($isTtd ? 'min-w-[100px]' : 'min-w-[90px]')) }}">
                                        {{ $headerName }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @php $rowNum = 0; @endphp
                            @foreach($sheet['rows'] as $row)
                                @php
                                    $firstKey = array_key_first($row);
                                    $firstVal = trim((string)($row[$firstKey] ?? ''));
                                    $isSummary = !is_numeric($firstVal) && $firstVal !== '' && strtoupper($firstVal) !== 'NO';
                                    if (!$isSummary) $rowNum++;

                                    // Skip baris kosong: semua nilai adalah kosong atau nol
                                    $rowIsEmpty = collect($row)->every(function($v, $k) {
                                        if ($k === '_rowspans' || $k === '_original_row') return true;
                                        $s = trim((string)($v ?? ''));
                                        return $s === '' || $s === '0' || (is_numeric($s) && (float)$s == 0);
                                    });
                                @endphp
                                @if($rowIsEmpty) @continue @endif
                                <tr class="border-b border-neutral-100 dark:border-neutral-800 transition-colors duration-100
                                    {{ $isSummary
                                        ? 'bg-neutral-100 dark:bg-neutral-800/60'
                                        : ($rowNum % 2 === 0
                                            ? 'bg-neutral-50/60 hover:bg-blue-50/40 dark:bg-neutral-800/20 dark:hover:bg-blue-900/10'
                                            : 'bg-white dark:bg-neutral-900 hover:bg-blue-50/40 dark:hover:bg-blue-900/10') }}">
                                    @foreach($sheet['headers'] as $colIdx => $headerName)
                                        @php
                                            $val   = $row[$headerName] ?? '';
                                            $upper = strtoupper(trim($headerName));

                                            $isNumericCol = in_array($upper, ['BIAYA','JUMLAH BIAYA','PPH 15%','PPH 5%','NETTO','PPH','PAJAK','TOTAL'])
                                                || str_contains($upper, 'BIAYA')
                                                || str_contains($upper, 'NETTO')
                                                || str_contains($upper, 'PPH');
                                            $isNoCol  = in_array($upper, ['NO','NO.','NOMOR']);
                                            $isJmlCol = str_contains($upper,'PERKARA') && str_contains($upper,'JUMLAH');
                                            $isTtdCol = str_contains($upper,'TANDA') || str_contains($upper,'TTD');

                                            // Format angka
                                            $displayVal = $val;
                                            $stripped = str_replace(['.', ',', ' ', 'Rp'], '', $val);
                                            if ($isNumericCol && $stripped !== '' && is_numeric($stripped)) {
                                                $num = (float) $stripped;
                                                $displayVal = $num != 0 ? 'Rp ' . number_format($num, 0, ',', '.') : '-';
                                            } elseif ($val === '' || $val === null) {
                                                $displayVal = $isTtdCol ? '' : '';
                                            }
                                        @endphp
                                        <td class="px-4 py-2.5 text-xs border-r border-neutral-100 dark:border-neutral-800/60 last:border-r-0
                                            {{ $isNoCol    ? 'text-center text-neutral-500 dark:text-neutral-400 w-10' : '' }}
                                            {{ $isNumericCol ? 'text-right tabular-nums font-medium' : '' }}
                                            {{ $isJmlCol   ? 'text-center font-semibold' : '' }}
                                            {{ $isTtdCol   ? 'text-center' : '' }}
                                            {{ !$isNoCol && !$isNumericCol && !$isJmlCol && !$isTtdCol ? 'text-left' : '' }}
                                            {{ $isSummary  ? 'font-bold text-neutral-800 dark:text-neutral-200' : 'text-neutral-800 dark:text-neutral-200' }}">
                                            {{ $displayVal }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Footer info --}}
                <div class="px-6 py-3 bg-neutral-50 dark:bg-neutral-800/30 border-t border-neutral-200 dark:border-neutral-800">
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                        Menampilkan <strong class="text-neutral-700 dark:text-neutral-300">{{ count($sheet['rows']) }}</strong> baris dari sheet <strong class="text-neutral-700 dark:text-neutral-300">{{ $sheet['sheetName'] }}</strong>
                    </p>
                </div>

            </div>
            @endforeach

        </div>{{-- end card --}}

    @elseif(!$error)
        {{-- ─── Empty State ─── --}}
        <div class="text-center py-24">
            <svg class="w-16 h-16 text-neutral-300 dark:text-neutral-700 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-neutral-500 dark:text-neutral-400 text-lg font-medium">Belum ada data honorarium</p>
            <p class="text-neutral-400 dark:text-neutral-500 text-sm mt-2">Harap upload file Excel terlebih dahulu di Dashboard</p>
            <a href="{{ route('dashboard') }}"
               class="inline-block mt-6 px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200">
                Ke Dashboard
            </a>
        </div>
    @endif

</div>

<script>
    function honorariumApp() {
        return {
            activeSheet: {{ $activeSheet ?? 0 }},
        }
    }
</script>
@endsection
