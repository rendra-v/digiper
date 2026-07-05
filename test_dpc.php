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

$ws = $spreadsheet->getSheetByName('Data Print copy');
if (!$ws) { echo "Sheet not found\n"; exit; }

$result = $method->invoke($controller, $ws, 'Data Print copy');
if ($result === null) {
    echo "NULL (no table found)\n";
} else {
    $r = $result[0];
    echo "Rows: " . count($r['rows']) . "\n";
    echo "Headers: " . implode(" | ", array_values($r['headers'])) . "\n";
    echo "First row: " . json_encode(array_slice($r['rows'][0], 0, 4)) . "\n";
    echo "Section titles (unique): ";
    $sections = array_unique(array_column($r['rows'], '_section_title'));
    echo count($sections) . " unique sections\n";
    foreach (array_slice($sections, 0, 3) as $s) {
        echo "  - " . $s . "\n";
    }
}
