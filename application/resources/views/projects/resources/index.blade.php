<x-layouts.app :project="$project" title="{{ $project->code }} · Site Workforce & Heavy Machinery Utilization">
    <div class="space-y-6">

        <!-- Top Header Bar -->
        <header class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200/80 pb-5">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold">
                    <a class="text-safety-600 hover:text-safety-700 hover:underline transition" href="{{ route('projects.portfolio') }}">&larr; Back to Portfolio</a>
                    <span class="text-slate-300">&bull;</span>
                    <a class="text-slate-600 hover:text-slate-900 hover:underline transition" href="{{ route('projects.executive', $project) }}">Executive Health</a>
                    <span class="text-slate-300">&bull;</span>
                    <a class="text-slate-600 hover:text-slate-900 hover:underline transition" href="{{ route('projects.constraints', $project) }}">Constraints</a>
                    <span class="text-slate-300">&bull;</span>
                    <a class="text-slate-600 hover:text-slate-900 hover:underline transition" href="{{ route('projects.reports.daily', $project) }}">Daily Report 📅</a>
                    <span class="text-slate-300">&bull;</span>
                    <a class="text-slate-600 hover:text-slate-900 hover:underline transition" href="{{ route('projects.procurement.index', $project) }}">Procurement</a>
                    <span class="text-slate-300">&bull;</span>
                    <a class="text-teal-700 hover:underline transition" href="{{ route('projects.hse.index', $project) }}">HSE & Safety</a>
                </div>
                <div class="flex items-center gap-2.5 mt-2">
                    <span class="px-2.5 py-0.5 rounded text-xs font-mono font-bold bg-orange-100 text-safety-600 border border-orange-200/70">
                        {{ $project->code }}
                    </span>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Daily Workforce Attendance & Heavy Machinery Utilization</h1>
                </div>
                <p class="text-xs text-slate-500 mt-1">บันทึกกำลังพลรายวันแยกตามหมวดช่าง (6A) และชั่วโมงทำงานเครื่องจักรหนัก (6B) พร้อมปลดล็อก LABOR_CREW และ EQUIPMENT Constraint อัตโนมัติ</p>
            </div>
        </header>

        @if(session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-900 shadow-sm">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800 shadow-sm">
                @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif

        <!-- KPI Metrics Grid -->
        <section class="grid grid-cols-2 lg:grid-cols-6 gap-4" aria-label="Resource Operations Metrics">
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Workers Logged</span>
                <div class="mt-2 text-2xl font-black text-slate-900">{{ number_format($stats['total_actual_workers']) }}</div>
                <span class="text-[10px] text-slate-400">Fulfillment {{ $stats['manpower_fulfillment_pct'] }}%</span>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Cumulative Man-Hours</span>
                <div class="mt-2 text-2xl font-black text-safety-600">{{ number_format($stats['total_man_hours'], 1) }} <span class="text-xs font-normal text-slate-500">hrs</span></div>
                <span class="text-[10px] text-slate-400">Active site effort</span>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Operational Machines</span>
                <div class="mt-2 text-2xl font-black text-emerald-700">{{ $stats['operational_machines'] }}</div>
                <span class="text-[10px] text-slate-400">Productive units</span>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-card border border-rose-200 bg-rose-50/20">
                <span class="text-[11px] font-bold uppercase tracking-wider text-rose-700">Breakdown / Maint.</span>
                <div class="mt-2 text-2xl font-black text-rose-700">{{ $stats['breakdown_machines'] }}</div>
                <span class="text-[10px] text-rose-600 font-semibold">Idle units</span>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Fleet Availability</span>
                <div class="mt-2 text-2xl font-black text-slate-900">{{ $stats['machine_availability_pct'] }}%</div>
                <span class="text-[10px] text-slate-400">Readiness rate</span>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-card border border-orange-200 bg-orange-50/20">
                <span class="text-[11px] font-bold uppercase tracking-wider text-safety-700">Open Resource Constraints</span>
                <div class="mt-2 text-2xl font-black text-safety-600">{{ $stats['open_resource_constraints'] }}</div>
                <span class="text-[10px] text-safety-700 font-semibold">Crew / Equipment</span>
            </div>
        </section>

        <!-- Twin Input Forms (6A Manpower & 6B Machinery) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- 6A: Daily Manpower & Trade Attendance Form -->
            <form action="{{ route('projects.resources.manpower.store', $project) }}" method="POST" class="space-y-4 rounded-2xl bg-white p-6 shadow-card border border-slate-200/80">
                @csrf
                <div class="border-b border-slate-100 pb-3">
                    <span class="text-xs font-mono font-bold uppercase tracking-wider text-safety-600 bg-orange-100 border border-orange-200/70 px-2.5 py-0.5 rounded-full inline-block">
                        6A &middot; Daily Manpower Log
                    </span>
                    <h2 class="text-base font-black text-slate-900 mt-1">บันทึกกำลังพลหน้างานแยกตามหมวดช่าง</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Log Date *</label>
                        <input type="date" name="log_date" value="{{ now()->toDateString() }}" required class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs focus:border-safety-500 focus:ring-safety-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Trade *</label>
                        <select name="trade" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-bold focus:border-safety-500 focus:ring-safety-500">
                            @foreach(\App\Models\SiteManpowerLog::TRADES as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Shift</label>
                        <select name="shift" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs focus:border-safety-500 focus:ring-safety-500">
                            <option value="DAY">DAY</option>
                            <option value="NIGHT">NIGHT</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Planned Headcount *</label>
                        <input type="number" min="0" name="planned_headcount" value="15" required class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-mono focus:border-safety-500 focus:ring-safety-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Actual Headcount *</label>
                        <input type="number" min="0" name="actual_headcount" value="15" required class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-mono focus:border-safety-500 focus:ring-safety-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Working Hours</label>
                        <input type="number" step="0.5" min="0" max="24" name="working_hours" value="8.0" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-mono focus:border-safety-500 focus:ring-safety-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Linked WBS Task (Auto-resolves constraint)</label>
                        <select name="wbs_task_id" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs focus:border-safety-500 focus:ring-safety-500">
                            <option value="">— Optional —</option>
                            @foreach($tasks as $task)
                                <option value="{{ $task->id }}">{{ $task->wbs_code }} · {{ $task->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Subcontractor Company</label>
                        <input type="text" name="contractor_company" placeholder="e.g. Thai Steel Crew Ltd." class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs focus:border-safety-500 focus:ring-safety-500">
                    </div>
                </div>

                <button type="submit" class="w-full rounded-xl bg-safety-600 hover:bg-safety-700 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition">+ Log Daily Manpower Attendance</button>
            </form>

            <!-- 6B: Heavy Machinery & Equipment Utilization Form -->
            <form action="{{ route('projects.resources.machinery.store', $project) }}" method="POST" class="space-y-4 rounded-2xl bg-white p-6 shadow-card border border-slate-200/80">
                @csrf
                <div class="border-b border-slate-100 pb-3">
                    <span class="text-xs font-mono font-bold uppercase tracking-wider text-indigo-700 bg-indigo-50 border border-indigo-200 px-2.5 py-0.5 rounded-full inline-block">
                        6B &middot; Heavy Machinery Log
                    </span>
                    <h2 class="text-base font-black text-slate-900 mt-1">บันทึกการใช้งานเครื่องจักรหนักและชั่วโมงทำงาน</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Log Date *</label>
                        <input type="date" name="log_date" value="{{ now()->toDateString() }}" required class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs focus:border-safety-500 focus:ring-safety-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Equipment Code *</label>
                        <input type="text" name="equipment_code" required placeholder="e.g. TC-01" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-mono uppercase focus:border-safety-500 focus:ring-safety-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Category *</label>
                        <select name="category" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs focus:border-safety-500 focus:ring-safety-500">
                            @foreach(\App\Models\SiteMachineryLog::CATEGORIES as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Equipment Name / Model *</label>
                        <input type="text" name="equipment_name" required placeholder="e.g. Potain Tower Crane MC310" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs focus:border-safety-500 focus:ring-safety-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Status *</label>
                        <select name="status" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-bold focus:border-safety-500 focus:ring-safety-500">
                            @foreach(\App\Models\SiteMachineryLog::STATUSES as $st)
                                <option value="{{ $st }}">{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Operating Hours</label>
                        <input type="number" step="0.5" min="0" max="24" name="operating_hours" value="8.0" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-mono focus:border-safety-500 focus:ring-safety-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Downtime Hours</label>
                        <input type="number" step="0.5" min="0" max="24" name="downtime_hours" value="0.0" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-mono focus:border-safety-500 focus:ring-safety-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Linked WBS Task</label>
                        <select name="wbs_task_id" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs focus:border-safety-500 focus:ring-safety-500">
                            <option value="">— Optional —</option>
                            @foreach($tasks as $task)
                                <option value="{{ $task->id }}">{{ $task->wbs_code }} · {{ $task->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <button type="submit" class="w-full rounded-xl bg-slate-900 hover:bg-slate-800 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition">+ Log Heavy Machinery Status</button>
            </form>
        </div>

        <!-- 6A Table: Manpower Attendance Register -->
        <section class="rounded-2xl bg-white shadow-card border border-slate-200/80 overflow-hidden">
            <div class="border-b border-slate-200/80 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">6A &middot; Daily Manpower Attendance Register</h2>
                    <p class="text-[11px] text-slate-500">Site trade workforce mobilization, contractor headcount and shifts</p>
                </div>
                <span class="text-xs text-slate-500 font-mono font-bold">{{ $manpowerLogs->count() }} Records</span>
            </div>
            @if($manpowerLogs->isEmpty())
                <div class="p-12 text-center text-xs text-slate-500">ยังไม่มีบันทึกกำลังพลรายวัน (No manpower logs yet)</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 font-bold border-b border-slate-200/80">
                            <tr>
                                <th class="px-6 py-3.5">Date / Shift</th>
                                <th class="px-6 py-3.5">Trade / Contractor</th>
                                <th class="px-6 py-3.5">WBS Task</th>
                                <th class="px-6 py-3.5 text-right">Actual / Planned</th>
                                <th class="px-6 py-3.5 text-right">Man-Hours</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach($manpowerLogs as $mLog)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-6 py-4 font-mono">{{ $mLog->log_date->format('d/m/Y') }} &middot; {{ $mLog->shift }}</td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-900">{{ $mLog->trade }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $mLog->contractor_company ?: 'Direct Labor' }}</div>
                                    </td>
                                    <td class="px-6 py-4">{{ $mLog->task ? $mLog->task->wbs_code.' · '.$mLog->task->name : '—' }}</td>
                                    <td class="px-6 py-4 text-right font-mono font-bold text-slate-900">{{ $mLog->actual_headcount }} / {{ $mLog->planned_headcount }} ({{ $mLog->attendancePercent() }}%)</td>
                                    <td class="px-6 py-4 text-right font-mono font-bold text-safety-600">{{ number_format($mLog->total_man_hours, 1) }} hrs</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <!-- 6B Table: Machinery Logs -->
        <section class="rounded-2xl bg-white shadow-card border border-slate-200/80 overflow-hidden">
            <div class="border-b border-slate-200/80 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">6B &middot; Heavy Machinery Utilization Register</h2>
                    <p class="text-[11px] text-slate-500">Tower cranes, excavators, piling rigs operational availability</p>
                </div>
                <span class="text-xs text-slate-500 font-mono font-bold">{{ $machineryLogs->count() }} Records</span>
            </div>
            @if($machineryLogs->isEmpty())
                <div class="p-12 text-center text-xs text-slate-500">ยังไม่มีบันทึกการใช้งานเครื่องจักรหนัก (No machinery logs yet)</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 font-bold border-b border-slate-200/80">
                            <tr>
                                <th class="px-6 py-3.5">Date</th>
                                <th class="px-6 py-3.5">Code / Equipment</th>
                                <th class="px-6 py-3.5">WBS Task</th>
                                <th class="px-6 py-3.5 text-center">Status</th>
                                <th class="px-6 py-3.5 text-right">Operating / Downtime</th>
                                <th class="px-6 py-3.5 text-right">Availability</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach($machineryLogs as $eqLog)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-6 py-4 font-mono">{{ $eqLog->log_date->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-900">{{ $eqLog->equipment_code }} &middot; {{ $eqLog->equipment_name }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $eqLog->category }}</div>
                                    </td>
                                    <td class="px-6 py-4">{{ $eqLog->task ? $eqLog->task->wbs_code.' · '.$eqLog->task->name : '—' }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-block rounded-full border px-2.5 py-0.5 text-[10px] font-bold {{ $eqLog->status === 'OPERATIONAL' ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-rose-100 text-rose-800 border-rose-200' }}">
                                            {{ $eqLog->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-mono">{{ number_format($eqLog->operating_hours, 1) }}h / {{ number_format($eqLog->downtime_hours, 1) }}h</td>
                                    <td class="px-6 py-4 text-right font-mono font-bold text-slate-900">{{ $eqLog->availabilityPercent() }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-layouts.app>
