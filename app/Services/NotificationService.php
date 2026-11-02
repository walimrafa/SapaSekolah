<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\SchoolEventProposal;
use App\Models\StudentQuestionnaire;
use App\Models\FollowUp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class NotificationService
{
    /**
     * Create and dispatch a new in-app notification.
     */
    public static function send(array $data): ?AppNotification
    {
        try {
            return AppNotification::create([
                'user_id' => $data['user_id'] ?? null,
                'role_id' => $data['role_id'] ?? null,
                'campus_id' => $data['campus_id'] ?? null,
                'type' => $data['type'] ?? 'system',
                'title' => $data['title'],
                'message' => $data['message'],
                'action_url' => $data['action_url'] ?? null,
                'icon' => $data['icon'] ?? 'bell',
                'color' => $data['color'] ?? 'brand',
                'is_read' => false,
                'data' => $data['data'] ?? null,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal membuat AppNotification: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get recent notifications visible for a given user.
     */
    public static function getRecentForUser(?User $user, int $limit = 10): Collection
    {
        if (!$user) {
            return new Collection();
        }

        try {
            if (AppNotification::count() === 0) {
                self::send([
                    'title' => 'Selamat Datang di SAPA Sekolah',
                    'message' => 'Sistem pemetaan digital dan monitoring marketing sekolah aktif.',
                    'action_url' => route('dashboard'),
                    'icon' => 'sparkles',
                    'color' => 'brand',
                ]);
                self::send([
                    'title' => 'Leaderboard Prioritas',
                    'message' => 'Lihat rekomendasi kunjungan sekolah prioritas tinggi hari ini.',
                    'action_url' => route('priorities.index'),
                    'icon' => 'target',
                    'color' => 'amber',
                ]);
            }

            return AppNotification::forUser($user)
                ->latest('id')
                ->take($limit)
                ->get();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal mengambil daftar notifikasi: ' . $e->getMessage());
            return new Collection();
        }
    }

    /**
     * Get unread notification count for a user.
     */
    public static function getUnreadCount(?User $user): int
    {
        if (!$user) {
            return 0;
        }

        try {
            return AppNotification::forUser($user)
                ->unread()
                ->count();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal mengambil unread count notifikasi: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Mark a single notification as read.
     */
    public static function markAsRead(int $id, ?User $user): bool
    {
        if (!$user) {
            return false;
        }

        try {
            $notification = AppNotification::forUser($user)->find($id);
            if ($notification) {
                return $notification->markAsRead();
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal menandai notifikasi dibaca: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Mark all unread notifications for a user as read.
     */
    public static function markAllAsRead(?User $user): int
    {
        if (!$user) {
            return 0;
        }

        try {
            return AppNotification::forUser($user)
                ->unread()
                ->update([
                    'is_read' => true,
                    'read_at' => Carbon::now(),
                ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal menandai seluruh notifikasi dibaca: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Helper: Trigger notification when a new proposal is submitted by a school.
     */
    public static function notifyProposalSubmitted(SchoolEventProposal $proposal): void
    {
        $schoolName = $proposal->school ? $proposal->school->name : ($proposal->school_name ?: 'Sekolah');
        $eventName = $proposal->event_name ?: 'Event Sekolah';

        self::send([
            'campus_id' => $proposal->campus_id,
            'type' => 'proposal',
            'title' => "Pengajuan Proposal Baru",
            'message' => "Proposal event '{$eventName}' masuk dari {$schoolName}.",
            'action_url' => route('event-proposals.index'),
            'icon' => 'file-text',
            'color' => 'indigo',
            'data' => [
                'proposal_id' => $proposal->id,
                'reference_no' => $proposal->reference_no ?? null,
            ],
        ]);
    }

    /**
     * Helper: Trigger notification when a student questionnaire is submitted.
     */
    public static function notifyQuestionnaireSubmitted(StudentQuestionnaire $questionnaire): void
    {
        $studentName = $questionnaire->student_name ?: 'Siswa';
        $schoolName = $questionnaire->school ? $questionnaire->school->name : ($questionnaire->school_name ?: 'SLTA');

        self::send([
            'campus_id' => $questionnaire->campus_id,
            'type' => 'questionnaire',
            'title' => "Kuisioner Siswa Baru",
            'message' => "{$studentName} ({$schoolName}) telah mengisi form minat & survei PMB.",
            'action_url' => route('student-questionnaires.index'),
            'icon' => 'clipboard-check',
            'color' => 'emerald',
            'data' => [
                'questionnaire_id' => $questionnaire->id,
            ],
        ]);
    }

    /**
     * Helper: Trigger notification for scheduled follow-up reminder.
     */
    public static function notifyFollowUpReminder(FollowUp $followUp): void
    {
        $schoolName = $followUp->school ? $followUp->school->name : 'Sekolah';
        $staffUser = $followUp->user;
        $targetDate = $followUp->fixed_date ?: ($followUp->visit?->target_date ?: $followUp->followup_date);
        $dateFormatted = $targetDate ? Carbon::parse($targetDate)->format('d M Y') : 'hari ini';

        self::send([
            'user_id' => $staffUser?->id,
            'campus_id' => $followUp->campus_id ?: ($followUp->school?->campus_id),
            'type' => 'followup',
            'title' => "Jadwal Follow-Up Sekolah",
            'message' => "Follow-up untuk {$schoolName} dijadwalkan tanggal {$dateFormatted}.",
            'action_url' => route('follow-ups.index'),
            'icon' => 'calendar-clock',
            'color' => 'amber',
            'data' => [
                'follow_up_id' => $followUp->id,
            ],
        ]);
    }
}
