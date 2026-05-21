<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Perkara - Digiper</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 py-6">
                <div class="flex items-center gap-4">
                    <a href="{{ route('perkaras.index') }}" class="text-blue-600 hover:text-blue-900">← Kembali</a>
                    <h1 class="text-3xl font-bold text-gray-900">Detail Perkara</h1>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-4xl mx-auto px-4 py-8">
            <div class="bg-white rounded-lg shadow p-8">
                <div class="grid grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div>
                        <div class="mb-6">
                            <h3 class="text-gray-600 text-sm font-medium mb-1">No. Registrasi</h3>
                            <p class="text-lg font-semibold">{{ $perkara->no_registrasi }}</p>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-gray-600 text-sm font-medium mb-1">Tanggal Perkara Masuk</h3>
                            <p class="text-lg">{{ $perkara->tanggal_perkara_masuk->format('d F Y') }}</p>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-gray-600 text-sm font-medium mb-1">Kamar</h3>
                            <p class="text-lg">{{ $perkara->kamar }}</p>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-gray-600 text-sm font-medium mb-1">Pihak 1</h3>
                            <p class="text-lg">{{ $perkara->nama_p1 ?? '-' }}</p>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-gray-600 text-sm font-medium mb-1">Pihak 2</h3>
                            <p class="text-lg">{{ $perkara->nama_p2 ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div>
                        <div class="mb-6">
                            <h3 class="text-gray-600 text-sm font-medium mb-1">Pihak 3</h3>
                            <p class="text-lg">{{ $perkara->nama_p3 ?? '-' }}</p>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-gray-600 text-sm font-medium mb-1">Pihak 4</h3>
                            <p class="text-lg">{{ $perkara->nama_p4 ?? '-' }}</p>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-gray-600 text-sm font-medium mb-1">Pihak 5</h3>
                            <p class="text-lg">{{ $perkara->nama_p5 ?? '-' }}</p>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-gray-600 text-sm font-medium mb-1">Tanggal Putus</h3>
                            <p class="text-lg">
                                @if ($perkara->tanggal_putus)
                                    {{ $perkara->tanggal_putus->format('d F Y') }}
                                @else
                                    <span class="text-yellow-600">Belum Putus</span>
                                @endif
                            </p>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-gray-600 text-sm font-medium mb-1">Usia Perkara</h3>
                            <p class="text-lg">
                                @if ($usia = $perkara->getUsiaPerkara())
                                    {{ $usia }} hari
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Full Width Section -->
                <div class="mt-8 pt-8 border-t">
                    <div class="mb-6">
                        <h3 class="text-gray-600 text-sm font-medium mb-2">Amar Putusan</h3>
                        <p class="text-gray-800 whitespace-pre-wrap">{{ $perkara->amar ?? '-' }}</p>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-gray-600 text-sm font-medium mb-1">Biaya</h3>
                        <p class="text-lg">
                            @if ($perkara->biaya)
                                Rp {{ number_format($perkara->biaya, 2, ',', '.') }}
                            @else
                                -
                            @endif
                        </p>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-gray-600 text-sm font-medium mb-1">Nama Panteraan Pengakhiri</h3>
                        <p class="text-lg">{{ $perkara->nama_panteraan_pengakhiri ?? '-' }}</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-8 pt-8 border-t flex gap-3">
                    <a href="{{ route('perkaras.index') }}" class="px-6 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">
                        Kembali ke List
                    </a>
                    <form method="POST" action="{{ route('perkaras.destroy', $perkara) }}" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                            onclick="return confirm('Yakin ingin menghapus perkara ini?')">
                            Hapus Perkara
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
