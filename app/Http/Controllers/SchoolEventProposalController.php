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
        $schools = School::query()
            ->select('id', 'name', 'district_id')
            ->with('district:id,name')
            ->orderBy('name')
            ->get();

        return view('public-events.create', compact('schools'));
    }

    /**
     * Proses Simpan Pengajuan Event dari Sekolah (Publik).
     */
    public function publicStore(Request $request): RedirectResponse
    {
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

        $proposal = SchoolEventProposal::create([
            'reference_no' => $refNo,
            'school_id' => $request->school_id,
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
        $query = SchoolEventProposal::with(['school.district', 'reviewer'])->latest();

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

        $stats = [
            'total' => SchoolEventProposal::count(),
            'menunggu_review' => SchoolEventProposal::where('status', SchoolEventProposal::STATUS_MENUNGGU_REVIEW)->count(),
            'diajukan_ke_pusat' => SchoolEventProposal::where('status', SchoolEventProposal::STATUS_DIAJUKAN_KE_PUSAT)->count(),
            'menunggu_acc' => SchoolEventProposal::where('status', SchoolEventProposal::STATUS_MENUNGGU_ACC)->count(),
            'disetujui' => SchoolEventProposal::where('status', SchoolEventProposal::STATUS_DISETUJUI)->count(),
            'ditolak' => SchoolEventProposal::where('status', SchoolEventProposal::STATUS_DITOLAK)->count(),
        ];

        return view('event-proposals.index', compact('proposals', 'stats'));
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
        $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(SchoolEventProposal::getStatusOptions())),
            'status_notes' => 'nullable|string|max:1000',
            'approved_amount' => 'nullable|numeric|min:0',
            'approved_support_items' => 'nullable|string|max:500',
        ]);

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

        return back()->with('success', "Status proposal #{$proposal->reference_no} berhasil diperbarui menjadi {$proposal->status_label}.");
    }

    /**
     * Hapus Proposal Kegiatan.
     */
    public function destroy(SchoolEventProposal $proposal): RedirectResponse
    {
        $refNo = $proposal->reference_no;
        $proposal->delete();

        return back()->with('success', "Proposal #{$refNo} berhasil dihapus.");
    }

    /**
     * Laporan Suport Kegiatan (Otomatis Tersinkronisasi dari Proposal yang Disetujui).
     */
    public function reportsIndex(Request $request): View
    {
        // Hanya tampilkan event yang sudah disetujui
        $query = SchoolEventProposal::where('status', SchoolEventProposal::STATUS_DISETUJUI)
            ->with(['school.district', 'reviewer', 'reporter'])
            ->withCount('studentLeads')
            ->orderBy('event_date', 'desc');

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

        $stats = [
            'total_disetujui' => SchoolEventProposal::where('status', SchoolEventProposal::STATUS_DISETUJUI)->count(),
            'menunggu_pelaksanaan' => SchoolEventProposal::where('status', SchoolEventProposal::STATUS_DISETUJUI)->where('execution_status', SchoolEventProposal::EXEC_MENUNGGU_PELAKSANAAN)->count(),
            'sedang_berjalan' => SchoolEventProposal::where('status', SchoolEventProposal::STATUS_DISETUJUI)->where('execution_status', SchoolEventProposal::EXEC_SEDANG_BERJALAN)->count(),
            'selesai' => SchoolEventProposal::where('status', SchoolEventProposal::STATUS_DISETUJUI)->where('execution_status', SchoolEventProposal::EXEC_SELESAI)->count(),
            'total_dana_disetujui' => SchoolEventProposal::where('status', SchoolEventProposal::STATUS_DISETUJUI)->sum('approved_amount'),
        ];

        return view('event-reports.index', compact('events', 'stats'));
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

        // Upload file baru (foto, berkas RAR, ZIP, atau dokumen)
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photoFile) {
                $originalName = $photoFile->getClientOriginalName();
                $path = $photoFile->store('event-documentations', 'public');
                $photos[] = [
                    'path' => $path,
                    'name' => $originalName,
                    'size' => $photoFile->getSize(),
                ];
            }
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
