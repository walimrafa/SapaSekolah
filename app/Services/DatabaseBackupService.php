<?php

namespace App\Services;

use App\Models\Campus;
use App\Models\DatabaseBackup;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PDO;
use Throwable;

class DatabaseBackupService
{
    protected GoogleDriveService $googleDriveService;

    public function __construct(GoogleDriveService $googleDriveService)
    {
        $this->googleDriveService = $googleDriveService;
    }

    /**
     * Buat berkas cadangan database (Full Dump .sql.gz) dan sinkronisasikan ke Google Drive.
     *
     * @param int|null $campusId Kampus target (UBSI Karawang / UBSI Cikampek / null untuk Pusat)
     * @param string $type 'manual' atau 'scheduled'
     * @param int|null $userId User yang memicu backup
     * @param bool $uploadToGdrive Apakah langsung diunggah ke Google Drive
     */
    public function createBackup(?int $campusId = null, string $type = 'manual', ?int $userId = null, bool $uploadToGdrive = true): DatabaseBackup
    {
        $campus = $campusId ? Campus::find($campusId) : null;
        $campusCode = strtolower($campus?->code ?? ($campus ? str()->slug($campus->name) : 'global'));
        $timestamp = Carbon::now()->format('Y-m-d_His');
        $filename = "backup-sapa-sekolah-{$campusCode}-{$timestamp}.sql.gz";
        $storageDir = 'backups';
        $relativeFilePath = "{$storageDir}/{$filename}";

        // Pastikan direktori backups ada
        if (!Storage::disk('local')->exists($storageDir)) {
            Storage::disk('local')->makeDirectory($storageDir);
        }

        $targetFolderId = GoogleDriveService::getBackupFolderIdForCampus($campus);

        // Buat record backup dengan status pending
        $backup = DatabaseBackup::create([
            'filename' => $filename,
            'disk' => 'local',
            'file_path' => $relativeFilePath,
            'file_size' => 0,
            'campus_id' => $campus?->id,
            'type' => $type,
            'status' => 'pending',
            'gdrive_status' => 'pending',
            'gdrive_folder_id' => $targetFolderId,
            'created_by' => $userId ?: auth()->id(),
        ]);

        try {
            // Generate SQL dump menggunakan PDO Streaming Dumper
            $sqlContent = $this->dumpDatabaseToSql($campus);

            // Kompresi menggunakan gzip untuk menghemat 90%+ ukuran disk dan transfer network
            $compressedSql = gzencode($sqlContent, 9);

            Storage::disk('local')->put($relativeFilePath, $compressedSql);

            $fileSize = strlen($compressedSql);
            $backup->update([
                'file_size' => $fileSize,
                'status' => 'success',
            ]);

            // Catat log aktivitas sistem
            $campusName = $campus ? $campus->name : 'Seluruh Database (Pusat)';
            ActivityLogService::log(
                'DATABASE_BACKUP',
                'system',
                $backup->id,
                "Membuat cadangan database ({$type}): {$filename} ({$backup->formatted_size}) untuk {$campusName}",
                ['filename' => $filename, 'campus_id' => $campus?->id, 'type' => $type]
            );

            // Upload ke Google Drive jika diminta
            if ($uploadToGdrive) {
                $this->googleDriveService->uploadBackup($backup, $targetFolderId);
            }

            return $backup->fresh();

        } catch (Throwable $e) {
            Log::error('Database Backup Failure: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            $backup->update([
                'status' => 'failed',
                'gdrive_status' => 'failed',
                'error_message' => 'Gagal membuat backup database: ' . $e->getMessage(),
            ]);

            return $backup->fresh();
        }
    }

    /**
     * Generate SQL Dump lengkap menggunakan PHP PDO Native.
     * Bekerja 100% andal di Windows, Laragon, Docker, Linux, VPS, cPanel tanpa memerlukan executable mysqldump terpasang.
     */
    public function dumpDatabaseToSql(?Campus $campus = null): string
    {
        /** @var PDO $pdo */
        $pdo = DB::connection()->getPdo();
        $dbName = DB::connection()->getDatabaseName();

        $output = "-- =====================================================\n";
        $output .= "-- SAPA Sekolah Database Dump\n";
        $output .= "-- Tanggal: " . Carbon::now()->toIso8601String() . "\n";
        $output .= "-- Database: {$dbName}\n";
        if ($campus) {
            $output .= "-- Kampus Konteks: {$campus->name} ({$campus->code})\n";
        }
        $output .= "-- PHP Version: " . PHP_VERSION . "\n";
        $output .= "-- =====================================================\n\n";

        $output .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $output .= "SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";\n";
        $output .= "SET AUTOCOMMIT=0;\n";
        $output .= "START TRANSACTION;\n";
        $output .= "SET time_zone = \"+00:00\";\n\n";

        // Ambil seluruh tabel dasar (abaikan VIEW jika ada)
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

            // 2. Data Dump: INSERT INTO
            $countStmt = $pdo->query("SELECT COUNT(*) FROM `{$tableName}`");
            $rowCount = (int)$countStmt->fetchColumn();

            if ($rowCount > 0) {
                $output .= "-- Data untuk Tabel: `{$tableName}` ({$rowCount} baris)\n";

                // Chunk query untuk menghindari kehabisan memory pada tabel besar
                $chunkSize = 300;
                $offset = 0;

                while ($offset < $rowCount) {
                    $rowsStmt = $pdo->query("SELECT * FROM `{$tableName}` LIMIT {$chunkSize} OFFSET {$offset}");
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
     * Hapus berkas cadangan lokal dan datanya di database.
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

        // Isolasi Kampus Cabang: Pimpinan & Koordinator Cikampek / Karawang hanya melihat backup kampusnya
        if ($user && !AppContextService::canSwitchCampus($user)) {
            $userCampusId = $user->campus_id ?? AppContextService::getActiveCampus()?->id;
            $query->where('campus_id', $userCampusId);
        } elseif ($targetCampusId && $targetCampusId !== 'ALL') {
            $query->where('campus_id', $targetCampusId);
        }

        return $query;
    }
}
