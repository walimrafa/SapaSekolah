<?php

namespace Tests\Feature;

use App\Http\Controllers\PriorityController;
use App\Models\School;
use App\Services\SchoolStudentIntakeImportService;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class SchoolStudentIntakeImportTest extends TestCase
{
    public function test_import_rejects_a_workbook_without_school_period_and_count_headers(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'school-intake-');
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray([['Kolom Salah'], ['Data']]);
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        try {
            $file = new UploadedFile($path, 'intakes.xlsx', null, UPLOAD_ERR_OK, true);
            $result = app(SchoolStudentIntakeImportService::class)->import($file);

            $this->assertFalse($result['status']);
            $this->assertSame(
                'Header wajib: NPSN atau Nama Sekolah, Periode PMB, dan Jumlah Mahasiswa Baru.',
                $result['message']
            );
        } finally {
            unlink($path);
        }
    }

    public function test_downloaded_template_contains_school_intake_headers(): void
    {
        $response = app(PriorityController::class)->downloadStudentIntakeTemplate();
        ob_start();
        $response->sendContent();
        $contents = ob_get_clean();
        $path = tempnam(sys_get_temp_dir(), 'school-intake-template-');

        try {
            file_put_contents($path, $contents);
            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $this->assertSame([
                'NPSN Sekolah',
                'Nama Sekolah Asal',
                'Periode PMB',
                'Jumlah Mahasiswa Baru',
            ], $sheet->toArray()[0]);
            $this->assertSame(School::count() + 1, $sheet->getHighestRow());

            $school = School::orderBy('name')->first();
            if ($school) {
                $this->assertSame([
                    (string) $school->npsn,
                    $school->name,
                    null,
                    null,
                ], $sheet->toArray()[1]);
            }
            $this->assertSame('A2', $sheet->getFreezePane());

            $spreadsheet->disconnectWorksheets();
        } finally {
            unlink($path);
        }
    }

    public function test_import_skips_prefilled_template_rows_without_period_and_count(): void
    {
        $school = School::orderBy('name')->first();
        if (!$school) {
            $this->markTestSkipped('No school exists for checking blank template rows.');
        }

        $path = tempnam(sys_get_temp_dir(), 'school-intake-blank-');
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray([
            ['NPSN Sekolah', 'Nama Sekolah Asal', 'Periode PMB', 'Jumlah Mahasiswa Baru'],
            [(string) $school->npsn, $school->name, null, null],
        ]);
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        try {
            $file = new UploadedFile($path, 'blank-intakes.xlsx', null, UPLOAD_ERR_OK, true);
            $result = app(SchoolStudentIntakeImportService::class)->import($file);

            $this->assertFalse($result['status']);
            $this->assertSame(0, $result['skipped']);
            $this->assertSame('Tidak ada baris data valid untuk diimpor.', $result['message']);
        } finally {
            unlink($path);
        }
    }
}
