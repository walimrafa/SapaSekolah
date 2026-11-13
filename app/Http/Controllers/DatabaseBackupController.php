<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\DatabaseBackup;
use App\Services\ActivityLogService;
use App\Services\AppContextService;
use App\Services\AppSettingService;
use App\Services\DatabaseBackupService;
use App\Services\GoogleDriveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatabaseBackupController extends Controller
{
    protected DatabaseBackupService $backupService;
    protected GoogleDriveService $googleDriveService;

    public function __construct(DatabaseBackupService $backupService, GoogleDriveService $googleDriveService)
    {
        $this->backupService = $backupService;
        $this->googleDriveService = $googleDriveService;
    }

    /**
     * Tampilkan halaman riwayat cadangan database, status penyimpanan & sinkronisasi Google Drive.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        if (!$user || !$user->canManageBackups()) {
            abort(403, 'Akses ditolak. Pengelolaan backup database hanya dapat diakses oleh Administrator, Pimpinan, dan Koordinator.');
        }

        $canSwitchCampus = $user->canAccessAllCampuses();
        $activeCampus = AppContextService::getActiveCampus();

        // Isolasi Kampus Cabang: jika user cabang (Karawang/Cikampek), paksa filter ke kampusnya sendiri
        if (!$canSwitchCampus) {
            $selectedCampusId = $user->campus_id ?? $activeCampus?->id;
        } else {
            $selectedCampusId = $request->get('campus_id');
        }

        // Ambil query backup terisolasi
        $backupsQuery = $this->backupService->getBackupsForUser($user, is_numeric($selectedCampusId) ? (int)$selectedCampusId : null);

        // Filter status lokal / gdrive jika ada
        if ($request->filled('status')) {
            $backupsQuery->where('status', $request->get('status'));
        }
        if ($request->filled('gdrive_status')) {
            $backupsQuery->where('gdrive_status', $request->get('gdrive_status'));
        }

        $backups = $backupsQuery->paginate(15)->withQueryString();

        // Hitung statistik
        $statsBaseQuery = DatabaseBackup::query();
        if (!$canSwitchCampus) {
            $statsBaseQuery->where('campus_id', $user->campus_id ?? $activeCampus?->id);
        } elseif ($selectedCampusId && $selectedCampusId !== 'ALL' && is_numeric($selectedCampusId)) {
            $statsBaseQuery->where('campus_id', $selectedCampusId);
        }

        $totalBackups = (clone $statsBaseQuery)->count();
        $totalBytes = (clone $statsBaseQuery)->where('status', 'success')->sum('file_size');
        $totalUploadedGdrive = (clone $statsBaseQuery)->where('gdrive_status', 'uploaded')->count();
        $lastBackup = (clone $statsBaseQuery)->where('status', 'success')->latest()->first();

        // Informasi folder Google Drive per kampus
        $cikampekFolderId = AppSettingService::get('gdrive_cikampek_folder_id', env('GDRIVE_CIKAMPEK_FOLDER_ID', GoogleDriveService::DEFAULT_CIKAMPEK_FOLDER_ID));
        $karawangFolderId = AppSettingService::get('gdrive_karawang_folder_id', env('GDRIVE_KARAWANG_FOLDER_ID', GoogleDriveService::DEFAULT_KARAWANG_FOLDER_ID));

        $cikampekFolderUrl = "https://drive.google.com/drive/folders/{$cikampekFolderId}?usp=sharing";
        $karawangFolderUrl = "https://drive.google.com/drive/folders/{$karawangFolderId}?usp=sharing";

        $campuses = Campus::where('is_active', true)->orderBy('name')->get();
        $isGdriveConfigured = $this->googleDriveService->isConfigured();

        return view('database-backups.index', compact(
            'backups',
            'totalBackups',
            'totalBytes',
            'totalUploadedGdrive',
            'lastBackup',
            'campuses',
            'selectedCampusId',
            'canSwitchCampus',
            'cikampekFolderId',
            'karawangFolderId',
            'cikampekFolderUrl',
            'karawangFolderUrl',
            'isGdriveConfigured'
        ));
    }

    /**
     * Buat cadangan database secara manual dari antarmuka web.
     */
    public function createManual(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (!$user || !$user->canManageBackups()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk membuat cadangan database.');
        }

        $canSwitchCampus = $user->canAccessAllCampuses();
        $uploadToGdrive = $request->boolean('upload_to_gdrive', true);

        if (!$canSwitchCampus) {
            // User cabang terikat hanya pada kampusnya sendiri
            $targetCampusId = $user->campus_id ?? AppContextService::getActiveCampus()?->id;
            $campusesToBackup = [Campus::find($targetCampusId)];
        } else {
            $campusChoice = $request->get('campus_choice', 'current');
            if ($campusChoice === 'ALL') {
                $campusesToBackup = Campus::where('is_active', true)->get()->all();
            } elseif ($campusChoice === 'current') {
                $campusesToBackup = [AppContextService::getActiveCampus()];
            } elseif (is_numeric($campusChoice)) {
                $campusesToBackup = [Campus::find((int)$campusChoice)];
            } else {
                $campusesToBackup = [null]; // Global
            }
        }

        $successCount = 0;
        $failedCount = 0;
        $gdriveSuccess = 0;

        foreach ($campusesToBackup as $campus) {
            $backup = $this->backupService->createBackup(
                campusId: $campus?->id,
                type: 'manual',
                userId: $user->id,
                uploadToGdrive: $uploadToGdrive
            );

            if ($backup->status === 'success') {
                $successCount++;
                if ($backup->gdrive_status === 'uploaded') {
                    $gdriveSuccess++;
                }
            } else {
                $failedCount++;
            }
        }

        if ($failedCount === 0) {
            $msg = "Berhasil membuat {$successCount} berkas cadangan database (.sql.gz).";
            if ($uploadToGdrive) {
                if ($gdriveSuccess > 0) {
                    $msg .= " Sinkronisasi ke Google Drive sukses ({$gdriveSuccess} file).";
                } else {
                    $msg .= " (Catatan: Berkas tersimpan lokal. Sinkronisasi Google Drive memerlukan kredensial Service Account / OAuth aktif).";
                }
            }
            return redirect()->route('database-backups.index')->with('success', $msg);
        }

        return redirect()->route('database-backups.index')->with('warning', "Cadangan selesai dengan catatan: {$successCount} sukses, {$failedCount} gagal.");
    }

    /**
     * Unduh file cadangan database lokal (.sql.gz).
     */
    public function download(DatabaseBackup $backup): StreamedResponse|RedirectResponse
    {
        $user = auth()->user();
        if (!$user || !$user->canManageBackups()) {
            abort(403, 'Akses ditolak.');
        }

        // Isolasi Kampus Cabang: User cabang hanya boleh unduh berkas kampusnya
        if (!$user->canAccessAllCampuses() && $backup->campus_id !== $user->campus_id) {
            abort(403, 'Akses ditolak. Anda hanya berhak mengunduh cadangan kampus Anda.');
        }

        if (!$backup->existsLocally()) {
            return redirect()->route('database-backups.index')->with('error', 'Berkas fisik cadangan tidak ditemukan di penyimpanan server.');
        }

        ActivityLogService::log(
            'DOWNLOAD_BACKUP',
            'system',
            $backup->id,
            "Mengunduh berkas cadangan database: {$backup->filename}"
        );

        return Storage::disk($backup->disk)->download(
            $backup->file_path,
            $backup->filename,
            ['Content-Type' => 'application/gzip']
        );
    }

    /**
     * Unggah ulang cadangan database ke Google Drive folder kampus.
     */
    public function uploadGdrive(DatabaseBackup $backup): RedirectResponse
    {
        $user = auth()->user();
        if (!$user || !$user->canManageBackups()) {
            abort(403, 'Akses ditolak.');
        }

        if (!$user->canAccessAllCampuses() && $backup->campus_id !== $user->campus_id) {
            abort(403, 'Akses ditolak.');
        }

        $result = $this->googleDriveService->uploadBackup($backup);

        if ($result['success']) {
            return redirect()->route('database-backups.index')->with('success', 'Berkas berhasil disinkronisasikan ke Google Drive.');
        }

        return redirect()->route('database-backups.index')->with('error', $result['message']);
    }

    /**
     * Hapus berkas cadangan database.
     */
    public function destroy(DatabaseBackup $backup): RedirectResponse
    {
        $user = auth()->user();
        if (!$user || !$user->canManageBackups()) {
            abort(403, 'Akses ditolak.');
        }

        if (!$user->canAccessAllCampuses() && $backup->campus_id !== $user->campus_id) {
            abort(403, 'Akses ditolak. Anda tidak berhak menghapus data kampus lain.');
        }

        $this->backupService->deleteBackup($backup);

        return redirect()->route('database-backups.index')->with('success', 'Berkas cadangan berhasil dihapus.');
    }

    /**
     * Uji koneksi folder Google Drive (Cikampek / Karawang).
     */
    public function testConnection(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->canManageBackups()) {
            abort(403);
        }

        $target = $request->get('target', 'karawang');
        $folderId = match ($target) {
            'cikampek' => AppSettingService::get('gdrive_cikampek_folder_id', env('GDRIVE_CIKAMPEK_FOLDER_ID', GoogleDriveService::DEFAULT_CIKAMPEK_FOLDER_ID)),
            default => AppSettingService::get('gdrive_karawang_folder_id', env('GDRIVE_KARAWANG_FOLDER_ID', GoogleDriveService::DEFAULT_KARAWANG_FOLDER_ID)),
        };

        $result = $this->googleDriveService->testConnection($folderId);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message'] . " (Folder: {$result['folder_name']})");
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * Simpan pengaturan kredensial dan Folder ID Google Drive.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Hanya Administrator Utama yang dapat mengubah kredensial sistem Google Drive.');
        }

        $validated = $request->validate([
            'gdrive_cikampek_folder_id' => 'required|string',
            'gdrive_karawang_folder_id' => 'required|string',
            'gdrive_client_id' => 'nullable|string',
            'gdrive_client_secret' => 'nullable|string',
            'gdrive_refresh_token' => 'nullable|string',
            'gdrive_service_account_json' => 'nullable|string',
        ]);

        foreach ($validated as $key => $val) {
            if ($val !== null) {
                AppSettingService::set($key, trim($val));
            }
        }

        return redirect()->route('database-backups.index')->with('success', 'Pengaturan integrasi Google Drive berhasil disimpan.');
    }
}

