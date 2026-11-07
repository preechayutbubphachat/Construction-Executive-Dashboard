<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $project->code }} · Material Procurement & Site Delivery</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
<main class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">

    <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl bg-white p-4 shadow-sm border border-slate-200">
        <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
            <a class="text-cyan-700 hover:underline" href="{{ route('projects.portfolio') }}">← Portfolio</a>
            <span class="text-slate-400">·</span>
            <a class="text-cyan-700 hover:underline" href="{{ route('projects.constraints', $project) }}">Constraints</a>
            <span class="text-slate-400">·</span>
            <a class="text-cyan-700 hover:underline" href="{{ route('projects.lookahead', $project) }}">Lookahead</a>
            <span class="text-slate-400">·</span>
            <a class="text-emerald-800 hover:underline" href="{{ route('projects.ipc.index', $project) }}">💰 IPC & Payment</a>
            <span class="text-slate-400">·</span>
            <a class="text-violet-700 hover:underline" href="{{ route('projects.vo.index', $project) }}">📑 Variation Orders</a>
            <span class="text-slate-400">·</span>
            <a class="text-rose-700 hover:underline" href="{{ route('projects.ncr.index', $project) }}">🛡️ QC & NCR</a>
            <span class="text-slate-400">·</span>
            <span class="text-amber-800 font-bold underline">🧱 Procurement & Materials</span>
        </div>
        <a href="{{ route('projects.procurement.create', $project) }}" class="rounded-lg bg-amber-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-amber-700">+ New Material Requisition (ขอซื้อวัสดุเข้าไซต์)</a>
    </div>

    @if(session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-900">{{ session('status') }}</div>
    @endif

    <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200">
        <span class="rounded bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-800">Supply Chain & Site Logistics</span>
        <h1 class="mt-1 text-2xl font-black">Material Procurement & Site Delivery Tracker</h1>
        <p class="text-xs text-slate-500">ติดตามใบขอซื้อวัสดุ (MR/PO), ตรวจรับของเข้าหน้างาน (DO & Mill Certificate), เตือนวัสดุขาดล่วงหน้า 7 วัน และปลดล็อก MATERIAL Constraint อัตโนมัติ</p>
    </div>

    @if($shortageRisks->isNotEmpty())
        <div class="rounded-xl border border-rose-300 bg-rose-50 p-4 space-y-2">
            <div class="text-xs font-black uppercase text-rose-800">🚨 7-Day Lookahead Material Shortage Early Warning (แจ้งเตือนวัสดุเสี่ยงไม่พอใช้ใน 7 วันข้างหน้า)</div>
            <ul class="space-y-1 text-xs text-rose-900">
                @foreach($shortageRisks as $riskMr)
                    <li>
                        <strong>{{ $riskMr->reference_no }} · {{ $riskMr->material_name }}</strong>
                        — Short {{ number_format($riskMr->shortageQty(), 2) }} {{ $riskMr->unit }}
                        (Required on site: {{ $riskMr->required_on_site_date->format('d/m/Y') }})
                        @if($riskMr->task) · Task {{ $riskMr->task->wbs_code }} @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
            <div class="text-[11px] font-bold uppercase text-slate-400">Total Requisitions</div>
            <div class="text-xl font-black">{{ $stats['total'] }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
            <div class="text-[11px] font-bold uppercase text-slate-400">Estimated Value</div>
            <div class="text-xl font-black text-cyan-800">฿{{ number_format($stats['total_estimated_cost'], 2) }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
            <div class="text-[11px] font-bold uppercase text-slate-400">Fully Delivered</div>
            <div class="text-xl font-black text-emerald-700">{{ $stats['delivered'] }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm border border-rose-200 bg-rose-50/40">
            <div class="text-[11px] font-bold uppercase text-rose-700">7-Day Shortage Risks</div>
            <div class="text-xl font-black text-rose-800">{{ $stats['shortage_risks'] }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm border border-amber-200 bg-amber-50/40">
            <div class="text-[11px] font-bold uppercase text-amber-800">Open Material Constraints</div>
            <div class="text-xl font-black text-amber-900">{{ $stats['open_material_constraints'] }}</div>
        </div>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden">
        @if($requisitions->isEmpty())
            <div class="p-10 text-center text-sm text-slate-500">ยังไม่มีรายการขอซื้อวัสดุเข้าหน้างาน (No material requisitions recorded)</div>
        @else
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">MR Ref / Material</th>
                        <th class="px-6 py-3">WBS Task / PO</th>
                        <th class="px-6 py-3 text-right">Delivered / Required</th>
                        <th class="px-6 py-3 text-center">Required Date</th>
                        <th class="px-6 py-3 text-center">Status</th>
                        <th class="px-6 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($requisitions as $mr)
                        @php
                            $badge = match($mr->status) {
                                'REQUESTED' => 'bg-slate-100 text-slate-800',
                                'PO_ISSUED' => 'bg-blue-100 text-blue-800',
                                'PARTIALLY_DELIVERED' => 'bg-amber-100 text-amber-800',
                                'DELIVERED' => 'bg-emerald-100 text-emerald-800',
                                'REJECTED_DELIVERY' => 'bg-rose-100 text-rose-800',
                                default => 'bg-slate-100 text-slate-800',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <div class="font-mono text-[11px] text-slate-500">{{ $mr->reference_no }} · {{ $mr->category }}</div>
                                <div class="font-bold text-slate-900">{{ $mr->material_name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($mr->task)
                                    <div class="font-bold text-slate-800">{{ $mr->task->wbs_code }} · {{ $mr->task->name }}</div>
                                @endif
                                @if($mr->po_number)
                                    <div class="font-mono text-[11px] text-blue-700">PO: {{ $mr->po_number }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-mono">
                                <div class="font-bold text-slate-900">{{ number_format($mr->delivered_qty, 2) }} / {{ number_format($mr->required_qty, 2) }} {{ $mr->unit }}</div>
                                <div class="text-[10px] text-slate-400">{{ $mr->fulfillmentPercent() }}% fulfilled</div>
                            </td>
                            <td class="px-6 py-4 text-center font-mono">
                                {{ $mr->required_on_site_date->format('d/m/Y') }}
                                @if($mr->isLookaheadShortageRisk())
                                    <div class="text-[10px] font-bold text-rose-700">🚨 Shortage Risk</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $badge }}">{{ $mr->status }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('projects.procurement.show', [$project, $mr]) }}" class="rounded bg-slate-100 px-3 py-1.5 font-bold text-slate-700 hover:bg-slate-200">Manage</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</main>
</body>
</html>
