<x-app-layout title="Aktivitas Marketing">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Aktivitas &amp; Event Marketing</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola Seminar, Workshop, Expo, Try Out, Sosialisasi PMB &amp; Beasiswa UBSI</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Table List (8 Cols) -->
        <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-800">Daftar Event / Aktivitas Marketing</h3>
                <span class="px-2.5 py-1 text-xs font-bold bg-brand-50 text-brand-700 rounded-lg">{{ $activities->total() }} Event</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-900 text-white font-bold uppercase">
                        <tr>
                            <th class="py-3.5 px-4">Nama Event</th>
                            <th class="py-3.5 px-4">Jenis Event</th>
                            <th class="py-3.5 px-4">Tanggal Pelaksanaan</th>
                            <th class="py-3.5 px-4 text-center">Dokumen Lampiran</th>
                            <th class="py-3.5 px-4 text-center">Keterangan</th>
                            <th class="py-3.5 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($activities as $a)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3.5 px-4 font-bold text-slate-900">
                                    {{ $a->name }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg bg-purple-50 text-purple-700 border border-purple-100">
                                        {{ $a->activity_type }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 font-semibold">
                                    {{ \Carbon\Carbon::parse($a->start_date)->format('d M Y') }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($a->document_path)
                                        @php
                                            $ext = $a->document_extension;
                                            $isArchive = in_array($ext, ['rar', 'zip', '7z']);
                                            $isPdf = ($ext === 'pdf');
                                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);
                                        @endphp
                                        <div class="inline-flex flex-col items-center gap-1">
                                            <a href="{{ $a->document_url }}" target="_blank" download class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl {{ $isArchive ? 'bg-amber-50 hover:bg-amber-100 text-amber-800 border-amber-200' : ($isPdf ? 'bg-rose-50 hover:bg-rose-100 text-rose-700 border-rose-200' : 'bg-brand-50 hover:bg-brand-100 text-brand-700 border-brand-200') }} border font-bold text-[11px] shadow-2xs transition" title="Klik untuk mengunduh dokumen: {{ $a->document_name }}">
                                                @if($isArchive)
                                                    <i data-lucide="archive" class="w-3.5 h-3.5 text-amber-600"></i>
                                                    <span class="uppercase text-[9px] font-black bg-amber-200/80 px-1 py-0.2 rounded">{{ $ext }}</span>
                                                @elseif($isPdf)
                                                    <i data-lucide="file-text" class="w-3.5 h-3.5 text-rose-600"></i>
                                                    <span class="uppercase text-[9px] font-black bg-rose-200/80 px-1 py-0.2 rounded">PDF</span>
                                                @elseif($isImage)
                                                    <i data-lucide="image" class="w-3.5 h-3.5 text-emerald-600"></i>
                                                    <span class="uppercase text-[9px] font-black bg-emerald-200/80 px-1 py-0.2 rounded">{{ $ext }}</span>
                                                @else
                                                    <i data-lucide="file" class="w-3.5 h-3.5 text-brand-600"></i>
                                                    <span class="uppercase text-[9px] font-black bg-slate-200 px-1 py-0.2 rounded">{{ $ext ?: 'DOC' }}</span>
                                                @endif
                                                <span class="max-w-[120px] truncate">{{ $a->document_name ?: 'Unduh Berkas' }}</span>
                                                <i data-lucide="download" class="w-3 h-3 opacity-60"></i>
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-xs italic">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 max-w-xs truncate">
                                    {{ $a->description ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <form method="POST" action="{{ route('activities.destroy', $a->id) }}" onsubmit="return confirm('Hapus aktivitas {{ $a->name }}?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg" title="Hapus aktivitas">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">Belum ada event marketing terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 bg-slate-50 border-t border-slate-100">
                {{ $activities->links() }}
            </div>
        </div>

        <!-- Add Form (4 Cols) -->
        <div class="lg:col-span-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft h-fit">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>Tambah Event Baru</span>
                <i data-lucide="calendar-plus" class="w-4 h-4 text-brand-600"></i>
            </h3>
            <form method="POST" action="{{ route('activities.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Event / Aktivitas <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300" placeholder="Sosialisasi Beasiswa UBSI Karawang">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Aktivitas <span class="text-red-500">*</span></label>
                    <select name="activity_type" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                        <option value="Sosialisasi PMB">Sosialisasi PMB</option>
                        <option value="Seminar">Seminar / Workshop</option>
                        <option value="Campus Visit">Campus Visit</option>
                        <option value="Expo">Education Expo</option>
                        <option value="Try Out">Try Out SBMPTN</option>
                        <option value="Beasiswa">Program Beasiswa</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                    <input type="date" name="start_date" required value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi &amp; Target Peserta</label>
                    <textarea name="description" rows="2.5" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300" placeholder="Keterangan singkat..."></textarea>
                </div>

                <!-- Upload Dokumen / Lampiran (Termasuk RAR, ZIP, PDF, dll) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                        <span>Upload Dokumen / Berkas Lampiran</span>
                        <span class="text-[10px] font-normal text-slate-400">Opsional</span>
                    </label>
                    <input type="file" name="document" accept=".rar,.zip,.7z,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,image/*" 
                           class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 border border-slate-300 rounded-xl p-1 bg-white cursor-pointer">
                    <div class="mt-1.5 flex flex-wrap items-center gap-1 text-[10px] text-slate-500">
                        <span class="font-bold text-slate-600">Format didukung:</span>
                        <span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 font-bold border border-amber-200">RAR</span>
                        <span class="px-1.5 py-0.5 rounded bg-purple-100 text-purple-800 font-bold border border-purple-200">ZIP</span>
                        <span class="px-1.5 py-0.5 rounded bg-rose-100 text-rose-800 font-bold border border-rose-200">PDF</span>
                        <span class="px-1.5 py-0.5 rounded bg-blue-100 text-blue-800 font-bold border border-blue-200">Word</span>
                        <span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold border border-emerald-200">Foto</span>
                        <span class="text-slate-400">(Maks. 50 MB)</span>
                    </div>
                    @error('document')
                        <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full py-2.5 bg-brand-navy hover:bg-slate-900 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Event Marketing</span>
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
