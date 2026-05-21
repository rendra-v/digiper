<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recap Perkara - Digiper</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 py-6">
                <div class="flex items-center gap-4">
                    <a href="{{ route('perkaras.index') }}" class="text-blue-600 hover:text-blue-900">← Kembali</a>
                    <h1 class="text-3xl font-bold text-gray-900">Recap Perkara</h1>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kamar</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Diputus</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Prodeo</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">0-400rb</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">400rb-2jt</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">2jt+</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Biaya</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($recapData as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $row['kamar'] }}</td>
                                <td class="px-6 py-4 text-sm text-right text-gray-600">{{ $row['diputus'] }}</td>
                                <td class="px-6 py-4 text-sm text-right text-gray-600">{{ $row['prodeo'] }}</td>
                                <td class="px-6 py-4 text-sm text-right text-gray-600">{{ $row['klasifikasi_0_400'] }}</td>
                                <td class="px-6 py-4 text-sm text-right text-gray-600">{{ $row['klasifikasi_400_2jt'] }}</td>
                                <td class="px-6 py-4 text-sm text-right text-gray-600">{{ $row['klasifikasi_2jt_plus'] }}</td>
                                <td class="px-6 py-4 text-sm text-right text-gray-600">
                                    Rp {{ number_format($row['total_biaya'], 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-right text-gray-600">{{ $row['jumlah'] }}</td>
                            </tr>
                        @endforeach

                        <!-- Grand Total Row -->
                        <tr class="bg-gray-100 font-semibold">
                            <td class="px-6 py-4 text-sm">TOTAL</td>
                            <td class="px-6 py-4 text-sm text-right">{{ $grandTotal['diputus'] }}</td>
                            <td class="px-6 py-4 text-sm text-right">{{ $grandTotal['prodeo'] }}</td>
                            <td class="px-6 py-4 text-sm text-right">{{ $grandTotal['klasifikasi_0_400'] }}</td>
                            <td class="px-6 py-4 text-sm text-right">{{ $grandTotal['klasifikasi_400_2jt'] }}</td>
                            <td class="px-6 py-4 text-sm text-right">{{ $grandTotal['klasifikasi_2jt_plus'] }}</td>
                            <td class="px-6 py-4 text-sm text-right">
                                Rp {{ number_format($grandTotal['total_biaya'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-right">{{ $grandTotal['jumlah'] }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Back Button -->
            <div class="mt-6">
                <a href="{{ route('perkaras.index') }}" class="px-6 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">
                    Kembali
                </a>
            </div>
        </div>
    </div>
</body>
</html>
