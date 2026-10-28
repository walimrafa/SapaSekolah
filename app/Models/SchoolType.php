<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    /**
     * SchoolType memiliki banyak School.
     */
    public function schools(): HasMany
    {
        return $this->hasMany(School::class);
    }
}
