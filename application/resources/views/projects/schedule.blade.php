<x-layouts.app :project="$project" title="{{ $project->code }} · Schedule & Timeline">
    <div class="space-y-6">
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
                    <a class="text-slate-600 hover:text-slate-900 hover:underline transition" href="{{ route('projects.alerts', $project) }}">Alerts</a>
                    <span class="text-slate-300">&bull;</span>
                    <a class="text-slate-600 hover:text-slate-900 hover:underline transition" href="{{ route('projects.heatmap') }}">Risk Heatmap</a>
                    <span class="text-slate-300">&bull;</span>
                    <a class="text-emerald-700 hover:underline transition" href="{{ route('projects.evm', $project) }}">EVM S-Curve 📈</a>
                </div>
                <div class="flex items-center gap-2.5 mt-2">
                    <span class="px-2.5 py-0.5 rounded text-xs font-mono font-bold bg-orange-100 text-safety-600 border border-orange-200/70">
                        {{ $project->code }}
                    </span>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ $project->name }} ({{ $project->code }})</h1>
                </div>
                <p class="text-xs text-slate-500 mt-1">Schedule & Timeline &middot; Baseline: {{ $project->baseline_start_date?->toDateString() ?? 'N/A' }} to {{ $project->baseline_finish_date?->toDateString() ?? 'N/A' }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-slate-800 transition" href="{{ route('projects.3d-twin', $project) }}">3D Twin 🌐</a>
                <a class="rounded-xl bg-safety-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-safety-700 transition" href="{{ route('projects.site', $project) }}">Site Capture 📱</a>
                <a class="rounded-xl bg-white px-4 py-2 text-xs font-bold text-slate-700 shadow-card border border-slate-200/80 hover:bg-slate-50 transition" href="{{ route('projects.lookahead', $project) }}">Lookahead</a>
                <a class="rounded-xl bg-white px-4 py-2 text-xs font-bold text-slate-700 shadow-card border border-slate-200/80 hover:bg-slate-50 transition" href="{{ route('projects.constraints', $project) }}">Constraints</a>
            </div>
        </header>

        <!-- Schedule KPI Overview -->
        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Schedule Overview Metrics">
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Tasks</p>
                <p class="mt-2 text-2xl font-black text-slate-900">{{ $stats['total'] }}</p>
                <p class="mt-1 text-xs text-slate-400">WBS work package items</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Completed</p>
                <p class="mt-2 text-2xl font-black text-emerald-700">{{ $stats['completed'] }}</p>
                <p class="mt-1 text-xs text-slate-400">Finished work packages</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">In Progress</p>
                <p class="mt-2 text-2xl font-black text-safety-600">{{ $stats['in_progress'] }}</p>
                <p class="mt-1 text-xs text-slate-400">Active site operations</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Delayed / Overdue</p>
                <p class="mt-2 text-2xl font-black {{ $stats['delayed'] > 0 ? 'text-rose-700' : 'text-slate-900' }}">{{ $stats['delayed'] }}</p>
                <p class="mt-1 text-xs text-slate-400">Beyond baseline finish date</p>
            </div>
        </section>

        <!-- WBS Tasks Timeline Breakdown -->
        <section class="overflow-hidden rounded-2xl bg-white shadow-card border border-slate-200/80" aria-label="Work Breakdown Structure Tasks">
            <div class="border-b border-slate-200/80 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Work Breakdown Schedule & Progress</h2>
                    <p class="text-[11px] text-slate-500">Baseline timelines, weights, and latest verified progress entries</p>
                </div>
                <span class="text-xs font-mono font-bold text-slate-400">{{ count($tasks) }} Tasks</span>
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
                                    <span class="rounded-lg bg-orange-100 border border-orange-200 px-2.5 py-0.5 font-mono text-xs font-bold text-safety-600">{{ $task->wbs_code }}</span>
                                    <h3 class="text-base font-bold text-slate-900">{{ $task->name }}</h3>
                                    @if ($isDelayed)
                                        <span class="rounded-full bg-rose-100 border border-rose-200 px-2.5 py-0.5 text-xs font-bold text-rose-800">Delayed</span>
                                    @endif
                                    @php
                                        $openConstraints = $task->constraints ? $task->constraints->where('status', \App\Models\ProjectConstraint::STATUS_OPEN)->count() : 0;
                                    @endphp
                                    @if ($openConstraints > 0)
                                        <span class="rounded-full bg-amber-100 border border-amber-200 px-2.5 py-0.5 text-xs font-bold text-amber-800" title="{{ $openConstraints }} open impediment(s)">
                                            ⚠️ {{ $openConstraints }} Impediment{{ $openConstraints > 1 ? 's' : '' }}
                                        </span>
                                    @endif
                                    <span class="rounded-full border px-2.5 py-0.5 text-xs font-bold {{ $task->status === 'COMPLETED' ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : ($task->status === 'IN_PROGRESS' ? 'bg-orange-100 text-safety-600 border-orange-200' : 'bg-slate-100 text-slate-600 border-slate-200') }}">
                                        {{ $task->status }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 font-medium">
                                    Baseline: {{ $task->baseline_start_date?->toDateString() ?? 'N/A' }} → {{ $task->baseline_finish_date?->toDateString() ?? 'N/A' }}
                                    &middot; Weight: {{ number_format((float) $task->weight, 2) }}%
                                </p>
                            </div>
                            <div class="w-full max-w-xs space-y-1.5 text-right">
                                <div class="flex justify-between text-xs font-bold">
                                    <span class="text-slate-500">Progress</span>
                                    <span class="text-slate-900 font-mono">{{ number_format($pct, 1) }}%</span>
                                </div>
                                <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
                                    <div
                                        class="h-full rounded-full transition-all duration-300 {{ $pct >= 100.0 ? 'bg-emerald-500' : ($isDelayed ? 'bg-rose-500' : 'bg-safety-500') }}"
                                        style="width: {{ min(100, max(0, $pct)) }}%"
                                    ></div>
                                </div>
                                @if ($progress !== null)
                                    <p class="text-[11px] text-slate-400 font-medium">Updated: {{ $progress->progress_date?->toDateString() ?? '' }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center text-slate-500">
                        <p class="text-sm font-bold text-slate-700">No WBS tasks recorded for this project.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.app>
