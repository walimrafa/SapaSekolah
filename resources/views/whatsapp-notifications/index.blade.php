<x-app-layout title="Notifikasi WhatsApp Otomatis">
    <div class="space-y-6">

        <!-- Notification Banner -->
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-500 text-white shadow-lg flex items-center justify-between text-sm font-bold">
                <div class="flex items-center space-x-2.5">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs shadow-sm">
                <p class="font-bold text-sm text-rose-900 mb-1">Periksa kembali data yang diinput:</p>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Header & Action Bar -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-emerald-950 to-slate-900 border border-slate-800 p-6 md:p-8 shadow-xl text-white">
            <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -left-10 -top-10 w-72 h-72 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-400/20 text-emerald-300 border border-emerald-400/30">
                            <i data-lucide="message-square" class="w-3.5 h-3.5 mr-1.5 text-emerald-400"></i> Gateway WhatsApp
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                            <i data-lucide="calendar" class="w-3.5 h-3.5 mr-1.5 text-blue-400"></i> Google Calendar & Email (.ics)
                        </span>
                        @if(!empty($appSettings['wa_api_token']))
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span> Gateway Aktif ({{ strtoupper($appSettings['wa_gateway_provider'] ?? 'FONNTE') }})
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-400/20 text-amber-300 border border-amber-400/30">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mr-1.5"></span> Mode Simulasi / Log (Siap Integrasi)
                            </span>
                        @endif
                    </div>
                    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-white flex items-center gap-2">
                        Pusat Notifikasi WhatsApp & Google Calendar
                    </h1>
                    <p class="text-sm md:text-base text-slate-300 max-w-3xl leading-relaxed">
                        Pengingat WhatsApp otomatis dan sinkronisasi undangan <strong>Google Calendar via email (.ics)</strong> untuk akun masing-masing pada agenda <strong>Kunjungan (H-1)</strong>, <strong>Sosialisasi (H-5)</strong>, <strong>Proposal Masuk (H-0)</strong>, dan <strong>Pelaksanaan Event (H-7)</strong>.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <form action="{{ route('whatsapp-notifications.trigger-reminders') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center space-x-2 px-5 py-3 rounded-xl text-xs font-bold bg-gradient-to-r from-emerald-500 via-teal-500 to-cyan-500 hover:from-emerald-400 hover:to-cyan-400 text-white shadow-xl shadow-emerald-500/30 transition transform hover:scale-105">
                            <i data-lucide="bell-ring" class="w-4 h-4 animate-bounce"></i>
                            <span>Jalankan Notifikasi Hari Ini</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
            <div class="p-4 bg-white rounded-2xl border border-slate-200 shadow-sm">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Notifikasi</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($metrics['total_all']) }}</p>
                <span class="text-[11px] text-slate-500 font-medium">Hari Ini: {{ $metrics['total_today'] }} terkirim</span>
            </div>

            <div class="p-4 bg-white rounded-2xl border border-amber-200 shadow-sm">
                <p class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Proposal Masuk (H-0)</p>
                <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ number_format($metrics['proposal_masuk']) }}</p>
                <span class="text-[11px] text-amber-700 font-medium">Notifikasi Instan Sekolah</span>
            </div>

            <div class="p-4 bg-white rounded-2xl border border-blue-200 shadow-sm">
                <p class="text-[11px] font-bold text-blue-600 uppercase tracking-wider">Kunjungan (H-1)</p>
                <p class="text-2xl font-extrabold text-blue-600 mt-1">{{ number_format($metrics['kunjungan_h1']) }}</p>
                <span class="text-[11px] text-blue-700 font-medium">Pengingat Jadwal Lapangan</span>
            </div>

            <div class="p-4 bg-white rounded-2xl border border-emerald-200 shadow-sm">
                <p class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Sosialisasi (H-5)</p>
                <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ number_format($metrics['sosialisasi_h5']) }}</p>
                <span class="text-[11px] text-emerald-700 font-medium">Koordinasi Jadwal FIX</span>
            </div>

            <div class="p-4 bg-white rounded-2xl border border-purple-200 shadow-sm">
                <p class="text-[11px] font-bold text-purple-600 uppercase tracking-wider">Pelaksanaan Event (H-7)</p>
                <p class="text-2xl font-extrabold text-purple-600 mt-1">{{ number_format($metrics['event_h7']) }}</p>
                <span class="text-[11px] text-purple-700 font-medium">Persiapan Booth & Logistik</span>
            </div>
        </div>

        <!-- 2 Column: Konfigurasi Gateway & Kirim Uji Coba -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Left: Konfigurasi Gateway WhatsApp (Free Fonnte / Custom) -->
            <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200/90 p-6 shadow-sm space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-slate-100">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                            <i data-lucide="settings" class="w-4 h-4"></i>
                        </div>
                        <h2 class="text-base font-extrabold text-slate-900">Pengaturan Gateway WhatsApp</h2>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button type="button" onclick="autoFillGatewaySettings()"
                            class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 shadow-sm transition hover:scale-105"
                            title="Isi otomatis formulir dengan konfigurasi rekomendasi Fonnte New API & Kampus UBSI">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5 text-emerald-600"></i>
                            <span>⚡ Isi Otomatis</span>
                        </button>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                            Aplikasi Pendukung Free
                        </span>
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-600 space-y-2">
                    <p class="font-bold text-slate-900 flex items-center gap-1.5">
                        <i data-lucide="info" class="w-4 h-4 text-emerald-600"></i> Cara Mendapatkan Token Gratis (Fonnte):
                    </p>
                    <ol class="list-decimal pl-5 space-y-1 text-slate-600">
                        <li>Daftar akun gratis di <a href="https://fonnte.com" target="_blank" class="text-emerald-700 font-bold underline">fonnte.com</a>.</li>
                        <li>Tambahkan nomor WhatsApp Anda dan scan QR Code WhatsApp Web di dashboard Fonnte.</li>
                        <li>Salin <strong>API Token Device</strong> dari Fonnte ke formulir di bawah ini, lalu klik Simpan.</li>
                        <li>Jika token belum diisi, sistem otomatis berjalan dalam <strong>Mode Simulasi/Log</strong> (pesan tetap tercatat & dapat dikirim via link langsung).</li>
                    </ol>
                </div>

                <form id="gatewayForm" action="{{ route('whatsapp-notifications.update-settings') }}" method="POST" class="space-y-4 pt-2">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Provider Gateway
                            </label>
                            <select id="wa_gateway_provider" name="wa_gateway_provider" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <option value="fonnte" {{ ($appSettings['wa_gateway_provider'] ?? 'fonnte') === 'fonnte' ? 'selected' : '' }}>Fonnte (Rekomendasi Free Tier - New API)</option>
                                <option value="generic" {{ ($appSettings['wa_gateway_provider'] ?? '') === 'generic' ? 'selected' : '' }}>Generic Webhook / Self-Hosted (Baileys/WPP)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Nomor WhatsApp Tujuan Admin / PIC
                            </label>
                            <input type="text" id="wa_target_phone" name="wa_target_phone" value="{{ $appSettings['wa_target_phone'] ?? '081220022851' }}" placeholder="Contoh: 081220022851"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <span class="text-[10px] text-slate-400 mt-0.5 block">Menerima notifikasi proposal masuk (H-0) & ringkasan pengingat</span>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                API Token Gateway (Fonnte)
                            </label>
                            <span class="text-[10px] text-emerald-600 font-semibold">Mode simulasi otomatis aktif jika belum diisi</span>
                        </div>
                        <input type="password" id="wa_api_token" name="wa_api_token" value="{{ $appSettings['wa_api_token'] ?? '' }}" placeholder="Tempel token API Device dari dashboard Fonnte di sini..."
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-mono text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Token disimpan aman di server SAPA Sekolah Command Center</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Custom Endpoint (Fonnte New API / Generic Gateway)
                        </label>
                        <input type="url" id="wa_custom_endpoint" name="wa_custom_endpoint" value="{{ $appSettings['wa_custom_endpoint'] ?? 'https://api.fonnte.com/send' }}" placeholder="https://api.fonnte.com/send"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-mono text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div class="pt-2 flex items-center justify-between">
                        <button type="button" onclick="autoFillGatewaySettings()" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 flex items-center space-x-1">
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                            <span>Reset Default Otomatis</span>
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white shadow-md transition hover:scale-105">
                            Simpan Pengaturan Gateway
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right: Kirim Pesan Uji Coba -->
            <div class="lg:col-span-5 bg-white rounded-3xl border border-slate-200/90 p-6 shadow-sm space-y-4 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center space-x-2.5">
                            <div class="w-8 h-8 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center font-bold">
                                <i data-lucide="send" class="w-4 h-4"></i>
                            </div>
                            <h2 class="text-base font-extrabold text-slate-900">Kirim Pesan Uji Coba</h2>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-200">
                            Instant Test
                        </span>
                    </div>

                    <!-- Quick Autofill Chips by Account Role -->
                    <div class="space-y-1.5">
                        <p class="text-[11px] font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1">
                            <i data-lucide="user-check" class="w-3.5 h-3.5 text-cyan-600"></i> ⚡ Isi Otomatis Nomor Akun:
                        </p>
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" onclick="fillTestRecipient('081234567890', 'Super Administrator (walim.wam@bsi.ac.id)')"
                                class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-100 hover:bg-cyan-100 hover:text-cyan-800 text-slate-700 border border-slate-200 transition">
                                👑 Admin
                            </button>
                            <button type="button" onclick="fillTestRecipient('081234567891', 'M.Syamsul Azis, M.Kom (Pimpinan Kampus)')"
                                class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-100 hover:bg-cyan-100 hover:text-cyan-800 text-slate-700 border border-slate-200 transition">
                                🏛️ Pimpinan
                            </button>
                            <button type="button" onclick="fillTestRecipient('081234567892', 'Ali William (Koordinator Marketing)')"
                                class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-100 hover:bg-cyan-100 hover:text-cyan-800 text-slate-700 border border-slate-200 transition">
                                🎯 Koord Marketing
                            </button>
                            <button type="button" onclick="fillTestRecipient('089876543210', 'Markom1 (Marketing Specialist)')"
                                class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-100 hover:bg-cyan-100 hover:text-cyan-800 text-slate-700 border border-slate-200 transition">
                                🚀 Marketing
                            </button>
                            <button type="button" onclick="fillTestRecipient('081234567893', 'Ibu Dina (Customer Service)')"
                                class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-100 hover:bg-cyan-100 hover:text-cyan-800 text-slate-700 border border-slate-200 transition">
                                🎧 CS Dina
                            </button>
                            <button type="button" onclick="fillTestRecipient('081234567894', 'Ka Devi (Customer Service)')"
                                class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-100 hover:bg-cyan-100 hover:text-cyan-800 text-slate-700 border border-slate-200 transition">
                                🎧 CS Devi
                            </button>
                            <button type="button" onclick="fillTestRecipient('081220022851', 'UBSI Kampus Karawang')"
                                class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 transition">
                                🏢 UBSI Karawang
                            </button>
                        </div>
                    </div>

                    <form id="testForm" action="{{ route('whatsapp-notifications.send-test') }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Nomor WhatsApp Penerima <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="test_phone" name="phone" value="{{ auth()->user()->phone ?? ($appSettings['wa_target_phone'] ?? '081220022851') }}" required placeholder="Contoh: 081220022851"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Isi Pesan Uji Coba <span class="text-rose-500">*</span>
                            </label>
                            <textarea id="test_message" name="message" rows="3" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-500">Halo! Ini adalah pesan uji coba integrasi notifikasi WhatsApp otomatis & sinkronisasi Google Calendar dari SAPA Sekolah UBSI Command Center.</textarea>
                        </div>

                        <button type="submit" class="w-full py-2.5 rounded-xl text-xs font-bold bg-cyan-600 hover:bg-cyan-500 text-white shadow-md shadow-cyan-600/20 transition flex items-center justify-center space-x-1.5 hover:scale-[1.02]">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            <span>Kirim Pesan Uji Coba Sekarang</span>
                        </button>
                    </form>
                </div>

                <div class="pt-3 border-t border-slate-100 text-[11px] text-slate-400">
                    <p>💡 Jadwal otomatis berjalan setiap hari pukul <strong>07:30 WIB</strong> via Cron Scheduler.</p>
                </div>
            </div>

        </div>

        <!-- Log Riwayat Notifikasi Terkirim -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">Riwayat Pengiriman Notifikasi WhatsApp</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Seluruh notifikasi otomatis yang berhasil di-dispatch oleh sistem SAPA Sekolah</p>
                </div>

                <!-- Filter Form -->
                <form method="GET" action="{{ route('whatsapp-notifications.index') }}" class="flex flex-wrap items-center gap-2">
                    <select name="type" class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="all">Semua Jenis Notifikasi</option>
                        <option value="proposal_masuk" {{ request('type') === 'proposal_masuk' ? 'selected' : '' }}>Proposal Masuk (H-0)</option>
                        <option value="kunjungan_h1" {{ request('type') === 'kunjungan_h1' ? 'selected' : '' }}>Kunjungan (H-1)</option>
                        <option value="sosialisasi_h5" {{ request('type') === 'sosialisasi_h5' ? 'selected' : '' }}>Sosialisasi (H-5)</option>
                        <option value="event_h7" {{ request('type') === 'event_h7' ? 'selected' : '' }}>Pelaksanaan Event (H-7)</option>
                        <option value="test" {{ request('type') === 'test' ? 'selected' : '' }}>Pesan Uji Coba</option>
                    </select>

                    <select name="status" class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="all">Semua Status</option>
                        <option value="terkirim" {{ request('status') === 'terkirim' ? 'selected' : '' }}>Terkirim via Gateway</option>
                        <option value="simulasi" {{ request('status') === 'simulasi' ? 'selected' : '' }}>Simulasi / Log</option>
                        <option value="gagal" {{ request('status') === 'gagal' ? 'selected' : '' }}>Gagal</option>
                    </select>

                    <button type="submit" class="px-3 py-2 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-white shadow-sm transition">
                        Filter
                    </button>

                    @if(request()->anyFilled(['type', 'status', 'search']))
                        <a href="{{ route('whatsapp-notifications.index') }}" class="px-2.5 py-2 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-600 transition" title="Reset">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </a>
                    @endif
                </form>
            </div>

            @if($logs->isEmpty())
                <div class="py-16 text-center text-slate-400 space-y-2">
                    <i data-lucide="message-square-off" class="w-12 h-12 text-slate-300 mx-auto"></i>
                    <p class="text-sm font-bold text-slate-700">Belum ada riwayat notifikasi WhatsApp.</p>
                    <p class="text-xs text-slate-400">Notifikasi akan otomatis muncul ketika ada proposal masuk, H-1 kunjungan, H-5 sosialisasi, atau setelah Anda menekan tombol "Jalankan Notifikasi Hari Ini".</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-900 text-white font-bold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="py-3 px-4">Waktu</th>
                                <th class="py-3 px-4">Jenis Notifikasi</th>
                                <th class="py-3 px-4">Penerima WhatsApp</th>
                                <th class="py-3 px-4">Google Calendar & Email</th>
                                <th class="py-3 px-4">Isi Pesan WhatsApp</th>
                                <th class="py-3 px-4">Status Gateway</th>
                                <th class="py-3 px-4 text-center">Aksi Direct WA</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($logs as $log)
                                <tr class="hover:bg-slate-50/80 transition duration-150 align-top">
                                    <td class="py-3.5 px-4 whitespace-nowrap text-slate-600">
                                        <div class="font-bold text-slate-900">{{ $log->created_at->translatedFormat('d M Y') }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $log->created_at->format('H:i:s') }} WIB</div>
                                    </td>

                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $log->type_badge_class }}">
                                            {{ $log->type_label }}
                                        </span>
                                    </td>

                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900">{{ $log->recipient_name ?: 'Admin / PIC' }}</div>
                                        <div class="font-mono text-slate-600 text-[11px]">{{ $log->recipient_phone }}</div>
                                    </td>

                                    <td class="py-3.5 px-4">
                                        @if($log->calendar_url)
                                            <a href="{{ $log->calendar_url }}" target="_blank" rel="noopener noreferrer"
                                                class="inline-flex items-center space-x-1.5 px-2.5 py-1.5 rounded-xl text-[11px] font-bold bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 shadow-sm transition hover:scale-105"
                                                title="Simpan jadwal ke Google Calendar Anda">
                                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-blue-600"></i>
                                                <span>Google Calendar</span>
                                            </a>
                                        @else
                                            <span class="text-[10px] text-slate-400 italic">Pesan Uji Coba</span>
                                        @endif

                                        @if($log->email_recipients)
                                            <div class="mt-2 text-[10px] text-slate-600 space-y-0.5">
                                                <div class="flex items-center space-x-1 font-bold text-emerald-700">
                                                    <i data-lucide="mail-check" class="w-3 h-3 text-emerald-600"></i>
                                                    <span>Terkirim ke Email:</span>
                                                </div>
                                                <div class="font-mono text-slate-500 text-[9px] max-w-xs break-all leading-tight bg-slate-50 p-1.5 rounded-lg border border-slate-200">
                                                    {{ $log->email_recipients }}
                                                </div>
                                            </div>
                                        @endif
                                    </td>

                                    <td class="py-3.5 px-4 max-w-sm">
                                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-[11px] font-mono leading-relaxed whitespace-pre-line line-clamp-3 hover:line-clamp-none transition-all cursor-pointer" title="Klik untuk membaca selengkapnya">
                                            {{ $log->message }}
                                        </div>
                                    </td>

                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $log->status_badge_class }}">
                                            {{ ucfirst($log->status) }}
                                        </span>
                                        @if($log->sent_at)
                                            <span class="text-[10px] text-slate-400 block mt-0.5">{{ $log->sent_at->format('H:i') }}</span>
                                        @endif
                                    </td>

                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <a href="{{ $log->direct_wa_link }}" target="_blank" rel="noopener noreferrer"
                                            class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 shadow-sm transition hover:scale-105"
                                            title="Buka Chat WhatsApp dengan pesan ini">
                                            <i data-lucide="message-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                                            <span>Buka WA</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                </div>
            @endif
        </div>

    </div>

    @push('scripts')
    <script>
        function autoFillGatewaySettings() {
            const provider = document.getElementById('wa_gateway_provider');
            const phone = document.getElementById('wa_target_phone');
            const endpoint = document.getElementById('wa_custom_endpoint');

            provider.value = 'fonnte';
            phone.value = '081220022851';
            endpoint.value = 'https://api.fonnte.com/send';

            [provider, phone, endpoint].forEach(el => {
                if (el) {
                    el.classList.add('ring-2', 'ring-emerald-500', 'bg-emerald-50/50');
                    setTimeout(() => {
                        el.classList.remove('ring-2', 'ring-emerald-500', 'bg-emerald-50/50');
                    }, 1200);
                }
            });
        }

        function fillTestRecipient(phoneNumber, accountLabel) {
            const phoneInput = document.getElementById('test_phone');
            const msgInput = document.getElementById('test_message');

            if (phoneInput) {
                phoneInput.value = phoneNumber;
                phoneInput.classList.add('ring-2', 'ring-cyan-500', 'bg-cyan-50/50');
                setTimeout(() => phoneInput.classList.remove('ring-2', 'ring-cyan-500', 'bg-cyan-50/50'), 1200);
            }

            if (msgInput) {
                msgInput.value = `Halo *${accountLabel}*!\n\nIni adalah pesan uji coba integrasi otomatis WhatsApp Gateway & Google Calendar Sync dari SAPA Sekolah UBSI Command Center.\n\n📅 Jadwal & pengingat otomatis telah aktif terintegrasi.`;
                msgInput.classList.add('ring-2', 'ring-cyan-500', 'bg-cyan-50/50');
                setTimeout(() => msgInput.classList.remove('ring-2', 'ring-cyan-500', 'bg-cyan-50/50'), 1200);
            }
        }
    </script>
    @endpush
</x-app-layout>
