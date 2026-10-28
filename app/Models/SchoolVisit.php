<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolVisit extends Model
{
    protected $fillable = [
        'school_id',
        'user_id',
        'visit_plan_id',
        'visit_date',
        'start_time',
        'end_time',
        'latitude',
        'longitude',
        'contact_person',
        'recipient_name',
        'recipient_position',
        'grade_12_class_count',
        'students_per_class',
        'target_month_year',
        'target_date',
        'target_time',
        'socialization_method',
        'other_activity_name',
        'purpose',
        'result',
        'photo',
        'notes',
        'next_followup_date',
        'status',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'target_date' => 'date',
        'next_followup_date' => 'date',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * SchoolVisit belongsTo School.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * SchoolVisit belongsTo User (marketing).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * SchoolVisit belongsTo VisitPlan.
     */
    public function visitPlan(): BelongsTo
    {
        return $this->belongsTo(VisitPlan::class);
    }

    /**
     * SchoolVisit memiliki banyak VisitResult.
     */
    public function results(): HasMany
    {
        return $this->hasMany(VisitResult::class, 'visit_id');
    }

    /**
     * SchoolVisit memiliki banyak FollowUp.
     */
    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class, 'visit_id');
    }

    /**
     * SchoolVisit memiliki banyak tracking.
     */
    public function trackings(): HasMany
    {
        return $this->hasMany(MarketingTracking::class, 'visit_id');
    }

    /**
     * SchoolVisit memiliki banyak dokumen.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(VisitDocument::class, 'visit_id');
    }
}
