<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\FollowUp;
use App\Models\MarketingActivity;
use App\Models\Prospect;
use App\Models\School;
use App\Models\SchoolType;
use App\Models\SchoolVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the SAPA Sekolah Marketing Command Center Dashboard.
     */
    public function index(): View
    {
        $activeCampus = \App\Services\AppContextService::getActiveCampus();
        $activePeriod = \App\Services\AppContextService::getActivePeriod();

        // Base Query Sekolah Sesuai Kampus Aktif
        $schoolsQuery = School::query();
        if ($activeCampus) {
            $schoolsQuery->where('campus_id', $activeCampus->id);
        }

        // 1. KPI Statistics
        $totalSchools = (clone $schoolsQuery)->count();
        $priorityA = (clone $schoolsQuery)->where('priority_level', 'A')->count();
        $priorityB = (clone $schoolsQuery)->where('priority_level', 'B')->count();
        $priorityC = (clone $schoolsQuery)->where('priority_level', 'C')->count();
        $unranked = (clone $schoolsQuery)->whereNull('priority_level')->count();

        $visitedCount = (clone $schoolsQuery)->has('visits')->count();
        $unvisitedCount = $totalSchools - $visitedCount;

        $followUpBase = FollowUp::query();
        if ($activeCampus) $followUpBase->where('campus_id', $activeCampus->id);
        if ($activePeriod) $followUpBase->where('academic_period_id', $activePeriod->id);

        $followUpsToday = (clone $followUpBase)->whereDate('followup_date', today())
            ->where('status', 'pending')
            ->count();

        $prospectBase = Prospect::query();
        if ($activeCampus) $prospectBase->where('campus_id', $activeCampus->id);
        if ($activePeriod) $prospectBase->where('academic_period_id', $activePeriod->id);

        $totalProspects = (clone $prospectBase)->count();
        $registeredProspects = (clone $prospectBase)->whereIn('status', [
            Prospect::STATUS_BAYAR_REGISTRASI,
            Prospect::STATUS_UJIAN_SARINGAN,
            Prospect::STATUS_BAYAR_DAFTAR_ULANG,
            Prospect::STATUS_BERKAS_LENGKAP,
            Prospect::STATUS_MAHASISWA
        ])->count();
        $mahasiswaCount = (clone $prospectBase)->where('status', Prospect::STATUS_MAHASISWA)->count();

        // 2. Charts Data
        // Schools per Kecamatan (Sesuai Kampus)
        $schoolsPerDistrictQuery = District::select('districts.name', DB::raw('COUNT(schools.id) as total'))
            ->leftJoin('schools', function ($join) use ($activeCampus) {
                $join->on('districts.id', '=', 'schools.district_id');
                if ($activeCampus) {
                    $join->where('schools.campus_id', '=', $activeCampus->id);
                }
            })
            ->whereNotIn('districts.name', District::EXCLUDED_DISTRICTS)
            ->groupBy('districts.id', 'districts.name')
            ->having('total', '>', 0)
            ->orderBy('total', 'desc');

        $schoolsPerDistrict = $schoolsPerDistrictQuery->get();

        // Schools per Jenjang (SMA, SMK, MA, SLB Sesuai Kampus)
        $schoolsPerType = SchoolType::select('school_types.name', DB::raw('COUNT(schools.id) as total'))
            ->leftJoin('schools', function ($join) use ($activeCampus) {
                $join->on('school_types.id', '=', 'schools.school_type_id');
                if ($activeCampus) {
                    $join->where('schools.campus_id', '=', $activeCampus->id);
                }
            })
            ->groupBy('school_types.id', 'school_types.name')
            ->get();

        // Monthly Visit Trends (Last 6 Months)
        $visitMonthlyTrendsQuery = SchoolVisit::select(
            DB::raw("DATE_FORMAT(visit_date, '%b %Y') as month"),
            DB::raw("COUNT(id) as total")
        );
        if ($activeCampus) $visitMonthlyTrendsQuery->where('campus_id', $activeCampus->id);
        if ($activePeriod) $visitMonthlyTrendsQuery->where('academic_period_id', $activePeriod->id);

        $visitMonthlyTrends = $visitMonthlyTrendsQuery
            ->groupBy('month')
            ->orderBy(DB::raw("MIN(visit_date)"), 'asc')
            ->limit(6)
            ->get();

        // 3. Widget Data
        // Top 10 Potential Schools
        $topPotentialSchools = (clone $schoolsQuery)->with(['district', 'schoolType'])
            ->orderBy('potential_score', 'desc')
            ->orderBy('student_count', 'desc')
            ->limit(10)
            ->get();

        // Next Suggested Schools (Unvisited or High XII grade count)
        $suggestedSchools = (clone $schoolsQuery)->with(['district', 'schoolType'])
            ->doesntHave('visits')
            ->orderBy('grade_12_count', 'desc')
            ->limit(5)
            ->get();

        // Upcoming Follow Ups
        $upcomingFollowups = (clone $followUpBase)->with(['school', 'user'])
            ->where('status', 'pending')
            ->orderBy('followup_date', 'asc')
            ->limit(5)
            ->get();

        // Recent Marketing Activities
        $recentActivities = MarketingActivity::orderBy('start_date', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'totalSchools',
            'priorityA',
            'priorityB',
            'priorityC',
            'unranked',
            'visitedCount',
            'unvisitedCount',
            'followUpsToday',
            'totalProspects',
            'registeredProspects',
            'mahasiswaCount',
            'schoolsPerDistrict',
            'schoolsPerType',
            'visitMonthlyTrends',
            'topPotentialSchools',
            'suggestedSchools',
            'upcomingFollowups',
            'recentActivities'
        ));
    }
}
