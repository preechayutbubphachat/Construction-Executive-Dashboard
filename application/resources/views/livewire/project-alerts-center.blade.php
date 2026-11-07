<section class="space-y-6" aria-labelledby="alerts-title">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold mb-2">
                <a class="text-safety-orange hover:underline transition" href="{{ route('projects.portfolio') }}">&larr; Back to Portfolio</a>
                <span class="text-slate-300">&bull;</span>
                <a class="text-slate-600 hover:text-slate-900 hover:underline transition" href="{{ route('projects.executive', $project) }}">Executive Health</a>
                <span class="text-slate-300">&bull;</span>
                <a class="text-slate-600 hover:text-slate-900 hover:underline transition" href="{{ route('projects.schedule.show', $project) }}">Schedule</a>
                <span class="text-slate-300">&bull;</span>
                <a class="text-slate-600 hover:text-slate-900 hover:underline transition" href="{{ route('projects.lookahead', $project) }}">Lookahead</a>
                <span class="text-slate-300">&bull;</span>
                <a class="text-slate-600 hover:text-slate-900 hover:underline transition" href="{{ route('projects.constraints', $project) }}">Constraints</a>
                <span class="text-slate-300">&bull;</span>
                <a class="text-slate-600 hover:text-slate-900 hover:underline transition" href="{{ route('projects.heatmap') }}">Risk Heatmap</a>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-safety-orange/10 text-safety-orange border border-safety-orange/20">
                    System Intelligence · Real-time Anomalies
                </span>
            </div>
            <p class="mt-2 text-xs font-bold uppercase tracking-wider text-slate-500">{{ $project->code }}</p>
            <h1 id="alerts-title" class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Active Early-Warning Alerts</h1>
            <p class="text-xs text-slate-500 mt-1">{{ $project->name }} · automated operational anomaly detection & risk mitigation</p>
        </div>
        <div class="flex items-center gap-2">
            @if (\Illuminate\Support\Facades\Route::has('projects.report'))
                <a href="{{ route('projects.report', $project) }}" class="rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-slate-800 transition">
                    Executive Report
                </a>
            @endif
        </div>
    </header>

    {{-- Alert Summary KPIs --}}
    <section class="grid gap-4 sm:grid-cols-4" aria-label="Alerts KPI summary">
        <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80 transition-all hover:shadow-card-hover">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-black uppercase tracking-wider text-slate-500">Total Alerts</p>
                <div class="h-2 w-2 rounded-full bg-slate-400"></div>
            </div>
            <p class="mt-2 text-3xl font-black text-slate-900">{{ $summary['total_alerts'] }}</p>
            <p class="mt-1 text-xs text-slate-500">Active operational signals</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80 transition-all hover:shadow-card-hover">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-black uppercase tracking-wider text-rose-600">Critical Severity</p>
                <div class="h-2 w-2 rounded-full bg-rose-500"></div>
            </div>
            <p class="mt-2 text-3xl font-black text-rose-600">{{ $summary['critical_count'] }}</p>
            <p class="mt-1 text-xs text-slate-500">Immediate action required</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80 transition-all hover:shadow-card-hover">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-black uppercase tracking-wider text-amber-600">Warning Level</p>
                <div class="h-2 w-2 rounded-full bg-amber-500"></div>
            </div>
            <p class="mt-2 text-3xl font-black text-amber-600">{{ $summary['warning_count'] }}</p>
            <p class="mt-1 text-xs text-slate-500">Approaching risk thresholds</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80 transition-all hover:shadow-card-hover">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-black uppercase tracking-wider text-slate-500">Status Level</p>
                <div class="h-2 w-2 rounded-full {{ $summary['highest_severity'] === 'CRITICAL' ? 'bg-rose-500' : ($summary['highest_severity'] === 'WARNING' ? 'bg-amber-500' : 'bg-emerald-500') }}"></div>
            </div>
            <p class="mt-2 text-3xl font-black {{ $summary['highest_severity'] === 'CRITICAL' ? 'text-rose-600' : ($summary['highest_severity'] === 'WARNING' ? 'text-amber-600' : 'text-emerald-600') }}">
                {{ $summary['highest_severity'] === 'NONE' ? 'NORMAL' : $summary['highest_severity'] }}
            </p>
            <p class="mt-1 text-xs text-slate-500">Evaluated {{ \Carbon\Carbon::parse($evaluatedAt)->format('H:i') }} UTC</p>
        </div>
    </section>

    {{-- Filter Toolbar --}}
    <section class="rounded-2xl bg-white p-4 shadow-card border border-slate-200/80 flex flex-wrap items-center justify-between gap-3" aria-label="Alert filters">
        <div class="flex flex-wrap items-center gap-1.5">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 mr-2">Filter Category:</span>
            @foreach ([
                'ALL' => 'All Alerts',
                'CRITICAL' => 'Critical Only',
                'SCHEDULE' => 'Schedule & Lookahead',
                'CONSTRAINT' => 'Site Constraints',
                'FINANCE' => 'Budget & Finance',
            ] as $key => $label)
                <button
                    wire:click="setFilter('{{ $key }}')"
                    class="rounded-xl px-3.5 py-1.5 text-xs font-bold transition {{ $filter === $key ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>
        <p class="text-xs font-semibold text-slate-400">Showing {{ count($alerts) }} item(s)</p>
    </section>

    {{-- Alerts List --}}
    <section class="space-y-3" aria-label="Alerts details">
        @if (empty($alerts))
            <div class="rounded-2xl bg-white p-12 text-center shadow-card border border-emerald-100">
                <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 mb-3">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">All Operations Clear</h3>
                <p class="mt-1 text-xs text-slate-500 max-w-md mx-auto">No operational early-warning alerts detected under the current filter. All schedule, lookahead, constraint, and budget indicators are within acceptable limits.</p>
            </div>
        @else
            @foreach ($alerts as $alert)
                @php
                    $isCritical = $alert['severity'] === 'CRITICAL';
                    $borderClass = $isCritical ? 'border-l-4 border-l-rose-500 border-rose-100 bg-rose-50/20' : 'border-l-4 border-l-amber-500 border-amber-100 bg-amber-50/20';
                    $badgeClass = $isCritical ? 'bg-rose-100 text-rose-800 border-rose-200' : 'bg-amber-100 text-amber-800 border-amber-200';
                @endphp
                <article class="rounded-2xl bg-white p-5 shadow-card border {{ $borderClass }} flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 transition hover:shadow-card-hover">
                    <div class="space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-block rounded-md border px-2 py-0.5 text-[10px] font-black uppercase {{ $badgeClass }}">
                                {{ $alert['severity'] }}
                            </span>
                            <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase text-slate-600">
                                {{ str_replace('_', ' ', $alert['category']) }}
                            </span>
                            <h2 class="text-sm font-bold text-slate-900">{{ $alert['title'] }}</h2>
                        </div>
                        <p class="text-xs text-slate-600">{{ $alert['message'] }}</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a
                            href="{{ $alert['action_url'] }}"
                            class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-safety-orange transition"
                        >
                            Take Action →
                        </a>
                    </div>
                </article>
            @endforeach
        @endif
    </section>
</section>
