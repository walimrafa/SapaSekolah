<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolPotential extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'school_id',
        'total_students',
        'grade_12_students',
        'estimated_college_interest',
        'estimated_it_interest',
        'estimated_business_interest',
        'previous_students',
        'competitor_count',
        'cooperation_status',
        'potential_score',
        'notes',
    ];

    protected $casts = [
        'estimated_college_interest' => 'decimal:2',
        'estimated_it_interest' => 'decimal:2',
        'estimated_business_interest' => 'decimal:2',
        'potential_score' => 'decimal:2',
        'total_students' => 'integer',
        'grade_12_students' => 'integer',
        'previous_students' => 'integer',
        'competitor_count' => 'integer',
    ];

    /**
     * SchoolPotential belongsTo School.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
