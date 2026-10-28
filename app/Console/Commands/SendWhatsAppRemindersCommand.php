<?php

namespace App\Console\Commands;

use App\Services\WhatsAppNotificationService;
use Illuminate\Console\Command;

class SendWhatsAppRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wa:send-reminders {--force : Paksa kirim ulang meskipun sudah terkirim hari ini}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim notifikasi otomatis pengingat WhatsApp: Agenda Kunjungan (H-1), Jadwal Sosialisasi (H-5), dan Pelaksanaan Event (H-7)';

    /**
     * Execute the console command.
     */
    public function handle(WhatsAppNotificationService $waService): int
    {
        $this->info('Memulai pengecekan pengingat WhatsApp...');

        $force = (bool)$this->option('force');
        $stats = $waService->sendScheduledReminders($force);

        $this->line("• Agenda Kunjungan H-1 : {$stats['kunjungan_h1']} pesan");
        $this->line("• Jadwal Sosialisasi H-5 : {$stats['sosialisasi_h5']} pesan");
        $this->line("• Pelaksanaan Event H-7  : {$stats['event_h7']} pesan");
        $this->line("• Proposal Masuk H-0     : " . ($stats['proposal_h0'] ?? 0) . " pesan");
        $this->info("Total notifikasi diproses: {$stats['total_sent']}");

        if (!empty($stats['details'])) {
            $this->newLine();
            $this->comment('Detail pengiriman:');
            foreach ($stats['details'] as $detail) {
                $this->line(" - {$detail}");
            }
        }

        return Command::SUCCESS;
    }
}
