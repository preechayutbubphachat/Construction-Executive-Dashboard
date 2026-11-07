<section class="space-y-6" aria-labelledby="lookahead-title">
    <header class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200/80 pb-5">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold">
                <a class="text-safety-600 hover:text-safety-700 hover:underline transition" href="{{ route('projects.portfolio') }}">&larr; Back to Portfolio</a>
                <span class="text-slate-300">&bull;</span>
                <a class="text-slate-600 hover:text-slate-900 hover:underline transition" href="{{ route('projects.executive', $project) }}">Executive Health</a>
                <span class="text-slate-300">&bull;</span>
                <a class="text-slate-600 hover:text-slate-900 hover:underline transition" href="{{ route('projects.schedule.show', $project) }}">Schedule & Timeline</a>
                <span class="text-slate-300">&bull;</span>
                <a class="text-slate-600 hover:text-slate-900 hover:underline transition" href="{{ route('projects.constraints', $project) }}">Constraints</a>
                <span class="text-slate-300">&bull;</span>
                <a class="text-slate-600 hover:text-slate-900 hover:underline transition" href="{{ route('projects.alerts', $project) }}">Alerts</a>
                <span class="text-slate-300">&bull;</span>
                <a class="text-emerald-700 hover:underline transition" href="{{ route('projects.evm', $project) }}">EVM S-Curve 📈</a>
            </div>
            <div class="flex items-center gap-2.5 mt-2">
                <span class="px-2.5 py-0.5 rounded text-xs font-mono font-bold bg-orange-100 text-safety-600 border border-orange-200/70">
                    {{ $project->code }}
                </span>
                <h1 id="lookahead-title" class="text-2xl font-black text-slate-900 tracking-tight">Weekly Lookahead Schedule (WLS)</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1">{{ $project->name }} &middot; Lean Last Planner {{ $weeks }}-week lookahead & make-ready analysis</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('projects.site', $project) }}" class="rounded-xl bg-safety-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-safety-700 transition">Site Capture 📱</a>
            <a href="{{ route('projects.3d-twin', $project) }}" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-slate-800 transition">3D Twin 🌐</a>
            @if (\Illuminate\Support\Facades\Route::has('projects.report'))
                <a href="{{ route('projects.report', $project) }}" class="rounded-xl bg-white px-4 py-2 text-xs font-bold text-slate-800 shadow-card border border-slate-200 hover:bg-slate-50 transition">Executive Report</a>
            @endif
        </div>
    </header>

    @php
        $summary = $lookahead['summary'];
        $window = $lookahead['window'];
        $rate = (float) $summary['make_ready_rate'];
        $rateColor = $rate >= 80 ? 'text-emerald-700 bg-emerald-50/40 border-emerald-200' : ($rate >= 60 ? 'text-amber-700 bg-amber-50/40 border-amber-200' : 'text-rose-700 bg-rose-50/40 border-rose-200');
    @endphp

    {{-- KPI Summary Bar --}}
    <section class="grid gap-4 sm:grid-cols-5" aria-label="Lookahead KPI Overview">
        <div class="rounded-2xl bg-white p-5 shadow-card border {{ $rateColor }}">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Make-Ready Rate</p>
            <p class="mt-2 text-2xl font-black">{{ number_format($rate, 1) }}%</p>
            <p class="mt-1 text-xs opacity-70">Sound tasks without constraints</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Tasks in Window</p>
            <p class="mt-2 text-2xl font-black text-slate-900">{{ $summary['total_tasks'] }}</p>
            <p class="mt-1 text-xs text-slate-400">Scheduled in next {{ $weeks }} weeks</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80">
            <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Make-Ready (Ready)</p>
            <p class="mt-2 text-2xl font-black text-emerald-700">{{ $summary['make_ready_count'] }}</p>
            <p class="mt-1 text-xs text-slate-400">Zero active impediments</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80">
            <p class="text-xs font-bold uppercase tracking-wider text-amber-700">Constrained (Blocked)</p>
            <p class="mt-2 text-2xl font-black text-amber-700">{{ $summary['constrained_count'] }}</p>
            <p class="mt-1 text-xs text-slate-400">Require constraint removal</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80">
            <p class="text-xs font-bold uppercase tracking-wider text-rose-700">Critical Blockers</p>
            <p class="mt-2 text-2xl font-black text-rose-700">{{ $summary['critical_blockers_count'] }}</p>
            <p class="mt-1 text-xs text-slate-400">Critical impediments</p>
        </div>
    </section>

    {{-- Controls & Filter Bar --}}
    <section class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80 space-y-4" aria-label="Lookahead controls">
        <div class="flex flex-wrap items-center justify-between gap-4">
            {{-- Window Duration Selector --}}
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold uppercase text-slate-500">Horizon:</span>
                @foreach ([3 => '3 Weeks', 4 => '4 Weeks', 6 => '6 Weeks'] as $wCount => $wLabel)
                    <button
                        wire:click="setWeeks({{ $wCount }})"
                        class="rounded-xl px-3 py-1.5 text-xs font-bold transition {{ $weeks === $wCount ? 'bg-safety-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
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
                        class="rounded-xl px-3 py-1.5 text-xs font-bold transition {{ $filter === $fKey ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
                    >
                        {{ $fLabel }}
                    </button>
                @endforeach
            </div>

            {{-- Timeline Window Navigation --}}
            <div class="flex items-center gap-2">
                <button wire:click="shiftWindow(-1)" class="rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-200 transition">
                    &larr; Prev
                </button>
                <button wire:click="resetToToday" class="rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-200 transition">
                    Current
                </button>
                <button wire:click="shiftWindow(1)" class="rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-200 transition">
                    Next &rarr;
                </button>
                <span class="text-xs font-mono font-bold text-slate-700 bg-slate-50 border border-slate-200 px-3 py-1 rounded-xl">
                    {{ $window['start_date'] }} &rarr; {{ $window['end_date'] }}
                </span>
            </div>
        </div>
    </section>

    {{-- Lookahead Schedule Matrix --}}
    <section class="rounded-2xl bg-white shadow-card border border-slate-200/80 overflow-hidden space-y-0" aria-label="Lookahead Task Matrix">
        <div class="border-b border-slate-200/80 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Lookahead Work Packages ({{ count($tasks) }})</h2>
                <p class="text-xs text-slate-500">Tasks active between {{ $window['start_date'] }} and {{ $window['end_date'] }}</p>
            </div>
            <a href="{{ route('projects.constraints', $project) }}" class="text-xs font-bold text-safety-600 hover:underline">
                Open Constraints Register &rarr;
            </a>
        </div>

        @if (empty($tasks))
            <div class="p-12 text-center text-xs text-slate-500">
                No tasks match the lookahead criteria for this period and filter.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="border-b border-slate-200/80 bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 font-bold">
                        <tr>
                            <th class="px-6 py-3.5">WBS / Task</th>
                            <th class="px-6 py-3.5">Baseline Dates</th>
                            <th class="px-6 py-3.5">Progress</th>
                            <th class="px-6 py-3.5 text-center">Make-Ready Status</th>
                            <th class="px-6 py-3.5">Active Weeks</th>
                            <th class="px-6 py-3.5">Active Impediments</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach ($tasks as $item)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-mono font-bold text-safety-600">[{{ $item['wbs_code'] }}]</span>
                                        <span class="font-bold text-slate-900">{{ $item['name'] }}</span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Status: {{ $item['status'] }}</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600 font-mono text-[11px]">
                                    {{ $item['baseline_start_date'] }} &rarr; {{ $item['baseline_finish_date'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="w-24 space-y-1">
                                        <div class="flex justify-between text-[11px] font-bold">
                                            <span class="text-slate-900 font-mono">{{ number_format($item['progress_percent'], 1) }}%</span>
                                        </div>
                                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                                            <div
                                                class="h-full rounded-full {{ $item['progress_percent'] >= 100 ? 'bg-emerald-500' : 'bg-safety-500' }}"
                                                style="width: {{ min(100, max(0, $item['progress_percent'])) }}%"
                                            ></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    @if ($item['is_make_ready'])
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 border border-emerald-200 px-2.5 py-0.5 text-[11px] font-bold text-emerald-800">
                                            ✓ Ready
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 border border-amber-200 px-2.5 py-0.5 text-[11px] font-bold text-amber-800">
                                            ⚠️ Blocked
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-1">
                                        @foreach ($lookahead['weeks'] as $wb)
                                            @php
                                                $isActiveInWeek = in_array($wb['week_number'], $item['active_weeks'], true);
                                            @endphp
                                            <span
                                                title="{{ $wb['label'] }}"
                                                class="rounded-lg px-2 py-0.5 text-[10px] font-bold {{ $isActiveInWeek ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-400' }}"
                                            >
                                                W{{ $wb['week_number'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if (empty($item['open_constraints']))
                                        <span class="text-slate-400 text-[11px]">No active impediments</span>
                                    @else
                                        <div class="space-y-1">
                                            @foreach ($item['open_constraints'] as $c)
                                                <div class="flex items-center gap-1.5">
                                                    <span class="rounded-full px-2 py-0.2 text-[9px] font-bold {{ $c['severity'] === 'CRITICAL' ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-orange-100 text-safety-700 border border-orange-200' }}">
                                                        {{ $c['severity'] }}
                                                    </span>
                                                    <span class="font-bold text-slate-800 text-[11px]">{{ $c['title'] }}</span>
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
