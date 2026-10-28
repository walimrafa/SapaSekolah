<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class SchoolEventProposal extends Model
{
    use SoftDeletes;

    public const STATUS_MENUNGGU_REVIEW = 'menunggu_review';
    public const STATUS_DIAJUKAN_KE_PUSAT = 'diajukan_ke_pusat';
    public const STATUS_MENUNGGU_ACC = 'menunggu_acc';
    public const STATUS_DISETUJUI = 'disetujui';
    public const STATUS_DITOLAK = 'ditolak';

    public const EXEC_MENUNGGU_PELAKSANAAN = 'menunggu_pelaksanaan';
    public const EXEC_SEDANG_BERJALAN = 'sedang_berjalan';
    public const EXEC_SELESAI = 'selesai';

    protected $fillable = [
        'reference_no',
        'school_id',
        'school_name',
        'pic_name',
        'pic_phone',
        'pic_position',
        'event_name',
        'event_date',
        'event_end_date',
        'event_time',
        'event_location',
        'target_participants',
        'support_type_requested',
        'estimated_budget',
        'proposal_file_path',
        'proposal_file_name',
        'proposal_file_size',
        'status',
        'status_notes',
        'approved_amount',
        'approved_support_items',
        'reviewed_by',
        'reviewed_at',
        'execution_status',
        'documentation_photos',
        'report_description',
        'realized_participants',
        'prospects_acquired',
        'reported_by',
        'reported_at',
    ];

    protected $casts = [
        'event_date' => 'date',
        'event_end_date' => 'date',
        'estimated_budget' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'reviewed_at' => 'datetime',
        'reported_at' => 'datetime',
        'documentation_photos' => 'array',
        'realized_participants' => 'integer',
        'prospects_acquired' => 'integer',
    ];

    protected $appends = [
        'status_label',
        'execution_status_label',
        'proposal_file_url',
        'documentation_items',
    ];

    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_MENUNGGU_REVIEW => 'Menunggu Review',
            self::STATUS_DIAJUKAN_KE_PUSAT => 'Diajukan ke Pusat',
            self::STATUS_MENUNGGU_ACC => 'Menunggu ACC Pusat',
            self::STATUS_DISETUJUI => 'Disetujui',
            self::STATUS_DITOLAK => 'Ditolak',
        ];
    }

    public static function getExecutionStatusOptions(): array
    {
        return [
            self::EXEC_MENUNGGU_PELAKSANAAN => 'Menunggu Pelaksanaan',
            self::EXEC_SEDANG_BERJALAN => 'Sedang Berjalan',
            self::EXEC_SELESAI => 'Selesai & Terdokumentasi',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatusOptions()[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getExecutionStatusLabelAttribute(): string
    {
        return self::getExecutionStatusOptions()[$this->execution_status] ?? ucfirst(str_replace('_', ' ', $this->execution_status));
    }

    public function getProposalFileUrlAttribute(): string
    {
        return $this->proposal_file_path ? Storage::url($this->proposal_file_path) : '#';
    }

    public function getDocumentationItemsAttribute(): array
    {
        $items = [];
        $raw = $this->documentation_photos ?? [];
        foreach ($raw as $entry) {
            if (is_array($entry)) {
                $path = $entry['path'] ?? '';
                $name = $entry['name'] ?? basename($path);
                $size = $entry['size'] ?? 0;
            } else {
                $path = $entry;
                $name = basename($path);
                $size = Storage::disk('public')->exists($path) ? Storage::disk('public')->size($path) : 0;
            }

            if (!$path) continue;

            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
            $isArchive = in_array($ext, ['rar', 'zip', '7z', 'tar', 'gz']);
            $isDoc = in_array($ext, ['pdf', 'doc', 'docx']);

            $items[] = [
                'path' => $path,
                'url' => Storage::url($path),
                'name' => $name,
                'size' => $size,
                'size_formatted' => $size ? round($size / (1024 * 1024), 2) . ' MB' : '',
                'extension' => $ext,
                'is_image' => $isImage,
                'is_archive' => $isArchive,
                'is_doc' => $isDoc,
            ];
        }
        return $items;
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function studentLeads()
    {
        return $this->hasMany(StudentQuestionnaire::class, 'school_event_proposal_id');
    }

    public static function generateReferenceNo(): string
    {
        $prefix = 'EVT-' . date('Ymd') . '-';
        $latest = self::where('reference_no', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('reference_no');

        if ($latest) {
            $number = (int) substr($latest, strlen($prefix)) + 1;
        } else {
            $number = 1;
        }

        return $prefix . str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }
}
