<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$file = App\Models\ExcelFile::latest()->first()->file_path;
$allSheets = [];
$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load(storage_path('app/' . $file));
$allSheetNames = $spreadsheet->getSheetNames();

$excludedSheets = [
    'Data Print', 'Data Print copy', 'Data Print Copy',
    'cek', 'Cek',
    'Rekap Keseluruhan', 'REKAP GABUNGAN', 'rekap keseluruhan',
    'Periode Laporan',
    'Sheet1', 'Sheet2', 'Sheet3', 'Sheet4',
    'list baru', 'List Baru',
    'Print_Amplop', 'print_amplop',
    'PEMBAGIAN PER-ANMUD', 'Pembagian Per-Anmud',
    'Rekap Khusus PDT',
];

$knownHonorNames = ['op - staf', 'op-staf', 'opstaf', 'tim', 'kepaniteraan',
    'rekap-kep', 'rekap-panmud', 'rekap kep', 'rekap panmud',
    'kma', 'panitera', 'all panmud', 'allpanmud', 'rekap kma',
    'pemilah', 'rekap pemilah'];

// Cari honor sheets
$honorSheets = array_values(array_filter($allSheetNames, function ($name) {
    $lower = strtolower($name);
    return str_contains($lower, 'honor') || str_contains($lower, 'honorarium');
}));

if (empty($honorSheets)) {
    $honorSheets = array_values(array_filter($allSheetNames, function ($n) use ($excludedSheets, $knownHonorNames) {
        if (in_array($n, $excludedSheets)) return false;
        $lower = strtolower(trim($n));
        foreach ($knownHonorNames as $known) {
            if (str_contains($lower, $known)) return true;
        }
        return false;
    }));
    if (empty($honorSheets)) {
        $honorSheets = array_values(array_filter($allSheetNames, fn ($n) => !in_array($n, $excludedSheets)));
    }
}

echo "HONOR SHEETS SELECTED (" . count($honorSheets) . "):\n";
foreach ($honorSheets as $sn) {
    echo "  - $sn\n";
}

echo "\nEXCLUDED SHEETS:\n";
foreach ($allSheetNames as $sn) {
    if (!in_array($sn, $honorSheets)) {
        echo "  - $sn\n";
    }
}
