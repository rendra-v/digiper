<?php

/**
 * Konfigurasi tarif biaya perkara DIGIPER.
 *
 * Semua nilai di sini fixed / tidak berubah antar periode.
 * Diextract dari file Excel template Mahkamah Agung.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Biaya Perkara per Jenis (dari Rekap Keseluruhan kolom G & L)
    |--------------------------------------------------------------------------
    | Biaya total per perkara, dipecah antara Kasasi dan PK.
    */
    'biaya_perkara' => [
        // Kasasi = kolom G, PK = kolom L di Rekap Keseluruhan
        'PERDATA'     => ['kasasi' => 500000,  'pk' => 2500000],
        'PHI'         => ['kasasi' => 500000,  'pk' => 0],
        'HKI'         => ['kasasi' => 5000000, 'pk' => 10000000],
        'KEPAILITAN'  => ['kasasi' => 5000000, 'pk' => 10000000],
        'ARBITRASE'   => ['kasasi' => 500000,  'pk' => 2500000],
        'PARPOL'      => ['kasasi' => 500000,  'pk' => 2500000],
        'KPPU'        => ['kasasi' => 500000,  'pk' => 2500000],
        'BPSK'        => ['kasasi' => 500000,  'pk' => 2500000],
        'KIP'         => ['kasasi' => 500000,  'pk' => 2500000],
        'AGAMA'       => ['kasasi' => 500000,  'pk' => 2500000],
        'JINAYAT'     => ['kasasi' => 0,       'pk' => 0],
        'TUN'         => ['kasasi' => 500000,  'pk' => 2500000],
        'PAJAK'       => ['kasasi' => 0,       'pk' => 2500000],
        'HUM'         => ['kasasi' => 1000000, 'pk' => 0],
        'KHUSUS'      => ['kasasi' => 1000000, 'pk' => 0],
    ],

    /*
    |--------------------------------------------------------------------------
    | Biaya Sheet "cek" — Tarif PPH 15% dan PPH 5% per jenis
    |--------------------------------------------------------------------------
    | Di sheet "cek", setiap jenis perkara punya 2 baris:
    |   - Baris PPH 15%: biaya utama (yang dikenakan PPH 15%)
    |   - Baris PPH 5%:  biaya operator (yang dikenakan PPH 5%)
    |
    | Tarif "500rb" = pph15=210000 + pph5=40000 = 250000 (honorarium)
    | Total biaya perkara 500rb = 250000 + komponen biaya (materai, redaksi, dll)
    |
    | Pola tarif berdasarkan total biaya perkara:
    |   500rb  → pph15=210000, pph5=40000
    |   1jt    → pph15=420000, pph5=80000
    |   2.5jt  → pph15=1117200, pph5=212800
    |   5jt    → pph15=2381400, pph5=453600
    |   10jt   → pph15=4481400, pph5=853600
    */
    'tarif_cek' => [
        // key = identifier jenis di sheet cek
        // Kasasi Perdata, Agama, TUN (500rb)
        'kasasi_pdt'       => ['pph15' => 210000,  'pph5' => 40000],
        'kasasi_ag'        => ['pph15' => 210000,  'pph5' => 40000],
        'kasasi_tun'       => ['pph15' => 210000,  'pph5' => 40000],
        // Kasasi Perdata Khusus 500rb (PHI, ARBITRASE, PARPOL, KPPU, BPSK, KIP)
        'kasasi_pdtsus_500' => ['pph15' => 210000,  'pph5' => 40000],
        // Kasasi Perdata Khusus 5jt (HKI, KEPAILITAN)
        'kasasi_pdtsus_5jt' => ['pph15' => 2381400, 'pph5' => 453600],

        // PK Perdata (2.5jt)
        'pk_pdt'           => ['pph15' => 1117200, 'pph5' => 212800],
        'pk_ag'            => ['pph15' => 1117200, 'pph5' => 212800],
        'pk_tun'           => ['pph15' => 1117200, 'pph5' => 212800],
        'pk_pajak'         => ['pph15' => 1117200, 'pph5' => 212800],
        // PK Perdata Khusus 2.5jt (PHI, ARBITRASE, PARPOL, KPPU, BPSK, KIP)
        'pk_pdtsus_2.5jt'  => ['pph15' => 1117200, 'pph5' => 212800],
        // PK Perdata Khusus 10jt (HKI, KEPAILITAN)
        'pk_pdtsus_10jt'   => ['pph15' => 4481400, 'pph5' => 853600],

        // P-HUM (1jt)
        'phum'             => ['pph15' => 420000,  'pph5' => 80000],
        // P-KHS (1jt)
        'pkhs'             => ['pph15' => 420000,  'pph5' => 80000],
    ],

    /*
    |--------------------------------------------------------------------------
    | Komponen Biaya (Tabel Kanan Rekap Keseluruhan, Q-AY)
    |--------------------------------------------------------------------------
    */
    'komponen_biaya' => [
        'materai'          => 10000,
        'redaksi'          => 10000,
        'atk'              => 50000,
        'fotocopy_kasasi'  => 20000,  // Kasasi
        'fotocopy_pk'      => 25000,  // PK
    ],

    /*
    |--------------------------------------------------------------------------
    | Honorarium per perkara (sheet TIM, Kepaniteraan, OP-STAF, Pemilah)
    |--------------------------------------------------------------------------
    */
    'honorarium_per_perkara' => [
        'tim'           => 25000,   // Majelis Hakim, Panmud, PP per perkara
        'kepaniteraan'  => 25000,   // Kepaniteraan per perkara
        'op_staf'       => 12500,   // Operator/Staf per perkara
        'pemilah'       => 15000,   // Hakim Pemilah per perkara
    ],

    /*
    |--------------------------------------------------------------------------
    | PPH Rates
    |--------------------------------------------------------------------------
    */
    'pph' => [
        'pph15' => 0.15,
        'pph5'  => 0.05,
    ],

    /*
    |--------------------------------------------------------------------------
    | Persentase Jabatan (Tabel Bawah Rekap Keseluruhan)
    |--------------------------------------------------------------------------
    */
    'jabatan_persentase' => [
        ['jabatan' => 'KETUA MAHKAMAH AGUNG',                 'persen' => 0.03],
        ['jabatan' => 'WAKIL KETUA MA BIDANG YUDISIAL',       'persen' => 0.02],
        ['jabatan' => 'WAKIL KETUA MA BIDANG NON YUDISIAL',   'persen' => 0.02],
        // TODO: extract remaining jabatan rows from Excel
    ],

    /*
    |--------------------------------------------------------------------------
    | Pejabat Tetap (Footer/Tanda Tangan)
    |--------------------------------------------------------------------------
    */
    'pejabat' => [
        'bendahara'       => 'FARIDA, S.H.',
        'kuasa_pengelola'  => 'ASEP NURSOBAH, S.Ag., M.H.',
        'ppk'             => 'ST. KRIS NUGROHO, S.H., M.H.',
    ],
];
