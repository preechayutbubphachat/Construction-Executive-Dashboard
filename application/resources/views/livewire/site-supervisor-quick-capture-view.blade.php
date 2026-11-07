<div class="min-h-screen bg-canvas-base text-slate-900 pb-24 md:pb-12">
    {{-- Sticky Header for Field Operations --}}
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-200/80 px-4 py-3 shadow-card">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-orange-100 text-safety-600 border border-orange-200/70">
                    {{ $project->code }}
                </span>
                <div>
                    <h1 class="text-base font-bold text-slate-900 leading-tight truncate max-w-[200px] sm:max-w-md">
                        {{ $project->name }}
                    </h1>
                    <p class="text-xs text-slate-500 flex items-center gap-1.5">
                        <span class="inline-block w-2 h-2 rounded-full bg-safety-500 animate-pulse"></span>
                        Site Quick-Capture &bull; {{ now()->translatedFormat('d M Y') }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('projects.reports.daily', $project) }}" class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-50 text-xs font-bold text-slate-700 border border-slate-200 shadow-sm transition">
                    Daily Log 📅
                </a>
                <a href="{{ route('projects.3d-twin', $project) }}" class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-xs font-bold text-white shadow-sm transition">
                    3D Twin 🌐
                </a>
                <a href="{{ route('projects.executive', $project) }}" class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-50 text-xs font-medium text-slate-600 border border-slate-200 transition">
                    Desktop View &rarr;
                </a>
            </div>
        </div>
    </header>

    {{-- Main Container --}}
    <main class="max-w-4xl mx-auto px-4 py-5 space-y-4">
        {{-- Flash Messages --}}
        @if ($successMessage)
            <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-sm animate-fade-in">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span class="font-medium">{{ $successMessage }}</span>
                </div>
                <button wire:click="resetFeedback" class="text-emerald-600 hover:text-emerald-800 text-xs font-bold px-2 py-1">&times;</button>
            </div>
        @endif

        @if ($errorMessage)
            <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center justify-between shadow-sm animate-fade-in">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span class="font-medium">{{ $errorMessage }}</span>
                </div>
                <button wire:click="resetFeedback" class="text-rose-600 hover:text-rose-800 text-xs font-bold px-2 py-1">&times;</button>
            </div>
        @endif

        {{-- Desktop/Tablet Top Tabs (Hidden on small mobile) --}}
        <div class="hidden sm:flex items-center gap-2 border-b border-slate-200/80 pb-3 overflow-x-auto">
            <button wire:click="switchTab('TASKS')" class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $activeTab === 'TASKS' ? 'bg-safety-600 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200/80 shadow-sm' }}">
                📋 Field Tasks ({{ $tasks->count() }})
            </button>
            <button wire:click="switchTab('LOG_BLOCKER')" class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $activeTab === 'LOG_BLOCKER' ? 'bg-rose-600 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200/80 shadow-sm' }}">
                🚨 Quick Blocker
            </button>
            <button wire:click="switchTab('CONSTRAINTS')" class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $activeTab === 'CONSTRAINTS' ? 'bg-safety-600 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200/80 shadow-sm' }}">
                ⚠️ Active Issues ({{ $constraints->count() }})
            </button>
            <button wire:click="switchTab('PHOTOS')" class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $activeTab === 'PHOTOS' ? 'bg-cyan-600 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200/80 shadow-sm' }}">
                📸 Field Photos ({{ $photoStats['total'] }})
            </button>
            <button wire:click="switchTab('SUMMARY')" class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $activeTab === 'SUMMARY' ? 'bg-slate-900 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200/80 shadow-sm' }}">
                📊 Shift Summary
            </button>
        </div>

        {{-- TAB 1: FIELD TASKS --}}
        @if ($activeTab === 'TASKS')
            <div class="space-y-3">
                <div class="flex items-center justify-between text-xs text-slate-500 px-1 font-medium">
                    <span>Showing {{ $tasks->count() }} active leaf tasks</span>
                    <span class="flex items-center gap-2">
                        <span class="text-emerald-700 font-bold">{{ $soundCount }} Sound</span> &bull;
                        <span class="text-rose-700 font-bold">{{ $blockedCount }} Blocked</span>
                    </span>
                </div>

                @forelse ($tasksWithSoundness as $item)
                    @php
                        $t = $item['task'];
                        $isSound = $item['is_sound'];
                    @endphp
                    <div class="p-5 rounded-2xl bg-white border {{ $isSound ? 'border-slate-200/80' : 'border-rose-200 bg-rose-50/20' }} shadow-card space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-mono font-bold text-safety-600">{{ $t->wbs_code }}</span>
                                    @if ($isSound)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            SOUND / READY
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200 animate-pulse">
                                            BLOCKED
                                        </span>
                                    @endif
                                </div>
                                <h3 class="text-base font-bold text-slate-900 mt-1">{{ $t->name }}</h3>
                            </div>

                            <span class="text-xs px-2.5 py-0.5 rounded-full font-bold uppercase border {{ $t->status === 'COMPLETED' ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : ($t->status === 'IN_PROGRESS' ? 'bg-orange-100 text-safety-600 border-orange-200' : 'bg-slate-100 text-slate-600 border-slate-200') }}">
                                {{ $t->status }}
                            </span>
                        </div>

                        <div class="text-xs text-slate-500 flex items-center justify-between border-t border-slate-100 pt-2.5 font-medium">
                            <span>Dates: {{ $t->baseline_start_date }} &rarr; {{ $t->baseline_finish_date }}</span>
                            <span class="font-mono">Weight: {{ $t->weight }}%</span>
                        </div>

                        {{-- One-Tap State Action Buttons --}}
                        <div class="flex items-center gap-2 pt-1">
                            @if ($t->status === 'NOT_STARTED')
                                <button wire:click="quickTransitionTask('{{ $t->id }}', 'IN_PROGRESS')" class="flex-1 py-2.5 rounded-xl bg-safety-600 hover:bg-safety-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-sm transition active:scale-95">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path></svg>
                                    Start Task
                                </button>
                            @elseif ($t->status === 'IN_PROGRESS')
                                <button wire:click="quickTransitionTask('{{ $t->id }}', 'COMPLETED')" class="flex-1 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-sm transition active:scale-95">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    Complete Task
                                </button>
                            @else
                                <span class="text-xs text-emerald-700 font-bold py-1">✓ Task Completed</span>
                            @endif

                            {{-- Quick Photo Snap for this task --}}
                            <button wire:click="$set('photoTaskId', '{{ $t->id }}'); switchTab('PHOTOS')" class="px-3.5 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-cyan-800 text-xs font-bold border border-slate-200 transition flex items-center gap-1">
                                <span>📸</span> Photo
                            </button>

                            {{-- Quick Blocker button for this specific task --}}
                            <button wire:click="$set('constraintTaskId', '{{ $t->id }}'); switchTab('LOG_BLOCKER')" class="px-3.5 py-2.5 rounded-xl bg-slate-50 hover:bg-rose-50 text-slate-700 hover:text-rose-700 text-xs font-bold border border-slate-200 transition">
                                + Issue
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="p-8 rounded-2xl bg-white border border-slate-200/80 text-center text-slate-500 shadow-card">
                        No active leaf tasks found for this project.
                    </div>
                @endforelse
            </div>
        @endif

        {{-- TAB 2: QUICK BLOCKER LOGGER --}}
        @if ($activeTab === 'LOG_BLOCKER')
            <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-card space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <span class="text-xs font-mono font-bold uppercase tracking-wider text-rose-600 bg-rose-100 border border-rose-200 px-2.5 py-0.5 rounded-full inline-block">
                        Impediment Intake
                    </span>
                    <h2 class="text-lg font-black text-slate-900 flex items-center gap-2 mt-1">
                        Report Site Blocker / Constraint
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Log field impediments immediately to alert executive dashboard and lookahead schedule.
                    </p>
                </div>

                <form wire:submit.prevent="quickLogConstraint" class="space-y-4">
                    {{-- Constraint Type Pills --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Blocker Category *
                        </label>
                        <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                            @foreach (['MATERIAL', 'EQUIPMENT', 'PERMIT', 'WEATHER', 'SAFETY', 'OTHER'] as $cat)
                                <button type="button" wire:click="$set('constraintType', '{{ $cat }}')"
                                    class="py-2 px-2 rounded-xl text-xs font-bold uppercase transition border {{ $constraintType === $cat ? 'bg-safety-600 text-white border-safety-600 shadow-sm' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                                    {{ $cat }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Title --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Issue Summary / What is blocked? *
                        </label>
                        <input type="text" wire:model="constraintTitle" placeholder="e.g. Missing 16mm Rebar on Ground Floor"
                            class="w-full h-11 px-3.5 rounded-xl border border-slate-200 bg-slate-50/50 text-slate-900 placeholder-slate-400 text-xs focus:border-safety-500 focus:ring-safety-500" />
                        @error('constraintTitle') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Severity Selector --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Impact Severity *
                        </label>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach (['CRITICAL', 'HIGH', 'MEDIUM', 'LOW'] as $sev)
                                <button type="button" wire:click="$set('constraintSeverity', '{{ $sev }}')"
                                    class="py-2 rounded-xl text-xs font-bold uppercase transition border {{ $constraintSeverity === $sev ? ($sev === 'CRITICAL' ? 'bg-rose-600 text-white border-rose-600' : ($sev === 'HIGH' ? 'bg-safety-600 text-white border-safety-600' : 'bg-slate-800 text-white border-slate-800')) : 'bg-slate-50 text-slate-600 border-slate-200' }}">
                                    {{ $sev }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Linked Task --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Linked Task (Optional)
                        </label>
                        <select wire:model="constraintTaskId" class="w-full h-11 px-3 rounded-xl border border-slate-200 bg-slate-50/50 text-slate-900 text-xs focus:border-safety-500 focus:ring-safety-500">
                            <option value="">-- General Project Blocker (No specific task) --</option>
                            @foreach ($tasks as $task)
                                <option value="{{ $task->id }}">[{{ $task->wbs_code }}] {{ $task->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Field Details (Optional)
                        </label>
                        <textarea wire:model="constraintDescription" rows="2" placeholder="Provide extra context, supplier name, or site location..."
                            class="w-full p-3 rounded-xl border border-slate-200 bg-slate-50/50 text-slate-900 placeholder-slate-400 text-xs focus:border-safety-500 focus:ring-safety-500"></textarea>
                    </div>

                    <button type="submit" class="w-full h-11 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-sm transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Log Blocker to Dashboard
                    </button>
                </form>
            </div>
        @endif

        {{-- TAB 3: ACTIVE CONSTRAINTS & RESOLUTION --}}
        @if ($activeTab === 'CONSTRAINTS')
            <div class="space-y-3">
                <div class="flex items-center justify-between text-xs text-slate-500 px-1 font-medium">
                    <span>{{ $constraints->count() }} Open Constraints</span>
                    <button wire:click="switchTab('LOG_BLOCKER')" class="text-safety-600 font-bold hover:underline">+ Log New</button>
                </div>

                @forelse ($constraints as $c)
                    <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-card space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase border {{ $c->severity === 'CRITICAL' ? 'bg-rose-100 text-rose-800 border-rose-200' : ($c->severity === 'HIGH' ? 'bg-orange-100 text-safety-600 border-orange-200' : 'bg-slate-100 text-slate-700 border-slate-200') }}">
                                        {{ $c->severity }}
                                    </span>
                                    <span class="text-xs font-mono text-slate-500">[{{ $c->type }}]</span>
                                </div>
                                <h3 class="text-base font-bold text-slate-900 mt-1">{{ $c->title }}</h3>
                                @if ($c->description)
                                    <p class="text-xs text-slate-600 mt-1">{{ $c->description }}</p>
                                @endif
                            </div>
                        </div>

                        {{-- Resolution Section --}}
                        @if ($resolvingConstraintId === $c->id)
                            <div class="p-4 rounded-xl bg-orange-50/50 border border-orange-200 space-y-2.5 animate-fade-in">
                                <label class="block text-xs font-bold text-slate-800">
                                    Resolution Verification Note *
                                </label>
                                <input type="text" wire:model="resolutionNote" placeholder="Describe how the impediment was cleared..."
                                    class="w-full h-10 px-3 rounded-xl border border-slate-200 bg-white text-slate-900 text-xs focus:border-safety-500 focus:ring-safety-500" />
                                <div class="flex items-center gap-2">
                                    <button wire:click="quickResolveConstraint" class="flex-1 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition">
                                        Confirm & Mark Cleared
                                    </button>
                                    <button wire:click="$set('resolvingConstraintId', null)" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        @else
                            <button wire:click="$set('resolvingConstraintId', '{{ $c->id }}')" class="w-full py-2.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs font-bold transition flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Clear & Mark Resolved
                            </button>
                        @endif
                    </div>
                @empty
                    <div class="p-8 rounded-2xl bg-white border border-slate-200/80 text-center text-slate-500 shadow-card">
                        No open constraints. Job site is completely clear!
                    </div>
                @endforelse
            </div>
        @endif

        {{-- TAB 4: FIELD PHOTO EVIDENCE & INSPECTION GALLERY --}}
        @if ($activeTab === 'PHOTOS')
            <div class="space-y-5" x-data="{
                requestLocation() {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            (pos) => {
                                @this.setCoordinates(pos.coords.latitude, pos.coords.longitude);
                            },
                            (err) => {
                                console.warn('Geolocation error:', err.message);
                            },
                            { enableHighAccuracy: true, timeout: 8000 }
                        );
                    }
                }
            }" x-init="requestLocation()">
                {{-- SECTION 1: FAST PHOTO CAPTURE & WATERMARK FORM --}}
                <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-card space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="p-2 rounded-xl bg-orange-100 text-safety-600 text-lg">📸</span>
                            <div>
                                <h2 class="text-base font-bold text-slate-900">Take Field Photo & Watermark Evidence</h2>
                                <p class="text-xs text-slate-500">Capture jobsite progress, defects & attach GPS telemetry</p>
                            </div>
                        </div>
                        <button type="button" @click="requestLocation()" class="px-3 py-1.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-xs font-bold text-slate-700 border border-slate-200 flex items-center gap-1.5 transition">
                            <svg class="w-3.5 h-3.5 text-safety-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <span>Refresh GPS</span>
                        </button>
                    </div>

                    {{-- Camera Input / Drag Zone --}}
                    <div>
                        <input type="file" id="site-camera-file" accept="image/*" capture="environment" wire:model="photoFile" class="hidden">
                        <label for="site-camera-file" class="cursor-pointer flex flex-col items-center justify-center p-6 border-2 border-dashed border-orange-200 hover:border-safety-500 bg-orange-50/20 rounded-2xl transition group">
                            <div class="w-14 h-14 rounded-full bg-orange-100 group-hover:bg-orange-200 flex items-center justify-center text-safety-600 text-2xl transition mb-2">
                                📷
                            </div>
                            <span class="text-sm font-bold text-slate-900 group-hover:text-safety-600">Tap to Snap Photo or Choose File</span>
                            <span class="text-xs text-slate-500 mt-1">Accepts live camera capture (JPG, PNG, WebP up to 12MB)</span>
                        </label>

                        <div wire:loading wire:target="photoFile" class="mt-2 p-3 text-center rounded-xl bg-orange-50 border border-orange-200 text-safety-700 text-xs font-bold animate-pulse">
                            Processing camera image and staging upload...
                        </div>

                        @if ($photoFile)
                            <div class="mt-3 p-3 rounded-2xl bg-slate-50 border border-slate-200 flex items-center gap-3">
                                <div class="w-16 h-16 rounded-xl overflow-hidden border border-slate-200 shrink-0 bg-white">
                                    <img src="{{ $photoFile->temporaryUrl() }}" class="w-full h-full object-cover" alt="Preview">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-slate-900 truncate">{{ $photoFile->getClientOriginalName() }}</p>
                                    <p class="text-[11px] text-emerald-700 mt-0.5 font-semibold">Ready to stamp with cryptographic SHA-256</p>
                                </div>
                            </div>
                        @endif
                        @error('photoFile') <span class="text-xs text-rose-600 block mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- GPS Telemetry Auto-Detection Status --}}
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <span class="inline-block w-2.5 h-2.5 rounded-full {{ $photoLat && $photoLng ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500' }}"></span>
                            <span class="text-slate-600 font-bold">GPS Telemetry:</span>
                            @if ($photoLat && $photoLng)
                                <span class="font-mono text-emerald-700 font-bold">
                                    {{ round($photoLat, 6) }}, {{ round($photoLng, 6) }}
                                </span>
                            @else
                                <span class="text-amber-700 italic">Detecting device coordinates...</span>
                            @endif
                        </div>
                        @if ($photoLat && $photoLng)
                            <span class="text-[10px] uppercase font-bold tracking-wider px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                                Geotag Ready
                            </span>
                        @endif
                    </div>

                    {{-- Category Selection Pills --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Inspection Category (ประเภทการตรวจ)</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <button type="button" wire:click="$set('photoCategory', 'PROGRESS')" class="py-2 px-3 rounded-xl text-xs font-bold border transition flex items-center justify-center gap-1.5 {{ $photoCategory === 'PROGRESS' ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                                <span>🟢</span> Progress
                            </button>
                            <button type="button" wire:click="$set('photoCategory', 'DEFECT')" class="py-2 px-3 rounded-xl text-xs font-bold border transition flex items-center justify-center gap-1.5 {{ $photoCategory === 'DEFECT' ? 'bg-rose-100 text-rose-800 border-rose-300' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                                <span>🔴</span> Defect
                            </button>
                            <button type="button" wire:click="$set('photoCategory', 'QUALITY_CHECK')" class="py-2 px-3 rounded-xl text-xs font-bold border transition flex items-center justify-center gap-1.5 {{ $photoCategory === 'QUALITY_CHECK' ? 'bg-cyan-100 text-cyan-800 border-cyan-300' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                                <span>🔵</span> QC Inspect
                            </button>
                            <button type="button" wire:click="$set('photoCategory', 'SAFETY')" class="py-2 px-3 rounded-xl text-xs font-bold border transition flex items-center justify-center gap-1.5 {{ $photoCategory === 'SAFETY' ? 'bg-orange-100 text-safety-700 border-orange-300' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                                <span>🟠</span> Safety
                            </button>
                        </div>
                    </div>

                    {{-- Target Association (WBS Task or Constraint) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Link to WBS Task (ผูกกับงานย่อย)</label>
                            <select wire:model="photoTaskId" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:border-safety-500 focus:ring-safety-500">
                                <option value="">-- None / General Site Overview --</option>
                                @foreach ($tasks as $t)
                                    <option value="{{ $t->id }}">[{{ $t->wbs_code }}] {{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Link to Constraint (ผูกกับอุปสรรค)</label>
                            <select wire:model="photoConstraintId" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:border-safety-500 focus:ring-safety-500">
                                <option value="">-- None / No Constraint Linked --</option>
                                @foreach ($constraints as $c)
                                    <option value="{{ $c->id }}">[{{ $c->type }}] {{ $c->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Caption / Field Notes --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Inspection Note / Caption (บันทึกตรวจรับ)</label>
                        <input type="text" wire:model="photoCaption" placeholder="e.g. Concrete slump test 12cm passed, rebar spacing verified" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-900 placeholder-slate-400 focus:border-safety-500 focus:ring-safety-500">
                        @error('photoCaption') <span class="text-xs text-rose-600 block mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Action Button --}}
                    <button type="button" wire:click="uploadSitePhoto" wire:loading.attr="disabled" class="w-full py-3 rounded-xl bg-safety-600 hover:bg-safety-700 text-white font-bold text-xs shadow-sm flex items-center justify-center gap-2 transition active:scale-[0.99] disabled:opacity-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        <span>Stamp & Save Evidence (บันทึกหลักฐานและประทับลายน้ำ)</span>
                    </button>
                </div>

                {{-- SECTION 2: FIELD INSPECTION GALLERY --}}
                <div class="space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200/80 pb-3">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                <span>🖼️</span> Field Inspection Gallery
                            </h3>
                            <p class="text-xs text-slate-500">Cryptographically verified on-site visual evidence</p>
                        </div>

                        {{-- Quick Telemetry Counters --}}
                        <div class="flex items-center gap-2 text-xs">
                            <span class="px-2.5 py-1 rounded-xl bg-white border border-slate-200 text-slate-700 font-mono shadow-sm">
                                Total: <strong class="text-slate-900">{{ $photoStats['total'] }}</strong>
                            </span>
                            <span class="px-2.5 py-1 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 font-mono">
                                GPS: <strong>{{ $photoStats['withGps'] }}</strong>
                            </span>
                            <span class="px-2.5 py-1 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 font-mono">
                                Defects: <strong>{{ $photoStats['defects'] }}</strong>
                            </span>
                        </div>
                    </div>

                    {{-- Filter Tabs --}}
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
                        <button wire:click="$set('galleryFilter', 'ALL')" class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap {{ $galleryFilter === 'ALL' ? 'bg-slate-900 text-white shadow-sm' : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200' }}">
                            All Photos ({{ $photoStats['total'] }})
                        </button>
                        <button wire:click="$set('galleryFilter', 'PROGRESS')" class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap {{ $galleryFilter === 'PROGRESS' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-emerald-700 border border-slate-200' }}">
                            🟢 Progress
                        </button>
                        <button wire:click="$set('galleryFilter', 'DEFECT')" class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap {{ $galleryFilter === 'DEFECT' ? 'bg-rose-600 text-white shadow-sm' : 'bg-white text-rose-700 border border-slate-200' }}">
                            🔴 Defects
                        </button>
                        <button wire:click="$set('galleryFilter', 'QUALITY_CHECK')" class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap {{ $galleryFilter === 'QUALITY_CHECK' ? 'bg-cyan-600 text-white shadow-sm' : 'bg-white text-cyan-700 border border-slate-200' }}">
                            🔵 QC Inspect
                        </button>
                        <button wire:click="$set('galleryFilter', 'SAFETY')" class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap {{ $galleryFilter === 'SAFETY' ? 'bg-safety-600 text-white shadow-sm' : 'bg-white text-safety-600 border border-slate-200' }}">
                            🟠 Safety
                        </button>
                        <button wire:click="$set('galleryFilter', 'GPS_ONLY')" class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap {{ $galleryFilter === 'GPS_ONLY' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-indigo-700 border border-slate-200' }}">
                            📍 GPS Only
                        </button>
                    </div>

                    {{-- Photos Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @forelse ($photos as $photo)
                            @php
                                $cat = $photo->metadata['category'] ?? 'PROGRESS';
                                $badgeColor = match ($cat) {
                                    'DEFECT' => 'bg-rose-100 text-rose-800 border-rose-200',
                                    'QUALITY_CHECK' => 'bg-cyan-100 text-cyan-800 border-cyan-200',
                                    'SAFETY' => 'bg-orange-100 text-safety-700 border-orange-200',
                                    default => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                };
                            @endphp
                            <div class="rounded-2xl bg-white border border-slate-200/80 overflow-hidden shadow-card hover:shadow-card-hover transition flex flex-col group">
                                {{-- Thumbnail Header --}}
                                <div class="relative h-48 bg-slate-100 cursor-pointer overflow-hidden" wire:click="openPhotoModal('{{ $photo->id }}')">
                                    <img src="{{ route('projects.evidence.preview', [$project, $photo]) }}" alt="{{ $photo->caption ?: 'Field inspection' }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" loading="lazy">

                                    {{-- Category badge --}}
                                    <span class="absolute top-2.5 left-2.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $badgeColor }} backdrop-blur-md">
                                        {{ $cat }}
                                    </span>

                                    {{-- Watermark / Verification pill --}}
                                    <span class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-white/90 text-safety-600 border border-slate-200 backdrop-blur-md flex items-center gap-1">
                                        <span>🛡️</span> Verified
                                    </span>

                                    {{-- Bottom Overlay with GPS & Time --}}
                                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-900/90 via-slate-900/50 to-transparent p-2.5 pt-6 text-[11px] flex items-center justify-between text-white">
                                        <span class="font-mono">{{ $photo->captured_at ? $photo->captured_at->format('d M H:i') : $photo->created_at->format('d M H:i') }}</span>
                                        @if ($photo->gps_latitude && $photo->gps_longitude)
                                            <span class="font-mono text-cyan-300 font-bold flex items-center gap-0.5">
                                                📍 {{ round($photo->gps_latitude, 4) }}, {{ round($photo->gps_longitude, 4) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Card Details --}}
                                <div class="p-4 space-y-2 flex-1 flex flex-col justify-between">
                                    <div>
                                        <p class="text-xs font-bold text-slate-900 leading-snug">
                                            {{ $photo->caption ?: ($photo->original_name ?: 'Daily site log photo') }}
                                        </p>

                                        {{-- Linked Entity Badges --}}
                                        <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                            @if ($photo->task)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-orange-50 text-safety-600 border border-orange-200">
                                                    WBS: {{ $photo->task->wbs_code }}
                                                </span>
                                            @endif
                                            @if ($photo->constraint)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    BLOCKER: {{ Str::limit($photo->constraint->title, 20) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500 font-medium">
                                        <span>By: {{ $photo->uploader->name ?? 'Supervisor' }}</span>
                                        <button wire:click="openPhotoModal('{{ $photo->id }}')" class="text-safety-600 hover:text-safety-700 font-bold flex items-center gap-0.5">
                                            Inspect &rarr;
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-1 sm:col-span-2 p-10 rounded-2xl bg-white border border-slate-200/80 text-center text-slate-500 space-y-2 shadow-card">
                                <span class="text-3xl">📷</span>
                                <p class="text-sm font-bold text-slate-800">No field photos match this filter.</p>
                                <p class="text-xs text-slate-400">Tap "Take Field Photo" above to log first visual evidence.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- SECTION 3: INSPECTION ZOOM & CRYPTOGRAPHIC TELEMETRY MODAL --}}
                @if ($selectedPhoto)
                    <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
                        <div class="bg-white border border-slate-200 rounded-2xl max-w-2xl w-full overflow-hidden shadow-2xl space-y-0 animate-fade-in" @click.outside="$wire.closePhotoModal()">
                            {{-- Modal Header --}}
                            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase {{ match ($selectedPhoto->metadata['category'] ?? 'PROGRESS') { 'DEFECT' => 'bg-rose-100 text-rose-800', 'QUALITY_CHECK' => 'bg-cyan-100 text-cyan-800', 'SAFETY' => 'bg-orange-100 text-safety-700', default => 'bg-emerald-100 text-emerald-800' } }}">
                                        {{ $selectedPhoto->metadata['category'] ?? 'PROGRESS' }}
                                    </span>
                                    <h4 class="text-sm font-bold text-slate-900 truncate max-w-xs sm:max-w-md">
                                        {{ $selectedPhoto->caption ?: $selectedPhoto->original_name }}
                                    </h4>
                                </div>
                                <button wire:click="closePhotoModal" class="p-1 rounded-lg text-slate-400 hover:text-slate-800 hover:bg-slate-100 text-lg leading-none">&times;</button>
                            </div>

                            {{-- High-Res Image View --}}
                            <div class="relative bg-slate-950 flex items-center justify-center max-h-[60vh] overflow-hidden">
                                <img src="{{ route('projects.evidence.preview', [$project, $selectedPhoto]) }}" alt="Inspection Full Preview" class="w-full max-h-[60vh] object-contain">
                            </div>

                            {{-- Telemetry Details Drawer --}}
                            <div class="p-5 bg-slate-50 space-y-3 text-xs">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-slate-700">
                                    <div>
                                        <span class="text-slate-500 block">Captured Date & Time:</span>
                                        <strong class="font-mono text-slate-900">{{ $selectedPhoto->captured_at ? $selectedPhoto->captured_at->format('Y-m-d H:i:s T') : $selectedPhoto->created_at }}</strong>
                                    </div>
                                    <div>
                                        <span class="text-slate-500 block">GPS Coordinates:</span>
                                        @if ($selectedPhoto->gps_latitude && $selectedPhoto->gps_longitude)
                                            <a href="https://www.google.com/maps?q={{ $selectedPhoto->gps_latitude }},{{ $selectedPhoto->gps_longitude }}" target="_blank" rel="noopener" class="font-mono text-safety-600 hover:underline font-bold flex items-center gap-1">
                                                📍 {{ round($selectedPhoto->gps_latitude, 6) }}, {{ round($selectedPhoto->gps_longitude, 6) }} &nearr;
                                            </a>
                                        @else
                                            <span class="text-slate-400">Not recorded</span>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="text-slate-500 block">Linked Work Item:</span>
                                        <span class="text-slate-900 font-semibold">{{ $selectedPhoto->task ? '['.$selectedPhoto->task->wbs_code.'] '.$selectedPhoto->task->name : ($selectedPhoto->constraint ? 'Blocker: '.$selectedPhoto->constraint->title : 'General Site Progress') }}</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-500 block">Site Inspector:</span>
                                        <span class="text-slate-900 font-semibold">{{ $selectedPhoto->uploader->name ?? 'Field Engineer' }}</span>
                                    </div>
                                </div>

                                {{-- SHA-256 Checksum --}}
                                <div class="p-3 rounded-xl bg-white border border-slate-200 font-mono text-[11px] break-all">
                                    <span class="text-slate-500 block text-[10px] uppercase font-bold tracking-wider">Cryptographic SHA-256 Fingerprint</span>
                                    <span class="text-safety-600 font-bold">{{ $selectedPhoto->sha256 }}</span>
                                </div>

                                {{-- Footer Action Buttons --}}
                                <div class="flex items-center justify-end gap-2 pt-2">
                                    <a href="{{ route('projects.evidence.download', [$project, $selectedPhoto]) }}" class="px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs border border-slate-200 flex items-center gap-1.5 transition">
                                        <svg class="w-4 h-4 text-safety-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        Download Raw
                                    </a>
                                    <button wire:click="closePhotoModal" class="px-4 py-2 rounded-xl bg-safety-600 hover:bg-safety-700 text-white font-bold text-xs shadow-sm transition">
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- TAB 5: SHIFT SUMMARY --}}
        @if ($activeTab === 'SUMMARY')
            <div class="space-y-4">
                <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-card space-y-4">
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span>📊</span> Daily Shift Snapshot
                    </h2>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="text-xs text-slate-500 font-medium">Total Work Items</span>
                            <p class="text-2xl font-black text-slate-900 mt-1">{{ $tasks->count() }}</p>
                        </div>
                        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200">
                            <span class="text-xs text-emerald-800 font-bold">Sound & Ready</span>
                            <p class="text-2xl font-black text-emerald-700 mt-1">{{ $soundCount }}</p>
                        </div>
                        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200">
                            <span class="text-xs text-rose-800 font-bold">Blocked Items</span>
                            <p class="text-2xl font-black text-rose-700 mt-1">{{ $blockedCount }}</p>
                        </div>
                        <div class="p-4 rounded-xl bg-orange-50 border border-orange-200">
                            <span class="text-xs text-safety-700 font-bold">Open Constraints</span>
                            <p class="text-2xl font-black text-safety-600 mt-1">{{ $constraints->count() }}</p>
                        </div>
                        <div class="p-4 rounded-xl bg-cyan-50 border border-cyan-200">
                            <span class="text-xs text-cyan-800 font-bold">Field Photos Stamped</span>
                            <p class="text-2xl font-black text-cyan-700 mt-1">{{ $photoStats['total'] }}</p>
                        </div>
                        <div class="p-4 rounded-xl bg-indigo-50 border border-indigo-200">
                            <span class="text-xs text-indigo-800 font-bold">GPS Verified</span>
                            <p class="text-2xl font-black text-indigo-700 mt-1">{{ $photoStats['withGps'] }}</p>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-3 text-xs text-slate-500 space-y-1 font-medium">
                        <p><strong>Device Mode:</strong> Mobile / Tablet Supervisor Capture</p>
                        <p><strong>Shift Timestamp:</strong> {{ now()->toRfc850String() }}</p>
                        <p><strong>Authorization:</strong> Granted for {{ Auth::user()->name }}</p>
                    </div>
                </div>
            </div>
        @endif
    </main>

    {{-- STICKY BOTTOM NAVIGATION BAR FOR MOBILE (Thumb-friendly 48px height) --}}
    <nav class="fixed bottom-0 inset-x-0 z-40 bg-white/98 border-t border-slate-200/80 px-2 py-2 backdrop-blur-lg flex items-center justify-around sm:hidden shadow-lg">
        <button wire:click="switchTab('TASKS')" class="flex flex-col items-center justify-center flex-1 py-1.5 rounded-xl transition {{ $activeTab === 'TASKS' ? 'text-safety-600 font-bold' : 'text-slate-400' }}">
            <span class="text-lg">📋</span>
            <span class="text-[10px] mt-0.5">Tasks</span>
        </button>
        <button wire:click="switchTab('LOG_BLOCKER')" class="flex flex-col items-center justify-center flex-1 py-1.5 rounded-xl transition {{ $activeTab === 'LOG_BLOCKER' ? 'text-rose-600 font-bold' : 'text-slate-400' }}">
            <span class="text-lg">🚨</span>
            <span class="text-[10px] mt-0.5">Report</span>
        </button>
        <button wire:click="switchTab('CONSTRAINTS')" class="flex flex-col items-center justify-center flex-1 py-1.5 rounded-xl transition {{ $activeTab === 'CONSTRAINTS' ? 'text-safety-600 font-bold' : 'text-slate-400' }}">
            <span class="text-lg">⚠️</span>
            <span class="text-[10px] mt-0.5">Issues</span>
        </button>
        <button wire:click="switchTab('PHOTOS')" class="flex flex-col items-center justify-center flex-1 py-1.5 rounded-xl transition {{ $activeTab === 'PHOTOS' ? 'text-cyan-600 font-bold' : 'text-slate-400' }}">
            <span class="text-lg">📸</span>
            <span class="text-[10px] mt-0.5">Photos</span>
        </button>
        <button wire:click="switchTab('SUMMARY')" class="flex flex-col items-center justify-center flex-1 py-1.5 rounded-xl transition {{ $activeTab === 'SUMMARY' ? 'text-slate-900 font-bold' : 'text-slate-400' }}">
            <span class="text-lg">📊</span>
            <span class="text-[10px] mt-0.5">Summary</span>
        </button>
    </nav>
</div>
