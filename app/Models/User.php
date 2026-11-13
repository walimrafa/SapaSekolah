<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'campus_id',
        'phone',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * User belongsTo Role.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    /**
     * User memiliki banyak rencana kunjungan.
     */
    public function visitPlans(): HasMany
    {
        return $this->hasMany(VisitPlan::class);
    }

    /**
     * User memiliki banyak kunjungan sekolah.
     */
    public function schoolVisits(): HasMany
    {
        return $this->hasMany(SchoolVisit::class);
    }

    /**
     * User memiliki banyak follow up.
     */
    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class);
    }

    /**
     * User memiliki banyak prospek (sebagai marketing).
     */
    public function prospects(): HasMany
    {
        return $this->hasMany(Prospect::class, 'marketing_user_id');
    }

    /**
     * User memiliki banyak rute marketing.
     */
    public function marketingRoutes(): HasMany
    {
        return $this->hasMany(MarketingRoute::class);
    }

    /**
     * User memiliki banyak tracking GPS.
     */
    public function marketingTrackings(): HasMany
    {
        return $this->hasMany(MarketingTracking::class);
    }

    /**
     * User memiliki banyak activity log.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * User memiliki banyak dokumen yang diupload.
     */
    public function uploadedDocuments(): HasMany
    {
        return $this->hasMany(VisitDocument::class, 'uploaded_by');
    }

    // ==================== HELPERS ====================

    /**
     * Cek apakah user memiliki role tertentu.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role && $this->role->name === $roleName;
    }

    /**
     * Cek apakah user memiliki salah satu dari role yang diberikan.
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->role && in_array($this->role->name, $roles);
    }

    /**
     * Cek apakah user adalah Admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('Admin');
    }

    /**
     * Cek apakah user adalah Customer Service.
     */
    public function isCustomerService(): bool
    {
        return $this->hasRole('Customer Service');
    }

    /**
     * Cek apakah user adalah Pimpinan.
     */
    public function isPimpinan(): bool
    {
        return $this->hasRole('Pimpinan');
    }

    /**
     * Cek apakah user adalah Koordinator Marketing.
     */
    public function isKoordinator(): bool
    {
        return $this->hasRole('Koordinator Marketing');
    }

    /**
     * Cek apakah user adalah Marketing.
     */
    public function isMarketing(): bool
    {
        return $this->hasRole('Marketing');
    }

    /**
     * Cek apakah user adalah staf/pejabat tingkat Pusat (multi-kampus).
     * Yaitu user yang tidak terikat ke salah satu cabang kampus tertentu (campus_id is null)
     * atau memiliki nama/role yang mengandung kata 'Pusat'.
     */
    public function isPusat(): bool
    {
        if (empty($this->campus_id)) {
            return true;
        }

        $nameAndRole = strtolower($this->name . ' ' . ($this->role?->name ?? ''));
        return str_contains($nameAndRole, 'pusat');
    }

    /**
     * Cek apakah user adalah Pimpinan Pusat.
     */
    public function isPimpinanPusat(): bool
    {
        return $this->isPimpinan() && $this->isPusat();
    }

    /**
     * Cek apakah user adalah Koordinator Pusat.
     */
    public function isKoordinatorPusat(): bool
    {
        return $this->isKoordinator() && $this->isPusat();
    }

    /**
     * Cek apakah user berwenang mengakses multi-kampus (lintas kampus).
     * Sesuai ketentuan:
     * - Administrator Pusat (Admin dengan campus_id null)
     * - Pimpinan Pusat (Pimpinan dengan campus_id null atau jabatan Pusat)
     * - Koordinator Pusat (Koordinator dengan campus_id null atau jabatan Pusat)
     * Pimpinan & Koordinator kampus cabang (misal UBSI Karawang atau UBSI Cikampek)
     * TIDAK BOLEH mengakses lintas kampus, hanya boleh mengakses seputar kampusnya sendiri.
     */
    public function canAccessAllCampuses(): bool
    {
        // Administrator sistem memiliki hak akses penuh ke seluruh kampus
        if ($this->isAdmin()) {
            return true;
        }

        // Pimpinan & Koordinator kampus cabang (misal UBSI Karawang atau UBSI Cikampek)
        // terikat ke cabang kampusnya sendiri dan TIDAK BOLEH mengakses lintas kampus
        if (!empty($this->campus_id) && !$this->isPusat()) {
            return false;
        }

        return $this->isPimpinan() || $this->isKoordinator() || $this->hasRole('Koordinator Marketing');
    }

    /**
     * Cek apakah user berwenang melakukan ploting / pembagian data follow up.
     * Ploting data siswa (kuisioner & hasil support event) dikelola oleh Pimpinan dan Koordinator Marketing.
     */
    public function canPlotFollowUps(): bool
    {
        return $this->isAdmin() || $this->isPimpinan() || $this->isKoordinator();
    }

    /**
     * Cek apakah user berwenang mengakses menu dan data Rekomendasi KOL.
     * Hanya Administrator, Pimpinan, dan Koordinator Marketing.
     */
    public function canAccessKolMenu(): bool
    {
        return $this->isAdmin() || $this->isPimpinan() || $this->isKoordinator();
    }

    /**
     * Cek apakah user berwenang mengelola backup database (manual dump, unduh, sync Google Drive).
     * Hak akses: Administrator, Pimpinan, dan Koordinator Marketing.
     */
    public function canManageBackups(): bool
    {
        return $this->isAdmin() || $this->isPimpinan() || $this->isKoordinator();
    }

    /**
     * User memiliki banyak penugasan data follow up siswa (ploting).
     */
    public function assignedStudentQuestionnaires(): HasMany
    {
        return $this->hasMany(StudentQuestionnaire::class, 'assigned_to');
    }

    /**
     * Scope: user dengan status aktif.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
