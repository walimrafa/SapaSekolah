<x-app-layout title="Sekolah Sudah Sosialisasi">
    <div x-data="{
        showUploadModal: false,
        activeFollowUpId: null,
        activeSchoolName: '',
        previewOpen: false,
        previewSrc: '',
        previewTitle: '',
        openUpload(id, name) {
            this.activeFollowUpId = id;
            this.activeSchoolName = name;
            this.showUploadModal = true;
        }
    }">
        <!-- Header Section -->
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="rounded-xl bg-emerald-100 p-2 text-emerald-700 shadow-xs">
                        <i data-lucide="presentation" class="h-5 w-5"></i>
                    </span>
                    <div>
                        <h1 class="text-xl font-extrabold tracking-tight text-slate-900">Sekolah Sudah Sosialisasi</h1>
                        <p class="mt-0.5 text-xs text-slate-500">Daftar sekolah yang presentasi sosialisasinya sudah terlaksana, terintegrasi dokumentasi Google Drive &amp; Data Kunjungan.</p>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                <!-- Buka Root Google Drive Dokumentasi (Item 8) -->
                <a href="https://drive.google.com/drive/folders/1DQkSEomL8cacePKG8Ex72JldQABKndiD?usp=sharing"
                    target="_blank"
                    class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-amber-200 bg-amber-50 px-3.5 py-2.5 text-xs font-bold text-amber-900 shadow-sm transition hover:bg-amber-100">
                    <i data-lucide="folder" class="h-4 w-4 text-amber-600"></i>
                    <span>Google Drive Dokumentasi</span>
                    <i data-lucide="external-link" class="h-3 w-3 text-amber-700"></i>
                </a>

                <a href="{{ route('follow-ups.index') }}"
                    class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                    <i data-lucide="arrow-left" class="h-4 w-4"></i>
                    Kembali ke Follow Up
                </a>
            </div>
        </div>

        @if(session('toast'))
            <div class="mb-6 flex items-center gap-3 rounded-2xl border {{ (session('toast')['type'] ?? '') === 'error' ? 'border-rose-200 bg-rose-50 text-rose-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800' }} p-4 text-sm font-semibold shadow-sm">
                <i data-lucide="{{ (session('toast')['type'] ?? '') === 'error' ? 'alert-circle' : 'circle-check' }}" class="h-5 w-5 shrink-0"></i>
                <span>{{ session('toast')['message'] }}</span>
            </div>
        @endif

        <!-- KPI Summary Cards -->
        <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="rounded-2xl border border-emerald-200 bg-white p-4 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Total Kegiatan Terlaksana</p>
                <p class="mt-1 text-2xl font-black text-slate-900">{{ number_format($totalCount) }}</p>
                <p class="text-xs text-slate-500">Catatan sosialisasi yang sudah dikonfirmasi</p>
            </div>
            <div class="rounded-2xl border border-blue-200 bg-white p-4 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-wider text-blue-700">Estimasi Peserta</p>
                <p class="mt-1 text-2xl font-black text-slate-900">{{ number_format($totalAudience) }}</p>
                <p class="text-xs text-slate-500">Jumlah target peserta dari jadwal</p>
            </div>
        </div>

        <!-- Search Bar -->
        <form method="GET" action="{{ route('socializations.completed.index') }}" class="mb-5 flex flex-col gap-2 sm:flex-row">
            <label for="search" class="sr-only">Cari sekolah atau PIC</label>
            <input id="search" type="search" name="search" value="{{ request('search') }}"
                placeholder="Cari nama sekolah, kecamatan, atau PIC..."
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
            <button type="submit" class="rounded-xl bg-brand-navy px-5 py-2.5 text-xs font-bold text-white transition hover:bg-slate-800">
                Cari
            </button>
            @if(request()->filled('search'))
                <a href="{{ route('socializations.completed.index') }}"
                    class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-center text-xs font-bold text-slate-600 transition hover:bg-slate-50">
                    Reset
                </a>
            @endif
        </form>

        <!-- Table Card -->
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1000px] text-left text-xs">
                    <thead class="bg-slate-50 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Sekolah &amp; Kecamatan</th>
                            <th class="px-4 py-3">Tanggal Terlaksana</th>
                            <th class="px-4 py-3">Metode / Lokasi</th>
                            <th class="px-4 py-3">PIC &amp; Marketing</th>
                            <th class="px-4 py-3 text-center">Dokumentasi &amp; Google Drive</th>
                            <th class="px-4 py-3">Catatan Kegiatan</th>
                            <th class="px-4 py-3 text-center">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($completedSocializations as $item)
                            @php
                                $docPath = $item->documentation_file ?: ($item->visit?->photo);
                                $hasDoc = !empty($docPath);
                                $docExt = $hasDoc ? strtolower(pathinfo($docPath, PATHINFO_EXTENSION)) : '';
                                $isImg = in_array($docExt, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp']);
                                $docUrl = $hasDoc ? asset('storage/' . $docPath) : null;
                                $driveUrl = $item->drive_folder_url;
                            @endphp
                            <tr class="align-top hover:bg-slate-50/70 transition">
                                <!-- Sekolah & Kecamatan -->
                                <td class="px-4 py-4">
                                    <p class="font-extrabold text-slate-900">{{ $item->school?->name ?? 'Sekolah dihapus' }}</p>
                                    <p class="mt-1 text-[11px] text-slate-500">Kec. {{ $item->school?->district?->name ?? '-' }}</p>
                                    @if($item->visit)
                                        <a href="{{ route('visits.index', ['search' => $item->school?->name]) }}" class="inline-block mt-1 text-[10px] font-bold text-brand-600 hover:underline">
                                            ↗ Lihat Data Kunjungan
                                        </a>
                                    @endif
                                </td>

                                <!-- Tanggal Terlaksana -->
                                <td class="whitespace-nowrap px-4 py-4 font-bold text-emerald-800">
                                    {{ $item->socialization_completed_at ? \Carbon\Carbon::parse($item->socialization_completed_at)->translatedFormat('d F Y') : '-' }}
                                    @if($item->fixed_date)
                                        <p class="mt-1 text-[10px] font-medium text-slate-400">Jadwal: {{ \Carbon\Carbon::parse($item->fixed_date)->translatedFormat('d M Y') }} {{ $item->fixed_time }}</p>
                                    @endif
                                </td>

                                <!-- Metode / Lokasi -->
                                <td class="px-4 py-4 text-slate-700">
                                    {{ $item->socialization_location ?: ($item->visit?->socialization_method ?: 'Sosialisasi') }}
                                    @if($item->target_audience_count)
                                        <p class="mt-1 text-[10px] text-slate-500">{{ number_format($item->target_audience_count) }} estimasi peserta</p>
                                    @endif
                                </td>

                                <!-- PIC & Marketing -->
                                <td class="px-4 py-4">
                                    <p class="font-semibold text-slate-800">{{ $item->pic_name ?: 'PIC sekolah' }}</p>
                                    <p class="mt-1 text-[10px] text-slate-500">{{ $item->user?->name ?? 'Marketing tidak tersedia' }}</p>
                                </td>

                                <!-- Dokumentasi & Google Drive (Item 8) -->
                                <td class="px-4 py-4 text-center whitespace-nowrap">
                                    <div class="flex flex-col items-center gap-1.5">
                                        @if($hasDoc)
                                            <div class="flex items-center gap-1.5">
                                                @if($isImg)
                                                    <button type="button"
                                                        @click="previewOpen = true; previewSrc = '{{ $docUrl }}'; previewTitle = '{{ addslashes($item->school?->name ?? 'Dokumentasi') }}'"
                                                        class="group relative inline-block focus:outline-none cursor-pointer"
                                                        title="Lihat Pratinjau Foto">
                                                        <img src="{{ $docUrl }}" alt="Dokumentasi" class="w-9 h-9 object-cover rounded-xl border border-slate-200 shadow-2xs group-hover:scale-105 transition">
                                                    </button>
                                                @else
                                                    <a href="{{ $docUrl }}" target="_blank" download class="inline-flex items-center gap-1 px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[10px] font-bold">
                                                        <i data-lucide="file" class="w-3 h-3"></i>
                                                        <span class="uppercase">{{ $docExt }}</span>
                                                    </a>
                                                @endif

                                                <button type="button"
                                                    @click="openUpload({{ $item->id }}, '{{ addslashes($item->school?->name ?? 'Sekolah') }}')"
                                                    title="Ganti Berkas Dokumentasi"
                                                    class="p-1 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-slate-100 transition">
                                                    <i data-lucide="refresh-cw" class="w-3 h-3"></i>
                                                </button>
                                            </div>
                                        @else
                                            <!-- Tombol Upload Dokumentasi -->
                                            <button type="button"
                                                @click="openUpload({{ $item->id }}, '{{ addslashes($item->school?->name ?? 'Sekolah') }}')"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-[10px] font-bold transition">
                                                <i data-lucide="upload" class="w-3 h-3"></i>
                                                <span>Upload Foto/Berkas</span>
                                            </button>
                                        @endif

                                        <!-- Direct Link to School Folder in Google Drive (Item 8) -->
                                        <a href="{{ $driveUrl }}" target="_blank"
                                            title="Buka Folder Google Drive Sekolah Ini"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-300 text-[10px] font-bold transition shadow-2xs">
                                            <i data-lucide="folder" class="w-3 h-3 text-amber-600"></i>
                                            <span>Folder Google Drive</span>
                                            <i data-lucide="external-link" class="w-2.5 h-2.5 text-amber-700"></i>
                                        </a>
                                    </div>
                                </td>

                                <!-- Catatan Kegiatan -->
                                <td class="max-w-xs whitespace-normal px-4 py-4 text-slate-600">
                                    {{ $item->socialization_completion_notes ?: 'Tidak ada catatan kegiatan.' }}
                                </td>

                                <!-- Tindakan -->
                                <td class="px-4 py-4 text-center whitespace-nowrap">
                                    <form method="POST" action="{{ route('socializations.completed.reopen', $item) }}"
                                        onsubmit="return confirm('Batalkan konfirmasi kegiatan ini? Data akan kembali ke daftar Jadwal FIX.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="whitespace-nowrap rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1.5 text-[10px] font-bold text-amber-800 transition hover:bg-amber-100">
                                            Batalkan
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-14 text-center">
                                    <i data-lucide="presentation" class="mx-auto mb-2 h-8 w-8 text-slate-300"></i>
                                    <p class="font-bold text-slate-600">Belum ada sosialisasi yang dikonfirmasi</p>
                                    <p class="mt-1 text-xs text-slate-400">Konfirmasi kegiatan dari kartu Jadwal FIX di menu Follow Up.</p>
                                    <a href="{{ route('follow-ups.index') }}" class="mt-3 inline-flex text-xs font-bold text-brand-600 hover:text-brand-800">Buka Follow Up Sekolah</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($completedSocializations->hasPages())
                <div class="border-t border-slate-100 p-4">
                    {{ $completedSocializations->links() }}
                </div>
            @endif
        </div>

        <!-- Modal Upload Dokumentasi ke Google Drive (Item 8) -->
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
                            <h3 class="text-sm font-extrabold text-slate-900">Upload Dokumentasi Sosialisasi</h3>
                            <p class="text-[11px] text-slate-500" x-text="'Sekolah: ' + activeSchoolName"></p>
                        </div>
                    </div>
                    <button @click="showUploadModal = false" type="button" class="text-slate-400 hover:text-slate-700 p-1">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form :action="'/socializations-completed/' + activeFollowUpId + '/documentation'" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                    @csrf

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">
                            Pilih Berkas Foto / Dokumen <span class="text-rose-500">*</span>
                        </label>
                        <input type="file" name="documentation" required accept="image/*,.pdf,.zip,.rar,.doc,.docx"
                            class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-50 file:text-amber-800 hover:file:bg-amber-100 border border-slate-300 rounded-xl p-1.5 focus:border-brand-500">
                        <p class="mt-1 text-[10px] text-slate-400">Format: JPG, PNG, PDF, RAR, ZIP. Berkas akan otomatis disinkronkan ke folder sekolah di Google Drive &amp; Data Kunjungan.</p>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Catatan Tambahan Kegiatan (Opsional)</label>
                        <textarea name="notes" rows="3" placeholder="Contoh: Dokumentasi sosialisasi di ruang Multimedia, dihadiri 120 siswa kelas 12..." class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:border-brand-500"></textarea>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2">
                        <button @click="showUploadModal = false" type="button" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-brand-navy hover:bg-slate-900 text-white font-bold text-xs shadow-md flex items-center gap-1.5 transition">
                            <i data-lucide="upload" class="w-3.5 h-3.5 text-amber-400"></i>
                            <span>Upload ke Google Drive &amp; Simpan</span>
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
                    <a :href="previewSrc" target="_blank" download class="px-3.5 py-1.5 bg-brand-navy hover:bg-slate-900 text-white font-bold rounded-xl text-xs transition flex items-center space-x-1.5">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span>Unduh Berkas</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
