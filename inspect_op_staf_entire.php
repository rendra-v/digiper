<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$file = public_path('sample.xls');
$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($file);
$ws = $spreadsheet->getSheetByName('OP - STAF');
$highestRow = $ws->getHighestRow();

$names = [];
$amounts = [];

for ($r = 1; $r <= $highestRow; $r++) {
    $no = trim((string)$ws->getCell('A' . $r)->getFormattedValue());
    $name = trim((string)$ws->getCell('B' . $r)->getFormattedValue());
    $job = trim((string)$ws->getCell('C' . $r)->getFormattedValue());
    $perkara = trim((string)$ws->getCell('D' . $r)->getFormattedValue());
    $biaya = trim((string)$ws->getCell('E' . $r)->getFormattedValue());
    $jml_biaya = trim((string)$ws->getCell('F' . $r)->getFormattedValue());
    $pph = trim((string)$ws->getCell('G' . $r)->getFormattedValue());
    $netto = trim((string)$ws->getCell('H' . $r)->getFormattedValue());

    if ($name !== '' || $biaya !== '' || $perkara !== '') {
        echo "Row $r: No=[$no] Name=[$name] Job=[$job] Pkr=[$perkara] Biaya=[$biaya] Netto=[$netto]\n";
    }
}
