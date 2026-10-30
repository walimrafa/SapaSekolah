<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\Campus;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\AppContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ContextController extends Controller
{
    /**
     * Switch Active Campus or Active Academic Period in Session.
     */
    public function switchContext(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'campus_id' => 'nullable',
            'period_id' => 'nullable',
        ]);

        $activeCampusName = 'Semua Kampus';
        if ($request->has('campus_id')) {
            $user = Auth::user();
            if ($user && $user->campus_id && !AppContextService::canSwitchCampus($user)) {
                if ($validated['campus_id'] !== 'ALL' && (int)$validated['campus_id'] !== (int)$user->campus_id) {
                    return back()->with('toast', [
                        'type' => 'warning',
                        'message' => 'Akun Anda ditugaskan khusus untuk ' . ($user->campus?->name ?? 'kampus tertentu') . ' dan tidak memiliki hak akses lintas kampus.',
                    ]);
                }
            }

            AppContextService::setActiveCampus($validated['campus_id']);
            $campus = Campus::find($validated['campus_id']);
            if ($campus) {
                $activeCampusName = $campus->name;
            }
        }

        $activePeriodName = 'Semua Periode';
        if ($request->has('period_id')) {
            AppContextService::setActivePeriod($validated['period_id']);
            $period = AcademicPeriod::find($validated['period_id']);
            if ($period) {
                $activePeriodName = $period->name;
            }
        }

        ActivityLogService::log(
            'SWITCH_CONTEXT',
            'system',
            null,
            "Mengganti filter aktif ke Kampus: '{$activeCampusName}', Periode: '{$activePeriodName}'",
            null,
            ['campus' => $activeCampusName, 'period' => $activePeriodName]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Konteks berhasil dialihkan.',
                'campus' => $activeCampusName,
                'period' => $activePeriodName,
            ]);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Filter aktif dialihkan ke Kampus: {$activeCampusName} | Periode: {$activePeriodName}",
        ]);
    }

    /**
     * Toggle status aktif / tidak aktif untuk Portal Publik (Tanpa Login).
     * Berwenang: Admin, Pimpinan, dan Koordinator Marketing.
     */
    public function togglePortal(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        if (!$user || (!$user->isAdmin() && !$user->isPimpinan() && !$user->isKoordinator())) {
            abort(403, 'Akses ditolak. Pengaturan status portal publik hanya dapat diubah oleh Admin, Pimpinan, dan Koordinator Marketing.');
        }

        $validated = $request->validate([
            'portal_key' => 'required|string|in:public_visit,student_questionnaire,school_event_proposal,event_student_lead',
            'is_active' => 'required|boolean',
        ]);

        $portalKey = $validated['portal_key'];
        $isActive = (bool) $validated['is_active'];

        AppContextService::setPortalStatus($portalKey, $isActive, $user);

        $portalName = AppContextService::getPublicPortalsStatus()[$portalKey]['name'] ?? $portalKey;
        $statusLabel = $isActive ? 'DIAKTIFKAN' : 'DINONAKTIFKAN';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "{$portalName} berhasil {$statusLabel}!",
                'portal_key' => $portalKey,
                'is_active' => $isActive,
            ]);
        }

        return back()->with('toast', [
            'type' => $isActive ? 'success' : 'warning',
            'message' => "{$portalName} berhasil {$statusLabel}!",
        ]);
    }

    /**
     * Halaman Profil & Ganti Password Mandiri.
     */
    public function showProfile(): View
    {
        $user = Auth::user();
        return view('auth.profile', compact('user'));
    }

    /**
     * Proses Ubah Password Mandiri oleh Petugas.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.min' => 'Password baru minimal 6 karakter.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini yang Anda masukkan salah.']);
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        ActivityLogService::log(
            'CHANGE_PASSWORD',
            'auth',
            $user->id,
            "Pengguna {$user->name} berhasil mengubah kata sandi mandiri."
        );

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Kata sandi Anda berhasil diperbarui dengan aman!',
        ]);
    }
}
