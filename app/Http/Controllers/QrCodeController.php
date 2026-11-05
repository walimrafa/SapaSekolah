<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class QrCodeController extends Controller
{
    /**
     * Display Master QR Code Center for Login and public access.
     */
    public function index(Request $request): View
    {
        $baseUrl = $request->getSchemeAndHttpHost();
        $activeCampus = \App\Services\AppContextService::getActiveCampus();
        $campusParams = $activeCampus ? ['campus_id' => $activeCampus->id] : [];
        
        $loginUrl = $baseUrl . route('login', [], false);
        $publicVisitUrl = $baseUrl . route('public-visit.index', $campusParams, false);
        $studentQuestionnaireUrl = $baseUrl . route('student-questionnaire.create', $campusParams, false);
        $schoolEventUrl = $baseUrl . route('school-events.create', $campusParams, false);
        $eventStudentUrl = $baseUrl . route('event-students.create', $campusParams, false);

        $loginQrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=15&data=" . urlencode($loginUrl);
        $publicVisitQrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=15&data=" . urlencode($publicVisitUrl);
        $studentQuestionnaireQrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=15&data=" . urlencode($studentQuestionnaireUrl);
        $schoolEventQrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=15&data=" . urlencode($schoolEventUrl);
        $eventStudentQrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=15&data=" . urlencode($eventStudentUrl);

        return view('qr-codes.index', compact(
            'loginUrl',
            'publicVisitUrl',
            'studentQuestionnaireUrl',
            'schoolEventUrl',
            'eventStudentUrl',
            'loginQrUrl',
            'publicVisitQrUrl',
            'studentQuestionnaireQrUrl',
            'schoolEventQrUrl',
            'eventStudentQrUrl',
            'activeCampus'
        ));
    }

    /**
     * Download PNG QR Code file for specified type (login, public-visit, school-events, event-students, etc.).
     */
    public function download(Request $request, string $type)
    {
        $baseUrl = $request->getSchemeAndHttpHost();
        $activeCampus = \App\Services\AppContextService::getActiveCampus();
        $campusParams = $activeCampus ? ['campus_id' => $activeCampus->id] : [];
        $campusSlug = $activeCampus ? '_' . \Illuminate\Support\Str::slug($activeCampus->name) : '';

        if ($type === 'login') {
            $targetUrl = $baseUrl . route('login', [], false);
            $filename = 'QR_Code_Login_SAPA_Sekolah.png';
        } elseif ($type === 'student-questionnaire') {
            $targetUrl = $baseUrl . route('student-questionnaire.create', $campusParams, false);
            $filename = "QR_Code_Kuisioner_Siswa{$campusSlug}.png";
        } elseif ($type === 'public-visit') {
            $targetUrl = $baseUrl . route('public-visit.index', $campusParams, false);
            $filename = "QR_Code_Input_Kunjungan{$campusSlug}.png";
        } elseif ($type === 'school-events') {
            $targetUrl = $baseUrl . route('school-events.create', $campusParams, false);
            $filename = "QR_Code_Pengajuan_Proposal_Event{$campusSlug}.png";
        } elseif ($type === 'event-students') {
            $eventId = $request->query('event_id');
            $params = array_filter(['event_id' => $eventId]) + $campusParams;
            $targetUrl = $baseUrl . route('event-students.create', $params, false);
            $filename = $eventId 
                ? "QR_Code_Hasil_Support_Event_ID_{$eventId}{$campusSlug}.png" 
                : "QR_Code_Hasil_Support_Event_Siswa{$campusSlug}.png";
        } else {
            abort(404);
        }

        $qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=600x600&margin=25&data=" . urlencode($targetUrl);

        $response = Http::timeout(10)->get($qrApiUrl);

        if ($response->successful()) {
            return response($response->body())
                ->header('Content-Type', 'image/png')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        }

        return back()->with('toast', [
            'type' => 'error',
            'message' => 'Gagal mengunduh file QR Code. Silakan coba lagi.',
        ]);
    }
}
