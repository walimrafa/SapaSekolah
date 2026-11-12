<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentQuestionnaire extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_BELUM_DIHUBUNGI = 'belum_dihubungi';
    public const STATUS_NO_RESPON = 'no_respon';
    public const STATUS_RESPON = 'respon';
    public const STATUS_BERMINAT = 'berminat';
    public const STATUS_DAFTAR_APLIKASI = 'daftar_aplikasi';
    public const STATUS_CLOSING = 'daftar_aplikasi'; // Alias for backwards compatibility

    public const KOL_STATUS_KANDIDAT = 'kandidat';
    public const KOL_STATUS_DIHUBUNGI = 'dihubungi';
    public const KOL_STATUS_BERSEDIA = 'bersedia';
    public const KOL_STATUS_DITOLAK = 'ditolak';
    public const KOL_STATUS_AKTIF = 'aktif';

    public const KOL_MIN_FOLLOWERS = 1500;

    protected $fillable = [
        'school_id',
        'campus_id',
        'academic_period_id',
        'school_event_proposal_id',
        'school_name',
        'student_name',
        'gender',
        'student_class',
        'student_major',
        'student_phone',
        'student_email',
        'student_social_media',
        'instagram_handle',
        'instagram_followers',
        'tiktok_handle',
        'tiktok_followers',
        'is_kol',
        'kol_status',
        'kol_notes',
        'guardian_name',
        'guardian_phone',
        'guardian_social_media',
        'scholarship_interest',
        'source',
        'assigned_to',
        'assigned_by',
        'assigned_at',
        'answers',
        'followup_status',
        'last_followup_at',
        'last_followed_up_by',
        'followup_notes',
        'college_interest_notes',
        'scholarship_notes',
    ];

    protected $casts = [
        'answers' => 'array',
        'last_followup_at' => 'datetime',
        'assigned_at' => 'datetime',
        'instagram_followers' => 'integer',
        'tiktok_followers' => 'integer',
        'is_kol' => 'boolean',
    ];

    protected $attributes = [
        'is_kol' => false,
        'kol_status' => self::KOL_STATUS_KANDIDAT,
        'instagram_followers' => 0,
        'tiktok_followers' => 0,
    ];

    public static function followupStatusOptions(): array
    {
        return [
            self::STATUS_BELUM_DIHUBUNGI => 'Belum Dihubungi',
            self::STATUS_NO_RESPON => 'No Respon',
            self::STATUS_RESPON => 'Respon',
            self::STATUS_BERMINAT => 'Berminat',
            self::STATUS_DAFTAR_APLIKASI => 'Daftar Aplikasi',
        ];
    }

    public function getGenderLabelAttribute(): string
    {
        return match($this->gender) {
            'L' => 'Laki-laki',
            'P' => 'Perempuan',
            default => '-'
        };
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function academicPeriod()
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    protected static function booted(): void
    {
        static::saving(function (StudentQuestionnaire $student) {
            $igFollowers = (int) ($student->instagram_followers ?? 0);
            $tiktokFollowers = (int) ($student->tiktok_followers ?? 0);

            // Jika followers instagram >= 1500 atau tiktok >= 1500, otomatis kandidat KOL
            if ($igFollowers >= self::KOL_MIN_FOLLOWERS || $tiktokFollowers >= self::KOL_MIN_FOLLOWERS) {
                $student->is_kol = true;
                if (empty($student->kol_status)) {
                    $student->kol_status = self::KOL_STATUS_KANDIDAT;
                }
            } else {
                if ($student->is_kol === null) {
                    $student->is_kol = false;
                }
            }
        });

        static::saved(function (StudentQuestionnaire $student) {
            if (in_array($student->followup_status, [self::STATUS_DAFTAR_APLIKASI, 'closing', 'daftar_aplikasi'])) {
                \App\Services\ProspectSyncService::syncStudentQuestionnaire($student);
            }
        });
    }

    /**
     * Scope: Hanya ambil kandidat KOL (berdasarkan flag is_kol atau follower >= 1500)
     */
    public function scopeKolCandidates($query)
    {
        return $query->where(function ($q) {
            $q->where('is_kol', true)
              ->orWhere('instagram_followers', '>=', self::KOL_MIN_FOLLOWERS)
              ->orWhere('tiktok_followers', '>=', self::KOL_MIN_FOLLOWERS);
        });
    }

    public static function kolStatusOptions(): array
    {
        return [
            self::KOL_STATUS_KANDIDAT => 'Kandidat',
            self::KOL_STATUS_DIHUBUNGI => 'Sudah Dihubungi',
            self::KOL_STATUS_BERSEDIA => 'Bersedia / Deal',
            self::KOL_STATUS_DITOLAK => 'Tidak Bersedia / Menolak',
            self::KOL_STATUS_AKTIF => 'Aktif KOL UBSI',
        ];
    }

    public function getKolStatusLabelAttribute(): string
    {
        return self::kolStatusOptions()[$this->kol_status] ?? ucfirst($this->kol_status ?? 'Kandidat');
    }

    public function getCleanInstagramHandleAttribute(): ?string
    {
        $handle = $this->instagram_handle ?: $this->student_social_media;
        if (!$handle) {
            return null;
        }
        $handle = trim($handle);
        $handle = ltrim($handle, '@');
        if (str_contains($handle, 'instagram.com/')) {
            $parts = explode('instagram.com/', $handle);
            $handle = trim($parts[1] ?? '', '/');
        }
        $handle = explode(' ', $handle)[0];
        $handle = explode('?', $handle)[0];
        return $handle ?: null;
    }

    public function getInstagramUrlAttribute(): ?string
    {
        $handle = $this->clean_instagram_handle;
        return $handle ? "https://instagram.com/{$handle}" : null;
    }

    public function syncToProspect(array $extra = []): ?Prospect
    {
        return \App\Services\ProspectSyncService::syncStudentQuestionnaire($this, $extra);
    }

    public function getSchoolDisplayNameAttribute(): string
    {
        return $this->school?->name ?? $this->school_name ?? '-';
    }

    public function eventProposal()
    {
        return $this->belongsTo(SchoolEventProposal::class, 'school_event_proposal_id');
    }

    public function followUps()
    {
        return $this->hasMany(StudentFollowUp::class, 'student_questionnaire_id')->latest('followup_date');
    }

    public function lastFollowedUpBy()
    {
        return $this->belongsTo(User::class, 'last_followed_up_by');
    }

    public function prospect()
    {
        return $this->hasOne(Prospect::class, 'phone', 'student_phone');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    public function getFollowupStatusLabelAttribute(): string
    {
        if ($this->followup_status === 'closing' || $this->followup_status === self::STATUS_DAFTAR_APLIKASI) {
            return 'Daftar Aplikasi';
        }
        return self::followupStatusOptions()[$this->followup_status] ?? 'Belum Dihubungi';
    }

    public function getFollowupBadgeClassAttribute(): string
    {
        return match ($this->followup_status) {
            self::STATUS_NO_RESPON => 'bg-rose-50 text-rose-700 border border-rose-200',
            self::STATUS_RESPON => 'bg-blue-50 text-blue-700 border border-blue-200',
            self::STATUS_BERMINAT => 'bg-amber-50 text-amber-800 border border-amber-200',
            self::STATUS_DAFTAR_APLIKASI, 'closing' => 'bg-emerald-50 text-emerald-800 border border-emerald-300 font-bold',
            default => 'bg-slate-100 text-slate-600 border border-slate-200',
        };
    }

    /**
     * Clean and format Indonesian phone number for WhatsApp link
     */
    public function getFormattedStudentPhoneAttribute(): string
    {
        return $this->formatWaNumber($this->student_phone);
    }

    public function getFormattedGuardianPhoneAttribute(): string
    {
        return $this->formatWaNumber($this->guardian_phone);
    }

    private function formatWaNumber(?string $phone): string
    {
        if (!$phone) {
            return '';
        }
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }
        return $digits;
    }
}
