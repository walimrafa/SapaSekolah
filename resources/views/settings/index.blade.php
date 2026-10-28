<x-app-layout title="Pengaturan Sistem & Branding">
    <div class="mb-6">
        <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Pengaturan Branding, Tampilan &amp; Kampus</h1>
        <p class="text-xs text-slate-500 mt-0.5">Kelola Logo, Nama Aplikasi, Header, Footer Dashboard, serta Informasi Kampus Acuan</p>
    </div>

    <div class="max-w-4xl">
        <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Form Card 1: Logo & Identitas Aplikasi -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft">
                <h3 class="text-sm font-bold text-slate-900 pb-3 mb-4 border-b border-slate-100 flex items-center gap-2">
                    <i data-lucide="layout-template" class="w-4 h-4 text-brand-600"></i>
                    <span>Branding Logo &amp; Nama Aplikasi</span>
                </h3>

                <div class="space-y-4 text-xs">
                    <!-- Logo Upload & Preview Section -->
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl flex flex-col sm:flex-row items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-center overflow-hidden shrink-0 shadow-md">
                            @if(!empty($settings['app_logo_url']))
                                <img src="{{ $settings['app_logo_url'] }}" alt="App Logo" class="w-full h-full object-contain p-1">
                            @else
                                <i data-lucide="map-pin" class="w-8 h-8 text-amber-400"></i>
                            @endif
                        </div>
                        <div class="flex-1 space-y-2">
                            <label class="block font-bold text-slate-700">Unggah Logo Aplikasi Baru (.png, .jpg, .svg)</label>
                            <input type="file" name="logo_file" accept="image/*"
                                class="w-full text-xs text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-600 file:text-white hover:file:bg-brand-700 cursor-pointer">
                            <p class="text-[10px] text-slate-400">Atau masukkan URL gambar logo langsung di bawah ini.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nama Utama Aplikasi <span class="text-red-500">*</span></label>
                            <input type="text" name="app_name" value="{{ old('app_name', $settings['app_name']) }}" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-extrabold text-slate-900">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Sub-Judul / Subtitle Aplikasi <span class="text-red-500">*</span></label>
                            <input type="text" name="app_subtitle" value="{{ old('app_subtitle', $settings['app_subtitle']) }}" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-semibold">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">URL Direct Gambar Logo (Opsional)</label>
                        <input type="text" name="app_logo_url" value="{{ old('app_logo_url', $settings['app_logo_url']) }}" placeholder="https://example.com/logo.png" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-mono">
                    </div>
                </div>
            </div>

            <!-- Form Card 2: Kustomisasi Palet Warna Tema (Manual) -->
            <div x-data="colorPaletteCustomizer()" class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 mb-4 border-b border-slate-100 gap-3">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="palette" class="w-4 h-4 text-rose-600"></i>
                        <span>Palet Warna Tema &amp; Tampilan Elegan</span>
                    </h3>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="applyOfficialPalette()"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white shadow-sm flex items-center gap-1.5 transition">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-400"></i>
                            <span>Terapkan Palet Resmi</span>
                        </button>
                        <button type="button" @click="resetDefaultColors()"
                            class="px-2.5 py-1.5 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition">
                            Reset
                        </button>
                    </div>
                </div>

                <p class="text-xs text-slate-500 mb-5">
                    Kustomisasi kombinasi warna tema website secara manual. Perubahan warna disimpan di sistem dan langsung diterapkan secara elegan ke seluruh sidebar navigasi, tombol utama, header tabel, dan kanvas latar belakang.
                </p>

                <!-- Color Showcase Bar (Visual Palette Preview) -->
                <div class="rounded-2xl overflow-hidden border border-slate-200 mb-6 shadow-sm">
                    <div class="grid grid-cols-1 md:grid-cols-4 text-xs font-bold">
                        <div class="p-4 transition-colors text-white flex flex-col justify-between h-24" :style="'background-color: ' + primaryColor">
                            <span class="text-[10px] tracking-wider uppercase opacity-80">Primary Color</span>
                            <div>
                                <p class="text-sm font-black">Visionary Navy</p>
                                <p class="font-mono text-[11px] opacity-90" x-text="primaryColor"></p>
                            </div>
                        </div>
                        <div class="p-4 transition-colors text-white flex flex-col justify-between h-24" :style="'background-color: ' + accentColor">
                            <span class="text-[10px] tracking-wider uppercase opacity-80">Accent / CTA Color</span>
                            <div>
                                <p class="text-sm font-black">Progressive Red</p>
                                <p class="font-mono text-[11px] opacity-90" x-text="accentColor"></p>
                            </div>
                        </div>
                        <div class="p-4 transition-colors text-white flex flex-col justify-between h-24" :style="'background-color: ' + secondaryColor">
                            <span class="text-[10px] tracking-wider uppercase opacity-80">Secondary Color</span>
                            <div>
                                <p class="text-sm font-black">Dark Charcoal</p>
                                <p class="font-mono text-[11px] opacity-90" x-text="secondaryColor"></p>
                            </div>
                        </div>
                        <div class="p-4 transition-colors text-slate-800 flex flex-col justify-between h-24 border-l border-slate-200" :style="'background-color: ' + bgColor">
                            <span class="text-[10px] tracking-wider uppercase text-slate-500">Accent Canvas / BG</span>
                            <div>
                                <p class="text-sm font-black text-slate-900">Light Grey</p>
                                <p class="font-mono text-[11px] text-slate-600" x-text="bgColor"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4 Interactive Color Editors -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs mb-6">
                    <!-- 1. Primary Color -->
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="font-bold text-slate-800 flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full border border-slate-300" :style="'background-color: ' + primaryColor"></span>
                                <span>Visionary Navy (Warna Primer)</span>
                            </label>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-slate-200 text-slate-700">Sidebar &amp; Brand</span>
                        </div>
                        <p class="text-[11px] text-slate-500">Warna utama untuk background sidebar, kartu sorotan, dan identitas brand.</p>
                        <div class="flex items-center space-x-2 pt-1">
                            <input type="color" x-model="primaryColor" class="w-10 h-10 p-1 rounded-xl border border-slate-300 cursor-pointer bg-white">
                            <input type="text" name="theme_color_primary" x-model="primaryColor" maxlength="7" required
                                class="flex-1 px-3 py-2 rounded-xl border border-slate-300 font-mono font-bold uppercase text-xs">
                        </div>
                    </div>

                    <!-- 2. Accent Color -->
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="font-bold text-slate-800 flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full border border-slate-300" :style="'background-color: ' + accentColor"></span>
                                <span>Progressive Red (Aksen &amp; Tombol CTA)</span>
                            </label>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-red-100 text-red-700">Aksi &amp; Indikator</span>
                        </div>
                        <p class="text-[11px] text-slate-500">Warna aksen energi untuk tombol aksi cepat, indikator menu aktif, dan badge sorotan.</p>
                        <div class="flex items-center space-x-2 pt-1">
                            <input type="color" x-model="accentColor" class="w-10 h-10 p-1 rounded-xl border border-slate-300 cursor-pointer bg-white">
                            <input type="text" name="theme_color_accent" x-model="accentColor" maxlength="7" required
                                class="flex-1 px-3 py-2 rounded-xl border border-slate-300 font-mono font-bold uppercase text-xs">
                        </div>
                    </div>

                    <!-- 3. Secondary Color -->
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="font-bold text-slate-800 flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full border border-slate-300" :style="'background-color: ' + secondaryColor"></span>
                                <span>Dark Charcoal (Warna Sekunder)</span>
                            </label>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-slate-800 text-slate-200">Table Header</span>
                        </div>
                        <p class="text-[11px] text-slate-500">Warna kontras gelap untuk kepala tabel data, strip judul, dan elemen pembatas.</p>
                        <div class="flex items-center space-x-2 pt-1">
                            <input type="color" x-model="secondaryColor" class="w-10 h-10 p-1 rounded-xl border border-slate-300 cursor-pointer bg-white">
                            <input type="text" name="theme_color_secondary" x-model="secondaryColor" maxlength="7" required
                                class="flex-1 px-3 py-2 rounded-xl border border-slate-300 font-mono font-bold uppercase text-xs">
                        </div>
                    </div>

                    <!-- 4. Background Color -->
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="font-bold text-slate-800 flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full border border-slate-300" :style="'background-color: ' + bgColor"></span>
                                <span>Light Grey (Kanvas / Background)</span>
                            </label>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-slate-200 text-slate-800">Latar Belakang</span>
                        </div>
                        <p class="text-[11px] text-slate-500">Warna lembut latar belakang halaman website agar konten terbaca elegan dan kontras.</p>
                        <div class="flex items-center space-x-2 pt-1">
                            <input type="color" x-model="bgColor" class="w-10 h-10 p-1 rounded-xl border border-slate-300 cursor-pointer bg-white">
                            <input type="text" name="theme_color_bg" x-model="bgColor" maxlength="7" required
                                class="flex-1 px-3 py-2 rounded-xl border border-slate-300 font-mono font-bold uppercase text-xs">
                        </div>
                    </div>
                </div>

                <!-- Live Mini-Interface Interactive Preview -->
                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-100/60">
                    <p class="text-[11px] font-bold text-slate-600 mb-2 flex items-center gap-1.5">
                        <i data-lucide="eye" class="w-3.5 h-3.5 text-brand-600"></i>
                        <span>Live Preview Tampilan Komponen (Simulasi Real-Time):</span>
                    </p>
                    <div class="rounded-xl overflow-hidden border border-slate-300 shadow-md flex h-36" :style="'background-color: ' + bgColor">
                        <!-- Mini Sidebar -->
                        <div class="w-44 p-3 text-white flex flex-col justify-between shrink-0" :style="'background-color: ' + primaryColor">
                            <div>
                                <div class="text-[10px] font-black tracking-wider uppercase opacity-90 flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full" :style="'background-color: ' + accentColor"></span>
                                    <span>SAPA SEKOLAH</span>
                                </div>
                                <div class="mt-3 space-y-1">
                                    <div class="px-2 py-1 rounded text-[9px] font-bold text-white shadow-sm flex items-center justify-between"
                                        :style="'background-color: ' + accentColor">
                                        <span>Dashboard</span>
                                        <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                    </div>
                                    <div class="px-2 py-1 rounded text-[9px] text-slate-300 opacity-70">
                                        Data &amp; GIS
                                    </div>
                                </div>
                            </div>
                            <div class="text-[8px] opacity-60">Sidebar Visionary Navy</div>
                        </div>

                        <!-- Mini Content -->
                        <div class="flex-1 p-3 flex flex-col justify-between overflow-hidden">
                            <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                                <span class="text-[10px] font-bold text-slate-800">Panel Konten</span>
                                <button type="button" class="px-2 py-0.5 rounded text-[9px] font-bold text-white shadow-xs"
                                    :style="'background-color: ' + accentColor">
                                    + Aksi Cepat
                                </button>
                            </div>
                            <div class="rounded overflow-hidden border border-slate-200 bg-white">
                                <div class="px-2 py-1 text-white text-[9px] font-bold flex justify-between" :style="'background-color: ' + secondaryColor">
                                    <span>Kolom Tabel</span>
                                    <span>Status</span>
                                </div>
                                <div class="px-2 py-1 text-[9px] text-slate-700 flex justify-between">
                                    <span>SMAN 1 Karawang</span>
                                    <span class="text-[8px] font-bold px-1 rounded" :style="'background-color: ' + accentColor + '20; color: ' + accentColor">Aktif</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Card 3: Kustomisasi Nama Menu & Sub-Menu Navigasi Sidebar -->
            <div x-data="{ 
                activeTab: 'gis',
                resetSidebarLabels() {
                    if (confirm('Kembalikan semua nama menu dan sub-menu navigasi sidebar ke nama default sistem?')) {
                        document.querySelectorAll('input[name^=\'sidebar_labels[\']').forEach(input => {
                            const def = input.getAttribute('data-default');
                            if (def !== null) {
                                input.value = def;
                            }
                        });
                    }
                }
            }" class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 mb-4 border-b border-slate-100 gap-3">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="menu-square" class="w-4 h-4 text-indigo-600"></i>
                        <span>Kustomisasi Nama Menu &amp; Sub-Menu Navigasi Sidebar</span>
                    </h3>
                    <button type="button" @click="resetSidebarLabels()"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 transition flex items-center gap-1.5 self-start sm:self-auto">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        <span>Reset Semua ke Default</span>
                    </button>
                </div>

                <p class="text-xs text-slate-500 mb-5">
                    Kustomisasi penamaan judul menu utama (accordion) dan seluruh sub-menu navigasi di bilah sidebar samping sesuai kebutuhan institusi atau terminologi internal kampus Anda.
                </p>

                <!-- Navigation Tabs for Menu Groups -->
                <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-3 mb-5 text-xs">
                    <button type="button" @click="activeTab = 'gis'"
                        :class="activeTab === 'gis' ? 'bg-slate-900 text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-semibold'"
                        class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5">
                        <i data-lucide="building-2" class="w-3.5 h-3.5 text-amber-400"></i>
                        <span>Dashboard &amp; GIS</span>
                    </button>
                    <button type="button" @click="activeTab = 'field'"
                        :class="activeTab === 'field' ? 'bg-slate-900 text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-semibold'"
                        class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5">
                        <i data-lucide="navigation-2" class="w-3.5 h-3.5 text-blue-400"></i>
                        <span>Operasional &amp; CS</span>
                    </button>
                    <button type="button" @click="activeTab = 'crm'"
                        :class="activeTab === 'crm' ? 'bg-slate-900 text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-semibold'"
                        class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5">
                        <i data-lucide="users" class="w-3.5 h-3.5 text-emerald-400"></i>
                        <span>Prospek &amp; CRM</span>
                    </button>
                    <button type="button" @click="activeTab = 'events'"
                        :class="activeTab === 'events' ? 'bg-slate-900 text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-semibold'"
                        class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-purple-400"></i>
                        <span>Support Event</span>
                    </button>
                    <button type="button" @click="activeTab = 'pmb'"
                        :class="activeTab === 'pmb' ? 'bg-slate-900 text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-semibold'"
                        class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5">
                        <i data-lucide="graduation-cap" class="w-3.5 h-3.5 text-amber-400"></i>
                        <span>Informasi PMB</span>
                    </button>
                    <button type="button" @click="activeTab = 'master'"
                        :class="activeTab === 'master' ? 'bg-slate-900 text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-semibold'"
                        class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5">
                        <i data-lucide="settings-2" class="w-3.5 h-3.5 text-rose-400"></i>
                        <span>Sistem &amp; Master</span>
                    </button>
                </div>

                <!-- Tab 1: Dashboard & GIS Sekolah -->
                <div x-show="activeTab === 'gis'" class="space-y-5 text-xs">
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
                        <div class="font-bold text-slate-800 flex items-center gap-2">
                            <i data-lucide="layout-dashboard" class="w-4 h-4 text-amber-500"></i>
                            <span>Menu Standalone Dashboard</span>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Nama Menu Dashboard</label>
                            <input type="text" name="sidebar_labels[menu_dashboard]"
                                value="{{ old('sidebar_labels.menu_dashboard', $settings['sidebar_labels']['menu_dashboard'] ?? 'Dashboard') }}"
                                data-default="Dashboard" placeholder="Default: Dashboard"
                                class="w-full px-3 py-2 rounded-xl border border-slate-300 font-semibold">
                        </div>
                    </div>

                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-4">
                        <div class="font-bold text-slate-800 flex items-center gap-2">
                            <i data-lucide="building-2" class="w-4 h-4 text-brand-600"></i>
                            <span>Kelompok Accordion Data &amp; GIS Sekolah</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Nama Menu Utama (Accordion)</label>
                                <input type="text" name="sidebar_labels[menu_gis]"
                                    value="{{ old('sidebar_labels.menu_gis', $settings['sidebar_labels']['menu_gis'] ?? 'Data & GIS Sekolah') }}"
                                    data-default="Data & GIS Sekolah" placeholder="Default: Data & GIS Sekolah"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-300 font-semibold">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Label Tag Badge Kanan</label>
                                <input type="text" name="sidebar_labels[badge_gis]"
                                    value="{{ old('sidebar_labels.badge_gis', $settings['sidebar_labels']['badge_gis'] ?? 'GIS') }}"
                                    data-default="GIS" placeholder="Default: GIS"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-300 font-semibold">
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-200">
                            <p class="text-[11px] font-bold text-slate-500 mb-3">Sub-Menu Navigasi GIS Sekolah:</p>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Sub-Menu Peta</label>
                                    <input type="text" name="sidebar_labels[submenu_map]"
                                        value="{{ old('sidebar_labels.submenu_map', $settings['sidebar_labels']['submenu_map'] ?? 'Peta Sekolah') }}"
                                        data-default="Peta Sekolah" placeholder="Peta Sekolah"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Sub-Menu Data Sekolah</label>
                                    <input type="text" name="sidebar_labels[submenu_schools]"
                                        value="{{ old('sidebar_labels.submenu_schools', $settings['sidebar_labels']['submenu_schools'] ?? 'Data Sekolah') }}"
                                        data-default="Data Sekolah" placeholder="Data Sekolah"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Sub-Menu Prioritas</label>
                                    <input type="text" name="sidebar_labels[submenu_priorities]"
                                        value="{{ old('sidebar_labels.submenu_priorities', $settings['sidebar_labels']['submenu_priorities'] ?? 'Prioritas Sekolah') }}"
                                        data-default="Prioritas Sekolah" placeholder="Prioritas Sekolah"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Operasional Lapangan & Customer Service -->
                <div x-show="activeTab === 'field'" class="space-y-5 text-xs" style="display: none;">
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-4">
                        <div class="font-bold text-slate-800 flex items-center gap-2">
                            <i data-lucide="navigation-2" class="w-4 h-4 text-blue-500"></i>
                            <span>Kelompok Operasional Lapangan (Marketing &amp; Pimpinan)</span>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Nama Menu Utama (Accordion)</label>
                            <input type="text" name="sidebar_labels[menu_field]"
                                value="{{ old('sidebar_labels.menu_field', $settings['sidebar_labels']['menu_field'] ?? 'Operasional Lapangan') }}"
                                data-default="Operasional Lapangan" placeholder="Operasional Lapangan"
                                class="w-full px-3 py-2 rounded-xl border border-slate-300 font-semibold">
                        </div>

                        <div class="pt-2 border-t border-slate-200">
                            <p class="text-[11px] font-bold text-slate-500 mb-3">Sub-Menu Operasional Lapangan:</p>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Rencana Kunjungan</label>
                                    <input type="text" name="sidebar_labels[submenu_visit_plans]"
                                        value="{{ old('sidebar_labels.submenu_visit_plans', $settings['sidebar_labels']['submenu_visit_plans'] ?? 'Rencana Kunjungan') }}"
                                        data-default="Rencana Kunjungan" placeholder="Rencana Kunjungan"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Kunjungan</label>
                                    <input type="text" name="sidebar_labels[submenu_visits]"
                                        value="{{ old('sidebar_labels.submenu_visits', $settings['sidebar_labels']['submenu_visits'] ?? 'Kunjungan') }}"
                                        data-default="Kunjungan" placeholder="Kunjungan"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Follow Up</label>
                                    <input type="text" name="sidebar_labels[submenu_field_followups]"
                                        value="{{ old('sidebar_labels.submenu_field_followups', $settings['sidebar_labels']['submenu_field_followups'] ?? 'Follow Up') }}"
                                        data-default="Follow Up" placeholder="Follow Up"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Sekolah Sudah Sosialisasi</label>
                                    <input type="text" name="sidebar_labels[submenu_socializations_completed]"
                                        value="{{ old('sidebar_labels.submenu_socializations_completed', $settings['sidebar_labels']['submenu_socializations_completed'] ?? 'Sekolah Sudah Sosialisasi') }}"
                                        data-default="Sekolah Sudah Sosialisasi" placeholder="Sekolah Sudah Sosialisasi"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Rute Marketing</label>
                                    <input type="text" name="sidebar_labels[submenu_marketing_routes]"
                                        value="{{ old('sidebar_labels.submenu_marketing_routes', $settings['sidebar_labels']['submenu_marketing_routes'] ?? 'Rute Marketing') }}"
                                        data-default="Rute Marketing" placeholder="Rute Marketing"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Aktivitas Marketing</label>
                                    <input type="text" name="sidebar_labels[submenu_activities]"
                                        value="{{ old('sidebar_labels.submenu_activities', $settings['sidebar_labels']['submenu_activities'] ?? 'Aktivitas Marketing') }}"
                                        data-default="Aktivitas Marketing" placeholder="Aktivitas Marketing"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-4">
                        <div class="font-bold text-slate-800 flex items-center gap-2">
                            <i data-lucide="phone-call" class="w-4 h-4 text-emerald-500"></i>
                            <span>Kelompok Khusus Customer Service (Interaksi &amp; Kontak)</span>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Nama Menu Utama CS (Accordion)</label>
                            <input type="text" name="sidebar_labels[menu_cs_interactions]"
                                value="{{ old('sidebar_labels.menu_cs_interactions', $settings['sidebar_labels']['menu_cs_interactions'] ?? 'Interaksi & Kontak') }}"
                                data-default="Interaksi & Kontak" placeholder="Interaksi & Kontak"
                                class="w-full px-3 py-2 rounded-xl border border-slate-300 font-semibold">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2 border-t border-slate-200">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Sub-Menu Follow Up Sekolah</label>
                                <input type="text" name="sidebar_labels[submenu_cs_followups]"
                                    value="{{ old('sidebar_labels.submenu_cs_followups', $settings['sidebar_labels']['submenu_cs_followups'] ?? 'Follow Up Sekolah') }}"
                                    data-default="Follow Up Sekolah" placeholder="Follow Up Sekolah"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-300">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Sub-Menu Master QR Code (CS)</label>
                                <input type="text" name="sidebar_labels[submenu_cs_qrcodes]"
                                    value="{{ old('sidebar_labels.submenu_cs_qrcodes', $settings['sidebar_labels']['submenu_cs_qrcodes'] ?? 'Master QR Code') }}"
                                    data-default="Master QR Code" placeholder="Master QR Code"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-300">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 3: CRM (Prospek & Konversi) -->
                <div x-show="activeTab === 'crm'" class="space-y-4 text-xs" style="display: none;">
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-4">
                        <div class="font-bold text-slate-800 flex items-center gap-2">
                            <i data-lucide="users" class="w-4 h-4 text-emerald-600"></i>
                            <span>Kelompok Accordion CRM &amp; Prospek Siswa</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Nama Menu Utama (Accordion)</label>
                                <input type="text" name="sidebar_labels[menu_crm]"
                                    value="{{ old('sidebar_labels.menu_crm', $settings['sidebar_labels']['menu_crm'] ?? 'Prospek & Konversi') }}"
                                    data-default="Prospek & Konversi" placeholder="Prospek & Konversi"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-300 font-semibold">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Label Tag Badge Kanan</label>
                                <input type="text" name="sidebar_labels[badge_crm]"
                                    value="{{ old('sidebar_labels.badge_crm', $settings['sidebar_labels']['badge_crm'] ?? 'CRM') }}"
                                    data-default="CRM" placeholder="CRM"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-300 font-semibold">
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-200">
                            <p class="text-[11px] font-bold text-slate-500 mb-3">Sub-Menu Navigasi CRM:</p>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Data Prospek</label>
                                    <input type="text" name="sidebar_labels[submenu_prospects]"
                                        value="{{ old('sidebar_labels.submenu_prospects', $settings['sidebar_labels']['submenu_prospects'] ?? 'Data Prospek') }}"
                                        data-default="Data Prospek" placeholder="Data Prospek"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Mahasiswa Baru</label>
                                    <input type="text" name="sidebar_labels[submenu_new_students]"
                                        value="{{ old('sidebar_labels.submenu_new_students', $settings['sidebar_labels']['submenu_new_students'] ?? 'Mahasiswa Baru') }}"
                                        data-default="Mahasiswa Baru" placeholder="Mahasiswa Baru"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Hasil Kuisioner</label>
                                    <input type="text" name="sidebar_labels[submenu_questionnaires]"
                                        value="{{ old('sidebar_labels.submenu_questionnaires', $settings['sidebar_labels']['submenu_questionnaires'] ?? 'Hasil Kuisioner') }}"
                                        data-default="Hasil Kuisioner" placeholder="Hasil Kuisioner"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Follow Up Siswa</label>
                                    <input type="text" name="sidebar_labels[submenu_student_followups]"
                                        value="{{ old('sidebar_labels.submenu_student_followups', $settings['sidebar_labels']['submenu_student_followups'] ?? 'Follow Up Siswa') }}"
                                        data-default="Follow Up Siswa" placeholder="Follow Up Siswa"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Program Studi</label>
                                    <input type="text" name="sidebar_labels[submenu_study_programs]"
                                        value="{{ old('sidebar_labels.submenu_study_programs', $settings['sidebar_labels']['submenu_study_programs'] ?? 'Program Studi') }}"
                                        data-default="Program Studi" placeholder="Program Studi"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Laporan &amp; Analytics</label>
                                    <input type="text" name="sidebar_labels[submenu_reports]"
                                        value="{{ old('sidebar_labels.submenu_reports', $settings['sidebar_labels']['submenu_reports'] ?? 'Laporan & Analytics') }}"
                                        data-default="Laporan & Analytics" placeholder="Laporan & Analytics"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 4: Support Event Sekolah -->
                <div x-show="activeTab === 'events'" class="space-y-4 text-xs" style="display: none;">
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-4">
                        <div class="font-bold text-slate-800 flex items-center gap-2">
                            <i data-lucide="sparkles" class="w-4 h-4 text-purple-600"></i>
                            <span>Kelompok Accordion Support Event Sekolah</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Nama Menu Utama (Accordion)</label>
                                <input type="text" name="sidebar_labels[menu_events]"
                                    value="{{ old('sidebar_labels.menu_events', $settings['sidebar_labels']['menu_events'] ?? 'Support Event Sekolah') }}"
                                    data-default="Support Event Sekolah" placeholder="Support Event Sekolah"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-300 font-semibold">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Label Tag Badge Kanan</label>
                                <input type="text" name="sidebar_labels[badge_events]"
                                    value="{{ old('sidebar_labels.badge_events', $settings['sidebar_labels']['badge_events'] ?? 'Event') }}"
                                    data-default="Event" placeholder="Event"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-300 font-semibold">
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-200">
                            <p class="text-[11px] font-bold text-slate-500 mb-3">Sub-Menu Navigasi Event:</p>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Data Proposal Masuk</label>
                                    <input type="text" name="sidebar_labels[submenu_event_proposals]"
                                        value="{{ old('sidebar_labels.submenu_event_proposals', $settings['sidebar_labels']['submenu_event_proposals'] ?? 'Data Proposal Masuk') }}"
                                        data-default="Data Proposal Masuk" placeholder="Data Proposal Masuk"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Laporan Suport Kegiatan</label>
                                    <input type="text" name="sidebar_labels[submenu_event_reports]"
                                        value="{{ old('sidebar_labels.submenu_event_reports', $settings['sidebar_labels']['submenu_event_reports'] ?? 'Laporan Suport Kegiatan') }}"
                                        data-default="Laporan Suport Kegiatan" placeholder="Laporan Suport Kegiatan"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Hasil Support Event</label>
                                    <input type="text" name="sidebar_labels[submenu_event_students]"
                                        value="{{ old('sidebar_labels.submenu_event_students', $settings['sidebar_labels']['submenu_event_students'] ?? 'Hasil Support Event') }}"
                                        data-default="Hasil Support Event" placeholder="Hasil Support Event"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 5: Informasi PMB -->
                <div x-show="activeTab === 'pmb'" class="space-y-4 text-xs" style="display: none;">
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-4">
                        <div class="font-bold text-slate-800 flex items-center gap-2">
                            <i data-lucide="graduation-cap" class="w-4 h-4 text-amber-500"></i>
                            <span>Kelompok Accordion Informasi PMB</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Nama Menu Utama (Accordion)</label>
                                <input type="text" name="sidebar_labels[menu_pmb]"
                                    value="{{ old('sidebar_labels.menu_pmb', $settings['sidebar_labels']['menu_pmb'] ?? 'Informasi PMB') }}"
                                    data-default="Informasi PMB" placeholder="Informasi PMB"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-300 font-semibold">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Label Tag Badge Kanan</label>
                                <input type="text" name="sidebar_labels[badge_pmb]"
                                    value="{{ old('sidebar_labels.badge_pmb', $settings['sidebar_labels']['badge_pmb'] ?? 'UBSI') }}"
                                    data-default="UBSI" placeholder="UBSI"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-300 font-semibold">
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-200">
                            <p class="text-[11px] font-bold text-slate-500 mb-3">Sub-Menu Internal &amp; Tautan PMB:</p>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Pusat Info &amp; Portal</label>
                                    <input type="text" name="sidebar_labels[submenu_pmb_portal]"
                                        value="{{ old('sidebar_labels.submenu_pmb_portal', $settings['sidebar_labels']['submenu_pmb_portal'] ?? 'Pusat Info & Portal') }}"
                                        data-default="Pusat Info & Portal" placeholder="Pusat Info & Portal"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">E-Brosur PMB</label>
                                    <input type="text" name="sidebar_labels[submenu_ebrochures]"
                                        value="{{ old('sidebar_labels.submenu_ebrochures', $settings['sidebar_labels']['submenu_ebrochures'] ?? 'E-Brosur PMB') }}"
                                        data-default="E-Brosur PMB" placeholder="E-Brosur PMB"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Link: Website PMB</label>
                                    <input type="text" name="sidebar_labels[submenu_pmb_web]"
                                        value="{{ old('sidebar_labels.submenu_pmb_web', $settings['sidebar_labels']['submenu_pmb_web'] ?? 'Website PMB UBSI') }}"
                                        data-default="Website PMB UBSI" placeholder="Website PMB UBSI"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Link: Kampus &amp; Biaya Kuliah</label>
                                    <input type="text" name="sidebar_labels[submenu_pmb_campus]"
                                        value="{{ old('sidebar_labels.submenu_pmb_campus', $settings['sidebar_labels']['submenu_pmb_campus'] ?? 'Kampus & Biaya Kuliah') }}"
                                        data-default="Kampus & Biaya Kuliah" placeholder="Kampus & Biaya Kuliah"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Link: Jadwal &amp; Gelombang</label>
                                    <input type="text" name="sidebar_labels[submenu_pmb_schedule]"
                                        value="{{ old('sidebar_labels.submenu_pmb_schedule', $settings['sidebar_labels']['submenu_pmb_schedule'] ?? 'Jadwal & Gelombang') }}"
                                        data-default="Jadwal & Gelombang" placeholder="Jadwal & Gelombang"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Link: Alur Pendaftaran</label>
                                    <input type="text" name="sidebar_labels[submenu_pmb_procedure]"
                                        value="{{ old('sidebar_labels.submenu_pmb_procedure', $settings['sidebar_labels']['submenu_pmb_procedure'] ?? 'Alur Pendaftaran') }}"
                                        data-default="Alur Pendaftaran" placeholder="Alur Pendaftaran"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Link: Beasiswa PMB</label>
                                    <input type="text" name="sidebar_labels[submenu_pmb_scholarship]"
                                        value="{{ old('sidebar_labels.submenu_pmb_scholarship', $settings['sidebar_labels']['submenu_pmb_scholarship'] ?? 'Beasiswa UBSI 2026') }}"
                                        data-default="Beasiswa UBSI 2026" placeholder="Beasiswa UBSI 2026"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 6: Sistem & Master -->
                <div x-show="activeTab === 'master'" class="space-y-4 text-xs" style="display: none;">
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-4">
                        <div class="font-bold text-slate-800 flex items-center gap-2">
                            <i data-lucide="settings-2" class="w-4 h-4 text-rose-500"></i>
                            <span>Kelompok Accordion Sistem &amp; Master</span>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Nama Menu Utama (Accordion)</label>
                            <input type="text" name="sidebar_labels[menu_master]"
                                value="{{ old('sidebar_labels.menu_master', $settings['sidebar_labels']['menu_master'] ?? 'Sistem & Master') }}"
                                data-default="Sistem & Master" placeholder="Sistem & Master"
                                class="w-full px-3 py-2 rounded-xl border border-slate-300 font-semibold">
                        </div>

                        <div class="pt-2 border-t border-slate-200">
                            <p class="text-[11px] font-bold text-slate-500 mb-3">Sub-Menu Navigasi Sistem &amp; Master:</p>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Master Data</label>
                                    <input type="text" name="sidebar_labels[submenu_master_data]"
                                        value="{{ old('sidebar_labels.submenu_master_data', $settings['sidebar_labels']['submenu_master_data'] ?? 'Master Data') }}"
                                        data-default="Master Data" placeholder="Master Data"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">User Management</label>
                                    <input type="text" name="sidebar_labels[submenu_users]"
                                        value="{{ old('sidebar_labels.submenu_users', $settings['sidebar_labels']['submenu_users'] ?? 'User Management') }}"
                                        data-default="User Management" placeholder="User Management"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Master QR Code</label>
                                    <input type="text" name="sidebar_labels[submenu_qr_codes]"
                                        value="{{ old('sidebar_labels.submenu_qr_codes', $settings['sidebar_labels']['submenu_qr_codes'] ?? 'Master QR Code') }}"
                                        data-default="Master QR Code" placeholder="Master QR Code"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Notifikasi WA</label>
                                    <input type="text" name="sidebar_labels[submenu_whatsapp]"
                                        value="{{ old('sidebar_labels.submenu_whatsapp', $settings['sidebar_labels']['submenu_whatsapp'] ?? 'Notifikasi WA') }}"
                                        data-default="Notifikasi WA" placeholder="Notifikasi WA"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Pengaturan</label>
                                    <input type="text" name="sidebar_labels[submenu_settings]"
                                        value="{{ old('sidebar_labels.submenu_settings', $settings['sidebar_labels']['submenu_settings'] ?? 'Pengaturan') }}"
                                        data-default="Pengaturan" placeholder="Pengaturan"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Tutup Periode</label>
                                    <input type="text" name="sidebar_labels[submenu_period_closing]"
                                        value="{{ old('sidebar_labels.submenu_period_closing', $settings['sidebar_labels']['submenu_period_closing'] ?? 'Tutup Periode') }}"
                                        data-default="Tutup Periode" placeholder="Tutup Periode"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-300">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Card 4: Pengaturan Header & Footer Dashboard -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft">
                <h3 class="text-sm font-bold text-slate-900 pb-3 mb-4 border-b border-slate-100 flex items-center gap-2">
                    <i data-lucide="panels-top-left" class="w-4 h-4 text-emerald-600"></i>
                    <span>Teks Header Navbar &amp; Footer Dashboard</span>
                </h3>

                <div class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Judul Default Banner Top Header Navbar <span class="text-red-500">*</span></label>
                        <input type="text" name="header_title" value="{{ old('header_title', $settings['header_title']) }}" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-bold">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Teks Hak Cipta Footer Utama <span class="text-red-500">*</span></label>
                            <input type="text" name="footer_text" value="{{ old('footer_text', $settings['footer_text']) }}" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Sub-Teks Lokasi Footer <span class="text-red-500">*</span></label>
                            <input type="text" name="footer_subtext" value="{{ old('footer_subtext', $settings['footer_subtext']) }}" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Card 3: Identitas Kampus Acuan (UBSI Karawang) -->
            <div x-data="{
                buildingPreview: '{{ $settings['campus_building_url'] ?? '' }}',
                initialPreview: '{{ $settings['campus_building_url'] ?? '' }}',
                directUrl: '{{ old('campus_building_url', $settings['campus_building_url'] ?? '') }}',
                isRemoved: false,
                fileName: '',
                fileSize: '',
                isDragging: false,

                handleFile(file) {
                    if (!file) return;
                    if (!file.type.match(/^image\/(jpeg|png|jpg|webp|gif)$/i)) {
                        alert('Format file tidak didukung. Harap pilih gambar dengan format JPG, JPEG, PNG, WEBP, atau GIF.');
                        return;
                    }
                    if (file.size > 5 * 1024 * 1024) {
                        alert('Ukuran file melebihi batas maksimal 5MB.');
                        return;
                    }
                    this.isRemoved = false;
                    this.fileName = file.name;
                    this.fileSize = (file.size / 1024).toFixed(1) + ' KB';

                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.buildingPreview = e.target.result;
                    };
                    reader.readAsDataURL(file);
                },

                handleBuildingFile(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.handleFile(file);
                    }
                },

                handleDrop(event) {
                    this.isDragging = false;
                    const file = event.dataTransfer.files[0];
                    if (file) {
                        if (this.$refs.buildingFileInput) {
                            this.$refs.buildingFileInput.files = event.dataTransfer.files;
                        }
                        this.handleFile(file);
                    }
                },

                removeBuilding() {
                    this.buildingPreview = '';
                    this.directUrl = '';
                    this.fileName = '';
                    this.fileSize = '';
                    this.isRemoved = true;
                    if (this.$refs.buildingFileInput) {
                        this.$refs.buildingFileInput.value = '';
                    }
                },

                resetToInitial() {
                    this.buildingPreview = this.initialPreview;
                    this.directUrl = this.initialPreview;
                    this.fileName = '';
                    this.fileSize = '';
                    this.isRemoved = false;
                    if (this.$refs.buildingFileInput) {
                        this.$refs.buildingFileInput.value = '';
                    }
                }
            }" class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft">
                <input type="hidden" name="remove_building_photo" :value="isRemoved ? '1' : '0'">

                <h3 class="text-sm font-bold text-slate-900 pb-3 mb-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="building-2" class="w-4 h-4 text-amber-500"></i>
                        <span>Informasi Kampus Acuan &amp; Foto Gedung</span>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-500">UBSI Kampus Karawang</span>
                </h3>

                <!-- Dedicated Gedung Upload Box (Stacked, Super Prominent & Never Clipped) -->
                <div class="mb-6 space-y-4">
                    <!-- 1. The Big Visual Image Preview Box -->
                    <div class="relative w-full aspect-video rounded-2xl overflow-hidden border-2 border-slate-300 shadow-md bg-slate-900 flex items-center justify-center group cursor-pointer"
                         @click="$refs.buildingFileInput.click()"
                         title="Klik untuk memilih foto gedung">

                        <template x-if="buildingPreview">
                            <img :src="buildingPreview" alt="Gedung Kampus" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                        </template>

                        <template x-if="!buildingPreview">
                            <div class="flex flex-col items-center justify-center p-6 text-center text-white">
                                <div class="w-14 h-14 rounded-2xl bg-slate-800 flex items-center justify-center text-amber-400 mb-3 border border-slate-700 shadow-inner">
                                    <i data-lucide="image-plus" class="w-7 h-7"></i>
                                </div>
                                <p class="text-sm font-extrabold text-white">Belum Ada Foto Gedung</p>
                                <p class="text-xs text-slate-300 mt-1">Klik di sini atau gunakan tombol kuning di bawah untuk memilih foto dari komputer</p>
                            </div>
                        </template>

                        <!-- Top Status Badges -->
                        <div class="absolute top-3 left-3 flex items-center gap-2">
                            <template x-if="fileName">
                                <div class="px-3 py-1.5 rounded-xl bg-emerald-900/90 backdrop-blur border border-emerald-400 text-xs font-bold text-emerald-200 flex items-center gap-2 shadow-lg">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                                    <span x-text="'Foto Baru: ' + fileName"></span>
                                </div>
                            </template>
                            <template x-if="!fileName && buildingPreview">
                                <div class="px-3 py-1.5 rounded-xl bg-slate-900/90 backdrop-blur border border-amber-400 text-xs font-black uppercase tracking-wider text-amber-300 flex items-center gap-2 shadow-lg">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    <span>Gedung Utama Aktif</span>
                                </div>
                            </template>
                        </div>

                        <!-- Hover Overlay -->
                        <div class="absolute inset-0 bg-slate-950/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <span class="px-4 py-2 rounded-xl bg-white/95 backdrop-blur text-slate-900 text-xs font-extrabold shadow-lg flex items-center gap-2">
                                <i data-lucide="camera" class="w-4 h-4 text-amber-600"></i>
                                <span>Klik untuk Ganti Foto Gedung</span>
                            </span>
                        </div>
                    </div>

                    <!-- 2. Action Buttons & Status Directly Under Preview -->
                    <div class="flex flex-wrap items-center justify-between gap-3 p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                        <div class="flex items-center gap-2 text-slate-600 font-semibold">
                            <i data-lucide="info" class="w-4 h-4 text-brand-600 shrink-0"></i>
                            <span>Rasio Rekomendasi: <strong>16:9</strong> (Landscape) • Format: JPG, PNG, WEBP, GIF (Maks. 5MB)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <template x-if="fileName || (isRemoved && initialPreview)">
                                <button type="button" @click="resetToInitial()"
                                    class="px-3 py-1.5 rounded-lg bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 font-bold transition flex items-center gap-1.5 shadow-sm">
                                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5 text-amber-600"></i>
                                    <span>Reset ke Foto Awal</span>
                                </button>
                            </template>
                            <template x-if="buildingPreview">
                                <button type="button" @click="removeBuilding()"
                                    class="px-3 py-1.5 rounded-lg bg-rose-50 border border-rose-200 hover:bg-rose-100 text-rose-700 font-bold transition flex items-center gap-1.5 shadow-sm">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    <span>Hapus Foto</span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- 3. Big Prominent Manual Upload Dropzone / Button -->
                    <div @dragover.prevent="isDragging = true"
                         @dragleave.prevent="isDragging = false"
                         @drop.prevent="handleDrop($event)"
                         :class="isDragging ? 'border-amber-500 bg-amber-50 ring-2 ring-amber-400' : 'border-slate-300 bg-amber-50/50 hover:bg-amber-50 hover:border-amber-400'"
                         class="border-2 border-dashed rounded-2xl p-6 text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-3"
                         @click="$refs.buildingFileInput.click()">

                        <div class="w-14 h-14 rounded-2xl bg-amber-500 text-slate-900 flex items-center justify-center shadow-md">
                            <i data-lucide="upload-cloud" class="w-7 h-7"></i>
                        </div>

                        <div>
                            <h4 class="text-sm font-extrabold text-slate-900">
                                Klik di Sini untuk Memilih Foto Gedung dari Komputer
                            </h4>
                            <p class="text-xs text-slate-600 mt-1">
                                atau Tarik dan Jatuhkan (Drag &amp; Drop) file gambar langsung ke kotak ini
                            </p>
                        </div>

                        <div class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-900 font-black text-xs shadow-md transition">
                            <i data-lucide="folder-open" class="w-4 h-4"></i>
                            <span>PILIH FILE FOTO GEDUNG MANUAL</span>
                        </div>
                    </div>

                    <!-- Hidden Input File -->
                    <input type="file" name="building_file" accept="image/jpeg,image/png,image/jpg,image/webp,image/gif"
                        x-ref="buildingFileInput"
                        @change="handleBuildingFile($event)"
                        class="hidden">

                    <!-- File Chosen Banner -->
                    <template x-if="fileName">
                        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-300 flex items-center justify-between gap-3 text-xs shadow-sm">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-8 h-8 rounded-lg bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                                    <i data-lucide="check" class="w-5 h-5"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-extrabold text-emerald-900 truncate" x-text="fileName"></p>
                                    <p class="text-[11px] text-emerald-700" x-text="'Ukuran: ' + fileSize + ' • Siap disimpan saat Anda klik Simpan Seluruh Pengaturan di bawah.'"></p>
                                </div>
                            </div>
                            <button type="button" @click="$refs.buildingFileInput.click()"
                                class="px-3 py-1.5 rounded-lg bg-white border border-emerald-300 text-emerald-800 font-bold hover:bg-emerald-100 transition shrink-0">
                                Ganti File Lain
                            </button>
                        </div>
                    </template>

                    <!-- Direct URL Option -->
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-1.5 text-xs">
                        <label class="block font-bold text-slate-700">
                            Atau Masukkan Direct URL Gambar (Opsional jika foto di-hosting eksternal):
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="link" class="w-4 h-4"></i>
                            </div>
                            <input type="text" name="campus_building_url" x-model="directUrl"
                                @input="buildingPreview = directUrl; isRemoved = false;"
                                placeholder="https://domain.ac.id/images/gedung-kampus.jpg"
                                class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 font-mono text-slate-800 focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>
                </div>

                <div class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Perguruan Tinggi / Kampus Acuan <span class="text-red-500">*</span></label>
                        <input type="text" name="campus_name" value="{{ old('campus_name', $settings['campus_name']) }}" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-bold">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Alamat Resmi Kampus <span class="text-red-500">*</span></label>
                        <textarea name="campus_address" rows="2" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300">{{ old('campus_address', $settings['campus_address']) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Latitude (Titik Lintang Acuan Jarak) <span class="text-red-500">*</span></label>
                            <input type="text" name="campus_latitude" value="{{ old('campus_latitude', $settings['campus_latitude']) }}" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-mono font-bold text-emerald-700">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Longitude (Titik Bujur Acuan Jarak) <span class="text-red-500">*</span></label>
                            <input type="text" name="campus_longitude" value="{{ old('campus_longitude', $settings['campus_longitude']) }}" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-mono font-bold text-emerald-700">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Telepon Resmi</label>
                            <input type="text" name="campus_phone" value="{{ old('campus_phone', $settings['campus_phone']) }}" class="w-full px-3 py-2.5 rounded-xl border border-slate-300">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Email Resmi</label>
                            <input type="email" name="campus_email" value="{{ old('campus_email', $settings['campus_email']) }}" class="w-full px-3 py-2.5 rounded-xl border border-slate-300">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Website Kampus</label>
                            <input type="text" name="campus_website" value="{{ old('campus_website', $settings['campus_website']) }}" class="w-full px-3 py-2.5 rounded-xl border border-slate-300">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-brand-navy hover:bg-slate-900 text-white font-bold text-xs rounded-xl shadow-lg transition flex items-center space-x-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Seluruh Pengaturan</span>
                </button>
            </div>
        </form>

        <!-- Danger Zone: Tutup Periode Pemasaran (Otorisasi 3 Pihak) -->
        <div class="mt-10 pt-8 border-t border-red-200" id="danger-zone">
            <div class="bg-gradient-to-br from-white via-red-50/30 to-rose-50/50 p-6 sm:p-7 rounded-2xl border border-red-200 shadow-sm relative overflow-hidden">
                <!-- Background subtle accent -->
                <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-red-500/5 rounded-full blur-2xl pointer-events-none"></div>

                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6 relative z-10">
                    <div class="space-y-3 max-w-2xl">
                        <div class="flex items-center gap-2.5">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-red-100 text-red-600 shadow-sm shrink-0">
                                <i data-lucide="shield-alert" class="w-4 h-4"></i>
                            </span>
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                                    <span>Zona Bahaya: Tutup Periode &amp; Reset Data Pemasaran</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-red-100 text-red-700 border border-red-200">Admin Only</span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">Membuka siklus pemasaran baru dengan meriset seluruh data operasional ke nol melalui <strong>persetujuan mutlak 3 pihak</strong>.</p>
                            </div>
                        </div>

                        <!-- Otorisasi 3 Pihak Explanation Notice -->
                        <div class="p-3 bg-amber-50/80 rounded-xl border border-amber-200 text-amber-900 text-xs">
                            <p class="font-bold flex items-center gap-1.5 mb-1 text-amber-800">
                                <i data-lucide="info" class="w-4 h-4 text-amber-600 shrink-0"></i>
                                <span>Mekanisme Otorisasi 3 Pihak (Pimpinan, Koordinator Marketing &amp; CS):</span>
                            </p>
                            <p class="text-[11px] text-amber-800/90 leading-relaxed">
                                Ketika diajukan oleh Admin, data <strong>TIDAK langsung direset</strong>. Permohonan membutuhkan persetujuan dari <strong>Pimpinan Kampus</strong>, <strong>Koordinator Marketing</strong>, dan <strong>Customer Service</strong>. Jika salah satu menolak, permohonan batal dan data tidak berubah. Reset hanya diproses apabila <strong>ketiganya menyetujui</strong>.
                            </p>
                        </div>

                        @if(!empty($activePeriodRequest))
                            <!-- Active Request In-Progress Banner -->
                            <div class="p-4 bg-white rounded-xl border-2 border-red-300 shadow-sm space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-extrabold text-red-700 flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-ping"></span>
                                        <span>Permohonan Tutup Periode Sedang Berjalan ({{ $activePeriodRequest->approvedCount() }}/3 Disetujui)</span>
                                    </span>
                                    <a href="{{ route('period-closing.index') }}" class="text-xs font-bold text-red-600 hover:text-red-800 underline">
                                        Buka Detail Tracker &rarr;
                                    </a>
                                </div>
                                <div class="grid grid-cols-3 gap-2 text-[11px] pt-1">
                                    <div class="p-2 rounded-lg text-center {{ $activePeriodRequest->pimpinan_status === 'approved' ? 'bg-emerald-50 text-emerald-800 font-bold' : ($activePeriodRequest->pimpinan_status === 'rejected' ? 'bg-red-50 text-red-800 font-bold' : 'bg-slate-100 text-slate-600') }}">
                                        Pimpinan: {{ $activePeriodRequest->pimpinan_status === 'approved' ? '✓ Setuju' : ($activePeriodRequest->pimpinan_status === 'rejected' ? '✗ Tolak' : '⏳ Menunggu') }}
                                    </div>
                                    <div class="p-2 rounded-lg text-center {{ $activePeriodRequest->koordinator_status === 'approved' ? 'bg-emerald-50 text-emerald-800 font-bold' : ($activePeriodRequest->koordinator_status === 'rejected' ? 'bg-red-50 text-red-800 font-bold' : 'bg-slate-100 text-slate-600') }}">
                                        Koordinator: {{ $activePeriodRequest->koordinator_status === 'approved' ? '✓ Setuju' : ($activePeriodRequest->koordinator_status === 'rejected' ? '✗ Tolak' : '⏳ Menunggu') }}
                                    </div>
                                    <div class="p-2 rounded-lg text-center {{ $activePeriodRequest->cs_status === 'approved' ? 'bg-emerald-50 text-emerald-800 font-bold' : ($activePeriodRequest->cs_status === 'rejected' ? 'bg-red-50 text-red-800 font-bold' : 'bg-slate-100 text-slate-600') }}">
                                        CS: {{ $activePeriodRequest->cs_status === 'approved' ? '✓ Setuju' : ($activePeriodRequest->cs_status === 'rejected' ? '✗ Tolak' : '⏳ Menunggu') }}
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Status Badge Periode Terakhir -->
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white/80 border border-slate-200 text-xs">
                                <span class="w-2 h-2 rounded-full {{ !empty($settings['last_period_closed_at']) ? 'bg-amber-500' : 'bg-emerald-500' }}"></span>
                                <span class="text-slate-600 font-medium">Status Siklus:</span>
                                @if(!empty($settings['last_period_closed_at']))
                                    <span class="font-bold text-slate-900">Periode Terakhir Ditutup pada {{ $settings['last_period_closed_at'] }}</span>
                                    @if(!empty($settings['last_period_closed_by']))
                                        <span class="text-slate-400">oleh <strong class="text-slate-700">{{ $settings['last_period_closed_by'] }}</strong></span>
                                    @endif
                                @else
                                    <span class="font-bold text-emerald-700">Periode Berjalan Aktif (Belum pernah di-reset)</span>
                                @endif
                            </div>
                        @endif

                        <!-- Detail Scope Reset & Preserve -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs pt-1">
                            <div class="p-3 bg-red-50/70 rounded-xl border border-red-100 text-red-900">
                                <p class="font-bold flex items-center gap-1.5 mb-1 text-red-800">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5 text-red-600"></i>
                                    <span>Data yang Diriset ke 0:</span>
                                </p>
                                <ul class="list-disc list-inside space-y-0.5 text-[11px] text-red-700/90 leading-relaxed">
                                    <li>Kuesioner siswa &amp; hasil survei minat</li>
                                    <li>Follow-up siswa &amp; prospek CRM (Maba)</li>
                                    <li>Rencana &amp; laporan kunjungan sekolah</li>
                                    <li>Rute marketing &amp; tracking operasional</li>
                                    <li>Proposal &amp; berkas event sponsorship</li>
                                    <li>Log WhatsApp, activity &amp; intake historis</li>
                                </ul>
                            </div>
                            <div class="p-3 bg-emerald-50/70 rounded-xl border border-emerald-100 text-emerald-900">
                                <p class="font-bold flex items-center gap-1.5 mb-1 text-emerald-800">
                                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                                    <span>Data Aman &amp; Tidak Berubah:</span>
                                </p>
                                <ul class="list-disc list-inside space-y-0.5 text-[11px] text-emerald-700/90 leading-relaxed">
                                    <li>Seluruh akun pengguna (<strong class="font-bold">users</strong> &amp; hak role)</li>
                                    <li>Master sekolah &amp; koordinat GIS wilayah</li>
                                    <li>Program studi, jenis &amp; akreditasi</li>
                                    <li>Katalog E-Brosur PMB</li>
                                    <li>Pengaturan branding &amp; identitas kampus</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="shrink-0 flex sm:flex-col justify-end gap-2 pt-2 md:pt-0">
                        @if(!empty($activePeriodRequest))
                            <a href="{{ route('period-closing.index') }}" class="w-full sm:w-auto px-5 py-3 bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-xs rounded-xl shadow-lg transition flex items-center justify-center space-x-2">
                                <i data-lucide="clock" class="w-4 h-4"></i>
                                <span>Pantau Persetujuan ({{ $activePeriodRequest->approvedCount() }}/3)</span>
                            </a>
                        @else
                            <button type="button" onclick="openClosePeriodModal()" class="w-full sm:w-auto px-5 py-3 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-extrabold text-xs rounded-xl shadow-lg shadow-red-600/30 transition flex items-center justify-center space-x-2 group">
                                <i data-lucide="power-off" class="w-4 h-4 group-hover:rotate-12 transition-transform"></i>
                                <span>Ajukan Tutup Periode</span>
                            </button>
                        @endif

                        <a href="{{ route('period-closing.index') }}" class="w-full sm:w-auto px-4 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition flex items-center justify-center space-x-1.5">
                            <i data-lucide="history" class="w-3.5 h-3.5 text-slate-500"></i>
                            <span>Riwayat Otorisasi</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== MODAL PENGAJUAN TUTUP PERIODE ==================== -->
    <div id="modalClosePeriod" class="fixed inset-0 z-50 hidden bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-200">
            <div class="flex items-start justify-between pb-4 mb-4 border-b border-slate-100">
                <div class="flex items-center space-x-3">
                    <div class="w-11 h-11 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center shadow-inner shrink-0">
                        <i data-lucide="shield-alert" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">Pengajuan Tutup Periode &amp; Reset</h3>
                        <p class="text-xs text-red-600 font-semibold">Memerlukan persetujuan Pimpinan, Koordinator, &amp; CS</p>
                    </div>
                </div>
                <button type="button" onclick="closeClosePeriodModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('period-closing.request') }}" class="space-y-4 text-xs">
                @csrf

                <div class="p-3.5 bg-amber-50 rounded-xl border border-amber-200 text-amber-900 space-y-1.5">
                    <p class="font-extrabold text-xs flex items-center gap-1.5 text-amber-800">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-amber-600"></i>
                        <span>Alur Persetujuan Bertingkat</span>
                    </p>
                    <p class="text-[11px] text-amber-800 leading-relaxed">
                        Setelah Anda mengajukan permohonan ini, status akan menunggu persetujuan dari <strong>3 Pihak Penentu</strong>: Pimpinan Kampus, Koordinator Marketing, dan Customer Service.
                    </p>
                    <p class="text-[11px] text-red-700 font-semibold pt-1 border-t border-amber-200/60">
                        * Jika salah satu pihak menolak, proses reset langsung dibatalkan. Harus ketiganya menyetujui baru data operasional kembali ke 0.
                    </p>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Alasan / Catatan Penutupan Periode <span class="text-red-500">*</span>
                    </label>
                    <textarea name="reason" rows="2" required placeholder="Contoh: Penutupan Siklus Pemasaran PMB TA 2025/2026 Gelombang 1..."
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Ketik kalimat persetujuan: <span class="font-mono text-red-600 select-all font-black">TUTUP PERIODE</span> <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="close_period_confirmation" name="confirmation_text" required autocomplete="off"
                        placeholder="Ketik TUTUP PERIODE di sini..."
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-mono font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Masukkan Password Akun Admin Anda <span class="text-red-500">*</span>
                    </label>
                    <input type="password" id="close_period_password" name="admin_password" required autocomplete="current-password"
                        placeholder="Password akun Admin Anda saat ini..."
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-mono text-slate-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    <p class="text-[10px] text-slate-400 mt-1">Dibutuhkan otorisasi password Admin untuk mengajukan permohonan tutup periode.</p>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeClosePeriodModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 font-semibold transition">
                        Batal
                    </button>
                    <button type="submit" id="btnSubmitClosePeriod" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-extrabold rounded-xl shadow-lg shadow-red-600/30 transition flex items-center space-x-2">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        <span>Kirim Pengajuan Otorisasi 3 Pihak</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function colorPaletteCustomizer() {
            return {
                primaryColor: '{{ old('theme_color_primary', $settings['theme_color_primary'] ?? '#0B023E') }}',
                accentColor: '{{ old('theme_color_accent', $settings['theme_color_accent'] ?? '#DF0B2B') }}',
                secondaryColor: '{{ old('theme_color_secondary', $settings['theme_color_secondary'] ?? '#1A1A1A') }}',
                bgColor: '{{ old('theme_color_bg', $settings['theme_color_bg'] ?? '#F2F2F2') }}',

                applyOfficialPalette() {
                    this.primaryColor = '#0B023E';
                    this.accentColor = '#DF0B2B';
                    this.secondaryColor = '#1A1A1A';
                    this.bgColor = '#F2F2F2';
                },

                resetDefaultColors() {
                    this.primaryColor = '#0B023E';
                    this.accentColor = '#DF0B2B';
                    this.secondaryColor = '#1A1A1A';
                    this.bgColor = '#F2F2F2';
                }
            };
        }

        function openClosePeriodModal() {
            const modal = document.getElementById('modalClosePeriod');
            if (modal) {
                modal.classList.remove('hidden');
                document.getElementById('close_period_confirmation').value = '';
                document.getElementById('close_period_password').value = '';
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        }

        function closeClosePeriodModal() {
            const modal = document.getElementById('modalClosePeriod');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        document.getElementById('modalClosePeriod')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeClosePeriodModal();
            }
        });
    </script>
    @endpush
</x-app-layout>
