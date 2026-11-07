<x-layouts.app :project="null" title="Executive Portfolio War Room — CED">
    <div class="space-y-6">
        <!-- War Room Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-obsidian-750">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider rounded-md bg-safety-500/15 text-safety-400 border border-safety-500/30">
                        CED-2026 · Multi-Project Command
                    </span>
                    <span class="text-xs font-mono text-slate-400">RM-08 COMPLIANT</span>
                </div>
                <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight text-white flex items-center gap-2.5">
                    <span>🏢</span>
                    <span>Executive Portfolio War Room</span>
                </h1>
                <p class="mt-1 text-xs text-slate-400">
                    Permitted projects only. Real-time EVM health signals, financial variances, and decision-required drill-downs.
                </p>
            </div>
            <div>
                <a href="{{ route('projects.heatmap') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-obsidian-850 hover:bg-obsidian-800 border border-obsidian-700 px-4 py-2.5 text-xs font-bold text-white shadow-industrial transition">
                    <span>🗺️</span>
                    <span>Executive Risk Heatmap Matrix →</span>
                </a>
            </div>
        </div>

        @php
            $totalProjects = count($cards);
            $redCount = 0;
            $amberCount = 0;
            $greenCount = 0;
            $totalBudget = 0.0;
            $totalExpense = 0.0;

            foreach ($cards as $cardItem) {
                $p = $cardItem['project'] ?? null;
                if ($p && isset($p->budget_amount)) {
                    $totalBudget += (float) $p->budget_amount;
                }
                $fin = $cardItem['finance'] ?? null;
                if ($fin && isset($fin['expense'])) {
                    $totalExpense += (float) $fin['expense'];
                }
                $h = $cardItem['health'] ?? null;
                $hStatus = is_array($h) && isset($h['status']) ? $h['status'] : 'UNKNOWN';
                if ($hStatus === 'RED') {
                    $redCount++;
                } elseif ($hStatus === 'AMBER') {
                    $amberCount++;
                } elseif ($hStatus === 'GREEN') {
                    $greenCount++;
                }
            }
        @endphp

        @if ($totalProjects > 0)
            <!-- Portfolio Top KPI Bar (MindInventory Style) -->
            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Portfolio KPI Overview">
                <x-ced.kpi-card
                    title="Total Projects"
                    value="{{ $totalProjects }}"
                    subtitle="In permitted executive scope"
                    badge="Active Sites"
                    badgeType="safety"
                />

                <div class="rounded-2xl bg-obsidian-850/90 border border-obsidian-700/80 p-5 shadow-industrial flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Risk Profile</span>
                            <span class="text-xs font-mono text-slate-500">Live Health</span>
                        </div>
                        <div class="mt-3 flex flex-wrap items-center gap-1.5">
                            @if ($redCount > 0)
                                <span class="px-2 py-0.5 rounded-full text-xs font-extrabold bg-ncr-500/15 text-ncr-400 border border-ncr-500/30">{{ $redCount }} RED</span>
                            @endif
                            @if ($amberCount > 0)
                                <span class="px-2 py-0.5 rounded-full text-xs font-extrabold bg-hazard-500/15 text-hazard-400 border border-hazard-500/30">{{ $amberCount }} AMBER</span>
                            @endif
                            @if ($greenCount > 0)
                                <span class="px-2 py-0.5 rounded-full text-xs font-extrabold bg-qc-500/15 text-qc-400 border border-qc-500/30">{{ $greenCount }} GREEN</span>
                            @endif
                            @if ($redCount === 0 && $amberCount === 0 && $greenCount === 0)
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-obsidian-800 text-slate-400 border border-obsidian-700">Pending Signals</span>
                            @endif
                        </div>
                    </div>
                    <div class="mt-2 text-xs text-slate-400 border-t border-obsidian-750 pt-2">
                        Accepted health dimension rules
                    </div>
                </div>

                <x-ced.kpi-card
                    title="Total Budget"
                    value="฿{{ number_format($totalBudget, 2) }}"
                    subtitle="Allocated planned capital"
                    badge="CAPEX"
                    badgeType="blueprint"
                />

                <x-ced.kpi-card
                    title="Posted Expenses"
                    value="฿{{ number_format($totalExpense, 2) }}"
                    subtitle="Canonical posted disbursements"
                    badge="Actual Cost (AC)"
                    badgeType="hazard"
                />
            </section>
        @endif

        <div x-data="{ search: '', filter: 'ALL' }" class="space-y-6">
            @if ($totalProjects > 0)
                <!-- Interactive Search & Status Filter -->
                <div class="flex flex-col gap-4 rounded-2xl bg-obsidian-850 border border-obsidian-750 p-4 shadow-industrial sm:flex-row sm:items-center sm:justify-between">
                    <div class="relative flex-1">
                        <input
                            x-model="search"
                            type="text"
                            placeholder="Search projects..."
                            class="w-full rounded-xl bg-obsidian-900 border border-obsidian-700 px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:border-safety-500 focus:outline-none transition"
                        >
                    </div>
                    <div class="flex flex-wrap gap-2 text-xs font-bold">
                        <button
                            type="button"
                            @click="filter = 'ALL'"
                            :class="filter === 'ALL' ? 'bg-safety-500 text-white shadow-safety-glow' : 'bg-obsidian-900 text-slate-300 hover:bg-obsidian-800 border border-obsidian-700'"
                            class="rounded-xl px-3.5 py-2 transition"
                        >
                            All ({{ $totalProjects }})
                        </button>
                        <button
                            type="button"
                            @click="filter = 'red'"
                            :class="filter === 'red' ? 'bg-ncr-600 text-white' : 'bg-obsidian-900 text-ncr-400 hover:bg-ncr-500/10 border border-obsidian-700'"
                            class="rounded-xl px-3.5 py-2 transition"
                        >
                            Critical ({{ $redCount }})
                        </button>
                        <button
                            type="button"
                            @click="filter = 'amber'"
                            :class="filter === 'amber' ? 'bg-hazard-500 text-obsidian-950 font-black' : 'bg-obsidian-900 text-hazard-400 hover:bg-hazard-500/10 border border-obsidian-700'"
                            class="rounded-xl px-3.5 py-2 transition"
                        >
                            Warning ({{ $amberCount }})
                        </button>
                        <button
                            type="button"
                            @click="filter = 'green'"
                            :class="filter === 'green' ? 'bg-qc-600 text-white shadow-qc-glow' : 'bg-obsidian-900 text-qc-400 hover:bg-qc-500/10 border border-obsidian-700'"
                            class="rounded-xl px-3.5 py-2 transition"
                        >
                            Healthy ({{ $greenCount }})
                        </button>
                    </div>
                </div>
            @endif

            @forelse ($cards as $card)
                @php
                    $health = $card['health'] ?? null;
                    $scheduleProgressException = $card['schedule_progress_exception'] ?? null;
                    $financeException = $card['finance_exception'] ?? null;
                    $evidenceDataQualityException = $card['evidence_data_quality_exception'] ?? null;
                    $healthIsRenderable = is_array($health)
                        && isset($health['project_id'], $health['rule_version'], $health['calculated_at'], $health['status'], $health['dimensions'], $health['reasons'], $health['source_scope'], $health['explanation'])
                        && is_string($health['project_id'])
                        && $health['project_id'] === (string) $card['project']->id
                        && is_string($health['rule_version'])
                        && trim($health['rule_version']) !== ''
                        && is_string($health['calculated_at'])
                        && trim($health['calculated_at']) !== ''
                        && is_string($health['status'])
                        && in_array($health['status'], ['GREEN', 'UNKNOWN', 'AMBER', 'RED'], true)
                        && is_array($health['dimensions'])
                        && $health['dimensions'] !== []
                        && is_array($health['reasons'])
                        && is_array($health['source_scope'])
                        && is_string($health['explanation'])
                        && trim($health['explanation']) !== '';

                    if ($healthIsRenderable) {
                        foreach ($health['dimensions'] as $dimension => $signal) {
                            $sourceKey = is_string($dimension) && $dimension === 'DATA_FRESHNESS' ? 'freshness' : strtolower((string) $dimension);
                            $sources = $health['source_scope'][$sourceKey] ?? null;

                            if (! is_string($dimension)
                                || ! is_array($signal)
                                || ! isset($signal['status'], $signal['reason'])
                                || ! is_string($signal['status'])
                                || ! in_array($signal['status'], ['GREEN', 'UNKNOWN', 'AMBER', 'RED'], true)
                                || ! is_string($signal['reason'])
                                || trim($signal['reason']) === ''
                                || ! array_key_exists($dimension, $health['reasons'])
                                || ! is_string($health['reasons'][$dimension])
                                || $health['reasons'][$dimension] !== $signal['reason']
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

                    $health = $healthIsRenderable ? $health : null;
                    $cardStatus = $health !== null ? strtolower($health['status']) : 'unknown';
                @endphp
                <article
                    class="rounded-2xl bg-obsidian-850 border border-obsidian-750 p-6 shadow-industrial hover:border-obsidian-600 transition"
                    data-project-id="{{ $card['project']->id }}"
                    x-show="(filter === 'ALL' || '{{ $cardStatus }}' === filter) && ('{{ strtolower($card['project']->code) }} {{ strtolower(addslashes($card['project']->name)) }}'.includes(search.toLowerCase().trim()))"
                >
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs font-bold text-safety-400 bg-safety-500/10 px-2 py-0.5 rounded border border-safety-500/20" data-project-code="{{ $card['project']->code }}">{{ $card['project']->code }}</span>
                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded bg-obsidian-800 text-slate-300 border border-obsidian-700" data-project-status="{{ $card['status'] }}">Project status: {{ $card['status'] }}</span>
                            </div>
                            <h2 class="text-xl font-bold text-white mt-1.5">{{ $card['project']->name }}</h2>
                        </div>
                        @if ($health !== null)
                            <div class="rounded-xl bg-obsidian-900 border border-obsidian-750 px-4 py-3 text-right">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Accepted health</p>
                                <p class="text-lg font-black {{ $health['status'] === 'GREEN' ? 'text-qc-400' : ($health['status'] === 'RED' ? 'text-ncr-400' : 'text-hazard-400') }}" data-health-status="{{ $health['status'] }}">{{ $health['status'] }}</p>
                                <p class="text-[11px] text-slate-500 font-mono">Rule version: {{ $health['rule_version'] }} · Calculated at: {{ $health['calculated_at'] }}</p>
                            </div>
                        @else
                            <div class="rounded-xl bg-hazard-500/10 border border-hazard-500/20 px-4 py-3 text-right text-hazard-400">
                                <p class="text-[10px] font-bold uppercase tracking-wider">Health unavailable</p>
                                <p class="text-xs">Not available from accepted inputs.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Quick Navigation Action Bar -->
                    <div class="mt-4 flex flex-wrap gap-2 border-t border-obsidian-750 pt-3 text-xs font-bold">
                        <a class="rounded-xl bg-blueprint-500/15 border border-blueprint-500/30 px-3 py-1.5 text-blueprint-400 transition hover:bg-blueprint-500/25" href="{{ route('projects.executive', $card['project']) }}">Executive Health</a>
                        <a class="rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-1.5 text-slate-300 transition hover:bg-obsidian-800" href="{{ route('projects.wbs.show', $card['project']) }}">WBS Tasks</a>
                        <a class="rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-1.5 text-slate-300 transition hover:bg-obsidian-800" href="{{ route('projects.finance', $card['project']) }}">Finance</a>
                        @if (\Illuminate\Support\Facades\Route::has('projects.report'))
                            <a class="rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-1.5 text-slate-300 transition hover:bg-obsidian-800" href="{{ route('projects.report', $card['project']) }}">Executive Report</a>
                        @else
                            <a class="rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-1.5 text-slate-300 transition hover:bg-obsidian-800" href="{{ url('/projects/' . $card['project']->id . '/report') }}">Executive Report</a>
                        @endif
                    </div>

                    @if ($scheduleProgressException !== null)
                        <section class="mt-5 rounded-xl border border-ncr-500/30 bg-ncr-500/10 p-4" aria-label="Schedule/progress exception" data-schedule-progress-exception="true">
                            <p class="text-xs font-black uppercase tracking-wider text-ncr-400">Schedule/progress exception</p>
                            <p class="mt-1 font-bold text-ncr-300">Status: <span data-schedule-progress-status>{{ $scheduleProgressException['status'] }}</span></p>
                            <p class="mt-1 text-xs text-slate-300">Schedule variance: <span data-schedule-progress-value>{{ $scheduleProgressException['value'] }} {{ $scheduleProgressException['unit'] }}</span></p>
                            <p class="mt-1 text-xs text-slate-300">Reason: <span data-schedule-progress-reason>{{ $scheduleProgressException['reason'] }}</span></p>
                            <p class="mt-2 text-[11px] text-slate-400 font-mono">Rule version: {{ $scheduleProgressException['rule_version'] }} · Calculated at: {{ $scheduleProgressException['calculated_at'] }}</p>
                            <p class="mt-0.5 text-[11px] text-slate-400 font-mono">Dimension: {{ $scheduleProgressException['dimension'] }} · Project ID: {{ $scheduleProgressException['project_id'] }}</p>
                            <ul class="mt-3 space-y-1.5">
                                @foreach ($scheduleProgressException['source_scope'] as $sourceRef)
                                    @php($sourceLink = app(\App\Services\CedExecutiveSourceLinkResolver::class)->resolve($card['project'], $sourceRef))
                                    <li data-schedule-progress-source-project-id="{{ $sourceRef['project_id'] }}" data-schedule-progress-source-type="{{ $sourceRef['source_type'] }}">
                                        @if ($sourceLink)
                                            <a class="block text-xs font-bold text-blueprint-400 hover:underline" data-schedule-progress-source-id="{{ $sourceLink['source_id'] }}" href="{{ $sourceLink['url'] }}">{{ $sourceLink['label'] }}</a>
                                        @else
                                            <span class="block text-xs text-slate-400">{{ $sourceRef['label'] }}</span>
                                        @endif
                                        <span class="block text-[11px] text-slate-500 font-mono">Source type: {{ $sourceRef['source_type'] }} · Source ID: {{ $sourceRef['source_id'] }} · Project ID: {{ $sourceRef['project_id'] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endif

                    @if ($financeException !== null)
                        <section class="mt-5 rounded-xl border border-hazard-500/30 bg-hazard-500/10 p-4" aria-label="Finance exception" data-finance-exception="true">
                            <p class="text-xs font-black uppercase tracking-wider text-hazard-400">Finance exception</p>
                            <p class="mt-1 font-bold text-hazard-300">Status: <span data-finance-exception-status>{{ $financeException['status'] }}</span></p>
                            <p class="mt-1 text-xs text-slate-300">Cost projection: <span data-finance-exception-value>{{ $financeException['value'] }} {{ $financeException['unit'] }}</span></p>
                            <p class="mt-1 text-xs text-slate-300">Reason: <span data-finance-exception-reason>{{ $financeException['reason'] }}</span></p>
                            <p class="mt-2 text-[11px] text-slate-400 font-mono">Health rule version: {{ $financeException['health_rule_version'] }} · Health calculated at: {{ $financeException['health_calculated_at'] }}</p>
                            <p class="mt-0.5 text-[11px] text-slate-400 font-mono">Finance rule version: {{ $financeException['finance_rule_version'] }} · Finance calculated at: {{ $financeException['finance_calculated_at'] }}</p>
                            <div class="mt-3 rounded-xl border border-obsidian-750 bg-obsidian-900/80 p-3" aria-label="Canonical finance summary">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Canonical finance summary</p>
                                <p class="mt-1 text-xs text-slate-300 tabular-nums">Income: <span data-finance-exception-income>{{ $card['finance']['income'] }}</span> · Expense: <span data-finance-exception-expense>{{ $card['finance']['expense'] }}</span> · Net: <span data-finance-exception-net>{{ $card['finance']['net'] }}</span></p>
                                <p class="mt-1 text-xs text-slate-400" data-finance-exception-explanation>{{ $card['finance']['explanation'] }}</p>
                            </div>
                            <p class="mt-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Canonical finance source scope</p>
                            <ul class="mt-2 space-y-1.5">
                                @foreach ($financeException['source_scope'] as $sourceRef)
                                    @php($sourceLink = app(\App\Services\CedExecutiveSourceLinkResolver::class)->resolve($card['project'], $sourceRef))
                                    <li data-finance-exception-source-project-id="{{ $sourceRef['project_id'] }}" data-finance-exception-source-type="{{ $sourceRef['source_type'] }}">
                                        @if ($sourceLink)
                                            <a class="block text-xs font-bold text-blueprint-400 hover:underline" data-finance-exception-source-id="{{ $sourceLink['source_id'] }}" href="{{ $sourceLink['url'] }}">{{ $sourceLink['label'] }}</a>
                                        @else
                                            <span class="block text-xs text-slate-400">{{ $sourceRef['label'] }}</span>
                                        @endif
                                        <span class="block text-[11px] text-slate-500 font-mono">Source type: {{ $sourceRef['source_type'] }} · Source ID: {{ $sourceRef['source_id'] }} · Project ID: {{ $sourceRef['project_id'] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endif

                    @if ($evidenceDataQualityException !== null)
                        <section class="mt-5 rounded-xl border border-hazard-500/30 bg-hazard-500/10 p-4" aria-label="Evidence/data-quality exception" data-evidence-data-quality-exception="true">
                            <p class="text-xs font-black uppercase tracking-wider text-hazard-400">Evidence/data-quality exception</p>
                            <p class="mt-1 font-bold text-hazard-300">Status: <span data-evidence-data-quality-status>{{ $evidenceDataQualityException['status'] }}</span></p>
                            <p class="mt-1 text-xs text-slate-300">Data freshness: <span data-evidence-data-quality-value>{{ $evidenceDataQualityException['value'] }} {{ $evidenceDataQualityException['unit'] }}</span></p>
                            <p class="mt-1 text-xs text-slate-300">Reason: <span data-evidence-data-quality-reason>{{ $evidenceDataQualityException['reason'] }}</span></p>
                            <p class="mt-2 text-[11px] text-slate-400 font-mono">Rule version: {{ $evidenceDataQualityException['rule_version'] }} · Calculated at: {{ $evidenceDataQualityException['calculated_at'] }}</p>
                            <p class="mt-0.5 text-[11px] text-slate-400 font-mono">Dimension: {{ $evidenceDataQualityException['dimension'] }} · Project ID: {{ $evidenceDataQualityException['project_id'] }}</p>
                            <ul class="mt-3 space-y-1.5">
                                @foreach ($evidenceDataQualityException['source_scope'] as $sourceRef)
                                    <li data-evidence-data-quality-source-project-id="{{ $sourceRef['project_id'] }}" data-evidence-data-quality-source-type="{{ $sourceRef['source_type'] }}">
                                        <span class="block text-xs text-slate-300">{{ $sourceRef['label'] }}</span>
                                        <span class="block text-[11px] text-slate-500 font-mono">Source type: {{ $sourceRef['source_type'] }} · Source ID: {{ $sourceRef['source_id'] }} · Project ID: {{ $sourceRef['project_id'] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                            @if ($evidenceDataQualityException['project_id'] === (string) $card['project']->id)
                                <a class="mt-3 inline-block text-xs font-bold text-blueprint-400 hover:underline" data-evidence-data-quality-drill-down href="{{ route('projects.executive', $card['project']) }}">Open evidence/data-quality context →</a>
                            @endif
                        </section>
                    @endif

                    @if ($health !== null && $card['decision_required']['required'])
                        <section class="mt-5 rounded-xl border border-hazard-500/30 bg-hazard-500/10 p-4" aria-label="Decision required" data-decision-required="true">
                            <p class="text-xs font-black uppercase tracking-wider text-hazard-400">Decision required</p>
                            <p class="mt-1 font-bold text-hazard-300">Severity: {{ $card['decision_required']['severity'] }}</p>
                            <ul class="mt-3 space-y-3">
                                @foreach ($card['decision_required']['reasons'] as $dimension => $reason)
                                    <li data-decision-dimension="{{ $dimension }}" class="rounded-lg bg-obsidian-900/60 p-3 border border-obsidian-750">
                                        <p class="font-bold text-white text-xs">{{ $dimension }} · <span class="text-hazard-400">{{ $reason['status'] }}</span></p>
                                        <p class="text-xs text-slate-300 mt-0.5">{{ $reason['reason'] }}</p>
                                        @foreach ($reason['source_scope'] as $sourceRef)
                                            @if (is_array($sourceRef)
                                                && isset($sourceRef['source_type'], $sourceRef['source_id'], $sourceRef['project_id'], $sourceRef['label'])
                                                && is_string($sourceRef['project_id'])
                                                && $sourceRef['project_id'] === (string) $card['project']->id)
                                                @php($sourceLink = app(\App\Services\CedExecutiveSourceLinkResolver::class)->resolve($card['project'], $sourceRef))
                                                @if ($sourceLink)
                                                    <a class="mt-1.5 block text-xs font-bold text-blueprint-400 hover:underline" data-decision-source-id="{{ $sourceLink['source_id'] }}" data-decision-source-type="{{ $sourceLink['source_type'] }}" href="{{ $sourceLink['url'] }}">{{ $sourceLink['label'] }}</a>
                                                @else
                                                    <span class="mt-1 block text-xs text-slate-400">{{ $sourceRef['label'] }}</span>
                                                @endif
                                            @endif
                                        @endforeach
                                    </li>
                                @endforeach
                            </ul>
                            <a class="mt-3 inline-block text-xs font-bold text-blueprint-400 hover:underline" href="{{ route('projects.executive', $card['project']) }}">Open decision context →</a>
                        </section>
                    @endif

                    @if ($health !== null)
                        <section class="mt-5 border-t border-obsidian-750 pt-4" aria-label="Health explanation">
                            <h3 class="font-bold text-white text-sm">Why this status</h3>
                            <p class="mt-1 text-xs text-slate-300" data-health-explanation>{{ $health['explanation'] }}</p>
                            <ul class="mt-3 grid gap-3 md:grid-cols-2">
                                @foreach ($health['dimensions'] as $dimension => $signal)
                                    <li class="rounded-xl border border-obsidian-750 bg-obsidian-900/70 p-3" data-dimension="{{ $dimension }}">
                                        <div class="flex items-center justify-between">
                                            <p class="font-bold text-xs text-white">{{ $dimension }} · {{ $signal['status'] }}</p>
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $signal['status'] === 'GREEN' ? 'bg-qc-500/15 text-qc-400 border border-qc-500/20' : ($signal['status'] === 'RED' ? 'bg-ncr-500/15 text-ncr-400 border border-ncr-500/20' : 'bg-hazard-500/15 text-hazard-400 border border-hazard-500/20') }}">{{ $signal['status'] }}</span>
                                        </div>
                                        @if (array_key_exists('value', $signal) && (is_int($signal['value']) || is_float($signal['value'])))
                                            <p class="text-xs text-slate-300 mt-1 font-mono">Value: {{ $signal['value'] }}@if (isset($signal['unit']) && is_string($signal['unit']) && trim($signal['unit']) !== '') {{ $signal['unit'] }}@endif</p>
                                        @endif
                                        <p class="text-xs text-slate-400 mt-1">Dimension reason: {{ $signal['reason'] }}</p>
                                        <p class="mt-1 text-[11px] text-slate-500">Canonical reason: {{ $health['reasons'][$dimension] }}</p>
                                        @php($sourceKey = $dimension === 'DATA_FRESHNESS' ? 'freshness' : strtolower($dimension))
                                        @php($source = $health['source_scope'][$sourceKey])
                                        @foreach ($source as $sourceRef)
                                            @if ($sourceRef['project_id'] === (string) $card['project']->id)
                                                @php($sourceLink = app(\App\Services\CedExecutiveSourceLinkResolver::class)->resolve($card['project'], $sourceRef))
                                                @if ($sourceLink)
                                                    <a class="mt-1.5 block text-xs font-bold text-blueprint-400 hover:underline" data-source-id="{{ $sourceLink['source_id'] }}" data-source-type="{{ $sourceLink['source_type'] }}" href="{{ $sourceLink['url'] }}">{{ $sourceLink['label'] }}</a>
                                                @else
                                                    <span class="mt-1 block text-xs text-slate-500">{{ $sourceRef['label'] }}</span>
                                                @endif
                                                <span class="block text-xs text-slate-500">Source type: {{ $sourceRef['source_type'] }} · Source ID: {{ $sourceRef['source_id'] }} · Project ID: {{ $sourceRef['project_id'] }}</span>
                                            @endif
                                        @endforeach
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endif

                    <dl class="mt-5 grid gap-3 text-xs sm:grid-cols-3 bg-obsidian-900/60 rounded-xl p-3 border border-obsidian-750">
                        <div><dt class="font-bold text-slate-400">Finance ({{ $card['finance']['rule_version'] }})</dt><dd class="text-slate-200 mt-0.5 font-mono">Income <span data-finance-income>{{ $card['finance']['income'] }}</span> · Expense <span data-finance-expense>{{ $card['finance']['expense'] }}</span> · Net <span data-finance-net>{{ $card['finance']['net'] }}</span></dd></div>
                        <div><dt class="font-bold text-slate-400">Schedule/progress sources</dt><dd class="text-slate-200 mt-0.5">{{ count($card['schedule_source_scope']['task_ids']) }} tasks · {{ count($card['schedule_source_scope']['progress_entry_ids']) }} progress</dd></div>
                        <div><dt class="font-bold text-slate-400">Traceability</dt><dd class="text-slate-200 mt-0.5">{{ count($card['evidence_ids']) }} evidence · {{ count($card['audit_ids']) }} audit events</dd></div>
                    </dl>
                    <p class="mt-3 text-xs text-slate-400" data-finance-explanation>{{ $card['finance']['explanation'] }}</p>
                    
                    <!-- Comprehensive Action Matrix Links -->
                    <div class="mt-5 flex flex-wrap gap-2 text-xs font-bold border-t border-obsidian-750 pt-4">
                        <a class="rounded-xl bg-blueprint-500/15 border border-blueprint-500/30 px-3 py-1.5 text-blueprint-400 hover:bg-blueprint-500/25 transition" href="{{ route('projects.executive', $card['project']) }}">Executive Health</a>
                        <a class="rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-1.5 text-slate-300 hover:bg-obsidian-800 transition" href="{{ route('projects.alerts', $card['project']) }}">Alerts</a>
                        <a class="rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-1.5 text-slate-300 hover:bg-obsidian-800 transition" href="{{ route('projects.lookahead', $card['project']) }}">Lookahead</a>
                        <a class="rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-1.5 text-slate-300 hover:bg-obsidian-800 transition" href="{{ route('projects.constraints', $card['project']) }}">Constraints</a>
                        <a class="rounded-xl bg-safety-500/15 border border-safety-500/30 px-3 py-1.5 text-safety-400 hover:bg-safety-500/25 transition" href="{{ route('projects.site', $card['project']) }}">Field Capture 📱</a>
                        <a class="rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-1.5 text-indigo-400 hover:bg-obsidian-800 transition" href="{{ route('projects.3d-twin', $card['project']) }}">3D Twin 🌐</a>
                        <a class="rounded-xl bg-qc-500/15 border border-qc-500/30 px-3 py-1.5 text-qc-400 hover:bg-qc-500/25 transition" href="{{ route('projects.evm', $card['project']) }}">EVM S-Curve 📈</a>
                        <a class="rounded-xl bg-qc-500/15 border border-qc-500/30 px-3 py-1.5 text-qc-400 hover:bg-qc-500/25 transition" href="{{ route('projects.ipc.index', $card['project']) }}">💰 IPC & Payment</a>
                        <a class="rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-1.5 text-violet-400 hover:bg-obsidian-800 transition" href="{{ route('projects.vo.index', $card['project']) }}">📑 Variation Orders</a>
                        <a class="rounded-xl bg-ncr-500/15 border border-ncr-500/30 px-3 py-1.5 text-ncr-400 hover:bg-ncr-500/25 transition" href="{{ route('projects.ncr.index', $card['project']) }}">🛡️ QC & NCR</a>
                        <a class="rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-1.5 text-amber-400 hover:bg-obsidian-800 transition" href="{{ route('projects.procurement.index', $card['project']) }}">🧱 Procurement</a>
                        <a class="rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-1.5 text-sky-400 hover:bg-obsidian-800 transition" href="{{ route('projects.resources.index', $card['project']) }}">👷 Workforce & Machinery</a>
                        <a class="rounded-xl bg-qc-500/15 border border-qc-500/30 px-3 py-1.5 text-qc-400 hover:bg-qc-500/25 transition" href="{{ route('projects.hse.index', $card['project']) }}">🦺 HSE & Permits</a>
                        <a class="rounded-xl bg-blueprint-500/15 border border-blueprint-500/30 px-3 py-1.5 text-blueprint-400 hover:bg-blueprint-500/25 transition" href="{{ route('projects.engineering.index', $card['project']) }}">📐 RFI & Submittals</a>
                        <a class="rounded-xl bg-safety-500/20 border border-safety-500/40 px-3 py-1.5 text-safety-400 hover:bg-safety-500/30 transition" href="{{ route('projects.handover.index', $card['project']) }}">🏁 Handover & Closeout</a>
                    </div>
                </article>
            @empty
                <section class="rounded-2xl bg-obsidian-850 border border-obsidian-750 p-12 text-center shadow-industrial">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-obsidian-900 border border-obsidian-700 text-2xl text-slate-400 mb-3">
                        📁
                    </div>
                    <h2 class="text-lg font-bold text-white">No projects in permitted scope</h2>
                    <p class="mt-1 text-xs text-slate-400">No project portfolio records are available for this user.</p>
                </section>
            @endforelse
        </div>
    </div>
</x-layouts.app>
