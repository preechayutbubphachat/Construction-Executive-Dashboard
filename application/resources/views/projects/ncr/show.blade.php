<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $ncr->reference_no }} · Non-Conformance Report</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>@media print { .no-print { display: none !important; } body { background: #fff !important; } }</style>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
<main class="mx-auto max-w-4xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">

    <div class="no-print flex items-center justify-between rounded-xl bg-white p-4 shadow-sm border border-slate-200 text-xs font-bold">
        <a class="text-cyan-700 hover:underline" href="{{ route('projects.ncr.index', $project) }}">← Back to NCR Register</a>
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
                <div class="text-[11px] font-black uppercase tracking-wider text-rose-600">Quality Control & Assurance</div>
                <h1 class="text-2xl font-black">NON-CONFORMANCE REPORT (NCR)</h1>
                <div class="text-sm font-semibold text-slate-700">{{ $ncr->title }}</div>
            </div>
            <div class="text-right">
                <div class="font-mono text-sm font-bold">{{ $ncr->reference_no }}</div>
                <div class="mt-1 inline-block rounded bg-slate-100 px-2 py-0.5 text-[11px] font-bold">{{ $ncr->status }}</div>
                @if($ncr->blocksPayment())
                    <div class="mt-1 text-[11px] font-bold text-rose-700">🚫 IPC Payment Hold Active</div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
            <div><span class="font-bold uppercase text-[10px] text-slate-500">Project</span><div class="font-bold">{{ $project->code }}</div></div>
            <div><span class="font-bold uppercase text-[10px] text-slate-500">WBS Task</span><div class="font-bold">{{ $ncr->task ? $ncr->task->wbs_code.' · '.$ncr->task->name : 'General Site' }}</div></div>
            <div><span class="font-bold uppercase text-[10px] text-slate-500">Category / Severity</span><div class="font-bold">{{ $ncr->category }} · {{ $ncr->severity }}</div></div>
            <div><span class="font-bold uppercase text-[10px] text-slate-500">Location / Zone</span><div class="font-bold">{{ $ncr->location_zone ?: '—' }}</div></div>
        </div>

        @if($ncr->description)
            <div class="rounded-lg bg-slate-50 p-4 text-xs text-slate-700">
                <div class="font-bold text-slate-900 mb-1">Non-Conformance Details</div>
                {{ $ncr->description }}
            </div>
        @endif

        @if($ncr->corrective_action)
            <div class="rounded-lg border border-amber-200 bg-amber-50/50 p-4 text-xs space-y-2">
                <div class="font-bold text-amber-900">CAPA Rectification Submitted by {{ $ncr->rectifier?->name }} ({{ $ncr->rectification_submitted_at?->format('d/m/Y H:i') }})</div>
                @if($ncr->root_cause)<div><strong>Root Cause:</strong> {{ $ncr->root_cause }}</div>@endif
                <div><strong>Corrective Action:</strong> {{ $ncr->corrective_action }}</div>
                @if($ncr->preventive_action)<div><strong>Preventive Action:</strong> {{ $ncr->preventive_action }}</div>@endif
            </div>
        @endif

        @if($ncr->verified_at)
            <div class="rounded-lg border {{ $ncr->status === 'CLOSED' ? 'border-emerald-200 bg-emerald-50' : 'border-rose-200 bg-rose-50' }} p-4 text-xs">
                <div class="font-bold">QC Verification by {{ $ncr->verifier?->name }} ({{ $ncr->verified_at->format('d/m/Y H:i') }})</div>
                <div class="mt-1">{{ $ncr->verification_notes }}</div>
            </div>
        @endif

        @if(in_array($ncr->status, ['OPEN', 'REJECTED_RECTIFICATION'], true))
            <form action="{{ route('projects.ncr.rectify', [$project, $ncr]) }}" method="POST" class="no-print space-y-3 border-t border-slate-200 pt-4">
                @csrf
                <h2 class="text-sm font-bold text-slate-900">Submit CAPA Rectification (ส่งผลการแก้ไขข้อบกพร่อง)</h2>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Root Cause (สาเหตุของข้อบกพร่อง)</label>
                    <input type="text" name="root_cause" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Corrective Action Taken (การแก้ไขที่ได้ดำเนินการแล้ว) <span class="text-rose-500">*</span></label>
                    <textarea name="corrective_action" rows="2" required class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Preventive Action (การป้องกันไม่ให้เกิดซ้ำ)</label>
                    <input type="text" name="preventive_action" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs">
                </div>
                <button type="submit" class="rounded bg-amber-600 px-4 py-2 text-xs font-bold text-white hover:bg-amber-700">Submit Rectification for QC Inspection</button>
            </form>
        @endif

        @if($ncr->status === 'RECTIFICATION_SUBMITTED')
            <div class="no-print grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-slate-200 pt-4">
                <form action="{{ route('projects.ncr.close', [$project, $ncr]) }}" method="POST" class="space-y-2 rounded-lg border border-emerald-200 bg-emerald-50/40 p-4">
                    @csrf
                    <div class="text-xs font-bold text-emerald-900">✓ QC Verify & Close NCR (Releases IPC Payment Hold)</div>
                    <input type="text" name="notes" placeholder="QC inspection remarks..." class="w-full rounded border border-slate-300 px-3 py-1.5 text-xs">
                    <button type="submit" class="w-full rounded bg-emerald-600 px-4 py-1.5 text-xs font-bold text-white hover:bg-emerald-700">Approve & Close NCR</button>
                </form>

                <form action="{{ route('projects.ncr.reject-rectification', [$project, $ncr]) }}" method="POST" class="space-y-2 rounded-lg border border-rose-200 bg-rose-50/40 p-4">
                    @csrf
                    <div class="text-xs font-bold text-rose-900">✕ Reject Rectification (Keep Payment Hold)</div>
                    <input type="text" name="reason" required placeholder="Reason rectification failed inspection..." class="w-full rounded border border-slate-300 px-3 py-1.5 text-xs">
                    <button type="submit" class="w-full rounded bg-rose-600 px-4 py-1.5 text-xs font-bold text-white hover:bg-rose-700">Reject Rectification</button>
                </form>
            </div>
        @endif
    </div>
</main>
</body>
</html>
