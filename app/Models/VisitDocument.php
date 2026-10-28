<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitDocument extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'visit_id',
        'file_name',
        'file_path',
        'file_type',
        'uploaded_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * VisitDocument belongsTo SchoolVisit.
     */
    public function visit(): BelongsTo
    {
        return $this->belongsTo(SchoolVisit::class, 'visit_id');
    }

    /**
     * VisitDocument belongsTo User (uploader).
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
