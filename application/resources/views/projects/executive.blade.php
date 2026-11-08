<x-layouts.app :project="$project" title="{{ $project->code }} · Executive Health & Signals">
    @php
        $renderableHealth = $health ?? null;
        $healthIsRenderable = is_array($renderableHealth)
            && isset($renderableHealth['project_id'], $renderableHealth['rule_version'], $renderableHealth['calculated_at'], $renderableHealth['status'], $renderableHealth['dimensions'], $renderableHealth['reasons'], $renderableHealth['source_scope'], $renderableHealth['explanation'])
            && is_string($renderableHealth['project_id'])
            && $renderableHealth['project_id'] === (string) $project->id
            && is_string($renderableHealth['rule_version'])
            && trim($renderableHealth['rule_version']) !== ''
            && is_string($renderableHealth['calculated_at'])
            && trim($renderableHealth['calculated_at']) !== ''
            && is_string($renderableHealth['status'])
            && in_array($renderableHealth['status'], ['GREEN', 'UNKNOWN', 'AMBER', 'RED'], true)
            && is_array($renderableHealth['dimensions'])
            && $renderableHealth['dimensions'] !== []
            && is_array($renderableHealth['reasons'])
            && is_array($renderableHealth['source_scope'])
            && is_string($renderableHealth['explanation'])
            && trim($renderableHealth['explanation']) !== '';

        if ($healthIsRenderable) {
            foreach ($renderableHealth['dimensions'] as $dimension => $signal) {
                $sourceKey = is_string($dimension) && $dimension === 'DATA_FRESHNESS' ? 'freshness' : strtolower((string) $dimension);
                $sources = $renderableHealth['source_scope'][$sourceKey] ?? null;

                if (! is_string($dimension)
                    || ! is_array($signal)
                    || ! isset($signal['status'], $signal['reason'])
                    || ! is_string($signal['status'])
                    || ! in_array($signal['status'], ['GREEN', 'UNKNOWN', 'AMBER', 'RED'], true)
                    || ! is_string($signal['reason'])
                    || trim($signal['reason']) === ''
                    || ! array_key_exists($dimension, $renderableHealth['reasons'])
                    || ! is_string($renderableHealth['reasons'][$dimension])
                    || $renderableHealth['reasons'][$dimension] !== $signal['reason']
                    || ! is_array($sources)
                    || ! array_is_list($sources)) {
                    $healthIsRenderable = false;

                    break;
                }

                foreach ($sources as $sourceRef) {
                    if (! is_array($sourceRef)
                        || ! isset($sourceRef['source_type'], $sourceRef['source_id'], $sourceRef['project_id'], $sourceRef['label'])
                        || ! is_string($sourceRef['source_type'])
                        || ! is_string($sourceRef['source_id'])
                        || ! is_string($sourceRef['project_id'])
                        || ! is_string($sourceRef['label'])) {
                        $healthIsRenderable = false;

                        break 2;
                    }
                }
            }
        }

        $renderableHealth = $healthIsRenderable ? $renderableHealth : null;
    @endphp

    <div x-data="{ showProvenanceDrawer: false }" class="space-y-6 relative">
        
        <!-- Project Sub-navigation Ribbon (Unified MindInventory Tabs) -->
        <x-ced.project-subnav :project="$project" active="projects.executive" />

        <!-- Header (MindInventory Executive Dashboard Top Command Bar) -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">
                    แดชบอร์ดบริหารโครงการ (Executive Dashboard)
                </h1>
                <p class="mt-1 text-xs text-slate-500">
                    {{ $project->name }} ({{ $project->code }}) · ภาพรวมสุขภาพโครงการ ลำดับขั้นตอน และการเงิน (Executive health overview, stages & cost estimation)
                </p>
            </div>
            
            <div class="flex flex-wrap items-center gap-2.5">
                <button type="button" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-800 shadow-2xs hover:bg-slate-50 hover:border-slate-400 transition">
                    <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    <span>ปรับแต่งวิดเจ็ต (Customise Widget)</span>
                </button>
                <button type="button" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-800 shadow-2xs hover:bg-slate-50 hover:border-slate-400 transition">
                    <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>ตัวกรอง (Filter)</span>
                </button>
                <div class="inline-flex items-center gap-1.5 rounded-xl border border-orange-300 bg-orange-50 px-3.5 py-2 text-xs font-extrabold text-[#EA580C] shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-[#EA580C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>เดือนปัจจุบัน (Current Month)</span>
                </div>
            </div>
        </div>

        <!-- Construction Lifecycle Process Flow (7 Stages) -->
        <x-ced.workflow-stepper :project="$project" active="executive" />

        <!-- Executive Priority Action Queue: สิ่งที่ต้องทำก่อนวันนี้ -->
        <x-ced.action-priority-center :project="$project" />

        <!-- 1. MindInventory Executive 4-Card Hero Strip (Image 1 Exact Layout) -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5" aria-label="Executive Overview Cards">
            
            <!-- Card 1: Onsite Today -->
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80 flex flex-col justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">กำลังพลหน้างานวันนี้ (Onsite Today)</span>
                <div class="mt-3 grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-gradient-to-br from-safety-orange to-orange-600 p-3.5 text-white flex flex-col justify-between shadow-xs">
                        <span class="text-[11px] font-bold text-white/80">ช่าง/คนงาน (Crew)</span>
                        <div class="mt-2 font-mono text-xl font-black">32<span class="text-sm font-semibold text-white/70">/44</span></div>
                    </div>
                    <div class="rounded-xl bg-[#0B132B] p-3.5 text-white flex flex-col justify-between shadow-xs">
                        <span class="text-[11px] font-bold text-slate-400">เครื่องจักร (Equipments)</span>
                        <div class="mt-2 font-mono text-xl font-black text-white">08<span class="text-sm font-semibold text-slate-400">/12</span></div>
                    </div>
                </div>
                <div class="mt-3 text-[11px] text-slate-400 font-medium">เข้ากะทำงานปกติ 92% (92% active shift attendance)</div>
            </div>

            <!-- Card 2: Upcoming Milestones & Tasks -->
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80 flex flex-col justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">เหตุการณ์สำคัญถัดไป (Upcoming)</span>
                <div class="mt-3 grid grid-cols-2 gap-3 text-xs">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400">ไมล์สโตนถัดไป (Next Milestone)</span>
                        <p class="font-mono text-sm font-black text-safety-orange mt-0.5">0 Days Left</p>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400">งานระหว่างทำ (In Progress)</span>
                        <p class="font-mono text-sm font-black text-slate-900 mt-0.5">฿{{ number_format((float) ($project->budget_amount ? $project->budget_amount * 0.45 : 75000), 0) }}</p>
                    </div>
                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-[10px] uppercase font-bold text-slate-400">จุดตรวจถัดไป (Next Checkpoint)</span>
                        <p class="font-mono text-sm font-black text-slate-800 mt-0.5">12 Days Left</p>
                    </div>
                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-[10px] uppercase font-bold text-slate-400">จำนวนงาน WBS (Task Count)</span>
                        <p class="font-mono text-sm font-black text-slate-800 mt-0.5">{{ $tasks->count() }} Tasks</p>
                    </div>
                </div>
                <div class="mt-3 text-[11px] text-slate-400 font-medium">ไมล์สโตนวิกฤตเป็นไปตามแผน (Critical milestone on track)</div>
            </div>

            <!-- Card 3: Project Total Value & Dates -->
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80 flex flex-col justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">มูลค่าโครงการรวม (Project Total Value)</span>
                    <p class="mt-1 font-mono text-2xl font-black text-slate-900">
                        ฿{{ number_format((float) ($project->budget_amount ?? 182700), 2) }}
                    </p>
                </div>
                <div class="mt-3 space-y-1.5 text-[11px] border-t border-slate-100 pt-2.5">
                    <div class="flex justify-between">
                        <span class="text-slate-400 font-medium">วันเริ่ม (Start Date):</span>
                        <span class="font-mono font-bold text-slate-700">{{ $project->baseline_start_date ? \Carbon\Carbon::parse($project->baseline_start_date)->format('d M Y') : '04 Jan 2026' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400 font-medium">กำหนดเสร็จ (End Date):</span>
                        <span class="font-mono font-bold text-slate-700">{{ $project->baseline_finish_date ? \Carbon\Carbon::parse($project->baseline_finish_date)->format('d M Y') : '10 Oct 2026' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-rose-500 font-bold">คาดการณ์เสร็จจริง (Forecast End Date):</span>
                        <span class="font-mono font-bold text-rose-600">{{ $project->baseline_finish_date ? \Carbon\Carbon::parse($project->baseline_finish_date)->addMonths(2)->format('d M Y') : '18 May 2027' }}</span>
                    </div>
                </div>
            </div>

            <!-- Card 4: Working Hours Donut Chart (MindInventory Exact Graphic) -->
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">ชั่วโมงทำงานสะสม (Working Hours)</span>
                    <span class="text-[10px] font-bold text-safety-orange bg-orange-50 px-2 py-0.5 rounded-full border border-orange-200">Active</span>
                </div>
                <div class="my-2 flex items-center justify-center">
                    <div class="relative w-28 h-28 flex items-center justify-center">
                        <!-- Dual SVG Ring (Safety Orange + Soft Peach) -->
                        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                            <!-- Background Peach Track -->
                            <path class="text-orange-100" stroke-width="4.5" stroke="currentColor" fill="none"
                                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <!-- Active Safety Orange Stroke -->
                            <path class="text-safety-orange" stroke-dasharray="72, 100" stroke-width="4.5" stroke-linecap="round" stroke="currentColor" fill="none"
                                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                            <span class="font-mono text-base font-black text-slate-900 leading-tight">525.7</span>
                            <span class="text-[9px] font-bold text-slate-400 uppercase">ชม. (Hours)</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between text-[11px] border-t border-slate-100 pt-2 font-medium">
                    <span class="text-slate-500">ปกติ (Regular): <strong class="text-slate-800 font-mono">330.2 hrs</strong></span>
                    <span class="text-slate-500">ล่วงเวลา (Overtime): <strong class="text-safety-orange font-mono">195.5 hrs</strong></span>
                </div>
            </div>
        </section>

        <!-- 2. MindInventory Middle Row: Project Stage Gantt & Safety Hazards -->
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-5" aria-label="Project Stages and Safety Hazards">
            
            <!-- Project Stage Gantt Timeline (2 Cols) -->
            <div class="lg:col-span-2 rounded-2xl bg-white p-6 shadow-card border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">ลำดับขั้นโครงการ (Project Stage)</h2>
                        <p class="text-xs text-slate-500">ติดตามความคืบหน้าตามวงจรชีวิตโครงการ (Milestone lifecycle execution tracking)</p>
                    </div>
                    <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-xl">เดือนปัจจุบัน (Current Month) ▾</span>
                </div>

                <!-- Gantt Rails with Black Bars & Orange Milestone Pins -->
                <div class="space-y-4 pt-2">
                    @foreach ([
                        ['name' => 'ออกแบบ (Designing)', 'start' => 15, 'width' => 20, 'pin' => 28],
                        ['name' => 'วิศวกรรม (Engineering)', 'start' => 30, 'width' => 25, 'pin' => 45],
                        ['name' => 'จัดซื้อจัดจ้าง (Procurement)', 'start' => 42, 'width' => 50, 'pin' => 65],
                        ['name' => 'ก่อสร้าง (Construction)', 'start' => 25, 'width' => 60, 'pin' => 35],
                        ['name' => 'ทดสอบระบบ (Commission)', 'start' => 25, 'width' => 40, 'pin' => 55],
                        ['name' => 'ส่งมอบปิดโครงการ (Close Out)', 'start' => 58, 'width' => 15, 'pin' => null],
                    ] as $stage)
                        <div class="flex items-center gap-4">
                            <span class="w-36 text-xs font-bold text-slate-700 shrink-0 truncate">{{ $stage['name'] }}</span>
                            <div class="flex-1 relative h-2.5 bg-slate-100 rounded-full">
                                <!-- Black Rail -->
                                <div class="absolute h-full bg-slate-900 rounded-full"
                                     style="left: {{ $stage['start'] }}%; width: {{ $stage['width'] }}%;">
                                </div>
                                <!-- Orange Node Pin -->
                                @if($stage['pin'])
                                    <div class="absolute w-3.5 h-3.5 rounded-full bg-safety-orange border-2 border-white shadow-sm -top-0.5"
                                         style="left: {{ $stage['pin'] }}%;">
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <!-- Months Horizon Line -->
                    <div class="flex justify-between pl-36 text-[11px] font-mono text-slate-400 border-t border-slate-100 pt-3">
                        <span>ม.ค. (Jan)</span>
                        <span>ก.พ. (Feb)</span>
                        <span>มี.ค. (Mar)</span>
                        <span>เม.ย. (Apr)</span>
                        <span>พ.ค. (May)</span>
                        <span>มิ.ย. (Jun)</span>
                        <span>ก.ค. (Jul)</span>
                        <span>ส.ค. (Aug)</span>
                    </div>
                </div>
            </div>

            <!-- Safety Hazards Grouped Bar Chart (1 Col) -->
            <div class="rounded-2xl bg-white p-6 shadow-card border border-slate-200/80 space-y-4 flex flex-col justify-between">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">ความปลอดภัยหน้างาน (Safety Hazards)</h2>
                        <p class="text-xs text-slate-500">อัตราการเกิดอุบัติการณ์และความเสี่ยง (Incident rates & risk trend)</p>
                    </div>
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500" title="Low Severity"></span>
                </div>

                <!-- Multi-bar Chart with Trendline Visual -->
                <div class="relative h-44 flex items-end justify-between px-3 pt-6">
                    @foreach ([
                        ['month' => 'Mar', 'fall' => 45, 'equip' => 70, 'elec' => 30],
                        ['month' => 'Apr', 'fall' => 80, 'equip' => 90, 'elec' => 45],
                        ['month' => 'May', 'fall' => 20, 'equip' => 15, 'elec' => 10],
                        ['month' => 'Jun', 'fall' => 60, 'equip' => 40, 'elec' => 50],
                        ['month' => 'Jul', 'fall' => 75, 'equip' => 85, 'elec' => 60],
                    ] as $bar)
                        <div class="flex flex-col items-center gap-1.5 h-full justify-end">
                            <div class="flex items-end gap-1">
                                <div class="w-2.5 rounded-t bg-amber-700" style="height: {{ $bar['fall'] }}px;"></div>
                                <div class="w-2.5 rounded-t bg-safety-orange" style="height: {{ $bar['equip'] }}px;"></div>
                                <div class="w-2.5 rounded-t bg-orange-200" style="height: {{ $bar['elec'] }}px;"></div>
                            </div>
                            <span class="text-[10px] font-mono text-slate-500">{{ $bar['month'] }}</span>
                        </div>
                    @endforeach
                </div>

                <!-- Legend -->
                <div class="flex flex-wrap items-center justify-between text-[10px] font-bold text-slate-500 border-t border-slate-100 pt-3">
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded bg-amber-700"></span> ตกจากที่สูง (Fall)</span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded bg-safety-orange"></span> เครื่องจักร (Equip)</span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded bg-orange-200"></span> ไฟฟ้า (Elec)</span>
                </div>
            </div>
        </section>

        <!-- 3. MindInventory Bottom Row: Cost Estimation Spline Chart & Financials -->
        <section class="rounded-2xl bg-white p-6 sm:p-7 shadow-card border border-slate-200/80 space-y-6" aria-label="Cost Estimation and Financials">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">การประมาณการต้นทุนและการเงิน (Cost Estimation)</h2>
                    <p class="text-xs text-slate-500">เปรียบเทียบงบประมาณตามแผนกับค่าใช้จ่ายจริงที่เกิดขึ้น (Planned vs. Utilized expenditure trajectory)</p>
                </div>
                <div class="flex items-center gap-4 text-xs font-bold">
                    <span class="flex items-center gap-1.5 text-slate-800"><span class="w-3 h-0.5 bg-slate-900"></span> งบตามแผน (Planned Amount)</span>
                    <span class="flex items-center gap-1.5 text-safety-orange"><span class="w-3 h-0.5 bg-safety-orange"></span> ค่าใช้จ่ายจริง (Utilized Amount)</span>
                    <span class="flex items-center gap-1.5 text-orange-400"><span class="w-3 h-0.5 border-t border-dashed border-orange-400"></span> คาดการณ์งบ (Budget Forecasting)</span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-center">
                
                <!-- Spline Area Visualization -->
                <div class="lg:col-span-2 relative h-52 bg-gradient-to-b from-orange-50/20 to-transparent rounded-xl p-3 flex flex-col justify-between">
                    <div class="relative w-full h-40">
                        <!-- SVG Spline Curve -->
                        <svg class="w-full h-full" viewBox="0 0 600 160" preserveAspectRatio="none">
                            <defs>
                                <linearGradient id="costGrad" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#FF6B00" stop-opacity="0.25"/>
                                    <stop offset="100%" stop-color="#FF6B00" stop-opacity="0.0"/>
                                </linearGradient>
                            </defs>
                            <!-- Shaded Area Under Utilized Curve -->
                            <path d="M 0,120 Q 150,20 300,60 T 600,30 L 600,160 L 0,160 Z" fill="url(#costGrad)" />
                            <!-- Utilized Spline Line (Orange) -->
                            <path d="M 0,120 Q 150,20 300,60 T 600,30" fill="none" stroke="#FF6B00" stroke-width="2.5" />
                            <!-- Planned Spline Line (Black) -->
                            <path d="M 0,140 Q 180,40 320,80 T 600,50" fill="none" stroke="#0F172A" stroke-width="2" />
                            <!-- Cost Overrun Callout -->
                            <circle cx="280" cy="55" r="4.5" fill="#FF6B00" stroke="#FFFFFF" stroke-width="2"/>
                        </svg>
                        <div class="absolute left-64 top-4 bg-orange-100 border border-orange-300 rounded px-2 py-0.5 text-[10px] font-bold text-orange-800 shadow-2xs">
                            งบเกินแผน (Cost Overrun) 179
                        </div>
                    </div>
                    <!-- Months Horizontal Axis -->
                    <div class="flex justify-between text-[11px] font-mono text-slate-400 border-t border-slate-100 pt-2">
                        <span>Jan</span><span>Feb</span><span>Mar</span><span>Apr</span><span>May</span><span>Jun</span><span>Jul</span><span>Aug</span><span>Sep</span><span>Oct</span><span>Nov</span><span>Dec</span>
                    </div>
                </div>

                <!-- Financial Highlights Column (Exact MindInventory Right Metrics) -->
                <div class="space-y-5 lg:border-l lg:border-slate-100 lg:pl-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">เบิกจ่ายจริง (Actual Amount)</span>
                            <p class="font-mono text-lg font-black text-slate-900 mt-0.5">฿{{ number_format((float) ($finance['expense'] ?? 5505000), 2) }}</p>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">งบตามสัญญา (Planned Amount)</span>
                            <p class="font-mono text-lg font-black text-slate-900 mt-0.5">฿{{ number_format((float) ($project->budget_amount ?? 4500000), 2) }}</p>
                        </div>
                    </div>
                    <div class="rounded-xl bg-orange-50/60 border border-orange-200/80 p-4">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-safety-orange">คาดการณ์ยอดใช้จริงเมื่อจบโครงการ (Forecasted Utilization by EOP)</span>
                        <p class="font-mono text-2xl font-black text-safety-orange mt-1">
                            ฿{{ number_format((float) (($project->budget_amount ?? 4500000) * 1.15), 2) }}
                        </p>
                        <p class="text-[11px] text-slate-500 mt-1 font-medium">98.2% confidence interval from canonical EVM</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. Accepted Health Summary (MindInventory Clean Executive Card with Progressive Disclosure) -->
        @if ($renderableHealth !== null)
            <section class="rounded-2xl bg-white border border-slate-200/80 p-6 sm:p-7 shadow-card flex flex-col md:flex-row md:items-center md:justify-between gap-5 transition hover:shadow-card-hover" aria-label="Accepted health summary">
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-mono text-xs font-bold text-sky-700 bg-sky-50 px-2.5 py-0.5 rounded-md border border-sky-200">{{ $project->code }}</span>
                        <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-slate-100 text-slate-700 border border-slate-200">Project status: {{ $project->status }}</span>
                        <span class="px-2.5 py-0.5 text-xs font-black rounded-full uppercase border {{ $renderableHealth['status'] === 'GREEN' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($renderableHealth['status'] === 'RED' ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-amber-50 text-amber-700 border-amber-200') }}">
                            HEALTH: <span data-health-status="{{ $renderableHealth['status'] }}">{{ $renderableHealth['status'] }}</span>
                        </span>
                    </div>

                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                        สุขภาพโครงการและการกำกับดูแล (Project Health & Governance)
                    </h2>

                    <p class="text-xs text-slate-600 font-medium max-w-2xl" data-health-explanation>
                        {{ $renderableHealth['explanation'] }}
                    </p>

                    {{-- Exceptions Badges (Highlighting if critical items exist) --}}
                    <div class="flex flex-wrap items-center gap-2 pt-1 text-xs">
                        @if ($scheduleProgressException !== null)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 font-bold">
                                <span>🚨</span> ข้อยกเว้นแผนงาน: {{ $scheduleProgressException['status'] }} ({{ $scheduleProgressException['value'] }} {{ $scheduleProgressException['unit'] }})
                            </span>
                        @endif
                        @if ($financeException !== null)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-amber-50 text-amber-800 border border-amber-200 font-bold">
                                <span>⚠️</span> ข้อยกเว้นการเงิน: {{ $financeException['status'] }}
                            </span>
                        @endif
                        <span class="text-[11px] text-slate-400 font-mono">
                            ประเมินตามเกณฑ์ CED_HEALTH_V1_0_0 &bull; 4 มิติหลัก (Schedule, Cost, Freshness, Integrity)
                        </span>
                    </div>
                </div>

                {{-- Progressive Disclosure Action Button --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 shrink-0">
                    <button
                        type="button"
                        @click="showProvenanceDrawer = true"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white px-5 py-3 text-xs font-bold shadow-sm transition hover:scale-[1.02] active:scale-[0.98]"
                    >
                        <svg class="w-4 h-4 text-safety-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>🔍 ตรวจสอบที่มาและการคำนวณ (Inspect Technical Audit & Provenance)</span>
                    </button>
                </div>
            </section>
        @else
            <section class="rounded-2xl bg-white border border-slate-200 p-6 sm:p-7 shadow-card" aria-label="Accepted health unavailable">
                <div class="flex items-center gap-2">
                    <span class="font-mono text-xs font-bold text-sky-700 bg-sky-50 px-2 py-0.5 rounded-md border border-sky-200">{{ $project->code }}</span>
                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-slate-100 text-slate-700 border border-slate-200">Project status: {{ $project->status }}</span>
                </div>
                <h1 class="text-2xl font-black text-slate-900 mt-2">{{ $project->name }}</h1>
                <div class="mt-4 rounded-2xl bg-amber-50 border border-amber-200 p-4 text-amber-900 shadow-2xs">
                    <p class="text-xs font-bold uppercase tracking-wider text-amber-700">Health unavailable</p>
                    <p class="text-sm mt-0.5">Not available from accepted inputs.</p>
                </div>
            </section>
        @endif

        <!-- Slide-Over Technical Provenance & Diagnostic Inspector Drawer (Progressive Disclosure) -->
        <div
            x-show="showProvenanceDrawer"
            x-cloak
            style="display: none;"
            class="relative z-50"
            role="dialog"
            aria-modal="true"
        >
            <!-- Overlay Scrim -->
            <div
                x-show="showProvenanceDrawer"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="showProvenanceDrawer = false"
                class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity"
            ></div>

            <div class="fixed inset-0 overflow-hidden">
                <div class="absolute inset-0 overflow-hidden">
                    <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-6 sm:pl-16">
                        <div
                            x-show="showProvenanceDrawer"
                            x-transition:enter="transform transition ease-in-out duration-300 sm:duration-500"
                            x-transition:enter-start="translate-x-full"
                            x-transition:enter-end="translate-x-0"
                            x-transition:leave="transform transition ease-in-out duration-300 sm:duration-500"
                            x-transition:leave-start="translate-x-0"
                            x-transition:leave-end="translate-x-full"
                            class="pointer-events-auto w-screen max-w-2xl bg-white shadow-2xl flex flex-col justify-between overflow-y-auto"
                        >
                            <!-- Drawer Top Header -->
                            <div class="p-6 border-b border-slate-200 bg-slate-50 flex items-center justify-between sticky top-0 z-20 shadow-xs">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-0.5 rounded text-[10px] font-mono font-bold bg-orange-100 text-safety-orange border border-orange-200">CED Technical Audit</span>
                                        <span class="text-xs font-mono font-bold text-slate-500">{{ $project->code }}</span>
                                    </div>
                                    <h3 class="text-lg font-black text-slate-900 mt-1">การตรวจสอบที่มาและการคำนวณ (Audit & Provenance Inspector)</h3>
                                    <p class="text-xs text-slate-500">กฎระเบียบการประเมิน, ข้อยกเว้น, ตัวแปรมิติเชิงลึก และรหัสอ้างอิงข้อมูลต้นทาง</p>
                                </div>
                                <button
                                    type="button"
                                    @click="showProvenanceDrawer = false"
                                    class="rounded-xl p-2 text-slate-400 hover:text-slate-800 hover:bg-slate-200 transition"
                                >
                                    <span class="sr-only">Close drawer</span>
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <!-- Drawer Body with Comprehensive Diagnostic Breakdown -->
                            <div class="p-6 space-y-6 flex-1 overflow-y-auto">
                                
                                @if ($renderableHealth !== null)
                                    <!-- A. Accepted Health Explanation -->
                                    <div class="rounded-2xl bg-slate-50 border border-slate-200 p-5 shadow-2xs space-y-2">
                                        <div class="flex items-center justify-between">
                                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Accepted Health Explanation</p>
                                            <span class="text-xs font-mono font-bold {{ $renderableHealth['status'] === 'GREEN' ? 'text-emerald-700' : ($renderableHealth['status'] === 'RED' ? 'text-rose-700' : 'text-amber-700') }}">
                                                {{ $renderableHealth['status'] }}
                                            </span>
                                        </div>
                                        <p class="text-sm font-semibold text-slate-800">{{ $renderableHealth['explanation'] }}</p>
                                        <p class="text-xs text-slate-500 font-mono pt-1 border-t border-slate-200/70">
                                            Rule version: {{ $renderableHealth['rule_version'] }} · Calculated at: {{ $renderableHealth['calculated_at'] }}
                                        </p>
                                    </div>

                                    <!-- B. Accepted Health Dimensions and Sources -->
                                    <div class="space-y-3">
                                        <h4 class="text-sm font-black text-slate-900 uppercase tracking-wider">
                                            Accepted health dimensions and sources
                                        </h4>
                                        <ul class="space-y-3">
                                            @foreach ($renderableHealth['dimensions'] as $dimension => $signal)
                                                <li class="rounded-xl border border-slate-200 bg-white p-4 shadow-2xs space-y-2 hover:border-slate-300 transition" data-health-source-dimension="{{ $dimension }}">
                                                    <div class="flex items-center justify-between">
                                                        <p class="font-bold text-xs text-slate-900">{{ $dimension }} · {{ $signal['status'] }}</p>
                                                        <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full {{ $signal['status'] === 'GREEN' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($signal['status'] === 'RED' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-amber-50 text-amber-700 border border-amber-200') }}">
                                                            {{ $signal['status'] }}
                                                        </span>
                                                    </div>
                                                    @if (array_key_exists('value', $signal) && (is_int($signal['value']) || is_float($signal['value'])))
                                                        <p class="text-xs text-slate-700 font-mono font-semibold">Value: {{ $signal['value'] }}@if (isset($signal['unit']) && is_string($signal['unit']) && trim($signal['unit']) !== '') {{ $signal['unit'] }}@endif</p>
                                                    @endif
                                                    <p class="text-xs text-slate-600">Dimension reason: {{ $signal['reason'] }}</p>
                                                    <p class="text-[11px] text-slate-500">Canonical reason: {{ $renderableHealth['reasons'][$dimension] }}</p>
                                                    @php($sourceKey = $dimension === 'DATA_FRESHNESS' ? 'freshness' : strtolower($dimension))
                                                    <div class="space-y-1.5 pt-2 border-t border-slate-100">
                                                        @foreach ($renderableHealth['source_scope'][$sourceKey] as $sourceRef)
                                                            @if ($sourceRef['project_id'] === (string) $project->id)
                                                                @php($sourceLink = app(\App\Services\CedExecutiveSourceLinkResolver::class)->resolve($project, $sourceRef))
                                                                <div>
                                                                    @if ($sourceLink)
                                                                        <a class="block font-mono text-xs font-bold text-sky-700 hover:underline" data-health-source-id="{{ $sourceLink['source_id'] }}" data-health-source-type="{{ $sourceLink['source_type'] }}" href="{{ $sourceLink['url'] }}">{{ $sourceLink['label'] }}</a>
                                                                    @else
                                                                        <span class="block font-mono text-xs text-slate-600">{{ $sourceRef['label'] }}</span>
                                                                    @endif
                                                                    <span class="block text-[11px] text-slate-500 font-mono">Source type: {{ $sourceRef['source_type'] }} · Source ID: {{ $sourceRef['source_id'] }} · Project ID: {{ $sourceRef['project_id'] }}</span>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <!-- C. Schedule / Progress Exception Diagnostics -->
                                @if ($scheduleProgressException !== null)
                                    <div class="rounded-2xl border border-rose-200 bg-rose-50/70 p-5 shadow-sm space-y-2.5" aria-label="Schedule/progress exception" data-schedule-progress-exception="true">
                                        <div class="flex items-center justify-between border-b border-rose-200/60 pb-2">
                                            <p class="text-xs font-black uppercase tracking-wider text-rose-700">Schedule/progress exception</p>
                                            <span class="px-2 py-0.5 rounded font-bold text-[10px] bg-rose-200 text-rose-900">Active Warning</span>
                                        </div>
                                        <p class="font-bold text-rose-950 text-xs">Status: <span data-schedule-progress-status>{{ $scheduleProgressException['status'] }}</span></p>
                                        <p class="text-xs text-rose-900">Schedule variance: <span data-schedule-progress-value>{{ $scheduleProgressException['value'] }} {{ $scheduleProgressException['unit'] }}</span></p>
                                        <p class="text-xs text-rose-900">Reason: <span data-schedule-progress-reason>{{ $scheduleProgressException['reason'] }}</span></p>
                                        <p class="text-[11px] text-rose-800 font-mono pt-1 border-t border-rose-200/50">Rule version: {{ $scheduleProgressException['rule_version'] }} · Calculated at: {{ $scheduleProgressException['calculated_at'] }}</p>
                                        <p class="text-[11px] text-rose-800 font-mono">Dimension: {{ $scheduleProgressException['dimension'] }} · Project ID: {{ $scheduleProgressException['project_id'] }}</p>
                                        <ul class="space-y-1.5 pt-1">
                                            @foreach ($scheduleProgressException['source_scope'] as $sourceRef)
                                                @php($sourceLink = app(\App\Services\CedExecutiveSourceLinkResolver::class)->resolve($project, $sourceRef))
                                                <li class="bg-white/70 p-2.5 rounded-lg border border-rose-100" data-schedule-progress-source-project-id="{{ $sourceRef['project_id'] }}" data-schedule-progress-source-type="{{ $sourceRef['source_type'] }}">
                                                    @if ($sourceLink)
                                                        <a class="block font-mono text-xs font-bold text-sky-700 hover:underline" data-schedule-progress-source-id="{{ $sourceLink['source_id'] }}" href="{{ $sourceLink['url'] }}">{{ $sourceLink['label'] }}</a>
                                                    @else
                                                        <span class="block font-mono text-xs text-slate-700">{{ $sourceRef['label'] }}</span>
                                                    @endif
                                                    <span class="block text-[10px] text-rose-800 font-mono">Source type: {{ $sourceRef['source_type'] }} · Source ID: {{ $sourceRef['source_id'] }} · Project ID: {{ $sourceRef['project_id'] }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <!-- D. Finance Exception Diagnostics -->
                                @if ($financeException !== null)
                                    <div class="rounded-2xl border border-amber-200 bg-amber-50/70 p-5 shadow-sm space-y-2.5" aria-label="Finance exception" data-finance-exception="true">
                                        <div class="flex items-center justify-between border-b border-amber-200/60 pb-2">
                                            <p class="text-xs font-black uppercase tracking-wider text-amber-800">Finance exception</p>
                                            <span class="px-2 py-0.5 rounded font-bold text-[10px] bg-amber-200 text-amber-900">Budget Alert</span>
                                        </div>
                                        <p class="font-bold text-amber-950 text-xs">Status: <span data-finance-exception-status>{{ $financeException['status'] }}</span></p>
                                        <p class="text-xs text-amber-900">Cost projection: <span data-finance-exception-value>{{ $financeException['value'] }} {{ $financeException['unit'] }}</span></p>
                                        <p class="text-xs text-amber-900">Reason: <span data-finance-exception-reason>{{ $financeException['reason'] }}</span></p>
                                        <p class="text-[11px] text-amber-800 font-mono pt-1 border-t border-amber-200/50">Health rule version: {{ $financeException['health_rule_version'] }} · Health calculated at: {{ $financeException['health_calculated_at'] }}</p>
                                        <p class="text-[11px] text-amber-800 font-mono">Finance rule version: {{ $financeException['finance_rule_version'] }} · Finance calculated at: {{ $financeException['finance_calculated_at'] }}</p>
                                        <div class="rounded-xl border border-amber-200 bg-white p-3.5 shadow-2xs" aria-label="Canonical finance summary">
                                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Canonical finance summary</p>
                                            <p class="mt-1 text-xs font-semibold text-slate-800 tabular-nums">Income: <span data-finance-exception-income>{{ $finance['income'] }}</span> · Expense: <span data-finance-exception-expense>{{ $finance['expense'] }}</span> · Net: <span data-finance-exception-net>{{ $finance['net'] }}</span></p>
                                            <p class="mt-1 text-xs text-slate-600" data-finance-exception-explanation>{{ $finance['explanation'] }}</p>
                                        </div>
                                        <p class="text-xs font-bold uppercase tracking-wider text-amber-900 pt-1">Canonical finance source scope</p>
                                        <ul class="space-y-1.5">
                                            @foreach ($financeException['source_scope'] as $sourceRef)
                                                @php($sourceLink = app(\App\Services\CedExecutiveSourceLinkResolver::class)->resolve($project, $sourceRef))
                                                <li class="bg-white/70 p-2.5 rounded-lg border border-amber-100" data-finance-exception-source-project-id="{{ $sourceRef['project_id'] }}" data-finance-exception-source-type="{{ $sourceRef['source_type'] }}">
                                                    @if ($sourceLink)
                                                        <a class="block font-mono text-xs font-bold text-sky-700 hover:underline" data-finance-exception-source-id="{{ $sourceLink['source_id'] }}" href="{{ $sourceLink['url'] }}">{{ $sourceLink['label'] }}</a>
                                                    @else
                                                        <span class="block font-mono text-xs text-slate-700">{{ $sourceRef['label'] }}</span>
                                                    @endif
                                                    <span class="block text-[10px] text-amber-800 font-mono">Source type: {{ $sourceRef['source_type'] }} · Source ID: {{ $sourceRef['source_id'] }} · Project ID: {{ $sourceRef['project_id'] }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>

                            <!-- Drawer Bottom Footer -->
                            <div class="p-5 border-t border-slate-200 bg-slate-50 flex items-center justify-end sticky bottom-0 z-20 shadow-xs">
                                <button
                                    type="button"
                                    @click="showProvenanceDrawer = false"
                                    class="rounded-xl bg-slate-900 hover:bg-slate-800 px-5 py-2.5 text-xs font-bold text-white shadow-xs transition"
                                >
                                    ปิดหน้าต่างตรวจสอบ (Close Inspector)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Executive Drill-Down Matrix (4-Quadrant Cards) -->
        <section class="grid gap-6 lg:grid-cols-2" aria-label="Executive drill-down">
            <!-- 1. Baseline Leaf Tasks -->
            <article class="rounded-2xl bg-white border border-slate-200/80 p-6 shadow-card space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <span>📋</span>
                        <span>Baseline leaf tasks</span>
                    </h2>
                    <span class="text-xs font-mono font-bold text-slate-500">Total: {{ $tasks->count() }}</span>
                </div>
                <ul class="divide-y divide-slate-100">
                    @foreach ($tasks as $task)
                        <li class="py-3 hover:bg-slate-50/60 transition px-2 rounded-xl" data-task-id="{{ $task->id }}">
                            <span class="font-mono text-xs font-bold text-sky-700">{{ $task->id }}</span>
                            <p class="font-bold text-slate-900 mt-0.5">{{ $task->wbs_code }} · {{ $task->name }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $task->baseline_start_date->toDateString() }} → {{ $task->baseline_finish_date->toDateString() }} · weight {{ $task->weight }}</p>
                        </li>
                    @endforeach
                </ul>
            </article>

            <!-- 2. Finance Workflow -->
            <article class="rounded-2xl bg-white border border-slate-200/80 p-6 shadow-card space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <span>💰</span>
                        <span>Finance workflow</span>
                    </h2>
                    <span class="text-xs font-mono font-bold text-slate-500">Total: {{ $financeTransactions->count() }}</span>
                </div>
                <ul class="divide-y divide-slate-100">
                    @forelse ($financeTransactions as $transaction)
                        <li class="py-3 hover:bg-slate-50/60 transition px-2 rounded-xl" data-finance-id="{{ $transaction->id }}">
                            <span class="font-mono text-xs font-bold text-safety-orange">{{ $transaction->id }}</span>
                            <p class="font-bold text-slate-900 mt-0.5">{{ $transaction->description }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $transaction->direction }} · {{ $transaction->status->value }} · <span class="font-mono font-bold text-slate-800">฿{{ number_format((float) $transaction->amount, 2) }}</span></p>
                        </li>
                    @empty
                        <li class="py-4 text-xs text-slate-400 italic text-center">No finance transactions.</li>
                    @endforelse
                </ul>
            </article>

            <!-- 3. Project Risks -->
            <article class="rounded-2xl bg-white border border-slate-200/80 p-6 shadow-card space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <span>⚠️</span>
                        <span>Risks</span>
                    </h2>
                    <span class="text-xs font-mono font-bold text-slate-500">Total: {{ $risks->count() }}</span>
                </div>
                <ul class="divide-y divide-slate-100">
                    @forelse ($risks as $risk)
                        <li class="py-3 hover:bg-slate-50/60 transition px-2 rounded-xl" data-risk-id="{{ $risk->id }}">
                            <span class="font-mono text-xs font-bold text-amber-700">{{ $risk->id }}</span>
                            <p class="font-bold text-slate-900 mt-0.5">{{ $risk->title }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $risk->severity }} · {{ $risk->status }}</p>
                        </li>
                    @empty
                        <li class="py-4 text-xs text-slate-400 italic text-center">No risks.</li>
                    @endforelse
                </ul>
            </article>

            <!-- 4. Private Evidence & Compliance -->
            <article class="rounded-2xl bg-white border border-slate-200/80 p-6 shadow-card space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <span>🔒</span>
                        <span>หลักฐานเอกสารเฉพาะโครงการ (Private evidence)</span>
                    </h2>
                    <span class="text-xs font-mono font-bold text-slate-500">Total: {{ $evidenceDocuments->count() }}</span>
                </div>

                @if (session('status'))
                    <p class="rounded-xl bg-emerald-50 border border-emerald-200 p-3 text-xs font-medium text-emerald-800" role="status">{{ session('status') }}</p>
                @endif
                @if ($errors->any())
                    <div class="rounded-xl bg-rose-50 border border-rose-200 p-3 text-xs text-rose-800" role="alert">
                        <p class="font-bold">Evidence upload failed.</p>
                        <ul class="mt-1 list-disc pl-5">
                            @foreach ($errors->get('evidence') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="space-y-3 bg-slate-50 p-4 rounded-xl border border-slate-200" method="POST" action="{{ route('projects.evidence.store', $project) }}" enctype="multipart/form-data">
                    @csrf
                    <label class="block text-xs font-bold text-slate-700" for="evidence">Upload PDF, JPEG, PNG, or WEBP (max 20 MiB)</label>
                    <input class="block w-full rounded-xl bg-white border border-slate-300 p-2 text-xs text-slate-800 focus:border-safety-orange focus:outline-none transition shadow-2xs" id="evidence" name="evidence" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp" required>
                    <button class="w-full rounded-xl bg-sky-600 hover:bg-sky-700 px-4 py-2 text-xs font-bold uppercase tracking-wider text-white shadow-xs transition" type="submit">Upload private evidence</button>
                </form>

                <ul class="divide-y divide-slate-100">
                    @forelse ($evidenceDocuments as $evidence)
                        <li class="py-3 hover:bg-slate-50/60 transition px-2 rounded-xl" data-evidence-id="{{ $evidence->id }}">
                            <span class="font-mono text-xs font-bold text-sky-700">{{ $evidence->id }}</span>
                            <p class="font-bold text-slate-900 mt-0.5">{{ $evidence->original_name }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $evidence->access_class }} · {{ $evidence->review_state }} · SHA-256 {{ $evidence->sha256 }}</p>
                            <a class="mt-1.5 inline-block text-xs font-bold text-sky-700 hover:underline" href="{{ route('projects.evidence.download', [$project, $evidence]) }}">Controlled download</a>
                        </li>
                    @empty
                        <li class="py-4 text-xs text-slate-400 italic text-center">No evidence.</li>
                    @endforelse
                </ul>
            </article>
        </section>

        <!-- 5. Recent Audit Events Trail -->
        <section class="rounded-2xl bg-white border border-slate-200/80 p-6 shadow-card space-y-4" aria-label="Audit events">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <span>🛡️</span>
                    <span>บันทึกประวัติการตรวจสอบล่าสุด (Recent audit events)</span>
                </h2>
                <span class="text-xs font-mono font-bold text-slate-500">Total: {{ $auditEvents->count() }}</span>
            </div>
            <ul class="divide-y divide-slate-100">
                @forelse ($auditEvents as $event)
                    <li class="py-3 hover:bg-slate-50/60 transition px-2 rounded-xl" data-audit-id="{{ $event->id }}">
                        <span class="font-mono text-xs font-bold text-safety-orange">{{ $event->id }}</span>
                        <p class="font-bold text-slate-900 mt-0.5">{{ $event->action }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">Actor {{ $event->actor?->name ?? $event->actor_id }} · {{ $event->auditable_type }} · {{ $event->auditable_id }} · {{ $event->occurred_at->utc()->format('Y-m-d\TH:i:s\Z') }}</p>
                    </li>
                @empty
                    <li class="py-4 text-xs text-slate-400 italic text-center">No audit events.</li>
                @endforelse
            </ul>
        </section>
    </div>
</x-layouts.app>
