<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $project->code }} · Site Workforce & Heavy Machinery Utilization</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
<main class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">

    <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl bg-white p-4 shadow-sm border border-slate-200">
        <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
            <a class="text-cyan-700 hover:underline" href="{{ route('projects.portfolio') }}">← Portfolio</a>
            <span class="text-slate-400">·</span>
            <a class="text-cyan-700 hover:underline" href="{{ route('projects.constraints', $project) }}">Constraints</a>
            <span class="text-slate-400">·</span>
            <a class="text-blue-700 hover:underline" href="{{ route('projects.reports.daily', $project) }}">📅 Daily Report</a>
            <span class="text-slate-400">·</span>
            <a class="text-emerald-800 hover:underline" href="{{ route('projects.ipc.index', $project) }}">💰 IPC & Payment</a>
            <span class="text-slate-400">·</span>
            <a class="text-violet-700 hover:underline" href="{{ route('projects.vo.index', $project) }}">📑 Variation Orders</a>
            <span class="text-slate-400">·</span>
            <a class="text-rose-700 hover:underline" href="{{ route('projects.ncr.index', $project) }}">🛡️ QC & NCR</a>
            <span class="text-slate-400">·</span>
            <a class="text-amber-800 hover:underline" href="{{ route('projects.procurement.index', $project) }}">🧱 Procurement</a>
            <span class="text-slate-400">·</span>
            <a class="text-teal-800 hover:underline" href="{{ route('projects.hse.index', $project) }}">🦺 HSE & Permits</a>
            <span class="text-slate-400">·</span>
            <span class="text-sky-800 font-bold underline">👷 Workforce & Machinery</span>
        </div>
    </div>

    @if(session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-900">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200">
        <span class="rounded bg-sky-100 px-2.5 py-0.5 text-xs font-bold text-sky-800">Phase 6A + 6B · Field Resource Operations</span>
        <h1 class="mt-1 text-2xl font-black">Daily Workforce Attendance & Heavy Machinery Utilization</h1>
        <p class="text-xs text-slate-500">บันทึกกำลังพลรายวันแยกตามหมวดช่าง (6A) และชั่วโมงทำงานเครื่องจักรหนัก (6B) พร้อมปลดล็อก LABOR_CREW และ EQUIPMENT Constraint อัตโนมัติ</p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
        <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
            <div class="text-[11px] font-bold uppercase text-slate-400">Total Workers Logged</div>
            <div class="text-xl font-black">{{ number_format($stats['total_actual_workers']) }}</div>
            <div class="text-[10px] text-slate-400">Fulfillment {{ $stats['manpower_fulfillment_pct'] }}%</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
            <div class="text-[11px] font-bold uppercase text-slate-400">Cumulative Man-Hours</div>
            <div class="text-xl font-black text-sky-800">{{ number_format($stats['total_man_hours'], 1) }} <span class="text-xs font-normal">hrs</span></div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
            <div class="text-[11px] font-bold uppercase text-slate-400">Operational Machines</div>
            <div class="text-xl font-black text-emerald-700">{{ $stats['operational_machines'] }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
            <div class="text-[11px] font-bold uppercase text-slate-400">Breakdown / Maint.</div>
            <div class="text-xl font-black text-rose-600">{{ $stats['breakdown_machines'] }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
            <div class="text-[11px] font-bold uppercase text-slate-400">Fleet Availability</div>
            <div class="text-xl font-black text-cyan-800">{{ $stats['machine_availability_pct'] }}%</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm border border-amber-200 bg-amber-50/40">
            <div class="text-[11px] font-bold uppercase text-amber-800">Open Crew/Equip Constraints</div>
            <div class="text-xl font-black text-amber-900">{{ $stats['open_resource_constraints'] }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- 6A: Daily Manpower & Trade Attendance Form -->
        <form action="{{ route('projects.resources.manpower.store', $project) }}" method="POST" class="space-y-4 rounded-xl bg-white p-6 shadow-sm border border-slate-200">
            @csrf
            <div class="border-b border-slate-100 pb-3">
                <span class="text-xs font-bold uppercase text-sky-700">6A · Daily Manpower Log</span>
                <h2 class="text-lg font-bold text-slate-900">บันทึกกำลังพลหน้างานแยกตามหมวดช่าง</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700">Log Date *</label>
                    <input type="date" name="log_date" value="{{ now()->toDateString() }}" required class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Trade *</label>
                    <select name="trade" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs">
                        @foreach(\App\Models\SiteManpowerLog::TRADES as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Shift</label>
                    <select name="shift" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs">
                        <option value="DAY">DAY</option>
                        <option value="NIGHT">NIGHT</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700">Planned Headcount *</label>
                    <input type="number" min="0" name="planned_headcount" value="15" required class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Actual Headcount *</label>
                    <input type="number" min="0" name="actual_headcount" value="15" required class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Working Hours</label>
                    <input type="number" step="0.5" min="0" max="24" name="working_hours" value="8.0" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700">Linked WBS Task (Auto-resolves LABOR_CREW constraint)</label>
                    <select name="wbs_task_id" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs">
                        <option value="">— Optional —</option>
                        @foreach($tasks as $task)
                            <option value="{{ $task->id }}">{{ $task->wbs_code }} · {{ $task->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Subcontractor Company</label>
                    <input type="text" name="contractor_company" placeholder="e.g. Thai Steel Crew Ltd." class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs">
                </div>
            </div>

            <button type="submit" class="w-full rounded-lg bg-sky-600 px-4 py-2 text-xs font-bold text-white hover:bg-sky-700">+ Log Daily Manpower Attendance</button>
        </form>

        <!-- 6B: Heavy Machinery & Equipment Utilization Form -->
        <form action="{{ route('projects.resources.machinery.store', $project) }}" method="POST" class="space-y-4 rounded-xl bg-white p-6 shadow-sm border border-slate-200">
            @csrf
            <div class="border-b border-slate-100 pb-3">
                <span class="text-xs font-bold uppercase text-indigo-700">6B · Heavy Machinery Log</span>
                <h2 class="text-lg font-bold text-slate-900">บันทึกการใช้งานเครื่องจักรหนักและชั่วโมงทำงาน</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700">Log Date *</label>
                    <input type="date" name="log_date" value="{{ now()->toDateString() }}" required class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Equipment Code *</label>
                    <input type="text" name="equipment_code" required placeholder="e.g. TC-01" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Category *</label>
                    <select name="category" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs">
                        @foreach(\App\Models\SiteMachineryLog::CATEGORIES as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700">Equipment Name / Model *</label>
                    <input type="text" name="equipment_name" required placeholder="e.g. Potain Tower Crane MC310" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Status *</label>
                    <select name="status" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs font-bold">
                        @foreach(\App\Models\SiteMachineryLog::STATUSES as $st)
                            <option value="{{ $st }}">{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700">Operating Hours</label>
                    <input type="number" step="0.5" min="0" max="24" name="operating_hours" value="8.0" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Downtime Hours</label>
                    <input type="number" step="0.5" min="0" max="24" name="downtime_hours" value="0.0" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Linked WBS Task</label>
                    <select name="wbs_task_id" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs">
                        <option value="">— Optional —</option>
                        @foreach($tasks as $task)
                            <option value="{{ $task->id }}">{{ $task->wbs_code }} · {{ $task->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-xs font-bold text-white hover:bg-indigo-700">+ Log Heavy Machinery Status</button>
        </form>
    </div>

    <!-- 6A Table: Manpower Logs -->
    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-50 px-6 py-3 font-bold text-xs text-slate-800">6A · Daily Manpower Attendance Register</div>
        @if($manpowerLogs->isEmpty())
            <div class="p-8 text-center text-xs text-slate-500">ยังไม่มีบันทึกกำลังพลรายวัน (No manpower logs yet)</div>
        @else
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[10px] uppercase text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-2.5">Date / Shift</th>
                        <th class="px-6 py-2.5">Trade / Contractor</th>
                        <th class="px-6 py-2.5">WBS Task</th>
                        <th class="px-6 py-2.5 text-right">Actual / Planned</th>
                        <th class="px-6 py-2.5 text-right">Man-Hours</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($manpowerLogs as $mLog)
                        <tr>
                            <td class="px-6 py-3 font-mono">{{ $mLog->log_date->format('d/m/Y') }} · {{ $mLog->shift }}</td>
                            <td class="px-6 py-3"><div class="font-bold text-slate-900">{{ $mLog->trade }}</div><div class="text-[11px] text-slate-400">{{ $mLog->contractor_company ?: 'Direct' }}</div></td>
                            <td class="px-6 py-3">{{ $mLog->task ? $mLog->task->wbs_code.' · '.$mLog->task->name : '—' }}</td>
                            <td class="px-6 py-3 text-right font-mono font-bold">{{ $mLog->actual_headcount }} / {{ $mLog->planned_headcount }} ({{ $mLog->attendancePercent() }}%)</td>
                            <td class="px-6 py-3 text-right font-mono font-bold text-sky-800">{{ number_format($mLog->total_man_hours, 1) }} hrs</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- 6B Table: Machinery Logs -->
    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-50 px-6 py-3 font-bold text-xs text-slate-800">6B · Heavy Machinery Utilization Register</div>
        @if($machineryLogs->isEmpty())
            <div class="p-8 text-center text-xs text-slate-500">ยังไม่มีบันทึกการใช้งานเครื่องจักรหนัก (No machinery logs yet)</div>
        @else
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[10px] uppercase text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-2.5">Date</th>
                        <th class="px-6 py-2.5">Code / Equipment</th>
                        <th class="px-6 py-2.5">WBS Task</th>
                        <th class="px-6 py-2.5 text-center">Status</th>
                        <th class="px-6 py-2.5 text-right">Operating / Downtime</th>
                        <th class="px-6 py-2.5 text-right">Availability</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($machineryLogs as $eqLog)
                        <tr>
                            <td class="px-6 py-3 font-mono">{{ $eqLog->log_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-3"><div class="font-bold text-slate-900">{{ $eqLog->equipment_code }} · {{ $eqLog->equipment_name }}</div><div class="text-[11px] text-slate-400">{{ $eqLog->category }}</div></td>
                            <td class="px-6 py-3">{{ $eqLog->task ? $eqLog->task->wbs_code.' · '.$eqLog->task->name : '—' }}</td>
                            <td class="px-6 py-3 text-center"><span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ $eqLog->status === 'OPERATIONAL' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">{{ $eqLog->status }}</span></td>
                            <td class="px-6 py-3 text-right font-mono">{{ number_format($eqLog->operating_hours, 1) }}h / {{ number_format($eqLog->downtime_hours, 1) }}h</td>
                            <td class="px-6 py-3 text-right font-mono font-bold">{{ $eqLog->availabilityPercent() }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

</main>
</body>
</html>
