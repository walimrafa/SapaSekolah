<?php

namespace Tests\Feature;

use App\Services\SchoolImportService;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class SchoolExcelImportTest extends TestCase
{
    public function test_xlsx_files_without_school_name_header_return_a_validation_message(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'school-import-');
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray([
            ['NPSN', 'Alamat'],
            ['12345678', 'Karawang'],
        ]);
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        try {
            $file = new UploadedFile($path, 'schools.xlsx', null, UPLOAD_ERR_OK, true);
            $result = app(SchoolImportService::class)->import($file);

            $this->assertFalse($result['status']);
            $this->assertSame('Header file Excel harus memiliki kolom "Nama Sekolah".', $result['message']);
        } finally {
            unlink($path);
        }
    }

    public function test_xlsx_files_are_parsed_and_report_missing_required_npsn_header(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'school-import-');
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray([
            ['Nama Sekolah', 'Alamat'],
            ['SMA Contoh', 'Karawang'],
        ]);
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        try {
            $file = new UploadedFile($path, 'schools.xlsx', null, UPLOAD_ERR_OK, true);
            $result = app(SchoolImportService::class)->import($file);

            $this->assertFalse($result['status']);
            $this->assertSame('Header file Excel harus memiliki kolom "NPSN".', $result['message']);
        } finally {
            unlink($path);
        }
    }

    public function test_import_skips_rows_with_invalid_counts_or_unpaired_coordinates(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'school-import-');
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray([
            ['NPSN', 'Nama Sekolah', 'Latitude', 'Longitude', 'Jumlah Siswa'],
            ['12345678', 'Sekolah Koordinat Tidak Lengkap', -6.3, null, 100],
            ['87654321', 'Sekolah Jumlah Tidak Valid', -6.3, 107.3, 'abc'],
        ]);
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        try {
            $file = new UploadedFile($path, 'schools.xlsx', null, UPLOAD_ERR_OK, true);
            $result = app(SchoolImportService::class)->import($file);

            $this->assertFalse($result['status']);
            $this->assertSame([
                'Baris 2: Latitude dan longitude harus diisi berpasangan.',
                'Baris 3: Jumlah siswa harus berupa bilangan bulat nol atau lebih.',
            ], $result['errors']);
        } finally {
            unlink($path);
        }
    }
}
