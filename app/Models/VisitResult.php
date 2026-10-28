<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitResult extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'visit_id',
        'result_type',
        'description',
    ];

    /**
     * VisitResult belongsTo SchoolVisit.
     */
    public function visit(): BelongsTo
    {
        return $this->belongsTo(SchoolVisit::class, 'visit_id');
    }
}
