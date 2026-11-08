@props([
    'project',
    'pendingIpcCount' => 1,
    'openNcrCount' => 1,
    'ordersTodayCount' => 13,
    'openConstraintsCount' => 2,
])

<div class="rounded-2xl bg-white p-5 sm:p-6 shadow-card border border-slate-200/80 space-y-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-3">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 border border-rose-200 flex items-center justify-center font-bold text-xs animate-pulse">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <h3 class="text-sm font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Executive Action Priority &middot; ลำดับงานที่ต้องทำก่อนวันนี้</span>
                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200">
                        สิ่งที่ต้องทำทันที (Action First)
                    </span>
                </h3>
                <p class="text-[11px] text-slate-500">สรุปลำดับความสำคัญในการตัดสินใจของผู้บริหารและผู้จัดการโครงการ (เรียงลำดับความเร่งด่วน 1 &rarr; 4)</p>
            </div>
        </div>

        <div class="text-xs font-semibold text-slate-400">
            ระบบคัดกรองงานค้างอัตโนมัติ (Live Priority Queue)
        </div>
    </div>

    <!-- 4 Priority Action Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Priority 1: Commercial Sign-Off (Highest Urgency) -->
        <div class="rounded-xl border-2 border-rose-200 bg-rose-50/40 p-4 flex flex-col justify-between hover:bg-rose-50/70 transition space-y-3">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-rose-600 text-white">
                        Priority 1 &middot; เร่งด่วนที่สุด
                    </span>
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-ping"></span>
                </div>
                <h4 class="text-sm font-extrabold text-slate-900 mt-2.5">อนุมัติค่างวดงาน (IPC Sign-Off)</h4>
                <p class="text-[11px] text-slate-600 mt-1">
                    มีใบรับรองค่างวด <strong class="text-rose-700 font-bold">{{ $pendingIpcCount }} รายการ</strong> รอการลงนามอนุมัติเบิกจ่าย (Director Gate)
                </p>
            </div>
            <a href="{{ isset($project) ? route('projects.ipc.index', $project) : route('projects.portfolio') }}"
               class="inline-flex items-center justify-center gap-1.5 w-full py-2 px-3 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-xs transition">
                <span>ตรวจสอบและอนุมัติ IPC (Approve IPC)</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>

        <!-- Priority 2: Quality & Payment Hold (NCR) -->
        <div class="rounded-xl border border-amber-300 bg-amber-50/50 p-4 flex flex-col justify-between hover:bg-amber-50/80 transition space-y-3">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-[#EA580C] text-white">
                        Priority 2 &middot; คุณภาพ & อุปสรรค (Quality & Hold)
                    </span>
                </div>
                <h4 class="text-sm font-extrabold text-slate-900 mt-2.5">ข้อบกพร่องคุณภาพ (NCR Defect)</h4>
                <p class="text-[11px] text-slate-600 mt-1">
                    มี <strong class="text-amber-800 font-bold">{{ $openNcrCount }} รายการ</strong> ที่ระงับการเบิกจ่าย (Payment Hold) รอการแก้ไขหน้างาน
                </p>
            </div>
            <a href="{{ isset($project) ? route('projects.ncr.index', $project) : route('projects.portfolio') }}"
               class="inline-flex items-center justify-center gap-1.5 w-full py-2 px-3 rounded-lg bg-[#EA580C] hover:bg-[#C2410C] text-white font-bold text-xs shadow-xs transition">
                <span>จัดการรายการ NCR (Resolve Defect)</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>

        <!-- Priority 3: Procurement & Material Delivery -->
        <div class="rounded-xl border border-orange-300 bg-orange-50/50 p-4 flex flex-col justify-between hover:bg-orange-50/80 transition space-y-3">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-[#FF6B00] text-white">
                        Priority 3 &middot; พัสดุเข้าไซต์ (Inbound Delivery)
                    </span>
                </div>
                <h4 class="text-sm font-extrabold text-slate-900 mt-2.5">ตรวจรับวัสดุหน้างาน (Material Delivery)</h4>
                <p class="text-[11px] text-slate-600 mt-1">
                    มีคำสั่งซื้อ <strong class="text-[#EA580C] font-bold">{{ $ordersTodayCount }} รายการ</strong> คาดว่าจะมาส่งวันนี้ พร้อมรายการคงคลังต่ำ
                </p>
            </div>
            <a href="{{ isset($project) ? route('projects.procurement.index', $project) : route('projects.portfolio') }}"
               class="inline-flex items-center justify-center gap-1.5 w-full py-2 px-3 rounded-lg bg-[#FF6B00] hover:bg-[#E05E00] text-white font-bold text-xs shadow-xs transition">
                <span>ตรวจรับของเข้าคลัง (Check Delivery)</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>

        <!-- Priority 4: Lookahead Constraints (Schedule Barrier) -->
        <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4 flex flex-col justify-between hover:bg-slate-100 transition space-y-3">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-slate-700 text-white">
                        Priority 4 &middot; แผนล่วงหน้า (Lookahead)
                    </span>
                </div>
                <h4 class="text-sm font-extrabold text-slate-900 mt-2.5">เคลียร์อุปสรรคงาน (Lookahead Constraints)</h4>
                <p class="text-[11px] text-slate-600 mt-1">
                    มี <strong class="text-slate-900 font-bold">{{ $openConstraintsCount }} อุปสรรค</strong> ที่ต้องปลดล็อกก่อนเริ่มกิจกรรมสัปดาห์หน้า
                </p>
            </div>
            <a href="{{ isset($project) ? route('projects.lookahead', $project) : route('projects.portfolio') }}"
               class="inline-flex items-center justify-center gap-1.5 w-full py-2 px-3 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition">
                <span>เปิดดู Lookahead 6 สัปดาห์ (View 6-Week)</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>

    </div>
</div>
