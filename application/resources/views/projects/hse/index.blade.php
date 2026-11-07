<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $project->code }} · HSE Safety, Permit-to-Work & Safe Man-Hours</title>
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
            <a class="text-sky-800 hover:underline" href="{{ route('projects.resources.index', $project) }}">👷 Workforce & Machinery</a>
            <span class="text-slate-400">·</span>
            <a class="text-emerald-800 hover:underline" href="{{ route('projects.ipc.index', $project) }}">💰 IPC & Payment</a>
            <span class="text-slate-400">·</span>
            <a class="text-rose-700 hover:underline" href="{{ route('projects.ncr.index', $project) }}">🛡️ QC & NCR</a>
            <span class="text-slate-400">·</span>
            <span class="text-teal-800 font-bold underline">🦺 HSE & Permit-to-Work</span>
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
        <span class="rounded bg-teal-100 px-2.5 py-0.5 text-xs font-bold text-teal-800">Phase 6C · HSE Safety & Permit-to-Work</span>
        <h1 class="mt-1 text-2xl font-black">HSE Safety Command, Safe Man-Hours & Permit-to-Work (PTW)</h1>
        <p class="text-xs text-slate-500">ติดตามชั่วโมงการทำงานสะสมปลอดอุบัติเหตุ (Safe Man-Hours), บันทึก Toolbox Talk / Near-Miss / LTI และอนุมัติใบอนุญาตทำงานเสี่ยงอันตรายพร้อมปลดล็อก PERMIT Constraint อัตโนมัติ</p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
        <div class="rounded-xl bg-white p-4 shadow-sm border border-emerald-200 bg-emerald-50/40">
            <div class="text-[11px] font-bold uppercase text-emerald-800">Safe Man-Hours</div>
            <div class="text-xl font-black text-emerald-900">{{ number_format($metrics['safe_man_hours'], 1) }} <span class="text-xs font-normal">hrs</span></div>
            <div class="text-[10px] text-emerald-700">Since last LTI</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
            <div class="text-[11px] font-bold uppercase text-slate-400">Lost Time Injuries (LTI)</div>
            <div class="text-xl font-black {{ $metrics['lti_count'] > 0 ? 'text-rose-600' : 'text-emerald-700' }}">{{ $metrics['lti_count'] }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
            <div class="text-[11px] font-bold uppercase text-slate-400">Near-Miss Reports</div>
            <div class="text-xl font-black text-amber-600">{{ $metrics['near_miss_count'] }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
            <div class="text-[11px] font-bold uppercase text-slate-400">Toolbox Talks</div>
            <div class="text-xl font-black text-sky-800">{{ $metrics['toolbox_talks_count'] }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
            <div class="text-[11px] font-bold uppercase text-slate-400">Active Approved PTWs</div>
            <div class="text-xl font-black text-teal-700">{{ $metrics['active_permits_count'] }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm border border-amber-200 bg-amber-50/40">
            <div class="text-[11px] font-bold uppercase text-amber-800">Open Permit Constraints</div>
            <div class="text-xl font-black text-amber-900">{{ $metrics['open_permit_constraints'] }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Request Permit-to-Work Form -->
        <form action="{{ route('projects.hse.permits.store', $project) }}" method="POST" class="space-y-4 rounded-xl bg-white p-6 shadow-sm border border-slate-200">
            @csrf
            <div class="border-b border-slate-100 pb-3">
                <span class="text-xs font-bold uppercase text-teal-700">Permit-to-Work (PTW) Request</span>
                <h2 class="text-lg font-bold text-slate-900">ขออนุมัติใบอนุญาตทำงานเสี่ยงอันตราย</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700">Permit Type *</label>
                    <select name="permit_type" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs font-bold">
                        @foreach(\App\Models\PermitToWork::TYPES as $pt)
                            <option value="{{ $pt }}">{{ str_replace('_', ' ', $pt) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Location / Zone</label>
                    <input type="text" name="location_zone" placeholder="e.g. Shaft B · -18m Confined Pit" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700">High-Risk Work Title & Scope *</label>
                <input type="text" name="work_title" required placeholder="e.g. Overhead Arc Welding on Pier Cap Steel Bracket" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700">Valid From *</label>
                    <input type="date" name="valid_from" value="{{ now()->toDateString() }}" required class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Valid Until *</label>
                    <input type="date" name="valid_until" value="{{ now()->addDays(3)->toDateString() }}" required class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs">
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

            <div>
                <label class="block text-xs font-bold text-slate-700">Safety Precautions Checklist (Gas Detector, Fire Watch, Lifeline)</label>
                <input type="text" name="precautions_checklist" placeholder="e.g. Fire extinguisher 2x, full-body harness double lanyard, gas monitor < 10% LEL" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs">
            </div>

            <button type="submit" class="w-full rounded-lg bg-teal-600 px-4 py-2 text-xs font-bold text-white hover:bg-teal-700">+ Submit Permit-to-Work Request</button>
        </form>

        <!-- Record HSE Safety Event / Toolbox Talk / Near-Miss / LTI -->
        <form action="{{ route('projects.hse.events.store', $project) }}" method="POST" class="space-y-4 rounded-xl bg-white p-6 shadow-sm border border-slate-200">
            @csrf
            <div class="border-b border-slate-100 pb-3">
                <span class="text-xs font-bold uppercase text-amber-700">HSE Daily Log & Incident Reporting</span>
                <h2 class="text-lg font-bold text-slate-900">บันทึกกิจกรรมความปลอดภัย / Near-Miss / LTI</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700">Date *</label>
                    <input type="date" name="log_date" value="{{ now()->toDateString() }}" required class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Record Type *</label>
                    <select name="record_type" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs font-bold">
                        @foreach(\App\Models\HseSafetyLog::RECORD_TYPES as $rt)
                            <option value="{{ $rt }}">{{ str_replace('_', ' ', $rt) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Attendees Count</label>
                    <input type="number" min="0" name="attendees_count" value="25" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs font-mono">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700">Topic / Incident Summary *</label>
                <input type="text" name="title" required placeholder="e.g. Morning Toolbox Talk: Crane Rigging & Lifting Zone Exclusion" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700">Description / Corrective Remarks</label>
                    <input type="text" name="description" placeholder="Details..." class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Lost Time Hours (if LTI)</label>
                    <input type="number" step="0.5" min="0" name="lost_time_hours" value="0" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs font-mono">
                </div>
            </div>

            <button type="submit" class="w-full rounded-lg bg-amber-600 px-4 py-2 text-xs font-bold text-white hover:bg-amber-700">+ Log HSE Safety Record</button>
        </form>
    </div>

    <!-- Permit-to-Work Register -->
    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-50 px-6 py-3 font-bold text-xs text-slate-800">Permit-to-Work (PTW) Register & Safety Authorization</div>
        @if($permits->isEmpty())
            <div class="p-8 text-center text-xs text-slate-500">ยังไม่มีรายการขอใบอนุญาตทำงานเสี่ยงอันตราย (No permits requested yet)</div>
        @else
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[10px] uppercase text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-2.5">Ref / Type</th>
                        <th class="px-6 py-2.5">Work Title / WBS Task</th>
                        <th class="px-6 py-2.5">Validity Window</th>
                        <th class="px-6 py-2.5 text-center">Status</th>
                        <th class="px-6 py-2.5 text-center">Safety Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($permits as $ptw)
                        <tr>
                            <td class="px-6 py-3">
                                <div class="font-mono text-[11px] text-slate-500">{{ $ptw->reference_no }}</div>
                                <div class="font-bold text-teal-800">{{ str_replace('_', ' ', $ptw->permit_type) }}</div>
                            </td>
                            <td class="px-6 py-3">
                                <div class="font-bold text-slate-900">{{ $ptw->work_title }}</div>
                                <div class="text-[11px] text-slate-500">
                                    {{ $ptw->task ? $ptw->task->wbs_code.' · '.$ptw->task->name : 'General Site' }}
                                    @if($ptw->location_zone) · 📍 {{ $ptw->location_zone }} @endif
                                </div>
                            </td>
                            <td class="px-6 py-3 font-mono text-[11px]">
                                {{ $ptw->valid_from->format('d/m/Y') }} – {{ $ptw->valid_until->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-3 text-center">
                                <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ $ptw->status === 'APPROVED' ? 'bg-emerald-100 text-emerald-800' : ($ptw->status === 'REVOKED' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                    {{ $ptw->status }}
                                </span>
                                @if($ptw->approver)
                                    <div class="text-[10px] text-slate-400 mt-0.5">by {{ $ptw->approver->name }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-center">
                                @if($ptw->status === 'REQUESTED')
                                    <form action="{{ route('projects.hse.permits.approve', [$project, $ptw]) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="rounded bg-emerald-600 px-3 py-1 text-[11px] font-bold text-white hover:bg-emerald-700">✓ Approve PTW</button>
                                    </form>
                                @elseif($ptw->status === 'APPROVED')
                                    <form action="{{ route('projects.hse.permits.revoke', [$project, $ptw]) }}" method="POST" class="inline-flex gap-1">
                                        @csrf
                                        <input type="hidden" name="reason" value="Unsafe site condition observed">
                                        <button type="submit" class="rounded bg-rose-100 px-2.5 py-1 text-[10px] font-bold text-rose-800 hover:bg-rose-200">Revoke</button>
                                    </form>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- HSE Safety Logs -->
    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-50 px-6 py-3 font-bold text-xs text-slate-800">HSE Toolbox Talks & Incident Log</div>
        @if($safetyLogs->isEmpty())
            <div class="p-8 text-center text-xs text-slate-500">ยังไม่มีประวัติกิจกรรมความปลอดภัย (No safety events logged yet)</div>
        @else
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[10px] uppercase text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-2.5">Date</th>
                        <th class="px-6 py-2.5">Type</th>
                        <th class="px-6 py-2.5">Title & Description</th>
                        <th class="px-6 py-2.5 text-right">Attendees</th>
                        <th class="px-6 py-2.5 text-right">Lost Time (hrs)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($safetyLogs as $sLog)
                        <tr>
                            <td class="px-6 py-3 font-mono">{{ $sLog->log_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-3">
                                <span class="rounded px-2 py-0.5 text-[10px] font-bold {{ $sLog->isLti() ? 'bg-rose-600 text-white' : 'bg-slate-100 text-slate-800' }}">
                                    {{ str_replace('_', ' ', $sLog->record_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <div class="font-bold text-slate-900">{{ $sLog->title }}</div>
                                @if($sLog->description)<div class="text-[11px] text-slate-500">{{ $sLog->description }}</div>@endif
                            </td>
                            <td class="px-6 py-3 text-right font-mono">{{ $sLog->attendees_count }}</td>
                            <td class="px-6 py-3 text-right font-mono font-bold {{ $sLog->lost_time_hours > 0 ? 'text-rose-700' : 'text-slate-400' }}">{{ number_format($sLog->lost_time_hours, 1) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

</main>
</body>
</html>
