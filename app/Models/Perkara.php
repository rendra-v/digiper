<?php

namespace App\Models;

use Database\Factories\PerkaraFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perkara extends Model
{
    /** @use HasFactory<PerkaraFactory> */
    use HasFactory;

    protected $fillable = [
        'no_registrasi',
        'tanggal_perkara_masuk',
        'kamar',
        'nama_p1',
        'nama_p2',
        'nama_p3',
        'nama_p4',
        'nama_p5',
        'nama_panteraan_pengakhiri',
        'tanggal_putus',
        'amar',
        'biaya',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_perkara_masuk' => 'date',
            'tanggal_putus' => 'date',
            'biaya' => 'decimal:2',
        ];
    }

    public function getUsiaPerkara(): ?int
    {
        if (! $this->tanggal_putus) {
            return null;
        }

        return $this->tanggal_perkara_masuk->diffInDays($this->tanggal_putus);
    }
}
