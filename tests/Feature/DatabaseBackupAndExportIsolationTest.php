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
}
