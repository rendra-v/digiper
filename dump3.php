<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$files = glob(__DIR__ . '/storage/app/uploads/*.xls*');
$file = $files[count($files)-1];
$spreadsheet = IOFactory::load($file);

$targetSheets = ["OP - STAF", "TIM", "Kepaniteraan", "Rekap & Tim", "Rekap-Panmud", "Pemilah", "ALL Panmud"];
foreach($targetSheets as $sn) {
    $sheet = $spreadsheet->getSheetByName($sn) ?? $spreadsheet->getSheetByName($sn . ' ');
    if(!$sheet) {
        foreach($spreadsheet->getSheetNames() as $actual) {
            if(str_contains(strtolower($actual), strtolower($sn))) {
                $sheet = $spreadsheet->getSheetByName($actual);
                break;
            }
        }
    }
    if(!$sheet) continue;
    echo "=== DUMPING SHEET: " . $sheet->getTitle() . " ===\n";
    for ($r = 1; $r <= 30; $r++) {
        $foundNo = false;
        $foundNama = false;
        $texts = [];
        for ($c = 1; $c <= 20; $c++) {
            $ref = Coordinate::stringFromColumnIndex($c) . $r;
            $val = trim((string)$sheet->getCell($ref)->getFormattedValue());
            if ($val !== '') {
                $texts[] = "C$c: $val";
                if(strtoupper($val) === 'NO' || strtoupper($val) === 'NO.' || strtoupper($val) === 'NOMOR') $foundNo = true;
                if(str_contains(strtoupper($val), 'NAMA')) $foundNama = true;
            }
        }
        if(!empty($texts)) echo "R$r: " . implode(" | ", $texts) . ($foundNo && $foundNama ? " [HEADER DETECTED]" : "") . "\n";
    }
}
