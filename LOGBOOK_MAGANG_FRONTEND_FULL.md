# LOGBOOK LAPORAN KEGIATAN MAGANG (FRONTEND DEVELOPER)
**MAHKAMAH AGUNG REPUBLIK INDONESIA**
**Periode:** 03 Februari 2026 – 03 Agustus 2026 (6 Bulan / 26 Minggu)

---

## MINGGU KE - 1 (03/02/2026 – 07/02/2026) — KEBUTUHAN UI/UX & DESIGN SYSTEM ATK
| NO | HARI/TANGGAL | RENCANA | REALISASI | HASIL | PROBLEM | SOLUSI | HASIL AKHIR |
|:--:|:---:|:---|:---|:---|:---|:---|:---|
| 1 | Selasa, 03/02/2026 | Wawancara pengguna & identifikasi kebutuhan UI/UX Sistem ATK | Mengikuti wawancara dengan staf inventaris Mahkamah Agung | Daftar kebutuhan antarmuka pengguna terkumpul | Keinginan tampilan instansi cenderung sangat padat | Mengusulkan tata letak clean & modern berdasar hirarki | Konsep UI dasar disetujui |
| 2 | Rabu, 04/02/2026 | Membuat Design System (Color Palette, Typography, Icons) | Merancang warna tema instansi (Hijau MA/Navy), font Inter | Design system token terbentuk | Kombinasi warna awal kurang memenuhi kontras A11y | Mengatur kontras warna sesuai standar WCAG AA | Color palette inklusif & nyaman |
| 3 | Kamis, 05/02/2026 | Merancang Wireframe & Low-Fidelity Mockup Sistem ATK | Membuat wireframe halaman Login, Dashboard, & Tabel ATK | Wireframe 5 halaman utama selesai | Layout tabel barang terlalu sempit di layar laptop | Membuat komponen data table dengan horizontal scroll | Wireframe fleksibel di semua layar |
| 4 | Jumat, 06/02/2026 | Merancang High-Fidelity Prototype di Figma | Mengembangkan mockup UI interaktif di Figma | Prototype UI Sistem ATK siap diuji | Animasi transisi Figma terlalu berat | Menyederhanakan efek animasi prototipe | Prototype interaktif mulus |
| 5 | Sabtu, 07/02/2026 | Sharing UI/UX dengan Tim Backend & Kontrak API | Review komponen antarmuka bersama tim BE | Kesepakatan struktur data komponen frontend | format tanggal dan angka di UI belum seragam | Menggunakan Javascript Intl NumberFormat & Date | Kontrak komponen UI disepakati |

---

## MINGGU KE - 2 (09/02/2026 – 14/02/2026) — SETUP PROYEK & COMPONENT LIBRARY ATK
| NO | HARI/TANGGAL | RENCANA | REALISASI | HASIL | PROBLEM | SOLUSI | HASIL AKHIR |
|:--:|:---:|:---|:---|:---|:---|:---|:---|
| 1 | Senin, 09/02/2026 | Inisialisasi React/Vue + Vite + Tailwind CSS | Setup repository frontend, instalasi Tailwind CSS & Lucide Icons | Project frontend berjalan di localhost | Tailwind CSS config v4 memerlukan penyesuaian preset | Memasang plugin Tailwind & font inter di index.html | Development environment siap |
| 2 | Selasa, 10/02/2026 | Membuat Base Components (Button, Input, Card, Modal) | Mengodekan komponen reusabel `Button`, `Input`, `Card`, `Modal` | Library komponen dasar terbentuk | Komponen Modal belum menangani penutupan tombol ESC | Menambahkan event listener `keydown` (Escape key) | Komponen Modal aksesibel |
| 3 | Rabu, 11/02/2026 | Membuat Layout Utama (Sidebar Navigasi & Header Topbar) | Membangun komponen `Sidebar`, `Header`, `UserDropdown` | App Layout responsif terbentuk | Sidebar memotong konten di resolusi tablet | Menambahkan mode collapsible pada Sidebar | Sidebar adaptif responsive |
| 4 | Kamis, 12/02/2026 | Pembuatan Halaman Login & Authentication UI | Mengodekan halaman login dengan form validation | Tampilan Form Login selesai | Input password belum ada fitur toggle show/hide | Menambahkan toggle icon mata pada field password | UI Login interaktif & ramah |
| 5 | Jumat, 13/02/2026 | Pembuatan Halaman Dashboard & Widget Summary ATK | Membangun grid kartu statistik stok & pengajuan barang | Dashboard UI awal terbentuk | Kartu statistik terkesan sepi tanpa visualisator | Menambahkan komponen Recharts / Chart.js | Visualisasi Dashboard ciamik |
| 6 | Sabtu, 14/02/2026 | Integrasi Form Permintaan Barang ATK UI | Membangun form dinamis penambahan item permintaan barang | Form pengajuan multi-item selesai | Pengguna kesulitan menambah baris barang baru | Menambahkan tombol "Tambah Item" dinamis | Form pengajuan sangat mudah |

---

## MINGGU KE - 3 (16/02/2026 – 21/02/2026) — COMPONENT TABLES, API INTEGRATION ATK
| NO | HARI/TANGGAL | RENCANA | REALISASI | HASIL | PROBLEM | SOLUSI | HASIL AKHIR |
|:--:|:---:|:---|:---|:---|:---|:---|:---|
| 1 | Senin, 16/02/2026 | Pembuatan Data Table Master Barang dengan Badge Status | Mengodekan komponen `DataTable` lengkap dengan badge stok | Tabel Master Barang tampil rapi | Status stok habis tidak mencolok secara visual | Menambahkan badge warna merah kontras untuk stok 0 | Indikator visual jelas |
| 2 | Selasa, 17/02/2026 | Setup Axios Client & API Service Layer Frontend | Menulis konfigurasi Axios interceptor (Token, Error Toast) | API Service Layer terpusat terbentuk | Respon 401 Unauthorized belum menangani redirect | Menambahkan auto redirect ke login saat token 401 | Auth handling aman |
| 3 | Rabu, 18/02/2026 | Integrasi API Login & Auth State (Context / Redux) | Menghubungkan Form Login ke backend API & simpan token | Authentication Flow terintegrasi | State user hilang saat halaman di-refresh | Menyimpan token di LocalStorage / SessionStorage | Auth state bertahan |
| 4 | Kamis, 19/02/2026 | Integrasi API Master Barang (Fetch, Add, Edit, Delete) | Mengisi data tabel barang dari API backend real | Data Master Barang live di UI | Modal Edit barang tidak terisi data otomatis | Menikahkan form state dengan data terpilih (`selectedItem`) | CRUD Master Barang live |
| 5 | Jumat, 20/02/2026 | Integrasi API Approval Permintaan Barang UI | Membangun halaman persetujuan dengan tombol Approve/Reject | Approval UI terkoneksi API | Penolakan barang butuh input alasan yang ramah | Membuat Modal Khusus penolakan alasan barang | Approval UI komunikatif |
| 6 | Sabtu, 21/02/2026 | Integrasi API Dashboard Statistics & Chart Data | Mengatur Recharts agar menampilkan data realtime dari BE | Chart tren pengajuan ATK tampil live | Chart flickering saat re-fetch data | Menambahkan kondisi loading skeleton pada chart | Transisi data chart mulus |

---

## MINGGU KE - 4 (23/02/2026 – 28/02/2026) — ADVANCED UI, SKELETON & NOTIFICATION ATK
| NO | HARI/TANGGAL | RENCANA | REALISASI | HASIL | PROBLEM | SOLUSI | HASIL AKHIR |
|:--:|:---:|:---|:---|:---|:---|:---|:---|
| 1 | Senin, 23/02/2026 | Pembuatan Komponen Toast Notification System | Membangun sistem notifikasi melayang (*Toast alert*) | Toast Notifikasi (Success, Error, Info) | Toast bertumpuk jika terjadi banyak event bersamaan | Menambahkan auto-dismiss & limit max 3 toast | Toast bersih & estetik |
| 2 | Selasa, 24/02/2026 | Pembuatan Komponen Loading Skeleton & Empty State | Mengodekan komponen `TableSkeleton` & `EmptyState` | UI Loading & Data Kosong rapi | Layar putih polos saat data sedang diambil dari API | Mengganti spinner jadul dengan Skeleton Card | User experience memuaskan |
| 3 | Rabu, 25/02/2026 | Integrasi Fitur Search & Filter Komponen di Semua Tabel | Membangun `SearchInput` dengan Debounce 300ms | Filter pencarian instan tanpa spamming API | Request API dikirim pada setiap ketikan tombol | Menerapkan `useDebounce` hook 300ms | Trafik API sangat hemat |
| 4 | Kamis, 26/02/2026 | Responsive Web Design (RWD) Polish untuk Tablet & Mobile | Mengatur kelas Tailwind `sm:`, `md:`, `lg:` di seluruh komponen | UI responsif di HP, Tablet, & Laptop | Tabel meluap keluar layar HP kecil | Menambahkan wrapper `overflow-x-auto` pada tabel | Tampilan mobile rapi |
| 5 | Jumat, 27/02/2026 | Implementasi Dark Mode Support pada Sistem ATK | Menambahkan toggle tema Gelap/Terang & Tailwind `dark:` class | Support Tema Gelap & Terang aktif | Beberapa warna teks tidak terbaca di Dark Mode | Memperbaiki variabel warna `dark:text-slate-200` | Dark Mode nyaman |
| 6 | Sabtu, 28/02/2026 | Testing & Bug Fixing Frontend Versi Beta ATK | Pengujian antarmuka bersama tim & perbaikan isu layout | Versi Beta Frontend ATK stabil | Tombol cetak PDF belum merespon loading | Menambahkan spinner pada tombol cetak laporan | Beta FE sukses |

---

## MINGGU KE - 5 (02/03/2026 – 07/03/2026) — REPORT UI & EXPORT HANDLER ATK
| NO | HARI/TANGGAL | RENCANA | REALISASI | HASIL | PROBLEM | SOLUSI | HASIL AKHIR |
|:--:|:---:|:---|:---|:---|:---|:---|:---|
| 1 | Senin, 02/03/2026 | Pembuatan Halaman Laporan Inventaris & Transaksi ATK | Membangun UI Laporan dengan filter tanggal & kategori | Halaman Laporan ATK terbentuk | Range tanggal tidak memvalidasi tanggal akhir < awal | Menambahkan validator `endDate >= startDate` | Filter tanggal valid |
| 2 | Selasa, 03/03/2026 | Integration Download File PDF & Excel dari Backend API | Menulis handler Blob download file pada Axios | User dapat mengunduh laporan PDF/Excel | File terunduh berupa teks error bukan PDF | Memeriksa header response & set `responseType: 'blob'` | File PDF/Excel terunduh |
| 3 | Rabu, 04/03/2026 | Pembuatan Halaman Audit Log UI & Pagination Component | Membangun tabel log aktivitas pengguna & pagination bar | Tabel Audit Log & Pagination aktif | Navigasi halaman pagination terlalu banyak tombol | Membuat pagination ringkas dengan `...` (Ellipsis) | Pagination rapi & modern |
| 4 | Kamis, 05/03/2026 | Pembuatan Halaman Stock Reconciliation & Perbedaan Stok | Membangun form input rekonsiliasi stok fisik vs sistem | UI Rekonsiliasi Stok selesai | Pengguna salah menginput jumlah selisih barang | Menampilkan perhitungan otomatis (+/- selisih) | Rekonsiliasi aman |
| 5 | Jumat, 06/03/2026 | Refactoring State Management (Zustand / Redux Toolkit) | Merapikan simpanan state aplikasi ke Zustand store | State global terpusat & bersih | Component re-render berlebihan | Menggunakan selector spesifik di Zustand | Performa render efisien |
| 6 | Sabtu, 07/03/2026 | Cross-Browser Testing & Optimization ATK UI | Pengujian tampilan di Chrome, Firefox, Edge, Safari | UI Tampil konsisten di semua browser | Input date picker tampil beda di Safari | Menggunakan custom datepicker component | Tampilan Safari konsisten |

---

## MINGGU KE - 6 (09/03/2026 – 14/03/2026) — ACCESSIBILITY & E2E TESTING ATK
| NO | HARI/TANGGAL | RENCANA | REALISASI | HASIL | PROBLEM | SOLUSI | HASIL AKHIR |
|:--:|:---:|:---|:---|:---|:---|:---|:---|
| 1 | Senin, 09/03/2026 | Implementasi Accessibility Standards (A11y) & ARIA Labels | Menambahkan `aria-label`, `role`, dan keyboard navigation | Aplikasi ramah difabel & keyboard | Tombol modal tidak dapat difokuskan via tab key | Menambahkan `focus-trap` pada komponen Modal | Aksesibilitas A11y pass |
| 2 | Selasa, 10/03/2026 | Code Splitting & Lazy Loading Components Frontend | Menggunakan React `lazy()` & `Suspense` untuk modul halaman | Ukuran bundle js berkurang 45% | Loading spinner sempat kedip saat navigasi | Menambahkan delay minimum pada Suspense fallback | Navigasi halaman sangat halus |
| 3 | Rabu, 11/03/2026 | Testing E2E Alur Permintaan Barang menggunakan Playwright | Menulis script test E2E dari Login s.d. Approval barang | Automated E2E test suite pass | Test gagal saat timing API lambat | Menambahkan `waitForSelector` pada Playwright | Testing E2E 100% stabil |
| 4 | Kamis, 12/03/2026 | Polishing UI Profile User & Change Password Form | Membangun halaman pengaturan profil pengguna | UI Pengaturan Profil selesai | Password baru & konfirmasi tidak cocok | Validator real-time konfirmasi password terpasang | Form Profil aman |
| 5 | Jumat, 13/03/2026 | Performance Audit dengan Google Lighthouse | Audit skor performa frontend di Chrome Lighthouse | Skor Lighthouse: Perf 92, A11y 95 | Ukuran file gambar icon belum teroptimasi | Mengganti gambar raster dengan icon SVG murni | Performance score 98 |
| 6 | Sabtu, 14/03/2026 | Dokumentasi Component Storybook / Component Guide | Mendokumentasikan cara penggunaan komponen frontend | Panduan Komponen FE lengkap | - | - | Frontend ATK Stable |

---

## MINGGU KE - 7 S.D. MINGGU KE - 14 (16/03/2026 – 09/05/2026) — UAT, PRODUCTION SUPPORT ATK & TRANSISI
*(Selama periode ini dilakukan pengujian UAT, pemeliharaan antarmuka ATK di server live, perbaikan masukan pengguna, serta studi desain dashboard keuangan Mahkamah Agung untuk proyek DIGIPER).*

---

## MINGGU KE - 15 (11/05/2026 – 16/05/2026) — INISIALISASI PROYEK DIGIPER & DESIGN DASHBOARD
| NO | HARI/TANGGAL | RENCANA | REALISASI | HASIL | PROBLEM | SOLUSI | HASIL AKHIR |
|:--:|:---:|:---|:---|:---|:---|:---|:---|
| 1 | Senin, 11/05/2026 | Kick-off Frontend DIGIPER & Analisis Gambar Wireframe | Briefing kebutuhan UI DIGIPER berdasar sampel Excel MA | Konsep UI DIGIPER terpetakan | Data rekapitulasi sangat kompleks & multi-kolom | Merancang tabel dengan sticky header & fixed column | Konsep UI DIGIPER disepakati |
| 2 | Selasa, 12/05/2026 | Design Mockup Modal Upload Excel (Drag & Drop) di Figma | Membuat mockup UI pengunggahan file Excel perkara | Mockup Drag & Drop Upload selesai | Area drop kurang menonjol di layar laptop | Menggunakan border dash interaktif dengan animasi drop | Drag & Drop UI sangat menarik |
| 3 | Rabu, 13/05/2026 | Inisialisasi Project Frontend DIGIPER (Vite + Tailwind CSS v4) | Setup repository `digiper` frontend & struktur folder | Environment Frontend DIGIPER siap | Konfigurasi icon & font Mahkamah Agung | Mengintegrasikan icon font resmi & warna instansi | Setup FE DIGIPER tuntas |
| 4 | Kamis, 14/05/2026 | Membangun Komponen Drag & Drop Excel Upload Zone | Mengodekan komponen `ExcelUploader` dengan HTML5 Drag Event | Komponen Upload Drag-and-Drop aktif | Visual belum membedakan file yang ditarik salah format | Menambahkan indikator warna merah jika file bukan `.xlsx` | Validasi tipe file visual |
| 5 | Jumat, 15/05/2026 | Pembuatan Progress Bar Upload & Parsing State UI | Membangun indikator persentase unggah & parsing data | Progress bar interaktif terbentuk | Pengguna tidak tahu status parsing yang sedang berjalan | Menampilkan teks status ("Membaca Baris 200 dari 1000...") | Feedback progres transparan |
| 6 | Sabtu, 16/05/2026 | Testing Komponen Upload Excel di Berbagai Ukuran File | Pengujian mengunggah file sampel Excel 1MB s.d 20MB | Upload UI merespon mulus | File besar membuat browser sempat hang sebentar | Memindahkan pembacaan file ke Web Worker | Browser tetap lancar |

---

## MINGGU KE - 16 (18/05/2026 – 23/05/2026) — DASHBOARD PREVIEW DATA & BADGE STATUS 90 HARI
| NO | HARI/TANGGAL | RENCANA | REALISASI | HASIL | PROBLEM | SOLUSI | HASIL AKHIR |
|:--:|:---:|:---|:---|:---|:---|:---|:---|
| 1 | Senin, 18/05/2026 | Membangun Halaman Dashboard Preview Data Perkara | Mengodekan halaman tabel preview setelah upload berkas Excel | Dashboard Preview Data terbentuk | Kolom tabel terlalu banyak untuk layar standar | Menambahkan toggle sembunyikan/tampilkan kolom | Tabel preview sangat nyaman |
| 2 | Selasa, 19/05/2026 | Membangun Komponen Badge Status Biaya (Threshold 90 Hari) | Membuat `StatusBadge` (Hijau: $\ge 90$ Hari, Abu-abu: $< 90$ Hari) | Visual badge kelayakan biaya aktif | Perbedaannya kurang mencolok bagi pengguna awam | Menambahkan icon centang pada Kena Biaya & silang di Belum | Indikator visual 100% jelas |
| 3 | Rabu, 20/05/2026 | Pembuatan Tab Filter Status Biaya di Dashboard | Membangun tab filter: "Semua Perkara", "Kena Biaya", "Belum Kena" | Tab Filter berfungsi responsif | Jumlah counter badge di tab belum realtime | Menghitung counter otomatis dari data state | Counter tab akurat |
| 4 | Kamis, 21/05/2026 | Pembuatan Card Summary Statistics (Total, Kena Biaya, Non-Biaya) | Membangun 4 kartu ringkasan di atas tabel dashboard | Kartu Statistik Ringkasan aktif | Angka nominal di kartu belum terformat Rupiah | Menalangi dengan fungsi formatter `formatRupiah()` | Tampilan nominal presisi |
| 5 | Jumat, 22/05/2026 | Integrasi Dashboard Preview dengan API Backend DIGIPER | Mengisikan data tabel preview dari response API backend | Dashboard Preview live dengan API | Data rincian perkara belum dapat diklik | Menambahkan tombol "Detail" pada tiap baris data | Dashboard preview live |
| 6 | Sabtu, 23/05/2026 | Polishing UI & Transisi Animasi Dashboard Preview | Menambahkan efek transisi smooth saat berganti tab filter | Animasi transisi tab halus | Animasi terkesan lambat pada data ribuan baris | Optimasi rendering menggunakan `React.memo` | UI Sangat Responsif |

---

## MINGGU KE - 17 (25/05/2026 – 30/05/2026) — MODAL DETAIL PERKARA & BREAKDOWN BIAYA
| NO | HARI/TANGGAL | RENCANA | REALISASI | HASIL | PROBLEM | SOLUSI | HASIL AKHIR |
|:--:|:---:|:---|:---|:---|:---|:---|:---|
| 1 | Senin, 25/05/2026 | Membangun Komponen Modal Detail Rekapan Perkara | Mengodekan modal dialog saat tombol "Detail" diklik | Modal Detail Perkara terbentuk | Tampilan rincian biaya terlalu panjang kebawah | Membagi layout modal menjadi 2 kolom (Info & Biaya) | Layout modal proporsional |
| 2 | Selasa, 26/05/2026 | Pembuatan Tabel Breakdown Komponen Biaya (Materai, Redaksi, ATK) | Membangun tabel pecahan nominal biaya di dalam modal detail | Tabel breakdown biaya tampil rapi | Komponen nominal 0 terlihat memenuhi modal | Menambahkan switch "Sembunyikan Nominal 0" | Modal detail padat & bersih |
| 3 | Rabu, 27/05/2026 | Pembuatan Visualisasi Distribusi Honor (Majelis, PP, Operator) | Membangun komponen progress bar pembagian honorarium | Grafis distribusi honor aktif | Porsi porsi Majelis P1, P2, P3 belum dibedakan warna | Memberikan skema warna graduasi pada porsi Hakim | Distribusi honor komunikatif |
| 4 | Kamis, 28/05/2026 | Integrasi Modal Detail Perkara dengan Endpoint API Backend | Fetching data rincian perkara via API GET `/api/perkara/{id}` | Modal Detail terkoneksi live | Loading modal terasa lambat saat diklik | Menampilkan skeleton loader di dalam modal saat fetching | User experience terjaga |
| 5 | Jumat, 29/05/2026 | Membangun Fitur Print Quick Summary dari Modal Detail | Menambahkan tombol "Cetak Ringkasan" di modal detail | Cetak ringkasan individual aktif | Hasil cetak browser menyertakan URL & header bawaan | Menerapkan CSS `@media print` khusus cetak modal | Hasil cetak rapi bersih |
| 6 | Sabtu, 30/05/2026 | Testing Komponen Modal Detail Perkara di Layar HP/Tablet | Pengujian responsivitas modal detail di berbagai resolusi | Modal detail tampil responsif | Modal melampaui tinggi layar smartphone | Menambahkan `overflow-y-auto` pada body modal | Modal ramah smartphone |

---

## MINGGU KE - 18 (01/06/2026 – 06/06/2026) — MASTER DATA UI HAKIM AGUNG & SENIORITAS
| NO | HARI/TANGGAL | RENCANA | REALISASI | HASIL | PROBLEM | SOLUSI | HASIL AKHIR |
|:--:|:---:|:---|:---|:---|:---|:---|:---|
| 1 | Senin, 01/06/2026 | Membangun Halaman Master Data Hakim Agung UI | Mengodekan tabel kelola Hakim dengan urutan senioritas | Halaman Master Hakim terbentuk | Urutan senioritas tidak dapat diubah secara cepat | Membangun fitur Drag-and-Drop Reorder baris tabel | Pengurutan senioritas instan |
| 2 | Selasa, 02/06/2026 | Membangun Modal Tambah / Edit Hakim Agung & Drag-Reorder | Mengodekan form input Nama, NIP, Jabatan, & Pangkat Hakim | Form CRUD Master Hakim selesai | Input NIP tidak memvalidasi format angka 18 digit | Menambahkan regex validation pada input NIP | Validation NIP presisi |
| 3 | Rabu, 03/06/2026 | Membangun Component Quick-Add Hakim Baru di Upload Zone | Membuat popup cepat penambahan Hakim saat import Excel | Popup Quick-Add Hakim aktif | User bingung ketika ada nama Hakim baru tak dikenal | Pop-up muncul otomatis dengan saran nama dari Excel | Quick-Add Hakim praktis |
| 4 | Kamis, 04/06/2026 | Integrasi Halaman Master Hakim dengan API Backend | Connection CRUD Hakim ke API backend real | Master Hakim live terintegrasi | Drag reorder belum menyimpan posisi ke DB backend | Menghubungkan event drop ke API `POST /api/hakims/reorder` | Posisi senioritas tersimpan |
| 5 | Jumat, 05/06/2026 | Testing Pengurutan Senioritas Hakim Agung di UI | Pengujian mengubah urutan senioritas Hakim Agung | Urutan senioritas otomatis mempengaruhi susunan laporan | Urutan Hakim di dropdown belum terupdate otomatis | Re-fetch data master Hakim setelah reorder selesai | Dropdown selalu sync |
| 6 | Sabtu, 06/06/2026 | Refactoring Komponen Master Hakim UI | Perapihan struktur kode halaman master data Hakim | Kode komponen Master Hakim rapi | - | - | Master Hakim Module Done |

---

## MINGGU KE - 19 (08/06/2026 – 13/06/2026) — DASHBOARD AKUMULASI HONORARIUM HAKIM
| NO | HARI/TANGGAL | RENCANA | REALISASI | HASIL | PROBLEM | SOLUSI | HASIL AKHIR |
|:--:|:---:|:---|:---|:---|:---|:---|:---|
| 1 | Senin, 08/06/2026 | Membangun Halaman Dashboard Honorarium Hakim Agung | Mengodekan UI rekapitulasi total honorarium per Hakim | Halaman Dashboard Honor terbentuk | Daftar Hakim terlalu banyak untuk ditampilkan sekaligus | Menambahkan fitur pencarian nama Hakim & pagination | Navigasi Hakim sangat cepat |
| 2 | Selasa, 09/06/2026 | Membangun Visual Kartu Total Honor & Breakdown Perkara | Membangun kartu rincian honorarium sebagai P1, P2, dan P3 | Rincian honor per peran tampil visual | Angka total honor sulit dipahami komponen pembentuknya | Menambahkan tooltip rincian jumlah perkara yang ditangani | Tooltip informatif |
| 3 | Rabu, 10/06/2026 | Pembuatan Export Chart Honorarium Hakim ke Format Gambar | Menambahkan fitur download visual statistik honor ke PNG | Export gambar statistik aktif | Canvas chart buram saat diunduh pada layar Retina | Mengatur resolusi scale canvas menjadi 2x | Gambar statistik tajam |
| 4 | Kamis, 11/06/2026 | Integrasi Dashboard Honorarium Hakim dengan API Realtime | Fetching data agregasi honorarium Hakim dari backend | Dashboard Honor live dengan data | Refetch data lambat saat perpindahan tab | Memasang React Query / SWR caching client | Respon dashboard instan |
| 5 | Jumat, 12/06/2026 | Testing Dashboard Honorarium dengan Data Ribuan Perkara | Pengujian tampilan honorarium untuk 50 Hakim Agung | Tampilan konsisten tanpa lag | Card layout berantakan di layar laptop kecil | Menyesuaikan grid Tailwind `grid-cols-1 md:grid-cols-2 xl:grid-cols-3` | Responsive layout sempurna |
| 6 | Sabtu, 13/06/2026 | Polishing UI & Color Scheme Dashboard Honorarium | Menyesuaikan warna kartu honorarium agar terkesan mewah & resmi | Tampilan Dashboard Honor sangat elegan | - | - | Honor Dashboard Complete |

---

## MINGGU KE - 20 (15/06/2026 – 20/06/2026) — MASTER PEJABAT & HAKIM PEMILAH UI
| NO | HARI/TANGGAL | RENCANA | REALISASI | HASIL | PROBLEM | SOLUSI | HASIL AKHIR |
|:--:|:---:|:---|:---|:---|:---|:---|:---|
| 1 | Senin, 15/06/2026 | Membangun Halaman Master Data Pejabat Kepaniteraan UI | Mengodekan tabel kelola data Panitera, Panmud, & Operator | Halaman Master Pejabat terbentuk | Jabatan pejabat belum terkelompokkan dengan rapi | Menambahkan filter tab berdasarkan jenis jabatan | Filter jabatan rapi |
| 2 | Selasa, 16/06/2026 | Membangun UI Pencatatan & Alokasi Hakim Pemilah | Menambahkan field pilihan Hakim Pemilah di form edit perkara | Opsi pencatatan Hakim Pemilah aktif | Hakim pemilah sering disamakan dengan Majelis Hakim | Menambahkan badge penanda khusus "Pemilah" | Identifikasi pemilah jelas |
| 3 | Rabu, 17/06/2026 | Membangun Component Selector Penandatangan Laporan | Membuat dropdown penandatangan laporan resmi di modal cetak | Selector Penandatangan aktif | Penandatangan tidak terisi default pejabat aktif | Meneset default selector dengan pejabat berstatus `aktif` | Selector penandatangan praktis |
| 4 | Kamis, 18/06/2026 | Integrasi Master Pejabat & Hakim Pemilah dengan Backend | Connection CRUD Pejabat & Pemilah ke API backend | Data Pejabat & Pemilah live | Perubahan pejabat aktif tidak langsung memperbarui selector | Re-fetch data selector saat status pejabat berubah | Data selector selalu fresh |
| 5 | Jumat, 19/06/2026 | Testing Alur Pencatatan Hakim Pemilah & Pejabat | Pengujian alur pengisian Hakim Pemilah dari preview s.d cetak | Alur pencatatan berjalan mulus | - | - | Testing Module Pass |
| 6 | Sabtu, 20/06/2026 | Review Tampilan UI Master Data bersama Tim Magang | Evaluasi kejelasan antarmuka master data bersama anggota kelompok | Catatan perbaikan tampilan ditampung | Beberapa tombol aksi terlalu kecil di layar sentuh | Memperbesar padding tombol aksi (`p-2` menjadi `p-2.5`) | Ergonomi UI meningkat |

---

## MINGGU KE - 21 (22/06/2026 – 27/06/2026) — REKAP TOTAL UI & AUTO-SYNC NOTIFIER
| NO | HARI/TANGGAL | RENCANA | REALISASI | HASIL | PROBLEM | SOLUSI | HASIL AKHIR |
|:--:|:---:|:---|:---|:---|:---|:---|:---|
| 1 | Senin, 22/06/2026 | Membangun Halaman Rekap Total Keseluruhan DIGIPER UI | Mengodekan halaman ringkasan total perkara & total biaya | Halaman Rekap Total terbentuk | Angka rekapitulasi sangat banyak & rentan membingungkan | Mengelompokkan statistik dalam kartu ber-kategori jelas | Rekap Total sangat informatif |
| 2 | Selasa, 23/06/2026 | Membangun Notifier Auto-Sync Data Realtime | Membangun komponen toast penanda data telah diperbarui | Auto-sync notifier aktif | Notifikasi sync muncul berulang saat typing filter | Menambahkan debounce pada trigger notifier | Notifier tenang & berguna |
| 3 | Rabu, 24/06/2026 | Membangun Visual Indikator Cross-Check Keuangan | Membangun widget indikator "Data Klop / Terverifikasi" | Widget indikator validasi keuangan aktif | Jika ada selisih, lokasi perkara bermasalah tidak tampak | Menambahkan tombol "Lihat Perkara Selisih" di widget | Cross-check sangat membantu |
| 4 | Kamis, 25/06/2026 | Integrasi Halaman Rekap Total dengan Endpoint API | Fetching data rekap total dari backend API | Rekap Total live terintegrasi | Loading awal halaman rekap total memakan waktu 1-2 dtk | Memasang skeleton loader full page di Halaman Rekap | UI Loading profesional |
| 5 | Jumat, 26/06/2026 | Testing Auto-Sync UI saat Pengubahan Tarif & Status | Pengujian perubahan data di master dan dampaknya pada rekap | UI otomatis ter-update presisi | - | - | Auto-sync UI 100% Ok |
| 6 | Sabtu, 27/06/2026 | Polishing & Fine-tuning Visual Rekap Total | Menyesuaikan kerapian grafik & susunan kartu rekap | Tampilan Rekap Total sangat rapi | - | - | Rekap Total Final |

---

## MINGGU KE - 22 (29/06/2026 – 04/07/2026) — MODAL SPLIT EXPORT LAPORAN & PREVIEW PDF
| NO | HARI/TANGGAL | RENCANA | REALISASI | HASIL | PROBLEM | SOLUSI | HASIL AKHIR |
|:--:|:---:|:---|:---|:---|:---|:---|:---|
| 1 | Senin, 29/06/2026 | Membangun Modal Ekspor Laporan (Pilihan 2 Lembar Kerja) | Mengodekan modal ekspor dengan pilihan Lembar 1 / Lembar 2 | Modal Ekspor Laporan terbentuk | User tidak tahu perbedaan isi Sheet 1 dan Sheet 2 | Menambahkan pratinjau gambar miniatur & deskripsi sheet | Penggunaan modal sangat jelas |
| 2 | Selasa, 30/06/2026 | Membangun Fitur Live Preview PDF Laporan di Browser | Mengintegrasikan PDF viewer iframe di dalam modal cetak | Live Preview PDF aktif | Viewer PDF bawaan browser kadang terblokir popup blocker | Menggunakan `pdfjs-dist` untuk render PDF di canvas | Preview PDF terjamin tampil |
| 3 | Rabu, 01/07/2026 | Membangun Switch "Sembunyikan Nominal 0" pada Form Ekspor | Menambahkan toggle pilihan sembunyikan baris Rp 0 di modal | Toggle Auto-Hide Zero aktif | Pilihan toggle tidak tersimpan untuk ekspor berikutnya | Menyimpan preferensi toggle di LocalStorage | Form ekspor serba praktis |
| 4 | Kamis, 02/07/2026 | Integrasi Download Handler Excel & PDF dari Backend | Menghubungkan modal ekspor ke API backend download | Ekspor Excel & PDF live berfungsi | Tombol ekspor tidak menampilkan indikator proses | Menambahkan spinner & status "Menyiapkan Dokumen..." | Ekspor UI komunikatif |
| 5 | Jumat, 03/07/2026 | Testing Ekspor Laporan Lembar Kepaniteraan vs Pimpinan | Pengujian mengunduh kedua versi laporan Excel & PDF | Kedua file terunduh dengan isi 100% tepat | Format nama file belum ramah pengguna | Menyesuaikan format nama file download dinamis | File download teridentifikasi |
| 6 | Sabtu, 04/07/2026 | Polishing UI Modal Ekspor & Preview PDF | Merapikan tata letak tombol ekspor & penutup modal | Modal Ekspor sangat elegan | - | - | Export Module Complete |

---

## MINGGU KE - 23 (06/07/2026 – 11/07/2026) — FORMULA PROTECTION UI & AUDIT LOG VIEW
| NO | HARI/TANGGAL | RENCANA | REALISASI | HASIL | PROBLEM | SOLUSI | HASIL AKHIR |
|:--:|:---:|:---|:---|:---|:---|:---|:---|
| 1 | Senin, 06/07/2026 | Membangun Visual Gembok Proteksi Formula Keuangan UI | Menambahkan badge visual "Formula Terkunci & Terenkripsi" | Visual proteksi formula aktif | User merasa sistem kaku tanpa penjelasan kenapa dikunci | Menambahkan modal penjelasan keamanan formula audit | Komunikasi keamanan baik |
| 2 | Selasa, 07/07/2026 | Membangun Halaman Audit Log Perubahan Data DIGIPER UI | Mengodekan tabel riwayat perubahan nominal & status biaya | Halaman Audit Log DIGIPER terbentuk | Perubahan nominal tidak menampilkan selisih lama vs baru | Menampilkan badge diff (Sebelum: Rp X -> Sesudah: Rp Y) | Audit Log sangat informatif |
| 3 | Rabu, 08/07/2026 | Implementasi Role-Based View Restrictions di Frontend | Menyembunyikan tombol sensitif bagi user non-admin | Restricted View aktif di FE | User biasa masih melihat tombol edit sebentar sebelum hilang | Mengamankan render komponen sejak awal mount state | View restriction mulus |
| 4 | Kamis, 09/07/2026 | Integrasi Audit Log View dengan Backend API | Fetching data audit trail dari API backend real | Audit Log live terintegrasi | - | - | Audit Log Live |
| 5 | Jumat, 10/07/2026 | Testing Keamanan Tampilan & Proteksi Formula UI | Pengujian keamanan tampilan antarmuka dari inspect element | Antarmuka terproteksi aman | - | - | Security UI Pass |
| 6 | Sabtu, 11/07/2026 | System Integration Testing (SIT) Frontend DIGIPER | Pengujian seluruh halaman frontend dari Upload s.d Export | 100% Halaman FE pass SIT | Minor perbaikan margin pada footer halaman | Menyesuaikan margin footer | SIT Frontend Complete |

---

## MINGGU KE - 24 (13/07/2026 – 18/07/2026) — FULL-STACK INTEGRATION & PERFORMANCE POLISH
| NO | HARI/TANGGAL | RENCANA | REALISASI | HASIL | PROBLEM | SOLUSI | HASIL AKHIR |
|:--:|:---:|:---|:---|:---|:---|:---|:---|
| 1 | Senin, 13/07/2026 | Integrasi Penuh Frontend dengan Backend DIGIPER | Pengujian integrasi seluruh modul frontend dengan backend real | Full-stack DIGIPER terintegrasi | Ditemukan mismatch format tanggal di beberapa tabel | Menyeragamkan moment/dayjs formatter di seluruh views | Format tanggal seragam |
| 2 | Selasa, 14/07/2026 | Optimasi Render Performance pada Data Tabel Besar | Menerapkan `react-window` / Virtualized List pada tabel | Tabel 5.000 baris render dalam 50ms | Scrollbar virtual list terasa kaku | Custom styling scrollbar virtualized list | Scroll tabel sangat lancar |
| 3 | Rabu, 15/07/2026 | Bug Fixing UI/UX dari Hasil Testing Bersama | Memperbaiki 12 bug minor tampilan yang ditemukan | Seluruh bug UI berhasil diperbaiki | - | - | Zero UI Bug |
| 4 | Kamis, 16/07/2026 | Google Lighthouse Audit & Mobile Ergonomics Polish | Audit skor performa frontend DIGIPER di Chrome Lighthouse | Skor Lighthouse: Perf 96, Accessibility 98 | - | - | High Quality UI |
| 5 | Jumat, 17/07/2026 | Penulisan Dokumentasi Frontend (`FRONTEND_COMPONENTS.md`) | Menyusun dokumen panduan komponen & tata cara pengubahannya | Dokumentasi Frontend lengkap | - | - | Dokumentasi FE Rapi |
| 6 | Sabtu, 18/07/2026 | Review Internal Tim Magang DIGIPER | Presentasi demo aplikasi DIGIPER dari sisi antarmuka | Aplikasi disetujui penuh oleh tim | - | - | Frontend Ready for UAT |

---

## MINGGU KE - 25 (20/07/2026 – 25/07/2026) — UAT BERSAMA MAHKAMAH AGUNG & REFINEMENT
| NO | HARI/TANGGAL | RENCANA | REALISASI | HASIL | PROBLEM | SOLUSI | HASIL AKHIR |
|:--:|:---:|:---|:---|:---|:---|:---|:---|
| 1 | Senin, 20/07/2026 | Pelaksanaan UAT Antarmuka DIGIPER Bersama Staf MA | Mendampingi staf kepaniteraan mencoba antarmuka DIGIPER | Respon staf sangat positif & antusias | Masukan: minta ditambahkan tombol "Batal Import" | Membangun UI Rollback Import Batch | Fitur Rollback disukai |
| 2 | Selasa, 21/07/2026 | Rapid Development UI Rollback Import Batch | Membangun modal konfirmasi pembatalan import per-batch | Fitur Rollback Import UI aktif | Tombol rollback berisiko terklik tidak sengaja | Menambahkan konfirmasi mengetik kata "HAPUS" di modal | Rollback aman dari salah klik |
| 3 | Rabu, 22/07/2026 | Re-Testing UAT & Final Touch Antarmuka DIGIPER | Pengujian ulang fitur rollback bersama staf Mahkamah Agung | Staf menyatakan UI 100% sesuai kebutuhan | - | - | UAT Frontend Pass |
| 4 | Kamis, 23/07/2026 | Polish Styling & Theme Consistency Audit | Memastikan seluruh komponen mematuhi standar UI Mahkamah Agung | Tampilan aplikasi sangat konsisten | - | - | Design Audit Passed |
| 5 | Jumat, 24/07/2026 | Production Build Optimization Frontend | Running `vite build` & optimasi minifikasi bundle Javascript | Bundle terkompresi optimal ($< 350\text{KB}$) | - | - | Production Asset Ready |
| 6 | Sabtu, 25/07/2026 | Preparasi Deployment Frontend ke Server Production MA | Menyiapkan asset static & script deployment web server | Asset siap di-deploy ke server live | - | - | Deployment Ready |

---

## MINGGU KE - 26 (27/07/2026 – 03/08/2026) — DEPLOYMENT PRODUCTION, HANDOVER & PENUTUPAN
| NO | HARI/TANGGAL | RENCANA | REALISASI | HASIL | PROBLEM | SOLUSI | HASIL AKHIR |
|:--:|:---:|:---|:---|:---|:---|:---|:---|
| 1 | Senin, 27/07/2026 | Deployment Final Frontend DIGIPER ke Production Server | Mengunggah asset static ke Nginx/Apache server Mahkamah Agung | Frontend DIGIPER live di server MA | Asset font sempat tidak ter-load karena CROS path | Menyesuaikan asset base path di `vite.config.js` | App live sempurna |
| 2 | Selasa, 28/07/2026 | Live Testing Antarmuka di Server Production | Testing akses seluruh halaman dari berbagai perangkat staf MA | Tampilan & fungsi 100% lancar di prod | - | - | Prod Live Test Pass |
| 3 | Rabu, 29/07/2026 | Pendampingan & User Training Tampilan DIGIPER | Pelatihan staf cara navigasi, upload, & ekspor laporan | Staf lancar mengoperasikan antarmuka | - | - | Training Staf Sukses |
| 4 | Kamis, 30/07/2026 | Serah Terima Dokumen UI/UX & Source Code Frontend | Penyerahan asset desain, dokumen komponen, & source code | Handover Frontend tuntas | - | - | Handover FE Ttd |
| 5 | Jumat, 31/07/2026 | Penyusunan Laporan Akhir Magang & Berkas Administrasi | Melengkapi laporan magang individu & berkas YARSI | Laporan magang lengkap disusun | - | - | Administrasi Selesai |
| 6 | Sabtu, 01/08/2026 | Briefing Evaluasi Akhir Magang bersama Pembimbing MA | Evaluasi hasil kerja antarmuka selama 6 bulan | Pembimbing memberikan apresiasi tinggi | - | - | Evaluasi Sempurna |
| 7 | Senin, 03/08/2026 | Penutupan Resmi Magang di Mahkamah Agung RI | Acara pelepasan mahasiswa magang secara resmi oleh instansi | Kegiatan Magang 6 Bulan Resmi Selesai | - | - | **MAGANG SELESAI DENGAN BAIK** |

---
*Logbook ini disusun secara resmi untuk memenuhi persyaratan administrasi Laporan Magang Universitas YARSI.*
