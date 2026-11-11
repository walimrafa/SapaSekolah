<x-app-layout title="Follow Up Sekolah & Fix Jadwal Sosialisasi">
    <div x-data="{ showAddModal: false, showScheduleModal: false, selectedFollowUp: null }">
        <!-- Page Header -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <span class="p-2 rounded-xl bg-gradient-to-tr from-brand-600 to-amber-500 text-white shadow-md">
                        <i data-lucide="phone-call" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Follow Up Sekolah & Fix Jadwal Sosialisasi</h1>
                        <p class="text-xs text-slate-500 mt-0.5">Tindak lanjut data kunjungan lapangan untuk koordinasi dan penguncian jadwal sosialisasi kampus UBSI.</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <!-- Sync button from Visits -->
                <form method="POST" action="{{ route('follow-ups.sync') }}" class="inline">
                    @csrf
                    <button type="submit" class="px-3.5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-bold rounded-xl text-xs shadow-soft flex items-center gap-2 transition duration-150" title="Sinkronkan data kunjungan terbaru">
                        <i data-lucide="refresh-cw" class="w-4 h-4 text-brand-600"></i>
                        <span>Sinkronkan Kunjungan</span>
                    </button>
                </form>

                <!-- Download Excel Dropdown / Button -->
                <div x-data="{ exportOpen: false }" class="relative inline-block text-left">
                    <button @click="exportOpen = !exportOpen" type="button" class="px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md flex items-center gap-2 transition cursor-pointer">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                        <span>Unduh Excel</span>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                    </button>
                    <div x-show="exportOpen" @click.away="exportOpen = false" x-cloak class="absolute right-0 mt-2 w-64 rounded-2xl bg-white shadow-xl ring-1 ring-black/5 border border-slate-100 z-50 py-1.5 divide-y divide-slate-100">
                        <a href="{{ route('follow-ups.export', array_merge(request()->query(), ['type' => 'fixed'])) }}" class="flex items-center gap-3 px-4 py-2.5 text-xs text-slate-700 hover:bg-emerald-50 hover:text-emerald-800 font-semibold transition">
                            <span class="p-1.5 rounded-lg bg-emerald-100 text-emerald-700">
                                <i data-lucide="calendar-check-2" class="w-4 h-4"></i>
                            </span>
                            <div>
                                <p class="font-bold text-slate-800">Unduh Jadwal FIX (Excel)</p>
                                <p class="text-[10px] text-slate-400">Khusus sekolah yang sudah FIX</p>
                            </div>
                        </a>
                        <a href="{{ route('follow-ups.export', request()->query()) }}" class="flex items-center gap-3 px-4 py-2.5 text-xs text-slate-700 hover:bg-slate-50 font-semibold transition">
                            <span class="p-1.5 rounded-lg bg-brand-50 text-brand-600">
                                <i data-lucide="table" class="w-4 h-4"></i>
                            </span>
                            <div>
                                <p class="font-bold text-slate-800">Unduh Semua Data (Excel)</p>
                                <p class="text-[10px] text-slate-400">Semua riwayat follow up & antrean</p>
                            </div>
                        </a>
                    </div>
                </div>

                <button @click="showAddModal = true" type="button"
                    class="px-4 py-2.5 bg-brand-navy hover:bg-slate-900 text-white font-bold rounded-xl text-xs shadow-md flex items-center space-x-2 transition cursor-pointer">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-amber-400"></i>
                    <span>Tambah Follow Up Manual</span>
                </button>
            </div>
        </div>

        <!-- Alert Notification -->
        @if(session('toast'))
            <div class="p-4 mb-6 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center gap-3 shadow-sm">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                <span>{{ session('toast')['message'] ?? 'Data berhasil diperbarui.' }}</span>
            </div>
        @endif

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
            <!-- Total Follow Up -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Follow Up</span>
                    <i data-lucide="building-2" class="w-4 h-4 text-slate-400"></i>
                </div>
                <p class="text-2xl font-black text-slate-900 mt-2">{{ number_format($totalCount) }}</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Sekolah Terdata</p>
            </div>

            <!-- Jadwal FIX (Completed) -->
            <div class="p-4 rounded-2xl bg-gradient-to-br from-emerald-500/10 to-teal-500/5 border border-emerald-300 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-extrabold text-emerald-800 uppercase tracking-wider">Jadwal FIX</span>
                    <i data-lucide="calendar-check-2" class="w-4 h-4 text-emerald-600"></i>
                </div>
                <p class="text-2xl font-black text-emerald-700 mt-2">{{ number_format($fixedScheduleCount) }}</p>
                <p class="text-[11px] font-bold text-emerald-600 mt-0.5">Terkonfirmasi Sekolah</p>
            </div>

            <!-- Dalam Koordinasi (Progress) -->
            <div class="p-4 rounded-2xl bg-white border border-blue-200 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-blue-700 uppercase tracking-wider">Koordinasi</span>
                    <i data-lucide="message-square" class="w-4 h-4 text-blue-500"></i>
                </div>
                <p class="text-2xl font-black text-blue-600 mt-2">{{ number_format($progressCount) }}</p>
                <p class="text-[11px] text-blue-500/80 mt-0.5">Sedang Diskusi Jadwal</p>
            </div>

            <!-- Menunggu Konfirmasi (Pending) -->
            <div class="p-4 rounded-2xl bg-white border border-amber-200 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Menunggu</span>
                    <i data-lucide="clock" class="w-4 h-4 text-amber-500"></i>
                </div>
                <p class="text-2xl font-black text-amber-600 mt-2">{{ number_format($pendingCount) }}</p>
                <p class="text-[11px] text-amber-500/80 mt-0.5">Baru Masuk Kunjungan</p>
            </div>

            <!-- Estimasi Audiens Terkunci -->
            <div class="p-4 rounded-2xl bg-white border border-purple-200 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-purple-700 uppercase tracking-wider">Estimasi Audiens</span>
                    <i data-lucide="users" class="w-4 h-4 text-purple-500"></i>
                </div>
                <p class="text-2xl font-black text-purple-700 mt-2">{{ number_format($totalEstimatedAudience) }}</p>
                <p class="text-[11px] text-purple-500/80 mt-0.5">Target Siswa Kelas 12</p>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- PENGALIHAN DIAGRAM ANALISIS FOLLOW UP KE PUSAT LAPORAN -->
        <!-- ======================================================== -->
        <div class="mb-6 flex flex-col sm:flex-row items-center justify-between p-4 bg-gradient-to-r from-blue-50/90 via-indigo-50/70 to-blue-50/90 border border-blue-200/80 rounded-2xl shadow-soft gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-600 text-white flex items-center justify-center font-bold shadow-xs shrink-0">
                    <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h4 class="text-xs font-black text-slate-900">Grafik &amp; Diagram Analisis Follow Up Telah Dipusatkan ke Menu Laporan</h4>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $conversionRate > 0 ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-100 text-amber-800 border border-amber-300' }}">
                            {{ $conversionRate }}% Jadwal FIX
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-0.5">Seluruh visualisasi diagram timeline sosialisasi FIX, estimasi audiens siswa, sebaran kecamatan, dan capaian staf marketing kini disajikan pada Pusat Laporan &amp; Analitik.</p>
                </div>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto justify-end shrink-0">
                <a href="{{ route('reports.index', ['category' => 'diagram']) }}" class="px-4 py-2 bg-brand-navy hover:bg-slate-900 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5">
                    <i data-lucide="line-chart" class="w-4 h-4 text-amber-400"></i>
                    <span>Buka Laporan Diagram</span>
                </a>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft p-4 mb-6">
            <form method="GET" action="{{ route('follow-ups.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs">
                <!-- Search -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Cari Sekolah / PIC</label>
                    <div class="relative">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                        <input type="search" name="search" value="{{ request('search') }}" 
                               placeholder="Nama sekolah, PIC, kontak..." 
                               class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Status Penetapan Jadwal</label>
                    <select name="schedule_status" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs font-semibold">
                        <option value="">Semua Status Jadwal</option>
                        <option value="fixed" {{ request('schedule_status') === 'fixed' ? 'selected' : '' }}>Jadwal Sudah FIX Terkonfirmasi</option>
                        <option value="unfixed" {{ request('schedule_status') === 'unfixed' ? 'selected' : '' }}>Belum FIX (Menunggu/Koordinasi)</option>
                    </select>
                </div>

                <!-- Staf Marketing -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Staf Marketing PIC</label>
                    <select name="user_id" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs">
                        <option value="">Semua Staf Marketing</option>
                        @foreach($marketingUsers as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Metode Komunikasi -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Metode Kontak</label>
                    <select name="method" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs">
                        <option value="">Semua Metode</option>
                        <option value="WhatsApp" {{ request('method') === 'WhatsApp' ? 'selected' : '' }}>WhatsApp</option>
                        <option value="Telepon" {{ request('method') === 'Telepon' ? 'selected' : '' }}>Telepon Langsung</option>
                        <option value="Kunjungan Lapangan" {{ request('method') === 'Kunjungan Lapangan' ? 'selected' : '' }}>Kunjungan Langsung</option>
                        <option value="Email" {{ request('method') === 'Email' ? 'selected' : '' }}>Email</option>
                    </select>
                </div>

                <!-- Action Button -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-md transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        <span>Terapkan</span>
                    </button>
                    <a href="{{ route('follow-ups.export', request()->query()) }}" class="py-2 px-2.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-bold text-xs transition flex items-center justify-center gap-1" title="Unduh hasil filter ke Excel">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span class="hidden sm:inline">Excel</span>
                    </a>
                    @if(request()->anyFilled(['search', 'schedule_status', 'user_id', 'method']))
                        <a href="{{ route('follow-ups.index') }}" class="py-2 px-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs transition" title="Reset Filter">
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Kanban Grid (4 Columns) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Column 1: Menunggu Konfirmasi (Pending) -->
            <div class="bg-slate-100/80 p-4 rounded-2xl border border-amber-200 flex flex-col min-h-[500px]">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-amber-200/80">
                    <h3 class="text-xs font-black uppercase text-amber-800 flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        <span>Menunggu Konfirmasi ({{ $pending->count() }})</span>
                    </h3>
                    <span class="text-[10px] font-bold bg-amber-200/70 text-amber-900 px-2 py-0.5 rounded-full">Baru Masuk</span>
                </div>
                <div class="space-y-3 flex-1 overflow-y-auto">
                    @forelse($pending as $item)
                        @include('follow-ups._card', ['item' => $item, 'borderClass' => 'border-amber-200 hover:border-amber-400'])
                    @empty
                        <div class="text-center py-12 text-slate-400">
                            <i data-lucide="inbox" class="w-6 h-6 mx-auto mb-1 opacity-50"></i>
                            <p class="text-xs font-medium">Tidak ada data antrean</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Column 2: Dalam Koordinasi Jadwal (Progress) -->
            <div class="bg-slate-100/80 p-4 rounded-2xl border border-blue-200 flex flex-col min-h-[500px]">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-blue-200/80">
                    <h3 class="text-xs font-black uppercase text-blue-800 flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                        <span>Koordinasi Jadwal ({{ $progress->count() }})</span>
                    </h3>
                    <span class="text-[10px] font-bold bg-blue-200/70 text-blue-900 px-2 py-0.5 rounded-full">Diskusi</span>
                </div>
                <div class="space-y-3 flex-1 overflow-y-auto">
                    @forelse($progress as $item)
                        @include('follow-ups._card', ['item' => $item, 'borderClass' => 'border-blue-200 hover:border-blue-400'])
                    @empty
                        <div class="text-center py-12 text-slate-400">
                            <i data-lucide="inbox" class="w-6 h-6 mx-auto mb-1 opacity-50"></i>
                            <p class="text-xs font-medium">Tidak ada diskusi aktif</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Column 3: Jadwal Sosialisasi FIX (Completed) -->
            <div class="bg-slate-100/80 p-4 rounded-2xl border border-emerald-300 flex flex-col min-h-[500px]">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-emerald-200/80">
                    <h3 class="text-xs font-black uppercase text-emerald-800 flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <span>Jadwal FIX ({{ $completed->count() }})</span>
                    </h3>
                    <span class="text-[10px] font-bold bg-emerald-200/80 text-emerald-900 px-2 py-0.5 rounded-full">Terkonfirmasi</span>
                </div>
                <div class="space-y-3 flex-1 overflow-y-auto">
                    @forelse($completed as $item)
                        @include('follow-ups._card', ['item' => $item, 'borderClass' => 'border-emerald-300 bg-emerald-50/20 hover:border-emerald-500'])
                    @empty
                        <div class="text-center py-12 text-slate-400">
                            <i data-lucide="calendar-x" class="w-6 h-6 mx-auto mb-1 opacity-50"></i>
                            <p class="text-xs font-medium">Belum ada jadwal yang dikunci FIX</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Column 4: Tidak Berhasil / Batal (Failed) -->
            <div class="bg-slate-100/80 p-4 rounded-2xl border border-rose-200 flex flex-col min-h-[500px]">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-rose-200/80">
                    <h3 class="text-xs font-black uppercase text-rose-800 flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                        <span>Belum Diizinkan ({{ $failed->count() }})</span>
                    </h3>
                    <span class="text-[10px] font-bold bg-rose-200/70 text-rose-900 px-2 py-0.5 rounded-full">Batal</span>
                </div>
                <div class="space-y-3 flex-1 overflow-y-auto">
                    @forelse($failed as $item)
                        @include('follow-ups._card', ['item' => $item, 'borderClass' => 'border-rose-200 hover:border-rose-400'])
                    @empty
                        <div class="text-center py-12 text-slate-400">
                            <i data-lucide="check" class="w-6 h-6 mx-auto mb-1 opacity-50"></i>
                            <p class="text-xs font-medium">Tidak ada jadwal dibatalkan</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- MODAL 1: FORM KUNCI & UPDATE JADWAL SOSIALISASI (FIX JADWAL) -->
        <div id="scheduleModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden">
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-2xl max-w-lg w-full overflow-hidden transform transition-all">
                <div class="px-6 py-4 bg-slate-950 text-white flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="p-1.5 rounded-xl bg-emerald-600 text-white">
                            <i data-lucide="calendar-check" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h3 class="font-extrabold text-sm tracking-tight">Kunci & Update Jadwal Sosialisasi</h3>
                            <p class="text-xs text-amber-400 mt-0.5" id="modalSchoolName">Nama Sekolah</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeScheduleModal()" class="text-slate-400 hover:text-white p-1 rounded-lg">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form id="scheduleForm" method="POST" action="" class="p-6 space-y-4 text-xs">
                    @csrf
                    @method('PUT')

                    <!-- Penetapan Status -->
                    <div>
                        <label class="block font-bold text-slate-800 uppercase tracking-wider mb-1.5">
                            Status Tindak Lanjut Jadwal <span class="text-rose-500">*</span>
                        </label>
                        <select name="status" id="formStatus" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-bold">
                            <option value="pending">Menunggu Konfirmasi (Belum Pasti)</option>
                            <option value="progress">Sedang Koordinasi Jadwal (Diskusi Aktif)</option>
                            <option value="completed" class="text-emerald-700 font-black">Jadwal Sosialisasi FIX (Sudah Terkunci)</option>
                            <option value="failed">Belum Diizinkan / Batal</option>
                        </select>
                    </div>

                    <!-- Tanggal & Jam FIX -->
                    <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-extrabold text-emerald-900 text-xs flex items-center gap-1.5">
                                <i data-lucide="calendar" class="w-4 h-4 text-emerald-600"></i>
                                Tanggal & Waktu Pelaksanaan FIX
                            </span>
                            <label class="flex items-center gap-1.5 font-bold text-emerald-800 cursor-pointer text-[11px]">
                                <input type="checkbox" name="is_schedule_fixed" id="formIsFixed" value="1" class="text-emerald-600 rounded">
                                <span>Kunci Jadwal FIX</span>
                            </label>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-emerald-900 mb-1">Tanggal FIX</label>
                                <input type="date" name="fixed_date" id="formFixedDate" class="w-full px-3 py-2 rounded-xl border border-emerald-300 bg-white font-bold text-emerald-800">
                            </div>
                            <div>
                                <label class="block font-bold text-emerald-900 mb-1">Jam / Waktu FIX</label>
                                <input type="text" name="fixed_time" id="formFixedTime" placeholder="Contoh: 08:30 WIB" class="w-full px-3 py-2 rounded-xl border border-emerald-300 bg-white font-semibold">
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-emerald-900 mb-1">Ruangan / Tempat Sosialisasi</label>
                            <input type="text" name="socialization_location" id="formLocation" placeholder="Contoh: Ruang Audio Visual / Kelas 12" class="w-full px-3 py-2 rounded-xl border border-emerald-300 bg-white">
                        </div>
                    </div>

                    <!-- PIC Kontak Sekolah -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nama PIC Sekolah</label>
                            <input type="text" name="pic_name" id="formPicName" class="w-full px-3 py-2 rounded-xl border border-slate-300">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">No. HP / WhatsApp PIC</label>
                            <input type="text" name="pic_phone" id="formPicPhone" class="w-full px-3 py-2 rounded-xl border border-slate-300">
                        </div>
                    </div>

                    <!-- Catatan Hasil Koordinasi -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Catatan Hasil Koordinasi & Ketentuan</label>
                        <textarea name="notes" id="formNotes" rows="2.5" placeholder="Contoh: Sekolah menyetujui tanggal 15 Nov jam 08:30 WIB di Aula, infocus disiapkan sekolah..." class="w-full px-3 py-2 rounded-xl border border-slate-300"></textarea>
                    </div>

                    <!-- Footer Buttons -->
                    <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                        <button type="button" onclick="closeScheduleModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow-md transition flex items-center gap-1.5">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Simpan Jadwal Sosialisasi</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 2: FORM TAMBAH FOLLOW UP MANUAL -->
        <div x-show="showAddModal" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
            style="display: none;">
            
            <div @click.away="showAddModal = false" class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-200">
                <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <i data-lucide="phone-outgoing" class="w-5 h-5 text-amber-400"></i>
                        <h3 class="font-extrabold text-sm tracking-tight">Tambah Follow Up Baru</h3>
                    </div>
                    <button @click="showAddModal = false" type="button" class="text-slate-400 hover:text-white p-1 rounded-lg">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('follow-ups.store') }}" class="p-6 space-y-4 text-xs">
                    @csrf

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Sekolah Tujuan <span class="text-rose-500">*</span></label>
                        <select name="school_id" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-semibold">
                            <option value="">-- Pilih Sekolah --</option>
                            @foreach($schools as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} (Kec. {{ $s->district ? $s->district->name : '-' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Staf Marketing PIC <span class="text-rose-500">*</span></label>
                            <select name="user_id" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-semibold">
                                @foreach($marketingUsers as $u)
                                    <option value="{{ $u->id }}" {{ auth()->id() == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tanggal Follow Up <span class="text-rose-500">*</span></label>
                            <input type="date" name="followup_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-bold text-brand-600">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Metode Komunikasi <span class="text-rose-500">*</span></label>
                            <select name="method" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-semibold">
                                <option value="WhatsApp" selected>WhatsApp Chat</option>
                                <option value="Telepon">Telepon Langsung</option>
                                <option value="Kunjungan Lapangan">Kunjungan Langsung</option>
                                <option value="Email">Email Resmi</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Status Follow Up <span class="text-rose-500">*</span></label>
                            <select name="status" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-semibold">
                                <option value="pending" selected>Menunggu Konfirmasi</option>
                                <option value="progress">Koordinasi Jadwal (In Progress)</option>
                                <option value="completed">Jadwal Sosialisasi FIX</option>
                                <option value="failed">Belum Diizinkan / Batal</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nama PIC Sekolah</label>
                            <input type="text" name="pic_name" placeholder="Contoh: Bpk. Budi Santoso" class="w-full px-3 py-2.5 rounded-xl border border-slate-300">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">No. WhatsApp PIC</label>
                            <input type="text" name="pic_phone" placeholder="Contoh: 081234567890" class="w-full px-3 py-2.5 rounded-xl border border-slate-300">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Catatan Pesan / Isi Diskusi <span class="text-rose-500">*</span></label>
                        <textarea name="notes" rows="3" required placeholder="Tuliskan catatan hasil pesan atau koordinasi jadwal dengan pihak sekolah..." class="w-full px-3 py-2.5 rounded-xl border border-slate-300"></textarea>
                    </div>

                    <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                        <button @click="showAddModal = false" type="button" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-brand-navy hover:bg-slate-900 text-white font-bold rounded-xl shadow-md transition">
                            Simpan Follow Up
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 3: KONFIRMASI HAPUS HISTORI FOLLOW UP -->
        <div id="deleteFollowUpModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden">
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-2xl max-w-sm w-full overflow-hidden transform transition-all animate-in fade-in zoom-in-95 duration-150">
                <div class="p-6 text-center">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-rose-50 border border-rose-200 text-rose-600 flex items-center justify-center mb-4 shadow-xs">
                        <i data-lucide="alert-triangle" class="w-7 h-7"></i>
                    </div>

                    <h3 class="text-base font-extrabold text-slate-900">Konfirmasi Hapus Follow Up</h3>
                    
                    <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                        Apakah Anda yakin ingin menghapus riwayat follow-up sekolah <span id="deleteModalSchoolName" class="font-extrabold text-slate-900 underline decoration-rose-300"></span>?
                    </p>
                    
                    <p class="text-[11px] text-slate-400 mt-1.5 bg-slate-50 p-2 rounded-xl border border-slate-100">
                        Data riwayat follow-up dan antrean jadwal sekolah ini akan dihapus dari sistem.
                    </p>

                    <form id="deleteFollowUpForm" method="POST" action="" class="mt-5 flex items-center justify-center gap-2">
                        @csrf
                        @method('DELETE')

                        <button type="button" onclick="closeDeleteModal()" class="w-1/2 py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition cursor-pointer">
                            Batal
                        </button>
                        
                        <button type="submit" class="w-1/2 py-2.5 px-4 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs shadow-md transition flex items-center justify-center gap-1.5 cursor-pointer">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            <span>Ya, Hapus</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function openScheduleModal(id, schoolName, status, fixedDate, fixedTime, picName, picPhone, location, notes, isFixed) {
            document.getElementById('modalSchoolName').textContent = schoolName;
            document.getElementById('scheduleForm').action = '/follow-ups/' + id + '/schedule';
            
            // Ketika menekan tombol Kunci Jadwal FIX, otomatis jadikan status 'completed' (Jadwal FIX)
            // agar kartu langsung berpindah ke kolom kategori Jadwal FIX
            const targetStatus = (isFixed == 1 || status === 'completed') ? 'completed' : 'completed';

            document.getElementById('formStatus').value = targetStatus;
            document.getElementById('formFixedDate').value = fixedDate || '';
            document.getElementById('formFixedTime').value = fixedTime || '';
            document.getElementById('formPicName').value = picName || '';
            document.getElementById('formPicPhone').value = picPhone || '';
            document.getElementById('formLocation').value = location || '';
            document.getElementById('formNotes').value = notes || '';
            document.getElementById('formIsFixed').checked = (targetStatus === 'completed');

            document.getElementById('scheduleModal').classList.remove('hidden');
            if (window.lucide) { window.lucide.createIcons(); }
        }

        function closeScheduleModal() {
            document.getElementById('scheduleModal').classList.add('hidden');
        }

        // Sinkronisasi status dropdown dengan checkbox Kunci Jadwal FIX
        document.addEventListener('DOMContentLoaded', function() {
            const statusSelect = document.getElementById('formStatus');
            const fixedCheckbox = document.getElementById('formIsFixed');

            if (statusSelect && fixedCheckbox) {
                statusSelect.addEventListener('change', function() {
                    fixedCheckbox.checked = (this.value === 'completed');
                });

                fixedCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        statusSelect.value = 'completed';
                    } else if (statusSelect.value === 'completed') {
                        statusSelect.value = 'progress';
                    }
                });
            }
        });

        function openDeleteModal(id, schoolName) {
            document.getElementById('deleteModalSchoolName').textContent = schoolName;
            document.getElementById('deleteFollowUpForm').action = '/follow-ups/' + id;
            document.getElementById('deleteFollowUpModal').classList.remove('hidden');
            if (window.lucide) { window.lucide.createIcons(); }
        }

        function closeDeleteModal() {
            document.getElementById('deleteFollowUpModal').classList.add('hidden');
        }
    </script>
    @endpush
</x-app-layout>
