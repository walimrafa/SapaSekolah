<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VisitPlan extends Model
{
    protected $fillable = [
        'school_id',
        'user_id',
        'planned_date',
        'purpose',
        'priority',
        'status',
        'notes',
    ];

    protected $casts = [
        'planned_date' => 'date',
    ];

    /**
     * VisitPlan belongsTo School.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * VisitPlan belongsTo User (marketing).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * VisitPlan memiliki banyak SchoolVisit.
     */
    public function visits(): HasMany
    {
        return $this->hasMany(SchoolVisit::class);
    }
}
