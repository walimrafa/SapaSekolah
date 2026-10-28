<?php

namespace App\Http\Controllers;

use App\Models\Accreditation;
use App\Models\District;
use App\Models\Regency;
use App\Models\School;
use App\Models\SchoolType;
use App\Models\Village;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MapController extends Controller
{
    /**
     * Display the full-screen Smart School GIS Map.
     */
    public function index(Request $request): View
    {
        // Get regencies that actually have integrated/mapped schools
        $regencies = Regency::whereHas('schools', function ($q) {
            $q->whereNotNull('latitude')->whereNotNull('longitude');
        })->withCount(['schools' => function ($q) {
            $q->whereNotNull('latitude')->whereNotNull('longitude');
        }])->orderBy('name')->get();

        // Fallback if no regencies with schools yet
        if ($regencies->isEmpty()) {
            $regencies = Regency::orderBy('name')->get();
        }

        // Default to Karawang or the first integrated regency
        $defaultRegency = $regencies->firstWhere('name', 'like', '%Karawang%') ?? $regencies->first();
        $defaultRegencyId = $defaultRegency ? (string)$defaultRegency->id : '';

        // Districts that belong to regencies
        $districts = District::whereNotNull('regency_id')->orderBy('name')->get(['id', 'name', 'regency_id']);
        $schoolTypes = SchoolType::all();
        $accreditations = Accreditation::all();

        return view('map.index', compact('regencies', 'defaultRegencyId', 'districts', 'schoolTypes', 'accreditations'));
    }

    /**
     * API endpoint returning filtered school markers for Leaflet.js map.
     */
    public function apiSchools(Request $request): JsonResponse
    {
        $query = School::with(['schoolType', 'accreditation', 'regency', 'district', 'village', 'primaryContact', 'latestVisit'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        // Regency Filter (Show only schools from integrated regency)
        if ($request->filled('regency_id') && $request->regency_id !== 'all') {
            $query->where('regency_id', $request->regency_id);
        } else {
            // Default: strictly filter by the integrated regency so dummy schools outside the area do not appear
            $firstRegencyId = Regency::whereHas('schools', function ($q) {
                $q->whereNotNull('latitude')->whereNotNull('longitude');
            })->value('id');

            if ($request->regency_id === 'all') {
                $query->whereNotNull('regency_id');
            } elseif ($firstRegencyId) {
                $query->where('regency_id', $firstRegencyId);
            } else {
                $query->whereNotNull('regency_id');
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('npsn', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('district_id')) {
            $query->where('district_id', $request->district_id);
        }

        if ($request->filled('school_type_id')) {
            $query->where('school_type_id', $request->school_type_id);
        }

        if ($request->filled('status')) {
            $query->where('status', strtolower($request->status));
        }

        if ($request->filled('priority_level')) {
            $query->where('priority_level', strtoupper($request->priority_level));
        }

        if ($request->filled('accreditation_id')) {
            $query->where('accreditation_id', $request->accreditation_id);
        }

        if ($request->filled('visited')) {
            if ($request->visited === 'yes') {
                $query->has('visits');
            } elseif ($request->visited === 'no') {
                $query->doesntHave('visits');
            }
        }

        $schools = $query->orderBy('priority_level', 'asc')
            ->orderBy('potential_score', 'desc')
            ->get();

        $data = $schools->map(function ($s) {
            return [
                'id' => $s->id,
                'npsn' => $s->npsn,
                'name' => $s->name,
                'type' => $s->schoolType ? $s->schoolType->name : 'SLTA',
                'status' => strtoupper($s->status),
                'regency' => $s->regency ? $s->regency->name : '-',
                'district' => $s->district ? $s->district->name : '-',
                'village' => $s->village ? $s->village->name : '-',
                'address' => $s->address,
                'latitude' => (float) $s->latitude,
                'longitude' => (float) $s->longitude,
                'student_count' => $s->student_count,
                'grade_12_count' => $s->grade_12_count,
                'potential_score' => (float) $s->potential_score,
                'priority_level' => $s->priority_level ?? 'C',
                'primary_contact' => $s->primaryContact ? [
                    'name' => $s->primaryContact->name,
                    'phone' => $s->primaryContact->whatsapp ?? $s->primaryContact->phone,
                ] : null,
                'last_visit' => $s->latestVisit ? $s->latestVisit->visit_date->format('d M Y') : 'Belum Pernah',
            ];
        });

        return response()->json([
            'status' => 'success',
            'count' => $data->count(),
            'data' => $data,
        ]);
    }
}
