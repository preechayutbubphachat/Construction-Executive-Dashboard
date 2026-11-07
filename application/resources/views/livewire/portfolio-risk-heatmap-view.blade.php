<section class="space-y-6" aria-labelledby="heatmap-title">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a class="text-xs font-bold text-cyan-700 hover:underline" href="{{ route('projects.portfolio') }}">← Back to Executive Portfolio</a>
            </div>
            <h1 id="heatmap-title" class="mt-1 text-2xl font-black">Executive Risk Heatmap & Comparative Portfolio</h1>
            <p class="text-sm text-slate-600">Cross-project risk index, make-ready benchmarking, and 2D operational quadrant matrix</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="rounded bg-slate-200 px-3 py-1 text-xs font-mono font-bold text-slate-700">
                CED_PORTFOLIO_RISK_HEATMAP_V1
            </span>
        </div>
    </header>

    {{-- Portfolio KPI Bar --}}
    <section class="grid gap-4 sm:grid-cols-5" aria-label="Portfolio Risk Overview">
        <div class="rounded-xl bg-white p-5 shadow border border-slate-100">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Portfolio Scope</p>
            <p class="mt-2 text-3xl font-black text-slate-900">{{ $summary['total_projects'] }}</p>
            <p class="mt-1 text-xs text-slate-500">Permitted projects</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow border border-slate-100">
            <p class="text-xs font-bold uppercase tracking-wide text-red-600">Critical Risk</p>
            <p class="mt-2 text-3xl font-black text-red-600">{{ $summary['critical_count'] }}</p>
            <p class="mt-1 text-xs text-slate-500">Require C-level intervention</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow border border-slate-100">
            <p class="text-xs font-bold uppercase tracking-wide text-orange-600">High Risk</p>
            <p class="mt-2 text-3xl font-black text-orange-600">{{ $summary['high_count'] }}</p>
            <p class="mt-1 text-xs text-slate-500">Escalated monitoring</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow border border-slate-100">
            <p class="text-xs font-bold uppercase tracking-wide text-emerald-600">Avg Make-Ready</p>
            <p class="mt-2 text-3xl font-black text-emerald-600">{{ number_format($summary['average_make_ready_rate'], 1) }}%</p>
            <p class="mt-1 text-xs text-slate-500">3-week lookahead readiness</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow border border-slate-100">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Total Portfolio Spent</p>
            <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($summary['total_expense'], 0) }}</p>
            <p class="mt-1 text-xs text-slate-500">of {{ number_format($summary['total_budget'], 0) }} planned</p>
        </div>
    </section>

    {{-- 2D Visual Risk Heatmap Matrix --}}
    <section class="rounded-xl bg-white p-6 shadow space-y-4" aria-label="2D Risk Matrix">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-base font-bold text-slate-800">2D Operational Risk Distribution Matrix</h2>
                <p class="text-xs text-slate-500">Horizontal: Schedule & Lookahead Risk → | Vertical: Financial & Constraint Risk ↑</p>
            </div>
            <div class="flex items-center gap-3 text-xs">
                <span class="flex items-center gap-1 font-bold text-red-700"><span class="h-2.5 w-2.5 rounded-full bg-red-600 inline-block"></span> Q1 Critical</span>
                <span class="flex items-center gap-1 font-bold text-amber-700"><span class="h-2.5 w-2.5 rounded-full bg-amber-500 inline-block"></span> Q2 Schedule</span>
                <span class="flex items-center gap-1 font-bold text-orange-700"><span class="h-2.5 w-2.5 rounded-full bg-orange-500 inline-block"></span> Q4 Finance</span>
                <span class="flex items-center gap-1 font-bold text-emerald-700"><span class="h-2.5 w-2.5 rounded-full bg-emerald-600 inline-block"></span> Q3 Sound</span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 rounded-xl border border-slate-200 bg-slate-50/50 p-4 min-h-[280px]">
            {{-- Q4: High Finance/Constraint, Low Schedule --}}
            <div class="rounded-lg border border-orange-200 bg-orange-50/40 p-4 space-y-2">
                <div class="flex justify-between items-center text-xs font-bold text-orange-900 border-b border-orange-200/60 pb-1">
                    <span>Q4: Finance & Constraint Risk</span>
                    <span class="text-[10px] text-orange-700 font-mono">High Finance · Normal Schedule</span>
                </div>
                <div class="flex flex-wrap gap-2 pt-1">
                    @forelse (collect($allProjects)->where('quadrant', 'FINANCE_CONSTRAINT_RISK_QUADRANT') as $qp)
                        <a href="{{ route('projects.executive', $qp['id']) }}" class="rounded bg-white border border-orange-300 px-2 py-1 text-xs font-bold text-orange-950 shadow-sm hover:bg-orange-100 transition">
                            {{ $qp['code'] }} ({{ $qp['risk_score'] }})
                        </a>
                    @empty
                        <span class="text-xs text-slate-400 italic">No projects in this quadrant</span>
                    @endforelse
                </div>
            </div>

            {{-- Q1: High Finance/Constraint AND High Schedule --}}
            <div class="rounded-lg border border-red-300 bg-red-50/50 p-4 space-y-2">
                <div class="flex justify-between items-center text-xs font-bold text-red-900 border-b border-red-200 pb-1">
                    <span>Q1: Critical Double Risk</span>
                    <span class="text-[10px] text-red-700 font-mono">High Finance · High Schedule</span>
                </div>
                <div class="flex flex-wrap gap-2 pt-1">
                    @forelse (collect($allProjects)->where('quadrant', 'CRITICAL_QUADRANT') as $qp)
                        <a href="{{ route('projects.executive', $qp['id']) }}" class="rounded bg-red-600 text-white px-2 py-1 text-xs font-bold shadow hover:bg-red-700 transition">
                            {{ $qp['code'] }} (Score: {{ $qp['risk_score'] }})
                        </a>
                    @empty
                        <span class="text-xs text-slate-400 italic">No projects in this quadrant</span>
                    @endforelse
                </div>
            </div>

            {{-- Q3: Low Finance/Constraint AND Low Schedule --}}
            <div class="rounded-lg border border-emerald-200 bg-emerald-50/40 p-4 space-y-2">
                <div class="flex justify-between items-center text-xs font-bold text-emerald-900 border-b border-emerald-200/60 pb-1">
                    <span>Q3: Sound Operations</span>
                    <span class="text-[10px] text-emerald-700 font-mono">On-Track & Sound</span>
                </div>
                <div class="flex flex-wrap gap-2 pt-1">
                    @forelse (collect($allProjects)->where('quadrant', 'SOUND_QUADRANT') as $qp)
                        <a href="{{ route('projects.executive', $qp['id']) }}" class="rounded bg-white border border-emerald-300 px-2 py-1 text-xs font-bold text-emerald-950 shadow-sm hover:bg-emerald-100 transition">
                            {{ $qp['code'] }} ({{ $qp['risk_score'] }})
                        </a>
                    @empty
                        <span class="text-xs text-slate-400 italic">No projects in this quadrant</span>
                    @endforelse
                </div>
            </div>

            {{-- Q2: High Schedule Risk, Low Finance --}}
            <div class="rounded-lg border border-amber-200 bg-amber-50/40 p-4 space-y-2">
                <div class="flex justify-between items-center text-xs font-bold text-amber-900 border-b border-amber-200/60 pb-1">
                    <span>Q2: Schedule & Lookahead Risk</span>
                    <span class="text-[10px] text-amber-700 font-mono">High Schedule · Normal Finance</span>
                </div>
                <div class="flex flex-wrap gap-2 pt-1">
                    @forelse (collect($allProjects)->where('quadrant', 'SCHEDULE_RISK_QUADRANT') as $qp)
                        <a href="{{ route('projects.executive', $qp['id']) }}" class="rounded bg-white border border-amber-300 px-2 py-1 text-xs font-bold text-amber-950 shadow-sm hover:bg-amber-100 transition">
                            {{ $qp['code'] }} ({{ $qp['risk_score'] }})
                        </a>
                    @empty
                        <span class="text-xs text-slate-400 italic">No projects in this quadrant</span>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    {{-- Comparative Ranking Table & Sorting --}}
    <section class="rounded-xl bg-white p-6 shadow space-y-4" aria-label="Comparative Table">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-bold text-slate-800">Portfolio Comparative Ranking</h2>
                <p class="text-xs text-slate-500">Sorted by: <span class="font-bold uppercase">{{ str_replace('_', ' ', $sortField) }}</span> ({{ $sortDirection }})</p>
            </div>

            {{-- Filter Pills --}}
            <div class="flex flex-wrap items-center gap-1.5">
                @foreach (['ALL' => 'All Tiers', 'CRITICAL' => 'Critical', 'HIGH' => 'High', 'MEDIUM' => 'Medium', 'LOW' => 'Low'] as $fKey => $fLabel)
                    <button
                        wire:click="setFilter('{{ $fKey }}')"
                        class="rounded-lg px-3 py-1.5 text-xs font-bold transition {{ $filter === $fKey ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
                    >
                        {{ $fLabel }}
                    </button>
                @endforeach
            </div>
        </div>

        @if (empty($projects))
            <div class="rounded-lg border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">
                No projects found matching the current risk tier filter.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="border-b border-slate-200 bg-slate-50 uppercase text-slate-600 font-semibold">
                        <tr>
                            <th class="p-3 cursor-pointer hover:bg-slate-100" wire:click="sortBy('code')">Project Code</th>
                            <th class="p-3">Health</th>
                            <th class="p-3 cursor-pointer hover:bg-slate-100" wire:click="sortBy('risk_score')">Risk Index ↕</th>
                            <th class="p-3 cursor-pointer hover:bg-slate-100" wire:click="sortBy('make_ready_rate')">Make-Ready % ↕</th>
                            <th class="p-3">Alerts (Crit/Total)</th>
                            <th class="p-3">Constraints</th>
                            <th class="p-3 cursor-pointer hover:bg-slate-100" wire:click="sortBy('budget_utilization_rate')">Budget Burn ↕</th>
                            <th class="p-3 text-right">Quick Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($projects as $item)
                            @php
                                $rLevel = $item['risk_level'];
                                $badgeColor = match($rLevel) {
                                    'CRITICAL' => 'bg-red-100 text-red-800 border-red-300',
                                    'HIGH' => 'bg-orange-100 text-orange-800 border-orange-300',
                                    'MEDIUM' => 'bg-amber-100 text-amber-800 border-amber-300',
                                    default => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/80">
                                <td class="p-3">
                                    <div class="font-bold text-slate-900">{{ $item['code'] }}</div>
                                    <div class="text-[11px] text-slate-500 truncate max-w-xs">{{ $item['name'] }}</div>
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    <span class="rounded px-2 py-0.5 text-[10px] font-black {{ $item['health_status'] === 'GREEN' ? 'bg-emerald-100 text-emerald-800' : ($item['health_status'] === 'RED' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">
                                        {{ $item['health_status'] }}
                                    </span>
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <span class="inline-block rounded border px-2 py-0.5 text-[10px] font-black {{ $badgeColor }}">
                                            {{ $item['risk_score'] }} · {{ $item['risk_level'] }}
                                        </span>
                                    </div>
                                </td>
                                <td class="p-3 whitespace-nowrap font-bold {{ $item['make_ready_rate'] >= 80 ? 'text-emerald-700' : ($item['make_ready_rate'] >= 60 ? 'text-amber-700' : 'text-red-700') }}">
                                    {{ number_format($item['make_ready_rate'], 1) }}%
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    <span class="font-bold {{ $item['critical_alerts_count'] > 0 ? 'text-red-600' : 'text-slate-700' }}">{{ $item['critical_alerts_count'] }}</span>
                                    <span class="text-slate-400">/ {{ $item['alerts_count'] }}</span>
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    <span class="font-bold {{ $item['critical_constraints_count'] > 0 ? 'text-red-600' : 'text-slate-700' }}">{{ $item['critical_constraints_count'] }}</span>
                                    <span class="text-slate-400">/ {{ $item['open_constraints_count'] }} open</span>
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    <div class="w-24 space-y-1">
                                        <div class="flex justify-between text-[11px] font-bold">
                                            <span class="{{ $item['budget_utilization_rate'] >= 90 ? 'text-red-700' : 'text-slate-700' }}">{{ number_format($item['budget_utilization_rate'], 1) }}%</span>
                                        </div>
                                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                                            <div
                                                class="h-full rounded-full {{ $item['budget_utilization_rate'] >= 90 ? 'bg-red-500' : ($item['budget_utilization_rate'] >= 75 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                                style="width: {{ min(100, max(0, $item['budget_utilization_rate'])) }}%"
                                            ></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('projects.executive', $item['id']) }}" class="rounded bg-slate-100 px-2 py-1 text-[11px] font-bold text-slate-700 hover:bg-slate-200">
                                            Health
                                        </a>
                                        <a href="{{ route('projects.alerts', $item['id']) }}" class="rounded bg-red-50 px-2 py-1 text-[11px] font-bold text-red-700 hover:bg-red-100">
                                            Alerts
                                        </a>
                                        <a href="{{ route('projects.lookahead', $item['id']) }}" class="rounded bg-cyan-50 px-2 py-1 text-[11px] font-bold text-cyan-700 hover:bg-cyan-100">
                                            WLS
                                        </a>
                                        <a href="{{ route('projects.report', $item['id']) }}" class="rounded bg-slate-900 px-2 py-1 text-[11px] font-bold text-white hover:bg-slate-800">
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
