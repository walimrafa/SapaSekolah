<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SchoolEventProposal;
use App\Models\StudentQuestionnaire;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventStudentLeadController extends Controller
{
    /**
     * Form Pendaftaran Siswa Publik - Hasil Support Event (Tanpa Login & Tanpa Kuisioner).
     */
    public function publicCreate(Request $request): View
    {
        $campuses = \App\Models\Campus::where('is_active', true)->orderBy('is_default', 'desc')->get();
        $requestedCampusId = $request->query('campus_id');

        $activeCampus = null;
        $isCampusLocked = false;

        if (auth()->check()) {
            $user = auth()->user();
            if ($user->campus_id) {
                $activeCampus = \App\Models\Campus::find($user->campus_id);
                $isCampusLocked = true;
            } elseif ($requestedCampusId) {
                $activeCampus = \App\Models\Campus::find($requestedCampusId);
                $isCampusLocked = true;
            } else {
                $activeCampus = \App\Services\AppContextService::getActiveCampus();
                $isCampusLocked = ($activeCampus !== null);
            }
        } elseif ($requestedCampusId) {
            $activeCampus = \App\Models\Campus::find($requestedCampusId);
            $isCampusLocked = ($activeCampus !== null);
        }

        $selectedEventId = $request->query('event_id');
        $selectedEvent = $selectedEventId ? SchoolEventProposal::find($selectedEventId) : null;
        if ($selectedEvent && $selectedEvent->campus_id && !$requestedCampusId) {
            $activeCampus = $selectedEvent->campus;
            $isCampusLocked = true;
        }

        if (!$activeCampus) {
            $activeCampus = \App\Services\AppContextService::getActiveCampus() ?? \App\Models\Campus::getDefault();
        }

        if (!\App\Services\AppContextService::isPortalActive(\App\Services\AppContextService::PORTAL_EVENT_STUDENT_LEAD, $activeCampus?->id)) {
            return view('errors.portal-disabled', [
                'portalName' => 'Portal Pendaftaran Siswa Event (' . ($activeCampus?->name ?? 'UBSI') . ')',
            ]);
        }

        $schoolsQuery = School::query()
            ->select('id', 'name', 'district_id', 'campus_id', 'school_type_id')
            ->with(['district:id,name', 'campus', 'schoolType']);

        if ($activeCampus) {
            $schoolsQuery->where('campus_id', $activeCampus->id);
        }

        $schools = $schoolsQuery->orderBy('name')->get();

        $eventsQuery = SchoolEventProposal::where('status', SchoolEventProposal::STATUS_DISETUJUI)
            ->orderByDesc('event_date');

        if ($activeCampus) {
            $eventsQuery->where('campus_id', $activeCampus->id);
        }

        $events = $eventsQuery->get(['id', 'event_name', 'school_name', 'school_id', 'event_date', 'event_location']);
        $selectedSchoolId = $request->query('school_id') ?? ($selectedEvent ? $selectedEvent->school_id : null);

        return view('public-events.student-form', compact('schools', 'events', 'selectedEvent', 'selectedEventId', 'selectedSchoolId', 'campuses', 'activeCampus', 'isCampusLocked'));
    }

    /**
     * Simpan Data Pendaftaran Siswa dari Event (Tersinkronisasi Langsung ke Follow Up Siswa).
     */
    public function publicStore(Request $request): RedirectResponse
    {
        $campusId = $request->input('campus_id') ?? $request->query('campus_id');
        if (!\App\Services\AppContextService::isPortalActive(\App\Services\AppContextService::PORTAL_EVENT_STUDENT_LEAD, $campusId)) {
            return back()->with('error', 'Portal Pendaftaran Siswa Event untuk kampus ini saat ini sedang ditutup atau dinonaktifkan.');
        }

        $request->validate([
            'student_name' => 'required|string|max:150',
            'gender' => 'nullable|string|in:L,P,laki-laki,perempuan,Laki-laki,Perempuan',
            'school_id' => 'required',
            'school_name' => 'nullable|string|max:150',
            'student_class' => 'required|string|max:50',
            'student_major' => 'required|string|max:100',
            'student_phone' => 'required|string|max:30',
            'student_email' => 'required|email|max:150',
            'student_social_media' => 'nullable|string|max:255',
            'instagram_handle' => 'nullable|string|max:150',
            'instagram_followers' => 'nullable|integer|min:0',
            'tiktok_handle' => 'nullable|string|max:150',
            'tiktok_followers' => 'nullable|integer|min:0',
            'guardian_name' => 'required|string|max:150',
            'guardian_phone' => 'required|string|max:30',
            'guardian_social_media' => 'nullable|string|max:255',
            'scholarship_interest' => 'required|in:ya,tidak',
            'school_event_proposal_id' => 'nullable|exists:school_event_proposals,id',
            'event_name_other' => 'nullable|string|max:200',
        ], [
            'student_name.required' => 'Nama lengkap siswa wajib diisi.',
            'school_id.required' => 'Asal sekolah wajib dipilih.',
            'student_class.required' => 'Kelas wajib diisi (Contoh: XII IPA 1).',
            'student_major.required' => 'Jurusan wajib diisi (Contoh: IPA, IPS, RPL).',
            'student_phone.required' => 'Nomor HP / WhatsApp siswa wajib diisi.',
            'student_email.required' => 'Email siswa wajib diisi.',
            'guardian_name.required' => 'Nama orang tua / wali wajib diisi.',
            'guardian_phone.required' => 'Nomor HP orang tua / wali wajib diisi.',
            'scholarship_interest.required' => 'Silakan pilih minat program beasiswa (Ya / Tidak).',
        ]);

        $schoolId = ($request->school_id === 'other') ? null : $request->school_id;
        $schoolName = $request->school_name;

        if ($schoolId) {
            $school = School::find($schoolId);
            if ($school) {
                $schoolName = $school->name;
            }
        }

        $eventTitle = null;
        if ($request->filled('school_event_proposal_id')) {
            $event = SchoolEventProposal::find($request->school_event_proposal_id);
            if ($event) {
                $eventTitle = $event->event_name;
                // Update total prospek siswa yang diperoleh di event ini
                $event->increment('prospects_acquired');
            }
        } elseif ($request->filled('event_name_other')) {
            $eventTitle = trim($request->event_name_other);
        }

        $collegeNotes = $eventTitle 
            ? "Hasil Support Event: {$eventTitle}" 
            : 'Hasil Support Event Sekolah';

        $scholarshipNotes = ($request->scholarship_interest === 'ya') 
            ? 'Berminat Program Beasiswa UBSI' 
            : 'Non-Beasiswa / Jalur Mandiri';

        $activeCampus = \App\Services\AppContextService::getActiveCampus();
        $activePeriod = \App\Services\AppContextService::getActivePeriod();

        $targetCampusId = $event?->campus_id ?: ($school?->campus_id ?: ($request->input('campus_id') ?: ($activeCampus?->id ?? 1)));

        $gender = $request->filled('gender')
            ? (strtoupper(substr($request->gender, 0, 1)) === 'P' ? 'P' : 'L')
            : null;

        // Simpan ke database student_questionnaires agar tersinkronisasi otomatis ke CRM Follow Up Siswa
        $lead = StudentQuestionnaire::create([
            'school_id' => $schoolId,
            'campus_id' => $targetCampusId,
            'academic_period_id' => $activePeriod?->id,
            'school_event_proposal_id' => $request->school_event_proposal_id,
            'school_name' => $schoolName,
            'student_name' => $request->student_name,
            'gender' => $gender,
            'student_class' => $request->student_class,
            'student_major' => $request->student_major,
            'student_phone' => $request->student_phone,
            'student_email' => $request->student_email,
            'student_social_media' => $request->student_social_media,
            'instagram_handle' => $request->instagram_handle,
            'instagram_followers' => (int) ($request->instagram_followers ?? 0),
            'tiktok_handle' => $request->tiktok_handle,
            'tiktok_followers' => (int) ($request->tiktok_followers ?? 0),
            'guardian_name' => $request->guardian_name,
            'guardian_phone' => $request->guardian_phone,
            'guardian_social_media' => $request->guardian_social_media,
            'scholarship_interest' => $request->scholarship_interest,
            'source' => 'support_event',
            'answers' => null, // Tanpa isian kuisioner 16 soal
            'followup_status' => StudentQuestionnaire::STATUS_BELUM_DIHUBUNGI,
            'college_interest_notes' => $collegeNotes,
            'scholarship_notes' => $scholarshipNotes,
        ]);

        \App\Services\ActivityLogService::log(
            'CREATE',
            'event_student',
            $lead->id,
            "Pendaftaran Siswa Event: {$lead->student_name} ({$schoolName})",
            null,
            ['student_name' => $lead->student_name, 'school' => $schoolName, 'event' => $eventTitle]
        );

        return redirect()->route('event-students.success')
            ->with('student_name', $lead->student_name)
            ->with('scholarship_interest', $lead->scholarship_interest);
    }

    /**
     * Halaman Konfirmasi Sukses Submit Form Siswa Event.
     */
    public function publicSuccess(): View
    {
        return view('public-events.student-success');
    }

    /**
     * Dashboard Data Hasil Support Event (Protected - Authenticated Staff).
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $canPlot = $user && $user->canPlotFollowUps();

        $activeCampus = \App\Services\AppContextService::getActiveCampus();

        $query = StudentQuestionnaire::query()
            ->where(function ($q) {
                $q->where('source', 'support_event')
                  ->orWhereNotNull('school_event_proposal_id')
                  ->orWhere('college_interest_notes', 'like', '%Support Event%');
            })
            ->with(['school.district', 'eventProposal', 'assignedUser', 'assignedBy', 'lastFollowedUpBy', 'followUps'])
            ->latest();

        if ($activeCampus) {
            $query->where(function ($q) use ($activeCampus) {
                $q->where('campus_id', $activeCampus->id)
                  ->orWhereHas('school', fn($sq) => $sq->where('campus_id', $activeCampus->id))
                  ->orWhereHas('eventProposal', fn($ep) => $ep->where('campus_id', $activeCampus->id));
            });
        }

        // Staf Marketing murni hanya melihat data hasil support event yang di-ploting ke dirinya
        if ($user && $user->isMarketing() && !$canPlot) {
            $query->where('assigned_to', $user->id);
        } elseif ($request->filled('assigned_to') && $request->assigned_to !== 'all') {
            if ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->assigned_to);
            }
        }

        if ($request->filled('event_id')) {
            $query->where('school_event_proposal_id', $request->event_id);
        }

        if ($request->filled('scholarship_interest')) {
            $query->where('scholarship_interest', $request->scholarship_interest);
        }

        if ($request->filled('status')) {
            $query->where('followup_status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('student_phone', 'like', "%{$search}%")
                  ->orWhere('student_email', 'like', "%{$search}%")
                  ->orWhere('school_name', 'like', "%{$search}%")
                  ->orWhere('student_class', 'like', "%{$search}%")
                  ->orWhere('student_major', 'like', "%{$search}%")
                  ->orWhereHas('assignedUser', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $students = $query->paginate(15)->withQueryString();

        $eventsQuery = SchoolEventProposal::where('status', SchoolEventProposal::STATUS_DISETUJUI)
            ->orderByDesc('event_date');
        if ($activeCampus) {
            $eventsQuery->where('campus_id', $activeCampus->id);
        }
        $events = $eventsQuery->get(['id', 'event_name', 'school_name', 'event_date']);

        $baseCount = StudentQuestionnaire::where(function ($q) {
            $q->where('source', 'support_event')
              ->orWhereNotNull('school_event_proposal_id')
              ->orWhere('college_interest_notes', 'like', '%Support Event%');
        });

        if ($activeCampus) {
            $baseCount->where(function ($q) use ($activeCampus) {
                $q->where('campus_id', $activeCampus->id)
                  ->orWhereHas('school', fn($sq) => $sq->where('campus_id', $activeCampus->id))
                  ->orWhereHas('eventProposal', fn($ep) => $ep->where('campus_id', $activeCampus->id));
            });
        }

        $metrics = [
            'total' => (clone $baseCount)->count(),
            'scholarship_yes' => (clone $baseCount)->where('scholarship_interest', 'ya')->count(),
            'scholarship_no' => (clone $baseCount)->where('scholarship_interest', 'tidak')->count(),
            'belum_dihubungi' => (clone $baseCount)->where('followup_status', StudentQuestionnaire::STATUS_BELUM_DIHUBUNGI)->count(),
            'terfollowup' => (clone $baseCount)->where('followup_status', '!=', StudentQuestionnaire::STATUS_BELUM_DIHUBUNGI)->count(),
            'unassigned' => (clone $baseCount)->whereNull('assigned_to')->count(),
            'assigned' => (clone $baseCount)->whereNotNull('assigned_to')->count(),
        ];

        $assignableOfficersQuery = \App\Models\User::query()
            ->whereHas('role', function ($q) {
                $q->whereIn('name', ['Marketing', 'Customer Service', 'Koordinator Marketing', 'Koordinator Customer Service']);
            })
            ->active();

        if ($activeCampus) {
            $assignableOfficersQuery->where(function ($q) use ($activeCampus) {
                $q->where('campus_id', $activeCampus->id)->orWhereNull('campus_id');
            });
        }

        $assignableOfficers = $assignableOfficersQuery
            ->orderBy('name')
            ->withCount([
                'assignedStudentQuestionnaires as total_assigned_count',
                'assignedStudentQuestionnaires as pending_count' => function ($q) {
                    $q->where('followup_status', StudentQuestionnaire::STATUS_BELUM_DIHUBUNGI);
                }
            ])
            ->get(['id', 'name', 'email', 'role_id']);

        $schoolsQuery = School::query()
            ->select('id', 'name', 'district_id', 'campus_id', 'school_type_id')
            ->with(['district:id,name', 'campus', 'schoolType']);

        if ($activeCampus) {
            $schoolsQuery->where('campus_id', $activeCampus->id);
        }

        $schools = $schoolsQuery->orderBy('name')->get();

        return view('event-students.index', compact('students', 'events', 'metrics', 'canPlot', 'assignableOfficers', 'activeCampus', 'schools'));
    }

    /**
     * Download Excel template for manual event student leads import.
     */
    public function downloadTemplate(\App\Services\EventStudentExportImportService $service)
    {
        return $service->downloadTemplate();
    }

    /**
     * Import manual event student leads from uploaded Excel file.
     */
    public function import(Request $request, \App\Services\EventStudentExportImportService $service): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [
            'file.required' => 'Pilih file Excel yang ingin diimpor.',
            'file.mimes' => 'Format file harus berupa Excel (.xlsx, .xls) atau .csv.',
            'file.max' => 'Ukuran file maksimal adalah 10 MB.',
        ]);

        $result = $service->import($request->file('file'));

        if (!$result['status']) {
            return back()->with('error', $result['message'])
                ->with('import_errors', $result['errors'] ?? []);
        }

        return back()->with('success', $result['message'])
            ->with('import_errors', $result['errors'] ?? []);
    }

    /**
     * Export Hasil Support Event data to Excel (.xlsx).
     */
    public function export(Request $request, \App\Services\EventStudentExportImportService $service)
    {
        return $service->export($request);
    }

    /**
     * Export Hasil Support Event data to Printable PDF View.
     */
    public function exportPdf(Request $request, \App\Services\EventStudentExportImportService $service)
    {
        return $service->exportPdf($request);
    }

    /**
     * Export Hasil Support Event data to PowerPoint (.pptx).
     */
    public function exportPptx(Request $request, \App\Services\EventStudentExportImportService $service)
    {
        return $service->exportPptx($request);
    }

    /**
     * Update data siswa hasil support event (Koreksi salah ketik/typo oleh Managerial).
     */
    public function update(Request $request, StudentQuestionnaire $studentQuestionnaire): RedirectResponse|JsonResponse
    {
        $this->authorizeCampusAccess($studentQuestionnaire);

        $user = auth()->user();
        if (!$user || !$user->hasFullMenuAccess()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak. Anda tidak berhak mengubah data siswa event ini.'], 403);
            }
            abort(403, 'Akses ditolak. Anda tidak berhak mengubah data siswa event ini.');
        }

        $validated = $request->validate([
            'student_name' => ['required', 'string', 'max:150'],
            'gender' => ['nullable', 'string', 'in:L,P,laki-laki,perempuan,Laki-laki,Perempuan'],
            'school_id' => ['nullable'],
            'school_name' => ['nullable', 'string', 'max:150'],
            'student_class' => ['required', 'string', 'max:50'],
            'student_major' => ['required', 'string', 'max:100'],
            'student_phone' => ['required', 'string', 'max:30'],
            'student_email' => ['nullable', 'email', 'max:150'],
            'student_social_media' => ['nullable', 'string', 'max:255'],
            'instagram_handle' => ['nullable', 'string', 'max:150'],
            'tiktok_handle' => ['nullable', 'string', 'max:150'],
            'guardian_name' => ['nullable', 'string', 'max:150'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
            'guardian_social_media' => ['nullable', 'string', 'max:255'],
            'scholarship_interest' => ['required', 'in:ya,tidak'],
            'school_event_proposal_id' => ['nullable', 'exists:school_event_proposals,id'],
            'event_name_other' => ['nullable', 'string', 'max:200'],
            'followup_status' => ['nullable', 'in:belum_dihubungi,no_respon,respon,berminat,daftar_aplikasi,closing'],
            'followup_notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'student_name.required' => 'Nama lengkap siswa wajib diisi.',
            'student_class.required' => 'Kelas wajib diisi.',
            'student_major.required' => 'Jurusan wajib diisi.',
            'student_phone.required' => 'Nomor HP / WhatsApp wajib diisi.',
            'scholarship_interest.required' => 'Minat beasiswa wajib dipilih.',
        ]);

        $schoolId = null;
        $schoolName = trim((string) ($request->input('school_name') ?? ''));

        if (!empty($validated['school_id']) && $validated['school_id'] !== 'other') {
            $school = School::find($validated['school_id']);
            if ($school) {
                $schoolId = $school->id;
                $schoolName = $school->name;
            }
        }

        $eventTitle = null;
        $newEventId = $validated['school_event_proposal_id'] ?? null;
        if ($newEventId) {
            $event = SchoolEventProposal::find($newEventId);
            if ($event) {
                $eventTitle = $event->event_name;
            }
        } elseif (!empty($validated['event_name_other'])) {
            $eventTitle = trim($validated['event_name_other']);
        }

        $collegeNotes = $eventTitle
            ? "Hasil Support Event: {$eventTitle}"
            : $studentQuestionnaire->college_interest_notes;

        $scholarshipNotes = ($validated['scholarship_interest'] === 'ya')
            ? 'Berminat Program Beasiswa UBSI'
            : 'Non-Beasiswa / Jalur Mandiri';

        $gender = $request->filled('gender')
            ? (strtoupper(substr($request->gender, 0, 1)) === 'P' ? 'P' : 'L')
            : $studentQuestionnaire->gender;

        $dataToUpdate = [
            'student_name' => $validated['student_name'],
            'gender' => $gender,
            'school_id' => $schoolId,
            'school_name' => $schoolName ?: $studentQuestionnaire->school_name,
            'student_class' => $validated['student_class'],
            'student_major' => $validated['student_major'],
            'student_phone' => $validated['student_phone'],
            'student_email' => $validated['student_email'] ?? null,
            'student_social_media' => $validated['student_social_media'] ?? $studentQuestionnaire->student_social_media,
            'instagram_handle' => $validated['instagram_handle'] ?? $studentQuestionnaire->instagram_handle,
            'tiktok_handle' => $validated['tiktok_handle'] ?? $studentQuestionnaire->tiktok_handle,
            'guardian_name' => $validated['guardian_name'] ?? null,
            'guardian_phone' => $validated['guardian_phone'] ?? null,
            'guardian_social_media' => $validated['guardian_social_media'] ?? $studentQuestionnaire->guardian_social_media,
            'scholarship_interest' => $validated['scholarship_interest'],
            'scholarship_notes' => $scholarshipNotes,
            'school_event_proposal_id' => $newEventId,
        ];

        if ($collegeNotes) {
            $dataToUpdate['college_interest_notes'] = $collegeNotes;
        }

        if (!empty($validated['followup_status'])) {
            $statusVal = ($validated['followup_status'] === 'closing') ? StudentQuestionnaire::STATUS_DAFTAR_APLIKASI : $validated['followup_status'];
            $dataToUpdate['followup_status'] = $statusVal;
        }

        if (array_key_exists('followup_notes', $validated)) {
            $dataToUpdate['followup_notes'] = $validated['followup_notes'];
        }

        $studentQuestionnaire->update($dataToUpdate);

        // Auto sync ke CRM Prospect jika sudah daftar aplikasi atau sudah ada record prospek
        if (in_array($studentQuestionnaire->followup_status, [StudentQuestionnaire::STATUS_DAFTAR_APLIKASI, 'closing', 'daftar_aplikasi'])) {
            \App\Services\ProspectSyncService::syncStudentQuestionnaire($studentQuestionnaire->fresh());
        } elseif ($studentQuestionnaire->prospect) {
            $studentQuestionnaire->prospect->update([
                'name' => $validated['student_name'],
                'gender' => $studentQuestionnaire->gender ?: $studentQuestionnaire->prospect->gender,
                'phone' => $validated['student_phone'],
                'email' => $validated['student_email'] ?? $studentQuestionnaire->prospect->email,
                'school_id' => $schoolId ?: $studentQuestionnaire->prospect->school_id,
            ]);
        }

        \App\Services\ActivityLogService::log(
            'UPDATE',
            'event_student',
            $studentQuestionnaire->id,
            "Memperbarui data siswa event: {$studentQuestionnaire->student_name} ({$schoolName})",
            null,
            ['student_name' => $studentQuestionnaire->student_name, 'school' => $schoolName]
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Data siswa event {$studentQuestionnaire->student_name} berhasil diperbarui.",
                'student' => $studentQuestionnaire->fresh(['school', 'eventProposal']),
            ]);
        }

        return redirect()->route('event-students.index')
            ->with('success', "Data siswa event {$studentQuestionnaire->student_name} berhasil diperbarui.");
    }

    /**
     * Hapus data siswa hasil support event secara langsung oleh Managerial.
     */
    public function destroy(Request $request, StudentQuestionnaire $studentQuestionnaire): RedirectResponse|JsonResponse
    {
        $this->authorizeCampusAccess($studentQuestionnaire);

        $user = auth()->user();
        if (!$user || !$user->hasFullMenuAccess()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak. Anda tidak berhak menghapus data siswa event ini.'], 403);
            }
            abort(403, 'Akses ditolak. Anda tidak berhak menghapus data siswa event ini.');
        }

        $studentName = $studentQuestionnaire->student_name;

        // Kurangi counter prospects_acquired pada proposal event jika ada
        if ($studentQuestionnaire->school_event_proposal_id && $studentQuestionnaire->eventProposal && $studentQuestionnaire->eventProposal->prospects_acquired > 0) {
            $studentQuestionnaire->eventProposal->decrement('prospects_acquired');
        }

        // Hapus prospek CRM jika ada
        if ($studentQuestionnaire->prospect) {
            $studentQuestionnaire->prospect->delete();
        }

        // Hapus riwayat interaksi follow up
        $studentQuestionnaire->followUps()->delete();

        $studentQuestionnaire->delete();

        \App\Services\ActivityLogService::log(
            'DELETE',
            'event_student',
            $studentQuestionnaire->id,
            "Menghapus data siswa event: {$studentName}",
            ['student_name' => $studentName]
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Data siswa event {$studentName} berhasil dihapus.",
            ]);
        }

        return redirect()->route('event-students.index')
            ->with('success', "Data siswa event {$studentName} berhasil dihapus.");
    }

    /**
     * Pastikan pengguna hanya mengakses data siswa event di wilayah kampusnya.
     */
    protected function authorizeCampusAccess(StudentQuestionnaire $sq): void
    {
        $user = auth()->user();
        if ($user && !\App\Services\AppContextService::canSwitchCampus($user)) {
            $userCampusId = $user->campus_id;
            $sqCampusId = $sq->campus_id ?: ($sq->school?->campus_id ?: $sq->eventProposal?->campus_id);
            if ($sqCampusId && $userCampusId && (int)$sqCampusId !== (int)$userCampusId) {
                abort(403, 'Akses ditolak. Data siswa event ini berada di luar wilayah kampus penugasan Anda.');
            }
        }
    }
}
