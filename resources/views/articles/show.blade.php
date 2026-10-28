<x-app-layout title="{{ $article->title }} — WordCraft AI">
    <div class="max-w-4xl mx-auto space-y-6">

        <!-- Top Navigation Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <nav class="flex items-center space-x-2 text-xs font-semibold text-slate-500">
                <a href="{{ route('articles.index') }}" class="hover:text-amber-600 transition">Daftar Artikel</a>
                <span>/</span>
                <span class="text-slate-700 truncate max-w-xs">{{ Str::limit($article->title, 35) }}</span>
            </nav>

            <div class="flex items-center space-x-2">
                @if($article->isCompleted())
                    <a href="{{ route('articles.download', $article) }}" 
                        class="inline-flex items-center space-x-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition duration-150">
                        <i data-lucide="file-down" class="w-4 h-4"></i>
                        <span>Unduh Dokumen (.DOCX)</span>
                    </a>
                @elseif($article->isPending())
                    <form action="{{ route('articles.process-now', $article) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                            class="inline-flex items-center space-x-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-bold rounded-xl shadow-md transition duration-150">
                            <i data-lucide="play" class="w-4 h-4"></i>
                            <span>Proses Sekarang</span>
                        </button>
                    </form>
                @elseif($article->canRetry())
                    <form action="{{ route('articles.retry', $article) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                            class="inline-flex items-center space-x-1.5 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-md transition duration-150">
                            <i data-lucide="rotate-cw" class="w-4 h-4"></i>
                            <span>Coba Ulang (Retry {{ $article->retry_count }}/3)</span>
                        </button>
                    </form>
                @endif

                <form action="{{ route('articles.destroy', $article) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus artikel ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-slate-100 rounded-xl transition">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Article Paper Container -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-md overflow-hidden">
            
            <!-- Cover Header -->
            <div class="p-6 sm:p-10 border-b border-slate-100 space-y-4">
                
                <!-- Category & Context Badges -->
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-black tracking-wider uppercase bg-amber-100 text-amber-800 border border-amber-300">
                        {{ $article->category }}
                    </span>

                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                        {{ $article->campus_context ?: 'Umum / Nasional' }}
                    </span>

                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                        {{ $article->writing_style }}
                    </span>

                    @php $badge = $article->status_badge; @endphp
                    <span class="ml-auto inline-flex items-center space-x-1 px-3 py-1 rounded-full text-xs font-bold border {{ $badge['bg'] }}">
                        <i data-lucide="{{ $badge['icon'] }}" class="w-3.5 h-3.5"></i>
                        <span>{{ $badge['label'] }}</span>
                    </span>
                </div>

                <!-- Title -->
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 leading-tight">
                    {{ $article->title }}
                </h1>

                <!-- Meta Line -->
                <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500 pt-2 border-t border-slate-100">
                    <div class="flex items-center space-x-1.5">
                        <i data-lucide="clock" class="w-4 h-4 text-slate-400"></i>
                        <span>Jadwal: {{ $article->scheduled_at ? $article->scheduled_at->format('d M Y, H:i') : '-' }} WIB</span>
                    </div>

                    @if($article->opinion_author)
                        <div class="flex items-center space-x-1.5">
                            <i data-lucide="user" class="w-4 h-4 text-slate-400"></i>
                            <span>Penulis Opini: <strong>{{ $article->opinion_author }}</strong></span>
                        </div>
                    @endif

                    @if($article->word_count > 0)
                        <div class="flex items-center space-x-1.5">
                            <i data-lucide="file-text" class="w-4 h-4 text-slate-400"></i>
                            <span>{{ number_format($article->word_count) }} kata</span>
                        </div>
                    @endif
                </div>

                <!-- Event Info Banner if applicable -->
                @if($article->is_event || in_array($article->category, ['BERITA', 'NEWS']))
                    <div class="p-5 bg-gradient-to-r from-amber-50/80 to-purple-50/60 rounded-2xl border border-amber-200/80 text-xs text-slate-800 space-y-2 mt-4">
                        <div class="flex items-center space-x-2 text-amber-900 font-bold text-xs uppercase tracking-wider pb-1 border-b border-amber-200/60">
                            <i data-lucide="calendar" class="w-4 h-4 text-amber-600"></i>
                            <span>Rincian Informasi Acara / Berita ({{ $article->event_category ?: 'Liputan Event' }})</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
                            @if($article->event_name) <div><strong class="text-slate-900">Nama Event:</strong> {{ $article->event_name }}</div> @endif
                            @if($article->event_theme) <div><strong class="text-slate-900">Tema Event:</strong> {{ $article->event_theme }}</div> @endif
                            @if($article->event_date) <div><strong class="text-slate-900">Hari & Tanggal:</strong> {{ $article->event_date }}</div> @endif
                            @if($article->event_time) <div><strong class="text-slate-900">Waktu:</strong> {{ $article->event_time }}</div> @endif
                            @if($article->event_location) <div class="sm:col-span-2"><strong class="text-slate-900">Lokasi:</strong> {{ $article->event_location }}</div> @endif
                            @if($article->event_organizer) <div><strong class="text-slate-900">Penyelenggara:</strong> {{ $article->event_organizer }}</div> @endif
                            @if($article->event_speaker) <div><strong class="text-slate-900">Narasumber:</strong> {{ $article->event_speaker }}</div> @endif
                            @if($article->event_host) <div><strong class="text-slate-900">MC / Host:</strong> {{ $article->event_host }}</div> @endif
                            @if($article->event_participants) <div><strong class="text-slate-900">Peserta:</strong> {{ $article->event_participants }}</div> @endif
                            @if($article->event_location_time) <div><strong class="text-slate-900">Waktu & Tempat:</strong> {{ $article->event_location_time }}</div> @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Featured Cover Image -->
            @if(!empty($article->featured_image))
                <div class="w-full max-h-[460px] overflow-hidden bg-slate-900 flex items-center justify-center">
                    <img src="{{ asset('storage/' . $article->featured_image) }}" alt="{{ $article->title }}" class="w-full object-cover">
                </div>
            @endif

            <!-- Article Body Content -->
            <div class="p-6 sm:p-10 space-y-6">
                
                @if($article->isCompleted() && !empty($article->content))
                    <!-- Rendered HTML Content with elegant typography -->
                    <div class="prose prose-slate max-w-none prose-headings:font-extrabold prose-h2:text-xl prose-h2:text-slate-900 prose-h2:mt-8 prose-h2:mb-4 prose-h3:text-lg prose-h3:text-slate-800 prose-p:text-slate-700 prose-p:leading-relaxed prose-p:text-base prose-blockquote:border-l-4 prose-blockquote:border-amber-500 prose-blockquote:bg-amber-50/30 prose-blockquote:py-2 prose-blockquote:px-4 prose-blockquote:rounded-r-lg prose-blockquote:italic prose-li:text-slate-700">
                        {!! $article->content !!}
                    </div>

                    <!-- Call To Action (CTA) Callout Box -->
                    <div class="mt-10 p-6 rounded-2xl bg-amber-50 border border-amber-300 shadow-xs space-y-2">
                        <div class="flex items-center space-x-2 text-amber-900 font-bold text-sm">
                            <i data-lucide="megaphone" class="w-4 h-4 text-amber-600"></i>
                            <span>Informasi & Call To Action (CTA):</span>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-700 leading-relaxed italic">
                            @php
                                $settings = \App\Services\WordCraftService::getSettings();
                                $cta = match ($article->campus_context) {
                                    'UBSI Karawang' => $settings['cta_ubsi_karawang'],
                                    'UBSI Cikampek' => $settings['cta_ubsi_cikampek'],
                                    'Signaltodays' => $settings['cta_signaltodays'],
                                    default => $settings['cta_general'],
                                };
                            @endphp
                            {{ $cta }}
                        </p>
                    </div>

                @elseif($article->isProcessing())
                    <div class="py-16 text-center space-y-3">
                        <div class="w-12 h-12 mx-auto text-blue-600 animate-spin flex items-center justify-center">
                            <i data-lucide="loader-2" class="w-10 h-10"></i>
                        </div>
                        <p class="text-base font-bold text-slate-800">Sedang Memproses Pembuatan Artikel...</p>
                        <p class="text-xs text-slate-500">Gemini AI sedang menyusun draf konten dan merancang dokumen Word (.docx).</p>
                    </div>

                @elseif($article->isPending())
                    <div class="py-16 text-center space-y-4">
                        <div class="w-12 h-12 mx-auto rounded-full bg-amber-50 text-amber-600 flex items-center justify-center">
                            <i data-lucide="clock" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <p class="text-base font-bold text-slate-800">Menunggu Jadwal Eksekusi</p>
                            <p class="text-xs text-slate-500">Artikel ini dijadwalkan untuk dirilis pada {{ $article->scheduled_at ? $article->scheduled_at->format('d M Y, H:i') : '-' }} WIB.</p>
                        </div>
                        <form action="{{ route('articles.process-now', $article) }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex items-center space-x-2 px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-bold rounded-xl shadow-md transition">
                                <i data-lucide="play" class="w-4 h-4"></i>
                                <span>Proses Sekarang Tanpa Menunggu</span>
                            </button>
                        </form>
                    </div>

                @elseif($article->isFailed())
                    <div class="py-12 p-6 bg-rose-50/50 rounded-2xl border border-rose-200 space-y-3 text-center">
                        <div class="w-12 h-12 mx-auto rounded-full bg-rose-100 text-rose-600 flex items-center justify-center">
                            <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                        </div>
                        <p class="text-base font-bold text-rose-800">Pembuatan Artikel Gagal</p>
                        <p class="text-xs text-rose-600 max-w-lg mx-auto font-mono bg-white p-3 rounded-lg border border-rose-200">
                            {{ $article->error_message ?: 'Terjadi kesalahan saat memanggil AI service atau membuat berkas dokumen.' }}
                        </p>
                        @if($article->canRetry())
                            <div class="pt-2">
                                <form action="{{ route('articles.retry', $article) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center space-x-2 px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-md transition">
                                        <i data-lucide="rotate-cw" class="w-4 h-4"></i>
                                        <span>Coba Ulang Sekarang (Retry {{ $article->retry_count }}/3)</span>
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @endif

            </div>

        </div>

    </div>
</x-app-layout>
