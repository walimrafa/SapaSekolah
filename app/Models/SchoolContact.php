<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolContact extends Model
{
    protected $fillable = [
        'school_id',
        'name',
        'position_id',
        'phone',
        'whatsapp',
        'email',
        'notes',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * SchoolContact belongsTo School.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * SchoolContact belongsTo ContactPosition.
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(ContactPosition::class, 'position_id');
    }
}
