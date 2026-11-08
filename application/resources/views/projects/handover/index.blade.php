<x-layouts.app :project="$project" title="Handover, Retention Release & Final Account — {{ $project->name }}">
    @php
        $fa = $dashboard['final_account'];
        $readiness = $dashboard['readiness'];
        $punchItems = $dashboard['punch_items'];
        $certificates = $dashboard['certificates'];
    @endphp

    <div class="space-y-6">
        <!-- Project Sub-navigation Ribbon (Unified MindInventory Tabs) -->
        <x-ced.project-subnav :project="$project" active="projects.handover.index" />

        <!-- Sub-Header & Action Bar (MindInventory Style) -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-slate-200">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider rounded-md bg-orange-100 text-orange-700 border border-orange-200">
                        Phase 8 · Project Handover & Closeout
                    </span>
                    <span class="text-xs font-mono font-bold text-slate-500">{{ $project->code }}</span>
                </div>
                <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 flex items-center gap-2.5">
                    <span>🏁</span>
                    <span>{{ $project->name }} — Handover & Final Account</span>
                </h1>
                <p class="mt-1 text-xs text-slate-500">
                    Pre-Handover Punch List (8A), Taking-Over & Defects Liability Certificates, Retention Money Release & Final Account Statement (8B)
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('projects.ipc.index', $project) }}"
                   class="px-3.5 py-2 text-xs font-semibold rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 shadow-2xs transition">
                    💰 IPC Ledger
                </a>
                <a href="{{ route('projects.ncr.index', $project) }}"
                   class="px-3.5 py-2 text-xs font-semibold rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 shadow-2xs transition">
                    🛡️ QC / NCR
                </a>
            </div>
        </div>

        <!-- Construction Lifecycle Process Flow (Stage 7 Handover Active) -->
        <x-ced.workflow-stepper :project="$project" active="handover" />

        <!-- Final Account Statement Summary KPI Cards (MindInventory Clean White Card Deck) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <x-ced.kpi-card
                title="มูลค่าสัญญาเดิม (Original Contract)"
                value="฿{{ number_format($fa['original_contract_sum'], 2) }}"
                subtitle="Approved VOs: +฿{{ number_format($fa['approved_vo_total'], 2) }}"
                badge="Baseline"
                badgeType="default"
            />

            <x-ced.kpi-card
                title="มูลค่าสัญญาสุดท้าย (Final Contract Sum)"
                value="฿{{ number_format($fa['revised_contract_sum'], 2) }}"
                subtitle="Certified Gross: ฿{{ number_format($fa['total_certified_gross'], 2) }}"
                badge="Revised"
                badgeType="blueprint"
            />

            <x-ced.kpi-card
                title="เงินประกันผลงานสะสม (Retention Held Total)"
                value="฿{{ number_format($fa['total_retention_held'], 2) }}"
                subtitle="Released: ฿{{ number_format($fa['total_retention_released'], 2) }}"
                badge="5% Deductions"
                badgeType="hazard"
            />

            <x-ced.kpi-card
                title="เงินประกันพร้อมคืน (Retention Available)"
                value="฿{{ number_format($fa['available_retention_balance'], 2) }}"
                subtitle="Ready for TOC / DLC Release"
                badge="Liquid Cash"
                badgeType="qc"
            />

            <x-ced.kpi-card
                title="ด่านตรวจส่งมอบ (Handover Gate)"
                value="{{ $readiness['is_ready_for_toc'] ? 'READY FOR TOC' : 'GATE BLOCKED' }}"
                subtitle="Open NCRs: {{ $readiness['open_ncr_count'] }} · Open Punch: {{ $readiness['open_punch_count'] }}"
                badge="{{ $readiness['is_ready_for_toc'] ? 'CLEARED' : 'BLOCKED' }}"
                badgeType="{{ $readiness['is_ready_for_toc'] ? 'qc' : 'ncr' }}"
            />
        </div>

        <!-- 8A: Pre-Handover Punch List / Snagging -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="rounded-2xl bg-white border border-slate-200/90 p-6 shadow-card">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <h2 class="text-base font-bold text-slate-900">🔍 Log Pre-Handover Punch Item (8A)</h2>
                </div>
                <p class="mt-1 text-xs text-slate-500">All punch items must be verified closed before issuing a Taking-Over Certificate.</p>

                <form method="POST" action="{{ route('projects.handover.punch.store', $project) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Location / Zone</label>
                        <input type="text" name="location_zone" required placeholder="e.g. Level 12 Roof Mechanical Room"
                               class="mt-1 w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-safety-500 focus:outline-none transition">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700">Discipline</label>
                            <select name="discipline" class="mt-1 w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:bg-white focus:border-safety-500 focus:outline-none transition">
                                @foreach ($disciplines as $disc)
                                    <option value="{{ $disc }}">{{ $disc }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700">Severity</label>
                            <select name="severity" class="mt-1 w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:bg-white focus:border-safety-500 focus:outline-none transition">
                                @foreach ($severities as $sev)
                                    <option value="{{ $sev }}">{{ $sev }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700">Linked WBS Task (Optional)</label>
                        <select name="wbs_task_id" class="mt-1 w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:bg-white focus:border-safety-500 focus:outline-none transition">
                            <option value="">— General Project Handover —</option>
                            @foreach ($wbsTasks as $task)
                                <option value="{{ $task->id }}">{{ $task->wbs_code }} — {{ $task->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700">Snag / Defect Description</label>
                        <textarea name="description" rows="3" required placeholder="Describe snag requiring rectification before handover..."
                                  class="mt-1 w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-safety-500 focus:outline-none transition"></textarea>
                    </div>

                    <button type="submit"
                            class="w-full rounded-xl bg-safety-500 hover:bg-safety-600 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white shadow-safety-glow transition">
                        + Log Punch List Item
                    </button>
                </form>
            </div>

            <div class="lg:col-span-2 rounded-2xl bg-white border border-slate-200/90 p-6 shadow-card">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                        <h2 class="text-base font-bold text-slate-900">📋 Pre-Handover Punch List Register</h2>
                    </div>
                    <span class="text-xs font-mono font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">
                        Verified Closed: <strong class="text-emerald-700">{{ $readiness['verified_punch_count'] }}</strong> / {{ $readiness['total_punch_count'] }}
                    </span>
                </div>

                <div class="mt-4 space-y-3">
                    @forelse ($punchItems as $item)
                        <div class="rounded-xl bg-slate-50 border border-slate-200 p-4 space-y-3 hover:border-slate-300 transition">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-bold text-orange-700 bg-orange-50 px-2 py-0.5 rounded border border-orange-200">
                                        {{ $item->punch_code }}
                                    </span>
                                    <span class="text-sm font-bold text-slate-900">{{ $item->location_zone }}</span>
                                    <span class="px-2 py-0.5 text-[11px] font-semibold rounded bg-slate-200 text-slate-700 border border-slate-300">{{ $item->discipline }}</span>
                                    <span class="px-2 py-0.5 text-[11px] font-semibold rounded bg-rose-50 text-rose-700 border border-rose-200">{{ $item->severity }}</span>
                                </div>
                                <x-ced.status-badge :status="$item->status" />
                            </div>

                            <p class="text-xs text-slate-700">{{ $item->description }}</p>

                            @if ($item->rectification_notes)
                                <div class="text-xs text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-xl p-3">
                                    <strong class="text-emerald-700">Rectification / Inspection:</strong> {{ $item->rectification_notes }}
                                </div>
                            @endif

                            @if ($item->status !== 'VERIFIED_CLOSED')
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-slate-200">
                                    <form method="POST" action="{{ route('projects.handover.punch.rectify', [$project, $item]) }}" class="flex gap-2">
                                        @csrf
                                        <input type="text" name="rectification_notes" required placeholder="Contractor rectification notes..."
                                               class="flex-1 rounded-xl bg-white border border-slate-300 px-3 py-1.5 text-xs text-slate-800 focus:border-safety-500 focus:outline-none transition">
                                        <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-xs font-semibold text-white transition">
                                            Rectify
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('projects.handover.punch.verify', [$project, $item]) }}" class="flex gap-2">
                                        @csrf
                                        <input type="text" name="verification_notes" placeholder="PM/Inspector sign-off notes..."
                                               class="flex-1 rounded-xl bg-white border border-slate-300 px-3 py-1.5 text-xs text-slate-800 focus:border-safety-500 focus:outline-none transition">
                                        <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-xs font-semibold text-white transition">
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
            <div class="rounded-2xl bg-white border border-slate-200/90 p-6 shadow-card">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <h2 class="text-base font-bold text-slate-900">📜 Issue Handover Certificate (8B)</h2>
                </div>
                <p class="mt-1 text-xs text-slate-500">
                    Issue Taking-Over Certificate (TOC) or Defects Liability Certificate (DLC) and specify retention release amount.
                </p>

                <form method="POST" action="{{ route('projects.handover.certificates.issue', $project) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700">Certificate Type</label>
                        <select name="certificate_type" class="mt-1 w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:bg-white focus:border-safety-500 focus:outline-none transition">
                            @foreach ($certificateTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700">Handover Date</label>
                            <input type="date" name="handover_date" value="{{ now()->toDateString() }}" required
                                   class="mt-1 w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:bg-white focus:border-safety-500 focus:outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700">DLP Period (Months)</label>
                            <input type="number" name="dlp_months" value="12" min="0" max="120" required
                                   class="mt-1 w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:bg-white focus:border-safety-500 focus:outline-none transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700">
                            Retention Release Amount (Max: ฿{{ number_format($fa['available_retention_balance'], 2) }})
                        </label>
                        <input type="number" step="0.01" name="retention_release_amount" value="{{ $fa['available_retention_balance'] }}" required
                               class="mt-1 w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:bg-white focus:border-safety-500 focus:outline-none transition tabular-nums">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700">Scope & Final Account Remarks</label>
                        <textarea name="scope_remarks" rows="2" placeholder="Practical completion and retention release remarks..."
                                  class="mt-1 w-full rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-safety-500 focus:outline-none transition"></textarea>
                    </div>

                    <button type="submit"
                            class="w-full rounded-xl bg-emerald-600 hover:bg-emerald-700 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white shadow-xs transition">
                        Issue Handover Certificate
                    </button>
                </form>
            </div>

            <div class="lg:col-span-2 rounded-2xl bg-white border border-slate-200/90 p-6 shadow-card">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                    <h2 class="text-base font-bold text-slate-900">🏦 Handover Certificates & Retention Money Release Ledger</h2>
                </div>
                <div class="mt-4 space-y-4">
                    @forelse ($certificates as $cert)
                        <div class="rounded-xl bg-slate-50 border border-slate-200 p-4 space-y-3 hover:border-slate-300 transition">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-sm font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded border border-emerald-200">
                                        {{ $cert->certificate_number }}
                                    </span>
                                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded bg-slate-200 text-slate-800 border border-slate-300">
                                        {{ $cert->certificate_type }}
                                    </span>
                                    <span class="text-xs text-slate-500 font-mono">
                                        Handover: {{ $cert->handover_date->toDateString() }} · DLP Expiry: {{ $cert->dlp_expiry_date->toDateString() }}
                                    </span>
                                </div>
                                <x-ced.status-badge :status="$cert->status" />
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs bg-white rounded-xl p-3.5 border border-slate-200 shadow-2xs">
                                <div>
                                    <span class="text-slate-500 block text-[11px] font-medium">Final Contract Sum</span>
                                    <span class="font-bold text-slate-900 tabular-nums">฿{{ number_format($cert->final_contract_sum, 2) }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-500 block text-[11px] font-medium">Approved VOs</span>
                                    <span class="font-bold text-sky-700 tabular-nums">฿{{ number_format($cert->total_approved_vo_amount, 2) }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-500 block text-[11px] font-medium">Total Retention Held</span>
                                    <span class="font-bold text-amber-700 tabular-nums">฿{{ number_format($cert->retention_held_total, 2) }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-500 block text-[11px] font-medium">Retention Release</span>
                                    <span class="font-bold text-emerald-700 tabular-nums">฿{{ number_format($cert->retention_release_amount, 2) }}</span>
                                </div>
                            </div>

                            @if ($cert->status !== 'RETENTION_RELEASED')
                                <form method="POST" action="{{ route('projects.handover.certificates.release-retention', [$project, $cert]) }}"
                                      class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-slate-200">
                                    @csrf
                                    <label class="inline-flex items-center gap-2 text-xs text-slate-700 font-medium">
                                        <input type="checkbox" name="mark_project_completed" value="1" checked
                                               class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                        Transition Project to COMPLETED (if all WBS tasks finished)
                                    </label>
                                    <button type="submit"
                                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white shadow-xs transition">
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
