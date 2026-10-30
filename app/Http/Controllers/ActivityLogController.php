<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\AppContextService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    /**
     * Display listing of system and user activity logs.
     */
    public function index(Request $request): View
    {
        $query = ActivityLog::with(['user.role', 'campus', 'academicPeriod']);

        if ($request->filled('user_id') && $request->user_id !== 'all') {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('module') && $request->module !== 'all') {
            $query->where('module', $request->module);
        }

        if ($request->filled('action') && $request->action !== 'all') {
            $query->where('action', $request->action);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('module', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        $currentUser = auth()->user();
        if ($currentUser && !$currentUser->isAdmin()) {
            $campusId = $currentUser->campus_id ?? AppContextService::getActiveCampus()?->id;
            $query->where('campus_id', $campusId);
            $users = User::where('campus_id', $campusId)->orderBy('name')->get(['id', 'name', 'email', 'role_id']);
        } else {
            $activeCampus = AppContextService::getActiveCampus();
            if ($activeCampus && !$request->filled('campus_id')) {
                $query->where(function($q) use ($activeCampus) {
                    $q->where('campus_id', $activeCampus->id)->orWhereNull('campus_id');
                });
            } elseif ($request->filled('campus_id') && $request->campus_id !== 'all') {
                $query->where('campus_id', $request->campus_id);
            }
            $users = User::orderBy('name')->get(['id', 'name', 'email', 'role_id']);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(25)->withQueryString();

        $modules = ActivityLog::distinct()->whereNotNull('module')->pluck('module')->sort()->values();
        $actions = ActivityLog::distinct()->whereNotNull('action')->pluck('action')->sort()->values();

        $totalToday = ActivityLog::whereDate('created_at', today())->count();
        $totalLoginsToday = ActivityLog::where('action', 'LOGIN')->whereDate('created_at', today())->count();
        $totalChangesToday = ActivityLog::whereIn('action', ['CREATE', 'UPDATE', 'DELETE', 'VALIDATE'])->whereDate('created_at', today())->count();

        return view('activity-logs.index', compact(
            'logs',
            'users',
            'modules',
            'actions',
            'totalToday',
            'totalLoginsToday',
            'totalChangesToday'
        ));
    }
}

