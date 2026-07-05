<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$file = App\Models\ExcelFile::latest()->first()->file_path;
$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load(storage_path('app/' . $file));

$ws = $spreadsheet->getSheetByName('Data Print copy');
if (!$ws) { echo "Sheet not found\n"; exit; }

$highestRow = $ws->getHighestRow();

$headerRows = [];
for ($r = 1; $r <= $highestRow; $r++) {
    $rowHasNo = false;
    $rowHasNama = false;
    for ($c = 1; $c <= 20; $c++) {
        $val = trim((string) $ws->getCell([$c, $r])->getFormattedValue());
        if ($val !== '') {
            $upper = strtoupper($val);
            if ($upper === 'NO' || $upper === 'NO.' || $upper === 'NOMOR') {
                $rowHasNo = true;
            }
            if (str_contains($upper, 'NAMA')) {
                $rowHasNama = true;
            }
        }
    }
    if ($rowHasNo && $rowHasNama) {
        $headerRows[] = $r;
    }
}

echo "Found " . count($headerRows) . " headers at rows: " . implode(', ', $headerRows) . "\n";

foreach (array_slice($headerRows, 0, 3) as $h) {
    echo "\nAround header at row $h:\n";
    for ($r = max(1, $h - 6); $r <= $h; $r++) {
        $c1 = trim((string) $ws->getCell([1, $r])->getFormattedValue());
        $text = '';
        for ($c=1; $c<=5; $c++) $text .= '['.trim((string) $ws->getCell([$c, $r])->getFormattedValue()).'] ';
        echo "Row $r (col1='$c1'): $text\n";
    }
}
