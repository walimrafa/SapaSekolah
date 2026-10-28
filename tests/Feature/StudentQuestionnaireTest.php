<?php

namespace Tests\Feature;

use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\StudentQuestionnaireController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class StudentQuestionnaireTest extends TestCase
{
    public function test_public_questionnaire_form_lists_all_survey_sections_and_scale(): void
    {
        $response = $this->get(route('student-questionnaire.create'));

        $response->assertOk()
            ->assertSee('Identitas Siswa dan Wali')
            ->assertSee('Kelas')
            ->assertSee('Jurusan')
            ->assertSee('A. Motivasi')
            ->assertSee('B. Pencarian informasi')
            ->assertSee('C. Pengaruh sosial')
            ->assertSee('D. Preferensi kampus')
            ->assertSee('E. Niat dan hambatan')
            ->assertSee('Saya berniat mencari informasi PMB UBSI lebih lanjut.')
            ->assertSee('Apakah Anda berminat mengikuti program beasiswa UBSI?')
            ->assertSee('Sangat tidak setuju')
            ->assertSee('Sangat setuju');
    }

    public function test_questionnaire_submission_requires_contact_information_and_all_answers(): void
    {
        $response = $this->from(route('student-questionnaire.create'))
            ->post(route('student-questionnaire.store'), [
                'student_name' => 'Nama Siswa',
                'student_class' => 'XII IPA 1',
                'student_major' => 'IPA',
                'student_phone' => '08123456789',
                'student_email' => 'siswa@example.com',
                'guardian_name' => 'Nama Wali',
                'guardian_phone' => '08123456789',
                'scholarship_interest' => 'ya',
                'answers' => [],
            ]);

        $response->assertRedirect(route('student-questionnaire.create'))
            ->assertSessionHasErrors([
                'answers.motivation_job',
                'answers.barrier_cost',
            ]);
    }

    public function test_public_questionnaire_form_is_accessible_without_login(): void
    {
        $response = $this->get(route('student-questionnaire.create'));

        $response->assertOk();
    }

    public function test_questionnaire_submission_requires_class_major_and_scholarship_answer(): void
    {
        $response = $this->from(route('student-questionnaire.create'))
            ->post(route('student-questionnaire.store'), [
                'student_name' => 'Nama Siswa',
                'student_phone' => '08123456789',
                'student_email' => 'siswa@example.com',
                'guardian_name' => 'Nama Wali',
                'guardian_phone' => '08123456789',
                'answers' => [],
            ]);

        $response->assertRedirect(route('student-questionnaire.create'))
            ->assertSessionHasErrors(['student_class', 'student_major', 'scholarship_interest']);
    }

    public function test_questionnaire_results_require_authentication(): void
    {
        $this->get(route('student-questionnaires.index'))
            ->assertRedirect(route('login'));

        $this->get(route('student-questionnaires.export'))
            ->assertRedirect(route('login'));
    }

    public function test_qr_code_center_includes_the_public_questionnaire_url(): void
    {
        $request = Request::create('/qr-codes', 'GET', [], [], [], [
            'HTTP_HOST' => 'sapasekolah.test',
            'HTTPS' => 'on',
        ]);

        $view = app(QrCodeController::class)->index($request);

        $this->assertSame(
            'https://sapasekolah.test' . route('student-questionnaire.create', [], false),
            $view->getData()['studentQuestionnaireUrl']
        );
        $this->assertStringContainsString(
            urlencode($view->getData()['studentQuestionnaireUrl']),
            $view->getData()['studentQuestionnaireQrUrl']
        );
    }

    public function test_student_questionnaire_qr_code_can_be_downloaded(): void
    {
        Http::fake([
            'api.qrserver.com/*' => Http::response('png-content', 200, ['Content-Type' => 'image/png']),
        ]);

        $response = app(QrCodeController::class)->download(
            Request::create('/qr-codes/download/student-questionnaire', 'GET', [], [], [], [
                'HTTP_HOST' => 'sapasekolah.test',
                'HTTPS' => 'on',
            ]),
            'student-questionnaire'
        );

        $this->assertSame('image/png', $response->headers->get('Content-Type'));
        $this->assertStringContainsString(
            'QR_Code_Kuisioner_Siswa_SAPA_Sekolah.png',
            $response->headers->get('Content-Disposition')
        );
        $this->assertSame('png-content', $response->getContent());
    }

    public function test_questionnaire_export_contains_response_and_statistics_sheets(): void
    {
        $response = app(StudentQuestionnaireController::class)->downloadExcel();
        ob_start();
        $response->sendContent();
        $contents = ob_get_clean();
        $path = tempnam(sys_get_temp_dir(), 'student-questionnaire-export-');

        try {
            file_put_contents($path, $contents);
            $spreadsheet = IOFactory::load($path);

            $this->assertSame(['Data Responden', 'Statistik'], $spreadsheet->getSheetNames());
            $this->assertSame('Nama Lengkap', $spreadsheet->getSheetByNameOrThrow('Data Responden')->getCell('B1')->getValue());
            $this->assertSame('Statistik Kuisioner Siswa', $spreadsheet->getSheetByNameOrThrow('Statistik')->getCell('A1')->getValue());
            $this->assertSame('Berminat Program Beasiswa UBSI', $spreadsheet->getSheetByNameOrThrow('Data Responden')->getCell('AB1')->getValue());

            $spreadsheet->disconnectWorksheets();
        } finally {
            unlink($path);
        }
    }

    public function test_questionnaire_statistics_are_available_for_chart_rendering(): void
    {
        $this->actingAs(User::query()->firstOrFail());
        $view = app(StudentQuestionnaireController::class)->index(Request::create('/hasil-kuisioner'));
        $statistics = $view->getData()['statistics'];
        $rendered = $view->render();

        $this->assertSame(16 * $statistics['total'], array_sum($statistics['scores']));
        $this->assertCount(5, $statistics['sections']);
        $this->assertSame(
            array_sum($statistics['scores']),
            array_sum(array_map(
                fn (array $question) => array_sum($question['scores']),
                $statistics['questions']
            ))
        );
        $this->assertStringContainsString('questionnaireScholarshipChart', $rendered);
        $this->assertStringContainsString('questionnaireSectionChart', $rendered);
        $this->assertStringContainsString('questionnaireMajorChart', $rendered);
        $this->assertStringContainsString('questionnaireClassChart', $rendered);
        $this->assertStringContainsString('questionnaireScoreChart', $rendered);
    }
}
