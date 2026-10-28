<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'writing_style',
        'opinion_author',
        'category',
        'campus_context',
        'is_event',
        'event_category',
        'event_name',
        'event_theme',
        'event_date',
        'event_time',
        'event_location',
        'event_organizer',
        'event_host',
        'event_speaker',
        'event_executor',
        'event_participants',
        'event_location_time',
        'featured_image',
        'image_source',
        'content',
        'word_count',
        'status',
        'error_message',
        'retry_count',
        'docx_path',
        'scheduled_at',
        'user_id',
    ];

    protected $casts = [
        'is_event' => 'boolean',
        'scheduled_at' => 'datetime',
        'word_count' => 'integer',
        'retry_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function canRetry(): bool
    {
        return $this->status === 'failed';
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'completed' => [
                'label' => 'Selesai',
                'bg' => 'bg-emerald-500/15 text-emerald-700 border-emerald-300 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800',
                'icon' => 'check-circle-2',
            ],
            'processing' => [
                'label' => 'Diproses...',
                'bg' => 'bg-blue-500/15 text-blue-700 border-blue-300 dark:bg-blue-950/40 dark:text-blue-400 dark:border-blue-800',
                'icon' => 'loader-2',
            ],
            'pending' => [
                'label' => 'Menunggu Jadwal',
                'bg' => 'bg-amber-500/15 text-amber-700 border-amber-300 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-800',
                'icon' => 'clock',
            ],
            'failed' => [
                'label' => 'Gagal',
                'bg' => 'bg-rose-500/15 text-rose-700 border-rose-300 dark:bg-rose-950/40 dark:text-rose-400 dark:border-rose-800',
                'icon' => 'alert-triangle',
            ],
            default => [
                'label' => ucfirst($this->status),
                'bg' => 'bg-slate-500/15 text-slate-700 border-slate-300',
                'icon' => 'help-circle',
            ],
        };
    }
}
