<x-layouts.app :project="$project" title="{{ $vo->reference_no }} · Variation Order">
    <div class="max-w-4xl mx-auto space-y-6">

        <div class="no-print flex items-center justify-between rounded-2xl bg-white p-4 shadow-card border border-slate-200/80 text-xs font-bold">
            <a class="text-safety-600 hover:text-safety-700 hover:underline transition" href="{{ route('projects.vo.index', $project) }}">&larr; Back to VO Register</a>
            <button type="button" onclick="window.print()" class="rounded-xl bg-slate-900 px-4 py-2 text-white hover:bg-slate-800 transition">Print / PDF</button>
        </div>

        @if(session('status'))
            <div class="no-print rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-900 shadow-sm">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="no-print rounded-2xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800 shadow-sm">
                @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif

        <div class="rounded-2xl bg-white p-8 shadow-card border border-slate-200/80 space-y-6">
            <div class="flex items-start justify-between border-b-2 border-slate-900 pb-4">
                <div>
                    <div class="text-[11px] font-black uppercase tracking-wider text-safety-600">Change Management</div>
                    <h1 class="text-2xl font-black text-slate-900">VARIATION ORDER</h1>
                    <div class="text-sm font-semibold text-slate-700">ใบสั่งเปลี่ยนแปลงงาน — {{ $vo->title }}</div>
                </div>
                <div class="text-right">
                    <div class="font-mono text-sm font-bold text-slate-900">{{ $vo->reference_no }}</div>
                    @php
                        $badge = match($vo->status) {
                            'SUBMITTED' => 'bg-amber-100 text-amber-800 border-amber-200',
                            'VERIFIED_SITE' => 'bg-blue-100 text-blue-800 border-blue-200',
                            'VERIFIED_QS' => 'bg-purple-100 text-purple-800 border-purple-200',
                            'APPROVED' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'REJECTED' => 'bg-rose-100 text-rose-800 border-rose-200',
                            default => 'bg-slate-100 text-slate-800 border-slate-200',
                        };
                    @endphp
                    <div class="mt-1 inline-block rounded-full border px-2.5 py-0.5 text-[11px] font-bold {{ $badge }}">{{ $vo->status }}</div>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-xs">
                <div><span class="font-bold uppercase text-[10px] text-slate-400">Project</span><div class="font-bold text-slate-900">{{ $project->name }} ({{ $project->code }})</div></div>
                <div><span class="font-bold uppercase text-[10px] text-slate-400">Requested by</span><div class="font-bold text-slate-900">{{ $vo->creator?->name }} &middot; {{ $vo->created_at->format('d/m/Y') }}</div></div>
                <div><span class="font-bold uppercase text-[10px] text-slate-400">Type / Reason</span><div class="font-bold text-slate-900">{{ $vo->type }} &middot; {{ str_replace('_', ' ', $vo->reason) }}</div></div>
            </div>

            @if($vo->description)
                <p class="rounded-xl bg-slate-50 border border-slate-200/60 p-4 text-xs text-slate-700 leading-relaxed">{{ $vo->description }}</p>
            @endif

            <div class="grid grid-cols-2 gap-4">
                <div class="rounded-xl border border-slate-200/80 bg-slate-50/50 p-4">
                    <div class="text-[10px] font-bold uppercase text-slate-400">Cost Impact</div>
                    <div class="text-2xl font-black font-mono {{ $vo->cost_impact >= 0 ? 'text-rose-700' : 'text-emerald-700' }}">{{ $vo->cost_impact >= 0 ? '+' : '-' }}฿{{ number_format(abs((float) $vo->cost_impact), 2) }}</div>
                </div>
                <div class="rounded-xl border border-slate-200/80 bg-slate-50/50 p-4">
                    <div class="text-[10px] font-bold uppercase text-slate-400">Time Impact (EOT)</div>
                    <div class="text-2xl font-black font-mono text-slate-900">{{ $vo->time_impact_days >= 0 ? '+' : '' }}{{ $vo->time_impact_days }} days</div>
                </div>
            </div>

            @if($vo->status === 'APPROVED')
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs shadow-sm">
                    <div class="font-bold text-emerald-900">Applied to Contract Baseline</div>
                    <div class="mt-1 font-mono text-slate-700">BAC: ฿{{ number_format($vo->previous_budget_amount, 2) }} &rarr; <strong class="text-emerald-800">฿{{ number_format($vo->new_budget_amount, 2) }}</strong></div>
                    <div class="font-mono text-slate-700">Finish: {{ $vo->previous_finish_date->format('d/m/Y') }} &rarr; <strong class="text-emerald-800">{{ $vo->new_finish_date->format('d/m/Y') }}</strong></div>
                </div>
            @endif

            @if($vo->status === 'REJECTED')
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-900 shadow-sm"><strong>Rejected:</strong> {{ $vo->rejection_reason }}</div>
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
                    <div class="rounded-xl border border-slate-200/80 p-4 bg-white shadow-sm {{ $vo->status === $needs ? 'ring-2 ring-safety-500/50' : '' }}">
                        <div class="text-[10px] font-bold uppercase text-slate-400">{{ $label }}</div>
                        @if($at)
                            <div class="mt-2 font-serif italic text-slate-800 font-bold">/ {{ $user?->name }} /</div>
                            <div class="text-[10px] text-slate-500">{{ $at->format('d/m/Y H:i') }}</div>
                            @if($notes)<div class="mt-1 text-[10px] italic text-slate-500">"{{ $notes }}"</div>@endif
                        @elseif($vo->status === $needs)
                            <form action="{{ route($routeName, [$project, $vo]) }}" method="POST" class="no-print mt-2 space-y-2">
                                @csrf
                                <input type="text" name="notes" placeholder="Notes..." class="w-full rounded-lg border border-slate-200 px-2.5 py-1 text-[11px] focus:border-safety-500 focus:ring-safety-500">
                                <button type="submit" class="w-full rounded-lg bg-safety-600 hover:bg-safety-700 px-3 py-1.5 font-bold text-white shadow-sm transition">{{ $btn }}</button>
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
                    <input type="text" name="reason" required placeholder="Rejection reason..." class="flex-1 rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-safety-500 focus:ring-safety-500">
                    <button type="submit" class="rounded-xl bg-rose-600 hover:bg-rose-700 px-4 py-2 text-xs font-bold text-white shadow-sm transition">Reject</button>
                </form>
            @endif
        </div>
    </div>
</x-layouts.app>
