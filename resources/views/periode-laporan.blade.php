@extends('layout')

@section('title', 'Periode Laporan')

@section('content')
<div class="min-h-screen">

    {{-- --- Header --- --}}
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="text-4xl font-semibold tracking-tight mb-3">Periode Laporan</h2>
            <p class="text-neutral-500 dark:text-neutral-400">
                Atur tanggal yang digunakan di semua halaman print
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('data-print') }}"
               class="px-4 py-2.5 text-sm bg-neutral-200 dark:bg-neutral-800 hover:bg-neutral-300 dark:hover:bg-neutral-700 text-neutral-900 dark:text-neutral-100 rounded-lg transition-colors duration-200">
                Kembali
            </a>
            <a href="{{ route('dashboard') }}"
               class="px-4 py-2.5 text-sm bg-neutral-200 dark:bg-neutral-800 hover:bg-neutral-300 dark:hover:bg-neutral-700 text-neutral-900 dark:text-neutral-100 rounded-lg transition-colors duration-200">
                Dashboard
            </a>
        </div>
    </div>

    {{-- --- Success --- --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/50 rounded-lg flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <p class="text-sm text-emerald-700 dark:text-emerald-400">{{ session('success') }}</p>
        </div>
    @endif

    @if(!$hasFile)
        <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-950/20 border border-yellow-200 dark:border-yellow-900/50 rounded-lg">
            <p class="text-sm text-yellow-700 dark:text-yellow-400">
                Belum ada file Excel yang diupload. Upload file terlebih dahulu agar tanggal bisa dibaca otomatis dari sheet "Periode Laporan".
            </p>
        </div>
    @endif

    <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-900/50 rounded-lg">
        <p class="text-sm text-blue-700 dark:text-blue-400">
            Tanggal dibaca otomatis dari sheet <strong>"Periode Laporan"</strong> saat upload Excel.
            Anda bisa mengubahnya di sini tanpa perlu re-upload file.
            Perubahan langsung berlaku di semua halaman print.
        </p>
    </div>

    {{-- --- Form --- --}}
    <form method="POST" action="{{ route('periode-laporan.update') }}">
        @csrf
        <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl shadow-sm overflow-hidden mb-8">

            <div class="bg-emerald-600 px-6 py-4">
                <h3 class="text-white font-bold text-sm uppercase tracking-wide">Pengaturan Periode Laporan</h3>
            </div>

            <div class="divide-y divide-neutral-100 dark:divide-neutral-800">

                <div class="flex items-center px-6 py-5 gap-6 bg-emerald-50/30 dark:bg-emerald-950/10">
                    <div class="w-64 shrink-0">
                        <div class="flex items-center gap-2">
                            <label for="laporan_periode" class="block text-sm font-bold text-neutral-900 dark:text-neutral-100 uppercase tracking-wide">Laporan Periode</label>
                            @if(!empty($laporan_periode))
                                <span class="px-2 py-0.5 text-[10px] font-bold bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 rounded">Terisi</span>
                            @endif
                        </div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Contoh: DESEMBER 2025 S/D FEBRUARI 2026</p>
                    </div>
                    <span class="text-neutral-400 font-bold shrink-0">:</span>
                    <input type="text" id="laporan_periode" name="laporan_periode"
                           value="{{ old('laporan_periode', $laporan_periode) }}"
                           placeholder="DESEMBER 2025 S/D FEBRUARI 2026"
                           class="flex-1 px-4 py-3 rounded-lg border border-emerald-400 dark:border-emerald-700 bg-white dark:bg-neutral-950 text-base font-bold text-emerald-900 dark:text-emerald-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 shadow-sm transition-colors">
                </div>

                <div class="flex items-center px-6 py-5 gap-6">
                    <div class="w-64 shrink-0">
                        <div class="flex items-center gap-2">
                            <label for="tgl_data_laporan" class="block text-sm font-bold text-neutral-900 dark:text-neutral-100 uppercase tracking-wide">Tanggal Data Laporan</label>
                            @if(!empty($tgl_data_laporan))
                                <span class="px-2 py-0.5 text-[10px] font-bold bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 rounded">Terisi</span>
                            @endif
                        </div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Digunakan di print Data Laporan</p>
                    </div>
                    <span class="text-neutral-400 font-bold shrink-0">:</span>
                    <input type="text" id="tgl_data_laporan" name="tgl_data_laporan"
                           value="{{ old('tgl_data_laporan', $tgl_data_laporan) }}"
                           placeholder="Jakarta, 02 Maret 2026"
                           class="flex-1 px-4 py-3 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-950 text-base font-bold text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 shadow-sm transition-colors">
                </div>

                <div class="flex items-center px-6 py-5 gap-6 bg-emerald-50/40 dark:bg-emerald-950/20">
                    <div class="w-64 shrink-0">
                        <div class="flex items-center gap-2">
                            <label for="tgl_rekap_keseluruhan" class="block text-sm font-bold text-neutral-900 dark:text-neutral-100 uppercase tracking-wide">Tanggal Rekap Keseluruhan</label>
                            @if(!empty($tgl_rekap_keseluruhan))
                                <span class="px-2 py-0.5 text-[10px] font-bold bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 rounded">Terisi</span>
                            @endif
                        </div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Digunakan di print Rekap Keseluruhan 1, 2, 3</p>
                    </div>
                    <span class="text-neutral-400 font-bold shrink-0">:</span>
                    <input type="text" id="tgl_rekap_keseluruhan" name="tgl_rekap_keseluruhan"
                           value="{{ old('tgl_rekap_keseluruhan', $tgl_rekap_keseluruhan) }}"
                           placeholder="Jakarta, 05 Maret 2026"
                           class="flex-1 px-4 py-3 rounded-lg border border-emerald-400 dark:border-emerald-700 bg-white dark:bg-neutral-950 text-base font-bold text-emerald-900 dark:text-emerald-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 shadow-sm transition-colors">
                </div>

                <div class="flex items-center px-6 py-5 gap-6">
                    <div class="w-64 shrink-0">
                        <div class="flex items-center gap-2">
                            <label for="tgl_kwitansi" class="block text-sm font-bold text-neutral-900 dark:text-neutral-100 uppercase tracking-wide">Tanggal Kwitansi</label>
                            @if(!empty($tgl_kwitansi))
                                <span class="px-2 py-0.5 text-[10px] font-bold bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 rounded">Terisi</span>
                            @endif
                        </div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Digunakan di print Honorarium</p>
                    </div>
                    <span class="text-neutral-400 font-bold shrink-0">:</span>
                    <input type="text" id="tgl_kwitansi" name="tgl_kwitansi"
                           value="{{ old('tgl_kwitansi', $tgl_kwitansi) }}"
                           placeholder="Jakarta, 05 Maret 2026"
                           class="flex-1 px-4 py-3 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-950 text-base font-bold text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 shadow-sm transition-colors">
                </div>

            </div>

            <div class="px-6 py-4 bg-neutral-50 dark:bg-neutral-900/50 border-t border-neutral-100 dark:border-neutral-800 flex items-center justify-between">
                <p class="text-xs text-neutral-400">Perubahan berlaku langsung di semua halaman print setelah disimpan.</p>
                <button type="submit"
                        class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors duration-200 shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>

    @if($hasFile)
    <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl shadow-sm p-6">
        <h3 class="text-sm font-bold text-neutral-700 dark:text-neutral-300 mb-4 uppercase tracking-wide">Buka Halaman Print</h3>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('rekap-keseluruhan.print') }}" target="_blank"
               class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                Rekap Keseluruhan
            </a>
            <a href="{{ route('rekap-keseluruhan-2.print') }}" target="_blank"
               class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                Rekap Keseluruhan 2
            </a>
            <a href="{{ route('rekap-keseluruhan-3.print') }}" target="_blank"
               class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                Rekap Keseluruhan 3
            </a>
            <a href="{{ route('honorarium.print') }}" target="_blank"
               class="px-4 py-2 text-sm bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors">
                Honorarium Print
            </a>
        </div>
    </div>
    @endif

</div>
@endsection
