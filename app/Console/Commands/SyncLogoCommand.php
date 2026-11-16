<?php

namespace App\Console\Commands;

use App\Services\AppSettingService;
use Illuminate\Console\Command;

class SyncLogoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-logo {--path= : Path spesifik berkas logo untuk disinkronkan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronkan logo aplikasi aktif ke seluruh favicon, apple-touch-icon, dan ikon PWA';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai sinkronisasi logo aplikasi...');

        $customPath = $this->option('path');
        $sourcePath = null;

        if ($customPath) {
            $sourcePath = realpath($customPath) ?: $customPath;
            if (!file_exists($sourcePath)) {
                $this->error("Berkas spesifik tidak ditemukan di: {$customPath}");
                return Command::FAILURE;
            }
        } else {
            $logoSetting = AppSettingService::get('app_logo_url');
            $this->line("URL Logo aktif di pengaturan: <comment>{$logoSetting}</comment>");
            $sourcePath = AppSettingService::resolveLocalPath($logoSetting);
        }

        if (!$sourcePath || !file_exists($sourcePath)) {
            $defaultFallback = public_path('logo-sapa-sekolah.png');
            if (file_exists($defaultFallback)) {
                $this->warn('Berkas dari app_logo_url tidak ditemukan fisik, menggunakan fallback public/logo-sapa-sekolah.png');
                $sourcePath = $defaultFallback;
            }
        }

        if (!$sourcePath || !file_exists($sourcePath)) {
            $this->error('Gagal menemukan berkas sumber logo fisik di server.');
            $this->line('Pastikan logo sudah diunggah atau berada di public/logo-sapa-sekolah.png.');
            return Command::FAILURE;
        }

        $this->line("Berkas sumber ditemukan di: <info>{$sourcePath}</info>");

        $success = AppSettingService::syncLogoToPublicAssets($sourcePath);

        if ($success) {
            $this->info('✅ Berhasil menyinkronkan logo ke seluruh aset publik!');
            $this->table(['Aset', 'Status'], [
                ['public/logo-sapa-sekolah.png', 'Tersinkron'],
                ['public/favicon.png', 'Tersinkron'],
                ['public/favicon.ico', 'Tersinkron'],
                ['public/favicon-32x32.png', 'Tersinkron (32x32)'],
                ['public/favicon-16x16.png', 'Tersinkron (16x16)'],
                ['public/apple-touch-icon.png', 'Tersinkron (180x180)'],
                ['public/pwa-icon-*.png (72..512)', 'Tersinkron'],
            ]);
            $this->line('URL Logo dengan cache buster: <comment>' . AppSettingService::getLogoUrl() . '</comment>');
            return Command::SUCCESS;
        }

        $this->error('Terjadi kesalahan saat menyinkronkan aset logo.');
        return Command::FAILURE;
    }
}
