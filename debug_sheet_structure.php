<?php

use PhpOffice\PhpSpreadsheet\IOFactory;

require 'vendor/autoload.php';

$filePath = __DIR__.'/public/sample.xls';
if (! file_exists($filePath)) {
    // Try uploads
    $files = glob(__DIR__.'/storage/app/uploads/*sample*');
    if (! empty($files)) {
        $filePath = $files[0];
    }
}

echo "Using file: $filePath\n";
$spreadsheet = IOFactory::load($filePath);
$sheet = $spreadsheet->getSheetByName('cek');

echo "=== Checking Structure ===\n";
echo 'Row 1: ';
var_dump($sheet->getCell('B1')->getValue());
echo 'Row 2: ';
var_dump($sheet->getCell('B2')->getValue());
echo 'Row 3: ';
var_dump($sheet->getCell('A3')->getValue());
var_dump($sheet->getCell('B3')->getValue());
echo 'Row 4: ';
var_dump($sheet->getCell('A4')->getValue());
var_dump($sheet->getCell('B4')->getValue());

echo "\n=== Looking for 'NO' header ===\n";
for ($row = 1; $row <= 10; $row++) {
    $value = $sheet->getCell('A'.$row)->getValue();
    if (strpos($value, 'NO') !== false || $value === 'NO') {
        echo "Found 'NO' at row $row\n";
        break;
    }
}
