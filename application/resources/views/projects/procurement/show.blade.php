<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $mr->reference_no }} · Material Requisition</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>@media print { .no-print { display: none !important; } body { background: #fff !important; } }</style>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
<main class="mx-auto max-w-4xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">

    <div class="no-print flex items-center justify-between rounded-xl bg-white p-4 shadow-sm border border-slate-200 text-xs font-bold">
        <a class="text-cyan-700 hover:underline" href="{{ route('projects.procurement.index', $project) }}">← Back to Procurement Register</a>
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
                <div class="text-[11px] font-black uppercase tracking-wider text-amber-700">Material Requisition & Site Delivery Record</div>
                <h1 class="text-2xl font-black">{{ $mr->material_name }}</h1>
                <div class="text-xs text-slate-500">Category: {{ $mr->category }} · Supplier: {{ $mr->supplier_name ?: 'TBD' }}</div>
            </div>
            <div class="text-right">
                <div class="font-mono text-sm font-bold">{{ $mr->reference_no }}</div>
                <div class="mt-1 inline-block rounded bg-slate-100 px-2 py-0.5 text-[11px] font-bold">{{ $mr->status }}</div>
                @if($mr->po_number)
                    <div class="mt-1 font-mono text-xs font-bold text-blue-700">PO: {{ $mr->po_number }}</div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
            <div><span class="font-bold uppercase text-[10px] text-slate-500">Required Qty</span><div class="font-mono text-sm font-black">{{ number_format($mr->required_qty, 2) }} {{ $mr->unit }}</div></div>
            <div><span class="font-bold uppercase text-[10px] text-slate-500">Delivered Qty</span><div class="font-mono text-sm font-black text-emerald-700">{{ number_format($mr->delivered_qty, 2) }} {{ $mr->unit }} ({{ $mr->fulfillmentPercent() }}%)</div></div>
            <div><span class="font-bold uppercase text-[10px] text-slate-500">Estimated Cost</span><div class="font-mono text-sm font-bold">฿{{ number_format($mr->total_estimated_cost, 2) }}</div></div>
            <div><span class="font-bold uppercase text-[10px] text-slate-500">Required On-Site</span><div class="font-mono text-sm font-bold">{{ $mr->required_on_site_date->format('d/m/Y') }}</div></div>
        </div>

        @if($mr->task || $mr->constraint)
            <div class="rounded-lg bg-slate-50 p-4 text-xs space-y-1">
                @if($mr->task)
                    <div><strong>Linked WBS Task:</strong> {{ $mr->task->wbs_code }} · {{ $mr->task->name }}</div>
                @endif
                @if($mr->constraint)
                    <div><strong>Linked Material Constraint:</strong> {{ $mr->constraint->title }} — Status: <span class="font-bold">{{ $mr->constraint->status }}</span></div>
                @endif
            </div>
        @endif

        @if($mr->delivery_order_no)
            <div class="rounded-lg border border-slate-200 p-4 text-xs space-y-1">
                <div class="font-bold text-slate-900">Latest Site Delivery & QC Inspection</div>
                <div><strong>Delivery Order (DO):</strong> {{ $mr->delivery_order_no }} · <strong>Mill/QC Certificate:</strong> {{ $mr->mill_cert_ref ?: '—' }}</div>
                <div><strong>Inspected by:</strong> {{ $mr->receiver?->name }} ({{ $mr->received_at?->format('d/m/Y H:i') }})</div>
                @if($mr->inspection_notes)<div><strong>Notes:</strong> {{ $mr->inspection_notes }}</div>@endif
            </div>
        @endif

        @if($mr->status === 'REQUESTED')
            <form action="{{ route('projects.procurement.issue-po', [$project, $mr]) }}" method="POST" class="no-print space-y-3 border-t border-slate-200 pt-4">
                @csrf
                <h2 class="text-sm font-bold text-slate-900">Step 1: Issue Purchase Order (ออกใบสั่งซื้อ PO)</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <input type="text" name="po_number" required placeholder="PO Number (e.g. PO-2026-045)" class="rounded border border-slate-300 px-3 py-1.5 text-xs font-mono">
                    <input type="text" name="supplier_name" value="{{ $mr->supplier_name }}" placeholder="Confirmed Supplier Name" class="rounded border border-slate-300 px-3 py-1.5 text-xs">
                </div>
                <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-700">Issue PO</button>
            </form>
        @endif

        @if($mr->status !== 'DELIVERED')
            <form action="{{ route('projects.procurement.deliver', [$project, $mr]) }}" method="POST" class="no-print space-y-3 border-t border-slate-200 pt-4">
                @csrf
                <h2 class="text-sm font-bold text-slate-900">Step 2: Record Site Delivery & QC Inspection (ตรวจรับวัสดุเข้าหน้างาน)</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600">Received Quantity ({{ $mr->unit }})</label>
                        <input type="number" step="0.01" min="0" name="received_qty" value="{{ $mr->shortageQty() }}" required class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600">Delivery Order (DO) No. *</label>
                        <input type="text" name="delivery_order_no" required placeholder="e.g. DO-88412" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600">Mill Test / Slump Cert Ref</label>
                        <input type="text" name="mill_cert_ref" placeholder="e.g. MTC-SYS-2026-09" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs font-mono">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600">QC Inspection Result</label>
                        <select name="qc_passed" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs font-bold">
                            <option value="1">✓ Passed QC — Accept into Site Stock (Auto-resolves Material Constraints when 100%)</option>
                            <option value="0">✕ Failed QC — Reject Delivery</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600">Inspection Notes</label>
                        <input type="text" name="inspection_notes" placeholder="Visual & certificate check remarks..." class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs">
                    </div>
                </div>
                <button type="submit" class="rounded bg-emerald-600 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-700">Record Delivery & Inspection</button>
            </form>
        @endif
    </div>
</main>
</body>
</html>
