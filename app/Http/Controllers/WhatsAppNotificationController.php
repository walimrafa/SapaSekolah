<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppNotificationLog;
use App\Services\AppSettingService;
use App\Services\WhatsAppNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WhatsAppNotificationController extends Controller
{
    /**
     * Dashboard Notifikasi WhatsApp & Riwayat Log Pengiriman.
     */
    public function index(Request $request): View
    {
        $query = WhatsAppNotificationLog::query()->latest();

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('notification_type', $request->type);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('recipient_phone', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(20)->withQueryString();

        $appSettings = AppSettingService::all();

        $today = now()->toDateString();
        $metrics = [
            'total_all' => WhatsAppNotificationLog::count(),
            'total_today' => WhatsAppNotificationLog::whereDate('created_at', $today)->count(),
            'proposal_masuk' => WhatsAppNotificationLog::where('notification_type', WhatsAppNotificationLog::TYPE_PROPOSAL_MASUK)->count(),
            'kunjungan_h1' => WhatsAppNotificationLog::where('notification_type', WhatsAppNotificationLog::TYPE_KUNJUNGAN_H1)->count(),
            'sosialisasi_h5' => WhatsAppNotificationLog::where('notification_type', WhatsAppNotificationLog::TYPE_SOSIALISASI_H5)->count(),
            'event_h7' => WhatsAppNotificationLog::where('notification_type', WhatsAppNotificationLog::TYPE_EVENT_H7)->count(),
        ];

        return view('whatsapp-notifications.index', compact('logs', 'appSettings', 'metrics'));
    }

    /**
     * Simpan pengaturan gateway WhatsApp.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $request->validate([
            'wa_gateway_provider' => 'required|in:fonnte,generic',
            'wa_api_token' => 'nullable|string|max:255',
            'wa_target_phone' => 'nullable|string|max:30',
            'wa_custom_endpoint' => 'nullable|url|max:255',
        ]);

        AppSettingService::setMany([
            'wa_gateway_provider' => $request->wa_gateway_provider,
            'wa_api_token' => trim((string)$request->wa_api_token),
            'wa_target_phone' => trim((string)$request->wa_target_phone),
            'wa_custom_endpoint' => trim((string)$request->wa_custom_endpoint),
        ]);

        return back()->with('success', 'Konfigurasi Notifikasi WhatsApp berhasil diperbarui.');
    }

    /**
     * Kirim pesan uji coba ke nomor tujuan.
     */
    public function sendTest(Request $request, WhatsAppNotificationService $service): RedirectResponse
    {
        $request->validate([
            'phone' => 'required|string|max:30',
            'message' => 'required|string|max:1000',
        ], [
            'phone.required' => 'Nomor WhatsApp tujuan wajib diisi.',
            'message.required' => 'Pesan uji coba tidak boleh kosong.',
        ]);

        $res = $service->sendMessage(
            $request->phone,
            $request->message,
            WhatsAppNotificationLog::TYPE_TEST,
            'Nomor Uji Coba'
        );

        if ($res['is_sent']) {
            return back()->with('success', 'Pesan WhatsApp uji coba berhasil terkirim via Gateway!');
        }

        return back()->with('success', 'Pesan uji coba tersimpan dalam mode Simulasi/Log. ' . $res['message']);
    }

    /**
     * Trigger manual pengecekan pengingat hari ini (H-1 Kunjungan, H-5 Sosialisasi, H-7 Event).
     */
    public function triggerReminders(WhatsAppNotificationService $service): RedirectResponse
    {
        $stats = $service->sendScheduledReminders(force: true);

        $proposalCount = $stats['proposal_h0'] ?? 0;
        $msg = "Berhasil! {$stats['total_sent']} notifikasi diproses ({$stats['kunjungan_h1']} Kunjungan H-1, {$stats['sosialisasi_h5']} Sosialisasi H-5, {$stats['event_h7']} Event H-7, {$proposalCount} Proposal H-0). Undangan Google Calendar (.ics) telah otomatis disinkronkan ke email akun masing-masing!";

        return back()->with('success', $msg);
    }
}
