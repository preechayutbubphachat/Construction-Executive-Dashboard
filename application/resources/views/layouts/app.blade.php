<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-obsidian-950 text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Construction Executive Dashboard' }} — CED</title>

    <!-- Google Fonts for High-Density Technical Readability -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased bg-obsidian-950 text-slate-100 flex flex-col selection:bg-safety-500 selection:text-white">
    <!-- 1. Top Executive Command Header -->
    <header class="sticky top-0 z-40 bg-obsidian-900/95 backdrop-blur border-b border-obsidian-700 px-4 sm:px-6 lg:px-8 py-3 transition-colors">
        <div class="flex items-center justify-between gap-4">
            <!-- Brand & Project Title -->
            <div class="flex items-center gap-3">
                <a href="{{ route('projects.portfolio') }}" class="flex items-center gap-2.5 group">
                    <span class="flex items-center justify-center w-9 h-9 rounded-xl bg-gradient-to-br from-safety-500 to-amber-600 text-white font-bold text-lg shadow-safety-glow group-hover:scale-105 transition-transform">
                        🏗️
                    </span>
                    <div>
                        <span class="font-extrabold text-sm sm:text-base tracking-tight text-white group-hover:text-safety-400 transition-colors">
                            CED <span class="text-xs uppercase px-1.5 py-0.5 rounded bg-obsidian-800 text-safety-400 border border-obsidian-700">PRO</span>
                        </span>
                        <span class="hidden sm:block text-[11px] text-slate-400 leading-tight">Construction Executive Intelligence</span>
                    </div>
                </a>

                @isset($project)
                    <div class="hidden md:flex items-center gap-2 pl-3 border-l border-obsidian-700">
                        <span class="text-xs font-mono text-blueprint-400 bg-blueprint-500/10 px-2 py-0.5 rounded border border-blueprint-500/20">
                            {{ $project->code }}
                        </span>
                        <span class="text-xs font-semibold text-slate-200 truncate max-w-[240px]">
                            {{ $project->name }}
                        </span>
                        <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded {{ $project->status === 'COMPLETED' ? 'bg-qc-500/15 text-qc-400 border border-qc-500/30' : 'bg-safety-500/15 text-safety-400 border border-safety-500/30' }}">
                            {{ $project->status }}
                        </span>
                    </div>
                @endisset
            </div>

            <!-- Global Status & User Persona Switcher -->
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-qc-500/10 border border-qc-500/20 text-qc-400 text-xs font-medium">
                    <span class="w-2 h-2 rounded-full bg-qc-500 animate-pulse"></span>
                    <span>Site Online</span>
                </div>

                @auth
                    <!-- Active Persona Pill -->
                    <div class="flex items-center gap-2 bg-obsidian-850 border border-obsidian-700 rounded-xl px-2.5 py-1.5">
                        <div class="w-6 h-6 rounded-lg bg-gradient-to-tr from-cyan-600 to-blue-500 flex items-center justify-center text-[11px] font-bold text-white uppercase">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="hidden lg:block text-left">
                            <div class="text-xs font-semibold text-slate-200 leading-none truncate max-w-[130px]">
                                {{ auth()->user()->name }}
                            </div>
                            <div class="text-[10px] text-slate-400 leading-tight">
                                {{ auth()->user()->email }}
                            </div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="ml-1 pl-2 border-l border-obsidian-700">
                            @csrf
                            <button type="submit" class="text-xs text-slate-400 hover:text-rose-400 transition" title="Log out">
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
            <!-- Collapsible Industrial Navigation Sidebar -->
            <aside class="w-64 bg-obsidian-900 border-r border-obsidian-700 flex flex-col justify-between overflow-y-auto shrink-0 hidden md:flex">
                <div class="p-3 space-y-6">
                    <!-- Section 1: Executive & Strategy -->
                    <div>
                        <div class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            1. Executive & Strategy
                        </div>
                        <nav class="mt-2 space-y-1">
                            <a href="{{ route('projects.portfolio') }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium rounded-lg text-slate-300 hover:bg-obsidian-800 hover:text-white transition">
                                📊 <span>Portfolio War Room</span>
                            </a>
                            <a href="{{ route('projects.executive', $project) }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium rounded-lg text-slate-300 hover:bg-obsidian-800 hover:text-white transition">
                                🩺 <span>Project Health & Signals</span>
                            </a>
                            <a href="{{ route('projects.evm', $project) }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium rounded-lg text-slate-300 hover:bg-obsidian-800 hover:text-white transition">
                                📈 <span>EVM S-Curve & Forecast</span>
                            </a>
                            <a href="{{ route('projects.lookahead', $project) }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium rounded-lg text-slate-300 hover:bg-obsidian-800 hover:text-white transition">
                                🔭 <span>3-6 Week Lookahead</span>
                            </a>
                            <a href="{{ route('projects.constraints', $project) }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium rounded-lg text-slate-300 hover:bg-obsidian-800 hover:text-white transition">
                                ⚠️ <span>Constraints Matrix</span>
                            </a>
                            <a href="{{ route('projects.3d-twin', $project) }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium rounded-lg text-slate-300 hover:bg-obsidian-800 hover:text-white transition">
                                🌐 <span>3D Digital Twin BIM</span>
                            </a>
                        </nav>
                    </div>

                    <!-- Section 2: Commercial & Finance -->
                    <div>
                        <div class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            2. Commercial & Finance
                        </div>
                        <nav class="mt-2 space-y-1">
                            <a href="{{ route('projects.ipc.index', $project) }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium rounded-lg text-slate-300 hover:bg-obsidian-800 hover:text-white transition">
                                💰 <span>IPC Payment Ledger (5A)</span>
                            </a>
                            <a href="{{ route('projects.vo.index', $project) }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium rounded-lg text-slate-300 hover:bg-obsidian-800 hover:text-white transition">
                                📑 <span>Variation Orders VO (5B)</span>
                            </a>
                            <a href="{{ route('projects.procurement.index', $project) }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium rounded-lg text-slate-300 hover:bg-obsidian-800 hover:text-white transition">
                                🧱 <span>Material Procurement (5D)</span>
                            </a>
                        </nav>
                    </div>

                    <!-- Section 3: Field & Safety Operations -->
                    <div>
                        <div class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            3. Site & Safety Command
                        </div>
                        <nav class="mt-2 space-y-1">
                            <a href="{{ route('projects.resources.index', $project) }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium rounded-lg text-slate-300 hover:bg-obsidian-800 hover:text-white transition">
                                👷 <span>Workforce & Machinery (6A-B)</span>
                            </a>
                            <a href="{{ route('projects.hse.index', $project) }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium rounded-lg text-slate-300 hover:bg-obsidian-800 hover:text-white transition">
                                🦺 <span>HSE & Permit-to-Work (6C)</span>
                            </a>
                            <a href="{{ route('projects.site', $project) }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium rounded-lg text-slate-300 hover:bg-obsidian-800 hover:text-white transition">
                                📱 <span>Field Mobile Capture (3A)</span>
                            </a>
                        </nav>
                    </div>

                    <!-- Section 4: Engineering & Quality Control -->
                    <div>
                        <div class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            4. Engineering & QC
                        </div>
                        <nav class="mt-2 space-y-1">
                            <a href="{{ route('projects.ncr.index', $project) }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium rounded-lg text-slate-300 hover:bg-obsidian-800 hover:text-white transition">
                                🛡️ <span>QC & NCR Defects (5C)</span>
                            </a>
                            <a href="{{ route('projects.engineering.index', $project) }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium rounded-lg text-slate-300 hover:bg-obsidian-800 hover:text-white transition">
                                📐 <span>RFI & Submittals (7A-B)</span>
                            </a>
                        </nav>
                    </div>

                    <!-- Section 5: Handover & Closeout -->
                    <div>
                        <div class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            5. Handover & Closeout
                        </div>
                        <nav class="mt-2 space-y-1">
                            <a href="{{ route('projects.handover.index', $project) }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium rounded-lg text-slate-300 hover:bg-obsidian-800 hover:text-white transition">
                                🏁 <span>Punch List & Final Account (8)</span>
                            </a>
                        </nav>
                    </div>
                </div>

                <!-- Footer Quick Info -->
                <div class="p-3 border-t border-obsidian-700 bg-obsidian-950/40 text-[11px] text-slate-400 space-y-1">
                    <div class="flex items-center justify-between">
                        <span>Engine Version:</span>
                        <span class="font-mono text-safety-400">v8.0.0-phase8</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Governance:</span>
                        <span class="text-qc-400 font-semibold">Maker-Checker</span>
                    </div>
                </div>
            </aside>
        @endisset

        <!-- Main Workspace Canvas -->
        <main class="flex-1 overflow-y-auto bg-obsidian-950 p-4 sm:p-6 lg:p-8 space-y-6">
            <!-- Global Flash Messages -->
            @if (session('status'))
                <div class="rounded-xl bg-qc-500/10 border border-qc-500/30 p-4 text-sm text-qc-300 flex items-center justify-between gap-3 shadow-qc-glow">
                    <div class="flex items-center gap-2.5">
                        <span class="text-lg">✅</span>
                        <span>{{ session('status') }}</span>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-xl bg-ncr-500/10 border border-ncr-500/30 p-4 text-sm text-ncr-300 space-y-1">
                    <div class="font-bold flex items-center gap-2">
                        <span>⚠️</span>
                        <span>Please correct the following errors:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 text-xs text-ncr-200 pl-6">
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
</body>
</html>
