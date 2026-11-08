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
<body class="h-full font-sans antialiased bg-slate-100 text-slate-900 flex flex-col selection:bg-safety-orange selection:text-white" x-data="{ mobileNavOpen: false }">
    <!-- Master Application Shell (Deep Navy Sidebar + Pure White Header + Clean Light Canvas) -->
    <div class="flex h-full w-full overflow-hidden relative">
        
        <!-- Mobile Slide-over Drawer (Off-canvas Navigation for Mobile/Tablet) -->
        <div x-show="mobileNavOpen" x-cloak style="display: none;" class="relative z-50 md:hidden" role="dialog" aria-modal="true">
            <div x-show="mobileNavOpen" 
                 x-transition:enter="transition-opacity ease-linear duration-200" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="transition-opacity ease-linear duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 @click="mobileNavOpen = false"
                 style="display: none;"
                 class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs"></div>

            <div class="fixed inset-0 flex">
                <div x-show="mobileNavOpen" 
                     x-transition:enter="transition ease-in-out duration-200 transform" 
                     x-transition:enter-start="-translate-x-full" 
                     x-transition:enter-end="translate-x-0" 
                     x-transition:leave="transition ease-in-out duration-200 transform" 
                     x-transition:leave-start="translate-x-0" 
                     x-transition:leave-end="-translate-x-full" 
                     class="relative mr-16 flex w-full max-w-xs flex-1">
                    
                    <div class="absolute left-full top-0 flex w-16 justify-center pt-5">
                        <button @click="mobileNavOpen = false" type="button" class="-m-2.5 p-2.5 text-white hover:text-safety-orange transition">
                            <span class="sr-only">Close sidebar</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <!-- Mobile Drawer Content -->
                    <div class="w-full bg-[#0B132B] shadow-2xl flex flex-col justify-between overflow-y-auto">
                        <x-ced.sidebar-nav :project="$project ?? null" />
                    </div>
                </div>
            </div>
        </div>

        <!-- 1. Deep-Navy Left Sidebar (Desktop Master Navigation) -->
        <aside class="w-64 bg-[#0B132B] text-slate-300 flex flex-col justify-between shrink-0 hidden md:flex border-r border-[#1E293B] shadow-xl z-30 select-none">
            <x-ced.sidebar-nav :project="$project ?? null" />
        </aside>

        <!-- 2. Right Canvas Area: Universal Top Bar + Main Workspace -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-100">
            
            <!-- Universal Top Header Bar (Pure White with Search & Project Switcher) -->
            <header class="sticky top-0 z-30 bg-white border-b border-slate-200/80 px-4 sm:px-6 py-2.5 sm:py-3 shadow-xs">
                <div class="flex items-center justify-between gap-3">
                    
                    <!-- Left: Mobile Menu Hamburger & Search Input -->
                    <div class="flex items-center gap-2.5 sm:gap-3 flex-1 min-w-0">
                        <button @click="mobileNavOpen = true" type="button" 
                                class="md:hidden p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-slate-200 shrink-0 transition" 
                                aria-label="Open sidebar menu">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>

                        <div class="relative w-full max-w-xs sm:max-w-sm md:max-w-md">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <input type="text"
                                   placeholder="Search..."
                                   class="w-full pl-9 pr-4 py-1.5 sm:py-2 text-xs rounded-xl border border-slate-200 bg-slate-50/70 text-slate-800 placeholder-slate-400 focus:bg-white focus:border-safety-orange focus:ring-1 focus:ring-safety-orange transition">
                        </div>
                    </div>

                    <!-- Right Controls: Project Switcher & Status -->
                    <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                        
                        <!-- Project Selector (MindInventory 'Project: Delta Phase ▾') -->
                        @if(isset($project))
                            @php
                                $navProjects = auth()->check()
                                    ? \App\Models\Project::query()
                                        ->whereHas('members', fn ($query) => $query->whereKey(auth()->id()))
                                        ->select(['projects.id', 'projects.code', 'projects.name', 'projects.status'])
                                        ->orderBy('code')
                                        ->get()
                                    : collect();
                            @endphp
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" type="button"
                                        class="inline-flex items-center gap-1.5 sm:gap-2 rounded-xl border border-slate-300 bg-white px-2.5 sm:px-3.5 py-1.5 sm:py-2 text-xs font-bold text-slate-800 hover:border-slate-400 hover:bg-slate-50 transition shadow-2xs">
                                    <span class="text-slate-400 font-medium hidden sm:inline">Project:</span>
                                    <span class="text-slate-900 font-extrabold truncate max-w-[120px] sm:max-w-[170px]">{{ $project->name }}</span>
                                    <svg class="w-3.5 h-3.5 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>

                                <!-- Backdrop to catch outside clicks and prevent unwanted overlay hanging -->
                                <div x-show="open" @click="open = false" x-cloak style="display: none;" class="fixed inset-0 z-40 bg-slate-900/10 backdrop-blur-2xs"></div>

                                <!-- Project Switcher Popover -->
                                <div x-show="open" 
                                     @click.away="open = false" 
                                     @keydown.escape.window="open = false" 
                                     x-cloak
                                     style="display: none;"
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute right-0 mt-2 w-72 sm:w-80 rounded-2xl bg-white p-3 shadow-2xl border border-slate-200 text-xs z-50">
                                    
                                    <div class="flex items-center justify-between px-2 pb-2 border-b border-slate-100">
                                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Switch Project (เปลี่ยนโครงการ)</span>
                                        <button @click="open = false" type="button" class="text-slate-400 hover:text-slate-700 font-bold p-1 text-base leading-none">&times;</button>
                                    </div>

                                    <!-- Current Active Scope Card -->
                                    <div class="my-2 p-2.5 rounded-xl bg-orange-50 border border-orange-200">
                                        <div class="text-[10px] uppercase font-bold text-safety-orange">Current Active Scope</div>
                                        <div class="font-extrabold text-slate-900 truncate mt-0.5">{{ $project->code }} &middot; {{ $project->name }}</div>
                                    </div>

                                    <!-- Other Projects List -->
                                    <div class="max-h-48 overflow-y-auto space-y-1 pr-1">
                                        @foreach ($navProjects as $navP)
                                            @if ($navP->id !== $project->id)
                                                <a href="{{ route('projects.executive', $navP) }}" 
                                                   class="flex items-center justify-between p-2 rounded-xl text-slate-700 hover:bg-slate-50 hover:text-safety-orange font-semibold transition group">
                                                    <div class="truncate mr-2">
                                                        <span class="font-mono text-[10px] font-bold text-slate-400 group-hover:text-safety-orange">{{ $navP->code }}</span>
                                                        <div class="text-xs text-slate-800 group-hover:text-slate-900 truncate font-bold">{{ $navP->name }}</div>
                                                    </div>
                                                    <span class="text-[10px] px-2 py-0.5 rounded-full border bg-slate-100 text-slate-600 font-mono shrink-0">{{ $navP->status }}</span>
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>

                                    <div class="mt-2 pt-2 border-t border-slate-100">
                                        <a href="{{ route('projects.portfolio') }}" 
                                           class="flex items-center justify-center gap-1.5 w-full py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs transition">
                                            <span>← All Projects Portfolio (ดูทุกโครงการ)</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('projects.portfolio') }}"
                               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-2.5 sm:px-3.5 py-1.5 sm:py-2 text-xs font-bold text-slate-800 shadow-2xs">
                                <span class="w-2 h-2 rounded-full bg-safety-orange"></span>
                                <span class="hidden sm:inline">Multi-Project Portfolio</span>
                                <span class="sm:hidden">Portfolio</span>
                            </a>
                        @endif

                        <!-- Notifications & Activity Icon -->
                        <button type="button" class="relative p-1.5 sm:p-2 rounded-xl border border-slate-200 text-slate-600 hover:text-safety-orange hover:bg-slate-50 transition" title="Notifications">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-safety-orange ring-2 ring-white"></span>
                        </button>

                        <!-- Messages Icon -->
                        <button type="button" class="p-1.5 sm:p-2 rounded-xl border border-slate-200 text-slate-600 hover:text-safety-orange hover:bg-slate-50 transition" title="Messages">
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
