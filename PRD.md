# Product Requirement Document (PRD)
## Aplikasi Optimalisasi Rute Marketing Sekolah Berbasis Web (Laravel 13 & Google Maps API)

---

## 1. Executive Summary & Ringkasan Proyek

### 1.1 Latar Belakang
Tim *marketing* kampus / institusi pendidikan setiap periodenya melakukan kunjungan sosialisasi dan promosi ke berbagai sekolah menengah (SMA/SMK/MA). Tantangan utama yang dihadapi saat ini adalah penyusunan rute harian yang kurang efisien, di mana tim sering memutar arah, terjebak kemacetan, atau melompati lokasi sekolah yang berdekatan. Hal ini memicu pemborosan waktu, peningkatan konsumsi bahan bakar, dan penurunan jumlah sekolah yang dapat dikunjungi dalam sehari.

### 1.2 Solusi Sistem
Aplikasi **Smart Marketing Route System** hadir sebagai platform manajemen dan optimalisasi rute kunjungan sekolah berbasis web. Menggunakan **Laravel 13**, **MySQL**, **Tailwind CSS (Responsive Mobile-First)**, dan terintegrasi secara mendalam dengan **Google Maps Platform (Distance Matrix API & Directions API)**, aplikasi ini secara otomatis menghitung rute perjalanan terbaik (paling efisien dari segi waktu dan jarak) dari titik awal keberangkatan hingga sekolah tujuan akhir.

---

## 2. Tujuan & Indikator Keberhasilan (KPI)

### 2.1 Tujuan Utama
1. Mempermudah manajemen data sekolah target kunjungan secara terpusat.
2. Membantu tim *marketing* menentukan rute kunjungan paling optimal secara otomatis.
3. Menampilkan visualisasi rute interaktif di Google Maps yang nyaman diakses via *smartphone* (*mobile response*).

### 2.2 Key Performance Indicators (KPI)
* **Efisiensi Perjalanan:** Efisiensi jarak dan waktu tempuh rute harian meningkat sebesar 20 - 35%.
* **Jumlah Kunjungan:** Meningkatkan rerata kunjungan dari 2–3 sekolah/hari menjadi 4–5 sekolah/hari per tim.
* **Kecepatan Perencanaan:** Memangkas waktu penyusunan rute dari 30–60 menit manual menjadi < 1 menit.

---

## 3. Analisis Kebutuhan Data Sekolah (Penjelasan Detail Field)

Untuk menjawab kebingungan mengenai data sekolah agar titik dan rute dapat terbaca secara presisi di Google Maps, berikut adalah pengelompokan *field* data yang wajib dan disarankan:

### 3.1 Field Utama Lokasi (Wajib untuk Google Maps API)
1. **Garis Lintang (*Latitude*)** & **Garis Bujur (*Longitude*)**:
   * *Format:* Desimal (contoh: `-6.3825, 107.4312`).
   * *Fungsi:* Titik koordinat absolut. Ini adalah atribut paling akurat bagi Google Maps API untuk menghitung rute dan *distance matrix* tanpa ambiguitas nama/alamat.
2. **Alamat Lengkap & Kode Pos**:
   * *Fungsi:* Dipakai jika koordinat diisi otomatis lewat fitur *Geocoding* atau sebagai acuan verifikasi visual oleh tim di lapangan.
3. **Google Place ID (Opsional namun Sangat Direkomendasikan)**:
   * *Fungsi:* Identifier unik bawaan Google Maps untuk menjamin akurasi titik gerbang/pintu masuk sekolah.

### 3.2 Field Operasional Marketing & Filter Rute
* **ID Sekolah / NPSN** (Primary Key / Kode Unik)
* **Nama Sekolah** (contoh: SMAN 1 Karawang)
* **Jenjang** (SMA / SMK / MA) & **Status** (Negeri / Swasta)
* **Kontak Person / Guru BK** (Nama, Jabatan, No. WhatsApp)
* **Jam Operasional / Jam Kunjungan** (contoh: 08:00 - 14:00 WIB)
* **Status Kunjungan** (Belum Dikunjungi / Terjadwal / Sudah Dikunjungi)
* **Catatan Khusus / Akreditasi** (contoh: Jumlah siswa kelas 12)

---

## 4. Arsitektur Sistem & Spesifikasi Teknologi

| Komponen | Teknologi / Fitur | Keterangan |
| :--- | :--- | :--- |
| **Framework Backend** | **Laravel 13** | PHP 8.3+, route routing modern, Eloquent ORM, Ingestion/Import jobs |
| **Database** | **MySQL 8.0+** | Spatial Indexes (`ST_Point`), Relational Data Engine |
| **Frontend & UI** | **Tailwind CSS v4** | Fully responsive, mobile-first drawer, touch-friendly UI |
| **Peta Interaktif** | **Google Maps JavaScript API** | Visualisasi marker interaktif, custom info-window, polyline route |
| **Mesin Rute** | **Google Directions API** | Algoritma *Traveling Salesperson Problem (TSP)* via `optimizeWaypoints: true` |
| **Penghitung Jarak** | **Google Distance Matrix API** | Matriks durasi & jarak real-time dengan kondisi lalu lintas |
| **Import Engine** | **Laravel Excel (Maatwebsite)** | Parsing file Excel/CSV untuk bulk upload data sekolah |

---

## 5. Algoritma & Logika Optimalisasi Rute (TSP / Traveling Salesperson Problem)

1. **Input Pengguna:**
   * **Titik Awal (Start Point):** Bisa lokasi *GPS live user*, lokasi kantor pusat, atau titik custom.
   * **Daftar Sekolah Singgahan (Waypoints):** Pilih 2 hingga 20 sekolah yang ingin dikunjungi.
   * **Titik Akhir (Destination/End Point):** Pilih 1 sekolah tujuan akhir (sesuai instruksi user).
2. **Proses Pengolahan (Google Directions API):**
   * Aplikasi melempar request ke Google Directions API dengan parameter:
     ```json
     {
       "origin": "-6.3000,107.3000",
       "destination": "-6.3825,107.4312",
       "waypoints": "optimize:true|-6.3200,107.3100|-6.3500,107.3300",
       "travelMode": "DRIVING"
     }
     ```
3. **Output Sistem:**
   * Google mengembalikan urutan *waypoints* yang sudah diurutkan ulang (`waypoint_order`) untuk menghasilkan **total waktu dan jarak terpendek**.
   * Aplikasi menggambar garis jalur (*Polyline*) di peta dan menampilkan panduan navigasi langkah demi langkah (*Turn-by-turn directions*).

---

## 6. Fitur Utama & Kebutuhan Fungsional (Functional Requirements)

### 6.1 Modul Auth & User Management
* Login, Logout, dan Reset Password untuk Petualang Marketing & Admin.
* Role: **Admin** (Kelola data sekolah & user) dan **Petugas Marketing** (Perencana Rute & Reporter Field).

### 6.2 Modul Kelola Data Sekolah (CRUD & Import)
* **Form Input Manual:** Input Nama, NPSN, Alamat, Wilayah, Kontak BK, Lat/Long via Peta Picker (klik peta untuk ambil koordinat).
* **Import Data Massal (Excel/CSV):**
  * Fitur unggah file `.xlsx` / `.csv`.
  * *Template Downloader:* Menyediakan file contoh format CSV/Excel dengan kolom valid.
  * *Auto-Geocoding Background Job:* Jika lat/long kosong, sistem otomatis mencari koordinat via Google Geocoding API berdasarkan Alamat + Nama Sekolah.
  * *Validation & Error Handling:* Menampilkan preview kesalahan data sebelum disimpan.

### 6.3 Modul Perencanaan & Optimalisasi Rute Marketing
* **Pemilih Titik Awal:**
  * Gunakan lokasi *GPS HP saat ini* (*Use My Current Location*).
  * Atau pilih titik awal dari daftar preset (misal: "Kampus Utama").
* **Pemilih Sekolah Target & Titik Akhir:**
  * Multi-select sekolah yang ingin dikunjungi hari ini.
  * Penentuan khusus **Titik Akhir (Sekolah Tujuan Akhir)**.
* **Proses Hitung Rute Terbaik:**
  * Tombol **"Generate Rute Paling Optimal"**.
  * Tampilan Ringkasan: Total Jarak (km), Estimasi Waktu Tempuh (Menit/Jam), Urutan Kunjungan (Urutan 1, 2, 3, dst.).
* **Integrasi Google Maps Mobile Direct Navigation:**
  * Tombol **"Buka di Aplikasi Google Maps"** yang langsung mentransfer rute urutan tersebut ke aplikasi Google Maps di HP petugas.

### 6.4 Modul Laporan Kunjungan (Field Report)
* Check-in lokasi di sekolah tujuan (dengan Geolocation lock).
* Catatan hasil sosialisasi dan unggah foto dokumentasi.

---

## 7. Perancangan Skema Database (MySQL Schema)

### 7.1 Tabel `users`
| Column | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | BigInt | PK, Auto Increment | ID User |
| `name` | Varchar(100) | Not Null | Nama Lengkap |
| `email` | Varchar(100) | Unique, Not Null | Email Login |
| `password` | Varchar(255) | Not Null | Password Hashed |
| `role` | Enum | 'admin', 'marketing' | Hak Akses |
| `created_at` | Timestamp | Nullable | - |

### 7.2 Tabel `schools`
| Column | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | BigInt | PK, Auto Increment | ID Sekolah |
| `npsn` | Varchar(20) | Unique, Nullable | Nomor Pokok Sekolah Nasional |
| `name` | Varchar(150) | Not Null | Nama Sekolah |
| `type` | Enum | 'SMA', 'SMK', 'MA', 'Lainnya' | Jenjang Sekolah |
| `status` | Enum | 'Negeri', 'Swasta' | Status Sekolah |
| `address` | Text | Not Null | Alamat Lengkap |
| `city` | Varchar(100) | Not Null | Kota / Kabupaten |
| `latitude` | Decimal(10,8) | Not Null, Index | Garis Lintang Koordinat |
| `longitude` | Decimal(11,8) | Not Null, Index | Garis Bujur Koordinat |
| `contact_person` | Varchar(100) | Nullable | Nama Guru BK / Kontak |
| `phone_number` | Varchar(20) | Nullable | Nomor WhatsApp / Telepon |
| `student_count` | Integer | Nullable, Default 0 | Estimasi Jumlah Siswa Kls 12 |
| `created_at` | Timestamp | Nullable | - |

### 7.3 Tabel `routes`
| Column | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | BigInt | PK, Auto Increment | ID Rute |
| `user_id` | BigInt | FK -> users.id | Marketing Pembuat Rute |
| `title` | Varchar(150) | Not Null | Nama Rute (e.g., Rute Karawang Barat 12 Mar) |
| `start_latitude` | Decimal(10,8) | Not Null | Koordinat Awal Lat |
| `start_longitude` | Decimal(11,8) | Not Null | Koordinat Awal Long |
| `end_school_id` | BigInt | FK -> schools.id | ID Sekolah Titik Akhir |
| `total_distance_km`| Decimal(8,2) | Not Null | Total Jarak Hasil Kalkulasi |
| `total_duration_min`| Integer | Not Null | Total Estimasi Menit |
| `scheduled_date` | Date | Not Null | Tanggal Pelaksanaan |
| `created_at` | Timestamp | Nullable | - |

### 7.4 Tabel `route_details`
| Column | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | BigInt | PK, Auto Increment | ID Detail Rute |
| `route_id` | BigInt | FK -> routes.id | Relasi Rute |
| `school_id` | BigInt | FK -> schools.id | Relasi Sekolah Singgahan |
| `stop_order` | Integer | Not Null | Urutan Kunjungan (1, 2, 3...) |
| `distance_from_prev`| Decimal(6,2) | Nullable | Jarak dari Titik Sebelumnya |
| `duration_from_prev`| Integer | Nullable | Menit Tempuh dari Sebelumnya |

---

## 8. Desain Antarmuka Pengguna (UI/UX) & Responsive Layout

Sistem dirancang dengan pendekatan **Mobile-First Responsive** menggunakan Tailwind CSS:

1. **Dashboard UI:**
   * Ringkasan statistik (Total Sekolah, Rute Aktif Hari Ini, Kunjungan Selesai).
   * Shortcut cepat "Buat Rute Baru" dan "Import Data".
2. **Interface Pemeta Rute (Route Planner UI):**
   * **Atas / Mobile Drawer:** Panel input titik awal, checklist pilihan sekolah, dan dropdown sekolah akhir.
   * **Peta Utama (Google Maps Canvas):** Memenuhi layar dengan penanda (marker) yang memiliki nomor urut kunjungan warna-warni (misal: Merah = Titik Awal, Biru = Singgahan, Hijau = Titik Akhir).
3. **Mobile Bottom Sheet:**
   * Di perangkat Android/iOS, urutan rute ditampilkan dalam bentuk *Bottom Sheet* yang bisa digeser naik-turun tanpa menutupi visualisasi peta.

---

## 9. Rencana Kerja Pembangunan (Roadmap & Implementation Steps)

1. **Fase 1: Setup Framework & Database (Hari 1-2)**
   * Instalasi Laravel 13, Tailwind CSS, Admin Template, MySQL DB migrations.
2. **Fase 2: Modul Kelola Sekolah & Import Engine (Hari 3-5)**
   * Pembuatan CRUD Data Sekolah, Map Picker untuk Ambil Koordinat, Integration Laravel Excel untuk Import `.xlsx`.
3. **Fase 3: Integrasi Google Maps API & Algoritma Rute (Hari 6-9)**
   * Pembuatan JS Controller untuk Google Maps JS API.
   * Implementasi pemanggilan API Directions dengan `optimizeWaypoints` dan penentuan *end-point*.
4. **Fase 4: Antarmuka Responsive Mobile & Fitur Navigasi Direct (Hari 10-12)**
   * Poles UI dengan Tailwind CSS (Drawer, Responsive Sidebar, Map Canvas).
   * Fitur generator Deep Link Google Maps App.
5. **Fase 5: Testing, Optimasi & Deployment (Hari 13-14)**
   * Uji coba import 500+ data sekolah.
   * Uji performa kalkulasi rute di koneksi mobile.

---
*Dokumen PRD ini disusun untuk mendukung pengembangan sistem Rute Marketing Kampus / Sekolah Berbasis Laravel 13 & Google Maps API.*

---

## 10. Pemetaan PRD ke Web SAPA Sekolah Saat Ini

Pemetaan ini melengkapi PRD tanpa menggantikan modul dan alur web SAPA Sekolah yang sudah berjalan. Fitur berstatus **Tersedia** tetap dipertahankan; daftar **Belum tersedia** adalah pekerjaan lanjutan, bukan penghapusan fitur yang ada.

### 10.1 Fitur yang Sudah Tersedia di Web

| Kebutuhan PRD | Implementasi web yang sudah tersedia |
| :--- | :--- |
| Login, logout, role, dan pengelolaan pengguna | Autentikasi Laravel, role, status akun, serta CRUD pengguna. |
| Data sekolah terpusat | CRUD sekolah, data wilayah, jenis sekolah, akreditasi, kontak, pencarian, filter, dan ekspor. |
| Peta dan koordinat | Peta sekolah Leaflet/OpenStreetMap, input koordinat manual, map picker, serta tombol mengambil GPS untuk mengisi koordinat sekolah. |
| Import sekolah dan template | Template CSV, import CSV/Excel (`.xls`/`.xlsx`), validasi dasar, dan laporan baris yang dilewati. |
| Prioritas sekolah | Skoring jarak, status sekolah, jumlah siswa kelas XII, serta mahasiswa baru UBSI dari sekolah asal. |
| Data mahasiswa baru per sekolah | Import statistik per sekolah/periode PMB, tampilan angka di leaderboard/rekomendasi, dan pemutakhiran skor prioritas. |
| Perencanaan rute dan navigasi | Rute klaster, pilihan titik awal/akhir preset, preview peta Leaflet dengan rute jalan OSRM, penyimpanan rute, dan tautan navigasi Google Maps. |
| Operasional kunjungan | Rencana kunjungan, laporan kunjungan, follow-up, aktivitas marketing, dan dashboard/laporan. |
| Pencatatan sosialisasi terlaksana | Jadwal FIX dapat dikonfirmasi setelah presentasi/kegiatan benar-benar berlangsung; tanggal dan catatan kegiatan masuk ke menu **Sekolah Sudah Sosialisasi** dan keluar dari antrean Jadwal FIX. |
| Check-in dan dokumentasi kunjungan | Check-in GPS laporan kunjungan diverifikasi server dalam radius 500 meter dari sekolah serta unggah foto dokumentasi opsional. |
| Tampilan responsif | Layout Tailwind responsif untuk halaman utama dan panel rute. |

### 10.2 Kebutuhan PRD yang Belum Tersedia Sepenuhnya

| Kebutuhan lanjutan | Kondisi / batasan |
| :--- | :--- |
| Optimasi rute berbasis Google Directions `optimizeWaypoints` dan traffic | Urutan saat ini menggunakan nearest-neighbor/Haversine; OSRM hanya menggambar rute jalan. Google Directions/Distance Matrix dan estimasi lalu lintas belum terintegrasi. |
| Titik awal dari GPS live pengguna | Pilihan saat ini adalah kampus atau sekolah preset; lokasi live pengguna belum digunakan sebagai origin rute. |
| Geocoding otomatis untuk koordinat kosong | Belum tersedia; koordinat perlu diimpor atau diisi melalui peta/GPS. |
| Preview seluruh kesalahan import sebelum penyimpanan | Validasi dan daftar baris gagal saat ini dilaporkan setelah proses import; belum ada langkah preview-konfirmasi. |
| Google Place ID dan jam operasional/jam kunjungan sekolah | Field belum tersedia di master sekolah. |
| Reset password melalui email | Login/logout tersedia, tetapi alur reset password dan konfigurasi email belum disiapkan. |
| Mobile route bottom sheet khusus | Halaman rute sudah responsif, tetapi urutan rute belum disajikan sebagai bottom sheet yang dapat digeser. |

### 10.3 Catatan Implementasi

Web saat ini menggunakan versi Laravel dan Tailwind yang sudah ada di proyek, peta Leaflet/OpenStreetMap, serta rute OSRM. Integrasi layanan Google berbayar tidak boleh dianggap aktif hanya karena tersedia tautan Google Maps; aktivasi Directions, Distance Matrix, atau Geocoding memerlukan konfigurasi API key dan pembatasan pemakaian yang sesuai.
