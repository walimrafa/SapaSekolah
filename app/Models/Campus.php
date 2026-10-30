<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campus extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'city',
        'address',
        'latitude',
        'longitude',
        'phone',
        'email',
        'website',
        'is_active',
        'is_default',
        'notes',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Get default campus or first active campus.
     */
    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first() 
            ?? static::where('is_active', true)->first() 
            ?? static::first();
    }

    public function schools(): HasMany
    {
        return $this->hasMany(School::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
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
}

