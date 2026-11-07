<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $project->code }} · Daily Site Report ({{ $report['date'] }})</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: #ffffff !important;
                color: #000000 !important;
                font-size: 10.5pt !important;
            }
            main {
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .print-card {
                box-shadow: none !important;
                border: 1px solid #cbd5e1 !important;
                break-inside: avoid;
                margin-bottom: 1rem !important;
            }
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased print:bg-white print:text-black">
<main class="mx-auto max-w-5xl space-y-5 px-4 py-6 sm:px-6 lg:px-8 print:max-w-none print:p-0">

    {{-- Top Action & Navigation Bar (Hidden in Print) --}}
    <div class="no-print flex flex-wrap items-center justify-between gap-4 rounded-xl bg-white p-4 shadow-sm border border-slate-200">
        <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
            <a class="text-cyan-700 hover:underline" href="{{ route('projects.portfolio') }}">← Portfolio</a>
            <span class="text-slate-400">·</span>
            <a class="text-cyan-700 hover:underline" href="{{ route('projects.site', $project) }}">📱 Site Capture</a>
            <span class="text-slate-400">·</span>
            <a class="text-cyan-700 hover:underline" href="{{ route('projects.executive', $project) }}">Executive Health</a>
            <span class="text-slate-400">·</span>
            <a class="text-indigo-700 hover:underline" href="{{ route('projects.reports.weekly', $project) }}">📊 Weekly Report &rarr;</a>
            <span class="text-slate-400">·</span>
            <a class="text-emerald-800 hover:underline font-bold" href="{{ route('projects.ipc.index', $project) }}">💰 IPC & Payment</a>
            <span class="text-slate-400">·</span>
            <a class="text-slate-600 hover:underline" href="{{ route('projects.report', $project) }}">Briefing Overview</a>
        </div>

        <div class="flex items-center gap-2">
            <form method="GET" action="{{ route('projects.reports.daily', $project) }}" class="flex items-center gap-1.5">
                <a href="{{ route('projects.reports.daily', [$project, 'date' => $report['previous_date']]) }}" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" title="Previous Day">
                    &larr; Prev
                </a>
                <input type="date" name="date" value="{{ $report['date'] }}" onchange="this.form.submit()" class="px-2.5 py-1 rounded-lg border border-slate-300 text-xs font-mono font-bold text-slate-800 bg-white">
                <a href="{{ route('projects.reports.daily', [$project, 'date' => $report['next_date']]) }}" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" title="Next Day">
                    Next &rarr;
                </a>
            </form>

            <button
                type="button"
                onclick="window.print()"
                class="flex items-center gap-1.5 rounded-lg bg-slate-900 px-4 py-1.5 text-xs font-bold text-white shadow hover:bg-slate-800 transition"
            >
                <svg class="h-4 w-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print / Save PDF
            </button>
        </div>
    </div>

    {{-- Official Report Header Banner --}}
    <header class="print-card rounded-xl bg-white p-6 shadow-sm border border-slate-200">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-200 pb-5">
            <div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold uppercase tracking-wider bg-amber-500/10 text-amber-800 border border-amber-500/20">
                    Official Construction Site Record
                </span>
                <h1 class="mt-2 text-2xl font-black text-slate-900 sm:text-3xl">Daily Site Construction Log Report</h1>
                <p class="text-sm font-semibold text-slate-600">รายงานประจำวันและบันทึกความคืบหน้าหน้างาน</p>
                <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-slate-600">
                    <span><strong>Project:</strong> {{ $project->name }} ({{ $project->code }})</span>
                    <span>&bull;</span>
                    <span><strong>Status:</strong> {{ $project->status }}</span>
                </div>
            </div>

            <div class="text-right sm:border-l sm:border-slate-200 sm:pl-6">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Document Reference</span>
                <span class="font-mono text-sm font-black text-slate-900 block">{{ $report['report_number'] }}</span>
                <div class="mt-2 font-mono text-xs">
                    <strong class="text-slate-800">{{ $report['formatted_date'] }}</strong>
                    <span class="text-slate-500 block">({{ $report['day_of_week'] }})</span>
                </div>
                @if ($report['is_today'])
                    <span class="mt-1.5 inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                        LIVE TODAY
                    </span>
                @endif
            </div>
        </div>

        {{-- Shift Metrics Snapshot --}}
        <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="p-3 rounded-lg bg-slate-50 border border-slate-200 text-center">
                <span class="text-[11px] font-semibold text-slate-500 uppercase">Active Work Items</span>
                <p class="text-xl font-bold text-slate-900 mt-0.5">{{ $report['tasks']['active_count'] }}</p>
            </div>
            <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-center">
                <span class="text-[11px] font-semibold text-emerald-700 uppercase">Field Photos Logged</span>
                <p class="text-xl font-bold text-emerald-800 mt-0.5">{{ $report['photos']['total'] }}</p>
            </div>
            <div class="p-3 rounded-lg bg-blue-50 border border-blue-200 text-center">
                <span class="text-[11px] font-semibold text-blue-700 uppercase">Total On-Site Manpower</span>
                <p class="text-xl font-bold text-blue-800 mt-0.5">{{ $report['manpower']['total_workers'] }}</p>
            </div>
            <div class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-center">
                <span class="text-[11px] font-semibold text-rose-700 uppercase">Open Site Constraints</span>
                <p class="text-xl font-bold text-rose-800 mt-0.5">{{ $report['constraints']['open_count'] }}</p>
            </div>
        </div>
    </header>

    {{-- Weather & Working Conditions --}}
    <section class="print-card rounded-xl bg-white p-5 shadow-sm border border-slate-200" aria-label="Weather and Working Conditions">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-3 flex items-center gap-1.5">
            <span>⛅</span> 1. Weather & Operating Conditions (สภาพอากาศและเวลาปฏิบัติงาน)
        </h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
            <div class="p-2.5 rounded bg-slate-50 border border-slate-200">
                <span class="text-slate-500 block">General Weather:</span>
                <strong class="text-slate-800">{{ $report['weather']['condition'] }}</strong>
            </div>
            <div class="p-2.5 rounded bg-slate-50 border border-slate-200">
                <span class="text-slate-500 block">Temperature:</span>
                <strong class="text-slate-800">{{ $report['weather']['temperature_low'] }} &ndash; {{ $report['weather']['temperature_high'] }}</strong>
            </div>
            <div class="p-2.5 rounded bg-slate-50 border border-slate-200">
                <span class="text-slate-500 block">Rainfall / Precipitation:</span>
                <strong class="text-slate-800">{{ $report['weather']['rainfall'] }}</strong>
            </div>
            <div class="p-2.5 rounded bg-slate-50 border border-slate-200">
                <span class="text-slate-500 block">Site Operating Hours:</span>
                <strong class="text-slate-800">{{ $report['weather']['working_hours'] }}</strong>
            </div>
        </div>
    </section>

    {{-- Work Execution & WBS Task Progress --}}
    <section class="print-card rounded-xl bg-white p-5 shadow-sm border border-slate-200" aria-label="Active Work Items">
        <div class="flex items-center justify-between border-b border-slate-200 pb-3 mb-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                <span>📋</span> 2. Work Execution & Daily Activity (รายการงานที่ปฏิบัติงานในวัน)
            </h2>
            <span class="text-xs font-bold text-slate-500">{{ $report['tasks']['active_count'] }} tasks active</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-slate-600 font-bold">
                        <th class="py-2.5 px-3">WBS Code</th>
                        <th class="py-2.5 px-3">Work Package / Task Description</th>
                        <th class="py-2.5 px-3">Planned Schedule</th>
                        <th class="py-2.5 px-3 text-center">Status</th>
                        <th class="py-2.5 px-3 text-right">Weight</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($report['tasks']['active_today'] as $task)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-2.5 px-3 font-mono font-bold text-amber-700">{{ $task->wbs_code }}</td>
                            <td class="py-2.5 px-3 font-medium text-slate-900">{{ $task->name }}</td>
                            <td class="py-2.5 px-3 font-mono text-slate-600">
                                {{ $task->baseline_start_date }} &rarr; {{ $task->baseline_finish_date }}
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold {{ $task->status === 'COMPLETED' ? 'bg-emerald-100 text-emerald-800' : ($task->status === 'IN_PROGRESS' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700') }}">
                                    {{ $task->status }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 font-mono text-right text-slate-700">{{ $task->weight ? number_format($task->weight, 1).'%' : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400">
                                No active leaf tasks scheduled on this specific date.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Manpower & Heavy Machinery Deployment --}}
    <section class="print-card rounded-xl bg-white p-5 shadow-sm border border-slate-200" aria-label="Manpower and Machinery">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-3 flex items-center gap-1.5">
            <span>👷</span> 3. Site Manpower & Equipment Deployment (กำลังพลและเครื่องจักรหน้างาน)
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Manpower Table --}}
            <div class="rounded-lg border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 px-3 py-2 border-b border-slate-200 text-xs font-bold text-slate-700 flex justify-between">
                    <span>Labor & Supervision Roster</span>
                    <span class="text-blue-700">Total: {{ $report['manpower']['total_workers'] }} personnel</span>
                </div>
                <table class="w-full text-xs divide-y divide-slate-100">
                    <tbody>
                        <tr class="px-3 py-1.5 flex justify-between"><td class="text-slate-600">Site Engineers & Supervisors:</td><td class="font-bold text-slate-800">{{ $report['manpower']['site_engineers'] }}</td></tr>
                        <tr class="px-3 py-1.5 flex justify-between"><td class="text-slate-600">Safety Officers:</td><td class="font-bold text-slate-800">{{ $report['manpower']['safety_officers'] }}</td></tr>
                        <tr class="px-3 py-1.5 flex justify-between"><td class="text-slate-600">Site Foremen:</td><td class="font-bold text-slate-800">{{ $report['manpower']['foremen'] }}</td></tr>
                        <tr class="px-3 py-1.5 flex justify-between"><td class="text-slate-600">Skilled Trades (Carpenters, Rebar, Welders):</td><td class="font-bold text-slate-800">{{ $report['manpower']['skilled_laborers'] }}</td></tr>
                        <tr class="px-3 py-1.5 flex justify-between"><td class="text-slate-600">General Construction Laborers:</td><td class="font-bold text-slate-800">{{ $report['manpower']['general_laborers'] }}</td></tr>
                    </tbody>
                </table>
            </div>

            {{-- Machinery Table --}}
            <div class="rounded-lg border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 px-3 py-2 border-b border-slate-200 text-xs font-bold text-slate-700">
                    Heavy Machinery & Equipment Log
                </div>
                <table class="w-full text-xs divide-y divide-slate-100">
                    <thead>
                        <tr class="text-slate-500 bg-slate-50/50">
                            <th class="py-1 px-3 text-left">Equipment</th>
                            <th class="py-1 px-2 text-center">Units</th>
                            <th class="py-1 px-2 text-center">Status</th>
                            <th class="py-1 px-3 text-right">Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($report['equipment'] as $eq)
                            <tr>
                                <td class="py-1.5 px-3 font-medium text-slate-800">{{ $eq['name'] }}</td>
                                <td class="py-1.5 px-2 text-center font-bold text-slate-700">{{ $eq['qty'] }}</td>
                                <td class="py-1.5 px-2 text-center">
                                    <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        {{ $eq['status'] }}
                                    </span>
                                </td>
                                <td class="py-1.5 px-3 text-right font-mono text-slate-700">{{ $eq['hours'] }}h</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- Field Photo Evidence for the Day (Pillar 3 Integration) --}}
    <section class="print-card rounded-xl bg-white p-5 shadow-sm border border-slate-200" aria-label="Field Photo Evidence">
        <div class="flex items-center justify-between border-b border-slate-200 pb-3 mb-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                <span>📸</span> 4. Field Photo Evidence & Inspection Telemetry (ภาพถ่ายตรวจรับงานพร้อมลายน้ำ GPS)
            </h2>
            <span class="text-xs font-bold text-slate-500">{{ $report['photos']['total'] }} photos stamped</span>
        </div>

        @if ($report['photos']['total'] > 0)
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @foreach ($report['photos']['items'] as $photo)
                    @php
                        $cat = $photo->metadata['category'] ?? 'PROGRESS';
                        $catColor = match ($cat) {
                            'DEFECT' => 'bg-rose-100 text-rose-800 border-rose-300',
                            'QUALITY_CHECK' => 'bg-cyan-100 text-cyan-800 border-cyan-300',
                            'SAFETY' => 'bg-amber-100 text-amber-800 border-amber-300',
                            default => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                        };
                    @endphp
                    <div class="rounded-lg border border-slate-200 overflow-hidden bg-slate-50 flex flex-col justify-between">
                        <div class="relative h-32 bg-slate-200">
                            <img src="{{ route('projects.evidence.preview', [$project, $photo]) }}" alt="{{ $photo->caption ?: 'Field Evidence' }}" class="w-full h-full object-cover">
                            <span class="absolute top-1.5 left-1.5 px-1.5 py-0.5 rounded text-[9px] font-bold uppercase border {{ $catColor }} bg-white/90">
                                {{ $cat }}
                            </span>
                        </div>
                        <div class="p-2 space-y-1 text-[11px]">
                            <p class="font-bold text-slate-900 truncate">{{ $photo->caption ?: ($photo->original_name ?: 'Site photo') }}</p>
                            @if ($photo->task)
                                <p class="text-slate-500 truncate">WBS: {{ $photo->task->wbs_code }}</p>
                            @endif
                            <div class="flex items-center justify-between text-[10px] text-slate-500 pt-1 border-t border-slate-200">
                                <span>{{ $photo->captured_at ? $photo->captured_at->format('H:i T') : $photo->created_at->format('H:i') }}</span>
                                @if ($photo->gps_latitude && $photo->gps_longitude)
                                    <span class="font-mono font-bold text-cyan-700">📍 {{ round($photo->gps_latitude, 3) }}, {{ round($photo->gps_longitude, 3) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-8 text-center text-slate-400 text-xs">
                No field photos recorded for this specific date. Use the Mobile Site Quick-Capture view to snap and watermark site progress.
            </div>
        @endif
    </section>

    {{-- Site Constraints, Blockers & Safety Incidents --}}
    <section class="print-card rounded-xl bg-white p-5 shadow-sm border border-slate-200" aria-label="Site Constraints and Blockers">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-3 flex items-center gap-1.5">
            <span>⚠️</span> 5. Site Constraints, Defects & Open Blockers (อุปสรรคและประเด็นติดขัดหน้างาน)
        </h2>

        <div class="space-y-3">
            @if ($report['constraints']['logged_today']->isNotEmpty())
                <div class="rounded-lg border border-amber-300 bg-amber-50/50 p-3">
                    <h3 class="text-xs font-bold text-amber-900 mb-2">New Blockers Logged Today ({{ $report['constraints']['logged_today']->count() }}):</h3>
                    <ul class="space-y-1.5 text-xs">
                        @foreach ($report['constraints']['logged_today'] as $c)
                            <li class="flex items-start gap-2">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-200 text-amber-900 shrink-0">{{ $c->severity }}</span>
                                <span class="font-bold text-slate-800">[{{ $c->type }}] {{ $c->title }}</span>
                                @if ($c->task)
                                    <span class="text-slate-500">(Task: {{ $c->task->wbs_code }})</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="rounded-lg border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 px-3 py-2 border-b border-slate-200 text-xs font-bold text-slate-700 flex justify-between">
                    <span>Active Open Site Constraints</span>
                    <span class="text-rose-700 font-bold">{{ $report['constraints']['open_count'] }} currently pending resolution</span>
                </div>
                <div class="divide-y divide-slate-100 text-xs">
                    @forelse ($report['constraints']['active_open'] as $c)
                        <div class="p-2.5 flex items-center justify-between gap-3">
                            <div>
                                <span class="font-bold text-slate-900">[{{ $c->type }}] {{ $c->title }}</span>
                                @if ($c->description)
                                    <p class="text-slate-500 text-[11px] mt-0.5">{{ $c->description }}</p>
                                @endif
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $c->severity === 'CRITICAL' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $c->severity }}
                            </span>
                        </div>
                    @empty
                        <div class="p-4 text-center text-slate-400">
                            Zero open constraints on site. Operations completely clear.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    {{-- Official Sign-Off and Certification Block --}}
    <section class="print-card rounded-xl bg-white p-6 shadow-sm border border-slate-200" aria-label="Official Verification Sign-off">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-4 flex items-center gap-1.5">
            <span>✍️</span> 6. Official Sign-off & Verification (การตรวจรับรองรายงาน)
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 pt-4 text-xs">
            <div class="border-t border-slate-300 pt-3 text-center">
                <div class="h-12 flex items-center justify-center italic text-slate-400 font-serif">
                    Digitally Verified
                </div>
                <p class="font-bold text-slate-900">{{ Auth::user()?->name ?? 'Site Supervisor' }}</p>
                <p class="text-slate-500 text-[11px]">Site Engineer / Supervisor</p>
                <p class="text-slate-400 text-[10px] font-mono mt-1">Date: {{ $report['date'] }}</p>
            </div>

            <div class="border-t border-slate-300 pt-3 text-center">
                <div class="h-12 flex items-center justify-center italic text-slate-400 font-serif">
                    Pending Inspection
                </div>
                <p class="font-bold text-slate-900">Lead Safety Officer</p>
                <p class="text-slate-500 text-[11px]">Safety & Environmental Division</p>
                <p class="text-slate-400 text-[10px] font-mono mt-1">Date: {{ $report['date'] }}</p>
            </div>

            <div class="border-t border-slate-300 pt-3 text-center">
                <div class="h-12 flex items-center justify-center italic text-slate-400 font-serif">
                    Executive Review
                </div>
                <p class="font-bold text-slate-900">Project Director / Manager</p>
                <p class="text-slate-500 text-[11px]">Construction Management</p>
                <p class="text-slate-400 text-[10px] font-mono mt-1">Date: {{ $report['date'] }}</p>
            </div>
        </div>
    </section>

</main>
</body>
</html>
