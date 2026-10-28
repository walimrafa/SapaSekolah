<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prospect extends Model
{
    public const STATUS_DAFTAR_AKUN = 'daftar_akun';
    public const STATUS_BAYAR_REGISTRASI = 'bayar_registrasi';
    public const STATUS_UJIAN_SARINGAN = 'ujian_saringan';
    public const STATUS_BAYAR_DAFTAR_ULANG = 'bayar_daftar_ulang';
    public const STATUS_BERKAS_LENGKAP = 'berkas_lengkap';
    public const STATUS_MAHASISWA = 'mahasiswa';
    public const STATUS_TIDAK_LANJUT = 'tidak_lanjut';

    public static function getStatusLabels(): array
    {
        return [
            self::STATUS_DAFTAR_AKUN => '1. Sudah Daftar Akun',
            self::STATUS_BAYAR_REGISTRASI => '2. Sudah Bayar Registrasi',
            self::STATUS_UJIAN_SARINGAN => '3. Sudah Ujian Saringan',
            self::STATUS_BAYAR_DAFTAR_ULANG => '4. Sudah Bayar Daftar Ulang',
            self::STATUS_BERKAS_LENGKAP => '5. Sudah Melengkapi Berkas dan Menjadi Mahasiswa Baru',
            self::STATUS_MAHASISWA => 'Mahasiswa Baru (Tervalidasi)',
            self::STATUS_TIDAK_LANJUT => 'Tidak Lanjut / Batal',
        ];
    }

    protected $fillable = [
        'nim',
        'school_id',
        'name',
        'gender',
        'phone',
        'email',
        'graduation_year',
        'interest_program',
        'study_time',
        'scholarship_type',
        'source',
        'status',
        'marketing_user_id',
        'notes',
        'validated_at',
        'validated_by',
    ];

    protected $casts = [
        'graduation_year' => 'integer',
        'validated_at' => 'datetime',
    ];

    protected $appends = [
        'status_label',
        'study_time_label',
        'scholarship_badge',
    ];

    /**
     * Prospect belongsTo School.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Prospect belongsTo User (marketing).
     */
    public function marketingUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marketing_user_id');
    }

    /**
     * Prospect validatedBy User.
     */
    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // ==================== SCOPES ====================

    /**
     * Scope: filter berdasarkan status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: prospek pipeline aktif (Tahap 1 s/d Tahap 5).
     */
    public function scopePipeline($query)
    {
        return $query->whereIn('status', [
            self::STATUS_DAFTAR_AKUN,
            self::STATUS_BAYAR_REGISTRASI,
            self::STATUS_UJIAN_SARINGAN,
            self::STATUS_BAYAR_DAFTAR_ULANG,
            self::STATUS_BERKAS_LENGKAP,
        ]);
    }

    /**
     * Scope: prospek yang sudah menjadi mahasiswa resmi tervalidasi.
     */
    public function scopeConverted($query)
    {
        return $query->where('status', self::STATUS_MAHASISWA);
    }

    /**
     * Scope: prospek yang siap divalidasi ke mahasiswa baru (Tahap 5).
     */
    public function scopeReadyForValidation($query)
    {
        return $query->where('status', self::STATUS_BERKAS_LENGKAP);
    }

    /**
     * Scope: mahasiswa / prospek jalur beasiswa (KIP, Prestasi, Jalur Sekolah, Talenta, dsb).
     */
    public function scopeScholarship($query)
    {
        return $query->where(function ($q) {
            $q->where(function ($sub) {
                $sub->where('scholarship_type', 'like', '%beasiswa%')
                    ->orWhere('scholarship_type', 'like', '%kip%')
                    ->orWhere('scholarship_type', 'like', '%prestasi%')
                    ->orWhere('scholarship_type', 'like', '%talenta%');
            })
            ->where(function ($sub) {
                $sub->where('scholarship_type', 'not like', '%non beasiswa%')
                    ->where('scholarship_type', 'not like', '%mandiri%');
            });
        });
    }

    /**
     * Scope: mahasiswa / prospek jalur umum non beasiswa (Reguler Mandiri).
     */
    public function scopeNonScholarship($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('scholarship_type')
              ->orWhere('scholarship_type', '')
              ->orWhere('scholarship_type', 'like', '%non beasiswa%')
              ->orWhere('scholarship_type', 'like', '%mandiri%');
        });
    }

    // ==================== ACCESSORS & HELPERS ====================

    public function getStatusLabelAttribute(): string
    {
        return self::getStatusLabels()[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function isReadyForValidation(): bool
    {
        return $this->status === self::STATUS_BERKAS_LENGKAP;
    }

    public function isConvertedToStudent(): bool
    {
        return $this->status === self::STATUS_MAHASISWA;
    }

    public function getStudyTimeLabelAttribute(): ?string
    {
        if (!$this->study_time) {
            return null;
        }

        $options = StudyProgram::getStudyTimeOptions();
        return $options[$this->study_time]['label'] ?? $this->study_time;
    }

    public function getStudyTimeBadgeAttribute(): array
    {
        if (!$this->study_time) {
            return [
                'label' => 'Belum Dipilih',
                'badge_class' => 'bg-slate-100 text-slate-500 border-slate-200',
                'icon' => 'clock',
            ];
        }

        $options = StudyProgram::getStudyTimeOptions();
        if (isset($options[$this->study_time])) {
            return $options[$this->study_time];
        }

        return [
            'label' => $this->study_time,
            'badge_class' => 'bg-brand-50 text-brand-700 border-brand-200',
            'icon' => 'clock',
        ];
    }

    public function isScholarship(): bool
    {
        if (!$this->scholarship_type) {
            return false;
        }

        $lower = strtolower($this->scholarship_type);
        if (str_contains($lower, 'non beasiswa') || str_contains($lower, 'mandiri')) {
            return false;
        }

        return str_contains($lower, 'beasiswa') || str_contains($lower, 'kip') || str_contains($lower, 'prestasi') || str_contains($lower, 'talenta');
    }

    public function getScholarshipBadgeAttribute(): array
    {
        if (!$this->scholarship_type) {
            return [
                'label' => 'Non Beasiswa',
                'is_scholarship' => false,
                'badge_class' => 'bg-slate-100 text-slate-700 border-slate-300',
                'icon' => 'user-check',
            ];
        }

        $isScholarship = $this->isScholarship();

        if ($isScholarship) {
            return [
                'label' => $this->scholarship_type,
                'is_scholarship' => true,
                'badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-300',
                'icon' => 'award',
            ];
        }

        return [
            'label' => $this->scholarship_type,
            'is_scholarship' => false,
            'badge_class' => 'bg-slate-100 text-slate-700 border-slate-300',
            'icon' => 'user-check',
        ];
    }
}
