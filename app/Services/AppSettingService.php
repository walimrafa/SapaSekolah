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
            'campus_name' => 'Universitas Bina Sarana Informatika (UBSI)',
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
            'submenu_study_programs' => 'Prodi',

            // 6. Support Event Sekolah
            'menu_events' => 'Support Event',
            'badge_events' => 'Event',
            'submenu_event_proposals' => 'Proposal',
            'submenu_event_students' => 'Data Siswa Hasil Support Event',

            // 7. Laporan
            'menu_reports' => 'Laporan',
            'badge_reports' => 'Laporan',
            'submenu_report_charts' => 'Diagram & Analisis',
            'submenu_report_data' => 'Laporan Data',
            'submenu_report_events' => 'Laporan & Dokumentasi Kegiatan',
            'submenu_report_export' => 'Ekspor & Rekap',

            // 8. Informasi PMB
            'menu_pmb' => 'Info PMB',
            'badge_pmb' => 'UBSI',
            'submenu_pmb_portal' => 'Portal',
            'submenu_ebrochures' => 'E-Brosur',
            'submenu_pmb_web' => 'Website',
            'submenu_pmb_campus' => 'Biaya Kuliah',
            'submenu_pmb_schedule' => 'Jadwal',
            'submenu_pmb_procedure' => 'Alur Daftar',
            'submenu_pmb_scholarship' => 'Beasiswa',

            // 8. Sistem & Master
            'menu_master' => 'Master & Sistem',
            'submenu_master_data' => 'Wilayah',
            'submenu_users' => 'User',
            'submenu_qr_codes' => 'QR Code',
            'submenu_whatsapp' => 'WhatsApp',
            'submenu_settings' => 'Pengaturan',
            'submenu_activity_logs' => 'Log Aktivitas',
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

        $dir = dirname(static::getFilePath());
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(static::getFilePath(), json_encode($updated, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        static::$cache = $updated;
    }
}
