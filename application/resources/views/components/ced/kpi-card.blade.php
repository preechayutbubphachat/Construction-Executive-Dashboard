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
        'safety' => 'bg-safety-500/15 text-safety-400 border-safety-500/30',
        'qc' => 'bg-qc-500/15 text-qc-400 border-qc-500/30',
        'hazard' => 'bg-hazard-500/15 text-hazard-400 border-hazard-500/30',
        'ncr' => 'bg-ncr-500/15 text-ncr-400 border-ncr-500/30',
        'blueprint' => 'bg-blueprint-500/15 text-blueprint-400 border-blueprint-500/30',
        default => 'bg-obsidian-750 text-slate-300 border-obsidian-700',
    };
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl bg-obsidian-850/90 border border-obsidian-700/80 p-5 shadow-industrial hover:border-obsidian-600 transition-all flex flex-col justify-between']) }}>
    <div>
        <div class="flex items-center justify-between gap-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $title }}</span>
            @if ($badge)
                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full border {{ $badgeClasses }}">
                    {{ $badge }}
                </span>
            @elseif ($icon)
                <span class="text-sm opacity-80">{{ $icon }}</span>
            @endif
        </div>
        <div class="mt-2 text-2xl font-bold tracking-tight text-white tabular-nums">
            {{ $value }}
        </div>
    </div>
    @if ($subtitle)
        <div class="mt-2 text-xs text-slate-400 border-t border-obsidian-750 pt-2 truncate">
            {{ $subtitle }}
        </div>
    @endif
</div>
