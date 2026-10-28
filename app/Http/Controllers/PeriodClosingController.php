<?php

namespace App\Http\Controllers;

use App\Models\PeriodClosingRequest;
use App\Services\PeriodClosingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PeriodClosingController extends Controller
{
    /**
     * Tampilkan halaman status dan tinjauan persetujuan Tutup Periode.
     */
    public function index(): View
    {
        $activeRequest = PeriodClosingService::getActiveRequest();
        $latestRequest = PeriodClosingService::getLatestRequest();
        $historyRequests = PeriodClosingRequest::with(['requester', 'pimpinan', 'koordinator', 'customerService', 'executor'])
            ->orderByDesc('id')
            ->paginate(10);

        $currentUser = auth()->user();
        $canApprove = $activeRequest ? $activeRequest->canUserApprove($currentUser) : false;
        $userRoleSlot = $activeRequest ? $activeRequest->getUserRoleSlot($currentUser) : null;

        return view('period-closing.index', compact(
            'activeRequest',
            'latestRequest',
            'historyRequests',
            'canApprove',
            'userRoleSlot'
        ));
    }

    /**
     * Admin mengajukan permohonan tutup periode baru.
     */
    public function storeRequest(Request $request): RedirectResponse
    {
        $request->validate([
            'admin_password' => ['required', 'string'],
            'confirmation_text' => ['required', 'string'],
            'reason' => ['required', 'string', 'max:500'],
            'academic_year' => ['nullable', 'string', 'max:50'],
        ], [
            'admin_password.required' => 'Password akun Admin wajib dimasukkan untuk otorisasi.',
            'confirmation_text.required' => 'Ketik TUTUP PERIODE untuk konfirmasi.',
            'reason.required' => 'Alasan atau catatan penutupan periode wajib diisi.',
        ]);

        if (trim(strtoupper($request->confirmation_text)) !== 'TUTUP PERIODE') {
            return back()->with('toast', [
                'type' => 'danger',
                'message' => 'Kalimat konfirmasi tidak sesuai! Ketik "TUTUP PERIODE" persis.',
            ]);
        }

        if (!Hash::check($request->admin_password, auth()->user()->password)) {
            return back()->with('toast', [
                'type' => 'danger',
                'message' => 'Password Admin yang Anda masukkan salah!',
            ]);
        }

        // Cek apakah ada permohonan pending yang belum selesai
        $existing = PeriodClosingService::getActiveRequest();
        if ($existing) {
            return redirect()->route('period-closing.index')->with('toast', [
                'type' => 'warning',
                'message' => 'Masih ada permohonan Tutup Periode yang sedang menunggu persetujuan.',
            ]);
        }

        $newRequest = PeriodClosingRequest::create([
            'requested_by' => auth()->id(),
            'reason' => $request->reason,
            'academic_year' => $request->academic_year ?: date('Y') . '/' . (date('Y') + 1),
            'status' => 'pending',
            'pimpinan_status' => 'pending',
            'koordinator_status' => 'pending',
            'cs_status' => 'pending',
        ]);

        return redirect()->route('period-closing.index')->with('toast', [
            'type' => 'success',
            'message' => 'Permohonan Tutup Periode berhasil diajukan! Menunggu persetujuan dari 3 pihak: Pimpinan, Koordinator Marketing, dan Customer Service.',
        ]);
    }

    /**
     * Berikan respon persetujuan (Setujui / Tolak) dari Pimpinan / Koordinator Marketing / Customer Service.
     */
    public function respond(Request $request, PeriodClosingRequest $periodClosingRequest): RedirectResponse
    {
        $request->validate([
            'decision' => ['required', 'in:approve,reject'],
            'password' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'password.required' => 'Password akun Anda wajib dimasukkan untuk memverifikasi keputusan.',
            'decision.required' => 'Pilih keputusan: Setujui atau Tolak.',
        ]);

        // Verifikasi password pengguna yang sedang mengambil tindakan
        if (!Hash::check($request->password, auth()->user()->password)) {
            return back()->with('toast', [
                'type' => 'danger',
                'message' => 'Password yang Anda masukkan salah! Otorisasi gagal.',
            ]);
        }

        if ($request->decision === 'reject' && empty(trim($request->notes))) {
            return back()->with('toast', [
                'type' => 'danger',
                'message' => 'Catatan/alasan penolakan wajib diisi jika Anda memilih Tolak.',
            ]);
        }

        $result = PeriodClosingService::recordDecision(
            $periodClosingRequest,
            auth()->user(),
            $request->decision,
            $request->notes
        );

        if (!$result['success']) {
            return back()->with('toast', [
                'type' => 'danger',
                'message' => $result['message'],
            ]);
        }

        return redirect()->route('period-closing.index')->with('toast', [
            'type' => $result['completed'] ? 'success' : ($request->decision === 'reject' ? 'warning' : 'info'),
            'message' => $result['message'],
        ]);
    }

    /**
     * Batalkan permohonan tutup periode oleh Admin.
     */
    public function cancel(PeriodClosingRequest $periodClosingRequest): RedirectResponse
    {
        if ($periodClosingRequest->status !== 'pending') {
            return back()->with('toast', [
                'type' => 'warning',
                'message' => 'Hanya permohonan dengan status pending yang dapat dibatalkan.',
            ]);
        }

        $periodClosingRequest->update([
            'status' => 'cancelled',
            'rejection_reason' => 'Dibatalkan oleh Admin pemohon (' . auth()->user()->name . ')',
        ]);

        return redirect()->route('period-closing.index')->with('toast', [
            'type' => 'info',
            'message' => 'Permohonan Tutup Periode berhasil dibatalkan.',
        ]);
    }
}
