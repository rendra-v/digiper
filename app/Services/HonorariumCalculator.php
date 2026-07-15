<?php

namespace App\Services;

/**
 * Menghitung seluruh data rekap/honorarium dari data perkara "Data Print".
 *
 * Alur: Data Print → countPerJenis → apply tarif → generate semua output.
 * Semua rumus diextract dari formula Excel asli.
 */
class HonorariumCalculator
{
    private array $tarif;

    public function __construct()
    {
        $this->tarif = config('tarif');
    }

    // ======================================================================
    // PUBLIC API
    // ======================================================================

    /**
     * Hitung semua data sheet "cek" dari array categories Data Print.
     *
     * @param  array  $categories  Output dari parseDataPrintSheet()
     * @return array  Data siap render untuk view sheet-cek
     */
        public function computeSheetCek(array $categories, string $period = ''): array
    {
        $groups = $this->buildCekGroups($categories);
        return [
            'groups' => $groups,
            'period' => $period,
            'error'  => null,
        ];
    }

            private function buildCekGroups(array $categories): array
    {
        $keyedCategories = [];
        foreach ($categories as $cat) {
            if (isset($cat['id'])) {
                $keyedCategories[$cat['id']] = $cat;
            }
        }
        $categories = $keyedCategories;

        $tarif = $this->tarif['tarif_cek'];
        
        $groupDefs = [
            [
                'no' => 1, 'perkara' => 'PERDATA', 'subGroups' => [
                    ['jenis' => 'KASASI', 'countKey' => 'kasasi-pdt-umum', 'tarifKey' => 'kasasi_pdt'],
                    ['jenis' => 'PK',     'countKey' => 'pk-pdt-umum',     'tarifKey' => 'pk_pdt'],
                ]
            ],
            [
                'no' => 2, 'perkara' => 'PERDATA AGAMA', 'subGroups' => [
                    ['jenis' => 'KASASI', 'countKey' => 'kasasi-pdt-agama', 'tarifKey' => 'kasasi_ag'],
                    ['jenis' => 'PK',     'countKey' => 'pk-pdt-agama',     'tarifKey' => 'pk_pdt'],
                ]
            ],
            [
                'no' => 3, 'perkara' => 'TUN', 'subGroups' => [
                    ['jenis' => 'KASASI', 'countKey' => 'kasasi-tun', 'tarifKey' => 'kasasi_tun'],
                    ['jenis' => 'PK',     'countKey' => 'pk-tun',     'tarifKey' => 'pk_pdt'],
                    ['jenis' => 'P-HUM',  'countKey' => 'phum',       'tarifKey' => 'phum'],
                    ['jenis' => 'PK-PJK', 'countKey' => 'pk-pajak',   'tarifKey' => 'pk_pajak'],
                    ['jenis' => 'P-KHS',  'countKey' => 'pkhs',       'tarifKey' => 'pkhs'],
                ]
            ],
            [
                'no' => 4, 'perkara' => 'PERDATA KHUSUS', 'subGroups' => [
                    ['jenis' => 'PHI',        'label' => 'K-PDTSUS 500', 'countKey' => 'kasasi-pdt-khusus', 'filter' => 'PHI', 'tarifKey' => 'kasasi_pdtsus_500'],
                    ['jenis' => 'ARBITRASE',  'countKey' => 'kasasi-pdt-khusus', 'filter' => 'ARBITRASE', 'tarifKey' => 'kasasi_pdtsus_500'],
                    ['jenis' => 'PARPOL',     'countKey' => 'kasasi-pdt-khusus', 'filter' => 'PARPOL', 'tarifKey' => 'kasasi_pdtsus_500'],
                    ['jenis' => 'KPPU',       'countKey' => 'kasasi-pdt-khusus', 'filter' => 'KPPU', 'tarifKey' => 'kasasi_pdtsus_500'],
                    ['jenis' => 'BPSK',       'countKey' => 'kasasi-pdt-khusus', 'filter' => 'BPSK', 'tarifKey' => 'kasasi_pdtsus_500'],
                    ['jenis' => 'KIP',        'countKey' => 'kasasi-pdt-khusus', 'filter' => 'KIP', 'tarifKey' => 'kasasi_pdtsus_500'],
                    ['jenis' => 'HAKI',       'label' => 'K-PDTSUS 5 JT', 'countKey' => 'kasasi-pdt-khusus', 'filter' => 'HAKI', 'tarifKey' => 'kasasi_pdtsus_5jt'],
                    ['jenis' => 'KEPAILITAN', 'countKey' => 'kasasi-pdt-khusus', 'filter' => 'KEPAILITAN', 'tarifKey' => 'kasasi_pdtsus_5jt'],
                    ['jenis' => 'PHI',        'label' => 'PK-PDTSUS 2,5 JT', 'countKey' => 'pk-pdt-khusus', 'filter' => 'PHI', 'tarifKey' => 'pk_pdtsus_2.5jt'],
                    ['jenis' => 'ARBITRASE',  'countKey' => 'pk-pdt-khusus', 'filter' => 'ARBITRASE', 'tarifKey' => 'pk_pdtsus_2.5jt'],
                    ['jenis' => 'PARPOL',     'countKey' => 'pk-pdt-khusus', 'filter' => 'PARPOL', 'tarifKey' => 'pk_pdtsus_2.5jt'],
                    ['jenis' => 'KPPU',       'countKey' => 'pk-pdt-khusus', 'filter' => 'KPPU', 'tarifKey' => 'pk_pdtsus_2.5jt'],
                    ['jenis' => 'BPSK',       'countKey' => 'pk-pdt-khusus', 'filter' => 'BPSK', 'tarifKey' => 'pk_pdtsus_2.5jt'],
                    ['jenis' => 'KIP',        'countKey' => 'pk-pdt-khusus', 'filter' => 'KIP', 'tarifKey' => 'pk_pdtsus_2.5jt'],
                    ['jenis' => 'HAKI',       'label' => 'PK-PDTSUS 10 JT', 'countKey' => 'pk-pdt-khusus', 'filter' => 'HAKI', 'tarifKey' => 'pk_pdtsus_10jt'],
                    ['jenis' => 'KEPAILITAN', 'countKey' => 'pk-pdt-khusus', 'filter' => 'KEPAILITAN', 'tarifKey' => 'pk_pdtsus_10jt'],
                ]
            ],
        ];

        $groups = [];
        foreach ($groupDefs as $gDef) {
            $group = [
                'no'       => $gDef['no'],
                'perkara'  => $gDef['perkara'],
                'sub_groups' => []
            ];

            foreach ($gDef['subGroups'] as $sg) {
                $count = 0;
                $catId = $sg['countKey'];
                if (isset($categories[$catId])) {
                    $allData = $categories[$catId]['data'];
                    if (isset($sg['filter'])) {
                        foreach ($allData as $row) {
                            $klas = strtoupper(trim($row['KLASIFIKASI'] ?? ''));
                            if (in_array($klas, ['HKI', 'HAKI'])) $klas = 'HAKI';
                            if ($klas === $sg['filter']) {
                                $count++;
                            }
                        }
                    } else {
                        $count = count($allData);
                    }
                }

                $t = $tarif[$sg['tarifKey']] ?? ['pph15' => 0, 'pph5' => 0];
                
                // === IMPLEMENTASI FORMULA PECAHAN DARI USER ===
                
                // 15% Row Calculations
                $biaya15 = $t['pph15'];
                $totalDpp15 = $count * $biaya15;
                
                $kep15 = $totalDpp15 * (23 / 56);
                $pemilah15 = $totalDpp15 * (1 / 14);
                $majelis15 = 0; // As per user prompt
                $tim15 = $totalDpp15 - $kep15 - $pemilah15 - $majelis15;
                
                // 5% Row Calculations
                $biaya5 = $t['pph5'];
                $totalDpp5 = $count * $biaya5;
                
                $kep5 = $totalDpp5 * (11 / 16);
                $pemilah5 = 0;
                $majelis5 = 0;
                $tim5 = $totalDpp5 - $kep5 - $pemilah5 - $majelis5;

                $group['sub_groups'][] = [
                    'jenis' => $sg['jenis'],
                    'label' => $sg['label'] ?? null,
                    'jumlah' => $count,
                    'biaya_total' => $biaya15 + $biaya5,
                    
                    'biaya_15' => $biaya15,
                    'total_15' => $totalDpp15,
                    'tim_15' => $tim15,
                    'kepaniteraan_15' => $kep15,
                    'pemilah_15' => $pemilah15,
                    'majelis5_15' => $majelis15,
                    'total_m_15' => $totalDpp15, // Total sebelum pajak
                    
                    'biaya_5' => $biaya5,
                    'total_5' => $totalDpp5,
                    'tim_5' => $tim5,
                    'kepaniteraan_5' => $kep5,
                    'pemilah_5' => $pemilah5,
                    'majelis5_5' => $majelis5,
                    'total_m_5' => $totalDpp5, // Total sebelum pajak
                ];
            }
            $groups[] = $group;
        }

        return $groups;
    }
public function countHakimFromRows(array $rows): int
    {
        $count = 0;
        foreach ($rows as $row) {
            foreach (['Nama P1', 'Nama P2', 'Nama P3', 'Nama P4', 'Nama P5'] as $col) {
                $val = trim((string)($row[$col] ?? ''));
                if ($val !== '' && $val !== '0' && $val !== '-') $count++;
            }
        }
        return $count;
    }

    public function countPemilahFromRows(array $rows): int
    {
        $count = 0;
        foreach ($rows as $row) {
            $val = trim((string)($row['Hakim Pemilah'] ?? ''));
            if ($val !== '' && $val !== '0' && $val !== '-') $count++;
        }
        return $count;
    }

    private function categoryToLabel(array $cat): string
    {
        return $cat['title'] ?? $cat['id'] ?? 'UNKNOWN';
    }
    public function countPPFromRows(array $rows): int
    {
        $count = 0;
        foreach ($rows as $row) {
            $val = trim((string)($row['Nama Panitera Pengganti'] ?? ''));
            if ($val !== '' && $val !== '0' && $val !== '-') $count++;
        }
        return $count;
    }
}