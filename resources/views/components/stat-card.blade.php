@props([
    'title',
    'value',
    'icon' => 'activity',
    'color' => 'blue',
    'subtitle' => null,
    'percentage' => null,
    'trend' => 'up'
])

@php
    $bgGradient = match($color) {
        'navy' => 'from-slate-900 to-brand-navy text-white',
        'blue' => 'from-brand-600 to-blue-700 text-white',
        'amber' => 'from-amber-500 to-amber-600 text-white',
        'emerald' => 'from-emerald-500 to-teal-600 text-white',
        'purple' => 'from-purple-600 to-indigo-700 text-white',
        'red' => 'from-red-500 to-rose-600 text-white',
        default => 'from-white to-slate-50 text-slate-900 border border-slate-200/80',
    };

    $iconBg = match($color) {
        'navy', 'blue', 'amber', 'emerald', 'purple', 'red' => 'bg-white/10 text-white',
        default => 'bg-brand-50 text-brand-600',
    };

    $textColor = match($color) {
        'navy', 'blue', 'amber', 'emerald', 'purple', 'red' => 'text-white/80',
        default => 'text-slate-500',
    };
@endphp

<div class="p-5 rounded-2xl bg-gradient-to-br {{ $bgGradient }} shadow-soft hover-lift relative overflow-hidden flex flex-col justify-between">
    <div class="flex items-start justify-between mb-3">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider {{ $textColor }}">{{ $title }}</p>
            <h3 class="text-2xl lg:text-3xl font-extrabold tracking-tight mt-1">{{ is_numeric($value) ? number_format($value) : $value }}</h3>
        </div>
        <div class="w-10 h-10 rounded-xl {{ $iconBg }} flex items-center justify-center shadow-sm">
            <i data-lucide="{{ $icon }}" class="w-5 h-5"></i>
        </div>
    </div>

    @if($subtitle || $percentage)
        <div class="flex items-center space-x-2 text-xs font-semibold pt-2 border-t border-white/10">
            @if($percentage)
                <span class="inline-flex items-center space-x-1 px-1.5 py-0.5 rounded-full {{ $trend === 'up' ? 'bg-emerald-400/20 text-emerald-300' : 'bg-red-400/20 text-red-300' }}">
                    <i data-lucide="{{ $trend === 'up' ? 'arrow-up-right' : 'arrow-down-right' }}" class="w-3 h-3"></i>
                    <span>{{ $percentage }}</span>
                </span>
            @endif
            @if($subtitle)
                <span class="{{ $textColor }} text-[11px] truncate">{{ $subtitle }}</span>
            @endif
        </div>
    @endif
</div>
