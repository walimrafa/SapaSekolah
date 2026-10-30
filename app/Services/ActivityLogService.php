<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    /**
     * Catat aktivitas pengguna ke dalam database ActivityLog.
     *
     * @param string $action Contoh: 'LOGIN', 'LOGOUT', 'CREATE', 'UPDATE', 'DELETE', 'VALIDATE', 'PLOT', 'TOGGLE'
     * @param string $module Contoh: 'auth', 'school', 'visit', 'follow_up', 'prospect', 'questionnaire', 'event', 'system'
     * @param int|null $recordId ID record yang terpengaruh
     * @param string|null $description Deskripsi ringkas aktivitas yang mudah dipahami manusia
     * @param array|null $oldData Data sebelum diubah (opsional)
     * @param array|null $newData Data sesudah diubah (opsional)
     * @param int|null $userId ID user pelaku (default auth()->id())
     */
    public static function log(
        string $action,
        string $module,
        ?int $recordId = null,
        ?string $description = null,
        ?array $oldData = null,
        ?array $newData = null,
        ?int $userId = null
    ): ?ActivityLog {
        try {
            $currentUserId = $userId ?: Auth::id();
            $ip = Request::ip();
            $userAgent = substr(Request::userAgent() ?? '', 0, 490);

            $activeCampus = AppContextService::getActiveCampus();
            $activePeriod = AppContextService::getActivePeriod();

            return ActivityLog::create([
                'user_id' => $currentUserId,
                'action' => strtoupper($action),
                'module' => strtolower($module),
                'record_id' => $recordId,
                'description' => $description,
                'old_data' => $oldData,
                'new_data' => $newData,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'campus_id' => $activeCampus?->id,
                'academic_period_id' => $activePeriod?->id,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Gagal mencatat ActivityLog: ' . $e->getMessage());
            return null;
        }
    }
}

