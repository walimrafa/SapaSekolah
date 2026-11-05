<?php

namespace App\Services;

use App\Models\Campus;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GoogleDriveService
{
    // Folder Google Drive UBSI Kampus Karawang (Default)
    public const KARAWANG_FOLDER_ID = '1DQkSEomL8cacePKG8Ex72JldQABKndiD';
    public const KARAWANG_FOLDER_URL = 'https://drive.google.com/drive/folders/1DQkSEomL8cacePKG8Ex72JldQABKndiD?usp=sharing';

    // Folder Google Drive UBSI Kampus Cikampek (Dipisah sesuai instruksi pimpinan)
    public const CIKAMPEK_FOLDER_ID = '1VpGirvZZ1s2mR3ooMc_s5ZmAquitC0tA';
    public const CIKAMPEK_FOLDER_URL = 'https://drive.google.com/drive/folders/1VpGirvZZ1s2mR3ooMc_s5ZmAquitC0tA?usp=sharing';

    // Fallback konstanta kompatibilitas
    public const ROOT_FOLDER_ID = self::KARAWANG_FOLDER_ID;
    public const ROOT_FOLDER_URL = self::KARAWANG_FOLDER_URL;

    /**
     * Helper untuk menentukan apakah suatu konteks adalah UBSI Kampus Cikampek.
     */
    public static function isCikampekCampus(int|string|Campus|null $campus = null, ?string $schoolName = null): bool
    {
        if ($campus instanceof Campus) {
            return $campus->code === 'CKP' || str_contains(strtolower($campus->name), 'cikampek') || $campus->id == 2;
        }

        if (is_numeric($campus)) {
            $c = Campus::find($campus);
            if ($c) {
                return $c->code === 'CKP' || str_contains(strtolower($c->name), 'cikampek') || $c->id == 2;
            }
            return (int) $campus === 2;
        }

        if (is_string($campus)) {
            if (strtoupper($campus) === 'CKP' || str_contains(strtolower($campus), 'cikampek')) {
                return true;
            }
        }

        // Jika schoolName diberikan, cari kampus dari sekolah tersebut
        if (!empty($schoolName)) {
            $school = School::where('name', $schoolName)->first();
            if ($school && $school->campus_id) {
                return self::isCikampekCampus($school->campus_id);
            }
        }

        // Cek active campus dari session / auth user
        $activeCampus = \App\Services\AppContextService::getActiveCampus();
        if ($activeCampus) {
            return $activeCampus->code === 'CKP' || str_contains(strtolower($activeCampus->name), 'cikampek') || $activeCampus->id == 2;
        }

        return false;
    }

    /**
     * Dapatkan Folder ID Google Drive berdasarkan Kampus (Karawang vs Cikampek).
     */
    public static function getFolderIdForCampus(int|string|Campus|null $campus = null, ?string $schoolName = null): string
    {
        return self::isCikampekCampus($campus, $schoolName) ? self::CIKAMPEK_FOLDER_ID : self::KARAWANG_FOLDER_ID;
    }

    /**
     * Dapatkan Folder URL Google Drive berdasarkan Kampus (Karawang vs Cikampek).
     */
    public static function getFolderUrlForCampus(int|string|Campus|null $campus = null, ?string $schoolName = null): string
    {
        return self::isCikampekCampus($campus, $schoolName) ? self::CIKAMPEK_FOLDER_URL : self::KARAWANG_FOLDER_URL;
    }

    /**
     * Dapatkan Label/Judul Tombol Google Drive Cloud sesuai Kampus Aktif.
     */
    public static function getFolderLabelForCampus(int|string|Campus|null $campus = null, ?string $schoolName = null): string
    {
        return self::isCikampekCampus($campus, $schoolName) ? 'Drive UBSI Cikampek' : 'Drive UBSI Karawang';
    }

    /**
     * Build direct or search Google Drive URL for a school and date.
     */
    public static function buildFolderUrlForSchool(string $schoolName, ?string $date = null, int|string|Campus|null $campus = null): string
    {
        return self::getFolderUrlForCampus($campus, $schoolName);
    }

    /**
     * Upload documentation file with real-time automatic folder creation:
     * Root (per kampus) -> {YYYY-MM-DD} -> {School Name} -> file
     *
     * Saves locally to public storage and syncs to Google Drive.
     */
    public static function uploadDocumentation(UploadedFile $file, string $schoolName, ?string $date = null, int|string|Campus|null $campus = null): array
    {
        $isCikampek = self::isCikampekCampus($campus, $schoolName);
        $rootFolderId = $isCikampek ? self::CIKAMPEK_FOLDER_ID : self::KARAWANG_FOLDER_ID;
        $rootFolderUrl = $isCikampek ? self::CIKAMPEK_FOLDER_URL : self::KARAWANG_FOLDER_URL;
        $campusName = $isCikampek ? 'UBSI Cikampek' : 'UBSI Karawang';

        $dateStr = $date ? Carbon::parse($date)->format('Y-m-d') : Carbon::today()->format('Y-m-d');
        $cleanSchool = preg_replace('/[^a-zA-Z0-9_\- ]/', '', trim($schoolName));
        $schoolSlug = Str::slug($cleanSchool) ?: 'sekolah';

        // 1. Save to local storage under organized folder hierarchy per campus
        $campusPrefix = $isCikampek ? 'cikampek' : 'karawang';
        $subPath = "visit-documents/{$campusPrefix}/{$dateStr}/{$schoolSlug}";
        $filename = time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
        $storedPath = $file->storeAs($subPath, $filename, 'public');

        $localUrl = asset('storage/' . ltrim($storedPath, '/'));
        $driveFolderUrl = $rootFolderUrl;
        $driveFileUrl = $rootFolderUrl;

        // 2. Check if automated Google Apps Script Webhook / Google Service is configured
        $appSettings = AppSettingService::all();
        $webhookUrl = trim((string)($appSettings['gdrive_webhook_url'] ?? env('GOOGLE_DRIVE_WEBHOOK_URL', '')));

        if (!empty($webhookUrl)) {
            try {
                $fileContent = base64_encode(file_get_contents($file->getRealPath()));
                $response = Http::timeout(30)->post($webhookUrl, [
                    'root_folder_id' => $rootFolderId,
                    'campus' => $campusName,
                    'campus_folder_id' => $rootFolderId,
                    'date_folder' => $dateStr,
                    'school_folder' => $schoolName,
                    'filename' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'file_base64' => $fileContent,
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    if (!empty($json['folder_url'])) {
                        $driveFolderUrl = $json['folder_url'];
                    }
                    if (!empty($json['file_url'])) {
                        $driveFileUrl = $json['file_url'];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Google Drive automated upload warning: ' . $e->getMessage());
            }
        }

        return [
            'stored_path' => $storedPath,
            'local_url' => $localUrl,
            'drive_folder_url' => $driveFolderUrl,
            'drive_file_url' => $driveFileUrl,
            'date_folder_name' => $dateStr,
            'school_folder_name' => $schoolName,
            'campus' => $campusName,
        ];
    }
}
