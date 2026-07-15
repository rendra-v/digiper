# Pemahaman Struktur Perhitungan & Prompt Penyempurnaan

## Status: 🔍 Menunggu Konfirmasi User

---

## 1. Pemahaman Alur Perhitungan

Berdasarkan penjelasan user dan riset mendalam terhadap file Excel serta kode yang sudah ada.

### Diagram Alur

```
┌─────────────────────────────────────────────────────────┐
│  BIAYA DASAR                                            │
│  Jumlah Perkara × Biaya per Perkara = Total Biaya       │
│  Contoh: 1.700 × Rp 210.000 = Rp 357.000.000           │
└────────────────────────┬────────────────────────────────┘
                         │
          ┌──────────────┼──────────────────┐
          ▼              ▼                  ▼
   ┌──────────┐   ┌──────────────┐   ┌──────────┐
   │   TIM    │   │ Kepaniteraan │   │  Pemilah │
   │184.875.000│  │  (baris 32) │   │25.500.000│
   └────┬─────┘   └──────┬───────┘   └────┬─────┘
        │                │                 │
        └────────────────┼─────────────────┘
                         ▼
              ┌─────────────────────┐
              │  SUBTOTAL           │
              │  TIM + Kepaniteraan │
              │     + Pemilah       │
              │  = 357.000.000      │
              └─────────┬───────────┘
                        ▼
              ┌─────────────────────┐
              │  PAJAK (PPN 15%)    │
              │  = 15% × SUBTOTAL   │
              │  = 53.550.000       │
              └─────────┬───────────┘
                        ▼
              ┌─────────────────────┐
              │  NETTO              │
              │  = SUBTOTAL - PAJAK │
              │  = 303.450.000      │
              └─────────────────────┘
```

### Tabel Ringkasan Nilai

| Komponen | Nilai | Sumber Data |
|:---------|------:|:------------|
| Jumlah Perkara | 1.700 | Rekap Keseluruhan, Row 9, Kolom D |
| Biaya per Perkara | Rp 210.000 *(perlu konfirmasi)* | Dari gambar yang belum dilihat |
| Total Biaya | Rp 357.000.000 | 1.700 × 210.000 |
| **TIM** | Rp 184.875.000 | Sheet `TIM`, baris 29–91, kolom JUMLAH BIAYA |
| **Kepaniteraan** | Rp *(perlu konfirmasi)* | Sheet `Kepaniteraan`, baris 32, kolom JUMLAH BIAYA |
| **Pemilah** | Rp 25.500.000 | Sheet `TIM` (sub-bagian Pemilah) |
| **Subtotal** | Rp 357.000.000 | TIM + Kepaniteraan + Pemilah |
| Pajak (15%) | Rp 53.550.000 | 15% × 357.000.000 |
| **Netto** | Rp 303.450.000 | 357.000.000 − 53.550.000 |

> **Cek silang:** 53.550.000 ÷ 0,15 = 357.000.000 ✅

---

## 2. Sumber Data di File Excel

### a. Sheet TIM (Baris 29–91)

| Item | Detail |
|:-----|:-------|
| Sheet | `TIM` |
| Baris data | 29 s/d 91 |
| Kolom diambil | `JUMLAH BIAYA` (kolom F) |
| Isi | Hakim Agung (TIM KOREKTOR), ASKOR/STAF TIM |
| Total orang | ~16 orang tersebar di beberapa blok |
| Nilai total | **Rp 184.875.000** |

Setiap blok di sheet TIM dimulai dengan judul:
```
HONORARIUM BIAYA PENYELESAIAN PERKARA [JENIS PERKARA] [PERIODE]
```

Kolom per baris:
```
NO | NAMA | Jabatan/Operator | JML PERKARA | BIAYA | JUMLAH BIAYA | PPH 5% | NETTO
```

### b. Sheet Kepaniteraan (Baris 32)

| Item | Detail |
|:-----|:-------|
| Sheet | `Kepaniteraan` |
| Baris | Baris 32 |
| Kolom diambil | `JUMLAH BIAYA` (kolom F) |
| Isi | Panitera Pengganti, Juru Sita, Staf, Pelaksana |
| Total orang | ~47 orang |

### c. Pemilah (Rp 25.500.000)

| Item | Detail |
|:-----|:-------|
| Sumber | Sheet `TIM` (bukan sheet `Pemilah`) |
| Posisi | Sub-bagian tertentu di sheet TIM |
| Nilai | Rp 25.500.000 |

> **Catatan:** Sheet `Pemilah` dan `Rekap Pemilah` ada di file Excel,
> namun saat ini **belum diparsing** oleh kode. Hanya sheet Kepaniteraan,
> TIM, dan OP-STAF yang di-parse oleh `parseHonorariumKamarSheet()`.

---

## 3. Status Kode Saat Ini

### Yang Sudah Ada

| Fitur | Status | Lokasi Kode |
|:------|:------:|:------------|
| Parsing sheet TIM | ✅ Ada | `parseHonorariumKamarSheet()` line 2930 |
| Parsing sheet Kepaniteraan | ✅ Ada | `parseHonorariumKamarSheet()` line 2930 |
| Parsing sheet OP-STAF | ✅ Ada | `parseHonorariumKamarSheet()` line 2930 |
| Tabel distribusi per jenis perkara | ✅ Ada | `rekapKeseluruhan2()` |
| Tabel honorarium BRUTO/PPH/NETTO | ✅ Ada | `rekapKeseluruhan3()` |
| Parsing sheet Pemilah | ❌ Belum | — |
| Agregasi TIM baris 29–91 | ❌ Belum | — |
| Pembacaan Kepaniteraan baris 32 | ❌ Belum | — |
| Perhitungan PPN/PPH 15% dari subtotal | ❌ Belum | — |
| Tampilan ringkasan TIM+Kep+Pemilah+Pajak | ❌ Belum | — |

### File-File Kunci

| File | Fungsi |
|:-----|:-------|
| `app/Http/Controllers/DashboardController.php` | Semua logika parsing & kalkulasi (3.153 baris) |
| `resources/views/rekap-keseluruhan-3.blade.php` | View tabel honorarium perkara |
| `resources/views/honorarium.blade.php` | View tab TIM/Kepaniteraan/OP-STAF |
| `resources/views/sheet-cek.blade.php` | View cek sheet (ada kolom TIM, KEPANITERAAN, PEMILAH) |

---

## 4. Pertanyaan yang Perlu Dijawab

> Mohon jawab agar prompt implementasi bisa akurat dan siap dieksekusi.

### A. Tentang Gambar
1. **Upload gambar** yang disebutkan — diperlukan untuk memverifikasi:
   - Nilai biaya per perkara (apakah Rp 210.000?)
   - Layout tampilan yang diinginkan
   - Kolom-kolom yang ditampilkan

### B. Tentang Rumus
2. **TIM baris 29–91**: Apakah ini SUM semua kolom "JUMLAH BIAYA" pada baris 29 s/d 91?
   Atau ada filter tertentu (misalnya hanya blok sub-tabel tertentu)?

3. **Kepaniteraan baris 32**: Apakah ini **satu baris saja** (baris 32)?
   Atau SUM dari range baris tertentu?

4. **Pemilah dari TIM**: Dari baris berapa di sheet TIM?
   Apakah ini sub-total dari orang-orang tertentu yang berkategori "Pemilah"?

5. **Pajak 15%**: Apakah ini **PPN** atau **PPH**?
   (Di kode dan Excel yang ada, istilah yang digunakan adalah **PPH** dengan dua tier: PPH 5% dan PPH 15%)

6. **Lokasi tampilan**: Perhitungan ini ditampilkan di halaman mana?
   - Rekap Keseluruhan 3?
   - Halaman honorarium?
   - Halaman baru tersendiri?
   - Atau sebagai komponen ringkasan di halaman yang sudah ada?

### C. Tentang Konteks
7. **Apakah 357.000.000 hanya untuk Kasasi PDT Umum** (1.700 perkara)?
   Atau total dari semua jenis perkara?

8. **Apakah perhitungan ini perlu per jenis perkara** (Kasasi PDT, Kasasi TUN, PK, dll.)
   atau cukup total keseluruhan?

---

## 5. Draft Prompt Implementasi

> Prompt ini akan disempurnakan setelah jawaban dan gambar diterima.

```
KONTEKS PROYEK:
Aplikasi Laravel (DashboardController.php) yang membaca file Excel
untuk menampilkan data honorarium biaya perkara Mahkamah Agung.

TUJUAN:
Implementasi perhitungan otomatis ringkasan biaya perkara yang menggabungkan
data dari sheet TIM, Kepaniteraan, dan Pemilah, beserta pajak (PPN/PPH 15%)
dan netto final.

SUMBER DATA:
─────────────────────────────────────────────────────────

1. BIAYA DASAR (357.000.000)
   Sumber : Sheet "Rekap Keseluruhan", baris 9, kolom D
   Rumus  : Jumlah Perkara (1.700) × Biaya per Perkara (Rp 210.000)
   Hasil  : Rp 357.000.000

2. TIM (184.875.000)
   Sumber : Sheet "TIM", baris 29 sampai 91
   Kolom  : JUMLAH BIAYA (kolom F)
   Cara   : SUM semua nilai JUMLAH BIAYA pada baris 29–91

3. KEPANITERAAN
   Sumber : Sheet "Kepaniteraan", baris 32
   Kolom  : JUMLAH BIAYA (kolom F)
   Cara   : Ambil nilai pada baris 32 kolom JUMLAH BIAYA

4. PEMILAH (25.500.000)
   Sumber : Sheet "TIM" (sub-bagian Pemilah, perlu konfirmasi baris)
   Cara   : Ambil nilai sub-total untuk baris berkategori Pemilah

RUMUS:
─────────────────────────────────────────────────────────

  SUBTOTAL  = TIM + KEPANITERAAN + PEMILAH
            = 184.875.000 + [Kepaniteraan] + 25.500.000
            = 357.000.000

  PAJAK     = 15% × SUBTOTAL
            = 0.15 × 357.000.000
            = 53.550.000

  NETTO     = SUBTOTAL − PAJAK
            = 357.000.000 − 53.550.000
            = 303.450.000

TAMPILAN YANG DIINGINKAN:
─────────────────────────────────────────────────────────

  ┌──────────────────────────────────────────────┐
  │  RINGKASAN PERHITUNGAN                       │
  ├────────────────────────┬─────────────────────┤
  │  TIM                   │  Rp 184.875.000     │
  │  Kepaniteraan          │  Rp ???             │
  │  Pemilah               │  Rp  25.500.000     │
  ├────────────────────────┼─────────────────────┤
  │  SUBTOTAL              │  Rp 357.000.000     │
  │  Pajak (PPN 15%)       │ (Rp  53.550.000)    │
  ├────────────────────────┼─────────────────────┤
  │  NETTO                 │  Rp 303.450.000     │
  └────────────────────────┴─────────────────────┘

CONSTRAINT IMPLEMENTASI:
─────────────────────────────────────────────────────────
- Jangan ubah logika parsing yang sudah berjalan
- Gunakan PhpSpreadsheet (sudah ter-install)
- Ikuti arsitektur DashboardController yang sudah ada
- Dukung dark mode via Tailwind CSS
- Data harus dibaca dinamis dari file Excel yang di-upload user
- Cache hasil di session (ikuti pola getCacheKey() yang sudah ada)
```

---

## 6. Rumus Lengkap Verifikasi

$$\text{Total Biaya} = \text{Jumlah Perkara} \times \text{Biaya per Perkara}$$
$$= 1.700 \times 210.000 = 357.000.000$$

$$\text{Subtotal} = TIM + Kepaniteraan + Pemilah$$

$$\text{Pajak} = 15\% \times \text{Subtotal} = 0{,}15 \times 357.000.000 = 53.550.000$$

$$\text{Netto} = \text{Subtotal} - \text{Pajak} = 357.000.000 - 53.550.000 = 303.450.000$$

---

*Dokumen ini diperbarui setelah gambar dan jawaban konfirmasi diterima dari user.*
*File terkait: `REKAP KESELURUHAN PERKARA PUTUS BULAN DESEMBER 2025 SD FEBRUARI 2026.xls`*
