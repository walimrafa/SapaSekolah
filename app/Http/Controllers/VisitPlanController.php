<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use App\Models\VisitPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VisitPlanController extends Controller
{
    /**
     * Display a listing of visit plans (Calendar & List view).
     */
    public function index(Request $request): View
    {
        $query = VisitPlan::with(['school.district', 'school.schoolType', 'user']);

        // Filter by Status (planned, completed, cancelled)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by Marketing User
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by Priority
        if ($request->filled('priority')) {
            $query->where('priority', strtoupper($request->priority));
        }

        // Filter Date Range
        if ($request->filled('date')) {
            $query->whereDate('planned_date', $request->date);
        }

        $plans = $query->orderBy('planned_date', 'asc')->paginate(15)->withQueryString();

        // Data for modal select dropdowns
        $schools = School::whereHas('district', function($q) {
            $q->whereNotIn('name', \App\Models\District::EXCLUDED_DISTRICTS);
        })->orderBy('name')->get();
        $marketingUsers = User::active()->get();

        // Agenda stats
        $totalPlanned = VisitPlan::where('status', 'planned')->count();
        $todayPlanned = VisitPlan::where('status', 'planned')->whereDate('planned_date', today())->count();
        $totalCompleted = VisitPlan::where('status', 'completed')->count();

        return view('visit-plans.index', compact(
            'plans',
            'schools',
            'marketingUsers',
            'totalPlanned',
            'todayPlanned',
            'totalCompleted'
        ));
    }

    /**
     * Show form page for creating a new visit plan.
     */
    public function create(): View
    {
        $districts = \App\Models\District::activeDistricts()->orderBy('name')->get();
        $schools = School::with('district')->whereHas('district', function($q) {
            $q->whereNotIn('name', \App\Models\District::EXCLUDED_DISTRICTS);
        })->orderBy('name')->get();
        $marketingUsers = User::active()->get();

        return view('visit-plans.create', compact('districts', 'schools', 'marketingUsers'));
    }

    /**
     * Store a newly created visit plan.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'user_id' => ['required', 'exists:users,id'],
            'planned_date' => ['required', 'date'],
            'purpose' => ['required', 'string', 'max:500'],
            'priority' => ['required', 'in:A,B,C'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['status'] = 'planned';

        $plan = VisitPlan::create($validated);

        return redirect()->route('visit-plans.index')
            ->with('toast', [
                'type' => 'success',
                'message' => "Rencana kunjungan ke {$plan->school->name} berhasil dijadwalkan.",
            ]);
    }

    /**
     * Show form page for editing an existing visit plan.
     */
    public function edit(VisitPlan $visitPlan): View
    {
        $districts = \App\Models\District::activeDistricts()->orderBy('name')->get();
        $schools = School::with('district')->whereHas('district', function($q) {
            $q->whereNotIn('name', \App\Models\District::EXCLUDED_DISTRICTS);
        })->orderBy('name')->get();
        $marketingUsers = User::active()->get();

        return view('visit-plans.edit', compact('visitPlan', 'districts', 'schools', 'marketingUsers'));
    }

    /**
     * Update visit plan details or status.
     */
    public function update(Request $request, VisitPlan $visitPlan): RedirectResponse
    {
        $validated = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'user_id' => ['required', 'exists:users,id'],
            'planned_date' => ['required', 'date'],
            'purpose' => ['required', 'string', 'max:500'],
            'priority' => ['required', 'in:A,B,C'],
            'status' => ['required', 'in:planned,completed,cancelled'],
            'notes' => ['nullable', 'string'],
        ]);

        $visitPlan->update($validated);

        return redirect()->route('visit-plans.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Rencana kunjungan diperbarui.',
            ]);
    }

    /**
     * Remove or cancel visit plan.
     */
    public function destroy(VisitPlan $visitPlan): RedirectResponse
    {
        $schoolName = $visitPlan->school ? $visitPlan->school->name : 'Sekolah';
        $visitPlan->delete();

        return redirect()->route('visit-plans.index')
            ->with('toast', [
                'type' => 'success',
                'message' => "Jadwal kunjungan ke {$schoolName} berhasil dibatalkan.",
            ]);
    }
}
