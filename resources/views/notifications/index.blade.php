<x-app-layout title="Notifikasi Sistem">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Notifikasi Sistem</h1>
                <p class="text-xs text-slate-500 mt-0.5">Pemberitahuan aktivitas, pengajuan proposal, kuisioner, dan jadwal follow-up.</p>
            </div>
            <div class="flex items-center gap-2">
                <form action="{{ route('notifications.read-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-700 shadow-xs transition">
                        <i data-lucide="check-check" class="w-3.5 h-3.5 text-brand-600"></i>
                        <span>Tandai Semua Dibaca</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Notification List Card -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
            @if($notifications->count() > 0)
                <div class="divide-y divide-slate-100">
                    @foreach($notifications as $item)
                        <div class="p-4 sm:p-5 flex items-start gap-4 transition hover:bg-slate-50/70 {{ $item->is_read ? 'opacity-85' : 'bg-brand-50/20' }}">
                            <!-- Icon -->
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $item->is_read ? 'bg-slate-100 text-slate-500' : 'bg-brand-50 text-brand-600' }}">
                                <i data-lucide="{{ $item->icon ?: 'bell' }}" class="w-5 h-5"></i>
                            </div>

                            <!-- Body -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-sm font-bold {{ $item->is_read ? 'text-slate-800' : 'text-brand-900' }}">
                                            {{ $item->title }}
                                        </h3>
                                        @if(!$item->is_read)
                                            <span class="inline-block w-2 h-2 rounded-full bg-rose-500"></span>
                                        @endif
                                    </div>
                                    <span class="text-[11px] text-slate-400 font-medium shrink-0">
                                        {{ $item->created_at ? $item->created_at->diffForHumans() : '' }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $item->message }}</p>

                                <div class="mt-3 flex items-center gap-2">
                                    @if($item->action_url)
                                        <form action="{{ route('notifications.read', $item->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="redirect" value="1">
                                            <button type="submit" class="inline-flex items-center gap-1 text-xs font-bold text-brand-600 hover:text-brand-700">
                                                <span>Buka Halaman</span>
                                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if(!$item->is_read)
                                        <span class="text-slate-300">•</span>
                                        <form action="{{ route('notifications.read', $item->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-xs text-slate-500 hover:text-slate-700 font-medium">
                                                Tandai Dibaca
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($notifications->hasPages())
                    <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                        {{ $notifications->links() }}
                    </div>
                @endif
            @else
                <div class="py-16 px-4 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="bell-off" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800">Tidak ada notifikasi</h3>
                    <p class="text-xs text-slate-500 mt-1">Anda sudah melihat seluruh pemberitahuan sistem terbaru.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
