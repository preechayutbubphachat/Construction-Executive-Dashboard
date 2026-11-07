@props([
    'makerName',
    'makerRole' => 'Prepared by (Maker)',
    'makerDate' => null,
    'checkerName' => null,
    'checkerRole' => 'Authorized by (Checker)',
    'checkerDate' => null,
    'isApproved' => false,
])

<div {{ $attributes->merge(['class' => 'rounded-xl bg-obsidian-900 border border-obsidian-750 p-3.5 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs']) }}>
    <!-- Left: Maker -->
    <div class="flex items-start gap-2.5">
        <div class="w-7 h-7 rounded-lg bg-obsidian-800 border border-obsidian-700 flex items-center justify-center text-xs font-bold text-slate-300">
            ✍️
        </div>
        <div>
            <div class="text-[10px] uppercase font-bold text-slate-400">{{ $makerRole }}</div>
            <div class="font-semibold text-slate-200 mt-0.5">{{ $makerName }}</div>
            @if ($makerDate)
                <div class="text-[11px] text-slate-400 mt-0.5">{{ $makerDate }}</div>
            @endif
        </div>
    </div>

    <!-- Right: Checker -->
    <div class="flex items-start gap-2.5 sm:border-l sm:border-obsidian-750 sm:pl-3">
        <div class="w-7 h-7 rounded-lg {{ $isApproved ? 'bg-qc-500/15 border-qc-500/30 text-qc-400' : 'bg-hazard-500/15 border-hazard-500/30 text-hazard-400' }} border flex items-center justify-center text-xs font-bold">
            {{ $isApproved ? '🛡️' : '⏳' }}
        </div>
        <div>
            <div class="text-[10px] uppercase font-bold text-slate-400">{{ $checkerRole }}</div>
            @if ($checkerName)
                <div class="font-semibold text-slate-200 mt-0.5">{{ $checkerName }}</div>
                @if ($checkerDate)
                    <div class="text-[11px] text-slate-400 mt-0.5">{{ $checkerDate }}</div>
                @endif
            @else
                <div class="text-hazard-400 italic mt-0.5">Pending Maker-Checker Sign-off</div>
            @endif
        </div>
    </div>
</div>
