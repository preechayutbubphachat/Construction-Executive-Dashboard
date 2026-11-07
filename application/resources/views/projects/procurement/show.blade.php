<x-layouts.app :project="$project">
    <div class="mx-auto max-w-4xl space-y-6">
        {{-- Navigation & Action Header --}}
        <div class="no-print flex items-center justify-between">
            <a class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-safety-orange transition" href="{{ route('projects.procurement.index', $project) }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Procurement Register
            </a>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-slate-800 transition">
                <svg class="w-4 h-4 text-safety-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print / PDF
            </button>
        </div>

        @if(session('status'))
            <div class="no-print rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4 text-xs font-semibold text-emerald-900 shadow-card flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('status') }}
            </div>
        @endif
        @if($errors->any())
            <div class="no-print rounded-2xl border border-rose-200 bg-rose-50/80 p-4 text-xs font-semibold text-rose-800 shadow-card">
                <div class="flex items-center gap-2 mb-1 font-bold text-rose-900">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Errors occurred:
                </div>
                @foreach($errors->all() as $error)<div>• {{ $error }}</div>@endforeach
            </div>
        @endif

        {{-- Main MR Document Card --}}
        <div class="rounded-2xl bg-white p-6 sm:p-8 shadow-card border border-slate-200/80 space-y-6">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-200 pb-5">
                <div>
                    <div class="text-[11px] font-black uppercase tracking-wider text-safety-orange">Material Requisition & Site Delivery Record</div>
                    <h1 class="text-2xl font-black text-slate-900 mt-1">{{ $mr->material_name }}</h1>
                    <div class="text-xs text-slate-500 mt-1">Category: {{ $mr->category }} · Supplier: {{ $mr->supplier_name ?: 'TBD' }}</div>
                </div>
                <div class="text-right">
                    <div class="font-mono text-sm font-black text-slate-900">{{ $mr->reference_no }}</div>
                    @php
                        $statusBadgeClass = match($mr->status) {
                            'DELIVERED' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'PO_ISSUED' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'PARTIAL' => 'bg-amber-50 text-amber-700 border-amber-200',
                            default => 'bg-slate-100 text-slate-700 border-slate-200',
                        };
                    @endphp
                    <div class="mt-1.5 inline-block rounded-full border px-3 py-0.5 text-[11px] font-black uppercase tracking-wider {{ $statusBadgeClass }}">
                        {{ $mr->status }}
                    </div>
                    @if($mr->po_number)
                        <div class="mt-1 font-mono text-xs font-bold text-blue-600">PO: {{ $mr->po_number }}</div>
                    @endif
                </div>
            </div>

            {{-- 4 Metric Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                <div class="rounded-xl bg-slate-50 p-4 border border-slate-100">
                    <span class="font-bold uppercase tracking-wider text-[10px] text-slate-500">Required Qty</span>
                    <div class="font-mono text-base font-black text-slate-900 mt-1">{{ number_format($mr->required_qty, 2) }} {{ $mr->unit }}</div>
                </div>
                <div class="rounded-xl bg-slate-50 p-4 border border-slate-100">
                    <span class="font-bold uppercase tracking-wider text-[10px] text-slate-500">Delivered Qty</span>
                    <div class="font-mono text-base font-black text-emerald-600 mt-1">{{ number_format($mr->delivered_qty, 2) }} {{ $mr->unit }} ({{ $mr->fulfillmentPercent() }}%)</div>
                </div>
                <div class="rounded-xl bg-slate-50 p-4 border border-slate-100">
                    <span class="font-bold uppercase tracking-wider text-[10px] text-slate-500">Estimated Cost</span>
                    <div class="font-mono text-base font-bold text-slate-900 mt-1">฿{{ number_format($mr->total_estimated_cost, 2) }}</div>
                </div>
                <div class="rounded-xl bg-slate-50 p-4 border border-slate-100">
                    <span class="font-bold uppercase tracking-wider text-[10px] text-slate-500">Required On-Site</span>
                    <div class="font-mono text-base font-bold text-slate-900 mt-1">{{ $mr->required_on_site_date->format('d/m/Y') }}</div>
                </div>
            </div>

            @if($mr->task || $mr->constraint)
                <div class="rounded-xl bg-slate-50/80 p-4 text-xs space-y-1.5 border border-slate-200/80">
                    @if($mr->task)
                        <div class="text-slate-700"><strong>Linked WBS Task:</strong> <span class="font-mono font-semibold">{{ $mr->task->wbs_code }}</span> · {{ $mr->task->name }}</div>
                    @endif
                    @if($mr->constraint)
                        <div class="text-slate-700"><strong>Linked Material Constraint:</strong> {{ $mr->constraint->title }} — Status: <span class="font-bold text-slate-900">{{ $mr->constraint->status }}</span></div>
                    @endif
                </div>
            @endif

            @if($mr->delivery_order_no)
                <div class="rounded-xl border border-slate-200 bg-emerald-50/30 p-4 text-xs space-y-1.5">
                    <div class="font-bold text-slate-900 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Latest Site Delivery & QC Inspection
                    </div>
                    <div class="text-slate-700"><strong>Delivery Order (DO):</strong> <span class="font-mono">{{ $mr->delivery_order_no }}</span> · <strong>Mill/QC Certificate:</strong> <span class="font-mono">{{ $mr->mill_cert_ref ?: '—' }}</span></div>
                    <div class="text-slate-700"><strong>Inspected by:</strong> {{ $mr->receiver?->name }} ({{ $mr->received_at?->format('d/m/Y H:i') }})</div>
                    @if($mr->inspection_notes)<div class="text-slate-600"><strong>Notes:</strong> {{ $mr->inspection_notes }}</div>@endif
                </div>
            @endif

            @if($mr->status === 'REQUESTED')
                <form action="{{ route('projects.procurement.issue-po', [$project, $mr]) }}" method="POST" class="no-print space-y-4 border-t border-slate-100 pt-5">
                    @csrf
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-wider text-blue-600">Procurement Stage 1</span>
                        <h2 class="text-base font-black text-slate-900">Step 1: Issue Purchase Order (ออกใบสั่งซื้อ PO)</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <input type="text" name="po_number" required placeholder="PO Number (e.g. PO-2026-045)" class="rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-xs font-mono text-slate-900 focus:bg-white focus:border-safety-orange focus:ring-1 focus:ring-safety-orange transition">
                        <input type="text" name="supplier_name" value="{{ $mr->supplier_name }}" placeholder="Confirmed Supplier Name" class="rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-xs text-slate-900 focus:bg-white focus:border-safety-orange focus:ring-1 focus:ring-safety-orange transition">
                    </div>
                    <button type="submit" class="rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-blue-700 transition">Issue PO</button>
                </form>
            @endif

            @if($mr->status !== 'DELIVERED')
                <form action="{{ route('projects.procurement.deliver', [$project, $mr]) }}" method="POST" class="no-print space-y-4 border-t border-slate-100 pt-5">
                    @csrf
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-wider text-emerald-600">Procurement Stage 2</span>
                        <h2 class="text-base font-black text-slate-900">Step 2: Record Site Delivery & QC Inspection (ตรวจรับวัสดุเข้าหน้างาน)</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Received Quantity ({{ $mr->unit }})</label>
                            <input type="number" step="0.01" min="0" name="received_qty" value="{{ $mr->shortageQty() }}" required class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-xs font-mono text-slate-900 focus:bg-white focus:border-safety-orange focus:ring-1 focus:ring-safety-orange transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Delivery Order (DO) No. *</label>
                            <input type="text" name="delivery_order_no" required placeholder="e.g. DO-88412" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-xs font-mono text-slate-900 focus:bg-white focus:border-safety-orange focus:ring-1 focus:ring-safety-orange transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Mill Test / Slump Cert Ref</label>
                            <input type="text" name="mill_cert_ref" placeholder="e.g. MTC-SYS-2026-09" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-xs font-mono text-slate-900 focus:bg-white focus:border-safety-orange focus:ring-1 focus:ring-safety-orange transition">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">QC Inspection Result</label>
                            <select name="qc_passed" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-xs font-bold text-slate-900 focus:bg-white focus:border-safety-orange focus:ring-1 focus:ring-safety-orange transition">
                                <option value="1">✓ Passed QC — Accept into Site Stock (Auto-resolves Material Constraints when 100%)</option>
                                <option value="0">✕ Failed QC — Reject Delivery</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Inspection Notes</label>
                            <input type="text" name="inspection_notes" placeholder="Visual & certificate check remarks..." class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-xs text-slate-900 focus:bg-white focus:border-safety-orange focus:ring-1 focus:ring-safety-orange transition">
                        </div>
                    </div>
                    <button type="submit" class="rounded-xl bg-emerald-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-emerald-700 transition">Record Delivery & Inspection</button>
                </form>
            @endif
        </div>
    </div>
</x-layouts.app>
