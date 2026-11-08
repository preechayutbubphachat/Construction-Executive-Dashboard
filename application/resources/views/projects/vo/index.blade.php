<x-layouts.app :project="$project" title="{{ $project->code }} · Variation Orders (VO Register)">
    <div class="space-y-6">

        <!-- Project Sub-navigation Ribbon (Unified MindInventory Tabs) -->
        <x-ced.project-subnav :project="$project" active="projects.vo.index" />

        <!-- Header Section -->
        <header class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200/80 pb-5">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="px-2.5 py-0.5 rounded text-xs font-mono font-bold bg-orange-100 text-safety-600 border border-orange-200/70">
                        {{ $project->code }}
                    </span>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">คำสั่งเปลี่ยนแปลงงานเพิ่ม-ลด (Variation Orders - VO Register)</h1>
                </div>
                <p class="text-xs text-slate-500 mt-1">ทะเบียนงานเพิ่ม-งานลด ประเมินผลกระทบด้านต้นทุนและเวลา (EOT) พร้อมปรับ BAC และกำหนดเสร็จอัตโนมัติเมื่ออนุมัติ</p>
            </div>
            <div>
                <a href="{{ route('projects.vo.create', $project) }}" class="inline-flex items-center gap-2 rounded-xl bg-safety-600 hover:bg-safety-700 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    + ยื่นขอเปลี่ยนแปลงงานใหม่ (New Variation Request)
                </a>
            </div>
        </header>

        <!-- Construction Lifecycle Process Flow (Stage 6 Active) -->
        <x-ced.workflow-stepper :project="$project" active="commercial" />

        @if(session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-900 shadow-sm">
                {{ session('status') }}
            </div>
        @endif

        <!-- KPI Summary Cards -->
        <section class="grid grid-cols-2 lg:grid-cols-5 gap-4" aria-label="Variation Orders Summary">
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">คำขอทั้งหมด (Total VOs)</span>
                <div class="mt-2 text-2xl font-black text-slate-900">{{ $stats['total'] }}</div>
                <span class="text-[10px] text-slate-400">บันทึกในระบบ (Logged)</span>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">รอพิจารณา (Pending Review)</span>
                <div class="mt-2 text-2xl font-black text-amber-600">{{ $stats['pending'] }}</div>
                <span class="text-[10px] text-slate-400">อยู่ในกระบวนการตรวจสอบ</span>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">ผลกระทบต้นทุนอนุมัติ (Cost Impact)</span>
                <div class="mt-2 text-2xl font-black {{ $stats['approved_cost'] >= 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                    {{ $stats['approved_cost'] >= 0 ? '+' : '-' }}฿{{ number_format(abs($stats['approved_cost']), 2) }}
                </div>
                <span class="text-[10px] text-slate-400">ส่วนต่างสัญญาสุทธิ</span>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">ขยายเวลาอนุมัติ (Approved EOT)</span>
                <div class="mt-2 text-2xl font-black text-slate-900">
                    {{ $stats['approved_days'] >= 0 ? '+' : '' }}{{ $stats['approved_days'] }} <span class="text-xs font-normal text-slate-500">วัน</span>
                </div>
                <span class="text-[10px] text-slate-400">ขยายเวลาสัญญา (Extension of Time)</span>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Current Contract (BAC)</span>
                <div class="mt-2 text-2xl font-black text-safety-600">฿{{ number_format($project->budget_amount, 2) }}</div>
                <div class="text-[10px] text-slate-400 mt-0.5">Finish: {{ $project->baseline_finish_date->format('d/m/Y') }}</div>
            </div>
        </section>

        <!-- VO Register Table -->
        <section class="rounded-2xl bg-white shadow-card border border-slate-200/80 overflow-hidden">
            <div class="border-b border-slate-200/80 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Variation Orders Register (ทะเบียนคำสั่งเปลี่ยนแปลงสัญญา)</h2>
                    <p class="text-[11px] text-slate-500">FIDIC Red Book / Commercial change log and baseline adjustments</p>
                </div>
                <span class="text-xs text-slate-500 font-mono font-bold">{{ $vos->count() }} Records</span>
            </div>

            @if($vos->isEmpty())
                <div class="p-12 text-center text-sm text-slate-500">
                    <div class="mx-auto w-12 h-12 text-slate-300 mb-2">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <p class="font-bold text-slate-700">ยังไม่มีรายการขอเปลี่ยนแปลงงาน (No variation orders yet)</p>
                    <p class="text-xs text-slate-400 mt-1">กดปุ่มด้านบนเพื่อยื่นคำร้องขอเปลี่ยนแปลงงานฉบับแรก</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 font-bold border-b border-slate-200/80">
                            <tr>
                                <th class="px-6 py-3.5">Ref / Title</th>
                                <th class="px-6 py-3.5">Type / Reason</th>
                                <th class="px-6 py-3.5 text-right">Cost Impact</th>
                                <th class="px-6 py-3.5 text-right">Time (EOT)</th>
                                <th class="px-6 py-3.5 text-center">Status</th>
                                <th class="px-6 py-3.5 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach($vos as $vo)
                                @php
                                    $badge = match($vo->status) {
                                        'SUBMITTED' => 'bg-amber-100 text-amber-800 border-amber-200',
                                        'VERIFIED_SITE' => 'bg-blue-100 text-blue-800 border-blue-200',
                                        'VERIFIED_QS' => 'bg-purple-100 text-purple-800 border-purple-200',
                                        'APPROVED' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                        'REJECTED' => 'bg-rose-100 text-rose-800 border-rose-200',
                                        default => 'bg-slate-100 text-slate-800 border-slate-200',
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-6 py-4">
                                        <div class="font-mono text-[11px] text-slate-500">{{ $vo->reference_no }}</div>
                                        <div class="font-bold text-slate-900">{{ $vo->title }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-800">{{ $vo->type }}</div>
                                        <div class="text-[11px] text-slate-500">{{ str_replace('_', ' ', $vo->reason) }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-right font-mono font-bold {{ $vo->cost_impact >= 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                                        {{ $vo->cost_impact >= 0 ? '+' : '-' }}฿{{ number_format(abs((float) $vo->cost_impact), 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-mono text-slate-700">
                                        {{ $vo->time_impact_days >= 0 ? '+' : '' }}{{ $vo->time_impact_days }} d
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-block rounded-full border px-2.5 py-0.5 text-[11px] font-bold {{ $badge }}">
                                            {{ $vo->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('projects.vo.show', [$project, $vo]) }}" class="inline-flex items-center gap-1 rounded-xl bg-slate-100 hover:bg-slate-200 px-3 py-1.5 text-xs font-bold text-slate-700 transition">
                                            <span>View</span>
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
