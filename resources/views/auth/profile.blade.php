<x-app-layout title="Profil Pengguna & Keamanan">
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <i data-lucide="user-cog" class="w-6 h-6 text-brand-600"></i>
                    <span>Profil Pengguna &amp; Keamanan Kata Sandi</span>
                </h1>
                <p class="text-xs text-slate-500 mt-1">Kelola data profil pribadi dan perbarui kata sandi akun Anda secara berkala.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center space-x-1.5 px-3 py-2 bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-xl hover:bg-slate-50 transition shadow-xs">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            <!-- Left Column: User Profile Overview -->
            <div class="md:col-span-5 space-y-6">
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-soft text-center">
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-brand-navy to-brand-600 text-white font-extrabold text-2xl flex items-center justify-center mx-auto shadow-md shadow-brand-600/20 mb-4">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <h2 class="text-base font-extrabold text-slate-900">{{ $user->name }}</h2>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $user->email }}</p>

                    <div class="mt-4 flex flex-wrap justify-center gap-1.5">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            {{ $user->role ? $user->role->name : 'Staff' }}
                        </span>
                        @if($user->campus)
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Kampus: {{ $user->campus->code }}
                            </span>
                        @endif
                    </div>

                    <div class="mt-6 pt-5 border-t border-slate-100 text-left space-y-3 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Nomor Telepon:</span>
                            <span class="font-bold text-slate-700">{{ $user->phone ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Status Akun:</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                Aktif
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Terdaftar Sejak:</span>
                            <span class="font-semibold text-slate-600">{{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Security Tip Box -->
                <div class="bg-amber-50 rounded-2xl border border-amber-200/80 p-5">
                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-xs font-extrabold text-amber-900">Tips Keamanan Akun</h3>
                            <p class="text-[11px] text-amber-700 mt-1 leading-relaxed">
                                Gunakan kata sandi unik minimal 6 karakter yang mengkombinasikan huruf besar, kecil, angka, dan simbol. Jangan berikan akses akun Anda kepada pihak lain.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Change Password Form -->
            <div class="md:col-span-7">
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-soft">
                    <div class="flex items-center space-x-2.5 pb-4 mb-5 border-b border-slate-100">
                        <div class="w-8 h-8 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                            <i data-lucide="key-round" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Ubah Kata Sandi Mandiri</h3>
                            <p class="text-xs text-slate-500">Masukkan kata sandi lama Anda sebelum menetapkan kata sandi baru.</p>
                        </div>
                    </div>

                    @if($errors->any())
                        <div class="mb-5 p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700 space-y-1">
                            @foreach($errors->all() as $err)
                                <div class="flex items-center space-x-1.5">
                                    <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0 text-rose-500"></i>
                                    <span>{{ $err }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Kata Sandi Saat Ini</label>
                            <div class="relative">
                                <input type="password" name="current_password" required
                                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 font-mono"
                                    placeholder="Masukkan kata sandi lama Anda">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Kata Sandi Baru</label>
                            <input type="password" name="new_password" required minlength="6"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 font-mono"
                                placeholder="Minimal 6 karakter">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Konfirmasi Kata Sandi Baru</label>
                            <input type="password" name="new_password_confirmation" required minlength="6"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 font-mono"
                                placeholder="Ketik ulang kata sandi baru">
                        </div>

                        <div class="pt-3">
                            <button type="submit" class="w-full py-2.5 px-4 bg-brand-navy hover:bg-slate-900 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-2">
                                <i data-lucide="check" class="w-4 h-4"></i>
                                <span>Perbarui Kata Sandi</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

