<x-app-layout title="WordCraft AI — Antrean & Daftar Artikel">
    <div class="space-y-6">

        <!-- Header Banner -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-amber-950 p-6 lg:p-8 text-white shadow-xl border border-slate-700/60">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="space-y-1">
                    <div class="inline-flex items-center space-x-2 px-2.5 py-1 rounded-full bg-amber-500/20 border border-amber-400/30 text-amber-300 text-xs font-bold tracking-wide uppercase">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-400"></i>
                        <span>WordCraft AI Engine</span>
                    </div>
                    <h1 class="text-2xl lg:text-3xl font-extrabold tracking-tight">Manajemen & Antrean Artikel</h1>
                    <p class="text-sm text-slate-300 max-w-2xl">
                        Otomatisasi pembuatan draf berita, liputan kegiatan kampus, dan artikel opini dengan Gemini AI serta ekspor langsung ke Microsoft Word (.docx).
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <a href="{{ route('articles.create') }}" 
                        class="inline-flex items-center space-x-2 px-4 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 font-bold rounded-xl shadow-lg shadow-amber-500/20 transition duration-150 transform hover:-translate-y-0.5">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        <span>Buat Artikel AI</span>
                    </a>

                    <a href="{{ route('articles.download-zip') }}" 
                        class="inline-flex items-center space-x-2 px-3.5 py-2.5 bg-slate-800 hover:bg-slate-700 text-white font-semibold rounded-xl border border-slate-600/60 shadow-md transition duration-150">
                        <i data-lucide="archive" class="w-4 h-4 text-amber-400"></i>
                        <span>Unduh Semua (.ZIP)</span>
                    </a>

                    <form action="{{ route('articles.sync-json') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                            title="Sinkronisasi judul & jadwal dari articles_to_generate.json"
                            class="inline-flex items-center space-x-1.5 px-3 py-2.5 bg-slate-800/80 hover:bg-slate-700 text-slate-300 hover:text-white font-medium rounded-xl border border-slate-700 transition duration-150">
                            <i data-lucide="refresh-cw" class="w-4 h-4 text-sky-400"></i>
                            <span>Sync JSON</span>
                        </button>
                    </form>

                    <a href="{{ route('articles.settings') }}" 
                        title="Pengaturan AI & Prompt"
                        class="p-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-xl border border-slate-700 transition duration-150">
                        <i data-lucide="settings" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>

            <!-- Background Glowing Accent -->
            <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center text-slate-700">
                    <i data-lucide="newspaper" class="w-6 h-6"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Artikel</p>
                    <p class="text-2xl font-black text-slate-800">{{ number_format($stats['total']) }}</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-6 h-6"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Selesai (.DOCX)</p>
                    <p class="text-2xl font-black text-emerald-600">{{ number_format($stats['completed']) }}</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="clock" class="w-6 h-6"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Antrean / Jadwal</p>
                    <p class="text-2xl font-black text-amber-600">{{ number_format($stats['pending']) }}</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i data-lucide="alert-circle" class="w-6 h-6"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Perlu Retry / Gagal</p>
                    <p class="text-2xl font-black text-rose-600">{{ number_format($stats['failed']) }}</p>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm space-y-3">
            <form method="GET" action="{{ route('articles.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                
                <!-- Search Keyword -->
                <div class="md:col-span-5 relative">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari judul artikel, topik, narasumber..."
                        class="w-full pl-10 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                </div>

                <!-- Status Filter -->
                <div class="md:col-span-3">
                    <select name="status" class="w-full py-2 px-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>✓ Selesai (Completed)</option>
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>⏳ Menunggu Jadwal (Pending)</option>
                        <option value="processing" {{ $status === 'processing' ? 'selected' : '' }}>⚙ Sedang Diproses</option>
                        <option value="failed" {{ $status === 'failed' ? 'selected' : '' }}>✗ Gagal (Failed)</option>
                    </select>
                </div>

                <!-- Campus Filter -->
                <div class="md:col-span-3">
                    <select name="campus" class="w-full py-2 px-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                        <option value="all" {{ $campus === 'all' ? 'selected' : '' }}>Semua Konteks</option>
                        <option value="UBSI Karawang" {{ $campus === 'UBSI Karawang' ? 'selected' : '' }}>UBSI Karawang</option>
                        <option value="UBSI Cikampek" {{ $campus === 'UBSI Cikampek' ? 'selected' : '' }}>UBSI Cikampek</option>
                        <option value="Signaltodays" {{ $campus === 'Signaltodays' ? 'selected' : '' }}>Signaltodays</option>
                        <option value="Umum" {{ $campus === 'Umum' ? 'selected' : '' }}>Umum</option>
                    </select>
                </div>

                <!-- Submit & Reset -->
                <div class="md:col-span-1 flex items-center space-x-1">
                    <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold transition flex items-center justify-center">
                        <i data-lucide="filter" class="w-4 h-4"></i>
                    </button>
                    @if(!empty($search) || $status !== 'all' || $campus !== 'all')
                        <a href="{{ route('articles.index') }}" title="Reset Filter" class="p-2 text-slate-400 hover:text-rose-500 hover:bg-slate-100 rounded-xl transition">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Articles Table Card with Bulk Action Support -->
        <form id="bulkForm" action="{{ route('articles.bulk-destroy') }}" method="POST">
            @csrf
        </form>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            
            <!-- Table Header Bar -->
            <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-slate-50/50">
                <div class="flex items-center space-x-2">
                    <span class="text-sm font-bold text-slate-700">Daftar Artikel</span>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-slate-200 text-slate-600 font-semibold">{{ $articles->total() }} Data</span>
                </div>

                <!-- Bulk Actions -->
                <div class="flex items-center space-x-2">
                    <button type="submit" form="bulkForm" name="mode" value="selected" 
                        onclick="return confirm('Hapus artikel yang dipilih? Berkas dokumen terkait akan dihapus secara permanen.')"
                        class="inline-flex items-center space-x-1.5 px-3 py-1.5 text-xs font-semibold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg transition">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        <span>Hapus Terpilih</span>
                    </button>

                    <button type="submit" form="bulkForm" name="mode" value="all" 
                        onclick="return confirm('PERINGATAN: Apakah Anda yakin ingin menghapus SELURUH artikel di database?')"
                        class="inline-flex items-center space-x-1.5 px-3 py-1.5 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition">
                        <i data-lucide="trash" class="w-3.5 h-3.5"></i>
                        <span>Hapus Semua</span>
                    </button>
                </div>
            </div>

                <!-- Table Content -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200/70 bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="py-3 px-4 w-10 text-center">
                                    <input type="checkbox" id="selectAll" class="rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                                </th>
                                <th class="py-3 px-4">Artikel & Topik</th>
                                <th class="py-3 px-3">Konteks / Kampus</th>
                                <th class="py-3 px-3">Kategori & Gaya</th>
                                <th class="py-3 px-3">Jadwal Rilis</th>
                                <th class="py-3 px-3 text-center">Status</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse($articles as $article)
                                <tr class="hover:bg-amber-50/20 transition duration-100">
                                    <td class="py-3.5 px-4 text-center">
                                        <input type="checkbox" name="ids[]" value="{{ $article->id }}" form="bulkForm" class="article-checkbox rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                                    </td>

                                    <!-- Title & Summary -->
                                    <td class="py-3.5 px-4 max-w-md">
                                        <div class="flex items-start space-x-3">
                                            @if(!empty($article->featured_image))
                                                <img src="{{ asset('storage/' . $article->featured_image) }}" alt="Cover" class="w-12 h-12 object-cover rounded-lg border border-slate-200 shadow-xs shrink-0">
                                            @else
                                                <div class="w-12 h-12 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 shrink-0">
                                                    <i data-lucide="image" class="w-5 h-5"></i>
                                                </div>
                                            @endif

                                            <div class="min-w-0">
                                                <a href="{{ route('articles.show', $article) }}" class="font-bold text-slate-800 hover:text-amber-600 transition block truncate">
                                                    {{ $article->title }}
                                                </a>
                                                <div class="flex items-center space-x-2 text-xs text-slate-500 mt-0.5">
                                                    @if($article->is_event)
                                                        <span class="inline-flex items-center space-x-1 text-purple-600 font-semibold">
                                                            <i data-lucide="calendar" class="w-3 h-3"></i>
                                                            <span>{{ $article->event_category ?: 'Event' }}</span>
                                                        </span>
                                                        <span>•</span>
                                                    @endif
                                                    @if($article->opinion_author)
                                                        <span class="text-slate-600">Oleh: <strong class="text-slate-800">{{ $article->opinion_author }}</strong></span>
                                                        <span>•</span>
                                                    @endif
                                                    <span>{{ $article->word_count > 0 ? $article->word_count . ' kata' : 'Draf AI' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Campus Context -->
                                    <td class="py-3.5 px-3">
                                        @if($article->campus_context === 'UBSI Karawang')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                                UBSI Karawang
                                            </span>
                                        @elseif($article->campus_context === 'UBSI Cikampek')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-sky-100 text-sky-800 border border-sky-300">
                                                UBSI Cikampek
                                            </span>
                                        @elseif($article->campus_context === 'Signaltodays')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                Signaltodays
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-700 border border-slate-300">
                                                Umum
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Category & Style -->
                                    <td class="py-3.5 px-3">
                                        <p class="font-bold text-xs text-slate-800 uppercase tracking-wide">{{ $article->category }}</p>
                                        <p class="text-[11px] text-slate-500 truncate max-w-[140px]">{{ $article->writing_style }}</p>
                                    </td>

                                    <!-- Scheduled Date -->
                                    <td class="py-3.5 px-3 whitespace-nowrap text-xs text-slate-600">
                                        <div class="flex items-center space-x-1.5">
                                            <i data-lucide="clock" class="w-3.5 h-3.5 text-slate-400"></i>
                                            <span>{{ $article->scheduled_at ? $article->scheduled_at->format('d M Y') : '-' }}</span>
                                        </div>
                                        <span class="text-[11px] text-slate-400 block ml-5">
                                            {{ $article->scheduled_at ? $article->scheduled_at->format('H:i') . ' WIB' : '' }}
                                        </span>
                                    </td>

                                    <!-- Status Badge -->
                                    <td class="py-3.5 px-3 text-center whitespace-nowrap">
                                        @php $badge = $article->status_badge; @endphp
                                        <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full text-xs font-bold border {{ $badge['bg'] }}">
                                            <i data-lucide="{{ $badge['icon'] }}" class="w-3.5 h-3.5"></i>
                                            <span>{{ $badge['label'] }}</span>
                                        </span>

                                        @if($article->isFailed() && $article->retry_count > 0)
                                            <span class="block text-[10px] text-rose-500 font-semibold mt-0.5">
                                                Retry: {{ $article->retry_count }}/3
                                            </span>
                                        @endif

                                        @if($article->error_message)
                                            <span title="{{ $article->error_message }}" class="block text-[10px] text-slate-400 underline decoration-dotted cursor-help max-w-[120px] truncate mx-auto">
                                                {{ Str::limit($article->error_message, 20) }}
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Action Buttons -->
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center space-x-1">
                                            
                                            <!-- Preview -->
                                            <a href="{{ route('articles.show', $article) }}" 
                                                title="Lihat Detail Artikel"
                                                class="p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition">
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                            </a>

                                            <!-- Download Docx -->
                                            @if($article->isCompleted())
                                                <a href="{{ route('articles.download', $article) }}" 
                                                    title="Unduh Dokumen Word (.docx)"
                                                    class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition font-semibold">
                                                    <i data-lucide="file-down" class="w-4 h-4"></i>
                                                </a>
                                            @endif

                                            <!-- Process Now (For Pending) -->
                                            @if($article->isPending())
                                                <form action="{{ route('articles.process-now', $article) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" 
                                                        title="Proses Sekarang dengan AI"
                                                        class="p-1.5 text-amber-600 hover:text-amber-800 hover:bg-amber-50 rounded-lg transition">
                                                        <i data-lucide="play" class="w-4 h-4"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            <!-- Retry (For Failed) -->
                                            @if($article->canRetry())
                                                <form action="{{ route('articles.retry', $article) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" 
                                                        title="Coba Ulang Pembuatan AI"
                                                        class="p-1.5 text-rose-600 hover:text-rose-800 hover:bg-rose-50 rounded-lg transition">
                                                        <i data-lucide="rotate-cw" class="w-4 h-4"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            <!-- Delete Single -->
                                            <form action="{{ route('articles.destroy', $article) }}" method="POST" class="inline" onsubmit="return confirm('Hapus artikel ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                    title="Hapus Artikel"
                                                    class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center">
                                        <div class="max-w-sm mx-auto space-y-3">
                                            <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                                <i data-lucide="newspaper" class="w-8 h-8"></i>
                                            </div>
                                            <p class="text-base font-bold text-slate-700">Belum Ada Artikel</p>
                                            <p class="text-xs text-slate-500">Mulai buat draf artikel berita atau kegiatan kampus pertama Anda dengan bantuan WordCraft AI.</p>
                                            <div class="pt-2">
                                                <a href="{{ route('articles.create') }}" class="inline-flex items-center space-x-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-xl text-xs transition">
                                                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                                                    <span>Buat Artikel Pertama</span>
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($articles->hasPages())
                    <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                        {{ $articles->links() }}
                    </div>
                @endif
            </div>

    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.article-checkbox');

            if (selectAll) {
                selectAll.addEventListener('change', (e) => {
                    checkboxes.forEach(cb => cb.checked = e.target.checked);
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
