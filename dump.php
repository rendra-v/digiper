<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$files = glob(__DIR__ . '/storage/app/uploads/*.xls*');
if(empty($files)) { echo "No excel files found\n"; exit; }
$file = $files[count($files)-1];
echo "Reading: " . basename($file) . "\n";
$spreadsheet = IOFactory::load($file);
$sheetNames = $spreadsheet->getSheetNames();
echo "Sheets: " . implode(", ", $sheetNames) . "\n";

$targetSheets = ["OP - STAF", "TIM", "Kepanitraan", "Rekap & Tim", "Rekap-Panmud", "Pemilah", "ALL Panmud"];
foreach($targetSheets as $sn) {
    $sheet = $spreadsheet->getSheetByName($sn) ?? $spreadsheet->getSheetByName($sn . ' ');
    if(!$sheet) {
        foreach($sheetNames as $actual) {
            if(str_contains(strtolower($actual), strtolower($sn))) {
                $sheet = $spreadsheet->getSheetByName($actual);
                break;
            }
        }
    }
    
    if(!$sheet) { echo "Sheet $sn not found\n"; continue; }
    echo "\n=== DUMPING SHEET: " . $sheet->getTitle() . " ===\n";
    for ($r = 1; $r <= 20; $r++) {
        $row = [];
        for ($c = 1; $c <= 10; $c++) {
            $ref = Coordinate::stringFromColumnIndex($c) . $r;
            $val = trim((string)$sheet->getCell($ref)->getFormattedValue());
            $row[] = $val === '' ? '[-]' : '['.substr($val, 0, 30).']';
        }
        echo "R" . $r . ": " . implode(" | ", $row) . "\n";
    }
}
