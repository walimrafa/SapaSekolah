<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SchoolVisit;
use App\Models\User;
use App\Models\VisitPlan;
use App\Services\GoogleDriveService;
use App\Services\WhatsAppNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SchoolVisitController extends Controller
{
    /**
     * Display a listing of field visits.
     */
    public function index(Request $request): View
    {
        $query = SchoolVisit::with(['school.district', 'school.schoolType', 'user', 'visitPlan']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('agenda_category')) {
            $query->where('agenda_category', $request->agenda_category);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('school', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        \App\Services\AppContextService::applyContextFilters($query);

        $visits = $query->orderBy('visit_date', 'desc')->paginate(15)->withQueryString();

        $activeCampus = \App\Services\AppContextService::getActiveCampus();

        $schoolsQuery = School::select('id', 'name', 'district_id')
            ->whereHas('district', function($q) {
                $q->whereNotIn('name', \App\Models\District::EXCLUDED_DISTRICTS);
            });
        if ($activeCampus) {
            $schoolsQuery->where('campus_id', $activeCampus->id);
        }
        $schools = $schoolsQuery->orderBy('name')->get();
        
        $marketingUsersQuery = User::active()->fieldMarketing()->select('id', 'name', 'role_id', 'campus_id');
        if ($activeCampus) {
            $marketingUsersQuery->where(function ($q) use ($activeCampus) {
                $q->where('campus_id', $activeCampus->id)->orWhereNull('campus_id');
            });
        }
        $marketingUsers = $marketingUsersQuery->orderBy('name')->get();

        $visitPlansQuery = VisitPlan::where('status', 'planned')->with('school:id,name');
        if ($activeCampus) {
            $visitPlansQuery->where('campus_id', $activeCampus->id);
        }
        $visitPlans = $visitPlansQuery->get();

        return view('visits.index', compact('visits', 'schools', 'marketingUsers', 'visitPlans'));
    }

    /**
     * Show form page for recording a new field visit.
     */
    public function create(Request $request): View
    {
        $activeCampus = \App\Services\AppContextService::getActiveCampus();

        $initialPlanId = $request->query('plan_id');
        $initialSchoolId = $request->query('school_id');

        $selectedPlan = null;
        if ($initialPlanId) {
            $selectedPlan = VisitPlan::with(['school.district', 'school.schoolType', 'user'])->find($initialPlanId);
            if ($selectedPlan && !$initialSchoolId) {
                $initialSchoolId = $selectedPlan->school_id;
            }
        }

        $districtsQuery = \App\Models\District::has('schools')
            ->withCount(['schools' => function($q) use ($activeCampus) {
                if ($activeCampus) {
                    $q->where('campus_id', $activeCampus->id);
                }
            }])
            ->orderBy('name');
        if ($activeCampus) {
            $districtsQuery->whereHas('schools', function($q) use ($activeCampus, $initialSchoolId) {
                $q->where('campus_id', $activeCampus->id);
                if ($initialSchoolId) {
                    $q->orWhere('id', $initialSchoolId);
                }
            });
        }
        $districts = $districtsQuery->get();

        $schoolsQuery = School::with(['district', 'schoolType'])
            ->whereHas('district', function($q) {
                $q->whereNotIn('name', \App\Models\District::EXCLUDED_DISTRICTS);
            })
            ->orderBy('name');
        if ($activeCampus) {
            $schoolsQuery->where(function($q) use ($activeCampus, $initialSchoolId) {
                $q->where('campus_id', $activeCampus->id);
                if ($initialSchoolId) {
                    $q->orWhere('id', $initialSchoolId);
                }
            });
        }
        $schools = $schoolsQuery->get();
        
        $marketingUsersQuery = User::active()->fieldMarketing()->with('role');
        if ($activeCampus) {
            $marketingUsersQuery->where(function ($q) use ($activeCampus) {
                $q->where('campus_id', $activeCampus->id)->orWhereNull('campus_id');
            });
        }
        $marketingUsers = $marketingUsersQuery->orderBy('name')->get();

        // Active planned visit plans
        $visitPlansQuery = VisitPlan::where(function($q) use ($initialPlanId) {
                $q->where('status', 'planned');
                if ($initialPlanId) {
                    $q->orWhere('id', $initialPlanId);
                }
            })
            ->with(['school.district', 'school.schoolType', 'user'])
            ->orderBy('planned_date', 'asc');
        if ($activeCampus) {
            $visitPlansQuery->where(function($q) use ($activeCampus, $initialPlanId) {
                $q->where('campus_id', $activeCampus->id);
                if ($initialPlanId) {
                    $q->orWhere('id', $initialPlanId);
                }
            });
        }
        $visitPlans = $visitPlansQuery->get();

        $initialUserId = $selectedPlan?->user_id;

        return view('visits.create', compact('districts', 'schools', 'marketingUsers', 'visitPlans', 'initialPlanId', 'initialSchoolId', 'initialUserId', 'selectedPlan'));
    }

    /**
     * Store a newly recorded field visit with socialization details.
     */
    public function store(Request $request): RedirectResponse
    {
        $rawCategories = $request->input('agenda_categories');
        if (is_array($rawCategories) && !empty($rawCategories)) {
            $agendaCategories = array_values(array_intersect($rawCategories, [
                SchoolVisit::AGENDA_PENJADWALAN_SOSIALISASI,
                SchoolVisit::AGENDA_MENGANTAR_UNDANGAN,
                SchoolVisit::AGENDA_KUNJUNGAN_LAINNYA,
            ]));
        } else {
            $rawCat = $request->input('agenda_category', SchoolVisit::AGENDA_PENJADWALAN_SOSIALISASI);
            $agendaCategories = array_filter(array_map('trim', explode(',', $rawCat)));
        }

        if (empty($agendaCategories)) {
            $agendaCategories = [SchoolVisit::AGENDA_PENJADWALAN_SOSIALISASI];
        }

        $agendaCategoryString = implode(',', $agendaCategories);
        $hasSosialisasi = in_array(SchoolVisit::AGENDA_PENJADWALAN_SOSIALISASI, $agendaCategories);
        $hasUndangan = in_array(SchoolVisit::AGENDA_MENGANTAR_UNDANGAN, $agendaCategories);
        $hasLainnya = in_array(SchoolVisit::AGENDA_KUNJUNGAN_LAINNYA, $agendaCategories);

        $rules = [
            'school_id' => ['required', 'exists:schools,id'],
            'user_id' => ['required', 'exists:users,id'],
            'visit_plan_id' => ['nullable', 'exists:visit_plans,id'],
            'visit_date' => ['required', 'date'],
            'headmaster_name' => ['nullable', 'string', 'max:150'],
            'recipient_name' => ['required', 'string', 'max:150'],
            'recipient_position' => ['required', 'string', 'max:100'],
            'recipient_phone' => ['nullable', 'string', 'max:30'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'result' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'photo' => [
                'nullable',
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
            'photo_camera_base64' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'next_followup_date' => ['nullable', 'date'],
        ];

        if ($hasSosialisasi) {
            $rules['grade_12_class_count'] = ['required', 'integer', 'min:1'];
            $rules['target_month_year'] = ['required', 'string', 'max:50'];
            $rules['target_date'] = ['nullable', 'date'];
            $rules['target_time'] = ['required', 'string', 'max:20'];
            $rules['students_per_class'] = ['required', 'integer', 'min:1'];
            $rules['socialization_method'] = ['required', 'string', 'max:100'];
            $rules['other_activity_name'] = ['nullable', 'required_if:socialization_method,Lainnya', 'string', 'max:200'];
            $rules['status'] = ['required', 'in:berhasil,tidak_berhasil,tidak_bertemu,pending'];
        } else {
            $rules['grade_12_class_count'] = ['nullable', 'integer'];
            $rules['target_month_year'] = ['nullable', 'string', 'max:50'];
            $rules['target_date'] = ['nullable', 'date'];
            $rules['target_time'] = ['nullable', 'string', 'max:20'];
            $rules['students_per_class'] = ['nullable', 'integer'];
            $rules['socialization_method'] = ['nullable', 'string', 'max:100'];
            $rules['other_activity_name'] = ['nullable', 'string', 'max:200'];
            $rules['status'] = ['nullable', 'in:berhasil,tidak_berhasil,tidak_bertemu,pending'];
        }

        if ($hasUndangan) {
            $rules['event_name'] = ['required', 'string', 'max:255'];
            $rules['invitation_attendance_status'] = ['required', 'string', 'in:akan_hadir,minta_ijin_dulu,tidak_bisa_hadir'];
        } else {
            $rules['event_name'] = ['nullable', 'string', 'max:255'];
            $rules['invitation_attendance_status'] = ['nullable', 'string', 'in:akan_hadir,minta_ijin_dulu,tidak_bisa_hadir'];
        }

        $validated = $request->validate($rules);
        $validated['agenda_category'] = $agendaCategoryString;
        $validated['status'] = $validated['status'] ?? 'berhasil';

        if (empty($validated['purpose'])) {
            $purposes = [];
            if ($hasSosialisasi) $purposes[] = 'Penjadwalan Sosialisasi';
            if ($hasUndangan) $purposes[] = 'Mengantar Undangan Acara' . (!empty($validated['event_name']) ? " ({$validated['event_name']})" : '');
            if ($hasLainnya) $purposes[] = 'Kunjungan Lainnya';
            $validated['purpose'] = implode(' & ', $purposes);
        }

        if ($hasUndangan && !empty($validated['invitation_attendance_status'])) {
            $eventLabel = !empty($validated['event_name']) ? "Event: {$validated['event_name']} - " : '';
            $resText = $eventLabel . 'Konfirmasi Kehadiran: ' . (SchoolVisit::invitationAttendanceOptions()[$validated['invitation_attendance_status']] ?? $validated['invitation_attendance_status']);
            $validated['result'] = !empty($validated['result']) ? $validated['result'] . ' | ' . $resText : $resText;
        }

        $school = School::findOrFail($validated['school_id']);
        // Check-in GPS bersifat opsional: konversi atau biarkan null jika tidak diisi
        $validated['latitude'] = !empty($validated['latitude']) ? (float) $validated['latitude'] : null;
        $validated['longitude'] = !empty($validated['longitude']) ? (float) $validated['longitude'] : null;

        $validated['contact_person'] = $validated['recipient_name'] . ' (' . $validated['recipient_position'] . ')';

        // Auto upload photo and sync to Google Drive (mendukung berkas & kamera base64)
        $photo = $request->file('photo');
        if (!$photo && $request->filled('photo_camera_base64')) {
            $base64Data = $request->input('photo_camera_base64');
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $typeMatches)) {
                $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
                $imgType = strtolower($typeMatches[1]);
                if (in_array($imgType, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $decoded = base64_decode($base64Data);
                    if ($decoded !== false) {
                        $tmpFilePath = sys_get_temp_dir() . '/' . uniqid('cam_visit_', true) . '.' . $imgType;
                        file_put_contents($tmpFilePath, $decoded);
                        $photo = new \Illuminate\Http\UploadedFile(
                            $tmpFilePath,
                            'kamera_' . Str::slug($school->name) . '_' . date('Ymd_His') . '.' . $imgType,
                            'image/' . ($imgType === 'jpg' ? 'jpeg' : $imgType),
                            null,
                            true
                        );
                    }
                }
            }
        }

        unset($validated['photo'], $validated['photo_camera_base64']);
        if ($photo) {
            $gdriveResult = GoogleDriveService::uploadDocumentation(
                $photo,
                $school->name,
                $validated['visit_date'],
                $school->campus_id
            );
            $validated['photo'] = $gdriveResult['stored_path'];
            $validated['google_drive_folder_url'] = $gdriveResult['drive_folder_url'];
            $validated['google_drive_file_url'] = $gdriveResult['drive_file_url'];
        } else {
            $validated['google_drive_folder_url'] = GoogleDriveService::buildFolderUrlForSchool(
                $school->name,
                $validated['visit_date'],
                $school->campus_id
            );
        }

        // Auto match active visit plan if not explicitly supplied
        if (empty($validated['visit_plan_id'])) {
            $matchedPlan = VisitPlan::where('school_id', $validated['school_id'])
                ->where('status', 'planned')
                ->where(function($q) use ($validated) {
                    $q->where('user_id', $validated['user_id'])
                      ->orWhereDate('planned_date', $validated['visit_date']);
                })
                ->first();
            if ($matchedPlan) {
                $validated['visit_plan_id'] = $matchedPlan->id;
            }
        }

        // Set campus & academic period context
        $activeCampus = \App\Services\AppContextService::getActiveCampus();
        $activePeriod = \App\Services\AppContextService::getActivePeriod();
        $validated['campus_id'] = $school->campus_id ?: ($activeCampus?->id);
        $validated['academic_period_id'] = $activePeriod?->id;

        $visit = SchoolVisit::create($validated);

        if (!empty($validated['headmaster_name']) && empty($school->headmaster_name)) {
            $school->update(['headmaster_name' => $validated['headmaster_name']]);
        }

        // If connected to a visit plan, mark plan as completed so it moves out of active plans
        if (!empty($validated['visit_plan_id'])) {
            VisitPlan::where('id', $validated['visit_plan_id'])->update(['status' => 'completed']);
        }

        // Integrasi Notifikasi WA & Google Calendar ke Petugas Bertugas berdasarkan User Management
        try {
            app(WhatsAppNotificationService::class)->notifySchoolVisit($visit);
        } catch (\Throwable $e) {
            Log::warning('Failed auto WA & Calendar notification for visit: ' . $e->getMessage());
        }

        // Otomatis masukkan ke data Follow Up untuk konfirmasi FIX jadwal sosialisasi
        try {
            app(\App\Services\FollowUpSyncService::class)->syncVisit($visit);
        } catch (\Throwable $e) {
            Log::warning('Failed auto sync visit to follow up: ' . $e->getMessage());
        }

        return redirect()->route('visits.index')
            ->with('toast', [
                'type' => 'success',
                'message' => "Hasil Kunjungan Lapangan ke {$visit->school->name} berhasil dicatat, notifikasi WA & kalender terkirim, serta otomatis tersinkron ke Follow Up!",
            ]);
    }

    /**
     * Pastikan pengguna memiliki hak akses terhadap kunjungan sekolah berdasarkan isolasi kampus.
     * Pengguna kampus cabang (misal UBSI Karawang / UBSI Cikampek) tidak boleh mengakses kunjungan kampus lain.
     */
    protected function authorizeCampusAccess(SchoolVisit $visit): void
    {
        $user = auth()->user();
        if ($user && !\App\Services\AppContextService::canSwitchCampus($user)) {
            $userCampusId = $user->campus_id ?? \App\Services\AppContextService::getActiveCampus()?->id;
            $visitCampusId = $visit->campus_id ?: ($visit->school?->campus_id);
            if ($visitCampusId && $userCampusId && (int)$visitCampusId !== (int)$userCampusId) {
                abort(403, 'Akses ditolak. Data kunjungan ini berada di luar wilayah kampus penugasan Anda.');
            }
        }
    }

    /**
     * Show form page for editing an existing field visit.
     */
    public function edit(SchoolVisit $visit): View
    {
        $this->authorizeCampusAccess($visit);

        $user = auth()->user();
        if (!$user || (!$user->hasFullMenuAccess() && $visit->user_id !== $user->id)) {
            abort(403, 'Akses ditolak. Anda tidak berhak mengubah data kunjungan ini.');
        }

        $activeCampus = \App\Services\AppContextService::getActiveCampus();

        $visit->load(['school.district', 'user']);

        $districtsQuery = \App\Models\District::has('schools')
            ->withCount(['schools' => function($q) use ($activeCampus) {
                if ($activeCampus) {
                    $q->where('campus_id', $activeCampus->id);
                }
            }])
            ->orderBy('name');
        if ($activeCampus) {
            $districtsQuery->whereHas('schools', function($q) use ($activeCampus, $visit) {
                $q->where('campus_id', $activeCampus->id)
                  ->orWhere('id', $visit->school_id);
            });
        }
        $districts = $districtsQuery->get();

        $schoolsQuery = School::with(['district', 'schoolType'])
            ->whereHas('district', function($q) {
                $q->whereNotIn('name', \App\Models\District::EXCLUDED_DISTRICTS);
            })
            ->orderBy('name');
        if ($activeCampus) {
            $schoolsQuery->where(function ($q) use ($activeCampus, $visit) {
                $q->where('campus_id', $activeCampus->id)
                  ->orWhere('id', $visit->school_id);
            });
        }
        $schools = $schoolsQuery->get();

        $marketingUsersQuery = User::active()->fieldMarketing()->with('role');
        if ($activeCampus) {
            $marketingUsersQuery->where(function ($q) use ($activeCampus) {
                $q->where('campus_id', $activeCampus->id)->orWhereNull('campus_id');
            });
        }
        $marketingUsers = $marketingUsersQuery->orderBy('name')->get();

        return view('visits.edit', compact('visit', 'districts', 'schools', 'marketingUsers'));
    }

    /**
     * Update an existing field visit record.
     */
    public function update(Request $request, SchoolVisit $visit): RedirectResponse
    {
        $this->authorizeCampusAccess($visit);

        $user = auth()->user();
        if (!$user || (!$user->hasFullMenuAccess() && $visit->user_id !== $user->id)) {
            abort(403, 'Akses ditolak. Anda tidak berhak mengubah data kunjungan ini.');
        }

        $rawCategories = $request->input('agenda_categories');
        if (is_array($rawCategories) && !empty($rawCategories)) {
            $agendaCategories = array_values(array_intersect($rawCategories, [
                SchoolVisit::AGENDA_PENJADWALAN_SOSIALISASI,
                SchoolVisit::AGENDA_MENGANTAR_UNDANGAN,
                SchoolVisit::AGENDA_KUNJUNGAN_LAINNYA,
            ]));
        } else {
            $rawCat = $request->input('agenda_category', $visit->agenda_category ?? SchoolVisit::AGENDA_PENJADWALAN_SOSIALISASI);
            $agendaCategories = array_filter(array_map('trim', explode(',', $rawCat)));
        }

        if (empty($agendaCategories)) {
            $agendaCategories = [SchoolVisit::AGENDA_PENJADWALAN_SOSIALISASI];
        }

        $agendaCategoryString = implode(',', $agendaCategories);
        $hasSosialisasi = in_array(SchoolVisit::AGENDA_PENJADWALAN_SOSIALISASI, $agendaCategories);
        $hasUndangan = in_array(SchoolVisit::AGENDA_MENGANTAR_UNDANGAN, $agendaCategories);
        $hasLainnya = in_array(SchoolVisit::AGENDA_KUNJUNGAN_LAINNYA, $agendaCategories);

        $rules = [
            'school_id' => ['required', 'exists:schools,id'],
            'user_id' => ['required', 'exists:users,id'],
            'visit_date' => ['required', 'date'],
            'headmaster_name' => ['nullable', 'string', 'max:150'],
            'recipient_name' => ['required', 'string', 'max:150'],
            'recipient_position' => ['required', 'string', 'max:100'],
            'recipient_phone' => ['nullable', 'string', 'max:30'],
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'photo' => [
                'nullable',
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
            'photo_camera_base64' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ];

        if ($hasSosialisasi) {
            $rules['grade_12_class_count'] = ['required', 'integer', 'min:1'];
            $rules['target_month_year'] = ['required', 'string', 'max:50'];
            $rules['target_date'] = ['nullable', 'date'];
            $rules['target_time'] = ['required', 'string', 'max:20'];
            $rules['students_per_class'] = ['required', 'integer', 'min:1'];
            $rules['socialization_method'] = ['required', 'string', 'max:100'];
            $rules['other_activity_name'] = ['nullable', 'required_if:socialization_method,Lainnya', 'string', 'max:200'];
            $rules['status'] = ['required', 'in:berhasil,tidak_berhasil,tidak_bertemu,pending'];
        } else {
            $rules['grade_12_class_count'] = ['nullable', 'integer'];
            $rules['target_month_year'] = ['nullable', 'string', 'max:50'];
            $rules['target_date'] = ['nullable', 'date'];
            $rules['target_time'] = ['nullable', 'string', 'max:20'];
            $rules['students_per_class'] = ['nullable', 'integer'];
            $rules['socialization_method'] = ['nullable', 'string', 'max:100'];
            $rules['other_activity_name'] = ['nullable', 'string', 'max:200'];
            $rules['status'] = ['nullable', 'in:berhasil,tidak_berhasil,tidak_bertemu,pending'];
        }

        if ($hasUndangan) {
            $rules['event_name'] = ['required', 'string', 'max:255'];
            $rules['invitation_attendance_status'] = ['required', 'string', 'in:akan_hadir,minta_ijin_dulu,tidak_bisa_hadir'];
        } else {
            $rules['event_name'] = ['nullable', 'string', 'max:255'];
            $rules['invitation_attendance_status'] = ['nullable', 'string', 'in:akan_hadir,minta_ijin_dulu,tidak_bisa_hadir'];
        }

        $validated = $request->validate($rules);
        $validated['agenda_category'] = $agendaCategoryString;
        $validated['status'] = $validated['status'] ?? 'berhasil';

        if (empty($validated['purpose'])) {
            $purposes = [];
            if ($hasSosialisasi) $purposes[] = 'Penjadwalan Sosialisasi';
            if ($hasUndangan) $purposes[] = 'Mengantar Undangan Acara' . (!empty($validated['event_name']) ? " ({$validated['event_name']})" : '');
            if ($hasLainnya) $purposes[] = 'Kunjungan Lainnya';
            $validated['purpose'] = implode(' & ', $purposes);
        }

        if ($hasUndangan && !empty($validated['invitation_attendance_status'])) {
            $eventLabel = !empty($validated['event_name']) ? "Event: {$validated['event_name']} - " : '';
            $resText = $eventLabel . 'Konfirmasi Kehadiran: ' . (SchoolVisit::invitationAttendanceOptions()[$validated['invitation_attendance_status']] ?? $validated['invitation_attendance_status']);
            $validated['result'] = !empty($validated['result']) ? $validated['result'] . ' | ' . $resText : $resText;
        }

        $schoolChanged = (int) $validated['school_id'] !== (int) $visit->school_id;
        if (isset($validated['latitude'])) {
            $validated['latitude'] = !empty($validated['latitude']) ? (float) $validated['latitude'] : null;
        }
        if (isset($validated['longitude'])) {
            $validated['longitude'] = !empty($validated['longitude']) ? (float) $validated['longitude'] : null;
        }

        $validated['contact_person'] = $validated['recipient_name'] . ' (' . $validated['recipient_position'] . ')';

        $photo = $request->file('photo');
        if (!$photo && $request->filled('photo_camera_base64')) {
            $base64Data = $request->input('photo_camera_base64');
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $typeMatches)) {
                $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
                $imgType = strtolower($typeMatches[1]);
                if (in_array($imgType, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $decoded = base64_decode($base64Data);
                    if ($decoded !== false) {
                        $tmpFilePath = sys_get_temp_dir() . '/' . uniqid('cam_visit_upd_', true) . '.' . $imgType;
                        file_put_contents($tmpFilePath, $decoded);
                        $photo = new \Illuminate\Http\UploadedFile(
                            $tmpFilePath,
                            'kamera_kunjungan_' . date('Ymd_His') . '.' . $imgType,
                            'image/' . ($imgType === 'jpg' ? 'jpeg' : $imgType),
                            null,
                            true
                        );
                    }
                }
            }
        }

        unset($validated['photo'], $validated['photo_camera_base64']);
        if ($photo) {
            $school = School::find($validated['school_id']);
            $schoolName = $school ? $school->name : ($visit->school?->name ?? 'Sekolah');
            $gdriveResult = GoogleDriveService::uploadDocumentation(
                $photo,
                $schoolName,
                $validated['visit_date'],
                $school?->campus_id ?: $visit->campus_id
            );
            if ($visit->photo) {
                Storage::disk('public')->delete($visit->photo);
            }
            $validated['photo'] = $gdriveResult['stored_path'];
            $validated['google_drive_folder_url'] = $gdriveResult['drive_folder_url'];
            $validated['google_drive_file_url'] = $gdriveResult['drive_file_url'];
        }

        $visit->update($validated);

        if (!empty($validated['headmaster_name'])) {
            $school = School::find($validated['school_id']);
            if ($school && empty($school->headmaster_name)) {
                $school->update(['headmaster_name' => $validated['headmaster_name']]);
            }
        }

        // Sync to follow up
        try {
            app(\App\Services\FollowUpSyncService::class)->syncVisit($visit);
        } catch (\Throwable $e) {
            Log::warning('Failed sync visit update to follow up: ' . $e->getMessage());
        }

        return redirect()->route('visits.index')
            ->with('toast', [
                'type' => 'success',
                'message' => "Data kunjungan ke {$visit->school->name} berhasil diperbarui.",
            ]);
    }

    /**
     * Download Excel rekap data kunjungan sekolah beserta link Google Drive masing-masing sekolah.
     */
    public function export(Request $request): StreamedResponse
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

        $query = SchoolVisit::with(['school.district', 'school.schoolType', 'user']);

        if ($activeCampus) {
            $query->where(function ($q) use ($activeCampus) {
                $q->where('campus_id', $activeCampus->id)
                  ->orWhereHas('school', fn($sq) => $sq->where('campus_id', $activeCampus->id));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('school', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }
        if ($request->filled('date')) {
            $query->whereDate('visit_date', $request->date);
        }

        $visits = $query->orderBy('visit_date', 'desc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Kunjungan');

        // Header Title
        $sheet->setCellValue('A1', 'REKAPITULASI DATA KUNJUNGAN SEKOLAH & LINK GOOGLE DRIVE');
        $sheet->mergeCells('A1:S1');
        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB('0B023E');

        $sheet->setCellValue('A2', 'SAPA Sekolah UBSI • Tanggal Unduh: ' . now()->translatedFormat('d F Y H:i') . ' WIB • Total Data: ' . $visits->count() . ' Kunjungan');
        $sheet->mergeCells('A2:S2');
        $sheet->getStyle('A2')->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('64748B');

        $headers = [
            'No',
            'Tanggal Kunjungan',
            'Nama Sekolah',
            'NPSN',
            'Kecamatan',
            'Bentuk Sekolah',
            'Marketing PIC',
            'No. HP Petugas (User)',
            'Email Petugas (User)',
            'Kepala Sekolah',
            'PIC Penerima Sekolah',
            'Jabatan PIC',
            'No. HP/WA PIC',
            'Target Sosialisasi',
            'Estimasi Siswa Kls 12',
            'Metode Kegiatan',
            'Status Kunjungan',
            'Link Google Drive Dokumentasi',
            'Catatan Kunjungan',
        ];

        $headerRow = 4;
        foreach ($headers as $colIdx => $h) {
            $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
            $sheet->setCellValue("{$colLetter}{$headerRow}", $h);
        }

        $lastColLetter = Coordinate::stringFromColumnIndex(count($headers));
        $headerRange = "A4:{$lastColLetter}4";
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0B023E');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $rowNum = 5;
        foreach ($visits as $idx => $v) {
            $school = $v->school;
            $user = $v->user;
            $totalSiswa = ($v->grade_12_class_count && $v->students_per_class) ? ($v->grade_12_class_count * $v->students_per_class) : 0;
            $targetStr = ($v->target_date ? $v->target_date->translatedFormat('d M Y') : $v->target_month_year) . ($v->target_time ? ' (' . $v->target_time . ')' : '');

            $driveFolderUrl = $v->drive_folder_url;

            $sheet->setCellValue("A{$rowNum}", $idx + 1);
            $sheet->setCellValue("B{$rowNum}", $v->visit_date ? $v->visit_date->format('Y-m-d') : '-');
            $sheet->setCellValue("C{$rowNum}", \App\Support\SecuritySanitizer::sanitizeSpreadsheetCell($school?->name ?? '-'));
            $sheet->setCellValueExplicit("D{$rowNum}", $school?->npsn ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValue("E{$rowNum}", \App\Support\SecuritySanitizer::sanitizeSpreadsheetCell($school?->district?->name ?? '-'));
            $sheet->setCellValue("F{$rowNum}", \App\Support\SecuritySanitizer::sanitizeSpreadsheetCell($school?->schoolType?->name ?? '-'));
            $sheet->setCellValue("G{$rowNum}", \App\Support\SecuritySanitizer::sanitizeSpreadsheetCell($user?->name ?? '-'));
            $sheet->setCellValueExplicit("H{$rowNum}", $user?->phone ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValue("I{$rowNum}", \App\Support\SecuritySanitizer::sanitizeSpreadsheetCell($user?->email ?? '-'));
            $sheet->setCellValue("J{$rowNum}", \App\Support\SecuritySanitizer::sanitizeSpreadsheetCell($school?->headmaster_name ?? ($v->headmaster_name ?? '-')));
            $sheet->setCellValue("K{$rowNum}", \App\Support\SecuritySanitizer::sanitizeSpreadsheetCell($v->recipient_name ?? '-'));
            $sheet->setCellValue("L{$rowNum}", \App\Support\SecuritySanitizer::sanitizeSpreadsheetCell($v->recipient_position ?? '-'));
            $sheet->setCellValueExplicit("M{$rowNum}", $v->recipient_phone ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValue("N{$rowNum}", \App\Support\SecuritySanitizer::sanitizeSpreadsheetCell($targetStr));
            $sheet->setCellValue("O{$rowNum}", $totalSiswa > 0 ? "{$totalSiswa} Siswa ({$v->grade_12_class_count} kls)" : '-');
            $sheet->setCellValue("P{$rowNum}", \App\Support\SecuritySanitizer::sanitizeSpreadsheetCell($v->socialization_method ?? '-'));
            $sheet->setCellValue("Q{$rowNum}", strtoupper($v->status));

            // Column R: Google Drive Folder Link (Hyperlink)
            $sheet->setCellValue("R{$rowNum}", $driveFolderUrl);
            $sheet->getCell("R{$rowNum}")->getHyperlink()->setUrl($driveFolderUrl);
            $driveFont = $sheet->getStyle("R{$rowNum}")->getFont();
            $driveFont->getColor()->setRGB('0B023E');
            $driveFont->setUnderline(true);

            $sheet->setCellValue("S{$rowNum}", \App\Support\SecuritySanitizer::sanitizeSpreadsheetCell($v->notes ?? '-'));

            $rowNum++;
        }

        // Auto size columns
        foreach (range(1, count($headers)) as $col) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
        }

        $filename = 'Data_Kunjungan_Sekolah_UBSI_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Remove a field visit record.
     */
    public function destroy(SchoolVisit $visit): RedirectResponse
    {
        $this->authorizeCampusAccess($visit);

        $user = auth()->user();
        if (!$user || (!$user->hasFullMenuAccess() && $visit->user_id !== $user->id)) {
            abort(403, 'Akses ditolak. Anda tidak memiliki wewenang untuk menghapus rekaman kunjungan ini.');
        }

        $schoolName = $visit->school ? $visit->school->name : 'Sekolah';
        $visit->delete();

        \App\Services\ActivityLogService::log(
            'DELETE',
            'visit',
            $visit->id,
            "Menghapus data kunjungan sekolah: {$schoolName} (Tanggal: " . ($visit->visit_date?->format('d/m/Y') ?? '-') . ")",
            ['school' => $schoolName, 'visit_date' => $visit->visit_date]
        );

        return redirect()->route('visits.index')
            ->with('toast', [
                'type' => 'success',
                'message' => "Data kunjungan ke {$schoolName} berhasil dihapus.",
            ]);
    }

    private function ensureCheckInWithinSchoolRadius(School $school, ?float $latitude, ?float $longitude): void
    {
        // Fitur check-in GPS bersifat opsional dan tidak memblokir proses penyimpanan data
        return;
    }
}
