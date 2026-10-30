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
use Illuminate\View\View;

class PublicVisitController extends Controller
{
    /**
     * Display public field visit socialization submission form (No Login Required).
     */
    public function index(): View
    {
        if (!AppContextService::isPortalActive(AppContextService::PORTAL_PUBLIC_VISIT)) {
            return view('errors.portal-disabled', [
                'portalName' => 'Portal Input Kunjungan Lapangan',
            ]);
        }

        $activeCampus = AppContextService::getActiveCampus();

        $districts = District::has('schools')
            ->withCount('schools')
            ->orderBy('name')
            ->get();

        $schoolsQuery = School::with(['district', 'schoolType'])
            ->whereHas('district', function ($q) {
                $q->whereNotIn('name', District::EXCLUDED_DISTRICTS);
            });

        if ($activeCampus) {
            $schoolsQuery->where(function ($q) use ($activeCampus) {
                $q->where('campus_id', $activeCampus->id)
                    ->orWhereNull('campus_id');
            });
        }

        $schools = $schoolsQuery->orderBy('name')->get();
        $marketingUsers = User::active()->get();

        return view('public-visit', compact('districts', 'schools', 'marketingUsers'));
    }

    /**
     * Store a public field visit submission (No Login Required).
     */
    public function store(Request $request): RedirectResponse
    {
        if (!AppContextService::isPortalActive(AppContextService::PORTAL_PUBLIC_VISIT)) {
            return back()->with('error', 'Portal Input Kunjungan Lapangan saat ini sedang ditutup atau dinonaktifkan.');
        }

        $validated = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'user_id' => ['required', 'exists:users,id'],
            'visit_date' => ['required', 'date'],
            'headmaster_name' => ['nullable', 'string', 'max:150'],
            'recipient_name' => ['required', 'string', 'max:150'],
            'recipient_position' => ['required', 'string', 'max:100'],
            'recipient_phone' => ['nullable', 'string', 'max:30'],
            'grade_12_class_count' => ['required', 'integer', 'min:1'],
            'target_month_year' => ['required', 'string', 'max:50'],
            'target_date' => ['nullable', 'date'],
            'target_time' => ['required', 'string', 'max:20'],
            'students_per_class' => ['required', 'integer', 'min:1'],
            'socialization_method' => ['required', 'string', 'max:100'],
            'other_activity_name' => ['nullable', 'required_if:socialization_method,Lainnya', 'string', 'max:200'],
            'notes' => ['nullable', 'string'],
            'photo' => [
                'nullable',
                'file',
                'max:51200',
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf', 'rar', 'zip', 'doc', 'docx', '7z'];
                    if (!in_array($ext, $allowed)) {
                        $fail('Format berkas harus berupa Gambar (JPG/PNG), Dokumen (PDF/DOCX), atau Arsip (RAR/ZIP).');
                    }
                },
            ],
            'status' => ['required', 'in:berhasil,tidak_bertemu,tidak_berhasil,pending'],
        ]);

        $school = School::findOrFail($validated['school_id']);
        $validated['contact_person'] = $validated['recipient_name'] . ' (' . $validated['recipient_position'] . ')';

        // Set campus & academic period context
        $activeCampus = AppContextService::getActiveCampus();
        $activePeriod = AppContextService::getActivePeriod();
        $validated['campus_id'] = $school->campus_id ?: ($activeCampus?->id);
        $validated['academic_period_id'] = $activePeriod?->id;

        $photo = $request->file('photo');
        unset($validated['photo']);
        if ($photo) {
            $gdriveResult = \App\Services\GoogleDriveService::uploadDocumentation(
                $photo,
                $school->name,
                $validated['visit_date']
            );
            $validated['photo'] = $gdriveResult['stored_path'];
            $validated['google_drive_folder_url'] = $gdriveResult['drive_folder_url'];
            $validated['google_drive_file_url'] = $gdriveResult['drive_file_url'];
        } else {
            $validated['google_drive_folder_url'] = \App\Services\GoogleDriveService::buildFolderUrlForSchool(
                $school->name,
                $validated['visit_date']
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

        return redirect()->route('public-visit.index')
            ->with('success_message', "Terima kasih! Data kunjungan ke {$visit->school->name} (PIC: {$visit->recipient_name}) berhasil terkirim, kalender & WA terintegrasi, serta masuk ke Follow Up!");
    }
}
