<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\StudentQuestionnaire;
use App\Services\ActivityLogService;
use App\Services\AppContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KolRecommendationController extends Controller
{
    /**
     * Memastikan hanya Administrator, Pimpinan, dan Koordinator Marketing yang dapat mengakses.
     */
    protected function authorizeAccess(): void
    {
        if (!auth()->check() || !auth()->user()->canAccessKolMenu()) {
            abort(403, 'Akses terbatas. Menu Rekomendasi KOL hanya dapat diakses oleh Administrator, Pimpinan, dan Koordinator Marketing.');
        }
    }

    /**
     * Tampilan Utama Rekomendasi KOL (Student Influencer / Duta Kampus)
     */
    public function index(Request $request): View
    {
        $this->authorizeAccess();

        $activeCampus = AppContextService::getActiveCampus();
        $campuses = Campus::where('is_active', true)->get();

        // Base Query Kandidat KOL (Followers >= 1500 atau di-flag is_kol)
        $baseQuery = StudentQuestionnaire::query()
            ->kolCandidates()
            ->with(['school', 'campus', 'eventProposal', 'assignedUser']);

        // Isolasi Kampus
        if ($activeCampus) {
            $baseQuery->where(function ($q) use ($activeCampus) {
                $q->where('campus_id', $activeCampus->id)
                    ->orWhereHas('school', fn($sq) => $sq->where('campus_id', $activeCampus->id))
                    ->orWhereHas('eventProposal', fn($ep) => $ep->where('campus_id', $activeCampus->id));
            });
        }

        // Kalkulasi Statistik Keseluruhan (Scoped Kampus)
        $statClone = clone $baseQuery;
        $allCandidates = $statClone->get();

        $stats = [
            'total_kandidat' => $allCandidates->count(),
            'asal_kuisioner' => $allCandidates->where('source', '!=', 'support_event')->whereNull('school_event_proposal_id')->count(),
            'asal_event' => $allCandidates->filter(fn($item) => $item->source === 'support_event' || !empty($item->school_event_proposal_id))->count(),
            'total_reach' => (int) $allCandidates->sum('instagram_followers'),
            'status_kandidat' => $allCandidates->where('kol_status', StudentQuestionnaire::KOL_STATUS_KANDIDAT)->count(),
            'status_dihubungi' => $allCandidates->where('kol_status', StudentQuestionnaire::KOL_STATUS_DIHUBUNGI)->count(),
            'status_bersedia' => $allCandidates->where('kol_status', StudentQuestionnaire::KOL_STATUS_BERSEDIA)->count(),
            'status_ditolak' => $allCandidates->where('kol_status', StudentQuestionnaire::KOL_STATUS_DITOLAK)->count(),
            'status_aktif' => $allCandidates->where('kol_status', StudentQuestionnaire::KOL_STATUS_AKTIF)->count(),
        ];

        // Filter Pencarian Teks
        if ($request->filled('q')) {
            $search = trim($request->q);
            $baseQuery->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                    ->orWhere('school_name', 'like', "%{$search}%")
                    ->orWhere('student_phone', 'like', "%{$search}%")
                    ->orWhere('instagram_handle', 'like', "%{$search}%")
                    ->orWhere('student_social_media', 'like', "%{$search}%")
                    ->orWhereHas('school', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        // Filter Status Rekrutmen KOL
        if ($request->filled('kol_status')) {
            $baseQuery->where('kol_status', $request->kol_status);
        }

        // Filter Sumber Data (Sosialisasi Kuisioner vs Hasil Event)
        if ($request->filled('source')) {
            if ($request->source === 'support_event') {
                $baseQuery->where(function ($q) {
                    $q->where('source', 'support_event')
                        ->orWhereNotNull('school_event_proposal_id');
                });
            } elseif ($request->source === 'kuisioner') {
                $baseQuery->where('source', '!=', 'support_event')
                    ->whereNull('school_event_proposal_id');
            }
        }

        // Filter Followers Minimum
        if ($request->filled('min_followers') && is_numeric($request->min_followers)) {
            $min = (int) $request->min_followers;
            $baseQuery->where('instagram_followers', '>=', $min);
        }

        // Sorting: default follower terbanyak lebih dulu
        $sortBy = $request->query('sort', 'followers_desc');
        switch ($sortBy) {
            case 'followers_asc':
                $baseQuery->orderBy('instagram_followers', 'asc');
                break;
            case 'name_asc':
                $baseQuery->orderBy('student_name', 'asc');
                break;
            case 'latest':
                $baseQuery->orderByDesc('id');
                break;
            case 'followers_desc':
            default:
                $baseQuery->orderByDesc('instagram_followers')->orderByDesc('id');
                break;
        }

        $candidates = $baseQuery->paginate(15)->withQueryString();
        $statusOptions = StudentQuestionnaire::kolStatusOptions();

        return view('kol-recommendations.index', compact(
            'candidates',
            'stats',
            'statusOptions',
            'activeCampus',
            'campuses'
        ));
    }

    /**
     * Update Status Rekrutmen KOL & Catatan Follow Up Duta Kampus
     */
    public function updateStatus(Request $request, StudentQuestionnaire $studentQuestionnaire): RedirectResponse|JsonResponse
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'kol_status' => 'required|string|in:' . implode(',', array_keys(StudentQuestionnaire::kolStatusOptions())),
            'kol_notes' => 'nullable|string|max:1000',
            'instagram_handle' => 'nullable|string|max:150',
            'instagram_followers' => 'nullable|integer|min:0',
        ]);

        $oldStatus = $studentQuestionnaire->kol_status;

        $studentQuestionnaire->update([
            'kol_status' => $validated['kol_status'],
            'kol_notes' => $validated['kol_notes'] ?? $studentQuestionnaire->kol_notes,
            'instagram_handle' => $request->has('instagram_handle') ? $validated['instagram_handle'] : $studentQuestionnaire->instagram_handle,
            'instagram_followers' => $request->has('instagram_followers') ? (int) $validated['instagram_followers'] : $studentQuestionnaire->instagram_followers,
            'is_kol' => true,
        ]);

        ActivityLogService::log(
            'UPDATE_KOL_STATUS',
            'student_questionnaire',
            $studentQuestionnaire->id,
            "Memperbarui status rekomendasi KOL siswa {$studentQuestionnaire->student_name} dari '{$oldStatus}' menjadi '{$validated['kol_status']}'."
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Status rekomendasi KOL berhasil diperbarui.',
                'lead' => $studentQuestionnaire->fresh(),
            ]);
        }

        return back()->with('success', "Status KOL untuk {$studentQuestionnaire->student_name} berhasil diperbarui.");
    }

    /**
     * Ekspor Data Rekomendasi KOL ke Microsoft Excel (.xlsx)
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $this->authorizeAccess();

        $activeCampus = AppContextService::getActiveCampus();

        $query = StudentQuestionnaire::query()
            ->kolCandidates()
            ->with(['school', 'campus', 'eventProposal']);

        if ($activeCampus) {
            $query->where(function ($q) use ($activeCampus) {
                $q->where('campus_id', $activeCampus->id)
                    ->orWhereHas('school', fn($sq) => $sq->where('campus_id', $activeCampus->id))
                    ->orWhereHas('eventProposal', fn($ep) => $ep->where('campus_id', $activeCampus->id));
            });
        }

        if ($request->filled('q')) {
            $search = trim($request->q);
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                    ->orWhere('school_name', 'like', "%{$search}%")
                    ->orWhere('student_phone', 'like', "%{$search}%")
                    ->orWhere('instagram_handle', 'like', "%{$search}%")
                    ->orWhereHas('school', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('kol_status')) {
            $query->where('kol_status', $request->kol_status);
        }

        if ($request->filled('source')) {
            if ($request->source === 'support_event') {
                $query->where(function ($q) {
                    $q->where('source', 'support_event')
                        ->orWhereNotNull('school_event_proposal_id');
                });
            } elseif ($request->source === 'kuisioner') {
                $query->where('source', '!=', 'support_event')
                    ->whereNull('school_event_proposal_id');
            }
        }

        $candidates = $query->orderByDesc('instagram_followers')->get();

        $campusName = $activeCampus ? $activeCampus->name : 'Semua Kampus';
        $fileName = 'rekomendasi-kol-siswa-' . str()->slug($campusName) . '-' . date('Ymd-His') . '.xlsx';

        return response()->streamDownload(function () use ($candidates, $campusName) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Rekomendasi KOL Siswa');

            // Header Judul
            $sheet->setCellValue('A1', 'DAFTAR REKOMENDASI KOL / STUDENT INFLUENCER');
            $sheet->setCellValue('A2', 'Kampus: ' . $campusName . ' | Diekspor: ' . now()->translatedFormat('d F Y H:i') . ' WIB');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('0B023E');
            $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->getColor()->setRGB('64748B');

            $headers = [
                'No',
                'Sumber Data',
                'Nama Lengkap Siswa',
                'JK',
                'Asal Sekolah',
                'Kelas / Jurusan',
                'No. WhatsApp Siswa',
                'Email Siswa',
                'Akun Instagram',
                'Followers IG',
                'Status Rekrutmen KOL',
                'Catatan / Hasil Kontak',
                'Kampus',
            ];

            // Render Header Kolom pada baris ke-4
            $headerRow = 4;
            foreach ($headers as $index => $header) {
                $colLetter = Coordinate::stringFromColumnIndex($index + 1);
                $sheet->setCellValue($colLetter . $headerRow, $header);
            }

            $sheet->getStyle("A{$headerRow}:M{$headerRow}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0B023E']], // Visionary Navy
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ]);
            $sheet->getRowDimension($headerRow)->setRowHeight(28);

            // Baris Data
            $currentRow = 5;
            $statusLabels = StudentQuestionnaire::kolStatusOptions();

            foreach ($candidates as $idx => $candidate) {
                $isEvent = $candidate->source === 'support_event' || !empty($candidate->school_event_proposal_id);
                $sourceLabel = $isEvent ? 'Support Event' : 'Kuisioner Sosialisasi';
                $statusLabel = $statusLabels[$candidate->kol_status] ?? ucfirst($candidate->kol_status ?? 'Kandidat');
                $classMajor = trim(($candidate->student_class ?? '') . ' ' . ($candidate->student_major ?? ''));

                $sheet->setCellValueExplicit('A' . $currentRow, $idx + 1, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicit('B' . $currentRow, $sourceLabel, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('C' . $currentRow, $candidate->student_name, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('D' . $currentRow, $candidate->gender_label, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('E' . $currentRow, $candidate->school_display_name, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('F' . $currentRow, $classMajor, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('G' . $currentRow, $candidate->student_phone, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('H' . $currentRow, $candidate->student_email ?? '-', DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('I' . $currentRow, '@' . ($candidate->clean_instagram_handle ?? '-'), DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('J' . $currentRow, (int) $candidate->instagram_followers, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicit('K' . $currentRow, $statusLabel, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('L' . $currentRow, $candidate->kol_notes ?? '-', DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('M' . $currentRow, $candidate->campus?->name ?? $campusName, DataType::TYPE_STRING);

                // Zebra striping
                if ($idx % 2 === 1) {
                    $sheet->getStyle("A{$currentRow}:M{$currentRow}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
                    ]);
                }

                $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("G{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("I{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("J{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("K{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $currentRow++;
            }

            $lastDataRow = max($headerRow + 1, $currentRow - 1);

            // Border styling
            $sheet->getStyle("A{$headerRow}:M{$lastDataRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CBD5E1'],
                    ],
                ],
            ]);

            // Auto-width columns
            foreach (range(1, 13) as $colIdx) {
                $colLetter = Coordinate::stringFromColumnIndex($colIdx);
                $sheet->getColumnDimension($colLetter)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}

