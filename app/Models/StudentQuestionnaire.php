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
    public const KOL_STATUS_BRAND_AMBASSADOR = 'brand_ambassador';
    public const KOL_STATUS_AKTIF = 'aktif';
    public const KOL_STATUS_DITOLAK = 'ditolak';

    public const KOL_VERIFY_UNVERIFIED = 'unverified';
    public const KOL_VERIFY_VERIFIED = 'verified';
    public const KOL_VERIFY_REJECTED = 'rejected';

    public const KOL_MIN_FOLLOWERS = 1500;

    /**
     * Dapatkan syarat minimal follower media sosial (Instagram/TikTok) dari Master Pengaturan Sistem.
     */
    public static function getKolMinFollowers(): int
    {
        return (int) \App\Services\AppSettingService::get('kol_min_followers', self::KOL_MIN_FOLLOWERS);
    }

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
        'instagram_followers_verified',
        'kol_verification_status',
        'kol_verification_notes',
        'estimated_rate_card',
        'work_links',
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
        'estimated_rate_card' => 'array',
        'work_links' => 'array',
        'last_followup_at' => 'datetime',
        'assigned_at' => 'datetime',
        'instagram_followers' => 'integer',
        'instagram_followers_verified' => 'integer',
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
            $minFollowers = self::getKolMinFollowers();

            // Jika followers instagram >= minFollowers atau tiktok >= minFollowers, otomatis kandidat KOL
            if ($igFollowers >= $minFollowers || $tiktokFollowers >= $minFollowers) {
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
     * Scope: Hanya ambil kandidat KOL (berdasarkan flag is_kol atau follower >= syarat minimal)
     */
    public function scopeKolCandidates($query)
    {
        $minFollowers = self::getKolMinFollowers();

        return $query->where(function ($q) use ($minFollowers) {
            $q->where('is_kol', true)
              ->orWhere('instagram_followers', '>=', $minFollowers)
              ->orWhere('tiktok_followers', '>=', $minFollowers);
        });
    }

    public static function kolStatusOptions(): array
    {
        return [
            self::KOL_STATUS_KANDIDAT => 'Kandidat',
            self::KOL_STATUS_DIHUBUNGI => 'Sudah Dihubungi',
            self::KOL_STATUS_BERSEDIA => 'Bersedia / Deal',
            self::KOL_STATUS_BRAND_AMBASSADOR => 'Brand Ambassador (Fix)',
            self::KOL_STATUS_AKTIF => 'Aktif KOL UBSI',
            self::KOL_STATUS_DITOLAK => 'Tidak Bersedia / Menolak',
        ];
    }

    /**
     * Hitung estimasi Rate Card Pasaran otomatis berdasarkan jumlah followers.
     */
    public function getEstimatedRateCardAttribute(): array
    {
        if (!empty($this->attributes['estimated_rate_card'])) {
            $decoded = json_decode($this->attributes['estimated_rate_card'], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        $followers = (int) ($this->instagram_followers_verified ?: $this->instagram_followers ?: $this->tiktok_followers ?: 0);

        if ($followers < 5000) {
            $tier = 'Nano Influencer';
            $feedRange = 'Rp 50.000 - Rp 150.000';
            $storyRange = 'Rp 35.000 - Rp 75.000';
            $reelsRange = 'Rp 100.000 - Rp 250.000';
            $baPackage = 'Rp 500.000 - Rp 1.500.000 / Semester';
            $er = '5.2% - 8.5%';
        } elseif ($followers < 20000) {
            $tier = 'Micro Influencer';
            $feedRange = 'Rp 150.000 - Rp 500.000';
            $storyRange = 'Rp 75.000 - Rp 200.000';
            $reelsRange = 'Rp 300.000 - Rp 750.000';
            $baPackage = 'Rp 1.500.000 - Rp 3.500.000 / Semester';
            $er = '3.8% - 6.0%';
        } elseif ($followers < 100000) {
            $tier = 'Mid-Tier Influencer';
            $feedRange = 'Rp 500.000 - Rp 2.000.000';
            $storyRange = 'Rp 250.000 - Rp 800.000';
            $reelsRange = 'Rp 1.000.000 - Rp 3.500.000';
            $baPackage = 'Rp 3.500.000 - Rp 10.000.000 / Semester';
            $er = '2.5% - 4.5%';
        } else {
            $tier = 'Macro Influencer';
            $feedRange = 'Rp 2.500.000 - Rp 8.000.000';
            $storyRange = 'Rp 1.000.000 - Rp 3.000.000';
            $reelsRange = 'Rp 4.000.000 - Rp 15.000.000';
            $baPackage = 'Rp 10.000.000+ / Semester';
            $er = '1.8% - 3.2%';
        }

        return [
            'tier' => $tier,
            'followers_used' => $followers,
            'feed_post' => $feedRange,
            'story_post' => $storyRange,
            'reels_tiktok' => $reelsRange,
            'brand_ambassador_package' => $baPackage,
            'engagement_benchmark' => $er,
            'generated_at' => now()->toIso8601String(),
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
        return $this->hasOne(Prospect::class, 'student_questionnaire_id');
    }

    public function getProspectAttribute()
    {
        $p = $this->getRelationValue('prospect');
        if ($p) {
            return $p;
        }
        if (!empty($this->student_phone) && !empty($this->student_name)) {
            $matched = Prospect::whereNull('student_questionnaire_id')
                ->where('phone', $this->student_phone)
                ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($this->student_name))])
                ->first();
            if ($matched) {
                $matched->updateQuietly(['student_questionnaire_id' => $this->id]);
                $this->setRelation('prospect', $matched);
                return $matched;
            }
        }
        return null;
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
