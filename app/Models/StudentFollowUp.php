<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentFollowUp extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_questionnaire_id',
        'user_id',
        'followup_date',
        'contact_method',
        'status',
        'scholarship_interest_notes',
        'college_interest_notes',
        'notes',
        'next_followup_date',
    ];

    protected $casts = [
        'followup_date' => 'date',
        'next_followup_date' => 'date',
    ];

    public const STATUS_NO_RESPON = 'no_respon';
    public const STATUS_RESPON = 'respon';
    public const STATUS_BERMINAT = 'berminat';
    public const STATUS_DAFTAR_APLIKASI = 'daftar_aplikasi';
    public const STATUS_CLOSING = 'daftar_aplikasi'; // Alias for backwards compatibility

    public static function statusLabels(): array
    {
        return [
            self::STATUS_NO_RESPON => 'No Respon',
            self::STATUS_RESPON => 'Respon',
            self::STATUS_BERMINAT => 'Berminat',
            self::STATUS_DAFTAR_APLIKASI => 'Daftar Aplikasi',
        ];
    }

    public static function contactMethods(): array
    {
        return [
            'wa' => 'WhatsApp',
            'telepon' => 'Telepon / Panggilan Suara',
            'email' => 'Email',
            'tatap_muka' => 'Tatap Muka / Sekolah',
            'lainnya' => 'Lainnya',
        ];
    }

    public function student()
    {
        return $this->belongsTo(StudentQuestionnaire::class, 'student_questionnaire_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->status === 'closing' || $this->status === self::STATUS_DAFTAR_APLIKASI) {
            return 'Daftar Aplikasi';
        }
        return self::statusLabels()[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_NO_RESPON => 'bg-slate-100 text-slate-700 border-slate-300',
            self::STATUS_RESPON => 'bg-blue-100 text-blue-800 border-blue-200',
            self::STATUS_BERMINAT => 'bg-amber-100 text-amber-800 border-amber-200',
            self::STATUS_DAFTAR_APLIKASI, 'closing' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            default => 'bg-slate-100 text-slate-600 border-slate-200',
        };
    }
}
