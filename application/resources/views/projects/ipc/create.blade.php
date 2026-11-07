<x-layouts.app :project="$project" title="{{ $project->code }} · Submit New Interim Payment Application">
    <div class="max-w-5xl mx-auto space-y-6">

        <!-- Top Breadcrumbs -->
        <div class="flex items-center gap-2 text-xs font-bold">
            <a class="text-safety-600 hover:text-safety-700 hover:underline transition" href="{{ route('projects.ipc.index', $project) }}">&larr; Back to IPC Register</a>
            <span class="text-slate-300">&bull;</span>
            <span class="text-slate-600">New Payment Application</span>
        </div>

        <!-- Error Alerts -->
        @if($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800 shadow-sm">
                <div class="font-bold text-sm">Please correct the following errors:</div>
                <ul class="mt-1 list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form Container -->
        <form action="{{ route('projects.ipc.store', $project) }}" method="POST" class="space-y-6">
            @csrf

            <!-- General Info Card -->
            <div class="rounded-2xl bg-white p-6 shadow-card border border-slate-200/80 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <span class="text-xs font-mono font-bold uppercase tracking-wider text-safety-600 bg-orange-100 border border-orange-200/70 px-2.5 py-0.5 rounded-full inline-block">
                        IPC Form Part 1
                    </span>
                    <h1 class="text-xl font-black text-slate-900 mt-1">Application Header & Billing Information</h1>
                    <p class="text-xs text-slate-500">กรอกข้อมูลผู้รับเหมาและงวดเวลาการประเมินผลงาน (Valuation Period)</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Certificate Reference Number (Generated)</label>
                        <input type="text" value="{{ $nextRef }}" disabled class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-mono font-bold text-slate-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Certificate No. (ลำดับงวด)</label>
                        <input type="number" value="{{ $nextNumber }}" disabled class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Contractor / Subcontractor Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="contractor_name" value="{{ old('contractor_name', 'Main Contractor Construction Joint Venture') }}" required class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-900 focus:border-safety-500 focus:ring-safety-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Contractor Invoice / Tax Invoice No.</label>
                        <input type="text" name="contractor_invoice_no" value="{{ old('contractor_invoice_no') }}" placeholder="e.g. INV-2026/089" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-900 focus:border-safety-500 focus:ring-safety-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Valuation Period Start <span class="text-rose-500">*</span></label>
                        <input type="date" name="period_start" value="{{ old('period_start', now()->startOfMonth()->toDateString()) }}" required class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-900 focus:border-safety-500 focus:ring-safety-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Valuation Period End <span class="text-rose-500">*</span></label>
                        <input type="date" name="period_end" value="{{ old('period_end', now()->endOfMonth()->toDateString()) }}" required class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-900 focus:border-safety-500 focus:ring-safety-500">
                    </div>
                </div>
            </div>

            <!-- Commercial Deductions & Tax Card -->
            <div class="rounded-2xl bg-white p-6 shadow-card border border-slate-200/80 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <span class="text-xs font-mono font-bold uppercase tracking-wider text-safety-600 bg-orange-100 border border-orange-200/70 px-2.5 py-0.5 rounded-full inline-block">
                        IPC Form Part 2
                    </span>
                    <h2 class="text-lg font-bold text-slate-900 mt-1">Commercial Deductions & Statutory VAT Rates</h2>
                    <p class="text-xs text-slate-500">อัตราการหักเงินประกันผลงาน (Retention), การหักคืนเงินจ่ายล่วงหน้า (Advance Recovery), และภาษีมูลค่าเพิ่ม (VAT 7%)</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Retention Rate (%)</label>
                        <div class="relative mt-1.5">
                            <input type="number" step="0.1" min="0" max="20" name="retention_rate_percent" value="{{ old('retention_rate_percent', '5.0') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs focus:border-safety-500 focus:ring-safety-500">
                            <span class="absolute right-3 top-2 text-xs text-slate-400">%</span>
                        </div>
                        <span class="text-[10px] text-slate-400">หักเงินประกัน (มาตรฐาน 5-10%)</span>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Advance Recovery (THB)</label>
                        <input type="number" step="0.01" min="0" name="advance_recovery_deduction" value="{{ old('advance_recovery_deduction', '0.00') }}" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs focus:border-safety-500 focus:ring-safety-500">
                        <span class="text-[10px] text-slate-400">หักคืนเงินจ่ายล่วงหน้างวดนี้</span>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Other Deductions (THB)</label>
                        <input type="number" step="0.01" min="0" name="other_deductions" value="{{ old('other_deductions', '0.00') }}" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs focus:border-safety-500 focus:ring-safety-500">
                        <span class="text-[10px] text-slate-400">ค่าน้ำ/ค่าไฟ/ค่าปรับ (ถ้ามี)</span>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700">VAT Rate (%)</label>
                        <div class="relative mt-1.5">
                            <input type="number" step="0.1" min="0" max="20" name="vat_rate_percent" value="{{ old('vat_rate_percent', '7.0') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs focus:border-safety-500 focus:ring-safety-500">
                            <span class="absolute right-3 top-2 text-xs text-slate-400">%</span>
                        </div>
                        <span class="text-[10px] text-slate-400">ภาษีมูลค่าเพิ่ม (ปกติ 7.0%)</span>
                    </div>
                </div>
            </div>

            <!-- WBS Breakdown Items Card -->
            <div class="rounded-2xl bg-white p-6 shadow-card border border-slate-200/80 space-y-4">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-mono font-bold uppercase tracking-wider text-safety-600 bg-orange-100 border border-orange-200/70 px-2.5 py-0.5 rounded-full inline-block">
                            IPC Form Part 3
                        </span>
                        <h2 class="text-lg font-bold text-slate-900 mt-1">Work Breakdown Structure (WBS) Claim Breakdown</h2>
                        <p class="text-xs text-slate-500">ระบุจำนวนเงินที่ขอเบิกสำหรับแต่ละ WBS Task ย่อย</p>
                    </div>
                    <span class="text-xs font-mono font-bold text-slate-500">{{ $tasks->count() }} Leaf Tasks Available</span>
                </div>

                @if($tasks->isEmpty())
                    <div class="p-6 text-center text-xs text-slate-500">
                        ไม่มี Leaf WBS Task ในระบบ คุณสามารถเพิ่มการขอเบิกแบบรวมยอดได้
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-600">
                            <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 font-bold border-b border-slate-200/80">
                                <tr>
                                    <th class="px-4 py-3">WBS Code & Task Name</th>
                                    <th class="px-4 py-3 text-right">Planned Cost (BAC)</th>
                                    <th class="px-4 py-3 text-center">Progress %</th>
                                    <th class="px-4 py-3 text-right w-48">Claim Amount (THB)</th>
                                    <th class="px-4 py-3">Remarks / Work Description</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($tasks as $index => $task)
                                    <tr class="hover:bg-slate-50/60 transition">
                                        <td class="px-4 py-3">
                                            <input type="hidden" name="items[{{ $index }}][wbs_task_id]" value="{{ $task->id }}">
                                            <div class="font-bold text-slate-900">{{ $task->wbs_code }} &middot; {{ $task->name }}</div>
                                            <div class="text-[10px] text-slate-400">{{ $task->status }}</div>
                                            @if(isset($blockingNcrsByTask[$task->id]))
                                                @foreach($blockingNcrsByTask[$task->id] as $bNcr)
                                                    <div class="mt-1 inline-block rounded bg-rose-100 border border-rose-300 px-2 py-0.5 text-[10px] font-bold text-rose-800">
                                                        🚫 Payment Hold: {{ $bNcr->reference_no }} ({{ $bNcr->severity }})
                                                    </div>
                                                @endforeach
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right font-mono text-slate-600 font-bold">
                                            ฿{{ number_format((float) ($task->planned_cost ?? 0), 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <input type="number" step="1" min="0" max="100" name="items[{{ $index }}][claimed_progress_percent]" placeholder="0" class="w-20 rounded-xl border border-slate-200 bg-slate-50/50 px-2 py-1 text-xs text-center focus:border-safety-500 focus:ring-safety-500">
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <input type="number" step="0.01" min="0" name="items[{{ $index }}][claimed_amount]" placeholder="0.00" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-2 py-1 text-xs text-right font-mono font-bold focus:border-safety-500 focus:ring-safety-500">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="text" name="items[{{ $index }}][remarks]" placeholder="รายละเอียดงานที่ส่งมอบ..." class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-2 py-1 text-xs text-slate-700 focus:border-safety-500 focus:ring-safety-500">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @if($approvedVos->isNotEmpty())
                    <div class="mt-4 border-t border-slate-100 pt-4">
                        <h3 class="text-sm font-bold text-slate-900">Approved Variation Orders (งานเพิ่ม VO ที่อนุมัติแล้ว)</h3>
                        <div class="mt-2 space-y-2">
                            @foreach($approvedVos as $vIndex => $vo)
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-2 items-center rounded-xl border border-emerald-100 bg-emerald-50/40 p-3 text-xs">
                                    <input type="hidden" name="items[{{ 1000 + $vIndex }}][variation_order_id]" value="{{ $vo->id }}">
                                    <div class="font-bold text-slate-800">{{ $vo->reference_no }} &middot; {{ $vo->title }}
                                        <div class="font-mono text-[10px] text-slate-500">Approved value ฿{{ number_format($vo->cost_impact, 2) }}</div>
                                    </div>
                                    <input type="number" step="1" min="0" max="100" name="items[{{ 1000 + $vIndex }}][current_percent]" placeholder="Cumulative % complete" class="rounded-xl border border-slate-200 px-2 py-1 text-xs focus:border-safety-500 focus:ring-safety-500">
                                    <input type="text" name="items[{{ 1000 + $vIndex }}][notes]" placeholder="Remarks" class="rounded-xl border border-slate-200 px-2 py-1 text-xs focus:border-safety-500 focus:ring-safety-500">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Submit Button Card -->
            <div class="rounded-2xl bg-white p-6 shadow-card border border-slate-200/80 flex flex-wrap items-center justify-between gap-4">
                <div class="text-xs text-slate-500">
                    เมื่อยื่นคำขอเบิกแล้ว เอกสารจะเข้าสู่สถานะ <span class="font-bold text-amber-700">SUBMITTED (รอตรวจหน้างานโดย Site Engineer)</span>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('projects.ipc.index', $project) }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-safety-600 hover:bg-safety-700 px-6 py-2.5 text-xs font-bold text-white shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Submit Payment Application (ยื่นคำขอเบิกเงิน)
                    </button>
                </div>
            </div>

        </form>
    </div>
</x-layouts.app>
