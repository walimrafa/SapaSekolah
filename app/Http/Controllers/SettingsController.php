<?php

namespace App\Http\Controllers;

use App\Models\PeriodClosingRequest;
use App\Services\AppSettingService;
use App\Services\PeriodClosingService;
use App\Services\SchoolScoringService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Display Campus Settings & System Branding Configuration.
     */
    public function index(): View
    {
        $settings = AppSettingService::all();
        $activePeriodRequest = PeriodClosingService::getActiveRequest();
        $latestPeriodRequest = PeriodClosingService::getLatestRequest();

        return view('settings.index', compact('settings', 'activePeriodRequest', 'latestPeriodRequest'));
    }

    /**
     * Update Application Branding, Header, Footer & Campus Reference Settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => ['required', 'string', 'max:100'],
            'app_subtitle' => ['required', 'string', 'max:150'],
            'app_logo_url' => ['nullable', 'string', 'max:500'],
            'logo_file' => ['nullable', 'image', 'max:2048'], // Max 2MB logo upload
            'campus_building_url' => ['nullable', 'string', 'max:500'],
            'building_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'], // Max 5MB building photo upload
            'remove_building_photo' => ['nullable'],
            'header_title' => ['required', 'string', 'max:200'],
            'footer_text' => ['required', 'string', 'max:300'],
            'footer_subtext' => ['required', 'string', 'max:200'],
            'campus_name' => ['required', 'string', 'max:200'],
            'campus_address' => ['required', 'string'],
            'campus_latitude' => ['required', 'numeric', 'between:-90,90'],
            'campus_longitude' => ['required', 'numeric', 'between:-180,180'],
            'campus_phone' => ['nullable', 'string', 'max:50'],
            'campus_email' => ['nullable', 'email', 'max:100'],
            'campus_website' => ['nullable', 'string', 'max:200'],
            'theme_color_primary' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_color_accent' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_color_secondary' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_color_bg' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sidebar_labels' => ['nullable', 'array'],
            'sidebar_labels.*' => ['nullable', 'string', 'max:100'],
        ]);

        if ($request->hasFile('logo_file')) {
            $currentLogo = AppSettingService::get('app_logo_url');
            if ($currentLogo && str_starts_with($currentLogo, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $currentLogo);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('logo_file')->store('branding', 'public');
            $validated['app_logo_url'] = '/storage/' . $path;
        }

        if ($request->hasFile('building_file')) {
            $currentBuilding = AppSettingService::get('campus_building_url');
            if ($currentBuilding && str_starts_with($currentBuilding, '/storage/') && !str_ends_with($currentBuilding, 'ubsi_karawang_building.jpg')) {
                $oldPath = str_replace('/storage/', '', $currentBuilding);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('building_file')->store('branding', 'public');
            $validated['campus_building_url'] = '/storage/' . $path;
        } elseif ($request->boolean('remove_building_photo')) {
            $currentBuilding = AppSettingService::get('campus_building_url');
            if ($currentBuilding && str_starts_with($currentBuilding, '/storage/') && !str_ends_with($currentBuilding, 'ubsi_karawang_building.jpg')) {
                $oldPath = str_replace('/storage/', '', $currentBuilding);
                Storage::disk('public')->delete($oldPath);
            }
            $validated['campus_building_url'] = null;
        }

        unset($validated['logo_file'], $validated['building_file'], $validated['remove_building_photo']);

        AppSettingService::setMany($validated);

        // Recalculate priority scores if reference lat/lng updated
        app(SchoolScoringService::class)->recalculateAllSchoolScores();

        return redirect()->route('settings.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Pengaturan Branding, Logo, Navigasi Sidebar, Tampilan & Kampus berhasil diperbarui!',
            ]);
    }

    /**
     * Ajukan Tutup Periode Pemasaran oleh Admin (Menunggu Persetujuan 3 Pihak: Pimpinan, Koordinator, CS).
     */
    public function closePeriod(Request $request): RedirectResponse
    {
        $request->validate([
            'admin_password' => ['required', 'string'],
            'confirmation_text' => ['required', 'string'],
            'reason' => ['nullable', 'string', 'max:500'],
        ], [
            'admin_password.required' => 'Password akun Admin wajib diisi sebagai otorisasi keamanan tingkat tinggi.',
            'confirmation_text.required' => 'Silakan ketik kalimat konfirmasi yang diminta.',
        ]);

        // Verifikasi kata kunci konfirmasi persis
        if (trim(strtoupper($request->confirmation_text)) !== 'TUTUP PERIODE') {
            return back()->with('toast', [
                'type' => 'danger',
                'message' => 'Kalimat konfirmasi tidak sesuai! Ketik "TUTUP PERIODE" persis seperti yang diminta.',
            ]);
        }

        // Verifikasi password admin yang sedang login
        if (!Hash::check($request->admin_password, auth()->user()->password)) {
            return back()->with('toast', [
                'type' => 'danger',
                'message' => 'Otorisasi gagal: Password Admin yang Anda masukkan salah!',
            ]);
        }

        // Cek apakah sudah ada permohonan aktif yang pending
        $existing = PeriodClosingService::getActiveRequest();
        if ($existing) {
            return redirect()->route('period-closing.index')->with('toast', [
                'type' => 'warning',
                'message' => 'Masih ada permohonan Tutup Periode yang sedang menunggu persetujuan.',
            ]);
        }

        $reason = $request->input('reason', 'Penutupan Buku & Periode Pemasaran Siklus Baru');

        PeriodClosingRequest::create([
            'requested_by' => auth()->id(),
            'reason' => $reason,
            'academic_year' => date('Y') . '/' . (date('Y') + 1),
            'status' => 'pending',
            'pimpinan_status' => 'pending',
            'koordinator_status' => 'pending',
            'cs_status' => 'pending',
        ]);

        return redirect()->route('period-closing.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Permohonan Tutup Periode berhasil diajukan! Menunggu persetujuan mutlak dari 3 pihak: Pimpinan, Koordinator Marketing, dan Customer Service.',
            ]);
    }
}
