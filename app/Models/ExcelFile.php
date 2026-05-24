<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExcelFile extends Model
{
    protected $fillable = [
        'original_filename',
        'file_path',
        'period',
    ];
}
