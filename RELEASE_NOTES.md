# 📋 CATATAN PERBAIKAN & UPDATE SISTEM SAPA SEKOLAH

### 📌 Ringkasan Versi
- **Versi**: 2.1.0 (Audit, Optimalisasi, Keamanan & Notifikasi Real-Time)
- **Tanggal Pembaruan**: 2 November 2026
- **Status Repository**: Terhubung & Tersinkronisasi ke GitHub (`main`)
- **Hasil Pengujian Otomatis**: 44 Test Berhasil (229 Assertions) — **0 Bug, 0 Error, 100% Pass**

---

## 🔍 Detail Perbaikan Sistem: Sebelum vs Sesudah

### 1. Sistem Notifikasi Real-Time (In-App Database Notification)
* **Sebelumnya**:
  * Ikon lonceng di pojok kanan atas navbar hanya berupa tampilan dummy statis (*hardcoded* tulisan "3 Baru", "Follow-up Terlambat SMAN 1 Ciampel").
  * Tidak terhubung ke database dan tidak ada pencatatan riwayat notifikasi.
* **Sesudah Perbaikan**:
  * **Database Terintegrasi**: Dibuat tabel database resmi `app_notifications` yang menyimpan setiap notifikasi sistem.
  * **Live Badge Counter**: Ikon lonceng dilengkapi indikator badge angka merah yang reaktif menghitung notifikasi belum dibaca.
  * **Pemicu Otomatis (Event Hooks)**:
    1. Ada sekolah mengajukan proposal event/sponsorship baru via portal publik.
    2. Ada siswa yang mengisi kuisioner minat survei PMB.
    3. Ada jadwal follow-up sekolah yang jatuh tempo di hari tersebut.
  * **Aksi Cepat & Navigasi**: Pengguna dapat menandai notifikasi dibaca secara satuan atau massal (*Mark All as Read*), serta mengklik notifikasi untuk langsung membuka halaman target.
  * **Halaman Arsip Notifikasi**: Disediakan menu `/notifications` lengkap dengan paginasi riwayat notifikasi.

---

### 2. Penyelarasan Multi-Kampus Dinamis (UBSI Karawang vs UBSI Cikampek)
* **Sebelumnya**:
  * Saat masuk atau berganti ke akun **UBSI Kampus Cikampek**, berbagai teks judul halaman, subjudul, parameter acuan jarak, dan kop laporan masih tertulis statis *"Data Sekolah SLTA Karawang"*, *"30 Kecamatan Karawang"*, atau *"UBSI Karawang"*.
* **Sesudah Perbaikan**:
  * Seluruh teks pada **Dashboard Command Center, Data Sekolah SLTA, Leaderboard Prioritas, Rute Mobil, dan Kop Surat Cetak Laporan** kini dinamis mengikuti `$activeCampus`.
  * Saat konteks kampus Cikampek aktif, seluruh teks, alamat kampus acuan, nomor telepon resmi, dan kota tanda tangan otomatis berubah menjadi Cikampek.

---

### 3. Perbaikan Berkas Lampiran Aktivitas Lapangan (Solusi Error 404)
* **Sebelumnya**:
  * Mengklik tautan foto/dokumen lampiran pada menu *Aktivitas Sosialisasi & Event Marketing* menghasilkan pesan error **`404 Not Found`** karena tautan langsung mengarah ke path URL storage statis.
* **Sesudah Perbaikan**:
  * Dibuatkan fungsi handler [`MarketingActivityController::document`](app/Http/Controllers/MarketingActivityController.php).
  * Sistem memeriksa ketersediaan fisik file di disk penyimpanan server lokal terlebih dahulu: jika ada, file langsung disajikan; jika file diarahkan ke Google Drive, sistem otomatis mengarahkan ke URL Google Drive resmi.

---

### 4. Pencarian Sekolah di Portal Pengajuan Event ("Format Kuisioner")
* **Sebelumnya**:
  * Form pengajuan proposal event sekolah (`/pengajuan-event`) menggunakan dropdown standar yang lambat dan menyulitkan pencarian nama sekolah dari ratusan data.
* **Sesudah Perbaikan**:
  * Diselaraskan dengan teknologi pada kuisioner siswa:
    * Menggunakan **Select2 Searchable Dropdown**: pengguna cukup mengetik nama sekolah atau kecamatan untuk mencari secara instan.
    * Format tampilan seragam: `[Nama Sekolah] ([Kecamatan])`.
    * Disediakan tombol fallback *"Sekolah Lain / Manual"* jika sekolah belum terdata di sistem.

---

### 5. Hak Akses Hapus & Pulihkan Sekolah untuk Koordinator Marketing
* **Sebelumnya**:
  * Fitur "Hapus Semua" atau hapus per sekolah hanya bisa diakses Admin dan Pimpinan. Koordinator Marketing yang mengakses mendapatkan pesan error **`403 Akses Dibatasi`**.
* **Sesudah Perbaikan**:
  * **Koordinator Marketing** resmi diberikan wewenang untuk menghapus data sekolah ke tempat sampah (*soft delete*) maupun memulihkannya (*restore*).
  * Dilengkapi pengaman isolasi ketat: aksi penghapusan massal hanya akan memproses sekolah milik kampus penugasannya saja dan **tidak menyentuh sekolah kampus lain**.

---

### 6. Peningkatan Keamanan Siber (Cyber Security OWASP)
* **Proteksi Formula Injection (CSV / Excel Injection)**:
  * Seluruh ekspor Excel (data master sekolah, kuisioner siswa, follow-up) kini disaring oleh `SecuritySanitizer`. Karakter awal seperti `=`, `+`, `-`, `@`, `\t` otomatis dinetralisir agar aman saat dibuka di Microsoft Excel.
* **Security Headers Middleware**:
  * Mengaktifkan HTTP Security Headers:
    * `X-Content-Type-Options: nosniff`
    * `X-Frame-Options: SAMEORIGIN`
    * `X-XSS-Protection: 1; mode=block`
    * `Referrer-Policy: strict-origin-when-cross-origin`
* **Anti Brute Force Login**:
  * Menambahkan middleware `throttle:10,1` pada endpoint login untuk mencegah upaya tebak kata sandi otomatis.
* **Strict SQL Sorting Whitelist**:
  * Parameter pengurutan tabel SQL dikunci ketat pada whitelist nilai yang sah guna mencegah manipulasi query URL.

---

### 7. Optimalisasi UI/UX (SaaS Modern & Teks Singkat)
* **Sebelumnya**:
  * Tombol aksi toolbar dan deskripsi tabel terlalu panjang, menyebabkan layout padat dan bertumpuk di layar smartphone maupun laptop resolusi kecil.
* **Sesudah Perbaikan**:
  * **Penyederhanaan Teks Toolbar Data Sekolah**:
    * `"Integrasi Dapodik & Kemenag"` ➔ **`Integrasi`**
    * `"Download Data Sekolah"` ➔ **`Export`**
    * `"Template Excel"` ➔ **`Template`**
    * `"Import Excel / CSV"` ➔ **`Import`**
    * `"Tambah Sekolah Baru"` ➔ **`+ Tambah`**
  * Teks sambutan banner dashboard dan kartu KPI dipersingkat menjadi padat, profesional, dan efisien ruang.
  * Tampilan tabel dan modal dialog dibuat responsif (*mobile-first*) dengan drawer navigasi yang nyaman disentuh di perangkat seluler.

---

## 🚀 Panduan Rilis di Server Staging / Production
Ketika melakukan pembaruan di server publik (`sapa.sobi.web.id`), jalankan langkah berikut:
```bash
git pull origin main
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
```

