<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolScore extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'school_id',
        'criterion_id',
        'score',
        'weighted_score',
        'period',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'weighted_score' => 'decimal:2',
    ];

    /**
     * SchoolScore belongsTo School.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * SchoolScore belongsTo ScoringCriterion.
     */
    public function criterion(): BelongsTo
    {
        return $this->belongsTo(ScoringCriterion::class, 'criterion_id');
    }
}
