<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$file = App\Models\ExcelFile::latest()->first()->file_path;
$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load(storage_path('app/' . $file));
$worksheet = $spreadsheet->getSheetByName('OP - STAF');
if (!$worksheet) $worksheet = $spreadsheet->getSheetByName('OP - STAF ');

echo "=== OP - STAF COLUMNS 1-10 ===\n";
for ($r = 1; $r <= 35; $r++) {
    $row = [];
    for ($c = 1; $c <= 10; $c++) {
        $ref = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c).$r;
        $val = trim((string) $worksheet->getCell($ref)->getFormattedValue());
        $row[] = $val === '' ? '[-]' : '['.substr($val, 0, 30).']';
    }
    echo "R$r: " . implode(" | ", $row) . "\n";
}
