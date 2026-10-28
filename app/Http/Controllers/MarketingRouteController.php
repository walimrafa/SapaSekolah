<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\MarketingRoute;
use App\Models\Regency;
use App\Models\RouteSchool;
use App\Models\School;
use App\Models\User;
use App\Services\RouteClusteringService;
use App\Services\SchoolScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketingRouteController extends Controller
{
    public function __construct(
        protected RouteClusteringService $clusteringService
    ) {}

    /**
     * Display Rute Marketing interactive map & route cluster builder.
     */
    public function index(Request $request): View
    {
        $routes = MarketingRoute::with(['user', 'routeSchools.school'])
            ->orderBy('date', 'desc')
            ->paginate(10);

        // Regencies that have schools or are active
        $regencies = Regency::whereHas('schools')
            ->withCount('schools')
            ->orderBy('name')
            ->get();

        if ($regencies->isEmpty()) {
            $regencies = Regency::withCount('schools')->orderBy('name')->get();
        }

        // Districts that have schools or belong to active regencies
        $districts = District::with('regency')
            ->withCount('schools')
            ->whereHas('regency')
            ->orderBy('name')
            ->get();

        $schools = School::with(['district', 'regency'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $marketingUsers = User::active()->get();

        $ubsiLat = SchoolScoringService::UBSI_LATITUDE;
        $ubsiLng = SchoolScoringService::UBSI_LONGITUDE;

        return view('marketing-routes.index', compact('routes', 'regencies', 'districts', 'schools', 'marketingUsers', 'ubsiLat', 'ubsiLng'));
    }

    /**
     * API returning automated 10-school route cluster preview.
     */
    public function apiGenerateCluster(Request $request): JsonResponse
    {
        $regencyId = $request->get('regency_id');
        if ($regencyId === 'ALL' || empty($regencyId)) {
            $regencyId = null;
        }

        $districtIds = $request->get('district_ids', []);
        // Backward compat: also accept single district_id
        if (empty($districtIds) && $request->filled('district_id')) {
            $districtIds = [$request->get('district_id')];
        }
        $schoolIds = $request->get('school_ids', []);
        $limit = $request->get('limit', 10);
        $startSchoolId = $request->get('start_school_id');
        $endSchoolId = $request->get('end_school_id');

        $cluster = $this->clusteringService->generateDailyCluster($schoolIds, $districtIds, (int)$limit, [], $startSchoolId, $endSchoolId, $regencyId ? (int)$regencyId : null);

        return response()->json([
            'status' => 'success',
            'data' => $cluster,
        ]);
    }

    /**
     * Auto generate and save a daily 10-school route cluster.
     */
    public function storeAutoCluster(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'date' => ['required', 'date'],
            'user_id' => ['required', 'exists:users,id'],
            'regency_id' => ['nullable'],
            'district_ids' => ['nullable', 'array'],
            'district_ids.*' => ['exists:districts,id'],
            'start_school_id' => ['nullable', 'string'],
            'end_school_id' => ['nullable', 'string'],
        ]);

        $regencyId = (!empty($validated['regency_id']) && $validated['regency_id'] !== 'ALL') ? (int) $validated['regency_id'] : null;
        $districtIds = $validated['district_ids'] ?? [];
        $startSchoolId = $validated['start_school_id'] ?? null;
        $endSchoolId = $validated['end_school_id'] ?? null;
        $cluster = $this->clusteringService->generateDailyCluster([], $districtIds, 10, [], $startSchoolId, $endSchoolId, $regencyId);

        if (empty($cluster['sequence'])) {
            return redirect()->route('marketing-routes.index')
                ->with('toast', ['type' => 'danger', 'message' => 'Tidak ada sekolah dengan koordinat valid untuk rute ini.']);
        }

        $route = MarketingRoute::create([
            'name' => $validated['name'],
            'date' => $validated['date'],
            'user_id' => $validated['user_id'],
            'start_location' => $cluster['start_location'],
            'end_location' => $cluster['end_location'],
            'total_distance' => $cluster['total_distance_km'],
            'estimated_duration' => $cluster['estimated_duration_minutes'],
            'status' => 'planned',
        ]);

        foreach ($cluster['sequence'] as $idx => $s) {
            RouteSchool::create([
                'route_id' => $route->id,
                'school_id' => $s['id'],
                'sequence' => $idx + 1,
                'status' => 'pending',
            ]);
        }

        return redirect()->route('marketing-routes.index')
            ->with('toast', [
                'type' => 'success',
                'message' => "Rute Klaster 10 Sekolah ({$route->name}) berhasil dibuat otomatis!",
            ]);
    }

    /**
     * Generate complete 10-Day Outing Campaign (Hari 1 s/d Hari 10).
     */
    public function store10DayCampaign(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $created = $this->clusteringService->generate10DayCampaign((int)$validated['user_id']);

        return redirect()->route('marketing-routes.index')
            ->with('toast', [
                'type' => 'success',
                'message' => "Kampanye Rute Outing Mobil 10 Hari (" . count($created) . " Rute Harian) berhasil dibuat otomatis!",
            ]);
    }

    /**
     * Delete marketing route.
     */
    public function destroy(MarketingRoute $marketingRoute): RedirectResponse
    {
        $name = $marketingRoute->name;
        $marketingRoute->delete();

        return redirect()->route('marketing-routes.index')
            ->with('toast', ['type' => 'success', 'message' => "Rute {$name} berhasil dihapus."]);
    }
}
