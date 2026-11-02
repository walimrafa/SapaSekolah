# 📋 CATATAN PERBAIKAN & UPDATE SISTEM SAPA SEKOLAH

### 📌 Ringkasan Versi
- **Versi**: 2.1.1 (Validasi Kelas Mahasiswa Baru, Netralisasi Event Form Multi-Kampus & Hardening)
- **Tanggal Pembaruan**: 2 November 2026
- **Status Repository**: Terhubung & Tersinkronisasi ke GitHub (`main`)
- **Hasil Pengujian Otomatis**: 45 Test Berhasil (242 Assertions) — **0 Bug, 0 Error, 100% Pass**

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

### 8. Pemisahan Data Support Event Karawang & Cikampek
* **Sebelumnya**:
  * Di portal pengajuan support event sekolah publik (`/pengajuan-event`), seluruh sekolah dari Karawang dan Cikampek tercampur dalam satu dropdown panjang tanpa filter kampus.
  * Di halaman internal Data Proposal Masuk (`/event-proposals`) dan Laporan Suport Kegiatan (`/event-reports`), data proposal kedua kampus bercampur dan tidak memiliki tab pemisah wilayah.
* **Sesudah Perbaikan**:
  * **Form Publik Pengajuan Event**: Radio button pilihan kampus (UBSI Karawang vs UBSI Cikampek) langsung memfilter opsi sekolah secara reaktif dan otomatis mengelompokkan opsi berdasarkan `<optgroup>` wilayah kampus.
  * **Dashboard Proposal & Laporan Admin**: Disediakan tab pemisah kampus (*Semua Kampus*, *UBSI Karawang*, *UBSI Cikampek*) lengkap dengan hitungan counter proposal, sehingga data kegiatan Karawang dan Cikampek terpisah rapi dengan 1 klik.

---

### 9. PWA Mobile Bottom Navigation Horizontal & Responsif Smartphone
* **Sebelumnya**:
  * Tampilan menu bawah (Bottom Navigation) di smartphone bertumpuk vertikal akibat styling stylesheet Tailwind yang belum terkompilasi penuh untuk komponen PWA mobile.
* **Sesudah Perbaikan**:
  * Menggunakan inline flexbox styling (`display: flex; flex-direction: row; justify-content: space-around; height: 58px; position: fixed; bottom: 0; z-index: 45;`) menjamin tata letak horizontal sempurna di semua browser handphone.
  * Tombol tengah FAB (`+ Kunjungan`) terangkat rapi tanpa merusak barisan tombol lainnya.
  * Panel drawer aksi cepat lapangan dilengkapi backdrop dimmer ber-z-index tinggi (`z-index: 60`).

---

### 10. Optimalisasi Peta GIS pada Smartphone
* **Sebelumnya**:
  * Pada layar HP (<1024px), panel leaderboard skor prioritas sekolah langsung terbuka menutupi peta dan kolom pencarian, serta dropdown filter saling bertumpuk banyak baris.
* **Sesudah Perbaikan**:
  * Panel leaderboard GIS kini tertutup secara default pada perangkat mobile (`panelOpen: window.innerWidth >= 1024`), dan dapat dibuka/tutup dengan tombol *"Daftar"* di navbar peta.
  * Filter GIS mobile dibuat ringkas dan dapat diciutkan (*collapsible*) melalui tombol filter bergambar ikon slider.

---

### 11. Halaman Login Bersih, Ringkas & Mobile-First
* **Sebelumnya**:
  * Teks pada hero banner login terlalu panjang dan bertele-tele. Di smartphone, pengguna harus scrolling jauh melewati teks promosi panjang sebelum menemukan kolom input email dan kata sandi.
* **Sesudah Perbaikan**:
  * Teks slogan dipadatkan menjadi: *"Digital Mapping & Monitoring PMB Realtime"*.
  * Kolom hero branding disembunyikan pada viewport kecil (`hidden lg:flex`) sehingga pengguna smartphone langsung disajikan formulir login yang bersih, rapi, dan cepat.
  * Kartu perkenalan 2 gedung kampus (Karawang & Cikampek) dikompresi ringkas dan proporsional.

---

### 12. Resolusi Peringatan Intelephense (Date Method)
* **Sebelumnya**:
  * Terdapat peringatan `Call to unknown method: date::format()` pada Intelephense VS Code di `SchoolEventProposalController.php`.
* **Sesudah Perbaikan**:
  * Seluruh pemformatan tanggal model dienkapsulasi menggunakan `\Carbon\Carbon::parse(...)` dan PHPDoc model `SchoolEventProposal` dilengkapi tipe eksplisit `\Illuminate\Support\Carbon|\Carbon\Carbon|null`. Peringatan di Intelephense bersih 100%.

---

### 13. Visibilitas Master Kampus & Master Periode pada Smartphone
* **Sebelumnya**:
  * Di layar smartphone (<640px), tombol pemilih kampus operasional dan tahun periode PMB di top navbar disembunyikan menggunakan class `hidden sm:flex` agar tidak bertumpuk di layar kecil.
  * Di dalam menu sidebar *Master & Sistem*, tautan hanya bertuliskan *"Wilayah"* (`/master-data`), tanpa adanya submenu khusus untuk *"Master Kampus"* dan *"Master Periode"*. Akibatnya, pengguna HP tidak menemukan menu pengelolaan maupun pengalihan kampus dan periode.
* **Sesudah Perbaikan**:
  * **Sub-Navbar Khusus Smartphone**: Ditambahkan utility bar modern gelap tepat di bawah top header HP (`sm:hidden`) yang menampilkan:
    * `[ 🎓 UBSI Kampus Karawang ⌵ ]` / `[ 🎓 UBSI Kampus Cikampek ⌵ ]` (lengkap dengan dropdown beralih kampus & tautan kelola *Master Kampus →*).
    * `[ 📅 T.A. 2026/2027 ⌵ ]` (lengkap dengan dropdown beralih periode & tautan kelola *Master Periode →*).
  * **Submenu Langsung di Sidebar**: Pada menu *Master & Sistem*, kini tersedia submenu resmi:
    * 🎓 **Master Kampus** (`/master-data?tab=campuses`)
    * 📅 **Master Periode** (`/master-data?tab=periods`)
    * 📍 **Master Wilayah** (`/master-data?tab=wilayah`)
  * **Shortcut Cepat di Drawer PWA**: Menu drawer cepat di bagian bawah smartphone dilengkapi kartu pintasan langsung ke *Master Kampus* dan *Master Periode*.
  * **Tab Swipeable di Master Data**: Halaman master data kini dapat di-geser horizontal (*swipe*) dengan mulus di smartphone.

---

### 14. Integrasi Kolom Jenis Kelamin & Sinkronisasi Sampai Data Mahasiswa Baru
* **Sebelumnya**:
  * Form Kuisioner Siswa Publik dan Form Hasil Support Event belum memiliki kolom isian **Jenis Kelamin (Laki-laki / Perempuan)**.
  * Ketika siswa mengisi kuisioner atau terdaftar dari event, data tersimpan tanpa informasi jenis kelamin sehingga saat prospek closing/daftar aplikasi dan berstatus menjadi Mahasiswa Baru (`status = 'mahasiswa'`), kolom jenis kelamin di halaman *Data Mahasiswa Baru* kosong (`-`), dan rasio gender L/P tidak akurat.
* **Sesudah Perbaikan**:
  * **Database Migration**: Menambahkan kolom `gender` (`varchar(10)`, nullable) pada tabel `student_questionnaires` dengan index dan accessor `gender_label` ('Laki-laki' / 'Perempuan').
  * **Formulir Publik & Modal Internal**:
    * Form Kuisioner Siswa Publik (`/kuisioner-siswa`): Pilihan radio button Jenis Kelamin L/P yang responsif dan mudah disentuh.
    * Form Hasil Support Event (`/event-siswa/daftar`): Pilihan radio button Jenis Kelamin L/P.
    * Modal Input Internal & Edit Siswa (`/hasil-kuisioner` & `/student-follow-ups`): Dropdown pilihan Jenis Kelamin (L/P).
  * **Sinkronisasi Otomatis ke CRM Prospek & Mahasiswa Baru**:
    * `ProspectSyncService::syncStudentQuestionnaire` memetakan `gender` dari kuisioner ke `prospects.gender`.
    * Ketika prospek berstatus `mahasiswa`, halaman *Data Mahasiswa Baru* (`/new-students`) langsung menampilkan nama siswa beserta badge gender, filter gender (L/P), dan persentase KPI mahasiswa baru L/P yang sinkron dan akurat secara *end-to-end*.
  * **Dukungan Import & Export Excel**:
    * Template dan importer kuisioner/event mengenali kolom `Jenis Kelamin`, `JK`, atau `Gender` ('L'/'P', 'Laki-laki'/'Perempuan').
    * Seluruh ekspor Excel (Kuisioner, Hasil Event, Follow-Up) menyertakan kolom resmi *Jenis Kelamin*.
  * **Optimalisasi Tampilan Smartphone**:
    * **Avatar Profil Top Navbar**: Memperbaiki kontras inisial user (misal: "SU") pada navbar atas smartphone dengan latar belakang tegas dan elegan.
    * **Bottom Navigation Bar PWA**: Memastikan menu bawah (Dashboard, Jadwal, + Kunjungan, Peta GIS, Aksi Cepat) selalu terkunci rapat secara horizontal (`flex-wrap: nowrap !important`) di atas peta dan dashboard pada seluruh ukuran layar handphone.

---

### 15. Penambahan Input Kelas Mahasiswa Baru & Netralisasi Header Form Event Multi-Kampus
* **Sebelumnya**:
  * Pada modal *"Validasi Mahasiswa Baru Resmi"* di halaman CRM Prospek (`/prospects`), hanya ada inputan Nomor Induk Mahasiswa (NIM), tanpa ada inputan **Kelas Mahasiswa Baru** (seperti `12.1A.01` atau `Reguler A`), sehingga informasi pembagian kelas harus dicatat manual di tempat terpisah.
  * Pada formulir publik *"PENDATAAN SISWA - HASIL SUPPORT EVENT"* (`/hasil-support-event`), header di bagian kanan atas tertulis statis *"Universitas Bina Sarana Informatika (UBSI) Kampus Karawang"*. Ketika event diselenggarakan untuk **UBSI Kampus Cikampek**, header tetap menampilkan Karawang, sehingga kurang sesuai dan membingungkan peserta.
* **Sesudah Perbaikan**:
  * **Input Kelas Mahasiswa Baru (NIM & Kelas Berdampingan)**:
    * **Database Migration**: Menambahkan kolom `student_class` (`string(50)`, nullable) pada tabel `prospects` via migration `2026_11_02_044500_add_student_class_to_prospects_table.php`.
    * **Modal Validasi Mahasiswa Baru**: Menghadirkan layout 2 kolom responsif:
      1. *Nomor Induk Mahasiswa (NIM) Resmi \**
      2. *Kelas Mahasiswa Baru \** (contoh placeholder: `12.1A.01 / Reguler A`), lengkap dengan validasi required server-side.
    * **Integrasi Data Mahasiswa Baru (`/new-students`)**:
      - Kolom NIM kini menyertakan badge identitas kelas siswa (`Kls: 12.1A.01`).
      - Modal detail mahasiswa baru menampilkan informasi kelas lengkap.
      - Input pencarian mendukung pencarian instan berdasarkan kode kelas.
      - Ekspor Excel Mahasiswa Baru kini memiliki kolom resmi **Kelas** di kolom C.
    * **Modal Tambah Prospek**: Menyertakan inputan *Kelas Asal* opsional untuk melengkapi data siswa dari awal.
  * **Netralisasi Header Form Hasil Support Event**:
    * Menghapus kata statis *"Kampus Karawang"* dari header kartu form pendataan siswa hasil support event (`resources/views/public-events/student-form.blade.php`).
    * Header kini menampilkan secara netral dan elegan: **Universitas Bina Sarana Informatika (UBSI)**, sehingga dapat digunakan secara universal baik untuk kegiatan di UBSI Kampus Karawang maupun UBSI Kampus Cikampek.
    * Nilai fallback pengaturan nama kampus di `AppSettingService` dan `storage/app/app_settings.json` diselaraskan menjadi nama universitas umum netral.

---

## 🚀 Panduan Rilis di Server Staging / Production
Ketika melakukan pembaruan di server publik (`sapa.sobi.web.id`), jalankan langkah berikut:
```bash
git pull origin main
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
```

