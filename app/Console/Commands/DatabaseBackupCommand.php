<?php

namespace App\Console\Commands;

use App\Models\Campus;
use App\Services\DatabaseBackupService;
use App\Services\GoogleDriveService;
use Illuminate\Console\Command;

class DatabaseBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup 
                            {--campus= : ID atau kode kampus (CKP, KRW, atau ALL)} 
                            {--upload-gdrive : Upload berkas backup ke Google Drive folder masing-masing kampus} 
                            {--format=both : Format cadangan (both, excel, atau sql)} 
                            {--type=scheduled : Tipe backup (manual atau scheduled)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cadangkan database SAPA Sekolah (SQL & Excel terisolasi kampus) dan sinkronisasikan ke Google Drive';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseBackupService $backupService): int
    {
        $this->info('Memulai proses backup database SAPA Sekolah...');

        $campusOption = $this->option('campus') ?: 'ALL';
        $uploadGdrive = (bool)$this->option('upload-gdrive');
        $format = $this->option('format') ?: 'both';
        $type = $this->option('type') ?: 'scheduled';

        $campuses = [];

        if (strtoupper($campusOption) === 'ALL') {
            $campuses = Campus::where('is_active', true)->get();
            if ($campuses->isEmpty()) {
                $campuses = [null];
            }
        } elseif (is_numeric($campusOption)) {
            $campus = Campus::find($campusOption);
            if (!$campus) {
                $this->error("Kampus dengan ID {$campusOption} tidak ditemukan.");
                return Command::FAILURE;
            }
            $campuses = [$campus];
        } else {
            $code = strtoupper($campusOption);
            $campus = Campus::where('code', $code)->first();
            if (!$campus) {
                $campus = Campus::where('name', 'like', "%{$campusOption}%")->first();
            }
            if (!$campus) {
                $this->error("Kampus dengan kode/nama '{$campusOption}' tidak ditemukan.");
                return Command::FAILURE;
            }
            $campuses = [$campus];
        }

        $results = [];

        foreach ($campuses as $campus) {
            $campusName = $campus ? $campus->name : 'Global / Pusat';
            $this->line("\n[>] Memproses cadangan untuk: <comment>{$campusName}</comment> (Format: {$format})...");

            $backupOutput = $backupService->createBackup(
                campusId: $campus?->id,
                type: $type,
                userId: null,
                uploadToGdrive: $uploadGdrive,
                format: $format
            );

            $backupsList = is_array($backupOutput) ? array_values($backupOutput) : [$backupOutput];
            $gdriveFolderId = GoogleDriveService::getBackupFolderIdForCampus($campus);

            foreach ($backupsList as $backup) {
                $results[] = [
                    'Kampus' => $campusName,
                    'Format' => strtoupper($backup->format ?? 'SQL'),
                    'File' => $backup->filename,
                    'Ukuran' => $backup->formatted_size,
                    'Status Lokal' => $backup->status,
                    'Status GDrive' => $backup->gdrive_status,
                    'GDrive Folder' => $gdriveFolderId,
                ];

                if ($backup->status === 'success') {
                    $this->info("   [OK] File dibuat: {$backup->filename} ({$backup->formatted_size})");
                    if ($uploadGdrive) {
                        if ($backup->gdrive_status === 'uploaded') {
                            $this->info("   [OK] Terunggah ke Google Drive Folder: {$gdriveFolderId}");
                        } else {
                            $this->warn("   [!] GDrive status: {$backup->gdrive_status} (" . ($backup->error_message ?: 'Belum diatur') . ')');
                        }
                    }
                } else {
                    $this->error("   [FAIL] Gagal: {$backup->error_message}");
                }
            }
        }

        $this->newLine();
        $this->table(['Kampus', 'Format', 'File', 'Ukuran', 'Status Lokal', 'Status GDrive', 'GDrive Folder'], $results);
        $this->info('Proses cadangan database selesai.');

        return Command::SUCCESS;
    }
}
