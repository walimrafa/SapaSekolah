<x-app-layout :title="$title">
    <div class="max-w-4xl mx-auto py-12 text-center">
        <div class="w-16 h-16 rounded-2xl bg-brand-50 text-brand-600 mx-auto flex items-center justify-center mb-4 shadow-sm">
            <i data-lucide="layers" class="w-8 h-8"></i>
        </div>
        <h1 class="text-2xl font-bold text-slate-800 mb-2">{{ $title }}</h1>
        <p class="text-slate-500 text-sm mb-6 max-w-md mx-auto">Modul operasional ini disiapkan dan akan diimplementasikan pada tahap selanjutnya ({{ $phase }}).</p>

        <a href="{{ route('dashboard') }}" class="inline-flex items-center space-x-2 px-5 py-2.5 bg-brand-navy hover:bg-brand-900 text-white font-semibold text-xs rounded-xl shadow-md transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Dashboard</span>
        </a>
    </div>
</x-app-layout>
