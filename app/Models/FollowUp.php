<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FollowUp extends Model
{
    use SoftDeletes;

    protected $table = 'follow_ups';

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROGRESS = 'progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'school_id',
        'visit_id',
        'user_id',
        'followup_date',
        'method',
        'description',
        'notes',
        'result',
        'next_followup_date',
        'status',
        'fixed_date',
        'fixed_time',
        'pic_name',
        'pic_phone',
        'pic_position',
        'socialization_location',
        'is_schedule_fixed',
        'target_audience_count',
        'socialization_completed_at',
        'socialization_completion_notes',
    ];

    protected $casts = [
        'followup_date' => 'date',
        'next_followup_date' => 'date',
        'fixed_date' => 'date',
        'is_schedule_fixed' => 'boolean',
        'target_audience_count' => 'integer',
        'socialization_completed_at' => 'date',
    ];

    public static function statusLabels(): array
    {
        return [
            self::STATUS_PENDING => 'Menunggu Konfirmasi',
            self::STATUS_PROGRESS => 'Koordinasi Jadwal',
            self::STATUS_COMPLETED => 'Jadwal Sosialisasi FIX',
            self::STATUS_FAILED => 'Ditolak / Batal',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'bg-amber-100 text-amber-800 border-amber-300',
            self::STATUS_PROGRESS => 'bg-blue-100 text-blue-800 border-blue-300',
            self::STATUS_COMPLETED => 'bg-emerald-100 text-emerald-800 border-emerald-300 font-bold',
            self::STATUS_FAILED => 'bg-rose-100 text-rose-800 border-rose-300',
            default => 'bg-slate-100 text-slate-700 border-slate-300',
        };
    }

    /**
     * Get clean WhatsApp link for PIC
     */
    public function getWaLinkAttribute(): ?string
    {
        $phone = $this->pic_phone ?: ($this->school?->primaryContact?->phone ?? $this->school?->phone);
        if (!$phone) {
            return null;
        }

        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }

        $schoolName = $this->school?->name ?? 'Sekolah';
        $targetStr = $this->fixed_date ? $this->fixed_date->format('d M Y') : ($this->visit?->target_month_year ?: 'jadwal sosialisasi');
        $msg = urlencode("Selamat pagi/siang Bapak/Ibu {$this->pic_name}, salam hangat dari Tim Kampus Universitas BSI (UBSI). Menindaklanjuti kunjungan kami ke {$schoolName}, kami ingin konfirmasi terkait penetapan FIX jadwal sosialisasi kampus untuk siswa kelas 12 (rencana tanggal/bulan: {$targetStr}). Apakah jadwal tersebut sudah dapat kami kunci? Terima kasih.");

        return "https://wa.me/{$digits}?text={$msg}";
    }

    /**
     * FollowUp belongsTo School.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * FollowUp belongsTo SchoolVisit.
     */
    public function visit(): BelongsTo
    {
        return $this->belongsTo(SchoolVisit::class, 'visit_id');
    }

    /**
     * FollowUp belongsTo User (marketing).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==================== SCOPES ====================

    /**
     * Scope: follow-up hari ini.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('followup_date', today());
    }

    /**
     * Scope: follow-up terlambat.
     */
    public function scopeOverdue($query)
    {
        return $query->where('followup_date', '<', today())
                     ->whereIn('status', [self::STATUS_PENDING, self::STATUS_PROGRESS]);
    }

    /**
     * Scope: follow-up 7 hari ke depan.
     */
    public function scopeUpcoming($query, int $days = 7)
    {
        return $query->whereBetween('followup_date', [today(), today()->addDays($days)])
                     ->whereIn('status', [self::STATUS_PENDING, self::STATUS_PROGRESS]);
    }
}
