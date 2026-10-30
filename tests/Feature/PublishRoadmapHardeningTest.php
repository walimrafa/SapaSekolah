<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\ActivityLog;
use App\Models\Campus;
use App\Models\Prospect;
use App\Models\Role;
use App\Models\School;
use App\Models\StudentQuestionnaire;
use App\Models\User;
use App\Services\AppContextService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PublishRoadmapHardeningTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_public_portal_toggle_and_disabled_guard(): void
    {
        // 1. Nonaktifkan portal input kunjungan
        AppContextService::setPortalStatus(AppContextService::PORTAL_PUBLIC_VISIT, false);

        $response = $this->get(route('public-visit.index'));
        $response->assertOk();
        $response->assertSee('Portal Input Kunjungan Lapangan');
        $response->assertSee('Status: Portal Nonaktif');

        // POST kunjungan ditolak saat nonaktif
        $postResponse = $this->post(route('public-visit.store'), [
            'school_id' => 1,
            'user_id' => 1,
        ]);
        $postResponse->assertSessionHas('error');

        // 2. Aktifkan kembali
        AppContextService::setPortalStatus(AppContextService::PORTAL_PUBLIC_VISIT, true);
        $activeResponse = $this->get(route('public-visit.index'));
        $activeResponse->assertOk();
        $activeResponse->assertDontSee('Status: Portal Nonaktif');
    }

    public function test_portal_toggle_authorization(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Admin'], ['description' => 'Admin']);
        $marketingRole = Role::firstOrCreate(['name' => 'Marketing'], ['description' => 'Marketing']);

        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $marketing = User::factory()->create(['role_id' => $marketingRole->id]);

        // Admin can toggle
        $this->actingAs($admin)
            ->post(route('portal-status.toggle'), [
                'portal_key' => 'student_questionnaire',
                'is_active' => 0,
            ])
            ->assertRedirect();

        $this->assertFalse(AppContextService::isPortalActive('student_questionnaire'));

        // Restore
        $this->actingAs($admin)
            ->post(route('portal-status.toggle'), [
                'portal_key' => 'student_questionnaire',
                'is_active' => 1,
            ]);

        $this->assertTrue(AppContextService::isPortalActive('student_questionnaire'));

        // Regular marketing user is denied (403)
        $this->actingAs($marketing)
            ->post(route('portal-status.toggle'), [
                'portal_key' => 'student_questionnaire',
                'is_active' => 0,
            ])
            ->assertForbidden();
    }

    public function test_student_followup_officer_assignment_isolation(): void
    {
        $csRole = Role::firstOrCreate(['name' => 'Customer Service'], ['description' => 'Customer Service']);
        $adminRole = Role::firstOrCreate(['name' => 'Admin'], ['description' => 'Admin']);

        $officerA = User::factory()->create(['role_id' => $csRole->id, 'name' => 'Petugas A']);
        $officerB = User::factory()->create(['role_id' => $csRole->id, 'name' => 'Petugas B']);
        $admin = User::factory()->create(['role_id' => $adminRole->id, 'name' => 'Administrator']);

        $defaultCampus = Campus::getDefault();
        $currentPeriod = AcademicPeriod::getCurrent();

        $school = School::create([
            'npsn' => 'TEST' . strtoupper(bin2hex(random_bytes(4))),
            'name' => 'SMK Uji Isolasi Follow Up',
            'campus_id' => $defaultCampus?->id,
        ]);

        $studentA = StudentQuestionnaire::create([
            'school_id' => $school->id,
            'campus_id' => $defaultCampus?->id,
            'academic_period_id' => $currentPeriod?->id,
            'student_name' => 'Siswa Khusus Petugas A',
            'student_class' => 'XII TKJ 1',
            'student_major' => 'TKJ',
            'student_phone' => '08111111111',
            'student_email' => 'siswaA@test.com',
            'guardian_name' => 'Wali Siswa A',
            'guardian_phone' => '08111111112',
            'motivation_job' => 5,
            'motivation_interest' => 5,
            'motivation_parent' => 5,
            'information_social_media' => 5,
            'information_testimonials' => 5,
            'information_comparison' => 5,
            'social_parent' => 5,
            'social_friends' => 5,
            'social_teacher' => 5,
            'campus_cost' => 5,
            'campus_reputation' => 5,
            'campus_career' => 5,
            'campus_location' => 5,
            'intention_ubsi' => 5,
            'intention_information' => 5,
            'barrier_cost' => 5,
            'assigned_to' => $officerA->id,
        ]);

        $studentB = StudentQuestionnaire::create([
            'school_id' => $school->id,
            'campus_id' => $defaultCampus?->id,
            'academic_period_id' => $currentPeriod?->id,
            'student_name' => 'Siswa Khusus Petugas B',
            'student_class' => 'XII RPL 1',
            'student_major' => 'RPL',
            'student_phone' => '08222222221',
            'student_email' => 'siswaB@test.com',
            'guardian_name' => 'Wali Siswa B',
            'guardian_phone' => '08222222222',
            'motivation_job' => 5,
            'motivation_interest' => 5,
            'motivation_parent' => 5,
            'information_social_media' => 5,
            'information_testimonials' => 5,
            'information_comparison' => 5,
            'social_parent' => 5,
            'social_friends' => 5,
            'social_teacher' => 5,
            'campus_cost' => 5,
            'campus_reputation' => 5,
            'campus_career' => 5,
            'campus_location' => 5,
            'intention_ubsi' => 5,
            'intention_information' => 5,
            'barrier_cost' => 5,
            'assigned_to' => $officerB->id,
        ]);

        // Officer A should ONLY see Siswa Khusus Petugas A
        $responseA = $this->actingAs($officerA)->get(route('student-follow-ups.index'));
        $responseA->assertOk();
        $responseA->assertSee('Siswa Khusus Petugas A');
        $responseA->assertDontSee('Siswa Khusus Petugas B');

        // Officer B should ONLY see Siswa Khusus Petugas B
        $responseB = $this->actingAs($officerB)->get(route('student-follow-ups.index'));
        $responseB->assertOk();
        $responseB->assertSee('Siswa Khusus Petugas B');
        $responseB->assertDontSee('Siswa Khusus Petugas A');

        // Admin should see both students
        $responseAdmin = $this->actingAs($admin)->get(route('student-follow-ups.index'));
        $responseAdmin->assertOk();
        $responseAdmin->assertSee('Siswa Khusus Petugas A');
        $responseAdmin->assertSee('Siswa Khusus Petugas B');
    }

    public function test_context_switching_and_activity_logging(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Admin'], ['description' => 'Admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $campus = Campus::firstOrCreate(
            ['code' => 'TKRW'],
            ['name' => 'Kampus Uji Hardening', 'city' => 'Karawang', 'is_active' => true]
        );

        $period = AcademicPeriod::firstOrCreate(
            ['name' => '2099/2100'],
            ['is_active' => true, 'is_current' => false]
        );

        $response = $this->actingAs($admin)
            ->post(route('context.switch'), [
                'campus_id' => $campus->id,
                'period_id' => $period->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('selected_campus_id', $campus->id);
        $response->assertSessionHas('selected_period_id', $period->id);

        // Assert activity log was created
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => 'SWITCH_CONTEXT',
        ]);
    }

    public function test_comprehensive_reports_hub_accessible(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Admin'], ['description' => 'Admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $this->actingAs($admin)
            ->get(route('comprehensive-reports.index'))
            ->assertOk()
            ->assertSee('Pusat Laporan & Data Export Master', false)
            ->assertSee('Unduh Laporan Excel Sesuai Filter');
    }

    public function test_user_management_campus_isolation(): void
    {
        $pimpinanRole = Role::firstOrCreate(['name' => 'Pimpinan'], ['description' => 'Pimpinan Kampus']);
        $marketingRole = Role::firstOrCreate(['name' => 'Marketing'], ['description' => 'Marketing']);
        $adminRole = Role::firstOrCreate(['name' => 'Admin'], ['description' => 'Admin']);

        $campusA = Campus::firstOrCreate(
            ['code' => 'TEST_KA'],
            ['name' => 'Kampus A Testing', 'city' => 'Kota A', 'is_active' => true]
        );
        $campusB = Campus::firstOrCreate(
            ['code' => 'TEST_KB'],
            ['name' => 'Kampus B Testing', 'city' => 'Kota B', 'is_active' => true]
        );

        $pimpinanA = User::factory()->create([
            'role_id' => $pimpinanRole->id,
            'campus_id' => $campusA->id,
            'name' => 'Pimpinan Kampus A',
            'email' => 'pimpinanA@test.com',
        ]);

        $staffA = User::factory()->create([
            'role_id' => $marketingRole->id,
            'campus_id' => $campusA->id,
            'name' => 'Staff Kampus A',
            'email' => 'staffA@test.com',
        ]);

        $staffB = User::factory()->create([
            'role_id' => $marketingRole->id,
            'campus_id' => $campusB->id,
            'name' => 'Staff Kampus B',
            'email' => 'staffB@test.com',
        ]);

        // 1. Pimpinan Kampus A can see Staff Kampus A but CANNOT see Staff Kampus B
        $responseA = $this->actingAs($pimpinanA)->get(route('users.index'));
        $responseA->assertOk();
        $responseA->assertSee('Staff Kampus A');
        $responseA->assertDontSee('Staff Kampus B');

        // 2. Pimpinan Kampus A cannot create an Admin account
        $storeAdminResponse = $this->actingAs($pimpinanA)->post(route('users.store'), [
            'name' => 'Attempt Admin',
            'email' => 'attempt_admin@test.com',
            'password' => 'secret123',
            'role_id' => $adminRole->id,
            'status' => 'active',
        ]);
        $storeAdminResponse->assertSessionHas('toast');
        $this->assertDatabaseMissing('users', ['email' => 'attempt_admin@test.com']);

        // 3. Pimpinan Kampus A cannot edit Staff Kampus B (403 Forbidden)
        $this->actingAs($pimpinanA)
            ->put(route('users.update', $staffB->id), [
                'name' => 'Tampered Staff B',
                'email' => $staffB->email,
                'role_id' => $marketingRole->id,
                'status' => 'active',
            ])
            ->assertForbidden();

        // 4. Pimpinan Kampus A cannot delete Staff Kampus B (403 Forbidden)
        $this->actingAs($pimpinanA)
            ->delete(route('users.destroy', $staffB->id))
            ->assertForbidden();
    }

    public function test_school_and_master_campus_strict_isolation(): void
    {
        $pimpinanRole = Role::firstOrCreate(['name' => 'Pimpinan'], ['description' => 'Pimpinan Kampus']);
        $adminRole = Role::firstOrCreate(['name' => 'Admin'], ['description' => 'Admin']);

        $campus1 = Campus::firstOrCreate(
            ['code' => 'TIS_1'],
            ['name' => 'Kampus Isolasi 1', 'city' => 'Kota 1', 'is_active' => true]
        );
        $campus2 = Campus::firstOrCreate(
            ['code' => 'TIS_2'],
            ['name' => 'Kampus Isolasi 2', 'city' => 'Kota 2', 'is_active' => true]
        );

        $pimpinan1 = User::factory()->create([
            'role_id' => $pimpinanRole->id,
            'campus_id' => $campus1->id,
            'name' => 'Pimpinan Kampus 1',
        ]);

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'name' => 'Super Administrator',
        ]);

        $school1 = School::create([
            'npsn' => 'ISO1' . bin2hex(random_bytes(3)),
            'name' => 'SMA Kampus 1 Saja',
            'campus_id' => $campus1->id,
        ]);

        $school2 = School::create([
            'npsn' => 'ISO2' . bin2hex(random_bytes(3)),
            'name' => 'SMA Kampus 2 Saja',
            'campus_id' => $campus2->id,
        ]);

        // Pimpinan 1 can see SMA Kampus 1 Saja, but NOT SMA Kampus 2 Saja
        $schoolResponse = $this->actingAs($pimpinan1)->get(route('schools.index'));
        $schoolResponse->assertOk();
        $schoolResponse->assertSee('SMA Kampus 1 Saja');
        $schoolResponse->assertDontSee('SMA Kampus 2 Saja');

        // Pimpinan 1 cannot add new campus in Master Data (403 Forbidden)
        $this->actingAs($pimpinan1)
            ->post(route('master-data.campuses.store'), [
                'code' => 'ILLEGAL',
                'name' => 'Illegal Campus',
            ])
            ->assertForbidden();

        // Pimpinan 1 cannot delete campus (403 Forbidden)
        $this->actingAs($pimpinan1)
            ->delete(route('master-data.campuses.destroy', $campus2->id))
            ->assertForbidden();
    }

    public function test_priorities_page_strictly_isolates_recommendations_and_leaderboard_per_campus(): void
    {
        $pimpinanRole = Role::firstOrCreate(['name' => 'Pimpinan'], ['description' => 'Pimpinan Kampus']);

        $campusKRW = Campus::firstOrCreate(
            ['code' => 'TKRW_P'],
            ['name' => 'UBSI Kampus Karawang Test', 'city' => 'Karawang', 'is_active' => true]
        );
        $campusCKP = Campus::firstOrCreate(
            ['code' => 'TCKP_P'],
            ['name' => 'UBSI Kampus Cikampek Test', 'city' => 'Cikampek', 'is_active' => true]
        );

        $pimpinanKRW = User::factory()->create([
            'role_id' => $pimpinanRole->id,
            'campus_id' => $campusKRW->id,
            'name' => 'Pimpinan Karawang',
        ]);

        $schoolKRW = School::create([
            'npsn' => 'PKRW' . bin2hex(random_bytes(3)),
            'name' => 'SMAN 1 KARAWANG PRIORITAS',
            'campus_id' => $campusKRW->id,
            'priority_level' => 'A',
            'potential_score' => 90.0,
            'latitude' => -6.3050,
            'longitude' => 107.2990,
            'status' => 'negeri',
            'grade_12_count' => 300,
        ]);

        $schoolCKP = School::create([
            'npsn' => 'PCKP' . bin2hex(random_bytes(3)),
            'name' => 'SMKN 1 CIKAMPEK PRIORITAS',
            'campus_id' => $campusCKP->id,
            'priority_level' => 'A',
            'potential_score' => 88.0,
            'latitude' => -6.4080,
            'longitude' => 107.4580,
            'status' => 'negeri',
            'grade_12_count' => 280,
        ]);

        // When Pimpinan Karawang opens /priorities:
        $response = $this->actingAs($pimpinanKRW)->get(route('priorities.index'));
        $response->assertOk();
        $response->assertSee('SMAN 1 KARAWANG PRIORITAS');
        $response->assertDontSee('SMKN 1 CIKAMPEK PRIORITAS');
    }

    public function test_navbar_campus_dropdown_visibility_restricted_to_admin_and_koordinator_marketing(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Admin'], ['description' => 'Admin']);
        $koordinatorRole = Role::firstOrCreate(['name' => 'Koordinator Marketing'], ['description' => 'Koordinator Marketing']);
        $pimpinanRole = Role::firstOrCreate(['name' => 'Pimpinan'], ['description' => 'Pimpinan Kampus']);
        $marketingRole = Role::firstOrCreate(['name' => 'Marketing'], ['description' => 'Marketing']);

        $campus = Campus::first();

        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $koordinator = User::factory()->create(['role_id' => $koordinatorRole->id]);
        $pimpinan = User::factory()->create(['role_id' => $pimpinanRole->id, 'campus_id' => $campus->id]);
        $marketing = User::factory()->create(['role_id' => $marketingRole->id, 'campus_id' => $campus->id]);

        $this->assertTrue(AppContextService::canSwitchCampus($admin));
        $this->assertTrue(AppContextService::canSwitchCampus($koordinator));
        $this->assertFalse(AppContextService::canSwitchCampus($pimpinan));
        $this->assertFalse(AppContextService::canSwitchCampus($marketing));

        // Pimpinan sees static pill, NOT dropdown with chevron-down
        $pimpinanResponse = $this->actingAs($pimpinan)->get(route('priorities.index'));
        $pimpinanResponse->assertOk();
        $pimpinanResponse->assertDontSee('Pilih Filter Kampus');

        // Admin sees dropdown
        $adminResponse = $this->actingAs($admin)->get(route('priorities.index'));
        $adminResponse->assertOk();
        $adminResponse->assertSee('Pilih Filter Kampus');
    }

    public function test_koordinator_marketing_can_delete_and_restore_school(): void
    {
        $koordinatorRole = Role::firstOrCreate(['name' => 'Koordinator Marketing'], ['description' => 'Koordinator Marketing']);
        $koordinator = User::factory()->create(['role_id' => $koordinatorRole->id]);

        $school = School::create([
            'npsn' => 'DEL_' . bin2hex(random_bytes(3)),
            'name' => 'SMK Uji Hapus Koordinator',
        ]);

        // Koordinator can delete school (no 403)
        $deleteResponse = $this->actingAs($koordinator)->delete(route('schools.destroy', $school->id));
        $deleteResponse->assertRedirect(route('schools.index'));
        $this->assertSoftDeleted('schools', ['id' => $school->id]);

        // Koordinator can restore school (no 403)
        $restoreResponse = $this->actingAs($koordinator)->post(route('schools.restore', $school->id));
        $restoreResponse->assertRedirect(route('schools.index'));
        $this->assertNotSoftDeleted('schools', ['id' => $school->id]);
    }

    public function test_destroy_all_schools_is_isolated_to_active_campus(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Admin'], ['description' => 'Admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $campusX = Campus::firstOrCreate(
            ['code' => 'TEST_CX'],
            ['name' => 'Kampus Hapus Massal X', 'city' => 'Kota X', 'is_active' => true]
        );
        $campusY = Campus::firstOrCreate(
            ['code' => 'TEST_CY'],
            ['name' => 'Kampus Hapus Massal Y', 'city' => 'Kota Y', 'is_active' => true]
        );

        $schoolX = School::create([
            'npsn' => 'SCHX_' . bin2hex(random_bytes(3)),
            'name' => 'SMA Kampus X Saja',
            'campus_id' => $campusX->id,
        ]);

        $schoolY = School::create([
            'npsn' => 'SCHY_' . bin2hex(random_bytes(3)),
            'name' => 'SMA Kampus Y Saja',
            'campus_id' => $campusY->id,
        ]);

        // 1. Set active campus to Campus X
        $this->actingAs($admin)
            ->withSession(['selected_campus_id' => $campusX->id])
            ->delete(route('schools.destroy-all'), ['force_delete' => 0]);

        // School X should be soft-deleted, but School Y must NOT be touched
        $this->assertSoftDeleted('schools', ['id' => $schoolX->id]);
        $this->assertNotSoftDeleted('schools', ['id' => $schoolY->id]);

        // 2. Restore all for Campus X
        $this->actingAs($admin)
            ->withSession(['selected_campus_id' => $campusX->id])
            ->post(route('schools.restore-all'));

        $this->assertNotSoftDeleted('schools', ['id' => $schoolX->id]);
        $this->assertNotSoftDeleted('schools', ['id' => $schoolY->id]);
    }

    public function test_marketing_activity_google_drive_integration_and_document_route(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Admin'], ['description' => 'Admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        \Illuminate\Support\Facades\Storage::fake('public');

        $fakeFile = \Illuminate\Http\UploadedFile::fake()->create('dokumentasi_expo.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($admin)->post(route('activities.store'), [
            'name' => 'Education Expo Jawa Barat 2026',
            'activity_type' => 'Expo',
            'start_date' => '2026-10-30',
            'description' => 'Uji coba upload dokumen aktivitas marketing ke Google Drive',
            'document' => $fakeFile,
        ]);

        $response->assertRedirect(route('activities.index'));

        $activity = \App\Models\MarketingActivity::where('name', 'Education Expo Jawa Barat 2026')->first();
        $this->assertNotNull($activity);
        $this->assertNotNull($activity->document_path);
        $this->assertNotEmpty($activity->drive_folder_url);
        $this->assertStringContainsString('drive.google.com', $activity->drive_folder_url);

        // Test route activities.document directs to Google Drive or serves file
        $docResponse = $this->actingAs($admin)->get(route('activities.document', $activity->id));
        $this->assertTrue($docResponse->isRedirection() || $docResponse->isOk());

        // Test view contains Google Drive Cloud button and Folder Drive
        $viewResponse = $this->actingAs($admin)->get(route('activities.index'));
        $viewResponse->assertOk();
        $viewResponse->assertSee('Google Drive Cloud');
        $viewResponse->assertSee('Folder Drive');
        $viewResponse->assertSee('Dokumentasi &amp; Drive', false);
    }

    public function test_followup_sync_to_marketing_activity_carries_google_drive_links(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Admin'], ['description' => 'Admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $defaultCampus = Campus::first();
        $school = School::create([
            'npsn' => 'SMK_' . bin2hex(random_bytes(3)),
            'name' => 'SMKN 1 Sync Test Drive',
            'campus_id' => $defaultCampus?->id,
            'grade_12_count' => 150,
        ]);

        $followUp = \App\Models\FollowUp::create([
            'user_id' => $admin->id,
            'school_id' => $school->id,
            'academic_period_id' => \App\Models\AcademicPeriod::first()?->id,
            'campus_id' => $defaultCampus?->id,
            'followup_date' => '2026-10-30',
            'status' => 'sosialisasi_fix',
            'fixed_date' => '2026-10-30',
            'socialization_completed_at' => '2026-10-30 10:00:00',
            'socialization_completion_notes' => 'Sosialisasi selesai dengan sukses',
            'documentation_file' => 'visit-documents/2026-10-30/smkn-1-sync/test.jpg',
            'google_drive_folder_url' => 'https://drive.google.com/drive/folders/test12345',
            'google_drive_file_url' => 'https://drive.google.com/file/d/testfile123',
        ]);

        $activity = \App\Http\Controllers\FollowUpController::syncFollowUpToMarketingActivity($followUp);
        $this->assertNotNull($activity);
        $this->assertEquals('https://drive.google.com/drive/folders/test12345', $activity->google_drive_folder_url);
        $this->assertEquals('https://drive.google.com/file/d/testfile123', $activity->google_drive_file_url);
        $this->assertEquals('https://drive.google.com/drive/folders/test12345', $activity->drive_folder_url);
    }

    public function test_student_daftar_aplikasi_automatically_syncs_to_prospect_crm(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Admin'], ['description' => 'Admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $defaultCampus = Campus::first();
        $defaultPeriod = AcademicPeriod::getCurrent() ?: AcademicPeriod::first();

        $school = School::create([
            'npsn' => 'AUTO_' . bin2hex(random_bytes(3)),
            'name' => 'SMK Uji Auto Prospek',
            'campus_id' => $defaultCampus?->id,
        ]);

        $phone = '0812' . rand(10000000, 99999999);

        // 1. Buat data siswa kuisioner
        $student = StudentQuestionnaire::create([
            'school_id' => $school->id,
            'campus_id' => $defaultCampus?->id,
            'academic_period_id' => $defaultPeriod?->id,
            'student_name' => 'Calon Maba Auto Sync',
            'student_phone' => $phone,
            'student_email' => 'calon_maba@test.com',
            'student_class' => 'XII',
            'student_major' => 'RPL',
            'scholarship_interest' => 'ya',
            'followup_status' => StudentQuestionnaire::STATUS_BERMINAT,
            'college_interest_notes' => 'S1 Sistem Informasi',
            'answers' => [1 => 5],
        ]);

        // Belum ada di prospek
        $this->assertNull(Prospect::where('phone', $phone)->first());

        // 2. Petugas follow-up mengubah status ke daftar_aplikasi
        $response = $this->actingAs($admin)->post(route('student-follow-ups.store', $student->id), [
            'status' => 'daftar_aplikasi',
            'contact_method' => 'wa',
            'followup_date' => date('Y-m-d'),
            'notes' => 'Siswa sudah download aplikasi PMB dan mengisi formulir akun',
            'college_interest_notes' => 'S1 Sistem Informasi',
            'scholarship_interest_notes' => 'Beasiswa KIP-Kuliah',
        ]);

        $response->assertRedirect();

        // 3. Verifikasi otomatis tercipta di tabel prospects
        $prospect = Prospect::where('phone', $phone)->first();
        $this->assertNotNull($prospect);
        $this->assertEquals('Calon Maba Auto Sync', $prospect->name);
        $this->assertEquals($school->id, $prospect->school_id);
        $this->assertEquals($defaultCampus?->id, $prospect->campus_id);
        $this->assertEquals($defaultPeriod?->id, $prospect->academic_period_id);
        $this->assertEquals(Prospect::STATUS_DAFTAR_AKUN, $prospect->status);

        // 4. Verifikasi muncul di halaman /prospects
        $prospectResponse = $this->actingAs($admin)
            ->withSession([
                'selected_campus_id' => $defaultCampus?->id,
                'selected_academic_period_id' => $defaultPeriod?->id,
            ])
            ->get(route('prospects.index'));

        $prospectResponse->assertOk();
        $prospectResponse->assertSee('Calon Maba Auto Sync');
    }

    public function test_public_proposal_success_uses_official_campus_phone_and_has_tracking_link(): void
    {
        $krwCampus = Campus::where('code', 'KRW')->first();
        $this->assertNotNull($krwCampus);

        $proposal = \App\Models\SchoolEventProposal::create([
            'reference_no' => 'EVT-TEST-' . time(),
            'campus_id' => $krwCampus->id,
            'school_name' => 'SMKN 1 Karawang',
            'pic_name' => 'Bapak Ahmad',
            'pic_phone' => '081299990001',
            'event_name' => 'Pentas Seni & Kreasi Siswa',
            'event_date' => date('Y-m-d', strtotime('+7 days')),
            'event_time' => '08:00 - 15:00',
            'event_location' => 'Aula Sekolah',
            'target_participants' => '500 Siswa',
            'support_type_requested' => 'Sponsorship & Stand Booth',
            'proposal_file_path' => 'event-proposals/test.pdf',
            'proposal_file_name' => 'test.pdf',
            'status' => \App\Models\SchoolEventProposal::STATUS_MENUNGGU_REVIEW,
        ]);

        $response = $this->get(route('school-events.success', $proposal->reference_no));
        $response->assertOk();

        // Must NOT use hardcoded unofficial number 6281211112222
        $response->assertDontSee('6281211112222');
        // Must see tracking link and official WA link
        $response->assertSee(route('school-events.tracking', ['ref' => $proposal->reference_no]));
        $response->assertSee('6281220022851');
    }

    public function test_public_proposal_status_tracking_page_for_schools(): void
    {
        $refNo = 'EVT-TRACK-' . time();
        $proposal = \App\Models\SchoolEventProposal::create([
            'reference_no' => $refNo,
            'campus_id' => 1,
            'school_name' => 'SMK Bina Informatika',
            'pic_name' => 'Ibu Siti',
            'pic_phone' => '085711223344',
            'event_name' => 'Seminar Karir Masa Depan',
            'event_date' => date('Y-m-d', strtotime('+10 days')),
            'event_time' => '09:00 - 12:00',
            'event_location' => 'Gedung Olahraga',
            'target_participants' => '300 Siswa',
            'support_type_requested' => 'Narasumber & Sponsorship',
            'proposal_file_path' => 'event-proposals/test.pdf',
            'proposal_file_name' => 'test.pdf',
            'status' => \App\Models\SchoolEventProposal::STATUS_DISETUJUI,
            'approved_amount' => 500000,
            'approved_support_items' => 'Speaker, Brosur & Spanduk',
        ]);

        // Access tracking page with ref parameter
        $response = $this->get(route('school-events.tracking', ['ref' => $refNo]));
        $response->assertOk();
        $response->assertSee($refNo);
        $response->assertSee('SMK Bina Informatika');
        $response->assertSee('Seminar Karir Masa Depan');
        $response->assertSee('Proposal Disetujui');
        $response->assertSee('Rp 500.000');
        $response->assertSee('Speaker, Brosur');
    }

    public function test_support_event_campus_isolation_between_karawang_and_cikampek(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Admin'], ['description' => 'Admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $krw = Campus::where('code', 'KRW')->first();
        $ckp = Campus::where('code', 'CKP')->first();

        $krwProposal = \App\Models\SchoolEventProposal::create([
            'reference_no' => 'EVT-KRW-' . time(),
            'campus_id' => $krw->id,
            'school_name' => 'Sekolah Karawang Unik',
            'pic_name' => 'Pak Karawang',
            'pic_phone' => '081200000001',
            'event_name' => 'Event Khusus Karawang',
            'event_date' => date('Y-m-d'),
            'event_time' => '08:00',
            'event_location' => 'Karawang',
            'target_participants' => '100',
            'support_type_requested' => 'Sponsorship',
            'proposal_file_path' => 'event-proposals/test.pdf',
            'proposal_file_name' => 'test.pdf',
            'status' => \App\Models\SchoolEventProposal::STATUS_MENUNGGU_REVIEW,
        ]);

        $ckpProposal = \App\Models\SchoolEventProposal::create([
            'reference_no' => 'EVT-CKP-' . time(),
            'campus_id' => $ckp->id,
            'school_name' => 'Sekolah Cikampek Unik',
            'pic_name' => 'Pak Cikampek',
            'pic_phone' => '081200000002',
            'event_name' => 'Event Khusus Cikampek',
            'event_date' => date('Y-m-d'),
            'event_time' => '08:00',
            'event_location' => 'Cikampek',
            'target_participants' => '100',
            'support_type_requested' => 'Sponsorship',
            'proposal_file_path' => 'event-proposals/test.pdf',
            'proposal_file_name' => 'test.pdf',
            'status' => \App\Models\SchoolEventProposal::STATUS_MENUNGGU_REVIEW,
        ]);

        // When viewing with Karawang active campus session
        $krwResponse = $this->actingAs($admin)
            ->withSession(['selected_campus_id' => $krw->id])
            ->get(route('event-proposals.index'));

        $krwResponse->assertOk();
        $krwResponse->assertSee('Event Khusus Karawang');
        $krwResponse->assertDontSee('Event Khusus Cikampek');

        // When viewing with Cikampek active campus session
        $ckpResponse = $this->actingAs($admin)
            ->withSession(['selected_campus_id' => $ckp->id])
            ->get(route('event-proposals.index'));

        $ckpResponse->assertOk();
        $ckpResponse->assertSee('Event Khusus Cikampek');
        $ckpResponse->assertDontSee('Event Khusus Karawang');
    }

    public function test_system_can_input_and_update_student_questionnaire_evaluation_points(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Admin'], ['description' => 'Admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $phone = '08139988' . rand(1000, 9999);

        // 1. Create from system with answers
        $response = $this->actingAs($admin)->post(route('student-questionnaires.store-system'), [
            'student_name' => 'Siswa Input Sistem',
            'school_name' => 'SMA Negeri 1 Karawang',
            'student_phone' => $phone,
            'student_email' => 'siswasistem@test.com',
            'scholarship_interest' => 'ya',
            'followup_status' => 'berminat',
            'answers' => [
                'motivation_job' => 5,
                'information_social_media' => 4,
                'campus_cost' => 5,
                'intention_ubsi' => 5,
            ],
        ]);

        $response->assertRedirect();

        $student = StudentQuestionnaire::where('student_phone', $phone)->first();
        $this->assertNotNull($student);
        $this->assertEquals('Siswa Input Sistem', $student->student_name);
        $this->assertEquals(5, $student->answers['motivation_job'] ?? null);
        $this->assertEquals(4, $student->answers['information_social_media'] ?? null);

        // 2. Update points from system
        $updateResponse = $this->actingAs($admin)->put(route('student-questionnaires.update', $student->id), [
            'student_name' => 'Siswa Input Sistem Updated',
            'student_phone' => $phone,
            'scholarship_interest' => 'ya',
            'followup_status' => 'berminat',
            'answers' => [
                'motivation_job' => 5,
                'information_social_media' => 5,
                'campus_cost' => 4,
            ],
        ]);

        $updateResponse->assertRedirect();
        $student->refresh();
        $this->assertEquals('Siswa Input Sistem Updated', $student->student_name);
        $this->assertEquals(5, $student->answers['information_social_media']);
        $this->assertEquals(4, $student->answers['campus_cost']);
    }
}
