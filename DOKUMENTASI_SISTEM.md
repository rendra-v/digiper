# Dokumentasi Sistem DIGIPER
## Sistem Rekapitulasi Biaya Perkara - Mahkamah Agung

**Versi:** 1.0  
**Tanggal:** Mei 2026  
**Status:** Development Phase - UI Foundation Complete  

---

## 📋 Daftar Isi

1. [Ringkasan Proyek](#ringkasan-proyek)
2. [Visi & Misi](#visi--misi)
3. [User Stories](#user-stories)
4. [Arsitektur Sistem](#arsitektur-sistem)
5. [Struktur Data](#struktur-data)
6. [Alur Bisnis](#alur-bisnis)
7. [Komponen Utama](#komponen-utama)
8. [Fase Implementasi](#fase-implementasi)
9. [Status Pengembangan](#status-pengembangan)
10. [Panduan Lanjutan](#panduan-lanjutan)

---

## 🎯 Ringkasan Proyek

**DIGIPER** adalah sistem terintegrasi untuk mengelola dan merekap biaya perkara di Mahkamah Agung. Sistem ini mengotomatisasi:

- **Import data** dari file Excel hasil download Mahkamah Agung
- **Filtering & validasi** perkara berdasarkan usia (minimum 90 hari)
- **Pengelompokan** perkara per jenis (Kasasi, PK, TUN, Perdata, Agama, Pajak, Hum, Perdata Khusus)
- **Aplikasi tarif default** biaya sesuai jenis perkara
- **Pemecahan biaya** ke komponen detail (Materai, Redaksi, ATK, Sidang, dll)
- **Distribusi honor** ke Hakim, Operator, Panitera Pengganti
- **Manajemen master data** (daftar Hakim, pejabat, komponen biaya)
- **Pembuatan laporan** terperinci dengan multi-lembar kerja

**Teknologi:**
- **Backend:** Laravel 12 + PHP 8.4
- **Frontend:** Blade Templates + Tailwind CSS v4
- **Database:** MySQL
- **Excel:** Maatwebsite Excel v3
- **Testing:** Pest PHP v3

---

## 💡 Visi & Misi

### Visi
Menjadi sistem terpercaya untuk manajemen keuangan perkara Mahkamah Agung dengan akurasi tinggi, transparansi penuh, dan otomasi cerdas.

### Misi
1. **Otomasi Penuh** - Menghilangkan input manual dan risiko kesalahan perhitungan
2. **Akurasi Data** - Memastikan setiap rupiah terhitung dengan benar
3. **Transparansi** - Menyediakan laporan terperinci untuk audit dan cross-check
4. **Fleksibilitas** - Mudah disesuaikan saat ada perubahan kebijakan atau tarif
5. **Keamanan** - Melindungi formula dan data dari manipulasi
6. **Skalabilitas** - Siap diimplementasikan di server resmi Mahkamah Agung

---

## 📖 User Stories

### A. ADMIN STORIES

#### **A1. Import Data Excel**
**User Story:**  
Sebagai Admin, saya ingin mengimpor data info perkara dari file Excel (hasil download info perkara Mahkamah Agung) agar sistem dapat membaca data secara otomatis tanpa perlu input manual.

**Acceptance Criteria:**
- [x] Halaman upload modal dengan drag-drop interface
- [ ] Support format: XLSX, XLS, XLSM, XLSB
- [ ] Max file size: 25 MB
- [ ] Error handling untuk file format invalid
- [ ] Progress indicator untuk proses import
- [ ] Mapping otomatis kolom Excel ke database schema

**Teknis:**
- Controller: `PerkaraController@store`
- Import Class: `App\Imports\PerkaraImport`
- View: `resources/views/perkaras/create.blade.php`

---

#### **A2. Auto-Filter Usia Perkara (90 hari)**
**User Story:**  
Sebagai Admin, saya ingin sistem otomatis mengabaikan atau mengeluarkan perkara yang usianya di bawah 90 hari (tanggal putus dikurangi tanggal masuk) agar tidak masuk ke dalam perhitungan biaya yang dibayarkan.

**Acceptance Criteria:**
- [ ] Hitung usia: `abs(tanggal_putus - tanggal_masuk)` dalam hari
- [ ] Perkara < 90 hari: status "Belum Kena Biaya" (tidak diproses)
- [ ] Perkara ≥ 90 hari: status "Kena Biaya" (diproses)
- [ ] Visual indicator di dashboard: badge warna untuk status
- [ ] Filter tab: tampilkan semua / hanya ≥90 hari / hanya <90 hari

**Formula:**
```
Usia Perkara (hari) = ABS(Tanggal Putus - Tanggal Masuk Perkara)
Threshold = 90 hari
Status = IF(Usia >= 90, "Kena Biaya", "Belum Kena Biaya")
```

**Teknis:**
- Model: `App\Models\Perkara->getUsiaPerkara()`
- Migration: tambahkan column `status_biaya` (enum: belum_kena, kena)

---

#### **A3. Filter & Pengelompokan Per Jenis Perkara**
**User Story:**  
Sebagai Admin, saya ingin sistem otomatis memfilter dan mengelompokkan perkara berdasarkan jenisnya (Kasasi, PK, TUN, Perdata, Agama, Pajak, Hum, Perdata Khusus) agar rekapitulasi data rapi dan terpisah per klasifikasi perkara.

**Acceptance Criteria:**
- [ ] Master data jenis perkara tersimpan di database
- [ ] Kolom `jenis_perkara` di tabel perkaras
- [ ] Dropdown filter di dashboard
- [ ] View per jenis: perkaras?filter=jenis&value=kasasi
- [ ] Rekap otomatis group by jenis

**Jenis Perkara yang Didukung:**
1. Kasasi
2. Peninjauan Kembali (PK)
3. Tata Usaha Negara (TUN)
4. Perdata
5. Agama
6. Pajak
7. Hukum & Lingkungan (Hum)
8. Perdata Khusus

**Teknis:**
- Model: `App\Models\JenisPerkara` (master table)
- Migration: create table `jenis_perkaras`
- Relationship: Perkara hasOne JenisPerkara

---

#### **A4. Aplikasi Tarif Default Per Jenis**
**User Story:**  
Sebagai Admin, saya ingin sistem otomatis menerapkan nominal biaya default berdasarkan jenis perkaranya (misal: Kasasi Elektronik Rp400.000, PK Rp2.000.000) agar terhindar dari kesalahan perhitungan nominal biaya perkara.

**Acceptance Criteria:**
- [ ] Master data tarif per jenis perkara
- [ ] Aplikasi otomatis saat import (VLOOKUP)
- [ ] UI untuk mengubah/menyesuaikan tarif
- [ ] History perubahan tarif
- [ ] Berlaku efektif per tanggal

**Default Tarif (Contoh - akan dikonfigurasi):**
| Jenis Perkara | Tarif Normal | Tarif Elektronik |
|---|---|---|
| Kasasi | Rp 2.000.000 | Rp 400.000 |
| PK | Rp 2.000.000 | - |
| TUN | Rp 2.000.000 | - |
| Perdata | Rp 1.500.000 | - |
| Agama | Rp 1.500.000 | - |
| Pajak | Rp 2.000.000 | - |
| Hum | Rp 2.000.000 | - |
| Perdata Khusus | Rp 1.500.000 | - |

**Teknis:**
- Model: `App\Models\TarifBiaya` (master table)
- Migration: create table `tarif_biayas`
- Logic: Auto-populate saat create Perkara

---

#### **A5. Pemecahan Biaya ke Komponen**
**User Story:**  
Sebagai Admin, saya ingin sistem otomatis memecah nominal biaya perkara ke dalam komponen pecahan yang fix (seperti Materai Rp10.000, Redaksi Rp10.000, ATK, Sidang, dll.) agar rincian kebutuhan anggaran setiap nomor perkara tercatat dengan jelas.

**Acceptance Criteria:**
- [ ] Master data komponen biaya tersimpan
- [ ] Persentase/nominal per komponen dapat dikonfigurasi
- [ ] Auto-split saat apply tarif
- [ ] View detail breakdown di halaman detail perkara
- [ ] Export rincian ke Excel

**Komponen Biaya Standar:**
| # | Komponen | Tipe | Default | Keterangan |
|---|---|---|---|---|
| 1 | Materai | Fixed | Rp 10.000 | Biaya materai dokumen |
| 2 | Redaksi | Fixed | Rp 10.000 | Penulisan dokumen putus |
| 3 | ATK | Percent | 5% | Alat Tulis Kantor |
| 4 | Sidang | Percent | 20% | Biaya sidang |
| 5 | Admin | Percent | 10% | Biaya administrasi |
| 6 | Operasional | Percent | 15% | Biaya operasional umum |
| (Lebih banyak - TBD) | ... | ... | ... | Dapat ditambah dinamis |

**Formula:**
```
Komponen_Fixed = Nilai Fixed per komponen
Komponen_Percent = (Tarif Total - Total Fixed) × Persentase

Contoh: Kasasi Normal Rp 2.000.000
- Materai (Fixed): Rp 10.000
- Redaksi (Fixed): Rp 10.000
- Subtotal Fixed: Rp 20.000
- Sisa: Rp 1.980.000

- ATK (5%): Rp 1.980.000 × 5% = Rp 99.000
- Sidang (20%): Rp 1.980.000 × 20% = Rp 396.000
- Admin (10%): Rp 1.980.000 × 10% = Rp 198.000
- Operasional (15%): Rp 1.980.000 × 15% = Rp 297.000
- Sisa (50%): Rp 1.980.000 × 50% = Rp 990.000
```

**Teknis:**
- Model: `App\Models\KomponenBiaya` (master table)
- Model: `App\Models\DetailBiayaPerkara` (breakdown per perkara)
- Migration: 
  - create table `komponen_biayas`
  - create table `detail_biaya_perkaras`
- Relationship: Perkara hasMany DetailBiayaPerkara

---

#### **A6. Distribusi Honor ke Pihak Terkait**
**User Story:**  
Sebagai Admin, saya ingin sistem otomatis membagi pecahan biaya penyelesaian perkara (seperti jatah Operator, Panitera Pengganti/PP, Majelis Hakim) berdasarkan data perkara yang diimport agar proses pembagian honor/insentif tepat sasaran.

**Acceptance Criteria:**
- [ ] Master data role & persentase jatah
- [ ] Auto-split biaya per role
- [ ] Jatah per individu (Operator, Panitera, Hakim P1/P2/P3)
- [ ] View distribusi di halaman detail
- [ ] Report honor per orang

**Distribusi (Contoh - akan dikonfigurasi):**
| Role | Persentase | Keterangan |
|---|---|---|
| Operator | 5% | Biaya operasional |
| Panitera Pengganti (PP) | 10% | Asisten Panitera |
| Majelis Hakim | 75% | Dibagi ke P1, P2, P3, dll |
| Cadangan | 10% | Untuk kebutuhan darurat |

**Distribusi Majelis Hakim (dari 75%):**
- Ketua Majelis: 35%
- Hakim Anggota 1: 25%
- Hakim Anggota 2: 25%
- (Dapat disesuaikan per kasus)

**Teknis:**
- Model: `App\Models\JatahHonor` (master table)
- Model: `App\Models\DistribusiHonor` (per perkara)
- Migration: 
  - create table `jatah_honors`
  - create table `distribusi_honors`

---

#### **A7. Hitung Total Jatah Honor Per Hakim**
**User Story:**  
Sebagai Admin, saya ingin sistem otomatis menghitung total jatah honor untuk setiap Hakim Agung berdasarkan volume perkara yang mereka tangani (baik sebagai P1, P2, P3, dll.) agar tidak perlu menghitung manual satu per satu.

**Acceptance Criteria:**
- [ ] Dashboard honor per Hakim
- [ ] List Hakim dengan total honor (sortable, filterable)
- [ ] Detail perkara yang ditangani per Hakim
- [ ] Export laporan honor
- [ ] Pivot table: Hakim × Jenis Perkara × Total Honor

**Teknis:**
- Aggregation query: `SUM(distribusi_honors.nominal) GROUP BY hakim_id`
- View: `HakimController@honorDashboard`
- Cache: Cache hasil untuk performa

---

#### **A8. Master Data Hakim + Seniority**
**User Story:**  
Sebagai Admin, saya ingin mengelola master data nama Hakim Agung beserta urutan senioritasnya agar data di dalam sistem selalu update dan posisi nama pada default laporan berurutan dari yang paling senior (Ketua, Wakil, Ketua Kamar, baru Hakim Agung).

**Acceptance Criteria:**
- [ ] CRUD Hakim (Create, Read, Update, Delete)
- [ ] Field: nama, NIP, pangkat/golongan, posisi (Ketua/Wakil/Ketua Kamar/Anggota)
- [ ] Urutan seniority/ranking otomatis
- [ ] Soft delete untuk Hakim yang pensiun
- [ ] Validasi: tidak duplikat NIP
- [ ] Export list Hakim

**Teknis:**
- Model: `App\Models\Hakim`
- Migration: create table `hakims`
- Fields:
  - id (PK)
  - nama (string)
  - nip (string, unique)
  - pangkat (enum: Ketua, Wakil Ketua, Ketua Kamar, Anggota)
  - urutan_seniority (integer)
  - status_aktif (boolean, default true)
  - tanggal_mulai (date)
  - tanggal_selesai (date, nullable)
  - created_at, updated_at
  - deleted_at (soft delete)

---

#### **A9. Master Data Pejabat (Dropdown)**
**User Story:**  
Sebagai Admin, saya ingin mengelola data pejabat yang sering berubah (seperti Panitera, Ketua Kamar, Ketua Majelis, Panmud, Askor, Operator) menggunakan fitur list/dropdown agar nama pejabat pada dokumen laporan bisa disesuaikan dengan mudah saat ada pergantian jabatan.

**Acceptance Criteria:**
- [ ] CRUD untuk setiap jenis pejabat
- [ ] Jabatan yang didukung: Panitera, Ketua Kamar, Ketua Majelis, Panmud, Askor, Operator
- [ ] Field: nama, NIP, jabatan, tanggal mulai/selesai
- [ ] Soft delete untuk tracking history
- [ ] Default pejabat untuk laporan
- [ ] History perubahan pejabat

**Teknis:**
- Model: `App\Models\Pejabat`
- Migration: create table `pejabats`
- Dropdown di form laporan

---

#### **A10. Add Hakim Baru (Direct Input)**
**User Story:**  
Sebagai Admin, saya ingin menambahkan nama Hakim baru secara langsung (fitur update/add data master) jika ada nama Hakim yang belum tercantum di sistem agar data penanganan perkara tetap akurat.

**Acceptance Criteria:**
- [ ] Form cepat add Hakim di halaman upload/import
- [ ] Validasi real-time: nama, NIP
- [ ] Auto-detect dari nama di Excel file
- [ ] Suggestion list saat typing
- [ ] Save langsung ke master data
- [ ] Confirmation dialog

**Teknis:**
- Modal form di create.blade.php atau show.blade.php
- API endpoint: `POST /api/hakims/quick-add`
- AJAX handler

---

#### **A11. Hakim Pemilah (Terpisah)**
**User Story:**  
Sebagai Admin, saya ingin mencatat data Hakim Pemilah secara tersendiri di dalam sistem karena tidak semua perkara melewati proses hakim pemilah.

**Acceptance Criteria:**
- [ ] Field: `hakim_pemilah_id` di tabel perkaras (nullable)
- [ ] Optional field di form upload/edit
- [ ] Terpisah di laporan breakdown honor
- [ ] Filter: tampilkan perkara dengan/tanpa hakim pemilah
- [ ] Report: total honor hakim pemilah

**Teknis:**
- Migration: tambahkan `hakim_pemilah_id` (foreign key, nullable)
- Relationship: Perkara belongsTo HakimPemilah

---

#### **A12. Split Laporan (2 Lembar Kerja)**
**User Story:**  
Sebagai Admin, saya ingin membuat rincian laporan yang di-split (dipisah) menjadi dua lembar kerja/lembar cetak (misal: lembar khusus Majelis/Kepaniteraan dan lembar khusus Pimpinan) agar distribusi laporan sesuai dengan peruntukan masing-masing pihak.

**Acceptance Criteria:**
- [ ] Export 2 worksheet: "Majelis & Kepaniteraan" + "Pimpinan"
- [ ] Lembar 1 (Majelis/Kepaniteraan): Detail honor Hakim, PP, Operator
- [ ] Lembar 2 (Pimpinan): Summary total per jenis perkara, total keseluruhan
- [ ] Format profesional, header/footer lengkap
- [ ] Signature lines untuk approval

**Struktur Laporan:**

**Lembar 1: Majelis & Kepaniteraan**
```
| No | Reg. Perkara | Jenis | P1 | P2 | P3 | Operator | PP | Total |
|----|----|---|---|---|---|---|---|---|
| ... | ... | ... | ... | ... | ... | ... | ... | ... |
```

**Lembar 2: Pimpinan**
```
| Jenis Perkara | Volume | Total Biaya | Rata-rata |
|---|---|---|---|
| Kasasi | 5 | Rp 10.000.000 | Rp 2.000.000 |
| PK | 3 | Rp 6.000.000 | Rp 2.000.000 |
| ... | ... | ... | ... |
| TOTAL | 8 | Rp 16.000.000 | - |
```

**Teknis:**
- Controller: `LaporanController@export`
- Package: `maatwebsite/excel` dengan multiple worksheets
- View: `resources/views/laporan/template-1.blade.php`, `template-2.blade.php`

---

#### **A13. Filter/Pengelompokan Khusus (Team Code)**
**User Story:**  
Sebagai Admin, saya ingin membuat kode filter atau pengelompokan khusus (seperti kode tim kepaniteraan) agar memudahkan pemisahan data saat pencetakan laporan.

**Acceptance Criteria:**
- [ ] Buat custom grouping code (misal: "TIM-001", "TIM-002")
- [ ] Master table untuk tim
- [ ] Assign perkara ke tim
- [ ] Filter laporan per tim
- [ ] Multi-select filter

**Teknis:**
- Model: `App\Models\TimKepaniteraan`
- Migration: 
  - create table `tim_kepaniteraanss`
  - tambahkan `tim_kepaniteraan_id` ke perkaras
- Relationship: Perkara belongsTo TimKepaniteraan

---

#### **A14. Halaman Rekap Total (Auto-Sync)**
**User Story:**  
Sebagai Admin, saya ingin melihat halaman rekap total keseluruhan yang datanya otomatis sinkron (nge-link) dari rekap-rekap komponen di depannya agar memudahkan proses cross-check data sebelum dicetak.

**Acceptance Criteria:**
- [ ] Dashboard rekap total
- [ ] Total perkara diproses
- [ ] Total biaya keseluruhan
- [ ] Breakdown per jenis perkara
- [ ] Breakdown per honor (Hakim, Operator, PP)
- [ ] Summary performa (kecepatan proses, dll)
- [ ] Real-time update (tidak perlu refresh)
- [ ] Verifikasi cross-check (tombol check consistency)

**Teknis:**
- Controller: `RekapController@total`
- View: `resources/views/rekap/total.blade.php`
- Query: Aggregation dengan SUM, COUNT, GROUP BY
- Cache dengan invalidation

---

#### **A15. Cetak Laporan Per Periode**
**User Story:**  
Sebagai Admin, saya ingin mencetak laporan rekapitulasi biaya perkara ini per periode tertentu agar bisa digunakan sebagai bahan laporan resmi untuk pemeriksaan LK BPK.

**Acceptance Criteria:**
- [ ] Form pemilihan periode (tanggal mulai - tanggal selesai)
- [ ] Filter: per jenis perkara, per tim, per hakim
- [ ] Format PDF & Excel
- [ ] Header resmi Mahkamah Agung
- [ ] Tanda tangan digital/placeholder
- [ ] Nomor surat & tanggal surat
- [ ] Watermark "RESMI"

**Teknis:**
- Controller: `LaporanController@cetak`
- Package: `dompdf`, `maatwebsite/excel`
- View: `resources/views/laporan/cetak-*.blade.php`
- Route: `GET /laporan/cetak` (form) + `POST /laporan/cetak` (process)

---

### B. SYSTEM STORIES

#### **B1. Auto-Link Formula (End-to-End)**
**User Story:**  
Sebagai Sistem, saya ingin memastikan seluruh formula perhitungan dan relasi data dari import Excel hingga rekapan terakhir terhubung secara otomatis (nge-link) agar ketika ada satu data berubah, seluruh rekapan ikut ter-update tanpa intervensi manual.

**Acceptance Criteria:**
- [ ] Database relationships (FK) konsisten
- [ ] Computed properties / virtual attributes
- [ ] Event listeners untuk update cascading
- [ ] No denormalization / hardcoded values
- [ ] Cache invalidation otomatis

**Alur Data:**
```
Excel Import → Perkara + DetailBiaya + DistribusiHonor
    ↓
Perubahan Tarif → Auto-recalc semua related records
    ↓
Perubahan Hakim → Auto-update distribusi honor
    ↓
Dashboard & Laporan → Selalu menampilkan data terbaru
```

**Teknis:**
- Eloquent events: `created`, `updated`, `deleted`
- Model observers
- Database triggers (optional, untuk performance)
- Queue jobs untuk heavy computation

---

#### **B2. Auto-Hide Zero/Empty Values**
**User Story:**  
Sebagai Sistem, saya ingin menyediakan fitur filter otomatis untuk menyembunyikan data nominal yang bernilai nol (0) atau kosong agar tampilan dokumen rekapitulasi tetap rapi, padat, dan urut.

**Acceptance Criteria:**
- [ ] Option: "Sembunyikan item Rp 0"
- [ ] Default: true (hidden)
- [ ] Berlaku di dashboard, laporan, export
- [ ] Query optimization: exclude null/zero di SELECT

**Teknis:**
- View helper: `formatNominal()`, `hideZero()`
- Query scope: `whereNotZero()`
- Config setting untuk default behavior

---

#### **B3. VLOOKUP Algorithm**
**User Story:**  
Sebagai Sistem, saya ingin menggunakan algoritma VLOOKUP atau pencarian data berbasis master data untuk mencocokkan nama Hakim atau jenis perkara dari file Excel yang diimport dengan database sistem.

**Acceptance Criteria:**
- [ ] Fuzzy matching untuk nama Hakim (support typo)
- [ ] VLOOKUP untuk jenis perkara dari code/nama
- [ ] Confidence score untuk matching
- [ ] Manual override jika match ambiguous
- [ ] Log untuk tracking matching result

**Teknis:**
- Package: `php-string-similarity` untuk fuzzy match
- Custom logic: `Imports\PerkaraImport->map()`
- Modal untuk resolve ambiguity

---

#### **B4. Deploy di Server Resmi**
**User Story:**  
Sebagai Sistem, saya ingin dapat dijalankan dan di-deploy di server resmi (seperti server Telkom Mahkamah Agung) agar keamanan data terjaga dan aplikasi dapat diakses secara resmi oleh tim internal terkait.

**Acceptance Criteria:**
- [ ] Environment configuration (production)
- [ ] SSL/TLS certificate setup
- [ ] Database backup & recovery procedure
- [ ] Load balancing config (if needed)
- [ ] Monitoring & alerting setup
- [ ] Security hardening checklist

**Teknis:**
- `.env.production` template
- Docker containerization (optional)
- CI/CD pipeline
- Server requirements: PHP 8.4, MySQL 8.0+, 2GB RAM minimum

---

#### **B5. Encryption & Formula Protection**
**User Story:**  
Sebagai Sistem, saya ingin memastikan keamanan formula perhitungan di dalam aplikasi terenkripsi atau dikunci rapat agar rumus pembagian biaya perkara tidak dapat dimanipulasi oleh pihak yang tidak bertanggung jawab demi menjaga integritas data keuangan (kepercayaan).

**Acceptance Criteria:**
- [ ] Enkripsi sensitive config (tarif, persentase distribusi)
- [ ] Role-based access control (RBAC)
- [ ] Audit log untuk semua perubahan formula
- [ ] Digital signature pada laporan
- [ ] Read-only mode untuk user tertentu
- [ ] Formula hashing untuk integrity check

**Teknis:**
- Encryption: Laravel `Crypt::encrypt()` / `decrypt()`
- RBAC: Middleware, Policies
- Audit: `laravel-audit` atau custom table
- Digital signature: `OpenSSL` atau PKI

---

## 🏗️ Arsitektur Sistem

### High-Level Architecture
```
┌─────────────────────────────────────────────────────────┐
│                    Frontend (Blade)                     │
│  - Upload Modal  - Dashboard  - Laporan  - Master Data │
└──────────────────┬──────────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────────┐
│              Controller Layer (HTTP)                     │
│  PerkaraController  LaporanController  MasterController │
└──────────────────┬──────────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────────┐
│           Business Logic Layer (Services)               │
│  PerkaraService  LaporanService  DistribusiService     │
└──────────────────┬──────────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────────┐
│               Model Layer (Eloquent ORM)                │
│  Perkara  Hakim  KomponenBiaya  DistribusiHonor        │
└──────────────────┬──────────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────────┐
│              Database Layer (MySQL)                     │
│  perkaras  hakims  komponen_biayas  distribusi_honors  │
└─────────────────────────────────────────────────────────┘
```

### Module Breakdown

#### **Module 1: Import & Validasi**
- Upload file Excel
- Parsing & mapping kolom
- Fuzzy matching (Hakim, Jenis Perkara)
- Validation rules
- Error handling & reporting

#### **Module 2: Processing & Calculation**
- Hitung usia perkara
- Filter (≥90 hari)
- Apply tarif default
- Split biaya ke komponen
- Distribusi honor

#### **Module 3: Manajemen Data**
- CRUD Master Hakim
- CRUD Master Pejabat
- CRUD Tarif & Komponen
- CRUD Tim/Grouping

#### **Module 4: Reporting**
- Dashboard statistik
- Laporan detail per perkara
- Laporan rekap per jenis
- Laporan honor per Hakim
- Export PDF & Excel

#### **Module 5: Security & Audit**
- Authentication & Authorization
- Audit logging
- Formula protection
- Data encryption

---

## 📊 Struktur Data

### Entity Relationship Diagram (ERD)

```
┌─────────────────┐
│     hakims      │
├─────────────────┤
│ id (PK)         │
│ nama            │
│ nip (unique)    │
│ pangkat         │
│ urutan_seniority│
│ status_aktif    │
│ deleted_at      │
└────────┬────────┘
         │
         │ 1:N
         │
┌────────▼──────────────────┐
│       perkaras             │
├────────────────────────────┤
│ id (PK)                    │
│ no_registrasi              │
│ tanggal_masuk              │
│ tanggal_putus              │
│ kamar                      │
│ jenis_perkara_id (FK)      │
│ nama_p1, nama_p2, ...      │
│ hakim_p1_id, ...           │
│ hakim_pemilah_id (FK, null)│
│ tarif_default              │
│ status_biaya (enum)        │
│ tim_kepaniteraan_id (FK)   │
│ created_at, updated_at     │
└────────┬────────┬──────────┘
         │        │
         │        └──────────────────┐
         │ 1:N                       │
         │                           │ 1:N
┌────────▼────────────────────┐  ┌──▼──────────────────┐
│ detail_biaya_perkaras        │  │ distribusi_honors   │
├─────────────────────────────┤  ├─────────────────────┤
│ id (PK)                     │  │ id (PK)             │
│ perkara_id (FK)             │  │ perkara_id (FK)     │
│ komponen_biaya_id (FK)      │  │ hakim_id (FK)       │
│ nominal                     │  │ role (enum)         │
│ persentase                  │  │ nominal             │
└────────────────────────────┘  └─────────────────────┘

┌──────────────────────┐
│  jenis_perkaras      │
├──────────────────────┤
│ id (PK)              │
│ nama (unique)        │
│ kode (unique)        │
├──────────────────────┤

┌──────────────────────┐
│ komponen_biayas      │
├──────────────────────┤
│ id (PK)              │
│ nama (unique)        │
│ tipe (fixed/percent) │
│ nilai                │
│ urutan               │

┌──────────────────────┐
│ tarif_biayas         │
├──────────────────────┤
│ id (PK)              │
│ jenis_perkara_id(FK) │
│ tarif_normal         │
│ tarif_elektronik     │
│ berlaku_mulai        │
│ berlaku_sampai       │

┌──────────────────────┐
│ tim_kepaniteraanss   │
├──────────────────────┤
│ id (PK)              │
│ kode (unique)        │
│ nama                 │
│ deskripsi            │

┌──────────────────────┐
│ pejabats             │
├──────────────────────┤
│ id (PK)              │
│ nama                 │
│ nip (unique)         │
│ jabatan              │
│ tanggal_mulai        │
│ tanggal_selesai      │
│ status_aktif         │
```

### Database Schema (Migration Code Structure)

**Key Tables:**

1. **perkaras** - Main table untuk perkara
2. **hakims** - Master hakim
3. **pejabats** - Master pejabat
4. **jenis_perkaras** - Master jenis perkara
5. **komponen_biayas** - Master komponen biaya
6. **detail_biaya_perkaras** - Breakdown biaya per perkara
7. **distribusi_honors** - Honor distribution per perkara
8. **tarif_biayas** - Tarif per jenis & periode
9. **tim_kepaniteraanss** - Team grouping
10. **audit_logs** - Audit trail (custom or package)

---

## 💼 Alur Bisnis

### Alur 1: Import Excel → Processing → Dashboard

```
┌──────────────┐
│  Upload File │
│  (Excel)     │
└──────┬───────┘
       │
       ▼
┌──────────────────────────────────┐
│ Parse & Mapping Kolom            │
│ - Detect kolom otomatis          │
│ - Mapping ke database field      │
└──────┬───────────────────────────┘
       │
       ▼
┌──────────────────────────────────┐
│ Fuzzy Matching Data              │
│ - Hakim (name similarity)        │
│ - Jenis Perkara (code/name)      │
│ - Manual override jika ambiguous  │
└──────┬───────────────────────────┘
       │
       ▼
┌──────────────────────────────────┐
│ Validasi & Filtering             │
│ - Check mandatory fields         │
│ - Hitung usia perkara           │
│ - Filter: usia >= 90 hari        │
│ - Mark: status_biaya             │
└──────┬───────────────────────────┘
       │
       ▼
┌──────────────────────────────────┐
│ Apply Tarif & Split Biaya        │
│ - Lookup tarif per jenis         │
│ - Split ke komponen              │
│ - Hitung distribusi honor        │
└──────┬───────────────────────────┘
       │
       ▼
┌──────────────────────────────────┐
│ Simpan ke Database               │
│ - Insert perkaras                │
│ - Insert detail_biaya_perkaras   │
│ - Insert distribusi_honors       │
└──────┬───────────────────────────┘
       │
       ▼
┌──────────────────────────────────┐
│ Update Dashboard & Rekap         │
│ - Invalidate cache               │
│ - Trigger rekap calculation      │
│ - Broadcast real-time updates    │
└──────┬───────────────────────────┘
       │
       ▼
┌──────────────────────────────────┐
│ Redirect ke Dashboard            │
│ - Tampilkan data terimpor        │
│ - Show import summary            │
└──────────────────────────────────┘
```

### Alur 2: Filter & View Detail

```
Dashboard
  │
  ├─ Stat Cards (Total, Valid, Biaya, Hakim)
  │
  ├─ Filter: Kamar / Jenis Perkara / Tim
  │
  ├─ Table: List Perkara
  │  │
  │  └─ Klik [Detail] → Detail Page
  │     │
  │     ├─ Info Umum (Registrasi, Tanggal, dll)
  │     ├─ Status (Usia, Status Biaya)
  │     ├─ Majelis (Hakim P1/P2/P3)
  │     ├─ Amar Putusan
  │     ├─ Breakdown Biaya (Komponen)
  │     ├─ Distribusi Honor (Hakim, Operator, PP)
  │     └─ Action: Edit / Delete / Print
```

### Alur 3: Cetak Laporan

```
Button "Cetak Laporan"
  │
  ├─ Form Periode Laporan
  │  ├─ Tanggal Mulai - Tanggal Selesai
  │  ├─ Filter: Jenis Perkara / Tim / Hakim
  │  └─ Format: PDF / Excel
  │
  ├─ Generate Laporan
  │  ├─ Query data sesuai filter
  │  ├─ Hitung agregasi
  │  ├─ Format template
  │  └─ Add header/footer resmi
  │
  └─ Download / Print
     ├─ PDF: Buka di browser
     └─ Excel: 2 worksheet
        ├─ Sheet 1: Majelis & Kepaniteraan
        └─ Sheet 2: Pimpinan Summary
```

---

## 🔧 Komponen Utama

### 1. Controllers

#### `PerkaraController`
- `index()` - Dashboard with filters
- `create()` - Upload modal
- `store()` - Process upload
- `show($id)` - Detail page
- `edit($id)` - Edit form
- `update($id)` - Save changes
- `destroy($id)` - Delete

#### `LaporanController`
- `index()` - Laporan home
- `cetak()` - Generate laporan
- `export()` - Export PDF/Excel

#### `HakimController`
- `index()` - List hakim
- `create()`, `store()` - Add hakim
- `edit()`, `update()` - Edit hakim
- `destroy()` - Delete hakim
- `honorDashboard()` - Honor summary per hakim

#### `MasterDataController`
- Manage: Tarif, Komponen, Pejabat, Tim

### 2. Models

#### `Perkara`
```php
class Perkara extends Model {
    public function hakim_p1() { ... }
    public function hakim_p2() { ... }
    // ...
    public function jenis_perkara() { ... }
    public function detail_biaya() { ... }
    public function distribusi_honor() { ... }
    public function getUsiaPerkara() { ... }
}
```

#### `Hakim`, `Pejabat`, `JenisPerkara`, `KomponenBiaya`, etc.

### 3. Imports

#### `PerkaraImport`
```php
class PerkaraImport implements ToModel {
    public function map($row): ?Perkara { ... }
}
```

### 4. Views

- `layouts/app.blade.php` - Main layout
- `perkaras/create.blade.php` - Upload modal
- `perkaras/index.blade.php` - Dashboard
- `perkaras/show.blade.php` - Detail
- `laporan/cetak.blade.php` - Laporan template

### 5. Services

#### `PerkaraService`
- Processing logic
- Calculation
- Validation

#### `LaporanService`
- Report generation
- Export logic
- Aggregation

### 6. Routes

```php
// Perkaras
Route::resource('perkaras', PerkaraController::class);

// Laporan
Route::post('/laporan/cetak', [LaporanController::class, 'cetak']);

// Master Data
Route::resource('hakims', HakimController::class);
Route::resource('master-data/pejabat', PejabatController::class);
// ... etc
```

---

## 📅 Fase Implementasi

### **FASE 1: Foundation ✅ (SELESAI)**
**Status:** 100%

**Komponen:**
- [x] Setup Laravel 12 + Tailwind CSS
- [x] Konfigurasi database
- [x] Model & Migration dasar (Perkara)
- [x] Upload modal UI
- [x] Dark mode toggle
- [x] Dashboard layout

**Deliverable:**
- Aplikasi berjalan di http://digiper.test/
- Upload modal siap ditest

---

### **FASE 2: Core Logic (NEXT PRIORITY)**
**Estimasi:** 2-3 minggu

**Komponen:**
- [ ] Excel import integration (PerkaraImport)
- [ ] Usia perkara calculation
- [ ] Tarif default application
- [ ] Biaya split logic
- [ ] Master data seeder
- [ ] Fuzzy matching algorithm

**Deliverable:**
- Excel file dapat di-import
- Data terproses otomatis
- Dashboard menampilkan data

---

### **FASE 3: Business Logic (Processing)**
**Estimasi:** 2-3 minggu

**Komponen:**
- [ ] Distribusi honor calculation
- [ ] Detail biaya breakdown
- [ ] Hakim honor aggregation
- [ ] Validasi & error handling
- [ ] Rollback mechanism

**Deliverable:**
- Honor terhitung per hakim
- Laporan honor akurat

---

### **FASE 4: Master Data Management**
**Estimasi:** 1-2 minggu

**Komponen:**
- [ ] CRUD Hakim
- [ ] CRUD Pejabat
- [ ] CRUD Tarif & Komponen
- [ ] CRUD Tim/Grouping
- [ ] Form validation

**Deliverable:**
- Admin bisa manage semua master data
- Dropdown di form terisi otomatis

---

### **FASE 5: Reporting**
**Estimasi:** 2-3 minggu

**Komponen:**
- [ ] Laporan detail per perkara
- [ ] Laporan rekap per jenis
- [ ] Laporan honor per hakim
- [ ] PDF export
- [ ] Excel multi-sheet export
- [ ] Report template design

**Deliverable:**
- Laporan PDF & Excel siap cetak
- Header/footer resmi

---

### **FASE 6: Security & Hardening**
**Estimasi:** 1-2 minggu

**Komponen:**
- [ ] RBAC (Role-Based Access Control)
- [ ] Formula encryption
- [ ] Audit logging
- [ ] Data validation & sanitization
- [ ] Security headers
- [ ] Rate limiting

**Deliverable:**
- Aplikasi aman dari manipulasi
- Audit trail lengkap

---

### **FASE 7: Testing & QA**
**Estimasi:** 1-2 minggu

**Komponen:**
- [ ] Unit tests
- [ ] Feature tests
- [ ] Integration tests
- [ ] UAT with stakeholders
- [ ] Performance testing
- [ ] Security testing (penetration test)

**Deliverable:**
- Test coverage > 80%
- UAT passed

---

### **FASE 8: Deployment**
**Estimasi:** 1 minggu

**Komponen:**
- [ ] Environment setup (production)
- [ ] Database migration
- [ ] SSL certificate
- [ ] Monitoring setup
- [ ] Backup procedure
- [ ] Documentation

**Deliverable:**
- Aplikasi live di server Mahkamah Agung
- Documentation lengkap

---

## 📊 Status Pengembangan

### Summary

| Fase | Komponen | Status | Progress |
|------|----------|--------|----------|
| 1 | Foundation | ✅ Done | 100% |
| 2 | Core Logic | ⏳ Next | 0% |
| 3 | Business Logic | ⏸ Todo | 0% |
| 4 | Master Data | ⏸ Todo | 0% |
| 5 | Reporting | ⏸ Todo | 0% |
| 6 | Security | ⏸ Todo | 0% |
| 7 | Testing | ⏸ Todo | 0% |
| 8 | Deployment | ⏸ Todo | 0% |

### Milestone Timeline

```
Mei 2026     | Jun 2026     | Jul 2026     | Agustus 2026
Phase 1      | Phase 2-3    | Phase 4-5    | Phase 6-8
(Selesai)    | (Core Logic) | (Master+Rep) | (Security+UAT)
             |              |              |
Foundation   | Import OK    | Reports OK   | Live!
UI Ready     | Data Process | Full Admin   |
Modal OK     | Calc OK      | Export OK    |
```

---

## 🚀 Panduan Lanjutan

### Untuk Developer Baru

1. **Setup Environment:**
   ```bash
   git clone <repository>
   cd digiper
   composer install
   npm install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate --seed
   ```

2. **Jalankan Development Server:**
   ```bash
   php artisan serve
   npm run dev
   ```

3. **Akses Aplikasi:**
   - URL: http://digiper.test/
   - Upload modal akan muncul

4. **Baca Kode Existing:**
   - `routes/web.php` - Routing
   - `app/Models/Perkara.php` - Main model
   - `resources/views/layouts/app.blade.php` - Layout
   - `tailwind.config.js` - Styling config

5. **Ikuti Laravel Best Practices:**
   - Use artisan commands: `php artisan make:model`, `make:controller`, etc.
   - Follow naming conventions
   - Write tests for new features
   - Use soft deletes untuk data history
   - Cache untuk performa

### Struktur Folder
```
digiper/
├── app/
│   ├── Http/Controllers/
│   ├── Models/
│   ├── Imports/
│   └── Services/
├── routes/
├── resources/
│   ├── views/
│   └── js/
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
├── storage/
├── tests/
└── DOKUMENTASI_SISTEM.md  ← YOU ARE HERE
```

### Naming Conventions

- **Models:** Singular, PascalCase (e.g., `Perkara`, `Hakim`)
- **Controllers:** Singular, PascalCase + Controller (e.g., `PerkaraController`)
- **Tables:** Plural, snake_case (e.g., `perkaras`, `hakims`)
- **Views:** kebab-case (e.g., `perkaras/create.blade.php`)
- **Methods:** camelCase (e.g., `getUsiaPerkara()`)

### Key Files to Edit for Phase 2

1. `app/Imports/PerkaraImport.php` - Excel parsing
2. `database/migrations/[date]_create_perkaras_table.php` - Schema
3. `app/Models/Perkara.php` - Model logic
4. `app/Http/Controllers/PerkaraController.php` - Controller
5. `resources/views/perkaras/index.blade.php` - Dashboard

### Testing

Run tests dengan Pest:
```bash
php artisan test
# atau
php artisan test --filter=testName
```

### Documentation Style

- Inline comments: Jelaskan **why**, bukan **what**
- PHPDoc: Lengkap dengan type hints
- Test names: Deskriptif, dimulai dengan `test_`

---

## 📞 Contact & Support

- **Project Lead:** [TBD]
- **Tech Lead:** [TBD]
- **Repository:** [TBD]

---

## 📝 Log Perubahan

### v1.0 (Mei 2026)
- ✅ Initial documentation
- ✅ Phase 1 (Foundation) complete
- 📝 Ready for Phase 2 development

---

**Dokumen ini akan diupdate seiring perkembangan project.**
