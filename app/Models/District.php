<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    public $timestamps = false;

    public const EXCLUDED_DISTRICTS = [];

    protected $fillable = [
        'regency_id',
        'name',
    ];

    /**
     * Scope query to districts in active regencies that have integrated schools.
     */
    public function scopeActiveDistricts($query)
    {
        return $query->whereHas('regency', function ($q) {
            $q->whereHas('schools');
        });
    }

    /**
     * District belongsTo Regency.
     */
    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class);
    }

    /**
     * District memiliki banyak Village (Desa/Kelurahan).
     */
    public function villages(): HasMany
    {
        return $this->hasMany(Village::class);
    }

    /**
     * District memiliki banyak School.
     */
    public function schools(): HasMany
    {
        return $this->hasMany(School::class);
    }
}
