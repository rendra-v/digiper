<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$file = App\Models\ExcelFile::latest()->first()->file_path;
$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load(storage_path('app/' . $file));
$ws = $spreadsheet->getSheetByName('Data Print copy');

$r = 83;
echo "ROW $r:\n";
for ($c=1; $c<=20; $c++) {
    $val = trim((string) $ws->getCell([$c, $r])->getFormattedValue());
    echo "Col $c: '$val'\n";
}
