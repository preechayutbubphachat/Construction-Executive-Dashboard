@props([
    'project' => null
])

<div class="flex flex-col h-full justify-between select-none bg-[#0B132B] text-slate-300">
    <div class="flex flex-col h-full overflow-y-auto">
        <!-- Brand & Logo -->
        <div class="p-5 flex items-center gap-3 border-b border-[#1E293B]/70 shrink-0">
            <a href="{{ route('projects.portfolio') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-safety-orange to-amber-500 flex items-center justify-center text-white shadow-md shadow-orange-500/20 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <span class="font-extrabold text-base tracking-tight text-white block leading-tight">
                        CED <span class="text-[10px] uppercase px-1.5 py-0.5 rounded bg-safety-orange/20 text-safety-orange font-bold border border-safety-orange/30">PRO</span>
                    </span>
                    <span class="text-[10px] text-slate-400 font-medium">Construction Management</span>
                </div>
            </a>
        </div>

        <!-- 8 Master Navigation Modules -->
        <nav class="flex-1 px-3 py-4 space-y-1 text-xs font-semibold">
            
            <!-- 1. Dashboard -->
            @php
                $isDashboardActive = request()->routeIs('projects.executive*') || request()->routeIs('projects.show*');
                $dashboardUrl = isset($project) ? route('projects.executive', $project) : route('projects.portfolio');
            @endphp
            <a href="{{ $dashboardUrl }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ $isDashboardActive ? 'bg-safety-orange text-white font-bold shadow-sm shadow-orange-500/30' : 'text-slate-300 hover:bg-[#152238] hover:text-white' }}">
                <svg class="w-4 h-4 {{ $isDashboardActive ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                <span>แดชบอร์ดบริหาร (Dashboard)</span>
            </a>

            <!-- 2. Projects Management -->
            @php
                $isProjectsActive = request()->routeIs('projects.portfolio*') || request()->routeIs('projects.heatmap*');
            @endphp
            <a href="{{ route('projects.portfolio') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ $isProjectsActive ? 'bg-safety-orange text-white font-bold shadow-sm shadow-orange-500/30' : 'text-slate-300 hover:bg-[#152238] hover:text-white' }}">
                <svg class="w-4 h-4 {{ $isProjectsActive ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span>การจัดการโครงการ (Projects Management)</span>
            </a>

            <!-- 3. Resources Management (Accordion/Group) -->
            @php
                $isResourcesActive = request()->routeIs('projects.resources*') || request()->routeIs('projects.procurement*');
            @endphp
            <div x-data="{ open: {{ $isResourcesActive ? 'true' : 'false' }} }" class="space-y-0.5">
                <button @click="open = !open" type="button"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition {{ $isResourcesActive ? 'bg-[#152238] text-white font-bold' : 'text-slate-300 hover:bg-[#152238] hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 {{ $isResourcesActive ? 'text-safety-orange' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"/></svg>
                        <span>จัดการทรัพยากร (Resources)</span>
                    </div>
                    <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-cloak class="pl-8 pr-2 py-1 space-y-0.5 text-[11px]">
                    @if(isset($project))
                        <a href="{{ route('projects.resources.index', $project) }}" class="block px-2.5 py-1.5 rounded-lg {{ request()->routeIs('projects.resources*') ? 'text-safety-orange font-bold bg-[#0e172e]' : 'text-slate-400 hover:text-slate-200 hover:bg-[#152238]/60' }}">
                            • เครื่องจักรและยานพาหนะ (Machinery & Fleet)
                        </a>
                        <a href="{{ route('projects.resources.index', $project) }}" class="block px-2.5 py-1.5 rounded-lg {{ request()->routeIs('projects.resources*') ? 'text-safety-orange font-bold bg-[#0e172e]' : 'text-slate-400 hover:text-slate-200 hover:bg-[#152238]/60' }}">
                            • กำลังพลและคนงาน (Labor & Manpower)
                        </a>
                        <a href="{{ route('projects.procurement.index', $project) }}" class="block px-2.5 py-1.5 rounded-lg {{ request()->routeIs('projects.procurement*') ? 'text-safety-orange font-bold bg-[#0e172e]' : 'text-slate-400 hover:text-slate-200 hover:bg-[#152238]/60' }}">
                            • จัดซื้อและพัสดุ (Materials & MR)
                        </a>
                    @else
                        <a href="{{ route('projects.portfolio') }}" class="block px-2.5 py-1.5 rounded-lg text-slate-500 hover:text-slate-400">
                            • เลือกโครงการก่อน (Select a project)
                        </a>
                    @endif
                </div>
            </div>

            <!-- 4. Role Management -->
            <a href="{{ isset($project) ? route('projects.resources.index', $project) : route('projects.portfolio') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-slate-300 hover:bg-[#152238] hover:text-white">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span>บทบาทและสิทธิ์ (Role Management)</span>
            </a>

            <!-- 5. Analytic & Reporting -->
            @php
                $isAnalyticsActive = request()->routeIs('projects.evm*') || request()->routeIs('projects.finance*') || request()->routeIs('projects.reports*') || request()->routeIs('projects.ipc*') || request()->routeIs('projects.vo*');
            @endphp
            <div x-data="{ open: {{ $isAnalyticsActive ? 'true' : 'false' }} }" class="space-y-0.5">
                <button @click="open = !open" type="button"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition {{ $isAnalyticsActive ? 'bg-[#152238] text-white font-bold' : 'text-slate-300 hover:bg-[#152238] hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 {{ $isAnalyticsActive ? 'text-safety-orange' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>วิเคราะห์และรายงาน (Analytic & Reports)</span>
                    </div>
                    <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-cloak class="pl-8 pr-2 py-1 space-y-0.5 text-[11px]">
                    @if(isset($project))
                        <a href="{{ route('projects.evm', $project) }}" class="block px-2.5 py-1.5 rounded-lg {{ request()->routeIs('projects.evm*') ? 'text-safety-orange font-bold bg-[#0e172e]' : 'text-slate-400 hover:text-slate-200 hover:bg-[#152238]/60' }}">
                            • กราฟ S-Curve & คาดการณ์ (EVM S-Curve & Forecast)
                        </a>
                        <a href="{{ route('projects.ipc.index', $project) }}" class="block px-2.5 py-1.5 rounded-lg {{ request()->routeIs('projects.ipc*') ? 'text-safety-orange font-bold bg-[#0e172e]' : 'text-slate-400 hover:text-slate-200 hover:bg-[#152238]/60' }}">
                            • เบิกงวดงาน & กระแสเงินสด (IPC & Cashflow)
                        </a>
                        <a href="{{ route('projects.reports.daily', $project) }}" class="block px-2.5 py-1.5 rounded-lg {{ request()->routeIs('projects.reports.daily*') ? 'text-safety-orange font-bold bg-[#0e172e]' : 'text-slate-400 hover:text-slate-200 hover:bg-[#152238]/60' }}">
                            • บันทึกงานประจำวัน (Daily Site Log)
                        </a>
                        <a href="{{ route('projects.reports.weekly', $project) }}" class="block px-2.5 py-1.5 rounded-lg {{ request()->routeIs('projects.reports.weekly*') ? 'text-safety-orange font-bold bg-[#0e172e]' : 'text-slate-400 hover:text-slate-200 hover:bg-[#152238]/60' }}">
                            • รายงานประจำสัปดาห์ (Weekly Status Report)
                        </a>
                    @else
                        <a href="{{ route('projects.heatmap') }}" class="block px-2.5 py-1.5 rounded-lg {{ request()->routeIs('projects.heatmap*') ? 'text-safety-orange font-bold bg-[#0e172e]' : 'text-slate-400 hover:text-slate-200 hover:bg-[#152238]/60' }}">
                            • เมทริกซ์ความเสี่ยง (Risk Heatmap Matrix)
                        </a>
                    @endif
                </div>
            </div>

            <!-- 6. BIM Integration -->
            @php
                $isBimActive = request()->routeIs('projects.3d-twin*');
                $bimUrl = isset($project) ? route('projects.3d-twin', $project) : route('projects.portfolio');
            @endphp
            <a href="{{ $bimUrl }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ $isBimActive ? 'bg-safety-orange text-white font-bold shadow-sm shadow-orange-500/30' : 'text-slate-300 hover:bg-[#152238] hover:text-white' }}">
                <svg class="w-4 h-4 {{ $isBimActive ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"/></svg>
                <span>แบบจำลอง 3 มิติ (BIM & 3D Twin)</span>
            </a>

            <!-- 7. Safety & Compliance -->
            @php
                $isSafetyActive = request()->routeIs('projects.hse*') || request()->routeIs('projects.ncr*') || request()->routeIs('projects.site*');
                $safetyUrl = isset($project) ? route('projects.ncr.index', $project) : route('projects.portfolio');
            @endphp
            <div x-data="{ open: {{ $isSafetyActive ? 'true' : 'false' }} }" class="space-y-0.5">
                <button @click="open = !open" type="button"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition {{ $isSafetyActive ? 'bg-[#152238] text-white font-bold' : 'text-slate-300 hover:bg-[#152238] hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 {{ $isSafetyActive ? 'text-safety-orange' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>ความปลอดภัยและคุณภาพ (Safety & QC)</span>
                    </div>
                    <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-cloak class="pl-8 pr-2 py-1 space-y-0.5 text-[11px]">
                    @if(isset($project))
                        <a href="{{ route('projects.ncr.index', $project) }}" class="block px-2.5 py-1.5 rounded-lg {{ request()->routeIs('projects.ncr*') ? 'text-safety-orange font-bold bg-[#0e172e]' : 'text-slate-400 hover:text-slate-200 hover:bg-[#152238]/60' }}">
                            • ควบคุมคุณภาพ & ปิด Defect (NCR)
                        </a>
                        <a href="{{ route('projects.hse.index', $project) }}" class="block px-2.5 py-1.5 rounded-lg {{ request()->routeIs('projects.hse*') ? 'text-safety-orange font-bold bg-[#0e172e]' : 'text-slate-400 hover:text-slate-200 hover:bg-[#152238]/60' }}">
                            • ใบอนุญาตงาน & ความปลอดภัย (HSE)
                        </a>
                        <a href="{{ route('projects.site', $project) }}" class="block px-2.5 py-1.5 rounded-lg {{ request()->routeIs('projects.site*') ? 'text-safety-orange font-bold bg-[#0e172e]' : 'text-slate-400 hover:text-slate-200 hover:bg-[#152238]/60' }}">
                            • บันทึกตรวจงานหน้างาน (Field Mobile)
                        </a>
                    @else
                        <a href="{{ route('projects.portfolio') }}" class="block px-2.5 py-1.5 rounded-lg text-slate-500 hover:text-slate-400">
                            • เลือกโครงการก่อน (Select a project)
                        </a>
                    @endif
                </div>
            </div>

            <!-- 8. Settings -->
            <a href="#"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-slate-400 hover:bg-[#152238] hover:text-white">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>ตั้งค่าระบบ (Settings)</span>
            </a>
        </nav>

        <!-- Bottom User Profile Card (MindInventory Style) -->
        @auth
            <div class="p-4 border-t border-[#1E293B] bg-[#080E20] shrink-0">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-safety-orange to-amber-500 flex items-center justify-center text-white font-bold text-xs shrink-0 shadow-sm">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="truncate text-left">
                            <div class="text-xs font-bold text-white truncate">{{ auth()->user()->name }}</div>
                            <div class="text-[10px] text-slate-400 font-medium">Project Manager</div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                        @csrf
                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-safety-orange hover:bg-[#152238] transition" title="Logout">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        @endauth
    </div>
</div>
