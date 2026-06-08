<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$files = glob('storage/app/uploads/*.xls*');
usort($files, function ($a, $b) {
    return filemtime($b) - filemtime($a);
});

$latestFile = $files[0];
echo 'Using file: '.basename($latestFile)."\n";
echo str_repeat('=', 100)."\n";

$reader = IOFactory::createReaderForFile($latestFile);
$reader->setReadDataOnly(true);
$spreadsheet = $reader->load($latestFile);
$ws = $spreadsheet->getSheetByName('cek');

echo "Examining first 15 rows to understand structure:\n";
echo str_repeat('-', 100)."\n";

for ($r = 1; $r <= 15; $r++) {
    echo "Row $r: ";
    $rowStr = '';
    $emptyCount = 0;
    for ($col = 'A'; $col <= 'P'; $col++) {
        $v = $ws->getCell($col.$r)->getValue();
        if ($v === null || $v === '') {
            $emptyCount++;
            $rowStr .= '[--] ';
        } else {
            $emptyCount = 0;
            $rowStr .= '['.$col.':'.$v.'] ';
        }
    }
    echo $rowStr.' (empty:'.$emptyCount.")\n";
}
