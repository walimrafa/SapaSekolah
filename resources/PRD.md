# Product Requirements Document (PRD)

## WordCraft AI — Sistem Otomatisasi Artikel

| Informasi | Detail |
|---|---|
| Nama produk | WordCraft AI |
| Jenis dokumen | Product Requirements Document |
| Versi | 1.0 |
| Tanggal | 17 Oktober 2026 |
| Dasar penyusunan | Fitur dan alur yang tersedia pada program saat ini |

## 1. Ringkasan Produk

WordCraft AI adalah aplikasi web internal untuk membantu operator membuat artikel berita dan artikel kampus dengan bantuan AI. Operator dapat membuat artikel langsung atau memasukkannya ke jadwal rilis, menambahkan konteks kampus maupun kegiatan, lalu mengunduh hasil sebagai dokumen Microsoft Word (.docx). Sistem memproses jadwal secara berkala dan menampilkan status artikel di dashboard.

Konten artikel dibuat menggunakan Gemini API. Untuk artikel non-kegiatan, sistem juga mencoba menyediakan gambar sampul dari layanan gambar eksternal dengan mekanisme cadangan. Untuk artikel kegiatan, operator dapat mengunggah gambar dokumentasi sendiri.

## 2. Latar Belakang dan Masalah

Penulisan artikel rutin untuk beberapa kampus atau kanal berita memerlukan waktu untuk menyusun konten, menyesuaikan gaya dan konteks, menyiapkan gambar, serta menghasilkan dokumen yang siap digunakan. Pengelolaan jadwal dan hasil yang tersebar juga menyulitkan operator mengetahui artikel yang masih menunggu, sedang diproses, berhasil, atau gagal.

WordCraft AI menyatukan pembuatan artikel, penjadwalan, pengolahan status, dan unduhan hasil dalam satu dashboard.

## 3. Tujuan Produk

1. Mempercepat pembuatan draf artikel dengan bantuan AI.
2. Mendukung artikel umum, artikel kampus, opini, dan liputan kegiatan.
3. Memungkinkan artikel dibuat segera atau diproses pada tanggal dan waktu yang ditentukan.
4. Menghasilkan dokumen `.docx` yang dapat diunduh satu per satu atau sekaligus.
5. Menyediakan satu tempat bagi operator untuk mengatur prompt, kredensial layanan, akun, dan antrean artikel.

## 4. Pengguna dan Peran

### Operator / pengguna terautentikasi

- Masuk ke dashboard dengan username dan password.
- Mengatur konfigurasi AI dan prompt artikel.
- Membuat dan memantau artikel.
- Mengunduh atau menghapus artikel.
- Mengubah password dan mengelola akun pengguna lain.

Program saat ini hanya membedakan pengguna yang sudah masuk dan tamu; belum terlihat adanya peran atau hak akses granular yang berbeda.

## 5. Cakupan Produk

### Termasuk

- Login, logout, dan verifikasi matematika sederhana pada form login.
- Dashboard konfigurasi dan antrean artikel.
- Pembuatan artikel instan dan terjadwal.
- Artikel untuk kampus UBSI Karawang, UBSI Cikampek, kanal Signaltodays, atau artikel umum tanpa pilihan kampus.
- Konteks artikel non-kegiatan dan liputan kegiatan.
- Pembuatan konten artikel berbantuan Gemini.
- Pengadaan gambar artikel non-kegiatan dan unggahan gambar untuk kegiatan.
- Pembuatan serta pengunduhan dokumen Word, termasuk unduhan massal dalam ZIP.
- Pengelolaan akun, password, dan penghapusan artikel.
- Sinkronisasi judul dan jadwal dari berkas JSON melalui perintah aplikasi.

### Tidak termasuk dalam fitur yang terkonfirmasi

- Publikasi artikel otomatis ke WordPress atau CMS lain.
- Editor untuk menyunting konten hasil AI sebelum menjadi dokumen.
- Persetujuan editorial atau alur review bertingkat.
- Analitik performa artikel, komentar, dan kolaborasi beberapa operator pada artikel yang sama.
- Pengaturan peran/izin pengguna secara granular.

## 6. Alur Pengguna Utama

1. Pengguna masuk ke aplikasi melalui halaman login.
2. Pengguna dapat mengatur prompt artikel dan kredensial layanan yang diperlukan.
3. Pengguna mengisi judul, gaya, kategori, pilihan kampus, dan jadwal atau memilih pembuatan instan.
4. Jika artikel merupakan liputan kegiatan, pengguna mengisi informasi kegiatan dan dapat mengunggah gambar dokumentasi.
5. Sistem membuat satu artikel untuk setiap kampus yang dipilih. Jika tidak ada kampus yang dipilih, sistem membuat satu artikel umum.
6. Untuk mode terjadwal, artikel masuk antrean dan diproses saat jadwalnya tercapai.
7. Sistem menghasilkan konten, menyiapkan gambar sesuai tipe artikel, lalu menyimpan dokumen Word.
8. Pengguna memantau status dan mengunduh atau menghapus hasil dari dashboard.

## 7. Persyaratan Fungsional

### FR-01 — Autentikasi

- Sistem harus menyediakan form login menggunakan username dan password.
- Form login harus meminta jawaban captcha matematika yang dibuat saat form ditampilkan.
- Setelah login berhasil, sesi pengguna harus diperbarui dan pengguna diarahkan ke dashboard.
- Pengguna harus dapat logout dan mengakhiri sesi.
- Halaman dashboard dan tindakan pengelolaan harus hanya dapat diakses oleh pengguna yang sudah terautentikasi.

### FR-02 — Konfigurasi AI dan prompt

- Pengguna dapat menyimpan konfigurasi Gemini API, Pexels API, dan API gambar non-kegiatan melalui halaman pengaturan.
- Pengguna dapat mengubah prompt artikel yang digunakan untuk konten kampus.
- Prompt mendukung substitusi variabel `[JUDUL]`, `[GAYA]`, dan `[KAMPUS]`.
- Gemini dapat menerima beberapa API key yang dipisahkan dengan baris baru, koma, atau titik koma; sistem mencoba key lain bila pemanggilan gagal dan dapat mencoba ulang pada kondisi kuota tertentu.
- Nilai konfigurasi harus tetap tersedia ketika halaman dashboard dibuka kembali.

### FR-03 — Input artikel

- Form pembuatan artikel menampilkan penentuan jumlah draf artikel di langkah pertama (1–50 artikel).
- Ketika jumlah artikel lebih dari satu, sistem menyediakan fitur auto-parafrase judul berbasis AI di langkah berikutnya, yang secara otomatis memparafrase dan menyesuaikan judul sebanyak kuantitas yang diinput.
- Pengguna dapat mengedit masing-masing judul hasil parafrase atau membiarkan sistem memparafrase judul secara otomatis saat penyimpanan artikel.
- Judul artikel wajib diisi dan panjangnya maksimal 255 karakter.
- Pengguna dapat memilih gaya penulisan: Judul Paling Menarik, Gaya Media Nasional, Gaya Emosional, atau Opini Formal & Akademis.
- Pengguna dapat memasukkan nama penulis opini; jika diisi, prompt harus meminta konten bergaya opini dan menyebut nama tersebut.
- Pengguna dapat memilih kategori berita non-kegiatan: NEWS, PENDIDIKAN, LOKER, OLAHRAGA, POLITIK, LIFESTYLE, TRAVELLER, UMKM, atau OPINI.
- Pengguna dapat memilih satu atau lebih konteks: UBSI Karawang, UBSI Cikampek, dan Signaltodays.
- Sistem membuat draf artikel sesuai jumlah yang ditentukan untuk setiap konteks kampus/kanal yang dipilih (atau artikel umum jika tidak ada target kampus yang dipilih).
- Setiap draf artikel yang dihasilkan AI memiliki variasi redaksional dan sudut pandang yang unik.

### FR-04 — Input liputan kegiatan

- Pengguna dapat menandai artikel sebagai liputan kegiatan.
- Kategori kegiatan yang tersedia adalah Pra Event, Hari H Event, dan Pasca Event.
- Pengguna dapat mengisi penyelenggara, pembawa acara, narasumber, pelaksana, peserta, serta tempat dan waktu.
- Semua rincian kegiatan bersifat opsional pada validasi input saat ini; apabila diberikan, prompt harus menyertakannya sebagai informasi liputan.
- Pengguna dapat mengunggah gambar kegiatan opsional dalam format JPEG, PNG, JPG, atau GIF dengan ukuran maksimal 5 MB.
- Jika gambar kegiatan tersedia, sistem harus menggunakannya sebagai gambar sampul dokumen.
- Prompt harus menyesuaikan sudut pandang artikel dengan kategori Pra Event, Hari H Event, atau Pasca Event.

### FR-05 — Pembuatan langsung dan penjadwalan

- Pengguna dapat memilih pembuatan langsung atau memasukkan artikel ke jadwal.
- Mode terjadwal mewajibkan tanggal dan waktu rilis.
- Artikel terjadwal disimpan dengan status `pending`.
- Proses terjadwal memeriksa artikel setiap menit dan hanya memproses artikel pending yang waktu jadwalnya telah tiba.
- Mode langsung menjalankan proses pembuatan saat form dikirim dan menampilkan ringkasan jumlah berhasil dan gagal.

### FR-06 — Pembuatan konten dan gambar

- Sistem harus meminta Gemini menghasilkan artikel dalam HTML sederhana dan menyimpan konten hasilnya.
- Prompt default menargetkan artikel komprehensif sekitar 800–1.500 kata dengan format HTML yang dapat diproses ke Word.
- Prompt artikel kampus harus menyesuaikan konteks kampus; prompt Signaltodays menggunakan arahan berita umum tersendiri.
- Sistem harus menambahkan arahan CTA yang sesuai untuk UBSI Karawang, UBSI Cikampek, dan Signaltodays.
- Untuk artikel non-kegiatan, sistem mencoba mendapatkan gambar yang relevan melalui layanan gambar yang dikonfigurasi dan menggunakan mekanisme fallback bila pencarian/generasi utama gagal.
- Untuk artikel kegiatan tanpa gambar unggahan, pembuatan artikel tetap dapat dilanjutkan tanpa gambar.
- Jika pembuatan artikel gagal, sistem harus menyimpan status gagal dan pesan kesalahan untuk ditampilkan/ditelusuri operator.

### FR-07 — Status dan antrean

- Setiap artikel harus memiliki salah satu status: `pending`, `processing`, `completed`, atau `failed`.
- Dashboard harus menampilkan daftar artikel terurut berdasarkan jadwal rilis terbaru dan membaginya menjadi halaman berisi lima artikel.
- Dashboard harus menampilkan jumlah artikel selesai.
- Artikel gagal dapat dicoba ulang otomatis selama jumlah retry masih kurang dari tiga.
- Pengguna harus dapat melihat status artikel dan mengakses unduhan apabila dokumen telah tersedia.

### FR-08 — Dokumen dan penghapusan

- Untuk artikel yang berhasil, sistem harus membuat dokumen `.docx` berisi judul, konten, dan gambar jika tersedia.
- Pengguna dapat mengunduh dokumen satu per satu.
- Pengguna dapat mengunduh seluruh dokumen artikel selesai yang tersedia sebagai satu berkas ZIP.
- Pengguna dapat menghapus satu artikel, beberapa artikel terpilih, atau seluruh artikel.
- Saat artikel dihapus, dokumen Word terkait harus ikut dihapus jika ada.
- Sistem harus memberi pesan yang jelas apabila dokumen belum tersedia atau berkas fisiknya tidak ditemukan.

### FR-09 — Manajemen pengguna

- Pengguna dapat mengganti password dengan memasukkan password saat ini.
- Password baru harus minimal enam karakter dan harus dikonfirmasi.
- Pengguna dapat membuat akun baru dengan nama, username unik, dan password yang memenuhi ketentuan.
- Pengguna tidak dapat menghapus akun yang sedang digunakan.
- Password harus disimpan dalam bentuk hash, bukan teks biasa.

### FR-10 — Impor daftar artikel

- Perintah aplikasi `articles:sync` dapat membaca `articles_to_generate.json` di direktori utama proyek.
- Setiap item yang diimpor harus memiliki judul dan `scheduled_at`.
- Artikel baru dibuat dengan status `pending`; judul yang sudah ada dilewati.
- Jika berkas tidak ditemukan atau JSON tidak valid, proses harus melaporkan kegagalan secara eksplisit.

## 8. Aturan Bisnis dan Status

| Status | Makna |
|---|---|
| `pending` | Artikel menunggu jadwal diproses |
| `processing` | Artikel sedang dibuat |
| `completed` | Konten dan dokumen Word berhasil dibuat |
| `failed` | Pembuatan gagal; pesan kesalahan dicatat dan artikel dapat dicoba ulang sesuai batas retry |

- Artikel terjadwal diproses pada atau setelah waktu `scheduled_at`.
- Batas percobaan ulang otomatis adalah tiga kegagalan yang tercatat.
- Satu pengiriman form dengan beberapa pilihan kampus membuat beberapa rekaman artikel, satu per pilihan.
- Kategori non-kegiatan dan kategori kegiatan merupakan alur input yang berbeda.

## 9. Persyaratan Non-Fungsional

- **Keamanan akses:** halaman dashboard dan aksi artikel/pengguna hanya tersedia bagi pengguna terautentikasi.
- **Keamanan kredensial:** password akun harus di-hash; kredensial layanan AI harus diperlakukan sebagai rahasia dan tidak ditampilkan sebagai teks biasa pada UI.
- **Keandalan integrasi:** kegagalan pemanggilan layanan eksternal harus menghasilkan status gagal atau fallback yang sesuai, bukan menganggap artikel berhasil dibuat.
- **Keterlacakan:** status dan pesan kesalahan artikel harus tersimpan agar operator dapat mengetahui hasil pemrosesan.
- **Kualitas dokumen:** hasil sukses harus tersedia sebagai `.docx` yang dapat diunduh.
- **Operasional terjadwal:** lingkungan deployment harus menjalankan scheduler Laravel secara berkala agar pemrosesan artikel terjadwal berjalan.
- **Batas unggahan:** gambar kegiatan tidak boleh melebihi 5 MB dan hanya format gambar yang didukung yang diterima.

## 10. Kriteria Penerimaan

1. Pengguna yang belum login tidak dapat membuka dashboard atau menjalankan aksi yang dilindungi.
2. Pengguna yang memasukkan jawaban captcha atau kredensial login yang salah menerima pesan kesalahan dan tetap berada pada alur login.
3. Pengguna dapat menyimpan konfigurasi dan prompt; nilai yang disimpan dimuat kembali pada dashboard.
4. Artikel terjadwal dengan tanggal di masa depan tersimpan sebagai pending dan tidak diproses sebelum waktunya.
5. Artikel pending yang jadwalnya telah tiba diproses oleh scheduler, statusnya berubah, dan hasil sukses menghasilkan dokumen Word.
6. Pembuatan langsung menghasilkan pesan jumlah artikel berhasil/gagal; pilihan beberapa kampus menghasilkan satu artikel per kampus.
7. Artikel kegiatan menyertakan rincian kegiatan yang diisi dan memakai gambar unggahan jika diberikan.
8. Artikel gagal mencatat pesan kesalahan dan percobaan ulang berhenti setelah batas tiga kali.
9. Pengguna dapat mengunduh dokumen artikel selesai, atau mengunduh kumpulan dokumen yang tersedia sebagai ZIP.
10. Penghapusan artikel satuan, massal, dan seluruhnya menghapus rekaman artikel serta dokumen terkait jika ada.
11. Pengguna dapat mengganti password dengan password lama yang benar, menambahkan pengguna dengan username unik, dan tidak dapat menghapus akun sendiri.
12. Sinkronisasi JSON menambahkan artikel valid yang belum terdaftar dan melewati judul yang sudah ada.

## 11. Dependensi Eksternal

- Gemini API untuk pembuatan konten artikel.
- Layanan OpenAI/generasi gambar dan Pexels untuk proses gambar artikel non-kegiatan, dengan fallback gambar yang tersedia dalam implementasi.
- Database aplikasi untuk akun, konfigurasi, dan data artikel.
- Laravel scheduler untuk memproses artikel terjadwal.
- PHPWord untuk pembuatan dokumen `.docx` dan dukungan arsip ZIP.

Ketersediaan, kuota, kredensial, dan perubahan API pihak ketiga dapat memengaruhi keberhasilan pembuatan artikel.

## 12. Asumsi dan Catatan

- Dokumen ini mendeskripsikan produk berdasarkan implementasi yang ditemukan, bukan spesifikasi fitur masa depan.
- Isi artikel adalah draf berbantuan AI; program yang ditinjau tidak menunjukkan proses verifikasi fakta atau persetujuan editorial.
- Adanya kolom data terkait URL WordPress tidak dengan sendirinya berarti publikasi WordPress sudah tersedia; tidak ada alur publikasi yang terkonfirmasi dalam cakupan ini.
- Fitur yang tidak tercantum pada cakupan saat ini tidak dianggap sebagai bagian dari komitmen produk.
