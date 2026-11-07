<x-layouts.app :project="$project" title="Handover, Retention Release & Final Account — {{ $project->name }}">
    @php
        $fa = $dashboard['final_account'];
        $readiness = $dashboard['readiness'];
        $punchItems = $dashboard['punch_items'];
        $certificates = $dashboard['certificates'];
    @endphp

    <div class="space-y-6">
        <!-- Sub-Header & Action Bar -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-obsidian-750">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider rounded-md bg-safety-500/15 text-safety-400 border border-safety-500/30">
                        Phase 8 · Project Handover & Closeout
                    </span>
                    <span class="text-xs font-mono text-slate-400">{{ $project->code }}</span>
                </div>
                <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight text-white flex items-center gap-2.5">
                    <span>🏁</span>
                    <span>{{ $project->name }} — Handover & Final Account</span>
                </h1>
                <p class="mt-1 text-xs text-slate-400">
                    Pre-Handover Punch List (8A), Taking-Over & Defects Liability Certificates, Retention Money Release & Final Account Statement (8B)
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('projects.ipc.index', $project) }}"
                   class="px-3.5 py-2 text-xs font-semibold rounded-xl bg-obsidian-850 hover:bg-obsidian-800 text-slate-200 border border-obsidian-700 transition">
                    💰 IPC Ledger
                </a>
                <a href="{{ route('projects.ncr.index', $project) }}"
                   class="px-3.5 py-2 text-xs font-semibold rounded-xl bg-obsidian-850 hover:bg-obsidian-800 text-slate-200 border border-obsidian-700 transition">
                    🛡️ QC / NCR
                </a>
                <a href="{{ route('projects.portfolio') }}"
                   class="px-3.5 py-2 text-xs font-semibold rounded-xl bg-obsidian-800 hover:bg-obsidian-750 text-white border border-obsidian-700 transition">
                    ← Portfolio
                </a>
            </div>
        </div>

        <!-- Final Account Statement Summary KPI Cards (MindInventory Industrial Style) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <x-ced.kpi-card
                title="Original Contract"
                value="฿{{ number_format($fa['original_contract_sum'], 2) }}"
                subtitle="Approved VOs: +฿{{ number_format($fa['approved_vo_total'], 2) }}"
                badge="Baseline"
                badgeType="default"
            />

            <x-ced.kpi-card
                title="Final Contract Sum"
                value="฿{{ number_format($fa['revised_contract_sum'], 2) }}"
                subtitle="Certified Gross: ฿{{ number_format($fa['total_certified_gross'], 2) }}"
                badge="Revised"
                badgeType="blueprint"
            />

            <x-ced.kpi-card
                title="Retention Held Total"
                value="฿{{ number_format($fa['total_retention_held'], 2) }}"
                subtitle="Released: ฿{{ number_format($fa['total_retention_released'], 2) }}"
                badge="5% Deductions"
                badgeType="hazard"
            />

            <x-ced.kpi-card
                title="Retention Available"
                value="฿{{ number_format($fa['available_retention_balance'], 2) }}"
                subtitle="Ready for TOC / DLC Release"
                badge="Liquid Cash"
                badgeType="qc"
            />

            <x-ced.kpi-card
                title="Handover Gate"
                value="{{ $readiness['is_ready_for_toc'] ? 'READY FOR TOC' : 'GATE BLOCKED' }}"
                subtitle="Open NCRs: {{ $readiness['open_ncr_count'] }} · Open Punch: {{ $readiness['open_punch_count'] }}"
                badge="{{ $readiness['is_ready_for_toc'] ? 'CLEARED' : 'BLOCKED' }}"
                badgeType="{{ $readiness['is_ready_for_toc'] ? 'qc' : 'ncr' }}"
            />
        </div>

        <!-- 8A: Pre-Handover Punch List / Snagging -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="rounded-2xl bg-obsidian-850 border border-obsidian-750 p-6 shadow-industrial">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-hazard-500"></span>
                    <h2 class="text-base font-bold text-white">🔍 Log Pre-Handover Punch Item (8A)</h2>
                </div>
                <p class="mt-1 text-xs text-slate-400">All punch items must be verified closed before issuing a Taking-Over Certificate.</p>

                <form method="POST" action="{{ route('projects.handover.punch.store', $project) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-300">Location / Zone</label>
                        <input type="text" name="location_zone" required placeholder="e.g. Level 12 Roof Mechanical Room"
                               class="mt-1 w-full rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-2 text-sm text-white focus:border-safety-500 focus:outline-none transition">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300">Discipline</label>
                            <select name="discipline" class="mt-1 w-full rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-2 text-sm text-white focus:border-safety-500 focus:outline-none transition">
                                @foreach ($disciplines as $disc)
                                    <option value="{{ $disc }}">{{ $disc }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300">Severity</label>
                            <select name="severity" class="mt-1 w-full rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-2 text-sm text-white focus:border-safety-500 focus:outline-none transition">
                                @foreach ($severities as $sev)
                                    <option value="{{ $sev }}">{{ $sev }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300">Linked WBS Task (Optional)</label>
                        <select name="wbs_task_id" class="mt-1 w-full rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-2 text-sm text-white focus:border-safety-500 focus:outline-none transition">
                            <option value="">— General Project Handover —</option>
                            @foreach ($wbsTasks as $task)
                                <option value="{{ $task->id }}">{{ $task->wbs_code }} — {{ $task->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300">Snag / Defect Description</label>
                        <textarea name="description" rows="3" required placeholder="Describe snag requiring rectification before handover..."
                                  class="mt-1 w-full rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-2 text-sm text-white focus:border-safety-500 focus:outline-none transition"></textarea>
                    </div>

                    <button type="submit"
                            class="w-full rounded-xl bg-safety-500 hover:bg-safety-600 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white shadow-safety-glow transition">
                        + Log Punch List Item
                    </button>
                </form>
            </div>

            <div class="lg:col-span-2 rounded-2xl bg-obsidian-850 border border-obsidian-750 p-6 shadow-industrial">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blueprint-500"></span>
                        <h2 class="text-base font-bold text-white">📋 Pre-Handover Punch List Register</h2>
                    </div>
                    <span class="text-xs font-mono text-slate-400 bg-obsidian-900 px-2.5 py-1 rounded-lg border border-obsidian-750">
                        Verified Closed: <strong class="text-qc-400">{{ $readiness['verified_punch_count'] }}</strong> / {{ $readiness['total_punch_count'] }}
                    </span>
                </div>

                <div class="mt-4 space-y-3">
                    @forelse ($punchItems as $item)
                        <div class="rounded-xl bg-obsidian-900 border border-obsidian-750 p-4 space-y-3 hover:border-obsidian-600 transition">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-bold text-safety-400 bg-safety-500/10 px-2 py-0.5 rounded border border-safety-500/20">
                                        {{ $item->punch_code }}
                                    </span>
                                    <span class="text-sm font-semibold text-white">{{ $item->location_zone }}</span>
                                    <span class="px-2 py-0.5 text-[11px] rounded bg-obsidian-800 text-slate-300 border border-obsidian-700">{{ $item->discipline }}</span>
                                    <span class="px-2 py-0.5 text-[11px] rounded bg-ncr-500/15 text-ncr-400 border border-ncr-500/20">{{ $item->severity }}</span>
                                </div>
                                <x-ced.status-badge :status="$item->status" />
                            </div>

                            <p class="text-xs text-slate-300">{{ $item->description }}</p>

                            @if ($item->rectification_notes)
                                <div class="text-xs text-qc-300 bg-qc-500/10 border border-qc-500/20 rounded-xl p-3">
                                    <strong class="text-qc-400">Rectification / Inspection:</strong> {{ $item->rectification_notes }}
                                </div>
                            @endif

                            @if ($item->status !== 'VERIFIED_CLOSED')
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-obsidian-750">
                                    <form method="POST" action="{{ route('projects.handover.punch.rectify', [$project, $item]) }}" class="flex gap-2">
                                        @csrf
                                        <input type="text" name="rectification_notes" required placeholder="Contractor rectification notes..."
                                               class="flex-1 rounded-xl bg-obsidian-950 border border-obsidian-700 px-3 py-1.5 text-xs text-white focus:border-safety-500 focus:outline-none transition">
                                        <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-blueprint-600 hover:bg-blueprint-500 text-xs font-semibold text-white transition">
                                            Rectify
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('projects.handover.punch.verify', [$project, $item]) }}" class="flex gap-2">
                                        @csrf
                                        <input type="text" name="verification_notes" placeholder="PM/Inspector sign-off notes..."
                                               class="flex-1 rounded-xl bg-obsidian-950 border border-obsidian-700 px-3 py-1.5 text-xs text-white focus:border-safety-500 focus:outline-none transition">
                                        <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-qc-600 hover:bg-qc-500 text-xs font-semibold text-white transition">
                                            Verify Close
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-8 text-sm text-slate-500">
                            No open punch list items. Project physical snag register is clean.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- 8B: Handover Certificates (TOC / DLC) & Retention Money Release -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="rounded-2xl bg-obsidian-850 border border-obsidian-750 p-6 shadow-industrial">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-qc-500"></span>
                    <h2 class="text-base font-bold text-white">📜 Issue Handover Certificate (8B)</h2>
                </div>
                <p class="mt-1 text-xs text-slate-400">
                    Issue Taking-Over Certificate (TOC) or Defects Liability Certificate (DLC) and specify retention release amount.
                </p>

                <form method="POST" action="{{ route('projects.handover.certificates.issue', $project) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-300">Certificate Type</label>
                        <select name="certificate_type" class="mt-1 w-full rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-2 text-sm text-white focus:border-safety-500 focus:outline-none transition">
                            @foreach ($certificateTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300">Handover Date</label>
                            <input type="date" name="handover_date" value="{{ now()->toDateString() }}" required
                                   class="mt-1 w-full rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-2 text-sm text-white focus:border-safety-500 focus:outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300">DLP Period (Months)</label>
                            <input type="number" name="dlp_months" value="12" min="0" max="120" required
                                   class="mt-1 w-full rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-2 text-sm text-white focus:border-safety-500 focus:outline-none transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300">
                            Retention Release Amount (Max: ฿{{ number_format($fa['available_retention_balance'], 2) }})
                        </label>
                        <input type="number" step="0.01" name="retention_release_amount" value="{{ $fa['available_retention_balance'] }}" required
                               class="mt-1 w-full rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-2 text-sm text-white focus:border-safety-500 focus:outline-none transition tabular-nums">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300">Scope & Final Account Remarks</label>
                        <textarea name="scope_remarks" rows="2" placeholder="Practical completion and retention release remarks..."
                                  class="mt-1 w-full rounded-xl bg-obsidian-900 border border-obsidian-700 px-3 py-2 text-sm text-white focus:border-safety-500 focus:outline-none transition"></textarea>
                    </div>

                    <button type="submit"
                            class="w-full rounded-xl bg-qc-600 hover:bg-qc-500 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white shadow-qc-glow transition">
                        Issue Handover Certificate
                    </button>
                </form>
            </div>

            <div class="lg:col-span-2 rounded-2xl bg-obsidian-850 border border-obsidian-750 p-6 shadow-industrial">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-safety-500"></span>
                    <h2 class="text-base font-bold text-white">🏦 Handover Certificates & Retention Money Release Ledger</h2>
                </div>
                <div class="mt-4 space-y-4">
                    @forelse ($certificates as $cert)
                        <div class="rounded-xl bg-obsidian-900 border border-obsidian-750 p-4 space-y-3 hover:border-obsidian-600 transition">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-sm font-bold text-qc-400 bg-qc-500/10 px-2.5 py-0.5 rounded border border-qc-500/20">
                                        {{ $cert->certificate_number }}
                                    </span>
                                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded bg-obsidian-800 text-slate-200 border border-obsidian-700">
                                        {{ $cert->certificate_type }}
                                    </span>
                                    <span class="text-xs text-slate-400 font-mono">
                                        Handover: {{ $cert->handover_date->toDateString() }} · DLP Expiry: {{ $cert->dlp_expiry_date->toDateString() }}
                                    </span>
                                </div>
                                <x-ced.status-badge :status="$cert->status" />
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs bg-obsidian-950/70 rounded-xl p-3 border border-obsidian-750/70">
                                <div>
                                    <span class="text-slate-400 block text-[11px]">Final Contract Sum</span>
                                    <span class="font-bold text-white tabular-nums">฿{{ number_format($cert->final_contract_sum, 2) }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[11px]">Approved VOs</span>
                                    <span class="font-bold text-blueprint-400 tabular-nums">฿{{ number_format($cert->total_approved_vo_amount, 2) }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[11px]">Total Retention Held</span>
                                    <span class="font-bold text-hazard-400 tabular-nums">฿{{ number_format($cert->retention_held_total, 2) }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[11px]">Retention Release</span>
                                    <span class="font-bold text-qc-400 tabular-nums">฿{{ number_format($cert->retention_release_amount, 2) }}</span>
                                </div>
                            </div>

                            @if ($cert->status !== 'RETENTION_RELEASED')
                                <form method="POST" action="{{ route('projects.handover.certificates.release-retention', [$project, $cert]) }}"
                                      class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-obsidian-750">
                                    @csrf
                                    <label class="inline-flex items-center gap-2 text-xs text-slate-300">
                                        <input type="checkbox" name="mark_project_completed" value="1" checked
                                               class="rounded bg-obsidian-950 border-obsidian-700 text-qc-500 focus:ring-qc-500">
                                        Transition Project to COMPLETED (if all WBS tasks finished)
                                    </label>
                                    <button type="submit"
                                            class="px-4 py-2 rounded-xl bg-qc-600 hover:bg-qc-500 text-xs font-bold text-white shadow-qc-glow transition">
                                        Approve & Release Retention Money (Maker-Checker)
                                    </button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-8 text-sm text-slate-500">
                            No handover certificates issued yet.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
