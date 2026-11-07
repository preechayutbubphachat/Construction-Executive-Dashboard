<x-layouts.app :project="$project" title="{{ $project->code }} · Material Procurement & Site Delivery">
    <div class="space-y-6">

        <!-- Top Header & Action Bar -->
        <header class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200/80 pb-5">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold">
                    <a class="text-safety-600 hover:text-safety-700 hover:underline transition" href="{{ route('projects.portfolio') }}">&larr; Back to Portfolio</a>
                    <span class="text-slate-300">&bull;</span>
                    <a class="text-slate-600 hover:text-slate-900 hover:underline transition" href="{{ route('projects.executive', $project) }}">Executive Health</a>
                    <span class="text-slate-300">&bull;</span>
                    <a class="text-slate-600 hover:text-slate-900 hover:underline transition" href="{{ route('projects.constraints', $project) }}">Constraints</a>
                    <span class="text-slate-300">&bull;</span>
                    <a class="text-slate-600 hover:text-slate-900 hover:underline transition" href="{{ route('projects.lookahead', $project) }}">Lookahead</a>
                    <span class="text-slate-300">&bull;</span>
                    <a class="text-slate-600 hover:text-slate-900 hover:underline transition" href="{{ route('projects.ipc.index', $project) }}">IPC Register 💰</a>
                    <span class="text-slate-300">&bull;</span>
                    <a class="text-rose-700 hover:underline transition" href="{{ route('projects.ncr.index', $project) }}">QC & NCR 🛡️</a>
                </div>
                <div class="flex items-center gap-2.5 mt-2">
                    <span class="px-2.5 py-0.5 rounded text-xs font-mono font-bold bg-orange-100 text-safety-600 border border-orange-200/70">
                        {{ $project->code }}
                    </span>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Material Procurement & Site Delivery Tracker</h1>
                </div>
                <p class="text-xs text-slate-500 mt-1">ติดตามใบขอซื้อวัสดุ (MR/PO), ตรวจรับของเข้าหน้างาน (DO & Mill Certificate), เตือนวัสดุขาดล่วงหน้า 7 วัน และปลดล็อก MATERIAL Constraint อัตโนมัติ</p>
            </div>
            <div>
                <a href="{{ route('projects.procurement.create', $project) }}" class="inline-flex items-center gap-2 rounded-xl bg-safety-600 hover:bg-safety-700 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    + New Material Requisition (ขอซื้อวัสดุเข้าไซต์)
                </a>
            </div>
        </header>

        @if(session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-900 shadow-sm">{{ session('status') }}</div>
        @endif

        @if($shortageRisks->isNotEmpty())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 space-y-2 shadow-sm">
                <div class="text-xs font-black uppercase text-rose-800 flex items-center gap-1.5">
                    <span>🚨</span> 7-Day Lookahead Material Shortage Early Warning (แจ้งเตือนวัสดุเสี่ยงไม่พอใช้ใน 7 วันข้างหน้า)
                </div>
                <ul class="space-y-1 text-xs text-rose-900 pl-5 list-disc font-medium">
                    @foreach($shortageRisks as $riskMr)
                        <li>
                            <strong>{{ $riskMr->reference_no }} &middot; {{ $riskMr->material_name }}</strong>
                            — Short {{ number_format($riskMr->shortageQty(), 2) }} {{ $riskMr->unit }}
                            (Required on site: {{ $riskMr->required_on_site_date->format('d/m/Y') }})
                            @if($riskMr->task) &middot; Task {{ $riskMr->task->wbs_code }} @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- KPI Metrics Grid -->
        <section class="grid grid-cols-2 lg:grid-cols-5 gap-4" aria-label="Procurement Summary">
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Requisitions</span>
                <div class="mt-2 text-2xl font-black text-slate-900">{{ $stats['total'] }}</div>
                <span class="text-[10px] text-slate-400">Total requisitions logged</span>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Estimated Value</span>
                <div class="mt-2 text-2xl font-black text-safety-600">฿{{ number_format($stats['total_estimated_cost'], 2) }}</div>
                <span class="text-[10px] text-slate-400">Commitment value</span>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Fully Delivered</span>
                <div class="mt-2 text-2xl font-black text-emerald-700">{{ $stats['delivered'] }}</div>
                <span class="text-[10px] text-slate-400">Arrived on jobsite</span>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-card border border-rose-200 bg-rose-50/20">
                <span class="text-[11px] font-bold uppercase tracking-wider text-rose-700">7-Day Shortage Risks</span>
                <div class="mt-2 text-2xl font-black text-rose-800">{{ $stats['shortage_risks'] }}</div>
                <span class="text-[10px] text-rose-600 font-semibold">Immediate supply action needed</span>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-card border border-orange-200 bg-orange-50/20">
                <span class="text-[11px] font-bold uppercase tracking-wider text-safety-700">Open Material Constraints</span>
                <div class="mt-2 text-2xl font-black text-safety-600">{{ $stats['open_material_constraints'] }}</div>
                <span class="text-[10px] text-safety-700 font-semibold">Impacting tasks today</span>
            </div>
        </section>

        <!-- Procurement Register Table -->
        <section class="rounded-2xl bg-white shadow-card border border-slate-200/80 overflow-hidden">
            <div class="border-b border-slate-200/80 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Material Requisitions & Delivery Register (ทะเบียนขอซื้อและตรวจรับพัสดุ)</h2>
                    <p class="text-[11px] text-slate-500">Supply chain fulfillment, supplier dispatch tracking & mill test certificates</p>
                </div>
                <span class="text-xs text-slate-500 font-mono font-bold">{{ $requisitions->count() }} Records</span>
            </div>

            @if($requisitions->isEmpty())
                <div class="p-12 text-center text-sm text-slate-500">
                    <div class="mx-auto w-12 h-12 text-slate-300 mb-2">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                    <p class="font-bold text-slate-700">ยังไม่มีรายการขอซื้อวัสดุเข้าหน้างาน (No material requisitions recorded)</p>
                    <p class="text-xs text-slate-400 mt-1">คลิกปุ่มด้านบนเพื่อสร้างใบขอซื้อวัสดุฉบับแรก</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 font-bold border-b border-slate-200/80">
                            <tr>
                                <th class="px-6 py-3.5">MR Ref / Material</th>
                                <th class="px-6 py-3.5">WBS Task / PO</th>
                                <th class="px-6 py-3.5 text-right">Delivered / Required</th>
                                <th class="px-6 py-3.5 text-center">Required Date</th>
                                <th class="px-6 py-3.5 text-center">Status</th>
                                <th class="px-6 py-3.5 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach($requisitions as $mr)
                                @php
                                    $badge = match($mr->status) {
                                        'REQUESTED' => 'bg-slate-100 text-slate-800 border-slate-200',
                                        'PO_ISSUED' => 'bg-blue-100 text-blue-800 border-blue-200',
                                        'PARTIALLY_DELIVERED' => 'bg-amber-100 text-amber-800 border-amber-200',
                                        'DELIVERED' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                        'REJECTED_DELIVERY' => 'bg-rose-100 text-rose-800 border-rose-200',
                                        default => 'bg-slate-100 text-slate-800 border-slate-200',
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-6 py-4">
                                        <div class="font-mono text-[11px] text-slate-500">{{ $mr->reference_no }} &middot; {{ $mr->category }}</div>
                                        <div class="font-bold text-slate-900">{{ $mr->material_name }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($mr->task)
                                            <div class="font-bold text-slate-800">{{ $mr->task->wbs_code }} &middot; {{ $mr->task->name }}</div>
                                        @endif
                                        @if($mr->po_number)
                                            <div class="font-mono text-[11px] text-blue-700 font-semibold">PO: {{ $mr->po_number }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right font-mono">
                                        <div class="font-bold text-slate-900">{{ number_format($mr->delivered_qty, 2) }} / {{ number_format($mr->required_qty, 2) }} {{ $mr->unit }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $mr->fulfillmentPercent() }}% fulfilled</div>
                                    </td>
                                    <td class="px-6 py-4 text-center font-mono">
                                        <div class="text-slate-800">{{ $mr->required_on_site_date->format('d/m/Y') }}</div>
                                        @if($mr->isLookaheadShortageRisk())
                                            <div class="text-[10px] font-bold text-rose-700">🚨 Shortage Risk</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-block rounded-full border px-2.5 py-0.5 text-[11px] font-bold {{ $badge }}">{{ $mr->status }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('projects.procurement.show', [$project, $mr]) }}" class="inline-flex items-center gap-1 rounded-xl bg-slate-100 hover:bg-slate-200 px-3 py-1.5 text-xs font-bold text-slate-700 transition">
                                            <span>Manage</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-layouts.app>
