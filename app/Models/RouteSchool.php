<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteSchool extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'route_id',
        'school_id',
        'sequence',
        'arrival_time',
        'departure_time',
        'status',
        'notes',
    ];

    protected $casts = [
        'sequence' => 'integer',
    ];

    /**
     * RouteSchool belongsTo MarketingRoute.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(MarketingRoute::class, 'route_id');
    }

    /**
     * RouteSchool belongsTo School.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
