@php
    $appSettings = \App\Services\AppSettingService::all();
@endphp
<x-guest-layout title="Buat Kata Sandi Baru — {{ $appSettings['app_name'] ?? 'SAPA Sekolah' }}">
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-950 via-brand-navy to-slate-900 p-4 sm:p-6 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#38bdf8_1px,transparent_1px)] [background-size:24px_24px] pointer-events-none"></div>

        <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-200/80 p-6 sm:p-8 relative z-10">
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
                <h2 class="text-xl font-black text-slate-900 tracking-tight">Buat Kata Sandi Baru 🔑</h2>
                <p class="text-xs text-slate-500 mt-1">
                    Atur kata sandi baru untuk akun <strong class="text-slate-800">{{ $email }}</strong>.
                </p>
            </div>

            @if ($errors->any())
                <div class="mb-5 p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                    <div class="flex items-center space-x-2 mb-1">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                        <span class="font-bold">Gagal Menyimpan Kata Sandi</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 text-[11px] text-rose-600 font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4" x-data="{ showPass: false, showConfirm: false }">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5">Kata Sandi Baru</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </div>
                        <input :type="showPass ? 'text' : 'password'" name="password" id="password" required minlength="6" autofocus
                            class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-slate-800 text-sm font-medium"
                            placeholder="Minimal 6 karakter">
                        <button type="button" @click="showPass = !showPass"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition">
                            <i data-lucide="eye" class="w-4 h-4" x-show="!showPass"></i>
                            <i data-lucide="eye-off" class="w-4 h-4" x-show="showPass" style="display: none;"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1.5">Konfirmasi Kata Sandi Baru</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                        </div>
                        <input :type="showConfirm ? 'text' : 'password'" name="password_confirmation" id="password_confirmation" required minlength="6"
                            class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-slate-800 text-sm font-medium"
                            placeholder="Ketik ulang kata sandi baru">
                        <button type="button" @click="showConfirm = !showConfirm"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition">
                            <i data-lucide="eye" class="w-4 h-4" x-show="!showConfirm"></i>
                            <i data-lucide="eye-off" class="w-4 h-4" x-show="showConfirm" style="display: none;"></i>
                        </button>
                    </div>
                </div>

                <button type="submit"
                    class="w-full py-3 px-4 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white font-extrabold rounded-xl shadow-md transition flex items-center justify-center gap-2 text-sm">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    <span>Simpan Kata Sandi Baru</span>
                </button>
            </form>

            <div class="mt-6 pt-4 border-t border-slate-100 text-center text-xs">
                <a href="{{ route('login') }}" class="font-bold text-slate-600 hover:text-brand-600 inline-flex items-center gap-1.5 transition">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    <span>Kembali ke Halaman Login</span>
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
