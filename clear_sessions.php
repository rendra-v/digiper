<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Clear all sessions from database
$cleared = DB::table('sessions')->delete();
echo "Cleared $cleared sessions.\n";
