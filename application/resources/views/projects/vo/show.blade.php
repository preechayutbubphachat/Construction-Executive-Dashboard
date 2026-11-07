<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $vo->reference_no }} · Variation Order</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>@media print { .no-print { display: none !important; } body { background: #fff !important; } }</style>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
<main class="mx-auto max-w-4xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">

    <div class="no-print flex items-center justify-between rounded-xl bg-white p-4 shadow-sm border border-slate-200 text-xs font-bold">
        <a class="text-cyan-700 hover:underline" href="{{ route('projects.vo.index', $project) }}">← Back to VO Register</a>
        <button type="button" onclick="window.print()" class="rounded-lg bg-slate-800 px-4 py-2 text-white">Print / PDF</button>
    </div>

    @if(session('status'))
        <div class="no-print rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-900">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="no-print rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="rounded-xl bg-white p-8 shadow-sm border border-slate-200 space-y-6">
        <div class="flex items-start justify-between border-b-2 border-slate-900 pb-4">
            <div>
                <div class="text-[11px] font-black uppercase tracking-wider text-slate-500">Change Management</div>
                <h1 class="text-2xl font-black">VARIATION ORDER</h1>
                <div class="text-sm font-semibold text-slate-700">ใบสั่งเปลี่ยนแปลงงาน — {{ $vo->title }}</div>
            </div>
            <div class="text-right">
                <div class="font-mono text-sm font-bold">{{ $vo->reference_no }}</div>
                <div class="mt-1 rounded bg-slate-100 px-2 py-0.5 text-[11px] font-bold">{{ $vo->status }}</div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 text-xs">
            <div><span class="font-bold uppercase text-[10px] text-slate-500">Project</span><div class="font-bold">{{ $project->name }} ({{ $project->code }})</div></div>
            <div><span class="font-bold uppercase text-[10px] text-slate-500">Requested by</span><div class="font-bold">{{ $vo->creator?->name }} · {{ $vo->created_at->format('d/m/Y') }}</div></div>
            <div><span class="font-bold uppercase text-[10px] text-slate-500">Type / Reason</span><div class="font-bold">{{ $vo->type }} · {{ str_replace('_', ' ', $vo->reason) }}</div></div>
        </div>

        @if($vo->description)
            <p class="rounded-lg bg-slate-50 p-4 text-xs text-slate-700">{{ $vo->description }}</p>
        @endif

        <div class="grid grid-cols-2 gap-4">
            <div class="rounded-lg border border-slate-200 p-4">
                <div class="text-[10px] font-bold uppercase text-slate-500">Cost Impact</div>
                <div class="text-2xl font-black font-mono {{ $vo->cost_impact >= 0 ? 'text-rose-700' : 'text-emerald-700' }}">{{ $vo->cost_impact >= 0 ? '+' : '-' }}฿{{ number_format(abs((float) $vo->cost_impact), 2) }}</div>
            </div>
            <div class="rounded-lg border border-slate-200 p-4">
                <div class="text-[10px] font-bold uppercase text-slate-500">Time Impact (EOT)</div>
                <div class="text-2xl font-black font-mono">{{ $vo->time_impact_days >= 0 ? '+' : '' }}{{ $vo->time_impact_days }} days</div>
            </div>
        </div>

        @if($vo->status === 'APPROVED')
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-xs">
                <div class="font-bold text-emerald-900">Applied to Contract Baseline</div>
                <div class="mt-1 font-mono">BAC: ฿{{ number_format($vo->previous_budget_amount, 2) }} → <strong>฿{{ number_format($vo->new_budget_amount, 2) }}</strong></div>
                <div class="font-mono">Finish: {{ $vo->previous_finish_date->format('d/m/Y') }} → <strong>{{ $vo->new_finish_date->format('d/m/Y') }}</strong></div>
            </div>
        @endif

        @if($vo->status === 'REJECTED')
            <div class="rounded-lg border border-rose-200 bg-rose-50 p-4 text-xs text-rose-900"><strong>Rejected:</strong> {{ $vo->rejection_reason }}</div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
            @php
                $levels = [
                    ['L1 Site Verification', $vo->siteEngineer, $vo->site_verified_at, $vo->site_notes, 'SUBMITTED', 'projects.vo.verify-site', 'Sign L1'],
                    ['L2 QS Cost Certification', $vo->quantitySurveyor, $vo->qs_verified_at, $vo->qs_notes, 'VERIFIED_SITE', 'projects.vo.verify-qs', 'Sign L2'],
                    ['L3 Director Approval', $vo->projectDirector, $vo->director_approved_at, $vo->director_notes, 'VERIFIED_QS', 'projects.vo.approve-director', 'Approve & Apply'],
                ];
            @endphp
            @foreach($levels as [$label, $user, $at, $notes, $needs, $routeName, $btn])
                <div class="rounded-lg border border-slate-200 p-4 {{ $vo->status === $needs ? 'ring-2 ring-violet-300' : '' }}">
                    <div class="text-[10px] font-bold uppercase text-slate-400">{{ $label }}</div>
                    @if($at)
                        <div class="mt-2 font-serif italic">/ {{ $user?->name }} /</div>
                        <div class="text-[10px] text-slate-500">{{ $at->format('d/m/Y H:i') }}</div>
                        @if($notes)<div class="mt-1 text-[10px] italic text-slate-500">"{{ $notes }}"</div>@endif
                    @elseif($vo->status === $needs)
                        <form action="{{ route($routeName, [$project, $vo]) }}" method="POST" class="no-print mt-2">
                            @csrf
                            <input type="text" name="notes" placeholder="Notes..." class="mb-2 w-full rounded border px-2 py-1 text-[11px]">
                            <button type="submit" class="w-full rounded bg-violet-600 px-3 py-1.5 font-bold text-white hover:bg-violet-700">{{ $btn }}</button>
                        </form>
                    @else
                        <div class="mt-2 text-slate-400 italic">Pending</div>
                    @endif
                </div>
            @endforeach
        </div>

        @if(! in_array($vo->status, ['APPROVED', 'REJECTED'], true))
            <form action="{{ route('projects.vo.reject', [$project, $vo]) }}" method="POST" class="no-print flex gap-2 border-t border-slate-100 pt-4">
                @csrf
                <input type="text" name="reason" required placeholder="Rejection reason..." class="flex-1 rounded border border-slate-300 px-3 py-1.5 text-xs">
                <button type="submit" class="rounded bg-rose-600 px-4 py-1.5 text-xs font-bold text-white hover:bg-rose-700">Reject</button>
            </form>
        @endif
    </div>
</main>
</body>
</html>
