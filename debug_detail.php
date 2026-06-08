<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;

$files = glob('storage/app/uploads/*.xls*');
usort($files, function ($a, $b) {
    return filemtime($b) - filemtime($a);
});

$latestFile = $files[0];
echo 'Using file: '.basename($latestFile)."\n";
echo str_repeat('=', 150)."\n";

$reader = IOFactory::createReaderForFile($latestFile);
$reader->setReadDataOnly(false); // Load formulas AND cached values
$spreadsheet = $reader->load($latestFile);
$ws = $spreadsheet->getSheetByName('cek');

echo "Detailed examination of rows with header and data:\n";
echo str_repeat('-', 150)."\n";

for ($r = 6; $r <= 13; $r++) {
    echo "\n=== ROW $r ===\n";
    for ($col = 'A'; $col <= 'Q'; $col++) {
        $cell = $ws->getCell($col.$r);
        $v = $cell->getValue();
        $dataType = $cell->getDataType();
        $calcVal = null;

        if ($dataType === DataType::TYPE_FORMULA) {
            try {
                $calcVal = $cell->getCalculatedValue();
            } catch (Exception $e) {
                $calcVal = 'ERR';
            }
        }

        if ($v !== null && $v !== '') {
            echo "$col: value=\"$v\" | type=$dataType | calculated=\"$calcVal\"\n";
        }
    }
}
