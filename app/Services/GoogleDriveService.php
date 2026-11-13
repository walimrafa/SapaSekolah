<?php

namespace App\Services;

use App\Models\Campus;
use App\Models\DatabaseBackup;
use App\Models\School;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class GoogleDriveService
{
    // =========================================================================
    // 1. FOLDER DOKUMENTASI VISIT & KEGIATAN LAPANGAN PER KAMPUS
    // =========================================================================
    public const KARAWANG_FOLDER_ID = '1DQkSEomL8cacePKG8Ex72JldQABKndiD';
    public const KARAWANG_FOLDER_URL = 'https://drive.google.com/drive/folders/1DQkSEomL8cacePKG8Ex72JldQABKndiD?usp=sharing';

    public const CIKAMPEK_FOLDER_ID = '1VpGirvZZ1s2mR3ooMc_s5ZmAquitC0tA';
    public const CIKAMPEK_FOLDER_URL = 'https://drive.google.com/drive/folders/1VpGirvZZ1s2mR3ooMc_s5ZmAquitC0tA?usp=sharing';

    public const ROOT_FOLDER_ID = self::KARAWANG_FOLDER_ID;
    public const ROOT_FOLDER_URL = self::KARAWANG_FOLDER_URL;

    // =========================================================================
    // 2. FOLDER CADANGAN DATABASE (.SQL.GZ) PER KAMPUS (RESMI DARI USER)
    // =========================================================================
    public const DEFAULT_CIKAMPEK_FOLDER_ID = '1YCNXg6CoeiFdPOoDWUNXGB5L1Nc765aT';
    public const DEFAULT_KARAWANG_FOLDER_ID = '1i_84WHAo5nlk8bwDg0vXdEffyX1SLf28';

    public const BACKUP_CIKAMPEK_FOLDER_ID = self::DEFAULT_CIKAMPEK_FOLDER_ID;
    public const BACKUP_KARAWANG_FOLDER_ID = self::DEFAULT_KARAWANG_FOLDER_ID;

    protected Client $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client([
            'timeout' => 60,
            'connect_timeout' => 15,
        ]);
    }

    // =========================================================================
    // HELPER & METODE DOKUMENTASI VISIT / KEGIATAN SEKOLAH
    // =========================================================================

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
     * Dapatkan Folder ID Google Drive untuk Dokumentasi Kegiatan (Karawang vs Cikampek).
     */
    public static function getFolderIdForCampus(int|string|Campus|null $campus = null, ?string $schoolName = null): string
    {
        return self::isCikampekCampus($campus, $schoolName) ? self::CIKAMPEK_FOLDER_ID : self::KARAWANG_FOLDER_ID;
    }

    /**
     * Dapatkan Folder URL Google Drive untuk Dokumentasi Kegiatan (Karawang vs Cikampek).
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

    // =========================================================================
    // METODE CADANGAN DATABASE & SINKRONISASI GOOGLE DRIVE
    // =========================================================================

    /**
     * Dapatkan Folder ID Google Drive untuk BACKUP database per kampus.
     */
    public static function getBackupFolderIdForCampus(int|string|Campus|null $campus = null): string
    {
        if (self::isCikampekCampus($campus)) {
            return AppSettingService::get('gdrive_cikampek_folder_id', env('GDRIVE_CIKAMPEK_FOLDER_ID', self::DEFAULT_CIKAMPEK_FOLDER_ID));
        }

        return AppSettingService::get('gdrive_karawang_folder_id', env('GDRIVE_KARAWANG_FOLDER_ID', self::DEFAULT_KARAWANG_FOLDER_ID));
    }

    /**
     * Dapatkan Folder ID berdasarkan kode kampus ('CKP', 'KRW', atau ID).
     */
    public static function getBackupFolderIdByCampusCodeOrId(string|int|null $campusIdOrCode): string
    {
        if (empty($campusIdOrCode) || $campusIdOrCode === 'ALL') {
            return AppSettingService::get('gdrive_karawang_folder_id', env('GDRIVE_KARAWANG_FOLDER_ID', self::DEFAULT_KARAWANG_FOLDER_ID));
        }

        return self::getBackupFolderIdForCampus($campusIdOrCode);
    }

    /**
     * Dapatkan URL Folder Google Drive untuk Backup Database.
     */
    public static function getBackupFolderUrlForCampus(int|string|Campus|null $campus = null): string
    {
        $folderId = self::getBackupFolderIdForCampus($campus);
        return "https://drive.google.com/drive/folders/{$folderId}?usp=sharing";
    }

    /**
     * Cek apakah kredensial otentikasi Google Drive telah dikonfigurasi.
     */
    public function isConfigured(): bool
    {
        return !empty($this->getServiceAccountCredentials()) || !empty($this->getOAuthRefreshToken());
    }

    /**
     * Upload berkas backup database ke Google Drive.
     */
    public function uploadBackup(DatabaseBackup $backup, ?string $targetFolderId = null): array
    {
        if (!$backup->existsLocally()) {
            return [
                'success' => false,
                'status' => 'failed',
                'message' => 'Berkas cadangan fisik tidak ditemukan di disk lokal.',
            ];
        }

        $folderId = $targetFolderId ?: ($backup->gdrive_folder_id ?: self::getBackupFolderIdForCampus($backup->campus));

        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'status' => 'not_configured',
                'folder_id' => $folderId,
                'message' => 'Kredensial otentikasi Google Drive (Service Account / OAuth2) belum dikonfigurasi di sistem.',
            ];
        }

        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return [
                    'success' => false,
                    'status' => 'failed',
                    'folder_id' => $folderId,
                    'message' => 'Gagal memperoleh access token dari Google OAuth. Periksa kredensial akun.',
                ];
            }

            $filePath = Storage::disk($backup->disk)->path($backup->file_path);
            $fileName = $backup->filename;
            $fileContent = file_get_contents($filePath);
            $mimeType = str_ends_with($fileName, '.gz') ? 'application/gzip' : 'application/sql';

            // Google Drive v3 Multipart Upload
            $metadata = [
                'name' => $fileName,
                'parents' => [$folderId],
                'description' => "Backup database SAPA Sekolah ({$backup->type}) per " . now()->toIso8601String(),
            ];

            $boundary = '-------' . md5(microtime());
            $delimiter = "\r\n--" . $boundary . "\r\n";
            $closeDelimiter = "\r\n--" . $boundary . "--";

            $multipartBody = $delimiter
                . "Content-Type: application/json; charset=UTF-8\r\n\r\n"
                . json_encode($metadata)
                . $delimiter
                . "Content-Type: {$mimeType}\r\n"
                . "Content-Transfer-Encoding: binary\r\n\r\n"
                . $fileContent
                . $closeDelimiter;

            $response = $this->httpClient->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&fields=id,name,webViewLink', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'multipart/related; boundary=' . $boundary,
                    'Content-Length' => strlen($multipartBody),
                ],
                'body' => $multipartBody,
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if (isset($result['id'])) {
                $fileId = $result['id'];
                $webViewLink = $result['webViewLink'] ?? "https://drive.google.com/file/d/{$fileId}/view";

                $backup->update([
                    'gdrive_status' => 'uploaded',
                    'gdrive_folder_id' => $folderId,
                    'gdrive_file_id' => $fileId,
                    'gdrive_file_url' => $webViewLink,
                    'error_message' => null,
                ]);

                return [
                    'success' => true,
                    'status' => 'uploaded',
                    'file_id' => $fileId,
                    'folder_id' => $folderId,
                    'url' => $webViewLink,
                    'message' => 'Berkas berhasil diunggah ke Google Drive.',
                ];
            }

            throw new \Exception('Respon Google Drive tidak menyertakan File ID.');

        } catch (Throwable $e) {
            Log::error('Google Drive Upload Error: ' . $e->getMessage(), [
                'backup_id' => $backup->id,
                'folder_id' => $folderId,
                'trace' => $e->getTraceAsString(),
            ]);

            $backup->update([
                'gdrive_status' => 'failed',
                'gdrive_folder_id' => $folderId,
                'error_message' => 'Google Drive Error: ' . $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => 'failed',
                'folder_id' => $folderId,
                'message' => 'Gagal upload ke Google Drive: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Uji koneksi dan akses ke folder Google Drive.
     */
    public function testConnection(string $folderId): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Kredensial Google Drive belum diisi di sistem.',
            ];
        }

        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return [
                    'success' => false,
                    'message' => 'Gagal mengotentikasi akun Google.',
                ];
            }

            $response = $this->httpClient->get("https://www.googleapis.com/drive/v3/files/{$folderId}?fields=id,name,mimeType,capabilities", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return [
                'success' => true,
                'folder_id' => $data['id'] ?? $folderId,
                'folder_name' => $data['name'] ?? 'Folder Google Drive',
                'can_add_children' => $data['capabilities']['canAddChildren'] ?? true,
                'message' => 'Koneksi ke Google Drive berhasil! Akses folder diverifikasi.',
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Koneksi gagal: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Dapatkan Google OAuth2 Access Token.
     */
    protected function getAccessToken(): ?string
    {
        // 1. Coba lewat Service Account JSON jika ada
        $serviceAccount = $this->getServiceAccountCredentials();
        if ($serviceAccount) {
            return $this->getAccessTokenFromServiceAccount($serviceAccount);
        }

        // 2. Coba lewat OAuth2 Refresh Token jika ada
        $refreshToken = $this->getOAuthRefreshToken();
        if ($refreshToken) {
            return $this->getAccessTokenFromRefreshToken($refreshToken);
        }

        return null;
    }

    /**
     * Dapatkan kredensial Service Account JSON dari file lokal, DB Setting, atau ENV.
     */
    protected function getServiceAccountCredentials(): ?array
    {
        // A. Cek dari file credentials.json di storage
        $filePath = storage_path('app/google-service-account.json');
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            $json = json_decode($content, true);
            if (is_array($json) && isset($json['client_email'], $json['private_key'])) {
                return $json;
            }
        }

        // B. Cek dari AppSettingService (bisa diisi via UI Pengaturan Sistem)
        $dbJson = AppSettingService::get('gdrive_service_account_json');
        if (!empty($dbJson)) {
            $json = json_decode($dbJson, true);
            if (is_array($json) && isset($json['client_email'], $json['private_key'])) {
                return $json;
            }
        }

        // C. Cek dari ENV
        $envJson = env('GOOGLE_SERVICE_ACCOUNT_JSON');
        if (!empty($envJson)) {
            $json = json_decode($envJson, true);
            if (is_array($json) && isset($json['client_email'], $json['private_key'])) {
                return $json;
            }
        }

        return null;
    }

    /**
     * Tukar Service Account JWT Assertion menjadi Google Access Token (RFC 7523).
     */
    protected function getAccessTokenFromServiceAccount(array $credentials): ?string
    {
        $clientEmail = $credentials['client_email'] ?? '';
        $privateKey = $credentials['private_key'] ?? '';

        if (empty($clientEmail) || empty($privateKey)) {
            return null;
        }

        $now = time();
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $payload = [
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/drive.file https://www.googleapis.com/auth/drive',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now,
        ];

        $base64UrlHeader = $this->base64UrlEncode(json_encode($header));
        $base64UrlPayload = $this->base64UrlEncode(json_encode($payload));
        $dataToSign = $base64UrlHeader . '.' . $base64UrlPayload;

        $signature = '';
        $pkeyResource = openssl_pkey_get_private($privateKey);
        if (!$pkeyResource) {
            throw new \Exception('Kunci privat Service Account (private_key) tidak valid.');
        }

        openssl_sign($dataToSign, $signature, $pkeyResource, OPENSSL_ALGO_SHA256);
        $base64UrlSignature = $this->base64UrlEncode($signature);

        $jwt = $dataToSign . '.' . $base64UrlSignature;

        $response = $this->httpClient->post('https://oauth2.googleapis.com/token', [
            'form_params' => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ],
        ]);

        $body = json_decode($response->getBody()->getContents(), true);
        return $body['access_token'] ?? null;
    }

    /**
     * Dapatkan OAuth2 Refresh Token dari AppSettingService atau ENV.
     */
    protected function getOAuthRefreshToken(): ?string
    {
        return AppSettingService::get('gdrive_refresh_token', env('GDRIVE_REFRESH_TOKEN'));
    }

    /**
     * Tukar Refresh Token menjadi Access Token.
     */
    protected function getAccessTokenFromRefreshToken(string $refreshToken): ?string
    {
        $clientId = AppSettingService::get('gdrive_client_id', env('GDRIVE_CLIENT_ID'));
        $clientSecret = AppSettingService::get('gdrive_client_secret', env('GDRIVE_CLIENT_SECRET'));

        if (empty($clientId) || empty($clientSecret)) {
            return null;
        }

        $response = $this->httpClient->post('https://oauth2.googleapis.com/token', [
            'form_params' => [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $refreshToken,
                'grant_type' => 'refresh_token',
            ],
        ]);

        $body = json_decode($response->getBody()->getContents(), true);
        return $body['access_token'] ?? null;
    }

    protected function base64UrlEncode(string $data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}
