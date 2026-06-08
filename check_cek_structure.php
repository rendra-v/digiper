<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$files = glob('storage/app/uploads/*.xls*');
usort($files, function ($a, $b) {
    return filemtime($b) - filemtime($a);
});

$latestFile = $files[0];
echo 'Using file: '.basename($latestFile)."\n";
echo str_repeat('=', 200)."\n";

$reader = IOFactory::createReaderForFile($latestFile);
$reader->setReadDataOnly(false);
$spreadsheet = $reader->load($latestFile);

echo "\n=== AVAILABLE SHEETS ===\n";
foreach ($spreadsheet->getSheetNames() as $name) {
    echo "- $name\n";
}

$ws = $spreadsheet->getSheetByName('cek');

echo "\n=== CEK SHEET STRUCTURE (ALL ROWS) ===\n";
echo str_repeat('-', 200)."\n";

$highestRow = $ws->getHighestRow();
$highestCol = $ws->getHighestColumn();

echo "Highest Row: $highestRow, Highest Column: $highestCol\n\n";

for ($r = 1; $r <= min($highestRow, 25); $r++) {
    echo "ROW $r: ";
    $rowValues = [];
    for ($col = 'A'; $col <= $highestCol; $col++) {
        $cell = $ws->getCell($col.$r);
        $v = $cell->getValue();

        if ($v !== null && $v !== '') {
            // Truncate long values
            $displayVal = strlen($v) > 30 ? substr($v, 0, 30).'...' : $v;
            $rowValues[] = "$col:$displayVal";
        }
    }

    if (empty($rowValues)) {
        echo '[EMPTY ROW]';
    } else {
        echo implode(' | ', $rowValues);
    }
    echo "\n";
}
