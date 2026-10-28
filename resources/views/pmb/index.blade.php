<x-app-layout title="Informasi PMB UBSI">
    <div class="space-y-6">

        <!-- Top Header & Banner -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-brand-950 to-slate-900 border border-slate-800 p-6 md:p-8 shadow-xl text-white">
            <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -left-10 -top-10 w-72 h-72 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <div class="flex items-center space-x-2.5">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-400/20 text-amber-300 border border-amber-400/30">
                            <i data-lucide="graduation-cap" class="w-3.5 h-3.5 mr-1.5 text-amber-400"></i> PMB UBSI
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span> Portal Aktif
                        </span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-white flex items-center gap-2">
                        Pusat Informasi PMB UBSI
                    </h1>
                    <p class="text-sm md:text-base text-slate-300 max-w-2xl leading-relaxed">
                        Akses cepat portal resmi Penerimaan Mahasiswa Baru Universitas Bina Sarana Informatika (UBSI), jadwal gelombang, rincian biaya kuliah, dan informasi beasiswa.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <a href="{{ route('e-brochures.index') }}"
                        class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl text-sm font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-600/20 transition-all duration-150 hover:scale-[1.02]">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                        <span>E-Brosur PMB</span>
                    </a>

                    <a href="https://pmbubsi.id/pmb" target="_blank" rel="noopener noreferrer"
                        class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl text-sm font-bold bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 shadow-lg shadow-amber-500/20 transition-all duration-150 hover:scale-[1.02]">
                        <i data-lucide="external-link" class="w-4 h-4"></i>
                        <span>Buka Website Resmi</span>
                    </a>

                    <button type="button" onclick="navigator.clipboard.writeText('https://pmbubsi.id/pmb'); alert('Link PMB UBSI (https://pmbubsi.id/pmb) berhasil disalin ke clipboard!');"
                        class="inline-flex items-center space-x-2 px-3.5 py-2.5 rounded-xl text-sm font-semibold bg-slate-800/80 hover:bg-slate-700 text-slate-200 border border-slate-700 shadow-sm transition">
                        <i data-lucide="copy" class="w-4 h-4 text-amber-400"></i>
                        <span>Salin Link PMB</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 5 Quick Access Cards to Important PMB Pages -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- 1. Beranda Utama -->
            <a href="https://pmbubsi.id/pmb" target="_blank" rel="noopener noreferrer"
                class="group p-4 bg-white rounded-xl border border-slate-200/80 hover:border-amber-400 shadow-sm hover:shadow-md transition duration-200 flex flex-col justify-between">
                <div>
                    <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <i data-lucide="globe" class="w-5 h-5"></i>
                    </div>
                    <h3 class="font-bold text-sm text-slate-900 group-hover:text-brand-600 flex items-center justify-between">
                        <span>Portal Utama PMB</span>
                        <i data-lucide="arrow-up-right" class="w-4 h-4 text-slate-400 group-hover:text-amber-500 transition-colors"></i>
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 leading-snug">
                        Beranda resmi pendaftaran mahasiswa baru UBSI.
                    </p>
                </div>
                <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] font-semibold text-blue-600">
                    <span>pmbubsi.id/pmb</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </div>
            </a>

            <!-- 2. Biaya & Lokasi Kampus -->
            <a href="https://pmbubsi.id/pmb/lokasi" target="_blank" rel="noopener noreferrer"
                class="group p-4 bg-white rounded-xl border border-slate-200/80 hover:border-emerald-400 shadow-sm hover:shadow-md transition duration-200 flex flex-col justify-between">
                <div>
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <i data-lucide="map-pin" class="w-5 h-5"></i>
                    </div>
                    <h3 class="font-bold text-sm text-slate-900 group-hover:text-emerald-600 flex items-center justify-between">
                        <span>Biaya & Kampus</span>
                        <i data-lucide="arrow-up-right" class="w-4 h-4 text-slate-400 group-hover:text-emerald-500 transition-colors"></i>
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 leading-snug">
                        Rincian SPP, uang gedung, dan lokasi kampus UBSI.
                    </p>
                </div>
                <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] font-semibold text-emerald-600">
                    <span>Lihat Rincian Biaya</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </div>
            </a>

            <!-- 3. Jadwal & Gelombang -->
            <a href="https://pmbubsi.id/infopmb/kampus/daftar_sep" target="_blank" rel="noopener noreferrer"
                class="group p-4 bg-white rounded-xl border border-slate-200/80 hover:border-purple-400 shadow-sm hover:shadow-md transition duration-200 flex flex-col justify-between">
                <div>
                    <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <i data-lucide="calendar" class="w-5 h-5"></i>
                    </div>
                    <h3 class="font-bold text-sm text-slate-900 group-hover:text-purple-600 flex items-center justify-between">
                        <span>Jadwal Gelombang</span>
                        <i data-lucide="arrow-up-right" class="w-4 h-4 text-slate-400 group-hover:text-purple-500 transition-colors"></i>
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 leading-snug">
                        Timeline pendaftaran, batas tes, dan daftar ulang.
                    </p>
                </div>
                <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] font-semibold text-purple-600">
                    <span>Cek Periode Aktif</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </div>
            </a>

            <!-- 4. Alur & Syarat Pendaftaran -->
            <a href="https://pmbubsi.id/pmb/prosedur" target="_blank" rel="noopener noreferrer"
                class="group p-4 bg-white rounded-xl border border-slate-200/80 hover:border-cyan-400 shadow-sm hover:shadow-md transition duration-200 flex flex-col justify-between">
                <div>
                    <div class="w-10 h-10 rounded-lg bg-cyan-50 text-cyan-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <i data-lucide="file-check" class="w-5 h-5"></i>
                    </div>
                    <h3 class="font-bold text-sm text-slate-900 group-hover:text-cyan-600 flex items-center justify-between">
                        <span>Alur Pendaftaran</span>
                        <i data-lucide="arrow-up-right" class="w-4 h-4 text-slate-400 group-hover:text-cyan-500 transition-colors"></i>
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 leading-snug">
                        Petunjuk registrasi akun, upload berkas, & tes PMB.
                    </p>
                </div>
                <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] font-semibold text-cyan-600">
                    <span>Panduan Langkah</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </div>
            </a>

            <!-- 5. Program Beasiswa -->
            <a href="https://bsi.today/beasiswa/" target="_blank" rel="noopener noreferrer"
                class="group p-4 bg-white rounded-xl border border-slate-200/80 hover:border-amber-400 shadow-sm hover:shadow-md transition duration-200 flex flex-col justify-between">
                <div>
                    <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <i data-lucide="award" class="w-5 h-5"></i>
                    </div>
                    <h3 class="font-bold text-sm text-slate-900 group-hover:text-amber-600 flex items-center justify-between">
                        <span>Beasiswa UBSI 2026</span>
                        <i data-lucide="arrow-up-right" class="w-4 h-4 text-slate-400 group-hover:text-amber-500 transition-colors"></i>
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 leading-snug">
                        KIP Kuliah, beasiswa prestasi, talenta & hafiz Quran.
                    </p>
                </div>
                <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] font-semibold text-amber-600">
                    <span>bsi.today/beasiswa</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </div>
            </a>
        </div>

        <!-- Interactive Live Browser Frame (PMB UBSI Web Viewer) -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden" x-data="{ isFullscreen: false, loading: true }">
            <!-- Browser Mockup Header -->
            <div class="bg-slate-900 px-4 py-3 border-b border-slate-800 flex items-center justify-between gap-3 text-slate-300">
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span>
                    <span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span>
                    <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                    <span class="text-xs font-semibold text-slate-400 ml-2 hidden sm:inline">Web Portal PMB UBSI</span>
                </div>

                <!-- URL Bar -->
                <div class="flex-1 max-w-xl mx-2">
                    <div class="flex items-center bg-slate-800 rounded-lg px-3 py-1.5 text-xs text-slate-300 border border-slate-700/80">
                        <i data-lucide="lock" class="w-3.5 h-3.5 text-emerald-400 mr-2 shrink-0"></i>
                        <span class="truncate font-mono text-[11px] text-slate-200">https://pmbubsi.id/pmb</span>
                    </div>
                </div>

                <!-- Controls -->
                <div class="flex items-center space-x-1.5">
                    <button type="button" title="Muat Ulang Iframe"
                        onclick="document.getElementById('pmbFrame').src = document.getElementById('pmbFrame').src"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                        <i data-lucide="rotate-cw" class="w-4 h-4"></i>
                    </button>
                    <a href="https://pmbubsi.id/pmb" target="_blank" rel="noopener noreferrer" title="Buka di Tab Baru"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-amber-400 hover:bg-slate-800 transition">
                        <i data-lucide="external-link" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>

            <!-- Iframe Container -->
            <div class="relative bg-slate-100" :class="isFullscreen ? 'fixed inset-0 z-50 bg-white p-4' : 'min-h-[750px]'">
                <iframe id="pmbFrame"
                    src="https://pmbubsi.id/pmb"
                    class="w-full h-[750px] border-0"
                    loading="lazy"
                    title="Website PMB UBSI"
                    allow="clipboard-write">
                </iframe>
            </div>

            <!-- Frame Footer Bar -->
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-2">
                <div class="flex items-center space-x-2">
                    <i data-lucide="info" class="w-4 h-4 text-amber-500"></i>
                    <span>Jika tampilan portal terpotong atau dibatasi oleh pengaturan keamanan browser, gunakan tombol <strong>Buka di Tab Baru</strong>.</span>
                </div>
                <a href="https://pmbubsi.id/pmb" target="_blank" rel="noopener noreferrer"
                    class="text-brand-600 hover:text-brand-700 font-semibold inline-flex items-center space-x-1">
                    <span>Kunjungi pmbubsi.id</span>
                    <i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

        <!-- Helpdesk & Info Tambahan Kontak PMB UBSI -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <!-- 1. Kontak Layanan PMB -->
            <div class="p-5 bg-white rounded-2xl border border-slate-200/90 shadow-sm space-y-3">
                <div class="flex items-center space-x-3">
                    <div class="p-2.5 rounded-xl bg-brand-50 text-brand-600">
                        <i data-lucide="phone-call" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-slate-900">Kontak PMB UBSI</h4>
                        <p class="text-xs text-slate-500">Hotline Resmi Call Center</p>
                    </div>
                </div>
                <div class="space-y-2 text-xs text-slate-600 pt-2 border-t border-slate-100">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Telepon:</span>
                        <a href="tel:+62218000063" class="font-semibold text-slate-800 hover:text-brand-600">(021) 80 000 63</a>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Email PMB:</span>
                        <a href="mailto:pmb@bsi.ac.id" class="font-semibold text-slate-800 hover:text-brand-600">pmb@bsi.ac.id</a>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Website:</span>
                        <a href="https://pmbubsi.id" target="_blank" class="font-semibold text-brand-600 hover:underline">www.pmbubsi.id</a>
                    </div>
                </div>
            </div>

            <!-- 2. Kampus UBSI Karawang -->
            @php $appSettings = \App\Services\AppSettingService::all(); @endphp
            <div class="p-5 bg-white rounded-2xl border border-slate-200/90 shadow-sm space-y-3 overflow-hidden">
                @if(!empty($appSettings['campus_building_url']))
                    <div class="relative aspect-video rounded-xl overflow-hidden border border-slate-200 shadow-sm group">
                        <img src="{{ $appSettings['campus_building_url'] }}" alt="Gedung Kampus UBSI Karawang" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        <div class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-slate-900/80 backdrop-blur text-[9px] font-bold text-amber-300 border border-white/20">
                            Gedung Utama UBSI Karawang
                        </div>
                    </div>
                @endif
                <div class="flex items-center space-x-3">
                    <div class="p-2.5 rounded-xl bg-amber-50 text-amber-600">
                        <i data-lucide="building-2" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-slate-900">{{ $appSettings['campus_name'] ?? 'Kampus UBSI Karawang' }}</h4>
                        <p class="text-xs text-slate-500">Pusat Layanan Wilayah Karawang</p>
                    </div>
                </div>
                <div class="space-y-1 text-xs text-slate-600 pt-2 border-t border-slate-100">
                    <p class="font-semibold text-slate-800">{{ $appSettings['campus_address'] ?? 'Jl. Banten No. 1, Karangpawitan' }}</p>
                    <p class="text-[11px] text-amber-600 font-medium pt-1">Jam Operasional: Senin - Sabtu (08.00 - 17.00 WIB)</p>
                </div>
            </div>

            <!-- 3. Template Pesan WhatsApp Calon Maba -->
            <div class="p-5 bg-white rounded-2xl border border-slate-200/90 shadow-sm space-y-3">
                <div class="flex items-center space-x-3">
                    <div class="p-2.5 rounded-xl bg-emerald-50 text-emerald-600">
                        <i data-lucide="message-square" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-slate-900">Template Pesan PMB</h4>
                        <p class="text-xs text-slate-500">Pesan Cepat Follow Up Prospek</p>
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100 space-y-2">
                    <p class="text-[11px] text-slate-500 leading-snug">
                        Salin teks template info PMB untuk dikirim ke calon mahasiswa via WhatsApp:
                    </p>
                    <button type="button"
                        onclick="const text = 'Halo! Untuk informasi lengkap pendaftaran mahasiswa baru Universitas Bina Sarana Informatika (UBSI), rincian biaya kuliah per semester, gelombang pendaftaran, serta beasiswa, silakan kunjungi website resmi kami di https://pmbubsi.id/pmb atau hubungi Customer Service kami. Kuliah...? BSI Aja!'; navigator.clipboard.writeText(text); alert('Template pesan PMB UBSI berhasil disalin!');"
                        class="w-full py-2 px-3 rounded-lg text-xs font-bold bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 transition flex items-center justify-center space-x-1.5">
                        <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                        <span>Salin Template WhatsApp</span>
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
