<x-layouts.app :project="null" title="Projects Management — CED">
    <div class="space-y-6">
        
        <!-- Header (MindInventory Projects Management Header) -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">
                    Project Management
                </h1>
                <p class="mt-1 text-xs text-slate-500">
                    Executive Portfolio oversight, capital allocation & operational status
                </p>
            </div>
            
            <!-- Action Toolbar (Download Report, Filter, + Add New Project) -->
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('projects.heatmap') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-700 shadow-2xs hover:border-slate-300 transition">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>Executive Risk Heatmap</span>
                </a>
                
                <button type="button" onclick="window.print()"
                        class="inline-flex items-center gap-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-700 shadow-2xs hover:border-slate-300 transition">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Download Report</span>
                </button>

                <button type="button"
                        class="inline-flex items-center gap-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-700 shadow-2xs hover:border-slate-300 transition">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>Filter</span>
                </button>

                <a href="#"
                   class="inline-flex items-center gap-2 rounded-xl bg-safety-orange hover:bg-orange-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-orange-500/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Add New Project</span>
                </a>
            </div>
        </div>

        @php
            $totalProjects = count($cards);
            $redCount = 0;
            $amberCount = 0;
            $greenCount = 0;
            $activeCount = 0;
            $completedCount = 0;
            $totalBudget = 0.0;
            $totalExpense = 0.0;

            foreach ($cards as $cardItem) {
                $p = $cardItem['project'] ?? null;
                if ($p && isset($p->budget_amount)) {
                    $totalBudget += (float) $p->budget_amount;
                }
                if ($p && $p->status === 'COMPLETED') {
                    $completedCount++;
                } else {
                    $activeCount++;
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

        <!-- 5 KPI Summary Strip (MindInventory Exact 5-Box Metric Cards) -->
        <section class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4" aria-label="Portfolio KPI Overview">
            
            <!-- 1. Total Projects -->
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80 transition-all hover:shadow-card-hover">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Projects</span>
                <p class="mt-2 text-3xl font-black text-slate-900">{{ str_pad($totalProjects, 2, '0', STR_PAD_LEFT) }}</p>
                <p class="mt-1 text-[11px] text-slate-400 font-medium">In portfolio register</p>
            </div>

            <!-- 2. Active Projects -->
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80 transition-all hover:shadow-card-hover">
                <span class="text-xs font-bold uppercase tracking-wider text-safety-orange">Active Projects</span>
                <p class="mt-2 text-3xl font-black text-safety-orange">{{ str_pad($activeCount, 2, '0', STR_PAD_LEFT) }}</p>
                <p class="mt-1 text-[11px] text-slate-400 font-medium">In execution phase</p>
            </div>

            <!-- 3. Completed Projects -->
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80 transition-all hover:shadow-card-hover">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-600">Completed Projects</span>
                <p class="mt-2 text-3xl font-black text-emerald-600">{{ str_pad($completedCount, 2, '0', STR_PAD_LEFT) }}</p>
                <p class="mt-1 text-[11px] text-slate-400 font-medium">Successfully delivered</p>
            </div>

            <!-- 4. Delayed Projects -->
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80 transition-all hover:shadow-card-hover">
                <span class="text-xs font-bold uppercase tracking-wider text-rose-600">Delayed Projects</span>
                <p class="mt-2 text-3xl font-black text-rose-600">{{ str_pad($redCount, 2, '0', STR_PAD_LEFT) }}</p>
                <p class="mt-1 text-[11px] text-slate-400 font-medium">Requiring executive action</p>
            </div>

            <!-- 5. Total Budget -->
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80 transition-all hover:shadow-card-hover col-span-2 sm:col-span-1">
                <span class="text-xs font-bold uppercase tracking-wider text-blue-600">Total Budget</span>
                <p class="mt-2 text-2xl sm:text-3xl font-black text-blue-600 truncate">฿{{ number_format($totalBudget, 2) }}</p>
                <p class="mt-1 text-[11px] text-slate-400 font-medium">Allocated capital</p>
            </div>
        </section>

        <!-- Executive Priority Action Queue: สิ่งที่ต้องทำก่อนวันนี้ -->
        <x-ced.action-priority-center :project="null" />

        <!-- Main Interactive Workspace (Search, Table, and Drill-down Cards) -->
        <div x-data="{ search: '', filter: 'ALL' }" class="space-y-6">
            
            @if ($totalProjects > 0)
                <!-- Search & Status Filter Bar -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 rounded-2xl bg-white border border-slate-200/80 p-4 shadow-card">
                    <div class="relative w-full sm:max-w-md">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input
                            x-model="search"
                            type="text"
                            placeholder="Search projects..."
                            class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50/70 text-slate-800 placeholder-slate-400 focus:bg-white focus:border-safety-orange focus:ring-1 focus:ring-safety-orange transition"
                        >
                    </div>

                    <div class="flex flex-wrap items-center gap-1.5 text-xs font-bold w-full sm:w-auto">
                        <button
                            type="button"
                            @click="filter = 'ALL'"
                            :class="filter === 'ALL' ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                            class="rounded-xl px-3.5 py-1.5 transition"
                        >
                            All ({{ $totalProjects }})
                        </button>
                        <button
                            type="button"
                            @click="filter = 'red'"
                            :class="filter === 'red' ? 'bg-rose-600 text-white shadow-sm' : 'bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200'"
                            class="rounded-xl px-3.5 py-1.5 transition"
                        >
                            Critical ({{ $redCount }})
                        </button>
                        <button
                            type="button"
                            @click="filter = 'amber'"
                            :class="filter === 'amber' ? 'bg-amber-500 text-white shadow-sm' : 'bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200'"
                            class="rounded-xl px-3.5 py-1.5 transition"
                        >
                            Warning ({{ $amberCount }})
                        </button>
                        <button
                            type="button"
                            @click="filter = 'green'"
                            :class="filter === 'green' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200'"
                            class="rounded-xl px-3.5 py-1.5 transition"
                        >
                            Healthy ({{ $greenCount }})
                        </button>
                    </div>
                </div>

                <!-- MindInventory Clean White Table View -->
                <div class="rounded-2xl bg-white border border-slate-200/80 shadow-card overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/60 text-[11px] font-black uppercase tracking-wider text-slate-500">
                                    <th class="py-3.5 px-5">Project Name</th>
                                    <th class="py-3.5 px-4">Budget</th>
                                    <th class="py-3.5 px-4">Location</th>
                                    <th class="py-3.5 px-4">Start Date</th>
                                    <th class="py-3.5 px-4">End Date</th>
                                    <th class="py-3.5 px-4">Health & Progress</th>
                                    <th class="py-3.5 px-4">Status</th>
                                    <th class="py-3.5 px-5 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                @foreach ($cards as $card)
                                    @php
                                        $p = $card['project'];
                                        $h = $card['health'] ?? null;
                                        $healthStatus = is_array($h) && isset($h['status']) ? $h['status'] : 'UNKNOWN';
                                        $cardFilterStatus = strtolower($healthStatus);
                                        $budgetVal = isset($p->budget_amount) ? (float) $p->budget_amount : 0.0;
                                        $isDelayed = $healthStatus === 'RED';
                                    @endphp
                                    <tr class="hover:bg-slate-50/60 transition group"
                                        x-show="(filter === 'ALL' || '{{ $cardFilterStatus }}' === filter) && ('{{ strtolower($p->code) }} {{ strtolower(addslashes($p->name)) }}'.includes(search.toLowerCase().trim()))"
                                    >
                                        <!-- Project Name & Code -->
                                        <td class="py-4 px-5">
                                            <div class="flex items-center gap-2.5">
                                                <span class="w-8 h-8 rounded-lg bg-orange-50 border border-orange-200 text-safety-orange font-bold font-mono text-[11px] flex items-center justify-center shrink-0">
                                                    {{ substr($p->code, 0, 3) }}
                                                </span>
                                                <div>
                                                    <a href="{{ route('projects.executive', $p) }}" class="font-extrabold text-slate-900 group-hover:text-safety-orange transition">
                                                        {{ $p->name }}
                                                    </a>
                                                    <span class="block text-[11px] font-mono text-slate-400">{{ $p->code }}</span>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Budget -->
                                        <td class="py-4 px-4 font-mono font-bold text-slate-800">
                                            ฿{{ number_format($budgetVal, 2) }}
                                        </td>

                                        <!-- Location -->
                                        <td class="py-4 px-4 text-slate-600">
                                            Bangkok, TH
                                        </td>

                                        <!-- Start Date -->
                                        <td class="py-4 px-4 font-mono text-slate-600">
                                            {{ $p->baseline_start_date ? \Carbon\Carbon::parse($p->baseline_start_date)->format('M d, Y') : 'N/A' }}
                                        </td>

                                        <!-- End Date -->
                                        <td class="py-4 px-4 font-mono text-slate-600">
                                            {{ $p->baseline_finish_date ? \Carbon\Carbon::parse($p->baseline_finish_date)->format('M d, Y') : 'N/A' }}
                                        </td>

                                        <!-- Health & Progress -->
                                        <td class="py-4 px-4">
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold {{ $healthStatus === 'GREEN' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($healthStatus === 'RED' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-amber-50 text-amber-700 border border-amber-200') }}">
                                                    {{ $healthStatus }}
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Status Pill with Dot (MindInventory Style) -->
                                        <td class="py-4 px-4 whitespace-nowrap">
                                            @if ($p->status === 'COMPLETED')
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    Completed
                                                </span>
                                            @elseif ($isDelayed)
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                    Delayed
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-orange-50 text-safety-orange border border-orange-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-safety-orange"></span>
                                                    Active
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Actions -->
                                        <td class="py-4 px-5 text-right whitespace-nowrap">
                                            <div class="inline-flex items-center gap-1.5">
                                                <!-- View / Executive Health -->
                                                <a href="{{ route('projects.executive', $p) }}"
                                                   class="p-2 rounded-xl text-slate-500 hover:text-safety-orange hover:bg-orange-50 transition"
                                                   title="Executive Health">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                </a>
                                                <!-- WBS Tasks -->
                                                <a href="{{ route('projects.wbs.show', $p) }}"
                                                   class="p-2 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition"
                                                   title="WBS Tasks">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                                </a>
                                                <!-- Finance -->
                                                <a href="{{ route('projects.finance', $p) }}"
                                                   class="p-2 rounded-xl text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 transition"
                                                   title="Finance & Disbursements">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                </a>
                                                <!-- Executive Report -->
                                                @if (\Illuminate\Support\Facades\Route::has('projects.report'))
                                                    <a href="{{ route('projects.report', $p) }}"
                                                       class="p-2 rounded-xl text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition"
                                                       title="Executive Report">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Detailed Provenance, Exceptions & Traceability Cards (Preserving Exact Test Rigor) -->
            <div class="space-y-6 pt-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-black uppercase tracking-wider text-slate-500">
                        Project Detail Cards & Canonical Traceability Scope
                    </h2>
                </div>

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
                        class="rounded-2xl bg-white border border-slate-200/80 p-6 shadow-card hover:shadow-card-hover transition-all"
                        data-project-id="{{ $card['project']->id }}"
                        x-show="(filter === 'ALL' || '{{ $cardStatus }}' === filter) && ('{{ strtolower($card['project']->code) }} {{ strtolower(addslashes($card['project']->name)) }}'.includes(search.toLowerCase().trim()))"
                    >
                        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-bold text-orange-700 bg-orange-50 px-2 py-0.5 rounded-md border border-orange-200" data-project-code="{{ $card['project']->code }}">{{ $card['project']->code }}</span>
                                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-slate-100 text-slate-700 border border-slate-200" data-project-status="{{ $card['status'] }}">Project status: {{ $card['status'] }}</span>
                                </div>
                                <h3 class="text-xl font-bold text-slate-900 mt-1.5">{{ $card['project']->name }}</h3>
                            </div>
                            @if ($health !== null)
                                <div class="rounded-xl bg-slate-50 border border-slate-200 px-4 py-2.5 text-right">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Accepted health</p>
                                    <p class="text-lg font-black {{ $health['status'] === 'GREEN' ? 'text-emerald-600' : ($health['status'] === 'RED' ? 'text-rose-600' : 'text-amber-600') }}" data-health-status="{{ $health['status'] }}">{{ $health['status'] }}</p>
                                    <p class="text-[11px] text-slate-500 font-mono">Rule version: {{ $health['rule_version'] }} · Calculated at: {{ $health['calculated_at'] }}</p>
                                </div>
                            @else
                                <div class="rounded-xl bg-amber-50 border border-amber-200 px-4 py-2.5 text-right text-amber-800">
                                    <p class="text-[10px] font-bold uppercase tracking-wider">Health unavailable</p>
                                    <p class="text-xs">Not available from accepted inputs.</p>
                                </div>
                            @endif
                        </div>

                        <!-- Quick Navigation Action Bar -->
                        <div class="mt-4 flex flex-wrap gap-2 text-xs font-bold">
                            <a class="rounded-xl bg-sky-50 border border-sky-200 px-3 py-1.5 text-sky-700 transition hover:bg-sky-100" href="{{ route('projects.executive', $card['project']) }}">Executive Health</a>
                            <a class="rounded-xl bg-slate-100 border border-slate-200 px-3 py-1.5 text-slate-700 transition hover:bg-slate-200/80" href="{{ route('projects.wbs.show', $card['project']) }}">WBS Tasks</a>
                            <a class="rounded-xl bg-slate-100 border border-slate-200 px-3 py-1.5 text-slate-700 transition hover:bg-slate-200/80" href="{{ route('projects.finance', $card['project']) }}">Finance</a>
                            @if (\Illuminate\Support\Facades\Route::has('projects.report'))
                                <a class="rounded-xl bg-slate-100 border border-slate-200 px-3 py-1.5 text-slate-700 transition hover:bg-slate-200/80" href="{{ route('projects.report', $card['project']) }}">Executive Report</a>
                            @else
                                <a class="rounded-xl bg-slate-100 border border-slate-200 px-3 py-1.5 text-slate-700 transition hover:bg-slate-200/80" href="{{ url('/projects/' . $card['project']->id . '/report') }}">Executive Report</a>
                            @endif
                        </div>

                        @if ($scheduleProgressException !== null)
                            <section class="mt-5 rounded-2xl border border-rose-200 bg-rose-50/70 p-4" aria-label="Schedule/progress exception" data-schedule-progress-exception="true">
                                <p class="text-xs font-black uppercase tracking-wider text-rose-700">Schedule/progress exception</p>
                                <p class="mt-1 font-bold text-rose-950">Status: <span data-schedule-progress-status>{{ $scheduleProgressException['status'] }}</span></p>
                                <p class="mt-1 text-xs text-rose-900">Schedule variance: <span data-schedule-progress-value>{{ $scheduleProgressException['value'] }} {{ $scheduleProgressException['unit'] }}</span></p>
                                <p class="mt-1 text-xs text-rose-900">Reason: <span data-schedule-progress-reason>{{ $scheduleProgressException['reason'] }}</span></p>
                                <p class="mt-2 text-[11px] text-rose-800 font-mono">Rule version: {{ $scheduleProgressException['rule_version'] }} · Calculated at: {{ $scheduleProgressException['calculated_at'] }}</p>
                                <p class="mt-0.5 text-[11px] text-rose-800 font-mono">Dimension: {{ $scheduleProgressException['dimension'] }} · Project ID: {{ $scheduleProgressException['project_id'] }}</p>
                                <ul class="mt-3 space-y-1.5">
                                    @foreach ($scheduleProgressException['source_scope'] as $sourceRef)
                                        @php($sourceLink = app(\App\Services\CedExecutiveSourceLinkResolver::class)->resolve($card['project'], $sourceRef))
                                        <li data-schedule-progress-source-project-id="{{ $sourceRef['project_id'] }}" data-schedule-progress-source-type="{{ $sourceRef['source_type'] }}">
                                            @if ($sourceLink)
                                                <a class="block text-xs font-bold text-sky-700 hover:underline" data-schedule-progress-source-id="{{ $sourceLink['source_id'] }}" href="{{ $sourceLink['url'] }}">{{ $sourceLink['label'] }}</a>
                                            @else
                                                <span class="block text-xs text-slate-600">{{ $sourceRef['label'] }}</span>
                                            @endif
                                            <span class="block text-[11px] text-rose-800 font-mono">Source type: {{ $sourceRef['source_type'] }} · Source ID: {{ $sourceRef['source_id'] }} · Project ID: {{ $sourceRef['project_id'] }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </section>
                        @endif

                        @if ($financeException !== null)
                            <section class="mt-5 rounded-2xl border border-amber-200 bg-amber-50/70 p-4" aria-label="Finance exception" data-finance-exception="true">
                                <p class="text-xs font-black uppercase tracking-wider text-amber-700">Finance exception</p>
                                <p class="mt-1 font-bold text-amber-950">Status: <span data-finance-exception-status>{{ $financeException['status'] }}</span></p>
                                <p class="mt-1 text-xs text-amber-900">Cost projection: <span data-finance-exception-value>{{ $financeException['value'] }} {{ $financeException['unit'] }}</span></p>
                                <p class="mt-1 text-xs text-amber-900">Reason: <span data-finance-exception-reason>{{ $financeException['reason'] }}</span></p>
                                <p class="mt-2 text-[11px] text-amber-800 font-mono">Health rule version: {{ $financeException['health_rule_version'] }} · Health calculated at: {{ $financeException['health_calculated_at'] }}</p>
                                <p class="mt-0.5 text-[11px] text-amber-800 font-mono">Finance rule version: {{ $financeException['finance_rule_version'] }} · Finance calculated at: {{ $financeException['finance_calculated_at'] }}</p>
                                <div class="mt-3 rounded-xl border border-amber-200 bg-white p-3.5" aria-label="Canonical finance summary">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Canonical finance summary</p>
                                    <p class="mt-1 text-xs text-slate-800 tabular-nums font-semibold">Income: <span data-finance-exception-income>{{ $card['finance']['income'] }}</span> · Expense: <span data-finance-exception-expense>{{ $card['finance']['expense'] }}</span> · Net: <span data-finance-exception-net>{{ $card['finance']['net'] }}</span></p>
                                    <p class="mt-1 text-xs text-slate-600" data-finance-exception-explanation>{{ $card['finance']['explanation'] }}</p>
                                </div>
                                <p class="mt-3 text-[10px] font-bold uppercase tracking-wider text-amber-900">Canonical finance source scope</p>
                                <ul class="mt-2 space-y-1.5">
                                    @foreach ($financeException['source_scope'] as $sourceRef)
                                        @php($sourceLink = app(\App\Services\CedExecutiveSourceLinkResolver::class)->resolve($card['project'], $sourceRef))
                                        <li data-finance-exception-source-project-id="{{ $sourceRef['project_id'] }}" data-finance-exception-source-type="{{ $sourceRef['source_type'] }}">
                                            @if ($sourceLink)
                                                <a class="block text-xs font-bold text-sky-700 hover:underline" data-finance-exception-source-id="{{ $sourceLink['source_id'] }}" href="{{ $sourceLink['url'] }}">{{ $sourceLink['label'] }}</a>
                                            @else
                                                <span class="block text-xs text-slate-600">{{ $sourceRef['label'] }}</span>
                                            @endif
                                            <span class="block text-[11px] text-amber-800 font-mono">Source type: {{ $sourceRef['source_type'] }} · Source ID: {{ $sourceRef['source_id'] }} · Project ID: {{ $sourceRef['project_id'] }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </section>
                        @endif

                        @if ($evidenceDataQualityException !== null)
                            <section class="mt-5 rounded-2xl border border-amber-200 bg-amber-50/70 p-4" aria-label="Evidence/data-quality exception" data-evidence-data-quality-exception="true">
                                <p class="text-xs font-black uppercase tracking-wider text-amber-700">Evidence/data-quality exception</p>
                                <p class="mt-1 font-bold text-amber-950">Status: <span data-evidence-data-quality-status>{{ $evidenceDataQualityException['status'] }}</span></p>
                                <p class="mt-1 text-xs text-amber-900">Data freshness: <span data-evidence-data-quality-value>{{ $evidenceDataQualityException['value'] }} {{ $evidenceDataQualityException['unit'] }}</span></p>
                                <p class="mt-1 text-xs text-amber-900">Reason: <span data-evidence-data-quality-reason>{{ $evidenceDataQualityException['reason'] }}</span></p>
                                <p class="mt-2 text-[11px] text-amber-800 font-mono">Rule version: {{ $evidenceDataQualityException['rule_version'] }} · Calculated at: {{ $evidenceDataQualityException['calculated_at'] }}</p>
                                <p class="mt-0.5 text-[11px] text-amber-800 font-mono">Dimension: {{ $evidenceDataQualityException['dimension'] }} · Project ID: {{ $evidenceDataQualityException['project_id'] }}</p>
                                <ul class="mt-3 space-y-1.5">
                                    @foreach ($evidenceDataQualityException['source_scope'] as $sourceRef)
                                        <li data-evidence-data-quality-source-project-id="{{ $sourceRef['project_id'] }}" data-evidence-data-quality-source-type="{{ $sourceRef['source_type'] }}">
                                            <span class="block text-xs text-slate-700">{{ $sourceRef['label'] }}</span>
                                            <span class="block text-[11px] text-amber-800 font-mono">Source type: {{ $sourceRef['source_type'] }} · Source ID: {{ $sourceRef['source_id'] }} · Project ID: {{ $sourceRef['project_id'] }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                                @if ($evidenceDataQualityException['project_id'] === (string) $card['project']->id)
                                    <a class="mt-3 inline-block text-xs font-bold text-sky-700 hover:underline" data-evidence-data-quality-drill-down href="{{ route('projects.executive', $card['project']) }}">Open evidence/data-quality context →</a>
                                @endif
                            </section>
                        @endif

                        @if ($health !== null && $card['decision_required']['required'])
                            <section class="mt-5 rounded-2xl border border-amber-200 bg-amber-50/70 p-4" aria-label="Decision required" data-decision-required="true">
                                <p class="text-xs font-black uppercase tracking-wider text-amber-700">Decision required</p>
                                <p class="mt-1 font-bold text-amber-950">Severity: {{ $card['decision_required']['severity'] }}</p>
                                <ul class="mt-3 space-y-3">
                                    @foreach ($card['decision_required']['reasons'] as $dimension => $reason)
                                        <li data-decision-dimension="{{ $dimension }}" class="rounded-xl bg-white p-3.5 border border-amber-200 shadow-2xs">
                                            <p class="font-bold text-slate-900 text-xs">{{ $dimension }} · <span class="text-amber-700">{{ $reason['status'] }}</span></p>
                                            <p class="text-xs text-slate-600 mt-0.5">{{ $reason['reason'] }}</p>
                                            @foreach ($reason['source_scope'] as $sourceRef)
                                                @if (is_array($sourceRef)
                                                    && isset($sourceRef['source_type'], $sourceRef['source_id'], $sourceRef['project_id'], $sourceRef['label'])
                                                    && is_string($sourceRef['project_id'])
                                                    && $sourceRef['project_id'] === (string) $card['project']->id)
                                                    @php($sourceLink = app(\App\Services\CedExecutiveSourceLinkResolver::class)->resolve($card['project'], $sourceRef))
                                                    @if ($sourceLink)
                                                        <a class="mt-1.5 block text-xs font-bold text-sky-700 hover:underline" data-decision-source-id="{{ $sourceLink['source_id'] }}" data-decision-source-type="{{ $sourceLink['source_type'] }}" href="{{ $sourceLink['url'] }}">{{ $sourceLink['label'] }}</a>
                                                    @else
                                                        <span class="mt-1 block text-xs text-slate-600">{{ $sourceRef['label'] }}</span>
                                                    @endif
                                                @endif
                                            @endforeach
                                        </li>
                                    @endforeach
                                </ul>
                                <a class="mt-3 inline-block text-xs font-bold text-sky-700 hover:underline" href="{{ route('projects.executive', $card['project']) }}">Open decision context →</a>
                            </section>
                        @endif

                        @if ($health !== null)
                            <section class="mt-5 border-t border-slate-100 pt-4" aria-label="Health explanation">
                                <h4 class="font-bold text-slate-900 text-sm">Why this status</h4>
                                <p class="mt-1 text-xs text-slate-600" data-health-explanation>{{ $health['explanation'] }}</p>
                                <ul class="mt-3 grid gap-3 md:grid-cols-2">
                                    @foreach ($health['dimensions'] as $dimension => $signal)
                                        <li class="rounded-2xl border border-slate-200 bg-slate-50/80 p-3.5" data-dimension="{{ $dimension }}">
                                            <div class="flex items-center justify-between">
                                                <p class="font-bold text-xs text-slate-900">{{ $dimension }} · {{ $signal['status'] }}</p>
                                                <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full {{ $signal['status'] === 'GREEN' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($signal['status'] === 'RED' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-amber-50 text-amber-700 border border-amber-200') }}">{{ $signal['status'] }}</span>
                                            </div>
                                            @if (array_key_exists('value', $signal) && (is_int($signal['value']) || is_float($signal['value'])))
                                                <p class="text-xs text-slate-700 mt-1 font-mono font-semibold">Value: {{ $signal['value'] }}@if (isset($signal['unit']) && is_string($signal['unit']) && trim($signal['unit']) !== '') {{ $signal['unit'] }}@endif</p>
                                            @endif
                                            <p class="text-xs text-slate-600 mt-1">Dimension reason: {{ $signal['reason'] }}</p>
                                            <p class="mt-1 text-[11px] text-slate-500">Canonical reason: {{ $health['reasons'][$dimension] }}</p>
                                            @php($sourceKey = $dimension === 'DATA_FRESHNESS' ? 'freshness' : strtolower($dimension))
                                            @php($source = $health['source_scope'][$sourceKey])
                                            @foreach ($source as $sourceRef)
                                                @if ($sourceRef['project_id'] === (string) $card['project']->id)
                                                    @php($sourceLink = app(\App\Services\CedExecutiveSourceLinkResolver::class)->resolve($card['project'], $sourceRef))
                                                    @if ($sourceLink)
                                                        <a class="mt-1.5 block text-xs font-bold text-sky-700 hover:underline" data-source-id="{{ $sourceLink['source_id'] }}" data-source-type="{{ $sourceLink['source_type'] }}" href="{{ $sourceLink['url'] }}">{{ $sourceLink['label'] }}</a>
                                                    @else
                                                        <span class="mt-1 block text-xs text-slate-600">{{ $sourceRef['label'] }}</span>
                                                    @endif
                                                    <span class="block text-xs text-slate-500">Source type: {{ $sourceRef['source_type'] }} · Source ID: {{ $sourceRef['source_id'] }} · Project ID: {{ $sourceRef['project_id'] }}</span>
                                                @endif
                                            @endforeach
                                        </li>
                                    @endforeach
                                </ul>
                            </section>
                        @endif

                        <dl class="mt-5 grid gap-3 text-xs sm:grid-cols-3 bg-slate-50 rounded-2xl p-3.5 border border-slate-200">
                            <div><dt class="font-bold text-slate-500">Finance ({{ $card['finance']['rule_version'] }})</dt><dd class="text-slate-900 mt-0.5 font-mono font-semibold">Income <span data-finance-income>{{ $card['finance']['income'] }}</span> · Expense <span data-finance-expense>{{ $card['finance']['expense'] }}</span> · Net <span data-finance-net>{{ $card['finance']['net'] }}</span></dd></div>
                            <div><dt class="font-bold text-slate-500">Schedule/progress sources</dt><dd class="text-slate-800 mt-0.5 font-medium">{{ count($card['schedule_source_scope']['task_ids']) }} tasks · {{ count($card['schedule_source_scope']['progress_entry_ids']) }} progress</dd></div>
                            <div><dt class="font-bold text-slate-500">Traceability</dt><dd class="text-slate-800 mt-0.5 font-medium">{{ count($card['evidence_ids']) }} evidence · {{ count($card['audit_ids']) }} audit events</dd></div>
                        </dl>
                        <p class="mt-3 text-xs text-slate-500" data-finance-explanation>{{ $card['finance']['explanation'] }}</p>
                        
                        <!-- Quick Matrix Actions -->
                        <div class="mt-5 flex flex-wrap gap-2 text-xs font-bold border-t border-slate-100 pt-4">
                            <a class="rounded-xl bg-sky-50 border border-sky-200 px-3 py-1.5 text-sky-700 hover:bg-sky-100 transition shadow-2xs" href="{{ route('projects.executive', $card['project']) }}">Executive Health</a>
                            <a class="rounded-xl bg-slate-100 border border-slate-200 px-3 py-1.5 text-slate-700 hover:bg-slate-200 transition shadow-2xs" href="{{ route('projects.alerts', $card['project']) }}">Alerts</a>
                            <a class="rounded-xl bg-slate-100 border border-slate-200 px-3 py-1.5 text-slate-700 hover:bg-slate-200 transition shadow-2xs" href="{{ route('projects.lookahead', $card['project']) }}">Lookahead</a>
                            <a class="rounded-xl bg-slate-100 border border-slate-200 px-3 py-1.5 text-slate-700 hover:bg-slate-200 transition shadow-2xs" href="{{ route('projects.constraints', $card['project']) }}">Constraints</a>
                            <a class="rounded-xl bg-orange-50 border border-orange-200 px-3 py-1.5 text-orange-700 hover:bg-orange-100 transition shadow-2xs" href="{{ route('projects.site', $card['project']) }}">Field Capture 📱</a>
                            <a class="rounded-xl bg-indigo-50 border border-indigo-200 px-3 py-1.5 text-indigo-700 hover:bg-indigo-100 transition shadow-2xs" href="{{ route('projects.3d-twin', $card['project']) }}">3D Twin 🌐</a>
                            <a class="rounded-xl bg-emerald-50 border border-emerald-200 px-3 py-1.5 text-emerald-700 hover:bg-emerald-100 transition shadow-2xs" href="{{ route('projects.evm', $card['project']) }}">EVM S-Curve 📈</a>
                            <a class="rounded-xl bg-emerald-50 border border-emerald-200 px-3 py-1.5 text-emerald-700 hover:bg-emerald-100 transition shadow-2xs" href="{{ route('projects.ipc.index', $card['project']) }}">💰 IPC & Payment</a>
                            <a class="rounded-xl bg-purple-50 border border-purple-200 px-3 py-1.5 text-purple-700 hover:bg-purple-100 transition shadow-2xs" href="{{ route('projects.vo.index', $card['project']) }}">📑 Variation Orders</a>
                            <a class="rounded-xl bg-rose-50 border border-rose-200 px-3 py-1.5 text-rose-700 hover:bg-rose-100 transition shadow-2xs" href="{{ route('projects.ncr.index', $card['project']) }}">🛡️ QC & NCR</a>
                            <a class="rounded-xl bg-amber-50 border border-amber-200 px-3 py-1.5 text-amber-700 hover:bg-amber-100 transition shadow-2xs" href="{{ route('projects.procurement.index', $card['project']) }}">🧱 Procurement</a>
                            <a class="rounded-xl bg-sky-50 border border-sky-200 px-3 py-1.5 text-sky-700 hover:bg-sky-100 transition shadow-2xs" href="{{ route('projects.resources.index', $card['project']) }}">👷 Workforce & Machinery</a>
                            <a class="rounded-xl bg-emerald-50 border border-emerald-200 px-3 py-1.5 text-emerald-700 hover:bg-emerald-100 transition shadow-2xs" href="{{ route('projects.hse.index', $card['project']) }}">🦺 HSE & Permits</a>
                            <a class="rounded-xl bg-sky-50 border border-sky-200 px-3 py-1.5 text-sky-700 hover:bg-sky-100 transition shadow-2xs" href="{{ route('projects.engineering.index', $card['project']) }}">📐 RFI & Submittals</a>
                            <a class="rounded-xl bg-orange-100 border border-orange-300 px-3 py-1.5 text-orange-800 hover:bg-orange-200 transition shadow-2xs" href="{{ route('projects.handover.index', $card['project']) }}">🏁 Handover & Closeout</a>
                        </div>
                    </article>
                @empty
                    <section class="rounded-2xl bg-white border border-slate-200 p-12 text-center shadow-card">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-slate-100 border border-slate-200 text-2xl text-slate-500 mb-3">
                            📁
                        </div>
                        <h2 class="text-lg font-bold text-slate-900">No projects in permitted scope</h2>
                        <p class="mt-1 text-xs text-slate-500">No project portfolio records are available for this user.</p>
                    </section>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.app>
