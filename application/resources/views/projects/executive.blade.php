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

    <div class="space-y-6">
        <!-- Sub-Header & Navigation (MindInventory Style) -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-slate-200">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider rounded-md bg-orange-100 text-orange-700 border border-orange-200">
                        RM-08 · Executive Health & Multi-Signal
                    </span>
                    <span class="text-xs font-mono font-bold text-slate-500">{{ $project->code }}</span>
                </div>
                <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 flex items-center gap-2.5">
                    <span>🩺</span>
                    <span>Executive Project Health & Diagnostics</span>
                </h1>
                <p class="mt-1 text-xs text-slate-500">
                    Real-time project health signals across Schedule, Cost, Data Freshness, and Governance.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('projects.evm', $project) }}"
                   class="px-3.5 py-2 text-xs font-semibold rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 shadow-2xs transition">
                    📈 EVM S-Curve
                </a>
                <a href="{{ route('projects.lookahead', $project) }}"
                   class="px-3.5 py-2 text-xs font-semibold rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 shadow-2xs transition">
                    🔭 Lookahead
                </a>
                <a href="{{ route('projects.portfolio') }}"
                   class="px-3.5 py-2 text-xs font-semibold rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 transition">
                    ← Portfolio
                </a>
            </div>
        </div>

        @if ($renderableHealth !== null)
            <!-- Accepted Health Summary Card (Clean White Surface) -->
            <section class="rounded-2xl bg-white border border-slate-200/90 p-6 sm:p-7 shadow-card space-y-6" aria-label="Accepted health explanation and source scope">
                <div class="flex flex-wrap items-start justify-between gap-4 pb-4 border-b border-slate-100">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs font-bold text-sky-700 bg-sky-50 px-2 py-0.5 rounded-md border border-sky-200">{{ $project->code }}</span>
                            <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-slate-100 text-slate-700 border border-slate-200">Project status: {{ $project->status }}</span>
                        </div>
                        <h2 class="text-2xl font-extrabold text-slate-900 mt-2">{{ $project->name }}</h2>
                    </div>
                    <div class="rounded-2xl bg-slate-50 border border-slate-200 px-5 py-3.5 text-right shadow-2xs">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Accepted health</p>
                        <p class="text-2xl font-black {{ $renderableHealth['status'] === 'GREEN' ? 'text-emerald-600' : ($renderableHealth['status'] === 'RED' ? 'text-rose-600' : 'text-amber-600') }}" data-health-status="{{ $renderableHealth['status'] }}">{{ $renderableHealth['status'] }}</p>
                    </div>
                </div>

                <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4 shadow-2xs">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Accepted Health Explanation</p>
                    <p class="mt-1 text-sm font-medium text-slate-800" data-health-explanation>{{ $renderableHealth['explanation'] }}</p>
                    <p class="mt-2 text-xs text-slate-500 font-mono">Rule version: {{ $renderableHealth['rule_version'] }} · Calculated at: {{ $renderableHealth['calculated_at'] }}</p>
                </div>

                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Accepted health dimensions and sources</h2>
                    <ul class="mt-4 grid gap-4 md:grid-cols-2">
                        @foreach ($renderableHealth['dimensions'] as $dimension => $signal)
                            <li class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 shadow-2xs space-y-2 hover:border-slate-300 transition" data-health-source-dimension="{{ $dimension }}">
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
                                <div class="space-y-1.5 pt-2 border-t border-slate-200/70">
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

        @if ($scheduleProgressException !== null)
            <section class="rounded-2xl border border-rose-200 bg-rose-50/70 p-6 shadow-card" aria-label="Schedule/progress exception" data-schedule-progress-exception="true">
                <p class="text-xs font-black uppercase tracking-wider text-rose-700">Schedule/progress exception</p>
                <p class="mt-1 font-bold text-rose-950">Status: <span data-schedule-progress-status>{{ $scheduleProgressException['status'] }}</span></p>
                <p class="mt-1 text-sm text-rose-900">Schedule variance: <span data-schedule-progress-value>{{ $scheduleProgressException['value'] }} {{ $scheduleProgressException['unit'] }}</span></p>
                <p class="mt-1 text-sm text-rose-900">Reason: <span data-schedule-progress-reason>{{ $scheduleProgressException['reason'] }}</span></p>
                <p class="mt-2 text-xs text-rose-800 font-mono">Rule version: {{ $scheduleProgressException['rule_version'] }} · Calculated at: {{ $scheduleProgressException['calculated_at'] }}</p>
                <p class="mt-0.5 text-xs text-rose-800 font-mono">Dimension: {{ $scheduleProgressException['dimension'] }} · Project ID: {{ $scheduleProgressException['project_id'] }}</p>
                <ul class="mt-4 space-y-2">
                    @foreach ($scheduleProgressException['source_scope'] as $sourceRef)
                        @php($sourceLink = app(\App\Services\CedExecutiveSourceLinkResolver::class)->resolve($project, $sourceRef))
                        <li data-schedule-progress-source-project-id="{{ $sourceRef['project_id'] }}" data-schedule-progress-source-type="{{ $sourceRef['source_type'] }}">
                            @if ($sourceLink)
                                <a class="block font-mono text-xs font-bold text-sky-700 hover:underline" data-schedule-progress-source-id="{{ $sourceLink['source_id'] }}" href="{{ $sourceLink['url'] }}">{{ $sourceLink['label'] }}</a>
                            @else
                                <span class="block font-mono text-xs text-slate-700">{{ $sourceRef['label'] }}</span>
                            @endif
                            <span class="block text-xs text-rose-800 font-mono">Source type: {{ $sourceRef['source_type'] }} · Source ID: {{ $sourceRef['source_id'] }} · Project ID: {{ $sourceRef['project_id'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if ($financeException !== null)
            <section class="rounded-2xl border border-amber-200 bg-amber-50/70 p-6 shadow-card" aria-label="Finance exception" data-finance-exception="true">
                <p class="text-xs font-black uppercase tracking-wider text-amber-700">Finance exception</p>
                <p class="mt-1 font-bold text-amber-950">Status: <span data-finance-exception-status>{{ $financeException['status'] }}</span></p>
                <p class="mt-1 text-sm text-amber-900">Cost projection: <span data-finance-exception-value>{{ $financeException['value'] }} {{ $financeException['unit'] }}</span></p>
                <p class="mt-1 text-sm text-amber-900">Reason: <span data-finance-exception-reason>{{ $financeException['reason'] }}</span></p>
                <p class="mt-2 text-xs text-amber-800 font-mono">Health rule version: {{ $financeException['health_rule_version'] }} · Health calculated at: {{ $financeException['health_calculated_at'] }}</p>
                <p class="mt-0.5 text-xs text-amber-800 font-mono">Finance rule version: {{ $financeException['finance_rule_version'] }} · Finance calculated at: {{ $financeException['finance_calculated_at'] }}</p>
                <div class="mt-3 rounded-xl border border-amber-200 bg-white p-4 shadow-2xs" aria-label="Canonical finance summary">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Canonical finance summary</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800 tabular-nums">Income: <span data-finance-exception-income>{{ $finance['income'] }}</span> · Expense: <span data-finance-exception-expense>{{ $finance['expense'] }}</span> · Net: <span data-finance-exception-net>{{ $finance['net'] }}</span></p>
                    <p class="mt-1 text-xs text-slate-600" data-finance-exception-explanation>{{ $finance['explanation'] }}</p>
                </div>
                <p class="mt-3 text-xs font-bold uppercase tracking-wider text-amber-900">Canonical finance source scope</p>
                <ul class="mt-2 space-y-2">
                    @foreach ($financeException['source_scope'] as $sourceRef)
                        @php($sourceLink = app(\App\Services\CedExecutiveSourceLinkResolver::class)->resolve($project, $sourceRef))
                        <li data-finance-exception-source-project-id="{{ $sourceRef['project_id'] }}" data-finance-exception-source-type="{{ $sourceRef['source_type'] }}">
                            @if ($sourceLink)
                                <a class="block font-mono text-xs font-bold text-sky-700 hover:underline" data-finance-exception-source-id="{{ $sourceLink['source_id'] }}" href="{{ $sourceLink['url'] }}">{{ $sourceLink['label'] }}</a>
                            @else
                                <span class="block font-mono text-xs text-slate-700">{{ $sourceRef['label'] }}</span>
                            @endif
                            <span class="block text-xs text-amber-800 font-mono">Source type: {{ $sourceRef['source_type'] }} · Source ID: {{ $sourceRef['source_id'] }} · Project ID: {{ $sourceRef['project_id'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <!-- Executive Drill-Down Matrix (MindInventory 4-Quadrant Cards) -->
        <section class="grid gap-6 lg:grid-cols-2" aria-label="Executive drill-down">
            <!-- 1. Baseline Leaf Tasks -->
            <article class="rounded-2xl bg-white border border-slate-200/90 p-6 shadow-card space-y-4">
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
            <article class="rounded-2xl bg-white border border-slate-200/90 p-6 shadow-card space-y-4">
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
                            <span class="font-mono text-xs font-bold text-orange-600">{{ $transaction->id }}</span>
                            <p class="font-bold text-slate-900 mt-0.5">{{ $transaction->description }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $transaction->direction }} · {{ $transaction->status->value }} · <span class="font-mono font-bold text-slate-800">฿{{ number_format((float) $transaction->amount, 2) }}</span></p>
                        </li>
                    @empty
                        <li class="py-4 text-xs text-slate-400 italic text-center">No finance transactions.</li>
                    @endforelse
                </ul>
            </article>

            <!-- 3. Project Risks -->
            <article class="rounded-2xl bg-white border border-slate-200/90 p-6 shadow-card space-y-4">
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
            <article class="rounded-2xl bg-white border border-slate-200/90 p-6 shadow-card space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <span>🔒</span>
                        <span>Private evidence</span>
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
                    <input class="block w-full rounded-xl bg-white border border-slate-300 p-2 text-xs text-slate-800 focus:border-safety-500 focus:outline-none transition shadow-2xs" id="evidence" name="evidence" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp" required>
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
        <section class="rounded-2xl bg-white border border-slate-200/90 p-6 shadow-card space-y-4" aria-label="Audit events">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <span>🛡️</span>
                    <span>Recent audit events</span>
                </h2>
                <span class="text-xs font-mono font-bold text-slate-500">Total: {{ $auditEvents->count() }}</span>
            </div>
            <ul class="divide-y divide-slate-100">
                @forelse ($auditEvents as $event)
                    <li class="py-3 hover:bg-slate-50/60 transition px-2 rounded-xl" data-audit-id="{{ $event->id }}">
                        <span class="font-mono text-xs font-bold text-orange-600">{{ $event->id }}</span>
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
