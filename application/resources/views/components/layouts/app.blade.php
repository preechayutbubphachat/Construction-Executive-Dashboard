<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100 text-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Construction Project Management Software' }} — CED</title>

    <!-- Google Fonts for Clean High-Density Readability -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased bg-slate-100 text-slate-900 flex flex-col selection:bg-safety-orange selection:text-white">
    <!-- Master Application Shell (Deep Navy Sidebar + Pure White Header + Clean Light Canvas) -->
    <div class="flex h-full w-full overflow-hidden">
        
        <!-- 1. Deep-Navy Left Sidebar (MindInventory Enterprise SaaS Master Navigation) -->
        <aside class="w-64 bg-[#0B132B] text-slate-300 flex flex-col justify-between shrink-0 hidden md:flex border-r border-[#1E293B] shadow-xl z-30 select-none">
            <div class="flex flex-col h-full overflow-y-auto">
                <!-- Brand & Logo -->
                <div class="p-5 flex items-center gap-3 border-b border-[#1E293B]/70">
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
                        <span>Dashboard</span>
                    </a>

                    <!-- 2. Projects Management -->
                    @php
                        $isProjectsActive = request()->routeIs('projects.portfolio*') || request()->routeIs('projects.heatmap*');
                    @endphp
                    <a href="{{ route('projects.portfolio') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ $isProjectsActive ? 'bg-safety-orange text-white font-bold shadow-sm shadow-orange-500/30' : 'text-slate-300 hover:bg-[#152238] hover:text-white' }}">
                        <svg class="w-4 h-4 {{ $isProjectsActive ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <span>Projects Management</span>
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
                                <span>Resources Management</span>
                            </div>
                            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-cloak class="pl-8 pr-2 py-1 space-y-0.5 text-[11px]">
                            @if(isset($project))
                                <a href="{{ route('projects.resources.index', $project) }}" class="block px-2.5 py-1.5 rounded-lg {{ request()->routeIs('projects.resources*') ? 'text-safety-orange font-bold bg-[#0e172e]' : 'text-slate-400 hover:text-slate-200 hover:bg-[#152238]/60' }}">
                                    • Machinery & Fleet
                                </a>
                                <a href="{{ route('projects.resources.index', $project) }}" class="block px-2.5 py-1.5 rounded-lg {{ request()->routeIs('projects.resources*') ? 'text-safety-orange font-bold bg-[#0e172e]' : 'text-slate-400 hover:text-slate-200 hover:bg-[#152238]/60' }}">
                                    • Labor & Manpower
                                </a>
                                <a href="{{ route('projects.procurement.index', $project) }}" class="block px-2.5 py-1.5 rounded-lg {{ request()->routeIs('projects.procurement*') ? 'text-safety-orange font-bold bg-[#0e172e]' : 'text-slate-400 hover:text-slate-200 hover:bg-[#152238]/60' }}">
                                    • Materials & MR
                                </a>
                            @else
                                <a href="{{ route('projects.portfolio') }}" class="block px-2.5 py-1.5 rounded-lg text-slate-500 hover:text-slate-400">
                                    • Select a project
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- 4. Role Management -->
                    <a href="{{ isset($project) ? route('projects.resources.index', $project) : route('projects.portfolio') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-slate-300 hover:bg-[#152238] hover:text-white">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span>Role Management</span>
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
                                <span>Analytic & Reporting</span>
                            </div>
                            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-cloak class="pl-8 pr-2 py-1 space-y-0.5 text-[11px]">
                            @if(isset($project))
                                <a href="{{ route('projects.evm', $project) }}" class="block px-2.5 py-1.5 rounded-lg {{ request()->routeIs('projects.evm*') ? 'text-safety-orange font-bold bg-[#0e172e]' : 'text-slate-400 hover:text-slate-200 hover:bg-[#152238]/60' }}">
                                    • EVM S-Curve & Forecast
                                </a>
                                <a href="{{ route('projects.ipc.index', $project) }}" class="block px-2.5 py-1.5 rounded-lg {{ request()->routeIs('projects.ipc*') ? 'text-safety-orange font-bold bg-[#0e172e]' : 'text-slate-400 hover:text-slate-200 hover:bg-[#152238]/60' }}">
                                    • IPC & Financial Cashflow
                                </a>
                                <a href="{{ route('projects.reports.daily', $project) }}" class="block px-2.5 py-1.5 rounded-lg {{ request()->routeIs('projects.reports.daily*') ? 'text-safety-orange font-bold bg-[#0e172e]' : 'text-slate-400 hover:text-slate-200 hover:bg-[#152238]/60' }}">
                                    • Daily Site Log
                                </a>
                                <a href="{{ route('projects.reports.weekly', $project) }}" class="block px-2.5 py-1.5 rounded-lg {{ request()->routeIs('projects.reports.weekly*') ? 'text-safety-orange font-bold bg-[#0e172e]' : 'text-slate-400 hover:text-slate-200 hover:bg-[#152238]/60' }}">
                                    • Weekly Status Report
                                </a>
                            @else
                                <a href="{{ route('projects.heatmap') }}" class="block px-2.5 py-1.5 rounded-lg {{ request()->routeIs('projects.heatmap*') ? 'text-safety-orange font-bold bg-[#0e172e]' : 'text-slate-400 hover:text-slate-200 hover:bg-[#152238]/60' }}">
                                    • Risk Heatmap Matrix
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
                        <span>BIM Integration</span>
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
                                <span>Safety & Compliance</span>
                            </div>
                            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-cloak class="pl-8 pr-2 py-1 space-y-0.5 text-[11px]">
                            @if(isset($project))
                                <a href="{{ route('projects.ncr.index', $project) }}" class="block px-2.5 py-1.5 rounded-lg {{ request()->routeIs('projects.ncr*') ? 'text-safety-orange font-bold bg-[#0e172e]' : 'text-slate-400 hover:text-slate-200 hover:bg-[#152238]/60' }}">
                                    • Safety & NCR Defects
                                </a>
                                <a href="{{ route('projects.hse.index', $project) }}" class="block px-2.5 py-1.5 rounded-lg {{ request()->routeIs('projects.hse*') ? 'text-safety-orange font-bold bg-[#0e172e]' : 'text-slate-400 hover:text-slate-200 hover:bg-[#152238]/60' }}">
                                    • HSE Permits & Hazards
                                </a>
                                <a href="{{ route('projects.site', $project) }}" class="block px-2.5 py-1.5 rounded-lg {{ request()->routeIs('projects.site*') ? 'text-safety-orange font-bold bg-[#0e172e]' : 'text-slate-400 hover:text-slate-200 hover:bg-[#152238]/60' }}">
                                    • Field Mobile Capture
                                </a>
                            @else
                                <a href="{{ route('projects.portfolio') }}" class="block px-2.5 py-1.5 rounded-lg text-slate-500 hover:text-slate-400">
                                    • Select a project
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- 8. Settings -->
                    <a href="#"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-slate-400 hover:bg-[#152238] hover:text-white">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Settings</span>
                    </a>
                </nav>

                <!-- Bottom User Profile Card (Darlene Robertson, Project Manager Style) -->
                @auth
                    <div class="p-4 border-t border-[#1E293B] bg-[#080E20]">
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
        </aside>

        <!-- 2. Right Canvas Area: Universal Top Bar + Main Workspace -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-100">
            
            <!-- Universal Top Header Bar (Pure White with Search & Project Switcher) -->
            <header class="sticky top-0 z-20 bg-white border-b border-slate-200/80 px-6 py-3 shadow-xs">
                <div class="flex items-center justify-between gap-4">
                    
                    <!-- Search Input (MindInventory Top Bar Search) -->
                    <div class="flex items-center gap-3 flex-1 max-w-md">
                        <div class="relative w-full">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <input type="text"
                                   placeholder="Search..."
                                   class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50/70 text-slate-800 placeholder-slate-400 focus:bg-white focus:border-safety-orange focus:ring-1 focus:ring-safety-orange transition">
                        </div>
                    </div>

                    <!-- Right Controls: Project Switcher & Status -->
                    <div class="flex items-center gap-3">
                        
                        <!-- Project Selector (MindInventory 'Project: Delta Phase ▾') -->
                        @if(isset($project))
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" type="button"
                                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-800 hover:border-slate-300 transition shadow-2xs">
                                    <span class="text-slate-400 font-medium">Project:</span>
                                    <span class="text-slate-900 font-extrabold truncate max-w-[160px]">{{ $project->name }}</span>
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="open" @click.away="open = false" x-cloak
                                     class="absolute right-0 mt-2 w-56 rounded-2xl bg-white p-2 shadow-card border border-slate-200 text-xs z-50">
                                    <div class="px-3 py-1.5 text-[10px] font-black uppercase text-slate-400">Current Scope</div>
                                    <div class="px-3 py-1 font-bold text-safety-orange">{{ $project->code }} · {{ $project->name }}</div>
                                    <div class="my-1 border-t border-slate-100"></div>
                                    <a href="{{ route('projects.portfolio') }}" class="block px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-50 hover:text-safety-orange font-semibold">
                                        ← All Projects Portfolio
                                    </a>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('projects.portfolio') }}"
                               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-800 shadow-2xs">
                                <span class="w-2 h-2 rounded-full bg-safety-orange"></span>
                                <span>Multi-Project Portfolio</span>
                            </a>
                        @endif

                        <!-- Notifications & Activity Icon -->
                        <button type="button" class="relative p-2 rounded-xl border border-slate-200 text-slate-600 hover:text-safety-orange hover:bg-slate-50 transition" title="Notifications">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-safety-orange ring-2 ring-white"></span>
                        </button>

                        <!-- Messages Icon -->
                        <button type="button" class="p-2 rounded-xl border border-slate-200 text-slate-600 hover:text-safety-orange hover:bg-slate-50 transition" title="Messages">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Main Scrollable Canvas -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6">
                
                <!-- Global Flash Status Messages -->
                @if (session('status'))
                    <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-xs font-semibold text-emerald-900 shadow-card flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ session('status') }}</span>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="rounded-2xl bg-rose-50 border border-rose-200 p-4 text-xs font-semibold text-rose-900 shadow-card space-y-1">
                        <div class="font-bold flex items-center gap-2 text-rose-950">
                            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Please correct the following errors:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-0.5 text-rose-800 pl-6">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Page Injected Content -->
                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
