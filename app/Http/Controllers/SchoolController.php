<?php

namespace App\Http\Controllers;

use App\Models\Accreditation;
use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use App\Models\School;
use App\Models\SchoolType;
use App\Models\Village;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolController extends Controller
{
    /**
     * Display a listing of schools with search, filter, and pagination.
     */
    public function index(Request $request): View
    {
        $query = School::with(['schoolType', 'accreditation', 'district', 'village'])
            ->withCount(['contacts', 'visits', 'prospects']);

        // Search NPSN or Name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('npsn', 'like', "%{$search}%");
            });
        }

        // Filter District (Kecamatan)
        if ($request->filled('district_id')) {
            $query->where('district_id', $request->district_id);
        }

        // Filter School Type (SMA/SMK/MA/SLB)
        if ($request->filled('school_type_id')) {
            $query->where('school_type_id', $request->school_type_id);
        }

        // Filter Status (negeri/swasta)
        if ($request->filled('status')) {
            $query->where('status', strtolower($request->status));
        }

        // Filter Priority Level (A/B/C)
        if ($request->filled('priority_level')) {
            $query->where('priority_level', strtoupper($request->priority_level));
        }

        // Filter Accreditation
        if ($request->filled('accreditation_id')) {
            $query->where('accreditation_id', $request->accreditation_id);
        }

        // Sorting
        $sortField = $request->get('sort', 'name');
        $sortDir = $request->get('direction', 'asc');
        $allowedSorts = ['name', 'npsn', 'student_count', 'grade_12_count', 'potential_score', 'priority_level'];
        
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir);
        } else {
            $query->orderBy('name', 'asc');
        }

        // Filter banyaknya data yang ditampilkan (Pagination per_page: 25, 50, 100, dll)
        $perPage = (int) $request->input('per_page', 25);
        $allowedPerPage = [10, 25, 50, 100, 250];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 25;
        }

        $schools = $query->paginate($perPage)->withQueryString();

        // Master lists for filter dropdowns - include all districts that have schools or are active
        $districts = District::whereHas('schools')
            ->orWhere(fn($q) => $q->activeDistricts())
            ->orderBy('name')
            ->get();
        $schoolTypes = SchoolType::all();
        $accreditations = Accreditation::all();
        $availableRegencies = app(\App\Services\SchoolIntegrationService::class)->getAvailableRegencies();
        $availableProvinces = app(\App\Services\SchoolIntegrationService::class)->getAvailableProvinces();
        $initialDistricts = app(\App\Services\SchoolIntegrationService::class)->getDistrictsByRegency('3215');
        $trashedSchools = School::onlyTrashed()->with(['schoolType', 'district'])->latest('deleted_at')->get();
        $trashedCount = $trashedSchools->count();

        return view('schools.index', compact('schools', 'districts', 'schoolTypes', 'accreditations', 'availableRegencies', 'availableProvinces', 'initialDistricts', 'trashedCount', 'trashedSchools', 'perPage'));
    }

    /**
     * Show form for creating a new school.
     */
    public function create(): View
    {
        $schoolTypes = SchoolType::all();
        $accreditations = Accreditation::all();
        $provinces = Province::all();
        $regencies = Regency::all();
        $districts = District::activeDistricts()->orderBy('name')->get();

        return view('schools.create', compact('schoolTypes', 'accreditations', 'provinces', 'regencies', 'districts'));
    }

    /**
     * Store a newly created school in database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'npsn' => ['required', 'string', 'max:20', 'unique:schools,npsn'],
            'name' => ['required', 'string', 'max:200'],
            'school_type_id' => ['nullable', 'exists:school_types,id'],
            'status' => ['required', 'in:negeri,swasta'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'regency_id' => ['nullable', 'exists:regencies,id'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'village_id' => ['nullable', 'exists:villages,id'],
            'address' => ['nullable', 'string'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'website' => ['nullable', 'string', 'max:200'],
            'instagram' => ['nullable', 'string', 'max:100'],
            'accreditation_id' => ['nullable', 'exists:accreditations,id'],
            'student_count' => ['nullable', 'integer', 'min:0'],
            'grade_10_count' => ['nullable', 'integer', 'min:0'],
            'grade_11_count' => ['nullable', 'integer', 'min:0'],
            'grade_12_count' => ['nullable', 'integer', 'min:0'],
            'status_school' => ['required', 'in:aktif,tidak_aktif'],
            'notes' => ['nullable', 'string'],
        ]);

        if (!empty($validated['latitude']) && !empty($validated['longitude'])) {
            $scoringService = app(\App\Services\SchoolScoringService::class);
            $validated['distance_from_campus'] = $scoringService->calculateDistanceFromCampus((float)$validated['latitude'], (float)$validated['longitude']);
        }

        $school = School::create($validated);

        // Recalculate priority scores for all schools
        app(\App\Services\SchoolScoringService::class)->recalculateAllSchoolScores();

        return redirect()->route('schools.index')
            ->with('toast', ['type' => 'success', 'message' => "Sekolah {$school->name} berhasil ditambahkan."]);
    }

    /**
     * Display detailed profile of a school.
     */
    public function show(School $school): View
    {
        $school->load([
            'schoolType', 'accreditation', 'province', 'regency', 'district', 'village',
            'contacts.position', 'potentials', 'scores.criterion',
            'visits.user', 'visits.results', 'followUps.user', 'prospects'
        ]);

        $nearestSchools = $school->getNearestSchools(6);

        return view('schools.show', compact('school', 'nearestSchools'));
    }

    /**
     * Show form for editing school details.
     */
    public function edit(School $school): View
    {
        $schoolTypes = SchoolType::all();
        $accreditations = Accreditation::all();
        $provinces = Province::all();
        $regencies = Regency::all();
        $districts = District::activeDistricts()->orderBy('name')->get();
        $villages = Village::where('district_id', $school->district_id)->get();

        return view('schools.edit', compact('school', 'schoolTypes', 'accreditations', 'provinces', 'regencies', 'districts', 'villages'));
    }

    /**
     * Update school record.
     */
    public function update(Request $request, School $school): RedirectResponse
    {
        $validated = $request->validate([
            'npsn' => ['required', 'string', 'max:20', 'unique:schools,npsn,' . $school->id],
            'name' => ['required', 'string', 'max:200'],
            'school_type_id' => ['nullable', 'exists:school_types,id'],
            'status' => ['required', 'in:negeri,swasta'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'regency_id' => ['nullable', 'exists:regencies,id'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'village_id' => ['nullable', 'exists:villages,id'],
            'address' => ['nullable', 'string'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'website' => ['nullable', 'string', 'max:200'],
            'instagram' => ['nullable', 'string', 'max:100'],
            'accreditation_id' => ['nullable', 'exists:accreditations,id'],
            'student_count' => ['nullable', 'integer', 'min:0'],
            'grade_10_count' => ['nullable', 'integer', 'min:0'],
            'grade_11_count' => ['nullable', 'integer', 'min:0'],
            'grade_12_count' => ['nullable', 'integer', 'min:0'],
            'status_school' => ['required', 'in:aktif,tidak_aktif'],
            'notes' => ['nullable', 'string'],
        ]);

        if (!empty($validated['latitude']) && !empty($validated['longitude'])) {
            $scoringService = app(\App\Services\SchoolScoringService::class);
            $validated['distance_from_campus'] = $scoringService->calculateDistanceFromCampus((float)$validated['latitude'], (float)$validated['longitude']);
        }

        $school->update($validated);

        // Recalculate priority scores for all schools
        app(\App\Services\SchoolScoringService::class)->recalculateAllSchoolScores();

        return redirect()->route('schools.show', $school->id)
            ->with('toast', ['type' => 'success', 'message' => "Data {$school->name} berhasil diperbarui."]);
    }

    /**
     * Quick update for school GIS latitude and longitude coordinates.
     */
    public function updateCoordinates(Request $request, School $school): RedirectResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $scoringService = app(\App\Services\SchoolScoringService::class);
        $validated['distance_from_campus'] = $scoringService->calculateDistanceFromCampus((float)$validated['latitude'], (float)$validated['longitude']);

        $school->update($validated);

        // Recalculate priority scores for all schools
        $scoringService->recalculateAllSchoolScores();

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'message' => "Koordinat GIS untuk {$school->name} (Lat: {$validated['latitude']}, Lng: {$validated['longitude']}) berhasil diperbarui!",
        ]);
    }

    /**
     * Remove the specified school from storage (Soft Delete).
     */
    public function destroy(School $school): RedirectResponse
    {
        $name = $school->name;
        $school->delete();

        return redirect()->route('schools.index')
            ->with('toast', ['type' => 'success', 'message' => "Sekolah {$name} telah dihapus."]);
    }

    /**
     * Restore all soft-deleted schools.
     */
    public function restoreAll(): RedirectResponse
    {
        $trashed = School::onlyTrashed()->get();
        $count = $trashed->count();

        foreach ($trashed as $school) {
            $school->restore();
            $school->update(['status_school' => 'aktif']);
        }

        app(\App\Services\SchoolScoringService::class)->recalculateAllSchoolScores();

        return redirect()->route('schools.index')
            ->with('toast', [
                'type' => 'success',
                'message' => $count > 0 
                    ? "Berhasil memulihkan {$count} data sekolah dari tempat sampah!" 
                    : "Tidak ada data sekolah di tempat sampah.",
            ]);
    }

    /**
     * Restore a specific soft-deleted school.
     */
    public function restore(int $id): RedirectResponse
    {
        $school = School::onlyTrashed()->findOrFail($id);
        $name = $school->name;
        $school->restore();
        $school->update(['status_school' => 'aktif']);

        app(\App\Services\SchoolScoringService::class)->recalculateAllSchoolScores();

        return redirect()->route('schools.index')
            ->with('toast', [
                'type' => 'success',
                'message' => "Sekolah {$name} berhasil dipulihkan kembali!",
            ]);
    }

    /**
     * Remove all schools from active storage (Soft Delete / Move to Trash or Force Delete).
     */
    public function destroyAll(Request $request): RedirectResponse
    {
        $force = $request->boolean('force_delete', false);

        if ($force) {
            $count = School::withTrashed()->count();
            if ($count === 0) {
                return redirect()->route('schools.index')->with('toast', [
                    'type' => 'warning',
                    'message' => 'Tidak ada data sekolah yang tersimpan untuk dihapus permanen.',
                ]);
            }

            School::withTrashed()->forceDelete();

            return redirect()->route('schools.index')->with('toast', [
                'type' => 'success',
                'message' => "Berhasil mengosongkan seluruh {$count} data sekolah secara permanen dari sistem.",
            ]);
        }

        $count = School::count();
        if ($count === 0) {
            return redirect()->route('schools.index')->with('toast', [
                'type' => 'warning',
                'message' => 'Tidak ada data sekolah aktif yang dapat dihapus.',
            ]);
        }

        School::whereNotNull('id')->delete();

        // Recalculate priority scores
        try {
            app(\App\Services\SchoolScoringService::class)->recalculateAllSchoolScores();
        } catch (\Throwable) {}

        return redirect()->route('schools.index')->with('toast', [
            'type' => 'success',
            'message' => "Berhasil memindahkan seluruh {$count} data sekolah aktif ke Tempat Sampah. Anda dapat memulihkannya kapan saja atau melakukan integrasi ulang Dapodik & Kemenag.",
        ]);
    }
}

