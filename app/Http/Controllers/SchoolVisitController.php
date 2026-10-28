<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SchoolVisit;
use App\Models\User;
use App\Models\VisitPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

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

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('school', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $visits = $query->orderBy('visit_date', 'desc')->paginate(15)->withQueryString();

        $schools = School::whereHas('district', function($q) {
            $q->whereNotIn('name', \App\Models\District::EXCLUDED_DISTRICTS);
        })->orderBy('name')->get();
        
        $marketingUsers = User::active()->get();
        $visitPlans = VisitPlan::where('status', 'planned')->with('school')->get();

        return view('visits.index', compact('visits', 'schools', 'marketingUsers', 'visitPlans'));
    }

    /**
     * Show form page for recording a new field visit.
     */
    public function create(): View
    {
        $districts = \App\Models\District::activeDistricts()->orderBy('name')->get();
        $schools = School::with('district')->whereHas('district', function($q) {
            $q->whereNotIn('name', \App\Models\District::EXCLUDED_DISTRICTS);
        })->orderBy('name')->get();
        
        $marketingUsers = User::active()->get();
        $visitPlans = VisitPlan::where('status', 'planned')->with('school')->get();

        return view('visits.create', compact('districts', 'schools', 'marketingUsers', 'visitPlans'));
    }

    /**
     * Store a newly recorded field visit with socialization details.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'user_id' => ['required', 'exists:users,id'],
            'visit_plan_id' => ['nullable', 'exists:visit_plans,id'],
            'visit_date' => ['required', 'date'],
            'recipient_name' => ['required', 'string', 'max:150'], // 1. Nama Penerima Beserta Gelarnya
            'recipient_position' => ['required', 'string', 'max:100'], // 2. Jabatan
            'grade_12_class_count' => ['required', 'integer', 'min:1'], // 3. Jumlah Kelas 12
            'target_month_year' => ['required', 'string', 'max:50'], // 4. Target Bulan dan Tahun Sosialisasinya
            'target_date' => ['nullable', 'date'], // 5. Target Tanggal Sosialisasinya (Opsional)
            'target_time' => ['required', 'string', 'max:20'], // 6. Target Waktu Sosialisasinya
            'students_per_class' => ['required', 'integer', 'min:1'], // 7. Jumlah Siswa Per Kelasnya
            'socialization_method' => ['required', 'string', 'max:100'], // 8. Metode Sosialisasi
            'other_activity_name' => ['nullable', 'required_if:socialization_method,Lainnya', 'string', 'max:200'], // Input jika pilih Lainnya
            'purpose' => ['nullable', 'string', 'max:255'],
            'result' => ['nullable', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'photo' => [
                'nullable',
                'file',
                'max:51200', // 50MB
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf', 'rar', 'zip', 'doc', 'docx', '7z'];
                    if (!in_array($ext, $allowed)) {
                        $fail('Format berkas harus berupa Gambar (JPG/PNG), Dokumen (PDF/DOCX), atau Arsip (RAR/ZIP).');
                    }
                },
            ],
            'notes' => ['nullable', 'string'],
            'next_followup_date' => ['nullable', 'date'],
            'status' => ['required', 'in:berhasil,tidak_berhasil,tidak_bertemu,pending'],
        ]);

        $school = School::findOrFail($validated['school_id']);
        $this->ensureCheckInWithinSchoolRadius(
            $school,
            (float) $validated['latitude'],
            (float) $validated['longitude']
        );

        $validated['contact_person'] = $validated['recipient_name'] . ' (' . $validated['recipient_position'] . ')';

        $photo = $request->file('photo');
        unset($validated['photo']);
        if ($photo) {
            $validated['photo'] = $photo->store('visit-documents', 'public');
            if (!$validated['photo']) {
                throw new \RuntimeException('Foto dokumentasi gagal disimpan ke penyimpanan publik.');
            }
        }

        $visit = SchoolVisit::create($validated);

        // If connected to a visit plan, mark plan as completed
        if (!empty($validated['visit_plan_id'])) {
            VisitPlan::where('id', $validated['visit_plan_id'])->update(['status' => 'completed']);
        }

        // Otomatis masukkan ke data Follow Up untuk konfirmasi FIX jadwal sosialisasi
        try {
            app(\App\Services\FollowUpSyncService::class)->syncVisit($visit);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed auto sync visit to follow up: ' . $e->getMessage());
        }

        return redirect()->route('visits.index')
            ->with('toast', [
                'type' => 'success',
                'message' => "Hasil Kunjungan Lapangan ke {$visit->school->name} (PIC: {$visit->recipient_name}) berhasil dicatat & masuk ke Follow Up!",
            ]);
    }

    /**
     * Show form page for editing an existing field visit.
     */
    public function edit(SchoolVisit $visit): View
    {
        $visit->load(['school.district', 'user']);

        $districts = \App\Models\District::activeDistricts()->orderBy('name')->get();
        $schools = School::with('district')->whereHas('district', function($q) {
            $q->whereNotIn('name', \App\Models\District::EXCLUDED_DISTRICTS);
        })->orderBy('name')->get();

        $marketingUsers = User::active()->get();

        return view('visits.edit', compact('visit', 'districts', 'schools', 'marketingUsers'));
    }

    /**
     * Update an existing field visit record.
     */
    public function update(Request $request, SchoolVisit $visit): RedirectResponse
    {
        $validated = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'user_id' => ['required', 'exists:users,id'],
            'visit_date' => ['required', 'date'],
            'recipient_name' => ['required', 'string', 'max:150'],
            'recipient_position' => ['required', 'string', 'max:100'],
            'grade_12_class_count' => ['required', 'integer', 'min:1'],
            'target_month_year' => ['required', 'string', 'max:50'],
            'target_date' => ['nullable', 'date'],
            'target_time' => ['required', 'string', 'max:20'],
            'students_per_class' => ['required', 'integer', 'min:1'],
            'socialization_method' => ['required', 'string', 'max:100'],
            'other_activity_name' => ['nullable', 'required_if:socialization_method,Lainnya', 'string', 'max:200'],
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'photo' => [
                'nullable',
                'file',
                'max:51200', // 50MB
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf', 'rar', 'zip', 'doc', 'docx', '7z'];
                    if (!in_array($ext, $allowed)) {
                        $fail('Format berkas harus berupa Gambar (JPG/PNG), Dokumen (PDF/DOCX), atau Arsip (RAR/ZIP).');
                    }
                },
            ],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'in:berhasil,tidak_berhasil,tidak_bertemu,pending'],
        ]);

        $schoolChanged = (int) $validated['school_id'] !== (int) $visit->school_id;
        if ($schoolChanged && !isset($validated['latitude'], $validated['longitude'])) {
            throw ValidationException::withMessages([
                'latitude' => 'Ambil ulang GPS check-in saat mengubah sekolah kunjungan.',
            ]);
        }

        if (isset($validated['latitude'], $validated['longitude'])) {
            $school = School::findOrFail($validated['school_id']);
            $this->ensureCheckInWithinSchoolRadius(
                $school,
                (float) $validated['latitude'],
                (float) $validated['longitude']
            );
        }

        $validated['contact_person'] = $validated['recipient_name'] . ' (' . $validated['recipient_position'] . ')';

        $photo = $request->file('photo');
        unset($validated['photo']);
        if ($photo) {
            $newPhoto = $photo->store('visit-documents', 'public');
            if (!$newPhoto) {
                throw new \RuntimeException('Foto dokumentasi gagal disimpan ke penyimpanan publik.');
            }
            if ($visit->photo) {
                Storage::disk('public')->delete($visit->photo);
            }
            $validated['photo'] = $newPhoto;
        }

        $visit->update($validated);

        return redirect()->route('visits.index')
            ->with('toast', [
                'type' => 'success',
                'message' => "Data kunjungan ke {$visit->school->name} berhasil diperbarui.",
            ]);
    }

    /**
     * Remove a field visit record.
     */
    public function destroy(SchoolVisit $visit): RedirectResponse
    {
        $schoolName = $visit->school ? $visit->school->name : 'Sekolah';
        $visit->delete();

        return redirect()->route('visits.index')
            ->with('toast', [
                'type' => 'success',
                'message' => "Data kunjungan ke {$schoolName} berhasil dihapus.",
            ]);
    }

    private function ensureCheckInWithinSchoolRadius(School $school, float $latitude, float $longitude): void
    {
        if ($school->latitude === null || $school->longitude === null) {
            throw ValidationException::withMessages([
                'school_id' => 'Sekolah belum memiliki koordinat. Lengkapi koordinat sekolah sebelum check-in.',
            ]);
        }

        $earthRadiusMeters = 6371000;
        $lat1 = deg2rad((float) $school->latitude);
        $lat2 = deg2rad($latitude);
        $deltaLat = $lat2 - $lat1;
        $deltaLng = deg2rad($longitude - (float) $school->longitude);
        $a = sin($deltaLat / 2) ** 2 +
            cos($lat1) * cos($lat2) * sin($deltaLng / 2) ** 2;
        $distanceMeters = 2 * $earthRadiusMeters * atan2(sqrt($a), sqrt(1 - $a));

        if ($distanceMeters > 500) {
            throw ValidationException::withMessages([
                'latitude' => 'Check-in ditolak. Lokasi harus berada maksimal 500 meter dari sekolah.',
            ]);
        }
    }
}
