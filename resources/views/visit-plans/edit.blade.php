<x-app-layout title="Edit Agenda Rencana Kunjungan">
    @push('styles')
    <style>
        .ts-wrapper.district-tom-select .ts-control,
        .ts-wrapper.user-tom-select .ts-control,
        .ts-wrapper.school-tom-select .ts-control {
            border-radius: 0.75rem !important;
            padding: 0.55rem 0.875rem !important;
            border-color: #cbd5e1 !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            background-color: #ffffff !important;
        }
        .ts-dropdown {
            background: #ffffff !important;
            background-color: #ffffff !important;
            border-radius: 0.75rem !important;
            font-size: 0.75rem !important;
            border: 1px solid #cbd5e1 !important;
            box-shadow: 0 16px 32px -4px rgba(15, 23, 42, 0.25) !important;
            z-index: 99999 !important;
            overflow: hidden !important;
        }
        .ts-dropdown .ts-dropdown-content {
            background-color: #ffffff !important;
            max-height: 250px !important;
        }
        .ts-dropdown .option {
            padding: 0.5rem 0.75rem !important;
            background-color: #ffffff !important;
        }
        .ts-dropdown .active {
            background-color: #eff6ff !important;
            color: #1e40af !important;
            font-weight: 700 !important;
        }
    </style>
    @endpush

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

    <div class="max-w-3xl" x-data="initVisitPlanEdit()">
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
                            <label class="block font-bold text-slate-700 mb-1">Filter Kecamatan Sekolah <span class="text-amber-600 font-normal">(Tom Select)</span></label>
                            <select id="edit_district_filter" class="district-tom-select w-full px-3 py-2.5 rounded-xl border border-slate-300 font-bold text-brand-700 bg-white focus:border-brand-500">
                                <option value="">-- Semua Kecamatan (Filter Wilayah) --</option>
                                @foreach($districts as $d)
                                    <option value="{{ $d->id }}" {{ (old('district_id', $visitPlan->school->district_id ?? '') == $d->id) ? 'selected' : '' }}>
                                        Kecamatan {{ $d->name }} ({{ $d->schools_count }} Sekolah)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Sekolah Tujuan SLTA <span class="text-red-500">*</span></label>
                            <select name="school_id" x-model="selectedSchoolId" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-semibold focus:border-brand-500">
                                <option value="">-- Pilih Sekolah Tujuan --</option>
                                @foreach($schools as $s)
                                    <option value="{{ $s->id }}" x-show="!selectedDistrictId || selectedDistrictId == '{{ $s->district_id }}'">
                                        {{ $s->name }} (Kec. {{ $s->district ? $s->district->name : '-' }}) - Prio {{ $s->priority_level ?? 'C' }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[10px] text-slate-400 mt-1" x-show="selectedDistrictId">Menampilkan <span class="font-bold text-brand-600" x-text="filteredSchools.length"></span> sekolah di kecamatan terpilih.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Staf Marketing PIC <span class="text-red-500">*</span> <span class="text-slate-400 font-normal">(Tom Select)</span></label>
                            <select id="edit_user_id" name="user_id" required class="user-tom-select w-full px-3 py-2.5 rounded-xl border border-slate-300 font-semibold">
                                <option value="">-- Pilih Staf Marketing PIC --</option>
                                @foreach($marketingUsers as $u)
                                    <option value="{{ $u->id }}" {{ old('user_id', $visitPlan->user_id) == $u->id ? 'selected' : '' }}
                                        data-name="{{ $u->name }}"
                                        data-role="{{ $u->role->name ?? 'Marketing' }}"
                                        data-email="{{ $u->email }}"
                                        data-campus="{{ $u->campus?->short_name ?: ($u->campus?->name ?? 'Semua Kampus') }}">
                                        {{ $u->name }} ({{ $u->role->name ?? 'Marketing' }})
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

    <script>
        function initVisitPlanEdit() {
            return {
                districts: @json($districts),
                allSchools: @json($schools),
                selectedDistrictId: '{{ old('district_id', $visitPlan->school->district_id ?? '') }}',
                selectedSchoolId: '{{ old('school_id', $visitPlan->school_id) }}',
                tsDistrict: null,
                tsUser: null,

                init() {
                    this.$nextTick(() => {
                        const districtEl = document.getElementById('edit_district_filter');
                        if (districtEl && window.TomSelect) {
                            this.tsDistrict = new TomSelect(districtEl, {
                                create: false,
                                placeholder: '-- Cari & Filter Kecamatan ({{ $schools->count() }} Sekolah Tersedia) --',
                                allowEmptyOption: true,
                                maxOptions: 100,
                                onChange: (val) => {
                                    this.selectedDistrictId = val;
                                }
                            });
                        }

                        const userEl = document.getElementById('edit_user_id');
                        if (userEl && window.TomSelect) {
                            this.tsUser = new TomSelect(userEl, {
                                create: false,
                                placeholder: '-- Cari & Pilih Staf Marketing PIC --',
                                maxOptions: 50,
                                render: {
                                    option: function(data, escape) {
                                        const role = data.role || (data.text.match(/\((.*?)\)/) ? data.text.match(/\((.*?)\)/)[1] : 'Marketing');
                                        const name = data.name || data.text.replace(/\(.*?\)/, '').trim();
                                        const email = data.email || '';
                                        const campus = data.campus || '';

                                        let badgeClass = 'bg-slate-100 text-slate-700 border-slate-200';
                                        const r = role.toLowerCase();
                                        if (r.includes('marketing')) {
                                            badgeClass = 'bg-amber-100 text-amber-800 border-amber-300';
                                        } else if (r.includes('koordinator')) {
                                            badgeClass = 'bg-purple-100 text-purple-800 border-purple-300';
                                        } else if (r.includes('admin')) {
                                            badgeClass = 'bg-blue-100 text-blue-800 border-blue-300';
                                        } else if (r.includes('pimpinan')) {
                                            badgeClass = 'bg-emerald-100 text-emerald-800 border-emerald-300';
                                        } else if (r.includes('customer')) {
                                            badgeClass = 'bg-teal-100 text-teal-800 border-teal-300';
                                        }

                                        return '<div class="py-2 px-3 flex items-center justify-between border-b border-slate-100 last:border-b-0 hover:bg-slate-50 transition">' +
                                            '<div>' +
                                                '<div class="font-bold text-slate-900 text-xs flex items-center gap-1.5">' +
                                                    '<span class="w-2 h-2 rounded-full ' + (r.includes('marketing') ? 'bg-amber-500' : 'bg-blue-600') + ' inline-block"></span>' +
                                                    '<span>' + escape(name) + '</span>' +
                                                '</div>' +
                                                '<div class="text-[10px] text-slate-500 font-normal mt-0.5 pl-3.5">' +
                                                    (email ? escape(email) : '') + (campus ? ' • ' + escape(campus) : '') +
                                                '</div>' +
                                            '</div>' +
                                            '<span class="px-2 py-0.5 text-[9px] font-extrabold rounded-full border ' + badgeClass + ' shrink-0">' +
                                                escape(role) +
                                            '</span>' +
                                        '</div>';
                                    },
                                    item: function(data, escape) {
                                        const role = data.role || (data.text.match(/\((.*?)\)/) ? data.text.match(/\((.*?)\)/)[1] : 'Marketing');
                                        const name = data.name || data.text.replace(/\(.*?\)/, '').trim();
                                        return '<div class="font-bold text-xs text-slate-900 flex items-center gap-1.5">' +
                                            '<span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>' +
                                            '<span>' + escape(name) + '</span>' +
                                            '<span class="text-[10px] text-slate-500 font-normal">(' + escape(role) + ')</span>' +
                                        '</div>';
                                    }
                                }
                            });
                        }
                    });
                },

                get filteredSchools() {
                    if (!this.selectedDistrictId) return this.allSchools;
                    return this.allSchools.filter(s => s.district_id == this.selectedDistrictId);
                }
            };
        }
    </script>
</x-app-layout>
