<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Campus;
use App\Models\Prospect;
use App\Models\Role;
use App\Models\School;
use App\Models\StudentFollowUp;
use App\Models\StudentQuestionnaire;
use App\Models\User;
use App\Services\AppSettingService;
use App\Services\ProspectSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProspectSyncAndSidebarCustomizerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Campus $campus;
    protected AcademicPeriod $period;
    protected School $school;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::firstOrCreate(['name' => 'Admin'], ['label' => 'Administrator']);

        $this->campus = Campus::firstOrCreate(
            ['code' => 'KRW'],
            [
                'name' => 'UBSI Kampus Karawang',
                'city' => 'Karawang',
                'is_active' => true,
                'is_default' => true,
            ]
        );

        $this->period = AcademicPeriod::firstOrCreate(
            ['name' => 'T.A. 2026/2027'],
            [
                'academic_year' => '2026/2027',
                'semester' => 'Ganjil',
                'is_active' => true,
                'is_current' => true,
                'start_date' => '2026-09-01',
                'end_date' => '2027-08-31',
            ]
        );

        $this->school = School::firstOrCreate(
            ['npsn' => '20217788'],
            [
                'name' => 'SMKN 1 Karawang',
                'campus_id' => $this->campus->id,
                'status' => 'Negeri',
                'latitude' => -6.3000,
                'longitude' => 107.3000,
            ]
        );

        $this->admin = User::factory()->create([
            'role_id' => $roleAdmin->id,
            'campus_id' => $this->campus->id,
        ]);

        AppSettingService::clearCache();
    }

    public function test_student_daftar_aplikasi_automatically_syncs_to_prospect_with_direct_relation(): void
    {
        $student = StudentQuestionnaire::create([
            'student_name' => 'Muhammmad Ilyas Maulana',
            'student_phone' => '082117489514',
            'student_email' => 'ilyas@gmail.com',
            'school_id' => $this->school->id,
            'campus_id' => $this->campus->id,
            'academic_period_id' => $this->period->id,
            'scholarship_interest' => 'ya',
            'scholarship_notes' => 'Beasiswa KIP-Kuliah',
            'college_interest_notes' => 'S1 Sistem Informasi',
            'followup_status' => StudentQuestionnaire::STATUS_BELUM_DIHUBUNGI,
        ]);

        // Simpan Follow Up dengan status daftar_aplikasi
        $response = $this->actingAs($this->admin)->post(route('student-follow-ups.store', $student), [
            'followup_date' => now()->toDateString(),
            'contact_method' => 'wa',
            'status' => 'daftar_aplikasi',
            'notes' => 'Siswa sudah berhasil mendaftar aplikasi PMB UBSI.',
            'college_interest_notes' => 'S1 Sistem Informasi',
            'scholarship_interest_notes' => 'Beasiswa KIP-Kuliah',
        ]);

        $response->assertSessionHasNoErrors();

        // Verifikasi student_questionnaires telah update
        $freshStudent = $student->fresh();
        $this->assertEquals(StudentQuestionnaire::STATUS_DAFTAR_APLIKASI, $freshStudent->followup_status);

        // Verifikasi prospect otomatis dibuat dan terhubung 1-to-1 via student_questionnaire_id
        $prospect = Prospect::where('student_questionnaire_id', $student->id)->first();
        $this->assertNotNull($prospect, 'Prospect harus otomatis dibuat saat siswa berstatus daftar_aplikasi');
        $this->assertEquals('Muhammmad Ilyas Maulana', $prospect->name);
        $this->assertEquals('082117489514', $prospect->phone);
        $this->assertEquals(Prospect::STATUS_DAFTAR_AKUN, $prospect->status);
        $this->assertEquals($this->campus->id, $prospect->campus_id);
        $this->assertEquals($this->period->id, $prospect->academic_period_id);

        // Verifikasi relasi Eloquent
        $this->assertEquals($prospect->id, $freshStudent->prospect->id);
        $this->assertEquals($freshStudent->id, $prospect->studentQuestionnaire->id);
    }

    public function test_prospect_index_auto_syncs_existing_unsynced_students(): void
    {
        // Buat student yang berstatus daftar_aplikasi tanpa melalui event Eloquent normal (simulasi data lama/unlinked)
        DB::table('student_questionnaires')->insert([
            'student_name' => 'Anisa Novitasari',
            'student_phone' => '0895406359160',
            'student_email' => 'anisa@gmail.com',
            'school_id' => $this->school->id,
            'campus_id' => $this->campus->id,
            'academic_period_id' => $this->period->id,
            'scholarship_interest' => 'ya',
            'followup_status' => 'daftar_aplikasi',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Verifikasi awal prospect belum ada
        $this->assertDatabaseMissing('prospects', [
            'name' => 'Anisa Novitasari',
        ]);

        // Saat membuka halaman prospek, sistem secara otomatis melakukan auto-sync
        $response = $this->actingAs($this->admin)->get(route('prospects.index'));
        $response->assertOk();
        $response->assertSee('Anisa Novitasari');

        // Verifikasi bahwa prospect sekarang sudah dibuat di database
        $this->assertDatabaseHas('prospects', [
            'name' => 'Anisa Novitasari',
            'phone' => '0895406359160',
            'status' => Prospect::STATUS_DAFTAR_AKUN,
        ]);
    }

    public function test_prospect_filtering_supports_daftar_and_daftar_akun_aliases(): void
    {
        Prospect::create([
            'name' => 'Calon Siswa Test Alias',
            'phone' => '081234567890',
            'campus_id' => $this->campus->id,
            'academic_period_id' => $this->period->id,
            'status' => Prospect::STATUS_DAFTAR_AKUN,
        ]);

        // 1. Filter dengan status 'daftar' (alias dari tombol follow-up lama)
        $response1 = $this->actingAs($this->admin)->get(route('prospects.index', ['status' => 'daftar']));
        $response1->assertOk();
        $response1->assertSee('Calon Siswa Test Alias');

        // 2. Filter dengan status 'daftar_aplikasi'
        $response2 = $this->actingAs($this->admin)->get(route('prospects.index', ['status' => 'daftar_aplikasi']));
        $response2->assertOk();
        $response2->assertSee('Calon Siswa Test Alias');

        // 3. Filter dengan status resmi 'daftar_akun'
        $response3 = $this->actingAs($this->admin)->get(route('prospects.index', ['status' => 'daftar_akun']));
        $response3->assertOk();
        $response3->assertSee('Calon Siswa Test Alias');
    }

    public function test_dedicated_sidebar_labels_update_route_saves_to_database_and_json(): void
    {
        $payload = [
            'sidebar_labels' => [
                'menu_dashboard' => 'Beranda Utama',
                'menu_gis' => 'Pemetaan Sekolah',
                'badge_gis' => 'GIS & PETA',
                'menu_kol_recommendations' => 'Talent Influencer',
                'badge_kol' => 'KOL TOP',
                'submenu_kol_candidates' => 'Daftar Calon Duta',
                'submenu_kol_documentation' => 'Galeri Konten Karya',
                'menu_master_data' => 'Master Akademik',
                'submenu_campuses' => 'Kampus UBSI',
            ],
        ];

        // 1. Simpan via route dedicated AJAX / POST
        $response = $this->actingAs($this->admin)
            ->postJson(route('settings.sidebar-labels.update'), $payload);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        // Verifikasi tersimpan di AppSettingService dan Database
        AppSettingService::clearCache();
        $this->assertEquals('Beranda Utama', AppSettingService::getSidebarLabel('menu_dashboard'));
        $this->assertEquals('Pemetaan Sekolah', AppSettingService::getSidebarLabel('menu_gis'));
        $this->assertEquals('GIS & PETA', AppSettingService::getSidebarLabel('badge_gis'));
        $this->assertEquals('Talent Influencer', AppSettingService::getSidebarLabel('menu_kol_recommendations'));
        $this->assertEquals('KOL TOP', AppSettingService::getSidebarLabel('badge_kol'));
        $this->assertEquals('Daftar Calon Duta', AppSettingService::getSidebarLabel('submenu_kol_candidates'));
        $this->assertEquals('Galeri Konten Karya', AppSettingService::getSidebarLabel('submenu_kol_documentation'));

        // Verifikasi database table app_settings menyimpan perubahan
        $this->assertDatabaseHas('app_settings', [
            'key' => 'sidebar_labels',
        ]);

        // Verifikasi halaman dashboard menampilkan label baru di sidebar
        $dashResponse = $this->actingAs($this->admin)->get(route('dashboard'));
        $dashResponse->assertOk();
        $dashResponse->assertSee('Beranda Utama');
        $dashResponse->assertSee('Pemetaan Sekolah');
        $dashResponse->assertSee('GIS & PETA');
        $dashResponse->assertSee('Talent Influencer');
        $dashResponse->assertSee('KOL TOP');
    }

    public function test_two_distinct_students_sharing_phone_and_email_sync_as_two_distinct_prospects(): void
    {
        $studentA = StudentQuestionnaire::create([
            'student_name' => 'WL Ali',
            'student_phone' => '+6285695662714',
            'student_email' => 'walimsaja@gmail.com',
            'school_id' => $this->school->id,
            'campus_id' => $this->campus->id,
            'academic_period_id' => $this->period->id,
            'scholarship_interest' => 'ya',
            'followup_status' => StudentQuestionnaire::STATUS_DAFTAR_APLIKASI,
        ]);

        $studentB = StudentQuestionnaire::create([
            'student_name' => 'Tania',
            'student_phone' => '+6285695662714',
            'student_email' => 'walimsaja@gmail.com',
            'school_id' => $this->school->id,
            'campus_id' => $this->campus->id,
            'academic_period_id' => $this->period->id,
            'scholarship_interest' => 'ya',
            'followup_status' => StudentQuestionnaire::STATUS_DAFTAR_APLIKASI,
        ]);

        ProspectSyncService::syncMissingDaftarAplikasi();

        $this->assertEquals(2, Prospect::whereIn('student_questionnaire_id', [$studentA->id, $studentB->id])->count());
        $this->assertDatabaseHas('prospects', [
            'student_questionnaire_id' => $studentA->id,
            'name' => 'WL Ali',
        ]);
        $this->assertDatabaseHas('prospects', [
            'student_questionnaire_id' => $studentB->id,
            'name' => 'Tania',
        ]);
    }
}
