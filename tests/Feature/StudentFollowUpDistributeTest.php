<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Role;
use App\Models\StudentQuestionnaire;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class StudentFollowUpDistributeTest extends TestCase
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
        $this->campusCikampek = Campus::firstOrCreate(['code' => 'CKP'], ['name' => 'UBSI Cikampek', 'is_active' => true, 'is_default' => false]);

        $this->admin = User::factory()->create(['role_id' => $adminRole->id, 'campus_id' => $this->campusKarawang->id]);
        $this->pimpinan = User::factory()->create(['role_id' => $pimpinanRole->id, 'campus_id' => $this->campusKarawang->id]);
        $this->koordinator = User::factory()->create(['role_id' => $koordinatorRole->id, 'campus_id' => $this->campusKarawang->id]);
        $this->marketing = User::factory()->create(['role_id' => $marketingRole->id, 'campus_id' => $this->campusKarawang->id]);
        $this->cs = User::factory()->create(['role_id' => $csRole->id, 'campus_id' => $this->campusKarawang->id]);
    }

    public function test_get_distribute_page_accessible_by_authorized_roles(): void
    {
        // Admin dapat mengakses (200 OK)
        $responseAdmin = $this->actingAs($this->admin)->get(route('student-follow-ups.distribute.page'));
        $responseAdmin->assertOk();
        $responseAdmin->assertSee('Ploting &amp; Distribusi Data Leads Siswa', false);

        // Pimpinan dapat mengakses (200 OK)
        $responsePimpinan = $this->actingAs($this->pimpinan)->get(route('student-follow-ups.distribute.page'));
        $responsePimpinan->assertOk();

        // Koordinator dapat mengakses (200 OK)
        $responseKoordinator = $this->actingAs($this->koordinator)->get(route('student-follow-ups.distribute.page'));
        $responseKoordinator->assertOk();

        // Marketing ditolak (403 Forbidden)
        $responseMarketing = $this->actingAs($this->marketing)->get(route('student-follow-ups.distribute.page'));
        $responseMarketing->assertForbidden();

        // CS ditolak (403 Forbidden)
        $responseCs = $this->actingAs($this->cs)->get(route('student-follow-ups.distribute.page'));
        $responseCs->assertForbidden();

        // Guest diarahkan ke login
        auth()->logout();
        $responseGuest = $this->get(route('student-follow-ups.distribute.page'));
        $responseGuest->assertRedirect(route('login'));
    }

    public function test_distribute_post_divides_leads_equally(): void
    {
        // Buat 4 leads siswa unassigned
        for ($i = 1; $i <= 4; $i++) {
            StudentQuestionnaire::create([
                'student_name' => "Siswa Antrean {$i}",
                'student_phone' => "08123450000{$i}",
                'student_email' => "antrean{$i}@gmail.com",
                'school_name' => 'SMAN 1 Karawang',
                'campus_id' => $this->campusKarawang->id,
                'student_class' => 'XII',
                'student_major' => 'IPA',
                'scholarship_interest' => 'ya',
                'assigned_to' => null,
            ]);
        }

        $officer1 = $this->marketing;
        $officer2 = User::factory()->create([
            'role_id' => Role::where('name', 'Marketing')->first()->id,
            'campus_id' => $this->campusKarawang->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('student-follow-ups.distribute'), [
            'distribution_type' => 'equal',
            'source_filter' => 'all',
            'officer_ids' => [$officer1->id, $officer2->id],
        ]);

        $response->assertRedirect(route('student-follow-ups.index'));

        // Cek bahwa semua siswa terbagi rata (masing-masing 2)
        $assignedOfficer1 = StudentQuestionnaire::where('assigned_to', $officer1->id)->count();
        $assignedOfficer2 = StudentQuestionnaire::where('assigned_to', $officer2->id)->count();

        $this->assertEquals(2, $assignedOfficer1);
        $this->assertEquals(2, $assignedOfficer2);
    }
}

