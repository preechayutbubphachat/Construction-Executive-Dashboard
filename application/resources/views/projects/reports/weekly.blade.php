<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $project->code }} · Weekly Executive Report (Week {{ $report['week_number'] }}, {{ $report['year'] }})</title>
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
            <a class="text-cyan-700 hover:underline" href="{{ route('projects.reports.daily', $project) }}">📅 Daily Report</a>
            <span class="text-slate-400">·</span>
            <a class="text-cyan-700 hover:underline" href="{{ route('projects.executive', $project) }}">Executive Health</a>
            <span class="text-slate-400">·</span>
            <a class="text-emerald-700 hover:underline" href="{{ route('projects.evm', $project) }}">EVM S-Curve 📈</a>
            <span class="text-slate-400">·</span>
            <a class="text-indigo-700 hover:underline" href="{{ route('projects.3d-twin', $project) }}">3D Twin 🌐</a>
            <span class="text-slate-400">·</span>
            <a class="text-slate-600 hover:underline" href="{{ route('projects.report', $project) }}">Briefing Overview</a>
        </div>

        <div class="flex items-center gap-2">
            <form method="GET" action="{{ route('projects.reports.weekly', $project) }}" class="flex items-center gap-1.5">
                <a href="{{ route('projects.reports.weekly', [$project, 'week' => $report['previous_week']]) }}" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" title="Previous Week">
                    &larr; Prev Week
                </a>
                <input type="date" name="week" value="{{ $report['start_date'] }}" onchange="this.form.submit()" class="px-2.5 py-1 rounded-lg border border-slate-300 text-xs font-mono font-bold text-slate-800 bg-white">
                <a href="{{ route('projects.reports.weekly', [$project, 'week' => $report['next_week']]) }}" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition" title="Next Week">
                    Next Week &rarr;
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

    {{-- Official Weekly Header Banner --}}
    <header class="print-card rounded-xl bg-white p-6 shadow-sm border border-slate-200">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-200 pb-5">
            <div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold uppercase tracking-wider bg-indigo-50 text-indigo-800 border border-indigo-200">
                    Executive Governance & Status Deliverable
                </span>
                <h1 class="mt-2 text-2xl font-black text-slate-900 sm:text-3xl">Weekly Executive Status Report</h1>
                <p class="text-sm font-semibold text-slate-600">รายงานสรุปผลการดำเนินงานและสถานะโครงการประจำสัปดาห์</p>
                <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-slate-600">
                    <span><strong>Project:</strong> {{ $project->name }} ({{ $project->code }})</span>
                    <span>&bull;</span>
                    <span><strong>Baseline:</strong> {{ $project->baseline_start_date?->toDateString() }} &rarr; {{ $project->baseline_finish_date?->toDateString() }}</span>
                </div>
            </div>

            <div class="text-right sm:border-l sm:border-slate-200 sm:pl-6">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Report Number</span>
                <span class="font-mono text-sm font-black text-slate-900 block">{{ $report['report_number'] }}</span>
                <div class="mt-2 text-xs">
                    <strong class="font-bold text-slate-800">Week {{ $report['week_number'] }}, {{ $report['year'] }}</strong>
                    <span class="text-slate-500 block font-mono text-[11px]">{{ $report['date_range_label'] }}</span>
                </div>

                @php
                    $hStatus = is_array($report['health']) && isset($report['health']['status']) ? $report['health']['status'] : 'UNKNOWN';
                @endphp
                <div class="mt-2 inline-block rounded-lg px-3 py-1 font-black text-xs {{ $hStatus === 'GREEN' ? 'bg-emerald-100 text-emerald-800' : ($hStatus === 'RED' ? 'bg-red-100 text-red-800' : ($hStatus === 'AMBER' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700')) }}">
                    OVERALL HEALTH: {{ $hStatus }}
                </div>
            </div>
        </div>

        @if (is_array($report['health']) && isset($report['health']['explanation']))
            <div class="mt-4 rounded-lg bg-slate-50 p-3 text-xs text-slate-700 border border-slate-200">
                <strong class="text-slate-900">Executive Health Diagnosis:</strong> {{ $report['health']['explanation'] }}
            </div>
        @endif
    </header>

    {{-- Section 1: Earned Value Management (EVM) Performance Snapshot --}}
    @php
        $evm = $report['evm'];
        $metrics = $evm['metrics'] ?? [];
        $indices = $evm['indices'] ?? [];
        $progress = $evm['progress'] ?? [];
        $forecasts = $evm['forecasts'] ?? [];
        $bac = (float) ($metrics['bac'] ?? 0.0);
        $pv = (float) ($metrics['pv'] ?? 0.0);
        $ev = (float) ($metrics['ev'] ?? 0.0);
        $ac = (float) ($metrics['ac'] ?? 0.0);
        $cpi = (float) ($indices['cpi'] ?? 1.0);
        $spi = (float) ($indices['spi'] ?? 1.0);
        $eac = (float) ($forecasts['eac'] ?? $bac);
        $vac = (float) ($forecasts['vac'] ?? 0.0);
        $plannedPercent = (float) ($progress['planned_percent'] ?? 0.0);
        $actualPercent = (float) ($progress['actual_percent'] ?? 0.0);
        $cpiGood = $cpi >= 1.0;
        $spiGood = $spi >= 1.0;
    @endphp
    <section class="print-card rounded-xl bg-white p-5 shadow-sm border border-slate-200" aria-label="EVM Performance">
        <div class="flex items-center justify-between border-b border-slate-200 pb-3 mb-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                <span>📈</span> 1. Earned Value Management (EVM) Performance (การวิเคราะห์มูลค่าที่ได้รับ)
            </h2>
            <a href="{{ route('projects.evm', $project) }}" class="text-xs font-bold text-emerald-700 hover:underline no-print">
                Interactive S-Curve &rarr;
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
            <div class="p-3 rounded-lg bg-slate-50 border border-slate-200">
                <span class="text-slate-500 block uppercase text-[10px] font-bold">Budget (BAC)</span>
                <p class="font-mono font-bold text-slate-900 text-base mt-0.5">${{ number_format($bac, 2) }}</p>
                <span class="text-slate-400 text-[10px]">Total baseline cost</span>
            </div>
            <div class="p-3 rounded-lg bg-slate-50 border border-slate-200">
                <span class="text-slate-500 block uppercase text-[10px] font-bold">Planned Value (PV)</span>
                <p class="font-mono font-bold text-slate-900 text-base mt-0.5">${{ number_format($pv, 2) }}</p>
                <span class="text-slate-400 text-[10px]">{{ number_format($plannedPercent, 1) }}% planned</span>
            </div>
            <div class="p-3 rounded-lg bg-slate-50 border border-slate-200">
                <span class="text-slate-500 block uppercase text-[10px] font-bold">Earned Value (EV)</span>
                <p class="font-mono font-bold text-slate-900 text-base mt-0.5">${{ number_format($ev, 2) }}</p>
                <span class="text-slate-400 text-[10px]">{{ number_format($actualPercent, 1) }}% actual work</span>
            </div>
            <div class="p-3 rounded-lg bg-slate-50 border border-slate-200">
                <span class="text-slate-500 block uppercase text-[10px] font-bold">Actual Cost (AC)</span>
                <p class="font-mono font-bold text-slate-900 text-base mt-0.5">${{ number_format($ac, 2) }}</p>
                <span class="text-slate-400 text-[10px]">Total posted spend</span>
            </div>
        </div>

        {{-- Indices and Variances --}}
        <div class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
            <div class="p-3 rounded-lg border {{ $cpiGood ? 'bg-emerald-50 border-emerald-200' : 'bg-rose-50 border-rose-200' }}">
                <span class="text-slate-500 block uppercase text-[10px] font-bold">Cost Index (CPI)</span>
                <p class="font-mono font-bold text-base mt-0.5 {{ $cpiGood ? 'text-emerald-800' : 'text-rose-800' }}">
                    {{ number_format($cpi, 3) }}
                </p>
                <span class="text-[10px] font-bold {{ $cpiGood ? 'text-emerald-700' : 'text-rose-700' }}">
                    {{ $cpiGood ? 'Under / On Budget' : 'Cost Overrun Risk' }}
                </span>
            </div>

            <div class="p-3 rounded-lg border {{ $spiGood ? 'bg-emerald-50 border-emerald-200' : 'bg-amber-50 border-amber-200' }}">
                <span class="text-slate-500 block uppercase text-[10px] font-bold">Schedule Index (SPI)</span>
                <p class="font-mono font-bold text-base mt-0.5 {{ $spiGood ? 'text-emerald-800' : 'text-amber-800' }}">
                    {{ number_format($spi, 3) }}
                </p>
                <span class="text-[10px] font-bold {{ $spiGood ? 'text-emerald-700' : 'text-amber-700' }}">
                    {{ $spiGood ? 'On / Ahead Schedule' : 'Behind Schedule' }}
                </span>
            </div>

            <div class="p-3 rounded-lg bg-slate-50 border border-slate-200">
                <span class="text-slate-500 block uppercase text-[10px] font-bold">Estimate at Completion (EAC)</span>
                <p class="font-mono font-bold text-slate-900 text-base mt-0.5">${{ number_format($eac, 2) }}</p>
                <span class="text-slate-400 text-[10px]">Forecasted total budget</span>
            </div>

            <div class="p-3 rounded-lg bg-slate-50 border border-slate-200">
                <span class="text-slate-500 block uppercase text-[10px] font-bold">Variance at Completion (VAC)</span>
                <p class="font-mono font-bold text-base mt-0.5 {{ $vac >= 0 ? 'text-emerald-800' : 'text-rose-800' }}">
                    ${{ number_format($vac, 2) }}
                </p>
                <span class="text-[10px] font-semibold {{ $vac >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                    {{ $vac >= 0 ? 'Projected Underspend' : 'Projected Deficit' }}
                </span>
            </div>
        </div>
    </section>

    {{-- Section 2: Weekly Lookahead Schedule Execution & Lean Soundness --}}
    @php
        $lookahead = $report['lookahead'];
        $lookSummary = $lookahead['summary'] ?? [];
    @endphp
    <section class="print-card rounded-xl bg-white p-5 shadow-sm border border-slate-200" aria-label="Weekly Lookahead">
        <div class="flex items-center justify-between border-b border-slate-200 pb-3 mb-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                <span>🗓️</span> 2. Lookahead Execution & Make-Ready Reliability (แผนงานล่วงหน้าและความพร้อม)
            </h2>
            <div class="flex items-center gap-2 text-xs">
                <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold font-mono">
                    {{ $lookSummary['make_ready_rate'] ?? 0 }}% Make-Ready Rate
                </span>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs mb-3">
            <div class="p-2.5 rounded bg-slate-50 border border-slate-200 text-center">
                <span class="text-slate-500 block">Lookahead Scope</span>
                <strong class="text-slate-900 text-sm font-bold">{{ $lookSummary['total_tasks'] ?? 0 }} Tasks</strong>
            </div>
            <div class="p-2.5 rounded bg-emerald-50 border border-emerald-200 text-center">
                <span class="text-emerald-700 block">Sound & Ready (Lean)</span>
                <strong class="text-emerald-800 text-sm font-bold">{{ $lookSummary['make_ready_count'] ?? 0 }} Tasks</strong>
            </div>
            <div class="p-2.5 rounded bg-amber-50 border border-amber-200 text-center">
                <span class="text-amber-700 block">Constrained Items</span>
                <strong class="text-amber-800 text-sm font-bold">{{ $lookSummary['constrained_count'] ?? 0 }} Tasks</strong>
            </div>
            <div class="p-2.5 rounded bg-rose-50 border border-rose-200 text-center">
                <span class="text-rose-700 block">Critical Path Blockers</span>
                <strong class="text-rose-800 text-sm font-bold">{{ $lookSummary['critical_blockers_count'] ?? 0 }} Blockers</strong>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-slate-600 font-bold">
                        <th class="py-2 px-3">WBS</th>
                        <th class="py-2 px-3">Task Name</th>
                        <th class="py-2 px-3">Schedule Window</th>
                        <th class="py-2 px-3 text-center">Reliability Status</th>
                        <th class="py-2 px-3 text-right">Progress</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse (collect($lookahead['tasks'] ?? [])->take(6) as $task)
                        <tr>
                            <td class="py-2 px-3 font-mono font-bold text-amber-700">{{ $task['wbs_code'] }}</td>
                            <td class="py-2 px-3 font-medium text-slate-800">{{ $task['name'] }}</td>
                            <td class="py-2 px-3 font-mono text-slate-600">{{ $task['baseline_start_date'] }} &rarr; {{ $task['baseline_finish_date'] }}</td>
                            <td class="py-2 px-3 text-center">
                                @if ($task['is_make_ready'])
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        SOUND / READY
                                    </span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">
                                        CONSTRAINED
                                    </span>
                                @endif
                            </td>
                            <td class="py-2 px-3 text-right font-mono text-slate-700">{{ number_format($task['progress_percent'], 1) }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center text-slate-400">No lookahead tasks defined.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Section 3: Financial Cash Flow Postings of the Week --}}
    @php
        $fin = $report['finance'];
    @endphp
    <section class="print-card rounded-xl bg-white p-5 shadow-sm border border-slate-200" aria-label="Financial Transactions">
        <div class="flex items-center justify-between border-b border-slate-200 pb-3 mb-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                <span>💵</span> 3. Weekly Financial Activity & Cash Flow (ธุรกรรมการเงินและกระแสเงินสดประจำสัปดาห์)
            </h2>
            <div class="text-xs font-bold text-slate-600">
                Net Cash Flow:
                <strong class="font-mono {{ $fin['net_cashflow_this_week'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                    ${{ number_format($fin['net_cashflow_this_week'], 2) }}
                </strong>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 text-xs mb-3">
            <div class="p-2.5 rounded bg-emerald-50 border border-emerald-200">
                <span class="text-emerald-700 block font-semibold">Total Weekly Inflow (Posted)</span>
                <strong class="font-mono text-emerald-800 text-sm font-bold">+${{ number_format($fin['inflow_this_week'], 2) }}</strong>
            </div>
            <div class="p-2.5 rounded bg-rose-50 border border-rose-200">
                <span class="text-rose-700 block font-semibold">Total Weekly Outflow (Posted)</span>
                <strong class="font-mono text-rose-800 text-sm font-bold">-${{ number_format($fin['outflow_this_week'], 2) }}</strong>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-slate-600 font-bold">
                        <th class="py-2 px-3">Date</th>
                        <th class="py-2 px-3">Description</th>
                        <th class="py-2 px-3">Direction</th>
                        <th class="py-2 px-3 text-right">Amount</th>
                        <th class="py-2 px-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($fin['transactions'] as $tx)
                        <tr>
                            <td class="py-2 px-3 font-mono text-slate-600">{{ $tx->created_at->format('d M') }}</td>
                            <td class="py-2 px-3 font-medium text-slate-900">{{ $tx->description }}</td>
                            <td class="py-2 px-3">
                                <span class="font-bold {{ $tx->direction === 'INFLOW' ? 'text-emerald-700' : 'text-rose-700' }}">
                                    {{ $tx->direction }}
                                </span>
                            </td>
                            <td class="py-2 px-3 text-right font-mono font-bold text-slate-800">${{ number_format($tx->amount, 2) }}</td>
                            <td class="py-2 px-3 text-center">
                                <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold {{ $tx->status === 'POSTED' ? 'bg-emerald-100 text-emerald-800' : ($tx->status === 'APPROVED' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700') }}">
                                    {{ $tx->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center text-slate-400">
                                No financial transactions recorded during this specific week.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Section 4: Site Photo Highlights of the Week (Pillar 3 Integration) --}}
    @php
        $photos = $report['photos'];
    @endphp
    <section class="print-card rounded-xl bg-white p-5 shadow-sm border border-slate-200" aria-label="Weekly Photo Highlights">
        <div class="flex items-center justify-between border-b border-slate-200 pb-3 mb-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                <span>📸</span> 4. Field Photo Evidence & Site Progress Highlights (ภาพถ่ายความคืบหน้ารอบสัปดาห์)
            </h2>
            <span class="text-xs font-bold text-slate-500">{{ $photos['total'] }} photos stamped</span>
        </div>

        @if ($photos['total'] > 0)
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach (collect($photos['items'])->take(8) as $photo)
                    @php
                        $cat = $photo->metadata['category'] ?? 'PROGRESS';
                    @endphp
                    <div class="rounded-lg border border-slate-200 overflow-hidden bg-slate-50">
                        <div class="h-28 bg-slate-200">
                            <img src="{{ route('projects.evidence.preview', [$project, $photo]) }}" alt="{{ $photo->caption }}" class="w-full h-full object-cover">
                        </div>
                        <div class="p-2 space-y-0.5 text-[10px]">
                            <p class="font-bold text-slate-900 truncate">{{ $photo->caption ?: ($photo->original_name ?: 'Site photo') }}</p>
                            <p class="text-slate-500">{{ $photo->captured_at ? $photo->captured_at->format('d M H:i') : $photo->created_at->format('d M') }}</p>
                            @if ($photo->gps_latitude && $photo->gps_longitude)
                                <p class="font-mono text-cyan-700 font-bold truncate">📍 {{ round($photo->gps_latitude, 3) }}, {{ round($photo->gps_longitude, 3) }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-6 text-center text-slate-400 text-xs">
                No field photos recorded during this week.
            </div>
        @endif
    </section>

    {{-- Section 5: Weekly Executive Sign-off --}}
    <section class="print-card rounded-xl bg-white p-6 shadow-sm border border-slate-200" aria-label="Weekly Executive Sign-off">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-4 flex items-center gap-1.5">
            <span>✍️</span> 5. Executive Endorsement & Certification (การรับรองรายงานระดับผู้บริหาร)
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 pt-4 text-xs">
            <div class="border-t border-slate-300 pt-3 text-center">
                <div class="h-12 flex items-center justify-center italic text-slate-400 font-serif">
                    Endorsed via Executive Dashboard
                </div>
                <p class="font-bold text-slate-900">{{ Auth::user()?->name ?? 'Project Director' }}</p>
                <p class="text-slate-500 text-[11px]">Construction Project Director</p>
                <p class="text-slate-400 text-[10px] font-mono mt-1">Week Ending: {{ $report['end_date'] }}</p>
            </div>

            <div class="border-t border-slate-300 pt-3 text-center">
                <div class="h-12 flex items-center justify-center italic text-slate-400 font-serif">
                    Employer Representative Acknowledged
                </div>
                <p class="font-bold text-slate-900">Client / Employer Representative</p>
                <p class="text-slate-500 text-[11px]">Program Governance Authority</p>
                <p class="text-slate-400 text-[10px] font-mono mt-1">Week Ending: {{ $report['end_date'] }}</p>
            </div>
        </div>
    </section>

</main>
</body>
</html>
