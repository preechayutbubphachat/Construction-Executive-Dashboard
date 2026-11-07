<div class="relative w-screen h-screen overflow-hidden bg-slate-950 text-slate-100 select-none">
    {{-- Top HUD Navigation Bar --}}
    <header class="absolute top-0 inset-x-0 z-20 bg-slate-900/80 backdrop-blur-md border-b border-slate-800/80 px-4 py-2.5 flex flex-wrap items-center justify-between gap-3 shadow-2xl">
        <div class="flex items-center gap-3">
            <a href="{{ route('projects.portfolio') }}" class="text-xs font-bold text-cyan-400 hover:text-cyan-300 transition">
                &larr; Portfolio
            </a>
            <span class="text-slate-600">&bull;</span>
            <span class="px-2 py-0.5 rounded text-xs font-mono font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                {{ $project->code }}
            </span>
            <div>
                <h1 class="text-sm font-bold text-white leading-tight">
                    {{ $project->name }} &middot; <span class="text-cyan-400">3D Spatial Digital Twin</span>
                </h1>
            </div>
        </div>

        {{-- KPI Quick Counters --}}
        <div class="flex items-center gap-2 text-xs">
            <span class="px-2.5 py-1 rounded-md bg-slate-800/80 border border-slate-700/80 font-mono text-slate-300">
                Total: <strong>{{ $summary['total_elements'] }}</strong>
            </span>
            <span class="px-2.5 py-1 rounded-md bg-emerald-950/60 border border-emerald-500/40 text-emerald-400 font-mono">
                Done: <strong>{{ $summary['completed_count'] }}</strong>
            </span>
            <span class="px-2.5 py-1 rounded-md bg-amber-950/60 border border-amber-500/40 text-amber-400 font-mono">
                Active: <strong>{{ $summary['in_progress_count'] }}</strong>
            </span>
            @if ($summary['blocked_count'] > 0)
                <span class="px-2.5 py-1 rounded-md bg-rose-950/80 border border-rose-500/60 text-rose-300 font-mono font-bold animate-pulse">
                    Blocked: {{ $summary['blocked_count'] }}
                </span>
            @endif
        </div>

        {{-- Cross Navigation Dropdown / Buttons --}}
        <div class="flex items-center gap-1.5 text-xs">
            <a href="{{ route('projects.schedule.show', $project) }}" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 transition">
                Timeline
            </a>
            <a href="{{ route('projects.lookahead', $project) }}" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 transition">
                Lookahead
            </a>
            <a href="{{ route('projects.constraints', $project) }}" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 transition">
                Constraints
            </a>
            <a href="{{ route('projects.site', $project) }}" class="px-2.5 py-1 rounded bg-amber-600 hover:bg-amber-500 text-slate-950 font-bold transition">
                Site 📱
            </a>
        </div>
    </header>

    {{-- Main 3D WebGL Canvas Container with Alpine.js lifecycle --}}
    <div 
        id="spatial-3d-canvas-container" 
        class="w-full h-full cursor-grab active:cursor-grabbing"
        x-data="{ visualizer: null }"
        x-init="visualizer = window.initSiteSpatial3D('spatial-3d-canvas-container', '{{ route('projects.3d-twin.data', $project) }}')"
        wire:ignore
    ></div>

    {{-- Floating Left Control Panel (Camera Presets & Exploded View) --}}
    <div class="absolute bottom-6 left-6 z-20 bg-slate-900/85 backdrop-blur-md border border-slate-800 rounded-xl p-4 shadow-2xl space-y-4 max-w-xs text-xs">
        <div>
            <label class="block font-bold text-slate-300 uppercase tracking-wider mb-1.5">Camera Presets</label>
            <div class="grid grid-cols-3 gap-1.5">
                <button wire:click="setCameraPreset('ISOMETRIC')" class="py-1.5 px-2 rounded font-semibold transition border {{ $cameraPreset === 'ISOMETRIC' ? 'bg-cyan-500 text-slate-950 border-cyan-400 font-bold' : 'bg-slate-800 text-slate-300 border-slate-700 hover:bg-slate-700' }}">
                    Isometric
                </button>
                <button wire:click="setCameraPreset('TOP_DOWN')" class="py-1.5 px-2 rounded font-semibold transition border {{ $cameraPreset === 'TOP_DOWN' ? 'bg-cyan-500 text-slate-950 border-cyan-400 font-bold' : 'bg-slate-800 text-slate-300 border-slate-700 hover:bg-slate-700' }}">
                    Top-Down
                </button>
                <button wire:click="setCameraPreset('FRONT')" class="py-1.5 px-2 rounded font-semibold transition border {{ $cameraPreset === 'FRONT' ? 'bg-cyan-500 text-slate-950 border-cyan-400 font-bold' : 'bg-slate-800 text-slate-300 border-slate-700 hover:bg-slate-700' }}">
                    Elevation
                </button>
            </div>
        </div>

        <div>
            <label class="block font-bold text-slate-300 uppercase tracking-wider mb-1.5">Spatial Filter</label>
            <div class="grid grid-cols-2 gap-1.5">
                <button wire:click="setFilterMode('ALL')" class="py-1.5 px-2 rounded font-semibold transition border {{ $filterMode === 'ALL' ? 'bg-amber-500 text-slate-950 border-amber-400 font-bold' : 'bg-slate-800 text-slate-300 border-slate-700 hover:bg-slate-700' }}">
                    All Elements
                </button>
                <button wire:click="setFilterMode('BLOCKED_ONLY')" class="py-1.5 px-2 rounded font-semibold transition border {{ $filterMode === 'BLOCKED_ONLY' ? 'bg-rose-600 text-white border-rose-500 font-bold' : 'bg-slate-800 text-slate-300 border-slate-700 hover:bg-slate-700' }}">
                    Blocked Only 🚨
                </button>
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between font-bold text-slate-300 uppercase tracking-wider mb-1">
                <span>Exploded View</span>
                <span class="font-mono text-cyan-400">{{ number_format($explodedGap, 1) }}m</span>
            </div>
            <input 
                type="range" 
                min="0" 
                max="6" 
                step="0.5" 
                wire:model.live="explodedGap"
                wire:change="setExplodedGap($event.target.value)"
                class="w-full accent-cyan-500 cursor-pointer" 
            />
        </div>

        <p class="text-[11px] text-slate-500 border-t border-slate-800/80 pt-2">
            Tip: Left-click + drag to rotate &bull; Right-click to pan &bull; Scroll to zoom &bull; Click element to inspect.
        </p>
    </div>

    {{-- Right Inspector Drawer (Slides in when element is clicked) --}}
    @if ($selectedElement)
        <aside class="absolute top-14 right-6 bottom-6 z-20 w-80 sm:w-96 bg-slate-900/95 backdrop-blur-lg border border-slate-800 rounded-2xl p-5 shadow-2xl flex flex-col justify-between overflow-y-auto animate-fade-in">
            <div class="space-y-4">
                <div class="flex items-start justify-between border-b border-slate-800 pb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-mono font-bold text-amber-400">
                                {{ $selectedElement['wbs_code'] }}
                            </span>
                            <span class="text-[10px] px-2 py-0.5 rounded font-bold uppercase {{ $selectedElement['status'] === 'COMPLETED' ? 'bg-emerald-500/20 text-emerald-400' : ($selectedElement['status'] === 'IN_PROGRESS' ? 'bg-amber-500/20 text-amber-400' : 'bg-slate-800 text-slate-400') }}">
                                {{ $selectedElement['status'] }}
                            </span>
                        </div>
                        <h2 class="text-base font-bold text-white mt-1">{{ $selectedElement['name'] }}</h2>
                        <p class="text-xs text-slate-400">Type: {{ $selectedElement['type'] }} &bull; Weight: {{ $selectedElement['weight'] }}%</p>
                    </div>
                    <button wire:click="closeInspector" class="text-slate-400 hover:text-white text-lg font-bold p-1">
                        &times;
                    </button>
                </div>

                {{-- Status & Soundness Card --}}
                <div class="p-3.5 rounded-xl border {{ $selectedElement['is_blocked'] ? 'bg-rose-950/40 border-rose-500/50 text-rose-200' : 'bg-emerald-950/40 border-emerald-500/50 text-emerald-200' }} space-y-1.5">
                    <div class="flex items-center gap-2">
                        @if ($selectedElement['is_blocked'])
                            <span class="text-rose-400 font-black text-sm">🚨 BLOCKED BY ACTIVE CONSTRAINT</span>
                        @else
                            <span class="text-emerald-400 font-bold text-sm">✓ SOUND & READY TO BUILD</span>
                        @endif
                    </div>
                    <p class="text-xs opacity-90">
                        {{ $selectedElement['is_blocked'] ? 'This building element has open impediments preventing work mobilization.' : 'No active impediments recorded against this structural package.' }}
                    </p>
                </div>

                {{-- Active Constraints Breakdown --}}
                @if ($selectedElement['is_blocked'])
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                            Active Roadblocks ({{ count($selectedElement['active_constraints']) }})
                        </label>
                        @foreach ($selectedElement['active_constraints'] as $con)
                            <div class="p-3 rounded-lg bg-slate-950 border border-slate-800 space-y-1 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-rose-400 uppercase">[{{ $con['severity'] }}] {{ $con['type'] }}</span>
                                </div>
                                <p class="text-slate-200 font-medium">{{ $con['title'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Drawer Bottom Drill-down Actions --}}
            <div class="border-t border-slate-800 pt-3 space-y-2">
                <a href="{{ route('projects.constraints', $project) }}" class="w-full py-2 rounded-lg bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs flex items-center justify-center gap-1.5 transition">
                    View in Constraints Register &rarr;
                </a>
                <a href="{{ route('projects.lookahead', $project) }}" class="w-full py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 font-medium text-xs flex items-center justify-center gap-1.5 transition">
                    Inspect in Lookahead Schedule &rarr;
                </a>
            </div>
        </aside>
    @endif
</div>
