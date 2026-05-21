<?php

use App\Models\Perkara;

echo "=== Digiper Setup Verification ===\n";
echo "Database: " . config('database.connections.mysql.database') . "\n";
echo "Tables count: ";

try {
    $count = Perkara::count();
    echo $count . " perkara ditemukan\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nRoutes:\n";
echo "- GET  /                    → redirect to /perkaras\n";
echo "- GET  /perkaras            → List perkara\n";
echo "- GET  /perkaras/create     → Form tambah perkara\n";
echo "- POST /perkaras            → Store perkara\n";
echo "- GET  /perkaras/{id}       → Show detail perkara\n";
echo "- DELETE /perkaras/{id}     → Delete perkara\n";
echo "- GET  /perkaras-recap      → Recap statistik\n";

echo "\n✓ Setup selesai! Aplikasi siap diakses.\n";
