# Alur Auto-Generate Rekap Keseluruhan (1, 2, dan 3)

Berdasarkan pengecekan pada *source code* (khususnya `DashboardController.php`), rekan Anda sebenarnya sudah merancang dasar logic untuk membaca ketiga bagian dari sheet **"Rekap Keseluruhan"**. Berikut adalah penjelasan alur, logic, dan prompt yang Anda butuhkan.

## 1. Alur Utama (Bagaimana Data Excel Dibaca)
Pada dasarnya, sheet "Rekap Keseluruhan" dalam file Excel Anda tidak hanya berisi 1 tabel, melainkan 3 tabel berbeda yang diletakkan di koordinat (posisi) yang berbeda-beda dalam satu sheet yang sama.

Alur pembacaannya adalah sebagai berikut:
1. **Upload File:** User mengunggah file Excel, dan sistem (Laravel) menyimpannya sementara di `Session`.
2. **Load Spreadsheet:** Sistem menggunakan library `PhpSpreadsheet` untuk membuka file Excel tersebut dan mencari sheet bernama `'Rekap Keseluruhan'` (atau variasi namanya seperti `'REKAP GABUNGAN'`).
3. **Parsing per Bagian:** 
   - Karena ada 3 tabel di dalam satu sheet, sistem memecah pembacaan berdasarkan rentang baris (row) dan kolom (column) tertentu.
   - Sistem juga mendeteksi *Merge Cells* (sel yang digabung) agar tabel HTML (`colspan` & `rowspan`) yang dihasilkan nanti tidak berantakan.
4. **Passing ke View (Blade):** Data array yang sudah rapi kemudian dikirim ke file `.blade.php` untuk di-*render* menjadi tabel di halaman web.

---

## 2. Perbedaan Auto-Generate "Rekap Keseluruhan" vs "Sheet Cek"

Sangat penting untuk dipahami bahwa **cara kerja (logic) "Rekap Keseluruhan" SANGAT BERBEDA dengan "Sheet Cek"**.

*   **Sheet Cek (`sheetCek()`):** 
    Ini adalah proses **Kalkulasi murni**. Sistem membaca data mentah (raw data) yang ada di sheet "Data Print", lalu sistem menghitung sendiri (menggunakan class `HonorariumCalculator.php`) jumlah perkara, mengalikannya dengan tarif, lalu merender hasilnya.
*   **Rekap Keseluruhan (1, 2, dan 3):** 
    Ini adalah proses **Parsing murni (Auto-Rendering)**. Sistem **TIDAK** menghitung tarif atau jumlah perkara. Sistem hanya mencari sheet yang bernama `Rekap Keseluruhan` di Excel Anda, lalu membaca sel-sel yang sudah dikalkulasi dari Excel tersebut (membaca nilai di dalam kotak tabel dari titik kordinat tertentu), lalu mengubahnya menjadi tampilan tabel di website (HTML). 

Jadi, "Auto generate" di Rekap Keseluruhan berarti sistem **secara otomatis mendeteksi ukuran, posisi, dan letak sel yang di-merge dari sheet Excel dan menyajikannya ke web tanpa harus di-hardcode**.

---

## 3. Logic Pembagian Tabel (Rekap 1, 2, dan 3)

Berikut adalah logic dari masing-masing bagian yang sudah ada di sistem:

### A. Rekap Keseluruhan 1 (Tabel Utama / Kiri Atas)
- **Method:** `buildRekapKeseluruhanReport()`
- **Target Area:** Kolom **A** sampai **N**, dari Baris **1** sampai **49**.
- **Logic:** Sistem akan melakukan looping pada area ini, mendeteksi sel mana yang di-merge, mengambil nilainya, lalu menyimpannya. Sel yang kosong atau diluar area ini diabaikan. Ini adalah tabel utama.

### B. Rekap Keseluruhan 2 (Tabel Kanan Atas)
- **Method:** `buildRekapKananReport()`
- **Target Area:** Kolom **Q** (Kolom ke-17) sampai **AY** (Kolom ke-51), dari Baris **4** sampai **38**.
- **Logic:** Sama seperti tabel utama, namun rentang pencariannya digeser jauh ke kanan. Sistem hanya mengambil merged cells dan data yang ada persis di dalam kotak (Q4 sampai AY38). Ini umumnya berisi tabel persentase/rincian peruntukan biaya (seperti 10x biaya, jumlah, sub total).

### C. Rekap Keseluruhan 3 (Tabel Bawah / Honorarium Perkara)
- **Method:** `buildRekapHonorariumPerkaraReport()`
- **Target Area:** Mulai dari **Baris 39** ke bawah (dinamis).
- **Logic (Paling Dinamis):**
  1. Mulai baris 39, sistem akan mencari (scan) ke bawah untuk menemukan kata kunci **"PERUNTUKAN"** atau **"PEJABATAN"** yang menandakan itu adalah baris Header (Judul Tabel).
  2. Setelah ketemu baris header-nya, sistem mendeteksi dari kolom ke berapa tabel ini dimulai, dan sampai kolom ke berapa batas akhirnya (biasanya kolom yang ada kata "NETTO" atau "JUMLAH").
  3. Setelah itu, sistem akan me-looping ke bawah untuk mengambil data orang/pejabat penerima honorarium sampai ia menemukan footer/tanda tangan (seperti kata "Jakarta" atau "Mengetahui").

---

## 3. Prompt untuk AI / Developer (Jika ingin Modifikasi/Melanjutkan)

Jika Anda ingin memerintahkan AI atau developer lain untuk memperbaiki, mengubah styling, atau menambahkan fitur di Rekap Keseluruhan 2 & 3, gunakan prompt berikut:

> **Prompt:**
> "Halo, di dalam project Laravel ini terdapat fitur untuk meng-*auto-generate* tabel dari file Excel khususnya pada sheet 'Rekap Keseluruhan'. Saat ini data dirender menjadi 3 bagian: Rekap 1 (Kiri Atas), Rekap 2 (Kanan Atas), dan Rekap 3 (Bawah/Honorarium).
> 
> Saya ingin fokus pada **[Pilih: Rekap Keseluruhan 2 / Rekap Keseluruhan 3]**. 
> Data tersebut di-*parse* melalui fungsi **[Pilih: `buildRekapKananReport()` / `buildRekapHonorariumPerkaraReport()`]** di dalam `DashboardController.php`, lalu ditampilkan di file view **[Pilih: `rekap-keseluruhan-2.blade.php` / `rekap-keseluruhan-3.blade.php`]**.
> 
> Tugas kamu:
> 1. Tolong pahami alur parsing koordinat Excel di controller tersebut.
> 2. Lakukan perbaikan pada **[Sebutkan perbaikannya, misal: cara merender colspan/rowspan di view agar tabelnya tidak miring ATAU ubah batas kolom pembacaan Excel-nya]**.
> 3. Jangan merusak logic dari Rekap Keseluruhan 1. Pastikan styling tabel konsisten dengan desain yang sudah ada (menggunakan Tailwind CSS)."

---

**Kesimpulan untuk Anda:**
Rekan Anda sudah memikirkan cara memotong 1 sheet Excel besar menjadi 3 halaman/tabel web yang rapi dengan membatasi **huruf kolom** dan **angka baris** di `DashboardController.php`. Anda hanya perlu fokus mengatur bagaimana data array tersebut ditampilkan (di-styling) di file `.blade.php` masing-masing jika tampilannya dirasa belum sempurna.
