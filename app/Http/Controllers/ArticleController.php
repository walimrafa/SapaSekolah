<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\WordCraftService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    /**
     * Display a listing of articles.
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $campus = $request->get('campus', 'all');
        $search = $request->get('search');

        $query = Article::query()->with('user');

        if ($status !== 'all' && in_array($status, ['pending', 'processing', 'completed', 'failed'])) {
            $query->where('status', $status);
        }

        if ($campus !== 'all') {
            if ($campus === 'Umum') {
                $query->whereNull('campus_context');
            } else {
                $query->where('campus_context', $campus);
            }
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('opinion_author', 'like', "%{$search}%")
                    ->orWhere('event_organizer', 'like', "%{$search}%");
            });
        }

        $articles = $query->orderBy('scheduled_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => Article::count(),
            'completed' => Article::where('status', 'completed')->count(),
            'pending' => Article::where('status', 'pending')->count(),
            'failed' => Article::where('status', 'failed')->count(),
        ];

        return view('articles.index', compact('articles', 'stats', 'status', 'campus', 'search'));
    }

    /**
     * Show the form for creating a new article.
     */
    public function create()
    {
        $writingStyles = [
            'Judul Paling Menarik' => 'Gaya jurnalisme viral dengan hook memikat pembaca seketika',
            'Gaya Media Nasional' => 'Gaya lugas, kredibel, dan berimbang ala media arus utama nasional (Kompas, Detik, Tempo)',
            'Gaya Emosional' => 'Sentuhan empati, kisah humanis, dan narasi yang menggerakkan hati',
            'Opini Formal & Akademis' => 'Ulasan kritis berbobot, berbasis argumen logis dan literatur ilmiah',
        ];

        $categories = [
            'EVENT' => 'Liputan Kegiatan & Acara Kampus',
            'NEWS' => 'Berita Umum & Terkini',
            'PENDIDIKAN' => 'Artikel Edukasi & Informasi Akademik',
            'OPINI' => 'Artikel Gagasan & Pandangan Kritis',
            'LOKER' => 'Informasi Karir & Lowongan Kerja',
            'OLAHRAGA' => 'Ulasan Olahraga & Prestasi',
            'POLITIK' => 'Kilas & Analisis Isu Publik',
            'LIFESTYLE' => 'Gaya Hidup & Tren Mahasiswa',
            'TRAVELLER' => 'Destinasi & Wisata',
            'UMKM' => 'Pemberdayaan Usaha & Kewirausahaan',
        ];

        $campuses = [
            'UBSI Karawang' => 'Fokus seputar kampus Karawang, industri, dan peluang karir lokal',
            'UBSI Cikampek' => 'Fokus seputar kampus Cikampek, mobilitas daerah, dan keunggulan vokasi',
            'Signaltodays' => 'Kanal media berita digital umum dengan jangkauan luas',
        ];

        return view('articles.create', compact('writingStyles', 'categories', 'campuses'));
    }

    /**
     * Paraphrase titles via Gemini AI.
     */
    public function paraphraseTitles(Request $request, WordCraftService $service)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'count' => 'required|integer|min:1|max:50',
            'writing_style' => 'nullable|string|max:100',
        ]);

        try {
            $titles = $service->paraphraseTitles(
                $validated['title'],
                (int) $validated['count'],
                $validated['writing_style'] ?? null
            );

            return response()->json([
                'success' => true,
                'titles' => $titles,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created article (or multiple articles per selected campus).
     */
    public function store(Request $request, WordCraftService $service)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'titles' => 'nullable|array',
            'titles.*' => 'nullable|string|max:255',
            'writing_style' => 'required|string|max:100',
            'opinion_author' => 'nullable|string|max:255',
            'category' => 'required|string|max:100',
            'campus_contexts' => 'nullable|array',
            'campus_contexts.*' => 'string|max:100',
            'article_count' => 'nullable|integer|min:1|max:50',
            'schedule_interval' => 'nullable|integer|min:0|max:1440',
            'is_event' => 'nullable|boolean',
            'event_category' => 'nullable|string|max:50',
            'event_name' => 'nullable|string|max:255',
            'event_theme' => 'nullable|string|max:255',
            'event_date' => 'nullable|string|max:255',
            'event_time' => 'nullable|string|max:255',
            'event_location' => 'nullable|string|max:255',
            'event_organizer' => 'nullable|string|max:255',
            'event_host' => 'nullable|string|max:255',
            'event_speaker' => 'nullable|string|max:255',
            'event_executor' => 'nullable|string|max:255',
            'event_participants' => 'nullable|string|max:255',
            'event_location_time' => 'nullable|string|max:255',
            'event_image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,svg,bmp,avif,tiff|max:10240',
            'generation_mode' => 'required|in:instant,scheduled',
            'scheduled_at' => 'required_if:generation_mode,scheduled|nullable|date',
        ]);

        $articleCount = max(1, min(50, (int) ($validated['article_count'] ?? 1)));
        $scheduleInterval = max(0, min(1440, (int) ($validated['schedule_interval'] ?? 0)));

        // Prepare titles for each article variation
        $rawTitles = $request->input('titles', []);
        $cleanedTitles = [];
        if (is_array($rawTitles)) {
            foreach ($rawTitles as $t) {
                $trimmed = trim((string) $t);
                if (!empty($trimmed)) {
                    $cleanedTitles[] = $trimmed;
                }
            }
        }

        if ($articleCount > 1) {
            if (empty($cleanedTitles)) {
                // Auto paraphrase with AI if user didn't pre-generate in UI
                $cleanedTitles = $service->paraphraseTitles($validated['title'], $articleCount, $validated['writing_style']);
            } elseif (count($cleanedTitles) < $articleCount) {
                // If fewer than needed, generate remaining variations
                $remaining = $articleCount - count($cleanedTitles);
                $extra = $service->paraphraseTitles($validated['title'], $remaining, $validated['writing_style']);
                $cleanedTitles = array_merge($cleanedTitles, $extra);
            }
        } else {
            $cleanedTitles = [!empty($cleanedTitles[0]) ? $cleanedTitles[0] : $validated['title']];
        }

        $isEvent = !empty($validated['is_event']) || in_array($validated['category'], ['EVENT', 'BERITA', 'NEWS']);
        $featuredImagePath = null;
        $imageSource = null;

        // Handle supporting / documentation image upload (all image formats)
        if ($request->hasFile('event_image')) {
            $file = $request->file('event_image');
            $dir = 'articles/images';
            $ext = strtolower($file->getClientOriginalExtension());
            $filename = 'img_' . time() . '_' . Str::random(8) . '.' . $ext;
            $path = $file->storeAs($dir, $filename, 'public');
            $featuredImagePath = $path;
            $imageSource = 'uploaded';
        }

        $contexts = $request->input('campus_contexts', []);
        if (empty($contexts)) {
            // Default: 1 umum article
            $contexts = [null];
        }

        $baseScheduledAt = null;
        if ($validated['generation_mode'] === 'scheduled' && !empty($validated['scheduled_at'])) {
            $baseScheduledAt = Carbon::parse($validated['scheduled_at']);
        } else {
            $baseScheduledAt = now();
        }

        $createdArticles = [];
        $articleIndex = 0;

        foreach ($contexts as $ctx) {
            for ($i = 0; $i < $articleCount; $i++) {
                $articleTitle = $cleanedTitles[$i] ?? ($validated['title'] . ($articleCount > 1 ? " (Versi " . ($i + 1) . ")" : ""));
                $scheduledAt = $baseScheduledAt ? $baseScheduledAt->copy()->addMinutes($articleIndex * $scheduleInterval) : now();

                $article = Article::create([
                    'title' => $articleTitle,
                    'writing_style' => $validated['writing_style'],
                    'opinion_author' => $validated['opinion_author'] ?? null,
                    'category' => $validated['category'],
                    'campus_context' => $ctx ?: null,
                    'is_event' => $isEvent,
                    'event_category' => $isEvent ? ($validated['event_category'] ?? 'Hari H Event') : null,
                    'event_name' => $isEvent ? ($validated['event_name'] ?? null) : null,
                    'event_theme' => $isEvent ? ($validated['event_theme'] ?? null) : null,
                    'event_date' => $isEvent ? ($validated['event_date'] ?? null) : null,
                    'event_time' => $isEvent ? ($validated['event_time'] ?? null) : null,
                    'event_location' => $isEvent ? ($validated['event_location'] ?? null) : null,
                    'event_organizer' => $isEvent ? ($validated['event_organizer'] ?? null) : null,
                    'event_host' => $isEvent ? ($validated['event_host'] ?? null) : null,
                    'event_speaker' => $isEvent ? ($validated['event_speaker'] ?? null) : null,
                    'event_executor' => $isEvent ? ($validated['event_executor'] ?? null) : null,
                    'event_participants' => $isEvent ? ($validated['event_participants'] ?? null) : null,
                    'event_location_time' => $isEvent ? ($validated['event_location_time'] ?? null) : null,
                    'featured_image' => $featuredImagePath,
                    'image_source' => $imageSource,
                    'status' => 'pending',
                    'scheduled_at' => $scheduledAt,
                    'user_id' => auth()->id(),
                ]);

                $createdArticles[] = $article;
                $articleIndex++;
            }
        }

        // If Instant Mode, execute generation right away
        if ($validated['generation_mode'] === 'instant') {
            @set_time_limit(300);
            $success = 0;
            $failed = 0;

            foreach ($createdArticles as $art) {
                $res = $service->processArticle($art);
                if ($res) {
                    $success++;
                } else {
                    $failed++;
                }
            }

            if ($failed > 0 && $success === 0) {
                return redirect()->route('articles.index')
                    ->with('error', "Pembuatan artikel gagal diproses. Silakan periksa kredensial Gemini API di Pengaturan AI.");
            } elseif ($failed > 0) {
                return redirect()->route('articles.index')
                    ->with('warning', "Pembuatan instan selesai: {$success} berhasil dibuat, {$failed} gagal diproses.");
            }

            return redirect()->route('articles.index')
                ->with('success', "Berhasil membuat {$success} artikel lengkap dengan dokumen Word (.docx)!");
        }

        // Scheduled mode
        $count = count($createdArticles);
        $formattedDate = $baseScheduledAt->format('d M Y, H:i');
        return redirect()->route('articles.index')
            ->with('success', "{$count} artikel berhasil masuk ke antrean terjadwal (Mulai rilis: {$formattedDate} WIB).");
    }

    /**
     * Display the specified article preview.
     */
    public function show(Article $article)
    {
        return view('articles.show', compact('article'));
    }

    /**
     * Download the .docx file of an article.
     */
    public function downloadDocx(Article $article, WordCraftService $service)
    {
        if (empty($article->docx_path) || !file_exists(storage_path('app/public/' . $article->docx_path))) {
            // If completed but docx missing, try generating
            if ($article->isCompleted() && !empty($article->content)) {
                $service->generateDocx($article);
            } else {
                return back()->with('error', 'Dokumen Word belum tersedia atau berkas fisiknya tidak ditemukan di server.');
            }
        }

        $fullPath = storage_path('app/public/' . $article->docx_path);
        $downloadName = Str::slug($article->title) . '.docx';

        return response()->download($fullPath, $downloadName);
    }

    /**
     * Download all completed articles as a single ZIP archive.
     */
    public function downloadZip(WordCraftService $service)
    {
        try {
            $zipPath = $service->createZipOfCompletedArticles();
            $filename = basename($zipPath);

            return response()->download($zipPath, $filename)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membuat arsip ZIP: ' . $e->getMessage());
        }
    }

    /**
     * Retry processing a failed article.
     */
    public function retry(Article $article, WordCraftService $service)
    {
        $result = $service->processArticle($article);

        if ($result) {
            return redirect()->back(fallback: route('articles.index'))->with('success', "Artikel #{$article->id} berhasil dibuat ulang!");
        }

        return redirect()->back(fallback: route('articles.index'))->with('error', "Percobaan ulang gagal: {$article->error_message}");
    }

    /**
     * Manually trigger processing for a pending article.
     */
    public function processNow(Article $article, WordCraftService $service)
    {
        $result = $service->processArticle($article);

        if ($result) {
            return redirect()->back(fallback: route('articles.index'))->with('success', "Artikel #{$article->id} berhasil diproses segera!");
        }

        return redirect()->back(fallback: route('articles.index'))->with('error', "Gagal memproses artikel: {$article->error_message}");
    }

    /**
     * Delete an article and its files.
     */
    public function destroy(Article $article)
    {
        if (!empty($article->docx_path)) {
            Storage::disk('public')->delete($article->docx_path);
        }
        if (!empty($article->featured_image) && !str_starts_with($article->featured_image, 'http')) {
            $otherUses = Article::where('id', '!=', $article->id)
                ->where('featured_image', $article->featured_image)
                ->exists();
            if (!$otherUses) {
                Storage::disk('public')->delete($article->featured_image);
            }
        }

        $article->delete();

        return redirect()->route('articles.index')->with('success', 'Artikel berhasil dihapus.');
    }

    /**
     * Bulk destroy articles.
     */
    public function bulkDestroy(Request $request)
    {
        $mode = $request->input('mode', 'selected');
        $ids = $request->input('ids', []);

        if ($mode === 'all') {
            $articles = Article::all();
            foreach ($articles as $art) {
                if (!empty($art->docx_path)) Storage::disk('public')->delete($art->docx_path);
                if (!empty($art->featured_image) && !str_starts_with($art->featured_image, 'http')) {
                    Storage::disk('public')->delete($art->featured_image);
                }
                $art->delete();
            }
            return redirect()->route('articles.index')->with('success', 'Seluruh artikel berhasil dihapus.');
        }

        if (empty($ids)) {
            return back()->with('error', 'Pilih minimal satu artikel untuk dihapus.');
        }

        $articles = Article::whereIn('id', $ids)->get();
        foreach ($articles as $art) {
            if (!empty($art->docx_path)) Storage::disk('public')->delete($art->docx_path);
            if (!empty($art->featured_image) && !str_starts_with($art->featured_image, 'http')) {
                Storage::disk('public')->delete($art->featured_image);
            }
            $art->delete();
        }

        return redirect()->route('articles.index')->with('success', count($ids) . ' artikel berhasil dihapus.');
    }

    /**
     * Display WordCraft AI Configuration & Prompts.
     */
    public function settings()
    {
        $settings = WordCraftService::getSettings();
        $keys = WordCraftService::getGeminiKeyList();

        return view('articles.settings', compact('settings', 'keys'));
    }

    /**
     * Update WordCraft AI Configuration & Prompts.
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'gemini_api_keys' => 'nullable|string',
            'gemini_model' => 'required|string',
            'pexels_api_key' => 'nullable|string',
            'prompt_template' => 'required|string',
            'cta_ubsi_karawang' => 'required|string',
            'cta_ubsi_cikampek' => 'required|string',
            'cta_signaltodays' => 'required|string',
            'cta_general' => 'required|string',
        ]);

        WordCraftService::saveSettings($validated);

        return back()->with('success', 'Konfigurasi WordCraft AI & Prompt berhasil disimpan!');
    }

    /**
     * Trigger sync from articles_to_generate.json via UI.
     */
    public function syncJson(WordCraftService $service)
    {
        try {
            $result = $service->syncFromJsonFile();

            return back()->with('success', "Sinkronisasi selesai! {$result['created']} artikel baru didaftarkan ke antrean pending, {$result['skipped']} judul dilewati (sudah ada).");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal sinkronisasi JSON: ' . $e->getMessage());
        }
    }
}
