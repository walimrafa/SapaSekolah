<x-app-layout title="Hasil Kunjungan Lainnya &amp; Audiensi Dinas">
    <div x-data="{
        showUploadModal: false,
        activeFollowUpId: null,
        activeSchoolName: '',
        showStatusModal: false,
        activeStatus: 'completed',
        activeNotes: '',
        previewOpen: false,
        previewSrc: '',
        previewTitle: '',
        openUpload(id, name) {
            this.activeFollowUpId = id;
            this.activeSchoolName = name;
            this.showUploadModal = true;
        },
        openStatus(id, name, status, notes) {
            this.activeFollowUpId = id;
            this.activeSchoolName = name;
            this.activeStatus = status || 'pending';
            this.activeNotes = notes || '';
            this.showStatusModal = true;
        }
    }">
        <!-- Header Section -->
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="rounded-xl bg-purple-100 p-2 text-purple-700 shadow-xs">
                        <i data-lucide="clipboard-list" class="h-5 w-5"></i>
                    </span>
                    <div>
                        <h1 class="text-xl font-extrabold tracking-tight text-slate-900">Hasil Kunjungan Lainnya &amp; Audiensi Dinas</h1>
                        <p class="mt-0.5 text-xs text-slate-500">Daftar kunjungan dinas, silaturahmi kepala sekolah, audiensi, penjajakan kerjasama &amp; monev terintegrasi dokumentasi Google Drive.</p>
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

                <a href="{{ route('follow-ups.index', ['agenda_category' => 'kunjungan_lainnya']) }}"
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

        <!-- KPI Summary Cards -->
        <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-5">
            <a href="{{ route('other-visits.completed.index') }}" 
               class="rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm transition hover:border-slate-300 {{ !request()->filled('status') ? 'ring-2 ring-brand-500/30' : '' }}">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Total Kunjungan</p>
                <p class="mt-1 text-2xl font-black text-slate-900">{{ number_format($totalVisits) }}</p>
                <p class="text-[11px] text-slate-400">Seluruh agenda dinas</p>
            </a>
            
            <a href="{{ route('other-visits.completed.index', ['status' => 'completed']) }}" 
               class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-3.5 shadow-sm transition hover:bg-emerald-50 {{ request('status') === 'completed' ? 'ring-2 ring-emerald-500' : '' }}">
                <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Tindak Lanjut Selesai
                </p>
                <p class="mt-1 text-2xl font-black text-emerald-800">{{ number_format($completedCount) }}</p>
                <p class="text-[11px] text-emerald-600 font-semibold">Telah tuntas / goal tercapai</p>
            </a>

            <a href="{{ route('other-visits.completed.index', ['status' => 'progress']) }}" 
               class="rounded-2xl border border-blue-200 bg-blue-50/50 p-3.5 shadow-sm transition hover:bg-blue-50 {{ request('status') === 'progress' ? 'ring-2 ring-blue-500' : '' }}">
                <p class="text-[10px] font-bold uppercase tracking-wider text-blue-700 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Sedang Diproses
                </p>
                <p class="mt-1 text-2xl font-black text-blue-800">{{ number_format($progressCount) }}</p>
                <p class="text-[11px] text-blue-600 font-semibold">Proses koordinasi dinas</p>
            </a>

            <a href="{{ route('other-visits.completed.index', ['status' => 'pending']) }}" 
               class="rounded-2xl border border-amber-200 bg-amber-50/50 p-3.5 shadow-sm transition hover:bg-amber-50 {{ request('status') === 'pending' ? 'ring-2 ring-amber-500' : '' }}">
                <p class="text-[10px] font-bold uppercase tracking-wider text-amber-700 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Perlu Tindak Lanjut
                </p>
                <p class="mt-1 text-2xl font-black text-amber-800">{{ number_format($pendingCount) }}</p>
                <p class="text-[11px] text-amber-600 font-semibold">Menunggu follow up</p>
            </a>

            <div class="rounded-2xl border border-purple-200 bg-purple-50/50 p-3.5 shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-wider text-purple-700 flex items-center gap-1">
                    <i data-lucide="image" class="w-3 h-3 text-purple-600"></i> Ada Dokumentasi
                </p>
                <p class="mt-1 text-2xl font-black text-purple-800">{{ number_format($hasDocCount) }}</p>
                <p class="text-[11px] text-purple-600 font-semibold">Foto &amp; arsip tersimpan</p>
            </div>
        </div>

        <!-- Filter Pills & Search Bar -->
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
                <a href="{{ route('other-visits.completed.index', array_filter(['search' => request('search')])) }}"
                   class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap {{ !request()->filled('status') ? 'bg-slate-900 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                    Semua ({{ $totalVisits }})
                </a>
                <a href="{{ route('other-visits.completed.index', array_filter(['status' => 'completed', 'search' => request('search')])) }}"
                   class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap {{ request('status') === 'completed' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                    Selesai ({{ $completedCount }})
                </a>
                <a href="{{ route('other-visits.completed.index', array_filter(['status' => 'progress', 'search' => request('search')])) }}"
                   class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap {{ request('status') === 'progress' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                    Sedang Diproses ({{ $progressCount }})
                </a>
                <a href="{{ route('other-visits.completed.index', array_filter(['status' => 'pending', 'search' => request('search')])) }}"
                   class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap {{ request('status') === 'pending' ? 'bg-amber-600 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                    Perlu Tindak Lanjut ({{ $pendingCount }})
                </a>
            </div>

            <!-- Search Bar -->
            <form method="GET" action="{{ route('other-visits.completed.index') }}" class="flex items-center gap-2">
                @if(request()->filled('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="relative w-full sm:w-64">
                    <input type="search" name="search" value="{{ request('search') }}"
                        placeholder="Cari sekolah, kecamatan, PIC..."
                        class="w-full rounded-xl border border-slate-200 bg-white pl-9 pr-3.5 py-2 text-xs shadow-xs focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20">
                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-2.5 pointer-events-none"></i>
                </div>
                <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white transition hover:bg-slate-800">
                    Cari
                </button>
                @if(request()->filled('search'))
                    <a href="{{ route('other-visits.completed.index', array_filter(['status' => request('status')])) }}"
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
                            <th class="px-4 py-3">Tanggal Kunjungan</th>
                            <th class="px-4 py-3">Pejabat / Pihak Ditemui</th>
                            <th class="px-4 py-3">Petugas Lapangan</th>
                            <th class="px-4 py-3">Hasil Kunjungan &amp; Resume Dinas</th>
                            <th class="px-4 py-3">Status Tindak Lanjut</th>
                            <th class="px-4 py-3 text-center">Dokumentasi &amp; Drive</th>
                            <th class="px-4 py-3 text-center">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($otherVisits as $item)
                            @php
                                $visit = $item->visit;
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

                                <!-- Tanggal Kunjungan -->
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div class="font-bold text-slate-800">
                                        {{ $visitDate ? \Carbon\Carbon::parse($visitDate)->format('d M Y') : '-' }}
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        {{ $visitDate ? \Carbon\Carbon::parse($visitDate)->diffForHumans() : '' }}
                                    </div>
                                </td>

                                <!-- Pihak Ditemui & Kontak -->
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

                                <!-- Petugas Lapangan -->
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div class="font-semibold text-slate-800">{{ $item->user?->name ?? ($visit?->user?->name ?? 'Petugas') }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $item->user?->role?->name ?? 'Staf Lapangan' }}</div>
                                </td>

                                <!-- Hasil Kunjungan / Catatan Dinas -->
                                <td class="px-4 py-3.5 max-w-[240px]">
                                    <p class="text-xs text-slate-700 font-medium line-clamp-3" title="{{ $notesText }}">
                                        {{ !empty($notesText) ? $notesText : '-' }}
                                    </p>
                                </td>

                                <!-- Status Tindak Lanjut -->
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @if($item->status === 'completed')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Selesai / Tuntas
                                        </span>
                                    @elseif($item->status === 'progress')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                            Sedang Diproses
                                        </span>
                                    @elseif($item->status === 'failed')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-slate-100 text-slate-700 border border-slate-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                                            Ditutup
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Perlu Tindak Lanjut
                                        </span>
                                    @endif
                                </td>

                                <!-- Dokumentasi & Google Drive -->
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    <div class="inline-flex flex-col items-center gap-1">
                                        @if($docFile)
                                            @php
                                                $isImg = preg_match('/\.(jpg|jpeg|png|webp)$/i', $docFile);
                                                $fileUrl = asset('storage/' . $docFile);
                                            @endphp
                                            @if($isImg)
                                                <button type="button"
                                                    @click="previewTitle = '{{ addslashes($item->school?->name ?? 'Dokumentasi Audiensi') }}'; previewSrc = '{{ $fileUrl }}'; previewOpen = true;"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-purple-50 border border-purple-200 text-purple-700 font-bold text-[11px] hover:bg-purple-100 transition">
                                                    <i data-lucide="image" class="w-3 h-3 text-purple-600"></i>
                                                    <span>Lihat Foto</span>
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
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 text-slate-600 font-bold text-[10px] hover:bg-purple-50 hover:text-purple-700 hover:border-purple-200 transition">
                                                <i data-lucide="upload" class="w-2.5 h-2.5"></i>
                                                <span>Upload Foto</span>
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
                                        <!-- Tombol Update Hasil / Status -->
                                        <button type="button"
                                            @click="openStatus({{ $item->id }}, '{{ addslashes($item->school?->name ?? 'Sekolah') }}', '{{ $item->status }}', '{{ addslashes($notesText) }}')"
                                            class="p-1.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-800 border border-purple-200 transition"
                                            title="Ubah Hasil Audiensi & Status">
                                            <i data-lucide="clipboard-edit" class="w-3.5 h-3.5"></i>
                                        </button>

                                        <!-- Tombol Upload Dokumentasi -->
                                        <button type="button"
                                            @click="openUpload({{ $item->id }}, '{{ addslashes($item->school?->name ?? 'Sekolah') }}')"
                                            class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition"
                                            title="Upload / Ganti Dokumentasi Google Drive">
                                            <i data-lucide="upload-cloud" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-14 text-center">
                                    <i data-lucide="clipboard" class="mx-auto mb-2 h-8 w-8 text-slate-300"></i>
                                    <p class="font-bold text-slate-600">Belum ada kunjungan lainnya yang dicatat</p>
                                    <p class="mt-1 text-xs text-slate-400">Input kunjungan sekolah dengan kategori "Kunjungan Lainnya".</p>
                                    <a href="{{ route('visits.create') }}" class="mt-3 inline-flex text-xs font-bold text-brand-600 hover:text-brand-800">Input Kunjungan Baru</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($otherVisits->hasPages())
                <div class="border-t border-slate-100 p-4">
                    {{ $otherVisits->links() }}
                </div>
            @endif
        </div>

        <!-- Modal Update Status & Catatan Kunjungan Dinas -->
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
                        <span class="p-1.5 rounded-xl bg-purple-600 text-white">
                            <i data-lucide="clipboard-edit" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900">Update Status &amp; Resume Kunjungan</h3>
                            <p class="text-[11px] text-slate-500" x-text="'Sekolah: ' + activeSchoolName"></p>
                        </div>
                    </div>
                    <button @click="showStatusModal = false" type="button" class="text-slate-400 hover:text-slate-700 p-1">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form :action="'/other-visits-completed/' + activeFollowUpId + '/status'" method="POST" class="space-y-4 text-xs">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block font-bold text-slate-700 mb-2">Status Progres Kunjungan <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-1 gap-2">
                            <label class="flex items-center gap-3 p-2.5 rounded-xl border border-slate-200 hover:bg-emerald-50/50 cursor-pointer transition"
                                   :class="activeStatus === 'completed' ? 'border-emerald-500 bg-emerald-50/80 font-bold text-emerald-900 ring-1 ring-emerald-500' : ''">
                                <input type="radio" name="status" value="completed" x-model="activeStatus" class="text-emerald-600 focus:ring-emerald-500">
                                <div>
                                    <span class="font-bold text-emerald-800">Tindak Lanjut Selesai / Tuntas</span>
                                    <p class="text-[10px] text-slate-500 font-normal">Hasil pertemuan selesai dan tujuan kunjungan sudah tercapai.</p>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 p-2.5 rounded-xl border border-slate-200 hover:bg-blue-50/50 cursor-pointer transition"
                                   :class="activeStatus === 'progress' ? 'border-blue-500 bg-blue-50/80 font-bold text-blue-900 ring-1 ring-blue-500' : ''">
                                <input type="radio" name="status" value="progress" x-model="activeStatus" class="text-blue-600 focus:ring-blue-500">
                                <div>
                                    <span class="font-bold text-blue-800">Sedang Diproses</span>
                                    <p class="text-[10px] text-slate-500 font-normal">Memerlukan koordinasi lanjutan atau draft dokumen kerjasama.</p>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 p-2.5 rounded-xl border border-slate-200 hover:bg-amber-50/50 cursor-pointer transition"
                                   :class="activeStatus === 'pending' ? 'border-amber-500 bg-amber-50/80 font-bold text-amber-900 ring-1 ring-amber-500' : ''">
                                <input type="radio" name="status" value="pending" x-model="activeStatus" class="text-amber-600 focus:ring-amber-500">
                                <div>
                                    <span class="font-bold text-amber-800">Perlu Tindak Lanjut</span>
                                    <p class="text-[10px] text-slate-500 font-normal">Menunggu kabar dari pihak sekolah atau pimpinan.</p>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer transition"
                                   :class="activeStatus === 'failed' ? 'border-slate-500 bg-slate-100 font-bold text-slate-900 ring-1 ring-slate-500' : ''">
                                <input type="radio" name="status" value="failed" x-model="activeStatus" class="text-slate-600 focus:ring-slate-500">
                                <div>
                                    <span class="font-bold text-slate-800">Ditutup</span>
                                    <p class="text-[10px] text-slate-500 font-normal">Agenda dinas telah diarsipkan atau tidak dilanjutkan.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Catatan Hasil Audiensi / Ringkasan Pertemuan (Opsional)</label>
                        <textarea name="notes" rows="3" x-model="activeNotes" placeholder="Contoh: Silaturahmi kepala sekolah membahas penjajakan beasiswa..." class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:border-purple-500"></textarea>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2">
                        <button @click="showStatusModal = false" type="button" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-md transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Upload Dokumentasi Kunjungan Dinas -->
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
                        <span class="p-1.5 rounded-xl bg-purple-600 text-white">
                            <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900">Upload Dokumentasi Kunjungan Dinas</h3>
                            <p class="text-[11px] text-slate-500" x-text="'Sekolah: ' + activeSchoolName"></p>
                        </div>
                    </div>
                    <button @click="showUploadModal = false" type="button" class="text-slate-400 hover:text-slate-700 p-1">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form :action="'/other-visits-completed/' + activeFollowUpId + '/documentation'" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                    @csrf

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">
                            Foto Kegiatan / Arsip Dokumen Pertemuan <span class="text-rose-500">*</span>
                        </label>
                        <input type="file" name="documentation" required accept="image/*,.pdf,.zip,.rar"
                            class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-800 hover:file:bg-purple-100 border border-slate-300 rounded-xl p-1.5 focus:border-purple-500">
                        <p class="mt-1 text-[10px] text-slate-400">Format: JPG, PNG, PDF. Otomatis disinkronkan ke folder Google Drive &amp; Data Kunjungan.</p>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                        <textarea name="notes" rows="2" placeholder="Contoh: Foto bersama kepala sekolah dan wakasek kurikulum..." class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:border-purple-500"></textarea>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2">
                        <button @click="showUploadModal = false" type="button" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-md flex items-center gap-1.5 transition">
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