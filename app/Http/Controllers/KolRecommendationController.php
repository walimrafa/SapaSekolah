<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\StudentQuestionnaire;
use App\Services\ActivityLogService;
use App\Services\AppContextService;
use App\Services\AppSettingService;
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
     * Pastikan pengguna memiliki hak akses terhadap data siswa berdasarkan isolasi kampus.
     * Pengguna kampus cabang (misal UBSI Karawang / UBSI Cikampek) tidak boleh mengakses data kampus lain.
     */
    protected function authorizeCampusAccess(StudentQuestionnaire $sq): void
    {
        $user = auth()->user();
        if ($user && !\App\Services\AppContextService::canSwitchCampus($user)) {
            $userCampusId = $user->campus_id ?? \App\Services\AppContextService::getActiveCampus()?->id;
            $sqCampusId = $sq->campus_id ?: ($sq->school?->campus_id);
            if ($sqCampusId && $userCampusId && (int)$sqCampusId !== (int)$userCampusId) {
                abort(403, 'Akses ditolak. Rekomendasi KOL ini berada di luar wilayah kampus penugasan Anda.');
            }
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
     * Tampilan Sub-Menu: Hasil Dokumentasi & Karya Brand Ambassador KOL (Instagram, TikTok, FB, YouTube)
     */
    public function documentation(Request $request): View
    {
        $this->authorizeAccess();

        $activeCampus = \App\Services\AppContextService::getActiveCampus();
        $user = auth()->user();
        $canSwitchCampus = \App\Services\AppContextService::canSwitchCampus($user);

        // Menampilkan siswa KOL yang sudah menjadi Brand Ambassador, aktif, bersedia, atau sudah melampirkan link karya
        $baseQuery = StudentQuestionnaire::query()
            ->with(['school.district', 'campus', 'eventProposal'])
            ->where('is_kol', true)
            ->where(function ($q) {
                $q->whereIn('kol_status', [
                    StudentQuestionnaire::KOL_STATUS_BRAND_AMBASSADOR,
                    'aktif',
                    'bersedia',
                ])->orWhereNotNull('work_links');
            });

        // Filter Kampus Isolasi
        if ($activeCampus) {
            $baseQuery->where(function ($q) use ($activeCampus) {
                $q->where('campus_id', $activeCampus->id)
                    ->orWhereHas('school', function ($sq) use ($activeCampus) {
                        $sq->where('campus_id', $activeCampus->id);
                    })
                    ->orWhereHas('eventProposal', function ($ep) use ($activeCampus) {
                        $ep->where('campus_id', $activeCampus->id);
                    });
            });
        }

        // Summary metrics
        $allDocs = (clone $baseQuery)->get();
        $totalAmbassadors = $allDocs->whereIn('kol_status', [StudentQuestionnaire::KOL_STATUS_BRAND_AMBASSADOR, 'aktif'])->count();
        $totalIgWorks = $allDocs->filter(fn($c) => !empty($c->work_links['instagram']))->count();
        $totalTiktokWorks = $allDocs->filter(fn($c) => !empty($c->work_links['tiktok']))->count();
        $totalYtFbWorks = $allDocs->filter(fn($c) => !empty($c->work_links['youtube']) || !empty($c->work_links['facebook']))->count();

        $stats = [
            'total_ambassadors' => $totalAmbassadors,
            'total_ig_works' => $totalIgWorks,
            'total_tiktok_works' => $totalTiktokWorks,
            'total_yt_fb_works' => $totalYtFbWorks,
        ];

        // Search Filter
        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            $baseQuery->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                    ->orWhere('student_phone', 'like', "%{$search}%")
                    ->orWhere('student_social_media', 'like', "%{$search}%")
                    ->orWhere('instagram_handle', 'like', "%{$search}%")
                    ->orWhere('tiktok_handle', 'like', "%{$search}%")
                    ->orWhere('kol_notes', 'like', "%{$search}%")
                    ->orWhereHas('school', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Platform Filter
        if ($request->filled('platform') && $request->platform !== 'all') {
            $p = $request->platform;
            if (in_array($p, ['instagram', 'tiktok', 'facebook', 'youtube'])) {
                $baseQuery->whereNotNull("work_links->{$p}");
            }
        }

        // Status Filter
        if ($request->filled('status') && $request->status !== 'all') {
            $baseQuery->where('kol_status', $request->status);
        }

        $items = $baseQuery->latest('updated_at')->paginate(12)->withQueryString();
        $campuses = Campus::where('is_active', true)->get();

        return view('kol-recommendations.documentation', compact(
            'items',
            'stats',
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
        $this->authorizeCampusAccess($studentQuestionnaire);

        $validated = $request->validate([
            'kol_status' => 'required|string|in:' . implode(',', array_keys(StudentQuestionnaire::kolStatusOptions())),
            'kol_notes' => 'nullable|string|max:1000',
            'instagram_handle' => 'nullable|string|max:150',
            'instagram_followers' => 'nullable|integer|min:0',
            'student_name' => 'nullable|string|max:150',
            'student_phone' => 'nullable|string|max:30',
        ]);

        $oldStatus = $studentQuestionnaire->kol_status;
        $minFollowers = StudentQuestionnaire::getKolMinFollowers();

        $newFollowers = $request->has('instagram_followers')
            ? (int) $validated['instagram_followers']
            : (int) $studentQuestionnaire->instagram_followers;

        $meetsMin = ($newFollowers >= $minFollowers);
        $isEligible = $meetsMin && ($validated['kol_status'] !== StudentQuestionnaire::KOL_STATUS_DITOLAK);

        $dataToUpdate = [
            'kol_status' => $validated['kol_status'],
            'kol_notes' => $validated['kol_notes'] ?? $studentQuestionnaire->kol_notes,
            'instagram_followers' => $newFollowers,
            'is_kol' => $isEligible,
        ];

        if ($request->has('instagram_handle')) {
            $dataToUpdate['instagram_handle'] = $validated['instagram_handle'];
        }

        if ($request->filled('student_name')) {
            $dataToUpdate['student_name'] = $validated['student_name'];
        }

        if ($request->filled('student_phone')) {
            $dataToUpdate['student_phone'] = $validated['student_phone'];
        }

        // Jika followers kurang dari syarat kelayakan minimal, otomatis tandai tidak layak / batalkan
        if (!$meetsMin) {
            $dataToUpdate['is_kol'] = false;
            $dataToUpdate['kol_status'] = StudentQuestionnaire::KOL_STATUS_DITOLAK;
            $dataToUpdate['kol_verification_status'] = StudentQuestionnaire::KOL_VERIFY_REJECTED;
            $dataToUpdate['kol_verification_notes'] = "Followers ({$newFollowers}) tidak memenuhi batas kelayakan minimal ({$minFollowers}). Dikeluarkan dari rekomendasi KOL.";
        }

        $studentQuestionnaire->update($dataToUpdate);

        ActivityLogService::log(
            'UPDATE_KOL_STATUS',
            'student_questionnaire',
            $studentQuestionnaire->id,
            "Memperbarui data/status KOL siswa {$studentQuestionnaire->student_name} ({$newFollowers} followers, status: {$dataToUpdate['kol_status']})."
        );

        $message = !$meetsMin
            ? "Data {$studentQuestionnaire->student_name} berhasil diperbarui. Karena jumlah followers ({$newFollowers}) di bawah batas kelayakan minimal ({$minFollowers}), data ini otomatis dikeluarkan dari daftar Rekomendasi KOL."
            : "Data dan status KOL untuk {$studentQuestionnaire->student_name} berhasil diperbarui.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'lead' => $studentQuestionnaire->fresh(),
                'is_eligible' => $isEligible,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Hapus / Keluarkan data siswa dari daftar Rekomendasi KOL jika follower tidak sesuai / tidak layak.
     */
    public function destroy(Request $request, StudentQuestionnaire $studentQuestionnaire): RedirectResponse|JsonResponse
    {
        $this->authorizeAccess();
        $this->authorizeCampusAccess($studentQuestionnaire);

        $mode = $request->input('mode', 'remove_kol'); // 'remove_kol' (default) atau 'delete_permanent'
        $studentName = $studentQuestionnaire->student_name;

        if ($mode === 'delete_permanent') {
            if ($studentQuestionnaire->prospect) {
                $studentQuestionnaire->prospect->delete();
            }
            $studentQuestionnaire->followUps()->delete();
            $studentQuestionnaire->delete();

            ActivityLogService::log(
                'DELETE_STUDENT_FROM_KOL',
                'student_questionnaire',
                $studentQuestionnaire->id,
                "Menghapus permanen data siswa {$studentName} dari menu Rekomendasi KOL."
            );

            $msg = "Data siswa {$studentName} berhasil dihapus permanen dari sistem.";
        } else {
            $studentQuestionnaire->update([
                'is_kol' => false,
                'kol_status' => StudentQuestionnaire::KOL_STATUS_DITOLAK,
                'kol_verification_status' => StudentQuestionnaire::KOL_VERIFY_REJECTED,
                'kol_verification_notes' => 'Dikeluarkan dari rekomendasi KOL karena follower tidak sesuai / tidak layak.',
            ]);

            ActivityLogService::log(
                'REMOVE_KOL',
                'student_questionnaire',
                $studentQuestionnaire->id,
                "Mengeluarkan siswa {$studentName} dari daftar Rekomendasi KOL karena follower tidak sesuai / tidak layak."
            );

            $msg = "Siswa {$studentName} berhasil dikeluarkan dari daftar Rekomendasi KOL karena tidak memenuhi kriteria kelayakan.";
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => $msg,
        ]);
    }

    /**
     * Verifikasi otomatis jumlah follower riil Instagram siswa.
     */
    public function verifyFollowers(Request $request, StudentQuestionnaire $studentQuestionnaire): JsonResponse|RedirectResponse
    {
        $this->authorizeAccess();
        $this->authorizeCampusAccess($studentQuestionnaire);

        $result = \App\Services\InstagramVerificationService::verifyCandidate($studentQuestionnaire);

        ActivityLogService::log(
            'VERIFY_KOL_FOLLOWERS',
            'student_questionnaire',
            $studentQuestionnaire->id,
            "Verifikasi follower IG siswa {$studentQuestionnaire->student_name}: Status {$result['status']} ({$result['verified_followers']} followers)"
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'result' => $result,
                'candidate' => $studentQuestionnaire->fresh(),
            ]);
        }

        $type = ($result['status'] === StudentQuestionnaire::KOL_VERIFY_VERIFIED) ? 'success' : 'warning';
        return back()->with('toast', [
            'type' => $type,
            'message' => $result['notes'],
        ]);
    }

    /**
     * Simpan tautan karya Brand Ambassador (IG, TikTok, Facebook, YouTube).
     */
    public function saveWorkLinks(Request $request, StudentQuestionnaire $studentQuestionnaire): JsonResponse|RedirectResponse
    {
        $this->authorizeAccess();
        $this->authorizeCampusAccess($studentQuestionnaire);

        $validated = $request->validate([
            'instagram_link' => ['nullable', 'url', 'max:255'],
            'tiktok_link' => ['nullable', 'url', 'max:255'],
            'facebook_link' => ['nullable', 'url', 'max:255'],
            'youtube_link' => ['nullable', 'url', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $workLinks = [
            'instagram' => $validated['instagram_link'] ?? null,
            'tiktok' => $validated['tiktok_link'] ?? null,
            'facebook' => $validated['facebook_link'] ?? null,
            'youtube' => $validated['youtube_link'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'updated_at' => now()->toIso8601String(),
        ];

        $studentQuestionnaire->update([
            'work_links' => $workLinks,
        ]);

        ActivityLogService::log(
            'SAVE_KOL_WORK_LINKS',
            'student_questionnaire',
            $studentQuestionnaire->id,
            "Memperbarui link karya Brand Ambassador siswa {$studentQuestionnaire->student_name}"
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Link karya Brand Ambassador berhasil disimpan!',
                'work_links' => $workLinks,
            ]);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Tautan karya Brand Ambassador berhasil diperbarui!',
        ]);
    }

    /**
     * Update masal status rekrutmen KOL yang dipilih.
     */
    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'selected_ids' => ['required', 'array'],
            'selected_ids.*' => ['exists:student_questionnaires,id'],
            'kol_status' => ['required', 'string', 'in:' . implode(',', array_keys(StudentQuestionnaire::kolStatusOptions()))],
        ]);

        $ids = $validated['selected_ids'];
        $newStatus = $validated['kol_status'];

        $count = StudentQuestionnaire::whereIn('id', $ids)->update([
            'kol_status' => $newStatus,
            'is_kol' => ($newStatus !== StudentQuestionnaire::KOL_STATUS_DITOLAK),
        ]);

        $label = StudentQuestionnaire::kolStatusOptions()[$newStatus] ?? $newStatus;

        ActivityLogService::log(
            'BULK_UPDATE_KOL_STATUS',
            'student_questionnaire',
            null,
            "Memperbarui status masal sebanyak {$count} kandidat KOL menjadi: {$label}"
        );

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Berhasil memperbarui status {$count} kandidat KOL menjadi '{$label}'.",
        ]);
    }

    /**
     * Hapus masal data siswa yang tidak sesuai dari daftar Rekomendasi KOL.
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'selected_ids' => ['required', 'array'],
            'selected_ids.*' => ['exists:student_questionnaires,id'],
        ]);

        $ids = $validated['selected_ids'];

        $count = StudentQuestionnaire::whereIn('id', $ids)->update([
            'is_kol' => false,
            'kol_status' => StudentQuestionnaire::KOL_STATUS_DITOLAK,
            'kol_verification_status' => StudentQuestionnaire::KOL_VERIFY_REJECTED,
            'kol_verification_notes' => 'Dikeluarkan dari rekomendasi KOL karena data tidak sesuai / tidak valid.',
        ]);

        ActivityLogService::log(
            'BULK_REMOVE_KOL',
            'student_questionnaire',
            null,
            "Mengeluarkan sebanyak {$count} siswa dari daftar Rekomendasi KOL."
        );

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Berhasil menghapus {$count} data yang tidak sesuai dari Rekomendasi KOL.",
        ]);
    }

    /**
     * Pengecekan real followers masal untuk akun yang dipilih.
     */
    public function bulkVerifyFollowers(Request $request): RedirectResponse
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'selected_ids' => ['required', 'array'],
            'selected_ids.*' => ['exists:student_questionnaires,id'],
        ]);

        $candidates = StudentQuestionnaire::whereIn('id', $validated['selected_ids'])->get();
        $verifiedCount = 0;
        $rejectedCount = 0;

        foreach ($candidates as $candidate) {
            $res = \App\Services\InstagramVerificationService::verifyCandidate($candidate);
            if ($res['status'] === StudentQuestionnaire::KOL_VERIFY_VERIFIED) {
                $verifiedCount++;
            } elseif ($res['status'] === StudentQuestionnaire::KOL_VERIFY_REJECTED) {
                $rejectedCount++;
            }
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Pengecekan selesai: {$verifiedCount} akun terverifikasi valid, {$rejectedCount} akun tertolak otomatis karena data tidak sesuai.",
        ]);
    }

    /**
     * Ekspor Data Rekomendasi KOL ke Microsoft Excel (.xlsx)
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $this->authorizeAccess();

        $user = auth()->user();
        if ($user && !AppContextService::canSwitchCampus($user)) {
            $campusId = $user->campus_id ?? AppContextService::getActiveCampus()?->id;
        } else {
            $campusId = $request->filled('campus_id') && $request->campus_id !== 'ALL'
                ? $request->campus_id
                : AppContextService::getActiveCampus()?->id;
        }

        $activeCampus = ($campusId && $campusId !== 'ALL') ? \App\Models\Campus::find($campusId) : null;

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

    /**
     * Update Master Pengaturan Syarat Minimal Follower Rekomendasi KOL.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'kol_min_followers' => ['required', 'integer', 'min:1', 'max:10000000'],
            'sync_existing' => ['nullable'],
        ]);

        $newMin = (int) $validated['kol_min_followers'];
        AppSettingService::set('kol_min_followers', $newMin);

        $syncedCount = 0;
        if ($request->boolean('sync_existing')) {
            $syncedCount = StudentQuestionnaire::where(function ($q) use ($newMin) {
                $q->where('instagram_followers', '>=', $newMin)
                    ->orWhere('tiktok_followers', '>=', $newMin);
            })->where('is_kol', false)->update([
                'is_kol' => true,
                'kol_status' => StudentQuestionnaire::KOL_STATUS_KANDIDAT,
            ]);
        }

        $formattedMin = number_format($newMin, 0, ',', '.');
        $message = "Syarat minimal follower KOL berhasil diperbarui menjadi {$formattedMin} followers!";
        if ($syncedCount > 0) {
            $message .= " Sebanyak {$syncedCount} siswa baru otomatis di-flag sebagai kandidat KOL.";
        }

        return redirect()->route('kol-recommendations.index')
            ->with('toast', [
                'type' => 'success',
                'message' => $message,
            ]);
    }
}

