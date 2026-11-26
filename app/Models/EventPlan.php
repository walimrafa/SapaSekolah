<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventPlan extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_AKTIF = 'aktif';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_DIBATALKAN = 'dibatalkan';

    protected $fillable = [
        'campus_id',
        'academic_period_id',
        'title',
        'event_date',
        'event_time',
        'location',
        'target_audience',
        'quota',
        'description',
        'status',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'event_date' => 'date',
        'is_active' => 'boolean',
        'quota' => 'integer',
    ];

    /**
     * Relasi ke Kampus UBSI.
     */
    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class, 'campus_id');
    }

    /**
     * Relasi ke Periode Akademik.
     */
    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class, 'academic_period_id');
    }

    /**
     * Relasi ke Pengguna Pembuat Event.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke Kunjungan Lapangan yang menyebarkan undangan event ini.
     */
    public function schoolVisits(): HasMany
    {
        return $this->hasMany(SchoolVisit::class, 'event_plan_id');
    }

    /**
     * Relasi ke Follow Up Kunjungan yang terkait event ini.
     */
    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class, 'event_plan_id');
    }

    /**
     * Scope: Hanya event yang aktif dan tidak dibatalkan.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', '!=', self::STATUS_DIBATALKAN);
    }

    /**
     * Scope: Hanya event mendatang yang belum kadaluarsa (tgl event >= hari ini).
     * Sesuai request: Event yang sudah lewat/kadaluarsa otomatis hilang dari list input sebar undangan.
     */
    public function scopeUpcoming($query)
    {
        return $query->active()
            ->where('event_date', '>=', now()->toDateString())
            ->orderBy('event_date', 'asc')
            ->orderBy('event_time', 'asc');
    }

    /**
     * Scope: Event yang sudah lewat dari tanggal sekarang (kadaluarsa).
     */
    public function scopeExpired($query)
    {
        return $query->where(function ($q) {
            $q->where('event_date', '<', now()->toDateString())
              ->orWhere('status', self::STATUS_SELESAI);
        });
    }

    /**
     * Scope: Filter berdasarkan kampus penugasan atau pusat (all).
     */
    public function scopeForCampus($query, $campusId)
    {
        if (empty($campusId)) {
            return $query;
        }

        return $query->where(function ($q) use ($campusId) {
            $q->where('campus_id', $campusId)
              ->orWhereNull('campus_id'); // Event pusat / umum
        });
    }

    /**
     * Cek apakah event sudah kadaluarsa (tanggal lewat dari hari ini).
     */
    public function getIsExpiredAttribute(): bool
    {
        if ($this->status === self::STATUS_SELESAI) {
            return true;
        }

        $eventDate = $this->event_date instanceof Carbon ? $this->event_date : Carbon::parse($this->event_date);
        return $eventDate->startOfDay()->isPast() && !$eventDate->isToday();
    }

    /**
     * Cek apakah event berlangsung hari ini.
     */
    public function getIsTodayAttribute(): bool
    {
        $eventDate = $this->event_date instanceof Carbon ? $this->event_date : Carbon::parse($this->event_date);
        return $eventDate->isToday();
    }

    /**
     * Hitung sisa hari / status waktu event.
     */
    public function getDaysRemainingAttribute(): string
    {
        if ($this->status === self::STATUS_DIBATALKAN) {
            return 'Dibatalkan';
        }

        $eventDate = $this->event_date instanceof Carbon ? $this->event_date : Carbon::parse($this->event_date);
        $today = now()->startOfDay();
        $target = $eventDate->copy()->startOfDay();

        if ($target->equalTo($today)) {
            return 'Hari Ini!';
        } elseif ($target->greaterThan($today)) {
            $diff = $today->diffInDays($target);
            return "H-{$diff} (" . ($diff === 1 ? 'Besok' : "{$diff} hari lagi") . ")";
        } else {
            $diff = $target->diffInDays($today);
            return "Kadaluarsa ({$diff} hari lalu)";
        }
    }

    /**
     * Format Tanggal Bahasa Indonesia.
     */
    public function getFormattedDateAttribute(): string
    {
        $eventDate = $this->event_date instanceof Carbon ? $this->event_date : Carbon::parse($this->event_date);
        return $eventDate->translatedFormat('l, d F Y');
    }

    /**
     * Badge visual status event.
     */
    public function getStatusBadgeAttribute(): array
    {
        if ($this->status === self::STATUS_DIBATALKAN || !$this->is_active) {
            return [
                'label' => 'Nonaktif / Batal',
                'class' => 'bg-rose-100 text-rose-800 border-rose-200',
                'dot' => 'bg-rose-500',
            ];
        }

        if ($this->is_today) {
            return [
                'label' => 'Berlangsung Hari Ini',
                'class' => 'bg-amber-100 text-amber-900 border-amber-300 font-bold animate-pulse',
                'dot' => 'bg-amber-500',
            ];
        }

        if ($this->is_expired) {
            return [
                'label' => 'Kadaluarsa / Selesai',
                'class' => 'bg-slate-100 text-slate-600 border-slate-200',
                'dot' => 'bg-slate-400',
            ];
        }

        return [
            'label' => 'Mendatang (Aktif)',
            'class' => 'bg-emerald-100 text-emerald-800 border-emerald-200 font-semibold',
            'dot' => 'bg-emerald-500',
        ];
    }

    /**
     * Jumlah sekolah/kunjungan yang sudah disebar undangan untuk event ini.
     */
    public function getInvitationsDistributedCountAttribute(): int
    {
        return SchoolVisit::where(function ($q) {
            $q->where('event_plan_id', $this->id)
              ->orWhere('event_name', $this->title);
        })->count();
    }
}
