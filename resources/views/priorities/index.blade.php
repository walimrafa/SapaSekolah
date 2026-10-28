<x-app-layout title="Prioritas Sekolah SLTA Karawang">
    <!-- Header Title & Calculation Info -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6" x-data="{ showIntakeImport: {{ $errors->has('intake_file') ? 'true' : 'false' }} }">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Leaderboard Prioritas Sekolah SLTA</h1>
            <p class="text-xs text-slate-500 mt-0.5">Prioritas kunjungan dihitung juga dari jumlah mahasiswa baru UBSI per sekolah asal</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" @click="showIntakeImport = true" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md flex items-center space-x-2 transition">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                <span>Import Mahasiswa Baru</span>
            </button>
            <form method="POST" action="{{ route('priorities.recalculate') }}">
                @csrf
                <button type="submit" class="px-4 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold rounded-xl text-xs shadow-md shadow-amber-500/20 flex items-center space-x-2 transition">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    <span>Hitung Ulang Skor Prioritas</span>
                </button>
            </form>
        </div>

        <div x-show="showIntakeImport" x-transition.opacity style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="intake-import-title">
            <div class="fixed inset-0 bg-slate-950/55 transition-opacity" @click="showIntakeImport = false"></div>
            <div class="relative z-10 flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-xl rounded-3xl border border-slate-200 bg-white p-6 text-left shadow-2xl">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h2 id="intake-import-title" class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600"></i>
                            Import Jumlah Mahasiswa Baru per Sekolah
                        </h2>
                        <button type="button" @click="showIntakeImport = false" class="text-slate-400 hover:text-slate-600" aria-label="Tutup">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form action="{{ route('priorities.import-student-intakes') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                        @csrf
                        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-between gap-3">
                            <div>
                                <span class="font-bold text-emerald-950 block text-xs">Unduh template rekap mahasiswa baru</span>
                                <span class="text-[11px] text-emerald-800">Daftar sekolah sudah terisi; lengkapi periode dan jumlah mahasiswa baru.</span>
                            </div>
                            <a href="{{ route('priorities.student-intakes-template') }}" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shrink-0">
                                Unduh Template
                            </a>
                        </div>
                        <div>
                            <label for="intake_file" class="block text-xs font-bold text-slate-700 mb-1">File rekap mahasiswa baru <span class="text-red-500">*</span></label>
                            <input id="intake_file" type="file" name="intake_file" accept=".xlsx,.xls,.csv" required class="w-full text-xs text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 cursor-pointer">
                            @error('intake_file')
                                <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-[11px] text-amber-900 space-y-1">
                            <p class="font-bold">Isi Periode PMB dan Jumlah Mahasiswa Baru pada setiap sekolah yang memiliki data.</p>
                            <p>NPSN dan Nama Sekolah sudah diisi dari data sekolah. Satu baris untuk satu sekolah/periode.</p>
                            <p>Baris yang periode dan jumlahnya kosong dilewati. Hindari memasukkan sekolah/periode yang sama lebih dari sekali.</p>
                            <p>Impor ulang periode yang sama akan mengganti angka sebelumnya untuk sekolah dalam file.</p>
                            <p>Jumlah mahasiswa dipakai sebagai 25% skor riwayat masuk UBSI; catatan per mahasiswa CRM tidak dihitung ganda.</p>
                        </div>
                        <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="showIntakeImport = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl">Batal</button>
                            <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow flex items-center gap-1.5">
                                <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                                Import dan Hitung Prioritas
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if(session('intake_import_errors'))
        <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-2xl text-xs text-amber-950">
            <p class="font-bold mb-2">Beberapa baris tidak diimpor:</p>
            <ul class="list-disc pl-5 space-y-1">
                @foreach(session('intake_import_errors') as $importError)
                    <li>{{ $importError }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Banner Ketentuan Variabel Prioritas UBSI Karawang -->
    <div class="mb-8 p-6 rounded-3xl bg-slate-900 text-white border border-slate-800 shadow-xl relative overflow-hidden">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
            <div class="flex items-start space-x-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-400/20 border border-amber-400/30 text-amber-400 flex items-center justify-center shrink-0">
                    <i data-lucide="award" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="flex items-center space-x-2 mb-1">
                        <span class="px-2 py-0.5 text-[10px] font-extrabold bg-amber-400 text-slate-950 rounded uppercase">Sistem Prioritas UBSI</span>
                        <span class="text-xs text-slate-400 font-medium">Titik Acuan Kampus UBSI Karawang</span>
                    </div>
                    <h2 class="text-lg font-bold text-white leading-tight">Parameter Sekolah Prioritas Marketing UBSI Karawang</h2>
                    <p class="text-xs text-slate-300 mt-1 flex items-center gap-1.5">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-amber-400"></i>
                        <span>{{ $ubsiCampusAddress }}</span>
                    </p>
                </div>
            </div>

            <!-- 4 Criteria Badges -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                <div class="p-2.5 rounded-xl bg-white/10 border border-white/10 text-center">
                    <span class="text-amber-400 font-extrabold block text-sm">25%</span>
                    <span class="text-[10px] text-slate-300 font-semibold block">1. Jarak ke UBSI</span>
                </div>
                <div class="p-2.5 rounded-xl bg-white/10 border border-white/10 text-center">
                    <span class="text-amber-400 font-extrabold block text-sm">20%</span>
                    <span class="text-[10px] text-slate-300 font-semibold block">2. Sekolah Negeri</span>
                </div>
                <div class="p-2.5 rounded-xl bg-white/10 border border-white/10 text-center">
                    <span class="text-amber-400 font-extrabold block text-sm">30%</span>
                    <span class="text-[10px] text-slate-300 font-semibold block">3. Rumbel / Siswa XII</span>
                </div>
                <div class="p-2.5 rounded-xl bg-white/10 border border-white/10 text-center">
                    <span class="text-amber-400 font-extrabold block text-sm">25%</span>
                    <span class="text-[10px] text-slate-300 font-semibold block">4. Masuk UBSI</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Smart Recommendation Widget: "Sekolah yang Disarankan Hari Ini" -->
    <div class="mb-8">
        <div class="flex items-center space-x-2 mb-4">
            <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                <i data-lucide="sparkles" class="w-4 h-4"></i>
            </div>
            <h3 class="text-base font-bold text-slate-900">Sekolah yang Disarankan Dikunjungi Hari Ini</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($todayRecommended as $rec)
                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-soft hover-lift flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2.5 py-1 text-[10px] font-extrabold rounded-full bg-red-100 text-red-700 border border-red-200">
                                PRIORITAS A
                            </span>
                            <span class="text-xs font-black text-brand-600">Skor: {{ number_format($rec->potential_score, 1) }}</span>
                        </div>

                        <h4 class="font-bold text-sm text-slate-900 leading-tight mb-1">{{ $rec->name }}</h4>
                        <p class="text-xs text-slate-500 mb-3">
                            Kec. {{ $rec->district ? $rec->district->name : '-' }} &bull; {{ $rec->schoolType ? $rec->schoolType->name : 'SLTA' }} {{ strtoupper($rec->status) }}
                        </p>

                        <div class="space-y-1.5 p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs mb-4">
                            <div class="flex items-center justify-between text-slate-700">
                                <span class="text-slate-500">Jarak dari UBSI:</span>
                                <span class="font-bold text-brand-600">{{ number_format($rec->distance_from_campus, 1) }} km</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-700">
                                <span class="text-slate-500">Siswa XII / Rumbel:</span>
                                <span class="font-bold text-slate-800">{{ number_format($rec->grade_12_count) }} Siswa</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-700">
                                <span class="text-slate-500">Maba dari Sekolah Ini:</span>
                                <span class="font-bold text-slate-800">{{ $rec->ubsi_students_count ?? 0 }} Mahasiswa</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <a href="{{ route('schools.show', $rec->id) }}" class="flex-1 py-2.5 bg-brand-navy hover:bg-slate-900 text-white font-bold text-center text-xs rounded-xl transition">
                            Kunjungi Sekarang
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Leaderboard Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Leaderboard Prioritas Sekolah SLTA Karawang</h3>
                <p class="text-xs text-slate-500">Terurut berdasarkan Total Skor Potensi Konversi PMB UBSI</p>
            </div>

            <!-- Filters -->
            <form method="GET" action="{{ route('priorities.index') }}" class="flex flex-wrap items-center gap-2">
                <select name="priority_level" onchange="this.form.submit()" class="py-1.5 px-3 text-xs rounded-xl border border-slate-300">
                    <option value="">Semua Prioritas</option>
                    <option value="A" {{ request('priority_level') == 'A' ? 'selected' : '' }}>Prioritas A (≥ 75)</option>
                    <option value="B" {{ request('priority_level') == 'B' ? 'selected' : '' }}>Prioritas B (60 - 74)</option>
                    <option value="C" {{ request('priority_level') == 'C' ? 'selected' : '' }}>Prioritas C (< 60)</option>
                </select>

                <select name="district_id" onchange="this.form.submit()" class="py-1.5 px-3 text-xs rounded-xl border border-slate-300">
                    <option value="">Semua Kecamatan</option>
                    @foreach($districts as $d)
                        <option value="{{ $d->id }}" {{ request('district_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-900 text-white font-bold uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4 text-center">Rank</th>
                        <th class="py-3.5 px-4">Nama Sekolah</th>
                        <th class="py-3.5 px-4">Kecamatan</th>
                        <th class="py-3.5 px-4 text-center">Jarak UBSI (km)</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center">Siswa Kelas XII</th>
                        <th class="py-3.5 px-4 text-center">Maba UBSI dari Sekolah</th>
                        <th class="py-3.5 px-4 text-center">Total Skor</th>
                        <th class="py-3.5 px-4 text-center">Prioritas</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($schools as $index => $school)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 text-center font-extrabold">
                                @php $rank = $schools->firstItem() + $index; @endphp
                                @if($rank === 1)
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-400 text-slate-950 font-black shadow-sm">🥇 1</span>
                                @elseif($rank === 2)
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-300 text-slate-900 font-black shadow-sm">🥈 2</span>
                                @elseif($rank === 3)
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-700 text-white font-black shadow-sm">🥉 3</span>
                                @else
                                    <span class="text-slate-500 font-bold">#{{ $rank }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <a href="{{ route('schools.show', $school->id) }}" class="font-bold text-slate-900 hover:text-brand-600 block leading-tight">
                                    {{ $school->name }}
                                </a>
                                <span class="text-[10px] text-slate-400 block mt-0.5">
                                    {{ $school->schoolType ? $school->schoolType->name : 'SLTA' }} &bull; NPSN: {{ $school->npsn }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-700">
                                {{ $school->district ? $school->district->name : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-brand-600">
                                {{ number_format($school->distance_from_campus, 1) }} km
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded uppercase {{ strtolower($school->status) === 'negeri' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $school->status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-extrabold text-slate-800">
                                {{ number_format($school->grade_12_count) }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-extrabold text-purple-700">
                                {{ $school->ubsi_students_count ?? 0 }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-black text-brand-600 text-sm">
                                {{ number_format($school->potential_score, 1) }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @php
                                    $badge = match($school->priority_level) {
                                        'A' => 'bg-red-100 text-red-700 border-red-200',
                                        'B' => 'bg-amber-100 text-amber-700 border-amber-200',
                                        'C' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                        default => 'bg-slate-100 text-slate-600 border-slate-200',
                                    };
                                @endphp
                                <span class="px-2.5 py-1 text-[10px] font-extrabold rounded-full border {{ $badge }}">
                                    Prioritas {{ $school->priority_level ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('schools.show', $school->id) }}" class="px-3 py-1.5 bg-brand-navy hover:bg-slate-900 text-white font-bold text-[10px] rounded-lg shadow transition inline-block">
                                    Detail Profile
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-8 text-center text-slate-400">Tidak ada sekolah ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4 bg-slate-50 border-t border-slate-100">
            {{ $schools->links() }}
        </div>
    </div>
</x-app-layout>
