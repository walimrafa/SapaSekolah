<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Campus;
use App\Models\Role;
use App\Models\School;
use App\Models\StudentQuestionnaire;
use App\Models\User;
use App\Services\AppSettingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ReportAndSettingsAlignmentTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;
    protected Campus $campus;
    protected AcademicPeriod $period;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'Admin']);

        $this->campus = Campus::firstOrCreate(
            ['code' => 'KRW'],
            ['name' => 'UBSI Kampus Karawang', 'is_active' => true, 'is_default' => true]
        );

        $this->period = AcademicPeriod::firstOrCreate(
            ['name' => '2026/2027'],
            ['start_date' => '2026-09-01', 'end_date' => '2027-08-31', 'is_current' => true]
        );

        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
            'campus_id' => $this->campus->id,
        ]);
    }

    public function test_settings_page_renders_all_sidebar_navigation_tabs_and_inputs(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get(route('settings.index'));
        $response->assertOk();

        // 10 Tabs navigation
        $response->assertSee("activeTab = 'gis'", false);
        $response->assertSee("activeTab = 'field'", false);
        $response->assertSee("activeTab = 'crm'", false);
        $response->assertSee("activeTab = 'kol'", false);
        $response->assertSee("activeTab = 'events'", false);
        $response->assertSee("activeTab = 'reports'", false);
        $response->assertSee("activeTab = 'pmb'", false);
        $response->assertSee("activeTab = 'master_data'", false);
        $response->assertSee("activeTab = 'portal'", false);
        $response->assertSee("activeTab = 'system'", false);

        // Sidebar input names
        $response->assertSee('sidebar_labels[menu_kol_recommendations]');
        $response->assertSee('sidebar_labels[submenu_kol_candidates]');
        $response->assertSee('sidebar_labels[submenu_kol_documentation]');
        $response->assertSee('sidebar_labels[menu_master_data]');
        $response->assertSee('sidebar_labels[submenu_campuses]');
        $response->assertSee('sidebar_labels[submenu_periods]');
        $response->assertSee('sidebar_labels[menu_portals_questionnaires]');
        $response->assertSee('sidebar_labels[submenu_portals]');
        $response->assertSee('sidebar_labels[menu_system_ops]');
        $response->assertSee('sidebar_labels[submenu_database_backups]');
    }

    public function test_settings_can_update_custom_sidebar_labels(): void
    {
        $this->actingAs($this->adminUser);

        $payload = [
            'app_name' => 'SAPA SEKOLAH',
            'app_subtitle' => 'Digital Marketing',
            'campus_name' => 'Universitas BSI Karawang',
            'campus_address' => 'Jl. BSI Karawang No. 1',
            'campus_latitude' => -6.3000,
            'campus_longitude' => 107.3000,
            'header_title' => 'SAPA SEKOLAH COMMAND CENTER',
            'footer_text' => 'SAPA Sekolah &copy; 2026',
            'footer_subtext' => 'UBSI Kampus Karawang',
            'sidebar_labels' => [
                'menu_kol_recommendations' => 'Talent KOL',
                'badge_kol' => 'INFLUENCER',
                'submenu_kol_candidates' => 'Calon Duta',
                'submenu_kol_documentation' => 'Galeri Karya',
                'menu_master_data' => 'Data Induk Kampus',
                'submenu_database_backups' => 'Arsip Cadangan Cloud',
            ],
        ];

        $response = $this->from(route('settings.index'))->post(route('settings.update'), $payload);
        $response->assertRedirect(route('settings.index'));

        $this->assertEquals('Talent KOL', AppSettingService::getSidebarLabel('menu_kol_recommendations'));
        $this->assertEquals('INFLUENCER', AppSettingService::getSidebarLabel('badge_kol'));
        $this->assertEquals('Calon Duta', AppSettingService::getSidebarLabel('submenu_kol_candidates'));
        $this->assertEquals('Galeri Karya', AppSettingService::getSidebarLabel('submenu_kol_documentation'));
        $this->assertEquals('Data Induk Kampus', AppSettingService::getSidebarLabel('menu_master_data'));
        $this->assertEquals('Arsip Cadangan Cloud', AppSettingService::getSidebarLabel('submenu_database_backups'));
    }

    public function test_reports_tabular_kol_tab_loads_successfully(): void
    {
        $this->actingAs($this->adminUser);

        $school = School::create([
            'npsn' => '69882211',
            'name' => 'SMA Bintang Bangsa',
            'campus_id' => $this->campus->id,
        ]);

        StudentQuestionnaire::create([
            'campus_id' => $this->campus->id,
            'academic_period_id' => $this->period->id,
            'school_id' => $school->id,
            'student_name' => 'Influencer Siswa Hebat',
            'student_phone' => '081299887766',
            'student_email' => 'influencer@bintang.sch.id',
            'instagram_handle' => 'influencer_siswa',
            'instagram_followers' => 4500,
            'kol_verification_status' => 'verified',
            'is_kol' => true,
            'kol_status' => 'brand_ambassador',
            'work_links' => [
                'instagram' => 'https://instagram.com/p/reel123',
                'notes' => 'Post video pengenalan beasiswa UBSI',
            ],
            'estimated_rate_card' => [
                'tier' => 'Nano Influencer',
                'package' => 'Rp 150.000',
            ],
        ]);

        $response = $this->get(route('reports.index', ['tab' => 'kol']));
        $response->assertOk();
        $response->assertSee('LAPORAN DETAIL REKOMENDASI KOL &amp; BRAND AMBASSADOR SISWA', false);
        $response->assertSee('Influencer Siswa Hebat');
        $response->assertSee('@influencer_siswa');
        $response->assertSee('✓ Real Instagram');
        $response->assertSee('Brand Ambassador');
        $response->assertSee('IG Post');
    }

    public function test_reports_diagram_category_includes_kol_analytics(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get(route('reports.index', ['category' => 'diagram']));
        $response->assertOk();
        $response->assertSee('Analisis Calon Rekomendasi KOL');
        $response->assertSee('chartKolStatus');
        $response->assertSee('chartKolVerification');
    }

    public function test_report_export_service_exports_kol_tab_xlsx(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get(route('reports.export', ['tab' => 'kol']));
        $response->assertOk();
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
        $this->assertStringContainsString('Laporan_Rekomendasi_KOL_', $response->headers->get('content-disposition'));
    }

    public function test_comprehensive_reports_downloads_kol_and_all_sheets(): void
    {
        $this->actingAs($this->adminUser);

        // Single Category: KOL
        $kolResponse = $this->get(route('comprehensive-reports.download', ['category' => 'kol']));
        $kolResponse->assertOk();
        $this->assertStringContainsString('kol', $kolResponse->headers->get('content-disposition'));

        // All Categories: Multi-Sheet including KOL
        $allResponse = $this->get(route('comprehensive-reports.download', ['category' => 'all']));
        $allResponse->assertOk();
        $this->assertStringContainsString('all', $allResponse->headers->get('content-disposition'));
    }
}
