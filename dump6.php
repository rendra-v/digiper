<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$file = App\Models\ExcelFile::latest()->first()->file_path;
$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load(storage_path('app/' . $file));
$worksheet = $spreadsheet->getSheetByName('OP - STAF');
if (!$worksheet) $worksheet = $spreadsheet->getSheetByName('OP - STAF ');

echo "=== OP - STAF COLUMNS 11-20 ===\n";
for ($r = 23; $r <= 30; $r++) {
    $row = [];
    $hasData = false;
    for ($c = 11; $c <= 20; $c++) {
        $ref = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c).$r;
        $val = trim((string) $worksheet->getCell($ref)->getFormattedValue());
        if ($val !== '') $hasData = true;
        $row[] = $val === '' ? '[-]' : '['.substr($val, 0, 30).']';
    }
    if ($hasData) {
        echo "R$r: " . implode(" | ", $row) . "\n";
    }
}
