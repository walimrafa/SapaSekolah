<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class AppSettingService
{
    protected static ?array $cache = null;

    protected static function getFilePath(): string
    {
        return storage_path('app/app_settings.json');
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
            'campus_name' => 'Universitas Bina Sarana Informatika (UBSI) Kampus Karawang',
            'campus_address' => SchoolScoringService::UBSI_ADDRESS,
            'campus_latitude' => SchoolScoringService::UBSI_LATITUDE,
            'campus_longitude' => SchoolScoringService::UBSI_LONGITUDE,
            'campus_phone' => '(0267) 8451234',
            'campus_email' => 'karawang@bsi.ac.id',
            'campus_website' => 'https://bsi.ac.id',
            'theme_color_primary' => '#0B023E',
            'theme_color_accent' => '#DF0B2B',
            'theme_color_secondary' => '#1A1A1A',
            'theme_color_bg' => '#F2F2F2',
            'last_period_closed_at' => null,
            'last_period_closed_by' => null,
            'sidebar_labels' => static::defaultSidebarLabels(),
        ];

        $filePath = static::getFilePath();
        if (file_exists($filePath)) {
            $json = json_decode(file_get_contents($filePath), true);
            if (is_array($json)) {
                if (isset($json['sidebar_labels']) && is_array($json['sidebar_labels'])) {
                    $json['sidebar_labels'] = array_merge(static::defaultSidebarLabels(), $json['sidebar_labels']);
                }
                static::$cache = array_merge($defaults, $json);
                return static::$cache;
            }
        }

        static::$cache = $defaults;
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
            'menu_gis' => 'Data & GIS Sekolah',
            'badge_gis' => 'GIS',
            'submenu_map' => 'Peta Sekolah',
            'submenu_schools' => 'Data Sekolah',
            'submenu_priorities' => 'Prioritas Sekolah',

            // 3. Interaksi & Kontak (Customer Service)
            'menu_cs_interactions' => 'Interaksi & Kontak',
            'submenu_cs_followups' => 'Follow Up Sekolah',
            'submenu_cs_qrcodes' => 'Master QR Code',

            // 4. Operasional Lapangan (Field)
            'menu_field' => 'Operasional Lapangan',
            'submenu_visit_plans' => 'Rencana Kunjungan',
            'submenu_visits' => 'Kunjungan',
            'submenu_field_followups' => 'Follow Up',
            'submenu_socializations_completed' => 'Sekolah Sudah Sosialisasi',
            'submenu_marketing_routes' => 'Rute Marketing',
            'submenu_activities' => 'Aktivitas Marketing',

            // 5. CRM (Prospek & Konversi)
            'menu_crm' => 'Prospek & Konversi',
            'badge_crm' => 'CRM',
            'submenu_prospects' => 'Data Prospek',
            'submenu_new_students' => 'Mahasiswa Baru',
            'submenu_questionnaires' => 'Hasil Kuisioner',
            'submenu_student_followups' => 'Follow Up Siswa',
            'submenu_study_programs' => 'Program Studi',
            'submenu_reports' => 'Laporan & Analytics',

            // 6. Support Event Sekolah
            'menu_events' => 'Support Event Sekolah',
            'badge_events' => 'Event',
            'submenu_event_proposals' => 'Data Proposal Masuk',
            'submenu_event_reports' => 'Laporan Suport Kegiatan',
            'submenu_event_students' => 'Hasil Support Event',

            // 7. Informasi PMB
            'menu_pmb' => 'Informasi PMB',
            'badge_pmb' => 'UBSI',
            'submenu_pmb_portal' => 'Pusat Info & Portal',
            'submenu_ebrochures' => 'E-Brosur PMB',
            'submenu_pmb_web' => 'Website PMB UBSI',
            'submenu_pmb_campus' => 'Kampus & Biaya Kuliah',
            'submenu_pmb_schedule' => 'Jadwal & Gelombang',
            'submenu_pmb_procedure' => 'Alur Pendaftaran',
            'submenu_pmb_scholarship' => 'Beasiswa UBSI 2026',

            // 8. Sistem & Master
            'menu_master' => 'Sistem & Master',
            'submenu_master_data' => 'Master Data',
            'submenu_users' => 'User Management',
            'submenu_qr_codes' => 'Master QR Code',
            'submenu_whatsapp' => 'Notifikasi WA',
            'submenu_settings' => 'Pengaturan',
            'submenu_period_closing' => 'Tutup Periode',
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

        $dir = dirname(static::getFilePath());
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(static::getFilePath(), json_encode($updated, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        static::$cache = $updated;
    }
}
