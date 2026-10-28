<?php

namespace Tests\Feature;

use App\Http\Controllers\ProspectController;
use App\Services\StudentImportService;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class StudentImportTest extends TestCase
{
    public function test_import_rejects_a_workbook_without_required_headers(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'student-import-');
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray([['Kolom Salah'], ['Data']]);
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        try {
            $file = new UploadedFile($path, 'students.xlsx', null, UPLOAD_ERR_OK, true);
            $result = app(StudentImportService::class)->import($file);

            $this->assertFalse($result['status']);
            $this->assertSame(
                'Header wajib: NIM, Nama Mahasiswa, dan NPSN atau Sekolah Asal.',
                $result['message']
            );
        } finally {
            unlink($path);
        }
    }

    public function test_downloaded_template_contains_import_headers(): void
    {
        $response = app(ProspectController::class)->downloadImportTemplate();
        ob_start();
        $response->sendContent();
        $contents = ob_get_clean();
        $path = tempnam(sys_get_temp_dir(), 'student-template-');

        try {
            file_put_contents($path, $contents);
            $spreadsheet = IOFactory::load($path);

            $this->assertSame([
                'NIM',
                'Nama Mahasiswa',
                'NPSN',
                'Sekolah Asal',
                'Jenis Kelamin',
                'No HP',
                'Email',
                'Tahun Lulus',
                'Program Studi',
            ], $spreadsheet->getActiveSheet()->toArray()[0]);

            $spreadsheet->disconnectWorksheets();
        } finally {
            unlink($path);
        }
    }
}
