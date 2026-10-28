<?php

namespace App\Http\Controllers;

use App\Models\FollowUp;
use App\Models\School;
use App\Models\User;
use App\Services\FollowUpExportService;
use App\Services\FollowUpSyncService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FollowUpController extends Controller
{
    /**
     * Display Follow-up Kanban & List View for Fixing Socialization Schedule.
     */
    public function index(Request $request, FollowUpSyncService $syncService): View
    {
        // Ensure all field visits are automatically synchronized into follow-ups
        try {
            $syncService->syncAllVisits();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Error auto-syncing visits to follow ups: ' . $e->getMessage());
        }

        $viewMode = $request->get('view', 'kanban'); // 'kanban' or 'list'

        $query = FollowUp::with([
            'school.district', 
            'school.primaryContact', 
            'user', 
            'visit'
        ])->whereNull('socialization_completed_at');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('method')) {
            $query->where('method', $request->input('method'));
        }

        if ($request->filled('schedule_status')) {
            if ($request->schedule_status === 'fixed') {
                $query->where(function ($q) {
                    $q->where('is_schedule_fixed', true)
                      ->orWhere('status', 'completed');
                });
            } elseif ($request->schedule_status === 'unfixed') {
                $query->where('is_schedule_fixed', false)
                      ->where('status', '!=', 'completed');
            }
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('school', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                })
                ->orWhere('pic_name', 'like', "%{$search}%")
                ->orWhere('pic_phone', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $allFollowups = $query->orderBy('followup_date', 'asc')->get();

        // Categorize for Kanban: Ensure locked schedule or status moves card to appropriate column
        $completed = $allFollowups->filter(fn($item) => $item->status === 'completed' || $item->is_schedule_fixed);
        $failed = $allFollowups->filter(fn($item) => $item->status === 'failed');
        $progress = $allFollowups->filter(fn($item) => $item->status === 'progress' && !$item->is_schedule_fixed);
        $pending = $allFollowups->filter(fn($item) => ($item->status === 'pending' || empty($item->status)) && !$item->is_schedule_fixed);

        // Summary Statistics
        $totalCount = $allFollowups->count();
        $fixedScheduleCount = $completed->count();
        $pendingCount = $pending->count();
        $progressCount = $progress->count();
        $totalEstimatedAudience = $completed->sum('target_audience_count');
        $conversionRate = $totalCount > 0 ? round(($fixedScheduleCount / $totalCount) * 100, 1) : 0;

        // ==========================================
        // ANALYTICS DATA FOR CHARTS & DIAGRAMS
        // ==========================================
        // 1. Status Conversion Breakdown (Donut Chart)
        $chartStatus = [
            'labels' => ['Jadwal FIX', 'Koordinasi Jadwal', 'Menunggu Konfirmasi', 'Batal / Ditolak'],
            'data' => [$completed->count(), $progress->count(), $pending->count(), $failed->count()],
            'colors' => ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
        ];

        // 2. Timeline / Monthly Distribution of Fixed Schedules & Tentative Targets
        $timelineMap = [];
        foreach ($allFollowups as $item) {
            $d = $item->fixed_date ?: ($item->visit?->target_date ?: null);
            $key = $d ? Carbon::parse($d)->translatedFormat('M Y') : 'Tentatif';
            if (!isset($timelineMap[$key])) {
                $timelineMap[$key] = [
                    'fixed_schools' => 0,
                    'pipeline_schools' => 0,
                    'fixed_audience' => 0,
                    'pipeline_audience' => 0,
                ];
            }
            if ($item->is_schedule_fixed || $item->status === 'completed') {
                $timelineMap[$key]['fixed_schools']++;
                $timelineMap[$key]['fixed_audience'] += (int) ($item->target_audience_count ?: 0);
            } else {
                $timelineMap[$key]['pipeline_schools']++;
                $timelineMap[$key]['pipeline_audience'] += (int) ($item->target_audience_count ?: 0);
            }
        }

        $chartTimeline = [
            'labels' => array_keys($timelineMap),
            'fixed_schools' => array_column($timelineMap, 'fixed_schools'),
            'pipeline_schools' => array_column($timelineMap, 'pipeline_schools'),
            'fixed_audience' => array_column($timelineMap, 'fixed_audience'),
            'pipeline_audience' => array_column($timelineMap, 'pipeline_audience'),
        ];

        // 3. District Distribution (Bar Chart)
        $districtStats = [];
        foreach ($allFollowups as $item) {
            $distName = $item->school?->district?->name ?: 'Lainnya';
            if (!isset($districtStats[$distName])) {
                $districtStats[$distName] = ['total' => 0, 'fixed' => 0];
            }
            $districtStats[$distName]['total']++;
            if ($item->is_schedule_fixed || $item->status === 'completed') {
                $districtStats[$distName]['fixed']++;
            }
        }
        uasort($districtStats, fn($a, $b) => $b['fixed'] <=> $a['fixed'] ?: $b['total'] <=> $a['total']);
        $topDistricts = array_slice($districtStats, 0, 7, true);

        $chartDistrict = [
            'labels' => array_keys($topDistricts),
            'fixed' => array_column($topDistricts, 'fixed'),
            'total' => array_column($topDistricts, 'total'),
        ];

        // 4. Marketing PIC Performance (Bar Chart)
        $marketingStats = [];
        foreach ($allFollowups as $item) {
            $userName = $item->user?->name ?: 'Marketing UBSI';
            if (!isset($marketingStats[$userName])) {
                $marketingStats[$userName] = ['total' => 0, 'fixed' => 0];
            }
            $marketingStats[$userName]['total']++;
            if ($item->is_schedule_fixed || $item->status === 'completed') {
                $marketingStats[$userName]['fixed']++;
            }
        }
        uasort($marketingStats, fn($a, $b) => $b['fixed'] <=> $a['fixed'] ?: $b['total'] <=> $a['total']);

        $chartMarketing = [
            'labels' => array_keys($marketingStats),
            'fixed' => array_column($marketingStats, 'fixed'),
            'total' => array_column($marketingStats, 'total'),
        ];

        // 5. Socialization Method / Format Distribution (Horizontal Bar / Pie)
        $methodStats = [];
        foreach ($allFollowups as $item) {
            $mLabel = $item->socialization_location ?: ($item->visit?->socialization_method ?: 'Sosialisasi di Kelas');
            $methodStats[$mLabel] = ($methodStats[$mLabel] ?? 0) + 1;
        }
        arsort($methodStats);

        $chartMethod = [
            'labels' => array_keys($methodStats),
            'data' => array_values($methodStats),
        ];

        $schools = School::orderBy('name')->get();
        $marketingUsers = User::active()->get();

        return view('follow-ups.index', compact(
            'viewMode',
            'allFollowups',
            'pending',
            'progress',
            'completed',
            'failed',
            'totalCount',
            'fixedScheduleCount',
            'pendingCount',
            'progressCount',
            'totalEstimatedAudience',
            'conversionRate',
            'chartStatus',
            'chartTimeline',
            'chartDistrict',
            'chartMarketing',
            'chartMethod',
            'schools',
            'marketingUsers'
        ));
    }

    /**
     * Show schools whose socialization sessions have actually taken place.
     */
    public function completedSocializations(Request $request): View
    {
        $query = FollowUp::with(['school.district', 'user', 'visit'])
            ->whereNotNull('socialization_completed_at');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('school', function ($schoolQuery) use ($search) {
                    $schoolQuery->where('name', 'like', "%{$search}%")
                        ->orWhereHas('district', fn ($districtQuery) => $districtQuery->where('name', 'like', "%{$search}%"));
                })
                    ->orWhere('pic_name', 'like', "%{$search}%")
                    ->orWhere('socialization_completion_notes', 'like', "%{$search}%");
            });
        }

        $completedSocializations = $query
            ->orderByDesc('socialization_completed_at')
            ->paginate(20)
            ->withQueryString();

        $totalCount = FollowUp::whereNotNull('socialization_completed_at')->count();
        $totalAudience = FollowUp::whereNotNull('socialization_completed_at')->sum('target_audience_count');

        return view('follow-ups.completed', compact(
            'completedSocializations',
            'totalCount',
            'totalAudience'
        ));
    }

    /**
     * Record that a fixed socialization schedule has actually taken place.
     */
    public function confirmSocialization(Request $request, FollowUp $followUp): RedirectResponse
    {
        if (!$followUp->is_schedule_fixed && $followUp->status !== FollowUp::STATUS_COMPLETED) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Konfirmasi hanya tersedia untuk jadwal sosialisasi yang sudah FIX.',
            ]);
        }

        $validated = $request->validate([
            'socialization_completed_at' => ['required', 'date', 'before_or_equal:today'],
            'socialization_completion_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $followUp->update($validated);

        return redirect()->route('socializations.completed.index')->with('toast', [
            'type' => 'success',
            'message' => "Sosialisasi di {$followUp->school?->name} berhasil dicatat sebagai kegiatan terlaksana.",
        ]);
    }

    /**
     * Remove an accidental completion confirmation and return the schedule to the FIX list.
     */
    public function reopenSocialization(FollowUp $followUp): RedirectResponse
    {
        $followUp->update([
            'socialization_completed_at' => null,
            'socialization_completion_notes' => null,
        ]);

        return redirect()->route('socializations.completed.index')->with('toast', [
            'type' => 'success',
            'message' => "Konfirmasi sosialisasi untuk {$followUp->school?->name} dibatalkan. Jadwal kembali ke daftar FIX.",
        ]);
    }

    /**
     * Export Follow-up and Fixed Socialization Schedules to Excel.
     */
    public function export(Request $request, FollowUpExportService $exportService): StreamedResponse
    {
        $query = FollowUp::with([
            'school.district', 
            'school.primaryContact', 
            'user', 
            'visit'
        ]);

        $filterInfo = [
            'status_label' => 'Semua Data Follow Up',
            'marketing_name' => 'Semua Staf Marketing',
        ];

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
            $user = User::find($request->user_id);
            if ($user) {
                $filterInfo['marketing_name'] = $user->name;
            }
        }

        if ($request->filled('method')) {
            $query->where('method', $request->input('method'));
        }

        if ($request->get('type') === 'fixed' || $request->schedule_status === 'fixed') {
            $query->where(function ($q) {
                $q->where('is_schedule_fixed', true)
                  ->orWhere('status', 'completed');
            });
            $filterInfo['status_label'] = 'Hanya Jadwal Sosialisasi FIX';
        } elseif ($request->schedule_status === 'unfixed') {
            $query->where('is_schedule_fixed', false)
                  ->where('status', '!=', 'completed');
            $filterInfo['status_label'] = 'Belum FIX (Menunggu/Koordinasi)';
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('school', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                })
                ->orWhere('pic_name', 'like', "%{$search}%")
                ->orWhere('pic_phone', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $followUps = $query->orderBy('followup_date', 'asc')->get();

        return $exportService->exportExcel($followUps, $filterInfo);
    }

    /**
     * Trigger manual sync of visits to follow-ups.
     */
    public function syncFromVisits(FollowUpSyncService $syncService): RedirectResponse
    {
        $count = $syncService->syncAllVisits();

        return redirect()->route('follow-ups.index')
            ->with('toast', [
                'type' => 'success',
                'message' => "Sinkronisasi berhasil! {$count} data kunjungan baru dimasukkan ke antrean follow-up jadwal sosialisasi.",
            ]);
    }

    /**
     * Store a new Follow-up record manually.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'user_id' => ['required', 'exists:users,id'],
            'followup_date' => ['required', 'date'],
            'method' => ['required', 'string', 'max:50'],
            'notes' => ['required', 'string'],
            'pic_name' => ['nullable', 'string', 'max:150'],
            'pic_phone' => ['nullable', 'string', 'max:50'],
            'fixed_date' => ['nullable', 'date'],
            'fixed_time' => ['nullable', 'string', 'max:50'],
            'socialization_location' => ['nullable', 'string', 'max:150'],
            'result' => ['nullable', 'string'],
            'status' => ['required', 'in:pending,progress,completed,failed'],
        ]);

        $validated['description'] = $validated['notes'];
        $validated['is_schedule_fixed'] = ($validated['status'] === 'completed');

        FollowUp::create($validated);

        return redirect()->route('follow-ups.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Data follow up sekolah berhasil ditambahkan.',
            ]);
    }

    /**
     * Update follow-up schedule and lock in fixed socialization dates.
     */
    public function updateSchedule(Request $request, FollowUp $followUp): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,progress,completed,failed'],
            'fixed_date' => ['nullable', 'date'],
            'fixed_time' => ['nullable', 'string', 'max:50'],
            'pic_name' => ['nullable', 'string', 'max:150'],
            'pic_phone' => ['nullable', 'string', 'max:50'],
            'socialization_location' => ['nullable', 'string', 'max:150'],
            'method' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'result' => ['nullable', 'string'],
            'is_schedule_fixed' => ['nullable', 'boolean'],
        ]);

        $isFixed = !empty($validated['is_schedule_fixed']) || $validated['status'] === 'completed';
        $validated['is_schedule_fixed'] = $isFixed;

        // When schedule is locked/FIX, ensure status is completed so it moves to Column 3 (Jadwal FIX)
        if ($isFixed) {
            $validated['status'] = 'completed';
        } else {
            $validated['is_schedule_fixed'] = false;
        }

        if (!empty($validated['notes'])) {
            $validated['description'] = $validated['notes'];
        }

        $followUp->update($validated);

        // Jika jadwal sosialisasi berhasil di-FIX-kan, kirim notifikasi WhatsApp ke tim marketing
        if ($isFixed && !empty($followUp->fixed_date)) {
            try {
                app(\App\Services\WhatsAppNotificationService::class)->notifySocializationH5($followUp);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Gagal kirim notifikasi WA jadwal sosialisasi: ' . $e->getMessage());
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Jadwal sosialisasi berhasil diperbarui!',
                'follow_up' => $followUp->fresh(['school', 'user', 'visit']),
            ]);
        }

        $msg = $isFixed 
            ? "Jadwal sosialisasi untuk {$followUp->school->name} BERHASIL DI-LOCK / FIX!" 
            : "Follow up untuk {$followUp->school->name} berhasil diperbarui.";

        return redirect()->route('follow-ups.index')
            ->with('toast', [
                'type' => 'success',
                'message' => $msg,
            ]);
    }

    /**
     * Update follow-up status (e.g. from Kanban drag or quick button).
     */
    public function updateStatus(Request $request, FollowUp $followUp): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,progress,completed,failed'],
        ]);

        $isFixed = ($validated['status'] === 'completed');
        $followUp->update([
            'status' => $validated['status'],
            'is_schedule_fixed' => $isFixed,
        ]);

        return redirect()->route('follow-ups.index')
            ->with('toast', [
                'type' => 'success',
                'message' => "Status follow up {$followUp->school->name} diubah menjadi " . $followUp->status_label,
            ]);
    }

    /**
     * Delete a follow-up history record for a school.
     */
    public function destroy(FollowUp $followUp): RedirectResponse
    {
        $schoolName = $followUp->school?->name ?? 'Sekolah';
        $followUp->delete();

        return redirect()->route('follow-ups.index')
            ->with('toast', [
                'type' => 'success',
                'message' => "Histori follow up untuk {$schoolName} berhasil dihapus.",
            ]);
    }
}
