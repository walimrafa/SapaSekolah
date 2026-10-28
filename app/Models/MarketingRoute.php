<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingRoute extends Model
{
    protected $fillable = [
        'name',
        'date',
        'user_id',
        'start_location',
        'end_location',
        'total_distance',
        'estimated_duration',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'total_distance' => 'decimal:2',
        'estimated_duration' => 'integer',
    ];

    /**
     * MarketingRoute belongsTo User (marketing).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * MarketingRoute memiliki banyak RouteSchool.
     */
    public function routeSchools(): HasMany
    {
        return $this->hasMany(RouteSchool::class, 'route_id');
    }
}
