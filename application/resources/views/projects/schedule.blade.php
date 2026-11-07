<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $project->code }} · Schedule & Timeline</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
<main class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
    <header class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <div class="flex items-center gap-2">
                <a class="text-xs font-bold text-cyan-700 hover:underline" href="{{ route('projects.portfolio') }}">← Back to Portfolio</a>
                <span class="text-xs text-slate-400">·</span>
                <a class="text-xs font-bold text-cyan-700 hover:underline" href="{{ route('projects.executive', $project) }}">Executive Health</a>
                <span class="text-xs text-slate-400">·</span>
                <a class="text-xs font-bold text-cyan-700 hover:underline" href="{{ route('projects.report', $project) }}">Executive Report</a>
                <span class="text-xs text-slate-400">·</span>
                <a class="text-xs font-bold text-cyan-700 hover:underline" href="{{ route('projects.constraints', $project) }}">Constraints</a>
                <span class="text-xs text-slate-400">·</span>
                <a class="text-xs font-bold text-cyan-700 hover:underline" href="{{ route('projects.lookahead', $project) }}">Lookahead</a>
                <span class="text-xs text-slate-400">·</span>
                <a class="text-xs font-bold text-cyan-700 hover:underline" href="{{ route('projects.alerts', $project) }}">Alerts</a>
                <span class="text-xs text-slate-400">·</span>
                <a class="text-xs font-bold text-cyan-700 hover:underline" href="{{ route('projects.heatmap') }}">Risk Heatmap</a>
            </div>
            <h1 class="mt-2 text-2xl font-black text-slate-900">{{ $project->name }} ({{ $project->code }})</h1>
            <p class="text-sm text-slate-600">Schedule & Timeline · Baseline: {{ $project->baseline_start_date?->toDateString() ?? 'N/A' }} to {{ $project->baseline_finish_date?->toDateString() ?? 'N/A' }}</p>
        </div>
        <div class="flex gap-2">
            <a class="rounded-lg bg-cyan-700 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-cyan-800" href="{{ route('projects.lookahead', $project) }}">Lookahead Schedule</a>
            <a class="rounded-lg bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-slate-800" href="{{ route('projects.constraints', $project) }}">Constraints Register</a>
            <a class="rounded-lg bg-white px-4 py-2 text-xs font-bold text-slate-700 shadow-sm border border-slate-200 hover:bg-slate-50" href="{{ route('projects.executive', $project) }}">Health Analysis</a>
            <a class="rounded-lg bg-white px-4 py-2 text-xs font-bold text-slate-700 shadow-sm border border-slate-200 hover:bg-slate-50" href="{{ route('projects.wbs.show', $project) }}">WBS Tree (JSON)</a>
        </div>
    </header>

    <!-- Schedule KPI Overview -->
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Schedule Overview Metrics">
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Total Tasks</p>
            <p class="mt-2 text-3xl font-black text-slate-900">{{ $stats['total'] }}</p>
            <p class="mt-1 text-xs text-slate-500">WBS work package items</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Completed</p>
            <p class="mt-2 text-3xl font-black text-emerald-700">{{ $stats['completed'] }}</p>
            <p class="mt-1 text-xs text-slate-500">Finished work packages</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">In Progress</p>
            <p class="mt-2 text-3xl font-black text-cyan-700">{{ $stats['in_progress'] }}</p>
            <p class="mt-1 text-xs text-slate-500">Active site operations</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Delayed / Overdue</p>
            <p class="mt-2 text-3xl font-black {{ $stats['delayed'] > 0 ? 'text-red-700' : 'text-slate-900' }}">{{ $stats['delayed'] }}</p>
            <p class="mt-1 text-xs text-slate-500">Beyond baseline finish date</p>
        </div>
    </section>

    <!-- WBS Tasks Timeline Breakdown -->
    <section class="overflow-hidden rounded-xl bg-white shadow-sm" aria-label="Work Breakdown Structure Tasks">
        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="text-lg font-bold text-slate-900">Work Breakdown Schedule & Progress</h2>
            <p class="text-xs text-slate-500">Baseline timelines, weights, and latest verified progress entries</p>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse ($tasks as $task)
                @php
                    $progress = $latestProgressByTask->get((string) $task->id);
                    $pct = $progress !== null ? (float) $progress->progress_percent : 0.0;
                    $isDelayed = $task->status !== 'COMPLETED'
                        && $pct < 100.0
                        && $task->baseline_finish_date !== null
                        && $task->baseline_finish_date->toDateString() < now()->toDateString();
                @endphp
                <div class="p-6 transition hover:bg-slate-50/80">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="rounded bg-slate-100 px-2 py-0.5 font-mono text-xs font-bold text-slate-700">{{ $task->wbs_code }}</span>
                                <h3 class="text-base font-bold text-slate-900">{{ $task->name }}</h3>
                                @if ($isDelayed)
                                    <span class="rounded bg-red-100 px-2 py-0.5 text-xs font-bold text-red-800">Delayed</span>
                                @endif
                                @php
                                    $openConstraints = $task->constraints ? $task->constraints->where('status', \App\Models\ProjectConstraint::STATUS_OPEN)->count() : 0;
                                @endphp
                                @if ($openConstraints > 0)
                                    <span class="rounded bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-800" title="{{ $openConstraints }} open impediment(s)">
                                        ⚠️ {{ $openConstraints }} Impediment{{ $openConstraints > 1 ? 's' : '' }}
                                    </span>
                                @endif
                                <span class="rounded px-2 py-0.5 text-xs font-bold {{ $task->status === 'COMPLETED' ? 'bg-emerald-100 text-emerald-800' : ($task->status === 'IN_PROGRESS' ? 'bg-cyan-100 text-cyan-800' : 'bg-slate-100 text-slate-600') }}">
                                    {{ $task->status }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500">
                                Baseline: {{ $task->baseline_start_date?->toDateString() ?? 'N/A' }} → {{ $task->baseline_finish_date?->toDateString() ?? 'N/A' }}
                                · Weight: {{ number_format((float) $task->weight, 2) }}%
                            </p>
                        </div>
                        <div class="w-full max-w-xs space-y-1 text-right">
                            <div class="flex justify-between text-xs font-bold">
                                <span class="text-slate-500">Progress</span>
                                <span class="text-slate-900">{{ number_format($pct, 1) }}%</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
                                <div
                                    class="h-full rounded-full transition-all duration-300 {{ $pct >= 100.0 ? 'bg-emerald-500' : ($isDelayed ? 'bg-red-500' : 'bg-cyan-600') }}"
                                    style="width: {{ min(100, max(0, $pct)) }}%"
                                ></div>
                            </div>
                            @if ($progress !== null)
                                <p class="text-[11px] text-slate-400">Updated: {{ $progress->progress_date?->toDateString() ?? '' }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-500">
                    <p class="text-base font-semibold">No WBS tasks recorded for this project.</p>
                </div>
            @endforelse
        </div>
    </section>
</main>
</body>
</html>
