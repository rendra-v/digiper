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

    /**
     * Hitung data Rekap Keseluruhan 1 dari Data Print.
     * Mengembalikan data dengan format:
     * - no, perkara, klasifikasi, is_category
     * - kasasi (sisa, masuk, putus, blm, minut)
     * - pk (sisa, masuk, putus, blm, minut)
     * - total_minut
     */
    public function computeRekap1(array $categories): array
    {
        $keyed = [];
        foreach ($categories as $cat) {
            if (isset($cat['id'])) {
                $keyed[$cat['id']] = $cat;
            }
        }

        $count = function($id, $filter = null) use ($keyed) {
            $cat = $keyed[$id] ?? null;
            if (!$cat) return 0;
            if (!$filter) return count($cat['data']);
            $total = 0;
            foreach ($cat['data'] as $row) {
                $klas = strtoupper(trim($row['KLASIFIKASI'] ?? ''));
                if (in_array($klas, ['HKI', 'HAKI'])) $klas = 'HAKI';
                if ($klas === strtoupper($filter)) $total++;
            }
            return $total;
        };

        $rows = [];

        // Helper untuk membuat baris
        $makeRow = function($no, $perkara, $klasifikasi, $isCategory, $k_id, $k_filter, $pk_id, $pk_filter) use ($count) {
            $k_putus  = $k_id ? $count($k_id, $k_filter) : 0;
            $pk_putus = $pk_id ? $count($pk_id, $pk_filter) : 0;
            return [
                'no' => $no,
                'perkara' => $perkara,
                'klasifikasi' => $klasifikasi,
                'is_category' => $isCategory,
                'kasasi' => ['sisa' => 0, 'masuk' => 0, 'putus' => $k_putus, 'blm' => 0, 'minut' => $k_putus],
                'pk'     => ['sisa' => 0, 'masuk' => 0, 'putus' => $pk_putus, 'blm' => 0, 'minut' => $pk_putus],
                'total_minut' => $k_putus + $pk_putus
            ];
        };

        $rows[] = $makeRow('I', 'PERDATA', '', true, 'kasasi-pdt-umum', null, 'pk-pdt-umum', null);
        $rows[] = $makeRow('II', 'PIDANA', '', true, null, null, null, null);
        $rows[] = $makeRow('III', 'PERDATA AGAMA', '', true, 'kasasi-pdt-agama', null, 'pk-pdt-agama', null);
        $rows[] = $makeRow('IV', 'PIDANA MILITER', '', true, null, null, null, null);
        $rows[] = $makeRow('V', 'TATA USAHA NEGARA', '', true, 'kasasi-tun', null, 'pk-tun', null);
        $rows[] = $makeRow('', '', 'a. Uji Materiil', false, null, null, 'phum', null);
        $rows[] = $makeRow('', '', 'b. Sengketa Pajak', false, null, null, 'pk-pajak', null);
        
        $rows[] = $makeRow('VI', 'PERDATA KHUSUS', '', true, null, null, null, null);
        $rows[] = $makeRow('', '', 'a. PHI', false, 'kasasi-pdt-khusus', 'PHI', 'pk-pdt-khusus', 'PHI');
        $rows[] = $makeRow('', '', 'b. Arbitrase', false, 'kasasi-pdt-khusus', 'ARBITRASE', 'pk-pdt-khusus', 'ARBITRASE');
        $rows[] = $makeRow('', '', 'c. Parpol', false, 'kasasi-pdt-khusus', 'PARPOL', 'pk-pdt-khusus', 'PARPOL');
        $rows[] = $makeRow('', '', 'd. KPPU', false, 'kasasi-pdt-khusus', 'KPPU', 'pk-pdt-khusus', 'KPPU');
        $rows[] = $makeRow('', '', 'e. BPSK', false, 'kasasi-pdt-khusus', 'BPSK', 'pk-pdt-khusus', 'BPSK');
        $rows[] = $makeRow('', '', 'f. KIP', false, 'kasasi-pdt-khusus', 'KIP', 'pk-pdt-khusus', 'KIP');
        $rows[] = $makeRow('', '', 'g. HAKI', false, 'kasasi-pdt-khusus', 'HAKI', 'pk-pdt-khusus', 'HAKI');
        $rows[] = $makeRow('', '', 'h. Kepailitan', false, 'kasasi-pdt-khusus', 'KEPAILITAN', 'pk-pdt-khusus', 'KEPAILITAN');

        // Total
        $total = [
            'kasasi' => ['sisa' => 0, 'masuk' => 0, 'putus' => 0, 'blm' => 0, 'minut' => 0],
            'pk'     => ['sisa' => 0, 'masuk' => 0, 'putus' => 0, 'blm' => 0, 'minut' => 0],
            'total_minut' => 0
        ];
        foreach ($rows as $r) {
            $total['kasasi']['putus'] += $r['kasasi']['putus'];
            $total['kasasi']['minut'] += $r['kasasi']['minut'];
            $total['pk']['putus'] += $r['pk']['putus'];
            $total['pk']['minut'] += $r['pk']['minut'];
            $total['total_minut'] += $r['total_minut'];
        }

        return [
            'rows'  => $rows,
            'total' => $total
        ];
    }

    /**
     * Hitung data Rekap Keseluruhan 2 (Distribusi Per Peruntukan) dari Data Print.

     *
     * Menghasilkan array baris peruntukan dengan nilai Biaya, Jumlah, Sub Total
     * untuk setiap jenis perkara. Menggantikan buildRekapKananReport() yang
     * bergantung pada sheet "Rekap Keseluruhan" Excel.
     *
     * @param  array  $categories  Output dari parseDataPrintSheet()
     * @return array{jenis_list: array, peruntukan_rows: array, jumlah_row: array, total_grand: int}
     */
    public function computeRekap2(array $categories): array
    {
        $keyed = [];
        foreach ($categories as $cat) {
            if (isset($cat['id'])) {
                $keyed[$cat['id']] = $cat;
            }
        }

        $tarif = $this->tarif['tarif_cek'];

        // Daftar jenis perkara (kolom horizontal tabel Rekap 2 & 3)
        $jenisList = $this->getJenisList();

        // Hitung jumlah perkara per jenis
        $counts = $this->countPerJenis($keyed, $jenisList);

        // Distribusi peruntukan:
        // PPh 15% pool: TIM = 29/56, KEP = 23/56, PEMILAH = 4/56 (=1/14)
        // PPh 5%  pool: TIM = 5/16,  KEP = 11/16
        $peruntukanDefs = [
            ['no' => 1, 'label_no' => 'a.', 'peruntukan' => 'TIM (MAJELIS HAKIM, PANMUD, PP)',
             'pool' => 15, 'frac_num' => 29, 'frac_den' => 56],
            ['no' => 1, 'label_no' => 'b.', 'peruntukan' => 'KEPANITERAAN',
             'pool' => 15, 'frac_num' => 23, 'frac_den' => 56],
            ['no' => 1, 'label_no' => 'c.', 'peruntukan' => 'PEMILAH',
             'pool' => 15, 'frac_num' => 4,  'frac_den' => 56],
            ['no' => 2, 'label_no' => 'a.', 'peruntukan' => 'TIM (MAJELIS HAKIM, PANMUD, PP)',
             'pool' => 5,  'frac_num' => 5,  'frac_den' => 16],
            ['no' => 2, 'label_no' => 'b.', 'peruntukan' => 'KEPANITERAAN',
             'pool' => 5,  'frac_num' => 11, 'frac_den' => 16],
        ];

        $rows = [];
        $grandTotal = 0;

        foreach ($peruntukanDefs as $def) {
            $perJenis = [];
            $rowTotal = 0;

            foreach ($jenisList as $jenis) {
                $jumlah     = $counts[$jenis['key']] ?? 0;
                $tarifObj   = $tarif[$jenis['tarif_key']] ?? ['pph15' => 0, 'pph5' => 0];
                $dpp        = $def['pool'] === 15 ? $tarifObj['pph15'] : $tarifObj['pph5'];
                $biayaPerPerkara = (int) round($dpp * $def['frac_num'] / $def['frac_den']);
                $subTotal   = $jumlah * $biayaPerPerkara;
                $rowTotal  += $subTotal;

                $perJenis[$jenis['key']] = [
                    'biaya'     => $biayaPerPerkara,
                    'jumlah'    => $jumlah,
                    'sub_total' => $subTotal,
                ];
            }

            $persen = ($def['frac_num'] / $def['frac_den']) * ($def['pool'] === 15 ? 85 : 95);
            $grandTotal += $rowTotal;

            $rows[] = [
                'no'         => $def['no'],
                'label_no'   => $def['label_no'],
                'peruntukan' => $def['peruntukan'],
                'pph_pool'   => $def['pool'],
                'persen'     => $def['frac_num'].'/'.$def['frac_den'],
                'per_jenis'  => $perJenis,
                'total'      => $rowTotal,
            ];
        }

        // Baris JUMLAH (sum semua peruntukan per jenis)
        $jumlahPerJenis = [];
        $jumlahGrand    = 0;
        foreach ($jenisList as $jenis) {
            $sum = 0;
            foreach ($rows as $r) {
                $sum += $r['per_jenis'][$jenis['key']]['sub_total'];
            }
            $jumlahPerJenis[$jenis['key']] = $sum;
            $jumlahGrand += $sum;
        }

        return [
            'jenis_list'    => $jenisList,
            'rows'          => $rows,
            'jumlah_jenis'  => $jumlahPerJenis,
            'jumlah_grand'  => $jumlahGrand,
        ];
    }

    /**
     * Hitung data Rekap Keseluruhan 3 (Honorarium Bruto/PPh/Netto) dari Data Print.
     *
     * Sama dengan Rekap 2 tapi menambahkan kolom BRUTO, PPh 15%, PPh 5%, NETTO.
     *
     * @param  array  $categories  Output dari parseDataPrintSheet()
     * @return array{jenis_list: array, rows: array, jumlah_row: array}
     */
    public function computeRekap3(array $categories): array
    {
        $keyed = [];
        foreach ($categories as $cat) {
            if (isset($cat['id'])) {
                $keyed[$cat['id']] = $cat;
            }
        }

        $tarif     = $this->tarif['tarif_cek'];
        $jenisList = $this->getJenisList();
        $counts    = $this->countPerJenis($keyed, $jenisList);

        // Baris peruntukan: per jenis → BIAYA | JML | SUB TOTAL
        $peruntukanDefs = [
            ['no' => 1, 'label_no' => 'a.', 'peruntukan' => 'TIM (MAJELIS HAKIM, PANMUD, PP)',
             'pool' => 15, 'frac_num' => 29, 'frac_den' => 56],
            ['no' => 1, 'label_no' => 'b.', 'peruntukan' => 'KEPANITERAAN',
             'pool' => 15, 'frac_num' => 23, 'frac_den' => 56],
            ['no' => 1, 'label_no' => 'c.', 'peruntukan' => 'PEMILAH',
             'pool' => 15, 'frac_num' => 4,  'frac_den' => 56],
            ['no' => 2, 'label_no' => 'a.', 'peruntukan' => 'TIM (MAJELIS HAKIM, PANMUD, PP)',
             'pool' => 5,  'frac_num' => 5,  'frac_den' => 16],
            ['no' => 2, 'label_no' => 'b.', 'peruntukan' => 'KEPANITERAAN',
             'pool' => 5,  'frac_num' => 11, 'frac_den' => 16],
        ];

        $rows = [];

        foreach ($peruntukanDefs as $def) {
            $perJenis = [];
            $bruto = 0;

            foreach ($jenisList as $jenis) {
                $jumlah            = $counts[$jenis['key']] ?? 0;
                $tarifObj          = $tarif[$jenis['tarif_key']] ?? ['pph15' => 0, 'pph5' => 0];
                $dpp               = $def['pool'] === 15 ? $tarifObj['pph15'] : $tarifObj['pph5'];
                $biayaPerPerkara   = (int) round($dpp * $def['frac_num'] / $def['frac_den']);
                $subTotal          = $jumlah * $biayaPerPerkara;
                $bruto            += $subTotal;

                $perJenis[$jenis['key']] = [
                    'biaya'     => $biayaPerPerkara,
                    'jumlah'    => $jumlah,
                    'sub_total' => $subTotal,
                ];
            }

            $pph15    = $def['pool'] === 15 ? (int) round($bruto * 0.15) : 0;
            $pph5     = $def['pool'] === 5  ? (int) round($bruto * 0.05) : 0;
            $netto    = $bruto - $pph15 - $pph5;

            $rows[] = [
                'no'         => $def['no'],
                'label_no'   => $def['label_no'],
                'peruntukan' => $def['peruntukan'],
                'pph_pool'   => $def['pool'],
                'persen'     => $def['frac_num'].'/'.$def['frac_den'],
                'per_jenis'  => $perJenis,
                'bruto'      => $bruto,
                'pph15'      => $pph15,
                'pph5'       => $pph5,
                'netto'      => $netto,
            ];
        }

        // Baris JUMLAH
        $jumlahPerJenis = [];
        $jumlahBruto = $jumlahPph15 = $jumlahPph5 = $jumlahNetto = 0;
        foreach ($jenisList as $jenis) {
            $sum = 0;
            foreach ($rows as $r) {
                $sum += $r['per_jenis'][$jenis['key']]['sub_total'];
            }
            $jumlahPerJenis[$jenis['key']] = $sum;
        }
        foreach ($rows as $r) {
            $jumlahBruto  += $r['bruto'];
            $jumlahPph15  += $r['pph15'];
            $jumlahPph5   += $r['pph5'];
            $jumlahNetto  += $r['netto'];
        }

        return [
            'jenis_list'    => $jenisList,
            'rows'          => $rows,
            'jumlah_jenis'  => $jumlahPerJenis,
            'jumlah_bruto'  => $jumlahBruto,
            'jumlah_pph15'  => $jumlahPph15,
            'jumlah_pph5'   => $jumlahPph5,
            'jumlah_netto'  => $jumlahNetto,
        ];
    }

    /**
     * Daftar jenis perkara untuk kolom horizontal Rekap 2 & 3.
     */
    private function getJenisList(): array
    {
        return [
            ['key' => 'kasasi-pdt',    'label' => 'KASASI PDT, PDTSUS, AG',   'tarif_key' => 'kasasi_pdt'],
            ['key' => 'kasasi-tun',    'label' => 'KASASI TUN',                'tarif_key' => 'kasasi_tun'],
            ['key' => 'kasasi-niaga',  'label' => 'KASASI NIAGA',              'tarif_key' => 'kasasi_pdtsus_5jt'],
            ['key' => 'pk-pdt',        'label' => 'PK PDT',                    'tarif_key' => 'pk_pdt'],
            ['key' => 'p-hum-khs',     'label' => 'P-HUM/KHS',                 'tarif_key' => 'phum'],
            ['key' => 'pk-pajak',      'label' => 'PK PAJAK',                  'tarif_key' => 'pk_pajak'],
            ['key' => 'pk-pdt-khusus', 'label' => 'PK PDT KHUSUS',             'tarif_key' => 'pk_pdtsus_2.5jt'],
            ['key' => 'pk-agama',      'label' => 'PK AGAMA',                  'tarif_key' => 'pk_pdt'],
            ['key' => 'pk-tun',        'label' => 'PK TUN',                    'tarif_key' => 'pk_pdt'],
            ['key' => 'pk-niaga',      'label' => 'PK NIAGA',                  'tarif_key' => 'pk_pdtsus_10jt'],
        ];
    }

    /**
     * Hitung jumlah perkara per jenis dari categories Data Print.
     */
    private function countPerJenis(array $keyed, array $jenisList): array
    {
        $jenisFilter = [
            'kasasi-pdt'    => [
                // Kasasi PDT Umum + Agama + PDT Khusus 500rb (PHI, ARB, PARPOL, KPPU, BPSK, KIP)
                'sources' => [
                    ['id' => 'kasasi-pdt-umum',   'filter' => null],
                    ['id' => 'kasasi-pdt-agama',  'filter' => null],
                    ['id' => 'kasasi-pdt-khusus', 'filter' => ['PHI','ARBITRASE','PARPOL','KPPU','BPSK','KIP']],
                ],
            ],
            'kasasi-tun'    => [
                'sources' => [['id' => 'kasasi-tun', 'filter' => null]],
            ],
            'kasasi-niaga'  => [
                // Kasasi PDT Khusus 5jt (HAKI, KEPAILITAN)
                'sources' => [
                    ['id' => 'kasasi-pdt-khusus', 'filter' => ['HAKI','HKI','KEPAILITAN']],
                ],
            ],
            'pk-pdt'        => [
                'sources' => [['id' => 'pk-pdt-umum', 'filter' => null]],
            ],
            'p-hum-khs'     => [
                'sources' => [
                    ['id' => 'phum', 'filter' => null],
                    ['id' => 'pkhs', 'filter' => null],
                ],
            ],
            'pk-pajak'      => [
                'sources' => [['id' => 'pk-pajak', 'filter' => null]],
            ],
            'pk-pdt-khusus' => [
                'sources' => [
                    ['id' => 'pk-pdt-khusus', 'filter' => ['PHI','ARBITRASE','PARPOL','KPPU','BPSK','KIP']],
                ],
            ],
            'pk-agama'      => [
                'sources' => [['id' => 'pk-pdt-agama', 'filter' => null]],
            ],
            'pk-tun'        => [
                'sources' => [['id' => 'pk-tun', 'filter' => null]],
            ],
            'pk-niaga'      => [
                'sources' => [
                    ['id' => 'pk-pdt-khusus', 'filter' => ['HAKI','HKI','KEPAILITAN']],
                ],
            ],
        ];

        $counts = [];
        foreach ($jenisList as $jenis) {
            $key  = $jenis['key'];
            $def  = $jenisFilter[$key] ?? null;
            $total = 0;

            if ($def) {
                foreach ($def['sources'] as $src) {
                    $cat = $keyed[$src['id']] ?? null;
                    if (! $cat) {
                        continue;
                    }
                    if ($src['filter'] === null) {
                        $total += count($cat['data']);
                    } else {
                        foreach ($cat['data'] as $row) {
                            $klas = strtoupper(trim($row['KLASIFIKASI'] ?? ''));
                            if (in_array($klas, ['HKI', 'HAKI'])) {
                                $klas = 'HAKI';
                            }
                            if (in_array($klas, $src['filter'])) {
                                $total++;
                            }
                        }
                    }
                }
            }

            $counts[$key] = $total;
        }

        return $counts;
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