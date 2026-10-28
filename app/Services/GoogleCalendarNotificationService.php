<?php

namespace App\Services;

use App\Models\FollowUp;
use App\Models\SchoolEventProposal;
use App\Models\User;
use App\Models\VisitPlan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GoogleCalendarNotificationService
{
    /**
     * Generate direct Google Calendar "Add Event" URL with prefilled parameters.
     */
    public static function generateGoogleCalendarUrl(
        string $title,
        Carbon $startDate,
        Carbon $endDate,
        string $details,
        string $location,
        array $attendeeEmails = []
    ): string {
        // Format ISO dates in UTC format (YYYYMMDDTHHMMSSZ)
        $startUtc = $startDate->copy()->utc()->format('Ymd\THis\Z');
        $endUtc = $endDate->copy()->utc()->format('Ymd\THis\Z');

        $params = [
            'action' => 'TEMPLATE',
            'text' => $title,
            'dates' => "{$startUtc}/{$endUtc}",
            'details' => $details,
            'location' => $location,
        ];

        if (!empty($attendeeEmails)) {
            $params['add'] = implode(',', array_unique(array_filter($attendeeEmails)));
        }

        return 'https://calendar.google.com/calendar/render?' . http_build_query($params);
    }

    /**
     * Generate RFC 5545 compliant iCalendar (.ics) string.
     */
    public static function generateIcsContent(
        string $uid,
        string $title,
        Carbon $startDate,
        Carbon $endDate,
        string $details,
        string $location,
        array $attendeeEmails = []
    ): string {
        $nowUtc = Carbon::now()->utc()->format('Ymd\THis\Z');
        $startUtc = $startDate->copy()->utc()->format('Ymd\THis\Z');
        $endUtc = $endDate->copy()->utc()->format('Ymd\THis\Z');

        $cleanTitle = str_replace(["\r\n", "\n", "\r"], ' ', $title);
        $cleanLocation = str_replace(["\r\n", "\n", "\r"], ', ', $location);
        $cleanDetails = str_replace(["\r\n", "\n", "\r"], "\\n", $details);

        $ics = "BEGIN:VCALENDAR\r\n"
            . "VERSION:2.0\r\n"
            . "PRODID:-//SAPA Sekolah UBSI//Command Center//ID\r\n"
            . "CALSCALE:GREGORIAN\r\n"
            . "METHOD:REQUEST\r\n"
            . "BEGIN:VEVENT\r\n"
            . "UID:{$uid}@sapasekolah.com\r\n"
            . "DTSTAMP:{$nowUtc}\r\n"
            . "DTSTART:{$startUtc}\r\n"
            . "DTEND:{$endUtc}\r\n"
            . "SUMMARY:{$cleanTitle}\r\n"
            . "DESCRIPTION:{$cleanDetails}\r\n"
            . "LOCATION:{$cleanLocation}\r\n"
            . "ORGANIZER;CN=SAPA Sekolah UBSI:mailto:walim.wam@bsi.ac.id\r\n";

        foreach ($attendeeEmails as $email) {
            $email = trim((string)$email);
            if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $ics .= "ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;RSVP=TRUE;CN={$email}:mailto:{$email}\r\n";
            }
        }

        $ics .= "STATUS:CONFIRMED\r\n"
            . "SEQUENCE:0\r\n"
            . "PRIORITY:5\r\n"
            . "CLASS:PUBLIC\r\n"
            . "END:VEVENT\r\n"
            . "END:VCALENDAR\r\n";

        return $ics;
    }

    /**
     * Dispatch an email with an attached iCalendar invitation and a direct Google Calendar button.
     */
    public static function dispatchCalendarInvite(
        array|string $recipients,
        string $title,
        Carbon $startDate,
        Carbon $endDate,
        string $details,
        string $location,
        string $notificationType = 'agenda',
        ?string $referenceNo = null
    ): array {
        $emails = is_array($recipients) ? $recipients : [$recipients];
        $emails = array_values(array_unique(array_filter($emails, fn($e) => !empty($e) && filter_var($e, FILTER_VALIDATE_EMAIL))));

        if (empty($emails)) {
            return [
                'status' => false,
                'message' => 'Tidak ada alamat email penerima yang valid.',
            ];
        }

        $uid = ($referenceNo ?: 'NOTIF') . '-' . time() . '-' . rand(100, 999);
        $gcalUrl = self::generateGoogleCalendarUrl($title, $startDate, $endDate, $details, $location, $emails);
        $icsContent = self::generateIcsContent($uid, $title, $startDate, $endDate, $details, $location, $emails);

        $htmlBody = self::buildHtmlEmail($title, $startDate, $endDate, $details, $location, $gcalUrl, $notificationType);

        $isSent = false;
        $errorMsg = null;

        try {
            // Attempt sending with standard mailer
            Mail::send([], [], function ($message) use ($emails, $title, $htmlBody, $icsContent, $uid) {
                $message->to($emails)
                    ->subject("[SAPA Sekolah Calendar] " . $title)
                    ->html($htmlBody)
                    ->attachData($icsContent, "invite-{$uid}.ics", [
                        'mime' => 'text/calendar; charset=UTF-8; method=REQUEST',
                    ]);
            });
            $isSent = true;
        } catch (\Throwable $e) {
            $errorMsg = $e->getMessage();
            Log::info("Catatan kirim email via SMTP utama: {$errorMsg}. Mengalihkan pencatatan ke log email kalender.");

            try {
                // Graceful fallback to log mailer so execution never breaks
                Mail::mailer('log')->send([], [], function ($message) use ($emails, $title, $htmlBody, $icsContent, $uid) {
                    $message->to($emails)
                        ->subject("[SAPA Sekolah Calendar] " . $title)
                        ->html($htmlBody)
                        ->attachData($icsContent, "invite-{$uid}.ics", [
                            'mime' => 'text/calendar; charset=UTF-8; method=REQUEST',
                        ]);
                });
            } catch (\Throwable $ex) {}
        }

        return [
            'status' => true,
            'is_sent' => $isSent,
            'gcal_url' => $gcalUrl,
            'recipients' => $emails,
            'ics_uid' => $uid,
            'message' => $isSent ? 'Email undangan Google Calendar berhasil dikirim.' : 'Undangan kalender tercatat (mode simulasi/log). URL Google Calendar aktif.',
        ];
    }

    /**
     * Dispatch notification & calendar invite for VisitPlan (Kunjungan H-1).
     */
    public static function notifyVisitPlan(VisitPlan $plan): array
    {
        $schoolName = $plan->school?->name ?: 'Sekolah Target';
        $location = $schoolName . ($plan->school?->address ? ', ' . $plan->school->address : '');
        $title = "Kunjungan Lapangan: {$schoolName}";

        // Schedule at 09:00 - 11:30 WIB on planned date
        $startDate = Carbon::parse($plan->planned_date)->setTime(9, 0, 0);
        $endDate = Carbon::parse($plan->planned_date)->setTime(11, 30, 0);

        $details = "Agenda Kunjungan Lapangan Tim Marketing SAPA Sekolah UBSI:\n"
            . "Sekolah: {$schoolName}\n"
            . "Tujuan: {$plan->purpose}\n"
            . "Petugas: " . ($plan->user?->name ?: 'Marketing Officer') . "\n"
            . "Prioritas: " . ucfirst($plan->priority ?? 'Sedang') . "\n"
            . "Catatan: " . ($plan->notes ?: '-') . "\n\n"
            . "Portal Kunjungan SAPA Sekolah: " . url('/public-visit');

        $recipients = self::getRelevantEmails([
            $plan->user?->email,
            'karawangmarkom@gmail.com',
            'walimsaja@gmail.com',
            'walim.wam@bsi.ac.id',
        ]);

        return self::dispatchCalendarInvite(
            $recipients,
            $title,
            $startDate,
            $endDate,
            $details,
            $location,
            'kunjungan_h1',
            'VP-' . $plan->id
        );
    }

    /**
     * Dispatch notification & calendar invite for Socialization (FollowUp H-5).
     */
    public static function notifySocialization(FollowUp $fu): array
    {
        $schoolName = $fu->school?->name ?: 'Sekolah';
        $location = ($fu->socialization_location ?: 'Aula / Ruang Pertemuan') . ' - ' . $schoolName;
        $title = "Sosialisasi PMB UBSI @ {$schoolName}";

        $fixedDate = $fu->fixed_date ?: Carbon::today()->addDays(5);
        $timeParts = explode(':', (string)$fu->fixed_time);
        $hour = isset($timeParts[0]) && is_numeric($timeParts[0]) ? (int)$timeParts[0] : 9;
        $minute = isset($timeParts[1]) && is_numeric($timeParts[1]) ? (int)$timeParts[1] : 0;

        $startDate = Carbon::parse($fixedDate)->setTime($hour, $minute, 0);
        $endDate = $startDate->copy()->addHours(2);

        $details = "Jadwal Presentasi Sosialisasi Profil Kampus UBSI:\n"
            . "Sekolah: {$schoolName}\n"
            . "Waktu: " . ($fu->fixed_time ?: '09:00 WIB') . "\n"
            . "Ruangan: " . ($fu->socialization_location ?: 'Aula Sekolah') . "\n"
            . "Audiens: " . ($fu->target_audience_count ? $fu->target_audience_count . ' Siswa' : 'Siswa Kelas XII') . "\n"
            . "PIC Sekolah: {$fu->pic_name} ({$fu->pic_phone})\n\n"
            . "Data Follow-Up: " . url('/follow-ups');

        $recipients = self::getRelevantEmails([
            'karawangmarkom@gmail.com',
            'walimsaja@gmail.com',
            'walim.wam@bsi.ac.id',
            'mohammad.myz@bsi.ac.id',
        ]);

        return self::dispatchCalendarInvite(
            $recipients,
            $title,
            $startDate,
            $endDate,
            $details,
            $location,
            'sosialisasi_h5',
            'FU-' . $fu->id
        );
    }

    /**
     * Dispatch notification & calendar invite for Event Execution (SchoolEventProposal H-7).
     */
    public static function notifyEventExecution(SchoolEventProposal $event): array
    {
        $title = "Support Event: {$event->event_name} ({$event->school_name})";
        $location = ($event->event_location ?: 'Area Sekolah') . ' - ' . $event->school_name;

        $eventDate = $event->event_date ?: Carbon::today()->addDays(7);
        $startDate = Carbon::parse($eventDate)->setTime(8, 0, 0);
        $endDate = $startDate->copy()->addHours(6);

        $budget = $event->approved_amount ? 'Rp ' . number_format((float) $event->approved_amount, 0, ',', '.') : 'Sponsorship';

        $details = "Agenda Kegiatan Pelaksanaan Event & Pameran Pendidikan UBSI:\n"
            . "Nama Acara: {$event->event_name}\n"
            . "Sekolah Penyelenggara: {$event->school_name}\n"
            . "Tanggal: " . Carbon::parse($eventDate)->translatedFormat('d F Y') . "\n"
            . "Waktu & Lokasi: {$event->event_time} @ {$location}\n"
            . "Dukungan Disetujui: {$budget}\n"
            . "Fasilitas Stand: " . ($event->approved_support_items ?: 'Tenda Booth, Roll Banner, Souvenir') . "\n"
            . "PIC Panitia: {$event->pic_name} ({$event->pic_phone})\n\n"
            . "Laporan Kegiatan: " . url('/event-reports');

        $recipients = self::getRelevantEmails([
            'walim.wam@bsi.ac.id',
            'mohammad.myz@bsi.ac.id',
            'walimsaja@gmail.com',
            'karawangmarkom@gmail.com',
            'cs@sapasekolah.com',
            'deviwinda@gmail.com',
        ]);

        return self::dispatchCalendarInvite(
            $recipients,
            $title,
            $startDate,
            $endDate,
            $details,
            $location,
            'event_h7',
            $event->reference_no ?: ('PRP-' . $event->id)
        );
    }

    /**
     * Dispatch notification & calendar invite for New Proposal (SchoolEventProposal H-0).
     */
    public static function notifyNewProposal(SchoolEventProposal $proposal): array
    {
        $title = "Proposal Masuk: {$proposal->event_name} ({$proposal->school_name})";
        $location = $proposal->event_location ?: $proposal->school_name;

        $eventDate = $proposal->event_date ?: Carbon::today()->addDays(14);
        $startDate = Carbon::parse($eventDate)->setTime(8, 30, 0);
        $endDate = $startDate->copy()->addHours(5);

        $details = "Pengajuan Proposal Kegiatan Sekolah Baru:\n"
            . "No. Referensi: #{$proposal->reference_no}\n"
            . "Sekolah: {$proposal->school_name}\n"
            . "Acara: {$proposal->event_name}\n"
            . "Jadwal Pelaksanaan: " . Carbon::parse($eventDate)->translatedFormat('d F Y') . "\n"
            . "Bentuk Support Diminta: {$proposal->support_type_requested}\n"
            . "Target Peserta: {$proposal->target_participants}\n"
            . "PIC: {$proposal->pic_name} ({$proposal->pic_phone})\n\n"
            . "Tinjau Berkas di SAPA Sekolah: " . url('/event-proposals');

        $recipients = self::getRelevantEmails([
            'walimsaja@gmail.com',
            'walim.wam@bsi.ac.id',
            'mohammad.myz@bsi.ac.id',
            'karawangmarkom@gmail.com',
        ]);

        return self::dispatchCalendarInvite(
            $recipients,
            $title,
            $startDate,
            $endDate,
            $details,
            $location,
            'proposal_masuk',
            $proposal->reference_no ?: ('PRP-' . $proposal->id)
        );
    }

    /**
     * Helper to get clean list of existing user emails.
     */
    public static function getRelevantEmails(array $preferredEmails = []): array
    {
        $existing = User::whereIn('email', $preferredEmails)->pluck('email')->toArray();
        if (empty($existing)) {
            $existing = User::pluck('email')->toArray();
        }
        return array_values(array_unique(array_merge($preferredEmails, $existing)));
    }

    /**
     * Beautiful HTML Email Template with direct "Add to Google Calendar" button.
     */
    protected static function buildHtmlEmail(
        string $title,
        Carbon $startDate,
        Carbon $endDate,
        string $details,
        string $location,
        string $gcalUrl,
        string $type
    ): string {
        $dateFormatted = $startDate->translatedFormat('l, d F Y');
        $timeFormatted = $startDate->format('H:i') . ' - ' . $endDate->format('H:i') . ' WIB';
        $formattedDetails = nl2br(e($details));

        $badgeColor = match ($type) {
            'kunjungan_h1' => '#2563eb',
            'sosialisasi_h5' => '#059669',
            'event_h7' => '#7c3aed',
            default => '#d97706',
        };

        $badgeTitle = match ($type) {
            'kunjungan_h1' => 'AGENDA KUNJUNGAN (H-1)',
            'sosialisasi_h5' => 'JADWAL SOSIALISASI (H-5)',
            'event_h7' => 'PELAKSANAAN SUPPORT EVENT (H-7)',
            default => 'PROPOSAL MASUK (H-0)',
        };

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{$title}</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f1f5f9; margin: 0; padding: 24px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center">
                <table width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 18px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 28px 32px; color: #ffffff;">
                            <div style="font-size: 11px; font-weight: 800; letter-spacing: 1px; color: #38bdf8; text-transform: uppercase;">SAPA SEKOLAH COMMAND CENTER &bull; UBSI</div>
                            <h1 style="margin: 8px 0 0 0; font-size: 20px; font-weight: 800; color: #ffffff;">Undangan Notifikasi &amp; Jadwal Agenda</h1>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 32px;">
                            <div style="display: inline-block; background-color: {$badgeColor}; color: #ffffff; font-size: 11px; font-weight: 800; padding: 4px 12px; border-radius: 20px; text-transform: uppercase; margin-bottom: 16px;">
                                {$badgeTitle}
                            </div>

                            <h2 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0 0 16px 0;">
                                {$title}
                            </h2>

                            <!-- Schedule Card -->
                            <div style="background-color: #f8fafc; border-radius: 14px; border: 1px solid #e2e8f0; padding: 18px 20px; margin-bottom: 24px;">
                                <div style="font-size: 13px; color: #334155; margin-bottom: 8px;">
                                    <strong>📅 Tanggal:</strong> {$dateFormatted}
                                </div>
                                <div style="font-size: 13px; color: #334155; margin-bottom: 8px;">
                                    <strong>⏰ Waktu:</strong> {$timeFormatted}
                                </div>
                                <div style="font-size: 13px; color: #334155;">
                                    <strong>📍 Lokasi:</strong> {$location}
                                </div>
                            </div>

                            <!-- Details -->
                            <div style="font-size: 13px; color: #475569; line-height: 1.6; margin-bottom: 28px;">
                                {$formattedDetails}
                            </div>

                            <!-- Big Google Calendar Button -->
                            <div style="text-align: center; margin: 28px 0;">
                                <a href="{$gcalUrl}" target="_blank" style="display: inline-block; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 800; padding: 14px 28px; border-radius: 12px; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);">
                                    📅 Tambahkan ke Google Calendar
                                </a>
                                <p style="font-size: 11px; color: #94a3b8; margin-top: 8px;">
                                    Klik tombol di atas atau buka file lampiran <code>invite.ics</code> untuk menyimpan ke kalender Anda.
                                </p>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 32px; text-align: center; font-size: 11px; color: #64748b;">
                            Notifikasi resmi otomatis dari <strong>SAPA Sekolah Command Center</strong> &bull; Universitas Bina Sarana Informatika.<br>
                            Surel ini dikirimkan otomatis ke akun resmi Anda.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }
}
