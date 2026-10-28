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
        // 1. KPI Statistics
        $totalSchools = School::count();
        $priorityA = School::where('priority_level', 'A')->count();
        $priorityB = School::where('priority_level', 'B')->count();
        $priorityC = School::where('priority_level', 'C')->count();
        $unranked = School::whereNull('priority_level')->count();

        $visitedCount = School::has('visits')->count();
        $unvisitedCount = $totalSchools - $visitedCount;

        $followUpsToday = FollowUp::whereDate('followup_date', today())
            ->where('status', 'pending')
            ->count();

        $totalProspects = Prospect::count();
        $registeredProspects = Prospect::whereIn('status', [
            Prospect::STATUS_BAYAR_REGISTRASI,
            Prospect::STATUS_UJIAN_SARINGAN,
            Prospect::STATUS_BAYAR_DAFTAR_ULANG,
            Prospect::STATUS_BERKAS_LENGKAP,
            Prospect::STATUS_MAHASISWA
        ])->count();
        $mahasiswaCount = Prospect::where('status', Prospect::STATUS_MAHASISWA)->count();

        // 2. Charts Data
        // Schools per Kecamatan
        $schoolsPerDistrict = District::select('districts.name', DB::raw('COUNT(schools.id) as total'))
            ->leftJoin('schools', 'districts.id', '=', 'schools.district_id')
            ->whereNotIn('districts.name', District::EXCLUDED_DISTRICTS)
            ->groupBy('districts.id', 'districts.name')
            ->having('total', '>', 0)
            ->orderBy('total', 'desc')
            ->get();

        // Schools per Jenjang (SMA, SMK, MA, SLB)
        $schoolsPerType = SchoolType::select('school_types.name', DB::raw('COUNT(schools.id) as total'))
            ->leftJoin('schools', 'school_types.id', '=', 'schools.school_type_id')
            ->groupBy('school_types.id', 'school_types.name')
            ->get();

        // Monthly Visit Trends (Last 6 Months)
        $visitMonthlyTrends = SchoolVisit::select(
            DB::raw("DATE_FORMAT(visit_date, '%b %Y') as month"),
            DB::raw("COUNT(id) as total")
        )
        ->groupBy('month')
        ->orderBy(DB::raw("MIN(visit_date)"), 'asc')
        ->limit(6)
        ->get();

        // 3. Widget Data
        // Top 10 Potential Schools
        $topPotentialSchools = School::with(['district', 'schoolType'])
            ->orderBy('potential_score', 'desc')
            ->orderBy('student_count', 'desc')
            ->limit(10)
            ->get();

        // Next Suggested Schools (Unvisited or High XII grade count)
        $suggestedSchools = School::with(['district', 'schoolType'])
            ->doesntHave('visits')
            ->orderBy('grade_12_count', 'desc')
            ->limit(5)
            ->get();

        // Upcoming Follow Ups
        $upcomingFollowups = FollowUp::with(['school', 'user'])
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
