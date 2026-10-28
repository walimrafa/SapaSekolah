<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentQuestionnaire extends Model
{
    use HasFactory;

    public const STATUS_BELUM_DIHUBUNGI = 'belum_dihubungi';
    public const STATUS_NO_RESPON = 'no_respon';
    public const STATUS_RESPON = 'respon';
    public const STATUS_BERMINAT = 'berminat';
    public const STATUS_DAFTAR_APLIKASI = 'daftar_aplikasi';
    public const STATUS_CLOSING = 'daftar_aplikasi'; // Alias for backwards compatibility

    protected $fillable = [
        'school_id',
        'school_event_proposal_id',
        'school_name',
        'student_name',
        'student_class',
        'student_major',
        'student_phone',
        'student_email',
        'student_social_media',
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

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
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
