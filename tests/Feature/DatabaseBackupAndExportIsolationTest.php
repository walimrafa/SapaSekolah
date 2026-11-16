<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\DatabaseBackup;
use App\Models\Role;
use App\Models\School;
use App\Models\StudentQuestionnaire;
use App\Models\User;
use App\Services\DatabaseBackupService;
use App\Services\GoogleDriveService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DatabaseBackupAndExportIsolationTest extends TestCase
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

    /**
     * Memastikan ID Folder Google Drive tepat sesuai spesifikasi:
     * - UBSI Cikampek: 1YCNXg6CoeiFdPOoDWUNXGB5L1Nc765aT
     * - UBSI Karawang: 1i_84WHAo5nlk8bwDg0vXdEffyX1SLf28
     */
    public function test_google_drive_folder_mapping_matches_spec(): void
    {
        $cikampekFolder = GoogleDriveService::getBackupFolderIdForCampus($this->campusCikampek);
        $karawangFolder = GoogleDriveService::getBackupFolderIdForCampus($this->campusKarawang);

        $this->assertEquals('1YCNXg6CoeiFdPOoDWUNXGB5L1Nc765aT', $cikampekFolder);
        $this->assertEquals('1i_84WHAo5nlk8bwDg0vXdEffyX1SLf28', $karawangFolder);

        $this->assertEquals('1YCNXg6CoeiFdPOoDWUNXGB5L1Nc765aT', GoogleDriveService::getBackupFolderIdByCampusCodeOrId('CKP'));
        $this->assertEquals('1i_84WHAo5nlk8bwDg0vXdEffyX1SLf28', GoogleDriveService::getBackupFolderIdByCampusCodeOrId('KRW'));
    }

    /**
     * Memastikan pembuatan backup database lokal (.sql.gz) berhasil dan valid.
     */
    public function test_database_backup_generation_and_local_storage(): void
    {
        Storage::fake('local');

        /** @var DatabaseBackupService $service */
        $service = app(DatabaseBackupService::class);

        $backup = $service->createBackup(
            campusId: $this->campusCikampek->id,
            type: 'manual',
            uploadToGdrive: false
        );

        $this->assertInstanceOf(DatabaseBackup::class, $backup);
        $this->assertEquals('success', $backup->status);
        $this->assertEquals($this->campusCikampek->id, $backup->campus_id);
        $this->assertStringContainsString('backup-sapa-sekolah-ckp-', $backup->filename);
        $this->assertStringEndsWith('.sql.gz', $backup->filename);
        $this->assertGreaterThan(0, $backup->file_size);

        // Pastikan file tersimpan di disk lokal
        $this->assertTrue(Storage::disk('local')->exists($backup->file_path));

        // Verifikasi isi file terkompresi gzip
        $compressedContent = Storage::disk('local')->get($backup->file_path);
        $decompressed = gzdecode($compressedContent);
        $this->assertNotFalse($decompressed);
        $this->assertStringContainsString('SAPA Sekolah Database Dump', $decompressed);
        $this->assertStringContainsString('SET FOREIGN_KEY_CHECKS=0;', $decompressed);
    }

    /**
     * Memastikan isolasi hak akses unduh dan kelola backup antar kampus cabang.
     */
    public function test_branch_user_backup_isolation(): void
    {
        Storage::fake('local');

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

        $superAdmin = User::factory()->create([
            'name' => 'Super Administrator',
            'role_id' => $this->adminRole->id,
            'campus_id' => null,
        ]);

        /** @var DatabaseBackupService $service */
        $service = app(DatabaseBackupService::class);

        $backupCkp = $service->createBackup(campusId: $this->campusCikampek->id, type: 'manual', uploadToGdrive: false);
        $backupKrw = $service->createBackup(campusId: $this->campusKarawang->id, type: 'manual', uploadToGdrive: false);

        // Pimpinan Karawang tidak boleh mengunduh backup Cikampek (403)
        $this->actingAs($pimpinanKarawang);
        $response = $this->get(route('database-backups.download', $backupCkp));
        $response->assertStatus(403);

        // Pimpinan Karawang boleh mengunduh backup Karawang
        $responseKrw = $this->get(route('database-backups.download', $backupKrw));
        $responseKrw->assertStatus(200);

        // Koordinator Cikampek tidak boleh mengunduh backup Karawang (403)
        $this->actingAs($koordinatorCikampek);
        $response = $this->get(route('database-backups.download', $backupKrw));
        $response->assertStatus(403);

        // Koordinator Cikampek boleh mengunduh backup Cikampek
        $responseCkp = $this->get(route('database-backups.download', $backupCkp));
        $responseCkp->assertStatus(200);

        // Super Admin boleh mengunduh keduanya
        $this->actingAs($superAdmin);
        $this->get(route('database-backups.download', $backupCkp))->assertStatus(200);
        $this->get(route('database-backups.download', $backupKrw))->assertStatus(200);
    }

    /**
     * Memastikan halaman index database backups dapat dimuat sempurna tanpa error rendering.
     */
    public function test_database_backups_index_page_loads_successfully(): void
    {
        $superAdmin = User::factory()->create([
            'name' => 'Super Administrator',
            'role_id' => $this->adminRole->id,
            'campus_id' => null,
        ]);

        $this->actingAs($superAdmin);
        $response = $this->get(route('database-backups.index'));
        $response->assertStatus(200);
        $response->assertSee('Cadangan Database & Google Drive');
        $response->assertSee('Riwayat Berkas Cadangan');
    }

    /**
     * Memastikan perintah Artisan db:backup berjalan mulus.
     */
    public function test_artisan_db_backup_command(): void
    {
        $exitCode = \Illuminate\Support\Facades\Artisan::call('db:backup', [
            '--campus' => 'CKP',
            '--type' => 'manual',
        ]);

        $this->assertEquals(0, $exitCode);

        $this->assertDatabaseHas('database_backups', [
            'campus_id' => $this->campusCikampek->id,
            'type' => 'manual',
            'status' => 'success',
        ]);
    }

    /**
     * Memastikan pemisahan data ekspor per kampus (UBSI Cikampek vs UBSI Karawang).
     */
    public function test_export_data_is_strictly_separated_by_campus(): void
    {
        $schoolCkp = School::create([
            'npsn' => 'CKP' . bin2hex(random_bytes(3)),
            'name' => 'SMK Bina Cikampek',
            'campus_id' => $this->campusCikampek->id,
            'status' => 'swasta',
        ]);

        $schoolKrw = School::create([
            'npsn' => 'KRW' . bin2hex(random_bytes(3)),
            'name' => 'SMA Negeri 1 Karawang',
            'campus_id' => $this->campusKarawang->id,
            'status' => 'negeri',
        ]);

        StudentQuestionnaire::create([
            'student_name' => 'Siswa Cikampek Asli',
            'student_phone' => '081299990001',
            'school_id' => $schoolCkp->id,
            'campus_id' => $this->campusCikampek->id,
            'whatsapp' => '081299990001',
            'scholarship_interest' => 'ya',
        ]);

        StudentQuestionnaire::create([
            'student_name' => 'Siswa Karawang Asli',
            'student_phone' => '081299990002',
            'school_id' => $schoolKrw->id,
            'campus_id' => $this->campusKarawang->id,
            'whatsapp' => '081299990002',
            'scholarship_interest' => 'tidak',
        ]);

        $koordinatorCikampek = User::factory()->create([
            'name' => 'Koordinator UBSI Cikampek',
            'role_id' => $this->koordinatorRole->id,
            'campus_id' => $this->campusCikampek->id,
        ]);

        // Ekspor data sekolah dari Koordinator Cikampek hanya memuat Cikampek
        $this->actingAs($koordinatorCikampek);
        $response = $this->get(route('schools.export'));
        $response->assertStatus(200);
        $csvContent = $response->streamedContent();

        $this->assertStringContainsString('SMK Bina Cikampek', $csvContent);
        $this->assertStringNotContainsString('SMA Negeri 1 Karawang', $csvContent);

        // Ekspor data follow up dari Koordinator Cikampek hanya memuat siswa Cikampek
        $responseFollowup = $this->get(route('student-follow-ups.export'));
        $responseFollowup->assertStatus(200);
        $fuContent = $responseFollowup->streamedContent();

        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($tempFile, $fuContent);
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tempFile);
        $allText = '';
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                foreach ($cellIterator as $cell) {
                    $allText .= ' ' . $cell->getValue();
                }
            }
        }
        @unlink($tempFile);

        $this->assertStringContainsString('Siswa Cikampek Asli', $allText);
        $this->assertStringNotContainsString('Siswa Karawang Asli', $allText);
    }

    /**
     * Memastikan pembuatan backup format Excel (.xlsx) menghasilkan 11 sheet lengkap dan terisolasi kampus.
     */
    public function test_excel_backup_generation_contains_all_sheets_and_is_isolated(): void
    {
        Storage::fake('local');

        $schoolCkp = School::create([
            'npsn' => 'CKP' . bin2hex(random_bytes(3)),
            'name' => 'SMK Laboratorium Cikampek',
            'campus_id' => $this->campusCikampek->id,
            'status' => 'swasta',
        ]);

        $schoolKrw = School::create([
            'npsn' => 'KRW' . bin2hex(random_bytes(3)),
            'name' => 'SMA Favorit Karawang',
            'campus_id' => $this->campusKarawang->id,
            'status' => 'negeri',
        ]);

        /** @var DatabaseBackupService $service */
        $service = app(DatabaseBackupService::class);

        $backup = $service->createBackup(
            campusId: $this->campusCikampek->id,
            type: 'manual',
            uploadToGdrive: false,
            format: 'excel'
        );

        $this->assertInstanceOf(DatabaseBackup::class, $backup);
        $this->assertEquals('excel', $backup->format);
        $this->assertEquals('success', $backup->status);
        $this->assertStringEndsWith('.xlsx', $backup->filename);
        $this->assertTrue(Storage::disk('local')->exists($backup->file_path));

        // Muat spreadsheet dan periksa seluruh 11 sheet
        $filePath = Storage::disk('local')->path($backup->file_path);
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $sheetNames = $spreadsheet->getSheetNames();

        $expectedSheets = [
            'Ringkasan Cadangan',
            'Master Sekolah',
            'Kunjungan Sekolah',
            'Hasil Kuisioner',
            'Follow Up Siswa',
            'Prospek CRM',
            'Mahasiswa Baru',
            'Support Event',
            'Leads Siswa Event',
            'Rekomendasi KOL',
            'Pengguna & Tim Petugas',
        ];

        foreach ($expectedSheets as $expected) {
            $this->assertContains($expected, $sheetNames, "Sheet '{$expected}' harus ada dalam workbook cadangan Excel.");
        }

        // Pastikan isi data sekolah terisolasi: hanya memuat sekolah Cikampek
        $sekolahSheet = $spreadsheet->getSheetByName('Master Sekolah');
        $sheetText = '';
        foreach ($sekolahSheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            foreach ($cellIterator as $cell) {
                $sheetText .= ' ' . $cell->getValue();
            }
        }

        $this->assertStringContainsString('SMK Laboratorium Cikampek', $sheetText);
        $this->assertStringNotContainsString('SMA Favorit Karawang', $sheetText);
    }

    /**
     * Memastikan pembuatan backup format 'both' menghasilkan berkas SQL dan Excel sekaligus.
     */
    public function test_backup_format_both_creates_sql_and_excel_files(): void
    {
        Storage::fake('local');

        /** @var DatabaseBackupService $service */
        $service = app(DatabaseBackupService::class);

        $backups = $service->createBackup(
            campusId: $this->campusKarawang->id,
            type: 'manual',
            uploadToGdrive: false,
            format: 'both'
        );

        $this->assertIsArray($backups);
        $this->assertArrayHasKey('sql', $backups);
        $this->assertArrayHasKey('excel', $backups);

        $sqlBackup = $backups['sql'];
        $excelBackup = $backups['excel'];

        $this->assertEquals('sql', $sqlBackup->format);
        $this->assertStringEndsWith('.sql.gz', $sqlBackup->filename);
        $this->assertTrue(Storage::disk('local')->exists($sqlBackup->file_path));

        $this->assertEquals('excel', $excelBackup->format);
        $this->assertStringEndsWith('.xlsx', $excelBackup->filename);
        $this->assertTrue(Storage::disk('local')->exists($excelBackup->file_path));
    }

    /**
     * Memastikan SQL Dump kampus Cikampek tidak membocorkan baris kampus Karawang.
     */
    public function test_sql_dump_strictly_excludes_other_campus_records(): void
    {
        $schoolCkp = School::create([
            'npsn' => 'CKP' . bin2hex(random_bytes(3)),
            'name' => 'SMK Isolated Cikampek',
            'campus_id' => $this->campusCikampek->id,
            'status' => 'swasta',
        ]);

        $schoolKrw = School::create([
            'npsn' => 'KRW' . bin2hex(random_bytes(3)),
            'name' => 'SMA Isolated Karawang',
            'campus_id' => $this->campusKarawang->id,
            'status' => 'negeri',
        ]);

        /** @var DatabaseBackupService $service */
        $service = app(DatabaseBackupService::class);

        $sqlContent = $service->dumpDatabaseToSql($this->campusCikampek);

        $this->assertStringContainsString('SMK Isolated Cikampek', $sqlContent);
        $this->assertStringNotContainsString('SMA Isolated Karawang', $sqlContent);
    }

    /**
     * Memastikan integrasi Webhook Google Apps Script berhasil mengunggah berkas.
     */
    public function test_google_drive_webhook_upload_integration(): void
    {
        Storage::fake('local');

        \Illuminate\Support\Facades\Http::fake([
            'https://script.google.com/*' => \Illuminate\Support\Facades\Http::response([
                'success' => true,
                'file_id' => 'mock-gdrive-file-12345',
                'file_url' => 'https://drive.google.com/file/d/mock-gdrive-file-12345/view',
            ], 200),
        ]);

        \App\Services\AppSettingService::set('gdrive_webhook_url', 'https://script.google.com/macros/s/test-webhook/exec');
        \App\Services\AppSettingService::set('gdrive_cikampek_webhook_url', 'https://script.google.com/macros/s/test-webhook/exec');
        \App\Services\AppSettingService::set('gdrive_karawang_webhook_url', 'https://script.google.com/macros/s/test-webhook/exec');

        /** @var DatabaseBackupService $service */
        $service = app(DatabaseBackupService::class);

        $backup = $service->createBackup(
            campusId: $this->campusCikampek->id,
            type: 'manual',
            uploadToGdrive: true,
            format: 'sql'
        );

        $this->assertEquals('uploaded', $backup->gdrive_status);
        $this->assertEquals('mock-gdrive-file-12345', $backup->gdrive_file_id);
        $this->assertStringContainsString('mock-gdrive-file-12345', $backup->gdrive_file_url);
    }

    /**
     * Memastikan aksi massal syncAll berhasil menyinkronkan seluruh berkas lokal pending.
     */
    public function test_bulk_sync_all_pending_backups_to_google_drive(): void
    {
        Storage::fake('local');

        \Illuminate\Support\Facades\Http::fake([
            'https://script.google.com/*' => \Illuminate\Support\Facades\Http::response([
                'success' => true,
                'file_id' => 'mock-bulk-file-' . uniqid(),
                'file_url' => 'https://drive.google.com/file/d/mock-bulk/view',
            ], 200),
        ]);

        \App\Services\AppSettingService::set('gdrive_webhook_url', 'https://script.google.com/macros/s/test-webhook/exec');
        \App\Services\AppSettingService::set('gdrive_cikampek_webhook_url', 'https://script.google.com/macros/s/test-webhook/exec');
        \App\Services\AppSettingService::set('gdrive_karawang_webhook_url', 'https://script.google.com/macros/s/test-webhook/exec');

        /** @var DatabaseBackupService $service */
        $service = app(DatabaseBackupService::class);

        // Buat 2 berkas tanpa upload (status pending)
        $backup1 = $service->createBackup(campusId: $this->campusCikampek->id, uploadToGdrive: false, format: 'sql');
        $backup2 = $service->createBackup(campusId: $this->campusKarawang->id, uploadToGdrive: false, format: 'excel');

        $this->assertEquals('pending', $backup1->gdrive_status);
        $this->assertEquals('pending', $backup2->gdrive_status);

        $superAdmin = User::factory()->create([
            'name' => 'Super Administrator',
            'role_id' => $this->adminRole->id,
            'campus_id' => null,
        ]);

        $this->actingAs($superAdmin);
        $response = $this->post(route('database-backups.sync-all'));
        $response->assertRedirect(route('database-backups.index'));
        $response->assertSessionHas('success');

        $this->assertEquals('uploaded', $backup1->fresh()->gdrive_status);
        $this->assertEquals('uploaded', $backup2->fresh()->gdrive_status);
    }
}
