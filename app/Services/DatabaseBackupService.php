<?php

namespace App\Services;

use App\Models\Campus;
use App\Models\DatabaseBackup;
use App\Models\Prospect;
use App\Models\School;
use App\Models\SchoolEventProposal;
use App\Models\SchoolVisit;
use App\Models\StudentQuestionnaire;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PDO;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Throwable;

class DatabaseBackupService
{
    protected GoogleDriveService $googleDriveService;

    public function __construct(GoogleDriveService $googleDriveService)
    {
        $this->googleDriveService = $googleDriveService;
    }

    /**
     * Buat berkas cadangan database (SQL Dump dan/atau Excel Lengkap) dan sinkronisasikan ke Google Drive.
     *
     * @param int|null $campusId Kampus target (UBSI Karawang / UBSI Cikampek / null untuk Pusat)
     * @param string $type 'manual' atau 'scheduled'
     * @param int|null $userId User yang memicu backup
     * @param bool $uploadToGdrive Apakah langsung diunggah ke Google Drive
     * @param string $format 'both', 'sql', atau 'excel'
     */
    public function createBackup(
        ?int $campusId = null,
        string $type = 'manual',
        ?int $userId = null,
        bool $uploadToGdrive = true,
        string $format = 'sql'
    ): DatabaseBackup|array {
        $campus = $campusId ? Campus::find($campusId) : null;

        // Jika minta format keduanya (SQL & Excel)
        if ($format === 'both') {
            $sqlBackup = $this->createSqlBackup($campus, $type, $userId, $uploadToGdrive);
            $excelBackup = $this->createExcelBackup($campus, $type, $userId, $uploadToGdrive);

            return [
                'sql' => $sqlBackup,
                'excel' => $excelBackup,
            ];
        }

        if ($format === 'excel') {
            return $this->createExcelBackup($campus, $type, $userId, $uploadToGdrive);
        }

        return $this->createSqlBackup($campus, $type, $userId, $uploadToGdrive);
    }

    /**
     * Buat berkas cadangan database format SQL Dump (.sql.gz) terisolasi per kampus.
     */
    public function createSqlBackup(?Campus $campus = null, string $type = 'manual', ?int $userId = null, bool $uploadToGdrive = true): DatabaseBackup
    {
        $campusCode = strtolower($campus?->code ?? ($campus ? str()->slug($campus->name) : 'global'));
        $timestamp = Carbon::now()->format('Y-m-d_His');
        $filename = "backup-sapa-sekolah-{$campusCode}-{$timestamp}.sql.gz";
        $storageDir = 'backups';
        $relativeFilePath = "{$storageDir}/{$filename}";

        if (!Storage::disk('local')->exists($storageDir)) {
            Storage::disk('local')->makeDirectory($storageDir);
        }

        $targetFolderId = GoogleDriveService::getBackupFolderIdForCampus($campus);

        $backup = DatabaseBackup::create([
            'filename' => $filename,
            'disk' => 'local',
            'file_path' => $relativeFilePath,
            'file_size' => 0,
            'campus_id' => $campus?->id,
            'type' => $type,
            'format' => 'sql',
            'status' => 'pending',
            'gdrive_status' => 'pending',
            'gdrive_folder_id' => $targetFolderId,
            'created_by' => $userId ?: auth()->id(),
        ]);

        try {
            $sqlContent = $this->dumpDatabaseToSql($campus);
            $compressedSql = gzencode($sqlContent, 9);

            Storage::disk('local')->put($relativeFilePath, $compressedSql);

            $fileSize = strlen($compressedSql);
            $backup->update([
                'file_size' => $fileSize,
                'status' => 'success',
            ]);

            $campusName = $campus ? $campus->name : 'Seluruh Kampus (Global)';
            ActivityLogService::log(
                'DATABASE_BACKUP_SQL',
                'system',
                $backup->id,
                "Membuat cadangan database SQL ({$type}): {$filename} ({$backup->formatted_size}) untuk {$campusName}",
                ['filename' => $filename, 'campus_id' => $campus?->id, 'format' => 'sql']
            );

            if ($uploadToGdrive) {
                $this->googleDriveService->uploadBackup($backup, $targetFolderId);
            }

            return $backup->fresh();

        } catch (Throwable $e) {
            Log::error('Database SQL Backup Failure: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            $backup->update([
                'status' => 'failed',
                'gdrive_status' => 'failed',
                'error_message' => 'Gagal membuat backup database SQL: ' . $e->getMessage(),
            ]);

            return $backup->fresh();
        }
    }

    /**
     * Buat berkas cadangan database lengkap format Microsoft Excel (.xlsx) terisolasi per kampus.
     */
    public function createExcelBackup(?Campus $campus = null, string $type = 'manual', ?int $userId = null, bool $uploadToGdrive = true): DatabaseBackup
    {
        $campusCode = strtolower($campus?->code ?? ($campus ? str()->slug($campus->name) : 'global'));
        $timestamp = Carbon::now()->format('Y-m-d_His');
        $filename = "backup-data-sapa-sekolah-{$campusCode}-{$timestamp}.xlsx";
        $storageDir = 'backups';
        $relativeFilePath = "{$storageDir}/{$filename}";

        if (!Storage::disk('local')->exists($storageDir)) {
            Storage::disk('local')->makeDirectory($storageDir);
        }

        $targetFolderId = GoogleDriveService::getBackupFolderIdForCampus($campus);

        $backup = DatabaseBackup::create([
            'filename' => $filename,
            'disk' => 'local',
            'file_path' => $relativeFilePath,
            'file_size' => 0,
            'campus_id' => $campus?->id,
            'type' => $type,
            'format' => 'excel',
            'status' => 'pending',
            'gdrive_status' => 'pending',
            'gdrive_folder_id' => $targetFolderId,
            'created_by' => $userId ?: auth()->id(),
        ]);

        try {
            $spreadsheet = new Spreadsheet();
            $spreadsheet->removeSheetByIndex(0); // Hapus sheet default

            $campusName = $campus ? $campus->name : 'Seluruh Kampus (Pusat & Cabang)';
            $campusId = $campus?->id;
            $nowStr = Carbon::now()->translatedFormat('d F Y, H:i:s') . ' WIB';

            // 1. Sheet Master Sekolah
            $this->buildSchoolsExcelSheet($spreadsheet, $campusId, $campusName, $nowStr);

            // 2. Sheet Kunjungan Sekolah
            $this->buildVisitsExcelSheet($spreadsheet, $campusId, $campusName, $nowStr);

            // 3. Sheet Hasil Kuisioner Siswa
            $this->buildQuestionnairesExcelSheet($spreadsheet, $campusId, $campusName, $nowStr);

            // 4. Sheet Follow Up Siswa
            $this->buildFollowUpsExcelSheet($spreadsheet, $campusId, $campusName, $nowStr);

            // 5. Sheet Data Prospek Siswa CRM
            $this->buildProspectsExcelSheet($spreadsheet, $campusId, $campusName, $nowStr);

            // 6. Sheet Mahasiswa Baru (PMB)
            $this->buildNewStudentsExcelSheet($spreadsheet, $campusId, $campusName, $nowStr);

            // 7. Sheet Proposal & Support Event
            $this->buildEventsExcelSheet($spreadsheet, $campusId, $campusName, $nowStr);

            // 8. Sheet Leads Siswa Support Event
            $this->buildEventStudentsExcelSheet($spreadsheet, $campusId, $campusName, $nowStr);

            // 9. Sheet Rekomendasi KOL Siswa
            $this->buildKolExcelSheet($spreadsheet, $campusId, $campusName, $nowStr);

            // 10. Sheet Pengguna & Tim Petugas
            $this->buildUsersExcelSheet($spreadsheet, $campusId, $campusName, $nowStr);

            // 11. Sheet Ringkasan Metadata Cadangan (ditaruh paling depan)
            $this->buildOverviewMetadataSheet($spreadsheet, $campus, $campusName, $nowStr);

            $spreadsheet->setActiveSheetIndex(0);

            // Simpan ke disk lokal
            $writer = new Xlsx($spreadsheet);
            $fullPath = Storage::disk('local')->path($relativeFilePath);
            $writer->save($fullPath);

            $fileSize = file_exists($fullPath) ? filesize($fullPath) : 0;
            $backup->update([
                'file_size' => $fileSize,
                'status' => 'success',
            ]);

            ActivityLogService::log(
                'DATABASE_BACKUP_EXCEL',
                'system',
                $backup->id,
                "Membuat cadangan data Excel ({$type}): {$filename} ({$backup->formatted_size}) untuk {$campusName}",
                ['filename' => $filename, 'campus_id' => $campus?->id, 'format' => 'excel']
            );

            if ($uploadToGdrive) {
                $this->googleDriveService->uploadBackup($backup, $targetFolderId);
            }

            return $backup->fresh();

        } catch (Throwable $e) {
            Log::error('Database Excel Backup Failure: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            $backup->update([
                'status' => 'failed',
                'gdrive_status' => 'failed',
                'error_message' => 'Gagal membuat cadangan data Excel: ' . $e->getMessage(),
            ]);

            return $backup->fresh();
        }
    }

    /**
     * Generate SQL Dump terisolasi per kampus menggunakan PHP PDO Native.
     * Jika $campus diberikan, memfilter secara ketat baris-baris data khusus kampus terkait.
     */
    public function dumpDatabaseToSql(?Campus $campus = null): string
    {
        /** @var PDO $pdo */
        $pdo = DB::connection()->getPdo();
        $dbName = DB::connection()->getDatabaseName();
        $campusId = $campus?->id;

        $output = "-- =====================================================\n";
        $output .= "-- SAPA Sekolah Database Dump\n";
        $output .= "-- Tanggal: " . Carbon::now()->toIso8601String() . "\n";
        $output .= "-- Database: {$dbName}\n";
        if ($campus) {
            $output .= "-- Isolasi Kampus: {$campus->name} (Kode: {$campus->code}, ID: {$campus->id})\n";
            $output .= "-- Keterangan: Data yang diekspor hanya data yang terkait dengan {$campus->name}\n";
        } else {
            $output .= "-- Isolasi Kampus: SELURUH KAMPUS (Global / Pusat)\n";
        }
        $output .= "-- PHP Version: " . PHP_VERSION . "\n";
        $output .= "-- =====================================================\n\n";

        $output .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $output .= "SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";\n";
        $output .= "SET AUTOCOMMIT=0;\n";
        $output .= "START TRANSACTION;\n";
        $output .= "SET time_zone = \"+00:00\";\n\n";

        $tablesStmt = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
        $tables = $tablesStmt->fetchAll(PDO::FETCH_NUM);

        foreach ($tables as $tableRow) {
            $tableName = $tableRow[0];

            // 1. Structure: DROP & CREATE TABLE
            $output .= "\n-- -----------------------------------------------------\n";
            $output .= "-- Struktur Tabel: `{$tableName}`\n";
            $output .= "-- -----------------------------------------------------\n";
            $output .= "DROP TABLE IF EXISTS `{$tableName}`;\n";

            $createStmt = $pdo->query("SHOW CREATE TABLE `{$tableName}`");
            $createRow = $createStmt->fetch(PDO::FETCH_NUM);
            if (!empty($createRow[1])) {
                $output .= $createRow[1] . ";\n\n";
            }

            // 2. Data Dump dengan filter kampus
            $whereClause = '';
            if ($campusId) {
                $condition = match ($tableName) {
                    'campuses' => "`id` = {$campusId}",
                    'schools' => "`campus_id` = {$campusId}",
                    'school_visits' => "(`campus_id` = {$campusId} OR `school_id` IN (SELECT `id` FROM `schools` WHERE `campus_id` = {$campusId}))",
                    'visit_results' => "`visit_id` IN (SELECT `id` FROM `school_visits` WHERE `campus_id` = {$campusId})",
                    'follow_ups' => "(`campus_id` = {$campusId} OR `school_id` IN (SELECT `id` FROM `schools` WHERE `campus_id` = {$campusId}))",
                    'student_questionnaires' => "(`campus_id` = {$campusId} OR `school_id` IN (SELECT `id` FROM `schools` WHERE `campus_id` = {$campusId}))",
                    'prospects' => "(`campus_id` = {$campusId} OR `school_id` IN (SELECT `id` FROM `schools` WHERE `campus_id` = {$campusId}))",
                    'school_event_proposals' => "`campus_id` = {$campusId}",
                    'marketing_routes' => "`campus_id` = {$campusId}",
                    'route_schools' => "`route_id` IN (SELECT `id` FROM `marketing_routes` WHERE `campus_id` = {$campusId})",
                    'study_programs' => "(`campus_id` = {$campusId} OR `campus_id` IS NULL)",
                    'users' => "(`campus_id` = {$campusId} OR `campus_id` IS NULL)",
                    'school_contacts' => "`school_id` IN (SELECT `id` FROM `schools` WHERE `campus_id` = {$campusId})",
                    'school_potentials' => "`school_id` IN (SELECT `id` FROM `schools` WHERE `campus_id` = {$campusId})",
                    'school_scores' => "`school_id` IN (SELECT `id` FROM `schools` WHERE `campus_id` = {$campusId})",
                    'school_student_intakes' => "`school_id` IN (SELECT `id` FROM `schools` WHERE `campus_id` = {$campusId})",
                    'visit_documents' => "`visit_id` IN (SELECT `id` FROM `school_visits` WHERE `campus_id` = {$campusId})",
                    'database_backups' => "`campus_id` = {$campusId}",
                    // Reference master data & brosur: dump semua
                    'roles', 'provinces', 'regencies', 'districts', 'villages', 'school_types', 'accreditations', 'scoring_criteria', 'questionnaire_questions', 'academic_periods', 'marketing_activities', 'e_brochures' => null,
                    default => Schema::hasColumn($tableName, 'campus_id') ? "`campus_id` = {$campusId}" : null,
                };

                if ($condition !== null) {
                    $whereClause = " WHERE {$condition}";
                }
            }

            $countStmt = $pdo->query("SELECT COUNT(*) FROM `{$tableName}`{$whereClause}");
            $rowCount = (int)$countStmt->fetchColumn();

            if ($rowCount > 0) {
                $output .= "-- Data untuk Tabel: `{$tableName}` ({$rowCount} baris" . ($whereClause ? " • Filter Kampus" : '') . ")\n";

                $chunkSize = 300;
                $offset = 0;

                while ($offset < $rowCount) {
                    $rowsStmt = $pdo->query("SELECT * FROM `{$tableName}`{$whereClause} LIMIT {$chunkSize} OFFSET {$offset}");
                    $rows = $rowsStmt->fetchAll(PDO::FETCH_ASSOC);

                    if (!empty($rows)) {
                        $firstRow = $rows[0];
                        $columns = array_keys($firstRow);
                        $escapedColumns = array_map(fn($col) => "`{$col}`", $columns);
                        $columnList = implode(', ', $escapedColumns);

                        $valuesSql = [];
                        foreach ($rows as $row) {
                            $valueParts = [];
                            foreach ($columns as $col) {
                                $val = $row[$col];
                                if ($val === null) {
                                    $valueParts[] = 'NULL';
                                } elseif (is_numeric($val) && !is_string($val)) {
                                    $valueParts[] = $val;
                                } else {
                                    $valueParts[] = $pdo->quote((string)$val);
                                }
                            }
                            $valuesSql[] = '(' . implode(', ', $valueParts) . ')';
                        }

                        $output .= "INSERT INTO `{$tableName}` ({$columnList}) VALUES\n" . implode(",\n", $valuesSql) . ";\n";
                    }

                    $offset += $chunkSize;
                }
                $output .= "\n";
            }
        }

        $output .= "SET FOREIGN_KEY_CHECKS=1;\n";
        $output .= "COMMIT;\n";
        $output .= "\n-- Dump selesai.\n";

        return $output;
    }

    /**
     * Hapus berkas cadangan lokal dan record di database.
     */
    public function deleteBackup(DatabaseBackup $backup): bool
    {
        if ($backup->existsLocally()) {
            Storage::disk($backup->disk)->delete($backup->file_path);
        }

        $filename = $backup->filename;
        $deleted = $backup->delete();

        ActivityLogService::log(
            'DELETE_BACKUP',
            'system',
            null,
            "Menghapus berkas cadangan database: {$filename}"
        );

        return (bool)$deleted;
    }

    /**
     * Dapatkan daftar berkas backup yang relevan untuk user (memperhatikan isolasi kampus).
     */
    public function getBackupsForUser(?User $user = null, ?int $targetCampusId = null)
    {
        $user = $user ?? auth()->user();
        $query = DatabaseBackup::with(['campus', 'creator'])->latest();

        if ($user && !AppContextService::canSwitchCampus($user)) {
            $userCampusId = $user->campus_id ?? AppContextService::getActiveCampus()?->id;
            $query->where('campus_id', $userCampusId);
        } elseif ($targetCampusId && $targetCampusId !== 'ALL') {
            $query->where('campus_id', $targetCampusId);
        }

        return $query;
    }

    // =========================================================================
    // BUILDER SHEET EXCEL LENGKAP UNTUK CADANGAN DATA
    // =========================================================================

    protected function buildOverviewMetadataSheet(Spreadsheet $spreadsheet, ?Campus $campus, string $campusName, string $nowStr): void
    {
        $sheet = $spreadsheet->createSheet(0);
        $sheet->setTitle('Ringkasan Cadangan');
        $sheet->setShowGridLines(true);

        $sheet->setCellValue('A1', 'SAPA SEKOLAH — COMMAND CENTER');
        $sheet->setCellValue('A2', 'BERKAS CADANGAN DATABASE & DATA MASTER RESMI');
        $sheet->setCellValue('A3', "KAMPUS: " . strtoupper($campusName));
        $sheet->setCellValue('A4', "WAKTU CADANGAN DIBUAT: {$nowStr}");

        $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true)->getColor()->setRGB('0B023E');
        $sheet->getStyle('A2')->getFont()->setSize(12)->setBold(true)->getColor()->setRGB('DF0B2B');
        $sheet->getStyle('A3')->getFont()->setSize(11)->setBold(true)->getColor()->setRGB('1E293B');
        $sheet->getStyle('A4')->getFont()->setSize(10)->setItalic(true)->getColor()->setRGB('64748B');

        $campusId = $campus?->id;
        $stats = [
            ['Data Master Sekolah', School::when($campusId, fn($q) => $q->where('campus_id', $campusId))->count()],
            ['Kunjungan Sekolah (Visit)', SchoolVisit::when($campusId, fn($q) => $q->where('campus_id', $campusId))->count()],
            ['Hasil Kuisioner Siswa', StudentQuestionnaire::when($campusId, fn($q) => $q->where('campus_id', $campusId))->count()],
            ['Siswa Berminat Beasiswa (Ya)', StudentQuestionnaire::when($campusId, fn($q) => $q->where('campus_id', $campusId))->where('scholarship_interest', 'ya')->count()],
            ['Follow Up Leads Kuisioner', StudentQuestionnaire::when($campusId, fn($q) => $q->where('campus_id', $campusId))->whereNotNull('assigned_to')->count()],
            ['Data Prospek CRM', Prospect::when($campusId, fn($q) => $q->where('campus_id', $campusId))->count()],
            ['Mahasiswa Baru (PMB Closing)', Prospect::when($campusId, fn($q) => $q->where('campus_id', $campusId))->where('status', Prospect::STATUS_MAHASISWA)->count()],
            ['Proposal & Kegiatan Event', SchoolEventProposal::when($campusId, fn($q) => $q->where('campus_id', $campusId))->count()],
            ['Leads Siswa Support Event', StudentQuestionnaire::when($campusId, fn($q) => $q->where('campus_id', $campusId))->where('source', 'support_event')->count()],
            ['Rekomendasi KOL Siswa', StudentQuestionnaire::when($campusId, fn($q) => $q->where('campus_id', $campusId))->where(fn($q) => $q->where('instagram_followers', '>=', 1500)->orWhere('tiktok_followers', '>=', 1500)->orWhere('is_kol', true))->count()],
            ['Petugas & Pengguna Kampus', User::when($campusId, fn($q) => $q->where('campus_id', $campusId))->count()],
        ];

        $sheet->setCellValue('A6', 'MODUL DATA');
        $sheet->setCellValue('B6', 'TOTAL BARIS DATA');
        $sheet->getStyle('A6:B6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0B023E']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $r = 7;
        foreach ($stats as $item) {
            $sheet->setCellValue("A{$r}", $item[0]);
            $sheet->setCellValue("B{$r}", $item[1]);
            $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $r++;
        }

        $sheet->getStyle("A6:B" . ($r - 1))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ]);

        $sheet->getColumnDimension('A')->setWidth(35);
        $sheet->getColumnDimension('B')->setWidth(22);
    }

    protected function buildSchoolsExcelSheet(Spreadsheet $spreadsheet, ?int $campusId, string $campusName, string $nowStr): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Master Sekolah');
        $sheet->setShowGridLines(true);

        $headers = [
            'No', 'NPSN', 'Nama Sekolah', 'Status', 'Akreditasi', 'Kecamatan', 'Kabupaten/Kota',
            'Alamat Lengkap', 'Jumlah Siswa Kelas 12', 'Potensi', 'Jarak Kampus (KM)', 'No Telepon', 'Kampus'
        ];
        $this->applyHeaderTitle($sheet, 'DATA MASTER SEKOLAH', $campusName, $nowStr, $headers);

        $schools = School::with(['district.regency', 'accreditation', 'campus'])
            ->when($campusId, fn($q) => $q->where('campus_id', $campusId))
            ->orderBy('name')
            ->get();

        $row = 5;
        $no = 1;
        foreach ($schools as $s) {
            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValueExplicit("B{$row}", (string)$s->npsn, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("C{$row}", $s->name);
            $sheet->setCellValue("D{$row}", strtoupper($s->status ?? '-'));
            $sheet->setCellValue("E{$row}", $s->accreditation?->name ?? ($s->accreditation_grade ?? '-'));
            $sheet->setCellValue("F{$row}", $s->district?->name ?? '-');
            $sheet->setCellValue("G{$row}", $s->district?->regency?->name ?? '-');
            $sheet->setCellValue("H{$row}", $s->address ?? '-');
            $sheet->setCellValue("I{$row}", $s->total_students ?? 0);
            $sheet->setCellValue("J{$row}", $s->potential_category ?? '-');
            $sheet->setCellValue("K{$row}", $s->distance_from_campus ? round($s->distance_from_campus, 1) : '-');
            $sheet->setCellValueExplicit("L{$row}", (string)($s->phone ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("M{$row}", $s->campus?->name ?? 'Global');
            $row++;
        }

        $this->finalizeSheetTable($sheet, 4, $row - 1, count($headers));
    }

    protected function buildVisitsExcelSheet(Spreadsheet $spreadsheet, ?int $campusId, string $campusName, string $nowStr): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Kunjungan Sekolah');
        $sheet->setShowGridLines(true);

        $headers = ['No', 'Tanggal Kunjungan', 'Nama Sekolah', 'Kecamatan', 'Petugas Lapangan', 'Tujuan Kunjungan', 'Hasil Kunjungan', 'Status Verifikasi', 'Catatan'];
        $this->applyHeaderTitle($sheet, 'RIWAYAT KUNJUNGAN SEKOLAH (VISIT)', $campusName, $nowStr, $headers);

        $visits = SchoolVisit::with(['school.district', 'user', 'campus'])
            ->when($campusId, fn($q) => $q->where('campus_id', $campusId)->orWhereHas('school', fn($sq) => $sq->where('campus_id', $campusId)))
            ->latest('visit_date')
            ->get();

        $row = 5;
        $no = 1;
        foreach ($visits as $v) {
            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValue("B{$row}", $v->visit_date ? Carbon::parse($v->visit_date)->format('d/m/Y') : '-');
            $sheet->setCellValue("C{$row}", $v->school?->name ?? '-');
            $sheet->setCellValue("D{$row}", $v->school?->district?->name ?? '-');
            $sheet->setCellValue("E{$row}", $v->user?->name ?? '-');
            $sheet->setCellValue("F{$row}", $v->visit_purpose ?? '-');
            $sheet->setCellValue("G{$row}", $v->visit_result ?? '-');
            $sheet->setCellValue("H{$row}", ucfirst($v->status ?? '-'));
            $sheet->setCellValue("I{$row}", $v->notes ?? '-');
            $row++;
        }

        $this->finalizeSheetTable($sheet, 4, $row - 1, count($headers));
    }

    protected function buildQuestionnairesExcelSheet(Spreadsheet $spreadsheet, ?int $campusId, string $campusName, string $nowStr): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Hasil Kuisioner');
        $sheet->setShowGridLines(true);

        $headers = [
            'No', 'Tanggal Isi', 'Nama Siswa', 'Jenis Kelamin', 'Asal Sekolah', 'Kelas', 'Jurusan',
            'No WhatsApp', 'Minat Kuliah', 'Minat Beasiswa', 'Pilihan Prodi', 'Nilai Potensi', 'Status Follow Up', 'Petugas'
        ];
        $this->applyHeaderTitle($sheet, 'HASIL KUISIONER SISWA', $campusName, $nowStr, $headers);

        $qList = StudentQuestionnaire::with(['school', 'assignedUser', 'campus'])
            ->when($campusId, fn($q) => $q->where('campus_id', $campusId)->orWhereHas('school', fn($sq) => $sq->where('campus_id', $campusId)))
            ->latest()
            ->get();

        $row = 5;
        $no = 1;
        foreach ($qList as $item) {
            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValue("B{$row}", $item->created_at ? $item->created_at->format('d/m/Y') : '-');
            $sheet->setCellValue("C{$row}", $item->student_name);
            $sheet->setCellValue("D{$row}", $item->gender ?? '-');
            $sheet->setCellValue("E{$row}", $item->school?->name ?? ($item->school_name ?? '-'));
            $sheet->setCellValue("F{$row}", $item->class_name ?? '-');
            $sheet->setCellValue("G{$row}", $item->major_name ?? '-');
            $sheet->setCellValueExplicit("H{$row}", (string)($item->whatsapp ?? $item->student_phone ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("I{$row}", ucfirst($item->college_interest ?? '-'));
            $sheet->setCellValue("J{$row}", strtoupper($item->scholarship_interest ?? '-'));
            $sheet->setCellValue("K{$row}", $item->study_program_interest ?? '-');
            $sheet->setCellValue("L{$row}", $item->score ?? '-');
            $sheet->setCellValue("M{$row}", $item->followup_status ?? 'Belum Dihubungi');
            $sheet->setCellValue("N{$row}", $item->assignedUser?->name ?? 'Belum Diplot');
            $row++;
        }

        $this->finalizeSheetTable($sheet, 4, $row - 1, count($headers));
    }

    protected function buildFollowUpsExcelSheet(Spreadsheet $spreadsheet, ?int $campusId, string $campusName, string $nowStr): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Follow Up Siswa');
        $sheet->setShowGridLines(true);

        $headers = ['No', 'Nama Siswa', 'Asal Sekolah', 'No WhatsApp', 'Petugas Follow Up', 'Status Respon', 'Minat Beasiswa', 'Catatan Interaksi'];
        $this->applyHeaderTitle($sheet, 'DATA FOLLOW UP SISWA LEADS', $campusName, $nowStr, $headers);

        $fuList = StudentQuestionnaire::with(['school', 'assignedUser'])
            ->when($campusId, fn($q) => $q->where('campus_id', $campusId)->orWhereHas('school', fn($sq) => $sq->where('campus_id', $campusId)))
            ->whereNotNull('assigned_to')
            ->latest('updated_at')
            ->get();

        $row = 5;
        $no = 1;
        foreach ($fuList as $fu) {
            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValue("C{$row}", $fu->school?->name ?? '-');
            $sheet->setCellValue("B{$row}", $fu->student_name);
            $sheet->setCellValueExplicit("D{$row}", (string)($fu->whatsapp ?? $fu->student_phone ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("E{$row}", $fu->assignedUser?->name ?? 'Belum Diplot');
            $sheet->setCellValue("F{$row}", ucwords(str_replace('_', ' ', $fu->followup_status ?? 'belum dihubungi')));
            $sheet->setCellValue("G{$row}", strtoupper($fu->scholarship_interest ?? '-'));
            $sheet->setCellValue("H{$row}", $fu->followup_notes ?? '-');
            $row++;
        }

        $this->finalizeSheetTable($sheet, 4, $row - 1, count($headers));
    }

    protected function buildProspectsExcelSheet(Spreadsheet $spreadsheet, ?int $campusId, string $campusName, string $nowStr): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Prospek CRM');
        $sheet->setShowGridLines(true);

        $headers = ['No', 'Tanggal Input', 'Nama Prospek', 'Asal Sekolah', 'No WhatsApp', 'Jurusan Pilihan', 'Waktu Kuliah', 'Beasiswa', 'Status Prospek', 'Marketing'];
        $this->applyHeaderTitle($sheet, 'DATA PROSPEK SISWA (LEADS CRM)', $campusName, $nowStr, $headers);

        $prospects = Prospect::with(['school', 'marketingUser', 'studyProgram'])
            ->when($campusId, fn($q) => $q->where('campus_id', $campusId)->orWhereHas('school', fn($sq) => $sq->where('campus_id', $campusId)))
            ->latest()
            ->get();

        $row = 5;
        $no = 1;
        foreach ($prospects as $p) {
            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValue("B{$row}", $p->created_at ? $p->created_at->format('d/m/Y') : '-');
            $sheet->setCellValue("C{$row}", $p->student_name);
            $sheet->setCellValue("D{$row}", $p->school?->name ?? ($p->school_name ?? '-'));
            $sheet->setCellValueExplicit("E{$row}", (string)($p->whatsapp ?? $p->phone ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("F{$row}", $p->studyProgram?->name ?? '-');
            $sheet->setCellValue("G{$row}", ucfirst($p->study_time ?? '-'));
            $sheet->setCellValue("H{$row}", $p->scholarship_type ?? '-');
            $sheet->setCellValue("I{$row}", ucfirst($p->status ?? 'Prospek'));
            $sheet->setCellValue("J{$row}", $p->marketingUser?->name ?? '-');
            $row++;
        }

        $this->finalizeSheetTable($sheet, 4, $row - 1, count($headers));
    }

    protected function buildNewStudentsExcelSheet(Spreadsheet $spreadsheet, ?int $campusId, string $campusName, string $nowStr): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Mahasiswa Baru');
        $sheet->setShowGridLines(true);

        $headers = ['No', 'Tanggal Validasi', 'NIM / No Daftar', 'Nama Lengkap', 'Asal Sekolah', 'No WhatsApp', 'Program Studi', 'Divalidasi Oleh'];
        $this->applyHeaderTitle($sheet, 'DATA MAHASISWA BARU (PMB CLOSING)', $campusName, $nowStr, $headers);

        $mhs = Prospect::with(['school', 'validatedBy', 'studyProgram'])
            ->where('status', Prospect::STATUS_MAHASISWA)
            ->when($campusId, fn($q) => $q->where('campus_id', $campusId)->orWhereHas('school', fn($sq) => $sq->where('campus_id', $campusId)))
            ->latest('validated_at')
            ->get();

        $row = 5;
        $no = 1;
        foreach ($mhs as $m) {
            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValue("B{$row}", $m->validated_at ? Carbon::parse($m->validated_at)->format('d/m/Y') : '-');
            $sheet->setCellValueExplicit("C{$row}", (string)($m->nim ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("D{$row}", $m->student_name);
            $sheet->setCellValue("E{$row}", $m->school?->name ?? '-');
            $sheet->setCellValueExplicit("F{$row}", (string)($m->whatsapp ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("G{$row}", $m->studyProgram?->name ?? '-');
            $sheet->setCellValue("H{$row}", $m->validatedBy?->name ?? '-');
            $row++;
        }

        $this->finalizeSheetTable($sheet, 4, $row - 1, count($headers));
    }

    protected function buildEventsExcelSheet(Spreadsheet $spreadsheet, ?int $campusId, string $campusName, string $nowStr): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Support Event');
        $sheet->setShowGridLines(true);

        $headers = ['No', 'Nama Event / Proposal', 'Asal Sekolah', 'Tanggal Mulai', 'Tanggal Selesai', 'Jenis Event', 'Target Peserta', 'PIC / Petugas', 'Status'];
        $this->applyHeaderTitle($sheet, 'PROPOSAL & KEGIATAN SUPPORT EVENT', $campusName, $nowStr, $headers);

        $events = SchoolEventProposal::with(['school', 'user'])
            ->when($campusId, fn($q) => $q->where('campus_id', $campusId))
            ->latest()
            ->get();

        $row = 5;
        $no = 1;
        foreach ($events as $ev) {
            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValue("B{$row}", $ev->event_name);
            $sheet->setCellValue("C{$row}", $ev->school?->name ?? '-');
            $sheet->setCellValue("D{$row}", $ev->start_date ? Carbon::parse($ev->start_date)->format('d/m/Y') : '-');
            $sheet->setCellValue("E{$row}", $ev->end_date ? Carbon::parse($ev->end_date)->format('d/m/Y') : '-');
            $sheet->setCellValue("F{$row}", $ev->event_type ?? '-');
            $sheet->setCellValue("G{$row}", $ev->target_audience ?? '-');
            $sheet->setCellValue("H{$row}", $ev->user?->name ?? '-');
            $sheet->setCellValue("I{$row}", ucfirst($ev->status ?? '-'));
            $row++;
        }

        $this->finalizeSheetTable($sheet, 4, $row - 1, count($headers));
    }

    protected function buildEventStudentsExcelSheet(Spreadsheet $spreadsheet, ?int $campusId, string $campusName, string $nowStr): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Leads Siswa Event');
        $sheet->setShowGridLines(true);

        $headers = ['No', 'Tanggal Input', 'Nama Siswa', 'Event Terkait', 'Sekolah', 'Kelas', 'No WhatsApp', 'Minat Kuliah', 'Petugas Follow Up'];
        $this->applyHeaderTitle($sheet, 'DATA SISWA HASIL SUPPORT EVENT', $campusName, $nowStr, $headers);

        $leads = StudentQuestionnaire::with(['school', 'eventProposal', 'assignedUser'])
            ->where(function ($q) {
                $q->where('source', 'support_event')
                    ->orWhereNotNull('school_event_proposal_id')
                    ->orWhere('college_interest_notes', 'like', '%Support Event%');
            })
            ->when($campusId, fn($q) => $q->where('campus_id', $campusId)->orWhereHas('school', fn($sq) => $sq->where('campus_id', $campusId)))
            ->latest()
            ->get();

        $row = 5;
        $no = 1;
        foreach ($leads as $lead) {
            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValue("B{$row}", $lead->created_at ? $lead->created_at->format('d/m/Y') : '-');
            $sheet->setCellValue("C{$row}", $lead->student_name);
            $sheet->setCellValue("D{$row}", $lead->eventProposal?->event_name ?? 'Support Event');
            $sheet->setCellValue("E{$row}", $lead->school?->name ?? ($lead->school_name ?? '-'));
            $sheet->setCellValue("F{$row}", $lead->class_name ?? '-');
            $sheet->setCellValueExplicit("G{$row}", (string)($lead->whatsapp ?? $lead->student_phone ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("H{$row}", ucfirst($lead->college_interest ?? '-'));
            $sheet->setCellValue("I{$row}", $lead->assignedUser?->name ?? 'Belum Diplot');
            $row++;
        }

        $this->finalizeSheetTable($sheet, 4, $row - 1, count($headers));
    }

    protected function buildKolExcelSheet(Spreadsheet $spreadsheet, ?int $campusId, string $campusName, string $nowStr): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Rekomendasi KOL');
        $sheet->setShowGridLines(true);

        $headers = ['No', 'Nama Siswa', 'Asal Sekolah', 'Akun Instagram', 'Jumlah Follower IG', 'Status Rekomendasi KOL', 'No WhatsApp'];
        $this->applyHeaderTitle($sheet, 'REKOMENDASI KEY OPINION LEADER (KOL)', $campusName, $nowStr, $headers);

        $minFollowers = StudentQuestionnaire::getKolMinFollowers();

        $kols = StudentQuestionnaire::with('school')
            ->where(function ($q) use ($minFollowers) {
                $q->where('instagram_followers', '>=', $minFollowers)
                    ->orWhere('tiktok_followers', '>=', $minFollowers)
                    ->orWhere('is_kol', true);
            })
            ->when($campusId, fn($q) => $q->where('campus_id', $campusId)->orWhereHas('school', fn($sq) => $sq->where('campus_id', $campusId)))
            ->orderByDesc('instagram_followers')
            ->get();

        $row = 5;
        $no = 1;
        foreach ($kols as $kol) {
            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValue("B{$row}", $kol->student_name);
            $sheet->setCellValue("C{$row}", $kol->school?->name ?? '-');
            $sheet->setCellValue("D{$row}", $kol->instagram_handle ? '@' . ltrim($kol->instagram_handle, '@') : ($kol->tiktok_handle ? '@' . ltrim($kol->tiktok_handle, '@') : '-'));
            $sheet->setCellValue("E{$row}", (int)max($kol->instagram_followers ?? 0, $kol->tiktok_followers ?? 0));
            $sheet->setCellValue("F{$row}", $kol->kol_status ? ucfirst($kol->kol_status) : ($kol->is_kol ? 'Kandidat KOL' : 'Follower Memenuhi'));
            $sheet->setCellValueExplicit("G{$row}", (string)($kol->student_phone ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $row++;
        }

        $this->finalizeSheetTable($sheet, 4, $row - 1, count($headers));
    }

    protected function buildUsersExcelSheet(Spreadsheet $spreadsheet, ?int $campusId, string $campusName, string $nowStr): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Pengguna & Tim Petugas');
        $sheet->setShowGridLines(true);

        $headers = ['No', 'Nama Petugas', 'Alamat Email', 'Role / Jabatan', 'Kampus', 'Status Akun'];
        $this->applyHeaderTitle($sheet, 'TIM OPERASIONAL & PENGGUNA SISTEM', $campusName, $nowStr, $headers);

        $users = User::with(['role', 'campus'])
            ->when($campusId, fn($q) => $q->where('campus_id', $campusId))
            ->orderBy('name')
            ->get();

        $row = 5;
        $no = 1;
        foreach ($users as $u) {
            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValue("B{$row}", $u->name);
            $sheet->setCellValue("C{$row}", $u->email);
            $sheet->setCellValue("D{$row}", $u->role?->name ?? '-');
            $sheet->setCellValue("E{$row}", $u->campus?->name ?? 'Pusat / Global');
            $sheet->setCellValue("F{$row}", ucfirst($u->status ?? 'Aktif'));
            $row++;
        }

        $this->finalizeSheetTable($sheet, 4, $row - 1, count($headers));
    }

    protected function applyHeaderTitle($sheet, string $title, string $campusName, string $nowStr, array $headers): void
    {
        $lastCol = Coordinate::stringFromColumnIndex(count($headers));

        $sheet->setCellValue('A1', 'SAPA SEKOLAH — ' . $title);
        $sheet->setCellValue('A2', 'KAMPUS: ' . strtoupper($campusName) . ' • TANGGAL CADANGAN: ' . $nowStr);

        $sheet->getStyle('A1')->getFont()->setSize(13)->setBold(true)->getColor()->setRGB('0B023E');
        $sheet->getStyle('A2')->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('64748B');

        $col = 1;
        foreach ($headers as $h) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . '4', $h);
            $col++;
        }

        $sheet->getStyle("A4:{$lastCol}4")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0B023E']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(26);
    }

    protected function finalizeSheetTable($sheet, int $startRow, int $endRow, int $totalCols): void
    {
        $lastCol = Coordinate::stringFromColumnIndex($totalCols);
        $safeEndRow = max($endRow, $startRow);

        $sheet->getStyle("A{$startRow}:{$lastCol}{$safeEndRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ]);

        for ($c = 1; $c <= $totalCols; $c++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
        }
    }
}
