<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $school_id
 * @property int $activity_id
 * @property \Carbon\Carbon|string $date
 * @property int $participants
 * @property string|null $result
 * @property string|null $notes
 * @property-read \App\Models\School|null $school
 * @property-read \App\Models\MarketingActivity|null $activity
 * @method static \Illuminate\Database\Eloquent\Builder|SchoolActivity where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|SchoolActivity whereDate($column, $operator = null, $value = null, $boolean = 'and')
 */
class SchoolActivity extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'school_id',
        'activity_id',
        'date',
        'participants',
        'result',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'participants' => 'integer',
    ];

    /**
     * SchoolActivity belongsTo School.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * SchoolActivity belongsTo MarketingActivity.
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(MarketingActivity::class, 'activity_id');
    }
}
