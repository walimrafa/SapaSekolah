<x-app-layout title="Master Program Studi">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <span>Sistem & Master</span>
                <span>/</span>
                <span class="text-brand-600 font-semibold">Master Program Studi</span>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Master Program Studi Kampus</h1>
            <p class="text-xs text-slate-500">Kelola daftar Program Studi serta pilihan Waktu Kuliah (Reguler & Karyawan) yang dibuka untuk calon mahasiswa</p>
        </div>
    </div>

    @php
        $canManagePrograms = auth()->user()->isAdmin() || auth()->user()->hasRole('Pimpinan');
    @endphp

    <div class="grid grid-cols-1 {{ $canManagePrograms ? 'lg:grid-cols-12' : '' }} gap-6">
        <!-- Table List (8 Cols) -->
        <div class="{{ $canManagePrograms ? 'lg:col-span-8' : 'w-full' }} bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Daftar Program Studi & Waktu Kuliah</h3>
                    <p class="text-[11px] text-slate-500">Program studi dan shift perkuliahan yang dibuka dipisah per baris pilihan</p>
                </div>
                @php
                    $totalSeparatedRows = 0;
                    foreach($programs as $progItem) {
                        $c = count($progItem->getActiveStudyTimesDetails());
                        $totalSeparatedRows += ($c > 0 ? $c : 1);
                    }
                @endphp
                <span class="px-2.5 py-1 text-xs font-bold bg-brand-50 text-brand-700 rounded-lg">
                    {{ $totalSeparatedRows }} Pilihan Shift ({{ $programs->count() }} Prodi)
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-900 text-white font-bold uppercase">
                        <tr>
                            <th class="py-3.5 px-4">Kode</th>
                            <th class="py-3.5 px-4">Nama Program Studi</th>
                            <th class="py-3.5 px-4">Fakultas</th>
                            <th class="py-3.5 px-4">Waktu Kuliah Dibuka</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            @if($canManagePrograms)
                                <th class="py-3.5 px-4 text-center">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @php
                            $renderedAny = false;
                        @endphp
                        @foreach($programs as $p)
                            @php
                                $activeTimes = $p->getActiveStudyTimesDetails();
                            @endphp
                            @if(!empty($activeTimes))
                                @foreach($activeTimes as $timeKey => $t)
                                    @php $renderedAny = true; @endphp
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-3.5 px-4 font-mono font-bold text-brand-600">{{ $p->code }}</td>
                                        <td class="py-3.5 px-4">
                                            <div class="font-bold text-slate-900">{{ $p->name }}</div>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-600">{{ $p->faculty ?? '-' }}</td>
                                        <td class="py-3.5 px-4">
                                            <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-md text-[10px] font-semibold border {{ $t['badge_class'] }} w-fit shadow-xs">
                                                @if($t['icon'] === 'sun')
                                                    <i data-lucide="sun" class="w-3.5 h-3.5 text-amber-600"></i>
                                                @elseif($t['icon'] === 'moon')
                                                    <i data-lucide="moon" class="w-3.5 h-3.5 text-indigo-600"></i>
                                                @else
                                                    <i data-lucide="briefcase" class="w-3.5 h-3.5 text-emerald-600"></i>
                                                @endif
                                                <span>{{ $t['label'] }}</span>
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            @if($p->status)
                                                <span class="px-2 py-0.5 text-[10px] font-bold bg-emerald-100 text-emerald-800 rounded-full">Aktif</span>
                                            @else
                                                <span class="px-2 py-0.5 text-[10px] font-bold bg-red-100 text-red-800 rounded-full">Nonaktif</span>
                                            @endif
                                        </td>
                                        @if($canManagePrograms)
                                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                                <div class="flex items-center justify-center space-x-1.5">
                                                    <!-- Edit Button -->
                                                    <button type="button" 
                                                        onclick="openEditProgramModal({{ json_encode($p) }})"
                                                        class="p-1.5 bg-brand-50 hover:bg-brand-100 text-brand-600 rounded-lg transition"
                                                        title="Edit Program Studi & Waktu Kuliah">
                                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                                    </button>

                                                    <!-- Delete Shift or Entire Prodi -->
                                                    @if(count($activeTimes) > 1)
                                                        <form method="POST" action="{{ route('study-programs.destroy', $p->id) }}" onsubmit="return confirm('Hapus waktu kuliah \'{{ $t['label'] }}\' untuk prodi {{ $p->name }}?')" class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <input type="hidden" name="study_time" value="{{ $timeKey }}">
                                                            <button type="submit" class="p-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition" title="Hapus Waktu Kuliah {{ $t['label'] }}">
                                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form method="POST" action="{{ route('study-programs.destroy', $p->id) }}" onsubmit="return confirm('Hapus Program Studi {{ $p->name }}?')" class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="p-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition" title="Hapus Program Studi">
                                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            @else
                                @php $renderedAny = true; @endphp
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3.5 px-4 font-mono font-bold text-brand-600">{{ $p->code }}</td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900">{{ $p->name }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-600">{{ $p->faculty ?? '-' }}</td>
                                    <td class="py-3.5 px-4">
                                        <span class="text-slate-400 italic text-[11px]">Belum diatur</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        @if($p->status)
                                            <span class="px-2 py-0.5 text-[10px] font-bold bg-emerald-100 text-emerald-800 rounded-full">Aktif</span>
                                        @else
                                            <span class="px-2 py-0.5 text-[10px] font-bold bg-red-100 text-red-800 rounded-full">Nonaktif</span>
                                        @endif
                                    </td>
                                    @if($canManagePrograms)
                                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center space-x-1.5">
                                                <button type="button" 
                                                    onclick="openEditProgramModal({{ json_encode($p) }})"
                                                    class="p-1.5 bg-brand-50 hover:bg-brand-100 text-brand-600 rounded-lg transition"
                                                    title="Edit Program Studi & Waktu Kuliah">
                                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                                </button>
                                                <form method="POST" action="{{ route('study-programs.destroy', $p->id) }}" onsubmit="return confirm('Hapus prodi {{ $p->name }}?')" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition" title="Hapus Prodi">
                                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endif
                        @endforeach

                        @if(!$renderedAny)
                            <tr>
                                <td colspan="{{ $canManagePrograms ? 6 : 5 }}" class="py-8 text-center text-slate-400">Belum ada Program Studi.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        @if($canManagePrograms)
        <!-- Add Form (4 Cols) -->
        <div class="lg:col-span-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft h-fit">
            <div class="flex items-center space-x-2 mb-4 pb-2 border-b border-slate-100">
                <i data-lucide="plus-circle" class="w-4 h-4 text-brand-600"></i>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Tambah Program Studi</h3>
            </div>

            <form method="POST" action="{{ route('study-programs.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kode Prodi <span class="text-red-500">*</span></label>
                    <input type="text" name="code" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500" placeholder="TI, SI, MN, dll">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Program Studi <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500" placeholder="Teknik Informatika">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Fakultas</label>
                    <input type="text" name="faculty" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500" placeholder="Fakultas Teknik">
                </div>

                <!-- Section: Waktu Kuliah yang Dibuka -->
                <div class="pt-2 border-t border-slate-100">
                    <label class="block text-xs font-bold text-slate-800 mb-1">Waktu Kuliah yang Dibuka</label>
                    <p class="text-[11px] text-slate-500 mb-3">Centang pilihan waktu/kelas kuliah yang tersedia untuk prodi ini:</p>

                    <div class="space-y-2.5">
                        <!-- 1. Reguler Pagi - Siang -->
                        <label class="flex items-start p-2.5 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-amber-50/40 hover:border-amber-300 transition cursor-pointer">
                            <input type="checkbox" name="study_times[]" value="reguler_pagi" checked class="mt-0.5 rounded text-amber-600 focus:ring-amber-500 border-slate-300">
                            <div class="ml-2.5 text-xs">
                                <span class="font-bold text-slate-900 flex items-center gap-1.5">
                                    <i data-lucide="sun" class="w-3.5 h-3.5 text-amber-500"></i>
                                    Reguler (Pagi - Siang)
                                </span>
                                <span class="text-[10px] text-slate-500 block mt-0.5">Kuliah reguler sesi pagi s/d siang (Senin - Jumat)</span>
                            </div>
                        </label>

                        <!-- 2. Reguler Sore - Malam -->
                        <label class="flex items-start p-2.5 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-indigo-50/40 hover:border-indigo-300 transition cursor-pointer">
                            <input type="checkbox" name="study_times[]" value="reguler_malam" checked class="mt-0.5 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300">
                            <div class="ml-2.5 text-xs">
                                <span class="font-bold text-slate-900 flex items-center gap-1.5">
                                    <i data-lucide="moon" class="w-3.5 h-3.5 text-indigo-500"></i>
                                    Reguler (Sore - Malam)
                                </span>
                                <span class="text-[10px] text-slate-500 block mt-0.5">Kuliah reguler sesi sore s/d malam (Senin - Jumat)</span>
                            </div>
                        </label>

                        <!-- 3. Karyawan Jumat - Sabtu -->
                        <label class="flex items-start p-2.5 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-emerald-50/40 hover:border-emerald-300 transition cursor-pointer">
                            <input type="checkbox" name="study_times[]" value="karyawan" checked class="mt-0.5 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                            <div class="ml-2.5 text-xs">
                                <span class="font-bold text-slate-900 flex items-center gap-1.5">
                                    <i data-lucide="briefcase" class="w-3.5 h-3.5 text-emerald-600"></i>
                                    Waktu Kuliah Karyawan (Jumat - Sabtu)
                                </span>
                                <span class="text-[10px] text-slate-500 block mt-0.5">Kelas khusus karyawan/pekerja di akhir pekan</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Status Prodi</label>
                    <select name="status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>

                <button type="submit" class="w-full py-2.5 bg-brand-navy hover:bg-brand-900 text-white font-bold text-xs rounded-xl shadow-soft hover:shadow-md transition">
                    Simpan Program Studi
                </button>
            </form>
        </div>
        @endif
    </div>

    <!-- ==================== MODAL EDIT PROGRAM STUDI ==================== -->
    <div id="modalEditProgram" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center">
                        <i data-lucide="edit" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Edit Program Studi & Waktu Kuliah</h3>
                        <p class="text-[11px] text-slate-500">Perbarui informasi prodi dan shift kuliah yang dibuka</p>
                    </div>
                </div>
                <button type="button" onclick="closeEditProgramModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="formEditProgram" method="POST" action="" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kode Prodi <span class="text-red-500">*</span></label>
                        <input type="text" id="edit_code" name="code" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Program Studi <span class="text-red-500">*</span></label>
                        <input type="text" id="edit_name" name="name" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Fakultas</label>
                        <input type="text" id="edit_faculty" name="faculty" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status</label>
                        <select id="edit_status" name="status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <!-- Section: Waktu Kuliah yang Dibuka (Edit) -->
                <div class="pt-3 border-t border-slate-100">
                    <label class="block text-xs font-bold text-slate-800 mb-1">Waktu Kuliah yang Dibuka</label>
                    <p class="text-[11px] text-slate-500 mb-2.5">Tentukan waktu/shift kuliah yang tersedia untuk prodi ini:</p>

                    <div class="space-y-2">
                        <!-- 1. Reguler Pagi - Siang -->
                        <label class="flex items-start p-2.5 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-amber-50/40 hover:border-amber-300 transition cursor-pointer">
                            <input type="checkbox" id="edit_time_reguler_pagi" name="study_times[]" value="reguler_pagi" class="mt-0.5 rounded text-amber-600 focus:ring-amber-500 border-slate-300">
                            <div class="ml-2.5 text-xs">
                                <span class="font-bold text-slate-900 flex items-center gap-1.5">
                                    <i data-lucide="sun" class="w-3.5 h-3.5 text-amber-500"></i>
                                    Reguler (Pagi - Siang)
                                </span>
                                <span class="text-[10px] text-slate-500 block mt-0.5">Kuliah reguler sesi pagi s/d siang (Senin - Jumat)</span>
                            </div>
                        </label>

                        <!-- 2. Reguler Sore - Malam -->
                        <label class="flex items-start p-2.5 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-indigo-50/40 hover:border-indigo-300 transition cursor-pointer">
                            <input type="checkbox" id="edit_time_reguler_malam" name="study_times[]" value="reguler_malam" class="mt-0.5 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300">
                            <div class="ml-2.5 text-xs">
                                <span class="font-bold text-slate-900 flex items-center gap-1.5">
                                    <i data-lucide="moon" class="w-3.5 h-3.5 text-indigo-500"></i>
                                    Reguler (Sore - Malam)
                                </span>
                                <span class="text-[10px] text-slate-500 block mt-0.5">Kuliah reguler sesi sore s/d malam (Senin - Jumat)</span>
                            </div>
                        </label>

                        <!-- 3. Karyawan Jumat - Sabtu -->
                        <label class="flex items-start p-2.5 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-emerald-50/40 hover:border-emerald-300 transition cursor-pointer">
                            <input type="checkbox" id="edit_time_karyawan" name="study_times[]" value="karyawan" class="mt-0.5 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                            <div class="ml-2.5 text-xs">
                                <span class="font-bold text-slate-900 flex items-center gap-1.5">
                                    <i data-lucide="briefcase" class="w-3.5 h-3.5 text-emerald-600"></i>
                                    Waktu Kuliah Karyawan (Jumat - Sabtu)
                                </span>
                                <span class="text-[10px] text-slate-500 block mt-0.5">Kelas khusus karyawan/pekerja di akhir pekan (Jumat - Sabtu)</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                    <button type="button" onclick="deleteEntireProgram()" class="px-3 py-2 text-xs font-bold text-red-600 hover:bg-red-50 rounded-xl transition flex items-center gap-1.5">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        <span>Hapus Seluruh Prodi</span>
                    </button>
                    <div class="flex items-center space-x-2">
                        <button type="button" onclick="closeEditProgramModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold bg-brand-navy hover:bg-brand-900 text-white rounded-xl shadow-soft hover:shadow-md transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        let currentEditingProgram = null;

        function openEditProgramModal(program) {
            currentEditingProgram = program;
            const form = document.getElementById('formEditProgram');
            form.action = `/study-programs/${program.id}`;

            document.getElementById('edit_code').value = program.code || '';
            document.getElementById('edit_name').value = program.name || '';
            document.getElementById('edit_faculty').value = program.faculty || '';
            document.getElementById('edit_status').value = program.status ? '1' : '0';

            // Checkbox waktu kuliah
            const times = Array.isArray(program.study_times) ? program.study_times : [];
            document.getElementById('edit_time_reguler_pagi').checked = times.includes('reguler_pagi');
            document.getElementById('edit_time_reguler_malam').checked = times.includes('reguler_malam');
            document.getElementById('edit_time_karyawan').checked = times.includes('karyawan');

            document.getElementById('modalEditProgram').classList.remove('hidden');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        function closeEditProgramModal() {
            document.getElementById('modalEditProgram').classList.add('hidden');
        }

        function deleteEntireProgram() {
            if (!currentEditingProgram) return;
            if (confirm(`Apakah Anda yakin ingin menghapus seluruh data Program Studi ${currentEditingProgram.name} beserta semua waktu kuliahnya?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/study-programs/${currentEditingProgram.id}`;

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                form.innerHTML = `
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <input type="hidden" name="_method" value="DELETE">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close on backdrop click
        document.getElementById('modalEditProgram')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditProgramModal();
            }
        });
    </script>
    @endpush
</x-app-layout>
