<section class="space-y-6" aria-labelledby="constraints-title">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a class="text-xs font-bold text-cyan-700 hover:underline" href="{{ route('projects.portfolio') }}">← Back to Portfolio</a>
                <span class="text-xs text-slate-400">·</span>
                <a class="text-xs font-bold text-cyan-700 hover:underline" href="{{ route('projects.executive', $project) }}">Executive Health</a>
                <span class="text-xs text-slate-400">·</span>
                <a class="text-xs font-bold text-cyan-700 hover:underline" href="{{ route('projects.schedule.show', $project) }}">Schedule & Timeline</a>
                <span class="text-xs text-slate-400">·</span>
                <a class="text-xs font-bold text-cyan-700 hover:underline" href="{{ route('projects.lookahead', $project) }}">Lookahead Schedule</a>
                <span class="text-xs text-slate-400">·</span>
                <a class="text-xs font-bold text-cyan-700 hover:underline" href="{{ route('projects.alerts', $project) }}">Alerts</a>
                <span class="text-xs text-slate-400">·</span>
                <a class="text-xs font-bold text-amber-700 hover:underline" href="{{ route('projects.site', $project) }}">Field Capture 📱</a>
                <span class="text-xs text-slate-400">·</span>
                <a class="text-xs font-bold text-indigo-700 hover:underline" href="{{ route('projects.3d-twin', $project) }}">3D Twin 🌐</a>
                <span class="text-xs text-slate-400">·</span>
                <a class="text-xs font-bold text-emerald-700 hover:underline" href="{{ route('projects.evm', $project) }}">EVM S-Curve 📈</a>
            </div>
            <p class="mt-1 text-sm font-semibold uppercase tracking-wide text-slate-500">{{ $project->code }}</p>
            <h1 id="constraints-title" class="text-2xl font-black">Constraints & Site Impediments</h1>
            <p class="text-sm text-slate-600">{{ $project->name }} · active site roadblock tracking & resolution</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('projects.evm', $project) }}" class="rounded-lg bg-emerald-700 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-emerald-800">EVM S-Curve 📈</a>
            <a href="{{ route('projects.3d-twin', $project) }}" class="rounded-lg bg-indigo-700 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-indigo-800">3D Twin 🌐</a>
            <a href="{{ route('projects.site', $project) }}" class="rounded-lg bg-amber-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-amber-700">Site Capture 📱</a>
            <a href="{{ route('projects.alerts', $project) }}" class="rounded-lg bg-red-700 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-red-800">Alerts Center</a>
            <a href="{{ route('projects.lookahead', $project) }}" class="rounded-lg bg-cyan-700 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-cyan-800">Lookahead Schedule</a>
            @if (\Illuminate\Support\Facades\Route::has('projects.report'))
                <a href="{{ route('projects.report', $project) }}" class="rounded-lg bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-slate-800">Executive Report</a>
            @endif
        </div>
    </header>

    @if ($successMessage)
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800" role="status">
            {{ $successMessage }}
        </div>
    @endif
    @if ($errorMessage)
        <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800" role="alert">
            {{ $errorMessage }}
        </div>
    @endif

    {{-- KPI Cards --}}
    <section class="grid gap-4 sm:grid-cols-4" aria-label="Constraint summary statistics">
        <div class="rounded-xl bg-white p-4 shadow">
            <p class="text-xs font-semibold uppercase text-slate-500">Total Logged</p>
            <p class="mt-1 text-2xl font-black text-slate-800">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow">
            <p class="text-xs font-semibold uppercase text-amber-600">Open Impediments</p>
            <p class="mt-1 text-2xl font-black text-amber-600">{{ $stats['open'] }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow">
            <p class="text-xs font-semibold uppercase text-red-600">Critical Severity</p>
            <p class="mt-1 text-2xl font-black text-red-600">{{ $stats['critical'] }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow">
            <p class="text-xs font-semibold uppercase text-emerald-600">Resolved</p>
            <p class="mt-1 text-2xl font-black text-emerald-600">{{ $stats['resolved'] }}</p>
        </div>
    </section>

    {{-- Create Form --}}
    <section class="rounded-xl bg-white p-6 shadow" aria-label="Log new constraint">
        <h2 class="text-base font-bold text-slate-800">Log New Site Constraint</h2>
        <p class="text-xs text-slate-500 mb-4">Record an obstacle (materials, permits, labor, site access) impacting execution or safety.</p>

        <form wire:submit.prevent="createConstraint" class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="constraint-type" class="block text-xs font-semibold uppercase text-slate-600">Type *</label>
                <select id="constraint-type" wire:model="type" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                    @foreach (\App\Models\ProjectConstraint::TYPES as $t)
                        <option value="{{ $t }}">{{ ucwords(str_replace('_', ' ', strtolower($t))) }}</option>
                    @endforeach
                </select>
                @error('type') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="constraint-severity" class="block text-xs font-semibold uppercase text-slate-600">Severity *</label>
                <select id="constraint-severity" wire:model="severity" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                    @foreach (\App\Models\ProjectConstraint::SEVERITIES as $sev)
                        <option value="{{ $sev }}">{{ $sev }}</option>
                    @endforeach
                </select>
                @error('severity') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="constraint-title" class="block text-xs font-semibold uppercase text-slate-600">Title / Summary *</label>
                <input id="constraint-title" type="text" wire:model="title" placeholder="e.g. Concrete mix batch delay from supplier" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500" />
                @error('title') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="constraint-description" class="block text-xs font-semibold uppercase text-slate-600">Description</label>
                <textarea id="constraint-description" wire:model="description" rows="2" placeholder="Details of the root cause, immediate impact, and current status..." class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500"></textarea>
                @error('description') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="constraint-task" class="block text-xs font-semibold uppercase text-slate-600">Linked WBS Task</label>
                <select id="constraint-task" wire:model="task_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                    <option value="">-- No specific task (Site-wide) --</option>
                    @foreach ($tasks as $task)
                        <option value="{{ $task->id }}">[{{ $task->wbs_code }}] {{ $task->name }}</option>
                    @endforeach
                </select>
                @error('task_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="constraint-target-date" class="block text-xs font-semibold uppercase text-slate-600">Target Resolution Date</label>
                <input id="constraint-target-date" type="date" wire:model="target_resolution_date" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500" />
                @error('target_resolution_date') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="constraint-assigned" class="block text-xs font-semibold uppercase text-slate-600">Assigned Member</label>
                <select id="constraint-assigned" wire:model="assigned_to" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                    <option value="">-- Unassigned --</option>
                    @foreach ($members as $member)
                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                    @endforeach
                </select>
                @error('assigned_to') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            <div class="sm:col-span-2 flex justify-end pt-2">
                <button type="submit" class="rounded-lg bg-cyan-700 px-5 py-2 text-sm font-bold text-white shadow hover:bg-cyan-800">
                    Log Constraint
                </button>
            </div>
        </form>
    </section>

    {{-- Resolution Modal / Card --}}
    @if ($resolvingId)
        <div class="rounded-xl border border-amber-300 bg-amber-50 p-5 shadow space-y-3" role="dialog" aria-labelledby="resolve-modal-title">
            <h3 id="resolve-modal-title" class="text-sm font-bold text-amber-900">Resolve Constraint</h3>
            <p class="text-xs text-amber-700">Please provide verification notes or describe how this impediment was resolved on-site.</p>
            <div>
                <textarea wire:model="resolutionNotes" rows="2" placeholder="Resolution details..." class="w-full rounded-lg border-amber-300 text-sm focus:border-amber-500 focus:ring-amber-500"></textarea>
                @error('resolutionNotes') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>
            <div class="flex gap-2">
                <button wire:click="resolveConstraint" class="rounded-lg bg-emerald-600 px-4 py-1.5 text-xs font-bold text-white hover:bg-emerald-700">
                    Confirm Resolution
                </button>
                <button wire:click="closeResolveModal" class="rounded-lg bg-slate-200 px-4 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-300">
                    Cancel
                </button>
            </div>
        </div>
    @endif

    {{-- Filter and Constraints Table --}}
    <section class="rounded-xl bg-white p-6 shadow space-y-4" aria-label="Constraints list">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-base font-bold text-slate-800">Constraints Register</h2>
            <div class="flex items-center gap-1">
                @foreach (['ALL' => 'All', 'OPEN' => 'Open', 'CRITICAL' => 'Critical', 'RESOLVED' => 'Resolved'] as $key => $label)
                    <button
                        wire:click="$set('filter', '{{ $key }}')"
                        class="rounded-lg px-3 py-1 text-xs font-bold transition {{ $filter === $key ? 'bg-cyan-700 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        @if ($constraints->isEmpty())
            <div class="rounded-lg border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">
                No constraints found under filter: <span class="font-bold">{{ $filter }}</span>.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="border-b border-slate-200 bg-slate-50 uppercase text-slate-600 font-semibold">
                        <tr>
                            <th class="p-3">Type</th>
                            <th class="p-3">Title & Task</th>
                            <th class="p-3">Severity</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Target Date</th>
                            <th class="p-3">Assigned To</th>
                            <th class="p-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($constraints as $c)
                            <tr class="hover:bg-slate-50">
                                <td class="p-3 whitespace-nowrap">
                                    <span class="rounded bg-slate-100 px-2 py-0.5 font-bold text-slate-700">
                                        {{ ucwords(str_replace('_', ' ', strtolower($c->type))) }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    <p class="font-bold text-slate-900">{{ $c->title }}</p>
                                    @if ($c->task)
                                        <p class="text-slate-500 text-[11px]">Task: [{{ $c->task->wbs_code }}] {{ $c->task->name }}</p>
                                    @endif
                                    @if ($c->description)
                                        <p class="text-slate-500 text-[11px] mt-0.5 line-clamp-1">{{ $c->description }}</p>
                                    @endif
                                    @if ($c->status === \App\Models\ProjectConstraint::STATUS_RESOLVED && $c->resolution_notes)
                                        <p class="text-emerald-700 text-[11px] mt-0.5">Resolved: {{ $c->resolution_notes }}</p>
                                    @endif
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    @php
                                        $sevColor = match($c->severity) {
                                            \App\Models\ProjectConstraint::SEVERITY_CRITICAL => 'bg-red-100 text-red-800 border-red-300',
                                            \App\Models\ProjectConstraint::SEVERITY_HIGH => 'bg-orange-100 text-orange-800 border-orange-300',
                                            \App\Models\ProjectConstraint::SEVERITY_MEDIUM => 'bg-amber-100 text-amber-800 border-amber-300',
                                            default => 'bg-slate-100 text-slate-700 border-slate-300',
                                        };
                                    @endphp
                                    <span class="inline-block rounded-full border px-2.5 py-0.5 text-[10px] font-bold {{ $sevColor }}">
                                        {{ $c->severity }}
                                    </span>
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    @if ($c->status === \App\Models\ProjectConstraint::STATUS_OPEN)
                                        <span class="inline-block rounded-full bg-amber-100 px-2.5 py-0.5 text-[10px] font-bold text-amber-800">OPEN</span>
                                    @else
                                        <span class="inline-block rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold text-emerald-800">RESOLVED</span>
                                    @endif
                                </td>
                                <td class="p-3 whitespace-nowrap text-slate-600">
                                    {{ $c->target_resolution_date ? $c->target_resolution_date->format('Y-m-d') : '—' }}
                                </td>
                                <td class="p-3 whitespace-nowrap text-slate-600">
                                    {{ $c->assignee ? $c->assignee->name : 'Unassigned' }}
                                </td>
                                <td class="p-3 text-right whitespace-nowrap">
                                    @if ($c->status === \App\Models\ProjectConstraint::STATUS_OPEN)
                                        <button
                                            wire:click="openResolveModal('{{ $c->id }}')"
                                            class="rounded bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 border border-emerald-200 hover:bg-emerald-100"
                                        >
                                            Resolve
                                        </button>
                                    @else
                                        <span class="text-xs text-slate-400">Closed</span>
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
