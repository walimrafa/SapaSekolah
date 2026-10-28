<?php

namespace App\Services;

use App\Models\Article;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use ZipArchive;

class WordCraftService
{
    protected static ?array $settingsCache = null;

    protected static function getSettingsPath(): string
    {
        return storage_path('app/wordcraft_settings.json');
    }

    /**
     * Get all WordCraft configuration settings.
     */
    public static function getSettings(): array
    {
        if (static::$settingsCache !== null) {
            return static::$settingsCache;
        }

        $defaults = [
            'gemini_api_keys' => env('GEMINI_API_KEY', ''),
            'gemini_model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
            'pexels_api_key' => env('PEXELS_API_KEY', ''),
            'prompt_template' => "Tulis artikel mendalam, komprehensif, dan sangat menarik dengan topik \"[JUDUL]\".\n\nGaya penulisan yang diminta adalah: [GAYA].\nKonteks target pembaca: [KAMPUS].\n\nKetentuan penulisan:\n1. Panjang artikel berkisar antara 800 hingga 1.500 kata.\n2. Sajikan dengan struktur logis: pembuka yang memikat, pembahasan masalah/poin utama dengan sub-judul, data/analisis atau contoh konkret, serta penutup berupa kesimpulan dan refleksi.\n3. Format penulisan HANYA dalam tag HTML bersih (gunakan <h2>, <h3>, <p>, <ul>, <li>, <blockquote>, <strong>, <em>). JANGAN menyertakan tag <html>, <head>, <body>, dan JANGAN menggunakan markdown triple backticks (```html).",
            'cta_ubsi_karawang' => "Kuliah Berkualitas & Siap Kerja di Karawang!\nPenerimaan Mahasiswa Baru Universitas Bina Sarana Informatika (UBSI) Kampus Karawang telah dibuka. Raih beasiswa dan peluang karir masa depan gemilang dengan mendaftar di https://bsi.ac.id atau unduh aplikasi PMB UBSI di Play Store / App Store. Informasi lebih lanjut hubungi WhatsApp Admin PMB UBSI Karawang: 0812-8888-5000.",
            'cta_ubsi_cikampek' => "Wujudkan Impian Kuliah Dekat & Berkualitas di Cikampek!\nUniversitas Bina Sarana Informatika (UBSI) Kampus Cikampek membuka pendaftaran mahasiswa baru dengan berbagai program studi unggulan dan fasilitas modern. Daftar sekarang melalui portal resmi https://bsi.ac.id atau konsultasikan masa depan pendidikan Anda melalui WhatsApp PMB UBSI Cikampek: 0812-7777-4000.",
            'cta_signaltodays' => "Ikuti Informasi Teraktual & Inspiratif Seputar Pendidikan & Tren Terkini!\nDapatkan ulasan berita terpercaya, tips karir, dan inspirasi kampus setiap hari hanya di Signaltodays.com. Bagikan artikel ini kepada rekan Anda!",
            'cta_general' => "Artikel ini dipersembahkan untuk memperkaya wawasan dan literasi civitas akademika serta masyarakat luas. Mari terus belajar, berinovasi, dan berkarya untuk kemajuan bersama.",
        ];

        $filePath = static::getSettingsPath();
        if (file_exists($filePath)) {
            $json = json_decode(file_get_contents($filePath), true);
            if (is_array($json)) {
                static::$settingsCache = array_merge($defaults, $json);
                return static::$settingsCache;
            }
        }

        static::$settingsCache = $defaults;
        return static::$settingsCache;
    }

    /**
     * Save WordCraft settings.
     */
    public static function saveSettings(array $newSettings): void
    {
        $current = static::getSettings();
        $updated = array_merge($current, $newSettings);

        $dir = dirname(static::getSettingsPath());
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(static::getSettingsPath(), json_encode($updated, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        static::$settingsCache = $updated;
    }

    /**
     * Extract multiple Gemini API keys into an array.
     */
    public static function getGeminiKeyList(): array
    {
        $settings = static::getSettings();
        $raw = $settings['gemini_api_keys'] ?? '';

        // Split by newline, comma, or semicolon
        $keys = preg_split('/[\r\n,;]+/', $raw);
        $clean = [];

        foreach ($keys as $k) {
            $t = trim($k);
            if (!empty($t)) {
                $clean[] = $t;
            }
        }

        return $clean;
    }

    /**
     * Paraphrase a given title into $count distinct variations using Gemini AI.
     */
    public function paraphraseTitles(string $baseTitle, int $count = 2, ?string $style = null): array
    {
        $count = max(1, min(50, $count));
        $keys = static::getGeminiKeyList();
        if (empty($keys)) {
            // Fallback when no API key configured
            $variations = [$baseTitle];
            for ($i = 2; $i <= $count; $i++) {
                $variations[] = $baseTitle . " (Versi {$i})";
            }
            return $variations;
        }

        $styleInstruction = $style ? "Sesuaikan dengan gaya penulisan: \"{$style}\"." : "Gaya jurnalisme menarik, segar, dan memikat pembaca.";
        $prompt = "Tugas: Buatkan TEPAT {$count} variasi judul artikel berita yang diparafrase dari topik/judul dasar berikut:\n\"{$baseTitle}\"\n\nInstruksi:\n1. {$styleInstruction}\n2. Setiap judul harus memiliki sudut pandang, pilihan kata (diksi), dan daya tarik (hook) yang unik, namun tetap membahas topik utama yang sama.\n3. Jangan menambahkan nomor, bullet point, tanda bintang, atau tanda petik di awal baris.\n4. Berikan output TEPAT {$count} baris teks murni (satu baris untuk setiap judul), tanpa pengantar, penutup, atau markdown apapun.";

        $settings = static::getSettings();
        $model = $settings['gemini_model'] ?? 'gemini-1.5-flash';

        $client = new Client([
            'timeout' => 25,
            'connect_timeout' => 10,
        ]);

        foreach ($keys as $apiKey) {
            try {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
                $response = $client->post($url, [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt]
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.85,
                            'maxOutputTokens' => 1500,
                        ]
                    ]
                ]);

                $body = json_decode((string) $response->getBody(), true);
                $content = $body['candidates'][0]['content']['parts'][0]['text'] ?? null;

                if (!empty($content)) {
                    $lines = preg_split('/[\r\n]+/', trim($content));
                    $cleanTitles = [];
                    foreach ($lines as $line) {
                        $clean = trim(preg_replace('/^[\d\.\-\*\#\s\"\']+|[\"\']+$/u', '', trim($line)));
                        if (!empty($clean)) {
                            $cleanTitles[] = $clean;
                        }
                    }

                    if (!empty($cleanTitles)) {
                        while (count($cleanTitles) < $count) {
                            $cleanTitles[] = $baseTitle . " (Versi " . (count($cleanTitles) + 1) . ")";
                        }
                        return array_slice($cleanTitles, 0, $count);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("Gemini paraphrase title gagal: " . $e->getMessage());
                continue;
            }
        }

        // Fallback if all keys fail
        $variations = [$baseTitle];
        for ($i = 2; $i <= $count; $i++) {
            $variations[] = $baseTitle . " (Versi {$i})";
        }
        return $variations;
    }

    /**
     * Generate article content via Gemini API.
     */
    public function generateArticleContent(Article $article): string
    {
        $keys = static::getGeminiKeyList();
        if (empty($keys)) {
            throw new \Exception("Gemini API Key belum dikonfigurasi. Silakan tambahkan API key di menu Pengaturan AI.");
        }

        $prompt = $this->buildPrompt($article);
        $settings = static::getSettings();
        $model = $settings['gemini_model'] ?? 'gemini-1.5-flash';

        $client = new Client([
            'timeout' => 90,
            'connect_timeout' => 15,
        ]);

        $lastException = null;

        foreach ($keys as $index => $apiKey) {
            try {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

                $response = $client->post($url, [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt]
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.7,
                            'maxOutputTokens' => 4096,
                        ]
                    ]
                ]);

                $body = json_decode((string) $response->getBody(), true);
                $content = $body['candidates'][0]['content']['parts'][0]['text'] ?? null;

                if (!empty($content)) {
                    // Clean possible markdown fences
                    $content = preg_replace('/^```html\s*/i', '', $content);
                    $content = preg_replace('/^```\s*/i', '', $content);
                    $content = preg_replace('/\s*```$/', '', $content);
                    return trim($content);
                }

                throw new \Exception("Respons Gemini tidak memuat teks konten.");
            } catch (\Throwable $e) {
                Log::warning("Gemini API Key #{$index} gagal: " . $e->getMessage());
                $lastException = $e;
                continue; // Try next key
            }
        }

        throw new \Exception("Semua Gemini API Key gagal diproses. Terakhir: " . ($lastException ? $lastException->getMessage() : 'Unknown error'));
    }

    /**
     * Build the structured prompt based on PRD requirements.
     */
    public function buildPrompt(Article $article): string
    {
        $settings = static::getSettings();
        $template = $settings['prompt_template'];

        $campusName = $article->campus_context ?: 'Umum / Nasional';
        $styleName = $article->writing_style;

        // Replace template variables
        $prompt = str_replace(
            ['[JUDUL]', '[GAYA]', '[KAMPUS]'],
            [$article->title, $styleName, $campusName],
            $template
        );

        // Kategori berita
        $prompt .= "\n\nKategori Artikel: " . $article->category . ".";

        // Gaya Opini / Penulis
        if (!empty($article->opinion_author)) {
            if ($article->category === 'OPINI' || empty($article->is_event)) {
                $prompt .= "\n\nInstruksi Khusus Opini:\nArtikel ini merupakan artikel OPINI yang ditulis oleh \"" . $article->opinion_author . "\". Gunakan sudut pandang penulis opini berbobot, sertakan gagasan segar, perspektif personal yang berdasar fakta, serta sebut nama penulis secara natural di awal atau di akhir artikel.";
            } else {
                $prompt .= "\n\nOpini / Sudut Pandang Tambahan:\nDi dalam berita ini, cantumkan dan kutip pandangan / opini ahli dari \"" . $article->opinion_author . "\" sebagai narasumber penguat analisis atau komentar berbobot atas peristiwa/acara ini.";
            }
        }

        // Liputan Berita Aktual (NEWS)
        if ($article->category === 'NEWS') {
            $prompt .= "\n\nInstruksi Format Berita Aktual (NEWS):\n";
            $prompt .= "Artikel ini merupakan berita aktual. Susun artikel jurnalisme bermutu dengan menerapkan kaidah 5W+1H berdasarkan data rincian berikut:\n";
            if (!empty($article->event_theme)) $prompt .= "- Topik Berita: {$article->event_theme}\n";
            if (!empty($article->event_location)) $prompt .= "- Lokasi Kejadian / Berita: {$article->event_location}\n";
            if (!empty($article->event_time)) $prompt .= "- Waktu Kejadian: {$article->event_time}\n";
            if (!empty($article->event_date)) $prompt .= "- Hari & Tanggal: {$article->event_date}\n";
            if (!empty($article->opinion_author)) $prompt .= "- Opini Narasumber: \"{$article->opinion_author}\". Sertakan dan kutip pernyataan/opini narasumber ini secara natural di dalam tubuh berita sebagai sudut pandang utama.\n";
        }

        // Liputan Kegiatan & Berita Event (EVENT)
        if ($article->category === 'EVENT' || ($article->is_event && $article->category !== 'NEWS')) {
            $evtCategory = $article->event_category ?: 'Liputan Event';
            $prompt .= "\n\nInstruksi Liputan Berita Kegiatan (" . $evtCategory . "):\n";
            $prompt .= "Artikel ini memberitakan acara/kegiatan nyata. Rincian acara adalah sebagai berikut:\n";
            if (!empty($article->event_name)) $prompt .= "- Nama Event: {$article->event_name}\n";
            if (!empty($article->event_theme)) $prompt .= "- Tema Event: {$article->event_theme}\n";
            if (!empty($article->event_date)) $prompt .= "- Hari & Tanggal Event: {$article->event_date}\n";
            if (!empty($article->event_time)) $prompt .= "- Waktu Event: {$article->event_time}\n";
            if (!empty($article->event_location)) $prompt .= "- Lokasi Event: {$article->event_location}\n";
            if (!empty($article->event_organizer)) $prompt .= "- Penyelenggara: {$article->event_organizer}\n";
            if (!empty($article->event_host)) $prompt .= "- Pembawa Acara / MC: {$article->event_host}\n";
            if (!empty($article->event_speaker)) $prompt .= "- Narasumber / Pembicara: {$article->event_speaker}\n";
            if (!empty($article->event_executor)) $prompt .= "- Pelaksana / Panitia: {$article->event_executor}\n";
            if (!empty($article->event_participants)) $prompt .= "- Peserta: {$article->event_participants}\n";
            if (!empty($article->event_location_time)) $prompt .= "- Waktu & Tempat (Tambahan): {$article->event_location_time}\n";

            if ($article->event_category === 'Pra Event') {
                $prompt .= "\nFokus Sudut Pandang Pra Event:\n";
                $prompt .= "1. Tulis sebagai berita pemberitahuan (preview) bahwa acara ini AKAN SEGERA DISELENGGARAKAN.\n";
                $prompt .= "2. Buat judul dan pembuka yang menarik minat publik untuk berpartisipasi.\n";
                $prompt .= "3. Jelaskan urgensi tema acara dan apa saja wawasan atau manfaat yang akan didapatkan peserta.\n";
                $prompt .= "4. Cantumkan informasi jadwal (hari, tanggal, waktu, dan lokasi) dengan sangat jelas di tubuh berita.\n";
            } elseif ($article->event_category === 'Pasca Event') {
                $prompt .= "\nFokus Sudut Pandang Pasca Event:\n";
                $prompt .= "1. Tulis sebagai laporan liputan komprehensif bahwa acara SUDAH SUKSES DISELENGGARAKAN.\n";
                $prompt .= "2. Ulas jalannya acara, antusiasme audiens, dan pesan-pesan utama yang disampaikan narasumber.\n";
                $prompt .= "3. Sertakan kesimpulan dari tema kegiatan dan evaluasi positif atas terselenggaranya acara di lokasi dan waktu yang telah terlaksana.\n";
            } else {
                $prompt .= "\nFokus Sudut Pandang Liputan: Laporkan dinamika, kemeriahan, dan poin penting dari terselenggaranya acara.\n";
            }
        }

        // Context-specific instructions & CTA
        $prompt .= "\n\nKonteks & CTA Kampus / Media:\n";
        if ($article->campus_context === 'UBSI Karawang') {
            $prompt .= "Sertakan sudut pandang atau hubungan dengan civitas akademika UBSI Kampus Karawang, kota Karawang, atau peluang karier industri di Karawang. Di akhir artikel tambahkan ajakan aksi (CTA):\n\"" . $settings['cta_ubsi_karawang'] . "\"\n";
        } elseif ($article->campus_context === 'UBSI Cikampek') {
            $prompt .= "Sertakan sudut pandang civitas akademika UBSI Kampus Cikampek dan kontribusinya bagi masyarakat Cikampek & sekitarnya. Di akhir artikel tambahkan ajakan aksi (CTA):\n\"" . $settings['cta_ubsi_cikampek'] . "\"\n";
        } elseif ($article->campus_context === 'Signaltodays') {
            $prompt .= "Sajikan dengan gaya jurnalisme berita digital modern khas portal Signaltodays yang lugas, padat, dan faktual. Di akhir artikel tambahkan ajakan aksi (CTA):\n\"" . $settings['cta_signaltodays'] . "\"\n";
        } else {
            $prompt .= "Di akhir artikel tambahkan pesan penutup:\n\"" . $settings['cta_general'] . "\"\n";
        }

        return $prompt;
    }

    /**
     * Fetch cover image for non-event articles or return existing.
     */
    public function ensureCoverImage(Article $article): ?string
    {
        // If article already has an image (e.g. uploaded documentation for event)
        if (!empty($article->featured_image) && file_exists(storage_path('app/public/' . $article->featured_image))) {
            return $article->featured_image;
        }

        if ($article->is_event) {
            // Event articles can proceed without image if not uploaded
            return null;
        }

        // Non-event article: Try Pexels API
        $settings = static::getSettings();
        $pexelsKey = trim($settings['pexels_api_key'] ?? '');

        if (!empty($pexelsKey)) {
            try {
                $client = new Client(['timeout' => 15]);
                $query = urlencode($article->category . ' ' . Str::words($article->title, 3, ''));
                $res = $client->get("https://api.pexels.com/v1/search?query={$query}&per_page=1&orientation=landscape", [
                    'headers' => ['Authorization' => $pexelsKey]
                ]);

                $data = json_decode((string) $res->getBody(), true);
                if (!empty($data['photos'][0]['src']['large'])) {
                    $imgUrl = $data['photos'][0]['src']['large'];
                    $imgContent = file_get_contents($imgUrl);
                    if ($imgContent) {
                        $dir = 'articles/images';
                        Storage::disk('public')->makeDirectory($dir);
                        $filename = $dir . '/cover_' . $article->id . '_' . time() . '.jpg';
                        Storage::disk('public')->put($filename, $imgContent);

                        $article->update([
                            'featured_image' => $filename,
                            'image_source' => 'pexels',
                        ]);
                        return $filename;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("Pexels fetch failed for article #{$article->id}: " . $e->getMessage());
            }
        }

        // Fallback: Generate a clean styled gradient cover SVG or local placeholder
        try {
            $dir = 'articles/images';
            Storage::disk('public')->makeDirectory($dir);
            $filename = $dir . '/cover_' . $article->id . '_' . time() . '.png';

            // Create a simple canvas image using GD if available
            if (function_exists('imagecreatetruecolor')) {
                $width = 1200;
                $height = 630;
                $img = imagecreatetruecolor($width, $height);
                $bg = imagecolorallocate($img, 15, 23, 42); // Slate 900
                $accent = imagecolorallocate($img, 245, 158, 11); // Amber 500
                $white = imagecolorallocate($img, 255, 255, 255);
                
                imagefilledrectangle($img, 0, 0, $width, $height, $bg);
                imagefilledrectangle($img, 0, $height - 16, $width, $height, $accent);
                
                // Add header border line
                imageline($img, 0, 80, $width, 80, $accent);

                $fullPath = storage_path('app/public/' . $filename);
                imagepng($img, $fullPath);
                imagedestroy($img);

                $article->update([
                    'featured_image' => $filename,
                    'image_source' => 'fallback',
                ]);
                return $filename;
            }
        } catch (\Throwable $e) {
            Log::warning("Fallback image generation failed: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Generate Word (.docx) document using PhpWord.
     */
    public function generateDocx(Article $article): string
    {
        $phpWord = new PhpWord();

        // Default Font Settings
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(11);

        // Section Setup with Standard Margins
        $section = $phpWord->addSection([
            'marginTop' => 1440, // 1 inch
            'marginBottom' => 1440,
            'marginLeft' => 1440,
            'marginRight' => 1440,
        ]);

        // Category Badge & Context Header
        $badgeText = strtoupper($article->category) . '  •  ' . ($article->campus_context ?: 'UMUM') . '  •  ' . ($article->writing_style);
        $section->addText($badgeText, [
            'name' => 'Calibri',
            'size' => 9.5,
            'bold' => true,
            'color' => 'B45309', // Amber-700
        ], [
            'spaceAfter' => 120,
        ]);

        // Article Title (Heading 1)
        $section->addText($article->title, [
            'name' => 'Calibri',
            'size' => 22,
            'bold' => true,
            'color' => '0F172A', // Slate-900
        ], [
            'spaceAfter' => 140,
            'lineHeight' => 1.2,
        ]);

        // Metadata Info Bar
        $metaText = 'Dipublikasikan: ' . ($article->scheduled_at ? $article->scheduled_at->format('d M Y, H:i') : now()->format('d M Y, H:i')) . ' WIB';
        if (!empty($article->opinion_author)) {
            $metaText .= '  |  Penulis: ' . $article->opinion_author;
        }
        $section->addText($metaText, [
            'name' => 'Calibri',
            'size' => 9.5,
            'italic' => true,
            'color' => '64748B', // Slate-500
        ], [
            'spaceAfter' => 240,
        ]);

        // Cover Image
        if (!empty($article->featured_image)) {
            $imagePath = storage_path('app/public/' . $article->featured_image);
            if (file_exists($imagePath)) {
                $section->addImage($imagePath, [
                    'width' => 450,
                    'alignment' => Jc::CENTER,
                    'spaceAfter' => 260,
                ]);
            }
        }

        // Horizontal Line Separator
        $section->addLine([
            'weight' => 1,
            'width' => 450,
            'height' => 0,
            'color' => 'E2E8F0',
        ]);
        $section->addTextBreak(1);

        // Body Content Parsing
        $rawContent = $article->content ?? '';
        $this->appendHtmlToSection($section, $rawContent);

        // CTA Callout Box at bottom
        $settings = static::getSettings();
        $ctaText = match ($article->campus_context) {
            'UBSI Karawang' => $settings['cta_ubsi_karawang'],
            'UBSI Cikampek' => $settings['cta_ubsi_cikampek'],
            'Signaltodays' => $settings['cta_signaltodays'],
            default => $settings['cta_general'],
        };

        $section->addTextBreak(1);
        $table = $section->addTable([
            'borderColor' => 'F59E0B',
            'borderSize' => 12,
            'cellMargin' => 180,
            'bgColor' => 'FFFBEB', // Light amber background
        ]);
        $table->addRow();
        $cell = $table->addCell(9000);
        $cell->addText("📌 Call to Action / Informasi Terkait:", [
            'bold' => true,
            'size' => 10.5,
            'color' => 'B45309',
        ], ['spaceAfter' => 60]);

        $cell->addText($ctaText, [
            'size' => 10,
            'italic' => true,
            'color' => '334155',
        ], ['lineHeight' => 1.25]);

        // Ensure export folder exists
        $dir = 'articles/docx';
        Storage::disk('public')->makeDirectory($dir);
        $slug = Str::slug(Str::limit($article->title, 40, ''));
        $filename = $dir . '/article_' . $article->id . '_' . $slug . '.docx';
        $fullPath = storage_path('app/public/' . $filename);

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($fullPath);

        $article->update([
            'docx_path' => $filename,
        ]);

        return $filename;
    }

    /**
     * Helper to append HTML elements into a PhpWord Section.
     */
    protected function appendHtmlToSection($section, string $html): void
    {
        // Strip markdown backticks if any remained
        $clean = preg_replace('/```[a-z]*\n?/i', '', $html);

        // Use DOMDocument to parse structured tags
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        // Load with UTF-8 encoding
        $dom->loadHTML('<?xml encoding="utf-8" ?><div>' . $clean . '</div>');
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $nodes = $xpath->query('//div/*');

        if ($nodes->length === 0) {
            // Fallback: split by double newlines
            $paras = preg_split('/\n\s*\n/', strip_tags($clean));
            foreach ($paras as $p) {
                $t = trim($p);
                if (!empty($t)) {
                    $section->addText($t, ['size' => 11], ['spaceAfter' => 140, 'lineHeight' => 1.25]);
                }
            }
            return;
        }

        foreach ($nodes as $node) {
            $tag = strtolower($node->nodeName);
            $text = trim($node->textContent);

            if (empty($text)) {
                continue;
            }

            switch ($tag) {
                case 'h2':
                    $section->addText($text, [
                        'name' => 'Calibri',
                        'size' => 15,
                        'bold' => true,
                        'color' => '0F172A',
                    ], [
                        'spaceBefore' => 200,
                        'spaceAfter' => 100,
                    ]);
                    break;

                case 'h3':
                    $section->addText($text, [
                        'name' => 'Calibri',
                        'size' => 13,
                        'bold' => true,
                        'color' => '1E293B',
                    ], [
                        'spaceBefore' => 160,
                        'spaceAfter' => 80,
                    ]);
                    break;

                case 'blockquote':
                    $section->addText('“' . $text . '”', [
                        'name' => 'Calibri',
                        'size' => 10.5,
                        'italic' => true,
                        'color' => '475569',
                    ], [
                        'indentLeft' => 360,
                        'spaceBefore' => 100,
                        'spaceAfter' => 140,
                    ]);
                    break;

                case 'ul':
                case 'ol':
                    $listItems = $node->getElementsByTagName('li');
                    foreach ($listItems as $li) {
                        $liText = trim($li->textContent);
                        if (!empty($liText)) {
                            $section->addListItem($liText, 0, [
                                'size' => 11,
                                'color' => '1E293B',
                            ], [
                                'spaceAfter' => 60,
                                'lineHeight' => 1.2,
                            ]);
                        }
                    }
                    $section->addTextBreak(0.5);
                    break;

                default: // p and others
                    $section->addText($text, [
                        'name' => 'Calibri',
                        'size' => 11,
                        'color' => '1E293B',
                    ], [
                        'spaceAfter' => 140,
                        'lineHeight' => 1.25,
                    ]);
                    break;
            }
        }
    }

    /**
     * Main execution pipeline for a single article.
     */
    public function processArticle(Article $article): bool
    {
        $article->update([
            'status' => 'processing',
            'error_message' => null,
        ]);

        try {
            // 1. Generate text content
            $content = $this->generateArticleContent($article);
            $wordCount = str_word_count(strip_tags($content));

            $article->update([
                'content' => $content,
                'word_count' => $wordCount,
            ]);

            // 2. Ensure cover image
            $this->ensureCoverImage($article);

            // 3. Generate Word document (.docx)
            $this->generateDocx($article);

            $article->update([
                'status' => 'completed',
                'error_message' => null,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error("Gagal memproses artikel #{$article->id}: " . $e->getMessage());

            $article->increment('retry_count');
            $article->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Create ZIP archive of all completed articles.
     */
    public function createZipOfCompletedArticles(?array $articleIds = null): string
    {
        $query = Article::where('status', 'completed')->whereNotNull('docx_path');
        if (!empty($articleIds)) {
            $query->whereIn('id', $articleIds);
        }
        $articles = $query->get();

        if ($articles->isEmpty()) {
            throw new \Exception("Tidak ada dokumen artikel selesai yang dapat diunduh.");
        }

        $zipDir = storage_path('app/public/articles/exports');
        if (!file_exists($zipDir)) {
            mkdir($zipDir, 0755, true);
        }

        $zipFileName = 'WordCraft_Articles_' . date('Ymd_His') . '.zip';
        $zipFilePath = $zipDir . '/' . $zipFileName;

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Gagal menginisialisasi berkas arsip ZIP.");
        }

        $addedCount = 0;
        foreach ($articles as $art) {
            $fullPath = storage_path('app/public/' . $art->docx_path);
            if (file_exists($fullPath)) {
                $cleanTitle = Str::slug(Str::limit($art->title, 50, ''));
                $entryName = "[$art->category] [{$art->campus_context}] {$cleanTitle}.docx";
                $zip->addFile($fullPath, $entryName);
                $addedCount++;
            }
        }

        $zip->close();

        if ($addedCount === 0) {
            throw new \Exception("Tidak ada berkas fisik dokumen yang ditemukan pada disk penyimpanan.");
        }

        return $zipFilePath;
    }

    /**
     * Sync articles from articles_to_generate.json in project root (FR-10).
     */
    public function syncFromJsonFile(?string $path = null): array
    {
        $filePath = $path ?: base_path('articles_to_generate.json');

        if (!file_exists($filePath)) {
            throw new \Exception("Berkas 'articles_to_generate.json' tidak ditemukan di direktori utama.");
        }

        $raw = file_get_contents($filePath);
        $items = json_decode($raw, true);

        if (!is_array($items)) {
            throw new \Exception("Format JSON pada 'articles_to_generate.json' tidak valid.");
        }

        $created = 0;
        $skipped = 0;

        foreach ($items as $item) {
            $title = trim($item['title'] ?? '');
            if (empty($title)) {
                continue;
            }

            // Check if article with this title already exists
            $exists = Article::where('title', $title)->exists();
            if ($exists) {
                $skipped++;
                continue;
            }

            Article::create([
                'title' => $title,
                'writing_style' => $item['writing_style'] ?? 'Judul Paling Menarik',
                'category' => $item['category'] ?? 'NEWS',
                'campus_context' => $item['campus_context'] ?? null,
                'opinion_author' => $item['opinion_author'] ?? null,
                'is_event' => !empty($item['is_event']),
                'event_category' => $item['event_category'] ?? null,
                'event_organizer' => $item['event_organizer'] ?? null,
                'status' => 'pending',
                'scheduled_at' => !empty($item['scheduled_at']) ? \Carbon\Carbon::parse($item['scheduled_at']) : now()->addMinutes(10),
                'user_id' => auth()->id() ?? null,
            ]);

            $created++;
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
            'total' => count($items),
        ];
    }
}
