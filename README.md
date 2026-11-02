# 🏫 SAPA Sekolah — Digital Mapping & Monitoring PMB Realtime

[![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%20%7C%208.3-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![PWA](https://img.shields.io/badge/PWA-Ready-5A0FC8?style=for-the-badge&logo=pwa&logoColor=white)](https://web.dev/progressive-web-apps/)
[![Status](https://img.shields.io/badge/Tests-46%20Passed%20(100%25)-success?style=for-the-badge)](RELEASE_NOTES.md)

**SAPA Sekolah** adalah sistem informasi pemetaan digital (*digital mapping*) dan monitoring pemasaran sekolah yang dirancang khusus untuk mendukung operasional divisi **Marketing Communication & Admisi Penerimaan Mahasiswa Baru (PMB)** multi-kampus **Universitas BSI (UBSI Kampus Karawang & UBSI Kampus Cikampek)**.

---

## 📑 Catatan Perubahan & Rilis Lengkap
Untuk melihat riwayat lengkap perubahan sistem, audit performa, perbaikan bug, dan panduan teknis Sebelum vs Sesudah:
👉 **[Lihat Catatan Rilis & Perubahan Lengkap (RELEASE_NOTES.md)](RELEASE_NOTES.md)**

---

## 🌟 Fitur Utama Sistem

### 1. 🗺️ Smart GIS School Mapping & Geolocation
* Pemetaan digital sebaran sekolah SLTA (SMA/SMK/MA) di seluruh wilayah Karawang, Cikampek, Purwakarta, Subang, dan Bekasi.
* Penghitungan otomatis jarak dan rute dari kampus operasional UBSI terdekat.
* Sistem penilaian prioritas sekolah (*Priority Scoring*) berbasis bobot akreditasi, jumlah siswa kelas XII, dan riwayat konversi PMB.

### 2. 📱 PWA Mobile First & Field Operation
* Kompatibel penuh dengan smartphone lapangan (Progressive Web App).
* Menu bawah horizontal (*Strict Horizontal Bottom Nav*) dengan elevated button untuk mencatat kunjungan sekolah baru dalam 1 ketukan.
* Panel drawer aksi cepat: input survei minat siswa, pemindaian QR Code kelas, tracking proposal kegiatan, dan rute navigasi.

### 3. 🏢 Multi-Campus Architecture (Karawang & Cikampek)
* Isolasi data sekolah, agenda kunjungan, dan prospek per kampus operasional.
* **Context Switcher**: Beralih kampus dan tahun periode akademik secara instan baik di desktop maupun melalui sub-navbar khusus smartphone.
* Master Kampus dan Master Periode dapat dikelola langsung dari panel admin.

### 4. 🤝 Portal Sponsorship & Support Event Sekolah
* Formulir publik mandiri bagi sekolah untuk mengajukan kerja sama sponsorship kegiatan.
* Pemfilteran sekolah otomatis berdasarkan kampus tujuan (UBSI Karawang vs UBSI Cikampek).
* Dashboard workflow persetujuan berjenjang: *Menunggu Review*, *Diajukan ke Pusat*, *Menunggu ACC*, *Disetujui*, hingga *Laporan Terdokumentasi*.
* Integrasi berkas dan dokumentasi kegiatan langsung ke Google Drive Cloud.

### 5. 📊 CRM Prospek Siswa, Kuisioner Beasiswa & Mahasiswa Baru
* Pengumpulan data profil lengkap siswa (termasuk jenis kelamin L/P, kontak WA, email, media sosial siswa & wali, serta minat beasiswa/prodi).
* Pengisian kuisioner evaluasi berstandar sistematis (Skala 1–5 motivasi, sosial, biaya, dan niat kuliah) baik via portal publik mandiri maupun input sistem internal staf.
* Pipeline follow-up terintegrasi: *Belum Dihubungi*, *Respon*, *Berminat*, *Daftar Aplikasi*, hingga *Closing Registrasi PMB*.
* Sinkronisasi data otomatis dari Kuisioner & Hasil Event langsung ke CRM Prospek hingga *Data Mahasiswa Baru* (`/new-students`) lengkap dengan rasio gender KPI L/P.

---

## 🛠️ Persyaratan Lingkungan (*Environment Requirements*)

* **PHP**: `>= 8.2` (ekstensi `pdo_mysql`, `gd`, `fileinfo`, `zip`, `mbstring`)
* **Composer**: `>= 2.x`
* **Node.js & NPM**: `>= 18.x`
* **Database**: MySQL `>= 8.0` atau MariaDB `>= 10.5`
* **Web Server**: Apache / Nginx / Laragon / PHP Built-in Server

---

## ⚙️ Panduan Instalasi Lokal

1. **Clone Repository**:
   ```bash
   git clone https://github.com/walimrafa/SapaSekolah.git
   cd SapaSekolah
   ```

2. **Install Dependensi Backend & Frontend**:
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Migrasi Database & Seeder**:
   ```bash
   php artisan migrate --seed
   ```

5. **Build Aset Frontend**:
   ```bash
   npm run build
   ```

6. **Jalankan Server Lokal**:
   ```bash
   php artisan serve
   ```
   Aplikasi dapat diakses melalui: `http://127.0.0.1:8000`

---

## 🚀 Prosedur Update di Server Production

```bash
git pull origin main
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
```

---

## 🔒 Lisensi & Hak Cipta
Hak Cipta © 2024–2026 **Universitas BSI (Bina Sarana Informatika)**. Dikelola oleh Tim Marketing & IT Command Center SAPA Sekolah.
