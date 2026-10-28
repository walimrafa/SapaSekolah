<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Accreditation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    /**
     * Accreditation memiliki banyak School.
     */
    public function schools(): HasMany
    {
        return $this->hasMany(School::class);
    }
}
