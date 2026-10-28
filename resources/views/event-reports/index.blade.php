<x-app-layout title="Laporan Suport Kegiatan - Support Event Sekolah">
    <div x-data="eventReportsApp(@js($events->keyBy('id')))" class="space-y-6">

        <!-- Header & Action Bar -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-emerald-950 to-slate-900 border border-slate-800 p-6 md:p-8 shadow-xl text-white">
            <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -left-10 -top-10 w-72 h-72 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <div class="flex items-center space-x-2.5">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-400/20 text-emerald-300 border border-emerald-400/30">
                            <i data-lucide="clipboard-check" class="w-3.5 h-3.5 mr-1.5 text-emerald-400"></i> Laporan Kegiatan
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 mr-1.5 animate-pulse"></span> Data Otomatis Tersinkron
                        </span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-white flex items-center gap-2">
                        Laporan Suport Kegiatan Sekolah
                    </h1>
                    <p class="text-sm md:text-base text-slate-300 max-w-3xl leading-relaxed">
                        Data kegiatan otomatis tersinkron dari proposal yang telah disetujui. Petugas lapangan hanya perlu mengunggah dokumentasi foto kegiatan, realisasi peserta, dan keterangan hasil pelaksanaan.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <a href="{{ route('event-proposals.index') }}"
                        class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-slate-800/80 hover:bg-slate-700 text-slate-200 border border-slate-700 shadow-sm transition">
                        <i data-lucide="inbox" class="w-4 h-4 text-amber-400"></i>
                        <span>Data Proposal Masuk</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
            <a href="{{ route('event-reports.index') }}" 
                class="p-4 bg-white rounded-2xl border transition hover:shadow-md {{ !request('execution_status') ? 'border-brand-500 ring-2 ring-brand-500/20' : 'border-slate-200/90' }}">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Disetujui</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($stats['total_disetujui']) }}</p>
                <span class="text-[11px] text-slate-500 font-medium">Event Siap Support</span>
            </a>

            <a href="{{ route('event-reports.index', ['execution_status' => 'menunggu_pelaksanaan']) }}" 
                class="p-4 bg-white rounded-2xl border transition hover:shadow-md {{ request('execution_status') === 'menunggu_pelaksanaan' ? 'border-amber-500 ring-2 ring-amber-500/20' : 'border-slate-200/90' }}">
                <p class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Menunggu Acara</p>
                <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ number_format($stats['menunggu_pelaksanaan']) }}</p>
                <span class="text-[11px] text-amber-700 font-medium">Jadwal Mendatang</span>
            </a>

            <a href="{{ route('event-reports.index', ['execution_status' => 'sedang_berjalan']) }}" 
                class="p-4 bg-white rounded-2xl border transition hover:shadow-md {{ request('execution_status') === 'sedang_berjalan' ? 'border-blue-500 ring-2 ring-blue-500/20' : 'border-slate-200/90' }}">
                <p class="text-[11px] font-bold text-blue-600 uppercase tracking-wider">Sedang Berjalan</p>
                <p class="text-2xl font-extrabold text-blue-600 mt-1">{{ number_format($stats['sedang_berjalan']) }}</p>
                <span class="text-[11px] text-blue-700 font-medium">Dalam Pelaksanaan</span>
            </a>

            <a href="{{ route('event-reports.index', ['execution_status' => 'selesai']) }}" 
                class="p-4 bg-white rounded-2xl border transition hover:shadow-md {{ request('execution_status') === 'selesai' ? 'border-emerald-500 ring-2 ring-emerald-500/20' : 'border-slate-200/90' }}">
                <p class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Terdokumentasi</p>
                <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ number_format($stats['selesai']) }}</p>
                <span class="text-[11px] text-emerald-700 font-medium">Selesai & Ada Foto</span>
            </a>

            <div class="p-4 bg-gradient-to-br from-emerald-50 to-teal-50/50 rounded-2xl border border-emerald-200">
                <p class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Total Dana Support</p>
                <p class="text-xl font-black text-emerald-900 mt-1">Rp {{ number_format($stats['total_dana_disetujui'], 0, ',', '.') }}</p>
                <span class="text-[11px] text-emerald-600 font-medium">Komitmen Kampus</span>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-sm">
            <form method="GET" action="{{ route('event-reports.index') }}" class="flex flex-col md:flex-row items-center gap-3">
                <div class="relative flex-1 w-full">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama event, sekolah, no. referensi, atau PIC..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                </div>

                <div class="flex items-center gap-2 w-full md:w-auto">
                    <select name="execution_status" class="w-full md:w-48 px-3 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">Semua Status Pelaksanaan</option>
                        <option value="menunggu_pelaksanaan" {{ request('execution_status') === 'menunggu_pelaksanaan' ? 'selected' : '' }}>Menunggu Pelaksanaan</option>
                        <option value="sedang_berjalan" {{ request('execution_status') === 'sedang_berjalan' ? 'selected' : '' }}>Sedang Berjalan</option>
                        <option value="selesai" {{ request('execution_status') === 'selesai' ? 'selected' : '' }}>Selesai & Terdokumentasi</option>
                    </select>

                    <button type="submit" class="px-4 py-2.5 rounded-xl text-sm font-bold bg-brand-600 hover:bg-brand-500 text-white shadow-sm transition">
                        Filter
                    </button>

                    @if(request()->anyFilled(['q', 'execution_status']))
                        <a href="{{ route('event-reports.index') }}" class="px-3 py-2.5 rounded-xl text-sm font-medium bg-slate-100 hover:bg-slate-200 text-slate-600 transition" title="Reset Filter">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Event Reports Cards -->
        @if($events->isEmpty())
            <div class="bg-white rounded-3xl border border-slate-200/90 p-16 text-center text-slate-400 space-y-3">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto text-slate-400">
                    <i data-lucide="clipboard-x" class="w-8 h-8"></i>
                </div>
                <p class="text-base font-bold text-slate-700">Belum ada kegiatan yang disetujui untuk dilaporkan.</p>
                <p class="text-xs text-slate-400 max-w-sm mx-auto">
                    Data laporan ini bersumber langsung dari menu <strong>Data Proposal Masuk</strong> setelah proposal disetujui (ACC).
                </p>
                <div class="pt-2">
                    <a href="{{ route('event-proposals.index') }}" class="inline-flex items-center space-x-2 px-4 py-2 rounded-xl text-xs font-bold bg-brand-600 text-white hover:bg-brand-500 transition">
                        <i data-lucide="inbox" class="w-4 h-4"></i>
                        <span>Lihat Data Proposal Masuk</span>
                    </a>
                </div>
            </div>
        @else
            <div class="space-y-6">
                @foreach($events as $event)
                    @php
                        $docItems = $event->documentation_items ?? [];
                        $photos = array_filter($docItems, fn($i) => !empty($i['is_image']));
                        $archives = array_filter($docItems, fn($i) => empty($i['is_image']));
                        $waNumber = preg_replace('/[^0-9]/', '', $event->pic_phone);
                        if (str_starts_with($waNumber, '0')) {
                            $waNumber = '62' . substr($waNumber, 1);
                        }
                    @endphp

                    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden hover:shadow-md transition">
                        <!-- Top Banner Card -->
                        <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 bg-slate-50/50">
                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-block px-2.5 py-0.5 rounded text-xs font-mono font-bold bg-white text-slate-700 border border-slate-200 shadow-sm">
                                        {{ $event->reference_no }}
                                    </span>
                                    <span class="text-xs font-bold text-brand-600 bg-brand-50 px-2.5 py-0.5 rounded-full border border-brand-200">
                                        {{ $event->school_name }}
                                    </span>

                                    <!-- Status Pelaksanaan Badge -->
                                    @if($event->execution_status === 'menunggu_pelaksanaan')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-300">
                                            <i data-lucide="clock" class="w-3.5 h-3.5 mr-1 text-amber-500"></i> Menunggu Pelaksanaan
                                        </span>
                                    @elseif($event->execution_status === 'sedang_berjalan')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1.5 animate-pulse"></span> Sedang Berjalan
                                        </span>
                                    @elseif($event->execution_status === 'selesai')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-300">
                                            <i data-lucide="check-circle" class="w-3.5 h-3.5 mr-1 text-emerald-500"></i> Selesai & Terdokumentasi
                                        </span>
                                    @endif
                                </div>

                                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight pt-1">
                                    {{ $event->event_name }}
                                </h2>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 shrink-0 w-full md:w-auto justify-end">
                                <a href="{{ route('event-students.create', ['event_id' => $event->id]) }}" target="_blank"
                                    class="inline-flex items-center space-x-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-amber-400 hover:bg-amber-300 text-slate-950 shadow-sm transition hover:scale-105"
                                    title="Buka Form Pendaftaran Siswa untuk event ini">
                                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                    <span>Form Siswa</span>
                                </a>

                                <a href="{{ route('event-students.index', ['event_id' => $event->id]) }}"
                                    class="inline-flex items-center space-x-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 shadow-sm transition hover:scale-105"
                                    title="Lihat data siswa yang terjaring dari event ini">
                                    <i data-lucide="users" class="w-3.5 h-3.5 text-indigo-600"></i>
                                    <span>Data Siswa ({{ $event->student_leads_count ?? $event->studentLeads()->count() }})</span>
                                </a>

                                <button type="button" @click="openQrModal({{ $event->id }})"
                                    class="inline-flex items-center space-x-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-cyan-50 hover:bg-cyan-100 text-cyan-800 border border-cyan-200 shadow-sm transition hover:scale-105"
                                    title="Tampilkan QR Code khusus event ini untuk dipajang di booth">
                                    <i data-lucide="qr-code" class="w-3.5 h-3.5 text-cyan-600"></i>
                                    <span>QR Code</span>
                                </button>

                                <button type="button" @click="openReportModal({{ $event->id }})"
                                    class="inline-flex items-center space-x-2 px-3.5 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-md shadow-emerald-600/20 transition hover:scale-[1.02]">
                                    <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                                    <span>Upload Dokumentasi</span>
                                </button>
                            </div>
                        </div>

                        <!-- Card Body (Synchronized Data & Reporting) -->
                        <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Left: Event Information (Synchronized from Proposal) -->
                            <div class="space-y-4 text-xs sm:text-sm border-b lg:border-b-0 lg:border-r border-slate-100 lg:pr-6">
                                <h4 class="font-extrabold text-slate-900 uppercase tracking-wider text-xs flex items-center gap-1.5">
                                    <i data-lucide="info" class="w-4 h-4 text-brand-500"></i> Data Kegiatan Tersinkron
                                </h4>

                                <div class="space-y-2.5">
                                    <div class="flex justify-between py-1 border-b border-slate-100">
                                        <span class="text-slate-500">Jadwal Event:</span>
                                        <span class="font-bold text-slate-800 text-right">
                                            {{ $event->event_date ? \Carbon\Carbon::parse($event->event_date)->translatedFormat('d F Y') : '-' }}
                                            @if($event->event_end_date && $event->event_end_date != $event->event_date)
                                                - {{ \Carbon\Carbon::parse($event->event_end_date)->translatedFormat('d F Y') }}
                                            @endif
                                        </span>
                                    </div>

                                    <div class="flex justify-between py-1 border-b border-slate-100">
                                        <span class="text-slate-500">Waktu:</span>
                                        <span class="font-semibold text-slate-800 text-right">{{ $event->event_time }}</span>
                                    </div>

                                    <div class="flex justify-between py-1 border-b border-slate-100">
                                        <span class="text-slate-500">Lokasi:</span>
                                        <span class="font-semibold text-slate-800 text-right">{{ $event->event_location }}</span>
                                    </div>

                                    <div class="flex justify-between py-1 border-b border-slate-100">
                                        <span class="text-slate-500">Target Peserta:</span>
                                        <span class="font-semibold text-slate-800 text-right">{{ $event->target_participants }}</span>
                                    </div>

                                    <div class="flex justify-between py-1 border-b border-slate-100">
                                        <span class="text-slate-500">PIC & Kontak:</span>
                                        <span class="font-semibold text-slate-800 text-right">
                                            {{ $event->pic_name }} ({{ $event->pic_position ?: 'Panitia' }})
                                        </span>
                                    </div>

                                    <!-- Support Disetujui -->
                                    <div class="pt-2 p-3 bg-emerald-50/70 rounded-xl border border-emerald-200 space-y-1">
                                        <div class="flex justify-between items-center">
                                            <span class="text-xs text-emerald-800 font-bold">Dana Disetujui:</span>
                                            <span class="text-sm font-black text-emerald-900">
                                                Rp {{ number_format($event->approved_amount ?: 0, 0, ',', '.') }}
                                            </span>
                                        </div>
                                        @if($event->approved_support_items)
                                            <div class="text-[11px] text-emerald-700">
                                                Fasilitas: <strong>{{ $event->approved_support_items }}</strong>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Middle & Right: Documentation Photos & Petugas Report -->
                            <div class="lg:col-span-2 space-y-5">
                                <!-- Realization Highlights -->
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Realisasi Peserta</p>
                                        <p class="text-xl font-extrabold text-slate-900 mt-0.5">
                                            {{ $event->realized_participants ? number_format($event->realized_participants) . ' Orang' : '-' }}
                                        </p>
                                    </div>

                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Prospek Siswa Didapat</p>
                                        <p class="text-xl font-extrabold text-emerald-600 mt-0.5">
                                            @php
                                                $leadCount = $event->student_leads_count ?? $event->studentLeads()->count();
                                                $displayCount = $leadCount > 0 ? $leadCount : ($event->prospects_acquired ?: 0);
                                            @endphp
                                            {{ $displayCount ? number_format($displayCount) . ' Lead' : '-' }}
                                        </p>
                                    </div>

                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100 col-span-2 sm:col-span-1">
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pelapor Petugas</p>
                                        <p class="text-xs font-bold text-slate-800 mt-1 truncate">
                                            {{ $event->reporter ? $event->reporter->name : 'Belum dilaporkan' }}
                                        </p>
                                        @if($event->reported_at)
                                            <span class="text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($event->reported_at)->translatedFormat('d M Y, H:i') }}</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Keterangan Hasil Pelaksanaan -->
                                <div>
                                    <h4 class="font-bold text-xs uppercase tracking-wider text-slate-400 mb-1.5">
                                        Keterangan & Catatan Kegiatan:
                                    </h4>
                                    @if($event->report_description)
                                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 text-xs sm:text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                                            {{ $event->report_description }}
                                        </div>
                                    @else
                                        <div class="p-4 rounded-2xl bg-slate-50 border border-dashed border-slate-200 text-xs text-slate-400 text-center">
                                            Petugas belum mengisi catatan laporan kegiatan. Klik tombol "Upload Dokumentasi & Keterangan" untuk melengkapi.
                                        </div>
                                    @endif
                                </div>

                                <!-- Galeri & Berkas Dokumentasi (Foto & File RAR/ZIP) -->
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-bold text-xs uppercase tracking-wider text-slate-400">
                                            Dokumentasi & Berkas Kegiatan ({{ count($docItems) }})
                                        </h4>
                                        @if(count($docItems) > 0)
                                            <span class="text-[11px] text-emerald-600 font-semibold flex items-center gap-1">
                                                <i data-lucide="check" class="w-3.5 h-3.5"></i> Tersimpan di Server
                                            </span>
                                        @endif
                                    </div>

                                    @if(empty($docItems))
                                        <div class="p-6 rounded-2xl border-2 border-dashed border-slate-200 text-center space-y-1">
                                            <i data-lucide="archive" class="w-8 h-8 text-slate-300 mx-auto"></i>
                                            <p class="text-xs font-semibold text-slate-500">Belum ada dokumentasi atau berkas RAR yang diunggah.</p>
                                            <p class="text-[11px] text-slate-400">Unggah foto kegiatan langsung atau berkas RAR / ZIP paket dokumentasi.</p>
                                        </div>
                                    @else
                                        <!-- File RAR / ZIP / Arsip / Dokumen -->
                                        @if(count($archives) > 0)
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                                @foreach($archives as $doc)
                                                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-3 shadow-sm hover:border-brand-300 transition">
                                                        <div class="flex items-center space-x-3 overflow-hidden">
                                                            <div class="w-10 h-10 rounded-xl {{ $doc['is_archive'] ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }} flex items-center justify-center shrink-0">
                                                                @if($doc['is_archive'])
                                                                    <i data-lucide="archive" class="w-5 h-5"></i>
                                                                @else
                                                                    <i data-lucide="file-text" class="w-5 h-5"></i>
                                                                @endif
                                                            </div>
                                                            <div class="overflow-hidden">
                                                                <p class="text-xs font-bold text-slate-900 truncate" title="{{ $doc['name'] }}">
                                                                    {{ $doc['name'] }}
                                                                </p>
                                                                <p class="text-[11px] text-slate-500">
                                                                    <span class="uppercase font-semibold text-slate-600">{{ $doc['extension'] }}</span>
                                                                    @if($doc['size_formatted']) • {{ $doc['size_formatted'] }} @endif
                                                                </p>
                                                            </div>
                                                        </div>

                                                        <a href="{{ $doc['url'] }}" download="{{ $doc['name'] }}"
                                                            class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-white text-slate-700 hover:text-brand-600 border border-slate-200 shadow-sm transition shrink-0">
                                                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                                            <span>Unduh</span>
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        <!-- Foto Dokumentasi -->
                                        @if(count($photos) > 0)
                                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-1">
                                                @foreach($photos as $photo)
                                                    <div class="group relative rounded-2xl overflow-hidden border border-slate-200 bg-slate-100 aspect-video shadow-sm">
                                                        <img src="{{ $photo['url'] }}" alt="Dokumentasi Event" class="w-full h-full object-cover group-hover:scale-105 transition duration-200">
                                                        <div class="absolute inset-0 bg-slate-950/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2">
                                                            <button type="button" @click="previewImage('{{ $photo['url'] }}')"
                                                                class="p-2 rounded-xl bg-white text-slate-900 shadow-md hover:bg-slate-100">
                                                                <i data-lucide="maximize-2" class="w-4 h-4"></i>
                                                            </button>
                                                            <a href="{{ $photo['url'] }}" download="{{ $photo['name'] }}"
                                                                class="p-2 rounded-xl bg-white text-slate-900 shadow-md hover:bg-slate-100" title="Unduh Foto">
                                                                <i data-lucide="download" class="w-4 h-4"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="p-4">
                    {{ $events->links() }}
                </div>
            </div>
        @endif

        <!-- Modal Upload Dokumentasi & Keterangan Kegiatan -->
        <div x-show="showReportModal" style="display: none;"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4">
            <div @click.away="showReportModal = false"
                class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-100 text-slate-800 space-y-5 animate-in fade-in zoom-in duration-200">
                
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <span class="text-[11px] font-mono font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded" x-text="activeEvent?.reference_no"></span>
                        <h3 class="text-lg font-extrabold text-slate-900 mt-1" x-text="'Laporan: ' + activeEvent?.event_name"></h3>
                    </div>
                    <button type="button" @click="showReportModal = false" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form :action="'/event-reports/' + activeEvent?.id + '/documentation'" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Status Pelaksanaan Kegiatan <span class="text-rose-500">*</span>
                        </label>
                        <select name="execution_status" x-model="modalExecutionStatus" required
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="menunggu_pelaksanaan">🟡 Menunggu Pelaksanaan (Jadwal Mendatang)</option>
                            <option value="sedang_berjalan">🔵 Sedang Berjalan (Sedang Dilaksanakan)</option>
                            <option value="selesai">🟢 Selesai & Terdokumentasi</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Realisasi Jumlah Peserta
                            </label>
                            <input type="number" name="realized_participants" x-model="modalRealizedParticipants" min="0" placeholder="Contoh: 450"
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Prospek Siswa Didapat
                            </label>
                            <input type="number" name="prospects_acquired" x-model="modalProspectsAcquired" min="0" placeholder="Contoh: 35"
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Keterangan & Catatan Hasil Kegiatan
                        </label>
                        <textarea name="report_description" x-model="modalDescription" rows="3" placeholder="Tuliskan jalannya kegiatan, respon pihak sekolah, kendala atau peluang kerjasama lanjutan..."
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
                    </div>

                    <!-- Upload Berkas Dokumentasi Baru (Foto / Berkas RAR / ZIP / PDF) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                            <span>Upload Dokumentasi / Berkas RAR</span>
                            <span class="text-[11px] font-semibold text-purple-700 bg-purple-50 px-2 py-0.5 rounded border border-purple-200">Support .RAR, .ZIP, Foto</span>
                        </label>
                        <div class="border-2 border-dashed border-slate-300 hover:border-emerald-500 rounded-2xl p-4 text-center cursor-pointer transition">
                            <input type="file" name="photos[]" multiple accept="image/*,.rar,.zip,.7z,.pdf,.doc,.docx"
                                class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                            <p class="text-[11px] text-slate-500 mt-1.5">
                                Dapat upload foto langsung (JPG, PNG) atau paket berkas arsip <strong>.RAR / .ZIP</strong> (sangat praktis untuk kumpulan banyak foto kegiatan) hingga 50MB per file.
                            </p>
                        </div>
                    </div>

                    <!-- Berkas yang sudah ada (bisa dihapus) -->
                    <template x-if="activeEvent?.documentation_items && activeEvent.documentation_items.length > 0">
                        <div class="pt-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Berkas Dokumentasi Tersimpan (Centang untuk menghapus):
                            </label>
                            <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                                <template x-for="(item, index) in activeEvent.documentation_items" :key="index">
                                    <label class="flex items-center justify-between p-2.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-rose-50/50 cursor-pointer group transition">
                                        <div class="flex items-center space-x-2.5 overflow-hidden">
                                            <template x-if="item.is_image">
                                                <img :src="item.url" class="w-9 h-9 object-cover rounded-lg border border-slate-200 shrink-0">
                                            </template>
                                            <template x-if="!item.is_image">
                                                <div class="w-9 h-9 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center shrink-0">
                                                    <i data-lucide="archive" class="w-4 h-4"></i>
                                                </div>
                                            </template>
                                            <div class="overflow-hidden">
                                                <span class="text-xs font-semibold text-slate-800 group-hover:text-rose-700 truncate block" x-text="item.name"></span>
                                                <span class="text-[10px] text-slate-400" x-text="item.size_formatted || (item.extension.toUpperCase())"></span>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-1.5 shrink-0 pl-2">
                                            <span class="text-[10px] text-rose-500 font-semibold group-hover:inline hidden">Hapus</span>
                                            <input type="checkbox" name="delete_photos[]" :value="item.path" class="w-4 h-4 text-rose-600 rounded border-slate-300">
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                        <button type="button" @click="showReportModal = false"
                            class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-md shadow-emerald-600/20 transition">
                            Simpan Dokumentasi & Laporan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lightbox Preview Image Modal -->
        <div x-show="previewModalOpen" style="display: none;"
            class="fixed inset-0 z-50 bg-slate-950/90 backdrop-blur-md flex items-center justify-center p-4">
            <div @click.away="previewModalOpen = false" class="relative max-w-4xl w-full text-center">
                <button type="button" @click="previewModalOpen = false"
                    class="absolute -top-12 right-0 p-2 text-white hover:text-amber-400">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
                <img :src="previewImageUrl" class="max-h-[85vh] mx-auto rounded-2xl shadow-2xl border border-slate-800 object-contain">
            </div>
        </div>

        <!-- Modal QR Code Khusus Event Ini -->
        <div x-show="showQrModal" style="display: none;"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4">
            <div @click.away="showQrModal = false"
                class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-100 text-slate-800 space-y-5 animate-in fade-in zoom-in duration-200">
                
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center">
                            <i data-lucide="qr-code" class="w-4 h-4"></i>
                        </div>
                        <h3 class="text-base font-extrabold text-slate-900">QR Code Booth Event</h3>
                    </div>
                    <button type="button" @click="showQrModal = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="space-y-1">
                    <p class="text-xs font-extrabold text-slate-900" x-text="qrTitle"></p>
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        Pajang QR Code ini di meja / stand booth. Siswa yang scan akan langsung diarahkan ke form pendaftaran dengan nama event yang sudah otomatis terpilih.
                    </p>
                </div>

                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 text-center shadow-inner">
                    <div class="inline-block p-3 bg-white rounded-2xl shadow-md border border-slate-200">
                        <img :src="qrImgUrl" alt="QR Code Event" class="w-48 h-48 mx-auto rounded-xl">
                    </div>
                    <p class="text-[11px] font-mono font-bold text-cyan-700 mt-2.5 truncate px-2" x-text="qrUrl"></p>
                </div>

                <div class="space-y-2 pt-1" x-data="{ copiedLink: false }">
                    <div class="grid grid-cols-2 gap-2">
                        <a :href="'/qr-codes/download/event-students?event_id=' + (activeEvent?.id || '')"
                            class="py-2.5 px-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-1.5">
                            <i data-lucide="download" class="w-4 h-4"></i>
                            <span>Unduh PNG HD</span>
                        </a>
                        <button type="button" @click="navigator.clipboard.writeText(qrUrl); copiedLink = true; setTimeout(() => copiedLink = false, 2000)"
                            class="py-2.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-xl transition flex items-center justify-center space-x-1.5">
                            <i data-lucide="copy" class="w-4 h-4"></i>
                            <span x-text="copiedLink ? '✓ Disalin!' : 'Salin URL'"></span>
                        </button>
                    </div>

                    <a :href="qrUrl" target="_blank"
                        class="w-full py-2 bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-600 font-semibold text-xs rounded-xl transition flex items-center justify-center space-x-1.5">
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                        <span>Buka Halaman Form di Tab Baru</span>
                    </a>
                </div>
            </div>
        </div>

    </div>

    <script>
        function eventReportsApp(eventsMap = {}) {
            return {
                eventsMap: eventsMap || {},
                showReportModal: false,
                showQrModal: false,
                activeEvent: null,
                qrUrl: '',
                qrImgUrl: '',
                qrTitle: '',
                modalExecutionStatus: 'menunggu_pelaksanaan',
                modalRealizedParticipants: '',
                modalProspectsAcquired: '',
                modalDescription: '',
                previewModalOpen: false,
                previewImageUrl: '',

                openQrModal(id) {
                    const event = this.eventsMap[id];
                    if (!event) return;
                    this.activeEvent = event;
                    this.qrTitle = event.event_name + ' - ' + event.school_name;
                    this.qrUrl = window.location.origin + '/hasil-support-event?event_id=' + event.id;
                    this.qrImgUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=350x350&margin=15&data=' + encodeURIComponent(this.qrUrl);
                    this.showQrModal = true;
                },

                openReportModal(id) {
                    const event = this.eventsMap[id];
                    if (!event) return;
                    this.activeEvent = event;
                    this.modalExecutionStatus = event.execution_status || 'menunggu_pelaksanaan';
                    this.modalRealizedParticipants = event.realized_participants || '';
                    this.modalProspectsAcquired = event.prospects_acquired || '';
                    this.modalDescription = event.report_description || '';
                    this.showReportModal = true;
                },

                previewImage(url) {
                    this.previewImageUrl = url;
                    this.previewModalOpen = true;
                }
            }
        }
    </script>
</x-app-layout>
