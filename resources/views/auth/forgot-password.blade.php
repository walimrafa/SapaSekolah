@php
    $appSettings = \App\Services\AppSettingService::all();
    $loginLbl = fn(string $k, string $def = '') => \App\Services\AppSettingService::getLoginLabel($k, $def);
    $adminPhone = preg_replace('/\D+/', '', (string) ($appSettings['admin_phone'] ?? $appSettings['campus_phone'] ?? '081220022851'));
    if (str_starts_with($adminPhone, '0')) {
        $adminPhoneWa = '62' . substr($adminPhone, 1);
    } else {
        $adminPhoneWa = $adminPhone;
    }
@endphp
<x-guest-layout title="Lupa Kata Sandi — {{ $appSettings['app_name'] ?? 'SAPA Sekolah' }}">
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-950 via-brand-navy to-slate-900 p-4 sm:p-6 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#38bdf8_1px,transparent_1px)] [background-size:24px_24px] pointer-events-none"></div>

        <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-200/80 p-6 sm:p-8 relative z-10">
            <!-- Brand Header -->
            <div class="flex items-center space-x-3 mb-6 pb-4 border-b border-slate-100">
                <div class="w-11 h-11 rounded-2xl bg-slate-900/5 p-1.5 border border-slate-200 shrink-0 flex items-center justify-center overflow-hidden">
                    <img src="{{ \App\Services\AppSettingService::getLogoUrl() }}" alt="{{ $appSettings['app_name'] ?? 'Logo' }}" class="w-full h-full object-contain">
                </div>
                <div>
                    <h1 class="text-base font-extrabold text-slate-900 leading-tight">{{ $appSettings['app_name'] ?? 'SAPA Sekolah' }}</h1>
                    <p class="text-[10px] text-brand-600 font-bold uppercase tracking-wider">{{ $appSettings['app_subtitle'] ?? 'Command Center' }}</p>
                </div>
            </div>

            <div class="mb-5">
                <h2 class="text-xl font-black text-slate-900 tracking-tight">Lupa Kata Sandi? 🔐</h2>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Masukkan {{ $loginLbl('login_identifier_label', 'Email / Username / NISN') }} akun Anda untuk mengatur ulang kata sandi secara aman.
                </p>
            </div>

            @if (session('status'))
                <div class="mb-5 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs leading-relaxed">
                    <div class="flex items-start gap-2.5">
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
                        <div class="font-semibold">{{ session('status') }}</div>
                    </div>
                </div>
            @endif

            @if (session('info_verify'))
                <div class="mb-5 p-4 rounded-2xl bg-blue-50 border border-blue-200 text-blue-800 text-xs leading-relaxed">
                    <div class="flex items-start gap-2.5">
                        <i data-lucide="shield-check" class="w-4 h-4 text-blue-600 shrink-0 mt-0.5"></i>
                        <div class="font-semibold">{{ session('info_verify') }}</div>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                    <div class="flex items-center space-x-2 mb-1">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                        <span class="font-bold">Permintaan Belum Dapat Diproses</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 text-[11px] text-rose-600 font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Step 2 (Fallback Verifikasi Nomor HP Terdaftar bila SMTP/WA belum aktif) -->
            @if(!empty($pendingReset))
                <div class="mb-6 p-4 rounded-2xl bg-slate-50 border-2 border-indigo-200 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-extrabold text-indigo-800 uppercase tracking-wider">Verifikasi Identitas Akun</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700">{{ $pendingReset['masked_email'] }}</span>
                    </div>

                    @if(!empty($pendingReset['has_phone']))
                        <p class="text-xs text-slate-600">
                            Halo <strong>{{ $pendingReset['name'] }}</strong>, masukkan <strong>Nomor HP / WhatsApp</strong> lengkap yang terdaftar pada akun Anda (<span class="font-mono font-bold text-slate-800">{{ $pendingReset['masked_phone'] }}</span>) untuk langsung membuat kata sandi baru:
                        </p>

                        <form method="POST" action="{{ route('password.verify-phone') }}" class="space-y-3">
                            @csrf
                            <div>
                                <label for="phone" class="block text-xs font-bold text-slate-700 mb-1">Nomor HP Terdaftar</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i data-lucide="phone" class="w-4 h-4"></i>
                                    </div>
                                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required autofocus
                                        placeholder="Contoh: 0812xxxxxxxx"
                                        class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-800 text-sm font-semibold">
                                </div>
                            </div>
                            <button type="submit"
                                class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold rounded-xl text-xs shadow-md transition flex items-center justify-center gap-2">
                                <i data-lucide="key-round" class="w-4 h-4"></i>
                                <span>Verifikasi &amp; Buat Kata Sandi Baru</span>
                            </button>
                        </form>
                    @else
                        <p class="text-xs text-slate-600">
                            Akun <strong>{{ $pendingReset['name'] }}</strong> belum memiliki Nomor HP terdaftar. Silakan hubungi Administrator melalui tombol WhatsApp di bawah agar kata sandi Anda diresetkan.
                        </p>
                    @endif

                    @if($adminPhoneWa)
                        <a href="https://wa.me/{{ $adminPhoneWa }}?text={{ urlencode('Halo Admin ' . ($appSettings['app_name'] ?? 'SAPA Sekolah') . ', saya butuh bantuan reset password untuk akun: ' . ($pendingReset['email'] ?? '')) }}"
                            target="_blank" rel="noopener noreferrer"
                            class="w-full py-2 px-3 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 font-bold text-xs transition flex items-center justify-center gap-1.5">
                            <i data-lucide="message-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                            <span>Hubungi Admin via WhatsApp</span>
                        </a>
                    @endif
                </div>
            @endif

            <!-- Step 1: Form Identifikasi Akun -->
            <form method="POST" action="{{ route('password.email') }}" class="space-y-4" x-data="{
                isReloadingCaptcha: false,
                async reloadCaptcha() {
                    this.isReloadingCaptcha = true;
                    try {
                        const res = await fetch('{{ route('captcha.refresh') }}');
                        const data = await res.json();
                        if (data.svg) {
                            document.getElementById('forgot-captcha-container').innerHTML = data.svg;
                        }
                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.isReloadingCaptcha = false;
                    }
                }
            }">
                @csrf

                <div>
                    <label for="login" class="block text-xs font-bold text-slate-700 mb-1.5">{{ $loginLbl('login_identifier_label', 'Email / Username / NISN') }}</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <input type="text" name="login" id="login" value="{{ old('login') }}" required {{ empty($pendingReset) ? 'autofocus' : '' }}
                            class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-slate-800 text-sm font-medium placeholder-slate-400"
                            placeholder="{{ $loginLbl('login_identifier_placeholder', 'Masukkan email, username, atau NISN') }}">
                    </div>
                </div>

                <!-- Captcha -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ $loginLbl('login_captcha_label', 'Kode Keamanan') }}</label>
                    <div class="flex items-center gap-2 mb-2">
                        <div id="forgot-captcha-container" class="rounded-xl overflow-hidden border border-slate-300 shadow-inner bg-slate-900 inline-flex items-center justify-center h-[42px] px-1 select-none">
                            {!! $captchaSvg !!}
                        </div>
                        <button type="button" @click="reloadCaptcha()"
                            class="h-[42px] px-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-600 hover:text-brand-600 font-bold transition flex items-center justify-center"
                            title="Ganti Kode Keamanan Lain">
                            <i data-lucide="refresh-cw" class="w-4 h-4" :class="isReloadingCaptcha ? 'animate-spin text-brand-600' : ''"></i>
                        </button>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                        </div>
                        <input type="text" name="captcha" id="captcha" required autocomplete="off"
                            class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-slate-800 text-sm font-semibold uppercase tracking-wider placeholder-slate-400"
                            placeholder="{{ $loginLbl('login_captcha_placeholder', 'Ketik kode') }}">
                    </div>
                </div>

                <button type="submit"
                    class="w-full py-3 px-4 bg-gradient-to-r from-brand-navy to-brand-700 hover:from-brand-900 hover:to-brand-800 text-white font-extrabold rounded-xl shadow-md transition flex items-center justify-center gap-2 text-sm">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    <span>Kirim Tautan Reset Password</span>
                </button>
            </form>

            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                <a href="{{ route('login') }}" class="font-bold text-slate-600 hover:text-brand-600 flex items-center gap-1.5 transition">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    <span>Kembali ke Halaman Login</span>
                </a>
                @if($adminPhoneWa)
                    <a href="https://wa.me/{{ $adminPhoneWa }}?text={{ urlencode('Halo Admin ' . ($appSettings['app_name'] ?? 'SAPA Sekolah') . ', saya lupa kata sandi akun saya.') }}"
                        target="_blank" rel="noopener noreferrer"
                        class="font-semibold text-emerald-600 hover:text-emerald-700">
                        Bantuan Admin ↗
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-guest-layout>
