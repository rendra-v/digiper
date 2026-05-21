<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require 'vendor/autoload.php';

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setCellValue('A1', 'Nomor');
$sheet->setCellValue('B1', 'Nama');
$sheet->setCellValue('C1', 'Deskripsi');

$sheet->setCellValue('A2', '001');
$sheet->setCellValue('B2', 'Kasus A');
$sheet->setCellValue('C2', 'Deskripsi kasus A');

$sheet->setCellValue('A3', '002');
$sheet->setCellValue('B3', 'Kasus B');
$sheet->setCellValue('C3', 'Deskripsi kasus B');

$writer = new Xlsx($spreadsheet);
$writer->save('test_upload.xlsx');
echo "Test file created: test_upload.xlsx\n";
