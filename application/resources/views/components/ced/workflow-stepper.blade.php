@props([
    'project',
    'active' => 'executive'
])

@php
    $stages = [
        [
            'id' => 'schedule',
            'step' => '01',
            'name' => 'WBS & Schedule',
            'th' => 'วางแผนงาน',
            'route' => 'projects.schedule.show',
            'icon' => 'calendar',
            'desc' => 'Baselines, WBS packages & tasks',
        ],
        [
            'id' => 'engineering',
            'step' => '02',
            'name' => 'Engineering',
            'th' => 'วิศวกรรม & แบบ',
            'route' => 'projects.engineering.index',
            'icon' => 'document-text',
            'desc' => 'RFIs & Shop drawing submittals',
        ],
        [
            'id' => 'procurement',
            'step' => '03',
            'name' => 'Procurement',
            'th' => 'จัดซื้อ & วัสดุ',
            'route' => 'projects.procurement.index',
            'icon' => 'truck',
            'desc' => 'Material requisitions, POs & inventory',
        ],
        [
            'id' => 'site',
            'step' => '04',
            'name' => 'Site & Safety',
            'th' => 'หน้างาน & ปลอดภัย',
            'route' => 'projects.resources.index',
            'icon' => 'user-group',
            'desc' => 'Manpower, machinery & HSE permits',
        ],
        [
            'id' => 'quality',
            'step' => '05',
            'name' => 'Quality (NCR)',
            'th' => 'ตรวจรับคุณภาพ',
            'route' => 'projects.ncr.index',
            'icon' => 'shield-check',
            'desc' => 'Defects, CAPA & payment holds',
        ],
        [
            'id' => 'commercial',
            'step' => '06',
            'name' => 'Billing (IPC/VO)',
            'th' => 'ค่างวด & การเงิน',
            'route' => 'projects.ipc.index',
            'icon' => 'currency-dollar',
            'desc' => 'IPC certificates, VOs & EVM S-Curve',
        ],
        [
            'id' => 'handover',
            'step' => '07',
            'name' => 'Handover',
            'th' => 'ส่งมอบโครงการ',
            'route' => 'projects.handover.index',
            'icon' => 'check-badge',
            'desc' => 'Punch lists, retention & TOC',
        ],
    ];

    // Determine active index
    $currentIndex = 0;
    foreach ($stages as $idx => $stg) {
        if ($active === $stg['id'] || request()->routeIs($stg['route'] . '*')) {
            $currentIndex = $idx;
            break;
        }
    }
@endphp

<div class="rounded-2xl bg-white p-5 sm:p-6 shadow-card border border-slate-200/80 space-y-4">
    <!-- Header of Stepper -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-3">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-orange-50 text-safety-orange border border-orange-200 flex items-center justify-center font-bold text-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
                <h3 class="text-sm font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>กระบวนการก่อสร้าง 7 ขั้นตอน (Construction Lifecycle Process Flow)</span>
                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">ลำดับโฟลว์งานก่อสร้าง</span>
                </h3>
                <p class="text-[11px] text-slate-500">ลำดับขั้นตอนการดำเนินงาน 7 ลำดับขั้น: วางแผนงาน (Planning) → วิศวกรรม (Engineering) → จัดซื้อจัดจ้าง (Procurement) → ก่อสร้างหน้างาน (Site) → ตรวจรับคุณภาพ (Quality) → เบิกจ่ายงวดงาน (Billing) → ส่งมอบโครงการ (Handover)</p>
            </div>
        </div>

        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
            <span>ขั้นตอนปัจจุบัน (Current Stage):</span>
            <span class="px-2.5 py-1 rounded-xl bg-orange-50 border border-orange-300 text-[#EA580C] font-extrabold font-mono">
                Stage {{ $stages[$currentIndex]['step'] }} &middot; {{ $stages[$currentIndex]['name'] }}
            </span>
        </div>
    </div>

    <!-- 7-Stage Horizontal Pipeline Rails (Responsive Scrollable) -->
    <div class="overflow-x-auto pb-2 -mx-1 px-1 scrollbar-none">
        <div class="flex items-center justify-between min-w-[760px] gap-2 relative">
            
            @foreach ($stages as $idx => $stg)
                @php
                    $isPast = $idx < $currentIndex;
                    $isCurrent = $idx === $currentIndex;
                    $isUpcoming = $idx > $currentIndex;
                    $url = isset($project) ? route($stg['route'], $project) : '#';
                @endphp

                <div class="flex-1 flex flex-col items-center group relative">
                    <!-- Connector line between steps -->
                    @if (!$loop->last)
                        <div class="absolute top-4 left-1/2 w-full h-0.5 -z-0 {{ $isPast ? 'bg-[#FF6B00]' : 'bg-slate-200' }}"></div>
                    @endif

                    <!-- Step Circle / Button -->
                    <a href="{{ $url }}"
                       class="relative z-10 w-9 h-9 rounded-xl flex items-center justify-center font-mono text-xs font-black transition-all duration-200 {{ $isCurrent ? 'bg-[#FF6B00] text-white shadow-md shadow-orange-500/40 ring-4 ring-orange-200 scale-105' : ($isPast ? 'bg-slate-900 text-white hover:bg-slate-800' : 'bg-slate-100 text-slate-500 border border-slate-300 hover:bg-slate-200 hover:text-slate-900') }}">
                        @if ($isPast)
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        @else
                            {{ $stg['step'] }}
                        @endif
                    </a>

                    <!-- Step Labels -->
                    <div class="mt-2 text-center">
                        <a href="{{ $url }}" class="block text-xs leading-tight transition {{ $isCurrent ? 'text-[#FF6B00] font-black' : ($isPast ? 'text-slate-900 font-bold hover:text-[#FF6B00]' : 'text-slate-500 font-semibold hover:text-slate-800') }}">
                            {{ $stg['name'] }}
                        </a>
                        <span class="block text-[10px] text-slate-400 font-medium">
                            {{ $stg['th'] }}
                        </span>
                    </div>
                </div>
            @endforeach

        </div>
    </div>
</div>
