<x-layouts.app :project="$project" title="{{ $ncr->reference_no }} · Non-Conformance Report">
    <div class="max-w-4xl mx-auto space-y-6">

        <div class="no-print flex items-center justify-between rounded-2xl bg-white p-4 shadow-card border border-slate-200/80 text-xs font-bold">
            <a class="text-safety-600 hover:text-safety-700 hover:underline transition" href="{{ route('projects.ncr.index', $project) }}">&larr; Back to NCR Register</a>
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
                    <div class="text-[11px] font-black uppercase tracking-wider text-rose-600">Quality Control & Assurance</div>
                    <h1 class="text-2xl font-black text-slate-900">NON-CONFORMANCE REPORT (NCR)</h1>
                    <div class="text-sm font-semibold text-slate-700">{{ $ncr->title }}</div>
                </div>
                <div class="text-right">
                    <div class="font-mono text-sm font-bold text-slate-900">{{ $ncr->reference_no }}</div>
                    @php
                        $statusBadge = match($ncr->status) {
                            'OPEN' => 'bg-rose-100 text-rose-800 border-rose-200',
                            'RECTIFICATION_SUBMITTED' => 'bg-amber-100 text-amber-800 border-amber-200',
                            'REJECTED_RECTIFICATION' => 'bg-rose-200 text-rose-900 border-rose-300',
                            'CLOSED' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            default => 'bg-slate-100 text-slate-800 border-slate-200',
                        };
                    @endphp
                    <div class="mt-1 inline-block rounded-full border px-2.5 py-0.5 text-[11px] font-bold {{ $statusBadge }}">{{ $ncr->status }}</div>
                    @if($ncr->blocksPayment())
                        <div class="mt-1.5 text-[11px] font-bold text-rose-700">🚫 IPC Payment Hold Active</div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                <div><span class="font-bold uppercase text-[10px] text-slate-400">Project</span><div class="font-bold text-slate-900">{{ $project->code }}</div></div>
                <div><span class="font-bold uppercase text-[10px] text-slate-400">WBS Task</span><div class="font-bold text-slate-900">{{ $ncr->task ? $ncr->task->wbs_code.' · '.$ncr->task->name : 'General Site' }}</div></div>
                <div><span class="font-bold uppercase text-[10px] text-slate-400">Category / Severity</span><div class="font-bold text-slate-900">{{ $ncr->category }} · {{ $ncr->severity }}</div></div>
                <div><span class="font-bold uppercase text-[10px] text-slate-400">Location / Zone</span><div class="font-bold text-slate-900">{{ $ncr->location_zone ?: '—' }}</div></div>
            </div>

            @if($ncr->description)
                <div class="rounded-xl bg-slate-50 border border-slate-200/80 p-4 text-xs text-slate-700 leading-relaxed">
                    <div class="font-bold text-slate-900 mb-1">Non-Conformance Details</div>
                    {{ $ncr->description }}
                </div>
            @endif

            @if($ncr->corrective_action)
                <div class="rounded-xl border border-amber-200 bg-amber-50/50 p-5 text-xs space-y-2">
                    <div class="font-bold text-amber-900 text-sm">CAPA Rectification Submitted by {{ $ncr->rectifier?->name }} ({{ $ncr->rectification_submitted_at?->format('d/m/Y H:i') }})</div>
                    @if($ncr->root_cause)<div class="text-slate-800"><strong>Root Cause:</strong> {{ $ncr->root_cause }}</div>@endif
                    <div class="text-slate-800"><strong>Corrective Action:</strong> {{ $ncr->corrective_action }}</div>
                    @if($ncr->preventive_action)<div class="text-slate-800"><strong>Preventive Action:</strong> {{ $ncr->preventive_action }}</div>@endif
                </div>
            @endif

            @if($ncr->verified_at)
                <div class="rounded-xl border {{ $ncr->status === 'CLOSED' ? 'border-emerald-200 bg-emerald-50' : 'border-rose-200 bg-rose-50' }} p-5 text-xs shadow-sm">
                    <div class="font-bold text-sm {{ $ncr->status === 'CLOSED' ? 'text-emerald-900' : 'text-rose-900' }}">QC Verification by {{ $ncr->verifier?->name }} ({{ $ncr->verified_at->format('d/m/Y H:i') }})</div>
                    <div class="mt-1 text-slate-700">{{ $ncr->verification_notes }}</div>
                </div>
            @endif

            @if(in_array($ncr->status, ['OPEN', 'REJECTED_RECTIFICATION'], true))
                <form action="{{ route('projects.ncr.rectify', [$project, $ncr]) }}" method="POST" class="no-print space-y-3 border-t border-slate-100 pt-5">
                    @csrf
                    <h2 class="text-sm font-bold text-slate-900">Submit CAPA Rectification (ส่งผลการแก้ไขข้อบกพร่อง)</h2>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Root Cause (สาเหตุของข้อบกพร่อง)</label>
                        <input type="text" name="root_cause" placeholder="Identify the primary root cause (e.g. insufficient formwork vibration)..." class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-900 focus:border-safety-500 focus:ring-safety-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Corrective Action Taken (การแก้ไขที่ได้ดำเนินการแล้ว) <span class="text-rose-500">*</span></label>
                        <textarea name="corrective_action" rows="2" required placeholder="Detail the remedial concrete repair, non-shrink grout injection..." class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-900 focus:border-safety-500 focus:ring-safety-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Preventive Action (การป้องกันไม่ให้เกิดซ้ำ)</label>
                        <input type="text" name="preventive_action" placeholder="Checklist revisions, tool inspection, foreman briefing..." class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-900 focus:border-safety-500 focus:ring-safety-500">
                    </div>
                    <button type="submit" class="rounded-xl bg-safety-600 hover:bg-safety-700 px-5 py-2.5 text-xs font-bold text-white shadow-sm transition">Submit Rectification for QC Inspection</button>
                </form>
            @endif

            @if($ncr->status === 'RECTIFICATION_SUBMITTED')
                <div class="no-print grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-slate-100 pt-5">
                    <form action="{{ route('projects.ncr.close', [$project, $ncr]) }}" method="POST" class="space-y-2.5 rounded-xl border border-emerald-200 bg-emerald-50/40 p-4">
                        @csrf
                        <div class="text-xs font-bold text-emerald-900">✓ QC Verify & Close NCR (Releases IPC Payment Hold)</div>
                        <input type="text" name="notes" placeholder="QC inspection remarks..." class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs">
                        <button type="submit" class="w-full rounded-xl bg-emerald-600 hover:bg-emerald-700 px-4 py-2 text-xs font-bold text-white shadow-sm transition">Approve & Close NCR</button>
                    </form>

                    <form action="{{ route('projects.ncr.reject-rectification', [$project, $ncr]) }}" method="POST" class="space-y-2.5 rounded-xl border border-rose-200 bg-rose-50/40 p-4">
                        @csrf
                        <div class="text-xs font-bold text-rose-900">✕ Reject Rectification (Keep Payment Hold)</div>
                        <input type="text" name="reason" required placeholder="Reason rectification failed inspection..." class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs">
                        <button type="submit" class="w-full rounded-xl bg-rose-600 hover:bg-rose-700 px-4 py-2 text-xs font-bold text-white shadow-sm transition">Reject Rectification</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
