@php
    $appSettings = \App\Services\AppSettingService::all();
    $campuses = \App\Models\Campus::where('is_active', true)->orderBy('is_default', 'desc')->orderBy('id')->get();
    $firstCampusBuilding = $campuses->first()?->building_image_url ?? ($appSettings['campus_building_url'] ?? null);
@endphp
<x-guest-layout title="Login Marketing Command Center">
    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-12 bg-white">
        <!-- Left Side: Branding & Visuals (Desktop & Tablet) -->
        <div class="hidden lg:flex lg:col-span-7 bg-gradient-to-br from-slate-950 via-slate-900 to-brand-navy text-white p-12 lg:p-16 flex-col justify-between relative overflow-hidden">
            @if(!empty($firstCampusBuilding))
                <!-- Subtle High-End Campus Building Backdrop -->
                <div class="absolute inset-0 bg-cover bg-center opacity-25 mix-blend-luminosity scale-105 pointer-events-none transition-transform duration-1000" style="background-image: url('{{ $firstCampusBuilding }}')"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/85 to-slate-950/90 pointer-events-none"></div>
            @endif

            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#38bdf8_1px,transparent_1px)] [background-size:24px_24px] pointer-events-none"></div>

            <!-- Top Logo -->
            <div class="relative z-10 flex items-center space-x-3">
                <div class="w-12 h-12 rounded-2xl bg-white/10 p-1.5 backdrop-blur-xs border border-white/20 shadow-lg shadow-brand-500/20 shrink-0 flex items-center justify-center overflow-hidden">
                    <img src="{{ \App\Services\AppSettingService::getLogoUrl() }}" alt="{{ $appSettings['app_name'] ?? 'Logo' }}" class="w-full h-full object-contain">
                </div>
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight text-white">{{ $appSettings['app_name'] ?? 'SAPA Sekolah' }}</h1>
                    <p class="text-[11px] text-amber-400 font-semibold tracking-wide uppercase">{{ $appSettings['app_subtitle'] ?? 'Command Center' }}</p>
                </div>
            </div>

            <!-- Hero Section (Ringkas & Rapi) -->
            <div class="relative z-10 my-auto py-8">
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-bold mb-5">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-400"></i>
                    <span>Command Center PMB</span>
                </div>

                <h2 class="text-3xl lg:text-4xl font-extrabold text-white tracking-tight leading-tight mb-4">
                    Digital Mapping &amp;<br>
                    <span class="bg-gradient-to-r from-brand-300 via-sky-300 to-amber-300 bg-clip-text text-transparent">
                        Monitoring PMB Realtime
                    </span>
                </h2>

                <p class="text-slate-300 text-sm max-w-lg leading-relaxed mb-8">
                    Pemetaan digital sekolah SLTA, agenda kunjungan lapangan, dan integrasi prospek PMB multi-kampus Universitas BSI.
                </p>

                <!-- Showcase Gedung Kampus Operasional (Karawang & Cikampek) -->
                @if($campuses->isNotEmpty())
                    <div class="max-w-xl">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-300 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span>Kampus Operasional</span>
                            </span>
                            <span class="text-[10px] text-amber-400 font-bold">{{ $campuses->count() }} Kampus Aktif</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($campuses->take(2) as $cmp)
                                <div class="p-3 rounded-2xl bg-slate-900/85 backdrop-blur-md border border-slate-700/70 shadow-lg flex items-center gap-3 hover:border-amber-400/50 transition">
                                    <div class="relative shrink-0">
                                        <img src="{{ $cmp->building_image_url }}" alt="{{ $cmp->name }}" class="w-14 h-12 object-cover rounded-xl border border-white/10 shadow">
                                        <span class="absolute -top-1.5 -left-1.5 px-1.5 py-0.2 rounded text-[8px] font-mono font-black {{ $cmp->is_default ? 'bg-amber-400 text-slate-950' : 'bg-emerald-500 text-white' }} shadow">
                                            {{ $cmp->code }}
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h4 class="text-xs font-bold text-white truncate">{{ $cmp->name }}</h4>
                                        <p class="text-[10px] text-slate-400 truncate">{{ $cmp->city ?: 'Jawa Barat' }}</p>
                                        <div class="mt-0.5 flex items-center gap-1 text-[10px] text-emerald-400 font-semibold truncate">
                                            <i data-lucide="phone" class="w-3 h-3 text-emerald-400 shrink-0"></i>
                                            <span class="font-mono">{{ $cmp->phone ?: '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Footer Info -->
            <div class="relative z-10 text-xs text-slate-400 flex items-center justify-between border-t border-slate-800/80 pt-5">
                <p>&copy; {{ date('Y') }} SAPA Sekolah • Universitas BSI</p>
                <span class="text-slate-500 font-mono text-[11px]">v1.0.0</span>
            </div>
        </div>

        <!-- Right Side: Login Form (Mobile First, Bersih & Responsif) -->
        <div class="col-span-1 lg:col-span-5 p-6 sm:p-10 lg:p-16 flex flex-col justify-center bg-white min-h-screen lg:min-h-0">
            <div class="max-w-md mx-auto w-full">
                <!-- Mobile Brand Header (Tampil hanya di layar kecil) -->
                <div class="lg:hidden flex items-center space-x-3 mb-6 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-slate-900/10 p-1 border border-slate-200/80 shadow-md shrink-0 flex items-center justify-center overflow-hidden">
                        <img src="{{ \App\Services\AppSettingService::getLogoUrl() }}" alt="{{ $appSettings['app_name'] ?? 'Logo' }}" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <h1 class="text-lg font-extrabold text-slate-900 tracking-tight">{{ $appSettings['app_name'] ?? 'SAPA Sekolah' }}</h1>
                        <p class="text-[10px] text-brand-600 font-bold uppercase tracking-wider">{{ $appSettings['app_subtitle'] ?? 'Command Center' }}</p>
                    </div>
                </div>

                <div class="mb-6">
                    <h2 class="text-2xl font-black text-slate-900 mb-1 tracking-tight">Masuk ke Akun 👋</h2>
                    <p class="text-slate-500 text-xs sm:text-sm">Silakan masukkan NISN/Username, kata sandi, dan kode keamanan.</p>
                </div>

                @if ($errors->any())
                    <div class="mb-5 p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                        <div class="flex items-center space-x-2 mb-1">
                            <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                            <span class="font-bold">Login Gagal</span>
                        </div>
                        <ul class="list-disc list-inside space-y-0.5 text-[11px] text-rose-600 font-medium">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-4" x-data="{
                    showPassword: false,
                    isReloadingCaptcha: false,
                    async reloadCaptcha() {
                        this.isReloadingCaptcha = true;
                        try {
                            const res = await fetch('{{ route('captcha.refresh') }}');
                            const data = await res.json();
                            if (data.svg) {
                                document.getElementById('captcha-container').innerHTML = data.svg;
                            }
                        } catch (e) {
                            console.error('Gagal memuat ulang captcha:', e);
                        } finally {
                            this.isReloadingCaptcha = false;
                        }
                    }
                }">
                    @csrf

                    <!-- NISN / Username / Email -->
                    <div>
                        <label for="login" class="block text-xs font-bold text-slate-700 mb-1.5">NISN / Username</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="user" class="w-4 h-4 text-slate-400"></i>
                            </div>
                            <input type="text" name="login" id="login" value="{{ old('login') ?: old('email') }}" required autofocus
                                class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-slate-800 text-sm font-medium transition duration-150 placeholder-slate-400"
                                placeholder="Masukkan NISN atau username">
                        </div>
                    </div>

                    <!-- Kata Sandi dengan Tombol Toggle Eye -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-bold text-slate-700">Kata Sandi</label>
                            <a href="#" class="text-xs font-semibold text-brand-600 hover:text-brand-700">Lupa password?</a>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="lock" class="w-4 h-4 text-slate-400"></i>
                            </div>
                            <input :type="showPassword ? 'text' : 'password'" name="password" id="password" required
                                class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-slate-800 text-sm font-medium transition duration-150 placeholder-slate-400"
                                placeholder="Masukkan kata sandi">
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition"
                                title="Lihat/Sembunyikan Kata Sandi">
                                <i data-lucide="eye" class="w-4 h-4" x-show="!showPassword"></i>
                                <i data-lucide="eye-off" class="w-4 h-4" x-show="showPassword" style="display: none;"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Kode Keamanan (Captcha) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Kode Keamanan</label>
                        <div class="flex items-center gap-2 mb-2">
                            <!-- Visual Box Captcha -->
                            <div id="captcha-container" class="rounded-xl overflow-hidden border border-slate-300 shadow-inner bg-slate-900 inline-flex items-center justify-center h-[42px] px-1 select-none">
                                {!! $captchaSvg ?? \App\Http\Controllers\Auth\LoginController::generateCaptcha()['svg'] !!}
                            </div>
                            <!-- Refresh Button -->
                            <button type="button" @click="reloadCaptcha()"
                                class="h-[42px] px-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-600 hover:text-brand-600 font-bold transition flex items-center justify-center shadow-xs"
                                title="Ganti Kode Keamanan Lain">
                                <i data-lucide="refresh-cw" class="w-4 h-4" :class="isReloadingCaptcha ? 'animate-spin text-brand-600' : ''"></i>
                            </button>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="shield-check" class="w-4 h-4 text-slate-400"></i>
                            </div>
                            <input type="text" name="captcha" id="captcha" required autocomplete="off"
                                class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-slate-800 text-sm font-semibold uppercase tracking-wider transition duration-150 placeholder-slate-400"
                                placeholder="Ketik kode">
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded text-brand-600 border-slate-300 focus:ring-brand-500">
                            <span class="ml-2 text-xs font-medium text-slate-600">Ingat saya</span>
                        </label>
                    </div>

                    <button type="submit"
                        class="w-full py-3 px-4 bg-gradient-to-r from-brand-navy to-brand-700 hover:from-brand-900 hover:to-brand-800 text-white font-extrabold rounded-xl shadow-md shadow-brand-900/20 transition duration-200 flex items-center justify-center space-x-2 text-sm cursor-pointer hover:shadow-lg">
                        <span>Masuk</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </form>

                <!-- Mobile Campus Showcase Preview (Ringkas di bagian bawah form untuk layar kecil) -->
                @if($campuses->isNotEmpty())
                    <div class="lg:hidden mt-8 pt-4 border-t border-slate-100">
                        <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-2 text-center">Kampus Operasional UBSI</p>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($campuses->take(2) as $cmp)
                                <div class="p-2 rounded-xl bg-slate-50 border border-slate-200 flex items-center gap-2">
                                    <img src="{{ $cmp->building_image_url }}" alt="{{ $cmp->name }}" class="w-8 h-8 object-cover rounded-lg shrink-0">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-[11px] font-bold text-slate-800 truncate">{{ $cmp->name }}</p>
                                        <p class="text-[9px] text-emerald-600 font-mono truncate">{{ $cmp->phone ?: '-' }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-guest-layout>