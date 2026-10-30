<?php

namespace App\Http\Controllers;

use App\Models\Accreditation;
use App\Models\AcademicPeriod;
use App\Models\Campus;
use App\Models\ContactPosition;
use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use App\Models\SchoolType;
use App\Models\ScoringCriterion;
use App\Models\Village;
use App\Services\ActivityLogService;
use App\Services\AppContextService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MasterDataController extends Controller
{
    /**
     * Display Master Data dashboard with tabs.
     */
    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'wilayah');

        $provinces = Province::withCount(['regencies', 'schools'])->get();
        $regencies = Regency::with('province')->withCount(['districts', 'schools'])->get();

        // Kabupaten / Kota yang memiliki data sekolah terintegrasi
        $activeRegencies = Regency::whereHas('schools')
            ->withCount(['districts', 'schools'])
            ->orderBy('name')
            ->get();

        $selectedRegencyId = $request->get('regency_id');
        $withSchoolsFilter = $request->get('with_schools', '');
        $districtSearch = $request->get('district_q', '');

        // Query Kecamatan: Singkron dengan kabupaten/kota yang memiliki data sekolah
        $districtQuery = District::activeDistricts()
            ->with('regency')
            ->withCount(['villages', 'schools']);

        if ($selectedRegencyId && $selectedRegencyId !== 'ALL') {
            $districtQuery->where('regency_id', $selectedRegencyId);
        }

        if ($withSchoolsFilter === '1') {
            $districtQuery->whereHas('schools');
        } elseif ($withSchoolsFilter === '0') {
            $districtQuery->doesntHave('schools');
        }

        if (!empty($districtSearch)) {
            $districtQuery->where('name', 'like', "%{$districtSearch}%");
        }

        // Tampilkan kecamatan dengan sekolah terbanyak terlebih dahulu, lalu urutkan abjad
        $districts = $districtQuery->orderByDesc('schools_count')
            ->orderBy('name')
            ->paginate(15, ['*'], 'districts_page')
            ->withQueryString();

        // Query Desa: Hanya desa dari kecamatan di kabupaten/kota aktif
        $villageSearch = $request->get('village_q', '');
        $villageDistrictId = $request->get('village_district_id');

        $villageQuery = Village::whereHas('district', function($q) use ($selectedRegencyId) {
            $q->activeDistricts();
            if ($selectedRegencyId && $selectedRegencyId !== 'ALL') {
                $q->where('regency_id', $selectedRegencyId);
            }
        })->with(['district.regency']);

        if (!empty($villageDistrictId)) {
            $villageQuery->where('district_id', $villageDistrictId);
        }

        if (!empty($villageSearch)) {
            $villageQuery->where('name', 'like', "%{$villageSearch}%");
        }

        $villages = $villageQuery->orderBy('name')
            ->paginate(15, ['*'], 'villages_page')
            ->withQueryString();

        $schoolTypes = SchoolType::withCount('schools')->get();
        $accreditations = Accreditation::withCount('schools')->get();
        $contactPositions = ContactPosition::withCount('schoolContacts')->get();
        $scoringCriteria = ScoringCriterion::orderBy('id')->get();

        $currentUser = auth()->user();
        $activeCampus = AppContextService::getActiveCampus();

        // Statistik Wilayah Singkron dengan Data Sekolah & Kampus Aktif
        $schoolCountQuery = \App\Models\School::query();
        if ($activeCampus) {
            $schoolCountQuery->where('campus_id', $activeCampus->id);
        }

        $wilayahStats = [
            'total_schools' => (clone $schoolCountQuery)->count(),
            'total_regencies' => $activeRegencies->count(),
            'total_districts_with_schools' => District::whereHas('schools', function ($q) use ($activeCampus) {
                if ($activeCampus) $q->where('campus_id', $activeCampus->id);
            })->count(),
            'total_districts' => District::activeDistricts()->count(),
            'total_villages' => Village::whereHas('district', fn($q) => $q->activeDistricts())->count(),
        ];

        // Master Kampus: Admin dapat melihat semua, non-admin hanya melihat kampusnya sendiri
        if ($currentUser && !$currentUser->isAdmin()) {
            $campuses = Campus::where('id', $currentUser->campus_id)->withCount(['schools', 'prospects', 'schoolVisits'])->get();
        } else {
            $campuses = Campus::withCount(['schools', 'prospects', 'schoolVisits'])->orderBy('name')->get();
        }

        $academicPeriods = AcademicPeriod::withCount(['prospects', 'schoolVisits'])->orderByDesc('id')->get();
        $publicPortals = AppContextService::getPublicPortalsStatus();

        return view('master-data.index', compact(
            'tab',
            'provinces',
            'regencies',
            'activeRegencies',
            'districts',
            'villages',
            'schoolTypes',
            'accreditations',
            'contactPositions',
            'scoringCriteria',
            'wilayahStats',
            'selectedRegencyId',
            'withSchoolsFilter',
            'districtSearch',
            'campuses',
            'academicPeriods',
            'publicPortals'
        ));
    }

    /**
     * Store a new School Type.
     */
    public function storeSchoolType(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:school_types,name'],
        ]);

        SchoolType::create($validated);

        return redirect()->route('master-data.index', ['tab' => 'school_types'])
            ->with('toast', ['type' => 'success', 'message' => 'Jenis Sekolah berhasil ditambahkan.']);
    }

    /**
     * Store a new Accreditation.
     */
    public function storeAccreditation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'code' => ['nullable', 'string', 'max:10'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        Accreditation::create($validated);

        return redirect()->route('master-data.index', ['tab' => 'accreditations'])
            ->with('toast', ['type' => 'success', 'message' => 'Akreditasi berhasil ditambahkan.']);
    }

    /**
     * Store a new Contact Position.
     */
    public function storeContactPosition(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:contact_positions,name'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        ContactPosition::create($validated);

        return redirect()->route('master-data.index', ['tab' => 'positions'])
            ->with('toast', ['type' => 'success', 'message' => 'Jabatan Kontak berhasil ditambahkan.']);
    }

    /**
     * Update Scoring Criteria weight & status.
     */
    public function updateScoringCriterion(Request $request, ScoringCriterion $criterion): RedirectResponse
    {
        $validated = $request->validate([
            'weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', 'boolean'],
        ]);

        $criterion->update($validated);

        return redirect()->route('master-data.index', ['tab' => 'scoring'])
            ->with('toast', ['type' => 'success', 'message' => "Kriteria {$criterion->name} berhasil diperbarui."]);
    }

    /**
     * Store new Campus.
     */
    public function storeCampus(Request $request): RedirectResponse
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak. Hanya Administrator yang memiliki wewenang untuk menambahkan kampus baru.');
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:campuses,code'],
            'name' => ['required', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'max:100'],
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['is_default'] = $request->has('is_default');

        if ($validated['is_default']) {
            Campus::where('is_default', true)->update(['is_default' => false]);
        }

        $campus = Campus::create($validated);

        ActivityLogService::log(
            'CREATE_CAMPUS',
            'campuses',
            $campus->id,
            "Menambahkan master kampus baru: {$campus->name} ({$campus->code})"
        );

        return redirect()->route('master-data.index', ['tab' => 'campuses'])
            ->with('toast', ['type' => 'success', 'message' => "Kampus {$campus->name} berhasil ditambahkan."]);
    }

    /**
     * Update Campus.
     */
    public function updateCampus(Request $request, Campus $campus): RedirectResponse
    {
        $currentUser = auth()->user();
        if (!$currentUser->isAdmin()) {
            if ($campus->id !== $currentUser->campus_id) {
                abort(403, 'Akses ditolak. Anda hanya dapat mengelola data profil kampus Anda sendiri.');
            }
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:campuses,code,' . $campus->id],
            'name' => ['required', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'max:100'],
        ]);

        if ($currentUser->isAdmin()) {
            $validated['is_active'] = $request->has('is_active');
            $validated['is_default'] = $request->has('is_default');

            if ($validated['is_default']) {
                Campus::where('id', '!=', $campus->id)->where('is_default', true)->update(['is_default' => false]);
            }
        }

        $campus->update($validated);

        ActivityLogService::log(
            'UPDATE_CAMPUS',
            'campuses',
            $campus->id,
            "Memperbarui master kampus: {$campus->name} ({$campus->code})"
        );

        return redirect()->route('master-data.index', ['tab' => 'campuses'])
            ->with('toast', ['type' => 'success', 'message' => "Kampus {$campus->name} berhasil diperbarui."]);
    }

    /**
     * Delete Campus.
     */
    public function destroyCampus(Campus $campus): RedirectResponse
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak. Hanya Administrator yang memiliki wewenang untuk menghapus kampus.');
        }

        if ($campus->schools()->count() > 0 || $campus->prospects()->count() > 0) {
            return redirect()->route('master-data.index', ['tab' => 'campuses'])
                ->with('toast', ['type' => 'danger', 'message' => "Kampus {$campus->name} tidak dapat dihapus karena sudah memiliki data sekolah atau prospek terkait."]);
        }

        $name = $campus->name;
        $campus->delete();

        ActivityLogService::log(
            'DELETE_CAMPUS',
            'campuses',
            null,
            "Menghapus master kampus: {$name}"
        );

        return redirect()->route('master-data.index', ['tab' => 'campuses'])
            ->with('toast', ['type' => 'success', 'message' => "Kampus {$name} berhasil dihapus."]);
    }

    /**
     * Store new Academic Period.
     */
    public function storeAcademicPeriod(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:academic_periods,name'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['is_current'] = $request->has('is_current');

        if ($validated['is_current']) {
            AcademicPeriod::where('is_current', true)->update(['is_current' => false]);
        }

        $period = AcademicPeriod::create($validated);

        ActivityLogService::log(
            'CREATE_PERIOD',
            'academic_periods',
            $period->id,
            "Menambahkan periode akademik baru: {$period->name}"
        );

        return redirect()->route('master-data.index', ['tab' => 'periods'])
            ->with('toast', ['type' => 'success', 'message' => "Periode Akademik {$period->name} berhasil ditambahkan."]);
    }

    /**
     * Update Academic Period.
     */
    public function updateAcademicPeriod(Request $request, AcademicPeriod $period): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:academic_periods,name,' . $period->id],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['is_current'] = $request->has('is_current');

        if ($validated['is_current']) {
            AcademicPeriod::where('id', '!=', $period->id)->where('is_current', true)->update(['is_current' => false]);
        }

        $period->update($validated);

        ActivityLogService::log(
            'UPDATE_PERIOD',
            'academic_periods',
            $period->id,
            "Memperbarui periode akademik: {$period->name}"
        );

        return redirect()->route('master-data.index', ['tab' => 'periods'])
            ->with('toast', ['type' => 'success', 'message' => "Periode Akademik {$period->name} berhasil diperbarui."]);
    }

    /**
     * Delete Academic Period.
     */
    public function destroyAcademicPeriod(AcademicPeriod $period): RedirectResponse
    {
        if ($period->prospects()->count() > 0 || $period->schoolVisits()->count() > 0) {
            return redirect()->route('master-data.index', ['tab' => 'periods'])
                ->with('toast', ['type' => 'danger', 'message' => "Periode {$period->name} tidak dapat dihapus karena sudah memiliki data prospek atau kunjungan terkait."]);
        }

        $name = $period->name;
        $period->delete();

        ActivityLogService::log(
            'DELETE_PERIOD',
            'academic_periods',
            null,
            "Menghapus periode akademik: {$name}"
        );

        return redirect()->route('master-data.index', ['tab' => 'periods'])
            ->with('toast', ['type' => 'success', 'message' => "Periode Akademik {$name} berhasil dihapus."]);
    }
}
