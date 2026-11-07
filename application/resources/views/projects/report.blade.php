<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $project->code }} · Executive Briefing Report</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: #ffffff !important;
                color: #000000 !important;
                font-size: 11pt !important;
            }
            main {
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .print-card {
                box-shadow: none !important;
                border: 1px solid #e2e8f0 !important;
                break-inside: avoid;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased print:bg-white print:text-black">
<main class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6 lg:px-8 print:max-w-none print:p-0">

    <!-- Top Action Bar (Hidden in Print) -->
    <div class="no-print flex flex-wrap items-center justify-between gap-4 rounded-xl bg-white p-4 shadow-sm border border-slate-200">
        <div class="flex items-center gap-2 text-xs font-bold">
            <a class="text-cyan-700 hover:underline" href="{{ route('projects.portfolio') }}">← Back to Portfolio</a>
            <span class="text-slate-400">·</span>
            <a class="text-cyan-700 hover:underline" href="{{ route('projects.executive', $project) }}">Executive Health</a>
            <span class="text-slate-400">·</span>
            <a class="text-cyan-700 hover:underline" href="{{ route('projects.schedule.show', $project) }}">Schedule & Timeline</a>
            <span class="text-slate-400">·</span>
            <a class="text-cyan-700 hover:underline" href="{{ route('projects.finance', $project) }}">Finance</a>
            <span class="text-slate-400">·</span>
            <a class="text-cyan-700 hover:underline" href="{{ route('projects.constraints', $project) }}">Constraints</a>
            <span class="text-slate-400">·</span>
            <a class="text-cyan-700 hover:underline" href="{{ route('projects.lookahead', $project) }}">Lookahead</a>
        </div>
        <button
            type="button"
            onclick="window.print()"
            class="flex items-center gap-1.5 rounded-lg bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow hover:bg-slate-800 transition"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Print / Save as PDF
        </button>
    </div>

    <!-- Executive Briefing Header -->
    <header class="print-card rounded-xl bg-white p-6 shadow-sm border border-slate-200">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-5">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-cyan-700">Construction Executive Dashboard · Official Brief</p>
                <h1 class="mt-1 text-3xl font-black text-slate-900">Executive Project Briefing</h1>
                <p class="mt-1 text-base font-bold text-slate-700">{{ $project->name }} ({{ $project->code }})</p>
                <p class="mt-0.5 text-xs text-slate-500">Project status: {{ $project->status }} · Baseline: {{ $project->baseline_start_date?->toDateString() ?? 'N/A' }} → {{ $project->baseline_finish_date?->toDateString() ?? 'N/A' }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs font-bold text-slate-500 uppercase">Briefing Generated</p>
                <p class="font-mono text-sm font-bold text-slate-800">{{ now('UTC')->toDateTimeString() }} UTC</p>
                @php
                    $hStatus = is_array($health) && isset($health['status']) ? $health['status'] : 'UNKNOWN';
                @endphp
                <div class="mt-2 inline-block rounded-lg px-3 py-1 font-black text-xs {{ $hStatus === 'GREEN' ? 'bg-emerald-100 text-emerald-800' : ($hStatus === 'RED' ? 'bg-red-100 text-red-800' : ($hStatus === 'AMBER' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700')) }}">
                    HEALTH: {{ $hStatus }}
                </div>
            </div>
        </div>

        @if (is_array($health) && isset($health['explanation']))
            <div class="mt-4 rounded-lg bg-slate-50 p-3 text-xs text-slate-700">
                <span class="font-bold text-slate-900">Health Assessment:</span> {{ $health['explanation'] }}
                <span class="text-slate-400">· Rule: {{ $health['rule_version'] ?? 'N/A' }}</span>
            </div>
        @endif
    </header>

    <!-- Key Financial Metrics -->
    <section class="print-card rounded-xl bg-white p-6 shadow-sm border border-slate-200" aria-label="Financial Performance">
        <h2 class="text-base font-bold uppercase tracking-wide text-slate-900 border-b border-slate-100 pb-3">Financial Performance</h2>
        @php
            $budget = (float) ($project->budget_amount ?? 0);
            $income = (float) ($finance['income'] ?? 0);
            $expense = (float) ($finance['expense'] ?? 0);
            $net = (float) ($finance['net'] ?? 0);
            $burnRate = $budget > 0 ? min(100.0, max(0.0, ($expense / $budget) * 100)) : 0.0;
        @endphp
        <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4 text-center">
            <div class="rounded-lg bg-slate-50 p-3">
                <p class="text-xs font-bold uppercase text-slate-500">Planned Budget</p>
                <p class="mt-1 text-xl font-black text-slate-900">{{ number_format($budget, 2) }}</p>
            </div>
            <div class="rounded-lg bg-slate-50 p-3">
                <p class="text-xs font-bold uppercase text-slate-500">Posted Income</p>
                <p class="mt-1 text-xl font-black text-emerald-700">{{ number_format($income, 2) }}</p>
            </div>
            <div class="rounded-lg bg-slate-50 p-3">
                <p class="text-xs font-bold uppercase text-slate-500">Posted Expense</p>
                <p class="mt-1 text-xl font-black text-red-700">{{ number_format($expense, 2) }}</p>
            </div>
            <div class="rounded-lg bg-slate-50 p-3">
                <p class="text-xs font-bold uppercase text-slate-500">Net Balance</p>
                <p class="mt-1 text-xl font-black {{ $net >= 0 ? 'text-slate-900' : 'text-red-700' }}">{{ number_format($net, 2) }}</p>
            </div>
        </div>
        @if ($budget > 0)
            <div class="mt-4 space-y-1">
                <div class="flex justify-between text-xs font-bold text-slate-600">
                    <span>Budget Burn-down Rate</span>
                    <span>{{ number_format($burnRate, 1) }}% used</span>
                </div>
                <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full {{ $burnRate > 90 ? 'bg-red-500' : ($burnRate > 75 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ $burnRate }}%"></div>
                </div>
            </div>
        @endif
    </section>

    <!-- Schedule & Key Milestones -->
    <section class="print-card rounded-xl bg-white p-6 shadow-sm border border-slate-200" aria-label="Schedule & Milestones">
        <h2 class="text-base font-bold uppercase tracking-wide text-slate-900 border-b border-slate-100 pb-3">Schedule & Milestones</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-left text-xs">
                <thead class="bg-slate-50 font-bold uppercase text-slate-500">
                    <tr>
                        <th class="px-3 py-2">WBS</th>
                        <th class="px-3 py-2">Work Package</th>
                        <th class="px-3 py-2">Baseline Window</th>
                        <th class="px-3 py-2">Weight</th>
                        <th class="px-3 py-2">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($tasks as $task)
                        <tr>
                            <td class="px-3 py-2 font-mono font-bold">{{ $task->wbs_code }}</td>
                            <td class="px-3 py-2 font-semibold text-slate-900">{{ $task->name }}</td>
                            <td class="px-3 py-2 text-slate-600">{{ $task->baseline_start_date?->toDateString() }} → {{ $task->baseline_finish_date?->toDateString() }}</td>
                            <td class="px-3 py-2 text-slate-600">{{ number_format((float) $task->weight, 2) }}%</td>
                            <td class="px-3 py-2 font-bold {{ $task->status === 'COMPLETED' ? 'text-emerald-700' : 'text-slate-700' }}">{{ $task->status }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-4 text-center text-slate-500">No WBS tasks recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- Weekly Lookahead Schedule Summary -->
    @if (isset($lookahead))
        @php
            $wSummary = $lookahead['summary'];
            $wRate = (float) $wSummary['make_ready_rate'];
        @endphp
        <section class="print-card rounded-xl bg-white p-6 shadow-sm border border-slate-200" aria-label="Weekly Lookahead Summary">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-base font-bold uppercase tracking-wide text-slate-900">Weekly Lookahead (3-Week Window)</h2>
                    <p class="text-xs text-slate-500">Period: {{ $lookahead['window']['start_date'] }} → {{ $lookahead['window']['end_date'] }}</p>
                </div>
                <a href="{{ route('projects.lookahead', $project) }}" class="no-print text-xs font-bold text-cyan-700 hover:underline">Full Lookahead Board →</a>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4 text-center">
                <div class="rounded-lg bg-slate-50 p-3">
                    <p class="text-xs font-bold uppercase text-slate-500">Make-Ready Readiness</p>
                    <p class="mt-1 text-2xl font-black {{ $wRate >= 80 ? 'text-emerald-700' : ($wRate >= 60 ? 'text-amber-700' : 'text-red-700') }}">{{ number_format($wRate, 1) }}%</p>
                </div>
                <div class="rounded-lg bg-slate-50 p-3">
                    <p class="text-xs font-bold uppercase text-slate-500">Scheduled Tasks</p>
                    <p class="mt-1 text-2xl font-black text-slate-900">{{ $wSummary['total_tasks'] }}</p>
                </div>
                <div class="rounded-lg bg-slate-50 p-3">
                    <p class="text-xs font-bold uppercase text-emerald-600">Make-Ready (Sound)</p>
                    <p class="mt-1 text-2xl font-black text-emerald-600">{{ $wSummary['make_ready_count'] }}</p>
                </div>
                <div class="rounded-lg bg-slate-50 p-3">
                    <p class="text-xs font-bold uppercase text-amber-600">Constrained (Blocked)</p>
                    <p class="mt-1 text-2xl font-black text-amber-600">{{ $wSummary['constrained_count'] }}</p>
                </div>
            </div>
        </section>
    @endif

    <!-- Active Constraints & Site Impediments -->
    <section class="print-card rounded-xl bg-white p-6 shadow-sm border border-slate-200" aria-label="Active Constraints">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h2 class="text-base font-bold uppercase tracking-wide text-slate-900">Active Constraints & Site Impediments ({{ $constraints->count() }})</h2>
            <a href="{{ route('projects.constraints', $project) }}" class="no-print text-xs font-bold text-cyan-700 hover:underline">Manage Constraints →</a>
        </div>
        <div class="mt-4">
            @if ($constraints->isEmpty())
                <p class="text-xs text-slate-500">No active site constraints or impediments recorded.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 uppercase text-slate-600 font-semibold border-b border-slate-200">
                            <tr>
                                <th class="p-2">Type</th>
                                <th class="p-2">Title & Impact</th>
                                <th class="p-2">Severity</th>
                                <th class="p-2">Target Date</th>
                                <th class="p-2">Assignee</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($constraints as $c)
                                <tr>
                                    <td class="p-2 font-bold text-slate-700">{{ ucwords(str_replace('_', ' ', strtolower($c->type))) }}</td>
                                    <td class="p-2">
                                        <p class="font-bold text-slate-900">{{ $c->title }}</p>
                                        @if ($c->task)
                                            <p class="text-slate-500 text-[11px]">Task: [{{ $c->task->wbs_code }}] {{ $c->task->name }}</p>
                                        @endif
                                    </td>
                                    <td class="p-2 font-bold {{ $c->severity === 'CRITICAL' ? 'text-red-700' : ($c->severity === 'HIGH' ? 'text-orange-700' : 'text-slate-700') }}">{{ $c->severity }}</td>
                                    <td class="p-2 text-slate-600">{{ $c->target_resolution_date?->format('Y-m-d') ?? '—' }}</td>
                                    <td class="p-2 text-slate-600">{{ $c->assignee?->name ?? 'Unassigned' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>

    <!-- Traceability, Evidence & Audit -->
    <section class="print-card rounded-xl bg-white p-6 shadow-sm border border-slate-200" aria-label="Traceability & Evidence">
        <h2 class="text-base font-bold uppercase tracking-wide text-slate-900 border-b border-slate-100 pb-3">Traceability & Governance</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 text-xs">
            <div>
                <p class="font-bold text-slate-700">Verified Evidence Records ({{ count($evidenceDocuments) }})</p>
                <ul class="mt-2 space-y-1 text-slate-600">
                    @forelse ($evidenceDocuments as $doc)
                        <li class="flex justify-between border-b border-slate-50 pb-1">
                            <span class="truncate max-w-xs font-mono">{{ $doc->original_name ?? $doc->id }}</span>
                            <span class="font-bold uppercase text-slate-500">{{ $doc->review_state }}</span>
                        </li>
                    @empty
                        <li class="text-slate-400">No evidence documents attached.</li>
                    @endforelse
                </ul>
            </div>
            <div>
                <p class="font-bold text-slate-700">Recent Audit Trail ({{ count($auditEvents) }})</p>
                <ul class="mt-2 space-y-1 text-slate-600">
                    @forelse ($auditEvents as $event)
                        <li class="flex justify-between border-b border-slate-50 pb-1">
                            <span class="truncate max-w-xs">{{ $event->event_type ?? 'Event' }}</span>
                            <span class="text-slate-400">{{ $event->occurred_at?->toDateTimeString() ?? '' }}</span>
                        </li>
                    @empty
                        <li class="text-slate-400">No audit events recorded.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </section>

    <!-- Sign-off Document Footer -->
    <footer class="print-card rounded-xl bg-slate-50 p-4 text-center text-[11px] text-slate-500 border border-slate-200">
        <p class="font-bold text-slate-700 uppercase tracking-wider">Confidential · Construction Executive Dashboard</p>
        <p class="mt-0.5">This document reflects deterministic accepted health signals and verified financial/schedule records as of the calculation timestamp.</p>
    </footer>
</main>
</body>
</html>
