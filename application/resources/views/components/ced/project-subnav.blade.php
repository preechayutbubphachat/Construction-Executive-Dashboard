@props([
    'project',
    'active' => null
])

@php
    $items = [
        ['name' => 'ภาพรวมบริหาร (Executive)', 'icon' => 'chart-bar', 'route' => 'projects.executive'],
        ['name' => 'ผังงาน (Schedule & WBS)', 'icon' => 'calendar', 'route' => 'projects.schedule.show'],
        ['name' => 'แผน 6 สัปดาห์ (Lookahead)', 'icon' => 'view-columns', 'route' => 'projects.lookahead'],
        ['name' => 'อุปสรรคงาน (Constraints)', 'icon' => 'exclamation-circle', 'route' => 'projects.constraints'],
        ['name' => 'ศูนย์แจ้งเตือน (Alerts)', 'icon' => 'bell', 'route' => 'projects.alerts'],
        ['name' => 'แผนผังความเสี่ยง (Risk Heatmap)', 'icon' => 'fire', 'route' => 'projects.heatmap', 'no_project' => true],
        ['name' => 'โมเดล 3D Twin 🌐', 'icon' => 'cube', 'route' => 'projects.3d-twin'],
        ['name' => 'ตรวจหน้างาน (Site Mobile) 📱', 'icon' => 'device-phone-mobile', 'route' => 'projects.site'],
        ['name' => 'กำลังคนและเครื่องจักร (Resources)', 'icon' => 'users', 'route' => 'projects.resources.index'],
        ['name' => 'จัดซื้อและพัสดุ (Procurement & MR)', 'icon' => 'shopping-cart', 'route' => 'projects.procurement.index'],
        ['name' => 'ความปลอดภัย (HSE)', 'icon' => 'shield-check', 'route' => 'projects.hse.index'],
        ['name' => 'วิศวกรรม (Engineering & RFI)', 'icon' => 'document-duplicate', 'route' => 'projects.engineering.index'],
        ['name' => 'ควบคุมคุณภาพ (Quality / NCR)', 'icon' => 'check-badge', 'route' => 'projects.ncr.index'],
        ['name' => 'เบิกจ่ายค่างวด (IPC Billing) 💰', 'icon' => 'banknotes', 'route' => 'projects.ipc.index'],
        ['name' => 'งานเพิ่ม-ลด (VO Orders) 📑', 'icon' => 'pencil-square', 'route' => 'projects.vo.index'],
        ['name' => 'กราฟ S-Curve 📈 (EVM)', 'icon' => 'arrow-trending-up', 'route' => 'projects.evm'],
        ['name' => 'กระแสเงินสด (Cashflow)', 'icon' => 'credit-card', 'route' => 'projects.finance'],
        ['name' => 'บันทึกประจำวัน (Daily Log)', 'icon' => 'document-text', 'route' => 'projects.reports.daily'],
        ['name' => 'รายงานสัปดาห์ (Weekly Report)', 'icon' => 'clipboard-document-list', 'route' => 'projects.reports.weekly'],
        ['name' => 'ส่งมอบงาน (Handover)', 'icon' => 'flag', 'route' => 'projects.handover.index'],
    ];
@endphp

<div class="border-b border-slate-200/90 -mt-2 pb-2">
    <!-- Horizontal scrolling subnav pills -->
    <div class="flex items-center gap-1.5 overflow-x-auto py-1 scrollbar-none text-xs">
        <!-- Back to Portfolio Link -->
        <a href="{{ route('projects.portfolio') }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-extrabold transition text-[#EA580C] bg-orange-50 border border-orange-200 hover:bg-orange-100 shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>← แฟ้มโครงการ (Portfolio)</span>
        </a>

        <div class="h-4 w-px bg-slate-300 shrink-0 mx-1"></div>

        @foreach ($items as $item)
            @php
                $isCurrent = ($active === $item['route']) || request()->routeIs($item['route'] . '*');
                $url = isset($item['no_project']) && $item['no_project'] 
                    ? route($item['route']) 
                    : (isset($project) ? route($item['route'], $project) : '#');
            @endphp

            <a href="{{ $url }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl whitespace-nowrap transition shrink-0 {{ $isCurrent ? 'bg-[#FF6B00] text-white font-extrabold shadow-sm ring-1 ring-orange-500' : 'bg-white border border-slate-300 text-slate-800 font-bold hover:bg-slate-100 hover:text-slate-950 hover:border-slate-400' }}">
                <span>{{ $item['name'] }}</span>
            </a>
        @endforeach
    </div>
</div>
