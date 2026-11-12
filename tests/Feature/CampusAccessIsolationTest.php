<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Role;
use App\Models\School;
use App\Models\StudentQuestionnaire;
use App\Models\User;
use App\Services\AppContextService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CampusAccessIsolationTest extends TestCase
{
    use DatabaseTransactions;

    protected Campus $campusKarawang;
    protected Campus $campusCikampek;
    protected Role $adminRole;
    protected Role $pimpinanRole;
    protected Role $koordinatorRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminRole = Role::firstOrCreate(['name' => 'Admin'], ['description' => 'Administrator']);
        $this->pimpinanRole = Role::firstOrCreate(['name' => 'Pimpinan'], ['description' => 'Pimpinan Kampus']);
        $this->koordinatorRole = Role::firstOrCreate(['name' => 'Koordinator Marketing'], ['description' => 'Koordinator Marketing']);

        $this->campusKarawang = Campus::firstOrCreate(['code' => 'KRW'], ['name' => 'UBSI Karawang', 'is_active' => true, 'is_default' => true]);
        $this->campusCikampek = Campus::firstOrCreate(['code' => 'CKP'], ['name' => 'UBSI Kampus Cikampek', 'is_active' => true, 'is_default' => false]);
    }

    public function test_branch_pimpinan_and_koordinator_cannot_switch_campus(): void
    {
        $pimpinanKarawang = User::factory()->create([
            'name' => 'Pimpinan UBSI Karawang',
            'role_id' => $this->pimpinanRole->id,
            'campus_id' => $this->campusKarawang->id,
        ]);

        $koordinatorCikampek = User::factory()->create([
            'name' => 'Koordinator UBSI Cikampek',
            'role_id' => $this->koordinatorRole->id,
            'campus_id' => $this->campusCikampek->id,
        ]);

        // Branch leaders cannot access cross-campus
        $this->assertFalse($pimpinanKarawang->canAccessAllCampuses());
        $this->assertFalse(AppContextService::canSwitchCampus($pimpinanKarawang));

        $this->assertFalse($koordinatorCikampek->canAccessAllCampuses());
        $this->assertFalse(AppContextService::canSwitchCampus($koordinatorCikampek));

        // Active campus is strictly locked to their own branch
        $this->actingAs($pimpinanKarawang);
        $this->assertEquals($this->campusKarawang->id, AppContextService::getActiveCampus()->id);

        // Attempting to switch context to another campus fails
        $response = $this->post(route('context.switch'), [
            'campus_id' => $this->campusCikampek->id,
        ]);
        $response->assertSessionHas('toast.type', 'warning');
        $this->assertEquals($this->campusKarawang->id, AppContextService::getActiveCampus()->id);

        // Koordinator Cikampek locked to Cikampek
        $this->actingAs($koordinatorCikampek);
        $this->assertEquals($this->campusCikampek->id, AppContextService::getActiveCampus()->id);

        $responseCkp = $this->post(route('context.switch'), [
            'campus_id' => $this->campusKarawang->id,
        ]);
        $responseCkp->assertSessionHas('toast.type', 'warning');
        $this->assertEquals($this->campusCikampek->id, AppContextService::getActiveCampus()->id);
    }

    public function test_pusat_pimpinan_and_koordinator_can_switch_campus(): void
    {
        $pimpinanPusat = User::factory()->create([
            'name' => 'Pimpinan Pusat UBSI',
            'role_id' => $this->pimpinanRole->id,
            'campus_id' => null,
        ]);

        $koordinatorPusat = User::factory()->create([
            'name' => 'Koordinator Marketing Pusat',
            'role_id' => $this->koordinatorRole->id,
            'campus_id' => null,
        ]);

        $adminPusat = User::factory()->create([
            'name' => 'Super Administrator',
            'role_id' => $this->adminRole->id,
            'campus_id' => null,
        ]);

        // Pusat roles can switch campus
        $this->assertTrue($pimpinanPusat->canAccessAllCampuses());
        $this->assertTrue(AppContextService::canSwitchCampus($pimpinanPusat));

        $this->assertTrue($koordinatorPusat->canAccessAllCampuses());
        $this->assertTrue(AppContextService::canSwitchCampus($koordinatorPusat));

        $this->assertTrue($adminPusat->canAccessAllCampuses());
        $this->assertTrue(AppContextService::canSwitchCampus($adminPusat));

        // Pusat user can switch to Cikampek successfully
        $this->actingAs($koordinatorPusat);
        $response = $this->post(route('context.switch'), [
            'campus_id' => $this->campusCikampek->id,
        ]);
        $response->assertSessionHas('toast.type', 'success');
        $this->assertEquals($this->campusCikampek->id, session('selected_campus_id'));
    }

    public function test_branch_user_cannot_access_or_mutate_cross_campus_records(): void
    {
        $koordinatorKarawang = User::factory()->create([
            'name' => 'Koordinator Marketing Karawang',
            'role_id' => $this->koordinatorRole->id,
            'campus_id' => $this->campusKarawang->id,
        ]);

        $cikampekSchool = School::create([
            'npsn' => 'CKP' . bin2hex(random_bytes(3)),
            'name' => 'SMK Negeri 1 Cikampek',
            'campus_id' => $this->campusCikampek->id,
            'status' => 'negeri',
        ]);

        $cikampekStudent = StudentQuestionnaire::create([
            'student_name' => 'Siswa Cikampek',
            'student_phone' => '089999999999',
            'school_id' => $cikampekSchool->id,
            'campus_id' => $this->campusCikampek->id,
            'scholarship_interest' => 'ya',
        ]);

        // Karawang coordinator attempting to update student of Cikampek is denied with 403
        $this->actingAs($koordinatorKarawang);
        $response = $this->put(route('student-follow-ups.update-student', $cikampekStudent), [
            'student_name' => 'Siswa Cikampek Diubah',
            'student_phone' => '089999999999',
            'scholarship_interest' => 'ya',
        ]);
        $response->assertStatus(403);

        // Attempting to delete student of Cikampek is denied with 403
        $deleteResponse = $this->delete(route('student-follow-ups.destroy-student', $cikampekStudent));
        $deleteResponse->assertStatus(403);
    }
}
