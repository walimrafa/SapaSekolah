<x-app-layout title="E-Brosur PMB UBSI">
    <div x-data="eBrochureApp()" class="space-y-6">

        <!-- Header & Action Bar -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-brand-950 to-slate-900 border border-slate-800 p-6 md:p-8 shadow-xl text-white">
            <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -left-10 -top-10 w-72 h-72 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <div class="flex items-center space-x-2.5">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-400/20 text-emerald-300 border border-emerald-400/30">
                            <i data-lucide="file-text" class="w-3.5 h-3.5 mr-1.5 text-emerald-400"></i> Repositori E-Brosur
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mr-1.5 animate-pulse"></span> Siap Unduh & Bagikan
                        </span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-white flex items-center gap-2">
                        E-Brosur & Materi Promosi PMB
                    </h1>
                    <p class="text-sm md:text-base text-slate-300 max-w-2xl leading-relaxed">
                        Pusat repositori brosur digital, rincian biaya kuliah per kampus, flyer beasiswa, dan materi sosialisasi resmi UBSI untuk di-view dan di-download oleh tim marketing & customer service.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <button type="button" @click="openUploadModal = true"
                        class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl text-sm font-bold bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 text-slate-950 shadow-lg shadow-emerald-500/20 transition-all duration-150 hover:scale-[1.02]">
                        <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                        <span>Upload E-Brosur</span>
                    </button>

                    <a href="{{ route('pmb.index') }}"
                        class="inline-flex items-center space-x-2 px-3.5 py-2.5 rounded-xl text-sm font-semibold bg-slate-800/80 hover:bg-slate-700 text-slate-200 border border-slate-700 shadow-sm transition">
                        <i data-lucide="layout" class="w-4 h-4 text-amber-400"></i>
                        <span>Portal Info PMB</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-4 bg-white rounded-xl border border-slate-200/90 shadow-sm flex items-center space-x-3.5">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <i data-lucide="files" class="w-5 h-5"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total E-Brosur</p>
                    <p class="text-xl font-extrabold text-slate-900 mt-0.5">{{ number_format($stats['total']) }}</p>
                </div>
            </div>

            <div class="p-4 bg-white rounded-xl border border-slate-200/90 shadow-sm flex items-center space-x-3.5">
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <i data-lucide="download" class="w-5 h-5"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Diunduh</p>
                    <p class="text-xl font-extrabold text-slate-900 mt-0.5">{{ number_format($stats['downloads']) }} <span class="text-xs font-normal text-slate-500">kali</span></p>
                </div>
            </div>

            <div class="p-4 bg-white rounded-xl border border-slate-200/90 shadow-sm flex items-center space-x-3.5">
                <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Brosur PDF</p>
                    <p class="text-xl font-extrabold text-slate-900 mt-0.5">{{ number_format($stats['pdf_count']) }}</p>
                </div>
            </div>

            <div class="p-4 bg-white rounded-xl border border-slate-200/90 shadow-sm flex items-center space-x-3.5">
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <i data-lucide="image" class="w-5 h-5"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Flyer Gambar</p>
                    <p class="text-xl font-extrabold text-slate-900 mt-0.5">{{ number_format($stats['image_count']) }}</p>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="p-4 bg-white rounded-xl border border-slate-200/90 shadow-sm">
            <form method="GET" action="{{ route('e-brochures.index') }}" class="flex flex-col md:flex-row gap-3">
                <!-- Search Input -->
                <div class="flex-1 relative">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari judul brosur, deskripsi, atau nama file..."
                        class="w-full pl-10 pr-4 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                </div>

                <!-- Category Filter -->
                <div class="w-full md:w-56">
                    <select name="category" onchange="this.form.submit()"
                        class="w-full py-2 px-3 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:border-brand-500 bg-white">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Type Filter -->
                <div class="w-full md:w-44">
                    <select name="type" onchange="this.form.submit()"
                        class="w-full py-2 px-3 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:border-brand-500 bg-white">
                        <option value="">Semua Format</option>
                        <option value="pdf" {{ request('type') == 'pdf' ? 'selected' : '' }}>Dokumen PDF</option>
                        <option value="image" {{ request('type') == 'image' ? 'selected' : '' }}>Gambar / Flyer</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                        Filter
                    </button>
                    @if(request()->hasAny(['q', 'category', 'type', 'year']))
                        <a href="{{ route('e-brochures.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-lg transition" title="Reset Filter">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- E-Brochure Cards Grid -->
        @if($brochures->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @foreach($brochures as $brochure)
                    <div class="bg-white rounded-xl border border-slate-200/90 shadow-sm hover:shadow-md transition duration-200 flex flex-col justify-between overflow-hidden group">
                        
                        <!-- Top Thumbnail & Preview Banner -->
                        <div class="relative bg-slate-100 h-44 overflow-hidden flex items-center justify-center border-b border-slate-100">
                            @if($brochure->is_image)
                                <img src="{{ $brochure->file_url }}" alt="{{ $brochure->title }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            @else
                                <div class="flex flex-col items-center justify-center p-6 text-center space-y-2">
                                    <div class="w-14 h-14 rounded-2xl bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-600 shadow-sm group-hover:scale-110 transition duration-200">
                                        <i data-lucide="file-text" class="w-7 h-7"></i>
                                    </div>
                                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider font-mono">DOKUMEN PDF</span>
                                </div>
                            @endif

                            <!-- Format Badge -->
                            <div class="absolute top-2.5 left-2.5 flex items-center space-x-1.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider {{ $brochure->is_pdf ? 'bg-rose-600 text-white' : 'bg-amber-500 text-slate-950' }} shadow-sm">
                                    {{ strtoupper($brochure->file_extension) }}
                                </span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-900/80 text-white backdrop-blur-sm shadow-sm">
                                    {{ $brochure->formatted_size }}
                                </span>
                            </div>

                            <!-- Year Badge -->
                            <div class="absolute top-2.5 right-2.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-white/90 text-slate-800 shadow-sm">
                                    {{ $brochure->academic_year }}
                                </span>
                            </div>

                            <!-- Hover Overlay for Instant Preview -->
                            <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center gap-2">
                                <button type="button"
                                    @click="previewBrochure('{{ route('e-brochures.show', $brochure) }}', '{{ addslashes($brochure->title) }}', '{{ $brochure->file_type }}', '{{ route('e-brochures.download', $brochure) }}')"
                                    class="p-2.5 rounded-xl bg-white text-slate-900 hover:bg-brand-50 hover:text-brand-600 shadow-lg font-bold text-xs flex items-center space-x-1.5 transition">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                    <span>Lihat Brosur</span>
                                </button>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-4 space-y-3 flex-1 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                    <span class="inline-block px-2 py-0.5 text-[10px] font-bold rounded bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        {{ $brochure->category }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-medium flex items-center">
                                        <i data-lucide="download" class="w-3 h-3 mr-1 text-slate-400"></i>
                                        {{ $brochure->download_count }}x
                                    </span>
                                </div>

                                <h3 class="font-bold text-sm text-slate-900 line-clamp-2 leading-snug group-hover:text-brand-600 transition" title="{{ $brochure->title }}">
                                    {{ $brochure->title }}
                                </h3>

                                @if($brochure->description)
                                    <p class="text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                                        {{ $brochure->description }}
                                    </p>
                                @endif
                            </div>

                            <!-- File Details & Upload Info -->
                            <div class="pt-2 border-t border-slate-100 text-[11px] text-slate-400 flex items-center justify-between">
                                <span class="truncate max-w-[150px]" title="{{ $brochure->file_name }}">
                                    {{ $brochure->file_name }}
                                </span>
                                <span>{{ $brochure->created_at->format('d M Y') }}</span>
                            </div>
                        </div>

                        <!-- Card Action Buttons -->
                        <div class="p-3 bg-slate-50 border-t border-slate-100 flex items-center gap-2">
                            <!-- View Button -->
                            <button type="button"
                                @click="previewBrochure('{{ route('e-brochures.show', $brochure) }}', '{{ addslashes($brochure->title) }}', '{{ $brochure->file_type }}', '{{ route('e-brochures.download', $brochure) }}')"
                                class="flex-1 py-1.5 px-2.5 rounded-lg text-xs font-semibold bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 shadow-sm flex items-center justify-center space-x-1 transition">
                                <i data-lucide="eye" class="w-3.5 h-3.5 text-blue-500"></i>
                                <span>Lihat</span>
                            </button>

                            <!-- Download Button -->
                            <a href="{{ route('e-brochures.download', $brochure) }}"
                                class="flex-1 py-1.5 px-2.5 rounded-lg text-xs font-bold bg-brand-600 hover:bg-brand-700 text-white shadow-sm flex items-center justify-center space-x-1 transition">
                                <i data-lucide="download" class="w-3.5 h-3.5 text-white"></i>
                                <span>Unduh</span>
                            </a>

                            <!-- Copy WhatsApp Share Link -->
                            <button type="button"
                                @click="copyShareLink('{{ route('e-brochures.show', $brochure) }}', '{{ addslashes($brochure->title) }}')"
                                title="Salin Teks Promosi WhatsApp"
                                class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 border border-slate-200 bg-white transition">
                                <i data-lucide="share-2" class="w-3.5 h-3.5"></i>
                            </button>

                            <!-- Delete Button (Admin, Pimpinan, or Marketing) -->
                            @if(auth()->user()->isAdmin() || auth()->user()->hasRole('Pimpinan') || auth()->id() === $brochure->created_by)
                                <form method="POST" action="{{ route('e-brochures.destroy', $brochure) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus e-brosur ini?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus E-Brosur"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 border border-slate-200 bg-white transition">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            @endif
                        </div>

                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $brochures->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="p-12 text-center bg-white rounded-2xl border border-slate-200/90 shadow-sm space-y-4">
                <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto shadow-sm">
                    <i data-lucide="folder-plus" class="w-8 h-8"></i>
                </div>
                <div class="max-w-md mx-auto space-y-1">
                    <h3 class="font-extrabold text-slate-900 text-lg">Belum Ada E-Brosur</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Materi brosur digital belum diunggah atau tidak ditemukan dengan filter yang dipilih. Silakan unggah brosur PMB pertama Anda sekarang.
                    </p>
                </div>
                <button type="button" @click="openUploadModal = true"
                    class="inline-flex items-center space-x-2 px-4 py-2 rounded-xl text-xs font-bold bg-brand-600 hover:bg-brand-700 text-white shadow-md transition">
                    <i data-lucide="upload" class="w-4 h-4"></i>
                    <span>Upload E-Brosur Pertama</span>
                </button>
            </div>
        @endif

        <!-- MODAL: Upload E-Brosur -->
        <div x-show="openUploadModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4"
            style="display: none;">
            
            <div @click.away="openUploadModal = false"
                class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-xl overflow-hidden animate-scale-up">
                
                <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <div class="p-2 rounded-lg bg-emerald-500/20 text-emerald-400">
                            <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-white">Upload E-Brosur PMB</h3>
                            <p class="text-[11px] text-slate-400">Unggah brosur resmi format PDF atau gambar (JPG/PNG)</p>
                        </div>
                    </div>
                    <button type="button" @click="openUploadModal = false" class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('e-brochures.store') }}" enctype="multipart/form-data" class="p-6 space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Judul E-Brosur <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" required placeholder="Contoh: Brosur PMB UBSI 2026/2027 Kampus Karawang"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Brosur <span class="text-rose-500">*</span></label>
                            <select name="category" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:border-brand-500 bg-white">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Akademik</label>
                            <input type="text" name="academic_year" value="2026/2027" placeholder="2026/2027"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi / Keterangan Singkat</label>
                        <textarea name="description" rows="2" placeholder="Informasi singkat mengenai isi brosur (opsional)..."
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
                    </div>

                    <!-- File Upload Input -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">File E-Brosur (PDF / Gambar) <span class="text-rose-500">*</span></label>
                        <div class="border-2 border-dashed border-slate-300 hover:border-emerald-500 rounded-xl p-5 text-center transition bg-slate-50/50">
                            <i data-lucide="file-up" class="w-8 h-8 text-slate-400 mx-auto mb-2"></i>
                            <input type="file" name="brochure_file" required accept=".pdf,.jpg,.jpeg,.png,.webp"
                                class="text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                            <p class="text-[10px] text-slate-400 mt-2">Mendukung format: PDF, JPG, PNG, WEBP (Maksimal 50 MB)</p>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-3">
                        <button type="button" @click="openUploadModal = false"
                            class="px-4 py-2 rounded-lg border border-slate-300 text-slate-600 text-xs font-semibold hover:bg-slate-50 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md transition flex items-center space-x-1.5">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Simpan & Upload</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: Fullscreen Interactive Viewer (View E-Brochure) -->
        <div x-show="openViewerModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex flex-col p-2 sm:p-4"
            style="display: none;">
            
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl flex-1 flex flex-col overflow-hidden max-w-6xl w-full mx-auto">
                <!-- Viewer Header -->
                <div class="px-5 py-3 bg-slate-900 text-white flex items-center justify-between gap-3 shrink-0">
                    <div class="flex items-center space-x-3 min-w-0">
                        <div class="p-2 rounded-lg bg-emerald-500/20 text-emerald-400 shrink-0">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-bold text-sm text-white truncate" x-text="previewTitle">Lihat E-Brosur</h3>
                            <p class="text-[10px] text-slate-400">Pratinjau Resolusi Tinggi</p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2 shrink-0">
                        <!-- Direct Download Button -->
                        <a :href="previewDownloadUrl"
                            class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-sm transition flex items-center space-x-1">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                            <span class="hidden sm:inline">Unduh File</span>
                        </a>

                        <!-- Open in New Tab -->
                        <a :href="previewUrl" target="_blank" rel="noopener noreferrer"
                            class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition" title="Buka di Tab Baru">
                            <i data-lucide="external-link" class="w-4 h-4"></i>
                        </a>

                        <!-- Close Modal -->
                        <button type="button" @click="closeViewer()"
                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition" title="Tutup Viewer">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                <!-- Viewer Body -->
                <div class="flex-1 bg-slate-100 relative overflow-auto flex items-center justify-center p-2 sm:p-4">
                    <!-- PDF Viewer -->
                    <template x-if="previewType === 'pdf'">
                        <iframe :src="previewUrl" class="w-full h-full min-h-[600px] rounded-lg border border-slate-300 bg-white" title="PDF Viewer"></iframe>
                    </template>

                    <!-- Image Viewer -->
                    <template x-if="previewType === 'image'">
                        <div class="max-h-full max-w-full overflow-auto flex items-center justify-center">
                            <img :src="previewUrl" :alt="previewTitle" class="max-h-[80vh] max-w-full object-contain rounded-lg shadow-lg border border-slate-200">
                        </div>
                    </template>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function eBrochureApp() {
            return {
                openUploadModal: false,
                openViewerModal: false,
                previewUrl: '',
                previewTitle: '',
                previewType: 'pdf',
                previewDownloadUrl: '',

                previewBrochure(url, title, type, downloadUrl) {
                    this.previewUrl = url;
                    this.previewTitle = title;
                    this.previewType = type;
                    this.previewDownloadUrl = downloadUrl;
                    this.openViewerModal = true;
                },

                closeViewer() {
                    this.openViewerModal = false;
                    this.previewUrl = '';
                },

                copyShareLink(url, title) {
                    const text = `Halo! Berikut adalah e-brosur resmi ${title} dari Universitas Bina Sarana Informatika (UBSI). Silakan lihat dan unduh pada tautan berikut:\n${url}\n\nInfo PMB lebih lanjut: https://pmbubsi.id/pmb\nKuliah...? BSI Aja!`;
                    navigator.clipboard.writeText(text).then(() => {
                        alert('Teks promosi dan tautan E-Brosur berhasil disalin! Siap dikirim ke calon mahasiswa / sekolah via WhatsApp.');
                    });
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
