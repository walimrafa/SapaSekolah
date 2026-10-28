<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolStudentIntake extends Model
{
    protected $fillable = [
        'school_id',
        'intake_period',
        'student_count',
    ];

    protected $casts = [
        'student_count' => 'integer',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
