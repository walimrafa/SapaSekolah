<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\MarketingRoute
 *
 * @property int $id
 * @property string $name
 * @property Carbon|null $date
 * @property int|null $user_id
 * @property string|null $start_location
 * @property string|null $end_location
 * @property float|string|null $total_distance
 * @property int|null $estimated_duration
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RouteSchool> $routeSchools
 */
class MarketingRoute extends Model
{
    protected $fillable = [
        'name',
        'date',
        'user_id',
        'campus_id',
        'academic_period_id',
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

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    /**
     * MarketingRoute memiliki banyak RouteSchool.
     */
    public function routeSchools(): HasMany
    {
        return $this->hasMany(RouteSchool::class, 'route_id');
    }
}
