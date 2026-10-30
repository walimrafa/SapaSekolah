<x-app-layout title="Data Kunjungan Lapangan">
    <!-- Header Title & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6" x-data="{ showQrModal: false }">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Data Kunjungan Lapangan</h1>
            <p class="text-xs text-slate-500 mt-0.5">Pencatatan hasil kunjungan marketing, penerima PIC, target sosialisasi, Google Drive &amp; integrasi kalender</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-2">
            <!-- Unduh Excel Beserta Link Google Drive (Item 6) -->
            <a href="{{ route('visits.export', request()->query()) }}"
                class="px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md flex items-center space-x-1.5 transition">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                <span>Unduh Excel &amp; Link Drive</span>
            </a>

            <!-- Tombol Buka Root Google Drive -->
            <a href="https://drive.google.com/drive/folders/1DQkSEomL8cacePKG8Ex72JldQABKndiD?usp=sharing"
                target="_blank"
                class="px-3.5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-bold rounded-xl text-xs shadow-soft flex items-center space-x-1.5 transition"
                title="Buka Folder Induk Dokumentasi di Google Drive">
                <i data-lucide="folder" class="w-4 h-4 text-amber-500"></i>
                <span>Google Drive Cloud</span>
                <i data-lucide="external-link" class="w-3 h-3 text-slate-400"></i>
            </a>

            <button @click="showQrModal = true" type="button"
                class="px-3.5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs shadow-md flex items-center space-x-1.5 transition cursor-pointer">
                <i data-lucide="qr-code" class="w-4 h-4"></i>
                <span>QR Code Portal</span>
            </button>

            <a href="{{ route('visits.create') }}"
                class="px-4 py-2.5 bg-brand-navy hover:bg-slate-900 text-white font-bold rounded-xl text-xs shadow-md flex items-center space-x-2 transition">
                <i data-lucide="plus-circle" class="w-4 h-4 text-amber-400"></i>
                <span>Catat Kunjungan Baru</span>
            </a>
        </div>

        <!-- Modal QR Code Standalone -->
        <div x-show="showQrModal" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" style="display: none;">
            
            <div @click.away="showQrModal = false" class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-6 text-center border border-slate-200 relative">
                <button @click="showQrModal = false" type="button" class="absolute top-4 right-4 text-slate-400 hover:text-slate-900 p-1">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>

                <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="qr-code" class="w-6 h-6"></i>
                </div>

                <h3 class="text-base font-extrabold text-slate-900">QR Code Portal Input Lapangan</h3>
                <p class="text-xs text-slate-500 mt-1">Scan QR Code ini menggunakan kamera Smartphone petugas di lapangan untuk membuka form tanpa perlu login.</p>

                <div class="my-4 p-3 bg-slate-50 rounded-2xl border border-slate-200 inline-block shadow-inner">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode(route('public-visit.index')) }}" alt="QR Code Portal Input Kunjungan" class="w-48 h-48 mx-auto rounded-xl shadow-xs">
                </div>

                <p class="text-[11px] font-mono font-bold text-brand-700 bg-brand-50 py-1.5 px-3 rounded-xl border border-brand-100 truncate mb-4">
                    {{ route('public-visit.index') }}
                </p>

                <div class="flex items-center justify-center gap-2">
                    <a href="{{ route('public-visit.index') }}" target="_blank" class="w-full py-2.5 bg-brand-navy hover:bg-slate-900 text-white font-bold rounded-xl text-xs transition flex items-center justify-center space-x-1.5">
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                        <span>Buka Link Portal ↗</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Notification -->
    @if(session('toast'))
        <div class="p-4 mb-6 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2 shadow-sm">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
            <span>{{ session('toast')['message'] ?? 'Data berhasil disimpan.' }}</span>
        </div>
    @endif

    <!-- QR Code Banner Card -->
    <div class="bg-gradient-to-r from-slate-900 to-brand-navy text-white rounded-3xl p-5 mb-6 shadow-xl border border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-5">
        <div class="flex items-center space-x-4">
            <div class="p-2 bg-white rounded-2xl shadow-md shrink-0">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode(route('public-visit.index')) }}" alt="QR Code" class="w-16 h-16 rounded-xl">
            </div>
            <div>
                <span class="px-2.5 py-0.5 text-[10px] font-extrabold bg-amber-400 text-slate-900 rounded-full uppercase tracking-wider mb-1 inline-block">Akses Tanpa Login</span>
                <h3 class="text-base font-extrabold tracking-tight">QR Code Portal Input Kunjungan Lapangan</h3>
                <p class="text-xs text-slate-300 mt-0.5">Scan QR Code untuk langsung mengisi formulir hasil kunjungan sekolah via HP/Tablet tanpa perlu login pengguna.</p>
            </div>
        </div>

        <div class="flex items-center space-x-2 shrink-0">
            <a href="{{ route('public-visit.index') }}" target="_blank" class="px-4 py-2.5 bg-amber-400 hover:bg-amber-300 text-slate-900 font-extrabold rounded-xl text-xs transition shadow-md flex items-center space-x-1.5">
                <i data-lucide="external-link" class="w-4 h-4"></i>
                <span>Buka Form Portal ↗</span>
            </a>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden" 
         x-data="{ previewOpen: false, previewSrc: '', previewTitle: '', previewDate: '' }"
         x-init="$watch('previewOpen', value => { if(value && window.lucide) { $nextTick(() => window.lucide.createIcons()); } })">
        <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-slate-50/50">
            <form method="GET" action="{{ route('visits.index') }}" class="flex flex-wrap items-center gap-2 text-xs">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama sekolah..." class="py-1.5 px-3 rounded-xl border border-slate-300">
                
                <select name="user_id" onchange="this.form.submit()" class="py-1.5 px-3 rounded-xl border border-slate-300">
                    <option value="">Semua Marketing PIC</option>
                    @foreach($marketingUsers as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>

                <select name="status" onchange="this.form.submit()" class="py-1.5 px-3 rounded-xl border border-slate-300">
                    <option value="">Semua Status Visit</option>
                    <option value="berhasil" {{ request('status') == 'berhasil' ? 'selected' : '' }}>Berhasil (Terlaksana)</option>
                    <option value="tidak_bertemu" {{ request('status') == 'tidak_bertemu' ? 'selected' : '' }}>Tidak Bertemu</option>
                    <option value="tidak_berhasil" {{ request('status') == 'tidak_berhasil' ? 'selected' : '' }}>Tidak Berhasil</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                </select>

                <button type="submit" class="px-3 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold rounded-xl text-xs transition">
                    Filter
                </button>

                @if(request()->anyFilled(['search', 'user_id', 'status']))
                    <a href="{{ route('visits.index') }}" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition">
                        Reset
                    </a>
                @endif
            </form>
            <span class="text-xs font-bold text-slate-500">Total: {{ $visits->total() }} Record Visit</span>
        </div>

        <div class="overflow-x-auto w-full">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-900 text-white font-bold uppercase text-[11px]">
                    <tr>
                        <th class="py-3.5 px-4">Tanggal Visit</th>
                        <th class="py-3.5 px-4">Sekolah Kunjungan</th>
                        <th class="py-3.5 px-4">Petugas PIC (User Management)</th>
                        <th class="py-3.5 px-4">Penerima &amp; Jabatan</th>
                        <th class="py-3.5 px-4">Target Sosialisasi</th>
                        <th class="py-3.5 px-4">Metode Kegiatan</th>
                        <th class="py-3.5 px-4 text-center">Estimasi Siswa XII</th>
                        <th class="py-3.5 px-4 text-center">Dokumentasi &amp; Drive</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($visits as $v)
                        <tr class="hover:bg-slate-50 transition">
                            <!-- Tanggal Visit -->
                            <td class="py-3.5 px-4 font-bold text-slate-900 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($v->visit_date)->translatedFormat('d M Y') }}
                                @if($v->visitPlan)
                                    <span class="inline-block px-1.5 py-0.5 text-[9px] rounded bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold mt-1">
                                        Dari Rencana
                                    </span>
                                @endif
                            </td>

                            <!-- Sekolah Kunjungan -->
                            <td class="py-3.5 px-4 font-bold text-slate-900 whitespace-nowrap">
                                <a href="{{ route('schools.show', $v->school_id) }}" class="hover:text-brand-600 block">
                                    {{ $v->school ? $v->school->name : '-' }}
                                </a>
                                <span class="block text-[10px] text-slate-400 font-normal">Kec. {{ $v->school && $v->school->district ? $v->school->district->name : '-' }}</span>
                            </td>

                            <!-- Petugas PIC (User Management Integrasi WA & Kalender) -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-extrabold text-brand-700 flex items-center gap-1">
                                    <i data-lucide="user-check" class="w-3.5 h-3.5 text-brand-600"></i>
                                    <span>{{ $v->user ? $v->user->name : '-' }}</span>
                                </div>
                                <div class="text-[10px] text-slate-500 mt-0.5 space-y-0.5">
                                    @if($v->user && $v->user->phone)
                                        <p class="font-mono text-emerald-700 font-semibold flex items-center gap-1">
                                            <i data-lucide="phone" class="w-3 h-3 text-emerald-600"></i>
                                            <span>{{ $v->user->phone }}</span>
                                        </p>
                                    @endif
                                    @if($v->user && $v->user->email)
                                        <p class="text-slate-400 truncate max-w-[140px]" title="{{ $v->user->email }}">
                                            {{ $v->user->email }}
                                        </p>
                                    @endif
                                </div>
                                <!-- Action Buttons: WA & Google Calendar Links -->
                                <div class="flex items-center gap-1.5 mt-1.5">
                                    @php
                                        $cleanUserPhone = $v->user?->phone ? preg_replace('/[^0-9]/', '', $v->user->phone) : '';
                                        if (str_starts_with($cleanUserPhone, '0')) {
                                            $cleanUserPhone = '62' . substr($cleanUserPhone, 1);
                                        }
                                        $waText = "Halo Kak {$v->user?->name},\n\nRekap Kunjungan Lapangan SAPA Sekolah UBSI:\nSekolah: " . ($v->school?->name ?? '-') . "\nTanggal: " . \Carbon\Carbon::parse($v->visit_date)->translatedFormat('d M Y') . "\nStatus: " . strtoupper($v->status) . "\nDrive: " . $v->drive_folder_url;
                                        $waUrl = !empty($cleanUserPhone) ? "https://wa.me/{$cleanUserPhone}?text=" . urlencode($waText) : null;
                                        
                                        $gcalStart = \Carbon\Carbon::parse($v->visit_date)->setTime(9, 0, 0);
                                        $gcalEnd = $gcalStart->copy()->addHours(2);
                                        $gcalUrl = \App\Services\GoogleCalendarNotificationService::generateGoogleCalendarUrl(
                                            "Kunjungan: " . ($v->school?->name ?? 'Sekolah'),
                                            $gcalStart,
                                            $gcalEnd,
                                            "Laporan Kunjungan Lapangan SAPA Sekolah UBSI\nSekolah: " . ($v->school?->name ?? '-') . "\nPetugas: " . ($v->user?->name ?? '-') . "\nDrive: " . $v->drive_folder_url,
                                            $v->school?->name ?? 'Lokasi Sekolah',
                                            array_filter([$v->user?->email])
                                        );
                                    @endphp

                                    @if($waUrl)
                                        <a href="{{ $waUrl }}" target="_blank" title="Kirim Pesan WhatsApp ke Petugas"
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-[10px] font-bold transition">
                                            <i data-lucide="message-circle" class="w-3 h-3 text-emerald-600"></i>
                                            <span>WA</span>
                                        </a>
                                    @endif

                                    <a href="{{ $gcalUrl }}" target="_blank" title="Simpan ke Google Calendar"
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-blue-50 hover:bg-blue-100 text-blue-800 border border-blue-200 text-[10px] font-bold transition">
                                        <i data-lucide="calendar" class="w-3 h-3 text-blue-600"></i>
                                        <span>GCal</span>
                                    </a>
                                </div>
                            </td>

                            <!-- Penerima & Jabatan -->
                            <td class="py-3.5 px-4 font-semibold text-slate-800 whitespace-nowrap">
                                {{ $v->recipient_name ?? $v->contact_person ?? '-' }}
                                <span class="block text-[10px] text-slate-500 font-normal">
                                    {{ $v->recipient_position ?? 'Jabatan' }}
                                    @if($v->recipient_phone)
                                        &bull; <span class="text-emerald-700 font-mono font-semibold">{{ $v->recipient_phone }}</span>
                                    @endif
                                </span>
                                @if($v->headmaster_name)
                                    <span class="block text-[10px] text-slate-400">Kepsek: {{ $v->headmaster_name }}</span>
                                @endif
                            </td>

                            <!-- Target Sosialisasi -->
                            <td class="py-3.5 px-4 text-slate-700 whitespace-nowrap">
                                <span class="font-bold text-emerald-700 block">{{ $v->target_month_year ?? '-' }}</span>
                                <span class="text-[10px] text-slate-500 block">Jam {{ $v->target_time ?? '-' }} &bull; Tgl: {{ $v->target_date ? \Carbon\Carbon::parse($v->target_date)->translatedFormat('d M Y') : 'Flexibel' }}</span>
                            </td>

                            <!-- Metode Kegiatan -->
                            <td class="py-3.5 px-4 font-semibold text-purple-700 whitespace-nowrap">
                                {{ $v->socialization_method === 'Lainnya' ? ($v->other_activity_name ?? 'Lainnya') : ($v->socialization_method ?? '-') }}
                            </td>

                            <!-- Estimasi Siswa XII -->
                            <td class="py-3.5 px-4 text-center font-extrabold text-brand-600 whitespace-nowrap">
                                @if($v->grade_12_class_count && $v->students_per_class)
                                    {{ $v->grade_12_class_count * $v->students_per_class }} Siswa
                                    <span class="block text-[10px] text-slate-400 font-normal">({{ $v->grade_12_class_count }} Kls &times; {{ $v->students_per_class }} Ssw)</span>
                                @else
                                    -
                                @endif
                            </td>

                            <!-- Dokumentasi & Google Drive (Item 5 & 6) -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @php
                                    $hasDoc = !empty($v->photo);
                                    $docExt = $hasDoc ? strtolower(pathinfo($v->photo, PATHINFO_EXTENSION)) : '';
                                    $isImg = in_array($docExt, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp']);
                                    $docUrl = $hasDoc ? asset('storage/' . $v->photo) : null;
                                    $driveUrl = $v->drive_folder_url;
                                @endphp

                                <div class="flex flex-col items-center gap-1.5">
                                    @if($hasDoc)
                                        @if($isImg)
                                            <div class="inline-flex items-center gap-1 justify-center">
                                                <button type="button"
                                                    @click="previewOpen = true; previewSrc = '{{ $docUrl }}'; previewTitle = '{{ addslashes($v->school?->name ?? 'Dokumentasi Kunjungan') }}'; previewDate = '{{ \Carbon\Carbon::parse($v->visit_date)->translatedFormat('d M Y') }}'"
                                                    class="group relative inline-block focus:outline-none cursor-pointer"
                                                    title="Klik untuk pratinjau foto">
                                                    <img src="{{ $docUrl }}" alt="Dokumentasi" class="w-9 h-9 object-cover rounded-xl border-2 border-white shadow-sm ring-1 ring-slate-200 group-hover:scale-110 group-hover:ring-brand-500 transition duration-150">
                                                    <span class="absolute inset-0 bg-black/25 rounded-xl opacity-0 group-hover:opacity-100 flex items-center justify-center transition">
                                                        <i data-lucide="zoom-in" class="w-3 h-3 text-white drop-shadow"></i>
                                                    </span>
                                                </button>
                                                <a href="{{ $docUrl }}" target="_blank" download title="Buka berkas di tab baru" class="p-1 text-slate-400 hover:text-brand-600 transition">
                                                    <i data-lucide="external-link" class="w-3 h-3"></i>
                                                </a>
                                            </div>
                                        @else
                                            <a href="{{ $docUrl }}" target="_blank" download
                                               title="Unduh {{ basename($v->photo) }}"
                                               class="inline-flex items-center gap-1 px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-xl text-[10px] font-bold transition">
                                                <i data-lucide="file-text" class="w-3 h-3 text-amber-600"></i>
                                                <span class="uppercase font-mono">{{ $docExt ?: 'FILE' }}</span>
                                            </a>
                                        @endif
                                    @endif

                                    <!-- Tombol Link Google Drive Sekolah Realtime (Item 5) -->
                                    <a href="{{ $driveUrl }}" target="_blank"
                                        title="Buka Folder Google Drive Sekolah Ini"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-300 text-[10px] font-bold transition shadow-2xs">
                                        <i data-lucide="folder" class="w-3 h-3 text-amber-600"></i>
                                        <span>Folder Drive</span>
                                        <i data-lucide="external-link" class="w-2.5 h-2.5 text-amber-700"></i>
                                    </a>
                                </div>
                            </td>

                            <!-- Status Kunjungan -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @php
                                    $sColor = match($v->status) {
                                        'berhasil' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
                                        'tidak_bertemu' => 'bg-amber-100 text-amber-800 border border-amber-200',
                                        'tidak_berhasil' => 'bg-red-100 text-red-800 border border-red-200',
                                        default => 'bg-slate-100 text-slate-700 border border-slate-200',
                                    };
                                @endphp
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full uppercase {{ $sColor }}">
                                    {{ str_replace('_', ' ', $v->status) }}
                                </span>
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <a href="{{ route('follow-ups.index', ['search' => $v->school?->name]) }}" title="Follow Up Fix Jadwal Sosialisasi" class="p-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-lg transition">
                                        <i data-lucide="phone-call" class="w-3.5 h-3.5"></i>
                                    </a>
                                    <a href="{{ route('visits.edit', $v->id) }}" title="Edit Kunjungan" class="p-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg transition">
                                        <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                                    </a>
                                    <form method="POST" action="{{ route('visits.destroy', $v->id) }}" onsubmit="return confirm('Hapus data kunjungan ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Kunjungan" class="p-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-12 text-center text-slate-400">
                                <i data-lucide="map-pin" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                <p class="font-bold text-slate-600">Belum ada data kunjungan lapangan dicatat.</p>
                                <p class="text-xs text-slate-400 mt-1">Gunakan tombol "Catat Kunjungan Baru" atau pilih dari Rencana Kunjungan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-slate-50 border-t border-slate-100">
            {{ $visits->links() }}
        </div>

        <!-- Modal Preview Dokumentasi Kunjungan -->
        <div x-show="previewOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/75 backdrop-blur-xs" 
             style="display: none;"
             @keydown.escape.window="previewOpen = false">
            <div @click.away="previewOpen = false" class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full p-5 border border-slate-200 relative overflow-hidden">
                <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                    <div>
                        <span class="px-2.5 py-0.5 text-[10px] font-bold bg-brand-50 text-brand-700 rounded-full uppercase tracking-wider">Dokumentasi Kunjungan Lapangan</span>
                        <h3 class="text-base font-extrabold text-slate-900 mt-1" x-text="previewTitle"></h3>
                        <p class="text-xs text-slate-500" x-text="'Tanggal Kunjungan: ' + previewDate"></p>
                    </div>
                    <div class="flex items-center space-x-1.5">
                        <a :href="previewSrc" target="_blank" download class="p-2 text-slate-500 hover:text-brand-600 hover:bg-slate-100 rounded-xl transition" title="Buka / Unduh Berkas Penuh">
                            <i data-lucide="external-link" class="w-4 h-4"></i>
                        </a>
                        <button @click="previewOpen = false" type="button" class="p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition cursor-pointer">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                <div class="bg-slate-900/5 rounded-2xl p-2 flex items-center justify-center max-h-[70vh] overflow-hidden border border-slate-100">
                    <img :src="previewSrc" :alt="previewTitle" class="max-h-[65vh] w-auto max-w-full object-contain rounded-xl shadow-xs">
                </div>

                <div class="mt-3 flex items-center justify-between text-xs">
                    <span class="text-slate-400 text-[11px]">Format Berkas: Foto / Dokumen Lapangan</span>
                    <a :href="previewSrc" target="_blank" download class="px-3.5 py-1.5 bg-brand-navy hover:bg-slate-900 text-white font-bold rounded-xl text-xs transition flex items-center space-x-1.5">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span>Unduh Berkas</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
