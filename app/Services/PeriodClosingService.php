<?php

namespace App\Services;

use App\Models\PeriodClosingRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PeriodClosingService
{
    /**
     * Dapatkan permohonan tutup periode yang sedang aktif / menunggu persetujuan.
     */
    public static function getActiveRequest(): ?PeriodClosingRequest
    {
        return PeriodClosingRequest::with(['requester', 'pimpinan', 'koordinator', 'customerService'])
            ->where('status', 'pending')
            ->latest()
            ->first();
    }

    /**
     * Dapatkan riwayat permohonan tutup periode terakhir.
     */
    public static function getLatestRequest(): ?PeriodClosingRequest
    {
        return PeriodClosingRequest::with(['requester', 'pimpinan', 'koordinator', 'customerService'])
            ->latest()
            ->first();
    }

    /**
     * Cek apakah ada permohonan pending yang membutuhkan persetujuan dari user ini.
     */
    public static function pendingForUser(?User $user): ?PeriodClosingRequest
    {
        if (!$user) return null;

        $active = self::getActiveRequest();
        if (!$active) return null;

        return $active->canUserApprove($user) ? $active : null;
    }

    /**
     * Catat persetujuan atau penolakan dari Pimpinan / Koordinator Marketing / Customer Service.
     *
     * @param PeriodClosingRequest $request
     * @param User $user
     * @param string $decision 'approve' | 'reject'
     * @param string|null $notes
     * @return array ['success' => bool, 'message' => string, 'completed' => bool]
     */
    public static function recordDecision(
        PeriodClosingRequest $request,
        User $user,
        string $decision,
        ?string $notes = null
    ): array {
        if ($request->status !== 'pending') {
            return [
                'success' => false,
                'message' => 'Permohonan ini sudah tidak dalam status menunggu persetujuan.',
                'completed' => false,
            ];
        }

        $slot = $request->getUserRoleSlot($user);
        if (!$slot) {
            return [
                'success' => false,
                'message' => 'Akun Anda tidak memiliki wewenang role (Pimpinan, Koordinator Marketing, atau Customer Service) untuk permohonan ini.',
                'completed' => false,
            ];
        }

        $statusField = "{$slot}_status";
        $idField = "{$slot}_id";
        $notesField = "{$slot}_notes";
        $timeField = "{$slot}_action_at";

        if ($request->$statusField !== 'pending') {
            return [
                'success' => false,
                'message' => 'Pihak ' . ucfirst($slot) . ' sudah memberikan keputusan sebelumnya.',
                'completed' => false,
            ];
        }

        if ($decision === 'reject') {
            // Jika salah satu menolak, permohonan langsung BATAL/REJECTED dan TIDAK diproses
            $roleLabel = match ($slot) {
                'pimpinan' => 'Pimpinan Kampus',
                'koordinator' => 'Koordinator Marketing',
                'cs' => 'Customer Service',
                default => 'Approver',
            };

            $request->update([
                $idField => $user->id,
                $statusField => 'rejected',
                $notesField => $notes,
                $timeField => now(),
                'status' => 'rejected',
                'rejection_reason' => "Ditolak oleh {$roleLabel} ({$user->name}): " . ($notes ?: 'Tidak ada alasan khusus.'),
            ]);

            return [
                'success' => true,
                'message' => "Permohonan Tutup Periode resmi DITOLAK oleh {$roleLabel}. Seluruh data operasional TIDAK diubah.",
                'completed' => false,
            ];
        }

        // Jika menyetujui
        $request->update([
            $idField => $user->id,
            $statusField => 'approved',
            $notesField => $notes,
            $timeField => now(),
        ]);

        $request->refresh();

        // Cek apakah ketiganya sekarang sudah menyetujui
        if ($request->allApproved()) {
            // KETIGANYA SUDAH MENYETUJUI -> Jalankan proses reset ke 0 secara aman!
            self::executeReset($request, $user);

            return [
                'success' => true,
                'message' => 'Ketiga pihak (Pimpinan, Koordinator Marketing, Customer Service) telah menyetujui! Tutup periode dan reset seluruh data ke 0 telah berhasil diproses.',
                'completed' => true,
            ];
        }

        $approvedCount = $request->approvedCount();
        return [
            'success' => true,
            'message' => "Persetujuan Anda telah dicatat ({$approvedCount}/3 pihak telah menyetujui). Menunggu persetujuan pihak lainnya.",
            'completed' => false,
        ];
    }

    /**
     * Eksekusi pembersihan seluruh data operasional kembali ke 0.
     * User accounts (users & roles) dan Master Sekolah & Referensi TIDAK dihapus.
     */
    public static function executeReset(PeriodClosingRequest $request, ?User $executor = null): void
    {
        Schema::disableForeignKeyConstraints();

        // 1. Follow Up & Kuisioner Siswa (Reset ke 0)
        DB::table('student_follow_ups')->truncate();
        DB::table('student_questionnaires')->truncate();

        // 2. Data Prospek & Mahasiswa Baru (Reset ke 0)
        DB::table('prospects')->truncate();

        // 3. Operasional Lapangan, Rencana & Laporan Kunjungan (Reset ke 0)
        DB::table('visit_documents')->truncate();
        DB::table('visit_results')->truncate();
        DB::table('school_visits')->truncate();
        DB::table('visit_plans')->truncate();
        DB::table('marketing_activities')->truncate();
        DB::table('school_activities')->truncate();
        DB::table('marketing_tracking')->truncate();
        DB::table('route_schools')->truncate();
        DB::table('marketing_routes')->truncate();
        DB::table('follow_ups')->truncate();

        // 4. Support Event Sekolah (Proposal & Laporan - Reset ke 0)
        DB::table('school_event_proposals')->truncate();

        // 5. Notifikasi & Log Aktivitas (Reset ke 0)
        DB::table('whatsapp_notification_logs')->truncate();
        DB::table('activity_logs')->truncate();

        // 6. Historis Intake Siswa & Potensi Sekolah (Reset ke 0)
        DB::table('school_student_intakes')->truncate();
        DB::table('school_potentials')->truncate();

        Schema::enableForeignKeyConstraints();

        // Bersihkan berkas fisik dokumen dan proposal event
        try {
            Storage::disk('public')->deleteDirectory('event-proposals');
            Storage::disk('public')->deleteDirectory('visit-documents');
        } catch (\Throwable $e) {
            // Ignore if directory missing
        }

        // Hitung ulang bobot & skor prioritas seluruh sekolah
        app(SchoolScoringService::class)->recalculateAllSchoolScores();

        // Update Request status ke completed
        $executorUser = $executor ?: auth()->user();
        $request->update([
            'status' => 'completed',
            'executed_by' => $executorUser ? $executorUser->id : $request->requested_by,
            'executed_at' => now(),
        ]);

        // Catat di AppSettingService
        AppSettingService::setMany([
            'last_period_closed_at' => now()->translatedFormat('d F Y, H:i:s'),
            'last_period_closed_by' => ($executorUser ? $executorUser->name : 'Sistem Otomatis (3 Approval)'),
        ]);
    }
}
