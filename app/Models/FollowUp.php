<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\FollowUp
 *
 * @property int $id
 * @property int $school_id
 * @property int|null $visit_id
 * @property int|null $user_id
 * @property Carbon|null $followup_date
 * @property string|null $method
 * @property string|null $description
 * @property string|null $notes
 * @property string|null $result
 * @property Carbon|null $next_followup_date
 * @property string $status
 * @property Carbon|null $fixed_date
 * @property string|null $fixed_time
 * @property string|null $pic_name
 * @property string|null $pic_phone
 * @property string|null $pic_position
 * @property string|null $socialization_location
 * @property bool $is_schedule_fixed
 * @property int|null $target_audience_count
 * @property Carbon|null $socialization_completed_at
 * @property string|null $socialization_completion_notes
 * @property string|null $documentation_file
 * @property string|null $google_drive_folder_url
 * @property string|null $google_drive_file_url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read \App\Models\School|null $school
 * @property-read \App\Models\SchoolVisit|null $visit
 * @property-read \App\Models\User|null $user
 */
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
        'campus_id',
        'academic_period_id',
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
        'documentation_file',
        'google_drive_folder_url',
        'google_drive_file_url',
    ];

    /**
     * Get effective Google Drive Folder URL for this socialization.
     */
    public function getDriveFolderUrlAttribute(): string
    {
        if (!empty($this->attributes['google_drive_folder_url'])) {
            return $this->attributes['google_drive_folder_url'];
        }

        if ($this->visit && !empty($this->visit->google_drive_folder_url)) {
            return $this->visit->google_drive_folder_url;
        }

        $targetDate = $this->socialization_completed_at ?: $this->fixed_date;
        $date = $targetDate ? Carbon::parse($targetDate)->format('Y-m-d') : null;

        return \App\Services\GoogleDriveService::buildFolderUrlForSchool(
            $this->school?->name ?? 'Sekolah',
            $date
        );
    }

    protected $casts = [
        'followup_date' => 'datetime',
        'next_followup_date' => 'datetime',
        'fixed_date' => 'datetime',
        'is_schedule_fixed' => 'boolean',
        'target_audience_count' => 'integer',
        'socialization_completed_at' => 'datetime',
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
        $targetStr = $this->fixed_date ? Carbon::parse($this->fixed_date)->format('d M Y') : ($this->visit?->target_month_year ?: 'jadwal sosialisasi');
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

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class);
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
