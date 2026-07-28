<?php
error_reporting(0);
ini_set('memory_limit', '1024M');
set_time_limit(300);
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
$file = 'REKAP KESELURUHAN PERKARA PUTUS BULAN DESEMBER 2025 SD FEBRUARI 2026.xls';
$reader = IOFactory::createReaderForFile($file);
$reader->setReadDataOnly(true);
if (method_exists($reader, 'setLoadSheetsOnly')) $reader->setLoadSheetsOnly(['OP - STAF']);
$spreadsheet = $reader->load($file);
$ws = $spreadsheet->getSheetByName('OP - STAF');

echo '--- Blok Agama Kasasi footer (920-944) ---' . PHP_EOL;
for ($r = 920; $r <= 944; $r++) {
    $parts = [];
    for ($c = 1; $c <= 9; $c++) {
        $v = trim((string)$ws->getCell([$c, $r])->getFormattedValue());
        if ($v !== '') $parts[] = "C{$c}=[{$v}]";
    }
    if ($parts) echo "Row {$r}: " . implode(' | ', $parts) . PHP_EOL;
}

echo PHP_EOL . '--- Blok Agama PK footer (985-1010) ---' . PHP_EOL;
for ($r = 985; $r <= 1010; $r++) {
    $parts = [];
    for ($c = 1; $c <= 9; $c++) {
        $v = trim((string)$ws->getCell([$c, $r])->getFormattedValue());
        if ($v !== '') $parts[] = "C{$c}=[{$v}]";
    }
    if ($parts) echo "Row {$r}: " . implode(' | ', $parts) . PHP_EOL;
}

$spreadsheet->disconnectWorksheets();
