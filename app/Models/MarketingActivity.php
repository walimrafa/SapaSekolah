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
        'google_drive_folder_url',
        'google_drive_file_url',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get effective Google Drive Folder URL for this activity.
     */
    public function getDriveFolderUrlAttribute(): string
    {
        if (!empty($this->attributes['google_drive_folder_url'])) {
            return $this->attributes['google_drive_folder_url'];
        }

        $school = $this->schoolActivities->first()?->school;
        $schoolName = $school ? $school->name : $this->name;

        return \App\Services\GoogleDriveService::buildFolderUrlForSchool(
            $schoolName,
            $this->start_date ? \Carbon\Carbon::parse($this->start_date)->format('Y-m-d') : null
        );
    }

    public function getDocumentUrlAttribute(): ?string
    {
        if (!empty($this->google_drive_file_url) && str_contains($this->google_drive_file_url, 'drive.google.com')) {
            return $this->google_drive_file_url;
        }

        if (empty($this->document_path)) {
            return !empty($this->google_drive_file_url) ? $this->google_drive_file_url : null;
        }

        if (str_starts_with($this->document_path, 'http://') || str_starts_with($this->document_path, 'https://')) {
            return $this->document_path;
        }

        return asset('storage/' . ltrim($this->document_path, '/'));
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
