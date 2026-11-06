<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $project->code }} · Executive Health</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
<main class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
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

    @if ($renderableHealth !== null)
        <section class="rounded-xl bg-white p-6 shadow" aria-label="Accepted health explanation and source scope">
            <p class="font-mono text-sm text-slate-500">{{ $project->code }}</p>
            <h1 class="text-2xl font-black">{{ $project->name }}</h1>
            <p class="mt-1 text-sm text-slate-600">Project status: {{ $project->status }}</p>
            <div class="mt-5 rounded-lg bg-slate-100 p-4">
                <p class="text-xs font-bold uppercase text-slate-500">Accepted health</p>
                <p class="text-xl font-black" data-health-status="{{ $renderableHealth['status'] }}">{{ $renderableHealth['status'] }}</p>
                <p class="mt-1 text-sm text-slate-700" data-health-explanation>{{ $renderableHealth['explanation'] }}</p>
                <p class="mt-2 text-xs text-slate-500">Rule version: {{ $renderableHealth['rule_version'] }} · Calculated at: {{ $renderableHealth['calculated_at'] }}</p>
            </div>
            <h2 class="mt-6 text-lg font-black">Accepted health dimensions and sources</h2>
            <ul class="mt-4 grid gap-3 md:grid-cols-2">
                @foreach ($renderableHealth['dimensions'] as $dimension => $signal)
                    <li class="rounded border border-slate-200 p-3" data-health-source-dimension="{{ $dimension }}">
                        <p class="font-semibold">{{ $dimension }} · {{ $signal['status'] }}</p>
                        @if (array_key_exists('value', $signal) && (is_int($signal['value']) || is_float($signal['value'])))
                            <p class="text-sm text-slate-700">Value: {{ $signal['value'] }}@if (isset($signal['unit']) && is_string($signal['unit']) && trim($signal['unit']) !== '') {{ $signal['unit'] }}@endif</p>
                        @endif
                        <p class="mt-1 text-sm text-slate-600">Dimension reason: {{ $signal['reason'] }}</p>
                        <p class="mt-1 text-xs text-slate-500">Canonical reason: {{ $renderableHealth['reasons'][$dimension] }}</p>
                        @php($sourceKey = $dimension === 'DATA_FRESHNESS' ? 'freshness' : strtolower($dimension))
                        @foreach ($renderableHealth['source_scope'][$sourceKey] as $sourceRef)
                            @if ($sourceRef['project_id'] === (string) $project->id)
                                @php($sourceLink = app(\App\Services\CedExecutiveSourceLinkResolver::class)->resolve($project, $sourceRef))
                                @if ($sourceLink)
                                    <a class="mt-1 block font-mono text-xs text-cyan-700" data-health-source-id="{{ $sourceLink['source_id'] }}" data-health-source-type="{{ $sourceLink['source_type'] }}" href="{{ $sourceLink['url'] }}">{{ $sourceLink['label'] }}</a>
                                @else
                                    <span class="mt-1 block font-mono text-xs text-slate-500">{{ $sourceRef['label'] }}</span>
                                @endif
                            @endif
                        @endforeach
                    </li>
                @endforeach
            </ul>
        </section>
    @else
        <section class="rounded-xl bg-white p-6 shadow" aria-label="Accepted health unavailable">
            <p class="font-mono text-sm text-slate-500">{{ $project->code }}</p>
            <h1 class="text-2xl font-black">{{ $project->name }}</h1>
            <p class="mt-1 text-sm text-slate-600">Project status: {{ $project->status }}</p>
            <div class="mt-5 rounded-lg bg-amber-50 p-4 text-amber-900">
                <p class="text-xs font-bold uppercase">Health unavailable</p>
                <p class="text-sm">Not available from accepted inputs.</p>
            </div>
        </section>
    @endif

    @if ($scheduleProgressException !== null)
        <section class="rounded-xl border border-red-300 bg-red-50 p-6 shadow" aria-label="Schedule/progress exception" data-schedule-progress-exception="true">
            <p class="text-sm font-black uppercase tracking-wide text-red-950">Schedule/progress exception</p>
            <p class="mt-1 font-bold text-red-950">Status: <span data-schedule-progress-status>{{ $scheduleProgressException['status'] }}</span></p>
            <p class="mt-1 text-sm text-slate-700">Schedule variance: <span data-schedule-progress-value>{{ $scheduleProgressException['value'] }} {{ $scheduleProgressException['unit'] }}</span></p>
            <p class="mt-1 text-sm text-slate-700">Reason: <span data-schedule-progress-reason>{{ $scheduleProgressException['reason'] }}</span></p>
            <p class="mt-2 text-xs text-slate-500">Rule version: {{ $scheduleProgressException['rule_version'] }} · Calculated at: {{ $scheduleProgressException['calculated_at'] }}</p>
            <p class="mt-1 text-xs text-slate-500">Dimension: {{ $scheduleProgressException['dimension'] }} · Project ID: {{ $scheduleProgressException['project_id'] }}</p>
            <ul class="mt-4 space-y-2">
                @foreach ($scheduleProgressException['source_scope'] as $sourceRef)
                    @php($sourceLink = app(\App\Services\CedExecutiveSourceLinkResolver::class)->resolve($project, $sourceRef))
                    <li data-schedule-progress-source-project-id="{{ $sourceRef['project_id'] }}" data-schedule-progress-source-type="{{ $sourceRef['source_type'] }}">
                        @if ($sourceLink)
                            <a class="block font-mono text-xs font-bold text-cyan-700" data-schedule-progress-source-id="{{ $sourceLink['source_id'] }}" href="{{ $sourceLink['url'] }}">{{ $sourceLink['label'] }}</a>
                        @else
                            <span class="block font-mono text-xs text-slate-600">{{ $sourceRef['label'] }}</span>
                        @endif
                        <span class="block text-xs text-slate-500">Source type: {{ $sourceRef['source_type'] }} · Source ID: {{ $sourceRef['source_id'] }} · Project ID: {{ $sourceRef['project_id'] }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @if ($financeException !== null)
        <section class="rounded-xl border border-amber-300 bg-amber-50 p-6 shadow" aria-label="Finance exception" data-finance-exception="true">
            <p class="text-sm font-black uppercase tracking-wide text-amber-950">Finance exception</p>
            <p class="mt-1 font-bold text-amber-950">Status: <span data-finance-exception-status>{{ $financeException['status'] }}</span></p>
            <p class="mt-1 text-sm text-slate-700">Cost projection: <span data-finance-exception-value>{{ $financeException['value'] }} {{ $financeException['unit'] }}</span></p>
            <p class="mt-1 text-sm text-slate-700">Reason: <span data-finance-exception-reason>{{ $financeException['reason'] }}</span></p>
            <p class="mt-2 text-xs text-slate-500">Health rule version: {{ $financeException['health_rule_version'] }} · Health calculated at: {{ $financeException['health_calculated_at'] }}</p>
            <p class="mt-1 text-xs text-slate-500">Finance rule version: {{ $financeException['finance_rule_version'] }} · Finance calculated at: {{ $financeException['finance_calculated_at'] }}</p>
            <div class="mt-3 rounded border border-amber-200 bg-white p-3" aria-label="Canonical finance summary">
                <p class="text-xs font-bold uppercase text-slate-500">Canonical finance summary</p>
                <p class="mt-1 text-sm text-slate-700">Income: <span data-finance-exception-income>{{ $finance['income'] }}</span> · Expense: <span data-finance-exception-expense>{{ $finance['expense'] }}</span> · Net: <span data-finance-exception-net>{{ $finance['net'] }}</span></p>
                <p class="mt-1 text-sm text-slate-600" data-finance-exception-explanation>{{ $finance['explanation'] }}</p>
            </div>
            <p class="mt-3 text-xs font-bold uppercase text-slate-500">Canonical finance source scope</p>
            <ul class="mt-2 space-y-2">
                @foreach ($financeException['source_scope'] as $sourceRef)
                    @php($sourceLink = app(\App\Services\CedExecutiveSourceLinkResolver::class)->resolve($project, $sourceRef))
                    <li data-finance-exception-source-project-id="{{ $sourceRef['project_id'] }}" data-finance-exception-source-type="{{ $sourceRef['source_type'] }}">
                        @if ($sourceLink)
                            <a class="block font-mono text-xs font-bold text-cyan-700" data-finance-exception-source-id="{{ $sourceLink['source_id'] }}" href="{{ $sourceLink['url'] }}">{{ $sourceLink['label'] }}</a>
                        @else
                            <span class="block font-mono text-xs text-slate-600">{{ $sourceRef['label'] }}</span>
                        @endif
                        <span class="block text-xs text-slate-500">Source type: {{ $sourceRef['source_type'] }} · Source ID: {{ $sourceRef['source_id'] }} · Project ID: {{ $sourceRef['project_id'] }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <section class="grid gap-6 lg:grid-cols-2" aria-label="Executive drill-down">
        <article class="rounded-xl bg-white p-6 shadow">
            <h2 class="text-lg font-black">Baseline leaf tasks</h2>
            <ul class="mt-4 divide-y divide-slate-200">
                @foreach ($tasks as $task)
                    <li class="py-3" data-task-id="{{ $task->id }}">
                        <span class="font-mono text-xs text-slate-500">{{ $task->id }}</span>
                        <p class="font-semibold">{{ $task->wbs_code }} · {{ $task->name }}</p>
                        <p class="text-sm text-slate-600">{{ $task->baseline_start_date->toDateString() }} → {{ $task->baseline_finish_date->toDateString() }} · weight {{ $task->weight }}</p>
                    </li>
                @endforeach
            </ul>
        </article>

        <article class="rounded-xl bg-white p-6 shadow">
            <h2 class="text-lg font-black">Finance workflow</h2>
            <ul class="mt-4 divide-y divide-slate-200">
                @forelse ($financeTransactions as $transaction)
                    <li class="py-3" data-finance-id="{{ $transaction->id }}">
                        <span class="font-mono text-xs text-slate-500">{{ $transaction->id }}</span>
                        <p class="font-semibold">{{ $transaction->description }}</p>
                        <p class="text-sm text-slate-600">{{ $transaction->direction }} · {{ $transaction->status->value }} · {{ number_format((float) $transaction->amount, 2) }}</p>
                    </li>
                @empty
                    <li class="py-3 text-sm text-slate-500">No finance transactions.</li>
                @endforelse
            </ul>
        </article>

        <article class="rounded-xl bg-white p-6 shadow">
            <h2 class="text-lg font-black">Risks</h2>
            <ul class="mt-4 divide-y divide-slate-200">
                @forelse ($risks as $risk)
                    <li class="py-3" data-risk-id="{{ $risk->id }}">
                        <span class="font-mono text-xs text-slate-500">{{ $risk->id }}</span>
                        <p class="font-semibold">{{ $risk->title }}</p>
                        <p class="text-sm text-slate-600">{{ $risk->severity }} · {{ $risk->status }}</p>
                    </li>
                @empty
                    <li class="py-3 text-sm text-slate-500">No risks.</li>
                @endforelse
            </ul>
        </article>

        <article class="rounded-xl bg-white p-6 shadow">
            <h2 class="text-lg font-black">Private evidence</h2>
            @if (session('status'))
                <p class="mt-3 rounded bg-emerald-50 p-3 text-sm text-emerald-800" role="status">{{ session('status') }}</p>
            @endif
            @if ($errors->any())
                <div class="mt-3 rounded bg-red-50 p-3 text-sm text-red-800" role="alert">
                    <p class="font-bold">Evidence upload failed.</p>
                    <ul class="mt-1 list-disc pl-5">
                        @foreach ($errors->get('evidence') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form class="mt-4 space-y-3" method="POST" action="{{ route('projects.evidence.store', $project) }}" enctype="multipart/form-data">
                @csrf
                <label class="block text-sm font-semibold" for="evidence">Upload PDF, JPEG, PNG, or WEBP (max 20 MiB)</label>
                <input class="block w-full rounded border border-slate-300 p-2 text-sm" id="evidence" name="evidence" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp" required>
                <button class="rounded bg-cyan-700 px-4 py-2 text-sm font-bold text-white" type="submit">Upload private evidence</button>
            </form>
            <ul class="mt-6 divide-y divide-slate-200">
                @forelse ($evidenceDocuments as $evidence)
                    <li class="py-3" data-evidence-id="{{ $evidence->id }}">
                        <span class="font-mono text-xs text-slate-500">{{ $evidence->id }}</span>
                        <p class="font-semibold">{{ $evidence->original_name }}</p>
                        <p class="text-sm text-slate-600">{{ $evidence->access_class }} · {{ $evidence->review_state }} · SHA-256 {{ $evidence->sha256 }}</p>
                        <a class="mt-2 inline-block text-sm font-bold text-cyan-700" href="{{ route('projects.evidence.download', [$project, $evidence]) }}">Controlled download</a>
                    </li>
                @empty
                    <li class="py-3 text-sm text-slate-500">No evidence.</li>
                @endforelse
            </ul>
        </article>
    </section>

    <section class="rounded-xl bg-white p-6 shadow" aria-label="Audit events">
        <h2 class="text-lg font-black">Recent audit events</h2>
        <ul class="mt-4 divide-y divide-slate-200">
            @forelse ($auditEvents as $event)
                <li class="py-3" data-audit-id="{{ $event->id }}">
                    <span class="font-mono text-xs text-slate-500">{{ $event->id }}</span>
                    <p class="font-semibold">{{ $event->action }}</p>
                    <p class="text-sm text-slate-600">Actor {{ $event->actor?->name ?? $event->actor_id }} · {{ $event->auditable_type }} · {{ $event->auditable_id }} · {{ $event->occurred_at->utc()->format('Y-m-d\TH:i:s\Z') }}</p>
                </li>
            @empty
                <li class="py-3 text-sm text-slate-500">No audit events.</li>
            @endforelse
        </ul>
    </section>
</main>
</body>
</html>
