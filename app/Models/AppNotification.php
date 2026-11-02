<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppNotification extends Model
{
    use HasFactory;

    protected $table = 'app_notifications';

    protected $fillable = [
        'user_id',
        'role_id',
        'campus_id',
        'type',
        'title',
        'message',
        'action_url',
        'icon',
        'color',
        'is_read',
        'read_at',
        'data',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope notifications visible to a specific user based on role and campus.
     */
    public function scopeForUser(Builder $query, ?User $user): Builder
    {
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        // Campus isolation: Admins and Marketing Coordinators can see all or current active context
        $query->where(function ($q) use ($user) {
            if ($user->isAdmin() || $user->isKoordinator()) {
                // If an active campus context is selected in session, respect it or allow all
                $activeCampus = \App\Services\AppContextService::getActiveCampus();
                if ($activeCampus) {
                    $q->whereNull('campus_id')->orWhere('campus_id', $activeCampus->id);
                }
            } else {
                $q->whereNull('campus_id');
                if ($user->campus_id) {
                    $q->orWhere('campus_id', $user->campus_id);
                }
            }
        });

        // Recipient isolation: direct user, role group, or broadcast
        $query->where(function ($q) use ($user) {
            $q->whereNull('user_id')->whereNull('role_id')
                ->orWhere('user_id', $user->id);

            if ($user->role_id) {
                $q->orWhere('role_id', $user->role_id);
            }
        });

        return $query;
    }

    public function markAsRead(): bool
    {
        return $this->update([
            'is_read' => true,
            'read_at' => Carbon::now(),
        ]);
    }
}

