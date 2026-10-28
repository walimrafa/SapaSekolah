<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingTracking extends Model
{
    public $timestamps = false;

    protected $table = 'marketing_tracking';

    protected $fillable = [
        'user_id',
        'school_id',
        'visit_id',
        'latitude',
        'longitude',
        'accuracy',
        'tracked_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'accuracy' => 'decimal:2',
        'tracked_at' => 'datetime',
    ];

    /**
     * MarketingTracking belongsTo User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * MarketingTracking belongsTo School.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * MarketingTracking belongsTo SchoolVisit.
     */
    public function visit(): BelongsTo
    {
        return $this->belongsTo(SchoolVisit::class, 'visit_id');
    }
}
