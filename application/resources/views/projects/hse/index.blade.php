<x-layouts.app :project="$project" title="{{ $project->code }} · HSE Safety Command & Permit-to-Work">
    <div class="space-y-6">
        <!-- Project Sub-navigation Ribbon (Unified MindInventory Tabs) -->
        <x-ced.project-subnav :project="$project" active="projects.hse.index" />

        <!-- Sub-Header & Navigation (MindInventory Style) -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-slate-200">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Phase 6C · HSE Safety & Operations Command
                    </span>
                    <span class="text-xs font-mono font-bold text-slate-500">{{ $project->code }}</span>
                </div>
                <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 flex items-center gap-2.5">
                    <span>🦺</span>
                    <span>ศูนย์ควบคุมความปลอดภัยและชั่วโมงการทำงาน HSE Safety Command & Safe Man-Hours</span>
                </h1>
                <p class="mt-1 text-xs text-slate-500">
                    ติดตามชั่วโมงการทำงานสะสมปลอดอุบัติเหตุ (Safe Man-Hours), บันทึก Toolbox Talk / Near-Miss / LTI และอนุมัติใบอนุญาตทำงานเสี่ยงอันตราย (Permit-to-Work: PTW)
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('projects.resources.index', $project) }}"
                   class="px-3.5 py-2 text-xs font-semibold rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 shadow-2xs transition">
                    👷 กำลังคนและเครื่องจักร (Workforce & Machinery)
                </a>
                <a href="{{ route('projects.constraints', $project) }}"
                   class="px-3.5 py-2 text-xs font-semibold rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 shadow-2xs transition">
                    ⚠️ อุปสรรคงาน (Constraints)
                </a>
                <a href="{{ route('projects.portfolio') }}"
                   class="px-3.5 py-2 text-xs font-semibold rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 transition">
                    ← แฟ้มโครงการ (Portfolio)
                </a>
            </div>
        </div>

        <!-- Construction Lifecycle Process Flow (Stage 4 Active) -->
        <x-ced.workflow-stepper :project="$project" active="site" />

        <!-- 1. MindInventory High-Vis Industrial Safety LED Scoreboard Hero -->
        <div class="rounded-3xl bg-slate-900 border border-slate-800 p-6 sm:p-8 shadow-card relative overflow-hidden text-white">
            <div class="absolute -right-8 -top-8 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
                <!-- Large Digital Safe Man-Hours Sign -->
                <div class="lg:col-span-5 space-y-2 border-b lg:border-b-0 lg:border-r border-slate-800 pb-6 lg:pb-0 lg:pr-6">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-3 h-3 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-xs font-extrabold uppercase tracking-widest text-emerald-400">ชั่วโมงทำงานปลอดภัยสะสม (Cumulative Safe Man-Hours)</span>
                    </div>
                    <div class="text-4xl sm:text-5xl font-black tracking-tight text-white font-mono tabular-nums">
                        {{ number_format($metrics['safe_man_hours'], 1) }}
                        <span class="text-lg font-sans font-bold text-slate-400">ชม. (hrs)</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-slate-400">
                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/15 text-emerald-300 font-semibold border border-emerald-500/30">
                            สถิติอุบัติเหตุถึงขั้นหยุดงานเป็นศูนย์ Zero Lost-Time Injury (LTI) Record
                        </span>
                    </div>
                </div>

                <!-- Scoreboard Micro Metrics -->
                <div class="lg:col-span-7 grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="rounded-2xl bg-slate-950/80 border border-slate-800 p-4">
                        <div class="text-[11px] font-bold uppercase text-slate-400">หยุดงาน (Lost Time / LTI)</div>
                        <div class="mt-1 text-2xl font-black font-mono {{ $metrics['lti_count'] > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                            {{ $metrics['lti_count'] }}
                        </div>
                        <div class="text-[10px] text-slate-400 mt-0.5">เหตุรุนแรง (Fatal/Severe)</div>
                    </div>

                    <div class="rounded-2xl bg-slate-950/80 border border-slate-800 p-4">
                        <div class="text-[11px] font-bold uppercase text-slate-400">เกือบเกิดเหตุ (Near-Miss)</div>
                        <div class="mt-1 text-2xl font-black font-mono text-amber-400">
                            {{ $metrics['near_miss_count'] }}
                        </div>
                        <div class="text-[10px] text-slate-400 mt-0.5">ป้องกันทัน (Preventions)</div>
                    </div>

                    <div class="rounded-2xl bg-slate-950/80 border border-slate-800 p-4">
                        <div class="text-[11px] font-bold uppercase text-slate-400">ประชุมความปลอดภัย (Toolbox Talks)</div>
                        <div class="mt-1 text-2xl font-black font-mono text-sky-400">
                            {{ $metrics['toolbox_talks_count'] }}
                        </div>
                        <div class="text-[10px] text-slate-400 mt-0.5">สรุปสถิติรอบ (Briefings)</div>
                    </div>

                    <div class="rounded-2xl bg-slate-950/80 border border-slate-800 p-4">
                        <div class="text-[11px] font-bold uppercase text-slate-400">ใบอนุญาตเสี่ยง (Active PTWs)</div>
                        <div class="mt-1 text-2xl font-black font-mono text-orange-400">
                            {{ $metrics['active_permits_count'] }}
                        </div>
                        <div class="text-[10px] text-slate-400 mt-0.5">งานร้อน/ที่สูง/ที่อับอากาศ</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Dual Forms: PTW Request & HSE Event Logging (MindInventory Clean White Cards) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Request Permit-to-Work Form -->
            <form action="{{ route('projects.hse.permits.store', $project) }}" method="POST"
                  class="space-y-4 rounded-2xl bg-white border border-slate-200/90 p-6 shadow-card">
                @csrf
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase text-orange-600">Permit-to-Work (PTW)</span>
                        <h2 class="text-base font-bold text-slate-900">ขออนุมัติใบอนุญาตทำงานเสี่ยงอันตราย</h2>
                    </div>
                    <span class="text-lg">🔥</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Permit Type *</label>
                        <select name="permit_type" class="mt-1 w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-xs font-bold text-slate-800 focus:bg-white focus:border-safety-500 focus:outline-none transition">
                            @foreach(\App\Models\PermitToWork::TYPES as $pt)
                                <option value="{{ $pt }}">{{ str_replace('_', ' ', $pt) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Location / Zone</label>
                        <input type="text" name="location_zone" placeholder="e.g. Shaft B · -18m Confined Pit"
                               class="mt-1 w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-safety-500 focus:outline-none transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700">High-Risk Work Title & Scope *</label>
                    <input type="text" name="work_title" required placeholder="e.g. Overhead Arc Welding on Pier Cap Steel Bracket"
                           class="mt-1 w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-safety-500 focus:outline-none transition">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Valid From *</label>
                        <input type="date" name="valid_from" value="{{ now()->toDateString() }}" required
                               class="mt-1 w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:bg-white focus:border-safety-500 focus:outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Valid Until *</label>
                        <input type="date" name="valid_until" value="{{ now()->addDays(3)->toDateString() }}" required
                               class="mt-1 w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:bg-white focus:border-safety-500 focus:outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Linked WBS Task</label>
                        <select name="wbs_task_id" class="mt-1 w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:bg-white focus:border-safety-500 focus:outline-none transition">
                            <option value="">— Optional —</option>
                            @foreach($tasks as $task)
                                <option value="{{ $task->id }}">{{ $task->wbs_code }} · {{ $task->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700">Safety Precautions Checklist</label>
                    <input type="text" name="precautions_checklist" placeholder="e.g. Fire extinguisher 2x, full-body harness double lanyard, gas monitor < 10% LEL"
                           class="mt-1 w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-safety-500 focus:outline-none transition">
                </div>

                <button type="submit"
                        class="w-full rounded-xl bg-safety-500 hover:bg-safety-600 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white shadow-safety-glow transition">
                    + Submit Permit-to-Work Request
                </button>
            </form>

            <!-- Record HSE Safety Event / Toolbox Talk / Near-Miss / LTI -->
            <form action="{{ route('projects.hse.events.store', $project) }}" method="POST"
                  class="space-y-4 rounded-2xl bg-white border border-slate-200/90 p-6 shadow-card">
                @csrf
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase text-amber-600">HSE Daily Reporting</span>
                        <h2 class="text-base font-bold text-slate-900">บันทึกกิจกรรมความปลอดภัย / Near-Miss / LTI</h2>
                    </div>
                    <span class="text-lg">📢</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Date *</label>
                        <input type="date" name="log_date" value="{{ now()->toDateString() }}" required
                               class="mt-1 w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:bg-white focus:border-safety-500 focus:outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Record Type *</label>
                        <select name="record_type" class="mt-1 w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-xs font-bold text-slate-800 focus:bg-white focus:border-safety-500 focus:outline-none transition">
                            @foreach(\App\Models\HseSafetyLog::RECORD_TYPES as $rt)
                                <option value="{{ $rt }}">{{ str_replace('_', ' ', $rt) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Attendees Count</label>
                        <input type="number" min="0" name="attendees_count" value="25"
                               class="mt-1 w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-xs font-mono text-slate-800 focus:bg-white focus:border-safety-500 focus:outline-none transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700">Topic / Incident Summary *</label>
                    <input type="text" name="title" required placeholder="e.g. Morning Toolbox Talk: Crane Rigging & Lifting Zone Exclusion"
                           class="mt-1 w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-safety-500 focus:outline-none transition">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Description / Corrective Remarks</label>
                        <input type="text" name="description" placeholder="Details..."
                               class="mt-1 w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-safety-500 focus:outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Lost Time Hours (if LTI)</label>
                        <input type="number" step="0.5" min="0" name="lost_time_hours" value="0"
                               class="mt-1 w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-xs font-mono text-slate-800 focus:bg-white focus:border-safety-500 focus:outline-none transition">
                    </div>
                </div>

                <button type="submit"
                        class="w-full rounded-xl bg-amber-500 hover:bg-amber-600 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white shadow-sm transition">
                    + Log HSE Safety Record
                </button>
            </form>
        </div>

        <!-- 3. Permit-to-Work Register Table (Clean White Surface) -->
        <div class="rounded-2xl bg-white border border-slate-200/90 shadow-card overflow-hidden">
            <div class="border-b border-slate-200 bg-slate-50/80 px-6 py-3.5 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                    <span class="font-bold text-xs uppercase tracking-wider text-slate-800">Permit-to-Work (PTW) Register & Safety Authorization</span>
                </div>
                <span class="text-xs font-mono font-bold text-slate-500">Total: {{ $permits->count() }}</span>
            </div>

            @if($permits->isEmpty())
                <div class="p-8 text-center text-xs text-slate-500">ยังไม่มีรายการขอใบอนุญาตทำงานเสี่ยงอันตราย (No permits requested yet)</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-50 text-[10px] uppercase text-slate-500 font-bold border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-3">เลขที่อ้างอิง/ประเภท (Ref / Type)</th>
                                <th class="px-6 py-3">ชื่องาน/รหัสงาน (Work Title / WBS Task)</th>
                                <th class="px-6 py-3">ระยะเวลาที่อนุญาต (Validity Window)</th>
                                <th class="px-6 py-3 text-center">สถานะ (Status)</th>
                                <th class="px-6 py-3 text-center">การดำเนินการ (Safety Action)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($permits as $ptw)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-6 py-3.5">
                                        <div class="font-mono text-xs font-bold text-orange-600">{{ $ptw->reference_no }}</div>
                                        <div class="font-bold text-slate-800 mt-0.5">{{ str_replace('_', ' ', $ptw->permit_type) }}</div>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <div class="font-bold text-slate-900">{{ $ptw->work_title }}</div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">
                                            {{ $ptw->task ? $ptw->task->wbs_code.' · '.$ptw->task->name : 'General Site' }}
                                            @if($ptw->location_zone) · 📍 {{ $ptw->location_zone }} @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-3.5 font-mono text-[11px] text-slate-600">
                                        {{ $ptw->valid_from->format('d/m/Y') }} – {{ $ptw->valid_until->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-3.5 text-center">
                                        <x-ced.status-badge :status="$ptw->status" />
                                        @if($ptw->approver)
                                            <div class="text-[10px] text-slate-500 mt-1">by {{ $ptw->approver->name }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3.5 text-center">
                                        @if($ptw->status === 'REQUESTED')
                                            <form action="{{ route('projects.hse.permits.approve', [$project, $ptw]) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        class="rounded-xl bg-emerald-600 hover:bg-emerald-700 px-3.5 py-1.5 text-xs font-bold text-white shadow-xs transition">
                                                    ✓ Approve PTW
                                                </button>
                                            </form>
                                        @elseif($ptw->status === 'APPROVED')
                                            <form action="{{ route('projects.hse.permits.revoke', [$project, $ptw]) }}" method="POST" class="inline-flex gap-1">
                                                @csrf
                                                <input type="hidden" name="reason" value="Unsafe site condition observed">
                                                <button type="submit"
                                                        class="rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 px-3 py-1 text-[11px] font-bold transition">
                                                    Revoke
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- 4. HSE Toolbox Talks & Incident Register -->
        <div class="rounded-2xl bg-white border border-slate-200/90 shadow-card overflow-hidden">
            <div class="border-b border-slate-200 bg-slate-50/80 px-6 py-3.5 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="font-bold text-xs uppercase tracking-wider text-slate-800">HSE Toolbox Talks & Incident Log</span>
                </div>
                <span class="text-xs font-mono font-bold text-slate-500">Total: {{ $safetyLogs->count() }}</span>
            </div>

            @if($safetyLogs->isEmpty())
                <div class="p-8 text-center text-xs text-slate-500">ยังไม่มีประวัติกิจกรรมความปลอดภัย (No safety events logged yet)</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-50 text-[10px] uppercase text-slate-500 font-bold border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-3">วันที่ (Date)</th>
                                <th class="px-6 py-3">ประเภท (Type)</th>
                                <th class="px-6 py-3">หัวข้อและรายละเอียด (Title & Description)</th>
                                <th class="px-6 py-3 text-right">ผู้เข้าร่วม (Attendees)</th>
                                <th class="px-6 py-3 text-right">ชั่วโมงสูญเสีย (Lost Time hrs)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($safetyLogs as $sLog)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-6 py-3.5 font-mono text-slate-600">{{ $sLog->log_date->format('d/m/Y') }}</td>
                                    <td class="px-6 py-3.5">
                                        <x-ced.status-badge :status="$sLog->record_type" />
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <div class="font-bold text-slate-900">{{ $sLog->title }}</div>
                                        @if($sLog->description)
                                            <div class="text-[11px] text-slate-500 mt-0.5">{{ $sLog->description }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3.5 text-right font-mono font-bold text-slate-800">{{ $sLog->attendees_count }}</td>
                                    <td class="px-6 py-3.5 text-right font-mono font-bold {{ $sLog->lost_time_hours > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                        {{ number_format($sLog->lost_time_hours, 1) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
