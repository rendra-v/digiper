<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$file = App\Models\ExcelFile::latest()->first()->file_path;
$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load(storage_path('app/' . $file));

$ws = $spreadsheet->getSheetByName('Data Print copy');
if (!$ws) { echo "Sheet not found\n"; exit; }

// Let's print rows 50-85 to see if headers repeat and data is above it
for ($r = 70; $r <= 85; $r++) {
    $rowText = '';
    for ($c = 1; $c <= 10; $c++) {
        $val = trim((string) $ws->getCell([$c, $r])->getFormattedValue());
        $rowText .= '[' . $val . '] ';
    }
    echo "Row $r: $rowText\n";
}
