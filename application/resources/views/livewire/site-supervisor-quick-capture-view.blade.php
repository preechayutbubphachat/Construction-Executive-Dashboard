<div class="min-h-screen bg-slate-950 text-slate-100 pb-24 md:pb-12 selection:bg-amber-500 selection:text-slate-950">
    {{-- Sticky Header for Field Operations --}}
    <header class="sticky top-0 z-30 bg-slate-900/95 backdrop-blur-md border-b border-slate-800 px-4 py-3 shadow-lg">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-mono font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                    {{ $project->code }}
                </span>
                <div>
                    <h1 class="text-base font-bold text-white leading-tight truncate max-w-[200px] sm:max-w-md">
                        {{ $project->name }}
                    </h1>
                    <p class="text-xs text-slate-400 flex items-center gap-1.5">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Site Quick-Capture &bull; {{ now()->translatedFormat('d M Y') }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('projects.3d-twin', $project) }}" class="px-2.5 py-1.5 rounded-lg bg-indigo-900/60 hover:bg-indigo-800 text-xs font-bold text-indigo-300 border border-indigo-700 transition">
                    3D Twin 🌐
                </a>
                <a href="{{ route('projects.executive', $project) }}" class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-medium text-slate-300 border border-slate-700 transition">
                    Desktop View &rarr;
                </a>
            </div>
        </div>
    </header>

    {{-- Main Container --}}
    <main class="max-w-4xl mx-auto px-4 py-4 space-y-4">
        {{-- Flash Messages --}}
        @if ($successMessage)
            <div class="p-3.5 rounded-xl bg-emerald-950/80 border border-emerald-500/50 text-emerald-200 text-sm flex items-center justify-between shadow-lg animate-fade-in">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span class="font-medium">{{ $successMessage }}</span>
                </div>
                <button wire:click="resetFeedback" class="text-emerald-400 hover:text-emerald-200 text-xs font-bold px-2 py-1">&times;</button>
            </div>
        @endif

        @if ($errorMessage)
            <div class="p-3.5 rounded-xl bg-rose-950/80 border border-rose-500/50 text-rose-200 text-sm flex items-center justify-between shadow-lg animate-fade-in">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span class="font-medium">{{ $errorMessage }}</span>
                </div>
                <button wire:click="resetFeedback" class="text-rose-400 hover:text-rose-200 text-xs font-bold px-2 py-1">&times;</button>
            </div>
        @endif

        {{-- Desktop/Tablet Top Tabs (Hidden on small mobile) --}}
        <div class="hidden sm:flex items-center gap-2 border-b border-slate-800 pb-2">
            <button wire:click="switchTab('TASKS')" class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $activeTab === 'TASKS' ? 'bg-amber-500 text-slate-950 shadow-md' : 'bg-slate-900 text-slate-300 hover:bg-slate-800 border border-slate-800' }}">
                📋 Field Tasks ({{ $tasks->count() }})
            </button>
            <button wire:click="switchTab('LOG_BLOCKER')" class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $activeTab === 'LOG_BLOCKER' ? 'bg-rose-500 text-white shadow-md' : 'bg-slate-900 text-slate-300 hover:bg-slate-800 border border-slate-800' }}">
                🚨 Quick Blocker
            </button>
            <button wire:click="switchTab('CONSTRAINTS')" class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $activeTab === 'CONSTRAINTS' ? 'bg-amber-500 text-slate-950 shadow-md' : 'bg-slate-900 text-slate-300 hover:bg-slate-800 border border-slate-800' }}">
                ⚠️ Active Issues ({{ $constraints->count() }})
            </button>
            <button wire:click="switchTab('SUMMARY')" class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $activeTab === 'SUMMARY' ? 'bg-indigo-500 text-white shadow-md' : 'bg-slate-900 text-slate-300 hover:bg-slate-800 border border-slate-800' }}">
                📊 Shift Summary
            </button>
        </div>

        {{-- TAB 1: FIELD TASKS --}}
        @if ($activeTab === 'TASKS')
            <div class="space-y-3">
                <div class="flex items-center justify-between text-xs text-slate-400 px-1">
                    <span>Showing {{ $tasks->count() }} active leaf tasks</span>
                    <span class="flex items-center gap-2">
                        <span class="text-emerald-400 font-semibold">{{ $soundCount }} Sound</span> &bull;
                        <span class="text-rose-400 font-semibold">{{ $blockedCount }} Blocked</span>
                    </span>
                </div>

                @forelse ($tasksWithSoundness as $item)
                    @php
                        $t = $item['task'];
                        $isSound = $item['is_sound'];
                    @endphp
                    <div class="p-4 rounded-xl bg-slate-900 border {{ $isSound ? 'border-slate-800 hover:border-slate-700' : 'border-rose-900/60 bg-rose-950/10' }} shadow-md space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-mono font-bold text-amber-400">{{ $t->wbs_code }}</span>
                                    @if ($isSound)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                            SOUND / READY
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30 animate-pulse">
                                            BLOCKED
                                        </span>
                                    @endif
                                </div>
                                <h3 class="text-base font-semibold text-white mt-1">{{ $t->name }}</h3>
                            </div>

                            <span class="text-xs px-2.5 py-1 rounded font-bold uppercase {{ $t->status === 'COMPLETED' ? 'bg-emerald-500/20 text-emerald-400' : ($t->status === 'IN_PROGRESS' ? 'bg-amber-500/20 text-amber-400' : 'bg-slate-800 text-slate-400') }}">
                                {{ $t->status }}
                            </span>
                        </div>

                        <div class="text-xs text-slate-400 flex items-center justify-between border-t border-slate-800/80 pt-2">
                            <span>Dates: {{ $t->baseline_start_date }} &rarr; {{ $t->baseline_finish_date }}</span>
                            <span class="font-mono">Weight: {{ $t->weight }}%</span>
                        </div>

                        {{-- One-Tap State Action Buttons --}}
                        <div class="flex items-center gap-2 pt-1">
                            @if ($t->status === 'NOT_STARTED')
                                <button wire:click="quickTransitionTask('{{ $t->id }}', 'IN_PROGRESS')" class="flex-1 py-2.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-sm flex items-center justify-center gap-1.5 shadow transition active:scale-95">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path></svg>
                                    Start Task
                                </button>
                            @elseif ($t->status === 'IN_PROGRESS')
                                <button wire:click="quickTransitionTask('{{ $t->id }}', 'COMPLETED')" class="flex-1 py-2.5 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-sm flex items-center justify-center gap-1.5 shadow transition active:scale-95">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    Complete Task
                                </button>
                            @else
                                <span class="text-xs text-emerald-400 font-semibold py-1">✓ Task Completed</span>
                            @endif

                            {{-- Quick Blocker button for this specific task --}}
                            <button wire:click="$set('constraintTaskId', '{{ $t->id }}'); switchTab('LOG_BLOCKER')" class="px-3 py-2.5 rounded-lg bg-slate-800 hover:bg-rose-900/40 text-slate-300 hover:text-rose-200 text-xs font-semibold border border-slate-700 transition">
                                + Report Issue
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="p-8 rounded-xl bg-slate-900 border border-slate-800 text-center text-slate-400">
                        No active leaf tasks found for this project.
                    </div>
                @endforelse
            </div>
        @endif

        {{-- TAB 2: QUICK BLOCKER LOGGER --}}
        @if ($activeTab === 'LOG_BLOCKER')
            <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 shadow-xl space-y-4">
                <div class="border-b border-slate-800 pb-3">
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <span class="text-rose-400">🚨</span> Report Site Blocker / Constraint
                    </h2>
                    <p class="text-xs text-slate-400 mt-1">
                        Log field impediments immediately to alert executive dashboard and lookahead schedule.
                    </p>
                </div>

                <form wire:submit.prevent="quickLogConstraint" class="space-y-4">
                    {{-- Constraint Type Pills --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                            Blocker Category *
                        </label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach (['MATERIAL', 'EQUIPMENT', 'PERMIT', 'WEATHER', 'SAFETY', 'OTHER'] as $cat)
                                <button type="button" wire:click="$set('constraintType', '{{ $cat }}')"
                                    class="py-2.5 px-2 rounded-lg text-xs font-bold uppercase transition border {{ $constraintType === $cat ? 'bg-amber-500 text-slate-950 border-amber-400 shadow-md' : 'bg-slate-800/80 text-slate-300 border-slate-700 hover:bg-slate-750' }}">
                                    {{ $cat }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Title --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Issue Summary / What is blocked? *
                        </label>
                        <input type="text" wire:model="constraintTitle" placeholder="e.g. Missing 16mm Rebar on Ground Floor"
                            class="w-full h-12 px-3.5 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500" />
                        @error('constraintTitle') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Severity Selector --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Impact Severity *
                        </label>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach (['CRITICAL', 'HIGH', 'MEDIUM', 'LOW'] as $sev)
                                <button type="button" wire:click="$set('constraintSeverity', '{{ $sev }}')"
                                    class="py-2 rounded-lg text-xs font-bold uppercase transition border {{ $constraintSeverity === $sev ? ($sev === 'CRITICAL' ? 'bg-rose-600 text-white border-rose-500' : ($sev === 'HIGH' ? 'bg-amber-500 text-slate-950 border-amber-400' : 'bg-slate-700 text-white border-slate-600')) : 'bg-slate-800 text-slate-400 border-slate-700' }}">
                                    {{ $sev }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Linked Task --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Linked Task (Optional)
                        </label>
                        <select wire:model="constraintTaskId" class="w-full h-11 px-3 rounded-lg bg-slate-800 border border-slate-700 text-white text-xs focus:outline-none focus:border-amber-500">
                            <option value="">-- General Project Blocker (No specific task) --</option>
                            @foreach ($tasks as $task)
                                <option value="{{ $task->id }}">[{{ $task->wbs_code }}] {{ $task->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Field Details (Optional)
                        </label>
                        <textarea wire:model="constraintDescription" rows="2" placeholder="Provide extra context, supplier name, or site location..."
                            class="w-full p-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 text-xs focus:outline-none focus:border-amber-500"></textarea>
                    </div>

                    <button type="submit" class="w-full h-12 rounded-xl bg-rose-600 hover:bg-rose-500 active:scale-98 text-white font-bold text-base shadow-lg transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Log Blocker to Dashboard
                    </button>
                </form>
            </div>
        @endif

        {{-- TAB 3: ACTIVE CONSTRAINTS & RESOLUTION --}}
        @if ($activeTab === 'CONSTRAINTS')
            <div class="space-y-3">
                <div class="flex items-center justify-between text-xs text-slate-400 px-1">
                    <span>{{ $constraints->count() }} Open Constraints</span>
                    <button wire:click="switchTab('LOG_BLOCKER')" class="text-amber-400 font-bold hover:underline">+ Log New</button>
                </div>

                @forelse ($constraints as $c)
                    <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 shadow-md space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $c->severity === 'CRITICAL' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : ($c->severity === 'HIGH' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'bg-slate-800 text-slate-300') }}">
                                        {{ $c->severity }}
                                    </span>
                                    <span class="text-xs font-mono text-slate-400">[{{ $c->type }}]</span>
                                </div>
                                <h3 class="text-base font-semibold text-white mt-1">{{ $c->title }}</h3>
                                @if ($c->description)
                                    <p class="text-xs text-slate-400 mt-1">{{ $c->description }}</p>
                                @endif
                            </div>
                        </div>

                        {{-- Resolution Section --}}
                        @if ($resolvingConstraintId === $c->id)
                            <div class="p-3 rounded-lg bg-slate-950 border border-amber-500/40 space-y-2 animate-fade-in">
                                <label class="block text-xs font-semibold text-amber-300">
                                    Resolution Verification Note *
                                </label>
                                <input type="text" wire:model="resolutionNote" placeholder="Describe how the impediment was cleared..."
                                    class="w-full h-10 px-3 rounded bg-slate-800 border border-slate-700 text-white text-xs focus:outline-none focus:border-emerald-500" />
                                <div class="flex items-center gap-2">
                                    <button wire:click="quickResolveConstraint" class="flex-1 py-2 rounded bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs">
                                        Confirm & Mark Cleared
                                    </button>
                                    <button wire:click="$set('resolvingConstraintId', null)" class="px-3 py-2 rounded bg-slate-800 text-slate-400 text-xs">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        @else
                            <button wire:click="$set('resolvingConstraintId', '{{ $c->id }}')" class="w-full py-2 rounded-lg bg-emerald-950/60 hover:bg-emerald-900/80 text-emerald-300 border border-emerald-800 text-xs font-bold transition flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Clear & Mark Resolved
                            </button>
                        @endif
                    </div>
                @empty
                    <div class="p-8 rounded-xl bg-slate-900 border border-slate-800 text-center text-slate-400">
                        No open constraints. Job site is completely clear!
                    </div>
                @endforelse
            </div>
        @endif

        {{-- TAB 4: SHIFT SUMMARY --}}
        @if ($activeTab === 'SUMMARY')
            <div class="space-y-4">
                <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 shadow-md space-y-4">
                    <h2 class="text-base font-bold text-white flex items-center gap-2">
                        <span>📊</span> Daily Shift Snapshot
                    </h2>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3.5 rounded-lg bg-slate-950 border border-slate-800">
                            <span class="text-xs text-slate-400">Total Work Items</span>
                            <p class="text-2xl font-bold text-white mt-1">{{ $tasks->count() }}</p>
                        </div>
                        <div class="p-3.5 rounded-lg bg-slate-950 border border-slate-800">
                            <span class="text-xs text-emerald-400 font-semibold">Sound & Ready</span>
                            <p class="text-2xl font-bold text-emerald-400 mt-1">{{ $soundCount }}</p>
                        </div>
                        <div class="p-3.5 rounded-lg bg-slate-950 border border-slate-800">
                            <span class="text-xs text-rose-400 font-semibold">Blocked Items</span>
                            <p class="text-2xl font-bold text-rose-400 mt-1">{{ $blockedCount }}</p>
                        </div>
                        <div class="p-3.5 rounded-lg bg-slate-950 border border-slate-800">
                            <span class="text-xs text-amber-400 font-semibold">Open Constraints</span>
                            <p class="text-2xl font-bold text-amber-400 mt-1">{{ $constraints->count() }}</p>
                        </div>
                    </div>

                    <div class="border-t border-slate-800 pt-3 text-xs text-slate-400 space-y-1">
                        <p><strong>Device Mode:</strong> Mobile / Tablet Supervisor Capture</p>
                        <p><strong>Shift Timestamp:</strong> {{ now()->toRfc850String() }}</p>
                        <p><strong>Authorization:</strong> Granted for {{ Auth::user()->name }}</p>
                    </div>
                </div>
            </div>
        @endif
    </main>

    {{-- STICKY BOTTOM NAVIGATION BAR FOR MOBILE (Thumb-friendly 48px height) --}}
    <nav class="fixed bottom-0 inset-x-0 z-40 bg-slate-900/98 border-t border-slate-800 px-2 py-2 backdrop-blur-lg flex items-center justify-around sm:hidden">
        <button wire:click="switchTab('TASKS')" class="flex flex-col items-center justify-center flex-1 py-1.5 rounded-lg transition {{ $activeTab === 'TASKS' ? 'text-amber-400 font-bold' : 'text-slate-400' }}">
            <span class="text-lg">📋</span>
            <span class="text-[10px] mt-0.5">Tasks</span>
        </button>
        <button wire:click="switchTab('LOG_BLOCKER')" class="flex flex-col items-center justify-center flex-1 py-1.5 rounded-lg transition {{ $activeTab === 'LOG_BLOCKER' ? 'text-rose-400 font-bold' : 'text-slate-400' }}">
            <span class="text-lg">🚨</span>
            <span class="text-[10px] mt-0.5">Report</span>
        </button>
        <button wire:click="switchTab('CONSTRAINTS')" class="flex flex-col items-center justify-center flex-1 py-1.5 rounded-lg transition {{ $activeTab === 'CONSTRAINTS' ? 'text-amber-400 font-bold' : 'text-slate-400' }}">
            <span class="text-lg">⚠️</span>
            <span class="text-[10px] mt-0.5">Issues</span>
        </button>
        <button wire:click="switchTab('SUMMARY')" class="flex flex-col items-center justify-center flex-1 py-1.5 rounded-lg transition {{ $activeTab === 'SUMMARY' ? 'text-indigo-400 font-bold' : 'text-slate-400' }}">
            <span class="text-lg">📊</span>
            <span class="text-[10px] mt-0.5">Summary</span>
        </button>
    </nav>
</div>
