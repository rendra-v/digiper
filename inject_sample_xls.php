<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$src = public_path('sample.xls');
$destDir = storage_path('app/uploads');
if (!is_dir($destDir)) {
    mkdir($destDir, 0755, true);
}
$dest = $destDir . '/sample.xls';
copy($src, $dest);

// Register in database
$file = App\Models\ExcelFile::updateOrCreate(
    ['original_filename' => 'sample.xls'],
    [
        'file_path' => 'uploads/sample.xls',
        'period' => 'Desember 2025',
    ]
);

$fullPath = storage_path('app/' . $file->file_path);
$originalName = 'sample.xls';

echo "Registered file: $fullPath\n";

// Update all active sessions
$sessions = DB::table('sessions')->get();
echo "Updating " . $sessions->count() . " sessions...\n";
foreach ($sessions as $sess) {
    $payload = unserialize(base64_decode($sess->payload));
    if (is_array($payload)) {
        $payload['excel_file_path'] = $fullPath;
        $payload['excel_file_name'] = $originalName;
        // Invalidate cache by clearing old keys
        foreach (array_keys($payload) as $k) {
            if (str_starts_with($k, 'cache_')) {
                unset($payload[$k]);
            }
        }
        DB::table('sessions')->where('id', $sess->id)->update([
            'payload' => base64_encode(serialize($payload)),
        ]);
        echo "Session " . substr($sess->id, 0, 8) . " updated.\n";
    }
}
echo "Done!\n";
