<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\School;
use App\Models\SchoolVisit;
use App\Models\User;
use App\Models\VisitPlan;
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
        $districts = District::activeDistricts()->orderBy('name')->get();

        $schools = School::with('district')
            ->whereHas('district', function ($q) {
                $q->whereNotIn('name', District::EXCLUDED_DISTRICTS);
            })
            ->orderBy('name')
            ->get();

        $marketingUsers = User::active()->get();

        return view('public-visit', compact('districts', 'schools', 'marketingUsers'));
    }

    /**
     * Store a public field visit submission (No Login Required).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'user_id' => ['required', 'exists:users,id'],
            'visit_date' => ['required', 'date'],
            'recipient_name' => ['required', 'string', 'max:150'], // 1. Nama Penerima Beserta Gelarnya
            'recipient_position' => ['required', 'string', 'max:100'], // 2. Jabatan
            'grade_12_class_count' => ['required', 'integer', 'min:1'], // 3. Jumlah Kelas 12
            'target_month_year' => ['required', 'string', 'max:50'], // 4. Target Bulan dan Tahun Sosialisasinya
            'target_date' => ['nullable', 'date'], // 5. Target Tanggal Sosialisasinya (Opsional)
            'target_time' => ['required', 'string', 'max:20'], // 6. Target Waktu Sosialisasinya
            'students_per_class' => ['required', 'integer', 'min:1'], // 7. Jumlah Siswa Per Kelasnya
            'socialization_method' => ['required', 'string', 'max:100'], // 8. Metode Sosialisasi
            'other_activity_name' => ['nullable', 'required_if:socialization_method,Lainnya', 'string', 'max:200'], // Dynamic input
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'in:berhasil,tidak_bertemu,tidak_berhasil,pending'],
        ]);

        $validated['contact_person'] = $validated['recipient_name'] . ' (' . $validated['recipient_position'] . ')';

        $visit = SchoolVisit::create($validated);

        // Otomatis masukkan ke data Follow Up untuk konfirmasi FIX jadwal sosialisasi
        try {
            app(\App\Services\FollowUpSyncService::class)->syncVisit($visit);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed auto sync public visit to follow up: ' . $e->getMessage());
        }

        return redirect()->route('public-visit.index')
            ->with('success_message', "Terima kasih! Data kunjungan ke {$visit->school->name} (PIC: {$visit->recipient_name}) berhasil terkirim & masuk ke Follow Up!");
    }
}
