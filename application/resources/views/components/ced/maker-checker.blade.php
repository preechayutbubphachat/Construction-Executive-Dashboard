@props([
    'makerName',
    'makerRole' => 'Prepared by (Maker)',
    'makerDate' => null,
    'checkerName' => null,
    'checkerRole' => 'Authorized by (Checker)',
    'checkerDate' => null,
    'isApproved' => false,
])

<div {{ $attributes->merge(['class' => 'rounded-2xl bg-white border border-slate-200/90 p-4 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs shadow-card']) }}>
    <!-- Left: Maker -->
    <div class="flex items-start gap-2.5">
        <div class="w-8 h-8 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-xs font-bold text-slate-600">
            ✍️
        </div>
        <div>
            <div class="text-[10px] uppercase font-bold text-slate-400">{{ $makerRole }}</div>
            <div class="font-bold text-slate-800 mt-0.5">{{ $makerName }}</div>
            @if ($makerDate)
                <div class="text-[11px] text-slate-500 mt-0.5">{{ $makerDate }}</div>
            @endif
        </div>
    </div>

    <!-- Right: Checker -->
    <div class="flex items-start gap-2.5 sm:border-l sm:border-slate-200 sm:pl-3">
        <div class="w-8 h-8 rounded-xl {{ $isApproved ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-amber-50 border-amber-200 text-amber-700' }} border flex items-center justify-center text-xs font-bold">
            {{ $isApproved ? '🛡️' : '⏳' }}
        </div>
        <div>
            <div class="text-[10px] uppercase font-bold text-slate-400">{{ $checkerRole }}</div>
            @if ($checkerName)
                <div class="font-bold text-slate-800 mt-0.5">{{ $checkerName }}</div>
                @if ($checkerDate)
                    <div class="text-[11px] text-slate-500 mt-0.5">{{ $checkerDate }}</div>
                @endif
            @else
                <div class="text-amber-600 font-semibold italic mt-0.5">Pending Maker-Checker Sign-off</div>
            @endif
        </div>
    </div>
</div>
