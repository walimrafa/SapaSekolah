<?php

namespace App\Services;

use App\Models\AcademicPeriod;
use App\Models\Campus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;

class AppContextService
{
    public const PORTAL_PUBLIC_VISIT = 'public_visit';
    public const PORTAL_STUDENT_QUESTIONNAIRE = 'student_questionnaire';
    public const PORTAL_SCHOOL_EVENT_PROPOSAL = 'school_event_proposal';
    public const PORTAL_EVENT_STUDENT_LEAD = 'event_student_lead';

    /**
     * Tentukan apakah user memiliki hak akses untuk berpindah kampus (multi-kampus).
     * Sesuai ketentuan: HANYA Administrator dan Koordinator Marketing.
     */
    public static function canSwitchCampus(?\App\Models\User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return false;
        }

        return $user->isAdmin() || $user->isKoordinator() || $user->hasRole('Koordinator Marketing');
    }

    /**
     * Dapatkan Kampus yang sedang aktif dipilih.
     */
    public static function getActiveCampus(): ?Campus
    {
        $user = auth()->user();

        // JIKA BUKAN ADMINISTRATOR & BUKAN KOORDINATOR MARKETING:
        // Kunci secara mutlak ke kampus penugasan user (isolasi per kampus)!
        if ($user && $user->campus_id && !self::canSwitchCampus($user)) {
            return Campus::find($user->campus_id) ?? Campus::getDefault();
        }

        $sessionCampusId = session('selected_campus_id');

        if ($sessionCampusId === 'ALL') {
            return null; // Mode semua kampus
        }

        if (!empty($sessionCampusId)) {
            $campus = Campus::find($sessionCampusId);
            if ($campus && $campus->is_active) {
                return $campus;
            }
        }

        // Fallback ke kampus default user atau kampus default sistem
        if ($user && $user->campus_id) {
            $userCampus = Campus::find($user->campus_id);
            if ($userCampus && $userCampus->is_active) {
                return $userCampus;
            }
        }

        return Campus::getDefault();
    }

    /**
     * Dapatkan Periode yang sedang aktif dipilih.
     */
    public static function getActivePeriod(): ?AcademicPeriod
    {
        $sessionPeriodId = session('selected_period_id');

        if ($sessionPeriodId === 'ALL') {
            return null; // Mode semua periode
        }

        if (!empty($sessionPeriodId)) {
            $period = AcademicPeriod::find($sessionPeriodId);
            if ($period) {
                return $period;
            }
        }

        return AcademicPeriod::getCurrent();
    }

    /**
     * Atur kampus aktif ke session.
     */
    public static function setActiveCampus(string|int|null $campusId): void
    {
        if ($campusId === 'ALL' || empty($campusId)) {
            session(['selected_campus_id' => 'ALL']);
        } else {
            session(['selected_campus_id' => (int) $campusId]);
        }
    }

    /**
     * Atur periode aktif ke session.
     */
    public static function setActivePeriod(string|int|null $periodId): void
    {
        if ($periodId === 'ALL' || empty($periodId)) {
            session(['selected_period_id' => 'ALL']);
        } else {
            session(['selected_period_id' => (int) $periodId]);
        }
    }

    /**
     * Dapatkan seluruh kampus aktif.
     */
    public static function getAllCampuses()
    {
        return Campus::where('is_active', true)->orderBy('is_default', 'desc')->orderBy('name')->get();
    }

    /**
     * Dapatkan seluruh periode akademik.
     */
    public static function getAllPeriods()
    {
        return AcademicPeriod::orderBy('start_date', 'desc')->orderBy('name', 'desc')->get();
    }

    /**
     * Terapkan filter kampus dan periode pada query Eloquent.
     */
    public static function applyContextFilters(
        Builder $query,
        string $campusColumn = 'campus_id',
        string $periodColumn = 'academic_period_id'
    ): Builder {
        $activeCampus = self::getActiveCampus();
        if ($activeCampus) {
            $query->where($campusColumn, $activeCampus->id);
        }

        $activePeriod = self::getActivePeriod();
        if ($activePeriod) {
            $query->where($periodColumn, $activePeriod->id);
        }

        return $query;
    }

    // ========================================================
    // KONTROL STATUS PORTAL PUBLIK (AKTIF / TIDAK AKTIF)
    // ========================================================

    /**
     * Ambil status seluruh portal publik.
     */
    public static function getPublicPortalsStatus(): array
    {
        $settings = AppSettingService::all();

        return [
            self::PORTAL_PUBLIC_VISIT => [
                'name' => 'Portal Input Kunjungan Lapangan',
                'url' => route('public-visit.index'),
                'is_active' => (bool) ($settings['portal_public_visit_active'] ?? true),
                'description' => 'Formulir input hasil kunjungan sosialisasi untuk tim lapangan tanpa login.',
            ],
            self::PORTAL_STUDENT_QUESTIONNAIRE => [
                'name' => 'Portal Kuisioner Siswa & Beasiswa',
                'url' => route('student-questionnaire.create'),
                'is_active' => (bool) ($settings['portal_student_questionnaire_active'] ?? true),
                'description' => 'Survei online minat kuliah & pengajuan beasiswa siswa SLTA via QR Code.',
            ],
            self::PORTAL_SCHOOL_EVENT_PROPOSAL => [
                'name' => 'Portal Pengajuan Event & Sponsorship Sekolah',
                'url' => route('school-events.create'),
                'is_active' => (bool) ($settings['portal_school_event_proposal_active'] ?? true),
                'description' => 'Formulir pengajuan proposal sponsorship dan dukungan kegiatan dari OSIS/Sekolah.',
            ],
            self::PORTAL_EVENT_STUDENT_LEAD => [
                'name' => 'Portal Pendaftaran Siswa Event',
                'url' => route('event-students.create'),
                'is_active' => (bool) ($settings['portal_event_student_lead_active'] ?? true),
                'description' => 'Formulir pendataan peserta event sekolah yang langsung tersinkron ke Follow-Up CRM.',
            ],
        ];
    }

    /**
     * Cek apakah portal publik tertentu sedang aktif.
     */
    public static function isPortalActive(string $portalKey): bool
    {
        $portals = self::getPublicPortalsStatus();
        return $portals[$portalKey]['is_active'] ?? true;
    }

    /**
     * Ubah status portal publik (hanya Admin, Pimpinan, dan Koordinator Marketing).
     */
    public static function setPortalStatus(string $portalKey, bool $active, ?User $byUser = null): void
    {
        $keyName = "portal_{$portalKey}_active";
        AppSettingService::set($keyName, $active);

        // Catat ke Activity Log
        ActivityLogService::log(
            'TOGGLE_PUBLIC_PORTAL',
            'system',
            null,
            "Mengubah status " . ucfirst(str_replace('_', ' ', $portalKey)) . " menjadi: " . ($active ? 'AKTIF' : 'NONAKTIF'),
            null,
            ['portal' => $portalKey, 'status' => $active]
        );
    }
}
