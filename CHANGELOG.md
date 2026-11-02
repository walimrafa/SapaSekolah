# CHANGELOG

## [2.1.0] - 2026-11-02

### 1. Ringkasan Perubahan
Optimalisasi arsitektur, penguatan keamanan (OWASP), implementasi sistem notifikasi real-time terintegrasi database, perbaikan isu attachment/dokumen lapangan, penyelarasan format pencarian sekolah Select2 di portal publik, dynamic campus branding (menghilangkan hardcoded Karawang saat kampus Cikampek aktif), serta penyederhanaan seluruh teks copy aplikasi (SaaS minimalis modern).

---

### 2. Modul yang Diubah
1. **Sistem Notifikasi Real-Time**: Database table `app_notifications`, model, service, controller, navbar live badge & dropdown, serta halaman arsip notifikasi.
2. **Keamanan & Proteksi Sistem (OWASP)**:
   - Sanitasi Spreadsheet Formula Injection (CSV Injection) pada seluruh modul export.
   - Whitelist query sorting (`direction` & column name) mencegah celah SQL parameter tampering.
   - Security Headers Middleware (`X-Content-Type-Options`, `X-Frame-Options`, `X-XSS-Protection`, `Referrer-Policy`).
   - Rate limiting throttle pada endpoint login dan portal publik.
3. **Dokumen & Media Lapangan**:
   - Perbaikan direct serving file dokumen aktivitas marketing (mengatasi isu 404 pada tautan localhost/storage).
   - Integrasi cerdas: sajikan file lokal jika ada di disk; redirect Google Drive jika tautan file langsung tersedia.
4. **Pencarian Sekolah Publik & Kuisioner**:
   - Penyelarasan format dropdown pencarian sekolah di portal pengajuan support event (`public-events/create.blade.php`) menggunakan Select2 sesuai format kuisioner siswa (`[Nama Sekolah] ([Kecamatan])`).
5. **Dynamic Campus Branding**:
   - Menghilangkan referensi teks statis "Karawang" pada Dashboard, Leaderboard Prioritas, Rute Mobil, dan Kop Surat Laporan agar otomatis menyesuaikan kampus yang sedang aktif (UBSI Karawang vs UBSI Cikampek).
6. **UI/UX & Copy Simplification ("Persingkat Seluruh Teks")**:
   - Penyederhanaan label tombol aksi toolbar Data Sekolah, teks header, subtitle, deskripsi card, dan modal dialog menjadi ringkas, padat, dan profesional.

---

### 3. File yang Ditambahkan / Dimodifikasi / Dihapus

#### Ditambahkan:
- `database/migrations/2026_11_02_000001_create_app_notifications_table.php` (Tabel notifikasi in-app)
- `app/Models/AppNotification.php` (Model notifikasi dengan user & campus scoping)
- `app/Services/NotificationService.php` (Service manajemen & trigger event notifikasi)
- `app/Http/Controllers/NotificationController.php` (Feed, index, markAsRead, markAllAsRead)
- `app/Http/Middleware/SecurityHeadersMiddleware.php` (Enforce OWASP security headers)
- `app/Support/SecuritySanitizer.php` (Sanitasi formula injection & safe filenames)
- `resources/views/notifications/index.blade.php` (Halaman arsip notifikasi responsif)
- `tests/Feature/NotificationAndSecurityHardeningTest.php` (Suite pengujian notifikasi & security)
- `CHANGELOG.md` (Dokumentasi audit dan pembaruan sistem)

#### Dimodifikasi:
- `app/Http/Kernel.php` (Mendaftarkan SecurityHeadersMiddleware ke grup web)
- `routes/web.php` (Menambahkan route notifikasi dan throttle proteksi login)
- `app/Http/Controllers/SchoolController.php` (Strict sorting direction whitelist)
- `app/Http/Controllers/SchoolImportController.php` (Sanitasi formula injection saat export Excel)
- `app/Http/Controllers/StudentQuestionnaireController.php` (Sanitasi export & hook notifikasi masuk)
- `app/Http/Controllers/SchoolEventProposalController.php` (Hook notifikasi proposal masuk)
- `app/Http/Controllers/MarketingActivityController.php` (Penyajian berkas dokumen langsung & Google Drive fallback)
- `resources/views/components/navbar.blade.php` (Komponen live notifikasi Alpine.js + badge count unread)
- `resources/views/dashboard.blade.php` (Penyederhanaan teks copy & dynamic campus title)
- `resources/views/schools/index.blade.php` (SaaS clean button bar, teks singkat)
- `resources/views/priorities/index.blade.php` (Dynamic campus parameters & concise labels)
- `resources/views/marketing-routes/index.blade.php` (Dynamic starting campus & shortened actions)
- `resources/views/public-events/create.blade.php` (Select2 sekolah format kuisioner)
- `resources/views/reports/index.blade.php` (Kop surat & tanda tangan dynamic per kampus aktif)

---

### 4. Perbaikan Bug
- **Bug 404 Unduh Berkas Aktivitas**: Memperbaiki method `MarketingActivityController::document` agar membaca file storage disk lokal secara langsung sebelum mencoba redirect ke tautan Google Drive, meniadakan error 404 saat pengguna mengklik dokumen kunjungan.
- **Hardcoded Kampus Karawang**: Menghilangkan teks "Karawang" statis di berbagai view ketika pengguna memilih UBSI Kampus Cikampek. Judul, subjudul, dan kop surat kini reaktif terhadap `$activeCampus`.
- **Dropdown Template Tag Issue di Pengajuan Event**: Mengganti `<template x-for>` di dalam tag `<select>` HTML dengan Select2 searchable autocomplete sehingga pilihan sekolah dapat dicari dengan mulus di mobile maupun desktop.
- **Formula Injection pada Export**: Menetralisir input karakter `=`, `+`, `-`, `@`, `\t` pada field sekolah dan siswa saat diunduh sebagai XLS/CSV.

---

### 5. Peningkatan UI / UX
- **Live Notification Bell**: Ikon lonceng navbar kini memiliki badge angka merah real-time, dropdown daftar notifikasi terbaru dengan penunjuk waktu relatif (*time ago*), tombol "Tandai Dibaca", serta tautan langsung ke halaman terkait.
- **Toolbar Minimalis & Padat**: Tombol aksi Data Sekolah SLTA disederhanakan dari teks panjang ("Integrasi Dapodik & Kemenag", "Download Data Sekolah", "Tambah Sekolah Baru") menjadi ("Integrasi", "Export", "+ Tambah", "Import", "Template").
- **Tipografi Bersih**: Menghilangkan kalimat redundan dan deskripsi panjang pada dashboard banner, card statistik, dan tabel leaderboard.

---

### 6. Peningkatan Keamanan & Performa
- **Security Headers**: Enforce `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `X-XSS-Protection: 1; mode=block`, dan `Referrer-Policy: strict-origin-when-cross-origin`.
- **Brute Force Protection**: Menambahkan middleware `throttle:10,1` pada route `POST /login`.
- **Query Hardening**: Validasi nilai arah sorting SQL (`$sortDir`) strictly dibatasi ke `['asc', 'desc']`.
- **Formula Injection Shield**: Prepend kutip satu `'` pada setiap sel spreadsheet yang berpotensi memicu eksekusi rumus.

---

### 7. Hasil Pengujian
- **PHPUnit Feature & Unit Tests**: 44 passed (229 assertions).
- **Execution Time**: 8.78 detik (100% lulus, 0 error, 0 failure).
- **Cakupan Pengujian**:
  - `NotificationAndSecurityHardeningTest` (5 passed, 35 assertions)
  - `PublishRoadmapHardeningTest` (18 passed)
  - `SchoolExcelImportTest` (3 passed)
  - `SchoolStudentIntakeImportTest` (3 passed)
  - `StudentImportTest` (2 passed)
  - `StudentQuestionnaireTest` (9 passed)
  - `CompletedSocializationTest` (2 passed)
  - `ExampleTest` (2 passed)

---

### 8. Catatan Deployment
Saat melakukan pembaruan di server staging/production (`sapa.sobi.web.id`), jalankan perintah berikut:
```bash
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
```

