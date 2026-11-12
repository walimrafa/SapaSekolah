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
     * Sesuai ketentuan:
     * - Administrator Pusat (Admin dengan campus_id null)
     * - Pimpinan Pusat (Pimpinan dengan campus_id null atau jabatan Pusat)
     * - Koordinator Pusat (Koordinator dengan campus_id null atau jabatan Pusat)
     * Pimpinan & Koordinator kampus cabang (misal UBSI Cikampek atau UBSI Karawang)
     * TIDAK BOLEH mengakses lintas kampus, hanya boleh mengakses seputar kampusnya sendiri.
     */
    public static function canSwitchCampus(?\App\Models\User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return false;
        }

        return $user->canAccessAllCampuses();
    }

    /**
     * Dapatkan Kampus yang sedang aktif dipilih.
     */
    public static function getActiveCampus(): ?Campus
    {
        $user = auth()->user();

        // KUNCI SECARA MUTLAK KE KAMPUS PENUGASAN USER JIKA BUKAN PUSAT:
        // Pimpinan & Koordinator kampus cabang (Karawang / Cikampek) terkunci 100% pada kampusnya sendiri.
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
     * Ambil status seluruh portal publik (bisa spesifik per kampus atau global).
     */
    public static function getPublicPortalsStatus(?int $campusId = null): array
    {
        $settings = AppSettingService::all();
        $queryParam = $campusId ? ['campus_id' => $campusId] : [];

        $getKey = function (string $key) use ($settings, $campusId): bool {
            if ($campusId !== null && isset($settings["portal_{$key}_campus_{$campusId}_active"])) {
                return (bool) $settings["portal_{$key}_campus_{$campusId}_active"];
            }
            return (bool) ($settings["portal_{$key}_active"] ?? true);
        };

        return [
            self::PORTAL_PUBLIC_VISIT => [
                'name' => 'Portal Input Kunjungan Lapangan',
                'url' => route('public-visit.index', $queryParam),
                'is_active' => $getKey(self::PORTAL_PUBLIC_VISIT),
                'description' => 'Formulir input hasil kunjungan sosialisasi untuk tim lapangan tanpa login.',
            ],
            self::PORTAL_STUDENT_QUESTIONNAIRE => [
                'name' => 'Portal Kuisioner Siswa & Beasiswa',
                'url' => route('student-questionnaire.create', $queryParam),
                'is_active' => $getKey(self::PORTAL_STUDENT_QUESTIONNAIRE),
                'description' => 'Survei online minat kuliah & pengajuan beasiswa siswa SLTA via QR Code.',
            ],
            self::PORTAL_SCHOOL_EVENT_PROPOSAL => [
                'name' => 'Portal Pengajuan Event & Sponsorship Sekolah',
                'url' => route('school-events.create', $queryParam),
                'is_active' => $getKey(self::PORTAL_SCHOOL_EVENT_PROPOSAL),
                'description' => 'Formulir pengajuan proposal sponsorship dan dukungan kegiatan dari OSIS/Sekolah.',
            ],
            self::PORTAL_EVENT_STUDENT_LEAD => [
                'name' => 'Portal Pendaftaran Siswa Event',
                'url' => route('event-students.create', $queryParam),
                'is_active' => $getKey(self::PORTAL_EVENT_STUDENT_LEAD),
                'description' => 'Formulir pendataan peserta event sekolah yang langsung tersinkron ke Follow-Up CRM.',
            ],
        ];
    }

    /**
     * Cek apakah portal publik tertentu sedang aktif (per kampus atau global).
     */
    public static function isPortalActive(string $portalKey, ?int $campusId = null): bool
    {
        $settings = AppSettingService::all();

        // 1. Jika global portal dinonaktifkan, seluruh kampus otomatis tertutup
        $globalActive = (bool) ($settings["portal_{$portalKey}_active"] ?? true);
        if (!$globalActive) {
            return false;
        }

        // 2. Cek spesifik per kampus jika ada
        if ($campusId === null) {
            if (request()->has('campus_id') && is_numeric(request('campus_id'))) {
                $campusId = (int) request('campus_id');
            } elseif (self::getActiveCampus()) {
                $campusId = self::getActiveCampus()->id;
            }
        }

        if ($campusId !== null && isset($settings["portal_{$portalKey}_campus_{$campusId}_active"])) {
            return (bool) $settings["portal_{$portalKey}_campus_{$campusId}_active"];
        }

        return true;
    }

    /**
     * Ubah status portal publik per kampus atau global (hanya Admin, Pimpinan, dan Koordinator Marketing).
     */
    public static function setPortalStatus(string $portalKey, bool $active, ?User $byUser = null, ?int $campusId = null): void
    {
        if ($campusId !== null) {
            $keyName = "portal_{$portalKey}_campus_{$campusId}_active";
            $campus = Campus::find($campusId);
            $campusName = $campus ? $campus->name : "Kampus #{$campusId}";
            $label = ucfirst(str_replace('_', ' ', $portalKey)) . " [{$campusName}]";
            AppSettingService::set($keyName, $active);
        } else {
            $keyName = "portal_{$portalKey}_active";
            $label = ucfirst(str_replace('_', ' ', $portalKey));
            
            $toUpdate = [$keyName => $active];
            // Sinkronkan ke seluruh kampus
            foreach (Campus::all() as $c) {
                $toUpdate["portal_{$portalKey}_campus_{$c->id}_active"] = $active;
            }
            AppSettingService::setMany($toUpdate);
        }

        // Catat ke Activity Log
        ActivityLogService::log(
            'TOGGLE_PUBLIC_PORTAL',
            'system',
            $byUser?->id,
            "Mengubah status {$label} menjadi: " . ($active ? 'AKTIF' : 'NONAKTIF'),
            null,
            ['portal' => $portalKey, 'campus_id' => $campusId, 'status' => $active]
        );
    }
}
