<x-layouts.app :project="$project" title="{{ $project->code }} · Materials & Supply Chain">
    <div class="space-y-6">

        <!-- Project Sub-navigation Ribbon (Unified MindInventory Tabs) -->
        <x-ced.project-subnav :project="$project" active="projects.procurement.index" />

        <!-- Top Header & Action Bar (MindInventory Breadcrumb & Actions) -->
        <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-slate-400">
                    <a href="{{ route('projects.portfolio') }}" class="hover:text-slate-600 transition">Resources Management</a>
                    <span>/</span>
                    <span class="text-slate-700">Materials</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1">
                    Materials
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Physical inventory stock, requisitions & material delivery tracking</p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-700 shadow-2xs hover:border-slate-300 transition">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Download CSV Report</span>
                </button>
                <a href="{{ route('projects.procurement.create', $project) }}" class="inline-flex items-center gap-2 rounded-xl bg-safety-orange hover:bg-orange-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-orange-500/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    <span>+ Add New Material</span>
                </a>
            </div>
        </header>

        <!-- Construction Lifecycle Process Flow (Stage 3 Active) -->
        <x-ced.workflow-stepper :project="$project" active="procurement" />

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

        <!-- 1. Top Section: 3 KPI Cards + Recent Activity Feed (MindInventory Image 3) -->
        <section class="grid grid-cols-1 lg:grid-cols-4 gap-5" aria-label="Materials Inventory Overview">
            
            <!-- Left 3 Cols: Metric Cards & Physical Inventory -->
            <div class="lg:col-span-3 space-y-5">
                
                <!-- 3 Top Metric Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    
                    <!-- Card 1: Order Expected Today -->
                    <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Order Expected Today</span>
                            <p class="mt-2 text-3xl font-black text-slate-900">13</p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-orange-50 border border-orange-200/70 text-safety-orange flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                    </div>

                    <!-- Card 2: Issued -->
                    <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Issued</span>
                            <p class="mt-2 text-3xl font-black text-slate-900">120</p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-200/70 text-blue-600 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                    </div>

                    <!-- Card 3: Invoices by Vendors -->
                    <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Invoices by Vendors</span>
                            <p class="mt-2 text-3xl font-black text-slate-900">140</p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-200/70 text-emerald-600 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Physical Inventory Table (MindInventory Image 3 Exact Table) -->
                <div class="rounded-2xl bg-white shadow-card border border-slate-200/80 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-extrabold text-slate-900">Physical Inventory</h2>
                            <p class="text-xs text-slate-500">Warehouse on-hand quantities & reorder points</p>
                        </div>
                        <div class="flex items-center gap-4 text-xs font-bold">
                            <span class="flex items-center gap-1.5 text-rose-600"><span class="w-2 h-2 rounded-full bg-rose-500"></span> Low Stock</span>
                            <span class="flex items-center gap-1.5 text-safety-orange"><span class="w-2 h-2 rounded-full bg-safety-orange"></span> Above Reorder Point</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/50 text-[11px] font-black uppercase tracking-wider text-slate-500">
                                    <th class="py-3 px-5">Material</th>
                                    <th class="py-3 px-4">Category</th>
                                    <th class="py-3 px-4">In Stock</th>
                                    <th class="py-3 px-4">Minimum Qty</th>
                                    <th class="py-3 px-4">Reorder Point</th>
                                    <th class="py-3 px-5 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                @foreach ([
                                    ['name' => 'Electrical Wire', 'cat' => 'Electrical', 'stock' => 1000, 'min' => 810, 'reorder' => 850, 'low' => false],
                                    ['name' => 'Lumber', 'cat' => 'Building Material', 'stock' => 800, 'min' => 900, 'reorder' => 950, 'low' => true],
                                    ['name' => 'PVC Pipes', 'cat' => 'Plumbing', 'stock' => 700, 'min' => 700, 'reorder' => 750, 'low' => false],
                                    ['name' => 'Plywood Sheets', 'cat' => 'Building Material', 'stock' => 900, 'min' => 750, 'reorder' => 780, 'low' => false],
                                    ['name' => 'Cement Bags', 'cat' => 'Building Material', 'stock' => 540, 'min' => 500, 'reorder' => 550, 'low' => true],
                                    ['name' => 'Gravel', 'cat' => 'Building Material', 'stock' => 502, 'min' => 600, 'reorder' => 650, 'low' => false],
                                ] as $inv)
                                    <tr class="hover:bg-slate-50/60 transition">
                                        <td class="py-3 px-5 font-bold text-slate-900">{{ $inv['name'] }}</td>
                                        <td class="py-3 px-4 text-slate-500">{{ $inv['cat'] }}</td>
                                        <td class="py-3 px-4">
                                            @if($inv['low'])
                                                <span class="inline-block px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-700 font-bold font-mono">
                                                    {{ $inv['stock'] }}
                                                </span>
                                            @else
                                                <span class="font-mono text-slate-800 font-semibold">{{ $inv['stock'] }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 font-mono text-slate-600">{{ $inv['min'] }}</td>
                                        <td class="py-3 px-4 font-mono text-slate-600">{{ $inv['reorder'] }}</td>
                                        <td class="py-3 px-5 text-right text-slate-400">
                                            <button type="button" class="p-1 rounded-lg hover:text-safety-orange hover:bg-orange-50 transition" title="Edit Inventory">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right 1 Col: Recent Activity Feed (MindInventory Image 3) -->
            <div class="rounded-2xl bg-white p-6 shadow-card border border-slate-200/80 flex flex-col justify-between space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-base font-extrabold text-slate-900">Recent Activity</h2>
                    <span class="text-xs font-bold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-xl">Today</span>
                </div>

                <div class="space-y-4 overflow-y-auto max-h-[380px] pr-1">
                    @foreach ([
                        ['name' => 'Clinton Barton', 'role' => 'Site Manager Zone-A', 'text' => 'Received delivery of 500 concrete blocks, 200 steel beams and 1000 plywood sheets.'],
                        ['name' => 'Steve Rogers', 'role' => 'Site Manager Zone-A', 'text' => 'Inspection sign-off on 50 tons DB32 rebar delivery order.'],
                        ['name' => 'Tony Stark', 'role' => 'Site Manager Zone-B', 'text' => 'Batching plant confirmed ready-mix delivery schedule for basement pour.'],
                        ['name' => 'Oliver Queen', 'role' => 'Site Manager Zone-A', 'text' => 'Issued 300 sacks cement to sub-contractor for masonry.'],
                    ] as $act)
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="font-extrabold text-slate-900 text-xs">{{ $act['name'] }}</span>
                                <span class="text-[10px] text-slate-400 font-medium">{{ $act['role'] }}</span>
                            </div>
                            <p class="text-[11px] text-slate-600 leading-relaxed">{{ $act['text'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-slate-100 pt-3 text-center">
                    <a href="#" class="text-xs font-bold text-safety-orange hover:underline">View All Warehouse Logs →</a>
                </div>
            </div>
        </section>

        <!-- 2. Bottom Row: Material Distribution Comparative Bar Chart (MindInventory Image 3) -->
        <section class="rounded-2xl bg-white p-6 shadow-card border border-slate-200/80 space-y-4" aria-label="Material Distribution">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Material Distribution</h2>
                    <p class="text-xs text-slate-500">Actual vs. Early vs. Forecasted Early Consumption</p>
                </div>
                <div class="flex flex-wrap items-center gap-4 text-xs font-bold">
                    <span class="flex items-center gap-1.5 text-slate-600"><span class="w-2.5 h-2.5 rounded bg-orange-100"></span> Actual Distribution</span>
                    <span class="flex items-center gap-1.5 text-slate-900"><span class="w-2.5 h-2.5 rounded bg-slate-900"></span> Early Distribution</span>
                    <span class="flex items-center gap-1.5 text-safety-orange"><span class="w-2.5 h-2.5 rounded bg-safety-orange"></span> Forecasted Early Distribution</span>
                </div>
            </div>

            <!-- 12-Month Grouped Bar Chart Visual -->
            <div class="relative h-48 flex items-end justify-between px-2 pt-6">
                @foreach ([
                    ['m' => 'Jan', 'act' => 45, 'early' => 60, 'fore' => 75],
                    ['m' => 'Feb', 'act' => 50, 'early' => 90, 'fore' => 85],
                    ['m' => 'Mar', 'act' => 65, 'early' => 80, 'fore' => 110],
                    ['m' => 'Apr', 'act' => 80, 'early' => 120, 'fore' => 105],
                    ['m' => 'May', 'act' => 70, 'early' => 100, 'fore' => 95],
                    ['m' => 'Jun', 'act' => 60, 'early' => 110, 'fore' => 115],
                    ['m' => 'Jul', 'act' => 85, 'early' => 95, 'fore' => 120],
                    ['m' => 'Aug', 'act' => 75, 'early' => 105, 'fore' => 90],
                    ['m' => 'Sep', 'act' => 95, 'early' => 85, 'fore' => 110],
                    ['m' => 'Oct', 'act' => 110, 'early' => 90, 'fore' => 125],
                    ['m' => 'Nov', 'act' => 80, 'early' => 100, 'fore' => 95],
                    ['m' => 'Dec', 'act' => 90, 'early' => 115, 'fore' => 105],
                ] as $bar)
                    <div class="flex flex-col items-center gap-1.5 h-full justify-end">
                        <div class="flex items-end gap-1">
                            <div class="w-2 rounded-t bg-orange-100" style="height: {{ $bar['act'] }}px;"></div>
                            <div class="w-2 rounded-t bg-slate-900" style="height: {{ $bar['early'] }}px;"></div>
                            <div class="w-2 rounded-t bg-safety-orange" style="height: {{ $bar['fore'] }}px;"></div>
                        </div>
                        <span class="text-[10px] font-mono text-slate-500">{{ $bar['m'] }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- 3. Canonical Material Requisitions Register Table (Preserving Exact Rigor & Assertions) -->
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
                    ยังไม่มีรายการขอซื้อวัสดุในโครงการนี้
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-[11px] font-black uppercase tracking-wider text-slate-500 border-b border-slate-200/80">
                            <tr>
                                <th class="p-3.5 pl-6">Ref No.</th>
                                <th class="p-3.5">Material & Spec</th>
                                <th class="p-3.5">Category</th>
                                <th class="p-3.5 text-right">Required Qty</th>
                                <th class="p-3.5 text-right">Delivered Qty</th>
                                <th class="p-3.5">Required On-Site</th>
                                <th class="p-3.5">Status</th>
                                <th class="p-3.5">PO Number</th>
                                <th class="p-3.5 text-right pr-6">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach($requisitions as $mr)
                                <tr class="hover:bg-slate-50/60 transition {{ $mr->isLookaheadShortageRisk() ? 'bg-rose-50/30' : '' }}">
                                    <td class="p-3.5 pl-6 font-mono font-bold text-slate-900">
                                        <a href="{{ route('projects.procurement.show', [$project, $mr]) }}" class="text-sky-700 hover:underline">
                                            {{ $mr->reference_no }}
                                        </a>
                                        @if($mr->isLookaheadShortageRisk())
                                            <span class="ml-1 inline-block px-1.5 py-0.2 rounded text-[10px] font-black bg-rose-100 text-rose-800">RISK</span>
                                        @endif
                                    </td>
                                    <td class="p-3.5">
                                        <div class="font-bold text-slate-900">{{ $mr->material_name }}</div>
                                        @if($mr->task)
                                            <div class="text-[11px] text-slate-500 font-mono">Task: {{ $mr->task->wbs_code }} · {{ $mr->task->name }}</div>
                                        @endif
                                        @if($mr->supplier_name)
                                            <div class="text-[11px] text-slate-400">Supplier: {{ $mr->supplier_name }}</div>
                                        @endif
                                    </td>
                                    <td class="p-3.5">
                                        <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-700">{{ $mr->category }}</span>
                                    </td>
                                    <td class="p-3.5 text-right font-mono font-bold">{{ number_format($mr->required_qty, 2) }} {{ $mr->unit }}</td>
                                    <td class="p-3.5 text-right font-mono font-bold {{ $mr->delivered_qty >= $mr->required_qty ? 'text-emerald-700' : 'text-slate-700' }}">
                                        {{ number_format($mr->delivered_qty, 2) }}
                                        <span class="text-[10px] text-slate-400">({{ $mr->fulfillmentPercent() }}%)</span>
                                    </td>
                                    <td class="p-3.5 font-mono {{ $mr->isLookaheadShortageRisk() ? 'text-rose-700 font-bold' : 'text-slate-600' }}">
                                        {{ $mr->required_on_site_date->format('d/m/Y') }}
                                    </td>
                                    <td class="p-3.5">
                                        @php
                                            $badgeClass = match($mr->status) {
                                                'REQUESTED' => 'bg-amber-100 text-amber-900 border-amber-300',
                                                'PO_ISSUED' => 'bg-sky-100 text-sky-900 border-sky-300',
                                                'PARTIALLY_DELIVERED' => 'bg-purple-100 text-purple-900 border-purple-300',
                                                'DELIVERED' => 'bg-emerald-100 text-emerald-900 border-emerald-300',
                                                'CANCELLED' => 'bg-slate-100 text-slate-600 border-slate-300',
                                                default => 'bg-slate-100 text-slate-700',
                                            };
                                        @endphp
                                        <span class="rounded-full border px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider {{ $badgeClass }}">
                                            {{ $mr->status }}
                                        </span>
                                    </td>
                                    <td class="p-3.5 font-mono text-[11px] text-slate-700">{{ $mr->po_number ?: '—' }}</td>
                                    <td class="p-3.5 pr-6 text-right">
                                        <a href="{{ route('projects.procurement.show', [$project, $mr]) }}" class="rounded-lg bg-slate-100 hover:bg-slate-200 px-3 py-1.5 text-xs font-bold text-slate-800 transition">
                                            Manage &rarr;
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
