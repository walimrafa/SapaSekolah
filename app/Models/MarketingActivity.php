<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingActivity extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'activity_type',
        'start_date',
        'end_date',
        'description',
        'document_path',
        'document_name',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function getDocumentUrlAttribute(): ?string
    {
        return $this->document_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->document_path) : null;
    }

    public function getDocumentExtensionAttribute(): ?string
    {
        return $this->document_path ? strtolower(pathinfo($this->document_path, PATHINFO_EXTENSION)) : null;
    }

    /**
     * MarketingActivity memiliki banyak SchoolActivity.
     */
    public function schoolActivities(): HasMany
    {
        return $this->hasMany(SchoolActivity::class, 'activity_id');
    }
}
