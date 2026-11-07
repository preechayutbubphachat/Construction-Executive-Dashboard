<section class="space-y-6" aria-labelledby="lookahead-title">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a class="text-xs font-bold text-cyan-700 hover:underline" href="{{ route('projects.portfolio') }}">← Back to Portfolio</a>
                <span class="text-xs text-slate-400">·</span>
                <a class="text-xs font-bold text-cyan-700 hover:underline" href="{{ route('projects.executive', $project) }}">Executive Health</a>
                <span class="text-xs text-slate-400">·</span>
                <a class="text-xs font-bold text-cyan-700 hover:underline" href="{{ route('projects.schedule.show', $project) }}">Schedule & Timeline</a>
                <span class="text-xs text-slate-400">·</span>
                <a class="text-xs font-bold text-cyan-700 hover:underline" href="{{ route('projects.constraints', $project) }}">Constraints Register</a>
                <span class="text-xs text-slate-400">·</span>
                <a class="text-xs font-bold text-cyan-700 hover:underline" href="{{ route('projects.alerts', $project) }}">Alerts</a>
            </div>
            <p class="mt-1 text-sm font-semibold uppercase tracking-wide text-slate-500">{{ $project->code }}</p>
            <h1 id="lookahead-title" class="text-2xl font-black">Weekly Lookahead Schedule (WLS)</h1>
            <p class="text-sm text-slate-600">{{ $project->name }} · Lean Last Planner {{ $weeks }}-week lookahead & make-ready analysis</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('projects.alerts', $project) }}" class="rounded-lg bg-red-700 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-red-800">Alerts Center</a>
            @if (\Illuminate\Support\Facades\Route::has('projects.report'))
                <a href="{{ route('projects.report', $project) }}" class="rounded-lg bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-slate-800">Executive Report</a>
            @endif
        </div>
    </header>

    @php
        $summary = $lookahead['summary'];
        $window = $lookahead['window'];
        $rate = (float) $summary['make_ready_rate'];
        $rateColor = $rate >= 80 ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : ($rate >= 60 ? 'text-amber-700 bg-amber-50 border-amber-200' : 'text-red-700 bg-red-50 border-red-200');
    @endphp

    {{-- KPI Summary Bar --}}
    <section class="grid gap-4 sm:grid-cols-5" aria-label="Lookahead KPI Overview">
        <div class="rounded-xl bg-white p-5 shadow border {{ $rateColor }}">
            <p class="text-xs font-bold uppercase tracking-wide">Make-Ready Rate</p>
            <p class="mt-2 text-3xl font-black">{{ number_format($rate, 1) }}%</p>
            <p class="mt-1 text-xs opacity-80">Sound tasks without constraints</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow border border-slate-100">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Tasks in Window</p>
            <p class="mt-2 text-3xl font-black text-slate-900">{{ $summary['total_tasks'] }}</p>
            <p class="mt-1 text-xs text-slate-500">Scheduled in next {{ $weeks }} weeks</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow border border-slate-100">
            <p class="text-xs font-bold uppercase tracking-wide text-emerald-600">Make-Ready (Ready)</p>
            <p class="mt-2 text-3xl font-black text-emerald-600">{{ $summary['make_ready_count'] }}</p>
            <p class="mt-1 text-xs text-slate-500">Zero active impediments</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow border border-slate-100">
            <p class="text-xs font-bold uppercase tracking-wide text-amber-600">Constrained (Blocked)</p>
            <p class="mt-2 text-3xl font-black text-amber-600">{{ $summary['constrained_count'] }}</p>
            <p class="mt-1 text-xs text-slate-500">Require constraint removal</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow border border-slate-100">
            <p class="text-xs font-bold uppercase tracking-wide text-red-600">Critical Blockers</p>
            <p class="mt-2 text-3xl font-black text-red-600">{{ $summary['critical_blockers_count'] }}</p>
            <p class="mt-1 text-xs text-slate-500">Critical impediments</p>
        </div>
    </section>

    {{-- Controls & Filter Bar --}}
    <section class="rounded-xl bg-white p-4 shadow space-y-4" aria-label="Lookahead controls">
        <div class="flex flex-wrap items-center justify-between gap-4">
            {{-- Window Duration Selector --}}
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold uppercase text-slate-500">Horizon:</span>
                @foreach ([3 => '3 Weeks', 4 => '4 Weeks', 6 => '6 Weeks'] as $wCount => $wLabel)
                    <button
                        wire:click="setWeeks({{ $wCount }})"
                        class="rounded-lg px-3 py-1.5 text-xs font-bold transition {{ $weeks === $wCount ? 'bg-cyan-700 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
                    >
                        {{ $wLabel }}
                    </button>
                @endforeach
            </div>

            {{-- Status Filters --}}
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold uppercase text-slate-500">Status:</span>
                @foreach (['ALL' => 'All Tasks', 'MAKE_READY' => 'Make-Ready Only', 'CONSTRAINED' => 'Constrained Only'] as $fKey => $fLabel)
                    <button
                        wire:click="setFilter('{{ $fKey }}')"
                        class="rounded-lg px-3 py-1.5 text-xs font-bold transition {{ $filter === $fKey ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
                    >
                        {{ $fLabel }}
                    </button>
                @endforeach
            </div>

            {{-- Timeline Window Navigation --}}
            <div class="flex items-center gap-2">
                <button wire:click="shiftWindow(-1)" class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-200">
                    ← Prev
                </button>
                <button wire:click="resetToToday" class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-200">
                    Current
                </button>
                <button wire:click="shiftWindow(1)" class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-200">
                    Next →
                </button>
                <span class="text-xs font-mono font-semibold text-slate-600 bg-slate-50 border border-slate-200 px-2 py-1 rounded">
                    {{ $window['start_date'] }} → {{ $window['end_date'] }}
                </span>
            </div>
        </div>
    </section>

    {{-- Lookahead Schedule Matrix --}}
    <section class="rounded-xl bg-white p-6 shadow space-y-4" aria-label="Lookahead Task Matrix">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-base font-bold text-slate-800">Lookahead Work Packages ({{ count($tasks) }})</h2>
                <p class="text-xs text-slate-500">Tasks active between {{ $window['start_date'] }} and {{ $window['end_date'] }}</p>
            </div>
            <a href="{{ route('projects.constraints', $project) }}" class="text-xs font-bold text-cyan-700 hover:underline">
                Open Constraints Register →
            </a>
        </div>

        @if (empty($tasks))
            <div class="rounded-lg border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">
                No tasks match the lookahead criteria for this period and filter.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="border-b border-slate-200 bg-slate-50 uppercase text-slate-600 font-semibold">
                        <tr>
                            <th class="p-3">WBS / Task</th>
                            <th class="p-3">Baseline Dates</th>
                            <th class="p-3">Progress</th>
                            <th class="p-3 text-center">Make-Ready Status</th>
                            <th class="p-3">Active Weeks</th>
                            <th class="p-3">Active Impediments</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($tasks as $item)
                            <tr class="hover:bg-slate-50/80">
                                <td class="p-3">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-mono font-bold text-slate-500">[{{ $item['wbs_code'] }}]</span>
                                        <span class="font-bold text-slate-900">{{ $item['name'] }}</span>
                                    </div>
                                    <p class="text-[11px] text-slate-400">Status: {{ $item['status'] }}</p>
                                </td>
                                <td class="p-3 whitespace-nowrap text-slate-600">
                                    {{ $item['baseline_start_date'] }} → {{ $item['baseline_finish_date'] }}
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    <div class="w-24 space-y-1">
                                        <div class="flex justify-between text-[11px] font-bold">
                                            <span class="text-slate-700">{{ number_format($item['progress_percent'], 1) }}%</span>
                                        </div>
                                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                                            <div
                                                class="h-full rounded-full {{ $item['progress_percent'] >= 100 ? 'bg-emerald-500' : 'bg-cyan-600' }}"
                                                style="width: {{ min(100, max(0, $item['progress_percent'])) }}%"
                                            ></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    @if ($item['is_make_ready'])
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-bold text-emerald-800">
                                            ✓ Ready
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-bold text-amber-800">
                                            ⚠️ Blocked
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    <div class="flex items-center gap-1">
                                        @foreach ($lookahead['weeks'] as $wb)
                                            @php
                                                $isActiveInWeek = in_array($wb['week_number'], $item['active_weeks'], true);
                                            @endphp
                                            <span
                                                title="{{ $wb['label'] }}"
                                                class="rounded px-1.5 py-0.5 text-[10px] font-bold {{ $isActiveInWeek ? 'bg-cyan-700 text-white' : 'bg-slate-100 text-slate-300' }}"
                                            >
                                                W{{ $wb['week_number'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="p-3">
                                    @if (empty($item['open_constraints']))
                                        <span class="text-slate-400 text-[11px]">No active impediments</span>
                                    @else
                                        <div class="space-y-1">
                                            @foreach ($item['open_constraints'] as $c)
                                                <div class="flex items-center gap-1">
                                                    <span class="rounded px-1.5 py-0.2 text-[9px] font-bold {{ $c['severity'] === 'CRITICAL' ? 'bg-red-100 text-red-800' : 'bg-orange-100 text-orange-800' }}">
                                                        {{ $c['severity'] }}
                                                    </span>
                                                    <span class="font-semibold text-slate-800 text-[11px]">{{ $c['title'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</section>
