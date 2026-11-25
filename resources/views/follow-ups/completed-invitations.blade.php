<x-app-layout title="Sekolah Menerima Undangan Acara">
    <div x-data="{
        showUploadModal: false,
        activeFollowUpId: null,
        activeSchoolName: '',
        showStatusModal: false,
        activeStatus: 'akan_hadir',
        activeNotes: '',
        activeEventName: '',
        previewOpen: false,
        previewSrc: '',
        previewTitle: '',
        openUpload(id, name) {
            this.activeFollowUpId = id;
            this.activeSchoolName = name;
            this.showUploadModal = true;
        },
        openStatus(id, name, status, notes, eventName) {
            this.activeFollowUpId = id;
            this.activeSchoolName = name;
            this.activeStatus = status || 'menunggu_respon';
            this.activeNotes = notes || '';
            this.activeEventName = eventName || '';
            this.showStatusModal = true;
        }
    }">
        <!-- Header Section -->
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="rounded-xl bg-amber-100 p-2 text-amber-700 shadow-xs">
                        <i data-lucide="mail-check" class="h-5 w-5"></i>
                    </span>
                    <div>
                        <h1 class="text-xl font-extrabold tracking-tight text-slate-900">Sekolah Menerima Undangan Acara</h1>
                        <p class="mt-0.5 text-xs text-slate-500">Daftar sekolah yang menerima undangan acara kampus, status konfirmasi kehadiran, PIC penerima &amp; bukti tanda terima.</p>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                <!-- Buka Root Google Drive Sesuai Kampus Aktif -->
                <a href="{{ \App\Services\GoogleDriveService::getFolderUrlForCampus() }}"
                    target="_blank"
                    class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-amber-200 bg-amber-50 px-3.5 py-2.5 text-xs font-bold text-amber-900 shadow-sm transition hover:bg-amber-100"
                    title="Buka Folder Google Drive Dokumentasi ({{ \App\Services\GoogleDriveService::getFolderLabelForCampus() }})">
                    <i data-lucide="folder" class="h-4 w-4 text-amber-600"></i>
                    <span>{{ \App\Services\GoogleDriveService::getFolderLabelForCampus() }}</span>
                    <i data-lucide="external-link" class="h-3 w-3 text-amber-700"></i>
                </a>

                <a href="{{ route('follow-ups.index', ['agenda_category' => 'mengantar_undangan']) }}"
                    class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                    <i data-lucide="arrow-left" class="h-4 w-4"></i>
                    Kembali ke Follow Up
                </a>
            </div>
        </div>

        <!-- Quick Switcher Tabs Output Lapangan -->
        @include('follow-ups._output-tabs')

        @if(session('toast'))
            <div class="mb-6 flex items-center gap-3 rounded-2xl border {{ (session('toast')['type'] ?? '') === 'error' ? 'border-rose-200 bg-rose-50 text-rose-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800' }} p-4 text-sm font-semibold shadow-sm">
                <i data-lucide="{{ (session('toast')['type'] ?? '') === 'error' ? 'alert-circle' : 'circle-check' }}" class="h-5 w-5 shrink-0"></i>
                <span>{{ session('toast')['message'] }}</span>
            </div>
        @endif

        <!-- KPI Summary Cards (5 Status Kehadiran Undangan) -->
        <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-5">
            <a href="{{ route('invitations.completed.index') }}" 
               class="rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm transition hover:border-slate-300 {{ !request()->filled('attendance_status') ? 'ring-2 ring-brand-500/30' : '' }}">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Total Undangan</p>
                <p class="mt-1 text-2xl font-black text-slate-900">{{ number_format($totalInvitations) }}</p>
                <p class="text-[11px] text-slate-400">Seluruh sekolah disebar</p>
            </a>
            
            <a href="{{ route('invitations.completed.index', ['attendance_status' => 'akan_hadir']) }}" 
               class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-3.5 shadow-sm transition hover:bg-emerald-50 {{ request('attendance_status') === 'akan_hadir' ? 'ring-2 ring-emerald-500' : '' }}">
                <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Akan Hadir
                </p>
                <p class="mt-1 text-2xl font-black text-emerald-800">{{ number_format($akanHadirCount) }}</p>
                <p class="text-[11px] text-emerald-600 font-semibold">Terkonfirmasi hadir</p>
            </a>

            <a href="{{ route('invitations.completed.index', ['attendance_status' => 'minta_ijin_dulu']) }}" 
               class="rounded-2xl border border-blue-200 bg-blue-50/50 p-3.5 shadow-sm transition hover:bg-blue-50 {{ request('attendance_status') === 'minta_ijin_dulu' ? 'ring-2 ring-blue-500' : '' }}">
                <p class="text-[10px] font-bold uppercase tracking-wider text-blue-700 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Minta Ijin Dulu
                </p>
                <p class="mt-1 text-2xl font-black text-blue-800">{{ number_format($mintaIjinCount) }}</p>
                <p class="text-[11px] text-blue-600 font-semibold">Menunggu disposisi</p>
            </a>

            <a href="{{ route('invitations.completed.index', ['attendance_status' => 'tidak_bisa_hadir']) }}" 
               class="rounded-2xl border border-rose-200 bg-rose-50/50 p-3.5 shadow-sm transition hover:bg-rose-50 {{ request('attendance_status') === 'tidak_bisa_hadir' ? 'ring-2 ring-rose-500' : '' }}">
                <p class="text-[10px] font-bold uppercase tracking-wider text-rose-700 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Tidak Bisa Hadir
                </p>
                <p class="mt-1 text-2xl font-black text-rose-800">{{ number_format($tidakHadirCount) }}</p>
                <p class="text-[11px] text-rose-600 font-semibold">Berhalangan hadir</p>
            </a>

            <a href="{{ route('invitations.completed.index', ['attendance_status' => 'menunggu_respon']) }}" 
               class="rounded-2xl border border-amber-200 bg-amber-50/50 p-3.5 shadow-sm transition hover:bg-amber-50 {{ request('attendance_status') === 'menunggu_respon' ? 'ring-2 ring-amber-500' : '' }}">
                <p class="text-[10px] font-bold uppercase tracking-wider text-amber-700 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Menunggu Respon
                </p>
                <p class="mt-1 text-2xl font-black text-amber-800">{{ number_format($menungguResponCount) }}</p>
                <p class="text-[11px] text-amber-600 font-semibold">Belum ada respon</p>
            </a>
        </div>

        <!-- Filter Pills & Search Bar -->
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
                <a href="{{ route('invitations.completed.index', array_filter(['search' => request('search')])) }}"
                   class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap {{ !request()->filled('attendance_status') ? 'bg-slate-900 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                    Semua ({{ $totalInvitations }})
                </a>
                <a href="{{ route('invitations.completed.index', array_filter(['attendance_status' => 'akan_hadir', 'search' => request('search')])) }}"
                   class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap {{ request('attendance_status') === 'akan_hadir' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                    Akan Hadir ({{ $akanHadirCount }})
                </a>
                <a href="{{ route('invitations.completed.index', array_filter(['attendance_status' => 'minta_ijin_dulu', 'search' => request('search')])) }}"
                   class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap {{ request('attendance_status') === 'minta_ijin_dulu' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                    Minta Ijin Dulu ({{ $mintaIjinCount }})
                </a>
                <a href="{{ route('invitations.completed.index', array_filter(['attendance_status' => 'tidak_bisa_hadir', 'search' => request('search')])) }}"
                   class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap {{ request('attendance_status') === 'tidak_bisa_hadir' ? 'bg-rose-600 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                    Tidak Bisa Hadir ({{ $tidakHadirCount }})
                </a>
                <a href="{{ route('invitations.completed.index', array_filter(['attendance_status' => 'menunggu_respon', 'search' => request('search')])) }}"
                   class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap {{ request('attendance_status') === 'menunggu_respon' ? 'bg-amber-600 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                    Menunggu Respon ({{ $menungguResponCount }})
                </a>
            </div>

            <!-- Search Bar -->
            <form method="GET" action="{{ route('invitations.completed.index') }}" class="flex items-center gap-2">
                @if(request()->filled('attendance_status'))
                    <input type="hidden" name="attendance_status" value="{{ request('attendance_status') }}">
                @endif
                <div class="relative w-full sm:w-64">
                    <input type="search" name="search" value="{{ request('search') }}"
                        placeholder="Cari sekolah, kecamatan, PIC..."
                        class="w-full rounded-xl border border-slate-200 bg-white pl-9 pr-3.5 py-2 text-xs shadow-xs focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-2.5 pointer-events-none"></i>
                </div>
                <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white transition hover:bg-slate-800">
                    Cari
                </button>
                @if(request()->filled('search'))
                    <a href="{{ route('invitations.completed.index', array_filter(['attendance_status' => request('attendance_status')])) }}"
                        class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-600 transition hover:bg-slate-50">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- Table Card -->
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1050px] text-left text-xs">
                    <thead class="bg-slate-50 text-[10px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3">Sekolah &amp; Kecamatan</th>
                            <th class="px-4 py-3">Nama Event / Acara</th>
                            <th class="px-4 py-3">Tanggal Antar</th>
                            <th class="px-4 py-3">Status Kehadiran</th>
                            <th class="px-4 py-3">Penerima Undangan (PIC)</th>
                            <th class="px-4 py-3">Marketing Pengantar</th>
                            <th class="px-4 py-3">Catatan Respon</th>
                            <th class="px-4 py-3 text-center">Tanda Terima &amp; Drive</th>
                            <th class="px-4 py-3 text-center">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($invitations as $item)
                            @php
                                $visit = $item->visit;
                                $eventName = $item->event_name ?: ($visit?->event_name ?? null);
                                $attStatus = $item->invitation_attendance_status ?: ($visit?->invitation_attendance_status ?? null);
                                $picName = $item->pic_name ?: ($visit?->recipient_name ?: '-');
                                $picPosition = $item->pic_position ?: ($visit?->recipient_position ?: '-');
                                $picPhone = $item->pic_phone ?: ($visit?->recipient_phone ?: '');
                                $cleanPhone = preg_replace('/[^0-9]/', '', $picPhone);
                                if (str_starts_with($cleanPhone, '0')) {
                                    $waPhone = '62' . substr($cleanPhone, 1);
                                } else {
                                    $waPhone = $cleanPhone;
                                }

                                $visitDate = $visit?->visit_date ?: ($item->followup_date ?: $item->created_at);
                                $docFile = $item->documentation_file ?: ($visit?->photo ?? null);
                                $driveFileUrl = $item->google_drive_file_url ?: ($visit?->google_drive_file_url ?? null);
                                $driveFolderUrl = $item->google_drive_folder_url ?: ($visit?->google_drive_folder_url ?? null);
                                $notesText = $item->notes ?: ($visit?->notes ?: ($item->description ?: ''));
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <!-- Sekolah & Kecamatan -->
                                <td class="px-4 py-3.5">
                                    <div class="font-extrabold text-slate-900 text-xs">
                                        {{ $item->school?->name ?? 'Sekolah Tidak Ditemukan' }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1">
                                        <i data-lucide="map-pin" class="w-3 h-3 text-slate-400"></i>
                                        <span>Kec. {{ $item->school?->district?->name ?? '-' }}</span>
                                    </div>
                                </td>

                                <!-- Nama Event / Acara -->
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @if(!empty($eventName))
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-black bg-amber-100/80 text-amber-900 border border-amber-300/80 shadow-2xs">
                                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-amber-700"></i>
                                            <span>{{ $eventName }}</span>
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">Undangan Umum</span>
                                    @endif
                                </td>

                                <!-- Tanggal Antar -->
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div class="font-bold text-slate-800">
                                        {{ $visitDate ? \Carbon\Carbon::parse($visitDate)->format('d M Y') : '-' }}
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        {{ $visitDate ? \Carbon\Carbon::parse($visitDate)->diffForHumans() : '' }}
                                    </div>
                                </td>
                                        {{ $visitDate ? \Carbon\Carbon::parse($visitDate)->format('d M Y') : '-' }}
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        {{ $visitDate ? \Carbon\Carbon::parse($visitDate)->diffForHumans() : '' }}
                                    </div>
                                </td>

                                <!-- Status Kehadiran Undangan -->
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @if($attStatus === 'akan_hadir')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Akan Hadir
                                        </span>
                                    @elseif($attStatus === 'minta_ijin_dulu')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                            Minta Ijin Dulu
                                        </span>
                                    @elseif($attStatus === 'tidak_bisa_hadir')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Tidak Bisa Hadir
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Menunggu Respon
                                        </span>
                                    @endif
                                </td>

                                <!-- PIC Penerima & Kontak -->
                                <td class="px-4 py-3.5">
                                    <div class="font-bold text-slate-900">{{ $picName }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $picPosition }}</div>
                                    @if(!empty($picPhone))
                                        <div class="mt-1 flex items-center gap-1">
                                            <a href="https://wa.me/{{ $waPhone }}" target="_blank"
                                               class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 rounded-md hover:bg-emerald-100 transition">
                                                <i data-lucide="message-circle" class="w-2.5 h-2.5"></i>
                                                <span>{{ $picPhone }}</span>
                                            </a>
                                        </div>
                                    @endif
                                </td>

                                <!-- Marketing Pengantar -->
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div class="font-semibold text-slate-800">{{ $item->user?->name ?? ($visit?->user?->name ?? 'Marketing') }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $item->user?->role?->name ?? 'Staf' }}</div>
                                </td>

                                <!-- Catatan Respon -->
                                <td class="px-4 py-3.5 max-w-[200px]">
                                    <p class="text-xs text-slate-600 line-clamp-2" title="{{ $notesText }}">
                                        {{ !empty($notesText) ? $notesText : '-' }}
                                    </p>
                                </td>

                                <!-- Tanda Terima & Google Drive -->
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    <div class="inline-flex flex-col items-center gap-1">
                                        @if($docFile)
                                            @php
                                                $isImg = preg_match('/\.(jpg|jpeg|png|webp)$/i', $docFile);
                                                $fileUrl = asset('storage/' . $docFile);
                                            @endphp
                                            @if($isImg)
                                                <button type="button"
                                                    @click="previewTitle = '{{ addslashes($item->school?->name ?? 'Bukti Tanda Terima') }}'; previewSrc = '{{ $fileUrl }}'; previewOpen = true;"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold text-[11px] hover:bg-emerald-100 transition">
                                                    <i data-lucide="image" class="w-3 h-3 text-emerald-600"></i>
                                                    <span>Lihat Bukti Foto</span>
                                                </button>
                                            @else
                                                <a href="{{ $fileUrl }}" target="_blank" download
                                                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 border border-blue-200 text-blue-700 font-bold text-[11px] hover:bg-blue-100 transition">
                                                    <i data-lucide="file-text" class="w-3 h-3 text-blue-600"></i>
                                                    <span>Unduh Berkas</span>
                                                </a>
                                            @endif
                                        @else
                                            <button type="button"
                                                @click="openUpload({{ $item->id }}, '{{ addslashes($item->school?->name ?? 'Sekolah') }}')"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 text-slate-600 font-bold text-[10px] hover:bg-amber-50 hover:text-amber-700 hover:border-amber-200 transition">
                                                <i data-lucide="upload" class="w-2.5 h-2.5"></i>
                                                <span>Upload Bukti</span>
                                            </button>
                                        @endif

                                        @if($driveFileUrl)
                                            <a href="{{ $driveFileUrl }}" target="_blank"
                                               class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-700 hover:underline">
                                                <i data-lucide="external-link" class="w-2.5 h-2.5"></i>
                                                <span>Google Drive</span>
                                            </a>
                                        @endif
                                    </div>
                                </td>

                                <!-- Tindakan -->
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <!-- Tombol Update Respon Kehadiran -->
                                        <button type="button"
                                            @click="openStatus({{ $item->id }}, '{{ addslashes($item->school?->name ?? 'Sekolah') }}', '{{ $attStatus }}', '{{ addslashes($notesText) }}', '{{ addslashes($eventName ?? '') }}')"
                                            class="p-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 transition"
                                            title="Ubah Respon Kehadiran & Catatan">
                                            <i data-lucide="clipboard-edit" class="w-3.5 h-3.5"></i>
                                        </button>

                                        <!-- Tombol Upload / Ganti Bukti Tanda Terima -->
                                        <button type="button"
                                            @click="openUpload({{ $item->id }}, '{{ addslashes($item->school?->name ?? 'Sekolah') }}')"
                                            class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition"
                                            title="Upload / Ganti Bukti Tanda Terima Google Drive">
                                            <i data-lucide="upload-cloud" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-14 text-center">
                                    <i data-lucide="mail" class="mx-auto mb-2 h-8 w-8 text-slate-300"></i>
                                    <p class="font-bold text-slate-600">Belum ada data undangan acara yang dicatat</p>
                                    <p class="mt-1 text-xs text-slate-400">Input kunjungan sekolah dengan kategori "Mengantar Undangan Acara".</p>
                                    <a href="{{ route('visits.create') }}" class="mt-3 inline-flex text-xs font-bold text-brand-600 hover:text-brand-800">Input Kunjungan Baru</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($invitations->hasPages())
                <div class="border-t border-slate-100 p-4">
                    {{ $invitations->links() }}
                </div>
            @endif
        </div>

        <!-- Modal Update Respon Kehadiran Cepat -->
        <div x-show="showStatusModal" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
            style="display: none;"
            @keydown.escape.window="showStatusModal = false">
            
            <div @click.away="showStatusModal = false" class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 border border-slate-200 relative">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="p-1.5 rounded-xl bg-amber-500 text-white">
                            <i data-lucide="mail-check" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900">Update Status Kehadiran Undangan</h3>
                            <p class="text-[11px] text-slate-500" x-text="'Sekolah: ' + activeSchoolName"></p>
                        </div>
                    </div>
                    <button @click="showStatusModal = false" type="button" class="text-slate-400 hover:text-slate-700 p-1">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form :action="'/invitations-completed/' + activeFollowUpId + '/status'" method="POST" class="space-y-4 text-xs">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Event / Acara Kampus yang Diundang</label>
                        <input type="text" name="event_name" x-model="activeEventName" placeholder="Contoh: BSI Flash 2026, Workshop Guru BK..." class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:border-amber-500 font-semibold text-slate-800 text-xs">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-2">Konfirmasi Kehadiran Acara <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-1 gap-2">
                            <label class="flex items-center gap-3 p-2.5 rounded-xl border border-slate-200 hover:bg-emerald-50/50 cursor-pointer transition"
                                   :class="activeStatus === 'akan_hadir' ? 'border-emerald-500 bg-emerald-50/80 font-bold text-emerald-900 ring-1 ring-emerald-500' : ''">
                                <input type="radio" name="invitation_attendance_status" value="akan_hadir" x-model="activeStatus" class="text-emerald-600 focus:ring-emerald-500">
                                <div>
                                    <span class="font-bold text-emerald-800">Akan Hadir</span>
                                    <p class="text-[10px] text-slate-500 font-normal">Pihak sekolah/guru konfirmasi siap hadir pada acara kampus.</p>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 p-2.5 rounded-xl border border-slate-200 hover:bg-blue-50/50 cursor-pointer transition"
                                   :class="activeStatus === 'minta_ijin_dulu' ? 'border-blue-500 bg-blue-50/80 font-bold text-blue-900 ring-1 ring-blue-500' : ''">
                                <input type="radio" name="invitation_attendance_status" value="minta_ijin_dulu" x-model="activeStatus" class="text-blue-600 focus:ring-blue-500">
                                <div>
                                    <span class="font-bold text-blue-800">Minta Ijin Dulu</span>
                                    <p class="text-[10px] text-slate-500 font-normal">Menunggu persetujuan kepala sekolah / yayasan.</p>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 p-2.5 rounded-xl border border-slate-200 hover:bg-rose-50/50 cursor-pointer transition"
                                   :class="activeStatus === 'tidak_bisa_hadir' ? 'border-rose-500 bg-rose-50/80 font-bold text-rose-900 ring-1 ring-rose-500' : ''">
                                <input type="radio" name="invitation_attendance_status" value="tidak_bisa_hadir" x-model="activeStatus" class="text-rose-600 focus:ring-rose-500">
                                <div>
                                    <span class="font-bold text-rose-800">Tidak Bisa Hadir</span>
                                    <p class="text-[10px] text-slate-500 font-normal">Ada agenda bersamaan atau tidak dapat menghadiri acara.</p>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 p-2.5 rounded-xl border border-slate-200 hover:bg-amber-50/50 cursor-pointer transition"
                                   :class="activeStatus === 'menunggu_respon' ? 'border-amber-500 bg-amber-50/80 font-bold text-amber-900 ring-1 ring-amber-500' : ''">
                                <input type="radio" name="invitation_attendance_status" value="menunggu_respon" x-model="activeStatus" class="text-amber-600 focus:ring-amber-500">
                                <div>
                                    <span class="font-bold text-amber-800">Menunggu Respon</span>
                                    <p class="text-[10px] text-slate-500 font-normal">Surat sudah diterima namun belum memberikan kepastian.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Catatan Respon Penerima Undangan (Opsional)</label>
                        <textarea name="notes" rows="3" x-model="activeNotes" placeholder="Contoh: Konfirmasi akan hadir 2 orang guru BK..." class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:border-amber-500"></textarea>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2">
                        <button @click="showStatusModal = false" type="button" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-md transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Upload Bukti Tanda Terima Undangan -->
        <div x-show="showUploadModal" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
            style="display: none;"
            @keydown.escape.window="showUploadModal = false">
            
            <div @click.away="showUploadModal = false" class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 border border-slate-200 relative">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="p-1.5 rounded-xl bg-amber-500 text-white">
                            <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900">Upload Bukti Tanda Terima Undangan</h3>
                            <p class="text-[11px] text-slate-500" x-text="'Sekolah: ' + activeSchoolName"></p>
                        </div>
                    </div>
                    <button @click="showUploadModal = false" type="button" class="text-slate-400 hover:text-slate-700 p-1">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form :action="'/invitations-completed/' + activeFollowUpId + '/documentation'" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                    @csrf

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">
                            Foto Lembar Tanda Terima / Penyerahan Undangan <span class="text-rose-500">*</span>
                        </label>
                        <input type="file" name="documentation" required accept="image/*,.pdf,.zip,.rar"
                            class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-50 file:text-amber-800 hover:file:bg-amber-100 border border-slate-300 rounded-xl p-1.5 focus:border-amber-500">
                        <p class="mt-1 text-[10px] text-slate-400">Format: JPG, PNG, PDF. Otomatis disinkronkan ke folder Google Drive &amp; Data Kunjungan.</p>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                        <textarea name="notes" rows="2" placeholder="Contoh: Surat fisik diserahkan kepada staf TU..." class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:border-amber-500"></textarea>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2">
                        <button @click="showUploadModal = false" type="button" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-md flex items-center gap-1.5 transition">
                            <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                            <span>Upload ke Google Drive</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Image Preview -->
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
            <div @click.away="previewOpen = false" class="bg-white rounded-3xl shadow-2xl max-w-xl w-full p-5 border border-slate-200 relative overflow-hidden">
                <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                    <h3 class="text-sm font-extrabold text-slate-900" x-text="previewTitle"></h3>
                    <button @click="previewOpen = false" type="button" class="p-1 text-slate-400 hover:text-slate-700">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                <div class="bg-slate-900/5 rounded-2xl p-2 flex items-center justify-center max-h-[65vh] overflow-hidden">
                    <img :src="previewSrc" :alt="previewTitle" class="max-h-[60vh] w-auto max-w-full object-contain rounded-xl shadow-xs">
                </div>
                <div class="mt-3 flex items-center justify-end">
                    <a :href="previewSrc" target="_blank" download class="px-3.5 py-1.5 bg-slate-900 hover:bg-black text-white font-bold rounded-xl text-xs transition flex items-center space-x-1.5">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span>Unduh Berkas</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>