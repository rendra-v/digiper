<?php

namespace App\Imports;

use App\Models\Perkara;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class PerkaraImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            Perkara::create([
                'no_registrasi' => $this->findValue($row, 'no_registrasi'),
                'tanggal_perkara_masuk' => $this->parseDate($this->findValue($row, 'tanggal_perkara_masuk')),
                'kamar' => $this->findValue($row, 'kamar'),
                'nama_p1' => $this->findValue($row, 'nama_p1'),
                'nama_p2' => $this->findValue($row, 'nama_p2'),
                'nama_p3' => $this->findValue($row, 'nama_p3'),
                'nama_p4' => $this->findValue($row, 'nama_p4'),
                'nama_p5' => $this->findValue($row, 'nama_p5'),
                'nama_panteraan_pengakhiri' => $this->findValue($row, 'nama_panteraan_pengakhiri'),
                'tanggal_putus' => $this->parseDate($this->findValue($row, 'tanggal_putus')),
                'amar' => $this->findValue($row, 'amar'),
                'biaya' => $this->parseBiaya($this->findValue($row, 'biaya')),
            ]);
        }
    }

    private function findValue(Collection $row, string $key)
    {
        $normalized = str_replace(' ', '_', strtolower($key));
        
        foreach ($row as $k => $v) {
            if (str_replace(' ', '_', strtolower($k)) === $normalized) {
                return $v;
            }
        }
        
        return null;
    }

    private function parseDate($date)
    {
        if (!$date) {
            return null;
        }

        if (is_numeric($date)) {
            return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date));
        }

        try {
            return Carbon::createFromFormat('d/m/Y', $date);
        } catch (\Exception $e) {
            try {
                return Carbon::createFromFormat('Y-m-d', $date);
            } catch (\Exception $e2) {
                return null;
            }
        }
    }

    private function parseBiaya($biaya)
    {
        if (!$biaya) {
            return null;
        }

        $biaya = str_replace('Rp', '', $biaya);
        $biaya = str_replace('.', '', $biaya);
        $biaya = str_replace(',', '.', $biaya);
        $biaya = trim($biaya);

        return (float) $biaya ?: null;
    }
}
