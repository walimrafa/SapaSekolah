<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Campus;
use App\Models\Prospect;
use App\Models\Role;
use App\Models\School;
use App\Models\StudentQuestionnaire;
use App\Models\User;
use App\Services\GoogleDriveService;
use App\Services\InstagramVerificationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EnhancementRequirementsTest extends TestCase
{
    use DatabaseTransactions;

    protected User $superAdmin;
    protected Campus $karawangCampus;
    protected Campus $cikampekCampus;
    protected AcademicPeriod $period;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'Admin'], ['description' => 'Administrator']);

        $this->karawangCampus = Campus::firstOrCreate(
            ['code' => 'KRW'],
            ['name' => 'UBSI Karawang', 'is_active' => true, 'is_default' => true]
        );

        $this->cikampekCampus = Campus::firstOrCreate(
            ['code' => 'CKP'],
            ['name' => 'UBSI Cikampek', 'is_active' => true, 'is_default' => false]
        );

        $this->period = AcademicPeriod::firstOrCreate(
            ['name' => '2026/2027'],
            ['is_active' => true]
        );

        $uid = uniqid();
        $this->superAdmin = User::create([
            'name' => 'Super Admin Test',
            'username' => "superadmin_{$uid}",
            'nisn' => "123456{$uid}",
            'email' => "admin_{$uid}@sapa.test",
            'password' => bcrypt('password123'),
            'role_id' => $adminRole->id,
            'role' => 'super_admin',
            'campus_id' => $this->karawangCampus->id,
            'is_active' => true,
        ]);
    }

    public function test_captcha_refresh_and_login_with_username_or_nisn()
    {
        // 1. Refresh captcha returns SVG and sets login_captcha session
        $captchaResponse = $this->get(route('captcha.refresh'));
        $captchaResponse->assertOk()
            ->assertJsonStructure(['success', 'svg'])
            ->assertSessionHas('login_captcha');

        // 2. Login succeeds with username
        $loginResponse = $this->post(route('login'), [
            'login' => $this->superAdmin->username,
            'password' => 'password123',
        ]);
        $loginResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->superAdmin);

        auth()->logout();

        // 3. Login succeeds with NISN
        $nisnLogin = $this->post(route('login'), [
            'login' => $this->superAdmin->nisn,
            'password' => 'password123',
        ]);
        $nisnLogin->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->superAdmin);
    }

    public function test_prospects_bulk_status_and_bulk_delete()
    {
        $this->actingAs($this->superAdmin);

        $prospect1 = Prospect::create([
            'name' => 'Prospek Satu',
            'phone' => '081234567891',
            'campus_id' => $this->karawangCampus->id,
            'status' => Prospect::STATUS_DAFTAR_AKUN,
        ]);

        $prospect2 = Prospect::create([
            'name' => 'Prospek Dua',
            'phone' => '081234567892',
            'campus_id' => $this->karawangCampus->id,
            'status' => Prospect::STATUS_DAFTAR_AKUN,
        ]);

        // Bulk status update
        $bulkStatusResponse = $this->post(route('prospects.bulk-status'), [
            'selected_ids' => [$prospect1->id, $prospect2->id],
            'status' => Prospect::STATUS_BAYAR_REGISTRASI,
        ]);
        $bulkStatusResponse->assertRedirect();

        $this->assertEquals(Prospect::STATUS_BAYAR_REGISTRASI, $prospect1->fresh()->status);
        $this->assertEquals(Prospect::STATUS_BAYAR_REGISTRASI, $prospect2->fresh()->status);

        // Bulk delete (soft delete)
        $bulkDeleteResponse = $this->post(route('prospects.bulk-delete'), [
            'selected_ids' => [$prospect1->id, $prospect2->id],
        ]);
        $bulkDeleteResponse->assertRedirect();

        $this->assertSoftDeleted('prospects', ['id' => $prospect1->id]);
        $this->assertSoftDeleted('prospects', ['id' => $prospect2->id]);
    }

    public function test_kol_automatic_follower_verification_and_rate_card()
    {
        $this->actingAs($this->superAdmin);

        $school = School::create([
            'npsn' => '20123456',
            'name' => 'SMKN 1 Karawang',
            'campus_id' => $this->karawangCampus->id,
        ]);

        $candidate = StudentQuestionnaire::create([
            'campus_id' => $this->karawangCampus->id,
            'period_id' => $this->period->id,
            'school_id' => $school->id,
            'student_name' => 'Seleb Siswa',
            'student_phone' => '085712345678',
            'instagram_handle' => 'selebsiswa',
            'instagram_followers' => 8500,
            'is_kol' => true,
            'kol_status' => 'kandidat',
        ]);

        // 1. Dynamic Rate Card calculation
        $rateCard = $candidate->estimated_rate_card;
        $this->assertEquals('Micro Influencer', $rateCard['tier']);
        $this->assertEquals(8500, $rateCard['followers_used']);
        $this->assertNotEmpty($rateCard['feed_post']);
        $this->assertNotEmpty($rateCard['brand_ambassador_package']);

        // 2. Instagram Verification Service handles candidate
        $result = InstagramVerificationService::verifyCandidate($candidate);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('verified_followers', $result);
        $this->assertArrayHasKey('notes', $result);

        // 3. Save Work Links for Brand Ambassador
        $workLinksResponse = $this->post(route('kol-recommendations.save-work-links', $candidate->id), [
            'instagram_link' => 'https://www.instagram.com/reel/C1234567890/',
            'tiktok_link' => 'https://www.tiktok.com/@selebsiswa/video/987654321',
            'youtube_link' => 'https://youtube.com/shorts/abcdefghijk',
            'notes' => 'Sudah tayang 2 video promosi kampus UBSI',
        ]);
        $workLinksResponse->assertRedirect();

        $updatedCandidate = $candidate->fresh();
        $this->assertNotNull($updatedCandidate->work_links);
        $this->assertEquals('https://www.instagram.com/reel/C1234567890/', $updatedCandidate->work_links['instagram']);
        $this->assertEquals('Sudah tayang 2 video promosi kampus UBSI', $updatedCandidate->work_links['notes']);

        // 4. KOL Bulk Status Update and Bulk Delete
        $bulkStatus = $this->post(route('kol-recommendations.bulk-status'), [
            'selected_ids' => [$candidate->id],
            'kol_status' => 'brand_ambassador',
        ]);
        $bulkStatus->assertRedirect();
        $this->assertEquals('brand_ambassador', $candidate->fresh()->kol_status);

        $bulkDelete = $this->post(route('kol-recommendations.bulk-delete'), [
            'selected_ids' => [$candidate->id],
        ]);
        $bulkDelete->assertRedirect();
        $this->assertFalse((bool) $candidate->fresh()->is_kol);
    }

    public function test_registration_proof_upload_and_folder_mapping()
    {
        Storage::fake('public');
        $this->actingAs($this->superAdmin);

        $driveService = new GoogleDriveService();

        // 1. Check folder mapping for Karawang and Cikampek proof folders
        $karawangFolder = $driveService->getProofFolderIdForCampus($this->karawangCampus);
        $this->assertEquals(GoogleDriveService::PROOF_KARAWANG_FOLDER_ID, $karawangFolder);

        $cikampekFolder = $driveService->getProofFolderIdForCampus($this->cikampekCampus);
        $this->assertEquals(GoogleDriveService::PROOF_CIKAMPEK_FOLDER_ID, $cikampekFolder);

        // 2. Upload proof to prospect directly
        $prospect = Prospect::create([
            'name' => 'Siswa Baru Berkas',
            'phone' => '089912345678',
            'campus_id' => $this->karawangCampus->id,
            'status' => Prospect::STATUS_DAFTAR_AKUN,
        ]);

        $file = UploadedFile::fake()->create('bukti_daftar.png', 500, 'image/png');

        $uploadResponse = $this->post(route('prospects.upload-proof', $prospect->id), [
            'registration_proof' => $file,
        ]);
        $uploadResponse->assertRedirect();

        $freshProspect = $prospect->fresh();
        $this->assertNotEmpty($freshProspect->registration_proof_path);
        $this->assertNotNull($freshProspect->registration_proof_uploaded_at);
        $this->assertTrue($freshProspect->has_registration_proof);
    }

    public function test_kol_documentation_page_loads_and_scopes_by_campus()
    {
        $this->actingAs($this->superAdmin);

        $school = School::create([
            'npsn' => '20998877',
            'name' => 'SMA Negeri 1 Karawang',
            'campus_id' => $this->karawangCampus->id,
        ]);

        $ambassador = StudentQuestionnaire::create([
            'campus_id' => $this->karawangCampus->id,
            'period_id' => $this->period->id,
            'school_id' => $school->id,
            'student_name' => 'Brand Ambassador Krw',
            'student_phone' => '081299988877',
            'instagram_handle' => 'ambassador_krw',
            'instagram_followers' => 12500,
            'is_kol' => true,
            'kol_status' => 'brand_ambassador',
            'work_links' => [
                'instagram' => 'https://www.instagram.com/reel/Doc12345/',
                'tiktok' => 'https://www.tiktok.com/@ambassador_krw/video/12345',
                'notes' => 'Tayang video PMB Gelombang 1',
            ],
        ]);

        // Request documentation page
        $response = $this->get(route('kol-recommendations.documentation'));
        $response->assertOk()
            ->assertViewIs('kol-recommendations.documentation')
            ->assertSee('Hasil Dokumentasi &amp; Karya Brand Ambassador', false)
            ->assertSee('Brand Ambassador Krw');

        // Filter by platform instagram
        $filterResponse = $this->get(route('kol-recommendations.documentation', ['platform' => 'instagram']));
        $filterResponse->assertOk()
            ->assertSee('Brand Ambassador Krw');
    }
}
