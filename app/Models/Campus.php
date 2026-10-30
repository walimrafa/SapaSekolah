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
        'image_url',
        'email',
        'website',
        'is_active',
        'is_default',
        'notes',
    ];

    /**
     * Dapatkan URL Foto Gedung Kampus (dengan fallback cerdas).
     */
    public function getBuildingImageUrlAttribute(): string
    {
        if (!empty($this->image_url)) {
            if (str_starts_with($this->image_url, 'http://') || str_starts_with($this->image_url, 'https://')) {
                return $this->image_url;
            }
            return asset(ltrim($this->image_url, '/'));
        }

        if ($this->code === 'CKP') {
            return asset('storage/branding/ubsi_cikampek_building.jpg');
        }

        return asset('storage/branding/ubsi_karawang_building.jpg');
    }

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

