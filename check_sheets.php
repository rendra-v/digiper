<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$file = App\Models\ExcelFile::latest()->first()->file_path;
$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load(storage_path('app/' . $file));

echo "=== Sheet List ===\n";
foreach ($spreadsheet->getSheetNames() as $sn) {
    $ws = $spreadsheet->getSheetByName($sn);
    echo "$sn - rows:" . $ws->getHighestRow() . "\n";
}
