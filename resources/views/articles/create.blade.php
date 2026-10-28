<x-app-layout title="Buat Berita / Artikel AI — WordCraft AI">
    <div class="max-w-5xl mx-auto space-y-6" x-data="{ 
        selectedCategory: 'EVENT',
        eventCategory: 'Pra Event',
        mode: 'instant',
        writingStyle: 'Gaya Media Nasional',
        titleCount: 0,
        articleCount: 1,
        scheduleInterval: 0,
        selectedCampuses: ['UBSI Karawang'],
        mainTitle: '{{ old('title', '') }}',
        paraphrasedTitles: [],
        isParaphrasing: false,
        paraphraseError: '',
        imagePreview: null,
        imageFileName: '',
        imageFileSize: '',
        isDragging: false,

        getTotalArticles() {
            const count = parseInt(this.articleCount) || 1;
            const campusCount = this.selectedCampuses.length > 0 ? this.selectedCampuses.length : 1;
            return campusCount * count;
        },

        initTitles() {
            const count = Math.max(1, Math.min(50, parseInt(this.articleCount) || 1));
            this.articleCount = count;
            while (this.paraphrasedTitles.length < count) {
                const idx = this.paraphrasedTitles.length;
                this.paraphrasedTitles.push(idx === 0 && this.mainTitle ? this.mainTitle : '');
            }
            if (this.paraphrasedTitles.length > count) {
                this.paraphrasedTitles = this.paraphrasedTitles.slice(0, count);
            }
        },

        setArticleCount(n) {
            this.articleCount = n;
            this.initTitles();
        },

        async triggerParaphrase() {
            if (!this.mainTitle || this.mainTitle.trim() === '') {
                alert('Silakan ketikkan Topik / Judul Utama terlebih dahulu.');
                return;
            }
            this.isParaphrasing = true;
            this.paraphraseError = '';

            try {
                const response = await fetch('{{ route('articles.paraphrase-titles') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        title: this.mainTitle,
                        count: parseInt(this.articleCount) || 1,
                        writing_style: this.writingStyle
                    })
                });

                const data = await response.json();
                if (data.success && Array.isArray(data.titles)) {
                    this.paraphrasedTitles = data.titles;
                } else {
                    this.paraphraseError = data.message || 'Gagal memparafrase judul dengan AI.';
                }
            } catch (err) {
                this.paraphraseError = 'Koneksi gagal saat menghubungi AI. Judul tetap akan diparafrase otomatis saat disimpan.';
            } finally {
                this.isParaphrasing = false;
            }
        },

        isEvent() {
            return this.selectedCategory === 'EVENT';
        },

        isNews() {
            return this.selectedCategory === 'NEWS';
        },

        handleImageUpload(e) {
            const file = e.target.files ? e.target.files[0] : (e.dataTransfer ? e.dataTransfer.files[0] : null);
            if (file) {
                this.imageFileName = file.name;
                this.imageFileSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                const reader = new FileReader();
                reader.onload = (event) => {
                    this.imagePreview = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        removeImage() {
            this.imagePreview = null;
            this.imageFileName = '';
            this.imageFileSize = '';
            document.querySelectorAll('.event-image-input').forEach((el) => { el.value = ''; });
        }
    }">

        <!-- Navigation Breadcrumb & Title -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <nav class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                    <a href="{{ route('articles.index') }}" class="hover:text-amber-600 transition">Artikel & Berita AI</a>
                    <span>/</span>
                    <span class="text-slate-700">Buat Tulisan Baru</span>
                </nav>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center space-x-2.5">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-600 flex items-center justify-center">
                        <i data-lucide="sparkles" class="w-4 h-4"></i>
                    </span>
                    <span>Generator Berita & Artikel Otomatis AI</span>
                </h1>
            </div>

            <div class="flex items-center space-x-2">
                <a href="{{ route('articles.index') }}" class="px-3.5 py-2 text-xs font-semibold text-slate-600 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl transition shadow-xs flex items-center space-x-1.5">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    <span>Kembali ke Antrean</span>
                </a>
            </div>
        </div>

        <form action="{{ route('articles.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Section 1: Tentukan Jumlah Artikel yang Ingin Dibuat (Muncul Pertama!) -->
            <div class="bg-white rounded-2xl p-6 border-2 border-amber-400/80 shadow-sm space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div class="flex items-center space-x-3">
                        <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-amber-500 to-amber-600 text-slate-950 font-black text-sm flex items-center justify-center shadow-sm">1</span>
                        <div>
                            <div class="flex items-center space-x-2">
                                <h2 class="text-base font-bold text-slate-800">Jumlah Artikel yang Ingin Dibuat</h2>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                    Langkah Pertama
                                </span>
                            </div>
                            <p class="text-xs text-slate-500">Tentukan berapa banyak draf artikel yang akan diproduksi. Jika lebih dari satu, judul akan otomatis disesuaikan & diparafrase oleh AI.</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200">
                        <div>
                            <label for="article_count" class="text-xs font-bold text-slate-800 uppercase tracking-wider block">
                                Kuantitas Draf Artikel <span class="text-rose-500">*</span>
                            </label>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Masukkan jumlah artikel (1 - 50) atau gunakan tombol cepat di samping.
                            </p>
                        </div>

                        <!-- Stepper & Input Kuantitas -->
                        <div class="flex items-center space-x-2 shrink-0">
                            <button type="button" 
                                @click="if (articleCount > 1) { articleCount--; initTitles(); }"
                                :disabled="articleCount <= 1"
                                class="w-10 h-10 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 active:bg-slate-200 disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center text-slate-700 transition font-bold shadow-xs cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                            </button>

                            <div class="relative w-24">
                                <input type="number" 
                                    id="article_count" 
                                    name="article_count" 
                                    min="1" 
                                    max="50" 
                                    required
                                    x-model.number="articleCount"
                                    @input="initTitles()"
                                    class="w-full text-center py-2 px-3 text-base font-extrabold text-slate-900 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition shadow-inner">
                            </div>

                            <button type="button" 
                                @click="if (articleCount < 50) { articleCount++; initTitles(); }"
                                :disabled="articleCount >= 50"
                                class="w-10 h-10 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 active:bg-slate-200 disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center text-slate-700 transition font-bold shadow-xs cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Preset Pills -->
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold text-slate-500 mr-1">Pilihan Cepat:</span>
                        <template x-for="preset in [1, 2, 3, 5, 10, 20]" :key="preset">
                            <button type="button" 
                                @click="setArticleCount(preset)"
                                :class="articleCount === preset ? 'bg-amber-500 text-slate-950 font-bold border-amber-500 shadow-xs' : 'bg-white text-slate-600 hover:border-slate-300 border-slate-200'"
                                class="px-3 py-1.5 text-xs rounded-lg border transition duration-150 flex items-center space-x-1 cursor-pointer">
                                <span x-text="preset + ' Artikel'"></span>
                            </button>
                        </template>
                    </div>

                    <!-- Dynamic Notification Banner -->
                    <div class="p-3.5 rounded-xl border flex items-start space-x-3 transition"
                        :class="articleCount > 1 ? 'bg-amber-50/80 border-amber-200 text-amber-900' : 'bg-slate-50 border-slate-200 text-slate-700'">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 mt-0.5"
                            :class="articleCount > 1 ? 'bg-amber-500 text-slate-950 font-black' : 'bg-slate-200 text-slate-600'">
                            <i data-lucide="info" class="w-4 h-4"></i>
                        </div>
                        <div class="text-xs leading-relaxed">
                            <template x-if="articleCount === 1">
                                <div>
                                    <span class="font-bold">Mode Tunggal:</span> Membuat 1 artikel berita/topik.
                                </div>
                            </template>
                            <template x-if="articleCount > 1">
                                <div>
                                    <span class="font-bold text-amber-950">Mode Multi-Artikel Aktif:</span>
                                    Sistem akan membuat <strong class="text-amber-800" x-text="articleCount + ' draf artikel'"></strong>. Di Langkah 2, judul tulisan akan otomatis disesuaikan dan diparafrase menjadi <strong class="text-amber-800" x-text="articleCount + ' variasi judul unik'"></strong>.
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Kategori & Judul Tulisan (dengan Auto-Parafrase Judul) -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-5">
                <div class="flex items-center space-x-3 border-b border-slate-100 pb-4">
                    <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 font-black text-xs flex items-center justify-center">2</span>
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Kategori & Judul Tulisan</h2>
                        <p class="text-xs text-slate-500">Pilih kategori penulisan berita, tentukan topik dasar, dan sesuaikan variasi judul</p>
                    </div>
                </div>

                <!-- Pemilihan Kategori Tulisan (Berita / Artikel Lainnya) -->
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider block">
                        Pilih Kategori Tulisan <span class="text-rose-500">*</span>
                    </label>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5">
                        @foreach($categories as $catKey => $catDesc)
                            <label class="flex flex-col p-3 rounded-xl border cursor-pointer transition select-none text-left"
                                :class="selectedCategory === '{{ $catKey }}' ? 'border-amber-500 bg-amber-50/40 ring-2 ring-amber-500/20 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-slate-50/40'">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-900 flex items-center space-x-1.5">
                                        @if($catKey === 'EVENT')
                                             <i data-lucide="calendar" class="w-3.5 h-3.5 text-amber-600"></i>
                                        @elseif($catKey === 'NEWS')
                                             <i data-lucide="newspaper" class="w-3.5 h-3.5 text-blue-600"></i>
                                        @elseif($catKey === 'OPINI')
                                             <i data-lucide="message-square" class="w-3.5 h-3.5 text-purple-600"></i>
                                        @else
                                             <i data-lucide="tag" class="w-3.5 h-3.5 text-slate-400"></i>
                                        @endif
                                        <span>{{ $catKey }}</span>
                                    </span>
                                    <input type="radio" name="category" value="{{ $catKey }}" x-model="selectedCategory" class="sr-only">
                                    <span class="w-3.5 h-3.5 rounded-full border border-slate-300 flex items-center justify-center"
                                        :class="selectedCategory === '{{ $catKey }}' ? 'border-amber-600 bg-amber-500' : ''">
                                        <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="selectedCategory === '{{ $catKey }}'"></span>
                                    </span>
                                </div>
                                <span class="text-[10px] text-slate-500 mt-1 line-clamp-1 leading-tight">{{ $catDesc }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Gaya Penulisan AI -->
                <div class="space-y-1.5 pt-1">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Gaya Penulisan AI <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        @foreach($writingStyles as $style => $desc)
                            <label class="flex items-start p-3 rounded-xl border cursor-pointer transition select-none"
                                :class="writingStyle === '{{ $style }}' ? 'border-amber-500 bg-amber-50/30 ring-1 ring-amber-500' : 'border-slate-200 hover:border-slate-300 bg-white'">
                                <input type="radio" name="writing_style" value="{{ $style }}" x-model="writingStyle" class="mt-0.5 text-amber-600 focus:ring-amber-500">
                                <div class="ml-3">
                                    <span class="block text-xs font-bold text-slate-800">{{ $style }}</span>
                                    <span class="block text-[11px] text-slate-500 mt-0.5 leading-tight">{{ $desc }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Input Judul & Penyesuaian Parafrase Otomatis -->
                <div class="space-y-3 pt-2 border-t border-slate-100">
                    <!-- Kasus A: Jika articleCount = 1 -->
                    <div x-show="articleCount <= 1" class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label for="title" class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Judul Berita / Artikel <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[11px] text-slate-400 font-mono" x-text="(mainTitle ? mainTitle.length : 0) + ' / 255 karakter'"></span>
                        </div>
                        <input type="text" id="title" name="title" maxlength="255"
                            :required="articleCount <= 1"
                            x-model="mainTitle"
                            @input="if (articleCount <= 1) { titleCount = mainTitle.length; initTitles(); }"
                            placeholder="Contoh: Semarak Dies Natalis UBSI Karawang: Ratusan Pelajar Ikuti Lomba Inovasi Digital 2026"
                            class="w-full px-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition font-medium">
                        @error('title')
                            <p class="text-xs text-rose-500 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kasus B: Jika articleCount > 1 (Auto-Parafrase Variasi Judul) -->
                    <div x-show="articleCount > 1" class="space-y-4 p-4 rounded-xl bg-amber-50/40 border border-amber-200/80">
                        <!-- Topik Dasar / Judul Utama -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label for="title_multi" class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center space-x-1.5">
                                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-600"></i>
                                    <span>Topik Dasar / Judul Utama <span class="text-rose-500">*</span></span>
                                </label>
                                <span class="text-[11px] text-amber-700 font-bold bg-amber-100 px-2 py-0.5 rounded-full" x-text="articleCount + ' Variasi Judul'"></span>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-2">
                                <input type="text" id="title_multi" name="title" maxlength="255"
                                    :required="articleCount > 1"
                                    x-model="mainTitle"
                                    @input="initTitles()"
                                    placeholder="Masukkan topik dasar atau ide judul di sini..."
                                    class="flex-1 px-4 py-2.5 text-sm bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition font-medium">

                                <button type="button" 
                                    @click="triggerParaphrase()" 
                                    :disabled="isParaphrasing || !mainTitle || mainTitle.trim() === ''"
                                    class="px-4 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 disabled:opacity-50 disabled:cursor-not-allowed text-slate-950 text-xs font-bold rounded-xl transition shadow-sm flex items-center justify-center space-x-2 shrink-0 cursor-pointer">
                                    <svg x-show="isParaphrasing" class="animate-spin -ml-1 mr-1 h-3.5 w-3.5 text-slate-900" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <i x-show="!isParaphrasing" data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                                    <span x-text="isParaphrasing ? 'Sedang Memparafrase...' : '✨ Auto-Parafrase ' + articleCount + ' Judul (AI)'"></span>
                                </button>
                            </div>
                            @error('title')
                                <p class="text-xs text-rose-500 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notifikasi Error jika gagal -->
                        <div x-show="paraphraseError" class="p-2.5 rounded-lg bg-rose-50 border border-rose-200 text-xs text-rose-700 flex items-center justify-between">
                            <span x-text="paraphraseError"></span>
                            <button type="button" @click="paraphraseError = ''" class="text-rose-500 hover:text-rose-800 font-bold">&times;</button>
                        </div>

                        <!-- Daftar Input Variasi Judul Hasil Parafrase -->
                        <div class="space-y-2 pt-2 border-t border-amber-200/60">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-800 flex items-center space-x-1.5">
                                    <i data-lucide="list-ordered" class="w-3.5 h-3.5 text-amber-600"></i>
                                    <span>Daftar Variasi Judul Artikel (Dapat Diedit Manual):</span>
                                </span>
                                <span class="text-[11px] text-amber-800 font-medium" x-text="articleCount + ' variasi'"></span>
                            </div>

                            <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                                <template x-for="(t, index) in paraphrasedTitles" :key="index">
                                    <div class="flex items-center space-x-2.5 p-2 bg-white rounded-xl border border-amber-200/80 shadow-2xs">
                                        <span class="w-7 h-7 rounded-lg bg-amber-500 text-slate-950 font-black text-xs flex items-center justify-center shrink-0" 
                                            x-text="'#' + (index + 1)"></span>
                                        <div class="flex-1">
                                            <input type="text" name="titles[]" 
                                                x-model="paraphrasedTitles[index]" 
                                                :placeholder="'Variasi Judul #' + (index + 1) + ' (otomatis diparafrase AI jika dibiarkan kosong)'"
                                                class="w-full text-xs font-semibold text-slate-800 bg-transparent border-0 focus:ring-0 p-1">
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <p class="text-[11px] text-amber-800/80 pt-1 leading-relaxed">
                                💡 <strong>Tips Otomatis:</strong> Klik tombol <em>Auto-Parafrase Judul</em> di atas untuk memparafrase secara instan dengan AI, atau biarkan kosong agar sistem secara otomatis membuat variasi judul saat artikel disimpan/digenerasi.
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Section 2: Form Khusus Liputan Event (Pra Event vs Pasca Event) -->
            <!-- Ditampilkan saat kategori EVENT dipilih -->
            <div class="bg-white rounded-2xl p-6 border-2 border-amber-300/80 shadow-md space-y-5" 
                x-show="isEvent()" 
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0">
                
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-amber-100 pb-4 gap-2">
                    <div class="flex items-center space-x-3">
                        <span class="w-7 h-7 rounded-lg bg-amber-500 text-white font-black text-xs flex items-center justify-center shadow-sm">2</span>
                        <div>
                            <h2 class="text-base font-bold text-slate-900 flex items-center space-x-2">
                                <span>Detail Liputan Kegiatan / Acara</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-300 uppercase">Modul Event</span>
                            </h2>
                            <p class="text-xs text-slate-500">Pilih tahapan event (Pra Event / Pasca Event) dan lengkapi rincian acara</p>
                        </div>
                    </div>

                    <!-- Hidden flag is_event -->
                    <input type="hidden" name="is_event" :value="(isEvent() || isNews()) ? '1' : '0'">
                </div>

                <!-- Sub-Kategori Berita: Pra Event vs Pasca Event -->
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center space-x-2">
                        <span>Pilihan Tahapan Berita Kegiatan</span>
                        <span class="text-rose-500">*</span>
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Pra Event -->
                        <label class="p-4 rounded-xl border-2 cursor-pointer transition select-none flex items-start"
                            :class="eventCategory === 'Pra Event' ? 'border-amber-500 bg-amber-50/50 ring-1 ring-amber-500 shadow-sm' : 'border-slate-200 hover:border-slate-300 bg-white'">
                            <input type="radio" name="event_category" value="Pra Event" :disabled="!isEvent()" x-model="eventCategory" class="mt-0.5 text-amber-600 focus:ring-amber-500">
                            <div class="ml-3">
                                <span class="block text-sm font-bold text-slate-900 flex items-center space-x-1.5">
                                    <i data-lucide="calendar-plus" class="w-4 h-4 text-amber-600"></i>
                                    <span>Pra Event (Sebelum Kegiatan)</span>
                                </span>
                                <span class="block text-xs text-slate-500 mt-1 leading-relaxed">
                                    Berita pemberitahuan / pengumuman bahwa kegiatan <strong>akan segera diselenggarakan</strong>. Menjelaskan maksud tema, ajakan hadir, dan info jadwal lengkap.
                                </span>
                            </div>
                        </label>

                        <!-- Pasca Event -->
                        <label class="p-4 rounded-xl border-2 cursor-pointer transition select-none flex items-start"
                            :class="eventCategory === 'Pasca Event' ? 'border-amber-500 bg-amber-50/50 ring-1 ring-amber-500 shadow-sm' : 'border-slate-200 hover:border-slate-300 bg-white'">
                            <input type="radio" name="event_category" value="Pasca Event" :disabled="!isEvent()" x-model="eventCategory" class="mt-0.5 text-amber-600 focus:ring-amber-500">
                            <div class="ml-3">
                                <span class="block text-sm font-bold text-slate-900 flex items-center space-x-1.5">
                                    <i data-lucide="check-check" class="w-4 h-4 text-emerald-600"></i>
                                    <span>Pasca Event (Setelah Kegiatan)</span>
                                </span>
                                <span class="block text-xs text-slate-500 mt-1 leading-relaxed">
                                    Berita laporan liputan bahwa kegiatan <strong>telah sukses diselenggarakan</strong>. Mengulas jalannya acara, pesan narasumber, dan antusiasme peserta.
                                </span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- 5 Isian Wajib / Utama Event: Nama, Tema, Hari & Tanggal, Waktu, Lokasi -->
                <div class="space-y-4 pt-2 border-t border-slate-100">
                    <p class="text-xs font-bold text-slate-700 uppercase tracking-wider">Informasi Pokok Kegiatan</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- 1. Nama Event -->
                        <div class="space-y-1">
                            <label for="event_name" class="text-xs font-bold text-slate-700 flex items-center space-x-1.5">
                                <i data-lucide="award" class="w-3.5 h-3.5 text-amber-500"></i>
                                <span>Nama Event / Kegiatan</span>
                            </label>
                            <input type="text" id="event_name" name="event_name" :disabled="!isEvent()" value="{{ old('event_name') }}"
                                placeholder="Contoh: Seminar Nasional Digital Creative UBSI 2026"
                                class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition font-medium">
                        </div>

                        <!-- 2. Tema Event -->
                        <div class="space-y-1">
                            <label for="event_theme" class="text-xs font-bold text-slate-700 flex items-center space-x-1.5">
                                <i data-lucide="target" class="w-3.5 h-3.5 text-indigo-500"></i>
                                <span>Tema Event</span>
                            </label>
                            <input type="text" id="event_theme" name="event_theme" :disabled="!isEvent()" value="{{ old('event_theme') }}"
                                placeholder="Contoh: Mengoptimalkan Potensi Kreatif Anak Bangsa di Era Kecerdasan Buatan"
                                class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition font-medium">
                        </div>

                        <!-- 3. Hari dan Tanggal Event -->
                        <div class="space-y-1">
                            <label for="event_date" class="text-xs font-bold text-slate-700 flex items-center space-x-1.5">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-sky-500"></i>
                                <span>Hari dan Tanggal Event</span>
                            </label>
                            <input type="text" id="event_date" name="event_date" :disabled="!isEvent()" value="{{ old('event_date') }}"
                                placeholder="Contoh: Sabtu, 24 Oktober 2026"
                                class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition font-medium">
                        </div>

                        <!-- 4. Waktu Event -->
                        <div class="space-y-1">
                            <label for="event_time" class="text-xs font-bold text-slate-700 flex items-center space-x-1.5">
                                <i data-lucide="clock" class="w-3.5 h-3.5 text-emerald-500"></i>
                                <span>Waktu Event</span>
                            </label>
                            <input type="text" id="event_time" name="event_time" :disabled="!isEvent()" value="{{ old('event_time') }}"
                                placeholder="Contoh: 08:30 s/d 12:30 WIB"
                                class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition font-medium">
                        </div>

                        <!-- 5. Lokasi Event -->
                        <div class="md:col-span-2 space-y-1">
                            <label for="event_location" class="text-xs font-bold text-slate-700 flex items-center space-x-1.5">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-rose-500"></i>
                                <span>Lokasi Event</span>
                            </label>
                            <input type="text" id="event_location" name="event_location" :disabled="!isEvent()" value="{{ old('event_location') }}"
                                placeholder="Contoh: Aula Gedung BSI Convention Hall / Gedung UBSI Kampus Karawang"
                                class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition font-medium">
                        </div>
                    </div>
                </div>

                <!-- Kolom Tambahan Berita: Opini Tokoh / Narasumber -->
                <div class="p-4 bg-purple-50/60 rounded-xl border border-purple-200 space-y-2">
                    <label for="opinion_author" class="text-xs font-bold text-purple-900 flex items-center justify-between">
                        <span class="flex items-center space-x-1.5">
                            <i data-lucide="user-check" class="w-3.5 h-3.5 text-purple-600"></i>
                            <span>Nama Opini / Penulis Sudut Pandang Tambahan (Opsional)</span>
                        </span>
                        <span class="text-[10px] text-purple-700 font-semibold bg-purple-100 px-2 py-0.5 rounded">Opini Berita</span>
                    </label>
                    <input type="text" id="opinion_author" name="opinion_author" :disabled="!isEvent()" value="{{ old('opinion_author') }}"
                        placeholder="Contoh: Dr. Ir. Mochamad Wahyudi, M.Kom, MM (Rektor) / Pengamat Pendidikan"
                        class="w-full px-3.5 py-2 text-xs bg-white border border-purple-200 rounded-lg focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition">
                    <p class="text-[11px] text-purple-700">
                        💡 AI akan menyisipkan ulasan opini dan kutipan perspektif berbobot atas nama tokoh ini di dalam tubuh berita.
                    </p>
                </div>

                <!-- Rincian Narasumber & Panitia Tambahan -->
                <div x-data="{ showExtra: false }" class="pt-1">
                    <button type="button" @click="showExtra = !showExtra" class="text-xs font-semibold text-slate-500 hover:text-slate-800 flex items-center space-x-1">
                        <span x-text="showExtra ? '− Sembunyikan Rincian Penyelenggara' : '+ Tambah Rincian Narasumber, MC, & Panitia (Opsional)'"></span>
                    </button>

                    <div x-show="showExtra" x-collapse class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-3">
                        <div class="space-y-1">
                            <label class="text-[11px] font-semibold text-slate-600">Penyelenggara</label>
                            <input type="text" name="event_organizer" :disabled="!isEvent()" placeholder="Contoh: BEM UBSI Karawang" class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[11px] font-semibold text-slate-600">Narasumber / Pembicara</label>
                            <input type="text" name="event_speaker" :disabled="!isEvent()" placeholder="Contoh: Dr. Budi Santoso" class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[11px] font-semibold text-slate-600">Pembawa Acara (MC)</label>
                            <input type="text" name="event_host" :disabled="!isEvent()" placeholder="Contoh: Sarah & Danu" class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg">
                        </div>
                    </div>
                </div>

            </div>

            <!-- Section 2 (NEWS): Form Khusus Liputan Berita Aktual -->
            <!-- Ditampilkan saat kategori NEWS dipilih: 1. Topik Berita, 2. Lokasi, 3. Waktu, 4. Tanggal, 5. Opini Narasumber -->
            <div class="bg-white rounded-2xl p-6 border-2 border-blue-300/80 shadow-md space-y-5" 
                x-show="isNews()" 
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0">
                
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-blue-100 pb-4 gap-2">
                    <div class="flex items-center space-x-3">
                        <span class="w-7 h-7 rounded-lg bg-blue-600 text-white font-black text-xs flex items-center justify-center shadow-sm">2</span>
                        <div>
                            <h2 class="text-base font-bold text-slate-900 flex items-center space-x-2">
                                <span>Detail Liputan Berita / Aktual</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-100 text-blue-800 border border-blue-300 uppercase">Modul News</span>
                            </h2>
                            <p class="text-xs text-slate-500">Lengkapi data pokok fakta berita terkini (Topik, Lokasi, Waktu, Tanggal, & Opini Narasumber)</p>
                        </div>
                    </div>
                </div>

                <!-- 4 Isian Pokok Berita: 1. Topik Berita, 2. Lokasi, 3. Waktu, 4. Tanggal -->
                <div class="space-y-4 pt-1">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- 1. Topik Berita -->
                        <div class="md:col-span-2 space-y-1">
                            <label for="news_theme" class="text-xs font-bold text-slate-700 flex items-center space-x-1.5">
                                <i data-lucide="newspaper" class="w-3.5 h-3.5 text-blue-600"></i>
                                <span>1. Topik Berita <span class="text-rose-500">*</span></span>
                            </label>
                            <input type="text" id="news_theme" name="event_theme" :disabled="!isNews()" value="{{ old('event_theme') }}"
                                placeholder="Contoh: Mahasiswa UBSI Karawang Kembangkan Solusi Teknologi Ramah Lingkungan untuk Karawang Hijau"
                                class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition font-medium">
                        </div>

                        <!-- 2. Lokasi -->
                        <div class="space-y-1">
                            <label for="news_location" class="text-xs font-bold text-slate-700 flex items-center space-x-1.5">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-rose-500"></i>
                                <span>2. Lokasi Kejadian / Berita</span>
                            </label>
                            <input type="text" id="news_location" name="event_location" :disabled="!isNews()" value="{{ old('event_location') }}"
                                placeholder="Contoh: Kampus UBSI Karawang / Karawang Barat"
                                class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition font-medium">
                        </div>

                        <!-- 3. Waktu -->
                        <div class="space-y-1">
                            <label for="news_time" class="text-xs font-bold text-slate-700 flex items-center space-x-1.5">
                                <i data-lucide="clock" class="w-3.5 h-3.5 text-emerald-500"></i>
                                <span>3. Waktu Kejadian</span>
                            </label>
                            <input type="text" id="news_time" name="event_time" :disabled="!isNews()" value="{{ old('event_time') }}"
                                placeholder="Contoh: Pukul 10:00 WIB s/d Selesai"
                                class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition font-medium">
                        </div>

                        <!-- 4. Tanggal -->
                        <div class="md:col-span-2 space-y-1">
                            <label for="news_date" class="text-xs font-bold text-slate-700 flex items-center space-x-1.5">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-sky-500"></i>
                                <span>4. Hari dan Tanggal</span>
                            </label>
                            <input type="text" id="news_date" name="event_date" :disabled="!isNews()" value="{{ old('event_date') }}"
                                placeholder="Contoh: Senin, 19 Oktober 2026"
                                class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition font-medium">
                        </div>
                    </div>
                </div>

                <!-- 5. Opini Narasumber -->
                <div class="p-4 bg-purple-50/60 rounded-xl border border-purple-200 space-y-2">
                    <label for="news_opinion" class="text-xs font-bold text-purple-900 flex items-center justify-between">
                        <span class="flex items-center space-x-1.5">
                            <i data-lucide="user-check" class="w-3.5 h-3.5 text-purple-600"></i>
                            <span>5. Opini / Pernyataan Narasumber</span>
                        </span>
                        <span class="text-[10px] text-purple-700 font-semibold bg-purple-100 px-2 py-0.5 rounded">Narasumber Berita</span>
                    </label>
                    <input type="text" id="news_opinion" name="opinion_author" :disabled="!isNews()" value="{{ old('opinion_author') }}"
                        placeholder="Contoh: Dr. Ir. Mochamad Wahyudi, M.Kom, MM (Rektor) / Pengamat Pendidikan"
                        class="w-full px-3.5 py-2 text-xs bg-white border border-purple-200 rounded-lg focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition">
                    <p class="text-[11px] text-purple-700">
                        💡 AI akan menyisipkan ulasan opini dan kutipan perspektif narasumber ini secara natural di dalam naskah berita.
                    </p>
                </div>

                <!-- 6. Upload Foto Dokumentasi Berita -->
                <div class="space-y-2 pt-2 border-t border-slate-100">
                    <label class="text-xs font-bold text-slate-700 flex items-center justify-between">
                        <span class="flex items-center space-x-1.5">
                            <i data-lucide="image" class="w-3.5 h-3.5 text-blue-600"></i>
                            <span>6. Upload Foto / Dokumentasi Berita</span>
                        </span>
                        <span class="text-[10px] font-bold text-blue-700 bg-blue-100 px-2 py-0.5 rounded">Semua Format Gambar</span>
                    </label>

                    <div class="relative border-2 border-dashed rounded-2xl p-6 text-center transition cursor-pointer"
                        :class="isDragging ? 'border-blue-500 bg-blue-50/40' : (imagePreview ? 'border-emerald-300 bg-emerald-50/20' : 'border-slate-300 hover:border-blue-400 bg-slate-50/60')"
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="isDragging = false; handleImageUpload($event)">
                        
                        <input type="file" id="news_image" name="event_image" 
                            :disabled="!isNews()"
                            accept="image/*,.jpg,.jpeg,.png,.webp,.gif,.svg,.bmp,.avif,.tiff" 
                            @change="handleImageUpload($event)"
                            class="event-image-input absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">

                        <div class="space-y-3 pointer-events-none">
                            <template x-if="!imagePreview">
                                <div>
                                    <div class="w-12 h-12 mx-auto rounded-xl bg-blue-100/70 text-blue-600 flex items-center justify-center mb-2">
                                        <i data-lucide="image-plus" class="w-6 h-6"></i>
                                    </div>
                                    <p class="text-sm font-bold text-slate-800">Tarik & Letakkan Foto di Sini, atau <span class="text-blue-600 underline">Pilih Berkas</span></p>
                                    <p class="text-xs text-slate-500 mt-1">
                                        Mendukung semua format gambar: <strong>JPG, JPEG, PNG, WEBP, GIF, SVG, BMP, AVIF, TIFF</strong> (Maks. 10 MB)
                                    </p>
                                </div>
                            </template>

                            <template x-if="imagePreview">
                                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pointer-events-auto">
                                    <div class="relative w-36 h-24 rounded-xl overflow-hidden border-2 border-emerald-500 shadow-md shrink-0 bg-slate-900">
                                        <img :src="imagePreview" alt="Preview Foto Berita" class="w-full h-full object-cover">
                                    </div>
                                    <div class="text-left space-y-1">
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                                <i data-lucide="check" class="w-3 h-3"></i>
                                                <span>Foto Siap Digunakan</span>
                                            </span>
                                        </div>
                                        <p class="text-xs font-bold text-slate-800 truncate max-w-xs" x-text="imageFileName"></p>
                                        <p class="text-[11px] text-slate-500" x-text="'Ukuran file: ' + imageFileSize"></p>
                                        <button type="button" @click="removeImage()" 
                                            class="inline-flex items-center space-x-1 text-xs font-semibold text-rose-600 hover:text-rose-800 transition pt-1">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            <span>Hapus / Ganti Foto</span>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Section 3: Upload Gambar Pendukung (Semua Format Gambar) -->
            <!-- Disembunyikan pada kategori NEWS karena sudah terintegrasi di Modul News -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4"
                x-show="!isNews()">
                <div class="flex items-center space-x-3 border-b border-slate-100 pb-4">
                    <span class="w-7 h-7 rounded-lg bg-sky-50 text-sky-600 font-black text-xs flex items-center justify-center">
                        <span x-text="isEvent() ? '3' : '2'"></span>
                    </span>
                    <div>
                        <h2 class="text-base font-bold text-slate-800 flex items-center space-x-2">
                            <span>Gambar Pendukung / Sampul Dokumen</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-700">Semua Format Gambar</span>
                        </h2>
                        <p class="text-xs text-slate-500">Unggah foto dokumentasi kegiatan atau ilustrasi artikel (JPG, PNG, WEBP, GIF, SVG, BMP, AVIF, TIFF)</p>
                    </div>
                </div>

                <!-- Custom Drag & Drop / File Input Box -->
                <div class="space-y-3">
                    <div class="relative border-2 border-dashed rounded-2xl p-6 text-center transition cursor-pointer"
                        :class="isDragging ? 'border-amber-500 bg-amber-50/40' : (imagePreview ? 'border-emerald-300 bg-emerald-50/20' : 'border-slate-300 hover:border-amber-400 bg-slate-50/60')"
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="isDragging = false; handleImageUpload($event)">
                        
                        <!-- File Input (Accepts ALL image extensions) -->
                        <input type="file" id="event_image" name="event_image" 
                            :disabled="isNews()"
                            accept="image/*,.jpg,.jpeg,.png,.webp,.gif,.svg,.bmp,.avif,.tiff" 
                            @change="handleImageUpload($event)"
                            class="event-image-input absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">

                        <div class="space-y-3 pointer-events-none">
                            <template x-if="!imagePreview">
                                <div>
                                    <div class="w-14 h-14 mx-auto rounded-2xl bg-amber-100/70 text-amber-600 flex items-center justify-center mb-2">
                                        <i data-lucide="image-plus" class="w-7 h-7"></i>
                                    </div>
                                    <p class="text-sm font-bold text-slate-800">Tarik & Letakkan Gambar di Sini, atau <span class="text-amber-600 underline">Pilih Berkas</span></p>
                                    <p class="text-xs text-slate-500 mt-1">
                                        Mendukung semua format gambar: <strong>JPG, JPEG, PNG, WEBP, GIF, SVG, BMP, AVIF</strong> (Maks. 10 MB)
                                    </p>
                                </div>
                            </template>

                            <!-- Live Image Preview State -->
                            <template x-if="imagePreview">
                                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pointer-events-auto">
                                    <div class="relative w-36 h-24 rounded-xl overflow-hidden border-2 border-emerald-500 shadow-md shrink-0 bg-slate-900">
                                        <img :src="imagePreview" alt="Preview Gambar Pendukung" class="w-full h-full object-cover">
                                    </div>
                                    <div class="text-left space-y-1">
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                                <i data-lucide="check" class="w-3 h-3"></i>
                                                <span>Gambar Siap Digunakan</span>
                                            </span>
                                        </div>
                                        <p class="text-xs font-bold text-slate-800 truncate max-w-xs" x-text="imageFileName"></p>
                                        <p class="text-[11px] text-slate-500" x-text="'Ukuran file: ' + imageFileSize"></p>
                                        <button type="button" @click="removeImage()" 
                                            class="inline-flex items-center space-x-1 text-xs font-semibold text-rose-600 hover:text-rose-800 transition pt-1">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            <span>Hapus / Ganti Gambar</span>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section: Target Kampus / Kanal Publikasi -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex items-center space-x-3 border-b border-slate-100 pb-4">
                    <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 font-black text-xs flex items-center justify-center">
                        <span x-text="isEvent() ? '5' : (isNews() ? '3' : '4')"></span>
                    </span>
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Target Kampus / Kanal Publikasi</h2>
                        <p class="text-xs text-slate-500">Pilih satu atau lebih target. Sistem membuat artikel tersendiri dengan CTA spesifik per kampus</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    @foreach($campuses as $campusName => $campusDesc)
                        <label class="relative flex items-start p-4 rounded-xl border border-slate-200 hover:border-amber-400 bg-slate-50/50 hover:bg-amber-50/20 cursor-pointer transition select-none group"
                            :class="selectedCampuses.includes('{{ $campusName }}') ? 'border-amber-500/80 bg-amber-50/30 ring-1 ring-amber-500/40' : ''">
                            <input type="checkbox" name="campus_contexts[]" value="{{ $campusName }}" 
                                x-model="selectedCampuses"
                                class="mt-0.5 rounded text-amber-600 focus:ring-amber-500 border-slate-300">
                            <div class="ml-3 min-w-0">
                                <span class="block text-xs font-bold text-slate-800 group-hover:text-amber-700 transition">{{ $campusName }}</span>
                                <span class="block text-[11px] text-slate-500 mt-1 leading-snug">{{ $campusDesc }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Section: Mode Eksekusi & Jadwal Rilis -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex items-center space-x-3 border-b border-slate-100 pb-4">
                    <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 font-black text-xs flex items-center justify-center">
                        <span x-text="isEvent() ? '6' : (isNews() ? '4' : '5')"></span>
                    </span>
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Metode Pembuatan & Jadwal Rilis</h2>
                        <p class="text-xs text-slate-500">Pilih apakah artikel dibuat langsung sekarang atau dimasukkan ke jadwal rilis</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Mode Langsung -->
                    <label class="p-4 rounded-xl border cursor-pointer transition select-none flex items-start"
                        :class="mode === 'instant' ? 'border-amber-500 bg-amber-50/30 ring-1 ring-amber-500' : 'border-slate-200 hover:border-slate-300'">
                        <input type="radio" name="generation_mode" value="instant" x-model="mode" class="mt-0.5 text-amber-600 focus:ring-amber-500">
                        <div class="ml-3">
                            <span class="block text-xs font-bold text-slate-800 flex items-center space-x-1.5">
                                <i data-lucide="zap" class="w-4 h-4 text-amber-500"></i>
                                <span>Buat Langsung (Instant Generate)</span>
                            </span>
                            <span class="block text-[11px] text-slate-500 mt-1 leading-snug">
                                Konten berita/artikel langsung digenerasi oleh AI dan dokumen Word (.docx) siap diunduh dalam hitungan detik.
                            </span>
                        </div>
                    </label>

                    <!-- Mode Terjadwal -->
                    <label class="p-4 rounded-xl border cursor-pointer transition select-none flex items-start"
                        :class="mode === 'scheduled' ? 'border-amber-500 bg-amber-50/30 ring-1 ring-amber-500' : 'border-slate-200 hover:border-slate-300'">
                        <input type="radio" name="generation_mode" value="scheduled" x-model="mode" class="mt-0.5 text-amber-600 focus:ring-amber-500">
                        <div class="ml-3">
                            <span class="block text-xs font-bold text-slate-800 flex items-center space-x-1.5">
                                <i data-lucide="clock" class="w-4 h-4 text-sky-500"></i>
                                <span>Jadwalkan Rilis (Scheduled)</span>
                            </span>
                            <span class="block text-[11px] text-slate-500 mt-1 leading-snug">
                                Artikel masuk ke antrean pending dan diproses otomatis oleh sistem saat tanggal & jam yang ditentukan tiba.
                            </span>
                        </div>
                    </label>
                </div>

                <!-- Datetime Picker & Interval (Muncul bila scheduled) -->
                <div class="pt-2 space-y-3" x-show="mode === 'scheduled'" x-transition>
                    <div>
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider block mb-1.5">
                            Waktu Rilis Jadwal Pertama <span class="text-rose-500">*</span>
                        </label>
                        <input type="datetime-local" name="scheduled_at" value="{{ now()->addHour()->format('Y-m-d\TH:i') }}"
                            class="px-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition font-medium">
                    </div>

                    <!-- Interval Antar Artikel untuk Mode Terjadwal bila articleCount > 1 -->
                    <template x-if="articleCount > 1">
                        <div class="pt-2 border-t border-slate-100">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider block mb-1.5">
                                Interval Jarak Waktu Antar Artikel
                            </label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                                <label class="p-2.5 rounded-xl border cursor-pointer transition text-center select-none"
                                    :class="scheduleInterval === 0 ? 'border-sky-500 bg-sky-50/50 ring-1 ring-sky-500 font-bold text-sky-800' : 'border-slate-200 hover:border-slate-300 bg-white text-slate-700'">
                                    <input type="radio" name="schedule_interval" value="0" x-model.number="scheduleInterval" class="sr-only">
                                    <span class="text-xs block">Sekaligus</span>
                                    <span class="text-[10px] text-slate-400 block mt-0.5">Waktu yang sama</span>
                                </label>
                                <label class="p-2.5 rounded-xl border cursor-pointer transition text-center select-none"
                                    :class="scheduleInterval === 15 ? 'border-sky-500 bg-sky-50/50 ring-1 ring-sky-500 font-bold text-sky-800' : 'border-slate-200 hover:border-slate-300 bg-white text-slate-700'">
                                    <input type="radio" name="schedule_interval" value="15" x-model.number="scheduleInterval" class="sr-only">
                                    <span class="text-xs block">Tiap 15 Menit</span>
                                    <span class="text-[10px] text-slate-400 block mt-0.5">+15 menit per draf</span>
                                </label>
                                <label class="p-2.5 rounded-xl border cursor-pointer transition text-center select-none"
                                    :class="scheduleInterval === 60 ? 'border-sky-500 bg-sky-50/50 ring-1 ring-sky-500 font-bold text-sky-800' : 'border-slate-200 hover:border-slate-300 bg-white text-slate-700'">
                                    <input type="radio" name="schedule_interval" value="60" x-model.number="scheduleInterval" class="sr-only">
                                    <span class="text-xs block">Tiap 1 Jam</span>
                                    <span class="text-[10px] text-slate-400 block mt-0.5">+1 jam per draf</span>
                                </label>
                                <label class="p-2.5 rounded-xl border cursor-pointer transition text-center select-none"
                                    :class="scheduleInterval === 1440 ? 'border-sky-500 bg-sky-50/50 ring-1 ring-sky-500 font-bold text-sky-800' : 'border-slate-200 hover:border-slate-300 bg-white text-slate-700'">
                                    <input type="radio" name="schedule_interval" value="1440" x-model.number="scheduleInterval" class="sr-only">
                                    <span class="text-xs block">Tiap 1 Hari</span>
                                    <span class="text-[10px] text-slate-400 block mt-0.5">+1 hari per draf</span>
                                </label>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Submit Button Bar -->
            <div class="flex items-center justify-between p-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm">
                <a href="{{ route('articles.index') }}" class="px-4 py-2.5 text-xs font-semibold text-slate-600 hover:text-slate-900 transition">
                    Batal
                </a>

                <button type="submit" 
                    class="inline-flex items-center space-x-2 px-6 py-3 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 font-bold rounded-xl shadow-lg shadow-amber-500/25 transition duration-150 transform hover:-translate-y-0.5 cursor-pointer">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                    <span x-text="mode === 'instant' 
                        ? (getTotalArticles() > 1 ? 'Generasi ' + getTotalArticles() + ' Artikel Sekarang (AI)' : 'Generasi Berita / Artikel Sekarang (AI)') 
                        : (getTotalArticles() > 1 ? 'Simpan ' + getTotalArticles() + ' Artikel ke Antrean Terjadwal' : 'Simpan ke Antrean Terjadwal')"></span>
                </button>
            </div>

        </form>

    </div>
</x-app-layout>
