# LAPORAN MAGANG / KERJA PRAKTIK

---

## COVER / HALAMAN JUDUL

**PEMBANGUNAN SISTEM REKAPITULASI BIAYA PERKARA TERINTEGRASI (DIGIPER) BERBASIS WEB MENGGUNAKAN FRAMEWORK LARAVEL DI MAHKAMAH AGUNG**

*Untuk Memenuhi Salah Satu Syarat Penyelesaian Program S-1 Teknik Informatika*

**Oleh:**  
[Nama Mahasiswa]  
NPM. [NPM Mahasiswa]  

**PROGRAM STUDI S-1 TEKNIK INFORMATIKA**  
**FAKULTAS TEKNOLOGI INFORMASI**  
**UNIVERSITAS YARSI**  
**JAKARTA**  
**2026**  

---

## LEMBAR PENGESAHAN LAPORAN MAGANG

**LAPORAN MAGANG DENGAN JUDUL:**  
**PEMBANGUNAN SISTEM REKAPITULASI BIAYA PERKARA TERINTEGRASI (DIGIPER) BERBASIS WEB MENGGUNAKAN FRAMEWORK LARAVEL DI MAHKAMAH AGUNG**

Laporan Magang ini telah disetujui, diperiksa, dan disahkan pada presentasi Laporan Magang pada tanggal: ………………………

<br>

**Mengetahui,**

| Pembimbing Magang PS-TI | Pembimbing Magang Instansi |
| :---: | :---: |
| <br><br><br>____________________<br>NIK: …………………… | <br><br><br>____________________<br>NIP: …………………… |

<br>

| Kepala Program Studi TI | Koordinator Magang |
| :---: | :---: |
| <br><br><br>**Elan Suherlan, M.Kom**<br>NIK: …………………… | <br><br><br>**Muhamad Fathurahman, M.Kom**<br>NIK: 531141116036 |

---

## KATA PENGANTAR

Puji dan syukur penulis panjatkan kehadirat Allah SWT karena atas rahmat dan karunia-Nya, penulis dapat menyelesaikan Kegiatan Magang (Kerja Praktik) beserta penyusunan laporan magang yang berjudul **"Pembangunan Sistem Rekapitulasi Biaya Perkara Terintegrasi (DIGIPER) Berbasis Web Menggunakan Framework Laravel di Mahkamah Agung"**.

Laporan ini disusun guna memenuhi salah satu syarat kelulusan dan penyelesaian Program Studi S-1 Teknik Informatika, Fakultas Teknologi Informasi, Universitas Yarsi. 

Dalam pelaksanaan magang hingga penyusunan laporan ini, penulis mendapatkan bantuan, bimbingan, serta dukungan dari berbagai pihak. Oleh karena itu, pada kesempatan ini penulis mengucapkan terima kasih kepada:
1. Bapak Elan Suherlan, M.Kom selaku Kepala Program Studi S-1 Teknik Informatika Universitas Yarsi.
2. Bapak Muhamad Fathurahman, M.Kom selaku Koordinator Magang Prodi S-1 Teknik Informatika Universitas Yarsi.
3. [Nama Dosen Pembimbing] selaku Dosen Pembimbing Magang dari Universitas Yarsi.
4. [Nama Pembimbing Lapangan] selaku Pembimbing Lapangan di Mahkamah Agung Republik Indonesia.
5. Seluruh jajaran Pimpinan, Hakim Agung, Pejabat Kepaniteraan, dan Staf Mahkamah Agung yang telah memberikan kesempatan serta bimbingan selama kegiatan magang berlangsung.
6. Orang tua dan keluarga terkasih atas doa, motivasi, dan dukungan moril maupun materil.
7. Rekan-rekan mahasiswa Teknik Informatika Universitas Yarsi atas kerja sama dan dukungannya.

Penulis menyadari bahwa dalam penyusunan laporan ini masih terdapat kekurangan. Oleh karena itu, kritik dan saran yang membangun sangat penulis harapkan demi perbaikan di masa mendatang. Semoga laporan ini dapat memberikan manfaat bagi pembaca dan pihak yang membutuhkan.

Jakarta, ……………………… 2026

<br>

**Penulis**

---

## DAFTAR ISI

- **LEMBAR PENGESAHAN LAPORAN MAGANG**
- **KATA PENGANTAR**
- **DAFTAR ISI**
- **DAFTAR TABEL**
- **DAFTAR GAMBAR**
- **BAB I PENDAHULUAN**
  - 1.1 Latar Belakang
  - 1.2 Perumusan Masalah
  - 1.3 Tujuan Magang
  - 1.4 Batasan Masalah
  - 1.5 Sistematika Penulisan
  - 1.6 Waktu Pelaksanaan Magang
- **BAB II LANDASAN TEORI**
  - 2.1 Metode Pengembangan Software (Prototyping)
  - 2.2 Framework Web Laravel & PHP 8.4
  - 2.3 Database Management System (MySQL)
  - 2.4 Tailwind CSS & Responsive User Interface
  - 2.5 Pengolahan Berkas Spreadsheet / Excel Data Parsing
- **BAB III PELAKSANAAN KERJA PRAKTIK**
  - 3.1 Deskripsi Umum Tugas Magang
  - 3.2 Penjelasan Aktivitas Magang (Log Book)
  - 3.3 Hasil Pengerjaan Tugas Magang (Aplikasi DIGIPER)
- **BAB IV HASIL PEMBELAJARAN**
  - 4.1 Manfaat Magang yang Didapat
  - 4.2 Penerapan Ilmu Dalam Magang
- **BAB V KESIMPULAN DAN SARAN TEKNIS**
  - 5.1 Kesimpulan
  - 5.2 Saran Teknis
- **DAFTAR PUSTAKA**
- **LAMPIRAN**
  - 1. Daftar Hadir Magang
  - 2. Laporan Kegiatan Magang Logbook
  - 3. Penilaian Magang Mahasiswa
  - 4. Profil Perusahaan / Instansi (Mahkamah Agung RI)

---

## DAFTAR TABEL

- **Tabel 1.** Rangkuman Log Book Aktivitas Magang per Pekan
- **Tabel 2.** Daftar Fitur Utama Sistem Rekapitulasi DIGIPER yang Telah Diterapkan
- **Tabel 3.** Pemecahan Komponen Biaya Perkara Standar
- **Tabel 4.** Skema Distribusi Honor per Role & Majelis Hakim
- **Tabel 5.** Matriks Penerapan Ilmu Perkuliahan dalam Kegiatan Magang

---

## DAFTAR GAMBAR

- **Gambar 1.** Alur Tahapan Metode Pengembangan Software Prototyping
- **Gambar 2.** High-Level Architecture Sistem DIGIPER (Laravel 12 + Tailwind CSS + MySQL)
- **Gambar 3.** Entity Relationship Diagram (ERD) Sistem DIGIPER
- **Gambar 4.** Tampilan Halaman Utama / Upload Data Excel Perkara (Drag & Drop)
- **Gambar 5.** Tampilan Dashboard Preview Data & Filtering Status Biaya (Threshold 90 Hari)
- **Gambar 6.** Tampilan Detail Breakdown Biaya Perkara & Distribusi Honor Hakim
- **Gambar 7.** Tampilan Rekapitulasi Total & Split Export Laporan (Lembar Kepaniteraan & Pimpinan)

---

# BAB I: PENDAHULUAN

### 1.1 Latar Belakang
Mahkamah Agung Republik Indonesia mengelola volume perkara yang sangat besar dalam berbagai bidang yurisdiksi, meliputi Perdata, Peninjauan Kembali (PK), Kasasi, Tata Usaha Negara (TUN), Agama, Pajak, dan Perdata Khusus. Dalam setiap penanganan perkara putus, terdapat kewajiban akuntabilitas keuangan terkait pengelolaan dan pemecahan biaya perkara, yang mencakup biaya materai, redaksi, Alat Tulis Kantor (ATK), biaya sidang, operasional, hingga pengalokasian honorarium bagi Majelis Hakim, Panitera Pengganti (PP), Operator, dan Hakim Pemilah.

Sebelumnya, pengolahan data rekapitulasi biaya perkara dan pendistribusian honorarium dilakukan secara parsial dengan spreadsheet berkala yang membutuhkan proses input manual, *vlookup* manual, serta verifikasi kelayakan usia perkara secara mandiri. Hal ini berpotensi menimbulkan kendala seperti tingginya kerentanan kesalahan hitung (*human error*), ketidaksinkronan data antar-unit, serta kelambatan dalam penyusunan laporan keuangan untuk keperluan audit Laporan Keuangan (LK) Badan Pemeriksa Keuangan (BPK).

Untuk mengatasinya, dikembangkan **Sistem Terintegrasi Rekapitulasi Biaya Perkara (DIGIPER)** berbasis web. Sistem ini dirancang untuk mengotomatisasi proses import data berkas Excel Mahkamah Agung, melakukan validasi usia perkara otomatis (threshold $\ge 90$ hari), mengaplikasikan tarif default per jenis perkara, memecah komponen biaya, hingga mendistribusikan honorarium berdasarkan tingkat senioritas Hakim Agung secara presisi, akurat, dan transparan. Melalui kegiatan magang di Mahkamah Agung, peserta magang berkontribusi dalam membangun dan mengimplementasikan fitur-fitur utama pada sistem DIGIPER.

---

### 1.2 Perumusan Masalah
Berdasarkan latar belakang di atas, perumusan masalah dalam kegiatan magang ini adalah:
1. Bagaimana merancang dan mengimplementasikan modul import berkas Excel dengan fitur *drag and drop* serta validasi kolom otomatis pada aplikasi DIGIPER?
2. Bagaimana membangun logika filter otomatis untuk memverifikasi kelayakan usia perkara berdasarkan ambang batas 90 hari?
3. Bagaimana mengotomatisasi skema pengelompokan jenis perkara, pengaplikasian tarif default, serta pemecahan komponen biaya secara presisi?
4. Bagaimana menghitung dan mendistribusikan honorarium untuk Majelis Hakim (P1, P2, P3), Panitera Pengganti, Operator, dan Hakim Pemilah sesuai hierarki senioritas?
5. Bagaimana menghasilkan laporan rekapitulasi terintegrasi yang dapat dipisah (*split worksheet*) untuk kebutuhan Majelis/Kepaniteraan dan Pimpinan?

---

### 1.3 Tujuan Magang
Tujuan dari kegiatan magang ini adalah:
1. Membangun aplikasi web DIGIPER menggunakan framework Laravel 12, PHP 8.4, Tailwind CSS, dan database MySQL.
2. Mengimplementasikan fitur otomatisasi import data Excel, pemecahan komponen biaya, dan perhitungan distribusi honor secara presisi.
3. Menyediakan dashboard interaktif untuk memonitor status biaya perkara, statistik pengelompokan jenis perkara, serta rekapitulasi honorarium.
4. Menerapkan disiplin ilmu Rekayasa Perangkat Lunak, Pemrograman Web, Pemrograman Berorientasi Objek, dan Basadata yang diperoleh di bangku kuliah pada dunia kerja nyata.

---

### 1.4 Batasan Masalah
Agar pembahasan laporan magang ini terfokus, maka diberikan batasan masalah sebagai berikut:
1. Sistem dikembangkan berbasis web menggunakan framework **Laravel 12**, **PHP 8.4**, dan styling **Tailwind CSS v4**.
2. Sumber data utama yang diolah berupa berkas data transaksi / info perkara berformat Excel (`.xlsx`, `.xls`).
3. Kriteria filter kelayakan biaya perkara berfokus pada usia perkara $\ge 90$ hari (selisih tanggal putus dikurangi tanggal masuk perkara).
4. Pengelompokan jenis perkara mencakup 8 kategori utama: Kasasi, Peninjauan Kembali (PK), TUN, Perdata, Agama, Pajak, Hukum/Lingkungan (Hum), dan Perdata Khusus.
5. Pembagian honorarium berorientasi pada aturan struktur internal Mahkamah Agung untuk Hakim Agung (Ketua Majelis, Anggota 1/2/3), Panitera Pengganti, Operator, dan Hakim Pemilah.

---

### 1.5 Sistematika Penulisan
Sistematika penulisan laporan magang ini dibagi menjadi lima bab sebagai berikut:
- **BAB I PENDAHULUAN**: Berisi latar belakang, perumusan masalah, tujuan magang, batasan masalah, sistematika penulisan, serta waktu pelaksanaan magang.
- **BAB II LANDASAN TEORI**: Menjelaskan konsep dan teknologi pendukung seperti metode Prototyping, Framework Laravel, Database MySQL, Tailwind CSS, serta Excel Parsing.
- **BAB III PELAKSANAAN KERJA PRAKTIK**: Menjelaskan deskripsi tugas magang, logbook kegiatan mingguan, arsitektur sistem, serta hasil pengerjaan fitur aplikasi DIGIPER.
- **BAB IV HASIL PEMBELAJARAN**: Menguraikan manfaat magang yang diperoleh serta relevansi penerapan ilmu perkuliahan dalam penyelesaian tugas magang.
- **BAB V KESIMPULAN DAN SARAN TEKNIS**: Menyajikan kesimpulan akhir hasil magang serta saran teknis pengembangan sistem di masa mendatang.

---

### 1.6 Waktu Pelaksanaan Magang
Kegiatan magang dilaksanakan pada:
- **Tanggal/Periode**: [Tanggal Mulai] s.d. [Tanggal Selesai] 2026 (Durasi: $\pm 3-5$ Bulan)
- **Lokasi**: Mahkamah Agung Republik Indonesia, Jakarta Central.

---

# BAB II: LANDASAN TEORI

### 2.1 Metode Pengembangan Software (Prototyping)
Metode pengembangan perangkat lunak yang diterapkan dalam pembuatan sistem DIGIPER adalah metode **Prototyping**. Metode ini berfokus pada siklus berulang (*iterative development*) di mana prototipe antarmuka dan alur logika dipresentasikan kepada pengguna sejak tahap awal untuk mendapatkan umpan balik langsung.

Tahapan Prototyping meliputi:
1. **Pengumpulan Kebutuhan**: Mengidentifikasi struktur data Excel Mahkamah Agung dan aturan bisnis perhitungan biaya perkara.
2. **Membangun Prototyping**: Merancang UI/UX dashboard, modal import drag-and-drop, serta tabel breakdown biaya.
3. **Evaluasi Prototipe**: Melakukan tinjauan bersama pembimbing lapangan dan calon pengguna (admin/kepaniteraan).
4. **Pengodean Sistem**: Membangun modul backend Laravel, Eloquent ORM, serta pustaka pengolahan Excel.
5. **Pengujian & Evaluasi Sistem**: Melakukan pengujian fungsi perhitungan dan validasi data.

---

### 2.2 Framework Web Laravel & PHP 8.4
Laravel adalah framework web berbasis PHP yang menggunakan arsitektur *Model-View-Controller* (MVC). Pada sistem DIGIPER, Laravel 12 dan PHP 8.4 dimanfaatkan karena menyediakan fitur unggulan:
- **Routing & Middleware**: Mengatur hak akses role Admin, Kepaniteraan, dan Pimpinan.
- **Eloquent ORM**: Mempermudah manipulasi relasi antartabel database (`Perkara`, `Hakim`, `DetailBiayaPerkara`, `DistribusiHonor`).
- **Maatwebsite / FastExcel Integration**: Memungkinkan pembacaan (*parsing*) dan pemrosesan ribuan baris data Excel secara cepat dan efisien.

---

### 2.3 Database Management System (MySQL)
MySQL digunakan sebagai sistem manajemen basis data relasional (*RDBMS*). Struktur data dirancang menggunakan konsep normalisasi untuk memastikan integritas data keuangan dan menghindari redundansi, seperti pemisahan master data Hakim Agung, Master Pejabat, Master Komponen Biaya, serta data Perkara.

---

### 2.4 Tailwind CSS & Responsive User Interface
Tailwind CSS v4 digunakan untuk membangun antarmuka pengguna yang modern, bersih, presisi, dan responsif. Penggunaan utilitas CSS memfasilitasi pembuatan tema terang (*light mode*) dan tema gelap (*dark mode*), visualisasi statistik berbasis kartu (*summary cards*), badge status berwarna, serta tabel data dengan pembacaan yang nyaman.

---

### 2.5 Pengolahan Berkas Spreadsheet / Excel Data Parsing
Proses bisnis utama DIGIPER bergantung pada otomatisasi pengolahan berkas Excel. Algoritma pencocokan data (*Fuzzy Matching & Lookup*) diterapkan untuk memetakan kolom dari file Excel hasil unduhan Mahkamah Agung secara otomatis ke skema tabel basis data tanpa memerlukan konversi manual.

---

# BAB III: PELAKSANAAN KERJA PRAKTIK

### 3.1 Deskripsi Umum Tugas Magang
Selama pelaksanaan magang di Mahkamah Agung, peserta magang bergabung dalam tim pengembang perangkat lunak untuk membangun **Sistem DIGIPER**. Tugas utama yang diamanahkan meliputi:
1. Merancang antarmuka antarmuka pengguna (*User Interface*) untuk halaman upload modal dan dashboard rekapitulasi data.
2. Mengembangkan alur logika otomatisasi import data berkas Excel perkara.
3. Membangun logika kalkulasi usia perkara, penentuan status biaya (ambang batas 90 hari), serta penerapan tarif default.
4. Membangun modul pemecahan biaya perkara ke dalam komponen pecahan (Materai, Redaksi, ATK, Sidang, Operasional) dan distribusi honor (Hakim Agung P1/P2/P3, PP, Operator, Hakim Pemilah).
5. Mengimplementasikan ekspor laporan keuangan terpisah (*split worksheet*) untuk Kepaniteraan dan Pimpinan.

---

### 3.2 Penjelasan Aktivitas Magang (Log Book)

**Tabel 1. Rangkuman Log Book Aktivitas Magang per Pekan**

| Pekan | Rentang Tanggal | Aktivitas / Kegiatan Magang |
| :---: | :--- | :--- |
| **1** | Minggu Ke-1 | • Orientasi lingkungan kerja Mahkamah Agung.<br>• Analisis kebutuhan sistem DIGIPER & pengumpulan sampel format Excel perkara.<br>• Penyusunan dokumen arsitektur dan skema database. |
| **2** | Minggu Ke-2 | • Inisialisasi proyek Laravel 12, PHP 8.4, Tailwind CSS v4, dan MySQL.<br>• Pembuatan struktur tabel database (`perkaras`, `hakims`, `komponen_biayas`, `distribusi_honors`). |
| **3** | Minggu Ke-3 | • Pengembangan halaman utama & modal import Excel *Drag and Drop*.<br>• Pengintegrasian library pengolahan Excel (`Maatwebsite Excel`). |
| **4** | Minggu Ke-4 | • Implementasi algoritma perhitungan usia perkara ($\text{Usia} = |\text{Tgl Putus} - \text{Tgl Masuk}|$).<br>• Penerapan logic filter status biaya (Status "Kena Biaya" jika $\ge 90$ hari). |
| **5** | Minggu Ke-5 | • Pengembangan modul klasifikasi 8 jenis perkara dan penerapan tarif default per jenis.<br>• Implementasi pemecahan biaya ke komponen fixed (Materai, Redaksi) dan persentase (ATK, Sidang). |
| **6** | Minggu Ke-6 | • Pengembangan modul distribusi honorarium ke Majelis Hakim (P1, P2, P3), PP, dan Operator.<br>• Pengintegrasian data Hakim Pemilah secara opsional/terpisah. |
| **7** | Minggu Ke-7 | • Pembuatan Master Data Hakim Agung dengan dukungan *Urutan Senioritas* (Ketua, Wakil, Ketua Kamar, Hakim Agung).<br>• Implementasi Master Data Pejabat Kepaniteraan. |
| **8** | Minggu Ke-8 | • Pembuatan Dashboard Rekap Total secara *real-time* (Auto-Sync data).<br>• Pengembangan fitur pencarian (*fuzzy matching*) nama Hakim. |
| **9** | Minggu Ke-9 | • Implementasi fitur *Split Laporan* ekspor Excel & PDF (Lembar Majelis/Kepaniteraan & Lembar Pimpinan).<br>• Pengaturan fitur *Auto-Hide Zero Values* untuk kerapian laporan. |
| **10**| Minggu Ke-10| • Uji coba integrasi sistem (System Integration Testing) & Perbaikan bug.<br>• Penyusunan dokumentasi teknis sistem dan panduan pengguna (User Guide). |

---

### 3.3 Hasil Pengerjaan Tugas Magang (Aplikasi DIGIPER)

Hasil dari pengerjaan tugas magang adalah aplikasi web **DIGIPER** yang siap digunakan untuk otomatisasi rekapitulasi biaya perkara.

**Tabel 2. Daftar Fitur Utama Sistem Rekapitulasi DIGIPER yang Telah Diterapkan**

| No | Nama Fitur | Deskripsi & Fungsi | Status |
| :---: | :--- | :--- | :---: |
| 1 | **Import Excel Drag & Drop** | Mengunggah berkas Excel (.xlsx/.xls) info perkara dengan visual indikator progres dan auto-mapping kolom. | Selesai |
| 2 | **Auto-Filter Usia Perkara (90 Hari)** | Menghitung otomatis usia perkara ($\ge 90$ hari = Kena Biaya, $< 90$ hari = Belum Kena Biaya). | Selesai |
| 3 | **Filter & Grouping Jenis Perkara** | Mengelompokkan perkara ke dalam 8 kategori (Kasasi, PK, TUN, Perdata, Agama, Pajak, Hum, Perdata Khusus). | Selesai |
| 4 | **Auto Tarif Default & Breakdown Biaya** | Menerapkan tarif default per jenis dan memecah nominal ke komponen Materai, Redaksi, ATK, Sidang, dll. | Selesai |
| 5 | **Distribusi Honor Per Role** | Membagi jatah honorarium ke Majelis Hakim (Ketua, Anggota 1, Anggota 2), Panitera Pengganti, dan Operator. | Selesai |
| 6 | **Calculated Honor Per Hakim Agung** | Menghitung total akumulasi jatah honor setiap Hakim Agung berdasarkan volume perkara yang ditangani. | Selesai |
| 7 | **Master Data Hakim & Senioritas** | Mengelola daftar Hakim Agung beserta urutan senioritas (Ketua, Wakil, Ketua Kamar, Hakim Agung). | Selesai |
| 8 | **Hakim Pemilah (Terpisah)** | Mencatat dan mengalokasikan honor untuk Hakim Pemilah secara proporsional dan terpisah. | Selesai |
| 9 | **Split Export Laporan (2 Lembar)** | Menghasilkan ekspor Excel/PDF terpisah untuk Kepaniteraan/Majelis (detail) dan Pimpinan (rekap ringkas). | Selesai |
| 10| **Auto-Sync & Proteksi Formula** | Mengunci rumus perhitungan serta melakukan sinkronisasi otomatis seluruh data rekapitulasi. | Selesai |

---

# BAB IV: HASIL PEMBELAJARAN

### 4.1 Manfaat Magang yang Didapat
Melalui kegiatan magang di Mahkamah Agung dalam pembuatan sistem DIGIPER, manfaat yang diperoleh peserta magang meliputi:
1. **Pengalaman Kerja Profesional**: Memahami alur kerja nyata di lingkungan instansi pemerintahan tinggi Mahkamah Agung RI, khususnya di bidang kepaniteraan dan pengolahan data perkara.
2. **Peningkatan Kemampuan Teknis (Hard Skills)**:
   - Menguasai pengembangan aplikasi web berbasis Laravel 12 dan PHP 8.4.
   - Memahami teknik manipulasi dan parsing data Excel berukuran besar (*large dataset processing*).
   - Mampu merancang skema basis data relasional yang scalable dan efisien.
   - Menguasai pembuatan antarmuka modern dengan Tailwind CSS v4.
3. **Peningkatan Kemampuan Non-Teknis (Soft Skills)**:
   - Kemampuan komunikasi teknis dengan pembimbing lapangan dan jajaran staf.
   - Kemampuan manajemen waktu dan pencapaian target dalam skema pengembangan Prototyping.
   - Kemampuan *problem-solving* dan analisis akar masalah (*root cause analysis*) dalam menangani bug aplikasi.

---

### 4.2 Penerapan Ilmu Dalam Magang
Materi perkuliahan yang diperoleh pada Program Studi S-1 Teknik Informatika Universitas Yarsi dapat diimplementasikan secara langsung dalam kegiatan magang ini.

**Tabel 5. Matriks Penerapan Ilmu Perkuliahan dalam Kegiatan Magang**

| No | Mata Kuliah / Konsep | Bentuk Penerapan dalam Kegiatan Magang |
| :---: | :--- | :--- |
| 1 | **Pemrograman Berorientasi Objek (PBO)** | Menerapkan konsep *Class*, *Object*, *Inheritance*, dan *Encapsulation* pada struktur Model Eloquent Laravel (Model `Perkara`, `Hakim`, `DistribusiHonor`). |
| 2 | **Rekayasa Perangkat Lunak (RPL)** | Menggunakan metode *Prototyping*, penyusunan *User Stories*, perancangan *Use Case*, dan perancangan *Entity Relationship Diagram* (ERD). |
| 3 | **Basis Data & Pemrograman Web** | Merancang skema tabel MySQL, query agregasi (`SUM`, `GROUP BY`, `JOIN`), pembuatan API Endpoint, serta manipulasi tampilan Blade + Tailwind CSS. |
| 4 | **Algoritma & Struktur Data** | Menerapkan algoritma pencocokan string (*Fuzzy Matching*) untuk verifikasi nama Hakim Agung dari berkas Excel ke database master. |
| 5 | **Keamanan Informasi & Sistem** | Menerapkan proteksi data keuangan, *Formula Hashing*, Enkripsi konfigurasi sensitif, serta validasi hak akses berbasis peran (*Role-Based Access Control*). |

---

# BAB V: KESIMPULAN DAN SARAN TEKNIS

### 5.1 Kesimpulan
Berdasarkan kegiatan magang yang telah dilaksanakan di Mahkamah Agung dalam pengembangan Sistem Rekapitulasi Biaya Perkara (DIGIPER), diperoleh kesimpulan sebagai berikut:
1. **Sistem DIGIPER** berhasil dibangun berbasis web dengan memanfaatkan framework Laravel 12, PHP 8.4, Tailwind CSS v4, dan MySQL untuk menggantikan proses rekapitulasi manual.
2. Fitur **Import Excel Drag and Drop** serta **Auto-Filter Usia Perkara ($\ge 90$ Hari)** mampu mempercepat validasi berkas perkara secara otomatis, akurat, dan meminimalkan kesalahan input (*human error*).
3. Pemecahan komponen biaya perkara dan distribusi honorarium kepada Majelis Hakim, Panitera Pengganti, Operator, serta Hakim Pemilah dapat dihitung secara presisi sesuai urutan senioritas Hakim Agung.
4. Fitur **Split Export Laporan** (Lembar Kepaniteraan dan Lembar Pimpinan) serta **Dashboard Rekap Total** berhasil menyediakan dokumen keuangan yang transparan dan akuntabel untuk audit LK BPK.

---

### 5.2 Saran Teknis
Untuk pengembangan Sistem DIGIPER lebih lanjut di masa depan, disarankan beberapa poin perbaikan teknis berikut:
1. **Antrean Pemrosesan Background (Queue Jobs)**: Mengimplementasikan fitur *Laravel Queue & Redis* untuk pemrosesan file Excel berukuran sangat besar ($> 50.000$ baris) agar tidak membebani memori server saat diakses bersamaan.
2. **Autentikasi Dua Faktor (2FA) & Single Sign-On (SSO)**: Mengintegrasikan sistem login DIGIPER dengan SSO Mahkamah Agung untuk meningkatkan keamanan akses user.
3. **Penyimpanan Log Audit Detail (Audit Trail)**: Menambahkan fitur perekaman aktivitas pengguna secara mendalam (*activity log*) untuk mencatat setiap aksi penambahan, perubahan tarif, maupun penghapusan data master demi menjaga transparansi audit.

---

# DAFTAR PUSTAKA

1. Otwell, T. (2026). *Laravel Documentation: The PHP Framework for Web Artisans*. Available at: https://laravel.com/docs
2. Pressman, R. S., & Maxim, B. R. (2020). *Software Engineering: A Practitioner's Approach* (9th ed.). McGraw-Hill Education.
3. Welling, L., & Thomson, L. (2017). *PHP and MySQL Web Development* (5th ed.). Addison-Wesley Professional.
4. Tailwind Labs. (2026). *Tailwind CSS Documentation (v4.0)*. Available at: https://tailwindcss.com/docs
5. Mahkamah Agung Republik Indonesia. (2024). *Pedoman Pengelolaan Biaya Perkara dan Kepaniteraan Mahkamah Agung RI*. Jakarta.

---

# LAMPIRAN

1. **Daftar Hadir Magang**
2. **Laporan Kegiatan Magang (Logbook)**
3. **Lembar Penilaian Magang Mahasiswa**
4. **Profil Instansi (Mahkamah Agung Republik Indonesia)**
   - **Sejarah & Profil Perusahaan/Instansi**: Mahkamah Agung RI merupakan lembaga tinggi negara dalam sistem ketatanegaraan Indonesia yang memegang kekuasaan kehakiman di samping Mahkamah Konstitusi.
   - **Visi**: *"Terwujudnya Badan Peradilan Indonesia yang Agung"*.
   - **Misi**:
     1. Menjaga kemandirian badan peradilan.
     2. Memberikan pelayanan hukum yang berkeadilan kepada pencari keadilan.
     3. Meningkatkan kualitas kepemimpinan badan peradilan.
     4. Kredibilitas dan transparansi badan peradilan.
   - **Struktur Organisasi & Departemen TI**: Kepaniteraan Mahkamah Agung & Tim Pengembangan Teknologi Informasi.
