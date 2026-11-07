@props([
    'title',
    'value',
    'subtitle' => null,
    'badge' => null,
    'badgeType' => 'default', // 'safety', 'qc', 'hazard', 'ncr', 'blueprint', 'default'
    'icon' => null,
])

@php
    $badgeClasses = match($badgeType) {
        'safety' => 'bg-orange-50 text-orange-700 border-orange-200',
        'qc' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'hazard' => 'bg-amber-50 text-amber-700 border-amber-200',
        'ncr' => 'bg-rose-50 text-rose-700 border-rose-200',
        'blueprint' => 'bg-sky-50 text-sky-700 border-sky-200',
        default => 'bg-slate-100 text-slate-700 border-slate-200',
    };
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl bg-white border border-slate-200/90 p-5 shadow-card hover:shadow-card-hover hover:border-safety-500/40 transition-all flex flex-col justify-between']) }}>
    <div>
        <div class="flex items-center justify-between gap-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $title }}</span>
            @if ($badge)
                <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full border {{ $badgeClasses }}">
                    {{ $badge }}
                </span>
            @elseif ($icon)
                <span class="text-sm opacity-80">{{ $icon }}</span>
            @endif
        </div>
        <div class="mt-2 text-2xl font-bold tracking-tight text-slate-900 tabular-nums">
            {{ $value }}
        </div>
    </div>
    @if ($subtitle)
        <div class="mt-2 text-xs text-slate-500 border-t border-slate-100 pt-2 truncate">
            {{ $subtitle }}
        </div>
    @endif
</div>
