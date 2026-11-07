<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 text-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Construction Executive Dashboard' }} — CED</title>

    <!-- Google Fonts for Modern Clean High-Density Readability -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased bg-slate-50 text-slate-900 flex flex-col selection:bg-safety-500 selection:text-white">
    <!-- 1. Top Executive Command Header (MindInventory Clean White Style) -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-slate-200/80 px-4 sm:px-6 lg:px-8 py-3.5 shadow-sm transition-colors">
        <div class="flex items-center justify-between gap-4">
            <!-- Brand & Project Breadcrumb -->
            <div class="flex items-center gap-3">
                <a href="{{ route('projects.portfolio') }}" class="flex items-center gap-2.5 group">
                    <span class="flex items-center justify-center w-9 h-9 rounded-xl bg-gradient-to-br from-safety-500 to-amber-600 text-white font-bold text-lg shadow-safety-glow group-hover:scale-105 transition-transform">
                        🏗️
                    </span>
                    <div>
                        <span class="font-extrabold text-sm sm:text-base tracking-tight text-slate-900 group-hover:text-safety-600 transition-colors">
                            CED <span class="text-xs uppercase px-1.5 py-0.5 rounded-md bg-orange-100 text-orange-700 font-bold border border-orange-200">PRO</span>
                        </span>
                        <span class="hidden sm:block text-[11px] text-slate-500 font-medium leading-tight">Construction Management Software</span>
                    </div>
                </a>

                @isset($project)
                    <div class="hidden md:flex items-center gap-2 pl-3 border-l border-slate-200">
                        <span class="text-xs font-mono font-bold text-sky-700 bg-sky-50 px-2 py-0.5 rounded-md border border-sky-200">
                            {{ $project->code }}
                        </span>
                        <span class="text-xs font-bold text-slate-800 truncate max-w-[260px]">
                            {{ $project->name }}
                        </span>
                        <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-full {{ $project->status === 'COMPLETED' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-orange-50 text-orange-700 border border-orange-200' }}">
                            {{ $project->status }}
                        </span>
                    </div>
                @endisset
            </div>

            <!-- Global Status & User Persona Switcher -->
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-medium">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Site Online</span>
                </div>

                @auth
                    <!-- Active Persona Pill -->
                    <div class="flex items-center gap-2.5 bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1.5 shadow-2xs">
                        <div class="w-6 h-6 rounded-lg bg-gradient-to-tr from-sky-600 to-blue-600 flex items-center justify-center text-[11px] font-bold text-white uppercase shadow-xs">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="hidden lg:block text-left">
                            <div class="text-xs font-bold text-slate-800 leading-none truncate max-w-[130px]">
                                {{ auth()->user()->name }}
                            </div>
                            <div class="text-[10px] text-slate-500 leading-tight">
                                {{ auth()->user()->email }}
                            </div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="ml-1 pl-2 border-l border-slate-200">
                            @csrf
                            <button type="submit" class="text-xs text-slate-400 hover:text-rose-600 transition" title="Log out">
                                🚪
                            </button>
                        </form>
                    </div>
                @endauth
            </div>
        </div>
    </header>

    <!-- 2. Master Layout Shell (Sidebar + Main Content Area) -->
    <div class="flex-1 flex overflow-hidden">
        @isset($project)
            <!-- MindInventory Clean Light Navigation Sidebar -->
            <aside class="w-64 bg-white border-r border-slate-200/80 flex flex-col justify-between overflow-y-auto shrink-0 hidden md:flex shadow-2xs">
                <div class="p-3 space-y-5">
                    <!-- Section 1: Executive & Strategy -->
                    <div>
                        <div class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            1. Executive & Strategy
                        </div>
                        <nav class="mt-1.5 space-y-0.5">
                            <a href="{{ route('projects.portfolio') }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition {{ request()->routeIs('projects.portfolio*') ? 'bg-orange-50 text-orange-600 font-bold border-l-4 border-safety-500 rounded-r-xl' : '' }}">
                                <span>📊</span> <span>Portfolio War Room</span>
                            </a>
                            <a href="{{ route('projects.executive', $project) }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition {{ request()->routeIs('projects.executive*') ? 'bg-orange-50 text-orange-600 font-bold border-l-4 border-safety-500 rounded-r-xl' : '' }}">
                                <span>🩺</span> <span>Project Health & Signals</span>
                            </a>
                            <a href="{{ route('projects.evm', $project) }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition {{ request()->routeIs('projects.evm*') ? 'bg-orange-50 text-orange-600 font-bold border-l-4 border-safety-500 rounded-r-xl' : '' }}">
                                <span>📈</span> <span>EVM S-Curve & Forecast</span>
                            </a>
                            <a href="{{ route('projects.lookahead', $project) }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition {{ request()->routeIs('projects.lookahead*') ? 'bg-orange-50 text-orange-600 font-bold border-l-4 border-safety-500 rounded-r-xl' : '' }}">
                                <span>🔭</span> <span>3-6 Week Lookahead</span>
                            </a>
                            <a href="{{ route('projects.constraints', $project) }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition {{ request()->routeIs('projects.constraints*') ? 'bg-orange-50 text-orange-600 font-bold border-l-4 border-safety-500 rounded-r-xl' : '' }}">
                                <span>⚠️</span> <span>Constraints Matrix</span>
                            </a>
                            <a href="{{ route('projects.3d-twin', $project) }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition {{ request()->routeIs('projects.3d-twin*') ? 'bg-orange-50 text-orange-600 font-bold border-l-4 border-safety-500 rounded-r-xl' : '' }}">
                                <span>🌐</span> <span>3D Digital Twin BIM</span>
                            </a>
                        </nav>
                    </div>

                    <!-- Section 2: Commercial & Finance -->
                    <div>
                        <div class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            2. Commercial & Finance
                        </div>
                        <nav class="mt-1.5 space-y-0.5">
                            <a href="{{ route('projects.ipc.index', $project) }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition {{ request()->routeIs('projects.ipc*') ? 'bg-orange-50 text-orange-600 font-bold border-l-4 border-safety-500 rounded-r-xl' : '' }}">
                                <span>💰</span> <span>IPC Payment Ledger (5A)</span>
                            </a>
                            <a href="{{ route('projects.vo.index', $project) }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition {{ request()->routeIs('projects.vo*') ? 'bg-orange-50 text-orange-600 font-bold border-l-4 border-safety-500 rounded-r-xl' : '' }}">
                                <span>📑</span> <span>Variation Orders VO (5B)</span>
                            </a>
                            <a href="{{ route('projects.procurement.index', $project) }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition {{ request()->routeIs('projects.procurement*') ? 'bg-orange-50 text-orange-600 font-bold border-l-4 border-safety-500 rounded-r-xl' : '' }}">
                                <span>🧱</span> <span>Material Procurement (5D)</span>
                            </a>
                        </nav>
                    </div>

                    <!-- Section 3: Field & Safety Operations -->
                    <div>
                        <div class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            3. Site & Safety Command
                        </div>
                        <nav class="mt-1.5 space-y-0.5">
                            <a href="{{ route('projects.resources.index', $project) }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition {{ request()->routeIs('projects.resources*') ? 'bg-orange-50 text-orange-600 font-bold border-l-4 border-safety-500 rounded-r-xl' : '' }}">
                                <span>👷</span> <span>Workforce & Machinery (6A-B)</span>
                            </a>
                            <a href="{{ route('projects.hse.index', $project) }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition {{ request()->routeIs('projects.hse*') ? 'bg-orange-50 text-orange-600 font-bold border-l-4 border-safety-500 rounded-r-xl' : '' }}">
                                <span>🦺</span> <span>HSE & Permit-to-Work (6C)</span>
                            </a>
                            <a href="{{ route('projects.site', $project) }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition {{ request()->routeIs('projects.site*') ? 'bg-orange-50 text-orange-600 font-bold border-l-4 border-safety-500 rounded-r-xl' : '' }}">
                                <span>📱</span> <span>Field Mobile Capture (3A)</span>
                            </a>
                        </nav>
                    </div>

                    <!-- Section 4: Engineering & Quality Control -->
                    <div>
                        <div class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            4. Engineering & QC
                        </div>
                        <nav class="mt-1.5 space-y-0.5">
                            <a href="{{ route('projects.ncr.index', $project) }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition {{ request()->routeIs('projects.ncr*') ? 'bg-orange-50 text-orange-600 font-bold border-l-4 border-safety-500 rounded-r-xl' : '' }}">
                                <span>🛡️</span> <span>QC & NCR Defects (5C)</span>
                            </a>
                            <a href="{{ route('projects.engineering.index', $project) }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition {{ request()->routeIs('projects.engineering*') ? 'bg-orange-50 text-orange-600 font-bold border-l-4 border-safety-500 rounded-r-xl' : '' }}">
                                <span>📐</span> <span>RFI & Submittals (7A-B)</span>
                            </a>
                        </nav>
                    </div>

                    <!-- Section 5: Handover & Closeout -->
                    <div>
                        <div class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            5. Handover & Closeout
                        </div>
                        <nav class="mt-1.5 space-y-0.5">
                            <a href="{{ route('projects.handover.index', $project) }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition {{ request()->routeIs('projects.handover*') ? 'bg-orange-50 text-orange-600 font-bold border-l-4 border-safety-500 rounded-r-xl' : '' }}">
                                <span>🏁</span> <span>Punch List & Final Account (8)</span>
                            </a>
                        </nav>
                    </div>
                </div>

                <!-- Footer Quick Info -->
                <div class="p-3.5 border-t border-slate-200/80 bg-slate-50 text-[11px] text-slate-500 space-y-1">
                    <div class="flex items-center justify-between">
                        <span>Engine Version:</span>
                        <span class="font-mono font-bold text-orange-600">v8.0.0-phase8</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Governance:</span>
                        <span class="text-emerald-700 font-bold">Maker-Checker</span>
                    </div>
                </div>
            </aside>
        @endisset

        <!-- Main Workspace Canvas (Light Slate Modern Background) -->
        <main class="flex-1 overflow-y-auto bg-slate-50/70 p-4 sm:p-6 lg:p-8 space-y-6">
            <!-- Global Flash Messages -->
            @if (session('status'))
                <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800 flex items-center justify-between gap-3 shadow-xs">
                    <div class="flex items-center gap-2.5">
                        <span class="text-lg">✅</span>
                        <span class="font-medium">{{ session('status') }}</span>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-2xl bg-rose-50 border border-rose-200 p-4 text-sm text-rose-800 space-y-1 shadow-xs">
                    <div class="font-bold flex items-center gap-2">
                        <span>⚠️</span>
                        <span>Please correct the following errors:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 text-xs text-rose-700 pl-6">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Page Injected Content -->
            @yield('content')
            {{ $slot ?? '' }}
        </main>
    </div>
</body>
</html>
