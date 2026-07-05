<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$file = App\Models\ExcelFile::latest()->first()->file_path;
$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load(storage_path('app/' . $file));
$worksheet = $spreadsheet->getSheetByName('OP - STAF');
if (!$worksheet) $worksheet = $spreadsheet->getSheetByName('OP - STAF ');

$controller = app()->make(App\Http\Controllers\DashboardController::class);
$reflection = new ReflectionClass($controller);
$method = $reflection->getMethod('parseHonorariumSheet');
$method->setAccessible(true);
$result = $method->invoke($controller, $worksheet, 'OP - STAF');

if ($result === null) {
    echo "RESULT IS NULL\n";
} else {
    echo "NUMBER OF TABLES: " . count($result) . "\n\n";
    foreach ($result as $i => $table) {
        echo "TABLE " . ($i+1) . ":\n";
        echo "  Title: " . substr($table['title'], 0, 80) . "\n";
        echo "  Headers: " . implode(" | ", array_values($table['headers'])) . "\n";
        echo "  Rows: " . count($table['rows']) . "\n";
        echo "  First row: " . json_encode(array_slice($table['rows'][0], 0, 4)) . "\n";
        echo "\n";
    }
}
