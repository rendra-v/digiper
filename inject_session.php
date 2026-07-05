<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Re-inject the latest file into a new browser-accessible session
// Find latest uploaded file
$file = App\Models\ExcelFile::latest()->first();
if (!$file) { echo "No file found\n"; exit; }

$fullPath = storage_path('app/' . $file->file_path);
$originalName = $file->original_name ?: basename($file->file_path);

echo "File: " . $fullPath . "\n";
echo "Name: " . $originalName . "\n";
echo "Exists: " . (file_exists($fullPath) ? 'YES' : 'NO') . "\n";

// Lookup what session IDs exist (in case user still has a cookie)
$sessions = DB::table('sessions')->get();
echo "\nCurrent sessions: " . $sessions->count() . "\n";

foreach ($sessions as $sess) {
    $payload = unserialize(base64_decode($sess->payload));
    echo "Session " . substr($sess->id, 0, 12) . "...: ";
    
    if (is_array($payload)) {
        // Update the excel file path in this session
        $payload['excel_file_path'] = $fullPath;
        $payload['excel_file_name'] = $originalName;
        
        DB::table('sessions')->where('id', $sess->id)->update([
            'payload' => base64_encode(serialize($payload)),
        ]);
        echo "UPDATED with file path\n";
    } else {
        echo "Could not unserialize payload\n";
    }
}

echo "\nDone. Please visit https://digiper.test/honorarium in your browser.\n";
