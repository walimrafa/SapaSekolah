<x-app-layout title="Edit Agenda Rencana Kunjungan">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Edit Agenda Rencana Kunjungan</h1>
            <p class="text-xs text-slate-500 mt-0.5">Jadwalkan agenda kunjungan tim marketing ke sekolah SLTA</p>
        </div>
        <a href="{{ route('visit-plans.index') }}" class="px-4 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-xl text-xs transition flex items-center space-x-1.5">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke List Agenda</span>
        </a>
    </div>

    <div class="max-w-3xl" x-data="{
        districts: {{ json_encode($districts) }},
        allSchools: {{ json_encode($schools) }},
        selectedDistrictId: '{{ old('district_id', $visitPlan->school->district_id ?? '') }}',
        selectedSchoolId: '{{ old('school_id', $visitPlan->school_id) }}',
        get filteredSchools() {
            if (!this.selectedDistrictId) return this.allSchools;
            return this.allSchools.filter(s => s.district_id == this.selectedDistrictId);
        }
    }">
        <form method="POST" action="{{ route('visit-plans.update', $visitPlan->id) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Form Card -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft">
                <h3 class="text-sm font-bold text-slate-900 pb-3 mb-4 border-b border-slate-100 flex items-center gap-2">
                    <i data-lucide="calendar-plus" class="w-4 h-4 text-brand-600"></i>
                    <span>Form Detail Agenda Kunjungan</span>
                </h3>

                <div class="space-y-4 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Filter Kecamatan Sekolah <span class="text-amber-600 font-normal">(Pilih Kecamatan Terlebih Dahulu)</span></label>
                            <select x-model="selectedDistrictId" @change="selectedSchoolId = ''" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-bold text-brand-700 bg-amber-50/40 focus:border-brand-500">
                                <option value="">-- Semua Kecamatan (Filter Wilayah) --</option>
                                <template x-for="d in districts" :key="d.id">
                                    <option :value="d.id" x-text="'Kecamatan ' + d.name"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Sekolah Tujuan SLTA <span class="text-red-500">*</span></label>
                            <select name="school_id" x-model="selectedSchoolId" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-semibold focus:border-brand-500">
                                <option value="">-- Pilih Sekolah Tujuan --</option>
                                <template x-for="s in filteredSchools" :key="s.id">
                                    <option :value="s.id" x-text="s.name + (s.district ? ' (Kec. ' + s.district.name + ')' : '') + ' - Prio ' + (s.priority_level || 'C')"></option>
                                </template>
                            </select>
                            <p class="text-[10px] text-slate-400 mt-1" x-show="selectedDistrictId">Menampilkan <span class="font-bold text-brand-600" x-text="filteredSchools.length"></span> sekolah di kecamatan terpilih.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Staf Marketing PIC <span class="text-red-500">*</span></label>
                            <select name="user_id" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-semibold">
                                @foreach($marketingUsers as $u)
                                    <option value="{{ $u->id }}" {{ old('user_id', $visitPlan->user_id) == $u->id ? 'selected' : '' }}>
                                        {{ $u->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tanggal Rencana Kunjungan <span class="text-red-500">*</span></label>
                            <input type="date" name="planned_date" value="{{ old('planned_date', \Carbon\Carbon::parse($visitPlan->planned_date)->format('Y-m-d')) }}" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-bold text-brand-600">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Level Prioritas Agenda <span class="text-red-500">*</span></label>
                            <select name="priority" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-bold">
                                <option value="A" {{ old('priority', $visitPlan->priority) == 'A' ? 'selected' : '' }}>Prioritas A (Tinggi / Top School)</option>
                                <option value="B" {{ old('priority', $visitPlan->priority) == 'B' ? 'selected' : '' }}>Prioritas B (Sedang / Potensial)</option>
                                <option value="C" {{ old('priority', $visitPlan->priority) == 'C' ? 'selected' : '' }}>Prioritas C (Standar)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tujuan / Agenda Kegiatan <span class="text-red-500">*</span></label>
                            <input type="text" name="purpose" value="{{ old('purpose', $visitPlan->purpose) }}" placeholder="Misal: Sosialisasi PMB / Meeting Guru BK" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-semibold">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                            <textarea name="notes" rows="3" placeholder="Catatan khusus atau syarat masuk sekolah..." class="w-full px-3 py-2.5 rounded-xl border border-slate-300">{{ old('notes', $visitPlan->notes) }}</textarea>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Status Kunjungan <span class="text-red-500">*</span></label>
                            <select name="status" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-bold">
                                <option value="planned" {{ old('status', $visitPlan->status) == 'planned' ? 'selected' : '' }}>Planned (Terjadwal)</option>
                                <option value="completed" {{ old('status', $visitPlan->status) == 'completed' ? 'selected' : '' }}>Completed (Selesai)</option>
                                <option value="cancelled" {{ old('status', $visitPlan->status) == 'cancelled' ? 'selected' : '' }}>Cancelled (Batal)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3">
                <a href="{{ route('visit-plans.index') }}" class="px-5 py-3 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs rounded-xl transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-3 bg-brand-navy hover:bg-slate-900 text-white font-bold text-xs rounded-xl shadow-lg transition flex items-center space-x-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Perubahan Agenda</span>
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
