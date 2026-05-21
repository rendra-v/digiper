<?php

use Illuminate\Support\Facades\DB;

DB::statement('CREATE DATABASE IF NOT EXISTS dgperkara');
echo "Database created successfully!";
