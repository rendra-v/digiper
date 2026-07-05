<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get latest file and re-insert into a fresh session
$file = App\Models\ExcelFile::latest()->first();
if (!$file) { echo "No file found\n"; exit; }

echo "File: " . $file->file_path . "\n";
echo "Original name: " . $file->original_name . "\n";

$fullPath = storage_path('app/' . $file->file_path);
echo "Full path exists: " . (file_exists($fullPath) ? 'YES' : 'NO') . "\n";

// Create a new session entry
$session_id = 'test_session_' . time();
$data = serialize([
    '_token' => str_repeat('a', 40),
    'excel_file_path' => $fullPath,
    'excel_file_name' => $file->original_name,
]);
DB::table('sessions')->insert([
    'id' => $session_id,
    'user_id' => null,
    'ip_address' => '127.0.0.1',
    'user_agent' => 'PHP',
    'payload' => base64_encode($data),
    'last_activity' => time(),
]);
echo "Created session: $session_id\n";
