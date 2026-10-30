<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
        'is_current',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'is_current' => 'boolean',
    ];

    /**
     * Get current active academic period.
     */
    public static function getCurrent(): ?self
    {
        return static::where('is_current', true)->first() 
            ?? static::where('is_active', true)->latest('id')->first()
            ?? static::latest('id')->first();
    }

    public function schoolVisits(): HasMany
    {
        return $this->hasMany(SchoolVisit::class);
    }

    public function visitPlans(): HasMany
    {
        return $this->hasMany(VisitPlan::class);
    }

    public function prospects(): HasMany
    {
        return $this->hasMany(Prospect::class);
    }

    public function studentQuestionnaires(): HasMany
    {
        return $this->hasMany(StudentQuestionnaire::class);
    }

    public function schoolEventProposals(): HasMany
    {
        return $this->hasMany(SchoolEventProposal::class);
    }
}

