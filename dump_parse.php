<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$controller = app()->make(App\Http\Controllers\DashboardController::class);
$file = App\Models\ExcelFile::latest()->first()->file_path;
$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load(storage_path('app/' . $file));
$ws = $spreadsheet->getSheetByName('OP - STAF');
if (!$ws) $ws = $spreadsheet->getSheetByName('OP - STAF ');
if (!$ws) { echo "Sheet OP - STAF not found\n"; exit; }

$reflection = new ReflectionClass($controller);
$method = $reflection->getMethod('parseHonorariumSheet');
$method->setAccessible(true);
$parsed = $method->invoke($controller, $ws, 'OP - STAF');

if ($parsed === null) {
    echo "PARSED IS NULL\n";
} else {
    echo "HEADERS:\n";
    print_r($parsed['headers']);
    echo "TITLE: " . $parsed['title'] . "\n";
    echo "ROWS COUNT: " . count($parsed['rows']) . "\n";
}
