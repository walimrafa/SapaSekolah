<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'npsn',
        'name',
        'school_type_id',
        'status',
        'province_id',
        'regency_id',
        'district_id',
        'village_id',
        'address',
        'postal_code',
        'latitude',
        'longitude',
        'phone',
        'email',
        'website',
        'instagram',
        'accreditation_id',
        'student_count',
        'grade_10_count',
        'grade_11_count',
        'grade_12_count',
        'potential_score',
        'priority_level',
        'distance_from_campus',
        'status_school',
        'notes',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'potential_score' => 'decimal:2',
        'distance_from_campus' => 'decimal:2',
        'student_count' => 'integer',
        'grade_10_count' => 'integer',
        'grade_11_count' => 'integer',
        'grade_12_count' => 'integer',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * School belongsTo SchoolType.
     */
    public function schoolType(): BelongsTo
    {
        return $this->belongsTo(SchoolType::class);
    }

    /**
     * School belongsTo Accreditation.
     */
    public function accreditation(): BelongsTo
    {
        return $this->belongsTo(Accreditation::class);
    }

    /**
     * School belongsTo Province.
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * School belongsTo Regency.
     */
    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class);
    }

    /**
     * School belongsTo District.
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * School belongsTo Village.
     */
    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    /**
     * School memiliki banyak kontak.
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(SchoolContact::class);
    }

    /**
     * School memiliki satu kontak utama.
     */
    public function primaryContact(): HasOne
    {
        return $this->hasOne(SchoolContact::class)->where('is_primary', true);
    }

    /**
     * School memiliki banyak data potensi.
     */
    public function potentials(): HasMany
    {
        return $this->hasMany(SchoolPotential::class);
    }

    /**
     * School memiliki satu data potensi terbaru.
     */
    public function latestPotential(): HasOne
    {
        return $this->hasOne(SchoolPotential::class)->latestOfMany();
    }

    /**
     * School memiliki banyak skor.
     */
    public function scores(): HasMany
    {
        return $this->hasMany(SchoolScore::class);
    }

    /**
     * School memiliki banyak rencana kunjungan.
     */
    public function visitPlans(): HasMany
    {
        return $this->hasMany(VisitPlan::class);
    }

    /**
     * School memiliki banyak kunjungan.
     */
    public function visits(): HasMany
    {
        return $this->hasMany(SchoolVisit::class);
    }

    /**
     * School memiliki kunjungan terakhir.
     */
    public function latestVisit(): HasOne
    {
        return $this->hasOne(SchoolVisit::class)->latestOfMany('visit_date');
    }

    /**
     * School memiliki banyak follow up.
     */
    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class);
    }

    /**
     * School memiliki banyak aktivitas.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(SchoolActivity::class);
    }

    /**
     * School memiliki banyak prospek.
     */
    public function prospects(): HasMany
    {
        return $this->hasMany(Prospect::class);
    }

    public function studentIntakes(): HasMany
    {
        return $this->hasMany(SchoolStudentIntake::class);
    }

    public function getUbsiStudentsCountAttribute(): int
    {
        $individualCount = (int) ($this->attributes['individual_ubsi_students_count'] ?? 0);
        $reportedCount = (int) ($this->attributes['reported_ubsi_students_count'] ?? 0);

        return max($individualCount, $reportedCount);
    }

    /**
     * School ada di banyak rute.
     */
    public function routeSchools(): HasMany
    {
        return $this->hasMany(RouteSchool::class);
    }

    /**
     * School memiliki banyak tracking data.
     */
    public function trackings(): HasMany
    {
        return $this->hasMany(MarketingTracking::class);
    }

    /**
     * Get Google Maps search URL using school name, district, and regency.
     */
    public function getGmapsSearchUrlAttribute(): string
    {
        $districtName = $this->district ? $this->district->name : '';
        $query = trim($this->name . ', ' . ($districtName ? $districtName . ', ' : '') . 'Kabupaten Karawang, Jawa Barat');
        
        return 'https://www.google.com/maps/search/?api=1&query=' . urlencode($query);
    }

    /**
     * Get Waze Navigation URL using coordinates or query.
     */
    public function getWazeUrlAttribute(): string
    {
        if ($this->latitude && $this->longitude) {
            return "https://waze.com/ul?ll={$this->latitude},{$this->longitude}&navigate=yes";
        }

        $districtName = $this->district ? $this->district->name : '';
        $query = trim($this->name . ', ' . ($districtName ? $districtName . ', ' : '') . 'Karawang');

        return 'https://waze.com/ul?q=' . urlencode($query) . '&navigate=yes';
    }

    // ==================== SCOPES ====================

    /**
     * Scope: filter berdasarkan prioritas.
     */
    public function scopePriority($query, string $level)
    {
        return $query->where('priority_level', $level);
    }

    /**
     * Scope: sekolah aktif.
     */
    public function scopeActive($query)
    {
        return $query->where('status_school', 'aktif');
    }

    /**
     * Scope: sekolah yang sudah dipetakan (punya koordinat).
     */
    public function scopeMapped($query)
    {
        return $query->whereNotNull('latitude')->whereNotNull('longitude');
    }

    /**
     * Scope: sekolah yang belum dipetakan.
     */
    public function scopeUnmapped($query)
    {
        return $query->whereNull('latitude')->orWhereNull('longitude');
    }

    /**
     * Get nearest neighboring schools regardless of district boundary using Haversine spatial formula.
     */
    public function getNearestSchools(int $limit = 6)
    {
        if (!$this->latitude || !$this->longitude) {
            return collect();
        }

        $lat = (float) $this->latitude;
        $lng = (float) $this->longitude;

        return static::with(['district', 'schoolType', 'contacts'])
            ->where('id', '!=', $this->id)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select('*')
            ->selectRaw(
                '( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS neighbor_distance_km',
                [$lat, $lng, $lat]
            )
            ->orderBy('neighbor_distance_km', 'asc')
            ->limit($limit)
            ->get();
    }
}
