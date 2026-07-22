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

            @if($sheet['sheetName'] === 'TIM' && !empty($timData))
                {{-- TIM: tampilkan computed blocks dari Data Print --}}
                @foreach($timData as $block)
                <div class="mb-8 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl shadow-sm overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/30 dark:to-indigo-950/30 border-b border-blue-100 dark:border-blue-900/50 px-6 py-5 text-center">
                        <p class="text-sm font-bold uppercase tracking-wide text-neutral-900 dark:text-neutral-100">
                            HONORARIUM BIAYA PENYELESAIAN PERKARA {{ $block['label'] }}
                        </p>
                        <p class="text-sm font-bold text-blue-700 dark:text-blue-400 mt-1.5">
                            Sebanyak {{ number_format($block['jumlah_perkara'], 0, ',', '.') }} Perkara
                        </p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-100 dark:bg-slate-800/60 border-b-2 border-slate-300 dark:border-slate-600">
                                    <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center w-8">NO</th>
                                    <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-left min-w-[160px]">NAMA</th>
                                    <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-left min-w-[180px]">JABATAN</th>
                                    <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[90px]">JUMLAH PERKARA</th>
                                    <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-right min-w-[90px]">BIAYA</th>
                                    <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-right min-w-[100px]">JUMLAH BIAYA</th>
                                    <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-right min-w-[90px]">PPH 15%</th>
                                    <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-right min-w-[90px]">PPH 5%</th>
                                    <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-right min-w-[100px]">NETTO</th>
                                    <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[100px]">TANDA TANGAN</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $rowNum = 0; @endphp
                                @foreach($block['rows'] as $row)
                                    @php $rowNum++; $bg = $rowNum % 2 === 0 ? 'bg-slate-50/50 dark:bg-slate-800/20' : ''; @endphp
                                    <tr class="border-b border-neutral-100 dark:border-neutral-800 hover:bg-blue-50/40 dark:hover:bg-blue-950/20 transition-colors {{ $bg }}">
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center text-neutral-800 dark:text-neutral-200">{{ $row['no'] }}</td>
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-neutral-800 dark:text-neutral-200 font-medium">{{ $row['nama'] }}</td>
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-neutral-700 dark:text-neutral-300">{{ $row['jabatan'] }}</td>
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center text-neutral-800 dark:text-neutral-200">{{ number_format($row['jumlah_perkara'], 0, ',', '.') }}</td>
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-right text-neutral-800 dark:text-neutral-200">Rp {{ number_format($row['biaya'], 0, ',', '.') }}</td>
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-right text-neutral-800 dark:text-neutral-200">Rp {{ number_format($row['jumlah_biaya'], 0, ',', '.') }}</td>
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-right text-neutral-800 dark:text-neutral-200">{{ $row['pph15'] > 0 ? 'Rp ' . number_format($row['pph15'], 0, ',', '.') : '-' }}</td>
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-right text-neutral-800 dark:text-neutral-200">{{ $row['pph5'] > 0 ? 'Rp ' . number_format($row['pph5'], 0, ',', '.') : '-' }}</td>
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-right text-neutral-800 dark:text-neutral-200">Rp {{ number_format($row['netto'], 0, ',', '.') }}</td>
                                        <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2"></td>
                                    </tr>
                                @endforeach
                                <tr class="bg-slate-100 dark:bg-slate-700/40 border-t-2 border-slate-400 dark:border-slate-500 font-bold">
                                    <td colspan="5" class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 text-neutral-900 dark:text-neutral-100 text-right">JUMLAH</td>
                                    <td class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 text-right text-neutral-900 dark:text-neutral-100">Rp {{ number_format($block['total']['jumlah_biaya'], 0, ',', '.') }}</td>
                                    <td class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 text-right text-neutral-900 dark:text-neutral-100">{{ $block['total']['pph15'] > 0 ? 'Rp ' . number_format($block['total']['pph15'], 0, ',', '.') : '-' }}</td>
                                    <td class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 text-right text-neutral-900 dark:text-neutral-100">{{ $block['total']['pph5'] > 0 ? 'Rp ' . number_format($block['total']['pph5'], 0, ',', '.') : '-' }}</td>
                                    <td class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 text-right text-neutral-900 dark:text-neutral-100">Rp {{ number_format($block['total']['netto'], 0, ',', '.') }}</td>
                                    <td class="border border-slate-300 dark:border-slate-600 px-2 py-2.5"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                @endforeach

            @else
                {{-- Kepaniteraan / OP-STAF / TIM tanpa computed data: tampilkan Excel blocks --}}

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

                                    $namaVal = $row[2] ?? '';
                                    $jabVal  = $row[3] ?? '';

                                    // Skip baris yang nama kosong, jabatan ada tapi semua nilai numerik kosong
                                    // (artefak merged-cell Excel — hanya menampilkan jabatan berulang tanpa data)
                                    if ($namaVal === '') {
                                        $hasValue = false;
                                        foreach ($row as $idx => $v) {
                                            if ($idx <= 3) continue; // skip NO, NAMA, JABATAN
                                            $stripped = str_replace([',', '.', ' '], '', preg_replace('/^Rp\s*/i', '', (string)$v));
                                            if ($stripped !== '' && $stripped !== '0' && $stripped !== '-' && is_numeric($stripped) && (float)$stripped != 0) {
                                                $hasValue = true;
                                                break;
                                            }
                                        }
                                        if (!$hasValue) continue;
                                    }

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

            @endif {{-- @if($sheet['sheetName'] === 'TIM' && !empty($timData)) --}}

        </div>
        @endforeach

    @else
        {{-- ─── Fallback: tidak ada Excel → tampilkan computed TIM ─── --}}
        <div x-data="{ active: 1 }">
            <div class="flex gap-2 mb-6 flex-wrap">
                @php $staticTabs = ['Kepaniteraan', 'TIM', 'OP - STAF']; @endphp
                @foreach($staticTabs as $i => $label)
                    <button @click="active = {{ $i }}"
                            :class="active === {{ $i }}
                                ? 'bg-blue-600 text-white shadow-sm'
                                : 'bg-white dark:bg-neutral-900 text-neutral-700 dark:text-neutral-300 border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800'"
                            class="px-5 py-2.5 rounded-lg text-sm font-medium transition-all duration-200">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            {{-- TIM Tab --}}
            <div x-show="active === 1" x-cloak>
                @if(!empty($timData))
                    @foreach($timData as $block)
                    <div class="mb-8 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl shadow-sm overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/30 dark:to-indigo-950/30 border-b border-blue-100 dark:border-blue-900/50 px-6 py-5 text-center">
                            <p class="text-sm font-bold uppercase tracking-wide text-neutral-900 dark:text-neutral-100">
                                HONORARIUM BIAYA PENYELESAIAN PERKARA {{ $block['label'] }}
                            </p>
                            <p class="text-sm font-bold text-blue-700 dark:text-blue-400 mt-1.5">
                                Sebanyak {{ number_format($block['jumlah_perkara'], 0, ',', '.') }} Perkara
                            </p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs border-collapse">
                                <thead>
                                    <tr class="bg-slate-100 dark:bg-slate-800/60 border-b-2 border-slate-300 dark:border-slate-600">
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center w-8">NO</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-left min-w-[160px]">NAMA</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-left min-w-[180px]">JABATAN</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[90px]">JUMLAH PERKARA</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-right min-w-[90px]">BIAYA</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-right min-w-[100px]">JUMLAH BIAYA</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-right min-w-[90px]">PPH 15%</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-right min-w-[90px]">PPH 5%</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-right min-w-[100px]">NETTO</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-2 py-2.5 font-bold text-neutral-800 dark:text-neutral-200 text-center min-w-[100px]">TANDA TANGAN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $rowNum = 0; @endphp
                                    @foreach($block['rows'] as $row)
                                        @php $rowNum++; $bg = $rowNum % 2 === 0 ? 'bg-slate-50/50 dark:bg-slate-800/20' : ''; @endphp
                                        <tr class="border-b border-neutral-100 dark:border-neutral-800 hover:bg-blue-50/40 dark:hover:bg-blue-950/20 transition-colors {{ $bg }}">
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center">{{ $row['no'] }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 font-medium">{{ $row['nama'] }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2">{{ $row['jabatan'] }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-center">{{ number_format($row['jumlah_perkara'], 0, ',', '.') }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-right">Rp {{ number_format($row['biaya'], 0, ',', '.') }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-right">Rp {{ number_format($row['jumlah_biaya'], 0, ',', '.') }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-right">{{ $row['pph15'] > 0 ? 'Rp ' . number_format($row['pph15'], 0, ',', '.') : '-' }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-right">{{ $row['pph5'] > 0 ? 'Rp ' . number_format($row['pph5'], 0, ',', '.') : '-' }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2 text-right">Rp {{ number_format($row['netto'], 0, ',', '.') }}</td>
                                            <td class="border border-neutral-200 dark:border-neutral-700 px-2 py-2"></td>
                                        </tr>
                                    @endforeach
                                    <tr class="bg-slate-100 dark:bg-slate-700/40 border-t-2 border-slate-400 font-bold">
                                        <td colspan="5" class="border border-slate-300 px-2 py-2.5 text-right">JUMLAH</td>
                                        <td class="border border-slate-300 px-2 py-2.5 text-right">Rp {{ number_format($block['total']['jumlah_biaya'], 0, ',', '.') }}</td>
                                        <td class="border border-slate-300 px-2 py-2.5 text-right">{{ $block['total']['pph15'] > 0 ? 'Rp ' . number_format($block['total']['pph15'], 0, ',', '.') : '-' }}</td>
                                        <td class="border border-slate-300 px-2 py-2.5 text-right">{{ $block['total']['pph5'] > 0 ? 'Rp ' . number_format($block['total']['pph5'], 0, ',', '.') : '-' }}</td>
                                        <td class="border border-slate-300 px-2 py-2.5 text-right">Rp {{ number_format($block['total']['netto'], 0, ',', '.') }}</td>
                                        <td class="border border-slate-300 px-2 py-2.5"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endforeach
                @else
                    <p class="text-sm text-neutral-500 text-center py-8">Tidak ada data honorarium TIM.</p>
                @endif
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
