<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$file = App\Models\ExcelFile::latest()->first()->file_path;
$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load(storage_path('app/' . $file));
$worksheet = $spreadsheet->getSheetByName('OP - STAF');
if (!$worksheet) $worksheet = $spreadsheet->getSheetByName('OP - STAF ');

$highestRow = $worksheet->getHighestRow();
$highestColumn = $worksheet->getHighestColumn();
$highestColIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

$headerRow = null;
for ($r = 1; $r <= min($highestRow, 30); $r++) { // I use 30 to see if it finds it at 28
    $rowHasNo = false;
    $rowHasNama = false;
    $rowText = '';

    for ($c = 1; $c <= min($highestColIdx, 20); $c++) {
        $ref = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c).$r;
        $val = trim((string) $worksheet->getCell($ref)->getFormattedValue());
        if ($val === '') continue;
        
        $upper = strtoupper($val);
        $rowText .= ' ['.$val.']';
        if ($upper === 'NO' || $upper === 'NO.' || $upper === 'NOMOR') $rowHasNo = true;
        if (str_contains($upper, 'NAMA')) $rowHasNama = true;
    }

    if ($rowHasNo && $rowHasNama) {
        $headerRow = $r;
        echo "FOUND HEADER AT ROW $r: $rowText\n";
    }
}
