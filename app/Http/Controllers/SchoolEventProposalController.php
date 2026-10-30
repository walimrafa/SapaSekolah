<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SchoolEventProposal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SchoolEventProposalController extends Controller
{
    /**
     * Form Pengajuan Publik dari Sekolah (Tanpa Login).
     */
    public function publicCreate(): View
    {
        if (!\App\Services\AppContextService::isPortalActive(\App\Services\AppContextService::PORTAL_SCHOOL_EVENT_PROPOSAL)) {
            return view('errors.portal-disabled', [
                'portalName' => 'Portal Pengajuan Event & Sponsorship Sekolah',
            ]);
        }

        $activeCampus = \App\Services\AppContextService::getActiveCampus();
        $campuses = \App\Models\Campus::orderBy('id')->get();
        
        $schools = School::query()
            ->select('id', 'name', 'district_id', 'campus_id')
            ->with('district:id,name')
            ->orderBy('name')
            ->get();

        return view('public-events.create', compact('schools', 'campuses', 'activeCampus'));
    }

    /**
     * Halaman Cek Status & Tracking Pengajuan Proposal Sekolah (Publik).
     */
    public function tracking(Request $request): View
    {
        $search = trim((string) ($request->query('ref') ?? $request->query('q') ?? ''));
        $proposal = null;
        $searched = false;

        if (!empty($search)) {
            $searched = true;
            $cleanPhone = preg_replace('/[^0-9]/', '', $search);

            $proposal = SchoolEventProposal::where('reference_no', $search)
                ->when($cleanPhone, function ($q) use ($cleanPhone) {
                    $q->orWhere('pic_phone', 'like', "%{$cleanPhone}%");
                })
                ->with(['school.district', 'campus', 'reviewer'])
                ->latest()
                ->first();
        }

        $campuses = \App\Models\Campus::all();

        return view('public-events.tracking', compact('proposal', 'search', 'searched', 'campuses'));
    }

    /**
     * Proses Simpan Pengajuan Event dari Sekolah (Publik).
     */
    public function publicStore(Request $request): RedirectResponse
    {
        if (!\App\Services\AppContextService::isPortalActive(\App\Services\AppContextService::PORTAL_SCHOOL_EVENT_PROPOSAL)) {
            return back()->with('error', 'Portal Pengajuan Event saat ini sedang ditutup atau dinonaktifkan.');
        }

        $request->validate([
            'school_id' => 'nullable|exists:schools,id',
            'school_name' => 'required_without:school_id|nullable|string|max:200',
            'pic_name' => 'required|string|max:150',
            'pic_phone' => 'required|string|max:50',
            'pic_position' => 'nullable|string|max:100',
            'event_name' => 'required|string|max:200',
            'event_date' => 'required|date',
            'event_end_date' => 'nullable|date|after_or_equal:event_date',
            'event_time' => 'required|string|max:100',
            'event_location' => 'required|string|max:255',
            'target_participants' => 'required|string|max:150',
            'support_type_requested' => 'nullable|array',
            'support_type_other' => 'nullable|string|max:150',
            'estimated_budget' => 'nullable|numeric|min:0',
            'proposal_file' => [
                'required',
                'file',
                'max:51200',
                'mimes:pdf,doc,docx,zip,rar,7z',
                function ($attribute, $value, $fail) {
                    if ($value instanceof \Illuminate\Http\UploadedFile) {
                        $ext = strtolower($value->getClientOriginalExtension());
                        $allowed = ['pdf', 'doc', 'docx', 'zip', 'rar', '7z'];
                        if (!in_array($ext, $allowed)) {
                            $fail('Format berkas proposal harus berupa PDF, DOC, DOCX, ZIP, atau RAR.');
                        }
                    }
                }
            ],
        ], [
            'proposal_file.max' => 'Ukuran file proposal maksimal adalah 50MB.',
            'proposal_file.required' => 'Berkas proposal kegiatan wajib diunggah.',
            'school_name.required_without' => 'Silakan pilih atau masukkan nama sekolah asal.',
            'pic_name.required' => 'Nama narahubung / PIC wajib diisi.',
            'pic_phone.required' => 'Nomor WhatsApp / HP narahubung wajib diisi.',
            'event_name.required' => 'Nama event / kegiatan wajib diisi.',
            'event_date.required' => 'Tanggal pelaksanaan event wajib diisi.',
            'event_time.required' => 'Waktu pelaksanaan event wajib diisi.',
            'event_location.required' => 'Lokasi / tempat kegiatan wajib diisi.',
            'target_participants.required' => 'Target peserta event wajib diisi.',
        ]);

        // Resolusi Nama Sekolah
        $schoolName = $request->school_name;
        if ($request->filled('school_id')) {
            $school = School::find($request->school_id);
            if ($school) {
                $schoolName = $school->name;
            }
        }

        // Susun jenis support yang diminta
        $supportTypes = $request->support_type_requested ?? [];
        if ($request->filled('support_type_other')) {
            $supportTypes[] = 'Lainnya: ' . trim($request->support_type_other);
        }
        $supportTypeStr = !empty($supportTypes) ? implode(', ', $supportTypes) : 'Sponsorship Umum';

        // Simpan File Proposal ke storage public
        $file = $request->file('proposal_file');
        $originalFileName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $filePath = $file->store('event-proposals', 'public');

        // Buat Nomor Referensi Unik
        $refNo = SchoolEventProposal::generateReferenceNo();

        $activeCampus = \App\Services\AppContextService::getActiveCampus();
        $activePeriod = \App\Services\AppContextService::getActivePeriod();

        $targetCampusId = $request->campus_id ?: ($school?->campus_id ?: ($activeCampus?->id ?? 1));

        $proposal = SchoolEventProposal::create([
            'reference_no' => $refNo,
            'school_id' => $request->school_id,
            'campus_id' => $targetCampusId,
            'academic_period_id' => $activePeriod?->id,
            'school_name' => $schoolName,
            'pic_name' => $request->pic_name,
            'pic_phone' => $request->pic_phone,
            'pic_position' => $request->pic_position,
            'event_name' => $request->event_name,
            'event_date' => $request->event_date,
            'event_end_date' => $request->event_end_date,
            'event_time' => $request->event_time,
            'event_location' => $request->event_location,
            'target_participants' => $request->target_participants,
            'support_type_requested' => $supportTypeStr,
            'estimated_budget' => $request->estimated_budget,
            'proposal_file_path' => $filePath,
            'proposal_file_name' => $originalFileName,
            'proposal_file_size' => $fileSize,
            'status' => SchoolEventProposal::STATUS_MENUNGGU_REVIEW,
            'execution_status' => SchoolEventProposal::EXEC_MENUNGGU_PELAKSANAAN,
        ]);

        \App\Services\ActivityLogService::log(
            'CREATE',
            'school_event',
            $proposal->id,
            "Pengajuan Proposal Event Baru #{$refNo}: {$request->event_name} dari {$schoolName}",
            null,
            ['ref_no' => $refNo, 'event_name' => $request->event_name, 'school' => $schoolName]
        );

        // Kirim Notifikasi WhatsApp Instan (H-0) saat proposal baru diajukan di hari itu
        try {
            app(\App\Services\WhatsAppNotificationService::class)->notifyNewProposal($proposal);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal kirim notifikasi WA proposal baru: ' . $e->getMessage());
        }

        return redirect()->route('school-events.success', $proposal->reference_no)
            ->with('just_submitted', true);
    }

    /**
     * Halaman Bukti / Struk Pengajuan Berhasil (Publik).
     */
    public function publicSuccess(string $referenceNo): View
    {
        $proposal = SchoolEventProposal::where('reference_no', $referenceNo)
            ->with('school.district')
            ->firstOrFail();

        return view('public-events.success', compact('proposal'));
    }

    /**
     * Dashboard Data Proposal Masuk (Protected - Authenticated).
     */
    public function index(Request $request): View
    {
        $activeCampus = \App\Services\AppContextService::getActiveCampus();
        $query = SchoolEventProposal::with(['school.district', 'reviewer', 'campus'])->latest();

        if ($activeCampus) {
            $query->where('campus_id', $activeCampus->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', "%{$search}%")
                  ->orWhere('event_name', 'like', "%{$search}%")
                  ->orWhere('school_name', 'like', "%{$search}%")
                  ->orWhere('pic_name', 'like', "%{$search}%")
                  ->orWhere('pic_phone', 'like', "%{$search}%")
                  ->orWhere('event_location', 'like', "%{$search}%");
            });
        }

        if ($request->filled('month')) {
            $query->whereMonth('event_date', $request->month);
        }

        if ($request->filled('year')) {
            $query->whereYear('event_date', $request->year);
        }

        $proposals = $query->paginate(12)->withQueryString();

        $baseQuery = SchoolEventProposal::query();
        if ($activeCampus) {
            $baseQuery->where('campus_id', $activeCampus->id);
        }

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'menunggu_review' => (clone $baseQuery)->where('status', SchoolEventProposal::STATUS_MENUNGGU_REVIEW)->count(),
            'diajukan_ke_pusat' => (clone $baseQuery)->where('status', SchoolEventProposal::STATUS_DIAJUKAN_KE_PUSAT)->count(),
            'menunggu_acc' => (clone $baseQuery)->where('status', SchoolEventProposal::STATUS_MENUNGGU_ACC)->count(),
            'disetujui' => (clone $baseQuery)->where('status', SchoolEventProposal::STATUS_DISETUJUI)->count(),
            'ditolak' => (clone $baseQuery)->where('status', SchoolEventProposal::STATUS_DITOLAK)->count(),
        ];

        return view('event-proposals.index', compact('proposals', 'stats', 'activeCampus'));
    }

    /**
     * Download Berkas Proposal Kegiatan.
     */
    public function downloadProposal(SchoolEventProposal $proposal): StreamedResponse|RedirectResponse
    {
        if (!Storage::disk('public')->exists($proposal->proposal_file_path)) {
            return back()->with('error', 'File berkas proposal tidak ditemukan di penyimpanan server.');
        }

        return Storage::disk('public')->download($proposal->proposal_file_path, $proposal->proposal_file_name);
    }

    /**
     * Ubah Status Proposal (Review -> Diajukan ke Pusat -> Menunggu ACC -> Disetujui/Ditolak).
     */
    public function updateStatus(Request $request, SchoolEventProposal $proposal): RedirectResponse
    {
        $user = auth()->user();
        if (!$user || (!$user->isAdmin() && !$user->isPimpinan() && !$user->isKoordinator())) {
            abort(403, 'Akses ditolak. Hanya Admin, Pimpinan, dan Koordinator Marketing yang berwenang meninjau proposal event.');
        }

        $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(SchoolEventProposal::getStatusOptions())),
            'status_notes' => 'nullable|string|max:1000',
            'approved_amount' => 'nullable|numeric|min:0',
            'approved_support_items' => 'nullable|string|max:500',
        ]);

        if ($request->has('approved_amount') && (float)$request->approved_amount > 0 && !$user->isAdmin() && !$user->isPimpinan()) {
            abort(403, 'Akses ditolak. Persetujuan anggaran pencairan dana sponsor hanya dapat disahkan oleh Pimpinan Kampus atau Admin.');
        }

        $oldStatus = $proposal->status;
        $proposal->status = $request->status;
        $proposal->status_notes = $request->status_notes;

        if ($request->has('approved_amount')) {
            $proposal->approved_amount = $request->approved_amount;
        }

        if ($request->has('approved_support_items')) {
            $proposal->approved_support_items = $request->approved_support_items;
        }

        $proposal->reviewed_by = auth()->id();
        $proposal->reviewed_at = now();

        // Jika disetujui, pastikan status pelaksanaan siap di laporan kegiatan
        if ($request->status === SchoolEventProposal::STATUS_DISETUJUI && empty($proposal->execution_status)) {
            $proposal->execution_status = SchoolEventProposal::EXEC_MENUNGGU_PELAKSANAAN;
        }

        $proposal->save();

        \App\Services\ActivityLogService::log(
            'UPDATE_STATUS',
            'school_event',
            $proposal->id,
            "Memperbarui status proposal #{$proposal->reference_no} dari '{$oldStatus}' menjadi '{$proposal->status}' (Anggaran: Rp " . number_format($proposal->approved_amount ?? 0, 0, ',', '.') . ")",
            ['old_status' => $oldStatus],
            ['new_status' => $proposal->status, 'approved_amount' => $proposal->approved_amount]
        );

        return back()->with('success', "Status proposal #{$proposal->reference_no} berhasil diperbarui menjadi {$proposal->status_label}.");
    }

    /**
     * Hapus Proposal Kegiatan.
     * Diizinkan bagi Admin, Pimpinan, dan Koordinator Marketing.
     */
    public function destroy(SchoolEventProposal $proposal): RedirectResponse
    {
        $user = auth()->user();
        if (!$user || (!$user->isAdmin() && !$user->isPimpinan() && !$user->isKoordinator())) {
            abort(403, 'Akses ditolak. Penghapusan proposal kegiatan hanya dapat dilakukan oleh Admin, Pimpinan, dan Koordinator Marketing.');
        }

        $refNo = $proposal->reference_no;
        $proposal->delete();

        \App\Services\ActivityLogService::log(
            'DELETE',
            'school_event',
            $proposal->id,
            "Menghapus proposal kegiatan #{$refNo} ({$proposal->event_name})",
            ['ref_no' => $refNo, 'event_name' => $proposal->event_name]
        );

        return back()->with('success', "Proposal #{$refNo} berhasil dihapus.");
    }

    /**
     * Laporan Suport Kegiatan (Otomatis Tersinkronisasi dari Proposal yang Disetujui).
     */
    public function reportsIndex(Request $request): View
    {
        $activeCampus = \App\Services\AppContextService::getActiveCampus();

        // Hanya tampilkan event yang sudah disetujui untuk kampus aktif
        $query = SchoolEventProposal::where('status', SchoolEventProposal::STATUS_DISETUJUI)
            ->with(['school.district', 'reviewer', 'reporter', 'campus'])
            ->withCount('studentLeads')
            ->orderBy('event_date', 'desc');

        if ($activeCampus) {
            $query->where('campus_id', $activeCampus->id);
        }

        if ($request->filled('execution_status')) {
            $query->where('execution_status', $request->execution_status);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', "%{$search}%")
                  ->orWhere('event_name', 'like', "%{$search}%")
                  ->orWhere('school_name', 'like', "%{$search}%")
                  ->orWhere('pic_name', 'like', "%{$search}%")
                  ->orWhere('event_location', 'like', "%{$search}%");
            });
        }

        $events = $query->paginate(12)->withQueryString();

        $baseStatsQuery = SchoolEventProposal::where('status', SchoolEventProposal::STATUS_DISETUJUI);
        if ($activeCampus) {
            $baseStatsQuery->where('campus_id', $activeCampus->id);
        }

        $stats = [
            'total_disetujui' => (clone $baseStatsQuery)->count(),
            'menunggu_pelaksanaan' => (clone $baseStatsQuery)->where('execution_status', SchoolEventProposal::EXEC_MENUNGGU_PELAKSANAAN)->count(),
            'sedang_berjalan' => (clone $baseStatsQuery)->where('execution_status', SchoolEventProposal::EXEC_SEDANG_BERJALAN)->count(),
            'selesai' => (clone $baseStatsQuery)->where('execution_status', SchoolEventProposal::EXEC_SELESAI)->count(),
            'total_dana_disetujui' => (clone $baseStatsQuery)->sum('approved_amount'),
        ];

        return view('event-reports.index', compact('events', 'stats', 'activeCampus'));
    }

    /**
     * Update Dokumentasi & Keterangan Hasil Kegiatan oleh Petugas Lapangan.
     */
    public function updateReport(Request $request, SchoolEventProposal $proposal): RedirectResponse
    {
        // Pastikan proposal berstatus disetujui
        if ($proposal->status !== SchoolEventProposal::STATUS_DISETUJUI) {
            return back()->with('error', 'Hanya kegiatan yang telah disetujui yang dapat diisi laporan pelaksanaannya.');
        }

        $request->validate([
            'execution_status' => 'required|in:' . implode(',', array_keys(SchoolEventProposal::getExecutionStatusOptions())),
            'report_description' => 'nullable|string|max:2000',
            'realized_participants' => 'nullable|integer|min:0',
            'prospects_acquired' => 'nullable|integer|min:0',
            'photos.*' => [
                'nullable',
                'file',
                'max:51200', // max 50MB per file / archive
                function ($attribute, $value, $fail) {
                    if ($value instanceof \Illuminate\Http\UploadedFile) {
                        $ext = strtolower($value->getClientOriginalExtension());
                        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'rar', 'zip', '7z', 'pdf', 'doc', 'docx'];
                        if (!in_array($ext, $allowed)) {
                            $fail("Format berkas '{$value->getClientOriginalName()}' tidak didukung. Gunakan format foto (JPG, PNG, WEBP), arsip (RAR, ZIP), atau dokumen (PDF).");
                        }
                    }
                }
            ],
            'delete_photos' => 'nullable|array',
        ], [
            'photos.*.max' => 'Ukuran masing-masing berkas dokumentasi maksimal 50MB.',
        ]);

        $photos = $proposal->documentation_photos ?? [];
        $existingPaths = array_map(function($p) {
            return is_array($p) ? ($p['path'] ?? '') : $p;
        }, $photos);

        // Hapus file yang dipilih untuk dihapus (hanya file yang terdaftar pada proposal ini)
        if ($request->has('delete_photos') && is_array($request->delete_photos)) {
            foreach ($request->delete_photos as $deletePath) {
                if (in_array($deletePath, $existingPaths, true) && Storage::disk('public')->exists($deletePath)) {
                    Storage::disk('public')->delete($deletePath);
                }
                $photos = array_values(array_filter($photos, function($p) use ($deletePath) {
                    $itemPath = is_array($p) ? ($p['path'] ?? '') : $p;
                    return $itemPath !== $deletePath;
                }));
            }
        }

        // Upload file baru (foto, berkas RAR, ZIP, atau dokumen) tersinkronisasi ke Google Drive Cloud & Local Storage
        if ($request->hasFile('photos')) {
            $eventDateStr = $proposal->event_date ? $proposal->event_date->format('Y-m-d') : now()->format('Y-m-d');
            $schoolTarget = $proposal->school?->name ?? $proposal->school_name ?? 'Sekolah';
            foreach ($request->file('photos') as $photoFile) {
                $originalName = $photoFile->getClientOriginalName();
                $gdriveResult = \App\Services\GoogleDriveService::uploadDocumentation($photoFile, $schoolTarget, $eventDateStr);
                $photos[] = [
                    'path' => $gdriveResult['stored_path'],
                    'name' => $originalName,
                    'size' => $photoFile->getSize(),
                    'drive_file_url' => $gdriveResult['drive_file_url'],
                    'drive_folder_url' => $gdriveResult['drive_folder_url'],
                ];
                $proposal->google_drive_folder_url = $gdriveResult['drive_folder_url'];
                $proposal->google_drive_file_url = $gdriveResult['drive_file_url'];
            }
        }

        if (empty($proposal->google_drive_folder_url)) {
            $eventDateStr = $proposal->event_date ? $proposal->event_date->format('Y-m-d') : now()->format('Y-m-d');
            $schoolTarget = $proposal->school?->name ?? $proposal->school_name ?? 'Sekolah';
            $proposal->google_drive_folder_url = \App\Services\GoogleDriveService::buildFolderUrlForSchool($schoolTarget, $eventDateStr);
        }

        $proposal->documentation_photos = $photos;
        $proposal->execution_status = $request->execution_status;
        $proposal->report_description = $request->report_description;
        $proposal->realized_participants = $request->realized_participants;
        $proposal->prospects_acquired = $request->prospects_acquired;
        $proposal->reported_by = auth()->id();
        $proposal->reported_at = now();
        $proposal->save();

        return back()->with('success', "Dokumentasi dan laporan kegiatan '{$proposal->event_name}' berhasil diperbarui.");
    }
}
