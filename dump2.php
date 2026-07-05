<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$files = glob(__DIR__ . '/storage/app/uploads/*.xls*');
$file = $files[count($files)-1];
$spreadsheet = IOFactory::load($file);

$targetSheets = ["OP - STAF"];
foreach($targetSheets as $sn) {
    $sheet = $spreadsheet->getSheetByName($sn) ?? $spreadsheet->getSheetByName($sn . ' ');
    if(!$sheet) continue;
    echo "=== DUMPING SHEET: " . $sheet->getTitle() . " ===\n";
    for ($r = 1; $r <= 5; $r++) {
        $row = [];
        for ($c = 1; $c <= 20; $c++) {
            $ref = Coordinate::stringFromColumnIndex($c) . $r;
            $val = trim((string)$sheet->getCell($ref)->getFormattedValue());
            if ($val !== '') echo "R$r C$c: $val\n";
        }
    }
}
