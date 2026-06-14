<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

ini_set('memory_limit', '1024M');
ini_set('max_execution_time', '300');

$filePath = 'public/sample.xls';
$reader = IOFactory::createReaderForFile($filePath);
$reader->setReadDataOnly(false);
$spreadsheet = $reader->load($filePath);

$sheet = $spreadsheet->getSheetByName('Rekap Keseluruhan');

$out = '';

// Tabel kanan atas: kolom Q(17) sampai AY(51), baris 4 sampai 38
$startCol = 17; // Q
$endCol = 51;   // AY
$startRow = 4;
$endRow = 38;

$out .= "=== TABEL KANAN ATAS (Q4:AY38) ===\n\n";

// Print setiap baris
for ($row = $startRow; $row <= $endRow; $row++) {
    $rowData = [];
    for ($colIdx = $startCol; $colIdx <= $endCol; $colIdx++) {
        $col = Coordinate::stringFromColumnIndex($colIdx);
        $cell = $sheet->getCell($col . $row);
        $val = $cell->getFormattedValue();
        if ($val !== '' && $val !== null) {
            $rowData[$col] = trim($val);
        }
    }
    if (!empty($rowData)) {
        $out .= "Row $row: " . json_encode($rowData, JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        $out .= "Row $row: (empty)\n";
    }
}

$out .= "\n=== MERGED CELLS UNTUK AREA KANAN (Q-AY) ===\n";
foreach ($sheet->getMergeCells() as $range) {
    // Cek apakah merge ada di area kanan
    preg_match('/^([A-Z]+)(\d+):([A-Z]+)(\d+)$/', $range, $m);
    if (!$m) continue;
    $sc = Coordinate::columnIndexFromString($m[1]);
    $sr = (int)$m[2];
    $ec = Coordinate::columnIndexFromString($m[3]);
    $er = (int)$m[4];
    // Hanya yang overlap dengan area Q(17)-AY(51), row 4-40
    if ($ec >= 17 && $sc <= 51 && $er >= 4 && $sr <= 40) {
        $out .= "  $range (cols $sc-$ec, rows $sr-$er, colspan=" . ($ec-$sc+1) . ", rowspan=" . ($er-$sr+1) . ")\n";
    }
}

$out .= "\n=== RAW VALUES (tidak formatted) ===\n";
for ($row = $startRow; $row <= $endRow; $row++) {
    $rowData = [];
    for ($colIdx = $startCol; $colIdx <= $endCol; $colIdx++) {
        $col = Coordinate::stringFromColumnIndex($colIdx);
        $cell = $sheet->getCell($col . $row);
        $val = $cell->getValue();
        if ($val !== '' && $val !== null) {
            $rowData[$col] = $val;
        }
    }
    if (!empty($rowData)) {
        $out .= "Row $row raw: " . json_encode($rowData, JSON_UNESCAPED_UNICODE) . "\n";
    }
}

file_put_contents('analyze_right_table.txt', $out);
echo "Done! Check analyze_right_table.txt\n";
