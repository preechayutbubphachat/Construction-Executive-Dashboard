<section class="space-y-6" aria-labelledby="heatmap-title">
    <!-- Header (MindInventory Clean Light Style) -->
    <header class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2">
                <a class="inline-flex items-center gap-1.5 text-xs font-bold text-orange-600 hover:text-orange-700 transition" href="{{ route('projects.portfolio') }}">
                    ← กลับสู่แฟ้มโครงการ (Back to Executive Portfolio)
                </a>
            </div>
            <h1 id="heatmap-title" class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 flex items-center gap-2.5">
                <span>🗺️</span>
                <span>แผนผังความเสี่ยงพอร์ตโครงการ (Executive Risk Heatmap)</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">ดัชนีความเสี่ยงข้ามโครงการ เกณฑ์ชี้วัดความพร้อมของงาน และเมทริกซ์การดำเนินงาน 2 มิติ (Cross-project risk index & 2D matrix)</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="rounded-lg bg-orange-100 border border-orange-200 px-3 py-1 text-xs font-mono font-bold text-orange-700">
                CED_PORTFOLIO_RISK_HEATMAP_V1
            </span>
        </div>
    </header>

    {{-- Portfolio KPI Bar (MindInventory Clean White Card Deck) --}}
    <section class="grid gap-4 sm:grid-cols-5" aria-label="Portfolio Risk Overview">
        <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/90 hover:shadow-card-hover transition-all">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">ขอบเขตโครงการ (Portfolio Scope)</p>
            <p class="mt-2 text-3xl font-black text-slate-900 tabular-nums">{{ $summary['total_projects'] }}</p>
            <p class="mt-1 text-xs text-slate-500">โครงการที่มีสิทธิ์เข้าถึง (Permitted)</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/90 hover:shadow-card-hover transition-all">
            <p class="text-xs font-bold uppercase tracking-wider text-rose-600">ความเสี่ยงวิกฤต (Critical Risk)</p>
            <p class="mt-2 text-3xl font-black text-rose-600 tabular-nums">{{ $summary['critical_count'] }}</p>
            <p class="mt-1 text-xs text-slate-500">ต้องเร่งตัดสินใจแก้ไข (Action required)</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/90 hover:shadow-card-hover transition-all">
            <p class="text-xs font-bold uppercase tracking-wider text-orange-600">ความเสี่ยงสูง (High Risk)</p>
            <p class="mt-2 text-3xl font-black text-orange-600 tabular-nums">{{ $summary['high_count'] }}</p>
            <p class="mt-1 text-xs text-slate-500">เฝ้าระวังอย่างใกล้ชิด (Monitoring)</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/90 hover:shadow-card-hover transition-all">
            <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">ความพร้อมเฉลี่ย (Avg Make-Ready)</p>
            <p class="mt-2 text-3xl font-black text-emerald-600 tabular-nums">{{ number_format($summary['average_make_ready_rate'], 1) }}%</p>
            <p class="mt-1 text-xs text-slate-500">ความพร้อมงาน 3 สัปดาห์ (3-wk readiness)</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/90 hover:shadow-card-hover transition-all">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">งบประมาณใช้จริง (Total Spent)</p>
            <p class="mt-2 text-2xl font-black text-slate-900 tabular-nums font-mono">฿{{ number_format($summary['total_expense'], 0) }}</p>
            <p class="mt-1 text-xs text-slate-500">จากแผน ฿{{ number_format($summary['total_budget'], 0) }}</p>
        </div>
    </section>

    {{-- 2D Visual Risk Heatmap Matrix (Clean White Surface) --}}
    <section class="rounded-2xl bg-white p-6 sm:p-7 shadow-card border border-slate-200/90 space-y-4" aria-label="2D Risk Matrix">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 border-b border-slate-100 pb-4">
            <div>
                <h2 class="text-base font-extrabold text-slate-900">เมทริกซ์การกระจายความเสี่ยงในการดำเนินงาน 2 มิติ (2D Operational Risk Distribution Matrix)</h2>
                <p class="text-xs text-slate-500">แนวนอน: ความเสี่ยงด้านแผนงาน (Schedule Risk &rarr;) | แนวตั้ง: ความเสี่ยงการเงินและข้อจำกัด (Financial & Constraint Risk &uarr;)</p>
            </div>
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-rose-50 border border-rose-200 font-bold text-rose-700">
                    <span class="h-2 w-2 rounded-full bg-rose-600 inline-block animate-pulse"></span> Q1 Critical
                </span>
                <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-50 border border-amber-200 font-bold text-amber-700">
                    <span class="h-2 w-2 rounded-full bg-amber-500 inline-block"></span> Q2 Schedule
                </span>
                <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-orange-50 border border-orange-200 font-bold text-orange-700">
                    <span class="h-2 w-2 rounded-full bg-orange-500 inline-block"></span> Q4 Finance
                </span>
                <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 font-bold text-emerald-700">
                    <span class="h-2 w-2 rounded-full bg-emerald-600 inline-block"></span> Q3 Sound
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 rounded-2xl border border-slate-200 bg-slate-50/60 p-4 min-h-[280px]">
            {{-- Q4: High Finance/Constraint, Low Schedule --}}
            <div class="rounded-2xl border border-orange-200 bg-orange-50/60 p-4 space-y-3 shadow-2xs">
                <div class="flex justify-between items-center text-xs font-bold text-orange-900 border-b border-orange-200 pb-1.5">
                    <span>Q4: Finance & Constraint Risk</span>
                    <span class="text-[10px] text-orange-700 font-mono">High Finance · Normal Schedule</span>
                </div>
                <div class="flex flex-wrap gap-2 pt-1">
                    @forelse (collect($allProjects)->where('quadrant', 'FINANCE_CONSTRAINT_RISK_QUADRANT') as $qp)
                        <a href="{{ route('projects.executive', $qp['id']) }}" class="rounded-xl bg-white border border-orange-300 px-3 py-1.5 text-xs font-bold text-orange-950 shadow-2xs hover:bg-orange-100 hover:border-orange-400 transition">
                            {{ $qp['code'] }} ({{ $qp['risk_score'] }})
                        </a>
                    @empty
                        <span class="text-xs text-slate-400 italic">No projects in this quadrant</span>
                    @endforelse
                </div>
            </div>

            {{-- Q1: High Finance/Constraint AND High Schedule --}}
            <div class="rounded-2xl border border-rose-200 bg-rose-50/60 p-4 space-y-3 shadow-2xs">
                <div class="flex justify-between items-center text-xs font-bold text-rose-900 border-b border-rose-200 pb-1.5">
                    <span>Q1: Critical Double Risk</span>
                    <span class="text-[10px] text-rose-700 font-mono">High Finance · High Schedule</span>
                </div>
                <div class="flex flex-wrap gap-2 pt-1">
                    @forelse (collect($allProjects)->where('quadrant', 'CRITICAL_QUADRANT') as $qp)
                        <a href="{{ route('projects.executive', $qp['id']) }}" class="rounded-xl bg-rose-600 text-white px-3 py-1.5 text-xs font-bold shadow-xs hover:bg-rose-700 transition">
                            {{ $qp['code'] }} (Score: {{ $qp['risk_score'] }})
                        </a>
                    @empty
                        <span class="text-xs text-slate-400 italic">No projects in this quadrant</span>
                    @endforelse
                </div>
            </div>

            {{-- Q3: Low Finance/Constraint AND Low Schedule --}}
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-4 space-y-3 shadow-2xs">
                <div class="flex justify-between items-center text-xs font-bold text-emerald-900 border-b border-emerald-200 pb-1.5">
                    <span>Q3: Sound Operations</span>
                    <span class="text-[10px] text-emerald-700 font-mono">On-Track & Sound</span>
                </div>
                <div class="flex flex-wrap gap-2 pt-1">
                    @forelse (collect($allProjects)->where('quadrant', 'SOUND_QUADRANT') as $qp)
                        <a href="{{ route('projects.executive', $qp['id']) }}" class="rounded-xl bg-white border border-emerald-300 px-3 py-1.5 text-xs font-bold text-emerald-950 shadow-2xs hover:bg-emerald-100 hover:border-emerald-400 transition">
                            {{ $qp['code'] }} ({{ $qp['risk_score'] }})
                        </a>
                    @empty
                        <span class="text-xs text-slate-400 italic">No projects in this quadrant</span>
                    @endforelse
                </div>
            </div>

            {{-- Q2: High Schedule Risk, Low Finance --}}
            <div class="rounded-2xl border border-amber-200 bg-amber-50/60 p-4 space-y-3 shadow-2xs">
                <div class="flex justify-between items-center text-xs font-bold text-amber-900 border-b border-amber-200 pb-1.5">
                    <span>Q2: Schedule & Lookahead Risk</span>
                    <span class="text-[10px] text-amber-700 font-mono">High Schedule · Normal Finance</span>
                </div>
                <div class="flex flex-wrap gap-2 pt-1">
                    @forelse (collect($allProjects)->where('quadrant', 'SCHEDULE_RISK_QUADRANT') as $qp)
                        <a href="{{ route('projects.executive', $qp['id']) }}" class="rounded-xl bg-white border border-amber-300 px-3 py-1.5 text-xs font-bold text-amber-950 shadow-2xs hover:bg-amber-100 hover:border-amber-400 transition">
                            {{ $qp['code'] }} ({{ $qp['risk_score'] }})
                        </a>
                    @empty
                        <span class="text-xs text-slate-400 italic">No projects in this quadrant</span>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    {{-- Comparative Ranking Table & Sorting (Clean White Card) --}}
    <section class="rounded-2xl bg-white p-6 sm:p-7 shadow-card border border-slate-200/90 space-y-4" aria-label="Comparative Table">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-extrabold text-slate-900">Portfolio Comparative Ranking</h2>
                <p class="text-xs text-slate-500">Sorted by: <span class="font-bold uppercase text-orange-600">{{ str_replace('_', ' ', $sortField) }}</span> ({{ $sortDirection }})</p>
            </div>

            {{-- Filter Pills --}}
            <div class="flex flex-wrap items-center gap-1.5">
                @foreach (['ALL' => 'All Tiers', 'CRITICAL' => 'Critical', 'HIGH' => 'High', 'MEDIUM' => 'Medium', 'LOW' => 'Low'] as $fKey => $fLabel)
                    <button
                        wire:click="setFilter('{{ $fKey }}')"
                        class="rounded-xl px-3.5 py-1.5 text-xs font-bold transition {{ $filter === $fKey ? 'bg-safety-500 text-white shadow-safety-glow' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200' }}"
                    >
                        {{ $fLabel }}
                    </button>
                @endforeach
            </div>
        </div>

        @if (empty($projects))
            <div class="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">
                No projects found matching the current risk tier filter.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="border-b border-slate-200 bg-slate-50 text-[10px] uppercase text-slate-500 font-bold">
                        <tr>
                            <th class="p-3.5 cursor-pointer hover:bg-slate-100" wire:click="sortBy('code')">Project Code</th>
                            <th class="p-3.5">Health</th>
                            <th class="p-3.5 cursor-pointer hover:bg-slate-100" wire:click="sortBy('risk_score')">Risk Index ↕</th>
                            <th class="p-3.5 cursor-pointer hover:bg-slate-100" wire:click="sortBy('make_ready_rate')">Make-Ready % ↕</th>
                            <th class="p-3.5">Alerts (Crit/Total)</th>
                            <th class="p-3.5">Constraints</th>
                            <th class="p-3.5 cursor-pointer hover:bg-slate-100" wire:click="sortBy('budget_utilization_rate')">Budget Burn ↕</th>
                            <th class="p-3.5 text-right">Quick Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($projects as $item)
                            @php
                                $rLevel = $item['risk_level'];
                                $badgeColor = match($rLevel) {
                                    'CRITICAL' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    'HIGH' => 'bg-orange-50 text-orange-700 border-orange-200',
                                    'MEDIUM' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    default => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="p-3.5">
                                    <div class="font-bold text-slate-900">{{ $item['code'] }}</div>
                                    <div class="text-[11px] text-slate-500 truncate max-w-xs">{{ $item['name'] }}</div>
                                </td>
                                <td class="p-3.5 whitespace-nowrap">
                                    <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $item['health_status'] === 'GREEN' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($item['health_status'] === 'RED' ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-amber-50 text-amber-700 border-amber-200') }}">
                                        {{ $item['health_status'] }}
                                    </span>
                                </td>
                                <td class="p-3.5 whitespace-nowrap">
                                    <span class="inline-block rounded-full border px-2.5 py-0.5 text-[10px] font-bold {{ $badgeColor }}">
                                        {{ $item['risk_score'] }} · {{ $item['risk_level'] }}
                                    </span>
                                </td>
                                <td class="p-3.5 whitespace-nowrap font-bold {{ $item['make_ready_rate'] >= 80 ? 'text-emerald-700' : ($item['make_ready_rate'] >= 60 ? 'text-amber-700' : 'text-rose-700') }}">
                                    {{ number_format($item['make_ready_rate'], 1) }}%
                                </td>
                                <td class="p-3.5 whitespace-nowrap">
                                    <span class="font-bold {{ $item['critical_alerts_count'] > 0 ? 'text-rose-600' : 'text-slate-800' }}">{{ $item['critical_alerts_count'] }}</span>
                                    <span class="text-slate-400">/ {{ $item['alerts_count'] }}</span>
                                </td>
                                <td class="p-3.5 whitespace-nowrap">
                                    <span class="font-bold {{ $item['critical_constraints_count'] > 0 ? 'text-rose-600' : 'text-slate-800' }}">{{ $item['critical_constraints_count'] }}</span>
                                    <span class="text-slate-400">/ {{ $item['open_constraints_count'] }} open</span>
                                </td>
                                <td class="p-3.5 whitespace-nowrap">
                                    <div class="w-24 space-y-1">
                                        <div class="flex justify-between text-[11px] font-bold">
                                            <span class="{{ $item['budget_utilization_rate'] >= 90 ? 'text-rose-700' : 'text-slate-700' }}">{{ number_format($item['budget_utilization_rate'], 1) }}%</span>
                                        </div>
                                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                                            <div
                                                class="h-full rounded-full {{ $item['budget_utilization_rate'] >= 90 ? 'bg-rose-500' : ($item['budget_utilization_rate'] >= 75 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                                style="width: {{ min(100, max(0, $item['budget_utilization_rate'])) }}%"
                                            ></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3.5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('projects.executive', $item['id']) }}" class="rounded-xl bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-700 hover:bg-slate-200 border border-slate-200 transition shadow-2xs">
                                            Health
                                        </a>
                                        <a href="{{ route('projects.alerts', $item['id']) }}" class="rounded-xl bg-rose-50 px-2.5 py-1 text-[11px] font-bold text-rose-700 hover:bg-rose-100 border border-rose-200 transition shadow-2xs">
                                            Alerts
                                        </a>
                                        <a href="{{ route('projects.lookahead', $item['id']) }}" class="rounded-xl bg-sky-50 px-2.5 py-1 text-[11px] font-bold text-sky-700 hover:bg-sky-100 border border-sky-200 transition shadow-2xs">
                                            WLS
                                        </a>
                                        <a href="{{ route('projects.report', $item['id']) }}" class="rounded-xl bg-orange-50 px-2.5 py-1 text-[11px] font-bold text-orange-700 hover:bg-orange-100 border border-orange-200 transition shadow-2xs">
                                            Brief
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</section>
