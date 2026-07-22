<?php
error_reporting(0);
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$path = 'public/REKAP KESELURUHAN PERKARA PUTUS BULAN DESEMBER 2025 SD FEBRUARI 2026.xls';
$spreadsheet = IOFactory::load($path);

$out = fopen('xls_output.txt', 'w');

foreach ($spreadsheet->getSheetNames() as $i => $name) {
    fwrite($out, "=== SHEET $i: $name ===\n");
    $sheet = $spreadsheet->getSheet($i);
    $data = $sheet->toArray(null, true, true, true);
    foreach ($data as $row) {
        $parts = [];
        foreach ($row as $cell) {
            $v = (string)($cell ?? '');
            if (trim($v) !== '') $parts[] = trim($v);
        }
        if ($parts) fwrite($out, implode(' | ', $parts) . "\n");
    }
    fwrite($out, "\n");
}

fclose($out);
echo "DONE. Output: xls_output.txt\n";
