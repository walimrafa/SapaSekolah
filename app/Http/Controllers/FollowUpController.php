<?php

namespace App\Http\Controllers;

use App\Models\FollowUp;
use App\Models\MarketingActivity;
use App\Models\School;
use App\Models\SchoolActivity;
use App\Models\SchoolVisit;
use App\Models\User;
use App\Services\FollowUpExportService;
use App\Services\FollowUpSyncService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FollowUpController extends Controller
{
    /**
     * Display Follow-up Kanban & List View for Fixing Socialization Schedule.
     */
    public function index(Request $request, FollowUpSyncService $syncService): View
    {
        // Ensure all field visits are automatically synchronized into follow-ups
        try {
            $syncService->syncAllVisits();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Error auto-syncing visits to follow ups: ' . $e->getMessage());
        }

        $viewMode = $request->get('view', 'kanban'); // 'kanban' or 'list'

        $activeCampus = \App\Services\AppContextService::getActiveCampus();
        $activePeriod = \App\Services\AppContextService::getActivePeriod();

        $query = FollowUp::with([
            'school.district', 
            'school.primaryContact', 
            'user', 
            'visit'
        ])->whereNull('socialization_completed_at');

        if ($activeCampus) {
            $query->whereHas('school', function ($sq) use ($activeCampus) {
                $sq->where('campus_id', $activeCampus->id);
            });
        }

        if ($activePeriod) {
            $query->where(function ($q) use ($activePeriod) {
                $q->where('academic_period_id', $activePeriod->id)
                  ->orWhere(function ($sub) use ($activePeriod) {
                      $sub->whereNull('academic_period_id')
                          ->whereHas('visit', fn($vq) => $vq->where('academic_period_id', $activePeriod->id));
                  });
            });
        }

        $selectedCategory = $request->input('agenda_category');
        if ($request->filled('agenda_category')) {
            $cat = $request->input('agenda_category');
            $query->where(function ($q) use ($cat) {
                $q->where('agenda_category', $cat)
                  ->orWhereHas('visit', fn($vq) => $vq->where('agenda_category', $cat));

                if ($cat === FollowUp::AGENDA_PENJADWALAN_SOSIALISASI || $cat === SchoolVisit::AGENDA_PENJADWALAN_SOSIALISASI) {
                    $q->orWhere(function ($sub) {
                        $sub->whereNull('agenda_category')
                            ->where(function ($vsub) {
                                $vsub->whereNull('visit_id')
                                     ->orWhereHas('visit', fn($vq) => $vq->whereNull('agenda_category')->orWhere('agenda_category', 'penjadwalan_sosialisasi'));
                            });
                    });
                }
            });
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('method')) {
            $query->where('method', $request->input('method'));
        }

        if ($request->filled('schedule_status')) {
            if ($request->schedule_status === 'fixed') {
                $query->where(function ($q) {
                    $q->where('is_schedule_fixed', true)
                      ->orWhere('status', 'completed')
                      ->orWhere('invitation_attendance_status', 'akan_hadir')
                      ->orWhereHas('visit', fn($vq) => $vq->where('invitation_attendance_status', 'akan_hadir'));
                });
            } elseif ($request->schedule_status === 'unfixed') {
                $query->where('is_schedule_fixed', false)
                      ->where('status', '!=', 'completed')
                      ->where(function ($q) {
                          $q->whereNull('invitation_attendance_status')
                            ->orWhere('invitation_attendance_status', '!=', 'akan_hadir');
                      });
            } elseif ($request->schedule_status === 'progress') {
                $query->where(function ($q) {
                    $q->where('status', 'progress')
                      ->orWhere('invitation_attendance_status', 'minta_ijin_dulu')
                      ->orWhereHas('visit', fn($vq) => $vq->where('invitation_attendance_status', 'minta_ijin_dulu'));
                });
            } elseif ($request->schedule_status === 'failed') {
                $query->where(function ($q) {
                    $q->where('status', 'failed')
                      ->orWhere('invitation_attendance_status', 'tidak_bisa_hadir')
                      ->orWhereHas('visit', fn($vq) => $vq->where('invitation_attendance_status', 'tidak_bisa_hadir'));
                });
            }
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('school', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                })
                ->orWhere('pic_name', 'like', "%{$search}%")
                ->orWhere('pic_phone', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $allFollowups = $query->orderBy('followup_date', 'asc')->get();

        // Categorize for Kanban based on category
        if ($selectedCategory === 'mengantar_undangan') {
            $completed = $allFollowups->filter(fn($item) => $item->status === 'completed' || $item->invitation_attendance_status === 'akan_hadir' || $item->visit?->invitation_attendance_status === 'akan_hadir');
            $failed = $allFollowups->filter(fn($item) => ($item->status === 'failed' || $item->invitation_attendance_status === 'tidak_bisa_hadir' || $item->visit?->invitation_attendance_status === 'tidak_bisa_hadir') && !$completed->contains('id', $item->id));
            $progress = $allFollowups->filter(fn($item) => ($item->status === 'progress' || $item->invitation_attendance_status === 'minta_ijin_dulu' || $item->visit?->invitation_attendance_status === 'minta_ijin_dulu') && !$completed->contains('id', $item->id) && !$failed->contains('id', $item->id));
            $pending = $allFollowups->filter(fn($item) => !$completed->contains('id', $item->id) && !$failed->contains('id', $item->id) && !$progress->contains('id', $item->id));
        } else {
            $completed = $allFollowups->filter(fn($item) => $item->status === 'completed' || $item->is_schedule_fixed);
            $failed = $allFollowups->filter(fn($item) => $item->status === 'failed');
            $progress = $allFollowups->filter(fn($item) => $item->status === 'progress' && !$item->is_schedule_fixed);
            $pending = $allFollowups->filter(fn($item) => ($item->status === 'pending' || empty($item->status)) && !$item->is_schedule_fixed);
        }

        // Summary Statistics
        $totalCount = $allFollowups->count();
        $fixedScheduleCount = $completed->count();
        $pendingCount = $pending->count();
        $progressCount = $progress->count();
        $totalEstimatedAudience = $completed->sum('target_audience_count');
        $conversionRate = $totalCount > 0 ? round(($fixedScheduleCount / $totalCount) * 100, 1) : 0;

        // ==========================================
        // ANALYTICS DATA FOR CHARTS & DIAGRAMS
        // ==========================================
        // 1. Status Conversion Breakdown (Donut Chart)
        $chartStatus = [
            'labels' => ['Jadwal FIX', 'Koordinasi Jadwal', 'Menunggu Konfirmasi', 'Batal / Ditolak'],
            'data' => [$completed->count(), $progress->count(), $pending->count(), $failed->count()],
            'colors' => ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
        ];

        // 2. Timeline / Monthly Distribution of Fixed Schedules & Tentative Targets
        $timelineMap = [];
        foreach ($allFollowups as $item) {
            $d = $item->fixed_date ?: ($item->visit?->target_date ?: null);
            $key = $d ? Carbon::parse($d)->translatedFormat('M Y') : 'Tentatif';
            if (!isset($timelineMap[$key])) {
                $timelineMap[$key] = [
                    'fixed_schools' => 0,
                    'pipeline_schools' => 0,
                    'fixed_audience' => 0,
                    'pipeline_audience' => 0,
                ];
            }
            if ($item->is_schedule_fixed || $item->status === 'completed') {
                $timelineMap[$key]['fixed_schools']++;
                $timelineMap[$key]['fixed_audience'] += (int) ($item->target_audience_count ?: 0);
            } else {
                $timelineMap[$key]['pipeline_schools']++;
                $timelineMap[$key]['pipeline_audience'] += (int) ($item->target_audience_count ?: 0);
            }
        }

        $chartTimeline = [
            'labels' => array_keys($timelineMap),
            'fixed_schools' => array_column($timelineMap, 'fixed_schools'),
            'pipeline_schools' => array_column($timelineMap, 'pipeline_schools'),
            'fixed_audience' => array_column($timelineMap, 'fixed_audience'),
            'pipeline_audience' => array_column($timelineMap, 'pipeline_audience'),
        ];

        // 3. District Distribution (Bar Chart)
        $districtStats = [];
        foreach ($allFollowups as $item) {
            $distName = $item->school?->district?->name ?: 'Lainnya';
            if (!isset($districtStats[$distName])) {
                $districtStats[$distName] = ['total' => 0, 'fixed' => 0];
            }
            $districtStats[$distName]['total']++;
            if ($item->is_schedule_fixed || $item->status === 'completed') {
                $districtStats[$distName]['fixed']++;
            }
        }
        uasort($districtStats, fn($a, $b) => $b['fixed'] <=> $a['fixed'] ?: $b['total'] <=> $a['total']);
        $topDistricts = array_slice($districtStats, 0, 7, true);

        $chartDistrict = [
            'labels' => array_keys($topDistricts),
            'fixed' => array_column($topDistricts, 'fixed'),
            'total' => array_column($topDistricts, 'total'),
        ];

        // 4. Marketing PIC Performance (Bar Chart)
        $marketingStats = [];
        foreach ($allFollowups as $item) {
            $userName = $item->user?->name ?: 'Marketing UBSI';
            if (!isset($marketingStats[$userName])) {
                $marketingStats[$userName] = ['total' => 0, 'fixed' => 0];
            }
            $marketingStats[$userName]['total']++;
            if ($item->is_schedule_fixed || $item->status === 'completed') {
                $marketingStats[$userName]['fixed']++;
            }
        }
        uasort($marketingStats, fn($a, $b) => $b['fixed'] <=> $a['fixed'] ?: $b['total'] <=> $a['total']);

        $chartMarketing = [
            'labels' => array_keys($marketingStats),
            'fixed' => array_column($marketingStats, 'fixed'),
            'total' => array_column($marketingStats, 'total'),
        ];

        // 5. Socialization Method / Format Distribution (Horizontal Bar / Pie)
        $methodStats = [];
        foreach ($allFollowups as $item) {
            $mLabel = $item->socialization_location ?: ($item->visit?->socialization_method ?: 'Sosialisasi di Kelas');
            $methodStats[$mLabel] = ($methodStats[$mLabel] ?? 0) + 1;
        }
        arsort($methodStats);

        $chartMethod = [
            'labels' => array_keys($methodStats),
            'data' => array_values($methodStats),
        ];

        $schoolsQuery = School::orderBy('name');
        if ($activeCampus) {
            $schoolsQuery->where('campus_id', $activeCampus->id);
        }
        $schools = $schoolsQuery->get();

        // Base query for category counts
        $baseCategoryQuery = FollowUp::query()->whereNull('socialization_completed_at');
        if ($activeCampus) {
            $baseCategoryQuery->whereHas('school', fn($sq) => $sq->where('campus_id', $activeCampus->id));
        }
        if ($activePeriod) {
            $baseCategoryQuery->where(function ($q) use ($activePeriod) {
                $q->where('academic_period_id', $activePeriod->id)
                  ->orWhere(function ($sub) use ($activePeriod) {
                      $sub->whereNull('academic_period_id')
                          ->whereHas('visit', fn($vq) => $vq->where('academic_period_id', $activePeriod->id));
                  });
            });
        }

        $countSosialisasi = (clone $baseCategoryQuery)->where(function ($q) {
            $q->where('agenda_category', 'penjadwalan_sosialisasi')
              ->orWhereHas('visit', fn($vq) => $vq->where('agenda_category', 'penjadwalan_sosialisasi'))
              ->orWhere(function ($sub) {
                  $sub->whereNull('agenda_category')
                      ->where(function ($vsub) {
                          $vsub->whereNull('visit_id')
                               ->orWhereHas('visit', fn($vq) => $vq->whereNull('agenda_category'));
                      });
              });
        })->count();

        $countUndangan = (clone $baseCategoryQuery)->where(function ($q) {
            $q->where('agenda_category', 'mengantar_undangan')
              ->orWhereHas('visit', fn($vq) => $vq->where('agenda_category', 'mengantar_undangan'));
        })->count();

        $countLainnya = (clone $baseCategoryQuery)->where(function ($q) {
            $q->where('agenda_category', 'kunjungan_lainnya')
              ->orWhereHas('visit', fn($vq) => $vq->where('agenda_category', 'kunjungan_lainnya'));
        })->count();

        $categoryCounts = [
            'all' => (clone $baseCategoryQuery)->count(),
            'penjadwalan_sosialisasi' => $countSosialisasi,
            'mengantar_undangan' => $countUndangan,
            'kunjungan_lainnya' => $countLainnya,
        ];

        $marketingUsersQuery = User::active()->fieldMarketing();
        if ($activeCampus) {
            $marketingUsersQuery->where(function ($uq) use ($activeCampus) {
                $uq->where('campus_id', $activeCampus->id)->orWhereNull('campus_id');
            });
        }
        $marketingUsers = $marketingUsersQuery->orderBy('name')->get();

        return view('follow-ups.index', compact(
            'viewMode',
            'allFollowups',
            'pending',
            'progress',
            'completed',
            'failed',
            'totalCount',
            'fixedScheduleCount',
            'pendingCount',
            'progressCount',
            'totalEstimatedAudience',
            'conversionRate',
            'chartStatus',
            'chartTimeline',
            'chartDistrict',
            'chartMarketing',
            'chartMethod',
            'schools',
            'marketingUsers',
            'categoryCounts',
            'selectedCategory'
        ));
    }

    /**
     * Show schools whose socialization sessions have actually taken place.
     */
    public function completedSocializations(Request $request): View
    {
        $activeCampus = \App\Services\AppContextService::getActiveCampus();
        $activePeriod = \App\Services\AppContextService::getActivePeriod();

        $query = FollowUp::with(['school.district', 'user', 'visit'])
            ->whereNotNull('socialization_completed_at');

        if ($activeCampus) {
            $query->whereHas('school', function ($sq) use ($activeCampus) {
                $sq->where('campus_id', $activeCampus->id);
            });
        }

        if ($activePeriod) {
            $query->where(function ($q) use ($activePeriod) {
                $q->where('academic_period_id', $activePeriod->id)
                  ->orWhere(function ($sub) use ($activePeriod) {
                      $sub->whereNull('academic_period_id')
                          ->whereHas('visit', fn($vq) => $vq->where('academic_period_id', $activePeriod->id));
                  });
            });
        }

        if ($request->filled('agenda_category')) {
            $cat = $request->input('agenda_category');
            $query->where(function ($q) use ($cat) {
                $q->where('agenda_category', $cat)
                  ->orWhereHas('visit', fn($vq) => $vq->where('agenda_category', $cat));

                if ($cat === SchoolVisit::AGENDA_PENJADWALAN_SOSIALISASI) {
                    $q->orWhere(function ($sub) {
                        $sub->whereNull('agenda_category')
                            ->where(function ($vsub) {
                                $vsub->whereNull('visit_id')
                                     ->orWhereHas('visit', fn($vq) => $vq->whereNull('agenda_category')->orWhere('agenda_category', 'penjadwalan_sosialisasi'));
                            });
                    });
                }
            });
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('school', function ($schoolQuery) use ($search) {
                    $schoolQuery->where('name', 'like', "%{$search}%")
                        ->orWhereHas('district', fn ($districtQuery) => $districtQuery->where('name', 'like', "%{$search}%"));
                })
                    ->orWhere('pic_name', 'like', "%{$search}%")
                    ->orWhere('socialization_completion_notes', 'like', "%{$search}%");
            });
        }

        $completedSocializations = $query
            ->orderByDesc('socialization_completed_at')
            ->paginate(20)
            ->withQueryString();

        $totalCount = FollowUp::whereNotNull('socialization_completed_at')->count();
        $totalAudience = FollowUp::whereNotNull('socialization_completed_at')->sum('target_audience_count');
        $navCounts = $this->getCategoryOutputsCounts($activeCampus, $activePeriod);

        return view('follow-ups.completed', compact(
            'completedSocializations',
            'totalCount',
            'totalAudience',
            'navCounts'
        ));
    }

    /**
     * Show schools that received event invitations and their attendance response status.
     */
    public function completedInvitations(Request $request): View
    {
        $activeCampus = \App\Services\AppContextService::getActiveCampus();
        $activePeriod = \App\Services\AppContextService::getActivePeriod();

        $query = FollowUp::with(['school.district', 'user', 'visit'])
            ->where(function ($q) {
                $q->where('agenda_category', 'like', '%mengantar_undangan%')
                  ->orWhereHas('visit', fn($vq) => $vq->where('agenda_category', 'like', '%mengantar_undangan%'));
            });

        if ($activeCampus) {
            $query->whereHas('school', function ($sq) use ($activeCampus) {
                $sq->where('campus_id', $activeCampus->id);
            });
        }

        if ($activePeriod) {
            $query->where(function ($q) use ($activePeriod) {
                $q->where('academic_period_id', $activePeriod->id)
                  ->orWhere(function ($sub) use ($activePeriod) {
                      $sub->whereNull('academic_period_id')
                          ->whereHas('visit', fn($vq) => $vq->where('academic_period_id', $activePeriod->id));
                  });
            });
        }

        // Base query clone for KPI counts
        $baseQuery = clone $query;
        $totalInvitations = (clone $baseQuery)->count();

        $akanHadirCount = (clone $baseQuery)->where(function($q) {
            $q->where('invitation_attendance_status', 'akan_hadir')
              ->orWhere(function($sub) {
                  $sub->whereNull('invitation_attendance_status')
                      ->whereHas('visit', fn($vq) => $vq->where('invitation_attendance_status', 'akan_hadir'));
              });
        })->count();

        $mintaIjinCount = (clone $baseQuery)->where(function($q) {
            $q->where('invitation_attendance_status', 'minta_ijin_dulu')
              ->orWhere(function($sub) {
                  $sub->whereNull('invitation_attendance_status')
                      ->whereHas('visit', fn($vq) => $vq->where('invitation_attendance_status', 'minta_ijin_dulu'));
              });
        })->count();

        $tidakHadirCount = (clone $baseQuery)->where(function($q) {
            $q->where('invitation_attendance_status', 'tidak_bisa_hadir')
              ->orWhere(function($sub) {
                  $sub->whereNull('invitation_attendance_status')
                      ->whereHas('visit', fn($vq) => $vq->where('invitation_attendance_status', 'tidak_bisa_hadir'));
              });
        })->count();

        $menungguResponCount = (clone $baseQuery)->where(function($q) {
            $q->where(function($sub) {
                $sub->whereNull('invitation_attendance_status')
                    ->orWhere('invitation_attendance_status', 'pending');
            })->where(function($sub) {
                $sub->whereDoesntHave('visit')
                    ->orWhereHas('visit', fn($vq) => $vq->whereNull('invitation_attendance_status'));
            });
        })->count();

        // Filter by attendance status
        if ($request->filled('attendance_status')) {
            $att = $request->input('attendance_status');
            if ($att === 'menunggu_respon') {
                $query->where(function($q) {
                    $q->where(function($sub) {
                        $sub->whereNull('invitation_attendance_status')
                            ->orWhere('invitation_attendance_status', 'pending');
                    })->where(function($sub) {
                        $sub->whereDoesntHave('visit')
                            ->orWhereHas('visit', fn($vq) => $vq->whereNull('invitation_attendance_status'));
                    });
                });
            } else {
                $query->where(function($q) use ($att) {
                    $q->where('invitation_attendance_status', $att)
                      ->orWhereHas('visit', fn($vq) => $vq->where('invitation_attendance_status', $att));
                });
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('school', function ($schoolQuery) use ($search) {
                    $schoolQuery->where('name', 'like', "%{$search}%")
                        ->orWhereHas('district', fn ($districtQuery) => $districtQuery->where('name', 'like', "%{$search}%"));
                })
                    ->orWhere('pic_name', 'like', "%{$search}%")
                    ->orWhere('pic_phone', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $invitations = $query->orderByDesc('id')->paginate(20)->withQueryString();
        $navCounts = $this->getCategoryOutputsCounts($activeCampus, $activePeriod);

        return view('follow-ups.completed-invitations', compact(
            'invitations',
            'totalInvitations',
            'akanHadirCount',
            'mintaIjinCount',
            'tidakHadirCount',
            'menungguResponCount',
            'navCounts'
        ));
    }

    /**
     * Show general visits & audiensi dinas results.
     */
    public function completedOtherVisits(Request $request): View
    {
        $activeCampus = \App\Services\AppContextService::getActiveCampus();
        $activePeriod = \App\Services\AppContextService::getActivePeriod();

        $query = FollowUp::with(['school.district', 'user', 'visit'])
            ->where(function ($q) {
                $q->where('agenda_category', 'like', '%kunjungan_lainnya%')
                  ->orWhereHas('visit', fn($vq) => $vq->where('agenda_category', 'like', '%kunjungan_lainnya%'));
            });

        if ($activeCampus) {
            $query->whereHas('school', function ($sq) use ($activeCampus) {
                $sq->where('campus_id', $activeCampus->id);
            });
        }

        if ($activePeriod) {
            $query->where(function ($q) use ($activePeriod) {
                $q->where('academic_period_id', $activePeriod->id)
                  ->orWhere(function ($sub) use ($activePeriod) {
                      $sub->whereNull('academic_period_id')
                          ->whereHas('visit', fn($vq) => $vq->where('academic_period_id', $activePeriod->id));
                  });
            });
        }

        $baseQuery = clone $query;
        $totalVisits = (clone $baseQuery)->count();
        $completedCount = (clone $baseQuery)->where('status', 'completed')->count();
        $progressCount = (clone $baseQuery)->where('status', 'progress')->count();
        $pendingCount = (clone $baseQuery)->where('status', 'pending')->count();
        $hasDocCount = (clone $baseQuery)->where(function($q) {
            $q->whereNotNull('documentation_file')
              ->orWhereNotNull('google_drive_file_url')
              ->orWhereHas('visit', fn($vq) => $vq->whereNotNull('photo')->orWhereNotNull('google_drive_file_url'));
        })->count();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('school', function ($schoolQuery) use ($search) {
                    $schoolQuery->where('name', 'like', "%{$search}%")
                        ->orWhereHas('district', fn ($districtQuery) => $districtQuery->where('name', 'like', "%{$search}%"));
                })
                    ->orWhere('pic_name', 'like', "%{$search}%")
                    ->orWhere('pic_phone', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $otherVisits = $query->orderByDesc('id')->paginate(20)->withQueryString();
        $navCounts = $this->getCategoryOutputsCounts($activeCampus, $activePeriod);

        return view('follow-ups.completed-other-visits', compact(
            'otherVisits',
            'totalVisits',
            'completedCount',
            'progressCount',
            'pendingCount',
            'hasDocCount',
            'navCounts'
        ));
    }

    /**
     * Get counts for category output switcher tabs.
     */
    private function getCategoryOutputsCounts(?\App\Models\Campus $activeCampus, ?\App\Models\AcademicPeriod $activePeriod): array
    {
        $baseQuery = function($catFilter) use ($activeCampus, $activePeriod) {
            $q = FollowUp::query();
            if ($activeCampus) {
                $q->whereHas('school', fn($sq) => $sq->where('campus_id', $activeCampus->id));
            }
            if ($activePeriod) {
                $q->where(function ($sq) use ($activePeriod) {
                    $sq->where('academic_period_id', $activePeriod->id)
                      ->orWhere(function ($sub) use ($activePeriod) {
                          $sub->whereNull('academic_period_id')
                              ->whereHas('visit', fn($vq) => $vq->where('academic_period_id', $activePeriod->id));
                      });
                });
            }
            $catFilter($q);
            return $q->count();
        };

        $socializationsCount = $baseQuery(fn($q) => $q->whereNotNull('socialization_completed_at'));
        $invitationsCount = $baseQuery(fn($q) => $q->where(function($sub) {
            $sub->where('agenda_category', 'like', '%mengantar_undangan%')
                ->orWhereHas('visit', fn($vq) => $vq->where('agenda_category', 'like', '%mengantar_undangan%'));
        }));
        $otherVisitsCount = $baseQuery(fn($q) => $q->where(function($sub) {
            $sub->where('agenda_category', 'like', '%kunjungan_lainnya%')
                ->orWhereHas('visit', fn($vq) => $vq->where('agenda_category', 'like', '%kunjungan_lainnya%'));
        }));

        return [
            'socializations' => $socializationsCount,
            'invitations' => $invitationsCount,
            'otherVisits' => $otherVisitsCount,
        ];
    }

    /**
     * Record that a fixed socialization schedule has actually taken place.
     */
    public function confirmSocialization(Request $request, FollowUp $followUp): RedirectResponse
    {
        if (!$followUp->is_schedule_fixed && $followUp->status !== FollowUp::STATUS_COMPLETED) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Konfirmasi hanya tersedia untuk jadwal sosialisasi yang sudah FIX.',
            ]);
        }

        $validated = $request->validate([
            'socialization_completed_at' => ['required', 'date', 'before_or_equal:today'],
            'socialization_completion_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $followUp->update($validated);

        if ($followUp->visit && !empty($validated['invitation_attendance_status'])) {
            $followUp->visit->update([
                'invitation_attendance_status' => $validated['invitation_attendance_status'],
            ]);
        }

        // Auto copy to MarketingActivity & SchoolActivity
        self::syncFollowUpToMarketingActivity($followUp->fresh());

        return redirect()->route('socializations.completed.index')->with('toast', [
            'type' => 'success',
            'message' => "Sosialisasi di {$followUp->school?->name} berhasil dicatat sebagai kegiatan terlaksana dan otomatis tercopy ke Aktivitas Sosialisasi!",
        ]);
    }

    /**
     * Remove an accidental completion confirmation and return the schedule to the FIX list.
     */
    public function reopenSocialization(FollowUp $followUp): RedirectResponse
    {
        // Remove auto-copied activity
        self::removeFollowUpFromMarketingActivity($followUp);

        $followUp->update([
            'socialization_completed_at' => null,
            'socialization_completion_notes' => null,
        ]);

        return redirect()->route('socializations.completed.index')->with('toast', [
            'type' => 'success',
            'message' => "Konfirmasi sosialisasi untuk {$followUp->school?->name} dibatalkan. Jadwal kembali ke daftar FIX.",
        ]);
    }

    /**
     * Upload documentation for completed socialization and sync with Google Drive & SchoolVisit.
     */
    public function uploadDocumentation(Request $request, FollowUp $followUp): RedirectResponse
    {
        $validated = $request->validate([
            'documentation' => [
                'required',
                'file',
                'max:51200', // 50MB
                'mimes:jpg,jpeg,png,webp,pdf,rar,zip,doc,docx,7z',
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf', 'rar', 'zip', 'doc', 'docx', '7z'];
                    if (!in_array($ext, $allowed)) {
                        $fail('Format berkas harus berupa Gambar (JPG/PNG), Dokumen (PDF/DOCX), atau Arsip (RAR/ZIP).');
                    }
                },
            ],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $file = $request->file('documentation');
        $school = $followUp->school;
        $schoolName = $school ? $school->name : 'Sekolah';
        $completedDate = $followUp->socialization_completed_at ?: $followUp->fixed_date;
        $date = $completedDate ? Carbon::parse($completedDate)->format('Y-m-d') : now()->format('Y-m-d');

        // Upload to Google Drive & save locally (terpisah sesuai folder kampus Karawang / Cikampek)
        $gdriveResult = \App\Services\GoogleDriveService::uploadDocumentation($file, $schoolName, $date, $school?->campus_id ?: $followUp->campus_id);

        // Delete old documentation file if existed
        if ($followUp->documentation_file) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($followUp->documentation_file);
        }

        $updateData = [
            'documentation_file' => $gdriveResult['stored_path'],
            'google_drive_folder_url' => $gdriveResult['drive_folder_url'],
            'google_drive_file_url' => $gdriveResult['drive_file_url'],
        ];

        if (!empty($validated['notes'])) {
            $updateData['socialization_completion_notes'] = $validated['notes'];
        }

        $followUp->update($updateData);

        // Sync with linked SchoolVisit
        if ($followUp->visit) {
            $followUp->visit->update([
                'photo' => $gdriveResult['stored_path'],
                'google_drive_folder_url' => $gdriveResult['drive_folder_url'],
                'google_drive_file_url' => $gdriveResult['drive_file_url'],
            ]);
        }

        // Auto sync documentation to MarketingActivity
        self::syncFollowUpToMarketingActivity($followUp->fresh());

        return redirect()->route('socializations.completed.index')->with('toast', [
            'type' => 'success',
            'message' => "Dokumentasi kegiatan untuk {$schoolName} berhasil diunggah ke Google Drive dan disinkronkan dengan Data Kunjungan serta Aktivitas Sosialisasi!",
        ]);
    }

    /**
     * Upload documentation for invitation delivery and sync with Google Drive & SchoolVisit.
     */
    public function uploadInvitationDocumentation(Request $request, FollowUp $followUp): RedirectResponse
    {
        $validated = $request->validate([
            'documentation' => [
                'required',
                'file',
                'max:51200', // 50MB
                'mimes:jpg,jpeg,png,webp,pdf,rar,zip,doc,docx,7z',
            ],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $file = $request->file('documentation');
        $school = $followUp->school;
        $schoolName = $school ? $school->name : 'Sekolah';
        $visitDate = $followUp->visit?->visit_date ?: ($followUp->fixed_date ?: now());
        $date = Carbon::parse($visitDate)->format('Y-m-d');

        $gdriveResult = \App\Services\GoogleDriveService::uploadDocumentation($file, $schoolName, $date, $school?->campus_id ?: $followUp->campus_id);

        if ($followUp->documentation_file) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($followUp->documentation_file);
        }

        $updateData = [
            'documentation_file' => $gdriveResult['stored_path'],
            'google_drive_folder_url' => $gdriveResult['drive_folder_url'],
            'google_drive_file_url' => $gdriveResult['drive_file_url'],
        ];

        if (!empty($validated['notes'])) {
            $updateData['notes'] = $validated['notes'];
        }

        $followUp->update($updateData);

        if ($followUp->visit) {
            $visitUpdate = [
                'photo' => $gdriveResult['stored_path'],
                'google_drive_folder_url' => $gdriveResult['drive_folder_url'],
                'google_drive_file_url' => $gdriveResult['drive_file_url'],
            ];
            if (!empty($validated['notes'])) {
                $visitUpdate['notes'] = $validated['notes'];
            }
            $followUp->visit->update($visitUpdate);
        }

        return redirect()->route('invitations.completed.index')->with('toast', [
            'type' => 'success',
            'message' => "Bukti tanda terima penyerahan undangan untuk {$schoolName} berhasil diunggah ke Google Drive dan disinkronkan!",
        ]);
    }

    /**
     * Upload documentation for general visit / audiensi dinas.
     */
    public function uploadOtherVisitDocumentation(Request $request, FollowUp $followUp): RedirectResponse
    {
        $validated = $request->validate([
            'documentation' => [
                'required',
                'file',
                'max:51200', // 50MB
                'mimes:jpg,jpeg,png,webp,pdf,rar,zip,doc,docx,7z',
            ],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $file = $request->file('documentation');
        $school = $followUp->school;
        $schoolName = $school ? $school->name : 'Sekolah';
        $visitDate = $followUp->visit?->visit_date ?: ($followUp->fixed_date ?: now());
        $date = Carbon::parse($visitDate)->format('Y-m-d');

        $gdriveResult = \App\Services\GoogleDriveService::uploadDocumentation($file, $schoolName, $date, $school?->campus_id ?: $followUp->campus_id);

        if ($followUp->documentation_file) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($followUp->documentation_file);
        }

        $updateData = [
            'documentation_file' => $gdriveResult['stored_path'],
            'google_drive_folder_url' => $gdriveResult['drive_folder_url'],
            'google_drive_file_url' => $gdriveResult['drive_file_url'],
        ];

        if (!empty($validated['notes'])) {
            $updateData['notes'] = $validated['notes'];
        }

        $followUp->update($updateData);

        if ($followUp->visit) {
            $visitUpdate = [
                'photo' => $gdriveResult['stored_path'],
                'google_drive_folder_url' => $gdriveResult['drive_folder_url'],
                'google_drive_file_url' => $gdriveResult['drive_file_url'],
            ];
            if (!empty($validated['notes'])) {
                $visitUpdate['notes'] = $validated['notes'];
            }
            $followUp->visit->update($visitUpdate);
        }

        return redirect()->route('other-visits.completed.index')->with('toast', [
            'type' => 'success',
            'message' => "Dokumentasi kunjungan dinas untuk {$schoolName} berhasil diunggah ke Google Drive dan disinkronkan!",
        ]);
    }

    /**
     * Quick update attendance status from invitations page.
     */
    public function quickUpdateInvitationStatus(Request $request, FollowUp $followUp): RedirectResponse
    {
        $validated = $request->validate([
            'event_name' => ['nullable', 'string', 'max:255'],
            'invitation_attendance_status' => ['required', 'in:akan_hadir,minta_ijin_dulu,tidak_bisa_hadir,menunggu_respon'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $statusValue = $validated['invitation_attendance_status'] === 'menunggu_respon' ? null : $validated['invitation_attendance_status'];
        
        $updateData = ['invitation_attendance_status' => $statusValue];
        if (!empty($validated['event_name'])) {
            $updateData['event_name'] = $validated['event_name'];
        }
        if ($validated['invitation_attendance_status'] === 'akan_hadir') {
            $updateData['status'] = 'completed';
        } elseif ($validated['invitation_attendance_status'] === 'minta_ijin_dulu') {
            $updateData['status'] = 'progress';
        } elseif ($validated['invitation_attendance_status'] === 'tidak_bisa_hadir') {
            $updateData['status'] = 'failed';
        } elseif ($validated['invitation_attendance_status'] === 'menunggu_respon') {
            $updateData['status'] = 'pending';
        }

        if (!empty($validated['notes'])) {
            $updateData['notes'] = $validated['notes'];
        }

        $followUp->update($updateData);

        if ($followUp->visit) {
            $visitUpdate = ['invitation_attendance_status' => $statusValue];
            if (!empty($validated['event_name'])) {
                $visitUpdate['event_name'] = $validated['event_name'];
            }
            if (!empty($validated['notes'])) {
                $visitUpdate['notes'] = $validated['notes'];
            }
            $followUp->visit->update($visitUpdate);
        }

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'message' => "Status konfirmasi kehadiran untuk {$followUp->school?->name} berhasil diperbarui.",
        ]);
    }

    /**
     * Quick update status from other visits page.
     */
    public function quickUpdateOtherVisitStatus(Request $request, FollowUp $followUp): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,progress,completed,failed'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $updateData = ['status' => $validated['status']];
        if (!empty($validated['notes'])) {
            $updateData['notes'] = $validated['notes'];
        }

        $followUp->update($updateData);

        if ($followUp->visit && !empty($validated['notes'])) {
            $followUp->visit->update(['notes' => $validated['notes']]);
        }

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'message' => "Status hasil kunjungan dinas {$followUp->school?->name} berhasil diperbarui.",
        ]);
    }

    /**
     * Automatically sync a completed school socialization into MarketingActivity & SchoolActivity.
     */
    public static function syncFollowUpToMarketingActivity(FollowUp $followUp): ?MarketingActivity
    {
        if (empty($followUp->socialization_completed_at) || empty($followUp->school_id)) {
            return null;
        }

        $school = $followUp->school;
        $schoolName = $school ? $school->name : 'Sekolah';
        $completedDate = Carbon::parse($followUp->socialization_completed_at)->format('Y-m-d');
        $activityName = "Sosialisasi PMB & Beasiswa - {$schoolName}";

        // Find existing MarketingActivity linked through SchoolActivity or by name and date
        $existingSchoolActivity = SchoolActivity::where('school_id', $followUp->school_id)
            ->where('date', $completedDate)
            ->first();

        $activity = null;
        if ($existingSchoolActivity && $existingSchoolActivity->activity) {
            $activity = $existingSchoolActivity->activity;
        } else {
            $activity = MarketingActivity::where('name', $activityName)
                ->where('start_date', $completedDate)
                ->first();
        }

        $description = $followUp->socialization_completion_notes 
            ?: "Sosialisasi kampus Universitas BSI (UBSI) telah terlaksana untuk siswa kelas 12 di {$schoolName}.";

        $docPath = $followUp->documentation_file ?: $followUp->visit?->photo;
        $docName = $docPath ? basename($docPath) : null;
        $driveFolderUrl = $followUp->google_drive_folder_url 
            ?: ($followUp->visit?->google_drive_folder_url 
                ?: \App\Services\GoogleDriveService::buildFolderUrlForSchool($schoolName, $completedDate, $school?->campus_id ?: $followUp->campus_id));
        $driveFileUrl = $followUp->google_drive_file_url 
            ?: ($followUp->visit?->google_drive_file_url ?: null);

        if ($activity) {
            $activity->update([
                'name' => $activityName,
                'activity_type' => 'Sosialisasi PMB',
                'start_date' => $completedDate,
                'end_date' => $completedDate,
                'description' => $description,
                'document_path' => $docPath ?: $activity->document_path,
                'document_name' => $docName ?: $activity->document_name,
                'google_drive_folder_url' => $driveFolderUrl ?: $activity->google_drive_folder_url,
                'google_drive_file_url' => $driveFileUrl ?: $activity->google_drive_file_url,
            ]);
        } else {
            $activity = MarketingActivity::create([
                'name' => $activityName,
                'activity_type' => 'Sosialisasi PMB',
                'start_date' => $completedDate,
                'end_date' => $completedDate,
                'description' => $description,
                'document_path' => $docPath,
                'document_name' => $docName,
                'google_drive_folder_url' => $driveFolderUrl,
                'google_drive_file_url' => $driveFileUrl,
            ]);
        }

        // Upsert SchoolActivity
        SchoolActivity::updateOrCreate(
            [
                'school_id' => $followUp->school_id,
                'activity_id' => $activity->id,
            ],
            [
                'date' => $completedDate,
                'participants' => (int) ($followUp->target_audience_count ?? ($school?->grade_12_count ?? 0)),
                'result' => $followUp->result ?? 'Terlaksana (Sosialisasi FIX)',
                'notes' => $followUp->socialization_completion_notes,
            ]
        );

        return $activity;
    }

    /**
     * Remove auto-copied MarketingActivity & SchoolActivity when a completed socialization is reopened.
     */
    public static function removeFollowUpFromMarketingActivity(FollowUp $followUp): void
    {
        if (empty($followUp->school_id)) {
            return;
        }

        $schoolName = $followUp->school?->name ?? 'Sekolah';
        $activityName = "Sosialisasi PMB & Beasiswa - {$schoolName}";

        $schoolActivities = SchoolActivity::where('school_id', $followUp->school_id)->get();
        foreach ($schoolActivities as $sa) {
            $act = $sa->activity;
            $sa->delete();
            if ($act && ($act->name === $activityName || str_starts_with($act->name, 'Sosialisasi PMB'))) {
                if ($act->schoolActivities()->count() === 0) {
                    $act->delete();
                }
            }
        }
    }

    /**
     * Export Follow-up and Fixed Socialization Schedules to Excel.
     */
    public function export(Request $request, FollowUpExportService $exportService): StreamedResponse
    {
        $user = auth()->user();
        if ($user && !\App\Services\AppContextService::canSwitchCampus($user)) {
            $campusId = $user->campus_id ?? \App\Services\AppContextService::getActiveCampus()?->id;
        } else {
            $campusId = $request->filled('campus_id') && $request->campus_id !== 'ALL'
                ? $request->campus_id
                : \App\Services\AppContextService::getActiveCampus()?->id;
        }

        $activeCampus = ($campusId && $campusId !== 'ALL') ? \App\Models\Campus::find($campusId) : null;
        $activePeriod = \App\Services\AppContextService::getActivePeriod();

        $query = FollowUp::with([
            'school.district', 
            'school.primaryContact', 
            'user', 
            'visit'
        ]);

        if ($activeCampus) {
            $query->whereHas('school', function ($sq) use ($activeCampus) {
                $sq->where('campus_id', $activeCampus->id);
            });
        }

        if ($activePeriod) {
            $query->where(function ($q) use ($activePeriod) {
                $q->where('academic_period_id', $activePeriod->id)
                  ->orWhere(function ($sub) use ($activePeriod) {
                      $sub->whereNull('academic_period_id')
                          ->whereHas('visit', fn($vq) => $vq->where('academic_period_id', $activePeriod->id));
                  });
            });
        }

        if ($request->filled('agenda_category')) {
            $cat = $request->input('agenda_category');
            $query->where(function ($q) use ($cat) {
                $q->where('agenda_category', $cat)
                  ->orWhereHas('visit', fn($vq) => $vq->where('agenda_category', $cat));

                if ($cat === SchoolVisit::AGENDA_PENJADWALAN_SOSIALISASI) {
                    $q->orWhere(function ($sub) {
                        $sub->whereNull('agenda_category')
                            ->where(function ($vsub) {
                                $vsub->whereNull('visit_id')
                                     ->orWhereHas('visit', fn($vq) => $vq->whereNull('agenda_category')->orWhere('agenda_category', 'penjadwalan_sosialisasi'));
                            });
                    });
                }
            });
        }

        $filterInfo = [
            'status_label' => 'Semua Data Follow Up',
            'marketing_name' => 'Semua Staf Marketing',
        ];

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
            $user = User::find($request->user_id);
            if ($user) {
                $filterInfo['marketing_name'] = $user->name;
            }
        }

        if ($request->filled('method')) {
            $query->where('method', $request->input('method'));
        }

        if ($request->get('type') === 'fixed' || $request->schedule_status === 'fixed') {
            $query->where(function ($q) {
                $q->where('is_schedule_fixed', true)
                  ->orWhere('status', 'completed');
            });
            $filterInfo['status_label'] = 'Hanya Jadwal Sosialisasi FIX';
        } elseif ($request->schedule_status === 'unfixed') {
            $query->where('is_schedule_fixed', false)
                  ->where('status', '!=', 'completed');
            $filterInfo['status_label'] = 'Belum FIX (Menunggu/Koordinasi)';
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('school', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                })
                ->orWhere('pic_name', 'like', "%{$search}%")
                ->orWhere('pic_phone', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $followUps = $query->orderBy('followup_date', 'asc')->get();

        return $exportService->exportExcel($followUps, $filterInfo);
    }

    /**
     * Trigger manual sync of visits to follow-ups.
     */
    public function syncFromVisits(FollowUpSyncService $syncService): RedirectResponse
    {
        $count = $syncService->syncAllVisits();

        return redirect()->route('follow-ups.index')
            ->with('toast', [
                'type' => 'success',
                'message' => "Sinkronisasi berhasil! {$count} data kunjungan baru dimasukkan ke antrean follow-up jadwal sosialisasi.",
            ]);
    }

    /**
     * Store a new Follow-up record manually.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'user_id' => ['required', 'exists:users,id'],
            'followup_date' => ['required', 'date'],
            'method' => ['required', 'string', 'max:50'],
            'notes' => ['required', 'string'],
            'pic_name' => ['nullable', 'string', 'max:150'],
            'pic_phone' => ['nullable', 'string', 'max:50'],
            'fixed_date' => ['nullable', 'date'],
            'fixed_time' => ['nullable', 'string', 'max:50'],
            'socialization_location' => ['nullable', 'string', 'max:150'],
            'result' => ['nullable', 'string'],
            'status' => ['required', 'in:pending,progress,completed,failed'],
            'agenda_category' => ['nullable', 'string', 'in:penjadwalan_sosialisasi,mengantar_undangan,kunjungan_lainnya'],
            'invitation_attendance_status' => ['nullable', 'string', 'in:akan_hadir,minta_ijin_dulu,tidak_bisa_hadir'],
        ]);

        $validated['agenda_category'] = $validated['agenda_category'] ?? 'penjadwalan_sosialisasi';
        $validated['description'] = $validated['notes'];
        $validated['is_schedule_fixed'] = ($validated['status'] === 'completed');

        $school = School::find($validated['school_id']);
        $activeCampus = \App\Services\AppContextService::getActiveCampus();
        $activePeriod = \App\Services\AppContextService::getActivePeriod();
        $validated['campus_id'] = $school?->campus_id ?: ($activeCampus?->id ?? 1);
        $validated['academic_period_id'] = $activePeriod?->id;

        FollowUp::create($validated);

        return redirect()->route('follow-ups.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Data follow up sekolah berhasil ditambahkan.',
            ]);
    }

    /**
     * Update follow-up schedule and lock in fixed socialization dates.
     */
    public function updateSchedule(Request $request, FollowUp $followUp): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,progress,completed,failed'],
            'fixed_date' => ['nullable', 'date'],
            'fixed_time' => ['nullable', 'string', 'max:50'],
            'pic_name' => ['nullable', 'string', 'max:150'],
            'pic_phone' => ['nullable', 'string', 'max:50'],
            'socialization_location' => ['nullable', 'string', 'max:150'],
            'method' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'result' => ['nullable', 'string'],
            'is_schedule_fixed' => ['nullable', 'boolean'],
            'target_audience_count' => ['nullable', 'integer', 'min:0'],
            'invitation_attendance_status' => ['nullable', 'string', 'in:akan_hadir,minta_ijin_dulu,tidak_bisa_hadir,belum_dikonfirmasi'],
        ]);

        $agendaCat = $followUp->agenda_category ?: ($followUp->visit?->agenda_category ?? 'penjadwalan_sosialisasi');

        if ($request->has('invitation_attendance_status')) {
            $att = $request->input('invitation_attendance_status');
            $validated['invitation_attendance_status'] = ($att === 'belum_dikonfirmasi') ? null : $att;

            if ($agendaCat === 'mengantar_undangan') {
                if ($att === 'akan_hadir') {
                    $validated['status'] = 'completed';
                } elseif ($att === 'minta_ijin_dulu') {
                    $validated['status'] = 'progress';
                } elseif ($att === 'tidak_bisa_hadir') {
                    $validated['status'] = 'failed';
                } elseif ($att === 'belum_dikonfirmasi') {
                    $validated['status'] = 'pending';
                }
            }
        }

        $isFixed = (!empty($validated['is_schedule_fixed']) || ($validated['status'] === 'completed' && $agendaCat === 'penjadwalan_sosialisasi'));
        $validated['is_schedule_fixed'] = ($agendaCat === 'penjadwalan_sosialisasi') ? $isFixed : false;

        if ($isFixed && $agendaCat === 'penjadwalan_sosialisasi') {
            $validated['status'] = 'completed';
        }

        if (!empty($validated['notes'])) {
            $validated['description'] = $validated['notes'];
        }

        $followUp->update($validated);

        if ($followUp->visit) {
            $visitUpdates = [];
            if ($request->has('invitation_attendance_status')) {
                $visitUpdates['invitation_attendance_status'] = $validated['invitation_attendance_status'];
            }
            if (!empty($validated['pic_name'])) {
                $visitUpdates['recipient_name'] = $validated['pic_name'];
            }
            if (!empty($validated['pic_phone'])) {
                $visitUpdates['recipient_phone'] = $validated['pic_phone'];
            }
            if (!empty($visitUpdates)) {
                $followUp->visit->update($visitUpdates);
            }
        }

        // Jika jadwal sosialisasi berhasil di-FIX-kan, kirim notifikasi WhatsApp ke tim marketing
        if ($isFixed && $agendaCat === 'penjadwalan_sosialisasi' && !empty($followUp->fixed_date)) {
            try {
                app(\App\Services\WhatsAppNotificationService::class)->notifySocializationH5($followUp);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Gagal kirim notifikasi WA jadwal sosialisasi: ' . $e->getMessage());
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data follow up berhasil diperbarui!',
                'follow_up' => $followUp->fresh(['school', 'user', 'visit']),
            ]);
        }

        $msg = $isFixed 
            ? "Jadwal sosialisasi untuk {$followUp->school->name} BERHASIL DI-LOCK / FIX!" 
            : "Follow up untuk {$followUp->school->name} berhasil diperbarui.";

        $redirectParams = [];
        if ($request->filled('agenda_category')) {
            $redirectParams['agenda_category'] = $request->input('agenda_category');
        } elseif ($agendaCat) {
            $redirectParams['agenda_category'] = $agendaCat;
        }

        return redirect()->route('follow-ups.index', $redirectParams)
            ->with('toast', [
                'type' => 'success',
                'message' => $msg,
            ]);
    }

    /**
     * Update follow-up status (e.g. from Kanban drag or quick button).
     */
    public function updateStatus(Request $request, FollowUp $followUp): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,progress,completed,failed'],
        ]);

        $cat = $followUp->agenda_category ?: ($followUp->visit?->agenda_category ?? 'penjadwalan_sosialisasi');
        $isFixed = ($validated['status'] === 'completed' && $cat === 'penjadwalan_sosialisasi');

        $data = [
            'status' => $validated['status'],
            'is_schedule_fixed' => $isFixed,
        ];

        if ($cat === 'mengantar_undangan') {
            if ($validated['status'] === 'completed') {
                $data['invitation_attendance_status'] = 'akan_hadir';
            } elseif ($validated['status'] === 'progress') {
                $data['invitation_attendance_status'] = 'minta_ijin_dulu';
            } elseif ($validated['status'] === 'failed') {
                $data['invitation_attendance_status'] = 'tidak_bisa_hadir';
            } elseif ($validated['status'] === 'pending') {
                $data['invitation_attendance_status'] = null;
            }
        }

        $followUp->update($data);

        if ($followUp->visit && array_key_exists('invitation_attendance_status', $data)) {
            $followUp->visit->update(['invitation_attendance_status' => $data['invitation_attendance_status']]);
        }

        $redirectParams = [];
        if ($request->filled('agenda_category')) {
            $redirectParams['agenda_category'] = $request->input('agenda_category');
        } elseif ($cat) {
            $redirectParams['agenda_category'] = $cat;
        }

        return redirect()->route('follow-ups.index', $redirectParams)
            ->with('toast', [
                'type' => 'success',
                'message' => "Status follow up {$followUp->school->name} berhasil diperbarui.",
            ]);
    }

    /**
     * Delete a follow-up history record for a school.
     */
    public function destroy(FollowUp $followUp): RedirectResponse
    {
        $user = auth()->user();
        if (!$user || (!$user->isAdmin() && !$user->isPimpinan() && !$user->isKoordinator() && !$user->isKoordinatorCs())) {
            abort(403, 'Akses ditolak. Penghapusan histori follow up hanya dapat dilakukan oleh Admin, Pimpinan, dan Koordinator.');
        }

        $schoolName = $followUp->school?->name ?? 'Sekolah';
        $followUp->delete();

        \App\Services\ActivityLogService::log(
            'DELETE',
            'follow_up',
            $followUp->id,
            "Menghapus histori follow up sekolah: {$schoolName}",
            ['school' => $schoolName, 'status' => $followUp->status]
        );

        return redirect()->route('follow-ups.index')
            ->with('toast', [
                'type' => 'success',
                'message' => "Histori follow up untuk {$schoolName} berhasil dihapus.",
            ]);
    }
}
