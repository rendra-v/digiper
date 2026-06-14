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

$sheetNames = $spreadsheet->getSheetNames();
file_put_contents('analyze_output.txt', 'Sheets: ' . implode(', ', $sheetNames) . "\n");

$sheet = null;
foreach ($sheetNames as $name) {
    if (stripos($name, 'rekap') !== false && stripos($name, 'keseluruhan') !== false) {
        $sheet = $spreadsheet->getSheetByName($name);
        file_put_contents('analyze_output.txt', 'Found sheet: ' . $name . "\n", FILE_APPEND);
        break;
    }
}

if (!$sheet) {
    $sheet = $spreadsheet->getActiveSheet();
    file_put_contents('analyze_output.txt', 'Using active sheet: ' . $sheet->getTitle() . "\n", FILE_APPEND);
}

$highestRow = $sheet->getHighestRow();
$highestCol = $sheet->getHighestColumn();
$highestColIdx = Coordinate::columnIndexFromString($highestCol);
file_put_contents('analyze_output.txt', "Highest row: $highestRow, Highest col: $highestCol (index: $highestColIdx)\n", FILE_APPEND);

// Print merged cells
file_put_contents('analyze_output.txt', "\nMERGED CELLS:\n", FILE_APPEND);
foreach ($sheet->getMergeCells() as $range) {
    file_put_contents('analyze_output.txt', "  $range\n", FILE_APPEND);
}

// Print all cell values rows 1-50
file_put_contents('analyze_output.txt', "\nCELL VALUES (rows 1-50):\n", FILE_APPEND);
for ($row = 1; $row <= 50; $row++) {
    $rowData = [];
    for ($colIdx = 1; $colIdx <= $highestColIdx; $colIdx++) {
        $col = Coordinate::stringFromColumnIndex($colIdx);
        $cell = $sheet->getCell($col . $row);
        $val = $cell->getFormattedValue();
        if ($val !== '' && $val !== null) {
            $rowData[$col] = $val;
        }
    }
    if (!empty($rowData)) {
        file_put_contents('analyze_output.txt', "Row $row: " . json_encode($rowData, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
    }
}

// Column widths
file_put_contents('analyze_output.txt', "\nCOLUMN WIDTHS:\n", FILE_APPEND);
for ($colIdx = 1; $colIdx <= $highestColIdx; $colIdx++) {
    $col = Coordinate::stringFromColumnIndex($colIdx);
    $width = $sheet->getColumnDimension($col)->getWidth();
    file_put_contents('analyze_output.txt', "  $col: $width\n", FILE_APPEND);
}

// Row heights
file_put_contents('analyze_output.txt', "\nROW HEIGHTS (rows 1-50):\n", FILE_APPEND);
for ($row = 1; $row <= 50; $row++) {
    $height = $sheet->getRowDimension($row)->getRowHeight();
    if ($height > 0) {
        file_put_contents('analyze_output.txt', "  Row $row: $height\n", FILE_APPEND);
    }
}

echo "Done! Check analyze_output.txt\n";
