<x-layouts.app :project="$project" title="{{ $project->code }} · Quality Control & NCR Register">
    <div class="space-y-6">

        <!-- Project Sub-navigation Ribbon (Unified MindInventory Tabs) -->
        <x-ced.project-subnav :project="$project" active="projects.ncr.index" />

        <!-- Top Header & Action Bar -->
        <header class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200/80 pb-5">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="px-2.5 py-0.5 rounded text-xs font-mono font-bold bg-orange-100 text-safety-600 border border-orange-200/70">
                        {{ $project->code }}
                    </span>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">รายงานข้อบกพร่องคุณภาพงาน (Non-Conformance Reports - NCR)</h1>
                </div>
                <p class="text-xs text-slate-500 mt-1">ระบบควบคุมคุณภาพ ตรวจจับข้อบกพร่องหน้างาน (CAPA Workflow) และเชื่อมโยงการระงับเบิกจ่ายเงินค่างวด (IPC Payment Hold) อัตโนมัติ</p>
            </div>
            <div>
                <a href="{{ route('projects.ncr.create', $project) }}" class="inline-flex items-center gap-2 rounded-xl bg-rose-600 hover:bg-rose-700 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    + เปิดใบแจ้งงานบกพร่องใหม่ (Issue New NCR)
                </a>
            </div>
        </header>

        <!-- Construction Lifecycle Process Flow (Stage 5 Active) -->
        <x-ced.workflow-stepper :project="$project" active="quality" />

        @if(session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-900 shadow-sm">{{ session('status') }}</div>
        @endif

        <!-- KPI Summary Cards -->
        <section class="grid grid-cols-2 lg:grid-cols-5 gap-4" aria-label="NCR Quality Summary">
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">NCR ทั้งหมด (Total NCRs)</span>
                <div class="mt-2 text-2xl font-black text-slate-900">{{ $stats['total'] }}</div>
                <span class="text-[10px] text-slate-400">บันทึกข้อบกพร่อง (Defects recorded)</span>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">รอแก้ไข/ไม่ผ่าน (Open / Rejected)</span>
                <div class="mt-2 text-2xl font-black text-rose-700">{{ $stats['open'] }}</div>
                <span class="text-[10px] text-slate-400">อยู่ระหว่างแก้ไข (Under CAPA)</span>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">รอ QC ตรวจงาน (Awaiting QC)</span>
                <div class="mt-2 text-2xl font-black text-amber-600">{{ $stats['awaiting_verification'] }}</div>
                <span class="text-[10px] text-slate-400">พร้อมตรวจสอบ (Ready to verify)</span>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">ปิดงานแก้ไขแล้ว (Closed)</span>
                <div class="mt-2 text-2xl font-black text-emerald-700">{{ $stats['closed'] }}</div>
                <span class="text-[10px] text-slate-400">ผ่านเกณฑ์คุณภาพ (Compliant)</span>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-card border border-rose-200 bg-rose-50/25">
                <span class="text-[11px] font-bold uppercase tracking-wider text-rose-700">ระงับเบิกค่างวด (IPC Holds)</span>
                <div class="mt-2 text-2xl font-black text-rose-800">{{ $stats['blocking_payment'] }} <span class="text-xs font-normal text-slate-500">NCRs</span></div>
                <span class="text-[10px] text-rose-600 font-semibold">ระงับเบิกเงินค่างวด WBS</span>
            </div>
        </section>

        <!-- NCR Register Table -->
        <section class="rounded-2xl bg-white shadow-card border border-slate-200/80 overflow-hidden">
            <div class="border-b border-slate-200/80 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Quality Non-Conformance Reports Register (ทะเบียนข้อบกพร่องด้านคุณภาพ)</h2>
                    <p class="text-[11px] text-slate-500">ISO 9001 / Construction site non-conformance log & payment barrier status</p>
                </div>
                <span class="text-xs text-slate-500 font-mono font-bold">{{ $ncrs->count() }} Records</span>
            </div>

            @if($ncrs->isEmpty())
                <div class="p-12 text-center text-sm text-slate-500">
                    <div class="mx-auto w-12 h-12 text-slate-300 mb-2">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <p class="font-bold text-slate-700">ยังไม่มีรายการแจ้งข้อบกพร่องหน้างาน (No NCRs recorded)</p>
                    <p class="text-xs text-slate-400 mt-1">คุณภาพงานก่อสร้างสอดคล้องตามมาตรฐาน 100%</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 font-bold border-b border-slate-200/80">
                            <tr>
                                <th class="px-6 py-3.5">Ref / Title</th>
                                <th class="px-6 py-3.5">WBS Task / Zone</th>
                                <th class="px-6 py-3.5 text-center">Category / Severity</th>
                                <th class="px-6 py-3.5 text-center">IPC Payment Gate</th>
                                <th class="px-6 py-3.5 text-center">Status</th>
                                <th class="px-6 py-3.5 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach($ncrs as $ncr)
                                @php
                                    $sevBadge = match($ncr->severity) {
                                        'CRITICAL' => 'bg-rose-100 text-rose-800 border-rose-300',
                                        'MAJOR' => 'bg-amber-100 text-amber-900 border-amber-300',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200',
                                    };
                                    $statusBadge = match($ncr->status) {
                                        'OPEN' => 'bg-rose-100 text-rose-800 border-rose-200',
                                        'RECTIFICATION_SUBMITTED' => 'bg-amber-100 text-amber-800 border-amber-200',
                                        'REJECTED_RECTIFICATION' => 'bg-rose-200 text-rose-900 border-rose-300',
                                        'CLOSED' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                        default => 'bg-slate-100 text-slate-800 border-slate-200',
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-6 py-4">
                                        <div class="font-mono text-[11px] text-slate-500">{{ $ncr->reference_no }}</div>
                                        <div class="font-bold text-slate-900">{{ $ncr->title }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($ncr->task)
                                            <div class="font-bold text-slate-800">{{ $ncr->task->wbs_code }} &middot; {{ $ncr->task->name }}</div>
                                        @else
                                            <div class="text-slate-400">General Site</div>
                                        @endif
                                        @if($ncr->location_zone)
                                            <div class="text-[11px] text-slate-500">📍 {{ $ncr->location_zone }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-block rounded-full border px-2.5 py-0.5 text-[10px] font-bold {{ $sevBadge }}">{{ $ncr->severity }}</span>
                                        <div class="mt-1 text-[10px] text-slate-500 font-mono">{{ $ncr->category }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($ncr->blocksPayment())
                                            <span class="inline-block rounded-full bg-rose-100 border border-rose-300 px-2.5 py-0.5 text-[10px] font-bold text-rose-800">🚫 HOLD PAYMENT</span>
                                        @elseif($ncr->hold_payment && $ncr->status === 'CLOSED')
                                            <span class="inline-block rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 text-[10px] font-bold text-emerald-700">✓ Hold Released</span>
                                        @else
                                            <span class="text-[10px] text-slate-400">Advisory</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-block rounded-full border px-2.5 py-0.5 text-[11px] font-bold {{ $statusBadge }}">{{ $ncr->status }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('projects.ncr.show', [$project, $ncr]) }}" class="inline-flex items-center gap-1 rounded-xl bg-slate-100 hover:bg-slate-200 px-3 py-1.5 text-xs font-bold text-slate-700 transition">
                                            <span>Inspect</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        </a>
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
