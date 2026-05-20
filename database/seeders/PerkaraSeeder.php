<?php

namespace Database\Seeders;

use App\Models\Perkara;
use Illuminate\Database\Seeder;

class PerkaraSeeder extends Seeder
{
    public function run(): void
    {
        Perkara::create([
            'no_registrasi' => '225/PK/TUN/2025',
            'tanggal_perkara_masuk' => '2025-01-15',
            'kamar' => 'TUN',
            'nama_p1' => 'PT. ABC Indonesia',
            'nama_p2' => null,
            'nama_p3' => null,
            'nama_p4' => null,
            'nama_p5' => null,
            'nama_panteraan_pengakhiri' => 'Budi Santoso, S.H.',
            'tanggal_putus' => '2025-12-10',
            'amar' => 'Tolak',
            'biaya' => 2000000,
        ]);

        Perkara::create([
            'no_registrasi' => '156/PK/TUN/2025',
            'tanggal_perkara_masuk' => '2025-02-20',
            'kamar' => 'TUN',
            'nama_p1' => 'Dinas Pendidikan',
            'nama_p2' => null,
            'nama_p3' => null,
            'nama_p4' => null,
            'nama_p5' => null,
            'nama_panteraan_pengakhiri' => 'Siti Nurhaliza, S.H., M.H.',
            'tanggal_putus' => '2025-12-01',
            'amar' => 'Dikabulkan',
            'biaya' => 500000,
        ]);

        Perkara::create([
            'no_registrasi' => '089/PK/TUN/2025',
            'tanggal_perkara_masuk' => '2025-03-10',
            'kamar' => 'TUN',
            'nama_p1' => 'CV. Maju Jaya',
            'nama_p2' => null,
            'nama_p3' => null,
            'nama_p4' => null,
            'nama_p5' => null,
            'nama_panteraan_pengakhiri' => 'Ahmad Wijaya, S.H.',
            'tanggal_putus' => '2025-12-15',
            'amar' => 'Ditolak',
            'biaya' => null,
        ]);
    }
}
