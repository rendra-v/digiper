<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$file = public_path('sample.xls');
$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($file);
$ws = $spreadsheet->getSheetByName('OP - STAF');

for ($r = 20; $r <= 70; $r++) {
    $rowVals = [];
    for ($c = 1; $c <= 9; $c++) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
        $rowVals[] = $ws->getCell($colLetter . $r)->getFormattedValue();
    }
    echo "Row $r: " . implode(" | ", $rowVals) . "\n";
}
