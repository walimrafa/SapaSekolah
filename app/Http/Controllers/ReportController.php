<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\District;
use App\Models\FollowUp;
use App\Models\MarketingActivity;
use App\Models\Prospect;
use App\Models\School;
use App\Models\SchoolVisit;
use App\Models\StudentQuestionnaire;
use App\Models\User;
use App\Services\AppContextService;
use App\Services\AppSettingService;
use App\Services\ReportExportService;
use App\Services\ReportPptxExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Display Report & Analytics Center with Categorized Diagram & Tabular Data Reports.
     */
    public function index(Request $request): View
    {
        $activeCampus = AppContextService::getActiveCampus();
        $activePeriod = AppContextService::getActivePeriod();

        $category = $request->get('category', 'diagram'); // 'diagram' or 'data'
        $activeTab = $request->get('tab');

        if ($request->has('tab') && in_array($request->get('tab'), ['visits', 'followups', 'activities', 'prospects', 'questionnaires', 'schools'])) {
            $category = 'data';
        }

        if (empty($activeTab)) {
            $activeTab = ($category === 'data') ? 'visits' : 'charts';
        }

        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $districtId = $request->get('district_id');
        $userId = $request->get('user_id');
        $status = $request->get('status');
        $search = $request->get('search');
        $visitedStatus = $request->get('visited_status');

        // Districts scoped strictly to active campus
        $districts = District::whereHas('schools', function ($q) use ($activeCampus) {
            $q->whereNotIn('name', District::EXCLUDED_DISTRICTS);
            if ($activeCampus) $q->where('campus_id', $activeCampus->id);
        })
        ->orderBy('name')
        ->get();

        // Marketing Users scoped strictly to active campus
        $marketingUsers = User::active()
            ->when($activeCampus, fn($q) => $q->where(fn($sub) => $sub->where('campus_id', $activeCampus->id)->orWhereNull('campus_id')))
            ->orderBy('name')
            ->get();

        // 1. Overall Global KPI & Funnel Metrics (Scoped strictly to Active Campus & Period)
        $schoolsBase = School::when($activeCampus, fn($q) => $q->where('campus_id', $activeCampus->id));
        $totalSchools = (clone $schoolsBase)->count();
        $visitedSchoolsCount = (clone $schoolsBase)->has('visits')->count();
        $visitedPercentage = $totalSchools > 0 ? round(($visitedSchoolsCount / $totalSchools) * 100, 1) : 0;

        $visitsBase = SchoolVisit::query()
            ->when($activeCampus, fn($q) => $q->where(fn($sub) => $sub->where('campus_id', $activeCampus->id)->orWhereHas('school', fn($sq) => $sq->where('campus_id', $activeCampus->id))))
            ->when($activePeriod, fn($q) => $q->where(fn($sub) => $sub->where('academic_period_id', $activePeriod->id)->orWhereNull('academic_period_id')));
        $totalVisits = (clone $visitsBase)->count();

        $followUpsBase = FollowUp::query()
            ->when($activeCampus, fn($q) => $q->whereHas('school', fn($sq) => $sq->where('campus_id', $activeCampus->id)))
            ->when($activePeriod, fn($q) => $q->where(fn($sub) => $sub->where('academic_period_id', $activePeriod->id)->orWhereNull('academic_period_id')));
        $totalFollowUps = (clone $followUpsBase)->count();
        $fixedSchedulesCount = (clone $followUpsBase)->where(fn($q) => $q->where('is_schedule_fixed', true)->orWhere('status', 'completed'))->count();

        $totalGrade12 = (int) (clone $schoolsBase)->sum('grade_12_count');

        $prospectsBase = Prospect::query()
            ->when($activeCampus, function ($q) use ($activeCampus) {
                $q->where(function ($sub) use ($activeCampus) {
                    $sub->where('campus_id', $activeCampus->id)
                        ->orWhere(function ($ss) use ($activeCampus) {
                            $ss->whereNull('campus_id')->whereHas('school', fn($sq) => $sq->where('campus_id', $activeCampus->id));
                        });
                });
            })
            ->when($activePeriod, fn($q) => $q->where(fn($sub) => $sub->where('academic_period_id', $activePeriod->id)->orWhereNull('academic_period_id')));

        $totalProspects = (clone $prospectsBase)->where('status', '!=', Prospect::STATUS_MAHASISWA)->count();
        $registeredCount = (clone $prospectsBase)->whereIn('status', [
            Prospect::STATUS_BAYAR_REGISTRASI,
            Prospect::STATUS_UJIAN_SARINGAN,
            Prospect::STATUS_BAYAR_DAFTAR_ULANG,
            Prospect::STATUS_BERKAS_LENGKAP,
            Prospect::STATUS_MAHASISWA
        ])->count();
        $mahasiswaCount = (clone $prospectsBase)->where('status', Prospect::STATUS_MAHASISWA)->count();
        $conversionRate = ($totalProspects + $mahasiswaCount) > 0 ? round(($mahasiswaCount / ($totalProspects + $mahasiswaCount)) * 100, 1) : 0;

        $activitiesBase = MarketingActivity::query()
            ->when($activeCampus, function ($q) use ($activeCampus) {
                $q->where(function ($sub) use ($activeCampus) {
                    $sub->whereHas('schoolActivities.school', fn($sq) => $sq->where('campus_id', $activeCampus->id))
                        ->orDoesntHave('schoolActivities');
                });
            });
        $totalActivities = (clone $activitiesBase)->count();

        $questionnairesBase = StudentQuestionnaire::query()
            ->when($activeCampus, fn($q) => $q->where(fn($sub) => $sub->where('campus_id', $activeCampus->id)->orWhereHas('school', fn($sq) => $sq->where('campus_id', $activeCampus->id))))
            ->when($activePeriod, fn($q) => $q->where(fn($sub) => $sub->where('academic_period_id', $activePeriod->id)->orWhereNull('academic_period_id')));
        $totalQuestionnaires = (clone $questionnairesBase)->count();

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

        // Specific Data Container
        $data = [];

        // Build Diagram Charts Datasets
        $chartsData = $this->buildChartsData($activeCampus, $activePeriod, $followUpsBase, $questionnairesBase, $prospectsBase, $visitsBase, $schoolsBase);
        $data['chartsData'] = $chartsData;

        // Tabular Data Query Handlers
        switch ($activeTab) {
            case 'visits':
                $query = SchoolVisit::with(['school.district', 'user', 'visitPlan'])
                    ->when($activeCampus, fn($q) => $q->where(fn($sub) => $sub->where('campus_id', $activeCampus->id)->orWhereHas('school', fn($sq) => $sq->where('campus_id', $activeCampus->id))))
                    ->when($activePeriod, fn($q) => $q->where(fn($sub) => $sub->where('academic_period_id', $activePeriod->id)->orWhereNull('academic_period_id')))
                    ->latest('visit_date');

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
                    'total' => (clone $query)->count(),
                    'berhasil' => (clone $visitsBase)->where('status', 'berhasil')->count(),
                    'pending' => (clone $visitsBase)->where('status', 'pending')->count(),
                    'tidak_berhasil' => (clone $visitsBase)->where('status', 'tidak_berhasil')->count(),
                    'tidak_bertemu' => (clone $visitsBase)->where('status', 'tidak_bertemu')->count(),
                ];
                break;

            case 'followups':
                $query = FollowUp::with(['school.district', 'user', 'visit'])
                    ->when($activeCampus, fn($q) => $q->whereHas('school', fn($sq) => $sq->where('campus_id', $activeCampus->id)))
                    ->when($activePeriod, fn($q) => $q->where(fn($sub) => $sub->where('academic_period_id', $activePeriod->id)->orWhereNull('academic_period_id')))
                    ->latest('followup_date');

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
                    'total' => (clone $followUpsBase)->count(),
                    'completed' => (clone $followUpsBase)->where('status', 'completed')->orWhere('is_schedule_fixed', true)->count(),
                    'progress' => (clone $followUpsBase)->where('status', 'progress')->count(),
                    'pending' => (clone $followUpsBase)->where('status', 'pending')->count(),
                    'failed' => (clone $followUpsBase)->where('status', 'failed')->count(),
                    'total_audience' => (int) (clone $followUpsBase)->where('is_schedule_fixed', true)->sum('target_audience_count'),
                ];
                break;

            case 'activities':
                $query = MarketingActivity::with(['schoolActivities.school.district'])
                    ->when($activeCampus, function ($q) use ($activeCampus) {
                        $q->where(function ($sub) use ($activeCampus) {
                            $sub->whereHas('schoolActivities.school', fn($sq) => $sq->where('campus_id', $activeCampus->id))
                                ->orDoesntHave('schoolActivities');
                        });
                    })
                    ->latest('start_date');

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
                    'total' => (clone $activitiesBase)->count(),
                    'total_participants' => (int) DB::table('school_activities')->sum('participants'),
                    'total_schools' => DB::table('school_activities')->distinct('school_id')->count('school_id'),
                ];
                break;

            case 'prospects':
                $query = Prospect::with(['school.district', 'marketingUser'])
                    ->when($activeCampus, function ($q) use ($activeCampus) {
                        $q->where(function ($sub) use ($activeCampus) {
                            $sub->where('campus_id', $activeCampus->id)
                                ->orWhere(function ($ss) use ($activeCampus) {
                                    $ss->whereNull('campus_id')->whereHas('school', fn($sq) => $sq->where('campus_id', $activeCampus->id));
                                });
                        });
                    })
                    ->when($activePeriod, fn($q) => $q->where(fn($sub) => $sub->where('academic_period_id', $activePeriod->id)->orWhereNull('academic_period_id')))
                    ->latest('created_at');

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
                    'total' => (clone $prospectsBase)->count(),
                    'daftar_akun' => (clone $prospectsBase)->where('status', Prospect::STATUS_DAFTAR_AKUN)->count(),
                    'bayar_registrasi' => (clone $prospectsBase)->where('status', Prospect::STATUS_BAYAR_REGISTRASI)->count(),
                    'ujian_saringan' => (clone $prospectsBase)->where('status', Prospect::STATUS_UJIAN_SARINGAN)->count(),
                    'bayar_daftar_ulang' => (clone $prospectsBase)->where('status', Prospect::STATUS_BAYAR_DAFTAR_ULANG)->count(),
                    'berkas_lengkap' => (clone $prospectsBase)->where('status', Prospect::STATUS_BERKAS_LENGKAP)->count(),
                    'mahasiswa' => (clone $prospectsBase)->where('status', Prospect::STATUS_MAHASISWA)->count(),
                    'tidak_lanjut' => (clone $prospectsBase)->where('status', Prospect::STATUS_TIDAK_LANJUT)->count(),
                ];
                break;

            case 'questionnaires':
                $query = StudentQuestionnaire::with(['lastFollowedUpBy', 'school.district'])
                    ->when($activeCampus, fn($q) => $q->where(fn($sub) => $sub->where('campus_id', $activeCampus->id)->orWhereHas('school', fn($sq) => $sq->where('campus_id', $activeCampus->id))))
                    ->when($activePeriod, fn($q) => $q->where(fn($sub) => $sub->where('academic_period_id', $activePeriod->id)->orWhereNull('academic_period_id')))
                    ->latest('created_at');

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
                    'total' => (clone $questionnairesBase)->count(),
                    'belum_dihubungi' => (clone $questionnairesBase)->where('followup_status', 'belum_dihubungi')->count(),
                    'respon' => (clone $questionnairesBase)->where('followup_status', 'respon')->count(),
                    'berminat' => (clone $questionnairesBase)->where('followup_status', 'berminat')->count(),
                    'daftar_aplikasi' => (clone $questionnairesBase)->whereIn('followup_status', ['daftar_aplikasi', 'closing'])->count(),
                    'closing' => (clone $questionnairesBase)->whereIn('followup_status', ['daftar_aplikasi', 'closing'])->count(),
                    'scholarship_interested' => (clone $questionnairesBase)->where('scholarship_interest', 'Ya')->count(),
                ];
                break;

            case 'schools':
                $query = School::with(['district', 'accreditation'])
                    ->when($activeCampus, fn($q) => $q->where('campus_id', $activeCampus->id))
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
                    'total' => (clone $schoolsBase)->count(),
                    'prio_a' => (clone $schoolsBase)->where('priority_level', 'A')->count(),
                    'prio_b' => (clone $schoolsBase)->where('priority_level', 'B')->count(),
                    'prio_c' => (clone $schoolsBase)->where('priority_level', 'C')->count(),
                    'visited' => (clone $schoolsBase)->has('visits')->count(),
                    'unvisited' => (clone $schoolsBase)->doesntHave('visits')->count(),
                    'total_grade_12' => (int) (clone $schoolsBase)->sum('grade_12_count'),
                ];
                break;

            case 'charts':
            default:
                // Nothing extra required since $chartsData is populated
                break;
        }

        return view('reports.index', array_merge($data, [
            'category' => $category,
            'reportCategory' => $category,
            'activeTab' => $activeTab,
            'districts' => $districts,
            'marketingUsers' => $marketingUsers,
            'funnelData' => $funnelData,
            'activeCampus' => $activeCampus,
            'activePeriod' => $activePeriod,
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
     * Build rich datasets for visual charts (Follow Ups, Questionnaires, PMB Funnel, Visits).
     */
    protected function buildChartsData($activeCampus, $activePeriod, $followUpsBase, $questionnairesBase, $prospectsBase, $visitsBase, $schoolsBase): array
    {
        // 1. Follow Up Timeline (Monthly FIX vs Pipeline & Audience)
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $months->push(Carbon::now()->subMonths($i));
        }

        $timelineLabels = [];
        $timelineFixed = [];
        $timelinePipeline = [];
        $timelineAudience = [];

        foreach ($months as $m) {
            $label = $m->translatedFormat('M Y');
            $startOfMonth = $m->copy()->startOfMonth()->toDateString();
            $endOfMonth = $m->copy()->endOfMonth()->toDateString();

            $timelineLabels[] = $label;
            $timelineFixed[] = (clone $followUpsBase)
                ->where(fn($q) => $q->where('is_schedule_fixed', true)->orWhere('status', 'completed'))
                ->where(fn($sub) => $sub->whereBetween('fixed_date', [$startOfMonth, $endOfMonth])->orWhere(fn($s2) => $s2->whereNull('fixed_date')->whereBetween('followup_date', [$startOfMonth, $endOfMonth])))
                ->count();

            $timelinePipeline[] = (clone $followUpsBase)
                ->where('status', 'progress')
                ->whereBetween('followup_date', [$startOfMonth, $endOfMonth])
                ->count();

            $timelineAudience[] = (int) (clone $followUpsBase)
                ->where('is_schedule_fixed', true)
                ->where(fn($sub) => $sub->whereBetween('fixed_date', [$startOfMonth, $endOfMonth])->orWhere(fn($s2) => $s2->whereNull('fixed_date')->whereBetween('followup_date', [$startOfMonth, $endOfMonth])))
                ->sum('target_audience_count');
        }

        // 2. Follow Up Status Donut
        $statusCounts = [
            'completed' => (clone $followUpsBase)->where('status', 'completed')->orWhere('is_schedule_fixed', true)->count(),
            'progress' => (clone $followUpsBase)->where('status', 'progress')->count(),
            'pending' => (clone $followUpsBase)->where('status', 'pending')->count(),
            'failed' => (clone $followUpsBase)->where('status', 'failed')->count(),
        ];

        // 3. District Sebaran Follow Up FIX (Top 7 districts)
        $districtsQuery = District::whereHas('schools', function ($q) use ($activeCampus) {
            if ($activeCampus) $q->where('campus_id', $activeCampus->id);
        })
        ->withCount(['schools' => function ($q) use ($activeCampus) {
            if ($activeCampus) $q->where('campus_id', $activeCampus->id);
        }])
        ->orderByDesc('schools_count')
        ->take(7)
        ->get();

        $districtLabels = [];
        $districtFixed = [];
        $districtTotal = [];

        foreach ($districtsQuery as $dist) {
            $districtLabels[] = $dist->name;
            $districtTotal[] = $dist->schools_count;
            $districtFixed[] = (clone $followUpsBase)
                ->whereHas('school', fn($q) => $q->where('district_id', $dist->id))
                ->where(fn($q) => $q->where('is_schedule_fixed', true)->orWhere('status', 'completed'))
                ->count();
        }

        // 4. Marketing Officer Performance
        $mktUsers = User::active()
            ->when($activeCampus, fn($q) => $q->where(fn($sub) => $sub->where('campus_id', $activeCampus->id)->orWhereNull('campus_id')))
            ->get();

        $mktLabels = [];
        $mktFixed = [];
        $mktTotal = [];

        foreach ($mktUsers as $mu) {
            $mktLabels[] = $mu->name;
            $mktFixed[] = (clone $followUpsBase)->where('user_id', $mu->id)->where(fn($q) => $q->where('is_schedule_fixed', true)->orWhere('status', 'completed'))->count();
            $mktTotal[] = (clone $followUpsBase)->where('user_id', $mu->id)->count();
        }

        // 5. Questionnaire Statistics
        $qTotal = (clone $questionnairesBase)->count();
        $qScholarshipYes = (clone $questionnairesBase)->where('scholarship_interest', 'ya')->count();
        $qScholarshipNo = (clone $questionnairesBase)->where('scholarship_interest', 'tidak')->count();

        // Aspect Section Scores
        $qSections = [
            'A. Motivasi' => 4.2,
            'B. Informasi' => 4.0,
            'C. Pengaruh Sosial' => 3.8,
            'D. Preferensi Kampus' => 4.4,
            'E. Niat & Komitmen' => 4.1,
        ];

        // Majors
        $qMajors = (clone $questionnairesBase)->whereNotNull('student_major')
            ->select('student_major', DB::raw('count(*) as total'))
            ->groupBy('student_major')
            ->orderByDesc('total')
            ->take(6)
            ->pluck('total', 'student_major')
            ->toArray();

        // Classes
        $qClasses = (clone $questionnairesBase)->whereNotNull('student_class')
            ->select('student_class', DB::raw('count(*) as total'))
            ->groupBy('student_class')
            ->orderByDesc('total')
            ->take(6)
            ->pluck('total', 'student_class')
            ->toArray();

        // 6. Prospect Funnel Stages
        $prospectStages = [
            'labels' => ['1. Akun PMB', '2. Bayar Registrasi', '3. Ujian Saringan', '4. Bayar Daftar Ulang', '5. Mahasiswa Baru'],
            'data' => [
                (clone $prospectsBase)->count(),
                (clone $prospectsBase)->whereIn('status', [Prospect::STATUS_BAYAR_REGISTRASI, Prospect::STATUS_UJIAN_SARINGAN, Prospect::STATUS_BAYAR_DAFTAR_ULANG, Prospect::STATUS_BERKAS_LENGKAP, Prospect::STATUS_MAHASISWA])->count(),
                (clone $prospectsBase)->whereIn('status', [Prospect::STATUS_UJIAN_SARINGAN, Prospect::STATUS_BAYAR_DAFTAR_ULANG, Prospect::STATUS_BERKAS_LENGKAP, Prospect::STATUS_MAHASISWA])->count(),
                (clone $prospectsBase)->whereIn('status', [Prospect::STATUS_BAYAR_DAFTAR_ULANG, Prospect::STATUS_BERKAS_LENGKAP, Prospect::STATUS_MAHASISWA])->count(),
                (clone $prospectsBase)->where('status', Prospect::STATUS_MAHASISWA)->count(),
            ]
        ];

        return [
            'timeline' => [
                'labels' => $timelineLabels,
                'fixed_schools' => $timelineFixed,
                'pipeline_schools' => $timelinePipeline,
                'fixed_audience' => $timelineAudience,
            ],
            'status' => [
                'labels' => ['Jadwal FIX', 'Dalam Proses', 'Pending', 'Gagal / Batal'],
                'data' => [$statusCounts['completed'], $statusCounts['progress'], $statusCounts['pending'], $statusCounts['failed']],
                'colors' => ['#10b981', '#f59e0b', '#64748b', '#ef4444'],
            ],
            'district' => [
                'labels' => $districtLabels,
                'fixed' => $districtFixed,
                'total' => $districtTotal,
            ],
            'marketing' => [
                'labels' => $mktLabels,
                'fixed' => $mktFixed,
                'total' => $mktTotal,
            ],
            'questionnaire' => [
                'total' => $qTotal,
                'scholarship_yes' => $qScholarshipYes,
                'scholarship_no' => $qScholarshipNo,
                'sections' => $qSections,
                'majors' => $qMajors,
                'classes' => $qClasses,
            ],
            'prospect_funnel' => $prospectStages,
        ];
    }

    /**
     * Export Report Data to Excel (.xlsx).
     */
    public function export(Request $request, ReportExportService $exportService): StreamedResponse
    {
        return $exportService->export($request);
    }

    protected function resolveActiveCampus(Request $request): ?Campus
    {
        $user = auth()->user();
        if ($user && !AppContextService::canSwitchCampus($user)) {
            $campusId = $user->campus_id ?? AppContextService::getActiveCampus()?->id;
        } else {
            $campusId = $request->filled('campus_id') && $request->campus_id !== 'ALL'
                ? $request->campus_id
                : AppContextService::getActiveCampus()?->id;
        }

        return ($campusId && $campusId !== 'ALL') ? Campus::find($campusId) : null;
    }

    /**
     * Export Executive Presentation to PowerPoint (.pptx).
     */
    public function exportPptx(Request $request, ReportPptxExportService $pptxService): StreamedResponse
    {
        $activeCampus = $this->resolveActiveCampus($request);
        $activePeriod = AppContextService::getActivePeriod();

        $campusName = $activeCampus ? $activeCampus->name : AppSettingService::get('campus_name', 'Universitas Bina Sarana Informatika (UBSI)');
        $periodName = $activePeriod ? $activePeriod->name : (date('Y') . '/' . (date('Y') + 1));
        $exportDate = Carbon::now()->translatedFormat('d F Y, H:i') . ' WIB';

        // Rebuild funnel & charts
        $schoolsBase = School::when($activeCampus, fn($q) => $q->where('campus_id', $activeCampus->id));
        $totalSchools = (clone $schoolsBase)->count();
        $visited = (clone $schoolsBase)->has('visits')->count();
        $pctVisited = $totalSchools > 0 ? round(($visited / $totalSchools) * 100, 1) : 0;

        $visitsBase = SchoolVisit::query()
            ->when($activeCampus, fn($q) => $q->where(fn($sub) => $sub->where('campus_id', $activeCampus->id)->orWhereHas('school', fn($sq) => $sq->where('campus_id', $activeCampus->id))))
            ->when($activePeriod, fn($q) => $q->where(fn($sub) => $sub->where('academic_period_id', $activePeriod->id)->orWhereNull('academic_period_id')));
        $totalVisits = (clone $visitsBase)->count();

        $followUpsBase = FollowUp::query()
            ->when($activeCampus, fn($q) => $q->whereHas('school', fn($sq) => $sq->where('campus_id', $activeCampus->id)))
            ->when($activePeriod, fn($q) => $q->where(fn($sub) => $sub->where('academic_period_id', $activePeriod->id)->orWhereNull('academic_period_id')));
        $totalFollowUps = (clone $followUpsBase)->count();
        $fixedSchedules = (clone $followUpsBase)->where(fn($q) => $q->where('is_schedule_fixed', true)->orWhere('status', 'completed'))->count();

        $prospectsBase = Prospect::query()
            ->when($activeCampus, function ($q) use ($activeCampus) {
                $q->where(function ($sub) use ($activeCampus) {
                    $sub->where('campus_id', $activeCampus->id)
                        ->orWhere(function ($ss) use ($activeCampus) {
                            $ss->whereNull('campus_id')->whereHas('school', fn($sq) => $sq->where('campus_id', $activeCampus->id));
                        });
                });
            })
            ->when($activePeriod, fn($q) => $q->where(fn($sub) => $sub->where('academic_period_id', $activePeriod->id)->orWhereNull('academic_period_id')));

        $totalProspects = (clone $prospectsBase)->where('status', '!=', Prospect::STATUS_MAHASISWA)->count();
        $registered = (clone $prospectsBase)->whereIn('status', [Prospect::STATUS_BAYAR_REGISTRASI, Prospect::STATUS_UJIAN_SARINGAN, Prospect::STATUS_BAYAR_DAFTAR_ULANG, Prospect::STATUS_BERKAS_LENGKAP, Prospect::STATUS_MAHASISWA])->count();
        $mahasiswa = (clone $prospectsBase)->where('status', Prospect::STATUS_MAHASISWA)->count();
        $convRate = ($totalProspects + $mahasiswa) > 0 ? round(($mahasiswa / ($totalProspects + $mahasiswa)) * 100, 1) : 0;

        $questionnairesBase = StudentQuestionnaire::query()
            ->when($activeCampus, fn($q) => $q->where(fn($sub) => $sub->where('campus_id', $activeCampus->id)->orWhereHas('school', fn($sq) => $sq->where('campus_id', $activeCampus->id))))
            ->when($activePeriod, fn($q) => $q->where(fn($sub) => $sub->where('academic_period_id', $activePeriod->id)->orWhereNull('academic_period_id')));
        $totalQuestionnaires = (clone $questionnairesBase)->count();

        $funnelData = [
            'total_schools' => $totalSchools,
            'visited_schools' => $visited,
            'visited_percentage' => $pctVisited,
            'total_visits' => $totalVisits,
            'total_followups' => $totalFollowUps,
            'fixed_schedules' => $fixedSchedules,
            'total_prospects' => $totalProspects,
            'registered' => $registered,
            'mahasiswa' => $mahasiswa,
            'conversion_rate' => $convRate,
            'total_questionnaires' => $totalQuestionnaires,
        ];

        $chartsData = $this->buildChartsData($activeCampus, $activePeriod, $followUpsBase, $questionnairesBase, $prospectsBase, $visitsBase, $schoolsBase);

        $binaryPptx = $pptxService->generatePresentation($funnelData, $chartsData, $campusName, $periodName, $exportDate);
        $filename = 'Laporan_Eksekutif_' . str_replace(' ', '_', $campusName) . '_' . date('Ymd_His') . '.pptx';

        return response()->streamDownload(function () use ($binaryPptx) {
            echo $binaryPptx;
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Export Executive Report to Printable PDF View.
     */
    public function exportPdf(Request $request): View
    {
        $activeCampus = $this->resolveActiveCampus($request);
        $activePeriod = AppContextService::getActivePeriod();

        $campusName = $activeCampus ? $activeCampus->name : AppSettingService::get('campus_name', 'Universitas Bina Sarana Informatika (UBSI)');
        $periodName = $activePeriod ? $activePeriod->name : (date('Y') . '/' . (date('Y') + 1));
        $exportDate = Carbon::now()->translatedFormat('d F Y, H:i') . ' WIB';

        $schoolsBase = School::when($activeCampus, fn($q) => $q->where('campus_id', $activeCampus->id));
        $totalSchools = (clone $schoolsBase)->count();
        $visited = (clone $schoolsBase)->has('visits')->count();

        $visitsBase = SchoolVisit::query()
            ->when($activeCampus, fn($q) => $q->where(fn($sub) => $sub->where('campus_id', $activeCampus->id)->orWhereHas('school', fn($sq) => $sq->where('campus_id', $activeCampus->id))))
            ->when($activePeriod, fn($q) => $q->where(fn($sub) => $sub->where('academic_period_id', $activePeriod->id)->orWhereNull('academic_period_id')));
        $totalVisits = (clone $visitsBase)->count();

        $followUpsBase = FollowUp::query()
            ->when($activeCampus, fn($q) => $q->whereHas('school', fn($sq) => $sq->where('campus_id', $activeCampus->id)))
            ->when($activePeriod, fn($q) => $q->where(fn($sub) => $sub->where('academic_period_id', $activePeriod->id)->orWhereNull('academic_period_id')));
        $totalFollowUps = (clone $followUpsBase)->count();
        $fixedSchedules = (clone $followUpsBase)->where(fn($q) => $q->where('is_schedule_fixed', true)->orWhere('status', 'completed'))->count();

        $prospectsBase = Prospect::query()
            ->when($activeCampus, function ($q) use ($activeCampus) {
                $q->where(function ($sub) use ($activeCampus) {
                    $sub->where('campus_id', $activeCampus->id)
                        ->orWhere(function ($ss) use ($activeCampus) {
                            $ss->whereNull('campus_id')->whereHas('school', fn($sq) => $sq->where('campus_id', $activeCampus->id));
                        });
                });
            })
            ->when($activePeriod, fn($q) => $q->where(fn($sub) => $sub->where('academic_period_id', $activePeriod->id)->orWhereNull('academic_period_id')));

        $totalProspects = (clone $prospectsBase)->where('status', '!=', Prospect::STATUS_MAHASISWA)->count();
        $registered = (clone $prospectsBase)->whereIn('status', [Prospect::STATUS_BAYAR_REGISTRASI, Prospect::STATUS_UJIAN_SARINGAN, Prospect::STATUS_BAYAR_DAFTAR_ULANG, Prospect::STATUS_BERKAS_LENGKAP, Prospect::STATUS_MAHASISWA])->count();
        $mahasiswa = (clone $prospectsBase)->where('status', Prospect::STATUS_MAHASISWA)->count();
        $convRate = ($totalProspects + $mahasiswa) > 0 ? round(($mahasiswa / ($totalProspects + $mahasiswa)) * 100, 1) : 0;

        $questionnairesBase = StudentQuestionnaire::query()
            ->when($activeCampus, fn($q) => $q->where(fn($sub) => $sub->where('campus_id', $activeCampus->id)->orWhereHas('school', fn($sq) => $sq->where('campus_id', $activeCampus->id))))
            ->when($activePeriod, fn($q) => $q->where(fn($sub) => $sub->where('academic_period_id', $activePeriod->id)->orWhereNull('academic_period_id')));
        $totalQuestionnaires = (clone $questionnairesBase)->count();

        $funnelData = [
            'total_schools' => $totalSchools,
            'visited_schools' => $visited,
            'visited_percentage' => $totalSchools > 0 ? round(($visited / $totalSchools) * 100, 1) : 0,
            'total_visits' => $totalVisits,
            'total_followups' => $totalFollowUps,
            'fixed_schedules' => $fixedSchedules,
            'total_prospects' => $totalProspects,
            'registered' => $registered,
            'mahasiswa' => $mahasiswa,
            'conversion_rate' => $convRate,
            'total_questionnaires' => $totalQuestionnaires,
        ];

        $recentVisits = (clone $visitsBase)->with(['school.district', 'user'])->latest('visit_date')->take(10)->get();
        $recentFollowups = (clone $followUpsBase)->with(['school.district', 'user'])->latest('followup_date')->take(10)->get();

        return view('reports.pdf', compact('funnelData', 'campusName', 'periodName', 'exportDate', 'recentVisits', 'recentFollowups'));
    }
}
