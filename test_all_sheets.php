<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$controller = app()->make(App\Http\Controllers\DashboardController::class);
$reflection = new ReflectionClass($controller);
$method = $reflection->getMethod('parseHonorariumSheet');
$method->setAccessible(true);

$file = App\Models\ExcelFile::latest()->first()->file_path;
$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load(storage_path('app/' . $file));

$testSheets = ['OP - STAF', 'TIM', 'Kepaniteraan', 'KMA', 'ALL PANMUD', 'Pemilah'];

foreach ($testSheets as $sn) {
    $ws = $spreadsheet->getSheetByName($sn);
    if (!$ws) { echo "$sn: NOT FOUND\n"; continue; }

    $result = $method->invoke($controller, $ws, $sn);
    if ($result === null) {
        echo "$sn: NULL (no honorarium table found)\n";
    } else {
        $r = $result[0];
        echo "$sn: OK - " . count($r['rows']) . " rows, headers: " . implode("|", array_values($r['headers'])) . "\n";
    }
}
