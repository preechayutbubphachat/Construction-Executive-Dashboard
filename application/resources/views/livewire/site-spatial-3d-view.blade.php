<div class="relative w-screen h-screen overflow-hidden bg-slate-900 text-slate-100 select-none">
    {{-- Top HUD Navigation Bar (Clean Light Glass) --}}
    <header class="absolute top-0 inset-x-0 z-20 bg-white/95 backdrop-blur-md border-b border-slate-200/80 px-4 py-2.5 flex flex-wrap items-center justify-between gap-3 shadow-card">
        <div class="flex items-center gap-3">
            <a href="{{ route('projects.portfolio') }}" class="text-xs font-bold text-safety-600 hover:text-safety-700 transition">
                &larr; Portfolio
            </a>
            <span class="text-slate-300">&bull;</span>
            <span class="px-2 py-0.5 rounded-lg text-xs font-mono font-bold bg-orange-100 text-safety-600 border border-orange-200/70">
                {{ $project->code }}
            </span>
            <div>
                <h1 class="text-sm font-bold text-slate-900 leading-tight">
                    {{ $project->name }} &middot; <span class="text-safety-600 font-extrabold">3D Spatial Digital Twin</span>
                </h1>
            </div>
        </div>

        {{-- KPI Quick Counters --}}
        <div class="flex items-center gap-2 text-xs">
            <span class="px-2.5 py-1 rounded-xl bg-slate-50 border border-slate-200 font-mono text-slate-700">
                Total: <strong class="text-slate-900">{{ $summary['total_elements'] }}</strong>
            </span>
            <span class="px-2.5 py-1 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 font-mono">
                Done: <strong>{{ $summary['completed_count'] }}</strong>
            </span>
            <span class="px-2.5 py-1 rounded-xl bg-orange-50 border border-orange-200 text-safety-700 font-mono">
                Active: <strong>{{ $summary['in_progress_count'] }}</strong>
            </span>
            @if ($summary['blocked_count'] > 0)
                <span class="px-2.5 py-1 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 font-mono font-bold animate-pulse">
                    Blocked: {{ $summary['blocked_count'] }}
                </span>
            @endif
        </div>

        {{-- Cross Navigation Dropdown / Buttons --}}
        <div class="flex items-center gap-1.5 text-xs">
            <a href="{{ route('projects.evm', $project) }}" class="px-2.5 py-1 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 font-bold transition">
                EVM 📈
            </a>
            <a href="{{ route('projects.schedule.show', $project) }}" class="px-2.5 py-1 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 transition">
                Timeline
            </a>
            <a href="{{ route('projects.lookahead', $project) }}" class="px-2.5 py-1 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 transition">
                Lookahead
            </a>
            <a href="{{ route('projects.constraints', $project) }}" class="px-2.5 py-1 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 transition">
                Constraints
            </a>
            <a href="{{ route('projects.site', $project) }}" class="px-3 py-1 rounded-xl bg-safety-600 hover:bg-safety-700 text-white font-bold transition shadow-sm">
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
    <div class="absolute bottom-6 left-6 z-20 bg-white/95 backdrop-blur-md border border-slate-200/80 rounded-2xl p-4 shadow-card space-y-4 max-w-xs text-xs text-slate-800">
        <div>
            <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Camera Presets</label>
            <div class="grid grid-cols-3 gap-1.5">
                <button wire:click="setCameraPreset('ISOMETRIC')" class="py-1.5 px-2 rounded-xl font-bold transition border {{ $cameraPreset === 'ISOMETRIC' ? 'bg-safety-600 text-white border-safety-600 shadow-sm' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                    Isometric
                </button>
                <button wire:click="setCameraPreset('TOP_DOWN')" class="py-1.5 px-2 rounded-xl font-bold transition border {{ $cameraPreset === 'TOP_DOWN' ? 'bg-safety-600 text-white border-safety-600 shadow-sm' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                    Top-Down
                </button>
                <button wire:click="setCameraPreset('FRONT')" class="py-1.5 px-2 rounded-xl font-bold transition border {{ $cameraPreset === 'FRONT' ? 'bg-safety-600 text-white border-safety-600 shadow-sm' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                    Elevation
                </button>
            </div>
        </div>

        <div>
            <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Spatial Filter</label>
            <div class="grid grid-cols-2 gap-1.5">
                <button wire:click="setFilterMode('ALL')" class="py-1.5 px-2 rounded-xl font-bold transition border {{ $filterMode === 'ALL' ? 'bg-slate-900 text-white border-slate-900 shadow-sm' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                    All Elements
                </button>
                <button wire:click="setFilterMode('BLOCKED_ONLY')" class="py-1.5 px-2 rounded-xl font-bold transition border {{ $filterMode === 'BLOCKED_ONLY' ? 'bg-rose-600 text-white border-rose-600 shadow-sm' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                    Blocked Only 🚨
                </button>
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between font-bold text-slate-700 uppercase tracking-wider mb-1">
                <span>Exploded View</span>
                <span class="font-mono text-safety-600">{{ number_format($explodedGap, 1) }}m</span>
            </div>
            <input 
                type="range" 
                min="0" 
                max="6" 
                step="0.5" 
                wire:model.live="explodedGap"
                wire:change="setExplodedGap($event.target.value)"
                class="w-full accent-orange-500 cursor-pointer" 
            />
        </div>

        <p class="text-[11px] text-slate-400 border-t border-slate-100 pt-2 font-medium">
            Tip: Left-click + drag to rotate &bull; Right-click to pan &bull; Scroll to zoom &bull; Click element to inspect.
        </p>
    </div>

    {{-- Right Inspector Drawer (Slides in when element is clicked) --}}
    @if ($selectedElement)
        <aside class="absolute top-14 right-6 bottom-6 z-20 w-80 sm:w-96 bg-white/95 backdrop-blur-lg border border-slate-200/80 rounded-2xl p-5 shadow-card flex flex-col justify-between overflow-y-auto animate-fade-in text-slate-800">
            <div class="space-y-4">
                <div class="flex items-start justify-between border-b border-slate-100 pb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-mono font-bold text-safety-600">
                                {{ $selectedElement['wbs_code'] }}
                            </span>
                            <span class="text-[10px] px-2.5 py-0.5 rounded-full font-bold uppercase border {{ $selectedElement['status'] === 'COMPLETED' ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : ($selectedElement['status'] === 'IN_PROGRESS' ? 'bg-orange-100 text-safety-600 border-orange-200' : 'bg-slate-100 text-slate-700 border-slate-200') }}">
                                {{ $selectedElement['status'] }}
                            </span>
                        </div>
                        <h2 class="text-base font-bold text-slate-900 mt-1">{{ $selectedElement['name'] }}</h2>
                        <p class="text-xs text-slate-500">Type: {{ $selectedElement['type'] }} &bull; Weight: {{ $selectedElement['weight'] }}%</p>
                    </div>
                    <button wire:click="closeInspector" class="text-slate-400 hover:text-slate-800 text-lg font-bold p-1">
                        &times;
                    </button>
                </div>

                {{-- Status & Soundness Card --}}
                <div class="p-4 rounded-xl border {{ $selectedElement['is_blocked'] ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-emerald-50 border-emerald-200 text-emerald-800' }} space-y-1.5">
                    <div class="flex items-center gap-2">
                        @if ($selectedElement['is_blocked'])
                            <span class="text-rose-700 font-black text-xs">🚨 BLOCKED BY ACTIVE CONSTRAINT</span>
                        @else
                            <span class="text-emerald-700 font-bold text-xs">✓ SOUND & READY TO BUILD</span>
                        @endif
                    </div>
                    <p class="text-xs opacity-90">
                        {{ $selectedElement['is_blocked'] ? 'This building element has open impediments preventing work mobilization.' : 'No active impediments recorded against this structural package.' }}
                    </p>
                </div>

                {{-- Active Constraints Breakdown --}}
                @if ($selectedElement['is_blocked'])
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Active Roadblocks ({{ count($selectedElement['active_constraints']) }})
                        </label>
                        @foreach ($selectedElement['active_constraints'] as $con)
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 space-y-1 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-rose-700 uppercase">[{{ $con['severity'] }}] {{ $con['type'] }}</span>
                                </div>
                                <p class="text-slate-800 font-medium">{{ $con['title'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Drawer Bottom Drill-down Actions --}}
            <div class="border-t border-slate-100 pt-3 space-y-2">
                <a href="{{ route('projects.constraints', $project) }}" class="w-full py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-sm transition">
                    View in Constraints Register &rarr;
                </a>
                <a href="{{ route('projects.lookahead', $project) }}" class="w-full py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs flex items-center justify-center gap-1.5 transition">
                    Inspect in Lookahead Schedule &rarr;
                </a>
            </div>
        </aside>
    @endif
</div>
