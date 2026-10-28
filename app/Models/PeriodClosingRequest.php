<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeriodClosingRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'requested_by',
        'reason',
        'academic_year',
        'status',
        'pimpinan_id',
        'pimpinan_status',
        'pimpinan_notes',
        'pimpinan_action_at',
        'koordinator_id',
        'koordinator_status',
        'koordinator_notes',
        'koordinator_action_at',
        'cs_id',
        'cs_status',
        'cs_notes',
        'cs_action_at',
        'executed_by',
        'executed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'pimpinan_action_at' => 'datetime',
        'koordinator_action_at' => 'datetime',
        'cs_action_at' => 'datetime',
        'executed_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function pimpinan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pimpinan_id');
    }

    public function koordinator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'koordinator_id');
    }

    public function customerService(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cs_id');
    }

    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    // ==================== HELPERS ====================

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function allApproved(): bool
    {
        return $this->pimpinan_status === 'approved'
            && $this->koordinator_status === 'approved'
            && $this->cs_status === 'approved';
    }

    public function approvedCount(): int
    {
        $count = 0;
        if ($this->pimpinan_status === 'approved') $count++;
        if ($this->koordinator_status === 'approved') $count++;
        if ($this->cs_status === 'approved') $count++;
        return $count;
    }

    public function canUserApprove(?User $user): bool
    {
        if (!$user || $this->status !== 'pending') {
            return false;
        }

        if ($user->isPimpinan() && $this->pimpinan_status === 'pending') {
            return true;
        }

        if ($user->isKoordinator() && $this->koordinator_status === 'pending') {
            return true;
        }

        if ($user->isCustomerService() && $this->cs_status === 'pending') {
            return true;
        }

        return false;
    }

    public function getUserRoleSlot(?User $user): ?string
    {
        if (!$user) return null;

        if ($user->isPimpinan()) return 'pimpinan';
        if ($user->isKoordinator()) return 'koordinator';
        if ($user->isCustomerService()) return 'cs';

        return null;
    }
}
