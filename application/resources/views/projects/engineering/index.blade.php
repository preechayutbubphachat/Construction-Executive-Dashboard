<x-layouts.app :project="$project" title="{{ $project->code }} · Engineering Document Control (RFI & Submittals)">
    <div class="space-y-6">

        <!-- Project Sub-navigation Ribbon (Unified MindInventory Tabs) -->
        <x-ced.project-subnav :project="$project" active="projects.engineering.index" />

        <!-- Top Header Bar -->
        <header class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200/80 pb-5">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="px-2.5 py-0.5 rounded text-xs font-mono font-bold bg-orange-100 text-safety-600 border border-orange-200/70">
                        {{ $project->code }}
                    </span>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Request for Information (RFI) & Technical Submittal Workflow</h1>
                </div>
                <p class="text-xs text-slate-500 mt-1">บริหารคำถามด้านวิศวกรรม (RFI) พร้อมแจ้งเตือน SLA เกินกำหนด, ปลดล็อก DESIGN_RFI Constraint อัตโนมัติ และควบคุม Revision ของ Shop Drawing / Material Submittals</p>
            </div>
        </header>

        <!-- Construction Lifecycle Process Flow (Stage 2 Active) -->
        <x-ced.workflow-stepper :project="$project" active="engineering" />

        @if(session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-900 shadow-sm">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800 shadow-sm">
                @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif

        @if($overdueRfis->isNotEmpty())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 space-y-1 shadow-sm">
                <div class="text-xs font-black uppercase text-rose-800 flex items-center gap-1.5">
                    <span>🚨</span> Overdue Engineering RFIs (เกินกำหนดเวลา SLA ตอบกลับจากผู้ออกแบบ/ที่ปรึกษา)
                </div>
                <ul class="text-xs text-rose-900 space-y-1 pl-5 list-disc">
                    @foreach($overdueRfis as $oRfi)
                        <li><strong>{{ $oRfi->reference_no }} &middot; {{ $oRfi->subject }}</strong> ({{ $oRfi->discipline }}) — Due {{ $oRfi->due_date->format('d/m/Y') }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- KPI Metrics Grid -->
        <section class="grid grid-cols-2 lg:grid-cols-6 gap-4" aria-label="Engineering Metrics">
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">คำถามทั้งหมด (Total RFIs)</span>
                <div class="mt-2 text-2xl font-black text-slate-900">{{ $stats['total_rfis'] }}</div>
                <span class="text-[10px] text-slate-400">ข้อซักถามแบบวิศวกรรม</span>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">รอคำตอบ (Open RFIs)</span>
                <div class="mt-2 text-2xl font-black text-amber-600">{{ $stats['open_rfis'] }}</div>
                <span class="text-[10px] text-slate-400">รอที่ปรึกษาตอบกลับ</span>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-card border border-rose-200 bg-rose-50/20">
                <span class="text-[11px] font-bold uppercase tracking-wider text-rose-700">เกินกำหนด (Overdue SLA RFIs)</span>
                <div class="mt-2 text-2xl font-black text-rose-800">{{ $stats['overdue_rfis'] }}</div>
                <span class="text-[10px] text-rose-600 font-semibold">เกินกำหนดระยะเวลา SLA</span>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">ส่งอนุมัติทั้งหมด (Total Submittals)</span>
                <div class="mt-2 text-2xl font-black text-slate-900">{{ $stats['total_submittals'] }}</div>
                <span class="text-[10px] text-slate-400">แบบและตัวอย่างวัสดุ</span>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">อนุมัติแล้ว (Approved Submittals)</span>
                <div class="mt-2 text-2xl font-black text-emerald-700">{{ $stats['approved_submittals'] }}</div>
                <span class="text-[10px] text-slate-400">อนุมัติให้นำไปก่อสร้างได้</span>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-card border border-orange-200 bg-orange-50/20">
                <span class="text-[11px] font-bold uppercase tracking-wider text-safety-700">อุปสรรคด้านแบบ (Open Design Constraints)</span>
                <div class="mt-2 text-2xl font-black text-safety-600">{{ $stats['open_design_constraints'] }}</div>
                <span class="text-[10px] text-safety-700 font-semibold">ที่กระทบต่อการเข้าทำงาน</span>
            </div>
        </section>

        <!-- Twin Form Container (7A RFI & 7B Submittals) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- 7A: Issue RFI Form -->
            <form action="{{ route('projects.engineering.rfi.store', $project) }}" method="POST" class="space-y-4 rounded-2xl bg-white p-6 shadow-card border border-slate-200/80">
                @csrf
                <div class="border-b border-slate-100 pb-3">
                    <span class="text-xs font-mono font-bold uppercase tracking-wider text-safety-600 bg-orange-100 border border-orange-200/70 px-2.5 py-0.5 rounded-full inline-block">
                        7A &middot; Issue Request for Information (RFI)
                    </span>
                    <h2 class="text-base font-black text-slate-900 mt-1">ตั้งคำถามขอความชัดเจนด้านแบบก่อสร้าง</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Discipline *</label>
                        <select name="discipline" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-bold focus:border-safety-500 focus:ring-safety-500">
                            @foreach(\App\Models\EngineeringRfi::DISCIPLINES as $d)
                                <option value="{{ $d }}">{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Drawing Ref</label>
                        <input type="text" name="drawing_ref" placeholder="e.g. ST-302 Rev.1" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-mono focus:border-safety-500 focus:ring-safety-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">SLA Due Date *</label>
                        <input type="date" name="due_date" value="{{ now()->addDays(7)->toDateString() }}" required class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs focus:border-safety-500 focus:ring-safety-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700">Subject *</label>
                    <input type="text" name="subject" required placeholder="e.g. Rebar Clash at Pier Cap P12 Post-Tensioning Anchorage" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs focus:border-safety-500 focus:ring-safety-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700">Technical Question *</label>
                    <textarea name="question" rows="2" required placeholder="Describe discrepancy or clarification needed..." class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs focus:border-safety-500 focus:ring-safety-500"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Proposed Contractor Solution</label>
                        <input type="text" name="proposed_solution" placeholder="Optional field proposal..." class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs focus:border-safety-500 focus:ring-safety-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Linked WBS Task (Auto-resolves constraint)</label>
                        <select name="wbs_task_id" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs focus:border-safety-500 focus:ring-safety-500">
                            <option value="">— Optional —</option>
                            @foreach($tasks as $task)
                                <option value="{{ $task->id }}">{{ $task->wbs_code }} · {{ $task->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <button type="submit" class="w-full rounded-xl bg-safety-600 hover:bg-safety-700 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition">+ Submit Engineering RFI</button>
            </form>

            <!-- 7B: Create Shop Drawing / Material Submittal Form -->
            <form action="{{ route('projects.engineering.submittals.store', $project) }}" method="POST" class="space-y-4 rounded-2xl bg-white p-6 shadow-card border border-slate-200/80">
                @csrf
                <div class="border-b border-slate-100 pb-3">
                    <span class="text-xs font-mono font-bold uppercase tracking-wider text-indigo-700 bg-indigo-50 border border-indigo-200 px-2.5 py-0.5 rounded-full inline-block">
                        7B &middot; Technical Submittal
                    </span>
                    <h2 class="text-base font-black text-slate-900 mt-1">ยื่นขออนุมัติแบบ Shop Drawing และตัวอย่างวัสดุ</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Submittal Type *</label>
                        <select name="submittal_type" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-bold focus:border-safety-500 focus:ring-safety-500">
                            @foreach(\App\Models\EngineeringSubmittal::TYPES as $st)
                                <option value="{{ $st }}">{{ str_replace('_', ' ', $st) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Discipline *</label>
                        <select name="discipline" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-bold focus:border-safety-500 focus:ring-safety-500">
                            @foreach(\App\Models\EngineeringSubmittal::DISCIPLINES as $d)
                                <option value="{{ $d }}">{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Initial Revision</label>
                        <input type="text" name="revision" value="Rev.0" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-mono focus:border-safety-500 focus:ring-safety-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700">Submittal Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Shop Drawing for Precast Segment Type S1-S8 Tendon Profile" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs focus:border-safety-500 focus:ring-safety-500">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Specification Section</label>
                        <input type="text" name="spec_section" placeholder="e.g. Div 03 · Section 03410" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs focus:border-safety-500 focus:ring-safety-500">
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

                <button type="submit" class="w-full rounded-xl bg-slate-900 hover:bg-slate-800 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition">+ Log Technical Submittal</button>
            </form>
        </div>

        <!-- 7A Table: RFI Register -->
        <section class="rounded-2xl bg-white shadow-card border border-slate-200/80 overflow-hidden">
            <div class="border-b border-slate-200/80 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">7A &middot; Request for Information (RFI) Register</h2>
                    <p class="text-[11px] text-slate-500">Engineering clarification tracking with contractual SLA timers</p>
                </div>
                <span class="text-xs text-slate-500 font-mono font-bold">{{ $rfis->count() }} Records</span>
            </div>
            @if($rfis->isEmpty())
                <div class="p-12 text-center text-xs text-slate-500">ยังไม่มีรายการ RFI (No RFIs issued yet)</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 font-bold border-b border-slate-200/80">
                            <tr>
                                <th class="px-6 py-3.5">Ref / Discipline</th>
                                <th class="px-6 py-3.5">Subject & Question</th>
                                <th class="px-6 py-3.5">SLA Due Date</th>
                                <th class="px-6 py-3.5 text-center">Status</th>
                                <th class="px-6 py-3.5">Official Consultant Response / Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach($rfis as $rfi)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-6 py-4">
                                        <div class="font-mono text-[11px] font-bold text-safety-600">{{ $rfi->reference_no }}</div>
                                        <div class="text-[10px] text-slate-500">{{ $rfi->discipline }} @if($rfi->drawing_ref) · {{ $rfi->drawing_ref }} @endif</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-900">{{ $rfi->subject }}</div>
                                        <div class="text-[11px] text-slate-600">{{ $rfi->question }}</div>
                                        @if($rfi->task)<div class="text-[10px] text-slate-400 mt-0.5">Task: {{ $rfi->task->wbs_code }}</div>@endif
                                    </td>
                                    <td class="px-6 py-4 font-mono text-[11px]">
                                        {{ $rfi->due_date->format('d/m/Y') }}
                                        @if($rfi->isOverdue())
                                            <div class="text-[10px] font-bold text-rose-700">🚨 OVERDUE</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-block rounded-full border px-2.5 py-0.5 text-[10px] font-bold {{ $rfi->status === 'CLOSED' ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-amber-100 text-amber-800 border-amber-200' }}">{{ $rfi->status }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($rfi->status === 'CLOSED')
                                            <div class="text-xs font-semibold text-emerald-900">{{ $rfi->official_response }}</div>
                                            <div class="text-[10px] text-slate-400">Answered by {{ $rfi->responder?->name }}</div>
                                        @else
                                            <form action="{{ route('projects.engineering.rfi.answer', [$project, $rfi]) }}" method="POST" class="flex gap-1.5">
                                                @csrf
                                                <input type="text" name="official_response" required placeholder="Official design answer..." class="flex-1 rounded-xl border border-slate-200 px-3 py-1.5 text-xs">
                                                <button type="submit" class="rounded-xl bg-emerald-600 hover:bg-emerald-700 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition">Answer & Close</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <!-- 7B Table: Submittals Register -->
        <section class="rounded-2xl bg-white shadow-card border border-slate-200/80 overflow-hidden">
            <div class="border-b border-slate-200/80 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">7B &middot; Shop Drawing & Material Submittal Register</h2>
                    <p class="text-[11px] text-slate-500">Revision control, consultant review stamp, and approval ledger</p>
                </div>
                <span class="text-xs text-slate-500 font-mono font-bold">{{ $submittals->count() }} Records</span>
            </div>
            @if($submittals->isEmpty())
                <div class="p-12 text-center text-xs text-slate-500">ยังไม่มีรายการยื่นขออนุมัติแบบ/วัสดุ (No submittals logged yet)</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 font-bold border-b border-slate-200/80">
                            <tr>
                                <th class="px-6 py-3.5">Ref / Revision</th>
                                <th class="px-6 py-3.5">Type / Title</th>
                                <th class="px-6 py-3.5 text-center">Status</th>
                                <th class="px-6 py-3.5">Consultant Review / Revision Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach($submittals as $sub)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-6 py-4">
                                        <div class="font-mono text-[11px] font-bold text-slate-900">{{ $sub->reference_no }}</div>
                                        <span class="inline-block rounded border border-slate-200 bg-slate-100 px-2 py-0.5 font-mono text-[10px] font-bold text-slate-700 mt-0.5">{{ $sub->revision }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-900">{{ $sub->title }}</div>
                                        <div class="text-[11px] text-slate-500">{{ str_replace('_', ' ', $sub->submittal_type) }} · {{ $sub->discipline }} @if($sub->spec_section) · {{ $sub->spec_section }} @endif</div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-block rounded-full border px-2.5 py-0.5 text-[10px] font-bold {{ $sub->isApprovedForConstruction() ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : ($sub->status === 'PENDING_REVIEW' ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-rose-100 text-rose-800 border-rose-200') }}">
                                            {{ $sub->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($sub->status === 'PENDING_REVIEW')
                                            <form action="{{ route('projects.engineering.submittals.review', [$project, $sub]) }}" method="POST" class="flex flex-wrap gap-1.5">
                                                @csrf
                                                <select name="decision" class="rounded-xl border border-slate-200 bg-white px-2 py-1 text-[11px] font-bold focus:border-safety-500 focus:ring-safety-500">
                                                    @foreach(\App\Models\EngineeringSubmittal::REVIEW_DECISIONS as $dec)
                                                        <option value="{{ $dec }}">{{ $dec }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="text" name="review_comments" placeholder="Consultant remarks..." class="rounded-xl border border-slate-200 bg-white px-3 py-1 text-[11px] focus:border-safety-500 focus:ring-safety-500">
                                                <button type="submit" class="rounded-xl bg-slate-900 hover:bg-slate-800 px-3 py-1 text-[11px] font-bold text-white shadow-sm transition">Record Review</button>
                                            </form>
                                        @elseif(in_array($sub->status, ['REVISE_AND_RESUBMIT', 'REJECTED'], true))
                                            <div class="text-[11px] text-rose-800 mb-1 font-medium">{{ $sub->review_comments }}</div>
                                            <form action="{{ route('projects.engineering.submittals.resubmit', [$project, $sub]) }}" method="POST" class="flex gap-1.5">
                                                @csrf
                                                <input type="text" name="revision" value="Rev.1" required class="w-20 rounded-xl border border-slate-200 bg-white px-2 py-1 text-[11px] font-mono">
                                                <button type="submit" class="rounded-xl bg-safety-600 hover:bg-safety-700 px-3 py-1 text-[11px] font-bold text-white shadow-sm transition">Resubmit Revision</button>
                                            </form>
                                        @else
                                            <div class="text-[11px] text-emerald-900 font-semibold">{{ $sub->review_comments }}</div>
                                            <div class="text-[10px] text-slate-400">by {{ $sub->reviewer?->name }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-layouts.app>
