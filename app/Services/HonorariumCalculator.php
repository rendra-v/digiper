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
                            $klas = $this->resolveKlasifikasi($row);
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


    /**
     * Hitung Rekap Keseluruhan 2 — tabel distribusi biaya per PERUNTUKAN × jenis perkara.
     *
     * Struktur output:
     *   columns   — 10 jenis perkara dengan JML masing-masing
     *   rows      — definisi baris PERUNTUKAN (label, %, type)
     *   cells     — [row_key][col_key] = ['biaya', 'jml', 'sub_total']
     *   row_totals— total per baris lintas kolom
     *   grand_total
     *
     * Kelas tarif (5 kelas):
     *   kasasi_500  : Kasasi PDT/TUN/Agama/PHI/dll  (base 500.000)
     *   kasasi_niaga: Kasasi Niaga HKI+Kepailitan    (base 5.000.000)
     *   pk_250      : PK semua kecuali Niaga          (base 2.500.000)
     *   phum        : P-HUM / P-KHS                   (base 1.000.000)
     *   pk_niaga    : PK Niaga HKI+Kepailitan         (base 10.000.000)
     *
     * Semua nilai BIAYA per komponen × kelas sudah diverifikasi:
     *   sum(semua komponen) === base_rate untuk setiap kelas ✓
     */
    public function computeRekapKeseluruhan2(array $categories, string $period = ''): array
    {
        // ── Key categories ──────────────────────────────────────────────────
        $keyed = [];
        foreach ($categories as $cat) {
            if (isset($cat['id'])) $keyed[$cat['id']] = $cat;
        }

        $count = function (string $catId, ?string $klas = null) use ($keyed): int {
            if (!isset($keyed[$catId])) return 0;
            if ($klas === null) return count($keyed[$catId]['data']);
            $t = strtoupper($klas);
            $n = 0;
            foreach ($keyed[$catId]['data'] as $row) {
                if ($this->resolveKlasifikasi($row) === $t) $n++;
            }
            return $n;
        };

        $nonNiaga = function (string $catId) use ($keyed): int {
            $niaga = ['HKI', 'KEPAILITAN'];
            $n = 0;
            foreach ($keyed[$catId]['data'] ?? [] as $row) {
                if (!in_array($this->resolveKlasifikasi($row), $niaga)) $n++;
            }
            return $n;
        };

        // ── Column definitions (10 jenis perkara) ──────────────────────────
        $columns = [
            ['key' => 'kasasi_pdt_ag',   'label' => 'KASASI PDT, PDTSUS, AG', 'rate_label' => 'Rp. 500 Rb', 'base_rate' => 500000,   'class' => 'kasasi_500',
             'jml' => $count('kasasi-pdt-umum') + $nonNiaga('kasasi-pdt-khusus') + $count('kasasi-pdt-agama')],
            ['key' => 'kasasi_tun',       'label' => 'KASASI TUN',              'rate_label' => 'Rp. 500 Rb', 'base_rate' => 500000,   'class' => 'kasasi_500',
             'jml' => $count('kasasi-tun')],
            ['key' => 'kasasi_niaga',     'label' => 'KASASI NIAGA',            'rate_label' => 'Rp. 5jt',   'base_rate' => 5000000,  'class' => 'kasasi_niaga',
             'jml' => $count('kasasi-pdt-khusus', 'HKI') + $count('kasasi-pdt-khusus', 'KEPAILITAN')],
            ['key' => 'pk',               'label' => 'PK',                      'rate_label' => 'Rp. 2,5 Jt','base_rate' => 2500000,  'class' => 'pk_250',
             'jml' => $count('pk-pdt-umum')],
            ['key' => 'phum',             'label' => 'P-HUM (TUN)',             'rate_label' => 'Rp. 1jt',   'base_rate' => 1000000,  'class' => 'phum',
             'jml' => $count('phum') + $count('pkhs')],
            ['key' => 'pk_pajak',         'label' => 'PK-PAJAK',               'rate_label' => 'Rp. 2,5 Jt','base_rate' => 2500000,  'class' => 'pk_250',
             'jml' => $count('pk-pajak')],
            ['key' => 'pk_pdt_khusus',   'label' => 'PK-PDT KHUSUS',          'rate_label' => 'Rp. 2,5 Jt','base_rate' => 2500000,  'class' => 'pk_250',
             'jml' => $nonNiaga('pk-pdt-khusus')],
            ['key' => 'pk_agama',         'label' => 'PK-AGAMA',               'rate_label' => 'Rp. 2,5 Jt','base_rate' => 2500000,  'class' => 'pk_250',
             'jml' => $count('pk-pdt-agama')],
            ['key' => 'pk_tun',           'label' => 'PK-TUN',                 'rate_label' => 'Rp. 2,5 Jt','base_rate' => 2500000,  'class' => 'pk_250',
             'jml' => $count('pk-tun')],
            ['key' => 'pk_niaga',         'label' => 'PK NIAGA',               'rate_label' => 'Rp. 10 Jt', 'base_rate' => 10000000, 'class' => 'pk_niaga',
             'jml' => $count('pk-pdt-khusus', 'HKI') + $count('pk-pdt-khusus', 'KEPAILITAN')],
        ];

        // ── BIAYA komponen per kelas tarif ──────────────────────────────────
        // Setiap baris sum === base_rate kelasnya (sudah diverifikasi dari Excel)
        //
        // kasasi_500  : sum = 500.000
        // kasasi_niaga: sum = 5.000.000
        // pk_250      : sum = 2.500.000
        // phum        : sum = 1.000.000
        // pk_niaga    : sum = 10.000.000
        $biayaKelas = [
            'kasasi_500' => [
                'materai'       => 10000,
                'redaksi'       => 10000,
                'atk'           => 50000,
                'fotocopy'      => 20000,
                'konsumsi'      => 25000,
                'penggandaan'   => 20000,
                'pemberitahuan' => 35000,
                'pemberkasan'   => 0,
                'penyelesaian'  => 250000,   // pph15(210.000) + pph5(40.000)
                'insentif'      => 0,
                'pengarsipan'   => 15000,
                'monitoring'    => 65000,    // residual = 500.000 - 435.000
            ],
            'kasasi_niaga' => [
                'materai'       => 10000,
                'redaksi'       => 10000,
                'atk'           => 50000,
                'fotocopy'      => 25000,
                'konsumsi'      => 25000,
                'penggandaan'   => 25000,
                'pemberitahuan' => 75000,
                'pemberkasan'   => 0,
                'penyelesaian'  => 2835000,  // pph15(2.381.400) + pph5(453.600)
                'insentif'      => 0,
                'pengarsipan'   => 100000,
                'monitoring'    => 1845000,  // residual = 5.000.000 - 3.155.000
            ],
            'pk_250' => [
                'materai'       => 10000,
                'redaksi'       => 10000,
                'atk'           => 50000,
                'fotocopy'      => 25000,
                'konsumsi'      => 25000,
                'penggandaan'   => 25000,
                'pemberitahuan' => 75000,
                'pemberkasan'   => 0,
                'penyelesaian'  => 1330000,  // pph15(1.117.200) + pph5(212.800)
                'insentif'      => 0,
                'pengarsipan'   => 150000,
                'monitoring'    => 800000,   // residual = 2.500.000 - 1.700.000
            ],
            'phum' => [
                'materai'       => 10000,
                'redaksi'       => 10000,
                'atk'           => 50000,
                'fotocopy'      => 25000,
                'konsumsi'      => 25000,
                'penggandaan'   => 25000,
                'pemberitahuan' => 75000,
                'pemberkasan'   => 0,
                'penyelesaian'  => 500000,   // pph15(420.000) + pph5(80.000)
                'insentif'      => 0,
                'pengarsipan'   => 15000,
                'monitoring'    => 265000,   // residual = 1.000.000 - 735.000
            ],
            'pk_niaga' => [
                'materai'       => 10000,
                'redaksi'       => 10000,
                'atk'           => 50000,
                'fotocopy'      => 25000,
                'konsumsi'      => 25000,
                'penggandaan'   => 25000,
                'pemberitahuan' => 75000,
                'pemberkasan'   => 0,
                'penyelesaian'  => 5335000,  // pph15(4.481.400) + pph5(853.600)
                'insentif'      => 0,
                'pengarsipan'   => 100000,
                'monitoring'    => 4345000,  // residual = 10.000.000 - 5.655.000
            ],
        ];

        // ── Baris PERUNTUKAN ────────────────────────────────────────────────
        // type: 'data'     — row normal (BIAYA×JML = SUB TOTAL)
        //       'jml_only' — hanya tampilkan JML (BIAYA & SUB TOTAL = -)
        //       'header'   — judul bagian, tanpa nilai numerik
        $rowDefs = [
            ['key' => 'materai',       'no' => '1',   'label' => 'MATERAI',         'persen' => '2%',  'type' => 'data'],
            ['key' => 'redaksi',       'no' => '2',   'label' => 'REDAKSI',          'persen' => '2%',  'type' => 'data'],
            ['key' => 'relaas_pmh',    'no' => '3',   'label' => 'RELAAS KEPADA PEMOHON',  'persen' => '', 'type' => 'jml_only'],
            ['key' => 'relaas_trm',    'no' => '4',   'label' => 'RELAAS KEPADA TERMOHON', 'persen' => '', 'type' => 'jml_only'],
            ['key' => 'administrasi',  'no' => '5',   'label' => 'ADMINISTRASI :',   'persen' => '', 'type' => 'header'],
            ['key' => 'atk',           'no' => 'a.',  'label' => 'ATK',              'persen' => '10%', 'type' => 'data'],
            ['key' => 'fotocopy',      'no' => 'b.',  'label' => 'PENGGADAAN/FOTO COPY BERKAS (SEWA MESIN FOTOKOPI)',      'persen' => '4%',  'type' => 'data'],
            ['key' => 'konsumsi',      'no' => 'c.',  'label' => 'KONSUMSI PERSIDANGAN',                                   'persen' => '5%',  'type' => 'data'],
            ['key' => 'penggandaan',   'no' => 'd.',  'label' => 'PENGGANDAAN SALINAN PUTUSAN (SEWA MESIN FOTO KOPI)',     'persen' => '4%',  'type' => 'data'],
            ['key' => 'pemberitahuan', 'no' => 'e.',  'label' => 'PEMBERITAHUAN/PENGIRIMAN',                               'persen' => '7%',  'type' => 'data'],
            ['key' => 'pemberkasan',   'no' => 'f.',  'label' => 'PEMBERKASAN DAN PENJILIDAN',                             'persen' => '',    'type' => 'jml_only'],
            ['key' => 'penyelesaian',  'no' => 'g.',  'label' => 'BIAYA PENYELESAIAN PERKARA',                             'persen' => '50%', 'type' => 'data'],
            ['key' => 'insentif',      'no' => 'h.',  'label' => 'INSENTIF TIM',                                           'persen' => '',    'type' => 'jml_only'],
            ['key' => 'pengarsipan',   'no' => 'i.',  'label' => 'PENGARSIPAN BERKAS PERKARA',                             'persen' => '3%',  'type' => 'data'],
            ['key' => 'monitoring',    'no' => 'j.',  'label' => 'MONITORING DAN EVALUASI PELAKSANAAN PENYELESAIAN PERKARA', 'persen' => '13%', 'type' => 'data'],
        ];

        // ── Hitung cells ────────────────────────────────────────────────────
        $cells      = [];
        $rowTotals  = [];
        $grandTotal = 0;

        foreach ($rowDefs as $row) {
            $cells[$row['key']] = [];
            $rowTotal = 0;

            foreach ($columns as $col) {
                if ($row['type'] === 'header') {
                    $cells[$row['key']][$col['key']] = null;
                    continue;
                }

                if ($row['type'] === 'jml_only') {
                    $cells[$row['key']][$col['key']] = ['biaya' => null, 'jml' => $col['jml'], 'sub_total' => null];
                    continue;
                }

                // 'data' type
                $biayaVal = $biayaKelas[$col['class']][$row['key']] ?? 0;
                $subTotal = $biayaVal * $col['jml'];
                $cells[$row['key']][$col['key']] = [
                    'biaya'     => $biayaVal,
                    'jml'       => $col['jml'],
                    'sub_total' => $subTotal,
                ];
                $rowTotal   += $subTotal;
                $grandTotal += $subTotal;
            }

            $rowTotals[$row['key']] = $rowTotal;
        }

        return [
            'columns'     => $columns,
            'rows'        => $rowDefs,
            'cells'       => $cells,
            'row_totals'  => $rowTotals,
            'grand_total' => $grandTotal,
            'period'      => $period,
        ];
    }


    /**
     * Resolve klasifikasi perdata khusus dari satu baris Data Print.
     *
     * Urutan prioritas:
     *  1. Kolom 'klasifikasi' (atau 'KLASIFIKASI') — jika berisi nilai spesifik
     *  2. Kolom 'Jenis Permohonan' — keyword matching (misal "pdt-sus-pailit" → KEPAILITAN)
     *
     * Mengembalikan string UPPERCASE yang siap dibandingkan dengan filter.
     */
    private function resolveKlasifikasi(array $row): string
    {
        // --- 1. Cek kolom klasifikasi (multi-case fallback) ---
        $klas = strtoupper(trim(
            $row['KLASIFIKASI'] ?? $row['klasifikasi'] ?? $row['Klasifikasi'] ?? ''
        ));

        // Normalisasi alias HKI
        if (in_array($klas, ['HKI', 'HAKI'])) return 'HKI';
        // Normalisasi alias KEPAILITAN — PKPU (Penundaan Kewajiban Pembayaran Utang) termasuk kepailitan
        if (in_array($klas, ['KEPAILITAN', 'PAILIT', 'PKPU', 'PDT-SUS-PAILIT', 'PDT-SUS-PKPU'])) return 'KEPAILITAN';

        // Nilai spesifik yang valid langsung dikembalikan
        if ($klas !== '' && !in_array($klas, [
            'PERDATA KHUSUS', 'PERDATA', 'KHUSUS', 'PDT', 'PDT-SUS',
        ])) {
            return $klas;
        }

        // --- 2. Fallback: kolom Jenis Permohonan ---
        $jp = strtolower(trim(
            $row['Jenis Permohonan'] ?? $row['JENIS PERMOHONAN'] ??
            $row['jenis permohonan'] ?? $row['Jenis'] ?? ''
        ));

        if ($jp !== '') {
            if (str_contains($jp, 'pailit') || str_contains($jp, 'pkpu')) return 'KEPAILITAN';
            if (str_contains($jp, 'phi'))       return 'PHI';
            if (str_contains($jp, 'haki') || str_contains($jp, 'hki')) return 'HKI';
            if (str_contains($jp, 'arbitrase')) return 'ARBITRASE';
            if (str_contains($jp, 'parpol'))    return 'PARPOL';
            if (str_contains($jp, 'kppu'))      return 'KPPU';
            if (str_contains($jp, 'bpsk'))      return 'BPSK';
            if (str_contains($jp, 'kip'))       return 'KIP';
        }

        return $klas;
    }

    // ======================================================================
    // REKAP KESELURUHAN — auto-generate dari Data Print
    // ======================================================================

    /**
     * Hitung tabel honorarium TIM per jenis perkara dari Data Print.
     *
     * Menghasilkan 1 blok per jenis perkara dengan 4 jabatan:
     *  1. TIM KOREKTOR / HAKIM AGUNG  – per nama dari Nama P1/P2/P3, PPH 15%
     *  2. PANITERA MUDA KAMAR DAN STAF – 1 baris, jumlah = total perkara, PPH 15%
     *  3. ASISTEN / PANITERA PENGGANTI – per nama dari Nama Panitera Pengganti, PPH 15%
     *  4. OPERATOR/ PENGETIK           – 1 baris, jumlah = total perkara, PPH 5%
     *
     * @param  array  $categories  Output dari parseDataPrintSheet()
     * @return array  Array of blocks: ['label', 'jumlah_perkara', 'rows', 'total']
     */
    public function computeTimHonorariumBlocks(array $categories): array
    {
        // Index by id
        $keyed = [];
        foreach ($categories as $cat) {
            if (isset($cat['id'])) $keyed[$cat['id']] = $cat;
        }

        // Tarif penyelesaian per kelas
        $tc = $this->tarif['tarif_cek'];
        $penyelesaian = [
            'kasasi_500'   => $tc['kasasi_pdt']['pph15']        + $tc['kasasi_pdt']['pph5'],        // 250.000
            'kasasi_niaga' => $tc['kasasi_pdtsus_5jt']['pph15'] + $tc['kasasi_pdtsus_5jt']['pph5'], // 2.835.000
            'pk_250'       => $tc['pk_pdt']['pph15']            + $tc['pk_pdt']['pph5'],             // 1.330.000
            'phum'         => $tc['phum']['pph15']              + $tc['phum']['pph5'],               // 500.000
            'pk_niaga'     => $tc['pk_pdtsus_10jt']['pph15']    + $tc['pk_pdtsus_10jt']['pph5'],    // 5.335.000
            'pkhs'         => $tc['pkhs']['pph15']              + $tc['pkhs']['pph5'],               // 500.000
        ];

        // Persentase per jabatan
        $persenHakim    = 0.30 / 3;   // majelis hakim 30% dibagi 3
        $persenPanmud   = 0.05;
        $persenPP       = 0.085;
        $persenOperator = 0.05;       // PPH 5%

        // Klasifikasi untuk filtering niaga (cek kolom 'klasifikasi' di data)
        $niagaKeywords = ['HAKI', 'PATEN', 'KEPAILITAN', 'NIAGA'];
        $isNiaga = function (array $row) use ($niagaKeywords): bool {
            $klas = strtoupper(trim((string) ($row['klasifikasi'] ?? '')));
            $jenis = strtoupper(trim((string) ($row['Jenis Perkara'] ?? '')));
            foreach ($niagaKeywords as $kw) {
                if (str_contains($klas, $kw) || str_contains($jenis, $kw)) return true;
            }
            return false;
        };

        // Hitung frekuensi nama per kolom (case-insensitive key lookup)
        $countByName = function (array $rows, array $columns): array {
            $counts = [];
            foreach ($rows as $row) {
                // Build case-insensitive key map sekali per row
                $rowUpper = [];
                foreach ($row as $k => $v) {
                    $rowUpper[strtoupper($k)] = $v;
                }
                foreach ($columns as $col) {
                    $name = trim((string) ($rowUpper[strtoupper($col)] ?? ''));
                    if ($name === '' || $name === '-' || $name === '0') continue;
                    $counts[$name] = ($counts[$name] ?? 0) + 1;
                }
            }
            arsort($counts);
            return $counts;
        };

        // Urutan mengikuti dropdown data-print (27 kategori)
        $jenisDefs = [
            // 1
            ['label' => 'KASASI PERDATA UMUM',                      'tarif' => 'kasasi_500',   'sources' => [['id' => 'kasasi-pdt-umum',             'filter' => null]]],
            // 2
            ['label' => 'PENINJAUAN KEMBALI PERDATA UMUM',          'tarif' => 'pk_250',       'sources' => [['id' => 'pk-pdt-umum',                'filter' => null]]],
            // 3  parent (all 98)
            ['label' => 'KASASI PERDATA KHUSUS',                    'tarif' => 'kasasi_500',   'sources' => [['id' => 'kasasi-pdt-khusus',          'filter' => null]]],
            // 4-11 sub
            ['label' => 'KASASI PERDATA KHUSUS (PHI)',              'tarif' => 'kasasi_500',   'sources' => [['id' => 'kasasi-pdt-khusus-PHI',      'filter' => null]]],
            ['label' => 'KASASI PERDATA KHUSUS (HKI)',              'tarif' => 'kasasi_niaga', 'sources' => [['id' => 'kasasi-pdt-khusus-HKI',      'filter' => null]]],
            ['label' => 'KASASI PERDATA KHUSUS (KEPAILITAN)',       'tarif' => 'kasasi_niaga', 'sources' => [['id' => 'kasasi-pdt-khusus-KEPAILITAN','filter' => null]]],
            ['label' => 'KASASI PERDATA KHUSUS (ARBITRASE)',        'tarif' => 'kasasi_500',   'sources' => [['id' => 'kasasi-pdt-khusus-ARBITRASE', 'filter' => null]]],
            ['label' => 'KASASI PERDATA KHUSUS (PARPOL)',           'tarif' => 'kasasi_500',   'sources' => [['id' => 'kasasi-pdt-khusus-PARPOL',   'filter' => null]]],
            ['label' => 'KASASI PERDATA KHUSUS (KPPU)',             'tarif' => 'kasasi_500',   'sources' => [['id' => 'kasasi-pdt-khusus-KPPU',     'filter' => null]]],
            ['label' => 'KASASI PERDATA KHUSUS (BPSK)',             'tarif' => 'kasasi_500',   'sources' => [['id' => 'kasasi-pdt-khusus-BPSK',     'filter' => null]]],
            ['label' => 'KASASI PERDATA KHUSUS (KIP)',              'tarif' => 'kasasi_500',   'sources' => [['id' => 'kasasi-pdt-khusus-KIP',      'filter' => null]]],
            // 12 parent PK Khusus
            ['label' => 'PENINJAUAN KEMBALI PERDATA KHUSUS',        'tarif' => 'pk_250',       'sources' => [['id' => 'pk-pdt-khusus',              'filter' => null]]],
            // 13-20 sub PK Khusus
            ['label' => 'PENINJAUAN KEMBALI PERDATA KHUSUS (PHI)',       'tarif' => 'pk_250',   'sources' => [['id' => 'pk-pdt-khusus-PHI',      'filter' => null]]],
            ['label' => 'PENINJAUAN KEMBALI PERDATA KHUSUS (HKI)',       'tarif' => 'pk_niaga', 'sources' => [['id' => 'pk-pdt-khusus-HKI',      'filter' => null]]],
            ['label' => 'PENINJAUAN KEMBALI PERDATA KHUSUS (KEPAILITAN)','tarif' => 'pk_niaga', 'sources' => [['id' => 'pk-pdt-khusus-KEPAILITAN','filter' => null]]],
            ['label' => 'PENINJAUAN KEMBALI PERDATA KHUSUS (ARBITRASE)', 'tarif' => 'pk_250',   'sources' => [['id' => 'pk-pdt-khusus-ARBITRASE', 'filter' => null]]],
            ['label' => 'PENINJAUAN KEMBALI PERDATA KHUSUS (PARPOL)',    'tarif' => 'pk_250',   'sources' => [['id' => 'pk-pdt-khusus-PARPOL',   'filter' => null]]],
            ['label' => 'PENINJAUAN KEMBALI PERDATA KHUSUS (KPPU)',      'tarif' => 'pk_250',   'sources' => [['id' => 'pk-pdt-khusus-KPPU',     'filter' => null]]],
            ['label' => 'PENINJAUAN KEMBALI PERDATA KHUSUS (BPSK)',      'tarif' => 'pk_250',   'sources' => [['id' => 'pk-pdt-khusus-BPSK',     'filter' => null]]],
            ['label' => 'PENINJAUAN KEMBALI PERDATA KHUSUS (KIP)',       'tarif' => 'pk_250',   'sources' => [['id' => 'pk-pdt-khusus-KIP',      'filter' => null]]],
            // 21-27 sisa
            ['label' => 'KASASI PERDATA AGAMA',                     'tarif' => 'kasasi_500',   'sources' => [['id' => 'kasasi-pdt-agama',           'filter' => null]]],
            ['label' => 'PENINJAUAN KEMBALI PERDATA AGAMA',         'tarif' => 'pk_250',       'sources' => [['id' => 'pk-pdt-agama',              'filter' => null]]],
            ['label' => 'KASASI TATA USAHA NEGARA (K-TUN)',         'tarif' => 'kasasi_500',   'sources' => [['id' => 'kasasi-tun',                'filter' => null]]],
            ['label' => 'P-HUM (PERMOHONAN HAK UJI MATERIL)',       'tarif' => 'phum',         'sources' => [['id' => 'phum',                      'filter' => null]]],
            ['label' => 'P-KHS (PERMOHONAN HAK UJI PENDAPAT)',      'tarif' => 'pkhs',         'sources' => [['id' => 'pkhs',                      'filter' => null]]],
            ['label' => 'PENINJAUAN KEMBALI TATA USAHA NEGARA (PK-TUN)', 'tarif' => 'pk_250',  'sources' => [['id' => 'pk-tun',                    'filter' => null]]],
            ['label' => 'PENINJAUAN KEMBALI PAJAK (PK-PJK)',        'tarif' => 'pk_250',       'sources' => [['id' => 'pk-pajak',                  'filter' => null]]],
        ];

        $blocks = [];

        foreach ($jenisDefs as $def) {
            // Kumpulkan baris dari sumber yang relevan
            $rows = [];
            foreach ($def['sources'] as $src) {
                if (! isset($keyed[$src['id']])) continue;
                $cat = $keyed[$src['id']];
                if (empty($cat['data'])) continue;

                $srcRows = $cat['data'];
                if ($src['filter'] === 'niaga') {
                    $srcRows = array_values(array_filter($srcRows, $isNiaga));
                } elseif ($src['filter'] === 'non-niaga') {
                    $srcRows = array_values(array_filter($srcRows, fn($r) => ! $isNiaga($r)));
                }
                $rows = array_merge($rows, $srcRows);
            }

            // Hitung total perkara (termasuk kategori dengan 0 baris)
            $totalPerkara = 0;
            foreach ($def['sources'] as $src) {
                if (! isset($keyed[$src['id']])) continue;
                $cat = $keyed[$src['id']];
                if ($src['filter'] === null) {
                    $totalPerkara += count($cat['data'] ?? []);
                } else {
                    foreach ($cat['data'] ?? [] as $r) {
                        $isN = $isNiaga($r);
                        if ($src['filter'] === 'niaga'     && $isN)  $totalPerkara++;
                        if ($src['filter'] === 'non-niaga' && !$isN) $totalPerkara++;
                    }
                }
            }

            $penyel = $penyelesaian[$def['tarif']];

            // P-KHS punya struktur % berbeda (dari Rekap Keseluruhan 3):
            //   hakim   = (majelis_hakim 30% + ketua_ma 3%) / 3 = 11%  → 500.000 × 0.11 = 55.000
            //   panmud  = panmud_perkara 6% (bukan panmud_staf_tim 5%) → 500.000 × 0.06 = 30.000
            //   PP      = 5%                                             → 500.000 × 0.05 = 25.000
            //   operator= 5%                                             → 500.000 × 0.05 = 25.000
            if ($def['tarif'] === 'pkhs') {
                $biayaHakim    = (int) round($penyel * (0.33 / 3));   // 55.000
                $biayaPanmud   = (int) round($penyel * 0.06);         // 30.000
                $biayaPP       = (int) round($penyel * 0.05);         // 25.000
                $biayaOperator = (int) round($penyel * $persenOperator); // 25.000
            } else {
                $biayaHakim    = (int) round($penyel * $persenHakim);
                $biayaPanmud   = (int) round($penyel * $persenPanmud);
                $biayaPP       = (int) round($penyel * $persenPP);
                $biayaOperator = (int) round($penyel * $persenOperator);
            }

            // Kolom nama hakim sesuai struktur Excel aktual (sama dengan countHakimFromRows)
            $hakimCounts = $countByName($rows, ['NAMA P1', 'NAMA P2', 'NAMA P3']);
            $ppCounts    = $countByName($rows, ['NAMA PANITERA PENGGANTI']);

            $tableRows = [];
            $no = 1;

            // 1. TIM KOREKTOR / HAKIM AGUNG — per nama, PPH 15%
            if (empty($hakimCounts)) {
                // Tidak ada data hakim — taro 1 baris kosong
                $tableRows[] = [
                    'no'             => $no++,
                    'nama'           => '',
                    'jabatan'        => 'TIM KOREKTOR / HAKIM AGUNG',
                    'jumlah_perkara' => 0,
                    'biaya'          => $biayaHakim,
                    'jumlah_biaya'   => 0,
                    'pph15'          => 0,
                    'pph5'           => 0,
                    'netto'          => 0,
                ];
            } else {
                foreach ($hakimCounts as $nama => $jmlPerkara) {
                    $jumlahBiaya = $jmlPerkara * $biayaHakim;
                    $pph15 = (int) round($jumlahBiaya * 0.15);
                    $tableRows[] = [
                        'no'             => $no++,
                        'nama'           => $nama,
                        'jabatan'        => 'TIM KOREKTOR / HAKIM AGUNG',
                        'jumlah_perkara' => $jmlPerkara,
                        'biaya'          => $biayaHakim,
                        'jumlah_biaya'   => $jumlahBiaya,
                        'pph15'          => $pph15,
                        'pph5'           => 0,
                        'netto'          => $jumlahBiaya - $pph15,
                    ];
                }
            }

            // 2. PANITERA MUDA KAMAR DAN STAF — 1 baris, PPH 15%
            $jbPanmud    = $totalPerkara * $biayaPanmud;
            $pph15Panmud = (int) round($jbPanmud * 0.15);
            $tableRows[] = [
                'no'             => $no++,
                'nama'           => '',
                'jabatan'        => 'PANITERA MUDA KAMAR DAN STAF',
                'jumlah_perkara' => $totalPerkara,
                'biaya'          => $biayaPanmud,
                'jumlah_biaya'   => $jbPanmud,
                'pph15'          => $pph15Panmud,
                'pph5'           => 0,
                'netto'          => $jbPanmud - $pph15Panmud,
            ];

            // 3. ASISTEN / PANITERA PENGGANTI — per nama, PPH 15%
            if (empty($ppCounts)) {
                // Tidak ada data PP — taro 1 baris kosong
                $tableRows[] = [
                    'no'             => $no++,
                    'nama'           => '',
                    'jabatan'        => 'ASISTEN / PANITERA PENGGANTI',
                    'jumlah_perkara' => 0,
                    'biaya'          => $biayaPP,
                    'jumlah_biaya'   => 0,
                    'pph15'          => 0,
                    'pph5'           => 0,
                    'netto'          => 0,
                ];
            } else {
                foreach ($ppCounts as $nama => $jmlPerkara) {
                    $jumlahBiaya = $jmlPerkara * $biayaPP;
                    $pph15 = (int) round($jumlahBiaya * 0.15);
                    $tableRows[] = [
                        'no'             => $no++,
                        'nama'           => $nama,
                        'jabatan'        => 'ASISTEN / PANITERA PENGGANTI',
                        'jumlah_perkara' => $jmlPerkara,
                        'biaya'          => $biayaPP,
                        'jumlah_biaya'   => $jumlahBiaya,
                        'pph15'          => $pph15,
                        'pph5'           => 0,
                        'netto'          => $jumlahBiaya - $pph15,
                    ];
                }
            }

            // 4. OPERATOR/ PENGETIK — 1 baris, PPH 5%
            $jbOperator   = $totalPerkara * $biayaOperator;
            $pph5Operator = (int) round($jbOperator * 0.05);
            $tableRows[] = [
                'no'             => $no++,
                'nama'           => '',
                'jabatan'        => 'OPERATOR/ PENGETIK',
                'jumlah_perkara' => $totalPerkara,
                'biaya'          => $biayaOperator,
                'jumlah_biaya'   => $jbOperator,
                'pph15'          => 0,
                'pph5'           => $pph5Operator,
                'netto'          => $jbOperator - $pph5Operator,
            ];

            $blocks[] = [
                'label'          => $def['label'],
                'jumlah_perkara' => $totalPerkara,
                'rows'           => $tableRows,
                'total'          => [
                    'jumlah_biaya' => array_sum(array_column($tableRows, 'jumlah_biaya')),
                    'pph15'        => array_sum(array_column($tableRows, 'pph15')),
                    'pph5'         => array_sum(array_column($tableRows, 'pph5')),
                    'netto'        => array_sum(array_column($tableRows, 'netto')),
                ],
            ];
        }

        return $blocks;
    }

    /**
     * Hitung rekapitulasi biaya penyelesaian perkara dari Data Print.
     *
     * @param  array  $categories  Output dari parseDataPrintSheet()
     * @return array  Data siap render: groups[], final_total, period
     */
    public function computeRekapKeseluruhan(array $categories, string $period = ''): array
    {
        // Key categories by id
        $keyed = [];
        foreach ($categories as $cat) {
            if (isset($cat['id'])) {
                $keyed[$cat['id']] = $cat;
            }
        }

        $biaya = config('tarif.biaya_perkara');

        // Count rows dari kategori, dengan opsional filter KLASIFIKASI
        $countRows = function (string $catId, ?string $klasFilter = null) use ($keyed): int {
            if (!isset($keyed[$catId])) return 0;
            if ($klasFilter === null) return count($keyed[$catId]['data']);
            $target = strtoupper($klasFilter);
            $n = 0;
            foreach ($keyed[$catId]['data'] as $row) {
                if ($this->resolveKlasifikasi($row) === $target) $n++;
            }
            return $n;
        };

        // Buat satu baris data
        $makeRow = function (string $label, int $jmlK, int $biayaK, int $jmlPK, int $biayaPK): array {
            return [
                'label'         => $label,
                'kasasi_jumlah' => $jmlK,
                'kasasi_biaya'  => $biayaK,
                'kasasi_total'  => $jmlK * $biayaK,
                'pk_jumlah'     => $jmlPK,
                'pk_biaya'      => $biayaPK,
                'pk_total'      => $jmlPK * $biayaPK,
                'grand_total'   => ($jmlK * $biayaK) + ($jmlPK * $biayaPK),
            ];
        };

        // Hitung total dari sekumpulan baris
        $groupTotals = function (array $rows): array {
            $kasasiJml = $kasasiTotal = $pkJml = $pkTotal = $grand = 0;
            foreach ($rows as $r) {
                $kasasiJml   += $r['kasasi_jumlah'];
                $kasasiTotal += $r['kasasi_total'];
                $pkJml       += $r['pk_jumlah'];
                $pkTotal     += $r['pk_total'];
                $grand       += $r['grand_total'];
            }
            return compact('kasasiJml', 'kasasiTotal', 'pkJml', 'pkTotal', 'grand');
        };

        $groups = [];

        // === I. PERDATA UMUM ===
        $rows1 = [
            $makeRow('Perdata Umum',
                $countRows('kasasi-pdt-umum'), $biaya['PERDATA']['kasasi'],
                $countRows('pk-pdt-umum'),     $biaya['PERDATA']['pk']
            ),
        ];
        $groups[] = ['no' => 'I', 'label' => 'PERDATA UMUM', 'rows' => $rows1] + $groupTotals($rows1);

        // === II. PERDATA KHUSUS ===
        // Setiap sub-klasifikasi punya biaya berbeda (500rb atau 5jt kasasi, 2.5jt atau 10jt PK)
        $pdtKhususDefs = [
            ['label' => 'PHI',        'filter' => 'PHI',        'biayaK' => $biaya['PHI']['kasasi'],        'biayaPK' => 2500000],
            ['label' => 'HKI',        'filter' => 'HKI',        'biayaK' => $biaya['HKI']['kasasi'],        'biayaPK' => $biaya['HKI']['pk']],
            ['label' => 'Kepailitan', 'filter' => 'KEPAILITAN', 'biayaK' => $biaya['KEPAILITAN']['kasasi'], 'biayaPK' => $biaya['KEPAILITAN']['pk']],
            ['label' => 'Arbitrase',  'filter' => 'ARBITRASE',  'biayaK' => $biaya['ARBITRASE']['kasasi'],  'biayaPK' => $biaya['ARBITRASE']['pk']],
            ['label' => 'Parpol',     'filter' => 'PARPOL',     'biayaK' => $biaya['PARPOL']['kasasi'],     'biayaPK' => $biaya['PARPOL']['pk']],
            ['label' => 'KPPU',       'filter' => 'KPPU',       'biayaK' => $biaya['KPPU']['kasasi'],       'biayaPK' => $biaya['KPPU']['pk']],
            ['label' => 'BPSK',       'filter' => 'BPSK',       'biayaK' => $biaya['BPSK']['kasasi'],       'biayaPK' => $biaya['BPSK']['pk']],
            ['label' => 'KIP',        'filter' => 'KIP',        'biayaK' => $biaya['KIP']['kasasi'],        'biayaPK' => $biaya['KIP']['pk']],
        ];
        $rows2 = [];
        foreach ($pdtKhususDefs as $def) {
            $jmlK  = $countRows('kasasi-pdt-khusus', $def['filter']);
            $jmlPK = $countRows('pk-pdt-khusus',     $def['filter']);
            $rows2[] = $makeRow($def['label'], $jmlK, $def['biayaK'], $jmlPK, $def['biayaPK']);
        }
        $groups[] = ['no' => 'II', 'label' => 'PERDATA KHUSUS', 'rows' => $rows2] + $groupTotals($rows2);

        // === III. AGAMA ===
        $rows3 = [
            $makeRow('Agama',
                $countRows('kasasi-pdt-agama'), $biaya['AGAMA']['kasasi'],
                $countRows('pk-pdt-agama'),     $biaya['AGAMA']['pk']
            ),
        ];
        $groups[] = ['no' => 'III', 'label' => 'AGAMA', 'rows' => $rows3] + $groupTotals($rows3);

        // === IV. TUN ===
        $rows4 = [
            $makeRow('TUN',
                $countRows('kasasi-tun'), $biaya['TUN']['kasasi'],
                $countRows('pk-tun'),     $biaya['TUN']['pk']
            ),
            $makeRow('P-HUM',   $countRows('phum'),     $biaya['HUM']['kasasi'],    0, 0),
            $makeRow('PK-PJK',  0,                      0, $countRows('pk-pajak'),  $biaya['PAJAK']['pk']),
            $makeRow('P-KHS',   $countRows('pkhs'),     $biaya['KHUSUS']['kasasi'], 0, 0),
        ];
        $groups[] = ['no' => 'IV', 'label' => 'TUN', 'rows' => $rows4] + $groupTotals($rows4);

        // Grand total dari semua baris
        $allRows    = array_merge(...array_map(fn ($g) => $g['rows'], $groups));
        $finalTotal = $groupTotals($allRows);

        return [
            'groups'      => $groups,
            'final_total' => $finalTotal,
            'period'      => $period,
        ];
    }

    /**
     * Hitung Rekap Keseluruhan 3 — distribusi honor per jabatan × jenis perkara.
     *
     * Input: hasil computeRekapKeseluruhan2() (columns + cells untuk row 'penyelesaian')
     *
     * Untuk setiap jabatan dan setiap kolom perkara:
     *   BIAYA      = biaya_penyelesaian_kolom × persen_jabatan
     *   SUB TOTAL  = BIAYA × JML (dari rekap2)
     *   BRUTO      = Σ sub_total semua kolom
     *
     * Aturan pajak:
     *   'pph15' : PPh 15% = BRUTO × 15%, PPh 5% = 0
     *   'pph5'  : PPh 15% = 0, PPh 5% = BRUTO × 5%
     *   'mixed' : Rumpun A (TUN,PHUM,PKPAJAK,PKTUN) → PPh 5%
     *             Rumpun B (sisanya) → PPh 15%
     */
    public function computeRekapKeseluruhan3(array $rekap2): array
    {
        $columns = $rekap2['columns'];    // [{key,label,rate_label,base_rate,class,jml}]
        $cells2  = $rekap2['cells'];      // [row_key][col_key] = {biaya,jml,sub_total}

        // JML per kolom (sama dengan rekap2)
        $jml = [];
        foreach ($columns as $col) {
            $jml[$col['key']] = $col['jml'];
        }

        // BIAYA PENYELESAIAN per kolom (dari rekap2 row 'penyelesaian')
        $penyelesaian = [];
        foreach ($columns as $col) {
            $penyelesaian[$col['key']] = $cells2['penyelesaian'][$col['key']]['biaya'] ?? 0;
        }

        // ── Rumpun pajak untuk 'mixed' ──────────────────────────────────────
        // Rumpun A = PPh 5%: kasasi_tun, phum, pk_pajak, pk_tun
        $rumpunA = ['kasasi_tun', 'phum', 'pk_pajak', 'pk_tun'];
        // Rumpun B = PPh 15%: sisanya
        $colKeys = array_column($columns, 'key');
        $rumpunB = array_diff($colKeys, $rumpunA);

        // ── Definisi jabatan ────────────────────────────────────────────────
        $jabatanList = [
            ['key' => 'ketua_ma',        'label' => 'Ketua Mahkamah Agung',                                               'persen' => 0.03,  'pajak' => 'pph15'],
            ['key' => 'waka_yudisial',   'label' => 'Wakil Ketua MA Bidang Yudisial',                                     'persen' => 0.02,  'pajak' => 'pph15'],
            ['key' => 'waka_non_yud',    'label' => 'Wakil Ketua MA Bidang Non-Yudisial',                                 'persen' => 0.02,  'pajak' => 'pph15'],
            ['key' => 'ketua_kamar',     'label' => 'Ketua Kamar',                                                        'persen' => 0.025, 'pajak' => 'pph15'],
            ['key' => 'majelis_hakim',   'label' => 'Majelis Hakim',                                                      'persen' => 0.30,  'pajak' => 'pph15'],
            ['key' => 'panitera_ma',     'label' => 'Panitera Mahkamah Agung',                                            'persen' => 0.06,  'pajak' => 'pph15'],
            ['key' => 'panmud_perkara',  'label' => 'Panitera Muda Perkara',                                              'persen' => 0.06,  'pajak' => 'pph15'],
            ['key' => 'hakim_pemilah',   'label' => 'Hakim Pemilah',                                                      'persen' => 0.06,  'pajak' => 'pph15'],
            ['key' => 'panmud_staf_tim', 'label' => 'Panitera Muda dan Staf Tim',                                         'persen' => 0.05,  'pajak' => 'pph15'],
            ['key' => 'pp',              'label' => 'Panitera Pengganti',                                                  'persen' => 0.085, 'pajak' => 'pph15'],
            ['key' => 'operator',        'label' => 'Operator / Pengetik',                                                 'persen' => 0.05,  'pajak' => 'pph5'],
            ['key' => 'tim_penelaah',    'label' => 'Tim Penelaah Kelengkapan/Formalitas Berkas',                         'persen' => 0.06,  'pajak' => 'mixed'],
            ['key' => 'staf_panmud',     'label' => 'Staf Panitera Muda Perkara',                                         'persen' => 0.06,  'pajak' => 'mixed'],
            ['key' => 'tim_data',        'label' => 'Tim Pendukung Pengolah Data, Pelaporan, dan Sistem Informasi',        'persen' => 0.05,  'pajak' => 'pph15'],
            ['key' => 'tim_biaya',       'label' => 'Tim Pengelola Biaya Proses',                                          'persen' => 0.05,  'pajak' => 'pph15'],
            ['key' => 'tim_penerima',    'label' => 'Tim Penerima Berkas',                                                  'persen' => 0.02,  'pajak' => 'pph15'],
        ];

        // ── Hitung cells ────────────────────────────────────────────────────
        $rows       = [];
        $grandBruto = 0;
        $grandPph15 = 0;
        $grandPph5  = 0;
        $grandNetto = 0;

        // Untuk total kolom: sub_total per kolom lintas semua jabatan
        $colGrandTotal = array_fill_keys($colKeys, 0);

        foreach ($jabatanList as $jab) {
            $cells = [];
            $bruto = 0;

            foreach ($columns as $col) {
                $biayaJab = (int) round($penyelesaian[$col['key']] * $jab['persen']);
                $subTotal = $biayaJab * $jml[$col['key']];
                $cells[$col['key']] = [
                    'biaya'     => $biayaJab,
                    'jml'       => $jml[$col['key']],
                    'sub_total' => $subTotal,
                ];
                $bruto                    += $subTotal;
                $colGrandTotal[$col['key']] += $subTotal;
            }

            // Hitung pajak
            $pph15 = 0;
            $pph5  = 0;

            if ($jab['pajak'] === 'pph15') {
                $pph15 = (int) round($bruto * 0.15);
            } elseif ($jab['pajak'] === 'pph5') {
                $pph5 = (int) round($bruto * 0.05);
            } elseif ($jab['pajak'] === 'mixed') {
                $subA = 0;
                $subB = 0;
                foreach ($rumpunA as $ck) $subA += $cells[$ck]['sub_total'] ?? 0;
                foreach ($rumpunB as $ck) $subB += $cells[$ck]['sub_total'] ?? 0;
                $pph5  = (int) round($subA * 0.05);
                $pph15 = (int) round($subB * 0.15);
            }

            $netto = $bruto - $pph15 - $pph5;

            $rows[] = [
                'key'    => $jab['key'],
                'label'  => $jab['label'],
                'persen' => $jab['persen'],
                'pajak'  => $jab['pajak'],
                'cells'  => $cells,
                'bruto'  => $bruto,
                'pph15'  => $pph15,
                'pph5'   => $pph5,
                'netto'  => $netto,
            ];

            $grandBruto += $bruto;
            $grandPph15 += $pph15;
            $grandPph5  += $pph5;
            $grandNetto += $netto;
        }

        return [
            'columns'         => $columns,
            'jabatan'         => $jabatanList,
            'rows'            => $rows,
            'col_grand_total' => $colGrandTotal,
            'grand_bruto'     => $grandBruto,
            'grand_pph15'     => $grandPph15,
            'grand_pph5'      => $grandPph5,
            'grand_netto'     => $grandNetto,
        ];
    }

    /**
     * Hitung seluruh blok tabel Kepaniteraan dari Data Print.
     *
     * Asumsi biaya satuan:
     *  ─ T1  : nilai flat dari Excel (kasasi_500 = screenshot), diskala proporsional ke kelas lain.
     *  ─ T2  : persen × penyelesaian_kelas (konsisten dengan Rekap 3).
     *  ─ Agama / TUN / Khusus : struktur T2 + Staf Panmud spesifik.
     *
     * @return array  [['title','tim','jml_perkara','rows','total'], ...]
     */
    public function computeKepaniteraanBlocks(array $categories): array
    {
        // ── Index & helpers ─────────────────────────────────────────────────
        $keyed = [];
        foreach ($categories as $cat) {
            if (isset($cat['id'])) $keyed[$cat['id']] = $cat;
        }

        $countRows = function (string $id, ?string $klas = null) use ($keyed): int {
            if (!isset($keyed[$id])) return 0;
            if ($klas === null) return count($keyed[$id]['data']);
            $n = 0;
            foreach ($keyed[$id]['data'] as $row) {
                if ($this->resolveKlasifikasi($row) === strtoupper($klas)) $n++;
            }
            return $n;
        };

        $countRowsMulti = function (string $id, array $klasList) use ($keyed): int {
            if (!isset($keyed[$id])) return 0;
            $n = 0;
            foreach ($keyed[$id]['data'] as $row) {
                if (in_array($this->resolveKlasifikasi($row), $klasList)) $n++;
            }
            return $n;
        };

        // ── Tarif & penyelesaian per kelas ──────────────────────────────────
        // Total biaya perkara (base rate)
        $tarifTotal = [
            'kasasi_500'   => 500_000,
            'phum'         => 1_000_000,
            'pk_250'       => 2_500_000,
            'kasasi_niaga' => 5_000_000,
            'pk_niaga'     => 10_000_000,
        ];

        // Penyelesaian = pph15 + pph5 per kelas (dari tarif_cek config)
        $tc = $this->tarif['tarif_cek'];
        $penyel = [
            'kasasi_500'   => $tc['kasasi_pdt']['pph15']         + $tc['kasasi_pdt']['pph5'],
            'phum'         => $tc['phum']['pph15']                + $tc['phum']['pph5'],
            'pk_250'       => $tc['pk_pdt']['pph15']              + $tc['pk_pdt']['pph5'],
            'kasasi_niaga' => $tc['kasasi_pdtsus_5jt']['pph15']   + $tc['kasasi_pdtsus_5jt']['pph5'],
            'pk_niaga'     => $tc['pk_pdtsus_10jt']['pph15']      + $tc['pk_pdtsus_10jt']['pph5'],
        ];

        // ── T1 biaya satuan (dari screenshot, kasasi_500) ───────────────────
        // Diskala proporsional: biaya_class = biaya_500 × (total_class / 500.000)
        $t1Base = [
            'kma'         => 27_500,
            'panitera'    => 20_000,
            'panmud'      => 22_500,
            'penelaah'    => 15_000,
            'staf_panmud' => 20_000,
            'operator'    => 10_000,
            'ppk'         => 15_000,
        ];

        $t1Biaya = function (string $class) use ($t1Base, $tarifTotal): array {
            $scale = $tarifTotal[$class] / 500_000;
            return array_map(fn ($v) => (int) round($v * $scale), $t1Base);
        };

        // ── T2 persentase (dari penyelesaian, mirip Rekap 3) ────────────────
        $t2Pct = [
            'kma'          => 0.03,
            'waka_yud'     => 0.02,
            'waka_non'     => 0.02,
            'ketua_kamar'  => 0.025,
            'panitera_1'   => 0.03,   // 2 Panitera MA masing-masing 3% (share 6%)
            'panitera_2'   => 0.03,
            'panmud'       => 0.06,
            'tim_penelaah' => 0.06,
            'staf_panmud'  => 0.06,
            'tim_data'     => 0.05,
            'tim_biaya'    => 0.05,
            'tim_penerima' => 0.02,
        ];

        $t2Biaya = function (string $class) use ($t2Pct, $penyel): array {
            $p = $penyel[$class];
            return array_map(fn ($pct) => (int) round($p * $pct), $t2Pct);
        };

        // ── Row & total builders ────────────────────────────────────────────
        $makeRow = function (int $no, string $nama, string $jabatan, int $jml, int $biaya, string $pajak): array {
            $jumlah = $jml * $biaya;
            $pph15  = $pajak === 'pph15' ? (int) round($jumlah * 0.15) : 0;
            $pph5   = $pajak === 'pph5'  ? (int) round($jumlah * 0.05) : 0;
            return [
                'no'          => $no,
                'nama'        => $nama,
                'jabatan'     => $jabatan,
                'jml_perkara' => $jml,
                'biaya'       => $biaya,
                'jml_biaya'   => $jumlah,
                'pph15'       => $pph15,
                'pph5'        => $pph5,
                'netto'       => $jumlah - $pph15 - $pph5,
            ];
        };

        $makeTotal = fn (array $rows): array => [
            'jml_biaya' => array_sum(array_column($rows, 'jml_biaya')),
            'pph15'     => array_sum(array_column($rows, 'pph15')),
            'pph5'      => array_sum(array_column($rows, 'pph5')),
            'netto'     => array_sum(array_column($rows, 'netto')),
        ];

        $wrapBlock = fn (string $title, string $tim, int $jml, array $rows): array => [
            'title'       => $title,
            'tim'         => $tim,
            'jml_perkara' => $jml,
            'rows'        => $rows,
            'total'       => $makeTotal($rows),
        ];

        // ── T1 block builder ────────────────────────────────────────────────
        $buildT1 = function (
            string $title,
            int    $jml,
            string $class,
            string $stafPanmud = 'TUIN, SH., MH.'
        ) use ($t1Biaya, $makeRow, $wrapBlock): array {
            $b    = $t1Biaya($class);
            $rows = [
                $makeRow(1, 'Prof. Dr. H. SUNARTO, S.H., M.H.',          'KETUA MAHKAMAH AGUNG SELAKU PENANGGUNG JAWAB BIAYA PROSES', $jml, $b['kma'],         'pph15'),
                $makeRow(2, 'Dr. HERU PRAMONO, S.H., M.Hum.',             'PANITERA SELAKU PENANGGUNG JAWAB MINUTASI',                  $jml, $b['panitera'],   'pph15'),
                $makeRow(3, 'ENNID HASANUDDIN',                            'PANITERA MUDA PERDATA UMUM',                                 $jml, $b['panmud'],     'pph15'),
                $makeRow(4, 'HARIAWAN PURBUDI, SH., MH.',                  'STAF PENELAAH',                                              $jml, $b['penelaah'],   'pph15'),
                $makeRow(5, $stafPanmud,                                    'STAF PANITERA MUDA',                                         $jml, $b['staf_panmud'],'pph15'),
                $makeRow(6, 'ASEP NURSOBAH, S.Ag., M.H.',                  'STAF PENUNJANG/OPERATOR PADA KEPANITERAAN',                  $jml, $b['operator'],   'pph5'),
                $makeRow(7, 'ST. KRIS NUGROHO, S.H., M.H.',                'STAFF PPK',                                                  $jml, $b['ppk'],        'pph15'),
            ];
            return $wrapBlock($title, 'T1', $jml, $rows);
        };

        // ── T2 block builder ────────────────────────────────────────────────
        $buildT2 = function (
            string $title,
            int    $jml,
            string $class,
            string $stafPanmud  = 'TUIN, SH., MH.',
            string $stafLabel   = 'STAF PANITERA MUDA PERKARA'
        ) use ($t2Biaya, $makeRow, $wrapBlock): array {
            $b    = $t2Biaya($class);
            $rows = [
                $makeRow(1,  'Prof. Dr. H. SUNARTO, S.H., M.H.',             'KETUA MAHKAMAH AGUNG',                                            $jml, $b['kma'],          'pph15'),
                $makeRow(2,  'SUHARTO, S.H., M.HUM.',                         'WAKIL KETUA MA BIDANG YUDISIAL',                                  $jml, $b['waka_yud'],     'pph15'),
                $makeRow(3,  'Dr. H. DWIARSO BUDI SANTIARTO, S.H., M.HUM.',  'WAKIL KETUA MA BIDANG NON YUDISIAL',                              $jml, $b['waka_non'],     'pph15'),
                $makeRow(4,  'I GUSTI AGUNG SUMANATHA, S.H., M.H.',           'KETUA KAMAR',                                                     $jml, $b['ketua_kamar'],  'pph15'),
                $makeRow(5,  'Dr. HERU PRAMONO, S.H., M.Hum.',                'PANITERA MAHKAMAH AGUNG',                                         $jml, $b['panitera_1'],  'pph15'),
                $makeRow(6,  'Dr. SUDHARMAWATININGSIH, S.H., M.Hum.',         'PANITERA MAHKAMAH AGUNG',                                         $jml, $b['panitera_2'],  'pph15'),
                $makeRow(7,  'ENNID HASANUDDIN',                               'PANITERA MUDA PERKARA',                                           $jml, $b['panmud'],      'pph15'),
                $makeRow(8,  'HARIAWAN PURBUDI, SH., MH.',                    'TIM PENELAAH KELENGKAPAN/FORMALITAS BERKAS',                      $jml, $b['tim_penelaah'], 'pph15'),
                $makeRow(9,  $stafPanmud,                                      $stafLabel,                                                        $jml, $b['staf_panmud'], 'pph15'),
                $makeRow(10, 'ASEP NURSOBAH, S.Ag., M.H.',                    'TIM PENDUKUNG PENGOLAH DATA, PELAPORAN DAN SISTEM INFORMASI',     $jml, $b['tim_data'],    'pph15'),
                $makeRow(11, 'ST. KRIS NUGROHO, S.H., M.H.',                  'TIM PENGELOLA BIAYA PROSES',                                      $jml, $b['tim_biaya'],   'pph15'),
                $makeRow(12, 'Dr. H. IYUS SURYANA, S.H., M.H.',               'TIM PENERIMA BERKAS',                                             $jml, $b['tim_penerima'],'pph15'),
            ];
            return $wrapBlock($title, 'T2', $jml, $rows);
        };

        $blocks = [];

        // ════════════════════════════════════════════════════════════════════
        // A. PERDATA UMUM (T1 + T2, pisah Kasasi & PK)
        // ════════════════════════════════════════════════════════════════════
        $jmlKasasiU = $countRows('kasasi-pdt-umum');
        $jmlPKU     = $countRows('pk-pdt-umum');

        $blocks[] = $buildT1('KASASI PERDATA UMUM',   $jmlKasasiU, 'kasasi_500');
        $blocks[] = $buildT2('KASASI PERDATA UMUM',   $jmlKasasiU, 'kasasi_500');
        $blocks[] = $buildT1('PK PERDATA UMUM',       $jmlPKU,     'pk_250');
        $blocks[] = $buildT2('PK PERDATA UMUM',       $jmlPKU,     'pk_250');

        // ════════════════════════════════════════════════════════════════════
        // B. PERDATA KHUSUS — satu tabel per sub-klasifikasi (T2 only)
        //    KPPU diabaikan, HKI+HAKI digabung, Kepailitan+PKPU digabung
        // ════════════════════════════════════════════════════════════════════
        $stafKhusus = [
            'PHI'        => ['staf' => 'RICO MARULI HAPOSAN NAPITUPULU, S.E.', 'klas' => ['PHI'],       'tarif_k' => 'kasasi_500',   'tarif_pk' => 'pk_250'],
            'ARBITRASE'  => ['staf' => 'PETRUS SIAN EDVANSA, S.H.',            'klas' => ['ARBITRASE'], 'tarif_k' => 'kasasi_500',   'tarif_pk' => 'pk_250'],
            'PARPOL'     => ['staf' => 'HJ. YUNI WANTI, SH.',                  'klas' => ['PARPOL'],    'tarif_k' => 'kasasi_500',   'tarif_pk' => 'pk_250'],
            'BPSK'       => ['staf' => 'PETRUS SIAN EDVANSA, S.H.',            'klas' => ['BPSK'],      'tarif_k' => 'kasasi_500',   'tarif_pk' => 'pk_250'],
            'KIP'        => ['staf' => 'HJ. YUNI WANTI, SH.',                  'klas' => ['KIP'],       'tarif_k' => 'kasasi_500',   'tarif_pk' => 'pk_250'],
            'HKI'        => ['staf' => 'LOLITA ESGHATRA PURBASARI, SH.',       'klas' => ['HKI'],       'tarif_k' => 'kasasi_niaga', 'tarif_pk' => 'pk_niaga',  'label' => 'HAKI / HKI'],
            'KEPAILITAN' => ['staf' => 'HJ. YUNI WANTI, SH.',                  'klas' => ['KEPAILITAN'],'tarif_k' => 'kasasi_niaga', 'tarif_pk' => 'pk_niaga',  'label' => 'KEPAILITAN / PKPU'],
        ];

        foreach ($stafKhusus as $key => $def) {
            $label = $def['label'] ?? $key;
            $jmlK  = $countRowsMulti('kasasi-pdt-khusus', $def['klas']);
            $jmlPK = $countRowsMulti('pk-pdt-khusus',     $def['klas']);

            $blocks[] = $buildT2("KASASI PERDATA KHUSUS {$label}", $jmlK,  $def['tarif_k'],  $def['staf']);
            $blocks[] = $buildT2("PK PERDATA KHUSUS {$label}",     $jmlPK, $def['tarif_pk'], $def['staf']);
        }

        // ════════════════════════════════════════════════════════════════════
        // C. PERDATA AGAMA (tanpa tim, staf = WASIYEM)
        // ════════════════════════════════════════════════════════════════════
        $jmlKasasiAg = $countRows('kasasi-pdt-agama');
        $jmlPKAg     = $countRows('pk-pdt-agama');

        $blocks[] = $buildT2('KASASI PERDATA AGAMA', $jmlKasasiAg, 'kasasi_500', 'WASIYEM', 'STAF PANITERA MUDA PERKARA');
        $blocks[] = $buildT2('PK PERDATA AGAMA',     $jmlPKAg,     'pk_250',     'WASIYEM', 'STAF PANITERA MUDA PERKARA');

        // ════════════════════════════════════════════════════════════════════
        // D. TUN / P-HUM / P-KHS / PK TUN / PK PAJAK (staf = ARIF DONOVAN)
        // ════════════════════════════════════════════════════════════════════
        $tunDefs = [
            ['title' => 'KASASI TUN', 'id' => 'kasasi-tun', 'class' => 'kasasi_500'],
            ['title' => 'P-HUM',      'id' => 'phum',       'class' => 'phum'],
            ['title' => 'P-KHS',      'id' => 'pkhs',       'class' => 'phum'],
            ['title' => 'PK TUN',     'id' => 'pk-tun',     'class' => 'pk_250'],
            ['title' => 'PK PAJAK',   'id' => 'pk-pajak',   'class' => 'pk_250'],
        ];

        foreach ($tunDefs as $def) {
            $jml      = $countRows($def['id']);
            $blocks[] = $buildT2($def['title'], $jml, $def['class'], 'ARIF DONOVAN, S.H.', 'STAF PANITERA MUDA PERKARA');
        }

        return $blocks;
    }
    /**
     * Hitung OP STAF honorarium — 27 kategori identik Data Print.
     * total_perkara = jumlah perkara di kategori (tanpa filter usia = Data Print).
     * Setiap row = 1 Panitera Pengganti dengan jumlah kemunculan nama-nya.
     */
    public function computeOpStafBlocks(array $categories): array
    {
        $keyed = [];
        foreach ($categories as $cat) {
            if (isset($cat['id'])) $keyed[$cat['id']] = $cat;
        }

        $tc = $this->tarif['tarif_cek'];
        $penyelesaian = [
            'kasasi_500'   => $tc['kasasi_pdt']['pph15']        + $tc['kasasi_pdt']['pph5'],
            'kasasi_niaga' => $tc['kasasi_pdtsus_5jt']['pph15'] + $tc['kasasi_pdtsus_5jt']['pph5'],
            'pk_250'       => $tc['pk_pdt']['pph15']            + $tc['pk_pdt']['pph5'],
            'phum'         => $tc['phum']['pph15']              + $tc['phum']['pph5'],
            'pk_niaga'     => $tc['pk_pdtsus_10jt']['pph15']    + $tc['pk_pdtsus_10jt']['pph5'],
            'pkhs'         => $tc['pkhs']['pph15']              + $tc['pkhs']['pph5'],
        ];

        $countPP = function (array $rows): array {
            $counts = [];
            foreach ($rows as $row) {
                $nama = '';
                foreach ($row as $k => $v) {
                    if (strtoupper(trim($k)) === 'NAMA PANITERA PENGGANTI') {
                        $nama = trim((string) $v);
                        break;
                    }
                }
                if ($nama === '' || $nama === '-') continue;
                $counts[$nama] = ($counts[$nama] ?? 0) + 1;
            }
            ksort($counts);
            return $counts;
        };

        $jenisDefs = [
            ['label' => 'KASASI PERDATA UMUM',                           'tarif' => 'kasasi_500',   'ids' => ['kasasi-pdt-umum']],
            ['label' => 'PENINJAUAN KEMBALI PERDATA UMUM',               'tarif' => 'pk_250',       'ids' => ['pk-pdt-umum']],
            ['label' => 'KASASI PERDATA KHUSUS',                         'tarif' => 'kasasi_500',   'ids' => ['kasasi-pdt-khusus']],
            ['label' => 'KASASI PERDATA KHUSUS (PHI)',                   'tarif' => 'kasasi_500',   'ids' => ['kasasi-pdt-khusus-PHI']],
            ['label' => 'KASASI PERDATA KHUSUS (HKI)',                   'tarif' => 'kasasi_niaga', 'ids' => ['kasasi-pdt-khusus-HKI']],
            ['label' => 'KASASI PERDATA KHUSUS (KEPAILITAN)',             'tarif' => 'kasasi_niaga', 'ids' => ['kasasi-pdt-khusus-KEPAILITAN']],
            ['label' => 'KASASI PERDATA KHUSUS (ARBITRASE)',              'tarif' => 'kasasi_500',   'ids' => ['kasasi-pdt-khusus-ARBITRASE']],
            ['label' => 'KASASI PERDATA KHUSUS (PARPOL)',                 'tarif' => 'kasasi_500',   'ids' => ['kasasi-pdt-khusus-PARPOL']],
            ['label' => 'KASASI PERDATA KHUSUS (KPPU)',                   'tarif' => 'kasasi_500',   'ids' => ['kasasi-pdt-khusus-KPPU']],
            ['label' => 'KASASI PERDATA KHUSUS (BPSK)',                   'tarif' => 'kasasi_500',   'ids' => ['kasasi-pdt-khusus-BPSK']],
            ['label' => 'KASASI PERDATA KHUSUS (KIP)',                    'tarif' => 'kasasi_500',   'ids' => ['kasasi-pdt-khusus-KIP']],
            ['label' => 'PENINJAUAN KEMBALI PERDATA KHUSUS',              'tarif' => 'pk_250',       'ids' => ['pk-pdt-khusus']],
            ['label' => 'PENINJAUAN KEMBALI PERDATA KHUSUS (PHI)',        'tarif' => 'pk_250',       'ids' => ['pk-pdt-khusus-PHI']],
            ['label' => 'PENINJAUAN KEMBALI PERDATA KHUSUS (HKI)',        'tarif' => 'pk_niaga',     'ids' => ['pk-pdt-khusus-HKI']],
            ['label' => 'PENINJAUAN KEMBALI PERDATA KHUSUS (KEPAILITAN)', 'tarif' => 'pk_niaga',     'ids' => ['pk-pdt-khusus-KEPAILITAN']],
            ['label' => 'PENINJAUAN KEMBALI PERDATA KHUSUS (ARBITRASE)',  'tarif' => 'pk_250',       'ids' => ['pk-pdt-khusus-ARBITRASE']],
            ['label' => 'PENINJAUAN KEMBALI PERDATA KHUSUS (PARPOL)',     'tarif' => 'pk_250',       'ids' => ['pk-pdt-khusus-PARPOL']],
            ['label' => 'PENINJAUAN KEMBALI PERDATA KHUSUS (KPPU)',       'tarif' => 'pk_250',       'ids' => ['pk-pdt-khusus-KPPU']],
            ['label' => 'PENINJAUAN KEMBALI PERDATA KHUSUS (BPSK)',       'tarif' => 'pk_250',       'ids' => ['pk-pdt-khusus-BPSK']],
            ['label' => 'PENINJAUAN KEMBALI PERDATA KHUSUS (KIP)',        'tarif' => 'pk_250',       'ids' => ['pk-pdt-khusus-KIP']],
            ['label' => 'KASASI PERDATA AGAMA',                           'tarif' => 'kasasi_500',   'ids' => ['kasasi-pdt-agama']],
            ['label' => 'PENINJAUAN KEMBALI PERDATA AGAMA',               'tarif' => 'pk_250',       'ids' => ['pk-pdt-agama']],
            ['label' => 'KASASI TATA USAHA NEGARA (K-TUN)',               'tarif' => 'kasasi_500',   'ids' => ['kasasi-tun']],
            ['label' => 'P-HUM (PERMOHONAN HAK UJI MATERIL)',             'tarif' => 'phum',         'ids' => ['phum']],
            ['label' => 'P-KHS (PERMOHONAN HAK UJI PENDAPAT)',            'tarif' => 'pkhs',         'ids' => ['pkhs']],
            ['label' => 'PENINJAUAN KEMBALI TATA USAHA NEGARA (PK-TUN)', 'tarif' => 'pk_250',       'ids' => ['pk-tun']],
            ['label' => 'PENINJAUAN KEMBALI PAJAK (PK-PJK)',              'tarif' => 'pk_250',       'ids' => ['pk-pajak']],
        ];

        $blocks = [];
        foreach ($jenisDefs as $def) {
            $allRows = [];
            foreach ($def['ids'] as $id) {
                if (!isset($keyed[$id])) continue;
                $allRows = array_merge($allRows, $keyed[$id]['data'] ?? []);
            }

            $totalPerkara  = count($allRows);
            $biayaOperator = (int) round($penyelesaian[$def['tarif']] * 0.05);
            $ppCounts      = $countPP($allRows);

            $rows = []; $no = 1; $gBruto = $gPph5 = $gNetto = 0;
            foreach ($ppCounts as $nama => $jml) {
                $bruto  = $jml * $biayaOperator;
                $pph5   = (int) round($bruto * 0.05);
                $netto  = $bruto - $pph5;
                $gBruto += $bruto; $gPph5 += $pph5; $gNetto += $netto;
                $rows[] = ['no' => $no++, 'nama' => $nama, 'jml' => $jml,
                           'tarif' => $biayaOperator, 'bruto' => $bruto,
                           'pph5' => $pph5, 'netto' => $netto];
            }

            // Placeholder agar semua 27 blok selalu tampil (0 perkara = 1 baris kosong)
            if (empty($rows)) {
                $rows[] = ['no' => 1, 'nama' => '', 'jml' => 0,
                           'tarif' => $biayaOperator, 'bruto' => 0, 'pph5' => 0, 'netto' => 0];
            }

            $blocks[] = [
                'title'         => $def['label'],
                'tarif'         => $biayaOperator,
                'total_perkara' => $totalPerkara,
                'rows'          => $rows,
                'total'         => ['jml'   => array_sum(array_column($rows, 'jml')),
                                    'bruto' => $gBruto, 'pph5' => $gPph5, 'netto' => $gNetto],
            ];
        }
        return $blocks;
    }
}
