<?php

namespace App\Services;

use App\Models\FollowUp;
use App\Models\SchoolEventProposal;
use App\Models\User;
use App\Models\VisitPlan;
use App\Models\WhatsAppNotificationLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationService
{
    /**
     * Send WhatsApp message via free supporting app (Fonnte or Generic Gateway)
     * with automatic fallback to database simulation log.
     */
    public function sendMessage(
        string $phone,
        string $message,
        string $type = WhatsAppNotificationLog::TYPE_TEST,
        ?string $recipientName = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $calendarUrl = null,
        ?string $emailRecipients = null
    ): array {
        $cleanPhone = $this->formatIndonesianPhone($phone);

        if (empty($cleanPhone)) {
            return [
                'status' => false,
                'message' => 'Nomor WhatsApp tidak valid atau kosong.',
            ];
        }

        $appSettings = AppSettingService::all();
        $provider = $appSettings['wa_gateway_provider'] ?? 'fonnte';
        $apiToken = trim((string)($appSettings['wa_api_token'] ?? env('FONNTE_TOKEN', '')));
        $customEndpoint = trim((string)($appSettings['wa_custom_endpoint'] ?? ''));

        $logStatus = WhatsAppNotificationLog::STATUS_SIMULASI;
        $responsePayload = null;
        $isSent = false;
        $errorMsg = null;

        // If API token is provided, attempt to send via Free Gateway
        if (!empty($apiToken)) {
            try {
                if ($provider === 'fonnte') {
                    // Fonnte Free Tier API Endpoint
                    $response = Http::withHeaders([
                        'Authorization' => $apiToken,
                    ])->timeout(12)->post('https://api.fonnte.com/send', [
                        'target' => $cleanPhone,
                        'message' => $message,
                        'countryCode' => '62',
                    ]);

                    $responsePayload = $response->body();
                    $json = $response->json();

                    if ($response->successful() && !empty($json['status'])) {
                        $isSent = true;
                        $logStatus = WhatsAppNotificationLog::STATUS_TERKIRIM;
                    } else {
                        $logStatus = WhatsAppNotificationLog::STATUS_GAGAL;
                        $errorMsg = $json['reason'] ?? 'Fonnte mengembalikan status gagal.';
                    }
                } elseif (!empty($customEndpoint)) {
                    // Custom / Self-Hosted Gateway (e.g. Baileys / WPPConnect / Generic Webhook)
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $apiToken,
                        'Content-Type' => 'application/json',
                    ])->timeout(12)->post($customEndpoint, [
                        'phone' => $cleanPhone,
                        'target' => $cleanPhone,
                        'message' => $message,
                    ]);

                    $responsePayload = $response->body();
                    if ($response->successful()) {
                        $isSent = true;
                        $logStatus = WhatsAppNotificationLog::STATUS_TERKIRIM;
                    } else {
                        $logStatus = WhatsAppNotificationLog::STATUS_GAGAL;
                        $errorMsg = 'Gateway HTTP error ' . $response->status();
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('WhatsApp Gateway error: ' . $e->getMessage());
                $logStatus = WhatsAppNotificationLog::STATUS_GAGAL;
                $responsePayload = $e->getMessage();
                $errorMsg = $e->getMessage();
            }
        } else {
            // Mode Simulasi / Logging jika token belum diisi di Settings
            $logStatus = WhatsAppNotificationLog::STATUS_SIMULASI;
            $responsePayload = 'Mode simulasi aktif (Token Gateway belum dikonfigurasi). Pesan tersimpan di log dan dapat dikirim manual via wa.me.';
        }

        // Simpan Log ke Database dengan Calendar URL & Penerima Email
        $log = WhatsAppNotificationLog::create([
            'notification_type' => $type,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'recipient_phone' => $cleanPhone,
            'recipient_name' => $recipientName,
            'message' => $message,
            'status' => $logStatus,
            'response_payload' => is_string($responsePayload) ? $responsePayload : json_encode($responsePayload),
            'calendar_url' => $calendarUrl,
            'email_recipients' => $emailRecipients,
            'sent_at' => $isSent ? now() : null,
        ]);

        return [
            'status' => $isSent || $logStatus === WhatsAppNotificationLog::STATUS_SIMULASI,
            'is_sent' => $isSent,
            'mode' => $logStatus,
            'message' => $isSent ? 'Pesan WhatsApp berhasil dikirim.' : ($errorMsg ?: 'Pesan disimpan dalam mode simulasi / log.'),
            'log' => $log,
            'direct_wa_link' => $log->direct_wa_link,
            'calendar_url' => $calendarUrl,
            'email_recipients' => $emailRecipients,
        ];
    }

    /**
     * 1. Notifikasi Proposal Baru Masuk (H-0 / Real-time ketika sekolah submit proposal).
     */
    public function notifyNewProposal(SchoolEventProposal $proposal): array
    {
        $targetPhone = $this->getAdminNotificationPhone();
        $appName = AppSettingService::get('app_name', 'SAPA Sekolah');
        $campusName = AppSettingService::get('campus_name', 'Universitas BSI');

        $estBudgetFormatted = $proposal->estimated_budget 
            ? 'Rp ' . number_format((float) $proposal->estimated_budget, 0, ',', '.') 
            : 'Belum ditentukan';

        $eventDateStr = $proposal->event_date ? Carbon::parse($proposal->event_date)->translatedFormat('d F Y') : '-';
        if ($proposal->event_end_date && $proposal->event_end_date != $proposal->event_date) {
            $eventDateStr .= ' s/d ' . Carbon::parse($proposal->event_end_date)->translatedFormat('d F Y');
        }

        // Generate Google Calendar Link & Kirim Undangan Email (.ics) ke Email Tim Akun Masing-masing
        $gcalRes = GoogleCalendarNotificationService::notifyNewProposal($proposal);
        $calendarUrl = $gcalRes['gcal_url'] ?? null;
        $emailRecipients = implode(', ', $gcalRes['recipients'] ?? []);

        $message = "📢 *NOTIFIKASI PROPOSAL BARU MASUK (H-0)*\n"
            . "Sistem {$appName} - {$campusName}\n\n"
            . "Telah masuk pengajuan proposal kegiatan/support dari sekolah:\n\n"
            . "📋 *No. Referensi:* #{$proposal->reference_no}\n"
            . "🏫 *Sekolah:* {$proposal->school_name}\n"
            . "🎉 *Nama Event:* {$proposal->event_name}\n"
            . "📅 *Jadwal Acara:* {$eventDateStr}\n"
            . "⏰ *Waktu & Lokasi:* {$proposal->event_time} @ {$proposal->event_location}\n"
            . "👥 *Target Peserta:* {$proposal->target_participants}\n"
            . "💰 *Estimasi Anggaran:* {$estBudgetFormatted}\n"
            . "🎁 *Bentuk Support:* {$proposal->support_type_requested}\n"
            . "👤 *PIC Sekolah:* {$proposal->pic_name} ({$proposal->pic_phone})\n\n"
            . ($calendarUrl ? "📅 *Simpan ke Google Calendar:*\n{$calendarUrl}\n\n" : "")
            . "📧 _Undangan kalender (.ics) otomatis dikirimkan ke email tim: " . ($emailRecipients ?: '-') . "_\n\n"
            . "Mohon tim marketing / koordinator segera meninjau berkas proposal di sistem SAPA Sekolah:\n"
            . url(route('event-proposals.index', [], false)) . "\n\n"
            . "_Pesan otomatis sistem SAPA Sekolah UBSI_";

        return $this->sendMessage(
            $targetPhone,
            $message,
            WhatsAppNotificationLog::TYPE_PROPOSAL_MASUK,
            'Admin / Koordinator Marketing',
            SchoolEventProposal::class,
            $proposal->id,
            $calendarUrl,
            $emailRecipients
        );
    }

    /**
     * 2. Notifikasi Agenda Kunjungan (H-1 sebelum planned_date).
     */
    public function notifyVisitH1(VisitPlan $plan): array
    {
        $recipientPhone = $plan->user?->phone ?: $this->getAdminNotificationPhone();
        $recipientName = $plan->user?->name ?: 'Petugas Marketing';

        $schoolName = $plan->school?->name ?: 'Sekolah Target';
        $districtName = $plan->school?->district?->name ? 'Kec. ' . $plan->school->district->name : '';
        $planDateStr = $plan->planned_date ? Carbon::parse($plan->planned_date)->translatedFormat('l, d F Y') : '-';

        // Generate Google Calendar Link & Undangan Email (.ics)
        $gcalRes = GoogleCalendarNotificationService::notifyVisitPlan($plan);
        $calendarUrl = $gcalRes['gcal_url'] ?? null;
        $emailRecipients = implode(', ', $gcalRes['recipients'] ?? []);

        $priorityLabel = match ($plan->priority) {
            'A' => 'Tinggi (Grade A)',
            'B' => 'Sedang (Grade B)',
            'C' => 'Standar (Grade C)',
            default => ucfirst((string) ($plan->priority ?? 'Sedang')),
        };

        $message = "🚗 *PENGINGAT AGENDA KUNJUNGAN SEKOLAH (H-1)*\n"
            . "Halo Kak *{$recipientName}*,\n\n"
            . "Mengingatkan agenda kunjungan lapangan Anda yang dijadwalkan untuk *BESOK*:\n\n"
            . "🏫 *Sekolah:* {$schoolName} {$districtName}\n"
            . "📅 *Hari & Tanggal:* {$planDateStr}\n"
            . "🎯 *Tujuan:* {$plan->purpose}\n"
            . "⚡ *Prioritas:* " . $priorityLabel . "\n"
            . "📝 *Catatan Rencana:* " . ($plan->notes ?: '-') . "\n\n"
            . ($calendarUrl ? "📅 *Simpan ke Google Calendar:*\n{$calendarUrl}\n\n" : "")
            . "📧 _Undangan kalender (.ics) otomatis dikirimkan ke email: " . ($emailRecipients ?: '-') . "_\n\n"
            . "Pastikan berkas sosialisasi, brosur PMB UBSI, dan formulir kunjungan sudah siap.\n"
            . "Portal input kunjungan lapangan tanpa login:\n"
            . url(route('public-visit.index', [], false)) . "\n\n"
            . "_Semangat beraktivitas & salam sukses SAPA Sekolah UBSI!_";

        return $this->sendMessage(
            $recipientPhone,
            $message,
            WhatsAppNotificationLog::TYPE_KUNJUNGAN_H1,
            $recipientName,
            VisitPlan::class,
            $plan->id,
            $calendarUrl,
            $emailRecipients
        );
    }

    /**
     * 3. Notifikasi Jadwal Sosialisasi (Minimal H-5 sebelum pelaksanaan sosialisasi).
     */
    public function notifySocializationH5(FollowUp $followUp): array
    {
        $targetPhone = $this->getAdminNotificationPhone();
        $schoolName = $followUp->school?->name ?: 'Sekolah';
        $fixedDateStr = $followUp->fixed_date ? Carbon::parse($followUp->fixed_date)->translatedFormat('l, d F Y') : '-';
        $picName = $followUp->pic_name ?: ($followUp->school?->primaryContact?->name ?: 'Bapak/Ibu Panitia');
        $picPhone = $followUp->pic_phone ?: ($followUp->school?->primaryContact?->phone ?: '-');

        // Generate Google Calendar Link & Undangan Email (.ics)
        $gcalRes = GoogleCalendarNotificationService::notifySocialization($followUp);
        $calendarUrl = $gcalRes['gcal_url'] ?? null;
        $emailRecipients = implode(', ', $gcalRes['recipients'] ?? []);

        $message = "📢 *PENGINGAT JADWAL SOSIALISASI KAMPUS (H-5)*\n"
            . "Universitas Bina Sarana Informatika (UBSI)\n\n"
            . "Pemberitahuan jadwal sosialisasi kampus yang akan dilaksanakan dalam 5 hari ke depan:\n\n"
            . "🏫 *Sekolah:* {$schoolName}\n"
            . "📅 *Jadwal Sosialisasi:* {$fixedDateStr}\n"
            . "⏰ *Waktu:* {$followUp->fixed_time}\n"
            . "📍 *Lokasi Ruangan:* " . ($followUp->socialization_location ?: 'Aula / Ruang Kelas Sekolah') . "\n"
            . "👥 *Estimasi Audiens:* " . ($followUp->target_audience_count ? $followUp->target_audience_count . ' Siswa' : 'Siswa Kelas XII') . "\n"
            . "👤 *PIC Sekolah:* {$picName} ({$picPhone})\n\n"
            . ($calendarUrl ? "📅 *Simpan ke Google Calendar:*\n{$calendarUrl}\n\n" : "")
            . "📧 _Undangan kalender (.ics) otomatis dikirimkan ke email tim: " . ($emailRecipients ?: '-') . "_\n\n"
            . "Harap tim marketing menyiapkan:\n"
            . "✓ Slide Presentasi Sosialisasi & Video Profil UBSI\n"
            . "✓ Brosur PMB & Merchandise / Souvenir Siswa\n"
            . "✓ QR Code Kuisioner Minat Kuliah Siswa\n\n"
            . "Link data follow up:\n"
            . url(route('follow-ups.index', [], false)) . "\n\n"
            . "_Pesan otomatis sistem SAPA Sekolah UBSI_";

        return $this->sendMessage(
            $targetPhone,
            $message,
            WhatsAppNotificationLog::TYPE_SOSIALISASI_H5,
            'Tim Marketing & Koordinator',
            FollowUp::class,
            $followUp->id,
            $calendarUrl,
            $emailRecipients
        );
    }

    /**
     * 4. Notifikasi Pelaksanaan Support Event Sekolah (H-7 sebelum event_date).
     */
    public function notifyEventExecutionH7(SchoolEventProposal $event): array
    {
        $targetPhone = $this->getAdminNotificationPhone();
        $eventDateStr = $event->event_date ? Carbon::parse($event->event_date)->translatedFormat('l, d F Y') : '-';
        $approvedAmountFormatted = $event->approved_amount 
            ? 'Rp ' . number_format((float) $event->approved_amount, 0, ',', '.') 
            : 'Sponsorship Kegiatan';

        // Generate Google Calendar Link & Undangan Email (.ics)
        $gcalRes = GoogleCalendarNotificationService::notifyEventExecution($event);
        $calendarUrl = $gcalRes['gcal_url'] ?? null;
        $emailRecipients = implode(', ', $gcalRes['recipients'] ?? []);

        $message = "🎪 *PENGINGAT PELAKSANAAN SUPPORT EVENT (H-7)*\n"
            . "Universitas Bina Sarana Informatika (UBSI)\n\n"
            . "Pemberitahuan bahwa kegiatan support event sekolah akan berlangsung 7 hari lagi:\n\n"
            . "🎉 *Nama Event:* {$event->event_name}\n"
            . "🏫 *Sekolah:* {$event->school_name}\n"
            . "📅 *Tanggal Acara:* {$eventDateStr}\n"
            . "⏰ *Waktu & Lokasi:* {$event->event_time} @ {$event->event_location}\n"
            . "💰 *Dana Disetujui:* {$approvedAmountFormatted}\n"
            . "🎁 *Fasilitas Support:* " . ($event->approved_support_items ?: 'Spanduk, Booth, Souvenir') . "\n"
            . "👤 *PIC Sekolah:* {$event->pic_name} ({$event->pic_phone})\n\n"
            . ($calendarUrl ? "📅 *Simpan ke Google Calendar:*\n{$calendarUrl}\n\n" : "")
            . "📧 _Undangan kalender (.ics) otomatis dikirimkan ke email tim: " . ($emailRecipients ?: '-') . "_\n\n"
            . "Checklist Persiapan Tim Lapangan:\n"
            . "1. Siapkan roll banner, stand booth, & taplak meja UBSI\n"
            . "2. Cetak poster QR Code Form Siswa Event\n"
            . "3. Siapkan souvenir & paket brosur informasi beasiswa\n"
            . "4. Koordinasi teknis H-3 dengan PIC sekolah\n\n"
            . "Kelola laporan kegiatan di SAPA Sekolah:\n"
            . url(route('event-reports.index', [], false)) . "\n\n"
            . "_Pesan otomatis sistem SAPA Sekolah UBSI_";

        return $this->sendMessage(
            $targetPhone,
            $message,
            WhatsAppNotificationLog::TYPE_EVENT_H7,
            'Tim Support Event & Marketing',
            SchoolEventProposal::class,
            $event->id,
            $calendarUrl,
            $emailRecipients
        );
    }

    /**
     * Menjalankan seluruh pemeriksaan terjadwal (H-1 Kunjungan, H-5 Sosialisasi, H-7 Event, H-0 Proposal).
     */
    public function sendScheduledReminders(bool $force = false): array
    {
        $stats = [
            'kunjungan_h1' => 0,
            'sosialisasi_h5' => 0,
            'event_h7' => 0,
            'proposal_h0' => 0,
            'total_sent' => 0,
            'details' => [],
        ];

        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();
        $h5Date = Carbon::today()->addDays(5);
        $h7Date = Carbon::today()->addDays(7);

        // 1. Kunjungan H-1 (planned_date = tomorrow)
        $plansTomorrow = VisitPlan::with(['school.district', 'user'])
            ->whereDate('planned_date', $tomorrow)
            ->whereIn('status', ['planned', 'pending', 'confirmed', 'in_progress'])
            ->get();

        foreach ($plansTomorrow as $plan) {
            $alreadySent = !$force && WhatsAppNotificationLog::where('notification_type', WhatsAppNotificationLog::TYPE_KUNJUNGAN_H1)
                ->where('reference_type', VisitPlan::class)
                ->where('reference_id', $plan->id)
                ->whereDate('created_at', $today)
                ->exists();

            if (!$alreadySent) {
                $res = $this->notifyVisitH1($plan);
                $stats['kunjungan_h1']++;
                $stats['total_sent']++;
                $stats['details'][] = "Kunjungan H-1: {$plan->school?->name} ({$res['mode']})";
            }
        }

        // 2. Sosialisasi H-5 (fixed_date = today + 5 days)
        $socializationsH5 = FollowUp::with(['school.district', 'school.primaryContact'])
            ->where(function ($q) {
                $q->where('is_schedule_fixed', true)
                  ->orWhere('status', FollowUp::STATUS_COMPLETED);
            })
            ->whereDate('fixed_date', $h5Date)
            ->get();

        foreach ($socializationsH5 as $fu) {
            $alreadySent = !$force && WhatsAppNotificationLog::where('notification_type', WhatsAppNotificationLog::TYPE_SOSIALISASI_H5)
                ->where('reference_type', FollowUp::class)
                ->where('reference_id', $fu->id)
                ->whereDate('created_at', $today)
                ->exists();

            if (!$alreadySent) {
                $res = $this->notifySocializationH5($fu);
                $stats['sosialisasi_h5']++;
                $stats['total_sent']++;
                $stats['details'][] = "Sosialisasi H-5: {$fu->school?->name} ({$res['mode']})";
            }
        }

        // 3. Pelaksanaan Event H-7 (event_date = today + 7 days, status = disetujui)
        $eventsH7 = SchoolEventProposal::where('status', SchoolEventProposal::STATUS_DISETUJUI)
            ->whereDate('event_date', $h7Date)
            ->get();

        foreach ($eventsH7 as $event) {
            $alreadySent = !$force && WhatsAppNotificationLog::where('notification_type', WhatsAppNotificationLog::TYPE_EVENT_H7)
                ->where('reference_type', SchoolEventProposal::class)
                ->where('reference_id', $event->id)
                ->whereDate('created_at', $today)
                ->exists();

            if (!$alreadySent) {
                $res = $this->notifyEventExecutionH7($event);
                $stats['event_h7']++;
                $stats['total_sent']++;
                $stats['details'][] = "Event H-7: {$event->event_name} ({$res['mode']})";
            }
        }

        // 4. Proposal Baru Masuk (H-0 / Menunggu Review)
        $newProposals = SchoolEventProposal::where('status', SchoolEventProposal::STATUS_MENUNGGU_REVIEW)
            ->latest()
            ->take(5)
            ->get();

        foreach ($newProposals as $proposal) {
            $alreadySent = !$force && WhatsAppNotificationLog::where('notification_type', WhatsAppNotificationLog::TYPE_PROPOSAL_MASUK)
                ->where('reference_type', SchoolEventProposal::class)
                ->where('reference_id', $proposal->id)
                ->exists();

            if (!$alreadySent) {
                $res = $this->notifyNewProposal($proposal);
                $stats['proposal_h0']++;
                $stats['total_sent']++;
                $stats['details'][] = "Proposal H-0: {$proposal->school_name} ({$res['mode']})";
            }
        }

        return $stats;
    }

    /**
     * Dapatkan nomor WhatsApp admin/marketing default untuk menerima notifikasi.
     */
    public function getAdminNotificationPhone(): string
    {
        $customPhone = AppSettingService::get('wa_target_phone');
        if (!empty($customPhone)) {
            return $customPhone;
        }

        // Fallback: nomor telepon user admin pertama
        $adminUser = User::whereHas('role', fn ($q) => $q->where('name', 'Admin'))->first();
        if ($adminUser && !empty($adminUser->phone)) {
            return $adminUser->phone;
        }

        return '081288885000'; // Default marketing admin UBSI
    }

    /**
     * Standarisasi nomor HP Indonesia menjadi format 628...
     */
    protected function formatIndonesianPhone(string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (empty($digits)) {
            return '';
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }

        return $digits;
    }
}
