<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Executive Portfolio</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
<main class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-sm font-bold uppercase tracking-wide text-cyan-700">CED-2026 · RM-08</p>
            <h1 class="mt-1 text-3xl font-black">Executive Portfolio</h1>
            <p class="mt-2 text-slate-600">Permitted projects only. Presentation ordering is stable and does not create a new ranking rule.</p>
        </div>
        <div>
            <a href="{{ route('projects.heatmap') }}" class="rounded-lg bg-slate-900 px-4 py-2.5 text-xs font-bold text-white shadow hover:bg-slate-800 transition">
                Executive Risk Heatmap Matrix →
            </a>
        </div>
    </header>

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
        <!-- Portfolio KPI Summary Bar -->
        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Portfolio KPI Overview">
            <div class="rounded-xl bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Total Projects</p>
                <p class="mt-2 text-3xl font-black text-slate-900">{{ $totalProjects }}</p>
                <p class="mt-1 text-xs text-slate-500">In permitted executive scope</p>
            </div>
            <div class="rounded-xl bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Risk Profile</p>
                <div class="mt-2 flex items-center gap-2">
                    @if ($redCount > 0)
                        <span class="inline-flex items-center rounded-md bg-red-100 px-2.5 py-1 text-xs font-black text-red-800">{{ $redCount }} RED</span>
                    @endif
                    @if ($amberCount > 0)
                        <span class="inline-flex items-center rounded-md bg-amber-100 px-2.5 py-1 text-xs font-black text-amber-800">{{ $amberCount }} AMBER</span>
                    @endif
                    @if ($greenCount > 0)
                        <span class="inline-flex items-center rounded-md bg-emerald-100 px-2.5 py-1 text-xs font-black text-emerald-800">{{ $greenCount }} GREEN</span>
                    @endif
                    @if ($redCount === 0 && $amberCount === 0 && $greenCount === 0)
                        <span class="inline-flex items-center rounded-md bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">Pending Evaluation</span>
                    @endif
                </div>
                <p class="mt-1 text-xs text-slate-500">Based on accepted health signals</p>
            </div>
            <div class="rounded-xl bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Total Budget</p>
                <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($totalBudget, 2) }}</p>
                <p class="mt-1 text-xs text-slate-500">Allocated planned capital</p>
            </div>
            <div class="rounded-xl bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Total Posted Expense</p>
                <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($totalExpense, 2) }}</p>
                <p class="mt-1 text-xs text-slate-500">Canonical posted disbursements</p>
            </div>
        </section>
    @endif

    <div x-data="{ search: '', filter: 'ALL' }" class="space-y-6">
        @if ($totalProjects > 0)
            <!-- Interactive Search & Status Filter -->
            <div class="flex flex-col gap-4 rounded-xl bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                <div class="relative flex-1">
                    <input
                        x-model="search"
                        type="text"
                        placeholder="Search projects..."
                        class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm placeholder-slate-400 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                    >
                </div>
                <div class="flex flex-wrap gap-2 text-xs font-bold">
                    <button
                        type="button"
                        @click="filter = 'ALL'"
                        :class="filter === 'ALL' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="rounded-lg px-3 py-2 transition"
                    >
                        All ({{ $totalProjects }})
                    </button>
                    <button
                        type="button"
                        @click="filter = 'red'"
                        :class="filter === 'red' ? 'bg-red-600 text-white' : 'bg-red-50 text-red-700 hover:bg-red-100'"
                        class="rounded-lg px-3 py-2 transition"
                    >
                        Critical ({{ $redCount }})
                    </button>
                    <button
                        type="button"
                        @click="filter = 'amber'"
                        :class="filter === 'amber' ? 'bg-amber-500 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100'"
                        class="rounded-lg px-3 py-2 transition"
                    >
                        Warning ({{ $amberCount }})
                    </button>
                    <button
                        type="button"
                        @click="filter = 'green'"
                        :class="filter === 'green' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'"
                        class="rounded-lg px-3 py-2 transition"
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
                class="rounded-xl bg-white p-6 shadow"
                data-project-id="{{ $card['project']->id }}"
                x-show="(filter === 'ALL' || '{{ $cardStatus }}' === filter) && ('{{ strtolower($card['project']->code) }} {{ strtolower(addslashes($card['project']->name)) }}'.includes(search.toLowerCase().trim()))"
            >
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="font-mono text-sm text-slate-500">{{ $card['project']->code }}</p>
                        <h2 class="text-xl font-black">{{ $card['project']->name }}</h2>
                        <p class="mt-1 text-sm text-slate-600">Project status: {{ $card['status'] }}</p>
                    </div>
                    @if ($health !== null)
                        <div class="rounded-lg bg-slate-100 px-4 py-3 text-right">
                            <p class="text-xs font-bold uppercase text-slate-500">Accepted health</p>
                            <p class="text-lg font-black" data-health-status="{{ $health['status'] }}">{{ $health['status'] }}</p>
                            <p class="text-xs text-slate-500">Rule version: {{ $health['rule_version'] }} · Calculated at: {{ $health['calculated_at'] }}</p>
                        </div>
                    @else
                        <div class="rounded-lg bg-amber-50 px-4 py-3 text-right text-amber-900">
                            <p class="text-xs font-bold uppercase">Health unavailable</p>
                            <p class="text-sm">Not available from accepted inputs.</p>
                        </div>
                    @endif
                </div>

                <!-- Quick Navigation Action Bar -->
                <div class="mt-4 flex flex-wrap gap-2 border-t border-slate-100 pt-3 text-xs font-bold">
                    <a class="rounded-md bg-cyan-50 px-3 py-1.5 text-cyan-800 transition hover:bg-cyan-100" href="{{ route('projects.executive', $card['project']) }}">Executive Health</a>
                    <a class="rounded-md bg-slate-100 px-3 py-1.5 text-slate-800 transition hover:bg-slate-200" href="{{ route('projects.wbs.show', $card['project']) }}">WBS Tasks</a>
                    <a class="rounded-md bg-slate-100 px-3 py-1.5 text-slate-800 transition hover:bg-slate-200" href="{{ route('projects.finance', $card['project']) }}">Finance</a>
                    @if (\Illuminate\Support\Facades\Route::has('projects.report'))
                        <a class="rounded-md bg-slate-100 px-3 py-1.5 text-slate-800 transition hover:bg-slate-200" href="{{ route('projects.report', $card['project']) }}">Executive Report</a>
                    @else
                        <a class="rounded-md bg-slate-100 px-3 py-1.5 text-slate-800 transition hover:bg-slate-200" href="{{ url('/projects/' . $card['project']->id . '/report') }}">Executive Report</a>
                    @endif
                </div>

                @if ($scheduleProgressException !== null)
                    <section class="mt-5 rounded-lg border border-red-300 bg-red-50 p-4" aria-label="Schedule/progress exception" data-schedule-progress-exception="true">
                        <p class="text-sm font-black uppercase tracking-wide text-red-950">Schedule/progress exception</p>
                        <p class="mt-1 font-bold text-red-950">Status: <span data-schedule-progress-status>{{ $scheduleProgressException['status'] }}</span></p>
                        <p class="mt-1 text-sm text-slate-700">Schedule variance: <span data-schedule-progress-value>{{ $scheduleProgressException['value'] }} {{ $scheduleProgressException['unit'] }}</span></p>
                        <p class="mt-1 text-sm text-slate-700">Reason: <span data-schedule-progress-reason>{{ $scheduleProgressException['reason'] }}</span></p>
                        <p class="mt-2 text-xs text-slate-500">Rule version: {{ $scheduleProgressException['rule_version'] }} · Calculated at: {{ $scheduleProgressException['calculated_at'] }}</p>
                        <p class="mt-1 text-xs text-slate-500">Dimension: {{ $scheduleProgressException['dimension'] }} · Project ID: {{ $scheduleProgressException['project_id'] }}</p>
                        <ul class="mt-3 space-y-2">
                            @foreach ($scheduleProgressException['source_scope'] as $sourceRef)
                                @php($sourceLink = app(\App\Services\CedExecutiveSourceLinkResolver::class)->resolve($card['project'], $sourceRef))
                                <li data-schedule-progress-source-project-id="{{ $sourceRef['project_id'] }}" data-schedule-progress-source-type="{{ $sourceRef['source_type'] }}">
                                    @if ($sourceLink)
                                        <a class="block text-xs font-bold text-cyan-700" data-schedule-progress-source-id="{{ $sourceLink['source_id'] }}" href="{{ $sourceLink['url'] }}">{{ $sourceLink['label'] }}</a>
                                    @else
                                        <span class="block text-xs text-slate-600">{{ $sourceRef['label'] }}</span>
                                    @endif
                                    <span class="block text-xs text-slate-500">Source type: {{ $sourceRef['source_type'] }} · Source ID: {{ $sourceRef['source_id'] }} · Project ID: {{ $sourceRef['project_id'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if ($financeException !== null)
                    <section class="mt-5 rounded-lg border border-amber-300 bg-amber-50 p-4" aria-label="Finance exception" data-finance-exception="true">
                        <p class="text-sm font-black uppercase tracking-wide text-amber-950">Finance exception</p>
                        <p class="mt-1 font-bold text-amber-950">Status: <span data-finance-exception-status>{{ $financeException['status'] }}</span></p>
                        <p class="mt-1 text-sm text-slate-700">Cost projection: <span data-finance-exception-value>{{ $financeException['value'] }} {{ $financeException['unit'] }}</span></p>
                        <p class="mt-1 text-sm text-slate-700">Reason: <span data-finance-exception-reason>{{ $financeException['reason'] }}</span></p>
                        <p class="mt-2 text-xs text-slate-500">Health rule version: {{ $financeException['health_rule_version'] }} · Health calculated at: {{ $financeException['health_calculated_at'] }}</p>
                        <p class="mt-1 text-xs text-slate-500">Finance rule version: {{ $financeException['finance_rule_version'] }} · Finance calculated at: {{ $financeException['finance_calculated_at'] }}</p>
                        <div class="mt-3 rounded border border-amber-200 bg-white p-3" aria-label="Canonical finance summary">
                            <p class="text-xs font-bold uppercase text-slate-500">Canonical finance summary</p>
                            <p class="mt-1 text-sm text-slate-700">Income: <span data-finance-exception-income>{{ $card['finance']['income'] }}</span> · Expense: <span data-finance-exception-expense>{{ $card['finance']['expense'] }}</span> · Net: <span data-finance-exception-net>{{ $card['finance']['net'] }}</span></p>
                            <p class="mt-1 text-sm text-slate-600" data-finance-exception-explanation>{{ $card['finance']['explanation'] }}</p>
                        </div>
                        <p class="mt-3 text-xs font-bold uppercase text-slate-500">Canonical finance source scope</p>
                        <ul class="mt-2 space-y-2">
                            @foreach ($financeException['source_scope'] as $sourceRef)
                                @php($sourceLink = app(\App\Services\CedExecutiveSourceLinkResolver::class)->resolve($card['project'], $sourceRef))
                                <li data-finance-exception-source-project-id="{{ $sourceRef['project_id'] }}" data-finance-exception-source-type="{{ $sourceRef['source_type'] }}">
                                    @if ($sourceLink)
                                        <a class="block text-xs font-bold text-cyan-700" data-finance-exception-source-id="{{ $sourceLink['source_id'] }}" href="{{ $sourceLink['url'] }}">{{ $sourceLink['label'] }}</a>
                                    @else
                                        <span class="block text-xs text-slate-600">{{ $sourceRef['label'] }}</span>
                                    @endif
                                    <span class="block text-xs text-slate-500">Source type: {{ $sourceRef['source_type'] }} · Source ID: {{ $sourceRef['source_id'] }} · Project ID: {{ $sourceRef['project_id'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if ($evidenceDataQualityException !== null)
                    <section class="mt-5 rounded-lg border border-amber-300 bg-amber-50 p-4" aria-label="Evidence/data-quality exception" data-evidence-data-quality-exception="true">
                        <p class="text-sm font-black uppercase tracking-wide text-amber-950">Evidence/data-quality exception</p>
                        <p class="mt-1 font-bold text-amber-950">Status: <span data-evidence-data-quality-status>{{ $evidenceDataQualityException['status'] }}</span></p>
                        <p class="mt-1 text-sm text-slate-700">Data freshness: <span data-evidence-data-quality-value>{{ $evidenceDataQualityException['value'] }} {{ $evidenceDataQualityException['unit'] }}</span></p>
                        <p class="mt-1 text-sm text-slate-700">Reason: <span data-evidence-data-quality-reason>{{ $evidenceDataQualityException['reason'] }}</span></p>
                        <p class="mt-2 text-xs text-slate-500">Rule version: {{ $evidenceDataQualityException['rule_version'] }} · Calculated at: {{ $evidenceDataQualityException['calculated_at'] }}</p>
                        <p class="mt-1 text-xs text-slate-500">Dimension: {{ $evidenceDataQualityException['dimension'] }} · Project ID: {{ $evidenceDataQualityException['project_id'] }}</p>
                        <ul class="mt-3 space-y-2">
                            @foreach ($evidenceDataQualityException['source_scope'] as $sourceRef)
                                <li data-evidence-data-quality-source-project-id="{{ $sourceRef['project_id'] }}" data-evidence-data-quality-source-type="{{ $sourceRef['source_type'] }}">
                                    <span class="block text-xs text-slate-600">{{ $sourceRef['label'] }}</span>
                                    <span class="block text-xs text-slate-500">Source type: {{ $sourceRef['source_type'] }} · Source ID: {{ $sourceRef['source_id'] }} · Project ID: {{ $sourceRef['project_id'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                        @if ($evidenceDataQualityException['project_id'] === (string) $card['project']->id)
                            <a class="mt-4 inline-block text-sm font-bold text-cyan-700" data-evidence-data-quality-drill-down href="{{ route('projects.executive', $card['project']) }}">Open evidence/data-quality context</a>
                        @endif
                    </section>
                @endif

                @if ($health !== null && $card['decision_required']['required'])
                    <section class="mt-5 rounded-lg border border-amber-300 bg-amber-50 p-4" aria-label="Decision required" data-decision-required="true">
                        <p class="text-sm font-black uppercase tracking-wide text-amber-950">Decision required</p>
                        <p class="mt-1 font-bold text-amber-950">Severity: {{ $card['decision_required']['severity'] }}</p>
                        <ul class="mt-3 space-y-3">
                            @foreach ($card['decision_required']['reasons'] as $dimension => $reason)
                                <li data-decision-dimension="{{ $dimension }}">
                                    <p class="font-semibold">{{ $dimension }} · {{ $reason['status'] }}</p>
                                    <p class="text-sm text-slate-700">{{ $reason['reason'] }}</p>
                                    @foreach ($reason['source_scope'] as $sourceRef)
                                        @if (is_array($sourceRef)
                                            && isset($sourceRef['source_type'], $sourceRef['source_id'], $sourceRef['project_id'], $sourceRef['label'])
                                            && is_string($sourceRef['project_id'])
                                            && $sourceRef['project_id'] === (string) $card['project']->id)
                                            @php($sourceLink = app(\App\Services\CedExecutiveSourceLinkResolver::class)->resolve($card['project'], $sourceRef))
                                            @if ($sourceLink)
                                                <a class="mt-1 block text-xs font-bold text-cyan-700" data-decision-source-id="{{ $sourceLink['source_id'] }}" data-decision-source-type="{{ $sourceLink['source_type'] }}" href="{{ $sourceLink['url'] }}">{{ $sourceLink['label'] }}</a>
                                            @else
                                                <span class="mt-1 block text-xs text-slate-500">{{ $sourceRef['label'] }}</span>
                                            @endif
                                        @endif
                                    @endforeach
                                </li>
                            @endforeach
                        </ul>
                        <a class="mt-4 inline-block text-sm font-bold text-cyan-700" href="{{ route('projects.executive', $card['project']) }}">Open decision context</a>
                    </section>
                @endif

                @if ($health !== null)
                    <section class="mt-5" aria-label="Health explanation">
                        <h3 class="font-bold">Why this status</h3>
                        <p class="mt-1 text-sm text-slate-700" data-health-explanation>{{ $health['explanation'] }}</p>
                        <ul class="mt-2 grid gap-2 md:grid-cols-2">
                            @foreach ($health['dimensions'] as $dimension => $signal)
                                <li class="rounded border border-slate-200 p-3" data-dimension="{{ $dimension }}">
                                    <p class="font-semibold">{{ $dimension }} · {{ $signal['status'] }}</p>
                                    @if (array_key_exists('value', $signal) && (is_int($signal['value']) || is_float($signal['value'])))
                                        <p class="text-sm text-slate-700">Value: {{ $signal['value'] }}@if (isset($signal['unit']) && is_string($signal['unit']) && trim($signal['unit']) !== '') {{ $signal['unit'] }}@endif</p>
                                    @endif
                                    <p class="text-sm text-slate-600">Dimension reason: {{ $signal['reason'] }}</p>
                                    <p class="mt-1 text-xs text-slate-500">Canonical reason: {{ $health['reasons'][$dimension] }}</p>
                                    @php($sourceKey = $dimension === 'DATA_FRESHNESS' ? 'freshness' : strtolower($dimension))
                                    @php($source = $health['source_scope'][$sourceKey])
                                    @foreach ($source as $sourceRef)
                                        @if ($sourceRef['project_id'] === (string) $card['project']->id)
                                            @php($sourceLink = app(\App\Services\CedExecutiveSourceLinkResolver::class)->resolve($card['project'], $sourceRef))
                                            @if ($sourceLink)
                                                <a class="mt-1 block text-xs font-bold text-cyan-700" data-source-id="{{ $sourceLink['source_id'] }}" data-source-type="{{ $sourceLink['source_type'] }}" href="{{ $sourceLink['url'] }}">{{ $sourceLink['label'] }}</a>
                                            @else
                                                <span class="mt-1 block text-xs text-slate-500">{{ $sourceRef['label'] }}</span>
                                            @endif
                                        @endif
                                    @endforeach
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                <dl class="mt-5 grid gap-3 text-sm sm:grid-cols-3">
                    <div><dt class="font-bold">Finance ({{ $card['finance']['rule_version'] }})</dt><dd>Income {{ $card['finance']['income'] }} · Expense {{ $card['finance']['expense'] }} · Net {{ $card['finance']['net'] }}</dd></div>
                    <div><dt class="font-bold">Schedule/progress sources</dt><dd>{{ count($card['schedule_source_scope']['task_ids']) }} tasks · {{ count($card['schedule_source_scope']['progress_entry_ids']) }} progress records</dd></div>
                    <div><dt class="font-bold">Traceability</dt><dd>{{ count($card['evidence_ids']) }} evidence records · {{ count($card['audit_ids']) }} audit events</dd></div>
                </dl>
                <p class="mt-3 text-sm text-slate-600">{{ $card['finance']['explanation'] }}</p>
                <div class="mt-5 flex flex-wrap gap-4 text-sm font-bold">
                    <a class="text-cyan-700" href="{{ route('projects.executive', $card['project']) }}">Open project executive detail and explanations</a>
                    <a class="text-cyan-700" href="{{ route('projects.alerts', $card['project']) }}">Alerts</a>
                    <a class="text-cyan-700" href="{{ route('projects.lookahead', $card['project']) }}">Lookahead</a>
                    <a class="text-cyan-700" href="{{ route('projects.constraints', $card['project']) }}">Constraints</a>
                    <a class="text-amber-700 font-bold" href="{{ route('projects.site', $card['project']) }}">Field Capture 📱</a>
                    <span class="text-slate-500">Dimension-specific source provenance is shown above.</span>
                </div>
            </article>
        @empty
            <section class="rounded-xl bg-white p-8 text-center shadow">
                <h2 class="text-xl font-black">No projects in permitted scope</h2>
                <p class="mt-2 text-slate-600">No project portfolio records are available for this user.</p>
            </section>
        @endforelse
    </div>
</main>
</body>
</html>
