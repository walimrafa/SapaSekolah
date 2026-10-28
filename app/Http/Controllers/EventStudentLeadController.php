<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SchoolEventProposal;
use App\Models\StudentQuestionnaire;
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
        $schools = School::query()
            ->select('id', 'name', 'district_id')
            ->with('district:id,name')
            ->orderBy('name')
            ->get();

        $events = SchoolEventProposal::where('status', SchoolEventProposal::STATUS_DISETUJUI)
            ->orderByDesc('event_date')
            ->get(['id', 'event_name', 'school_name', 'school_id', 'event_date', 'event_location']);

        $selectedEventId = $request->query('event_id');
        $selectedEvent = $selectedEventId ? SchoolEventProposal::find($selectedEventId) : null;
        $selectedSchoolId = $request->query('school_id') ?? ($selectedEvent ? $selectedEvent->school_id : null);

        return view('public-events.student-form', compact('schools', 'events', 'selectedEvent', 'selectedEventId', 'selectedSchoolId'));
    }

    /**
     * Simpan Data Pendaftaran Siswa dari Event (Tersinkronisasi Langsung ke Follow Up Siswa).
     */
    public function publicStore(Request $request): RedirectResponse
    {
        $request->validate([
            'student_name' => 'required|string|max:150',
            'school_id' => 'required',
            'school_name' => 'nullable|string|max:150',
            'student_class' => 'required|string|max:50',
            'student_major' => 'required|string|max:100',
            'student_phone' => 'required|string|max:30',
            'student_email' => 'required|email|max:150',
            'student_social_media' => 'nullable|string|max:255',
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

        // Simpan ke database student_questionnaires agar tersinkronisasi otomatis ke CRM Follow Up Siswa
        $lead = StudentQuestionnaire::create([
            'school_id' => $schoolId,
            'school_event_proposal_id' => $request->school_event_proposal_id,
            'school_name' => $schoolName,
            'student_name' => $request->student_name,
            'student_class' => $request->student_class,
            'student_major' => $request->student_major,
            'student_phone' => $request->student_phone,
            'student_email' => $request->student_email,
            'student_social_media' => $request->student_social_media,
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

        $query = StudentQuestionnaire::query()
            ->where(function ($q) {
                $q->where('source', 'support_event')
                  ->orWhereNotNull('school_event_proposal_id')
                  ->orWhere('college_interest_notes', 'like', '%Support Event%');
            })
            ->with(['school.district', 'eventProposal', 'assignedUser', 'assignedBy', 'lastFollowedUpBy', 'followUps'])
            ->latest();

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

        $events = SchoolEventProposal::where('status', SchoolEventProposal::STATUS_DISETUJUI)
            ->orderByDesc('event_date')
            ->get(['id', 'event_name', 'school_name', 'event_date']);

        $baseCount = StudentQuestionnaire::where(function ($q) {
            $q->where('source', 'support_event')
              ->orWhereNotNull('school_event_proposal_id')
              ->orWhere('college_interest_notes', 'like', '%Support Event%');
        });

        $metrics = [
            'total' => (clone $baseCount)->count(),
            'scholarship_yes' => (clone $baseCount)->where('scholarship_interest', 'ya')->count(),
            'scholarship_no' => (clone $baseCount)->where('scholarship_interest', 'tidak')->count(),
            'belum_dihubungi' => (clone $baseCount)->where('followup_status', StudentQuestionnaire::STATUS_BELUM_DIHUBUNGI)->count(),
            'terfollowup' => (clone $baseCount)->where('followup_status', '!=', StudentQuestionnaire::STATUS_BELUM_DIHUBUNGI)->count(),
            'unassigned' => (clone $baseCount)->whereNull('assigned_to')->count(),
            'assigned' => (clone $baseCount)->whereNotNull('assigned_to')->count(),
        ];

        $assignableOfficers = \App\Models\User::query()
            ->whereHas('role', function ($q) {
                $q->whereIn('name', ['Marketing', 'Customer Service', 'Koordinator Marketing']);
            })
            ->active()
            ->orderBy('name')
            ->withCount([
                'assignedStudentQuestionnaires as total_assigned_count',
                'assignedStudentQuestionnaires as pending_count' => function ($q) {
                    $q->where('followup_status', StudentQuestionnaire::STATUS_BELUM_DIHUBUNGI);
                }
            ])
            ->get(['id', 'name', 'email', 'role_id']);

        return view('event-students.index', compact('students', 'events', 'metrics', 'canPlot', 'assignableOfficers'));
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
}
