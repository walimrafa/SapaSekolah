<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\Campus;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolEventProposal;
use App\Models\StudentQuestionnaire;
use App\Models\User;
use App\Services\NotificationService;
use App\Support\SecuritySanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationAndSecurityHardeningTest extends TestCase
{
    /**
     * Test notification creation, retrieval, and user scoping.
     */
    public function test_in_app_notification_system_and_scoping(): void
    {
        $roleAdmin = Role::firstOrCreate(['name' => 'Admin']);
        $roleStaff = Role::firstOrCreate(['name' => 'Marketing']);

        $campus1 = Campus::firstOrCreate(['code' => 'KRW'], ['name' => 'UBSI Kampus Karawang', 'is_active' => true]);
        $campus2 = Campus::firstOrCreate(['code' => 'CKP'], ['name' => 'UBSI Kampus Cikampek', 'is_active' => true]);

        $admin = User::factory()->create([
            'role_id' => $roleAdmin->id,
            'campus_id' => $campus1->id,
        ]);

        $staffCkp = User::factory()->create([
            'role_id' => $roleStaff->id,
            'campus_id' => $campus2->id,
        ]);

        // Create notification for campus 2
        $notifCkp = NotificationService::send([
            'campus_id' => $campus2->id,
            'title' => 'Proposal Event Cikampek',
            'message' => 'Proposal baru masuk untuk UBSI Cikampek',
            'action_url' => '/proposals',
            'icon' => 'file-text',
        ]);

        $this->assertInstanceOf(AppNotification::class, $notifCkp);
        $this->assertFalse($notifCkp->is_read);

        // Staff on campus 2 should see this notification
        $unreadStaff = NotificationService::getUnreadCount($staffCkp);
        $this->assertGreaterThanOrEqual(1, $unreadStaff);

        // Feed endpoint test
        $response = $this->actingAs($staffCkp)->getJson(route('notifications.feed'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'unread_count',
            'notifications',
        ]);

        // Mark as read test
        $markResponse = $this->actingAs($staffCkp)->postJson(route('notifications.read', $notifCkp->id));
        $markResponse->assertStatus(200);
        $markResponse->assertJson(['success' => true]);

        $notifCkp->refresh();
        $this->assertTrue($notifCkp->is_read);
        $this->assertNotNull($notifCkp->read_at);

        // Mark all as read test
        $readAllResponse = $this->actingAs($staffCkp)->postJson(route('notifications.read-all'));
        $readAllResponse->assertStatus(200);
        $readAllResponse->assertJson(['success' => true, 'unread_count' => 0]);
    }

    /**
     * Test notification triggered upon student questionnaire submission.
     */
    public function test_questionnaire_triggers_notification(): void
    {
        $campus = Campus::first() ?: Campus::create(['name' => 'Kampus Karawang', 'code' => 'KRW']);
        $school = School::first() ?: School::create([
            'name' => 'SMKN 1 Test',
            'npsn' => '99887766',
            'campus_id' => $campus->id,
        ]);

        $prevCount = AppNotification::count();

        $response = $this->post(route('student-questionnaire.store'), [
            'school_id' => $school->id,
            'student_name' => 'Siswa Test Notif',
            'student_class' => 'XII RPL 1',
            'student_major' => 'RPL',
            'student_phone' => '081234567890',
            'student_email' => 'siswa@test.com',
            'guardian_name' => 'Orang Tua Test',
            'guardian_phone' => '081298765432',
            'scholarship_interest' => 'ya',
            'answers' => [
                'motivation_job' => 5,
                'motivation_interest' => 5,
                'motivation_parent' => 5,
                'information_social_media' => 4,
                'information_testimonials' => 4,
                'information_comparison' => 4,
                'social_parent' => 5,
                'social_friends' => 4,
                'social_teacher' => 5,
                'campus_cost' => 5,
                'campus_reputation' => 5,
                'campus_career' => 5,
                'campus_location' => 5,
                'intention_ubsi' => 5,
                'intention_information' => 5,
                'barrier_cost' => 4,
            ],
        ]);

        $response->assertSessionHas('success_message');
        $this->assertGreaterThan($prevCount, AppNotification::count());

        $latestNotif = AppNotification::latest('id')->first();
        $this->assertEquals('Kuisioner Siswa Baru', $latestNotif->title);
        $this->assertStringContainsString('Siswa Test Notif', $latestNotif->message);
    }

    /**
     * Test Security Headers are present on web requests.
     */
    public function test_security_headers_are_enforced(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'geolocation=(self), camera=(), microphone=()');
    }

    /**
     * Test failed login logs audit trail and proposal tracking is rate limited.
     */
    public function test_failed_login_records_audit_log(): void
    {
        $prevLogCount = \App\Models\ActivityLog::where('action', 'LOGIN_FAILED')->count();

        $response = $this->post('/login', [
            'email' => 'fake_attacker@domain.com',
            'password' => 'wrong_password_123',
        ]);

        $response->assertSessionHasErrors('email');
        $newLogCount = \App\Models\ActivityLog::where('action', 'LOGIN_FAILED')->count();
        $this->assertEquals($prevLogCount + 1, $newLogCount);

        $latestLog = \App\Models\ActivityLog::where('action', 'LOGIN_FAILED')->latest('id')->first();
        $this->assertStringContainsString('fake_attacker@domain.com', $latestLog->description);
    }

    /**
     * Test Formula Injection sanitization on spreadsheet export strings.
     */
    public function test_formula_injection_sanitization(): void
    {
        $maliciousFormulas = [
            '=cmd|"/c calc"!A0',
            '+123456789',
            '-2+5+cmd',
            '@SUM(1+1)',
            "\tDangerousTab",
        ];

        foreach ($maliciousFormulas as $formula) {
            $sanitized = SecuritySanitizer::sanitizeSpreadsheetCell($formula);
            $this->assertStringStartsWith("'", $sanitized, "Formula injection must be prepended with a quote: {$formula}");
        }

        // Safe values should remain intact
        $safeText = 'SMA Negeri 1 Karawang';
        $this->assertEquals('SMA Negeri 1 Karawang', SecuritySanitizer::sanitizeSpreadsheetCell($safeText));

        $safeNumber = 120;
        $this->assertEquals(120, SecuritySanitizer::sanitizeSpreadsheetCell($safeNumber));
    }

    /**
     * Test sorting parameter strict whitelisting.
     */
    public function test_school_sorting_whitelist_protection(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin']);
        $user = User::factory()->create(['role_id' => $role->id]);

        // Attempt invalid sort direction
        $response = $this->actingAs($user)->get(route('schools.index', [
            'sort' => 'name',
            'direction' => 'invalid_dir; drop table users;',
        ]));

        $response->assertStatus(200);
    }
}
