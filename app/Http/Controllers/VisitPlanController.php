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

        // Filter by Status (Default: 'planned' so finished plans automatically move out of active list)
        $currentStatus = $request->get('status', 'planned');
        if ($currentStatus !== 'all' && !empty($currentStatus)) {
            $query->where('status', $currentStatus);
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

        \App\Services\AppContextService::applyContextFilters($query);

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
        $totalAll = VisitPlan::count();

        return view('visit-plans.index', compact(
            'plans',
            'schools',
            'marketingUsers',
            'totalPlanned',
            'todayPlanned',
            'totalCompleted',
            'totalAll',
            'currentStatus'
        ));
    }

    /**
     * Show form page for creating a new visit plan.
     */
    public function create(): View
    {
        $districts = \App\Models\District::has('schools')
            ->withCount('schools')
            ->orderBy('name')
            ->get();

        $schools = School::with(['district', 'schoolType'])
            ->whereHas('district', function($q) {
                $q->whereNotIn('name', \App\Models\District::EXCLUDED_DISTRICTS);
            })
            ->orderBy('name')
            ->get();

        $marketingUsers = User::active()->get();

        return view('visit-plans.create', compact('districts', 'schools', 'marketingUsers'));
    }

    /**
     * Store a newly created visit plan (supports single or multiple schools).
     */
    public function store(Request $request): RedirectResponse
    {
        // Support both school_ids (array) and single school_id
        if ($request->has('school_ids') && is_array($request->school_ids)) {
            $schoolIds = array_values(array_filter($request->school_ids));
        } elseif ($request->filled('school_id')) {
            $schoolIds = [$request->school_id];
        } else {
            $schoolIds = [];
        }

        $request->merge(['school_ids' => $schoolIds]);

        $validated = $request->validate([
            'school_ids' => ['required', 'array', 'min:1'],
            'school_ids.*' => ['required', 'exists:schools,id'],
            'user_id' => ['required', 'exists:users,id'],
            'planned_date' => ['required', 'date'],
            'purpose' => ['required', 'string', 'max:500'],
            'priority' => ['required', 'in:A,B,C'],
            'notes' => ['nullable', 'string'],
        ], [
            'school_ids.required' => 'Pilih minimal satu sekolah tujuan untuk diagendakan.',
            'school_ids.min' => 'Pilih minimal satu sekolah tujuan untuk diagendakan.',
            'school_ids.*.exists' => 'Sekolah yang dipilih tidak valid.',
        ]);

        $schools = School::whereIn('id', $validated['school_ids'])->get();
        $createdPlans = [];
        $activeCampus = \App\Services\AppContextService::getActiveCampus();
        $activePeriod = \App\Services\AppContextService::getActivePeriod();

        \Illuminate\Support\Facades\DB::transaction(function () use ($schools, $validated, $activeCampus, $activePeriod, &$createdPlans) {
            foreach ($schools as $school) {
                $schoolPriority = !empty($school->priority_level) ? $school->priority_level : $validated['priority'];
                $createdPlans[] = VisitPlan::create([
                    'school_id' => $school->id,
                    'user_id' => $validated['user_id'],
                    'campus_id' => $school->campus_id ?: ($activeCampus?->id),
                    'academic_period_id' => $activePeriod?->id,
                    'planned_date' => $validated['planned_date'],
                    'purpose' => $validated['purpose'],
                    'priority' => $schoolPriority,
                    'notes' => $validated['notes'] ?? null,
                    'status' => 'planned',
                ]);
            }
        });

        $count = count($createdPlans);
        $dateFormatted = \Carbon\Carbon::parse($validated['planned_date'])->translatedFormat('d F Y');
        
        if ($count === 1) {
            $message = "Rencana kunjungan ke {$schools->first()->name} ({$dateFormatted}) berhasil dijadwalkan.";
        } else {
            $schoolNames = $schools->take(3)->pluck('name')->join(', ');
            $remaining = $count > 3 ? ' dan ' . ($count - 3) . ' sekolah lainnya' : '';
            $message = "Berhasil menjadwalkan {$count} agenda rencana kunjungan ({$schoolNames}{$remaining}) pada {$dateFormatted}.";
        }

        return redirect()->route('visit-plans.index')
            ->with('toast', [
                'type' => 'success',
                'message' => $message,
            ]);
    }

    /**
     * Show form page for editing an existing visit plan.
     */
    public function edit(VisitPlan $visitPlan): View
    {
        $districts = \App\Models\District::has('schools')
            ->withCount('schools')
            ->orderBy('name')
            ->get();

        $schools = School::with(['district', 'schoolType'])
            ->whereHas('district', function($q) {
                $q->whereNotIn('name', \App\Models\District::EXCLUDED_DISTRICTS);
            })
            ->orderBy('name')
            ->get();

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
        $user = auth()->user();
        if (!$user || (!$user->isAdmin() && !$user->isPimpinan() && !$user->isKoordinator() && $visitPlan->user_id !== $user->id)) {
            abort(403, 'Akses ditolak. Anda tidak berhak menghapus rencana kunjungan ini.');
        }

        $schoolName = $visitPlan->school ? $visitPlan->school->name : 'Sekolah';
        $visitPlan->delete();

        \App\Services\ActivityLogService::log(
            'DELETE',
            'visit_plan',
            $visitPlan->id,
            "Menghapus rencana kunjungan ke: {$schoolName} (Rencana: " . ($visitPlan->planned_date?->format('d/m/Y') ?? '-') . ")",
            ['school' => $schoolName, 'planned_date' => $visitPlan->planned_date]
        );

        return redirect()->route('visit-plans.index')
            ->with('toast', [
                'type' => 'success',
                'message' => "Rencana kunjungan ke {$schoolName} berhasil dihapus.",
            ]);
    }

    /**
     * Bulk remove selected visit plans.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return redirect()->route('visit-plans.index')
                ->with('toast', [
                'type' => 'error',
                'message' => 'Tidak ada agenda yang dipilih untuk dihapus.',
            ]);
        }

        $count = VisitPlan::whereIn('id', $ids)->count();
        VisitPlan::whereIn('id', $ids)->delete();

        return redirect()->route('visit-plans.index')
            ->with('toast', [
                'type' => 'success',
                'message' => "Berhasil menghapus {$count} agenda rencana kunjungan yang dipilih.",
            ]);
    }
}
