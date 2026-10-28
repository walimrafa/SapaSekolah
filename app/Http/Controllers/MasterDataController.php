<?php

namespace App\Http\Controllers;

use App\Models\Accreditation;
use App\Models\ContactPosition;
use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use App\Models\SchoolType;
use App\Models\ScoringCriterion;
use App\Models\Village;
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

        // Statistik Wilayah Singkron dengan Data Sekolah
        $wilayahStats = [
            'total_schools' => \App\Models\School::count(),
            'total_regencies' => $activeRegencies->count(),
            'total_districts_with_schools' => District::whereHas('schools')->count(),
            'total_districts' => District::activeDistricts()->count(),
            'total_villages' => Village::whereHas('district', fn($q) => $q->activeDistricts())->count(),
        ];

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
            'districtSearch'
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
}
