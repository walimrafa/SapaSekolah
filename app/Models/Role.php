<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Role memiliki banyak User.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
