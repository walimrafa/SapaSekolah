<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Services\WordCraftService;
use Illuminate\Console\Command;

class ProcessScheduledArticlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'articles:process {--limit=10 : Jumlah maksimal artikel yang diproses}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memproses artikel terjadwal (pending) yang jadwal rilisnya telah tiba';

    /**
     * Execute the console command.
     */
    public function handle(WordCraftService $service)
    {
        $limit = (int) $this->option('limit');

        $articles = Article::where('status', 'pending')
            ->where(function ($query) {
                $query->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', now());
            })
            ->orderBy('scheduled_at', 'asc')
            ->take($limit)
            ->get();

        if ($articles->isEmpty()) {
            $this->info("Tidak ada artikel pending yang siap diproses.");
            return 0;
        }

        $this->info("Ditemukan {$articles->count()} artikel pending. Memulai pemrosesan...");

        $success = 0;
        $failed = 0;

        foreach ($articles as $article) {
            $this->line("Memproses [#{$article->id}]: {$article->title}...");
            $result = $service->processArticle($article);

            if ($result) {
                $this->info(" ✓ Sukses [#{$article->id}]");
                $success++;
            } else {
                $this->error(" ✗ Gagal [#{$article->id}]: {$article->error_message}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Selesai! Berhasil: {$success}, Gagal: {$failed}");
        return 0;
    }
}
