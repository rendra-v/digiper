<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Perkara - Digiper</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 py-6">
                <h1 class="text-3xl font-bold text-gray-900">Manajemen Perkara</h1>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 py-8">
            <!-- Alerts -->
            @if ($message = Session::get('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-green-800">{{ $message }}</p>
                </div>
            @endif

            @if ($message = Session::get('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-red-800">{{ $message }}</p>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="mb-6 flex gap-3">
                <a href="{{ route('perkaras.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    + Tambah Perkara
                </a>
                <a href="{{ route('perkaras.recap') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    📊 Lihat Recap
                </a>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                @if ($perkaras->count())
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Registrasi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Masuk</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kamar</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pihak 1</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Putus</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usia</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($perkaras as $perkara)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $perkara->no_registrasi }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $perkara->tanggal_perkara_masuk->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $perkara->kamar }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $perkara->nama_p1 ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        @if ($perkara->tanggal_putus)
                                            {{ $perkara->tanggal_putus->format('d/m/Y') }}
                                        @else
                                            <span class="text-yellow-600">Belum Putus</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        @if ($usia = $perkara->getUsiaPerkara())
                                            {{ $usia }} hari
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm space-x-2">
                                        <a href="{{ route('perkaras.show', $perkara) }}" class="text-blue-600 hover:text-blue-900">Lihat</a>
                                        <form method="POST" action="{{ route('perkaras.destroy', $perkara) }}" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Yakin?')">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $perkaras->links() }}
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <p class="text-gray-500 mb-4">Tidak ada data perkara</p>
                        <a href="{{ route('perkaras.create') }}" class="text-blue-600 hover:text-blue-900">Tambah perkara sekarang</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
