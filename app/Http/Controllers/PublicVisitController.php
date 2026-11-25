<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\School;
use App\Models\SchoolVisit;
use App\Models\User;
use App\Models\VisitPlan;
use App\Services\ActivityLogService;
use App\Services\AppContextService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicVisitController extends Controller
{
    /**
     * Display public field visit socialization submission form (No Login Required).
     */
    public function index(Request $request): View
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
                $activeCampus = AppContextService::getActiveCampus();
                $isCampusLocked = ($activeCampus !== null);
            }
        } elseif ($requestedCampusId) {
            $activeCampus = \App\Models\Campus::find($requestedCampusId);
            $isCampusLocked = ($activeCampus !== null);
        } else {
            $activeCampus = AppContextService::getActiveCampus() ?? \App\Models\Campus::getDefault();
        }

        if (!AppContextService::isPortalActive(AppContextService::PORTAL_PUBLIC_VISIT, $activeCampus?->id)) {
            return view('errors.portal-disabled', [
                'portalName' => 'Portal Input Kunjungan Lapangan (' . ($activeCampus?->name ?? 'UBSI') . ')',
            ]);
        }

        $schoolsQuery = School::with(['district', 'schoolType', 'campus'])
            ->whereHas('district', function ($q) {
                $q->whereNotIn('name', District::EXCLUDED_DISTRICTS);
            });

        if ($activeCampus) {
            $schoolsQuery->where('campus_id', $activeCampus->id);
        }

        $schools = $schoolsQuery->orderBy('name')->get();

        $districtsQuery = District::whereHas('schools', function ($q) use ($activeCampus) {
            if ($activeCampus) {
                $q->where('campus_id', $activeCampus->id);
            }
        })->withCount(['schools' => function ($q) use ($activeCampus) {
            if ($activeCampus) {
                $q->where('campus_id', $activeCampus->id);
            }
        }])->orderBy('name');
        $districts = $districtsQuery->get();

        $marketingUsersQuery = User::active()->fieldMarketing()->with('role');
        if ($activeCampus) {
            $marketingUsersQuery->where(function ($q) use ($activeCampus) {
                $q->where('campus_id', $activeCampus->id)->orWhereNull('campus_id');
            });
        }
        $marketingUsers = $marketingUsersQuery->orderBy('name')->get();

        return view('public-visit', compact('districts', 'schools', 'marketingUsers', 'campuses', 'activeCampus', 'isCampusLocked'));
    }

    /**
     * Store a public field visit submission (No Login Required).
     */
    public function store(Request $request): RedirectResponse
    {
        $campusId = $request->input('campus_id') ?? $request->query('campus_id');
        if (!AppContextService::isPortalActive(AppContextService::PORTAL_PUBLIC_VISIT, $campusId)) {
            return back()->with('error', 'Portal Input Kunjungan Lapangan untuk kampus ini saat ini sedang ditutup atau dinonaktifkan.');
        }

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
            'visit_date' => ['required', 'date'],
            'headmaster_name' => ['nullable', 'string', 'max:150'],
            'recipient_name' => ['required', 'string', 'max:150'],
            'recipient_position' => ['required', 'string', 'max:100'],
            'recipient_phone' => ['nullable', 'string', 'max:30'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'photo' => [
                'nullable',
                'file',
                'max:51200',
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
        ];

        if ($hasSosialisasi) {
            $rules['grade_12_class_count'] = ['required', 'integer', 'min:1'];
            $rules['target_month_year'] = ['required', 'string', 'max:50'];
            $rules['target_date'] = ['nullable', 'date'];
            $rules['target_time'] = ['required', 'string', 'max:20'];
            $rules['students_per_class'] = ['required', 'integer', 'min:1'];
            $rules['socialization_method'] = ['required', 'string', 'max:100'];
            $rules['other_activity_name'] = ['nullable', 'required_if:socialization_method,Lainnya', 'string', 'max:200'];
            $rules['status'] = ['required', 'in:berhasil,tidak_bertemu,tidak_berhasil,pending'];
        } else {
            $rules['grade_12_class_count'] = ['nullable', 'integer'];
            $rules['target_month_year'] = ['nullable', 'string', 'max:50'];
            $rules['target_date'] = ['nullable', 'date'];
            $rules['target_time'] = ['nullable', 'string', 'max:20'];
            $rules['students_per_class'] = ['nullable', 'integer'];
            $rules['socialization_method'] = ['nullable', 'string', 'max:100'];
            $rules['other_activity_name'] = ['nullable', 'string', 'max:200'];
            $rules['status'] = ['nullable', 'in:berhasil,tidak_bertemu,tidak_berhasil,pending'];
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
        $validated['contact_person'] = $validated['recipient_name'] . ' (' . $validated['recipient_position'] . ')';

        // Set campus & academic period context
        $activeCampus = AppContextService::getActiveCampus();
        $activePeriod = AppContextService::getActivePeriod();
        $targetCampusId = $school->campus_id ?: ($request->input('campus_id') ?: ($activeCampus?->id ?? 1));
        $validated['campus_id'] = $targetCampusId;
        $validated['academic_period_id'] = $activePeriod?->id;

        // Ambil berkas foto dari unggahan standar atau hasil kamera langsung (Base64)
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
            $gdriveResult = \App\Services\GoogleDriveService::uploadDocumentation(
                $photo,
                $school->name,
                $validated['visit_date'],
                $targetCampusId
            );
            $validated['photo'] = $gdriveResult['stored_path'];
            $validated['google_drive_folder_url'] = $gdriveResult['drive_folder_url'];
            $validated['google_drive_file_url'] = $gdriveResult['drive_file_url'];
        } else {
            $validated['google_drive_folder_url'] = \App\Services\GoogleDriveService::buildFolderUrlForSchool(
                $school->name,
                $validated['visit_date'],
                $targetCampusId
            );
        }

        // Auto match active visit plan if not explicitly supplied
        $matchedPlan = VisitPlan::where('school_id', $validated['school_id'])
            ->where('status', 'planned')
            ->where(function ($q) use ($validated) {
                $q->where('user_id', $validated['user_id'])
                    ->orWhereDate('planned_date', $validated['visit_date']);
            })
            ->first();
        if ($matchedPlan) {
            $validated['visit_plan_id'] = $matchedPlan->id;
        }

        $visit = SchoolVisit::create($validated);

        if (!empty($validated['headmaster_name'])) {
            if (empty($school->headmaster_name)) {
                $school->update(['headmaster_name' => $validated['headmaster_name']]);
            }
        }

        // Mark plan completed
        if (!empty($validated['visit_plan_id'])) {
            VisitPlan::where('id', $validated['visit_plan_id'])->update(['status' => 'completed']);
        }

        // Catat ke Activity Log
        ActivityLogService::log(
            'CREATE',
            'public_visit',
            $visit->id,
            "Input Kunjungan Lapangan Publik ke {$school->name} oleh Petugas ID {$validated['user_id']}",
            null,
            $validated,
            $validated['user_id']
        );

        // Integrasi Notifikasi WA & Google Calendar
        try {
            app(\App\Services\WhatsAppNotificationService::class)->notifySchoolVisit($visit);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed auto WA & Calendar in public visit: ' . $e->getMessage());
        }

        // Otomatis masukkan ke data Follow Up untuk konfirmasi FIX jadwal sosialisasi
        try {
            app(\App\Services\FollowUpSyncService::class)->syncVisit($visit);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed auto sync public visit to follow up: ' . $e->getMessage());
        }

        return redirect()->route('public-visit.index', ['campus_id' => $targetCampusId])
            ->with('success_message', "Terima kasih! Data kunjungan ke {$visit->school->name} (PIC: {$visit->recipient_name}) berhasil terkirim, kalender & WA terintegrasi, serta masuk ke Follow Up!");
    }
}
