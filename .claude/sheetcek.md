1. Aturan Header Kolom
Gunakan nama kolom asli untuk 3 kolom terakhir secara berurutan. Jangan diubah menjadi nama lain. Urutan kolom kanan adalah:

TOTAL (Kolom total pertama setelah Pajak)

TOTAL (Kolom total kedua / tengah)

TOTAL (Kolom total ketiga / paling kanan)

2. Aturan Kosongkan Baris Pembuka & Baris Pendek
Pada Baris Pembuka KASASI (yang biayanya 250.000): Karena dari kolom setelah BIAYA hingga ke kanan semuanya kosong di gambar asli, maka ketiga kolom TOTAL di ujung kanan WAJIB diisi kosong (- atau null). Jangan masukkan nilai total ke baris pembuka ini.

Pada Baris yang hanya terisi sampai kolom TIM: Jika sebuah baris hanya berisi data nominal sampai kolom TIM saja dan kolom-kolom setelahnya kosong, maka ketiga kolom TOTAL di ujung kanan juga WAJIB diisi kosong (- atau null).

3. Aturan Penempatan & Rowspan untuk 3 Kolom TOTAL
Hanya isi ketiga kolom TOTAL tersebut pada baris spesifik tempat angka itu berada di gambar dokumen asli:

TOTAL Pertama: Diisi hanya pada baris yang memiliki komponen nilai Pajak (misal baris Pajak 15% dan Pajak 5%).

TOTAL Kedua (Tengah - Khusus untuk Merged Row/1 Kolom Saja):

Jangan pecah barisnya menjadi data terpisah.

Tuliskan nilai nominalnya (misal: 228968750) hanya pada baris pertama di dalam blok kelompok tersebut, lalu berikan properti tambahan bernama "rowspan_total_2": 2 (atau sesuaikan dengan jumlah baris yang digabung pada kelompok tersebut).

Pada baris berikutnya di dalam kelompok yang sama, set nilai kolom TOTAL kedua ini menjadi "SKIP_OR_NULL" agar sistem tahu baris itu merupakan bagian dari penggabungan kolom.

TOTAL Ketiga (Paling Kanan): Diisi murni murni angka total akhir (misal: 470441888) hanya pada baris tempat angka tersebut tertulis di gambar asli. Sisanya biarkan null.

4. Format Output
Berikan hasil ekstrak data dalam bentuk array JSON yang bersih, dengan nilai nominal angka murni tanpa titik atau simbol Rp agar mudah dibaca oleh skrip frontend."

Cara Membaca Output JSON di Kode Frontend Kamu:
Setelah Gemini CLI mengeluarkan data dengan format di atas, pastikan kode looping tabel kamu (React atau Blade) membaca properti "rowspan_total_2".

Baris yang punya "rowspan_total_2": 2 akan dipasangi atribut <td rowspan="2">.

Baris berikutnya yang bernilai "SKIP_OR_NULL" akan langsung dilewati (tidak membuat tag <td>), sehingga kolom TOTAL kedua yang di tengah otomatis menyatu menjadi 1 kotak persis seperti Excel aslinya!
