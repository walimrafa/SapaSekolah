<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactPosition extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * ContactPosition memiliki banyak SchoolContact.
     */
    public function schoolContacts(): HasMany
    {
        return $this->hasMany(SchoolContact::class, 'position_id');
    }
}
