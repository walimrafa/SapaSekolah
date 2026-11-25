<div class="mb-6 flex items-center gap-2 overflow-x-auto border-b border-slate-200 pb-3">
    <a href="{{ route('socializations.completed.index') }}" 
       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-bold transition shadow-xs {{ request()->routeIs('socializations.completed.*') ? 'bg-emerald-600 text-white shadow-emerald-600/20' : 'bg-white border border-slate-200/80 text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
        <i data-lucide="presentation" class="w-4 h-4 {{ request()->routeIs('socializations.completed.*') ? 'text-white' : 'text-emerald-500' }}"></i>
        <span>1. Sosialisasi Terlaksana</span>
        <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ request()->routeIs('socializations.completed.*') ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' }}">
            {{ $navCounts['socializations'] ?? 0 }}
        </span>
    </a>

    <a href="{{ route('invitations.completed.index') }}" 
       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-bold transition shadow-xs {{ request()->routeIs('invitations.completed.*') ? 'bg-amber-600 text-white shadow-amber-600/20' : 'bg-white border border-slate-200/80 text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
        <i data-lucide="mail-check" class="w-4 h-4 {{ request()->routeIs('invitations.completed.*') ? 'text-white' : 'text-amber-500' }}"></i>
        <span>2. Hasil Undangan Acara</span>
        <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ request()->routeIs('invitations.completed.*') ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800' }}">
            {{ $navCounts['invitations'] ?? 0 }}
        </span>
    </a>

    <a href="{{ route('other-visits.completed.index') }}" 
       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-bold transition shadow-xs {{ request()->routeIs('other-visits.completed.*') ? 'bg-purple-600 text-white shadow-purple-600/20' : 'bg-white border border-slate-200/80 text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
        <i data-lucide="clipboard-list" class="w-4 h-4 {{ request()->routeIs('other-visits.completed.*') ? 'text-white' : 'text-purple-500' }}"></i>
        <span>3. Kunjungan Lainnya &amp; Audiensi</span>
        <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ request()->routeIs('other-visits.completed.*') ? 'bg-white/20 text-white' : 'bg-purple-100 text-purple-800' }}">
            {{ $navCounts['otherVisits'] ?? 0 }}
        </span>
    </a>
</div>