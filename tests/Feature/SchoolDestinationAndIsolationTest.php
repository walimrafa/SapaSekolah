<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Campus;
use App\Models\District;
use App\Models\FollowUp;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolType;
use App\Models\User;
use App\Services\RouteClusteringService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SchoolDestinationAndIsolationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_google_maps_driving_url_includes_school_names()
    {
        $service = app(RouteClusteringService::class);

        $sequence = [
            [
                'id' => 1,
                'name' => 'SMAN 1 CIAMPEL',
                'latitude' => -6.3908,
                'longitude' => 107.3624,
                'district' => ['name' => 'Ciampel'],
                'regency' => ['name' => 'Karawang'],
            ],
            [
                'id' => 2,
                'name' => 'SMK SURYACIPTA',
                'latitude' => -6.4007,
                'longitude' => 107.3307,
                'district' => ['name' => 'Ciampel'],
                'regency' => ['name' => 'Karawang'],
            ]
        ];

        $url = $service->buildGoogleMapsDrivingUrl($sequence);

        $this->assertStringContainsString('google.com/maps/dir', $url);
        // Verify school names are encoded into the URL query
        $this->assertStringContainsString(urlencode('SMAN 1 CIAMPEL'), $url);
        $this->assertStringContainsString(urlencode('SMK SURYACIPTA'), $url);
    }

    public function test_google_maps_driving_url_for_no_return_ends_at_last_school()
    {
        $service = app(RouteClusteringService::class);

        $sequence = [
            [
                'id' => 1,
                'name' => 'SMAN 1 CIAMPEL',
                'latitude' => -6.3908,
                'longitude' => 107.3624,
                'district' => ['name' => 'Ciampel'],
            ],
            [
                'id' => 2,
                'name' => 'SMK SURYACIPTA',
                'latitude' => -6.4007,
                'longitude' => 107.3307,
                'district' => ['name' => 'Ciampel'],
            ]
        ];

        $url = $service->buildGoogleMapsDrivingUrl($sequence, 'no_return');

        $this->assertStringContainsString('destination=' . urlencode('SMK SURYACIPTA, Ciampel, Karawang'), $url);
        $this->assertStringContainsString('waypoints=' . urlencode('SMAN 1 CIAMPEL, Ciampel, Karawang'), $url);
    }

    public function test_cikampek_campus_does_not_show_karawang_schools_in_follow_ups()
    {
        $karawangCampus = Campus::firstOrCreate(['code' => 'KRW'], ['name' => 'UBSI Kampus Karawang', 'is_default' => true, 'is_active' => true]);
        $cikampekCampus = Campus::firstOrCreate(['code' => 'CKP'], ['name' => 'UBSI Kampus Cikampek', 'is_default' => false, 'is_active' => true]);
        $period = AcademicPeriod::firstOrCreate(['name' => '2026/2027'], ['start_date' => '2026-09-01', 'end_date' => '2027-08-31', 'is_active' => true, 'is_current' => true]);

        $province = \App\Models\Province::firstOrCreate(['name' => 'Jawa Barat']);
        $regency = \App\Models\Regency::firstOrCreate(['name' => 'Kabupaten Karawang'], ['province_id' => $province->id]);
        $krwDistrict = District::firstOrCreate(['name' => 'Karawang Barat'], ['regency_id' => $regency->id]);
        $ckpDistrict = District::firstOrCreate(['name' => 'Cikampek'], ['regency_id' => $regency->id]);
        $st = SchoolType::firstOrCreate(['name' => 'SMA']);
        $rolePimpinan = Role::firstOrCreate(['name' => 'Pimpinan']);

        $krwSchool = School::create([
            'campus_id' => $karawangCampus->id,
            'district_id' => $krwDistrict->id,
            'school_type_id' => $st->id,
            'name' => 'SMAS KORPRI KARAWANG',
            'npsn' => '20237366',
            'status' => 'SWASTA',
            'latitude' => -6.307,
            'longitude' => 107.295,
        ]);

        $ckpSchool = School::create([
            'campus_id' => $cikampekCampus->id,
            'district_id' => $ckpDistrict->id,
            'school_type_id' => $st->id,
            'name' => 'SMKN 1 CIKAMPEK',
            'npsn' => '20237367',
            'status' => 'NEGERI',
            'latitude' => -6.411,
            'longitude' => 107.452,
        ]);

        $pimpinanCikampek = User::firstOrCreate(
            ['email' => 'nurulichsan_test@bsi.ac.id'],
            [
                'name' => 'Nurul Ichsan, M.Kom',
                'password' => bcrypt('password'),
                'role_id' => $rolePimpinan->id,
                'campus_id' => $cikampekCampus->id,
                'status' => 'active',
            ]
        );

        // Create follow-up for Karawang school, even if campus_id was accidentally set to Cikampek
        FollowUp::create([
            'school_id' => $krwSchool->id,
            'campus_id' => $cikampekCampus->id, // corrupted/mismatched campus_id
            'academic_period_id' => $period->id,
            'user_id' => $pimpinanCikampek->id,
            'followup_date' => now(),
            'method' => 'WhatsApp',
            'status' => 'pending',
            'notes' => 'Follow up Karawang school',
        ]);

        // Create follow-up for Cikampek school
        FollowUp::create([
            'school_id' => $ckpSchool->id,
            'campus_id' => $cikampekCampus->id,
            'academic_period_id' => $period->id,
            'user_id' => $pimpinanCikampek->id,
            'followup_date' => now(),
            'method' => 'WhatsApp',
            'status' => 'pending',
            'notes' => 'Follow up Cikampek school',
        ]);

        $response = $this->actingAs($pimpinanCikampek)
            ->withSession(['selected_campus_id' => $cikampekCampus->id, 'selected_period_id' => $period->id])
            ->get(route('follow-ups.index'));

        $response->assertStatus(200);
        // Cikampek school must be visible
        $response->assertSee('SMKN 1 CIKAMPEK');
        // Karawang school must NOT be visible under Cikampek
        $response->assertDontSee('SMAS KORPRI KARAWANG');
    }
}

