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
            if ($user && !AppContextService::canSwitchCampus($user)) {
                $userCampusId = $user->campus_id;
                $targetCampusId = $validated['campus_id'] ?? null;

                if ($targetCampusId === 'ALL' || (int)$targetCampusId !== (int)$userCampusId) {
                    $campusName = $user->campus?->name ?? 'kampus penugasan Anda';
                    $roleName = $user->role?->name ?? 'Pengguna';
                    return back()->with('toast', [
                        'type' => 'warning',
                        'message' => "Akun Anda terdaftar sebagai {$roleName} untuk {$campusName} dan tidak memiliki izin akses lintas kampus.",
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
            'campus_id' => 'nullable|exists:campuses,id',
        ]);

        $portalKey = $validated['portal_key'];
        $isActive = (bool) $validated['is_active'];
        $campusId = !empty($validated['campus_id']) ? (int) $validated['campus_id'] : null;

        // Jika user bukan staf pusat (terikat kampus cabang), kunci hanya pada kampusnya sendiri
        if (!AppContextService::canSwitchCampus($user) && $user->campus_id) {
            $campusId = $user->campus_id;
        }

        AppContextService::setPortalStatus($portalKey, $isActive, $user, $campusId);

        $portalMeta = AppContextService::getPublicPortalsStatus($campusId)[$portalKey] ?? null;
        $portalName = $portalMeta['name'] ?? $portalKey;
        $campus = $campusId ? \App\Models\Campus::find($campusId) : null;
        $campusSuffix = $campus ? " [{$campus->name}]" : '';
        $statusLabel = $isActive ? 'DIAKTIFKAN' : 'DINONAKTIFKAN';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "{$portalName}{$campusSuffix} berhasil {$statusLabel}!",
                'portal_key' => $portalKey,
                'campus_id' => $campusId,
                'is_active' => $isActive,
            ]);
        }

        return back()->with('toast', [
            'type' => $isActive ? 'success' : 'warning',
            'message' => "{$portalName}{$campusSuffix} berhasil {$statusLabel}!",
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
