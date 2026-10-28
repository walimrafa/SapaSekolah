<?php

namespace App\Http\Controllers;

use App\Models\StudentFollowUp;
use App\Models\StudentQuestionnaire;
use App\Models\User;
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
use App\Services\StudentQuestionnaireImportService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentFollowUpController extends Controller
{
    private const QUESTION_SECTIONS = [
        'A. Motivasi' => [
            'motivation_job' => 'Saya ingin kuliah untuk meningkatkan peluang memperoleh pekerjaan yang baik.',
            'motivation_interest' => 'Saya ingin memilih program studi yang sesuai dengan minat dan bakat saya.',
            'motivation_parent' => 'Harapan orang tua menjadi alasan penting bagi saya untuk kuliah.',
        ],
        'B. Pencarian informasi' => [
            'information_social_media' => 'Saya mencari informasi kampus melalui media sosial sebelum menentukan pilihan.',
            'information_testimonials' => 'Testimoni mahasiswa atau alumni memengaruhi ketertarikan saya terhadap kampus.',
            'information_comparison' => 'Saya membandingkan informasi dari beberapa kampus sebelum mengambil keputusan.',
        ],
        'C. Pengaruh sosial' => [
            'social_parent' => 'Pendapat orang tua sangat memengaruhi pilihan kampus saya.',
            'social_friends' => 'Pilihan teman memengaruhi pertimbangan saya dalam memilih kampus.',
            'social_teacher' => 'Rekomendasi guru atau alumni meningkatkan kepercayaan saya terhadap kampus.',
        ],
        'D. Preferensi kampus' => [
            'campus_cost' => 'Biaya kuliah yang terjangkau merupakan faktor utama bagi saya.',
            'campus_reputation' => 'Akreditasi dan reputasi kampus sangat penting dalam keputusan saya.',
            'campus_career' => 'Peluang kerja, magang, dan keterampilan praktis menjadi pertimbangan utama saya.',
            'campus_location' => 'Lokasi kampus dan kemudahan transportasi memengaruhi pilihan saya.',
        ],
        'E. Niat dan hambatan' => [
            'intention_ubsi' => 'Saya mempertimbangkan UBSI sebagai salah satu pilihan kuliah.',
            'intention_information' => 'Saya berniat mencari informasi PMB UBSI lebih lanjut.',
            'barrier_cost' => 'Kendala biaya dapat membuat saya menunda atau membatalkan rencana kuliah.',
        ],
    ];

    public function index(Request $request): View
    {
        $user = auth()->user();
        $canPlot = $user && $user->canPlotFollowUps();

        $query = StudentQuestionnaire::query()
            ->with(['lastFollowedUpBy', 'assignedUser', 'assignedBy', 'followUps.user', 'school', 'eventProposal'])
            ->withCount('followUps');

        // Pembatasan hak akses: Staf Marketing murni hanya melihat data follow up yang di-ploting ke dirinya
        if ($user && $user->isMarketing() && !$canPlot) {
            $query->where('assigned_to', $user->id);
        } elseif ($request->filled('assigned_to') && $request->assigned_to !== 'all') {
            if ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->assigned_to);
            }
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                    ->orWhere('student_phone', 'like', "%{$search}%")
                    ->orWhere('student_email', 'like', "%{$search}%")
                    ->orWhere('guardian_name', 'like', "%{$search}%")
                    ->orWhere('guardian_phone', 'like', "%{$search}%")
                    ->orWhere('student_class', 'like', "%{$search}%")
                    ->orWhere('student_major', 'like', "%{$search}%")
                    ->orWhere('school_name', 'like', "%{$search}%")
                    ->orWhereHas('school', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('assignedUser', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Status Criteria Filter (Option Report criteria: no_respon, respon, berminat, daftar_aplikasi, closing, belum_dihubungi)
        if ($request->filled('status') && $request->status !== 'all') {
            if (in_array($request->status, ['daftar_aplikasi', 'closing'])) {
                $query->whereIn('followup_status', [StudentQuestionnaire::STATUS_DAFTAR_APLIKASI, 'closing']);
            } else {
                $query->where('followup_status', $request->status);
            }
        }

        // Scholarship Interest Filter (Khususnya minat beasiswa)
        if ($request->filled('scholarship') && $request->scholarship !== 'all') {
            $query->where('scholarship_interest', $request->scholarship);
        }

        // Class and Major Filter
        if ($request->filled('class') && $request->class !== 'all') {
            $query->where('student_class', $request->class);
        }

        if ($request->filled('major') && $request->major !== 'all') {
            $query->where('student_major', $request->major);
        }

        // Filter Minat Program Studi (Tersinkronisasi dengan Master Prodi)
        if ($request->filled('interest_program') && $request->interest_program !== 'all') {
            $query->where('college_interest_notes', 'like', "%{$request->interest_program}%");
        }

        // Filter Sumber Data (Kuisioner vs Support Event)
        if ($request->filled('source') && $request->source !== 'all') {
            if ($request->source === 'support_event') {
                $query->where(function ($q) {
                    $q->where('source', 'support_event')
                      ->orWhereNotNull('school_event_proposal_id')
                      ->orWhere('college_interest_notes', 'like', '%Support Event%');
                });
            } else {
                $query->where('source', $request->source);
            }
        }

        // Summary metrics & criteria statistics
        $allTotal = StudentQuestionnaire::count();
        $scholarshipYesCount = StudentQuestionnaire::where('scholarship_interest', 'ya')->count();
        $scholarshipNoCount = StudentQuestionnaire::where('scholarship_interest', 'tidak')->count();

        $daftarAplikasiCount = StudentQuestionnaire::whereIn('followup_status', [StudentQuestionnaire::STATUS_DAFTAR_APLIKASI, 'closing'])->count();

        $statusCounts = [
            'belum_dihubungi' => StudentQuestionnaire::where('followup_status', StudentQuestionnaire::STATUS_BELUM_DIHUBUNGI)->count(),
            'no_respon' => StudentQuestionnaire::where('followup_status', StudentQuestionnaire::STATUS_NO_RESPON)->count(),
            'respon' => StudentQuestionnaire::where('followup_status', StudentQuestionnaire::STATUS_RESPON)->count(),
            'berminat' => StudentQuestionnaire::where('followup_status', StudentQuestionnaire::STATUS_BERMINAT)->count(),
            'daftar_aplikasi' => $daftarAplikasiCount,
            'closing' => $daftarAplikasiCount, // fallback
        ];

        $scholarshipDaftarAplikasiCount = StudentQuestionnaire::where('scholarship_interest', 'ya')
            ->whereIn('followup_status', [StudentQuestionnaire::STATUS_DAFTAR_APLIKASI, 'closing'])
            ->count();

        // Specific scholarship breakdown per criteria
        $scholarshipStatusCounts = [
            'belum_dihubungi' => StudentQuestionnaire::where('scholarship_interest', 'ya')->where('followup_status', StudentQuestionnaire::STATUS_BELUM_DIHUBUNGI)->count(),
            'no_respon' => StudentQuestionnaire::where('scholarship_interest', 'ya')->where('followup_status', StudentQuestionnaire::STATUS_NO_RESPON)->count(),
            'respon' => StudentQuestionnaire::where('scholarship_interest', 'ya')->where('followup_status', StudentQuestionnaire::STATUS_RESPON)->count(),
            'berminat' => StudentQuestionnaire::where('scholarship_interest', 'ya')->where('followup_status', StudentQuestionnaire::STATUS_BERMINAT)->count(),
            'daftar_aplikasi' => $scholarshipDaftarAplikasiCount,
            'closing' => $scholarshipDaftarAplikasiCount,
        ];

        $totalContacted = $statusCounts['no_respon'] + $statusCounts['respon'] + $statusCounts['berminat'] + $daftarAplikasiCount;
        $conversionRate = $totalContacted > 0 ? round(($daftarAplikasiCount / $totalContacted) * 100, 1) : 0;
        $interestedRate = $totalContacted > 0 ? round((($statusCounts['berminat'] + $daftarAplikasiCount) / $totalContacted) * 100, 1) : 0;

        // Metrik Ploting Pembagian Data untuk Pimpinan & Koordinator
        $totalUnassigned = StudentQuestionnaire::whereNull('assigned_to')->count();
        $totalAssigned = StudentQuestionnaire::whereNotNull('assigned_to')->count();
        $unassignedKuisioner = StudentQuestionnaire::whereNull('assigned_to')->where('source', 'kuisioner')->count();
        $unassignedSupportEvent = StudentQuestionnaire::whereNull('assigned_to')
            ->where(function ($q) {
                $q->where('source', 'support_event')
                  ->orWhereNotNull('school_event_proposal_id')
                  ->orWhere('college_interest_notes', 'like', '%Support Event%');
            })->count();

        $assignableOfficers = User::query()
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

        $classes = StudentQuestionnaire::query()->distinct()->whereNotNull('student_class')->orderBy('student_class')->pluck('student_class');
        $majors = StudentQuestionnaire::query()->distinct()->whereNotNull('student_major')->orderBy('student_major')->pluck('student_major');
        $studyPrograms = \App\Models\StudyProgram::where('status', 1)->orderBy('name')->get();

        $students = $query->latest('id')->paginate(15)->withQueryString();

        return view('student-follow-ups.index', [
            'students' => $students,
            'classes' => $classes,
            'majors' => $majors,
            'studyPrograms' => $studyPrograms,
            'canPlot' => $canPlot,
            'assignableOfficers' => $assignableOfficers,
            'plottingMetrics' => [
                'total_unassigned' => $totalUnassigned,
                'total_assigned' => $totalAssigned,
                'unassigned_kuisioner' => $unassignedKuisioner,
                'unassigned_event' => $unassignedSupportEvent,
            ],
            'metrics' => [
                'total' => $allTotal,
                'scholarship_yes' => $scholarshipYesCount,
                'scholarship_no' => $scholarshipNoCount,
                'total_contacted' => $totalContacted,
                'conversion_rate' => $conversionRate,
                'interested_rate' => $interestedRate,
                'status_counts' => $statusCounts,
                'scholarship_status_counts' => $scholarshipStatusCounts,
            ],
            'statusOptions' => StudentQuestionnaire::followupStatusOptions(),
            'contactMethods' => StudentFollowUp::contactMethods(),
        ]);
    }

    public function store(Request $request, StudentQuestionnaire $studentQuestionnaire): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:no_respon,respon,berminat,daftar_aplikasi,closing,belum_dihubungi'],
            'contact_method' => ['required', 'in:wa,telepon,email,tatap_muka,lainnya'],
            'followup_date' => ['required', 'date'],
            'scholarship_interest_notes' => ['nullable', 'string', 'max:500'],
            'college_interest_notes' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'next_followup_date' => ['nullable', 'date'],
        ]);

        $statusToSave = ($validated['status'] === 'closing') 
            ? StudentQuestionnaire::STATUS_DAFTAR_APLIKASI 
            : $validated['status'];

        $followUp = StudentFollowUp::create([
            'student_questionnaire_id' => $studentQuestionnaire->id,
            'user_id' => auth()->id(),
            'followup_date' => $validated['followup_date'],
            'contact_method' => $validated['contact_method'],
            'status' => $statusToSave,
            'scholarship_interest_notes' => $validated['scholarship_interest_notes'] ?? null,
            'college_interest_notes' => $validated['college_interest_notes'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'next_followup_date' => $validated['next_followup_date'] ?? null,
        ]);

        // Determine scholarship_interest (ya / tidak) based on program selection
        $scholarshipInterestValue = $studentQuestionnaire->scholarship_interest;
        if (!empty($validated['scholarship_interest_notes'])) {
            $notesLower = strtolower($validated['scholarship_interest_notes']);
            if (str_contains($notesLower, 'non beasiswa') || str_contains($notesLower, 'mandiri')) {
                $scholarshipInterestValue = 'tidak';
            } elseif (str_contains($notesLower, 'beasiswa') || str_contains($notesLower, 'kip') || str_contains($notesLower, 'prestasi')) {
                $scholarshipInterestValue = 'ya';
            }
        }

        // Update student questionnaire record
        $studentQuestionnaire->update([
            'followup_status' => $statusToSave,
            'last_followup_at' => now(),
            'last_followed_up_by' => auth()->id(),
            'scholarship_interest' => $scholarshipInterestValue,
            'followup_notes' => $validated['notes'] ?? $studentQuestionnaire->followup_notes,
            'scholarship_notes' => $validated['scholarship_interest_notes'] ?? $studentQuestionnaire->scholarship_notes,
            'college_interest_notes' => $validated['college_interest_notes'] ?? $studentQuestionnaire->college_interest_notes,
        ]);

        // Auto-sync into Data Prospek (prospects table) when status is 'daftar_aplikasi'
        $prospect = null;
        $isDaftarAplikasi = ($statusToSave === StudentQuestionnaire::STATUS_DAFTAR_APLIKASI);

        if ($isDaftarAplikasi) {
            $noteParts = [];
            if (!empty($validated['notes'])) {
                $noteParts[] = "Catatan Follow Up: " . $validated['notes'];
            }
            if (!empty($validated['college_interest_notes'])) {
                $noteParts[] = "Minat Prodi: " . $validated['college_interest_notes'];
            }
            if (!empty($validated['scholarship_interest_notes'])) {
                $noteParts[] = "Minat Beasiswa: " . $validated['scholarship_interest_notes'];
            }
            if ($studentQuestionnaire->guardian_name || $studentQuestionnaire->guardian_phone) {
                $noteParts[] = "Wali/Ortu: " . ($studentQuestionnaire->guardian_name ?? '-') . " (WA: " . ($studentQuestionnaire->guardian_phone ?? '-') . ")";
            }
            $noteText = implode(" | ", $noteParts);

            $interestProgram = $validated['college_interest_notes'] 
                ?? $studentQuestionnaire->college_interest_notes 
                ?? 'S1 Sistem Informasi';

            // Find matching school if any, or default school
            $schoolId = $studentQuestionnaire->school_id ?? \App\Models\School::first()?->id;

            // Search existing prospect by phone, email, or name
            if ($studentQuestionnaire->student_phone) {
                $prospect = \App\Models\Prospect::where('phone', $studentQuestionnaire->student_phone)->first();
            }
            if (!$prospect && $studentQuestionnaire->student_email) {
                $prospect = \App\Models\Prospect::where('email', $studentQuestionnaire->student_email)->first();
            }
            if (!$prospect) {
                $prospect = \App\Models\Prospect::where('name', $studentQuestionnaire->student_name)->first();
            }

            // Determine scholarship type category
            $scholarshipType = $validated['scholarship_interest_notes'] 
                ?? $studentQuestionnaire->scholarship_notes;
            if (!$scholarshipType) {
                $scholarshipType = ($studentQuestionnaire->scholarship_interest === 'ya') 
                    ? 'Beasiswa KIP-Kuliah' 
                    : 'Non Beasiswa (Reguler Mandiri)';
            }

            // Determine study_time and clean prodi name from interestProgram
            $studyTime = null;
            if ($interestProgram) {
                if (str_contains($interestProgram, ' - ')) {
                    $parts = explode(' - ', $interestProgram);
                    $potentialName = trim($parts[0]);
                    $matchedProdi = \App\Models\StudyProgram::where('name', $potentialName)
                        ->orWhere('code', $potentialName)
                        ->first();
                    if ($matchedProdi) {
                        $interestProgram = $matchedProdi->name;
                        $lowerSuffix = strtolower($interestProgram . ' ' . $validated['college_interest_notes']);
                        if (str_contains($lowerSuffix, 'karyawan') || str_contains($lowerSuffix, 'sabtu')) {
                            $studyTime = \App\Models\StudyProgram::TIME_KARYAWAN;
                        } elseif (str_contains($lowerSuffix, 'malam') || str_contains($lowerSuffix, 'sore')) {
                            $studyTime = \App\Models\StudyProgram::TIME_REGULER_MALAM;
                        } else {
                            $studyTime = \App\Models\StudyProgram::TIME_REGULER_PAGI;
                        }
                    }
                } else {
                    $matchedProdi = \App\Models\StudyProgram::where('name', $interestProgram)->first();
                    if ($matchedProdi && !empty($matchedProdi->study_times)) {
                        $studyTime = $matchedProdi->study_times[0];
                    }
                }
            }

            $prospectSource = ($studentQuestionnaire->source === 'support_event' || $studentQuestionnaire->school_event_proposal_id)
                ? 'Support Event Sekolah (Daftar Aplikasi)'
                : 'Kuisioner Siswa (Daftar Aplikasi)';

            if ($prospect) {
                $prospect->update([
                    'status' => \App\Models\Prospect::STATUS_DAFTAR_AKUN,
                    'source' => $prospectSource,
                    'interest_program' => $interestProgram ?: $prospect->interest_program,
                    'study_time' => $prospect->study_time ?: $studyTime,
                    'scholarship_type' => $scholarshipType ?: $prospect->scholarship_type,
                    'marketing_user_id' => auth()->id() ?? $prospect->marketing_user_id,
                    'notes' => trim(($prospect->notes ? $prospect->notes . "\n---\n" : '') . $noteText),
                ]);
            } else {
                $prospect = \App\Models\Prospect::create([
                    'name' => $studentQuestionnaire->student_name,
                    'phone' => $studentQuestionnaire->student_phone,
                    'email' => $studentQuestionnaire->student_email,
                    'school_id' => $schoolId,
                    'graduation_year' => (int) date('Y'),
                    'interest_program' => $interestProgram,
                    'study_time' => $studyTime,
                    'scholarship_type' => $scholarshipType,
                    'source' => $prospectSource,
                    'status' => \App\Models\Prospect::STATUS_DAFTAR_AKUN,
                    'marketing_user_id' => auth()->id(),
                    'notes' => $noteText,
                ]);
            }
        }

        $redirectUrl = route('prospects.index', [
            'search' => $studentQuestionnaire->student_name,
            'status' => \App\Models\Prospect::STATUS_DAFTAR_AKUN
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $isDaftarAplikasi 
                    ? "Status berhasil diubah ke 'Daftar Aplikasi' dan otomatis tersinkron ke Data Prospek!" 
                    : 'Hasil follow up siswa berhasil disimpan!',
                'follow_up' => $followUp->load('user'),
                'student' => $studentQuestionnaire->fresh(['lastFollowedUpBy']),
                'status_label' => $studentQuestionnaire->fresh()->followup_status_label,
                'badge_class' => $studentQuestionnaire->fresh()->followup_badge_class,
                'is_daftar_aplikasi' => $isDaftarAplikasi,
                'redirect_url' => $redirectUrl,
                'prospect_id' => $prospect?->id,
            ]);
        }

        if ($isDaftarAplikasi) {
            return redirect($redirectUrl)->with('toast', [
                'type' => 'success',
                'message' => "Siswa {$studentQuestionnaire->student_name} berhasil berstatus Daftar Aplikasi dan otomatis masuk ke Data Prospek untuk follow up intensif!",
            ]);
        }

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'message' => 'Hasil follow up untuk ' . $studentQuestionnaire->student_name . ' berhasil dicatat!',
        ]);
    }

    public function history(StudentQuestionnaire $studentQuestionnaire): JsonResponse
    {
        $studentQuestionnaire->load(['followUps.user', 'lastFollowedUpBy']);

        $questions = [];
        foreach (self::QUESTION_SECTIONS as $section => $qList) {
            foreach ($qList as $key => $text) {
                $questions[$key] = [
                    'section' => $section,
                    'text' => $text,
                    'score' => $studentQuestionnaire->answers[$key] ?? null,
                ];
            }
        }

        return response()->json([
            'student' => [
                'id' => $studentQuestionnaire->id,
                'name' => $studentQuestionnaire->student_name,
                'class' => $studentQuestionnaire->student_class,
                'major' => $studentQuestionnaire->student_major,
                'phone' => $studentQuestionnaire->student_phone,
                'formatted_phone' => $studentQuestionnaire->formatted_student_phone,
                'email' => $studentQuestionnaire->student_email,
                'social_media' => $studentQuestionnaire->student_social_media,
                'guardian_name' => $studentQuestionnaire->guardian_name,
                'guardian_phone' => $studentQuestionnaire->guardian_phone,
                'formatted_guardian_phone' => $studentQuestionnaire->formatted_guardian_phone,
                'guardian_social_media' => $studentQuestionnaire->guardian_social_media,
                'scholarship_interest' => $studentQuestionnaire->scholarship_interest,
                'followup_status' => $studentQuestionnaire->followup_status,
                'followup_status_label' => $studentQuestionnaire->followup_status_label,
                'badge_class' => $studentQuestionnaire->followup_badge_class,
                'scholarship_notes' => $studentQuestionnaire->scholarship_notes,
                'college_interest_notes' => $studentQuestionnaire->college_interest_notes,
                'followup_notes' => $studentQuestionnaire->followup_notes,
                'last_followup_at' => $studentQuestionnaire->last_followup_at?->format('d/m/Y H:i'),
                'last_officer' => $studentQuestionnaire->lastFollowedUpBy?->name ?? '-',
                'created_at' => $studentQuestionnaire->created_at->format('d/m/Y H:i'),
            ],
            'questions' => $questions,
            'follow_ups' => $studentQuestionnaire->followUps->map(function ($fu) {
                return [
                    'id' => $fu->id,
                    'date' => $fu->followup_date->format('d/m/Y'),
                    'method' => StudentFollowUp::contactMethods()[$fu->contact_method] ?? $fu->contact_method,
                    'status' => $fu->status,
                    'status_label' => $fu->status_label,
                    'badge_class' => $fu->status_badge_class,
                    'scholarship_interest_notes' => $fu->scholarship_interest_notes,
                    'college_interest_notes' => $fu->college_interest_notes,
                    'notes' => $fu->notes,
                    'next_date' => $fu->next_followup_date?->format('d/m/Y'),
                    'officer' => $fu->user?->name ?? 'Admin',
                    'created_at' => $fu->created_at->format('d/m/Y H:i'),
                ];
            }),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $query = StudentQuestionnaire::query()->with(['lastFollowedUpBy']);

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                    ->orWhere('student_phone', 'like', "%{$search}%")
                    ->orWhere('student_email', 'like', "%{$search}%")
                    ->orWhere('guardian_name', 'like', "%{$search}%")
                    ->orWhere('guardian_phone', 'like', "%{$search}%")
                    ->orWhere('student_class', 'like', "%{$search}%")
                    ->orWhere('student_major', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            if (in_array($request->status, ['daftar_aplikasi', 'closing'])) {
                $query->whereIn('followup_status', [StudentQuestionnaire::STATUS_DAFTAR_APLIKASI, 'closing']);
            } else {
                $query->where('followup_status', $request->status);
            }
        }

        if ($request->filled('scholarship') && $request->scholarship !== 'all') {
            $query->where('scholarship_interest', $request->scholarship);
        }

        if ($request->filled('class') && $request->class !== 'all') {
            $query->where('student_class', $request->class);
        }

        if ($request->filled('major') && $request->major !== 'all') {
            $query->where('student_major', $request->major);
        }

        $records = $query->latest('id')->get();

        return response()->streamDownload(function () use ($records) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Data Follow Up Siswa');

            // Sheet 1: Detailed follow up data
            $headers = [
                'Waktu Kuisioner',
                'Nama Lengkap Siswa',
                'Asal Sekolah',
                'Kelas',
                'Jurusan',
                'No. HP Siswa',
                'Email Siswa',
                'Nama Wali',
                'No. HP Wali',
                'Minat Beasiswa Awal',
                'Kriteria Status Follow Up',
                'Tanggal Follow Up Terakhir',
                'Petugas Follow Up',
                'Catatan Minat Beasiswa',
                'Catatan Minat Kuliah / Prodi',
                'Catatan Interaksi & Hasil',
            ];

            foreach ($headers as $index => $header) {
                $coord = Coordinate::stringFromColumnIndex($index + 1) . '1';
                $sheet->setCellValueExplicit($coord, $header, DataType::TYPE_STRING);
            }

            $highestCol = Coordinate::stringFromColumnIndex(count($headers));
            $sheet->getStyle("A1:{$highestCol}1")->getFont()->setBold(true);
            $sheet->getStyle("A1:{$highestCol}1")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF1E293B'); // Slate 800
            $sheet->getStyle("A1:{$highestCol}1")->getFont()->getColor()->setARGB('FFFFFFFF');

            $row = 2;
            foreach ($records as $item) {
                $statusLabel = StudentQuestionnaire::followupStatusOptions()[$item->followup_status] ?? $item->followup_status;
                $scholarshipLabel = $item->scholarship_interest === 'ya' ? 'Ya (Berminat)' : 'Tidak';

                $rowData = [
                    $item->created_at->format('Y-m-d H:i'),
                    $item->student_name,
                    $item->school_display_name,
                    $item->student_class,
                    $item->student_major,
                    $item->student_phone,
                    $item->student_email,
                    $item->guardian_name,
                    $item->guardian_phone,
                    $scholarshipLabel,
                    $statusLabel,
                    $item->last_followup_at ? $item->last_followup_at->format('Y-m-d H:i') : '-',
                    $item->lastFollowedUpBy?->name ?? '-',
                    $item->scholarship_notes ?? '-',
                    $item->college_interest_notes ?? '-',
                    $item->followup_notes ?? '-',
                ];

                foreach ($rowData as $colIdx => $val) {
                    $coord = Coordinate::stringFromColumnIndex($colIdx + 1) . $row;
                    $sheet->setCellValueExplicit($coord, (string) $val, DataType::TYPE_STRING);
                }
                $row++;
            }

            foreach (range(1, count($headers)) as $col) {
                $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
            }
            $sheet->freezePane('A2');

            // Sheet 2: Kriteria Report & Rekapitulasi Option Report
            $summarySheet = $spreadsheet->createSheet();
            $summarySheet->setTitle('Rekapitulasi Kriteria');

            $totalAll = $records->count();
            $noResponCount = $records->where('followup_status', 'no_respon')->count();
            $responCount = $records->where('followup_status', 'respon')->count();
            $berminatCount = $records->where('followup_status', 'berminat')->count();
            $daftarAplikasiCount = $records->filter(fn ($r) => in_array($r->followup_status, ['daftar_aplikasi', 'closing']))->count();
            $belumCount = $records->where('followup_status', 'belum_dihubungi')->count();

            $summaryData = [
                ['LAPORAN HASIL FOLLOW UP SISWA KUISIONER - MINAT KULIAH & BEASISWA UBSI'],
                ['Tanggal Ekspor: ' . date('d F Y H:i')],
                ['Total Data Diekspor: ' . $totalAll . ' Siswa'],
                [],
                ['Kriteria Status Follow Up', 'Jumlah Siswa', 'Persentase (%)', 'Minat Beasiswa (Ya)', 'Tidak Berminat Beasiswa'],
                [
                    'Belum Dihubungi',
                    $belumCount,
                    $totalAll > 0 ? round(($belumCount / $totalAll) * 100, 1) . '%' : '0%',
                    $records->where('followup_status', 'belum_dihubungi')->where('scholarship_interest', 'ya')->count(),
                    $records->where('followup_status', 'belum_dihubungi')->where('scholarship_interest', 'tidak')->count(),
                ],
                [
                    'No Respon (Tidak Menjawab)',
                    $noResponCount,
                    $totalAll > 0 ? round(($noResponCount / $totalAll) * 100, 1) . '%' : '0%',
                    $records->where('followup_status', 'no_respon')->where('scholarship_interest', 'ya')->count(),
                    $records->where('followup_status', 'no_respon')->where('scholarship_interest', 'tidak')->count(),
                ],
                [
                    'Respon (Menjawab / Diskusi)',
                    $responCount,
                    $totalAll > 0 ? round(($responCount / $totalAll) * 100, 1) . '%' : '0%',
                    $records->where('followup_status', 'respon')->where('scholarship_interest', 'ya')->count(),
                    $records->where('followup_status', 'respon')->where('scholarship_interest', 'tidak')->count(),
                ],
                [
                    'Berminat (Tertarik Kuliah / Beasiswa)',
                    $berminatCount,
                    $totalAll > 0 ? round(($berminatCount / $totalAll) * 100, 1) . '%' : '0%',
                    $records->where('followup_status', 'berminat')->where('scholarship_interest', 'ya')->count(),
                    $records->where('followup_status', 'berminat')->where('scholarship_interest', 'tidak')->count(),
                ],
                [
                    'Daftar Aplikasi (Masuk Data Prospek)',
                    $daftarAplikasiCount,
                    $totalAll > 0 ? round(($daftarAplikasiCount / $totalAll) * 100, 1) . '%' : '0%',
                    $records->filter(fn ($r) => in_array($r->followup_status, ['daftar_aplikasi', 'closing']))->where('scholarship_interest', 'ya')->count(),
                    $records->filter(fn ($r) => in_array($r->followup_status, ['daftar_aplikasi', 'closing']))->where('scholarship_interest', 'tidak')->count(),
                ],
                [
                    'TOTAL',
                    $totalAll,
                    '100%',
                    $records->where('scholarship_interest', 'ya')->count(),
                    $records->where('scholarship_interest', 'tidak')->count(),
                ],
            ];

            $summarySheet->fromArray($summaryData, null, 'A1');
            $summarySheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $summarySheet->getStyle('A5:E5')->getFont()->setBold(true);
            $summarySheet->getStyle('A5:E5')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE2E8F0');
            $summarySheet->getStyle('A11:E11')->getFont()->setBold(true);

            foreach (range('A', 'E') as $col) {
                $summarySheet->getColumnDimension($col)->setAutoSize(true);
            }

            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, 'laporan-followup-siswa-kuisioner.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function import(Request $request, StudentQuestionnaireImportService $importService): RedirectResponse
    {
        $validated = Validator::make($request->all(), [
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ], [
            'file.required' => 'Pilih file Excel yang akan diimpor.',
            'file.mimes' => 'File harus berformat .xlsx, .xls, atau .csv.',
            'file.max' => 'Ukuran file maksimal 10 MB.',
        ])->validate();

        try {
            $result = $importService->import($validated['file']);
        } catch (\Exception $e) {
            return redirect()->route('student-follow-ups.index')
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Gagal membaca file: ' . $e->getMessage(),
                ]);
        }

        return redirect()->route('student-follow-ups.index')
            ->with('toast', [
                'type' => $result['status'] ? 'success' : 'error',
                'message' => $result['message'],
            ])
            ->with('import_errors', $result['errors'] ?? []);
    }

    public function downloadImportTemplate(StudentQuestionnaireImportService $importService): StreamedResponse
    {
        return $importService->downloadTemplate();
    }

    /**
     * Distribusi / Ploting Jumlah Data Follow Up Siswa secara Massal.
     * Hanya berwenang dilakukan oleh Pimpinan, Koordinator Marketing, dan Admin.
     */
    public function distribute(Request $request): RedirectResponse
    {
        if (!auth()->user() || !auth()->user()->canPlotFollowUps()) {
            abort(403, 'Akses ditolak. Pengaturan pembagian data follow up hanya dapat dilakukan oleh Pimpinan dan Koordinator Marketing.');
        }

        $validated = $request->validate([
            'distribution_type' => ['required', 'in:equal,custom'],
            'source_filter' => ['required', 'in:all,kuisioner,support_event'],
            'total_limit' => ['nullable', 'integer', 'min:1'],
            'officer_ids' => ['required_if:distribution_type,equal', 'array'],
            'officer_ids.*' => ['exists:users,id'],
            'allocations' => ['required_if:distribution_type,custom', 'array'],
            'allocations.*' => ['nullable', 'integer', 'min:0'],
        ], [
            'officer_ids.required_if' => 'Pilih minimal satu petugas marketing/CS untuk pembagian rata.',
            'allocations.required_if' => 'Tentukan alokasi jumlah data follow up per petugas.',
        ]);

        $query = StudentQuestionnaire::query()->whereNull('assigned_to');

        if ($validated['source_filter'] === 'kuisioner') {
            $query->where('source', 'kuisioner');
        } elseif ($validated['source_filter'] === 'support_event') {
            $query->where(function ($q) {
                $q->where('source', 'support_event')
                  ->orWhereNotNull('school_event_proposal_id')
                  ->orWhere('college_interest_notes', 'like', '%Support Event%');
            });
        }

        // Prioritaskan yang belum dihubungi
        $query->orderByRaw("CASE WHEN followup_status = ? THEN 0 ELSE 1 END", [StudentQuestionnaire::STATUS_BELUM_DIHUBUNGI])
              ->orderBy('id', 'asc');

        $distributionType = $validated['distribution_type'];
        $assignedTotal = 0;

        DB::transaction(function () use ($query, $distributionType, $validated, &$assignedTotal) {
            $now = now();
            $authId = auth()->id();

            if ($distributionType === 'equal') {
                $officerIds = array_values(array_filter($validated['officer_ids'] ?? []));
                $officerCount = count($officerIds);
                if ($officerCount === 0) {
                    return;
                }

                $limit = !empty($validated['total_limit']) ? (int) $validated['total_limit'] : null;
                if ($limit) {
                    $leads = $query->limit($limit)->get(['id']);
                } else {
                    $leads = $query->get(['id']);
                }

                $leadIds = $leads->pluck('id')->all();
                $totalLeads = count($leadIds);

                if ($totalLeads === 0) {
                    return;
                }

                $perOfficer = intdiv($totalLeads, $officerCount);
                $remainder = $totalLeads % $officerCount;

                $offset = 0;
                foreach ($officerIds as $index => $officerId) {
                    $countForThis = $perOfficer + ($index < $remainder ? 1 : 0);
                    if ($countForThis <= 0) {
                        continue;
                    }

                    $slice = array_slice($leadIds, $offset, $countForThis);
                    StudentQuestionnaire::whereIn('id', $slice)->update([
                        'assigned_to' => $officerId,
                        'assigned_by' => $authId,
                        'assigned_at' => $now,
                    ]);
                    $offset += $countForThis;
                    $assignedTotal += count($slice);
                }
            } else {
                // Custom allocation per officer
                $allocations = $validated['allocations'] ?? [];
                foreach ($allocations as $officerId => $amount) {
                    $amount = (int) $amount;
                    if ($amount <= 0) {
                        continue;
                    }

                    $leadsToAssign = (clone $query)->limit($amount)->pluck('id')->all();
                    if (empty($leadsToAssign)) {
                        break;
                    }

                    StudentQuestionnaire::whereIn('id', $leadsToAssign)->update([
                        'assigned_to' => $officerId,
                        'assigned_by' => $authId,
                        'assigned_at' => $now,
                    ]);
                    $assignedTotal += count($leadsToAssign);
                }
            }
        });

        if ($assignedTotal === 0) {
            return back()->with('toast', [
                'type' => 'warning',
                'message' => 'Tidak ada data siswa baru yang belum di-ploting untuk kriteria sumber yang dipilih.',
            ])->with('warning', 'Tidak ada data siswa yang memenuhi kriteria untuk di-ploting.');
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Berhasil memploting {$assignedTotal} data follow up siswa ke tim marketing/CS.",
        ])->with('success', "Berhasil memploting {$assignedTotal} data follow up siswa ke tim marketing/CS.");
    }

    /**
     * Penugasan data siswa terpilih melalui checkbox tabel (Bulk Assignment).
     */
    public function bulkAssign(Request $request): RedirectResponse
    {
        if (!auth()->user() || !auth()->user()->canPlotFollowUps()) {
            abort(403, 'Akses ditolak. Pengaturan pembagian data follow up hanya dapat dilakukan oleh Pimpinan dan Koordinator Marketing.');
        }

        $validated = $request->validate([
            'selected_ids' => ['required', 'array', 'min:1'],
            'selected_ids.*' => ['exists:student_questionnaires,id'],
            'assigned_to' => ['required', 'exists:users,id'],
        ], [
            'selected_ids.required' => 'Pilih minimal satu data siswa.',
            'assigned_to.required' => 'Pilih petugas tujuan penugasan.',
        ]);

        $count = StudentQuestionnaire::whereIn('id', $validated['selected_ids'])->update([
            'assigned_to' => $validated['assigned_to'],
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
        ]);

        $officer = User::find($validated['assigned_to']);
        $officerName = $officer ? $officer->name : 'Petugas';

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Berhasil menugaskan {$count} data follow up siswa kepada {$officerName}.",
        ])->with('success', "Berhasil menugaskan {$count} data follow up siswa kepada {$officerName}.");
    }

    /**
     * Penugasan cepat satuan per siswa (Single Inline Assignment).
     */
    public function assignSingle(Request $request, StudentQuestionnaire $studentQuestionnaire): JsonResponse|RedirectResponse
    {
        if (!auth()->user() || !auth()->user()->canPlotFollowUps()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
            }
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $newAssignedTo = $validated['assigned_to'] ?: null;

        $studentQuestionnaire->update([
            'assigned_to' => $newAssignedTo,
            'assigned_by' => $newAssignedTo ? auth()->id() : null,
            'assigned_at' => $newAssignedTo ? now() : null,
        ]);

        $targetName = $studentQuestionnaire->assignedUser ? $studentQuestionnaire->assignedUser->name : 'Belum Di-ploting';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Penugasan siswa berhasil diperbarui ke: {$targetName}.",
                'assigned_to' => $studentQuestionnaire->assigned_to,
                'assigned_name' => $targetName,
            ]);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Penugasan siswa berhasil diperbarui ke: {$targetName}.",
        ])->with('success', "Penugasan siswa berhasil diperbarui ke: {$targetName}.");
    }
}
