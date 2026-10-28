<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Village extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'district_id',
        'name',
    ];

    /**
     * Village belongsTo District.
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Village memiliki banyak School.
     */
    public function schools(): HasMany
    {
        return $this->hasMany(School::class);
    }
}
