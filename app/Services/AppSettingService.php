<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AppSettingService
{
    protected static ?array $cache = null;

    protected static function getFilePath(): string
    {
        return storage_path('app/app_settings.json');
    }

    public static function clearCache(): void
    {
        static::$cache = null;
    }

    /**
     * Get all app settings.
     */
    public static function all(): array
    {
        if (static::$cache !== null) {
            return static::$cache;
        }

        $defaults = [
            'app_name' => 'SAPA Sekolah',
            'app_subtitle' => 'Command Center',
            'app_logo_url' => '/logo-sapa-sekolah.png',
            'campus_building_url' => '/storage/branding/ubsi_karawang_building.jpg',
            'header_title' => 'SAPA Sekolah Marketing Command Center',
            'footer_text' => 'SAPA Sekolah © ' . date('Y') . ' • Perguruan Tinggi Marketing Mapping System',
            'footer_subtext' => 'UBSI Kampus Karawang • Jawa Barat',
            'campus_name' => 'Universitas Bina Sarana Informatika (UBSI)',
            'campus_address' => SchoolScoringService::UBSI_ADDRESS,
            'campus_latitude' => SchoolScoringService::UBSI_LATITUDE,
            'campus_longitude' => SchoolScoringService::UBSI_LONGITUDE,
            'campus_phone' => '081220022851',
            'admin_phone' => '081220022851',
            'campus_email' => 'karawang@bsi.ac.id',
            'campus_website' => 'https://bsi.ac.id',
            'theme_color_primary' => '#0B023E',
            'theme_color_accent' => '#DF0B2B',
            'theme_color_secondary' => '#1A1A1A',
            'theme_color_bg' => '#F2F2F2',
            'kol_min_followers' => 1500,
            'last_period_closed_at' => null,
            'last_period_closed_by' => null,
            'sidebar_labels' => static::defaultSidebarLabels(),
        ];

        $dbData = [];
        try {
            if (Schema::hasTable('app_settings')) {
                $rows = DB::table('app_settings')->get();
                foreach ($rows as $row) {
                    $val = $row->value;
                    $decoded = json_decode($val, true);
                    $dbData[$row->key] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : $val;
                }
            }
        } catch (\Throwable $e) {
            // DB not available yet
        }

        $fileData = [];
        $filePath = static::getFilePath();
        if (file_exists($filePath)) {
            $json = json_decode(file_get_contents($filePath), true);
            if (is_array($json)) {
                $fileData = $json;
            }
        }

        $merged = array_merge($defaults, $fileData, $dbData);
        if (isset($merged['sidebar_labels']) && is_array($merged['sidebar_labels'])) {
            $merged['sidebar_labels'] = array_merge(static::defaultSidebarLabels(), $merged['sidebar_labels']);
        }

        static::$cache = $merged;
        return static::$cache;
    }

    /**
     * Daftar label default untuk seluruh menu dan sub-menu navigasi sidebar.
     */
    public static function defaultSidebarLabels(): array
    {
        return [
            // 1. Dashboard
            'menu_dashboard' => 'Dashboard',

            // 2. Data & GIS Sekolah
            'menu_gis' => 'Data Sekolah',
            'badge_gis' => 'GIS',
            'submenu_map' => 'Peta GIS',
            'submenu_schools' => 'Daftar Sekolah',
            'submenu_priorities' => 'Prioritas',

            // 3. Interaksi & Kontak (Customer Service)
            'menu_cs_interactions' => 'Kontak & CS',
            'submenu_cs_followups' => 'Follow Up',
            'submenu_cs_qrcodes' => 'QR Code',

            // 4. Operasional Lapangan (Field)
            'menu_field' => 'Operasional',
            'submenu_visit_plans' => 'Rencana',
            'submenu_visits' => 'Kunjungan',
            'submenu_field_followups' => 'Follow Up',
            'submenu_socializations_completed' => 'Sosialisasi',
            'submenu_marketing_routes' => 'Rute Mobil',
            'submenu_activities' => 'Aktivitas',

            // 5. CRM (Prospek & Konversi)
            'menu_crm' => 'Prospek & CRM',
            'badge_crm' => 'CRM',
            'submenu_prospects' => 'Prospek',
            'submenu_new_students' => 'Mahasiswa Baru',
            'submenu_questionnaires' => 'Kuisioner',
            'submenu_student_followups' => 'Follow Up',
            'submenu_cs_progress' => 'Kontrol Progres CS',
            'submenu_study_programs' => 'Prodi',

            // 6. Rekomendasi KOL & Brand Ambassador
            'menu_kol_recommendations' => 'Rekomendasi KOL',
            'badge_kol' => 'KOL',
            'submenu_kol_candidates' => 'Daftar Calon KOL',
            'submenu_kol_documentation' => 'Hasil Dokumentasi KOL',

            // 7. Support Event Sekolah
            'menu_events' => 'Support Event',
            'badge_events' => 'Event',
            'submenu_event_proposals' => 'Proposal',
            'submenu_event_students' => 'Data Siswa Hasil Support Event',

            // 8. Laporan
            'menu_reports' => 'Laporan',
            'badge_reports' => 'Laporan',
            'submenu_report_charts' => 'Diagram & Analisis',
            'submenu_report_data' => 'Laporan Data',
            'submenu_report_events' => 'Laporan & Dokumentasi Kegiatan',
            'submenu_report_export' => 'Ekspor & Rekap',

            // 9. Informasi PMB
            'menu_pmb' => 'Info PMB',
            'badge_pmb' => 'UBSI',
            'submenu_pmb_portal' => 'Portal',
            'submenu_ebrochures' => 'E-Brosur',
            'submenu_pmb_web' => 'Website',
            'submenu_pmb_campus' => 'Biaya Kuliah',
            'submenu_pmb_schedule' => 'Jadwal',
            'submenu_pmb_procedure' => 'Alur Daftar',
            'submenu_pmb_scholarship' => 'Beasiswa',

            // 10. Master Data (Akademik & Kampus)
            'menu_master_data' => 'Master Data',
            'badge_master_data' => 'Akademik',
            'submenu_campuses' => 'Master Kampus',
            'submenu_periods' => 'Master Periode',
            'submenu_master_data' => 'Master Wilayah',

            // 11. Portal & Kuisioner Publik
            'menu_portals_questionnaires' => 'Portal & Kuisioner',
            'badge_portals' => 'Publik',
            'submenu_portals' => 'Portal Publik',
            'submenu_master_questionnaires' => 'Master Kuisioner',
            'submenu_qr_codes' => 'QR Code',

            // 12. Sistem & Manajemen Operasional
            'menu_system_ops' => 'Sistem & Manajemen',
            'badge_system_ops' => 'Admin',
            'submenu_users' => 'User & Akun',
            'submenu_whatsapp' => 'WhatsApp Gateway',
            'submenu_settings' => 'Pengaturan Branding',
            'submenu_database_backups' => 'Cadangan Database GDrive',
            'submenu_activity_logs' => 'Log Aktivitas',
            'submenu_period_closing' => 'Tutup Periode',

            // Legacy backward compatibility keys
            'menu_master' => 'Master & Sistem',
            'submenu_kol_recommendations' => 'Rekomendasi KOL',
        ];
    }

    /**
     * Dapatkan label sidebar berdasarkan key (dengan fallback ke default).
     */
    public static function getSidebarLabel(string $key, ?string $fallback = null): string
    {
        $defaults = static::defaultSidebarLabels();
        $all = static::all();
        $custom = $all['sidebar_labels'][$key] ?? null;

        if (!empty($custom) && trim($custom) !== '') {
            return trim($custom);
        }

        return $defaults[$key] ?? ($fallback ?? $key);
    }

    /**
     * Get a specific setting value by key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $all = static::all();
        return $all[$key] ?? $default;
    }

    /**
     * Save/update single setting.
     */
    public static function set(string $key, mixed $value): void
    {
        static::setMany([$key => $value]);
    }

    /**
     * Save/update array of settings.
     */
    public static function setMany(array $settings): void
    {
        $current = static::all();

        if (isset($settings['sidebar_labels']) && is_array($settings['sidebar_labels'])) {
            $defaultLabels = static::defaultSidebarLabels();
            $mergedLabels = $current['sidebar_labels'] ?? $defaultLabels;

            foreach ($settings['sidebar_labels'] as $k => $v) {
                if (array_key_exists($k, $defaultLabels)) {
                    $trimmed = is_string($v) ? trim($v) : '';
                    $mergedLabels[$k] = $trimmed !== '' ? $trimmed : $defaultLabels[$k];
                }
            }
            $settings['sidebar_labels'] = $mergedLabels;
        }

        $updated = array_merge($current, $settings);

        // Persist each key-value to MySQL database
        try {
            if (Schema::hasTable('app_settings')) {
                foreach ($settings as $key => $val) {
                    $encodedVal = is_array($val) ? json_encode($val, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : (string) $val;
                    DB::table('app_settings')->updateOrInsert(
                        ['key' => $key],
                        [
                            'value' => $encodedVal,
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("AppSettingService save to DB warning: " . $e->getMessage());
        }

        // Also save to JSON file as local cache fallback
        try {
            $dir = dirname(static::getFilePath());
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
            file_put_contents(static::getFilePath(), json_encode($updated, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("AppSettingService save to JSON warning: " . $e->getMessage());
        }

        static::$cache = $updated;
    }

    /**
     * Dapatkan URL resmi logo aplikasi yang terpasang di sistem.
     * Dilengkapi cache-busting otomatis (?v=timestamp) agar perubahan logo langsung tampak seketika di browser.
     */
    public static function getLogoUrl(bool $withCacheBuster = true): string
    {
        $logo = static::get('app_logo_url');
        if (empty($logo)) {
            $logo = '/logo-sapa-sekolah.png';
        }

        // Jika URL eksternal (http:// atau https://), kembalikan langsung
        if (str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://')) {
            return $logo;
        }

        $relativePath = ltrim($logo, '/');
        $resolvedUrl = asset($relativePath);

        if (!$withCacheBuster) {
            return $resolvedUrl;
        }

        // Dapatkan path fisik file di server untuk mengecek timestamp modifikasi (filemtime)
        $localPath = static::resolveLocalPath($logo);
        $v = ($localPath && file_exists($localPath)) ? filemtime($localPath) : substr(md5($logo), 0, 8);

        return $resolvedUrl . '?v=' . $v;
    }

    /**
     * Cari lokasi file fisik di server berdasarkan URL path logo/gambar.
     */
    public static function resolveLocalPath(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return null;
        }

        $clean = ltrim($url, '/');

        // Jika diawali storage/
        if (str_starts_with($clean, 'storage/')) {
            $storageSubPath = substr($clean, 8); // buang 'storage/'
            $storageDiskPath = storage_path('app/public/' . $storageSubPath);
            if (file_exists($storageDiskPath)) {
                return $storageDiskPath;
            }
        }

        $publicPath = public_path($clean);
        if (file_exists($publicPath)) {
            return $publicPath;
        }

        return null;
    }

    /**
     * Sinkronisasikan berkas logo ke seluruh aset publik:
     * - public/logo-sapa-sekolah.png (logo acuan utama)
     * - public/favicon.png, favicon.ico, favicon-32x32.png, favicon-16x16.png
     * - public/apple-touch-icon.png
     * - Seluruh varian ikon PWA (72, 96, 128, 144, 152, 192, 384, 512)
     */
    public static function syncLogoToPublicAssets(?string $sourcePath = null): bool
    {
        if (!$sourcePath) {
            $logoSetting = static::get('app_logo_url');
            $sourcePath = static::resolveLocalPath($logoSetting);
        }

        if (!$sourcePath || !file_exists($sourcePath)) {
            return false;
        }

        try {
            // 1. Copy ke public/logo-sapa-sekolah.png (hanya jika sumbernya bukan file itu sendiri)
            $publicLogo = public_path('logo-sapa-sekolah.png');
            if (realpath($sourcePath) !== realpath($publicLogo)) {
                @copy($sourcePath, $publicLogo);
            }

            // 2. Copy ke favicon utama & apple touch icon
            @copy($sourcePath, public_path('favicon.png'));
            @copy($sourcePath, public_path('favicon.ico'));
            @copy($sourcePath, public_path('apple-touch-icon.png'));

            // 3. Resize menggunakan GD jika tersedia
            $sizes = [
                'favicon-16x16.png' => 16,
                'favicon-32x32.png' => 32,
                'pwa-icon-72.png' => 72,
                'pwa-icon-96.png' => 96,
                'pwa-icon-128.png' => 128,
                'pwa-icon-144.png' => 144,
                'pwa-icon-152.png' => 152,
                'pwa-icon-192.png' => 192,
                'pwa-icon-384.png' => 384,
                'pwa-icon-512.png' => 512,
            ];

            $gdAvailable = extension_loaded('gd') && function_exists('imagecreatefromstring');

            if ($gdAvailable) {
                $rawContent = file_get_contents($sourcePath);
                $srcImg = @imagecreatefromstring($rawContent);

                if ($srcImg) {
                    $srcW = imagesx($srcImg);
                    $srcH = imagesy($srcImg);

                    foreach ($sizes as $filename => $size) {
                        $targetPath = public_path($filename);
                        $dst = imagecreatetruecolor($size, $size);
                        imagealphablending($dst, false);
                        imagesavealpha($dst, true);
                        $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
                        imagefilledrectangle($dst, 0, 0, $size, $size, $transparent);
                        imagecopyresampled($dst, $srcImg, 0, 0, 0, 0, $size, $size, $srcW, $srcH);
                        imagepng($dst, $targetPath, 9);
                        imagedestroy($dst);
                    }

                    imagedestroy($srcImg);
                    return true;
                }
            }

            // Fallback jika GD gagal / non-raster: salin file langsung
            foreach ($sizes as $filename => $size) {
                @copy($sourcePath, public_path($filename));
            }

            return true;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal menyinkronkan aset logo ke public: ' . $e->getMessage());
            return false;
        }
    }
}
