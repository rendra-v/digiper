@extends('layout')

@section('title', 'Honorarium Biaya Perkara')

@section('content')
<div x-data="honorariumApp()" class="min-h-screen">

    {{-- ─── Header ─── --}}
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="text-4xl font-semibold tracking-tight mb-3">Honorarium Per Kamar</h2>
            <p class="text-neutral-500 dark:text-neutral-400">
                Dokumen honorarium biaya penyelesaian perkara per jenis kamar
                @if($fileName)
                    — <span class="font-medium text-neutral-700 dark:text-neutral-300">{{ $fileName }}</span>
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            @if(!$error && count($sheets) > 0)
            <button @click="window.open('{{ route('honorarium.print') }}?sheet=' + activeSheet + '&block=' + (activeBlock[activeSheet] === null ? 'all' : activeBlock[activeSheet]), '_blank')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 font-medium shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <polyline points="6 9 6 2 18 2 18 9"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
                    <rect x="6" y="14" width="12" height="8"/>
                </svg>
                Cetak PDF
            </button>
            @endif
            <a href="{{ route('dashboard') }}"
               class="px-4 py-2.5 text-sm bg-neutral-200 dark:bg-neutral-800 hover:bg-neutral-300 dark:hover:bg-neutral-700 text-neutral-900 dark:text-neutral-100 rounded-lg transition-colors duration-200">
                Dashboard
            </a>
        </div>
    </div>

    {{-- ─── Error ─── --}}
    @if($error)
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/50 rounded-xl">
            <p class="text-sm text-red-700 dark:text-red-400">{{ $error }}</p>
        </div>
    @endif

    @if(!$error && count($sheets) > 0)

        {{-- ─── Sheet Tabs ─── --}}
        <div class="flex gap-2 mb-6 flex-wrap">
            @foreach($sheets as $si => $sheet)
                <button @click="activeSheet = {{ $si }}"
                        :class="activeSheet === {{ $si }}
                            ? 'bg-blue-600 text-white shadow-sm'
                            : 'bg-white dark:bg-neutral-900 text-neutral-700 dark:text-neutral-300 border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800'"
                        class="px-5 py-2.5 rounded-lg text-sm font-medium transition-all duration-200">
                    {{ $sheet['sheetName'] }}
                    <span class="ml-2 text-xs opacity-70">({{ count($sheet['blocks']) }} dok)</span>
                </button>
            @endforeach
        </div>

        {{-- ─── Sheets Content ─── --}}
        @foreach($sheets as $si => $sheet)
        <div x-show="activeSheet === {{ $si }}" x-cloak>

            {{-- Block filter: dropdown pilih dokumen --}}
            <div class="mb-6 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl p-4">
                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-3">
                    Pilih Dokumen — {{ $sheet['sheetName'] }}
                </label>
                <div class="flex items-center gap-3">
                    <div class="relative flex-1 max-w-xl">
                        <select
                            @change="activeBlock[{{ $si }}] = $event.target.value === 'null' ? null : parseInt($event.target.value)"
                            :value="activeBlock[{{ $si }}] === null ? 'null' : activeBlock[{{ $si }}]"
                            class="w-full appearance-none rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-950 px-4 py-2.5 pr-10 text-sm text-neutral-900 dark:text-neutral-100 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-colors duration-200 cursor-pointer">
                            <option value="null">— Semua Dokumen ({{ count($sheet['blocks']) }}) —</option>
                            @foreach($sheet['blocks'] as $bi => $block)
                                @php
                                    $shortTitle = preg_replace('/^HONORARIUM BIAYA PENYELESAIAN PERKARA\s*/i', '', $block['title1']);
                                    // Ambil jumlah perkara dari title3
                                    preg_match('/Sebanyak\s*([\d]+)\s*Perkara/i', $block['title3'], $m);
                                    $jmlPerkara = isset($m[1]) ? ' — ' . $m[1] . ' Perkara' : '';
                                @endphp
                                <option value="{{ $bi }}">{{ $shortTitle }}{{ $jmlPerkara }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-neutral-500 dark:text-neutral-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/>
                            </svg>
                        </div>
                    </div>
                    <span class="text-xs text-neutral-400 dark:text-neutral-500 whitespace-nowrap">
                        <span x-text="activeBlock[{{ $si }}] === null ? '{{ count($sheet['blocks']) }} dokumen ditampilkan' : '1 dokumen ditampilkan'"></span>
                    </span>
                </div>
            </div>

            {{-- Dokumen per blok --}}
            @foreach($sheet['blocks'] as $bi => $block)
            <div x-show="activeBlock[{{ $si }}] === null || activeBlock[{{ $si }}] === {{ $bi }}"
                 class="mb-8 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl shadow-sm overflow-hidden">

                {{-- Judul dokumen --}}
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/30 dark:to-indigo-950/30 border-b border-blue-100 dark:border-blue-900/50 px-6 py-5 text-center">
                    <p class="text-sm font-bold uppercase tracking-wide text-neutral-900 dark:text-neutral-100">
                        {{ $block['title1'] }}
                    </p>
                    <p class="text-xs font-semibold uppercase tracking-wide text-neutral-700 dark:text-neutral-300 mt-1">
                        {{ $block['title2'] }}
                    </p>
                    <p class="text-sm font-bold text-blue-700 dark:text-blue-400 mt-1.5">
                        {{ $block['title3'] }}
                    </p>
                </div>

                {{-- Tabel data --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-xs border-collapse">
                        {{-- Header --}}
                        <thead>
                            <tr class="bg-slate-100 dark:bg-slate-800/60 border-b-2 border-slate-300 dark:border-slate-600">
                                @foreach($block['headers'] as $colIdx => $hdr)
                                    @php
                                        $hdrUp = strtoupper(trim($hdr ?? ''));
                                        $isNo   = $hdrUp === 'NO' || $hdrUp === 'NO.';
                                        $isNama = strpos($hdrUp, 'NAMA') === 0;
                                        $isJab  = strpos($hdrUp, 'JABATAN') !== false || strpos($hdrUp, 'NAMA OPERATOR') !== false;
                                        $isNum  = !$isNo && !$isNama && !$isJab;
                                        $thAlign = $isNum ? 'text-center' : ($isNo ? 'text-center' : 'text-left');
                                        $thWidth = $isNo ? 'w-8' : ($isNama || $isJab ? 'min-w-[140px]' : 'min-w-[90px]');
                                    @endphp
                                    <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 {{ $thAlign }} {{ $thWidth }} whitespace-nowrap">
                                        {{ $hdr ?? '' }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        {{-- Body --}}
                        <tbody>
                            @php $rowNum = 0; @endphp
                            @foreach($block['rows'] as $row)
                                @php
                                    $rowNum++;
                                    $isEven = $rowNum % 2 === 0;
                                    $bgClass = $isEven ? 'bg-slate-50/50 dark:bg-slate-800/20' : '';

                                    // Cek apakah baris punya nama (kolom 2) — jika tidak, ini baris biaya saja (merged cell issue)
                                    $namaVal = $row[2] ?? '';
                                    $jabVal  = $row[3] ?? '';
                                    $isDataOnlyRow = ($namaVal === '' && $jabVal === '');
                                @endphp
                                <tr class="border-b border-neutral-100 dark:border-neutral-800 hover:bg-blue-50/40 dark:hover:bg-blue-950/20 transition-colors {{ $bgClass }} {{ $isDataOnlyRow ? 'opacity-70' : '' }}">
                                    @foreach($block['headers'] as $colIdx => $hdr)
                                        @php
                                            $val    = $row[$colIdx] ?? '';
                                            $hdrUp  = strtoupper(trim($hdr ?? ''));
                                            $isNo   = $hdrUp === 'NO' || $hdrUp === 'NO.';
                                            $isNama = strpos($hdrUp, 'NAMA') === 0;
                                            $isJab  = strpos($hdrUp, 'JABATAN') !== false || strpos($hdrUp, 'NAMA OPERATOR') !== false;
                                            $isNum  = !$isNo && !$isNama && !$isJab;

                                            // Format angka (JUMLAH PERKARA = count, bukan currency)
                                            $isCount = str_contains(strtoupper($hdrUp), 'JUMLAH PERKARA');
                                            if ($isNum && !$isCount && $val !== '' && $val !== '-') {
                                                $stripped = str_replace([',', '.', ' '], '', preg_replace('/^Rp\s*/i', '', $val));
                                                if (is_numeric($stripped) && (float)$stripped != 0) {
                                                    $val = 'Rp ' . number_format((float)$stripped, 0, ',', '.');
                                                } elseif ($val === '0' || $val === 'Rp -' || $val === 'Rp 0') {
                                                    $val = 'Rp -';
                                                }
                                            }

                                            $tdAlign = $isNo ? 'text-center' : ($isNum ? 'text-right' : 'text-left');
                                            $tdFont  = ($isNo || $isNama) ? 'font-medium' : '';
                                        @endphp
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-neutral-800 dark:text-neutral-200 {{ $tdAlign }} {{ $tdFont }}">
                                            {{ $val }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach

                            {{-- Total row --}}
                            @if($block['totalRow'])
                                <tr class="bg-slate-100 dark:bg-slate-700/40 border-t-2 border-slate-400 dark:border-slate-500 font-bold">
                                    @foreach($block['headers'] as $colIdx => $hdr)
                                        @php
                                            $val   = $block['totalRow'][$colIdx] ?? '';
                                            $hdrUp = strtoupper(trim($hdr ?? ''));
                                            $isNo  = $hdrUp === 'NO' || $hdrUp === 'NO.';
                                            $isNama = strpos($hdrUp, 'NAMA') === 0;
                                            $isJab  = strpos($hdrUp, 'JABATAN') !== false;
                                            $isNum  = !$isNo && !$isNama && !$isJab;

                                            $isCount = str_contains(strtoupper($hdrUp), 'JUMLAH PERKARA');
                                            if ($isNum && !$isCount && $val !== '' && $val !== '-') {
                                                $stripped = str_replace([',', '.', ' '], '', preg_replace('/^Rp\s*/i', '', $val));
                                                if (is_numeric($stripped) && (float)$stripped != 0) {
                                                    $val = 'Rp ' . number_format((float)$stripped, 0, ',', '.');
                                                }
                                            }
                                            $tdAlign = $isNo ? 'text-center' : ($isNum ? 'text-right' : 'text-left');
                                        @endphp
                                        <td class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 text-neutral-900 dark:text-neutral-100 {{ $tdAlign }}">
                                            {{ $val }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- Footer / Tanda Tangan --}}
                <div class="px-6 py-5 border-t border-neutral-100 dark:border-neutral-800">
                    @php $footerInfo = $block['footerInfo']; @endphp

                    @if($footerInfo['date'])
                        <div class="flex justify-end mb-6">
                            <p class="text-xs text-neutral-600 dark:text-neutral-400 font-medium">{{ $footerInfo['date'] }}</p>
                        </div>
                    @endif

                    <div class="grid grid-cols-3 gap-6 text-xs">
                        {{-- Kiri: Petugas Pembuat Komitmen --}}
                        <div>
                            @if($footerInfo['left'])
                                @foreach(explode("\n", $footerInfo['left']) as $line)
                                    <p class="font-semibold uppercase text-neutral-700 dark:text-neutral-300 leading-relaxed">{{ $line }}</p>
                                @endforeach
                            @else
                                <p class="font-semibold uppercase text-neutral-700 dark:text-neutral-300">Petugas Pembuat Komitmen<br>Biaya Proses</p>
                            @endif
                            <div class="mt-14 border-t border-dashed border-neutral-400 dark:border-neutral-600 pt-2">
                                <p class="text-neutral-500">{{ $footerInfo['left_name'] ?? '' }}</p>
                            </div>
                        </div>

                        {{-- Tengah: Mengetahui --}}
                        <div class="text-center">
                            @if($footerInfo['center'])
                                @foreach(explode("\n", $footerInfo['center']) as $line)
                                    <p class="font-semibold uppercase text-neutral-700 dark:text-neutral-300 leading-relaxed">{{ $line }}</p>
                                @endforeach
                            @else
                                <p class="font-semibold uppercase text-neutral-700 dark:text-neutral-300">Mengetahui,<br>Kuasa Pengelola Biaya Proses</p>
                            @endif
                            <div class="mt-14 border-t border-dashed border-neutral-400 dark:border-neutral-600 pt-2">
                                <p class="text-neutral-500">{{ $footerInfo['center_name'] ?? '' }}</p>
                            </div>
                        </div>

                        {{-- Kanan: Bendahara --}}
                        <div class="text-right">
                            @if($footerInfo['right'])
                                @foreach(explode("\n", $footerInfo['right']) as $line)
                                    <p class="font-semibold uppercase text-neutral-700 dark:text-neutral-300 leading-relaxed">{{ $line }}</p>
                                @endforeach
                            @else
                                <p class="font-semibold uppercase text-neutral-700 dark:text-neutral-300">Bendahara Biaya Proses</p>
                            @endif
                            <div class="mt-14 border-t border-dashed border-neutral-400 dark:border-neutral-600 pt-2">
                                <p class="text-neutral-500">{{ $footerInfo['right_name'] ?? '' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            @endforeach

        </div>
        @endforeach

    @else
        {{-- ─── Empty State ─── --}}
        <div class="text-center py-24">
            <div class="w-20 h-20 mx-auto mb-6 bg-neutral-100 dark:bg-neutral-800 rounded-2xl flex items-center justify-center">
                <svg class="w-10 h-10 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            @if(!$error)
                <p class="text-neutral-500 dark:text-neutral-400 text-lg font-medium">Tidak ada data honorarium kamar</p>
                <p class="text-neutral-400 dark:text-neutral-500 text-sm mt-2">
                    Pastikan file Excel memiliki sheet Kepaniteraan, TIM, atau OP - STAF dengan data honorarium.
                </p>
            @endif
            <div class="flex gap-3 justify-center mt-6">
                <a href="{{ route('dashboard') }}"
                   class="inline-block px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 text-sm font-medium">
                    Upload File
                </a>
            </div>
        </div>
    @endif

</div>
@endsection

@section('scripts')
<script>
function honorariumApp() {
    return {
        activeSheet: 0,
        activeBlock: {},

        init() {
            // Inisialisasi activeBlock untuk setiap sheet → null = tampil semua
            @foreach($sheets as $si => $sheet)
            this.activeBlock[{{ $si }}] = null;
            @endforeach
        }
    };
}
</script>
@endsection
