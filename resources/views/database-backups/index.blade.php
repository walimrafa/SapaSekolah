<x-app-layout title="Cadangan Database & Google Drive">
    <div class="space-y-6" x-data="{ 
        createModal: false, 
        settingsModal: false, 
        testModal: false,
        testTarget: 'karawang',
        testLoading: false,
        testResult: null,
        async runTest(target) {
            this.testTarget = target;
            this.testLoading = true;
            this.testResult = null;
            try {
                let res = await fetch('{{ route('database-backups.test-connection') }}?target=' + target, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                let data = await res.json();
                this.testResult = data;
            } catch (e) {
                this.testResult = { success: false, message: 'Gagal menghubungi server: ' + e.message };
            } finally {
                this.testLoading = false;
            }
        }
    }">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800 text-slate-300 text-xs font-semibold mb-2 border border-slate-700">
                    <i data-lucide="database" class="w-3.5 h-3.5 text-blue-400"></i>
                    <span>Penyimpanan Aman & Pemulihan Bencana</span>
                </div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Cadangan Database & Google Drive</h1>
                <p class="text-xs text-slate-500 mt-1">
                    Cadangan data terisolasi kampus: Format Excel (.xlsx multi-sheet) dan SQL Dump (.sql.gz) berkala mingguan serta sinkronisasi cloud Google Drive terpisah per kampus.
                </p>
            </div>

            <!-- Tombol Aksi Utama -->
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Tombol Sinkronisasi Semua Berkas Tertunda -->
                <form method="POST" action="{{ route('database-backups.sync-all') }}" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-blue-50 border border-blue-200 text-blue-700 text-xs font-bold hover:bg-blue-100 transition shadow-xs">
                        <i data-lucide="cloud-upload" class="w-4 h-4 text-blue-600"></i>
                        <span>Unggah Semua ke GDrive</span>
                    </button>
                </form>

                @if(auth()->user()->isAdmin())
                <button type="button" @click="settingsModal = true" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-50 transition shadow-xs">
                    <i data-lucide="settings" class="w-4 h-4 text-slate-500"></i>
                    <span>Kredensial GDrive</span>
                </button>
                @endif

                <button type="button" @click="createModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition shadow-sm shadow-brand-600/30">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Buat Cadangan Sekarang</span>
                </button>
            </div>
        </div>

        <!-- Alert Notifikasi Flash -->
        @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start gap-3 text-xs">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 shrink-0"></i>
            <div>
                <p class="font-bold">Sukses!</p>
                <p class="mt-0.5 text-emerald-700">{{ session('success') }}</p>
            </div>
        </div>
        @endif

        @if(session('warning'))
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 flex items-start gap-3 text-xs">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 shrink-0"></i>
            <div>
                <p class="font-bold">Perhatian</p>
                <p class="mt-0.5 text-amber-800">{{ session('warning') }}</p>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 flex items-start gap-3 text-xs">
            <i data-lucide="x-circle" class="w-5 h-5 text-red-600 shrink-0"></i>
            <div>
                <p class="font-bold">Kesalahan Terjadi</p>
                <p class="mt-0.5 text-red-700">{{ session('error') }}</p>
            </div>
        </div>
        @endif

        @php
            $pendingCount = $backups->filter(fn($b) => $b->gdrive_status !== 'uploaded' && $b->status === 'success')->count();
        @endphp
        @if($pendingCount > 0)
        <div class="p-4 rounded-2xl bg-amber-50/90 border border-amber-300 text-amber-950 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs shadow-xs">
            <div class="flex items-start sm:items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-200/80 flex items-center justify-center text-amber-800 shrink-0">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </div>
                <div>
                    <p class="font-extrabold text-amber-950">Ada {{ $pendingCount }} berkas cadangan lokal berstatus "Menunggu Upload".</p>
                    <p class="text-[11px] text-amber-800 mt-0.5">Setelah URL Webhook GDrive disimpan, klik tombol di samping untuk otomatis mengirim seluruh berkas ke folder Google Drive masing-masing.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('database-backups.sync-all') }}" class="shrink-0">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-xs transition shadow-xs flex items-center gap-1.5">
                    <i data-lucide="cloud-upload" class="w-4 h-4"></i>
                    <span>Unggah Semua Sekarang ({{ $pendingCount }})</span>
                </button>
            </form>
        </div>
        @endif

        <!-- Banner Target Folder Google Drive Per Kampus -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Kartu Folder Cikampek -->
            <div class="bg-gradient-to-br from-emerald-50 to-teal-50/50 p-5 rounded-3xl border border-emerald-200/80 shadow-soft relative overflow-hidden">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-200/80 text-emerald-900 text-[10px] font-extrabold uppercase tracking-wider mb-2">
                            <i data-lucide="folder" class="w-3 h-3 text-emerald-700"></i>
                            <span>Google Drive UBSI Cikampek</span>
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900">Folder Cadangan UBSI Cikampek</h3>
                        <p class="text-[11px] text-slate-600 mt-1 font-mono break-all">ID: {{ $cikampekFolderId }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-2xl bg-emerald-600/10 flex items-center justify-center shrink-0">
                        <i data-lucide="cloud-upload" class="w-5 h-5 text-emerald-600"></i>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-emerald-200/60 flex items-center justify-between text-xs">
                    <button type="button" @click="testModal = true; runTest('cikampek')" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-800 hover:text-emerald-950 underline decoration-emerald-400">
                        <i data-lucide="activity" class="w-3.5 h-3.5"></i>
                        <span>Uji Koneksi Folder</span>
                    </button>
                    <a href="{{ $cikampekFolderUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] transition shadow-xs">
                        <span>Buka Google Drive</span>
                        <i data-lucide="external-link" class="w-3 h-3"></i>
                    </a>
                </div>
            </div>

            <!-- Kartu Folder Karawang -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50/50 p-5 rounded-3xl border border-blue-200/80 shadow-soft relative overflow-hidden">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-blue-200/80 text-blue-900 text-[10px] font-extrabold uppercase tracking-wider mb-2">
                            <i data-lucide="folder" class="w-3 h-3 text-blue-700"></i>
                            <span>Google Drive UBSI Karawang</span>
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900">Folder Cadangan UBSI Karawang</h3>
                        <p class="text-[11px] text-slate-600 mt-1 font-mono break-all">ID: {{ $karawangFolderId }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-2xl bg-blue-600/10 flex items-center justify-center shrink-0">
                        <i data-lucide="cloud-upload" class="w-5 h-5 text-blue-600"></i>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-blue-200/60 flex items-center justify-between text-xs">
                    <button type="button" @click="testModal = true; runTest('karawang')" class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-800 hover:text-blue-950 underline decoration-blue-400">
                        <i data-lucide="activity" class="w-3.5 h-3.5"></i>
                        <span>Uji Koneksi Folder</span>
                    </button>
                    <a href="{{ $karawangFolderUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-[11px] transition shadow-xs">
                        <span>Buka Google Drive</span>
                        <i data-lucide="external-link" class="w-3 h-3"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- 4 Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-soft">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Cadangan</p>
                    <div class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center">
                        <i data-lucide="archive" class="w-4 h-4 text-slate-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-black text-slate-900 mt-2">{{ number_format($totalBackups) }}</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Berkas Excel & SQL tersimpan</p>
            </div>

            <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-soft">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Kapasitas Disk Terpakai</p>
                    <div class="w-8 h-8 rounded-xl bg-blue-100/60 flex items-center justify-center">
                        <i data-lucide="hard-drive" class="w-4 h-4 text-blue-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-black text-slate-900 mt-2">
                    @php
                        if ($totalBytes >= 1073741824) {
                            $fmtBytes = round($totalBytes / 1073741824, 2) . ' GB';
                        } elseif ($totalBytes >= 1048576) {
                            $fmtBytes = round($totalBytes / 1048576, 2) . ' MB';
                        } elseif ($totalBytes >= 1024) {
                            $fmtBytes = round($totalBytes / 1024, 2) . ' KB';
                        } else {
                            $fmtBytes = $totalBytes . ' B';
                        }
                    @endphp
                    {{ $fmtBytes }}
                </p>
                <p class="text-[11px] text-slate-500 mt-0.5">Kompresi Gzip tingkat 9</p>
            </div>

            <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-soft">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Tersinkron GDrive</p>
                    <div class="w-8 h-8 rounded-xl bg-emerald-100/60 flex items-center justify-center">
                        <i data-lucide="cloud-check" class="w-4 h-4 text-emerald-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-black text-emerald-600 mt-2">{{ number_format($totalUploadedGdrive) }}</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Berkas di cloud Google</p>
            </div>

            <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-soft">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider">Jadwal Otomatis</p>
                    <div class="w-8 h-8 rounded-xl bg-indigo-100/60 flex items-center justify-center">
                        <i data-lucide="clock" class="w-4 h-4 text-indigo-600"></i>
                    </div>
                </div>
                <p class="text-sm font-black text-slate-900 mt-2 flex items-center gap-1.5">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Setiap Minggu</span>
                </p>
                <p class="text-[11px] text-slate-500 mt-0.5">Pukul 02:00 WIB • Cron Schedule</p>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-soft">
            <form method="GET" action="{{ route('database-backups.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                @if($canSwitchCampus)
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Filter Kampus</label>
                    <select name="campus_id" class="w-full text-xs rounded-xl border-slate-200 focus:border-brand-500 focus:ring-brand-500 py-2">
                        <option value="">Semua Kampus</option>
                        @foreach($campuses as $campus)
                            <option value="{{ $campus->id }}" {{ (string)$selectedCampusId === (string)$campus->id ? 'selected' : '' }}>
                                {{ $campus->name }} ({{ $campus->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Format Berkas</label>
                    <select name="format" class="w-full text-xs rounded-xl border-slate-200 focus:border-brand-500 focus:ring-brand-500 py-2">
                        <option value="">Semua Format</option>
                        <option value="excel" {{ request('format') === 'excel' ? 'selected' : '' }}>Excel (.xlsx)</option>
                        <option value="sql" {{ request('format') === 'sql' ? 'selected' : '' }}>SQL Dump (.sql.gz)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Status Lokal</label>
                    <select name="status" class="w-full text-xs rounded-xl border-slate-200 focus:border-brand-500 focus:ring-brand-500 py-2">
                        <option value="">Semua Status Lokal</option>
                        <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Sukses</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Gagal</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Status Google Drive</label>
                    <select name="gdrive_status" class="w-full text-xs rounded-xl border-slate-200 focus:border-brand-500 focus:ring-brand-500 py-2">
                        <option value="">Semua Status Cloud</option>
                        <option value="uploaded" {{ request('gdrive_status') === 'uploaded' ? 'selected' : '' }}>Terunggah (Uploaded)</option>
                        <option value="pending" {{ request('gdrive_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="failed" {{ request('gdrive_status') === 'failed' ? 'selected' : '' }}>Gagal Upload</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition">
                        Terapkan Filter
                    </button>
                    @if(request()->hasAny(['campus_id', 'format', 'status', 'gdrive_status']))
                    <a href="{{ route('database-backups.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs transition">
                        Reset
                    </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Tabel Riwayat Cadangan Database -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900">Riwayat Berkas Cadangan (Excel & SQL Dump)</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar file cadangan database dan lembar kerja Excel terisolasi kampus serta link unduh langsung.</p>
                </div>
                <span class="text-xs font-bold text-slate-400">Total: {{ $backups->total() }} File</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200/80">
                            <th class="py-3 px-4">Waktu Dibuat</th>
                            <th class="py-3 px-4">Kampus</th>
                            <th class="py-3 px-4">Format</th>
                            <th class="py-3 px-4">Nama File</th>
                            <th class="py-3 px-4">Ukuran</th>
                            <th class="py-3 px-4">Tipe</th>
                            <th class="py-3 px-4">Status Lokal</th>
                            <th class="py-3 px-4">Status Google Drive</th>
                            <th class="py-3 px-4">Dibuat Oleh</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($backups as $item)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="font-bold text-slate-800">{{ $item->created_at->format('d M Y') }}</span>
                                <span class="block text-[11px] text-slate-400 font-mono">{{ $item->created_at->format('H:i:s') }} WIB</span>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($item->campus)
                                    @if(str_contains(strtolower($item->campus->code), 'ckp') || str_contains(strtolower($item->campus->name), 'cikampek'))
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                                            <span>UBSI Cikampek</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-blue-100 text-blue-800 border border-blue-300">
                                            <span>UBSI Karawang</span>
                                        </span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">
                                        Pusat / Seluruhnya
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                {!! $item->format_badge !!}
                            </td>
                            <td class="py-3 px-4 font-mono text-[11px] text-slate-700">
                                {{ $item->filename }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap font-bold text-slate-800">
                                {{ $item->formatted_size }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($item->type === 'scheduled')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 border border-purple-200 text-[10px] font-bold">
                                        <i data-lucide="clock" class="w-3 h-3"></i>
                                        <span>Mingguan</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 border border-slate-200 text-[10px] font-bold">
                                        <i data-lucide="user" class="w-3 h-3"></i>
                                        <span>Manual</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if(is_string($item->status_badge))
                                    {!! $item->status_badge !!}
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-700">
                                        {{ $item->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    @if(is_string($item->gdrive_status_badge))
                                        {!! $item->gdrive_status_badge !!}
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-700">
                                            {{ $item->gdrive_status }}
                                        </span>
                                    @endif
                                    @if($item->gdrive_file_url)
                                        <a href="{{ $item->gdrive_file_url }}" target="_blank" rel="noopener noreferrer" title="Buka di Google Drive" class="p-1 rounded text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition">
                                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                        </a>
                                    @endif
                                </div>
                                @if($item->error_message && $item->gdrive_status === 'failed')
                                    <p class="text-[10px] text-red-500 mt-0.5 truncate max-w-xs" title="{{ $item->error_message }}">
                                        {{ $item->error_message }}
                                    </p>
                                @endif
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap text-[11px] text-slate-600">
                                {{ $item->creator ? $item->creator->name : 'Sistem Otomatis' }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap text-center">
                                <div class="inline-flex items-center gap-1">
                                    <!-- Unduh File Lokal -->
                                    @if($item->existsLocally())
                                    <a href="{{ route('database-backups.download', $item) }}" title="Unduh Berkas .sql.gz" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                                        <i data-lucide="download" class="w-4 h-4"></i>
                                    </a>
                                    @endif

                                    <!-- Unggah / Retry ke GDrive -->
                                    @if($item->existsLocally())
                                    <form method="POST" action="{{ route('database-backups.upload-gdrive', $item) }}" class="inline">
                                        @csrf
                                        <button type="submit" title="Sinkronisasikan ke Google Drive" class="p-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 transition">
                                            <i data-lucide="cloud-upload" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                    @endif

                                    <!-- Hapus -->
                                    <form method="POST" action="{{ route('database-backups.destroy', $item) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus berkas cadangan ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Berkas" class="p-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-700 transition">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                        <i data-lucide="database" class="w-6 h-6"></i>
                                    </div>
                                    <p class="font-bold text-slate-600">Belum ada riwayat cadangan database</p>
                                    <p class="text-xs text-slate-400">Klik tombol "Buat Cadangan Sekarang" untuk membuat dump pertama.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($backups->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $backups->links() }}
            </div>
            @endif
        </div>

        <!-- MODAL: Buat Cadangan Manual -->
        <div x-show="createModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="createModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-brand-50 flex items-center justify-center text-brand-600">
                            <i data-lucide="database" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900">Buat Cadangan Data</h3>
                            <p class="text-xs text-slate-400">Pilih format Excel (.xlsx) atau SQL Dump (.sql.gz)</p>
                        </div>
                    </div>
                    <button type="button" @click="createModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('database-backups.create') }}" class="mt-5 space-y-4">
                    @csrf

                    <!-- Pilihan Format Cadangan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Format Cadangan</label>
                        <div class="space-y-2">
                            <label class="flex items-start gap-3 p-3 rounded-xl border border-brand-200 bg-brand-50/30 cursor-pointer hover:bg-brand-50/50 transition">
                                <input type="radio" name="format" value="both" checked class="mt-0.5 text-brand-600 focus:ring-brand-500">
                                <div>
                                    <span class="text-xs font-bold text-slate-900 block flex items-center gap-1.5">
                                        <span>Keduanya (Excel & SQL Dump)</span>
                                        <span class="px-2 py-0.2 rounded-full text-[9px] font-black bg-brand-100 text-brand-800 uppercase tracking-wider">Direkomendasikan</span>
                                    </span>
                                    <span class="text-[11px] text-slate-500 block mt-0.5">Membuat berkas Excel lengkap (.xlsx) untuk analisis & berkas SQL (.sql.gz) untuk pemulihan teknis.</span>
                                </div>
                            </label>

                            <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 bg-white cursor-pointer hover:bg-slate-50 transition">
                                <input type="radio" name="format" value="excel" class="mt-0.5 text-brand-600 focus:ring-brand-500">
                                <div>
                                    <span class="text-xs font-bold text-slate-900 block">Hanya Berkas Excel (.xlsx Multi-Sheet)</span>
                                    <span class="text-[11px] text-slate-500 block mt-0.5">Memuat 11 sheet lengkap: Sekolah, Kunjungan, Hasil Kuisioner, Follow Up, Prospek, PMB, Event, dll.</span>
                                </div>
                            </label>

                            <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 bg-white cursor-pointer hover:bg-slate-50 transition">
                                <input type="radio" name="format" value="sql" class="mt-0.5 text-brand-600 focus:ring-brand-500">
                                <div>
                                    <span class="text-xs font-bold text-slate-900 block">Hanya Berkas SQL Dump (.sql.gz)</span>
                                    <span class="text-[11px] text-slate-500 block mt-0.5">Dump skema dan data database terisolasi kampus terkompresi Gzip.</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    @if($canSwitchCampus)
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Kampus Target Cadangan</label>
                        <select name="campus_choice" class="w-full text-xs rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 py-2.5">
                            <option value="ALL">Semua Kampus (UBSI Karawang & UBSI Cikampek)</option>
                            @foreach($campuses as $campus)
                                <option value="{{ $campus->id }}">{{ $campus->name }} ({{ $campus->code }})</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Berkas akan dipisahkan dan diunggah ke folder Google Drive masing-masing kampus.</p>
                    </div>
                    @else
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                        <p class="font-bold text-slate-800">Kampus Terikat:</p>
                        <p class="text-slate-600">{{ auth()->user()->campus?->name ?? 'Kampus Cabang Anda' }}</p>
                    </div>
                    @endif

                    <div class="p-3.5 rounded-xl bg-blue-50/60 border border-blue-200/80">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" name="upload_to_gdrive" value="1" checked class="mt-0.5 rounded text-brand-600 focus:ring-brand-500">
                            <div>
                                <span class="text-xs font-bold text-slate-900 block">Unggah Otomatis ke Google Drive</span>
                                <span class="text-[11px] text-slate-500 block mt-0.5">
                                    Berkas akan langsung disinkronisasikan ke folder Google Drive resmi masing-masing kampus.
                                </span>
                            </div>
                        </label>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                        <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-brand-600 hover:bg-brand-700 text-white transition shadow-sm shadow-brand-600/30 flex items-center gap-1.5">
                            <i data-lucide="play" class="w-3.5 h-3.5"></i>
                            <span>Proses Cadangan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: Uji Koneksi Google Drive -->
        <div x-show="testModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="testModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                            <i data-lucide="activity" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900">Uji Koneksi Google Drive</h3>
                            <p class="text-xs text-slate-400" x-text="testTarget === 'cikampek' ? 'UBSI Cikampek' : 'UBSI Karawang'"></p>
                        </div>
                    </div>
                    <button type="button" @click="testModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="mt-5 space-y-4">
                    <div x-show="testLoading" class="py-8 text-center space-y-3">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-brand-500 border-t-transparent"></div>
                        <p class="text-xs font-bold text-slate-600">Menghubungi Google Drive API v3...</p>
                    </div>

                    <div x-show="!testLoading && testResult">
                        <template x-if="testResult && testResult.success">
                            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs space-y-2">
                                <div class="flex items-center gap-2 font-bold text-emerald-900">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                                    <span>Koneksi Berhasil!</span>
                                </div>
                                <p x-text="testResult.message"></p>
                                <div class="pt-2 border-t border-emerald-200 text-[11px]">
                                    <p><strong>Nama Folder:</strong> <span x-text="testResult.folder_name"></span></p>
                                    <p><strong>ID:</strong> <span class="font-mono text-[10px]" x-text="testResult.folder_id"></span></p>
                                </div>
                            </div>
                        </template>

                        <template x-if="testResult && !testResult.success">
                            <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs space-y-2">
                                <div class="flex items-center gap-2 font-bold text-red-900">
                                    <i data-lucide="x-circle" class="w-4 h-4 text-red-600"></i>
                                    <span>Koneksi Gagal!</span>
                                </div>
                                <p x-text="testResult.message"></p>
                                <p class="text-[11px] text-red-700">Pastikan folder Google Drive telah di-share izin Editor ke Service Account / email terotentikasi.</p>
                            </div>
                        </template>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        <div class="flex gap-2">
                            <button type="button" @click="runTest('cikampek')" class="px-2.5 py-1.5 rounded-lg border text-[11px] font-bold text-emerald-700 bg-emerald-50 border-emerald-200">
                                Cek Cikampek
                            </button>
                            <button type="button" @click="runTest('karawang')" class="px-2.5 py-1.5 rounded-lg border text-[11px] font-bold text-blue-700 bg-blue-50 border-blue-200">
                                Cek Karawang
                            </button>
                        </div>
                        <button type="button" @click="testModal = false" class="px-4 py-1.5 rounded-xl text-xs font-bold bg-slate-900 text-white">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL: Pengaturan Kredensial Google Drive (Admin Only) -->
        @if(auth()->user()->isAdmin())
        <div x-show="settingsModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="settingsModal = false" class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-slate-200 max-h-[90vh] flex flex-col">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-slate-700">
                            <i data-lucide="sliders" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900">Pengaturan Kredensial & Integrasi Google Drive</h3>
                            <p class="text-xs text-slate-400">Konfigurasi Folder & Google Service Account untuk sinkronisasi otomatis</p>
                        </div>
                    </div>
                    <button type="button" @click="settingsModal = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="overflow-y-auto flex-1 pr-1 space-y-4 my-4">
                    <!-- Penjelasan & Panduan Langkah demi Langkah -->
                    <div x-data="{ openGuide: true }" class="rounded-2xl border border-blue-200 bg-blue-50/60 p-4">
                        <div class="flex items-center justify-between cursor-pointer" @click="openGuide = !openGuide">
                            <div class="flex items-center gap-2">
                                <i data-lucide="help-circle" class="w-4 h-4 text-blue-600"></i>
                                <h4 class="text-xs font-black text-blue-950">Panduan: Apa itu Google Service Account & Cara Mendapatkannya (Gratis 3 Menit)</h4>
                            </div>
                            <button type="button" class="text-blue-600 hover:text-blue-800 text-xs font-bold flex items-center gap-1">
                                <span x-text="openGuide ? 'Tutup Panduan' : 'Lihat Panduan'"></span>
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5 transition-transform" :class="{ 'rotate-180': openGuide }"></i>
                            </button>
                        </div>

                        <div x-show="openGuide" class="mt-3 pt-3 border-t border-blue-200/60 text-xs text-blue-900 space-y-2">
                            <p class="leading-relaxed">
                                <strong>Apa itu Service Account?</strong> Service Account adalah <em>akun robot khusus</em> dari Google Cloud yang bertindak atas nama server SAPA Sekolah untuk mengunggah file cadangan database secara berkala ke Google Drive tanpa perlu login browser manual.
                            </p>
                            <div class="bg-white/80 p-3 rounded-xl border border-blue-200/80 space-y-2 text-[11px]">
                                <p class="font-extrabold text-blue-950 uppercase tracking-wider text-[10px]">5 Langkah Mudah Mendapatkan JSON Service Account:</p>
                                <ol class="list-decimal list-inside space-y-1.5 text-slate-700">
                                    <li>
                                        Buka <a href="https://console.cloud.google.com/" target="_blank" rel="noopener noreferrer" class="font-bold text-blue-600 underline">Google Cloud Console</a> lalu buat Project baru (nama bebas, misal: <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-800">SapaSekolah-Backup</code>).
                                    </li>
                                    <li>
                                        Di kotak pencarian atas, ketik <strong>"Google Drive API"</strong> lalu klik tombol <strong>Enable / Aktifkan</strong>.
                                    </li>
                                    <li>
                                        Buka menu kiri: <strong>IAM & Admin > Service Accounts</strong>, lalu klik <strong>+ Create Service Account</strong> (isi nama bebas, misal: <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-800">backup-bot</code>), klik Done.
                                    </li>
                                    <li>
                                        Klik nama Service Account yang baru dibuat tersebut -> tab <strong>Keys (Kunci)</strong> -> klik <strong>Add Key > Create new key</strong> -> pilih format <strong>JSON</strong> -> klik Create. Berkas <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-800">.json</code> akan otomatis terunduh ke komputer Anda.
                                    </li>
                                    <li class="text-amber-900 font-bold bg-amber-50/80 p-2 rounded-lg border border-amber-300">
                                        ★ LANGKAH WAJIB: Salin email robot tersebut (berakhiran <code class="font-mono text-slate-800">@...iam.gserviceaccount.com</code>), lalu buka folder Google Drive UBSI Cikampek dan UBSI Karawang di bawah, klik kanan <strong>Bagikan (Share)</strong>, masukkan email robot tersebut dan berikan izin <strong>"Editor"</strong>.
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <!-- Indikator Service Account yang Aktif saat ini -->
                    @if(!empty($serviceAccountEmail))
                    <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-300 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-emerald-600/10 flex items-center justify-center text-emerald-600 shrink-0">
                                <i data-lucide="bot" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider">Email Robot Service Account Aktif:</p>
                                <p class="font-mono font-bold text-emerald-950 select-all break-all">{{ $serviceAccountEmail }}</p>
                            </div>
                        </div>
                        <button type="button" onclick="navigator.clipboard.writeText('{{ $serviceAccountEmail }}'); alert('Email Service Account disalin ke clipboard!');" class="shrink-0 px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] transition shadow-xs">
                            Salin Email Robot
                        </button>
                    </div>
                    @endif

                    <form id="gdriveSettingsForm" method="POST" action="{{ route('database-backups.settings') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf

                        <!-- Folder Target -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Google Drive Folder ID (UBSI Cikampek)</label>
                                <input type="text" name="gdrive_cikampek_folder_id" value="{{ $cikampekFolderId }}" required class="w-full text-xs font-mono rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 py-2">
                                <p class="text-[10px] text-slate-400 mt-1">Default resmi: <code>1YCNXg6CoeiFdPOoDWUNXGB5L1Nc765aT</code></p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Google Drive Folder ID (UBSI Karawang)</label>
                                <input type="text" name="gdrive_karawang_folder_id" value="{{ $karawangFolderId }}" required class="w-full text-xs font-mono rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 py-2">
                                <p class="text-[10px] text-slate-400 mt-1">Default resmi: <code>1i_84WHAo5nlk8bwDg0vXdEffyX1SLf28</code></p>
                            </div>
                        </div>

                        <!-- OPSI 1: Google Apps Script Webhook (Paling Mudah & Cepat) -->
                        <div class="pt-3 border-t border-slate-200">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-extrabold text-slate-900 flex items-center gap-1.5">
                                    <span class="w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px] font-black">1</span>
                                    <span>Google Apps Script Webhook (Paling Mudah & Cepat - 1 Menit)</span>
                                </label>
                                <span class="text-[10px] font-black text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-full">Sangat Disarankan</span>
                            </div>

                            <div class="p-3.5 bg-emerald-50/70 rounded-2xl border border-emerald-200 text-xs space-y-2 mb-3">
                                <p class="text-emerald-950 font-bold leading-relaxed">
                                    💡 <strong>Tidak perlu Google Cloud Console / Kunci JSON!</strong> Anda cukup membuat Apps Script sederhana di akun Google Anda, dan seluruh berkas backup (Excel & SQL) akan otomatis tersimpan langsung di folder Google Drive Anda.
                                </p>
                                
                                <div x-data="{ showScriptGuide: false }" class="mt-2">
                                    <button type="button" @click="showScriptGuide = !showScriptGuide" class="text-emerald-700 font-bold underline hover:text-emerald-900 flex items-center gap-1 text-[11px]">
                                        <i data-lucide="code-2" class="w-3.5 h-3.5"></i>
                                        <span x-text="showScriptGuide ? 'Sembunyikan Panduan 1 Menit' : 'Lihat Cara Buat Webhook 1 Menit'"></span>
                                    </button>

                                    <div x-show="showScriptGuide" class="mt-2.5 p-3 rounded-xl bg-white border border-emerald-200 text-[11px] text-slate-700 space-y-2">
                                        <ol class="list-decimal list-inside space-y-1.5">
                                            <li>Buka browser Anda dan kunjungi <a href="https://script.new" target="_blank" class="font-bold text-blue-600 underline">script.new</a> (pastikan sudah login dengan akun Google Anda).</li>
                                            <li>Hapus semua teks yang ada di editor, lalu tempel kode di bawah ini:</li>
                                        </ol>

                                        <div class="relative">
                                            <textarea id="appsScriptCodeBlock" readonly rows="8" class="w-full text-[10px] font-mono bg-slate-900 text-emerald-300 p-2.5 rounded-xl border border-slate-700">function doPost(e) {
  try {
    var data = JSON.parse(e.postData.contents);
    var targetFolderId = data.folder_id || data.root_folder_id;
    var folder = DriveApp.getFolderById(targetFolderId);

    if (data.action === "test" || data.action === "ping") {
      return ContentService.createTextOutput(JSON.stringify({
        success: true,
        message: "Koneksi Google Apps Script Webhook berhasil",
        folder_name: folder.getName(),
        folder_id: folder.getId()
      })).setMimeType(ContentService.MimeType.JSON);
    }

    var base64Data = data.file_base64 || data.content_base64;
    var bytes = Utilities.base64Decode(base64Data);
    var blob = Utilities.newBlob(bytes, data.mime_type || "application/octet-stream", data.filename);
    var file = folder.createFile(blob);

    return ContentService.createTextOutput(JSON.stringify({
      success: true,
      file_id: file.getId(),
      file_url: file.getUrl()
    })).setMimeType(ContentService.MimeType.JSON);
  } catch(err) {
    return ContentService.createTextOutput(JSON.stringify({
      success: false,
      message: err.toString()
    })).setMimeType(ContentService.MimeType.JSON);
  }
}</textarea>
                                            <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('appsScriptCodeBlock').value); alert('Kode Google Apps Script disalin!');" class="absolute top-2 right-2 px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] shadow">
                                                Salin Kode
                                            </button>
                                        </div>

                                        <ol start="3" class="list-decimal list-inside space-y-1.5 text-slate-700">
                                            <li>Klik tombol biru <strong>Deploy (Terapkan) > New deployment (Penerapan baru)</strong> di pojok kanan atas.</li>
                                            <li>Pilih jenis (ikon gerigi): <strong>Web app (Aplikasi web)</strong>.</li>
                                            <li>Isi Description: <em>Sapa Sekolah Backup</em>.</li>
                                            <li>Execute as: <strong>Me (Saya)</strong>.</li>
                                            <li>Who has access: <strong>Anyone (Siapa saja)</strong>.</li>
                                            <li>Klik <strong>Deploy</strong>, lalu klik <strong>Authorize Access</strong> (pilih email Anda > Advanced > Go to Untitled project > Allow).</li>
                                            <li>Salin <strong>Web App URL</strong> (berakhiran <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-900 font-mono">/exec</code>) lalu tempel pada kotak di bawah.</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">URL Webhook Google Apps Script</label>
                                <input type="url" name="gdrive_webhook_url" value="{{ $gdriveWebhookUrl }}" placeholder="https://script.google.com/macros/s/AKfycb.../exec" class="w-full text-xs font-mono rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 py-2">
                                <p class="text-[10px] text-slate-400 mt-1">Tempelkan URL Web App Google Apps Script di sini untuk sinkronisasi otomatis.</p>
                            </div>
                        </div>

                        <!-- OPSI 2: Service Account JSON (Resmi Google Cloud) -->
                        <div class="pt-3 border-t border-slate-200">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-extrabold text-slate-900 flex items-center gap-1.5">
                                    <span class="w-5 h-5 rounded-full bg-slate-700 text-white flex items-center justify-center text-[10px] font-black">2</span>
                                    <span>Google Service Account JSON (Google Cloud Console)</span>
                                </label>
                                <span class="text-[10px] font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-full">Alternatif</span>
                            </div>

                            <!-- Opsi Unggah File Langsung -->
                            <div class="mb-3 p-3 bg-slate-50 rounded-xl border border-dashed border-slate-300">
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Cara A: Unggah Berkas .json Service Account</label>
                                <input type="file" name="gdrive_service_account_file" accept=".json,application/json,text/plain" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 cursor-pointer">
                                <p class="text-[10px] text-slate-400 mt-1">Pilih berkas JSON yang diunduh dari Google Cloud Console.</p>
                            </div>

                            <!-- Opsi Tempel Teks JSON -->
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Cara B: Atau Tempel Isi Teks JSON</label>
                                <textarea name="gdrive_service_account_json" rows="4" placeholder='{"type": "service_account", "project_id": "...", "private_key": "...", "client_email": "...", ...}' class="w-full text-[11px] font-mono rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500 p-2.5">{{ \App\Services\AppSettingService::get('gdrive_service_account_json', '') }}</textarea>
                                <p class="text-[10px] text-slate-400 mt-1">Buka file JSON dengan Notepad, salin seluruh kodenya dan tempel di sini.</p>
                            </div>
                        </div>

                        <!-- OPSI 3: OAuth2 Refresh Token (Alternatif) -->
                        <div class="pt-3 border-t border-slate-200">
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                    <span class="w-5 h-5 rounded-full bg-slate-400 text-white flex items-center justify-center text-[10px] font-black">3</span>
                                    <span>OAuth2 Refresh Token (Opsi Alternatif)</span>
                                </label>
                                <span class="text-[10px] font-bold text-slate-500">Opsional</span>
                            </div>
                            
                            <div class="p-2.5 bg-amber-50 rounded-xl border border-amber-200 text-[11px] text-amber-900 mb-2.5">
                                <strong>⚠️ Catatan Penting:</strong> Kolom Client ID OAuth2 <u>BUKAN</u> alamat email pribadi (seperti <code class="font-mono bg-white px-1 py-0.2 rounded">walim.wam@bsi.ac.id</code>), melainkan kode Client ID aplikasi Google OAuth berformat <code class="font-mono bg-white px-1 py-0.2 rounded">xxxx.apps.googleusercontent.com</code>. Jika tidak memilikinya, <strong>cukup gunakan Opsi 1 (Service Account)</strong> di atas.
                            </div>

                            <div class="space-y-2 opacity-90">
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500">Client ID (OAuth)</label>
                                        <input type="text" name="gdrive_client_id" value="{{ \App\Services\AppSettingService::get('gdrive_client_id', '') }}" placeholder="xxxx.apps.googleusercontent.com" class="w-full text-xs rounded-xl border-slate-300 py-1.5 font-mono">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500">Client Secret</label>
                                        <input type="password" name="gdrive_client_secret" value="{{ \App\Services\AppSettingService::get('gdrive_client_secret', '') }}" class="w-full text-xs rounded-xl border-slate-300 py-1.5 font-mono">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500">Refresh Token</label>
                                    <input type="password" name="gdrive_refresh_token" value="{{ \App\Services\AppSettingService::get('gdrive_refresh_token', '') }}" placeholder="1//0xxxx..." class="w-full text-xs rounded-xl border-slate-300 py-1.5 font-mono">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5 shrink-0">
                    <button type="button" @click="settingsModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                        Batal
                    </button>
                    <button type="button" onclick="document.getElementById('gdriveSettingsForm').submit();" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white transition shadow-sm flex items-center gap-1.5">
                        <i data-lucide="save" class="w-3.5 h-3.5"></i>
                        <span>Simpan Pengaturan</span>
                    </button>
                </div>
            </div>
        </div>
        @endif

    </div>
</x-app-layout>

