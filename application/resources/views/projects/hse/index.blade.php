<x-layouts.app :project="$project" title="{{ $project->code }} · HSE Safety Command & Permit-to-Work">
    <div class="space-y-6">
        <!-- Sub-Header & Navigation -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-obsidian-750">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider rounded-md bg-qc-500/15 text-qc-400 border border-qc-500/30">
                        Phase 6C · HSE Safety & Operations Command
                    </span>
                    <span class="text-xs font-mono text-slate-400">{{ $project->code }}</span>
                </div>
                <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight text-white flex items-center gap-2.5">
                    <span>🦺</span>
                    <span>HSE Safety Command & Safe Man-Hours</span>
                </h1>
                <p class="mt-1 text-xs text-slate-400">
                    ติดตามชั่วโมงการทำงานสะสมปลอดอุบัติเหตุ (Safe Man-Hours), บันทึก Toolbox Talk / Near-Miss / LTI และอนุมัติใบอนุญาตทำงานเสี่ยงอันตราย (PTW)
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('projects.resources.index', $project) }}"
                   class="px-3.5 py-2 text-xs font-semibold rounded-xl bg-obsidian-850 hover:bg-obsidian-800 text-slate-200 border border-obsidian-700 transition">
                    👷 Workforce & Machinery
                </a>
                <a href="{{ route('projects.constraints', $project) }}"
                   class="px-3.5 py-2 text-xs font-semibold rounded-xl bg-obsidian-850 hover:bg-obsidian-800 text-slate-200 border border-obsidian-700 transition">
                    ⚠️ Constraints
                </a>
                <a href="{{ route('projects.portfolio') }}"
                   class="px-3.5 py-2 text-xs font-semibold rounded-xl bg-obsidian-800 hover:bg-obsidian-750 text-white border border-obsidian-700 transition">
                    ← Portfolio
                </a>
            </div>
        </div>

        <!-- 1. MindInventory High-Vis Industrial Safety LED Scoreboard Hero -->
        <div class="rounded-3xl bg-gradient-to-br from-obsidian-900 via-obsidian-850 to-obsidian-900 border border-obsidian-700 p-6 sm:p-8 shadow-industrial relative overflow-hidden">
            <div class="absolute -right-8 -top-8 w-64 h-64 bg-qc-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
                <!-- Large Digital Safe Man-Hours Sign -->
                <div class="lg:col-span-5 space-y-2 border-b lg:border-b-0 lg:border-r border-obsidian-750 pb-6 lg:pb-0 lg:pr-6">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-3 h-3 rounded-full bg-qc-500 animate-pulse"></span>
                        <span class="text-xs font-extrabold uppercase tracking-widest text-qc-400">Cumulative Safe Man-Hours</span>
                    </div>
                    <div class="text-4xl sm:text-5xl font-black tracking-tight text-white font-mono tabular-nums">
                        {{ number_format($metrics['safe_man_hours'], 1) }}
                        <span class="text-lg font-sans font-bold text-slate-400">hrs</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-slate-400">
                        <span class="px-2 py-0.5 rounded bg-qc-500/15 text-qc-300 font-semibold border border-qc-500/30">
                            Zero Lost-Time Injury (LTI) Record
                        </span>
                    </div>
                </div>

                <!-- Scoreboard Micro Metrics -->
                <div class="lg:col-span-7 grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="rounded-2xl bg-obsidian-950/80 border border-obsidian-750 p-4">
                        <div class="text-[11px] font-bold uppercase text-slate-400">Lost Time (LTI)</div>
                        <div class="mt-1 text-2xl font-black font-mono {{ $metrics['lti_count'] > 0 ? 'text-ncr-500' : 'text-qc-400' }}">
                            {{ $metrics['lti_count'] }}
                        </div>
                        <div class="text-[10px] text-slate-400 mt-0.5">Fatal/Severe</div>
                    </div>

                    <div class="rounded-2xl bg-obsidian-950/80 border border-obsidian-750 p-4">
                        <div class="text-[11px] font-bold uppercase text-slate-400">Near-Miss</div>
                        <div class="mt-1 text-2xl font-black font-mono text-hazard-400">
                            {{ $metrics['near_miss_count'] }}
                        </div>
                        <div class="text-[10px] text-slate-400 mt-0.5">Preventions</div>
                    </div>

                    <div class="rounded-2xl bg-obsidian-950/80 border border-obsidian-750 p-4">
                        <div class="text-[11px] font-bold uppercase text-slate-400">Toolbox Talks</div>
                        <div class="mt-1 text-2xl font-black font-mono text-blueprint-400">
                            {{ $metrics['toolbox_talks_count'] }}
                        </div>
                        <div class="text-[10px] text-slate-400 mt-0.5">Briefings</div>
                    </div>

                    <div class="rounded-2xl bg-obsidian-950/80 border border-obsidian-750 p-4">
                        <div class="text-[11px] font-bold uppercase text-slate-400">Active PTWs</div>
                        <div class="mt-1 text-2xl font-black font-mono text-safety-400">
                            {{ $metrics['active_permits_count'] }}
                        </div>
                        <div class="text-[10px] text-slate-400 mt-0.5">Hot/Height/Space</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Dual Forms: PTW Request & HSE Event Logging -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Request Permit-to-Work Form -->
            <form action="{{ route('projects.hse.permits.store', $project) }}" method="POST"
                  class="space-y-4 rounded-2xl bg-obsidian-850 border border-obsidian-750 p-6 shadow-industrial">
                @csrf
                <div class="border-b border-obsidian-750 pb-3 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase text-safety-400">Permit-to-Work (PTW)</span>
                        <h2 class="text-base font-bold text-white">ขออนุมัติใบอนุญาตทำงานเสี่ยงอันตราย</h2>
                    </div>
                    <span class="text-lg">🔥</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300">Permit Type *</label>
                        <select name="permit_type" class="mt-1 w-full rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-2 text-xs font-bold text-white focus:border-safety-500 focus:outline-none transition">
                            @foreach(\App\Models\PermitToWork::TYPES as $pt)
                                <option value="{{ $pt }}">{{ str_replace('_', ' ', $pt) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300">Location / Zone</label>
                        <input type="text" name="location_zone" placeholder="e.g. Shaft B · -18m Confined Pit"
                               class="mt-1 w-full rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-2 text-xs text-white focus:border-safety-500 focus:outline-none transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300">High-Risk Work Title & Scope *</label>
                    <input type="text" name="work_title" required placeholder="e.g. Overhead Arc Welding on Pier Cap Steel Bracket"
                           class="mt-1 w-full rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-2 text-xs text-white focus:border-safety-500 focus:outline-none transition">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300">Valid From *</label>
                        <input type="date" name="valid_from" value="{{ now()->toDateString() }}" required
                               class="mt-1 w-full rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-2 text-xs text-white focus:border-safety-500 focus:outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300">Valid Until *</label>
                        <input type="date" name="valid_until" value="{{ now()->addDays(3)->toDateString() }}" required
                               class="mt-1 w-full rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-2 text-xs text-white focus:border-safety-500 focus:outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300">Linked WBS Task</label>
                        <select name="wbs_task_id" class="mt-1 w-full rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-2 text-xs text-white focus:border-safety-500 focus:outline-none transition">
                            <option value="">— Optional —</option>
                            @foreach($tasks as $task)
                                <option value="{{ $task->id }}">{{ $task->wbs_code }} · {{ $task->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300">Safety Precautions Checklist (Gas Detector, Fire Watch, Lifeline)</label>
                    <input type="text" name="precautions_checklist" placeholder="e.g. Fire extinguisher 2x, full-body harness double lanyard, gas monitor < 10% LEL"
                           class="mt-1 w-full rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-2 text-xs text-white focus:border-safety-500 focus:outline-none transition">
                </div>

                <button type="submit"
                        class="w-full rounded-xl bg-safety-500 hover:bg-safety-600 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white shadow-safety-glow transition">
                    + Submit Permit-to-Work Request
                </button>
            </form>

            <!-- Record HSE Safety Event / Toolbox Talk / Near-Miss / LTI -->
            <form action="{{ route('projects.hse.events.store', $project) }}" method="POST"
                  class="space-y-4 rounded-2xl bg-obsidian-850 border border-obsidian-750 p-6 shadow-industrial">
                @csrf
                <div class="border-b border-obsidian-750 pb-3 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase text-hazard-400">HSE Daily Reporting</span>
                        <h2 class="text-base font-bold text-white">บันทึกกิจกรรมความปลอดภัย / Near-Miss / LTI</h2>
                    </div>
                    <span class="text-lg">📢</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300">Date *</label>
                        <input type="date" name="log_date" value="{{ now()->toDateString() }}" required
                               class="mt-1 w-full rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-2 text-xs text-white focus:border-safety-500 focus:outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300">Record Type *</label>
                        <select name="record_type" class="mt-1 w-full rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-2 text-xs font-bold text-white focus:border-safety-500 focus:outline-none transition">
                            @foreach(\App\Models\HseSafetyLog::RECORD_TYPES as $rt)
                                <option value="{{ $rt }}">{{ str_replace('_', ' ', $rt) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300">Attendees Count</label>
                        <input type="number" min="0" name="attendees_count" value="25"
                               class="mt-1 w-full rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-2 text-xs font-mono text-white focus:border-safety-500 focus:outline-none transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300">Topic / Incident Summary *</label>
                    <input type="text" name="title" required placeholder="e.g. Morning Toolbox Talk: Crane Rigging & Lifting Zone Exclusion"
                           class="mt-1 w-full rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-2 text-xs text-white focus:border-safety-500 focus:outline-none transition">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300">Description / Corrective Remarks</label>
                        <input type="text" name="description" placeholder="Details..."
                               class="mt-1 w-full rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-2 text-xs text-white focus:border-safety-500 focus:outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300">Lost Time Hours (if LTI)</label>
                        <input type="number" step="0.5" min="0" name="lost_time_hours" value="0"
                               class="mt-1 w-full rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-2 text-xs font-mono text-white focus:border-safety-500 focus:outline-none transition">
                    </div>
                </div>

                <button type="submit"
                        class="w-full rounded-xl bg-hazard-500 hover:bg-hazard-600 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-obsidian-950 transition">
                    + Log HSE Safety Record
                </button>
            </form>
        </div>

        <!-- 3. Permit-to-Work Register Table -->
        <div class="rounded-2xl bg-obsidian-850 border border-obsidian-750 shadow-industrial overflow-hidden">
            <div class="border-b border-obsidian-750 bg-obsidian-900/80 px-6 py-3.5 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-blueprint-500"></span>
                    <span class="font-bold text-xs uppercase tracking-wider text-slate-200">Permit-to-Work (PTW) Register & Safety Authorization</span>
                </div>
                <span class="text-xs font-mono text-slate-400">Total: {{ $permits->count() }}</span>
            </div>

            @if($permits->isEmpty())
                <div class="p-8 text-center text-xs text-slate-500">ยังไม่มีรายการขอใบอนุญาตทำงานเสี่ยงอันตราย (No permits requested yet)</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="bg-obsidian-900 text-[10px] uppercase text-slate-400 font-bold border-b border-obsidian-750">
                            <tr>
                                <th class="px-6 py-3">Ref / Type</th>
                                <th class="px-6 py-3">Work Title / WBS Task</th>
                                <th class="px-6 py-3">Validity Window</th>
                                <th class="px-6 py-3 text-center">Status</th>
                                <th class="px-6 py-3 text-center">Safety Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-obsidian-750">
                            @foreach($permits as $ptw)
                                <tr class="hover:bg-obsidian-800/50 transition">
                                    <td class="px-6 py-3.5">
                                        <div class="font-mono text-xs font-bold text-safety-400">{{ $ptw->reference_no }}</div>
                                        <div class="font-bold text-slate-200 mt-0.5">{{ str_replace('_', ' ', $ptw->permit_type) }}</div>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <div class="font-semibold text-white">{{ $ptw->work_title }}</div>
                                        <div class="text-[11px] text-slate-400 mt-0.5">
                                            {{ $ptw->task ? $ptw->task->wbs_code.' · '.$ptw->task->name : 'General Site' }}
                                            @if($ptw->location_zone) · 📍 {{ $ptw->location_zone }} @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-3.5 font-mono text-[11px] text-slate-300">
                                        {{ $ptw->valid_from->format('d/m/Y') }} – {{ $ptw->valid_until->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-3.5 text-center">
                                        <x-ced.status-badge :status="$ptw->status" />
                                        @if($ptw->approver)
                                            <div class="text-[10px] text-slate-400 mt-1">by {{ $ptw->approver->name }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3.5 text-center">
                                        @if($ptw->status === 'REQUESTED')
                                            <form action="{{ route('projects.hse.permits.approve', [$project, $ptw]) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        class="rounded-xl bg-qc-600 hover:bg-qc-500 px-3 py-1.5 text-xs font-bold text-white shadow-qc-glow transition">
                                                    ✓ Approve PTW
                                                </button>
                                            </form>
                                        @elseif($ptw->status === 'APPROVED')
                                            <form action="{{ route('projects.hse.permits.revoke', [$project, $ptw]) }}" method="POST" class="inline-flex gap-1">
                                                @csrf
                                                <input type="hidden" name="reason" value="Unsafe site condition observed">
                                                <button type="submit"
                                                        class="rounded-xl bg-ncr-500/20 hover:bg-ncr-500/30 text-ncr-400 border border-ncr-500/30 px-3 py-1 text-[11px] font-semibold transition">
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
        <div class="rounded-2xl bg-obsidian-850 border border-obsidian-750 shadow-industrial overflow-hidden">
            <div class="border-b border-obsidian-750 bg-obsidian-900/80 px-6 py-3.5 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-qc-500"></span>
                    <span class="font-bold text-xs uppercase tracking-wider text-slate-200">HSE Toolbox Talks & Incident Log</span>
                </div>
                <span class="text-xs font-mono text-slate-400">Total: {{ $safetyLogs->count() }}</span>
            </div>

            @if($safetyLogs->isEmpty())
                <div class="p-8 text-center text-xs text-slate-500">ยังไม่มีประวัติกิจกรรมความปลอดภัย (No safety events logged yet)</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="bg-obsidian-900 text-[10px] uppercase text-slate-400 font-bold border-b border-obsidian-750">
                            <tr>
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3">Type</th>
                                <th class="px-6 py-3">Title & Description</th>
                                <th class="px-6 py-3 text-right">Attendees</th>
                                <th class="px-6 py-3 text-right">Lost Time (hrs)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-obsidian-750">
                            @foreach($safetyLogs as $sLog)
                                <tr class="hover:bg-obsidian-800/50 transition">
                                    <td class="px-6 py-3.5 font-mono text-slate-300">{{ $sLog->log_date->format('d/m/Y') }}</td>
                                    <td class="px-6 py-3.5">
                                        <x-ced.status-badge :status="$sLog->record_type" />
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <div class="font-bold text-white">{{ $sLog->title }}</div>
                                        @if($sLog->description)
                                            <div class="text-[11px] text-slate-400 mt-0.5">{{ $sLog->description }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3.5 text-right font-mono font-semibold text-slate-200">{{ $sLog->attendees_count }}</td>
                                    <td class="px-6 py-3.5 text-right font-mono font-bold {{ $sLog->lost_time_hours > 0 ? 'text-ncr-500' : 'text-slate-400' }}">
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
