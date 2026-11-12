<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolEventProposal;
use App\Models\StudentQuestionnaire;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class KolRecommendationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $pimpinan;
    protected User $koordinator;
    protected User $marketing;
    protected User $cs;
    protected Campus $campusKarawang;
    protected Campus $campusCikampek;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'Admin'], ['description' => 'Administrator']);
        $pimpinanRole = Role::firstOrCreate(['name' => 'Pimpinan'], ['description' => 'Pimpinan']);
        $koordinatorRole = Role::firstOrCreate(['name' => 'Koordinator Marketing'], ['description' => 'Koordinator Marketing']);
        $marketingRole = Role::firstOrCreate(['name' => 'Marketing'], ['description' => 'Marketing Lapangan']);
        $csRole = Role::firstOrCreate(['name' => 'Customer Service'], ['description' => 'Customer Service']);

        $this->campusKarawang = Campus::firstOrCreate(['code' => 'KRW'], ['name' => 'UBSI Karawang', 'is_active' => true, 'is_default' => true]);
        $this->campusCikampek = Campus::firstOrCreate(['code' => 'CKP'], ['name' => 'UBSI Kampus Cikampek', 'is_active' => true, 'is_default' => false]);

        $uid = uniqid();
        $this->admin = User::factory()->create(['email' => "admin_{$uid}@example.com", 'role_id' => $adminRole->id, 'campus_id' => $this->campusKarawang->id]);
        $this->pimpinan = User::factory()->create(['email' => "pimpinan_{$uid}@example.com", 'role_id' => $pimpinanRole->id, 'campus_id' => $this->campusKarawang->id]);
        $this->koordinator = User::factory()->create(['email' => "koordinator_{$uid}@example.com", 'role_id' => $koordinatorRole->id, 'campus_id' => $this->campusKarawang->id]);
        $this->marketing = User::factory()->create(['email' => "marketing_{$uid}@example.com", 'role_id' => $marketingRole->id, 'campus_id' => $this->campusKarawang->id]);
        $this->cs = User::factory()->create(['email' => "cs_{$uid}@example.com", 'role_id' => $csRole->id, 'campus_id' => $this->campusKarawang->id]);
    }

    public function test_automatic_kol_flagging_when_instagram_followers_ge_1500(): void
    {
        // 1. Siswa dengan follower >= 1500 otomatis menjadi KOL
        $kolStudent = StudentQuestionnaire::create([
            'student_name' => 'Influencer Siswa',
            'student_phone' => '081234567890',
            'student_email' => 'influencer@gmail.com',
            'instagram_handle' => 'influencer_siswa',
            'instagram_followers' => 1500,
            'school_name' => 'SMAN 1 Karawang',
            'campus_id' => $this->campusKarawang->id,
            'student_class' => 'XII MIPA 1',
            'student_major' => 'IPA',
            'scholarship_interest' => 'ya',
        ]);

        $this->assertTrue($kolStudent->is_kol);
        $this->assertEquals(StudentQuestionnaire::KOL_STATUS_KANDIDAT, $kolStudent->kol_status);
        $this->assertEquals('influencer_siswa', $kolStudent->clean_instagram_handle);
        $this->assertEquals('https://instagram.com/influencer_siswa', $kolStudent->instagram_url);

        // 2. Siswa dengan follower < 1500 tidak di-flag sebagai KOL
        $regularStudent = StudentQuestionnaire::create([
            'student_name' => 'Siswa Biasa',
            'student_phone' => '081234567891',
            'student_email' => 'biasa@gmail.com',
            'instagram_handle' => 'siswa_biasa',
            'instagram_followers' => 1400,
            'school_name' => 'SMAN 1 Karawang',
            'campus_id' => $this->campusKarawang->id,
            'student_class' => 'XII MIPA 2',
            'student_major' => 'IPA',
            'scholarship_interest' => 'ya',
        ]);

        $this->assertFalse($regularStudent->is_kol);
    }

    public function test_role_access_authorization_for_kol_recommendations(): void
    {
        // Admin dapat mengakses (200 OK)
        $responseAdmin = $this->actingAs($this->admin)->get(route('kol-recommendations.index'));
        $responseAdmin->assertOk();
        $responseAdmin->assertSee('Rekomendasi KOL');

        // Pimpinan dapat mengakses (200 OK)
        $responsePimpinan = $this->actingAs($this->pimpinan)->get(route('kol-recommendations.index'));
        $responsePimpinan->assertOk();

        // Koordinator Marketing dapat mengakses (200 OK)
        $responseKoordinator = $this->actingAs($this->koordinator)->get(route('kol-recommendations.index'));
        $responseKoordinator->assertOk();

        // Marketing Lapangan DITOLAK (403 Forbidden)
        $responseMarketing = $this->actingAs($this->marketing)->get(route('kol-recommendations.index'));
        $responseMarketing->assertForbidden();

        // Customer Service DITOLAK (403 Forbidden)
        $responseCs = $this->actingAs($this->cs)->get(route('kol-recommendations.index'));
        $responseCs->assertForbidden();
    }

    public function test_update_status_and_notes_for_kol_candidate(): void
    {
        $student = StudentQuestionnaire::create([
            'student_name' => 'Calon Duta',
            'student_phone' => '081234567899',
            'student_email' => 'calon@gmail.com',
            'instagram_handle' => 'calon_duta',
            'instagram_followers' => 3000,
            'school_name' => 'SMAN 2 Karawang',
            'campus_id' => $this->campusKarawang->id,
            'student_class' => 'XII IPS 1',
            'student_major' => 'IPS',
            'scholarship_interest' => 'ya',
        ]);

        $response = $this->actingAs($this->admin)->put(route('kol-recommendations.update-status', $student->id), [
            'kol_status' => StudentQuestionnaire::KOL_STATUS_BERSEDIA,
            'kol_notes' => 'Siswa bersedia membuat 3 video TikTok & Reels promosi beasiswa.',
            'instagram_handle' => 'calon_duta_official',
            'instagram_followers' => 3200,
        ]);

        $response->assertSessionHas('success');

        $student->refresh();
        $this->assertEquals(StudentQuestionnaire::KOL_STATUS_BERSEDIA, $student->kol_status);
        $this->assertEquals('Siswa bersedia membuat 3 video TikTok & Reels promosi beasiswa.', $student->kol_notes);
        $this->assertEquals('calon_duta_official', $student->instagram_handle);
        $this->assertEquals(3200, $student->instagram_followers);
    }

    public function test_kol_export_excel_streamed_response(): void
    {
        StudentQuestionnaire::create([
            'student_name' => 'KOL Export Test',
            'student_phone' => '081234567888',
            'student_email' => 'export@gmail.com',
            'instagram_handle' => 'export_kol',
            'instagram_followers' => 5000,
            'school_name' => 'SMAN 1 Karawang',
            'campus_id' => $this->campusKarawang->id,
            'student_class' => 'XII',
            'student_major' => 'IPA',
            'scholarship_interest' => 'ya',
        ]);

        $response = $this->actingAs($this->admin)->get(route('kol-recommendations.export'));
        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_event_form_submission_creates_kol_candidate(): void
    {
        $school = School::firstOrCreate([
            'npsn' => '20269999',
        ], [
            'name' => 'SMAN 3 Karawang',
            'campus_id' => $this->campusKarawang->id,
            'address' => 'Karawang Barat',
            'phone' => '0267123456',
        ]);

        $proposal = SchoolEventProposal::create([
            'reference_no' => 'EVT-TEST-001',
            'event_name' => 'Pentas Seni Pelajar 2026',
            'school_id' => $school->id,
            'campus_id' => $this->campusKarawang->id,
            'school_name' => $school->name,
            'status' => SchoolEventProposal::STATUS_DISETUJUI,
            'event_date' => now()->addDays(3),
            'event_time' => '08:00 - 12:00',
            'event_location' => 'Aula Sekolah',
            'target_participants' => 300,
            'pic_name' => 'Budi Santoso',
            'pic_phone' => '081234567800',
            'pic_position' => 'Ketua Panitia',
            'proposal_file_path' => 'proposals/test-proposal.pdf',
            'proposal_file_name' => 'proposal.pdf',
            'proposal_file_size' => 102400,
            'description' => 'Event pentas seni sekolah tahunan.',
        ]);

        $postData = [
            'student_name' => 'Rian Event Influencer',
            'gender' => 'L',
            'school_id' => $school->id,
            'student_class' => 'XII IPS 2',
            'student_major' => 'IPS',
            'student_phone' => '089876543210',
            'student_email' => 'rian_influencer@gmail.com',
            'student_social_media' => '@rian_event',
            'instagram_handle' => 'rian_event',
            'instagram_followers' => 4500,
            'guardian_name' => 'Samsul',
            'guardian_phone' => '089876543211',
            'scholarship_interest' => 'ya',
            'school_event_proposal_id' => $proposal->id,
            'campus_id' => $this->campusKarawang->id,
        ];

        $response = $this->post(route('event-students.store'), $postData);
        $response->assertRedirect(route('event-students.success'));

        $savedStudent = StudentQuestionnaire::where('student_email', 'rian_influencer@gmail.com')->first();
        $this->assertNotNull($savedStudent);
        $this->assertTrue($savedStudent->is_kol);
        $this->assertEquals(4500, $savedStudent->instagram_followers);
        $this->assertEquals('rian_event', $savedStudent->instagram_handle);
    }

    public function test_campus_isolation_in_kol_recommendations(): void
    {
        // Kandidat Karawang
        $karawangStudent = StudentQuestionnaire::create([
            'student_name' => 'Bintang Karawang',
            'student_phone' => '081111111111',
            'student_email' => 'karawang_star@gmail.com',
            'instagram_handle' => 'karawang_star',
            'instagram_followers' => 8000,
            'school_name' => 'SMAN 1 Karawang',
            'campus_id' => $this->campusKarawang->id,
            'student_class' => 'XII',
            'student_major' => 'IPA',
            'scholarship_interest' => 'ya',
        ]);

        // Kandidat Cikampek
        $cikampekStudent = StudentQuestionnaire::create([
            'student_name' => 'Bintang Cikampek',
            'student_phone' => '082222222222',
            'student_email' => 'cikampek_star@gmail.com',
            'instagram_handle' => 'cikampek_star',
            'instagram_followers' => 9000,
            'school_name' => 'SMAN 1 Cikampek',
            'campus_id' => $this->campusCikampek->id,
            'student_class' => 'XII',
            'student_major' => 'IPS',
            'scholarship_interest' => 'ya',
        ]);

        // Simulasikan session context kampus Karawang
        \App\Services\AppContextService::setActiveCampus($this->campusKarawang->id);

        $response = $this->actingAs($this->admin)->get(route('kol-recommendations.index'));
        $response->assertOk();
        $response->assertSee('Bintang Karawang');
        $response->assertDontSee('Bintang Cikampek');

        // Simulasikan session context kampus Cikampek
        \App\Services\AppContextService::setActiveCampus($this->campusCikampek->id);

        $response2 = $this->actingAs($this->admin)->get(route('kol-recommendations.index'));
        $response2->assertOk();
        $response2->assertSee('Bintang Cikampek');
        $response2->assertDontSee('Bintang Karawang');
    }
}
