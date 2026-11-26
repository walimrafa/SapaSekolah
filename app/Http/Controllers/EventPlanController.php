<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\Campus;
use App\Models\EventPlan;
use App\Services\ActivityLogService;
use App\Services\AppContextService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventPlanController extends Controller
{
    /**
     * Tampilkan daftar Master Rencana Event Kampus.
     */
    public function index(Request $request): View
    {
        $activeCampus = AppContextService::getActiveCampus();
        $campuses = Campus::where('is_active', true)->get();
        $periods = AcademicPeriod::all();

        $query = EventPlan::query()
            ->with(['campus', 'academicPeriod', 'creator'])
            ->withCount('schoolVisits');

        // Isolasi kampus jika ada kampus aktif dan user bukan admin pusat
        if ($activeCampus) {
            $query->forCampus($activeCampus->id);
        }

        // Kalkulasi Statistik Keseluruhan (Scoped Kampus)
        $statClone = clone $query;
        $allEvents = $statClone->get();
        $todayStr = now()->toDateString();

        $stats = [
            'total_events' => $allEvents->count(),
            'upcoming_events' => $allEvents->filter(fn($e) => $e->is_active && $e->status !== EventPlan::STATUS_DIBATALKAN && Carbon::parse($e->event_date)->toDateString() > $todayStr)->count(),
            'today_events' => $allEvents->filter(fn($e) => $e->is_active && Carbon::parse($e->event_date)->toDateString() === $todayStr)->count(),
            'expired_events' => $allEvents->filter(fn($e) => $e->is_expired)->count(),
            'total_invitations' => $allEvents->sum('school_visits_count'),
        ];

        // Filter Pencarian Teks (Tema / Lokasi / Target / Deskripsi)
        if ($request->filled('q')) {
            $search = trim($request->string('q')->toString());
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('target_audience', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter Status Event
        $statusFilter = $request->input('status', 'upcoming'); // Default tampilkan event aktif & mendatang
        if ($statusFilter === 'upcoming') {
            $query->where('is_active', true)
                  ->where('status', '!=', EventPlan::STATUS_DIBATALKAN)
                  ->where('event_date', '>=', $todayStr);
        } elseif ($statusFilter === 'today') {
            $query->where('is_active', true)
                  ->whereDate('event_date', $todayStr);
        } elseif ($statusFilter === 'expired') {
            $query->where(function ($q) use ($todayStr) {
                $q->where('event_date', '<', $todayStr)
                  ->orWhere('status', EventPlan::STATUS_SELESAI);
            });
        } elseif ($statusFilter === 'dibatalkan') {
            $query->where(function ($q) {
                $q->where('status', EventPlan::STATUS_DIBATALKAN)
                  ->orWhere('is_active', false);
            });
        }
        // Jika 'all', tidak ada filter status

        // Filter Kampus Manual (jika user memilih kampus spesifik)
        if ($request->filled('campus_id') && is_numeric($request->campus_id)) {
            $query->where('campus_id', (int) $request->campus_id);
        }

        // Sorting: default tanggal event terdekat lebih dulu
        $events = $query->orderBy('event_date', $statusFilter === 'expired' ? 'desc' : 'asc')
                        ->orderBy('event_time', 'asc')
                        ->paginate(15)
                        ->withQueryString();

        return view('event-plans.index', compact('events', 'stats', 'activeCampus', 'campuses', 'periods', 'statusFilter'));
    }

    /**
     * Simpan Master Rencana Event Baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date'],
            'event_time' => ['required', 'string', 'max:100'],
            'location' => ['required', 'string', 'max:255'],
            'target_audience' => ['nullable', 'string', 'max:255'],
            'quota' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:1500'],
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'academic_period_id' => ['nullable', 'exists:academic_periods,id'],
        ]);

        $activeCampus = AppContextService::getActiveCampus();
        $activePeriod = AppContextService::getActivePeriod();

        $eventPlan = EventPlan::create([
            'title' => $validated['title'],
            'event_date' => $validated['event_date'],
            'event_time' => $validated['event_time'],
            'location' => $validated['location'],
            'target_audience' => $validated['target_audience'] ?? null,
            'quota' => $validated['quota'] ?? null,
            'description' => $validated['description'] ?? null,
            'campus_id' => $validated['campus_id'] ?? $activeCampus?->id,
            'academic_period_id' => $validated['academic_period_id'] ?? $activePeriod?->id,
            'status' => EventPlan::STATUS_AKTIF,
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        ActivityLogService::log(
            'CREATE',
            'event_plan',
            $eventPlan->id,
            "Membuat Master Rencana Event baru: {$eventPlan->title} (Tgl: {$eventPlan->event_date->format('d/m/Y')}, Lokasi: {$eventPlan->location})."
        );

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Rencana Event '{$eventPlan->title}' berhasil ditambahkan ke Master Event Kampus.",
        ]);
    }

    /**
     * Update data Rencana Event.
     */
    public function update(Request $request, EventPlan $eventPlan): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date'],
            'event_time' => ['required', 'string', 'max:100'],
            'location' => ['required', 'string', 'max:255'],
            'target_audience' => ['nullable', 'string', 'max:255'],
            'quota' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:1500'],
            'status' => ['required', 'string', 'in:aktif,selesai,dibatalkan'],
            'campus_id' => ['nullable', 'exists:campuses,id'],
        ]);

        $oldData = $eventPlan->toArray();

        $eventPlan->update([
            'title' => $validated['title'],
            'event_date' => $validated['event_date'],
            'event_time' => $validated['event_time'],
            'location' => $validated['location'],
            'target_audience' => $validated['target_audience'] ?? null,
            'quota' => $validated['quota'] ?? null,
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'is_active' => $validated['status'] !== EventPlan::STATUS_DIBATALKAN,
            'campus_id' => $validated['campus_id'] ?? $eventPlan->campus_id,
        ]);

        ActivityLogService::log(
            'UPDATE',
            'event_plan',
            $eventPlan->id,
            "Memperbarui Master Rencana Event: {$eventPlan->title}.",
            $oldData,
            $eventPlan->toArray()
        );

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Data Rencana Event '{$eventPlan->title}' berhasil diperbarui.",
        ]);
    }

    /**
     * Hapus Master Rencana Event.
     */
    public function destroy(EventPlan $eventPlan): RedirectResponse
    {
        $title = $eventPlan->title;
        $id = $eventPlan->id;

        $eventPlan->delete();

        ActivityLogService::log(
            'DELETE',
            'event_plan',
            $id,
            "Menghapus Master Rencana Event: {$title}."
        );

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Rencana Event '{$title}' berhasil dihapus.",
        ]);
    }

    /**
     * Toggle status aktif / selesai / dibatalkan.
     */
    public function toggleStatus(Request $request, EventPlan $eventPlan): RedirectResponse|JsonResponse
    {
        $newStatus = $request->input('status');
        if (!in_array($newStatus, [EventPlan::STATUS_AKTIF, EventPlan::STATUS_SELESAI, EventPlan::STATUS_DIBATALKAN])) {
            $newStatus = $eventPlan->status === EventPlan::STATUS_AKTIF ? EventPlan::STATUS_SELESAI : EventPlan::STATUS_AKTIF;
        }

        $eventPlan->update([
            'status' => $newStatus,
            'is_active' => $newStatus !== EventPlan::STATUS_DIBATALKAN,
        ]);

        ActivityLogService::log(
            'UPDATE',
            'event_plan',
            $eventPlan->id,
            "Mengubah status Master Rencana Event {$eventPlan->title} menjadi '{$newStatus}'."
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Status event berhasil diubah menjadi {$newStatus}.",
                'event' => $eventPlan->fresh(),
            ]);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Status event '{$eventPlan->title}' berhasil diubah menjadi {$newStatus}.",
        ]);
    }

    /**
     * API JSON: Mengambil list event aktif & belum kadaluarsa untuk dynamic AJAX jika dibutuhkan.
     */
    public function getActiveEventsJson(Request $request): JsonResponse
    {
        $activeCampus = AppContextService::getActiveCampus();
        $events = EventPlan::upcoming()
            ->when($activeCampus, fn($q) => $q->forCampus($activeCampus->id))
            ->get(['id', 'title', 'event_date', 'event_time', 'location', 'target_audience', 'quota']);

        return response()->json([
            'success' => true,
            'events' => $events,
        ]);
    }
}
