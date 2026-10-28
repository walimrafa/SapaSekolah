<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolActivity extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'school_id',
        'activity_id',
        'date',
        'participants',
        'result',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'participants' => 'integer',
    ];

    /**
     * SchoolActivity belongsTo School.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * SchoolActivity belongsTo MarketingActivity.
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(MarketingActivity::class, 'activity_id');
    }
}
