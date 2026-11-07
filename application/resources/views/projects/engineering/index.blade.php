<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $project->code }} · Engineering Document Control (RFI & Submittals)</title>
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
            <span class="text-indigo-800 font-bold underline">📐 RFI & Submittals</span>
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
        <span class="rounded bg-indigo-100 px-2.5 py-0.5 text-xs font-bold text-indigo-800">Phase 7A + 7B · Engineering Document Control</span>
        <h1 class="mt-1 text-2xl font-black">Request for Information (RFI) & Technical Submittal Workflow</h1>
        <p class="text-xs text-slate-500">บริหารคำถามด้านวิศวกรรม (RFI) พร้อมแจ้งเตือน SLA เกินกำหนด, ปลดล็อก DESIGN_RFI Constraint อัตโนมัติ และควบคุม Revision ของ Shop Drawing / Material Submittals</p>
    </div>

    @if($overdueRfis->isNotEmpty())
        <div class="rounded-xl border border-rose-300 bg-rose-50 p-4 space-y-1">
            <div class="text-xs font-black uppercase text-rose-800">🚨 Overdue Engineering RFIs (เกินกำหนดเวลา SLA ตอบกลับจากผู้ออกแบบ/ที่ปรึกษา)</div>
            <ul class="text-xs text-rose-900 space-y-1">
                @foreach($overdueRfis as $oRfi)
                    <li><strong>{{ $oRfi->reference_no }} · {{ $oRfi->subject }}</strong> ({{ $oRfi->discipline }}) — Due {{ $oRfi->due_date->format('d/m/Y') }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
        <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
            <div class="text-[11px] font-bold uppercase text-slate-400">Total RFIs</div>
            <div class="text-xl font-black">{{ $stats['total_rfis'] }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
            <div class="text-[11px] font-bold uppercase text-slate-400">Open RFIs</div>
            <div class="text-xl font-black text-amber-600">{{ $stats['open_rfis'] }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm border border-rose-200 bg-rose-50/40">
            <div class="text-[11px] font-bold uppercase text-rose-700">Overdue SLA RFIs</div>
            <div class="text-xl font-black text-rose-800">{{ $stats['overdue_rfis'] }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
            <div class="text-[11px] font-bold uppercase text-slate-400">Total Submittals</div>
            <div class="text-xl font-black text-indigo-800">{{ $stats['total_submittals'] }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
            <div class="text-[11px] font-bold uppercase text-slate-400">Approved Submittals</div>
            <div class="text-xl font-black text-emerald-700">{{ $stats['approved_submittals'] }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm border border-amber-200 bg-amber-50/40">
            <div class="text-[11px] font-bold uppercase text-amber-800">Open Design Constraints</div>
            <div class="text-xl font-black text-amber-900">{{ $stats['open_design_constraints'] }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- 7A: Issue RFI Form -->
        <form action="{{ route('projects.engineering.rfi.store', $project) }}" method="POST" class="space-y-3 rounded-xl bg-white p-6 shadow-sm border border-slate-200">
            @csrf
            <div class="border-b border-slate-100 pb-2">
                <span class="text-xs font-bold uppercase text-indigo-700">7A · Issue Request for Information (RFI)</span>
                <h2 class="text-lg font-bold text-slate-900">ตั้งคำถามขอความชัดเจนด้านแบบก่อสร้าง</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700">Discipline *</label>
                    <select name="discipline" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs font-bold">
                        @foreach(\App\Models\EngineeringRfi::DISCIPLINES as $d)
                            <option value="{{ $d }}">{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Drawing Ref</label>
                    <input type="text" name="drawing_ref" placeholder="e.g. ST-302 Rev.1" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">SLA Due Date *</label>
                    <input type="date" name="due_date" value="{{ now()->addDays(7)->toDateString() }}" required class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700">Subject *</label>
                <input type="text" name="subject" required placeholder="e.g. Rebar Clash at Pier Cap P12 Post-Tensioning Anchorage" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700">Technical Question *</label>
                <textarea name="question" rows="2" required placeholder="Describe discrepancy or clarification needed..." class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700">Proposed Contractor Solution</label>
                    <input type="text" name="proposed_solution" placeholder="Optional field proposal..." class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Linked WBS Task (Auto-resolves DESIGN_RFI constraint)</label>
                    <select name="wbs_task_id" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs">
                        <option value="">— Optional —</option>
                        @foreach($tasks as $task)
                            <option value="{{ $task->id }}">{{ $task->wbs_code }} · {{ $task->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-xs font-bold text-white hover:bg-indigo-700">+ Submit Engineering RFI</button>
        </form>

        <!-- 7B: Create Shop Drawing / Material Submittal Form -->
        <form action="{{ route('projects.engineering.submittals.store', $project) }}" method="POST" class="space-y-3 rounded-xl bg-white p-6 shadow-sm border border-slate-200">
            @csrf
            <div class="border-b border-slate-100 pb-2">
                <span class="text-xs font-bold uppercase text-violet-700">7B · Technical Submittal (Shop Drawing / Material Sample)</span>
                <h2 class="text-lg font-bold text-slate-900">ยื่นขออนุมัติแบบ Shop Drawing และตัวอย่างวัสดุ</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700">Submittal Type *</label>
                    <select name="submittal_type" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs font-bold">
                        @foreach(\App\Models\EngineeringSubmittal::TYPES as $st)
                            <option value="{{ $st }}">{{ str_replace('_', ' ', $st) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Discipline *</label>
                    <select name="discipline" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs font-bold">
                        @foreach(\App\Models\EngineeringSubmittal::DISCIPLINES as $d)
                            <option value="{{ $d }}">{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Initial Revision</label>
                    <input type="text" name="revision" value="Rev.0" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs font-mono">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700">Submittal Title *</label>
                <input type="text" name="title" required placeholder="e.g. Shop Drawing for Precast Segment Type S1-S8 Tendon Profile" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700">Specification Section</label>
                    <input type="text" name="spec_section" placeholder="e.g. Div 03 · Section 03410" class="mt-1 w-full rounded border border-slate-300 px-3 py-1.5 text-xs">
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

            <button type="submit" class="w-full rounded-lg bg-violet-600 px-4 py-2 text-xs font-bold text-white hover:bg-violet-700">+ Log Technical Submittal</button>
        </form>
    </div>

    <!-- 7A Table: RFI Register -->
    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-50 px-6 py-3 font-bold text-xs text-slate-800">7A · Request for Information (RFI) Register</div>
        @if($rfis->isEmpty())
            <div class="p-8 text-center text-xs text-slate-500">ยังไม่มีรายการ RFI (No RFIs issued yet)</div>
        @else
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[10px] uppercase text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-2.5">Ref / Discipline</th>
                        <th class="px-6 py-2.5">Subject & Question</th>
                        <th class="px-6 py-2.5">SLA Due Date</th>
                        <th class="px-6 py-2.5 text-center">Status</th>
                        <th class="px-6 py-2.5">Official Consultant Response / Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($rfis as $rfi)
                        <tr>
                            <td class="px-6 py-3">
                                <div class="font-mono text-[11px] font-bold text-indigo-800">{{ $rfi->reference_no }}</div>
                                <div class="text-[10px] text-slate-500">{{ $rfi->discipline }} @if($rfi->drawing_ref) · {{ $rfi->drawing_ref }} @endif</div>
                            </td>
                            <td class="px-6 py-3">
                                <div class="font-bold text-slate-900">{{ $rfi->subject }}</div>
                                <div class="text-[11px] text-slate-600">{{ $rfi->question }}</div>
                                @if($rfi->task)<div class="text-[10px] text-slate-400">Task: {{ $rfi->task->wbs_code }}</div>@endif
                            </td>
                            <td class="px-6 py-3 font-mono text-[11px]">
                                {{ $rfi->due_date->format('d/m/Y') }}
                                @if($rfi->isOverdue())
                                    <div class="text-[10px] font-bold text-rose-700">🚨 OVERDUE</div>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-center">
                                <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ $rfi->status === 'CLOSED' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ $rfi->status }}</span>
                            </td>
                            <td class="px-6 py-3">
                                @if($rfi->status === 'CLOSED')
                                    <div class="text-xs font-semibold text-emerald-900">{{ $rfi->official_response }}</div>
                                    <div class="text-[10px] text-slate-400">Answered by {{ $rfi->responder?->name }}</div>
                                @else
                                    <form action="{{ route('projects.engineering.rfi.answer', [$project, $rfi]) }}" method="POST" class="flex gap-1.5">
                                        @csrf
                                        <input type="text" name="official_response" required placeholder="Official design answer..." class="flex-1 rounded border border-slate-300 px-2 py-1 text-[11px]">
                                        <button type="submit" class="rounded bg-emerald-600 px-3 py-1 text-[11px] font-bold text-white hover:bg-emerald-700">Answer & Close</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- 7B Table: Submittals Register -->
    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-50 px-6 py-3 font-bold text-xs text-slate-800">7B · Shop Drawing & Material Submittal Register</div>
        @if($submittals->isEmpty())
            <div class="p-8 text-center text-xs text-slate-500">ยังไม่มีรายการยื่นขออนุมัติแบบ/วัสดุ (No submittals logged yet)</div>
        @else
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[10px] uppercase text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-2.5">Ref / Revision</th>
                        <th class="px-6 py-2.5">Type / Title</th>
                        <th class="px-6 py-2.5 text-center">Status</th>
                        <th class="px-6 py-2.5">Consultant Review / Revision Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($submittals as $sub)
                        <tr>
                            <td class="px-6 py-3">
                                <div class="font-mono text-[11px] font-bold text-violet-800">{{ $sub->reference_no }}</div>
                                <span class="rounded bg-slate-100 px-2 py-0.5 font-mono text-[10px] font-bold">{{ $sub->revision }}</span>
                            </td>
                            <td class="px-6 py-3">
                                <div class="font-bold text-slate-900">{{ $sub->title }}</div>
                                <div class="text-[11px] text-slate-500">{{ str_replace('_', ' ', $sub->submittal_type) }} · {{ $sub->discipline }} @if($sub->spec_section) · {{ $sub->spec_section }} @endif</div>
                            </td>
                            <td class="px-6 py-3 text-center">
                                <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ $sub->isApprovedForConstruction() ? 'bg-emerald-100 text-emerald-800' : ($sub->status === 'PENDING_REVIEW' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                    {{ $sub->status }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                @if($sub->status === 'PENDING_REVIEW')
                                    <form action="{{ route('projects.engineering.submittals.review', [$project, $sub]) }}" method="POST" class="flex flex-wrap gap-1.5">
                                        @csrf
                                        <select name="decision" class="rounded border border-slate-300 px-2 py-1 text-[11px] font-bold">
                                            @foreach(\App\Models\EngineeringSubmittal::REVIEW_DECISIONS as $dec)
                                                <option value="{{ $dec }}">{{ $dec }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="review_comments" placeholder="Consultant remarks..." class="rounded border border-slate-300 px-2 py-1 text-[11px]">
                                        <button type="submit" class="rounded bg-violet-600 px-3 py-1 text-[11px] font-bold text-white hover:bg-violet-700">Record Review</button>
                                    </form>
                                @elseif(in_array($sub->status, ['REVISE_AND_RESUBMIT', 'REJECTED'], true))
                                    <div class="text-[11px] text-rose-800 mb-1">{{ $sub->review_comments }}</div>
                                    <form action="{{ route('projects.engineering.submittals.resubmit', [$project, $sub]) }}" method="POST" class="flex gap-1.5">
                                        @csrf
                                        <input type="text" name="revision" value="Rev.1" required class="w-20 rounded border border-slate-300 px-2 py-1 text-[11px] font-mono">
                                        <button type="submit" class="rounded bg-amber-600 px-3 py-1 text-[11px] font-bold text-white hover:bg-amber-700">Resubmit Revision</button>
                                    </form>
                                @else
                                    <div class="text-[11px] text-emerald-900">{{ $sub->review_comments }}</div>
                                    <div class="text-[10px] text-slate-400">by {{ $sub->reviewer?->name }}</div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

</main>
</body>
</html>
