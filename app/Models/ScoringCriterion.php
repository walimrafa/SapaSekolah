<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScoringCriterion extends Model
{
    public $timestamps = false;

    protected $table = 'scoring_criteria';

    protected $fillable = [
        'name',
        'weight',
        'max_score',
        'status',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'max_score' => 'integer',
        'status' => 'boolean',
    ];

    /**
     * ScoringCriterion memiliki banyak SchoolScore.
     */
    public function schoolScores(): HasMany
    {
        return $this->hasMany(SchoolScore::class, 'criterion_id');
    }
}
