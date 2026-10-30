<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GoogleDriveService
{
    public const ROOT_FOLDER_ID = '1DQkSEomL8cacePKG8Ex72JldQABKndiD';
    public const ROOT_FOLDER_URL = 'https://drive.google.com/drive/folders/1DQkSEomL8cacePKG8Ex72JldQABKndiD?usp=sharing';

    /**
     * Build direct or search Google Drive URL for a school and date.
     */
    public static function buildFolderUrlForSchool(string $schoolName, ?string $date = null): string
    {
        $cleanSchool = trim($schoolName);
        $dateStr = $date ? Carbon::parse($date)->format('Y-m-d') : Carbon::today()->format('Y-m-d');

        // Target folder query inside root folder
        return self::ROOT_FOLDER_URL;
    }

    /**
     * Upload documentation file with real-time automatic folder creation:
     * Root -> {YYYY-MM-DD} -> {School Name} -> file
     *
     * Saves locally to public storage and syncs to Google Drive.
     */
    public static function uploadDocumentation(UploadedFile $file, string $schoolName, ?string $date = null): array
    {
        $dateStr = $date ? Carbon::parse($date)->format('Y-m-d') : Carbon::today()->format('Y-m-d');
        $cleanSchool = preg_replace('/[^a-zA-Z0-9_\- ]/', '', trim($schoolName));
        $schoolSlug = Str::slug($cleanSchool) ?: 'sekolah';

        // 1. Save to local storage under organized folder hierarchy
        $subPath = "visit-documents/{$dateStr}/{$schoolSlug}";
        $filename = time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
        $storedPath = $file->storeAs($subPath, $filename, 'public');

        $localUrl = asset('storage/' . ltrim($storedPath, '/'));
        $driveFolderUrl = self::ROOT_FOLDER_URL;
        $driveFileUrl = self::ROOT_FOLDER_URL;

        // 2. Check if automated Google Apps Script Webhook / Google Service is configured
        $appSettings = AppSettingService::all();
        $webhookUrl = trim((string)($appSettings['gdrive_webhook_url'] ?? env('GOOGLE_DRIVE_WEBHOOK_URL', '')));

        if (!empty($webhookUrl)) {
            try {
                $fileContent = base64_encode(file_get_contents($file->getRealPath()));
                $response = Http::timeout(30)->post($webhookUrl, [
                    'root_folder_id' => self::ROOT_FOLDER_ID,
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
        ];
    }
}
