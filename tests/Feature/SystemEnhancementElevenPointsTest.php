<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\School;
use App\Models\StudyProgram;
use App\Models\User;
use App\Models\VisitPlan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SystemEnhancementElevenPointsTest extends TestCase
{
    use DatabaseTransactions;

    protected Campus $karawangCampus;
    protected Campus $cikampekCampus;
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->karawangCampus = Campus::where('code', 'KRW')->firstOrFail();
        $this->cikampekCampus = Campus::where('code', 'CKP')->firstOrFail();
        $this->adminUser = User::firstOrFail();
    }

    public function test_visit_plan_creation_scopes_schools_and_districts_by_active_campus(): void
    {
        $this->actingAs($this->adminUser);

        // Switch to Cikampek
        $this->post(route('context.switch'), ['campus_id' => $this->cikampekCampus->id]);

        $response = $this->get(route('visit-plans.create'));
        $response->assertOk();

        // Ensure Cikampek view contains only Cikampek schools count and no Karawang leakage
        $cikampekSchoolCount = School::where('campus_id', $this->cikampekCampus->id)->count();
        $response->assertSee("{$cikampekSchoolCount} Sekolah Tersedia");
        $response->assertSee('tom-select');
    }

    public function test_past_unexecuted_visit_plans_are_automatically_expired(): void
    {
        $this->actingAs($this->adminUser);

        $school = School::where('campus_id', $this->cikampekCampus->id)->first() ?? School::first();

        // Create an expired plan in the past
        $pastPlan = VisitPlan::create([
            'campus_id' => $this->cikampekCampus->id,
            'school_id' => $school->id,
            'user_id' => $this->adminUser->id,
            'planned_date' => Carbon::yesterday()->toDateString(),
            'status' => 'planned',
            'notes' => 'Old plan',
        ]);

        // Access index to trigger auto-expiration
        $this->post(route('context.switch'), ['campus_id' => $this->cikampekCampus->id]);
        $response = $this->get(route('visit-plans.index'));
        $response->assertOk();

        $pastPlan->refresh();
        $this->assertSame('cancelled', $pastPlan->status);
    }

    public function test_marketing_route_can_generate_automatically_from_active_visit_plans(): void
    {
        $this->actingAs($this->adminUser);
        $this->post(route('context.switch'), ['campus_id' => $this->karawangCampus->id]);

        $krwSchool = School::where('campus_id', $this->karawangCampus->id)->first() ?? School::first();
        $krwSchool->update(['latitude' => -6.3054, 'longitude' => 107.2995]);
        VisitPlan::create([
            'campus_id' => $this->karawangCampus->id,
            'school_id' => $krwSchool->id,
            'user_id' => $this->adminUser->id,
            'planned_date' => Carbon::tomorrow()->toDateString(),
            'status' => 'planned',
        ]);

        $response = $this->get(route('marketing-routes.index'));
        $response->assertOk();
        $response->assertSee('Rute dari Rencana Kunjungan');
        $response->assertSee('Buat Rute Otomatis');
        $response->assertSee('loadFromVisitPlans');
    }

    public function test_study_programs_are_isolated_by_campus(): void
    {
        $this->actingAs($this->adminUser);

        $krwProdi = StudyProgram::where('campus_id', $this->karawangCampus->id)->pluck('code')->toArray();
        $ckpProdi = StudyProgram::where('campus_id', $this->cikampekCampus->id)->pluck('code')->toArray();

        $this->assertNotEmpty($krwProdi);
        $this->assertNotEmpty($ckpProdi);

        // Ensure Cikampek prodi has distinct codes/campus_id
        $this->assertContains('SI-CKP', $ckpProdi);
        $this->assertNotContains('SI-CKP', $krwProdi);
        $this->assertContains('SI-KRW', $krwProdi);
    }

    public function test_reports_diagram_and_data_categories_render_properly(): void
    {
        $this->actingAs($this->adminUser);

        // Diagram Category
        $diagramResponse = $this->get(route('reports.index', ['category' => 'diagram']));
        $diagramResponse->assertOk();
        $diagramResponse->assertSee('Laporan');
        $diagramResponse->assertSee('chartTimeline');
        $diagramResponse->assertSee('chartStatus');
        $diagramResponse->assertSee('chartDistrict');
        $diagramResponse->assertSee('chartMarketing');
        $diagramResponse->assertSee('chartProspectFunnel');

        // Data Category
        $dataResponse = $this->get(route('reports.index', ['category' => 'data', 'tab' => 'visits']));
        $dataResponse->assertOk();
        $dataResponse->assertSee('Progres Kunjungan');
    }

    public function test_reports_can_export_pptx_and_pdf(): void
    {
        $this->actingAs($this->adminUser);

        // PowerPoint PPTX Export
        $pptxResponse = $this->get(route('reports.export-pptx'));
        $pptxResponse->assertOk();
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.presentationml.presentation', $pptxResponse->headers->get('content-type'));

        // Executive PDF / Print View
        $pdfResponse = $this->get(route('reports.export-pdf'));
        $pdfResponse->assertOk();
        $pdfResponse->assertSee('Laporan Eksekutif Marketing');
    }
}
