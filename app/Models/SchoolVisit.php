<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\SchoolVisit
 *
 * @property int $id
 * @property int $school_id
 * @property string|null $headmaster_name
 * @property int|null $user_id
 * @property int|null $visit_plan_id
 * @property Carbon|null $visit_date
 * @property string|null $start_time
 * @property string|null $end_time
 * @property string|null $latitude
 * @property string|null $longitude
 * @property string|null $contact_person
 * @property string|null $recipient_name
 * @property string|null $recipient_position
 * @property string|null $recipient_phone
 * @property int|null $grade_12_class_count
 * @property int|null $students_per_class
 * @property string|null $target_month_year
 * @property Carbon|null $target_date
 * @property string|null $target_time
 * @property string|null $socialization_method
 * @property string|null $other_activity_name
 * @property string|null $purpose
 * @property string|null $result
 * @property string|null $photo
 * @property string|null $google_drive_folder_url
 * @property string|null $google_drive_file_url
 * @property string|null $notes
 * @property Carbon|null $next_followup_date
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\School|null $school
 * @property-read \App\Models\User|null $user
 */
class SchoolVisit extends Model
{
    protected $fillable = [
        'school_id',
        'campus_id',
        'academic_period_id',
        'headmaster_name',
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
        'recipient_phone',
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
        'google_drive_folder_url',
        'google_drive_file_url',
        'notes',
        'next_followup_date',
        'status',
    ];

    /**
     * Get effective Google Drive Folder URL for this visit.
     */
    public function getDriveFolderUrlAttribute(): string
    {
        if (!empty($this->attributes['google_drive_folder_url'])) {
            return $this->attributes['google_drive_folder_url'];
        }

        return \App\Services\GoogleDriveService::buildFolderUrlForSchool(
            $this->school?->name ?? 'Sekolah',
            $this->visit_date ? Carbon::parse($this->visit_date)->format('Y-m-d') : null
        );
    }

    protected $casts = [
        'visit_date' => 'datetime',
        'target_date' => 'datetime',
        'next_followup_date' => 'datetime',
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

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class);
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
