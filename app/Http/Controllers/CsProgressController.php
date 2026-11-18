<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\Prospect;
use App\Models\StudentFollowUp;
use App\Models\StudentQuestionnaire;
use App\Models\User;
use App\Services\AppContextService;
use App\Services\ProspectSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsProgressController extends Controller
{
    /**
     * Halaman Monitoring & Kontrol Progres Tim Customer Service.
     * Hak Akses: Admin, Pimpinan, Koordinator Marketing, dan Koordinator Customer Service.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        if (!$user || !$user->canMonitorCsProgress()) {
            abort(403, 'Akses ditolak. Menu Kontrol Progres Customer Service hanya dapat diakses oleh Administrator, Pimpinan, Koordinator Marketing, dan Koordinator Customer Service.');
        }

        ProspectSyncService::syncMissingDaftarAplikasi();

        $data = $this->buildProgressData($request);

        return view('cs-progress.index', $data);
    }

    /**
     * Ekspor Laporan Kontrol Progres Customer Service ke Excel (.xlsx).
     */
    public function export(Request $request): StreamedResponse
    {
        $user = auth()->user();
        if (!$user || !$user->canMonitorCsProgress()) {
            abort(403, 'Akses ditolak.');
        }

        $data = $this->buildProgressData($request);
        $officers = $data['officers'];
        $activeCampus = $data['activeCampus'];
        $activePeriod = $data['activePeriod'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Kontrol Progres CS');

        $campusLabel = $activeCampus ? $activeCampus->name : 'Semua Kampus';
        $periodLabel = $activePeriod ? $activePeriod->name : 'Semua Periode';

        $sheet->setCellValue('A1', 'LAPORAN KONTROL PROGRES & KINERJA TIM CUSTOMER SERVICE');
        $sheet->mergeCells('A1:N1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', "Kampus: {$campusLabel} | Periode: {$periodLabel} | Dicetak: " . now()->format('d/m/Y H:i') . ' WIB');
        $sheet->mergeCells('A2:N2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = [
            'No',
            'Nama Petugas',
            'Jabatan / Role',
            'Total Ploting',
            'Belum Dihubungi',
            'Sudah Dihubungi',
            'Progres (%)',
            'No Respon',
            'Respon',
            'Berminat',
            'Daftar Aplikasi',
            'Konversi (%)',
            'Total Interaksi',
            'Aktivitas Terakhir',
        ];

        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue("{$col}4", $h);
            $col++;
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0B023E']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ];
        $sheet->getStyle('A4:N4')->applyFromArray($headerStyle);
        $sheet->getRowDimension(4)->setRowHeight(24);

        $rowNum = 5;
        foreach ($officers as $idx => $item) {
            $sheet->setCellValue("A{$rowNum}", $idx + 1);
            $sheet->setCellValue("B{$rowNum}", $item['user']->name);
            $sheet->setCellValue("C{$rowNum}", $item['user']->role?->name ?? '-');
            $sheet->setCellValue("D{$rowNum}", $item['total_assigned']);
            $sheet->setCellValue("E{$rowNum}", $item['belum_dihubungi']);
            $sheet->setCellValue("F{$rowNum}", $item['contacted']);
            $sheet->setCellValue("G{$rowNum}", $item['progress_rate'] . '%');
            $sheet->setCellValue("H{$rowNum}", $item['no_respon']);
            $sheet->setCellValue("I{$rowNum}", $item['respon']);
            $sheet->setCellValue("J{$rowNum}", $item['berminat']);
            $sheet->setCellValue("K{$rowNum}", $item['daftar_aplikasi']);
            $sheet->setCellValue("L{$rowNum}", $item['conversion_rate'] . '%');
            $sheet->setCellValue("M{$rowNum}", $item['total_interactions']);
            $sheet->setCellValue("N{$rowNum}", $item['last_activity_at'] ? $item['last_activity_at']->format('d/m/Y H:i') : 'Belum ada aktivitas');

            $sheet->getStyle("A{$rowNum}:N{$rowNum}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
            ]);
            $sheet->getStyle("A{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$rowNum}:M{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $rowNum++;
        }

        foreach (range('A', 'N') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $fileName = 'Laporan-Kontrol-Progres-CS-' . now()->format('Y-m-d_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Kumpulkan seluruh data metrik progres petugas CS & aktivitas terbaru.
     */
    protected function buildProgressData(Request $request): array
    {
        $user = auth()->user();

        // Isolasi kampus ketat
        if ($user && !AppContextService::canSwitchCampus($user)) {
            $campusId = $user->campus_id ?? AppContextService::getActiveCampus()?->id;
        } else {
            $campusId = $request->filled('campus_id') && $request->campus_id !== 'ALL'
                ? $request->campus_id
                : AppContextService::getActiveCampus()?->id;
        }

        $activeCampus = ($campusId && $campusId !== 'ALL') ? Campus::find($campusId) : null;
        $activePeriod = AppContextService::getActivePeriod();

        $roleFilter = $request->input('role_filter', 'cs');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $search = trim((string) $request->input('search', ''));

        $targetRoles = match ($roleFilter) {
            'marketing' => ['Marketing', 'Koordinator Marketing'],
            'all' => ['Customer Service', 'Koordinator Customer Service', 'Marketing', 'Koordinator Marketing'],
            default => ['Customer Service', 'Koordinator Customer Service'],
        };

        $officersQuery = User::query()
            ->with(['role', 'campus'])
            ->whereHas('role', fn($q) => $q->whereIn('name', $targetRoles))
            ->active()
            ->orderBy('name');

        if ($activeCampus) {
            $officersQuery->where(function ($q) use ($activeCampus) {
                $q->where('campus_id', $activeCampus->id)
                  ->orWhereNull('campus_id');
            });
        }

        if ($search !== '') {
            $officersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $officerUsers = $officersQuery->get();
        $officerIds = $officerUsers->pluck('id')->all();

        // Base query untuk student questionnaires sesuai kampus & periode aktif
        $sqBase = StudentQuestionnaire::query();
        if ($activeCampus) {
            $sqBase->where(function ($q) use ($activeCampus) {
                $q->where('campus_id', $activeCampus->id)
                  ->orWhereHas('school', fn($sq) => $sq->where('campus_id', $activeCampus->id));
            });
        }
        if ($activePeriod) {
            $sqBase->where('academic_period_id', $activePeriod->id);
        }

        // Hitung data siswa yang belum diploting ke petugas manapun
        $unassignedCount = (clone $sqBase)->whereNull('assigned_to')->count();

        // Ambil ringkasan penugasan per petugas secara efisien
        $assignedRecords = (clone $sqBase)
            ->whereIn('assigned_to', $officerIds)
            ->get(['id', 'assigned_to', 'followup_status', 'last_followup_at']);

        $assignedByUser = $assignedRecords->groupBy('assigned_to');

        // Ambil interaksi follow-up dari petugas terkait
        $fuQuery = StudentFollowUp::query()
            ->whereIn('user_id', $officerIds)
            ->whereHas('studentQuestionnaire', function ($sq) use ($activeCampus, $activePeriod) {
                if ($activeCampus) {
                    $sq->where(function ($q) use ($activeCampus) {
                        $q->where('campus_id', $activeCampus->id)
                          ->orWhereHas('school', fn($s) => $s->where('campus_id', $activeCampus->id));
                    });
                }
                if ($activePeriod) {
                    $sq->where('academic_period_id', $activePeriod->id);
                }
            });

        if ($dateFrom) {
            $fuQuery->whereDate('followup_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $fuQuery->whereDate('followup_date', '<=', $dateTo);
        }

        $allInteractions = (clone $fuQuery)->get(['id', 'user_id', 'followup_date', 'created_at', 'status']);
        $interactionsByUser = $allInteractions->groupBy('user_id');

        // Prospek & Mahasiswa Baru yang ditangani oleh petugas
        $prospectsBase = Prospect::query()->whereIn('marketing_user_id', $officerIds);
        if ($activeCampus) {
            $prospectsBase->where(function ($q) use ($activeCampus) {
                $q->where('campus_id', $activeCampus->id)
                  ->orWhere(function ($sub) use ($activeCampus) {
                      $sub->whereNull('campus_id')
                          ->whereHas('school', fn($sq) => $sq->where('campus_id', $activeCampus->id));
                  });
            });
        }
        if ($activePeriod) {
            $prospectsBase->where(function ($q) use ($activePeriod) {
                $q->where('academic_period_id', $activePeriod->id)
                  ->orWhereNull('academic_period_id');
            });
        }
        $prospectsByUser = $prospectsBase->get(['id', 'marketing_user_id', 'status'])->groupBy('marketing_user_id');

        $todayStr = now()->toDateString();

        $officers = $officerUsers->map(function (User $officer) use ($assignedByUser, $interactionsByUser, $prospectsByUser, $todayStr) {
            /** @var Collection $assigned */
            $assigned = $assignedByUser->get($officer->id, collect());
            /** @var Collection $interactions */
            $interactions = $interactionsByUser->get($officer->id, collect());
            /** @var Collection $prospects */
            $prospects = $prospectsByUser->get($officer->id, collect());

            $totalAssigned = $assigned->count();
            $belumDihubungi = $assigned->where('followup_status', StudentQuestionnaire::STATUS_BELUM_DIHUBUNGI)->count();
            $noRespon = $assigned->where('followup_status', StudentQuestionnaire::STATUS_NO_RESPON)->count();
            $respon = $assigned->where('followup_status', StudentQuestionnaire::STATUS_RESPON)->count();
            $berminat = $assigned->where('followup_status', StudentQuestionnaire::STATUS_BERMINAT)->count();
            $daftarAplikasi = $assigned->filter(fn($r) => in_array($r->followup_status, [StudentQuestionnaire::STATUS_DAFTAR_APLIKASI, 'closing', 'daftar_aplikasi']))->count();

            $contacted = max(0, $totalAssigned - $belumDihubungi);
            $progressRate = $totalAssigned > 0 ? round(($contacted / $totalAssigned) * 100, 1) : 0;
            $conversionRate = $contacted > 0 ? round(($daftarAplikasi / $contacted) * 100, 1) : 0;

            $todayInteractions = $interactions->filter(function ($fu) use ($todayStr) {
                $d = $fu->created_at?->toDateString() ?? (string) $fu->followup_date;
                return $d === $todayStr;
            })->count();

            $lastActivityAt = $interactions->max('created_at') ?: $assigned->max('last_followup_at');

            return [
                'user' => $officer,
                'total_assigned' => $totalAssigned,
                'belum_dihubungi' => $belumDihubungi,
                'contacted' => $contacted,
                'progress_rate' => $progressRate,
                'no_respon' => $noRespon,
                'respon' => $respon,
                'berminat' => $berminat,
                'daftar_aplikasi' => $daftarAplikasi,
                'conversion_rate' => $conversionRate,
                'total_interactions' => $interactions->count(),
                'today_interactions' => $todayInteractions,
                'last_activity_at' => $lastActivityAt ? \Illuminate\Support\Carbon::parse($lastActivityAt) : null,
                'prospects_count' => $prospects->where('status', '!=', Prospect::STATUS_MAHASISWA)->count(),
                'new_students_count' => $prospects->where('status', Prospect::STATUS_MAHASISWA)->count(),
            ];
        })->sortByDesc('total_assigned')->values();

        // Ringkasan KPI Global Tim
        $totalAssignedAll = $officers->sum('total_assigned');
        $totalContactedAll = $officers->sum('contacted');
        $totalPendingAll = $officers->sum('belum_dihubungi');
        $totalDaftarAplikasiAll = $officers->sum('daftar_aplikasi');
        $totalTodayInteractionsAll = $officers->sum('today_interactions');
        $avgProgressRate = $totalAssignedAll > 0 ? round(($totalContactedAll / $totalAssignedAll) * 100, 1) : 0;
        $avgConversionRate = $totalContactedAll > 0 ? round(($totalDaftarAplikasiAll / $totalContactedAll) * 100, 1) : 0;

        // 20 Log Aktivitas Interaksi Follow Up Terbaru dari Tim
        $recentActivities = (clone $fuQuery)
            ->with(['user.role', 'studentQuestionnaire.school'])
            ->latest('created_at')
            ->limit(20)
            ->get();

        return [
            'officers' => $officers,
            'activeCampus' => $activeCampus,
            'activePeriod' => $activePeriod,
            'roleFilter' => $roleFilter,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'search' => $search,
            'recentActivities' => $recentActivities,
            'summary' => [
                'officers_count' => $officers->count(),
                'total_assigned' => $totalAssignedAll,
                'total_pending' => $totalPendingAll,
                'total_contacted' => $totalContactedAll,
                'total_daftar_aplikasi' => $totalDaftarAplikasiAll,
                'today_interactions' => $totalTodayInteractionsAll,
                'avg_progress_rate' => $avgProgressRate,
                'avg_conversion_rate' => $avgConversionRate,
                'unassigned_count' => $unassignedCount,
            ],
        ];
    }
}
