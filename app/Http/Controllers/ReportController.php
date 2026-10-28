<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\FollowUp;
use App\Models\MarketingActivity;
use App\Models\Prospect;
use App\Models\School;
use App\Models\SchoolVisit;
use App\Models\StudentQuestionnaire;
use App\Models\User;
use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Display Report & Analytics Center with Detail per Progress and Overall Summary.
     */
    public function index(Request $request): View
    {
        $activeTab = $request->get('tab', 'overview'); // 'overview', 'visits', 'followups', 'activities', 'prospects', 'questionnaires', 'schools'
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $districtId = $request->get('district_id');
        $userId = $request->get('user_id');
        $status = $request->get('status');
        $search = $request->get('search');
        $visitedStatus = $request->get('visited_status');

        $districts = District::orderBy('name')->get();
        $marketingUsers = User::active()->get();

        // 1. Overall Global KPI & Funnel Metrics
        $totalSchools = School::count();
        $visitedSchoolsCount = School::has('visits')->count();
        $visitedPercentage = $totalSchools > 0 ? round(($visitedSchoolsCount / $totalSchools) * 100, 1) : 0;
        $totalVisits = SchoolVisit::count();
        $totalFollowUps = FollowUp::count();
        $fixedSchedulesCount = FollowUp::where('is_schedule_fixed', true)->orWhere('status', 'completed')->count();
        $totalGrade12 = (int) School::sum('grade_12_count');
        $totalProspects = Prospect::count();
        $registeredCount = Prospect::whereIn('status', [
            Prospect::STATUS_BAYAR_REGISTRASI,
            Prospect::STATUS_UJIAN_SARINGAN,
            Prospect::STATUS_BAYAR_DAFTAR_ULANG,
            Prospect::STATUS_BERKAS_LENGKAP,
            Prospect::STATUS_MAHASISWA
        ])->count();
        $mahasiswaCount = Prospect::where('status', Prospect::STATUS_MAHASISWA)->count();
        $conversionRate = $totalProspects > 0 ? round(($mahasiswaCount / $totalProspects) * 100, 1) : 0;
        $totalActivities = MarketingActivity::count();
        $totalQuestionnaires = StudentQuestionnaire::count();

        $funnelData = [
            'total_schools' => $totalSchools,
            'visited_schools' => $visitedSchoolsCount,
            'visited_percentage' => $visitedPercentage,
            'total_visits' => $totalVisits,
            'total_followups' => $totalFollowUps,
            'fixed_schedules' => $fixedSchedulesCount,
            'total_grade_12' => $totalGrade12,
            'total_prospects' => $totalProspects,
            'registered' => $registeredCount,
            'mahasiswa' => $mahasiswaCount,
            'conversion_rate' => $conversionRate,
            'total_activities' => $totalActivities,
            'total_questionnaires' => $totalQuestionnaires,
        ];

        // Specific Tab Data
        $data = [];

        switch ($activeTab) {
            case 'visits':
                $query = SchoolVisit::with(['school.district', 'user', 'visitPlan'])->latest('visit_date');

                if ($startDate) $query->whereDate('visit_date', '>=', $startDate);
                if ($endDate) $query->whereDate('visit_date', '<=', $endDate);
                if ($districtId) $query->whereHas('school', fn($q) => $q->where('district_id', $districtId));
                if ($userId) $query->where('user_id', $userId);
                if ($status && $status !== 'all') $query->where('status', $status);
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->whereHas('school', fn($sq) => $sq->where('name', 'like', "%{$search}%"))
                          ->orWhere('recipient_name', 'like', "%{$search}%")
                          ->orWhere('purpose', 'like', "%{$search}%")
                          ->orWhere('result', 'like', "%{$search}%");
                    });
                }

                $visits = $query->paginate(15)->withQueryString();
                
                $data['visits'] = $visits;
                $data['stats'] = [
                    'total' => SchoolVisit::count(),
                    'berhasil' => SchoolVisit::where('status', 'berhasil')->count(),
                    'pending' => SchoolVisit::where('status', 'pending')->count(),
                    'tidak_berhasil' => SchoolVisit::where('status', 'tidak_berhasil')->count(),
                    'tidak_bertemu' => SchoolVisit::where('status', 'tidak_bertemu')->count(),
                ];
                break;

            case 'followups':
                $query = FollowUp::with(['school.district', 'user', 'visit'])->latest('followup_date');

                if ($startDate) $query->whereDate('followup_date', '>=', $startDate);
                if ($endDate) $query->whereDate('followup_date', '<=', $endDate);
                if ($districtId) $query->whereHas('school', fn($q) => $q->where('district_id', $districtId));
                if ($userId) $query->where('user_id', $userId);
                if ($status && $status !== 'all') $query->where('status', $status);
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->whereHas('school', fn($sq) => $sq->where('name', 'like', "%{$search}%"))
                          ->orWhere('pic_name', 'like', "%{$search}%")
                          ->orWhere('pic_phone', 'like', "%{$search}%")
                          ->orWhere('notes', 'like', "%{$search}%");
                    });
                }

                $followups = $query->paginate(15)->withQueryString();

                $data['followups'] = $followups;
                $data['stats'] = [
                    'total' => FollowUp::count(),
                    'completed' => FollowUp::where('status', 'completed')->orWhere('is_schedule_fixed', true)->count(),
                    'progress' => FollowUp::where('status', 'progress')->count(),
                    'pending' => FollowUp::where('status', 'pending')->count(),
                    'failed' => FollowUp::where('status', 'failed')->count(),
                    'total_audience' => (int) FollowUp::where('is_schedule_fixed', true)->sum('target_audience_count'),
                ];
                break;

            case 'activities':
                $query = MarketingActivity::with(['schoolActivities.school.district'])->latest('start_date');

                if ($startDate) $query->whereDate('start_date', '>=', $startDate);
                if ($endDate) $query->whereDate('end_date', '<=', $endDate);
                if ($status && $status !== 'all') $query->where('activity_type', $status);
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('description', 'like', "%{$search}%");
                    });
                }

                $activities = $query->paginate(15)->withQueryString();

                $data['activities'] = $activities;
                $data['stats'] = [
                    'total' => MarketingActivity::count(),
                    'total_participants' => (int) DB::table('school_activities')->sum('participants'),
                    'total_schools' => DB::table('school_activities')->distinct('school_id')->count('school_id'),
                ];
                break;

            case 'prospects':
                $query = Prospect::with(['school.district', 'marketingUser'])->latest('created_at');

                if ($startDate) $query->whereDate('created_at', '>=', $startDate);
                if ($endDate) $query->whereDate('created_at', '<=', $endDate);
                if ($districtId) $query->whereHas('school', fn($q) => $q->where('district_id', $districtId));
                if ($userId) $query->where('marketing_user_id', $userId);
                if ($status && $status !== 'all') $query->where('status', $status);
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('phone', 'like', "%{$search}%")
                          ->orWhere('interest_program', 'like', "%{$search}%")
                          ->orWhereHas('school', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
                    });
                }

                $prospects = $query->paginate(15)->withQueryString();

                $data['prospects'] = $prospects;
                $data['stats'] = [
                    'total' => Prospect::count(),
                    'daftar_akun' => Prospect::where('status', Prospect::STATUS_DAFTAR_AKUN)->count(),
                    'bayar_registrasi' => Prospect::where('status', Prospect::STATUS_BAYAR_REGISTRASI)->count(),
                    'ujian_saringan' => Prospect::where('status', Prospect::STATUS_UJIAN_SARINGAN)->count(),
                    'bayar_daftar_ulang' => Prospect::where('status', Prospect::STATUS_BAYAR_DAFTAR_ULANG)->count(),
                    'berkas_lengkap' => Prospect::where('status', Prospect::STATUS_BERKAS_LENGKAP)->count(),
                    'mahasiswa' => Prospect::where('status', Prospect::STATUS_MAHASISWA)->count(),
                    'tidak_lanjut' => Prospect::where('status', Prospect::STATUS_TIDAK_LANJUT)->count(),
                ];
                break;

            case 'questionnaires':
                $query = StudentQuestionnaire::with(['lastFollowedUpBy'])->latest('created_at');

                if ($startDate) $query->whereDate('created_at', '>=', $startDate);
                if ($endDate) $query->whereDate('created_at', '<=', $endDate);
                if ($status && $status !== 'all') $query->where('followup_status', $status);
                if ($userId) $query->where('last_followed_up_by', $userId);
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('student_name', 'like', "%{$search}%")
                          ->orWhere('student_phone', 'like', "%{$search}%")
                          ->orWhere('student_class', 'like', "%{$search}%")
                          ->orWhere('student_major', 'like', "%{$search}%");
                    });
                }

                $questionnaires = $query->paginate(15)->withQueryString();

                $data['questionnaires'] = $questionnaires;
                $data['stats'] = [
                    'total' => StudentQuestionnaire::count(),
                    'belum_dihubungi' => StudentQuestionnaire::where('followup_status', 'belum_dihubungi')->count(),
                    'respon' => StudentQuestionnaire::where('followup_status', 'respon')->count(),
                    'berminat' => StudentQuestionnaire::where('followup_status', 'berminat')->count(),
                    'daftar_aplikasi' => StudentQuestionnaire::whereIn('followup_status', ['daftar_aplikasi', 'closing'])->count(),
                    'closing' => StudentQuestionnaire::whereIn('followup_status', ['daftar_aplikasi', 'closing'])->count(),
                    'scholarship_interested' => StudentQuestionnaire::where('scholarship_interest', 'Ya')->count(),
                ];
                break;

            case 'schools':
                $query = School::with(['district', 'accreditation'])
                    ->withCount(['visits', 'followUps', 'prospects'])
                    ->orderBy('priority_level')
                    ->orderBy('name');

                if ($districtId) $query->where('district_id', $districtId);
                if ($status && $status !== 'all') $query->where('priority_level', $status);
                if ($visitedStatus) {
                    if ($visitedStatus === 'visited') {
                        $query->has('visits');
                    } elseif ($visitedStatus === 'unvisited') {
                        $query->doesntHave('visits');
                    }
                }
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('npsn', 'like', "%{$search}%");
                    });
                }

                $schools = $query->paginate(20)->withQueryString();

                $data['schools'] = $schools;
                $data['stats'] = [
                    'total' => School::count(),
                    'prio_a' => School::where('priority_level', 'A')->count(),
                    'prio_b' => School::where('priority_level', 'B')->count(),
                    'prio_c' => School::where('priority_level', 'C')->count(),
                    'visited' => School::has('visits')->count(),
                    'unvisited' => School::doesntHave('visits')->count(),
                    'total_grade_12' => (int) School::sum('grade_12_count'),
                ];
                break;

            case 'overview':
            default:
                // 1. Rekapitulasi Sekolah per Kecamatan
                $schoolsSummary = District::select(
                    'districts.id',
                    'districts.name',
                    DB::raw('COUNT(DISTINCT schools.id) as total_schools'),
                    DB::raw('SUM(CASE WHEN schools.priority_level = "A" THEN 1 ELSE 0 END) as priority_a'),
                    DB::raw('SUM(CASE WHEN schools.priority_level = "B" THEN 1 ELSE 0 END) as priority_b'),
                    DB::raw('SUM(CASE WHEN schools.priority_level = "C" THEN 1 ELSE 0 END) as priority_c'),
                    DB::raw('SUM(schools.grade_12_count) as total_grade_12')
                )
                ->leftJoin('schools', 'districts.id', '=', 'schools.district_id')
                ->groupBy('districts.id', 'districts.name')
                ->having('total_schools', '>', 0)
                ->orderBy('total_schools', 'desc')
                ->get();

                // Hitung performa per wilayah
                foreach ($schoolsSummary as $s) {
                    $s->visited_schools_count = SchoolVisit::whereHas('school', fn($q) => $q->where('district_id', $s->id))->distinct('school_id')->count('school_id');
                    $s->follow_ups_count = FollowUp::whereHas('school', fn($q) => $q->where('district_id', $s->id))->count();
                    $s->prospects_count = Prospect::whereHas('school', fn($q) => $q->where('district_id', $s->id))->count();
                    $s->mahasiswa_count = Prospect::whereHas('school', fn($q) => $q->where('district_id', $s->id))->where('status', 'mahasiswa')->count();
                }

                // 2. Performa Tim Marketing
                $marketingPerformance = User::active()
                    ->withCount([
                        'schoolVisits',
                        'followUps',
                        'prospects',
                        'visitPlans',
                    ])
                    ->get();

                foreach ($marketingPerformance as $m) {
                    $m->fixed_schedules_count = FollowUp::where('user_id', $m->id)->where(fn($q) => $q->where('is_schedule_fixed', true)->orWhere('status', 'completed'))->count();
                    $m->converted_mahasiswa_count = Prospect::where('marketing_user_id', $m->id)->where('status', 'mahasiswa')->count();
                    $m->conversion_rate = $m->prospects_count > 0 ? round(($m->converted_mahasiswa_count / $m->prospects_count) * 100, 1) : 0;
                }

                // 3. Prospek Status Breakdown (5 Tahapan PMB)
                $prospectStatusBreakdown = [
                    'daftar_akun' => Prospect::where('status', Prospect::STATUS_DAFTAR_AKUN)->count(),
                    'bayar_registrasi' => Prospect::where('status', Prospect::STATUS_BAYAR_REGISTRASI)->count(),
                    'ujian_saringan' => Prospect::where('status', Prospect::STATUS_UJIAN_SARINGAN)->count(),
                    'bayar_daftar_ulang' => Prospect::where('status', Prospect::STATUS_BAYAR_DAFTAR_ULANG)->count(),
                    'berkas_lengkap' => Prospect::where('status', Prospect::STATUS_BERKAS_LENGKAP)->count(),
                    'mahasiswa' => Prospect::where('status', Prospect::STATUS_MAHASISWA)->count(),
                    'tidak_lanjut' => Prospect::where('status', Prospect::STATUS_TIDAK_LANJUT)->count(),
                ];

                // 4. Kunjungan Terbaru & Follow Up Terbaru
                $recentVisits = SchoolVisit::with(['school.district', 'user'])->latest('visit_date')->take(5)->get();
                $recentFollowups = FollowUp::with(['school.district', 'user'])->latest('followup_date')->take(5)->get();

                // 5. Distribusi Prioritas Sekolah
                $priorityDistribution = [
                    'A' => School::where('priority_level', 'A')->count(),
                    'B' => School::where('priority_level', 'B')->count(),
                    'C' => School::where('priority_level', 'C')->count(),
                ];

                $data['schoolsSummary'] = $schoolsSummary;
                $data['marketingPerformance'] = $marketingPerformance;
                $data['prospectStatusBreakdown'] = $prospectStatusBreakdown;
                $data['priorityDistribution'] = $priorityDistribution;
                $data['recentVisits'] = $recentVisits;
                $data['recentFollowups'] = $recentFollowups;
                break;
        }

        return view('reports.index', array_merge($data, [
            'activeTab' => $activeTab,
            'districts' => $districts,
            'marketingUsers' => $marketingUsers,
            'funnelData' => $funnelData,
            'filter' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'district_id' => $districtId,
                'user_id' => $userId,
                'status' => $status,
                'search' => $search,
                'visited_status' => $visitedStatus,
            ],
        ]));
    }

    /**
     * Export Report Data to Excel.
     */
    public function export(Request $request, ReportExportService $exportService): StreamedResponse
    {
        return $exportService->export($request);
    }
}
