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
        
        $loginUrl = $baseUrl . route('login', [], false);
        $publicVisitUrl = $baseUrl . route('public-visit.index', [], false);
        $studentQuestionnaireUrl = $baseUrl . route('student-questionnaire.create', [], false);
        $schoolEventUrl = $baseUrl . route('school-events.create', [], false);
        $eventStudentUrl = $baseUrl . route('event-students.create', [], false);

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
            'eventStudentQrUrl'
        ));
    }

    /**
     * Download PNG QR Code file for specified type (login, public-visit, school-events, event-students, etc.).
     */
    public function download(Request $request, string $type)
    {
        $baseUrl = $request->getSchemeAndHttpHost();

        if ($type === 'login') {
            $targetUrl = $baseUrl . route('login', [], false);
            $filename = 'QR_Code_Login_SAPA_Sekolah.png';
        } elseif ($type === 'student-questionnaire') {
            $targetUrl = $baseUrl . route('student-questionnaire.create', [], false);
            $filename = 'QR_Code_Kuisioner_Siswa_SAPA_Sekolah.png';
        } elseif ($type === 'public-visit') {
            $targetUrl = $baseUrl . route('public-visit.index', [], false);
            $filename = 'QR_Code_Input_Kunjungan_SAPA_Sekolah.png';
        } elseif ($type === 'school-events') {
            $targetUrl = $baseUrl . route('school-events.create', [], false);
            $filename = 'QR_Code_Pengajuan_Proposal_Event_Sekolah.png';
        } elseif ($type === 'event-students') {
            $eventId = $request->query('event_id');
            $targetUrl = $baseUrl . route('event-students.create', array_filter(['event_id' => $eventId]), false);
            $filename = $eventId 
                ? "QR_Code_Hasil_Support_Event_ID_{$eventId}.png" 
                : 'QR_Code_Hasil_Support_Event_Siswa.png';
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
