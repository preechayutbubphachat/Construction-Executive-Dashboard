<section class="space-y-6" aria-labelledby="constraints-title">
    <!-- Project Sub-navigation Ribbon (Unified MindInventory Tabs) -->
    <x-ced.project-subnav :project="$project" active="projects.constraints" />

    <header class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200/80 pb-5">
        <div>
            <div class="flex items-center gap-2.5">
                <span class="px-2.5 py-0.5 rounded text-xs font-mono font-bold bg-orange-100 text-safety-600 border border-orange-200/70">
                    {{ $project->code }}
                </span>
                <h1 id="constraints-title" class="text-2xl font-black text-slate-900 tracking-tight">Constraints & Site Impediments</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1">{{ $project->name }} &middot; active site roadblock tracking & resolution</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('projects.site', $project) }}" class="rounded-xl bg-safety-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-safety-700 transition">Site Capture 📱</a>
            <a href="{{ route('projects.3d-twin', $project) }}" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-slate-800 transition">3D Twin 🌐</a>
            @if (\Illuminate\Support\Facades\Route::has('projects.report'))
                <a href="{{ route('projects.report', $project) }}" class="rounded-xl bg-white px-4 py-2 text-xs font-bold text-slate-800 shadow-card border border-slate-200 hover:bg-slate-50 transition">Executive Report</a>
            @endif
        </div>
    </header>

    <!-- Construction Lifecycle Process Flow (Stage 1 Active) -->
    <x-ced.workflow-stepper :project="$project" active="schedule" />

    @if ($successMessage)
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800 shadow-sm" role="status">
            {{ $successMessage }}
        </div>
    @endif
    @if ($errorMessage)
        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800 shadow-sm" role="alert">
            {{ $errorMessage }}
        </div>
    @endif

    {{-- KPI Cards --}}
    <section class="grid gap-4 sm:grid-cols-4" aria-label="Constraint summary statistics">
        <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Logged</p>
            <p class="mt-2 text-2xl font-black text-slate-900">{{ $stats['total'] }}</p>
            <p class="mt-1 text-xs text-slate-400">Total registered impediments</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-card border border-orange-200 bg-orange-50/20">
            <p class="text-xs font-bold uppercase tracking-wider text-safety-700">Open Impediments</p>
            <p class="mt-2 text-2xl font-black text-safety-600">{{ $stats['open'] }}</p>
            <p class="mt-1 text-xs text-safety-700 font-semibold">Requiring field resolution</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-card border border-rose-200 bg-rose-50/20">
            <p class="text-xs font-bold uppercase tracking-wider text-rose-700">Critical Severity</p>
            <p class="mt-2 text-2xl font-black text-rose-700">{{ $stats['critical'] }}</p>
            <p class="mt-1 text-xs text-rose-600 font-semibold">Immediate stop-work hazards</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-card border border-slate-200/80">
            <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Resolved</p>
            <p class="mt-2 text-2xl font-black text-emerald-700">{{ $stats['resolved'] }}</p>
            <p class="mt-1 text-xs text-slate-400">Cleared & unblocked</p>
        </div>
    </section>

    {{-- Create Form --}}
    <section class="rounded-2xl bg-white p-6 shadow-card border border-slate-200/80" aria-label="Log new constraint">
        <div class="border-b border-slate-100 pb-3 mb-4">
            <span class="text-xs font-mono font-bold uppercase tracking-wider text-safety-600 bg-orange-100 border border-orange-200/70 px-2.5 py-0.5 rounded-full inline-block">
                Field Intake
            </span>
            <h2 class="text-base font-black text-slate-900 mt-1">Log New Site Constraint</h2>
            <p class="text-xs text-slate-500">Record an obstacle (materials, permits, labor, site access) impacting execution or safety.</p>
        </div>

        <form wire:submit.prevent="createConstraint" class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="constraint-type" class="block text-xs font-bold uppercase text-slate-600">Type *</label>
                <select id="constraint-type" wire:model="type" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 text-xs px-3 py-2 text-slate-900 focus:border-safety-500 focus:ring-safety-500">
                    @foreach (\App\Models\ProjectConstraint::TYPES as $t)
                        <option value="{{ $t }}">{{ ucwords(str_replace('_', ' ', strtolower($t))) }}</option>
                    @endforeach
                </select>
                @error('type') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="constraint-severity" class="block text-xs font-bold uppercase text-slate-600">Severity *</label>
                <select id="constraint-severity" wire:model="severity" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 text-xs px-3 py-2 text-slate-900 focus:border-safety-500 focus:ring-safety-500">
                    @foreach (\App\Models\ProjectConstraint::SEVERITIES as $sev)
                        <option value="{{ $sev }}">{{ $sev }}</option>
                    @endforeach
                </select>
                @error('severity') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="constraint-title" class="block text-xs font-bold uppercase text-slate-600">Title / Summary *</label>
                <input id="constraint-title" type="text" wire:model="title" placeholder="e.g. Concrete mix batch delay from supplier" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 text-xs px-3 py-2 text-slate-900 focus:border-safety-500 focus:ring-safety-500" />
                @error('title') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="constraint-description" class="block text-xs font-bold uppercase text-slate-600">Description</label>
                <textarea id="constraint-description" wire:model="description" rows="2" placeholder="Details of the root cause, immediate impact, and current status..." class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 text-xs px-3 py-2 text-slate-900 focus:border-safety-500 focus:ring-safety-500"></textarea>
                @error('description') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="constraint-task" class="block text-xs font-bold uppercase text-slate-600">Linked WBS Task</label>
                <select id="constraint-task" wire:model="task_id" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 text-xs px-3 py-2 text-slate-900 focus:border-safety-500 focus:ring-safety-500">
                    <option value="">-- No specific task (Site-wide) --</option>
                    @foreach ($tasks as $task)
                        <option value="{{ $task->id }}">[{{ $task->wbs_code }}] {{ $task->name }}</option>
                    @endforeach
                </select>
                @error('task_id') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="constraint-target-date" class="block text-xs font-bold uppercase text-slate-600">Target Resolution Date</label>
                <input id="constraint-target-date" type="date" wire:model="target_resolution_date" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 text-xs px-3 py-2 text-slate-900 focus:border-safety-500 focus:ring-safety-500" />
                @error('target_resolution_date') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="constraint-assigned" class="block text-xs font-bold uppercase text-slate-600">Assigned Member</label>
                <select id="constraint-assigned" wire:model="assigned_to" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 text-xs px-3 py-2 text-slate-900 focus:border-safety-500 focus:ring-safety-500">
                    <option value="">-- Unassigned --</option>
                    @foreach ($members as $member)
                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                    @endforeach
                </select>
                @error('assigned_to') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="sm:col-span-2 flex justify-end pt-2">
                <button type="submit" class="rounded-xl bg-safety-600 hover:bg-safety-700 px-5 py-2.5 text-xs font-bold text-white shadow-sm transition">
                    Log Constraint
                </button>
            </div>
        </form>
    </section>

    {{-- Resolution Modal / Card --}}
    @if ($resolvingId)
        <div class="rounded-2xl border border-orange-300 bg-orange-50/50 p-5 shadow-card space-y-3" role="dialog" aria-labelledby="resolve-modal-title">
            <h3 id="resolve-modal-title" class="text-sm font-bold text-slate-900">Resolve Constraint</h3>
            <p class="text-xs text-slate-600">Please provide verification notes or describe how this impediment was resolved on-site.</p>
            <div>
                <textarea wire:model="resolutionNotes" rows="2" placeholder="Resolution details..." class="w-full rounded-xl border border-slate-200 bg-white text-xs px-3 py-2 text-slate-900 focus:border-safety-500 focus:ring-safety-500"></textarea>
                @error('resolutionNotes') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div class="flex gap-2">
                <button wire:click="resolveConstraint" class="rounded-xl bg-emerald-600 hover:bg-emerald-700 px-4 py-2 text-xs font-bold text-white shadow-sm transition">
                    Confirm Resolution
                </button>
                <button wire:click="closeResolveModal" class="rounded-xl bg-white border border-slate-200 hover:bg-slate-100 px-4 py-2 text-xs font-bold text-slate-700 transition">
                    Cancel
                </button>
            </div>
        </div>
    @endif

    {{-- Filter and Constraints Table --}}
    <section class="rounded-2xl bg-white shadow-card border border-slate-200/80 overflow-hidden space-y-0" aria-label="Constraints list">
        <div class="border-b border-slate-200/80 bg-slate-50/50 px-6 py-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Constraints Register (ทะเบียนข้อจำกัดและอุปสรรค)</h2>
                <p class="text-xs text-slate-500">Live impediment register categorized by root impact</p>
            </div>
            <div class="flex items-center gap-1.5">
                @foreach (['ALL' => 'All', 'OPEN' => 'Open', 'CRITICAL' => 'Critical', 'RESOLVED' => 'Resolved'] as $key => $label)
                    <button
                        wire:click="$set('filter', '{{ $key }}')"
                        class="rounded-xl px-3 py-1.5 text-xs font-bold transition {{ $filter === $key ? 'bg-safety-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        @if ($constraints->isEmpty())
            <div class="p-12 text-center text-xs text-slate-500">
                No constraints found under filter: <span class="font-bold">{{ $filter }}</span>.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="border-b border-slate-200/80 bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 font-bold">
                        <tr>
                            <th class="px-6 py-3.5">Type</th>
                            <th class="px-6 py-3.5">Title & Task</th>
                            <th class="px-6 py-3.5">Severity</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5">Target Date</th>
                            <th class="px-6 py-3.5">Assigned To</th>
                            <th class="px-6 py-3.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach ($constraints as $c)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="rounded-lg bg-slate-100 border border-slate-200 px-2 py-0.5 font-bold text-slate-700 text-[11px]">
                                        {{ ucwords(str_replace('_', ' ', strtolower($c->type))) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-bold text-slate-900">{{ $c->title }}</p>
                                    @if ($c->task)
                                        <p class="text-slate-500 text-[11px] mt-0.5 font-mono">Task: [{{ $c->task->wbs_code }}] {{ $c->task->name }}</p>
                                    @endif
                                    @if ($c->description)
                                        <p class="text-slate-500 text-[11px] mt-0.5 line-clamp-1">{{ $c->description }}</p>
                                    @endif
                                    @if ($c->status === \App\Models\ProjectConstraint::STATUS_RESOLVED && $c->resolution_notes)
                                        <p class="text-emerald-700 text-[11px] mt-0.5 font-semibold">Resolved: {{ $c->resolution_notes }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $sevColor = match($c->severity) {
                                            \App\Models\ProjectConstraint::SEVERITY_CRITICAL => 'bg-rose-100 text-rose-800 border-rose-300',
                                            \App\Models\ProjectConstraint::SEVERITY_HIGH => 'bg-orange-100 text-safety-700 border-orange-300',
                                            \App\Models\ProjectConstraint::SEVERITY_MEDIUM => 'bg-amber-100 text-amber-800 border-amber-300',
                                            default => 'bg-slate-100 text-slate-700 border-slate-300',
                                        };
                                    @endphp
                                    <span class="inline-block rounded-full border px-2.5 py-0.5 text-[10px] font-bold {{ $sevColor }}">
                                        {{ $c->severity }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($c->status === \App\Models\ProjectConstraint::STATUS_OPEN)
                                        <span class="inline-block rounded-full bg-amber-100 border border-amber-200 px-2.5 py-0.5 text-[10px] font-bold text-amber-800">OPEN</span>
                                    @else
                                        <span class="inline-block rounded-full bg-emerald-100 border border-emerald-200 px-2.5 py-0.5 text-[10px] font-bold text-emerald-800">RESOLVED</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600 font-mono text-[11px]">
                                    {{ $c->target_resolution_date ? $c->target_resolution_date->format('Y-m-d') : '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-700">
                                    {{ $c->assignee ? $c->assignee->name : 'Unassigned' }}
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    @if ($c->status === \App\Models\ProjectConstraint::STATUS_OPEN)
                                        <button
                                            wire:click="openResolveModal('{{ $c->id }}')"
                                            class="rounded-xl bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-800 border border-emerald-200 hover:bg-emerald-100 transition"
                                        >
                                            Resolve
                                        </button>
                                    @else
                                        <span class="text-xs text-slate-400 font-medium">Closed</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</section>
