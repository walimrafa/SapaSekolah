<?php

namespace App\Console\Commands;

use App\Services\WordCraftService;
use Illuminate\Console\Command;

class SyncArticlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'articles:sync {--file= : Path ke file JSON kustom}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi judul dan jadwal artikel dari file JSON (articles_to_generate.json)';

    /**
     * Execute the console command.
     */
    public function handle(WordCraftService $service)
    {
        $customFile = $this->option('file');
        $this->info("Menyinkronkan daftar artikel...");

        try {
            $result = $service->syncFromJsonFile($customFile);

            $this->info("Sinkronisasi berhasil!");
            $this->table(
                ['Keterangan', 'Jumlah'],
                [
                    ['Total Artikel di JSON', $result['total']],
                    ['Artikel Baru Didaftarkan (Pending)', $result['created']],
                    ['Dilewati (Sudah Ada)', $result['skipped']],
                ]
            );

            return 0;
        } catch (\Throwable $e) {
            $this->error("Gagal sinkronisasi: " . $e->getMessage());
            return 1;
        }
    }
}
