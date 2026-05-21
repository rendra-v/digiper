<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Perkara - Digiper</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 py-6">
                <div class="flex items-center gap-4">
                    <a href="{{ route('perkaras.index') }}" class="text-blue-600 hover:text-blue-900">← Kembali</a>
                    <h1 class="text-3xl font-bold text-gray-900">Tambah Perkara</h1>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-3xl mx-auto px-4 py-8">
            <div class="bg-white rounded-lg shadow p-6">
                <!-- Upload Form -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">Import dari Excel</h2>
                    <form action="{{ route('perkaras.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Pilih file Excel (.xlsx, .xls)
                            </label>
                            <input type="file" name="file" accept=".xlsx,.xls" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            @error('file')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Upload dan Import
                        </button>
                    </form>
                </div>

                <hr class="my-8">

                <!-- Manual Entry Form -->
                <div>
                    <h2 class="text-xl font-semibold mb-4">Atau Input Manual</h2>
                    <form action="{{ route('perkaras.store') }}" method="POST">
                        @csrf
                        @method('POST')

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">No. Registrasi *</label>
                                <input type="text" name="no_registrasi" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    value="{{ old('no_registrasi') }}">
                                @error('no_registrasi')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Perkara Masuk *</label>
                                <input type="date" name="tanggal_perkara_masuk" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    value="{{ old('tanggal_perkara_masuk') }}">
                                @error('tanggal_perkara_masuk')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kamar *</label>
                                <input type="text" name="kamar" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    value="{{ old('kamar') }}">
                                @error('kamar')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pihak 1</label>
                                <input type="text" name="nama_p1"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    value="{{ old('nama_p1') }}">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pihak 2</label>
                                <input type="text" name="nama_p2"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    value="{{ old('nama_p2') }}">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pihak 3</label>
                                <input type="text" name="nama_p3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    value="{{ old('nama_p3') }}">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Putus</label>
                                <input type="date" name="tanggal_putus"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    value="{{ old('tanggal_putus') }}">
                            </div>

                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Amar Putusan</label>
                                <textarea name="amar" rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('amar') }}</textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Biaya (Rp)</label>
                                <input type="number" name="biaya" step="0.01"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    value="{{ old('biaya') }}">
                            </div>
                        </div>

                        <div class="mt-6 flex gap-3">
                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                Simpan Perkara
                            </button>
                            <a href="{{ route('perkaras.index') }}" class="px-6 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
