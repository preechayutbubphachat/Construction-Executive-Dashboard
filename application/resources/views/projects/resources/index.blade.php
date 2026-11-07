<x-layouts.app :project="$project" title="{{ $project->code }} · Labor & Workforce Management">
    <div class="space-y-6">

        <!-- Top Header & Action Bar (MindInventory Breadcrumb & Actions) -->
        <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-slate-400">
                    <a href="{{ route('projects.portfolio') }}" class="hover:text-slate-600 transition">Resources Management</a>
                    <span>/</span>
                    <span class="text-slate-700">Labor</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1">
                    Labor
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Workforce attendance, individual productivity & trade performance</p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-700 shadow-2xs hover:border-slate-300 transition">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Download CSV Report</span>
                </button>
                <a href="#manpower-form" class="inline-flex items-center gap-2 rounded-xl bg-safety-orange hover:bg-orange-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-orange-500/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    <span>+ Add New Labor</span>
                </a>
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

        <!-- 1. MindInventory 4 KPI Stat Cards (Image 2 & 4 Exact Layout) -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" aria-label="Labor KPI Metrics">
            
            <!-- Card 1: Manpower Onsite -->
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Manpower Onsite</span>
                    <p class="mt-2 text-3xl font-black text-slate-900">
                        32<span class="text-base font-semibold text-slate-400">/44</span>
                    </p>
                    <p class="mt-1 text-[11px] text-slate-400 font-medium">92% trade mobilization</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-orange-50 border border-orange-200/70 text-safety-orange flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>

            <!-- Card 2: Pending Tasks -->
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Pending Tasks</span>
                    <p class="mt-2 text-3xl font-black text-slate-900">10</p>
                    <p class="mt-1 text-[11px] text-rose-600 font-bold">3+ more pending from last week</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-orange-50 border border-orange-200/70 text-safety-orange flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </div>
            </div>

            <!-- Card 3: Inprogress Tasks -->
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Inprogress Tasks</span>
                    <p class="mt-2 text-3xl font-black text-slate-900">22</p>
                    <p class="mt-1 text-[11px] text-emerald-600 font-bold">7+ more completed from last week</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-orange-50 border border-orange-200/70 text-safety-orange flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
            </div>

            <!-- Card 4: Closed Deadlines -->
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Closed Deadlines</span>
                    <p class="mt-2 text-3xl font-black text-slate-900">75</p>
                    <p class="mt-1 text-[11px] text-emerald-600 font-bold">12+ more closed from last week</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-orange-50 border border-orange-200/70 text-safety-orange flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </section>

        <!-- 2. MindInventory Middle Row: Manpower Distribution & Individual Performance (Image 2 & 4) -->
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-5" aria-label="Manpower & Performance">
            
            <!-- Manpower Distribution & Team Performance (2 Cols) -->
            <div class="lg:col-span-2 rounded-2xl bg-white p-6 shadow-card border border-slate-200/80 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">Manpower Distribution</h2>
                        <p class="text-xs text-slate-500">Team performance across construction trades</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1.5 rounded-xl">Civil Works ▾</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <!-- Left Department Submenu List -->
                    <div class="space-y-2 text-xs font-semibold text-slate-600 border-r border-slate-100 pr-4">
                        <div class="p-2 rounded-xl bg-orange-50 text-safety-orange font-bold flex justify-between">
                            <span>Floor & Wall Tiles</span>
                            <span class="font-mono">45% Done</span>
                        </div>
                        <div class="p-2 rounded-xl hover:bg-slate-50 text-slate-600 flex justify-between">
                            <span>Glazing & Electrical</span>
                            <span class="font-mono text-slate-400">30%</span>
                        </div>
                        <div class="p-2 rounded-xl hover:bg-slate-50 text-slate-600 flex justify-between">
                            <span>RC Works</span>
                            <span class="font-mono text-slate-400">60%</span>
                        </div>
                        <div class="p-2 rounded-xl hover:bg-slate-50 text-slate-600 flex justify-between">
                            <span>Plumbing & Piping</span>
                            <span class="font-mono text-slate-400">80%</span>
                        </div>
                    </div>

                    <!-- Right Bar Chart + Trendline (Jan to Jul) -->
                    <div class="md:col-span-3 relative h-48 flex items-end justify-between px-3 pt-4">
                        @foreach ([
                            ['m' => 'Jan', 'val' => 35],
                            ['m' => 'Feb', 'val' => 75],
                            ['m' => 'Mar', 'val' => 90],
                            ['m' => 'Apr', 'val' => 85],
                            ['m' => 'May', 'val' => 45],
                            ['m' => 'Jun', 'val' => 70],
                            ['m' => 'Jul', 'val' => 95],
                        ] as $bar)
                            <div class="flex flex-col items-center gap-2 h-full justify-end">
                                <div class="w-8 rounded-t-lg bg-gradient-to-t from-orange-500 to-safety-orange transition-all hover:brightness-110" style="height: {{ $bar['val'] }}px;"></div>
                                <span class="text-[11px] font-mono text-slate-500">{{ $bar['m'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Individual Performance (1 Col) -->
            <div class="rounded-2xl bg-white p-6 shadow-card border border-slate-200/80 flex flex-col justify-between space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-base font-extrabold text-slate-900">Individual Performance</h2>
                    <span class="text-xs font-bold text-safety-orange bg-orange-50 px-2.5 py-0.5 rounded-full border border-orange-200">High Energy</span>
                </div>

                <!-- Highlight Announcement Banner -->
                <div class="rounded-xl bg-orange-50/70 border border-orange-200/80 p-3 text-xs space-y-1">
                    <div class="font-bold text-slate-900 flex items-center gap-1.5">
                        <span>🌟</span>
                        <span>4+ work performance showed improvement</span>
                    </div>
                    <p class="text-[11px] text-slate-600 leading-relaxed">
                        Productivity inefficiencies decreased by 25% after constructive work completion and deadlines met.
                    </p>
                </div>

                <!-- Worker Cards with Flame Scores -->
                <div class="space-y-2.5">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-slate-900 text-xs block">Kristin Watson</span>
                            <span class="text-[10px] text-slate-400">Civil Works · Deadlines Missed: 01</span>
                        </div>
                        <div class="flex items-center gap-1 font-mono text-xs font-bold text-safety-orange bg-orange-100/60 px-2.5 py-1 rounded-lg">
                            <span>🔥</span>
                            <span>72</span>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-slate-900 text-xs block">Jerome Bell</span>
                            <span class="text-[10px] text-slate-400">Civil Works · Deadlines Missed: 02</span>
                        </div>
                        <div class="flex items-center gap-1 font-mono text-xs font-bold text-safety-orange bg-orange-100/60 px-2.5 py-1 rounded-lg">
                            <span>🔥</span>
                            <span>83</span>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-3 text-center">
                    <a href="#" class="text-xs font-bold text-safety-orange hover:underline">View All Workers Directory →</a>
                </div>
            </div>
        </section>

        <!-- 3. MindInventory Bottom Row: Safety Inspection & Safety Hazards -->
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-5" aria-label="Safety Inspections and Hazards">
            
            <!-- Safety Inspection Table -->
            <div class="rounded-2xl bg-white p-6 shadow-card border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">Safety Inspection</h2>
                        <p class="text-xs text-slate-500">Stage checklist & completion percentage</p>
                    </div>
                    <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">OSHA Standard</span>
                </div>

                <div class="space-y-3">
                    @foreach ([
                        ['stage' => 'Test Plumbing Fixtures', 'tasks' => 134, 'pct' => 70],
                        ['stage' => 'Electrical Safety', 'tasks' => 103, 'pct' => 40],
                        ['stage' => 'Assess Flooring Conditions', 'tasks' => 37, 'pct' => 60],
                        ['stage' => 'Ensure Scaffolding Stability', 'tasks' => 32, 'pct' => 70],
                        ['stage' => 'Inspect Foundation Excavation', 'tasks' => 32, 'pct' => 50],
                        ['stage' => 'Review Emergency Response Plan', 'tasks' => 21, 'pct' => 60],
                    ] as $insp)
                        <div class="flex items-center justify-between gap-4 text-xs font-medium">
                            <span class="w-48 text-slate-800 font-bold truncate">{{ $insp['stage'] }}</span>
                            <span class="font-mono text-slate-500 text-[11px]">{{ $insp['tasks'] }} tasks</span>
                            <div class="flex-1 max-w-[140px] h-3 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-orange-400 to-safety-orange rounded-full text-[9px] text-white text-center font-bold font-mono leading-none flex items-center justify-center" style="width: {{ $insp['pct'] }}%;">
                                    {{ $insp['pct'] }}%
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Safety Hazards Multi-Bar Chart -->
            <div class="rounded-2xl bg-white p-6 shadow-card border border-slate-200/80 space-y-4 flex flex-col justify-between">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">Safety Hazards</h2>
                        <p class="text-xs text-slate-500">Field incident metrics & hazard categories</p>
                    </div>
                    <span class="text-xs font-bold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-xl">Last 5 Months</span>
                </div>

                <!-- Multi-bar Chart with Trendline Visual -->
                <div class="relative h-44 flex items-end justify-between px-3 pt-6">
                    @foreach ([
                        ['month' => 'Mar', 'fall' => 45, 'equip' => 70, 'elec' => 30],
                        ['month' => 'Apr', 'fall' => 80, 'equip' => 90, 'elec' => 45],
                        ['month' => 'May', 'fall' => 20, 'equip' => 15, 'elec' => 10],
                        ['month' => 'Jun', 'fall' => 60, 'equip' => 40, 'elec' => 50],
                        ['month' => 'Jul', 'fall' => 75, 'equip' => 85, 'elec' => 60],
                    ] as $bar)
                        <div class="flex flex-col items-center gap-1.5 h-full justify-end">
                            <div class="flex items-end gap-1">
                                <div class="w-2.5 rounded-t bg-amber-700" style="height: {{ $bar['fall'] }}px;"></div>
                                <div class="w-2.5 rounded-t bg-safety-orange" style="height: {{ $bar['equip'] }}px;"></div>
                                <div class="w-2.5 rounded-t bg-orange-200" style="height: {{ $bar['elec'] }}px;"></div>
                            </div>
                            <span class="text-[10px] font-mono text-slate-500">{{ $bar['month'] }}</span>
                        </div>
                    @endforeach
                </div>

                <!-- Legend -->
                <div class="flex flex-wrap items-center justify-between text-[10px] font-bold text-slate-500 border-t border-slate-100 pt-3">
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded bg-amber-700"></span> Fall</span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded bg-safety-orange"></span> Heavy Equip</span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded bg-orange-200"></span> Electrical</span>
                </div>
            </div>
        </section>

        <!-- 4. Twin Input Forms (6A Manpower & 6B Machinery) -->
        <div id="manpower-form" class="grid grid-cols-1 lg:grid-cols-2 gap-6 pt-2">
            <!-- 6A: Daily Manpower & Trade Attendance Form -->
            <form action="{{ route('projects.resources.manpower.store', $project) }}" method="POST" class="space-y-4 rounded-2xl bg-white p-6 shadow-card border border-slate-200/80">
                @csrf
                <div class="border-b border-slate-100 pb-3">
                    <span class="text-xs font-mono font-bold uppercase tracking-wider text-safety-orange bg-orange-50 border border-orange-200 px-2.5 py-0.5 rounded-full inline-block">
                        6A &middot; Daily Manpower Log
                    </span>
                    <h2 class="text-base font-black text-slate-900 mt-1">บันทึกกำลังพลหน้างานแยกตามหมวดช่าง</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Log Date *</label>
                        <input type="date" name="log_date" value="{{ now()->toDateString() }}" required class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs focus:border-safety-orange focus:ring-safety-orange">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Trade *</label>
                        <select name="trade" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-bold focus:border-safety-orange focus:ring-safety-orange">
                            @foreach(\App\Models\SiteManpowerLog::TRADES as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Shift</label>
                        <select name="shift" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs focus:border-safety-orange focus:ring-safety-orange">
                            <option value="DAY">DAY</option>
                            <option value="NIGHT">NIGHT</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Planned Headcount *</label>
                        <input type="number" min="0" name="planned_headcount" value="15" required class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-mono focus:border-safety-orange focus:ring-safety-orange">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Actual Headcount *</label>
                        <input type="number" min="0" name="actual_headcount" value="15" required class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-mono focus:border-safety-orange focus:ring-safety-orange">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Working Hours</label>
                        <input type="number" step="0.5" min="0" max="24" name="working_hours" value="8.0" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-mono focus:border-safety-orange focus:ring-safety-orange">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Linked WBS Task (Auto-resolves constraint)</label>
                        <select name="wbs_task_id" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs focus:border-safety-orange focus:ring-safety-orange">
                            <option value="">— Optional —</option>
                            @foreach($tasks as $task)
                                <option value="{{ $task->id }}">{{ $task->wbs_code }} · {{ $task->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Subcontractor Company</label>
                        <input type="text" name="contractor_company" placeholder="e.g. Thai Steel Crew Ltd." class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs focus:border-safety-orange focus:ring-safety-orange">
                    </div>
                </div>

                <button type="submit" class="w-full rounded-xl bg-safety-orange hover:bg-orange-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition">+ Log Daily Manpower Attendance</button>
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
                        <input type="date" name="log_date" value="{{ now()->toDateString() }}" required class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs focus:border-safety-orange focus:ring-safety-orange">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Equipment Code *</label>
                        <input type="text" name="equipment_code" required placeholder="e.g. TC-01" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-mono uppercase focus:border-safety-orange focus:ring-safety-orange">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Category *</label>
                        <select name="category" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs focus:border-safety-orange focus:ring-safety-orange">
                            @foreach(\App\Models\SiteMachineryLog::CATEGORIES as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Equipment Name / Model *</label>
                        <input type="text" name="equipment_name" required placeholder="e.g. Potain Tower Crane MC310" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs focus:border-safety-orange focus:ring-safety-orange">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Status *</label>
                        <select name="status" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-bold focus:border-safety-orange focus:ring-safety-orange">
                            @foreach(\App\Models\SiteMachineryLog::STATUSES as $st)
                                <option value="{{ $st }}">{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Operating Hours</label>
                        <input type="number" step="0.5" min="0" max="24" name="operating_hours" value="8.0" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-mono focus:border-safety-orange focus:ring-safety-orange">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Downtime Hours</label>
                        <input type="number" step="0.5" min="0" max="24" name="downtime_hours" value="0.0" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-mono focus:border-safety-orange focus:ring-safety-orange">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Linked WBS Task</label>
                        <select name="wbs_task_id" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs focus:border-safety-orange focus:ring-safety-orange">
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

        <!-- 5. 6A Table: Manpower Attendance Register -->
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
                                    <td class="px-6 py-4 text-right font-mono font-bold text-safety-orange">{{ number_format($mLog->total_man_hours, 1) }} hrs</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <!-- 6. 6B Table: Machinery Logs -->
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
