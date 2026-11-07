<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Handover, Retention Release & Final Account — {{ $project->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen antialiased">
    @php
        $fa = $dashboard['final_account'];
        $readiness = $dashboard['readiness'];
        $punchItems = $dashboard['punch_items'];
        $certificates = $dashboard['certificates'];
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-800 pb-6">
            <div>
                <div class="flex items-center gap-3">
                    <span class="px-2.5 py-1 text-xs font-semibold uppercase tracking-wider rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        Phase 8 · Project Handover & Final Account
                    </span>
                    <span class="text-xs font-mono text-slate-400">{{ $project->code }}</span>
                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded bg-slate-800 text-slate-200 border border-slate-700">
                        Status: {{ $project->status }}
                    </span>
                </div>
                <h1 class="mt-2 text-2xl sm:text-3xl font-bold tracking-tight text-white">
                    🏁 {{ $project->name }} — Handover, Retention Release & Closeout
                </h1>
                <p class="mt-1 text-sm text-slate-400">
                    Pre-Handover Punch List (8A), Taking-Over & Defects Liability Certificates, Retention Money Release & Final Account Statement (8B)
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('projects.ipc.index', $project) }}"
                   class="px-3.5 py-2 text-xs font-medium rounded-lg bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 transition">
                    💰 IPC Ledger
                </a>
                <a href="{{ route('projects.ncr.index', $project) }}"
                   class="px-3.5 py-2 text-xs font-medium rounded-lg bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 transition">
                    🛡️ QC / NCR
                </a>
                <a href="{{ route('projects.portfolio') }}"
                   class="px-3.5 py-2 text-xs font-medium rounded-lg bg-slate-800 hover:bg-slate-700 text-white transition">
                    ← Portfolio
                </a>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl bg-emerald-950/60 border border-emerald-500/40 p-4 text-sm text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl bg-rose-950/60 border border-rose-500/40 p-4 text-sm text-rose-200 space-y-1">
                @foreach ($errors->all() as $error)
                    <div>• {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <!-- Final Account Statement Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="rounded-2xl bg-slate-900/80 border border-slate-800 p-5">
                <div class="text-xs font-medium uppercase tracking-wider text-slate-400">Original Contract</div>
                <div class="mt-2 text-xl font-bold text-white">฿{{ number_format($fa['original_contract_sum'], 2) }}</div>
                <div class="mt-1 text-xs text-slate-400">Approved VOs: +฿{{ number_format($fa['approved_vo_total'], 2) }}</div>
            </div>

            <div class="rounded-2xl bg-slate-900/80 border border-slate-800 p-5">
                <div class="text-xs font-medium uppercase tracking-wider text-slate-400">Final Contract Sum</div>
                <div class="mt-2 text-xl font-bold text-cyan-400">฿{{ number_format($fa['revised_contract_sum'], 2) }}</div>
                <div class="mt-1 text-xs text-slate-400">Certified Gross: ฿{{ number_format($fa['total_certified_gross'], 2) }}</div>
            </div>

            <div class="rounded-2xl bg-slate-900/80 border border-slate-800 p-5">
                <div class="text-xs font-medium uppercase tracking-wider text-slate-400">Retention Held Total</div>
                <div class="mt-2 text-xl font-bold text-amber-400">฿{{ number_format($fa['total_retention_held'], 2) }}</div>
                <div class="mt-1 text-xs text-slate-400">Released: ฿{{ number_format($fa['total_retention_released'], 2) }}</div>
            </div>

            <div class="rounded-2xl bg-slate-900/80 border border-slate-800 p-5">
                <div class="text-xs font-medium uppercase tracking-wider text-slate-400">Retention Available</div>
                <div class="mt-2 text-xl font-bold text-emerald-400">฿{{ number_format($fa['available_retention_balance'], 2) }}</div>
                <div class="mt-1 text-xs text-slate-400">Ready for TOC / DLC Release</div>
            </div>

            <div class="rounded-2xl bg-slate-900/80 border border-slate-800 p-5">
                <div class="text-xs font-medium uppercase tracking-wider text-slate-400">Handover Gate</div>
                <div class="mt-2 text-lg font-bold {{ $readiness['is_ready_for_toc'] ? 'text-emerald-400' : 'text-rose-400' }}">
                    {{ $readiness['is_ready_for_toc'] ? 'READY FOR TOC' : 'GATE BLOCKED' }}
                </div>
                <div class="mt-1 text-xs text-slate-400">
                    Open NCRs: {{ $readiness['open_ncr_count'] }} · Open Punch: {{ $readiness['open_punch_count'] }}
                </div>
            </div>
        </div>

        <!-- 8A: Pre-Handover Punch List / Snagging -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="rounded-2xl bg-slate-900/90 border border-slate-800 p-6">
                <h2 class="text-lg font-semibold text-white">🔍 Log Pre-Handover Punch Item (8A)</h2>
                <p class="mt-1 text-xs text-slate-400">All punch items must be verified closed before issuing a Taking-Over Certificate.</p>

                <form method="POST" action="{{ route('projects.handover.punch.store', $project) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-300">Location / Zone</label>
                        <input type="text" name="location_zone" required placeholder="e.g. Level 12 Roof Mechanical Room"
                               class="mt-1 w-full rounded-lg bg-slate-950 border border-slate-800 px-3 py-2 text-sm text-white">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-300">Discipline</label>
                            <select name="discipline" class="mt-1 w-full rounded-lg bg-slate-950 border border-slate-800 px-3 py-2 text-sm text-white">
                                @foreach ($disciplines as $disc)
                                    <option value="{{ $disc }}">{{ $disc }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300">Severity</label>
                            <select name="severity" class="mt-1 w-full rounded-lg bg-slate-950 border border-slate-800 px-3 py-2 text-sm text-white">
                                @foreach ($severities as $sev)
                                    <option value="{{ $sev }}">{{ $sev }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300">Linked WBS Task (Optional)</label>
                        <select name="wbs_task_id" class="mt-1 w-full rounded-lg bg-slate-950 border border-slate-800 px-3 py-2 text-sm text-white">
                            <option value="">— General Project Handover —</option>
                            @foreach ($wbsTasks as $task)
                                <option value="{{ $task->id }}">{{ $task->wbs_code }} — {{ $task->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300">Snag / Defect Description</label>
                        <textarea name="description" rows="3" required placeholder="Describe snag requiring rectification before handover..."
                                  class="mt-1 w-full rounded-lg bg-slate-950 border border-slate-800 px-3 py-2 text-sm text-white"></textarea>
                    </div>

                    <button type="submit"
                            class="w-full rounded-lg bg-amber-600 hover:bg-amber-500 px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-white transition">
                        + Log Punch List Item
                    </button>
                </form>
            </div>

            <div class="lg:col-span-2 rounded-2xl bg-slate-900/90 border border-slate-800 p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-white">📋 Pre-Handover Punch List Register</h2>
                    <span class="text-xs text-slate-400">
                        Verified Closed: {{ $readiness['verified_punch_count'] }} / {{ $readiness['total_punch_count'] }}
                    </span>
                </div>

                <div class="mt-4 space-y-3">
                    @forelse ($punchItems as $item)
                        <div class="rounded-xl bg-slate-950/80 border border-slate-800 p-4 space-y-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-bold text-amber-400">{{ $item->punch_code }}</span>
                                    <span class="text-sm font-semibold text-white">{{ $item->location_zone }}</span>
                                    <span class="px-2 py-0.5 text-[11px] rounded bg-slate-800 text-slate-300">{{ $item->discipline }}</span>
                                    <span class="px-2 py-0.5 text-[11px] rounded bg-rose-500/20 text-rose-300">{{ $item->severity }}</span>
                                </div>
                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $item->status === 'VERIFIED_CLOSED' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-amber-500/20 text-amber-300' }}">
                                    {{ $item->status }}
                                </span>
                            </div>

                            <p class="text-xs text-slate-300">{{ $item->description }}</p>

                            @if ($item->rectification_notes)
                                <div class="text-xs text-emerald-300 bg-emerald-950/30 border border-emerald-500/20 rounded-lg p-2.5">
                                    <strong>Rectification / Inspection:</strong> {{ $item->rectification_notes }}
                                </div>
                            @endif

                            @if ($item->status !== 'VERIFIED_CLOSED')
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-slate-800">
                                    <form method="POST" action="{{ route('projects.handover.punch.rectify', [$project, $item]) }}" class="flex gap-2">
                                        @csrf
                                        <input type="text" name="rectification_notes" required placeholder="Contractor rectification notes..."
                                               class="flex-1 rounded-lg bg-slate-900 border border-slate-700 px-2.5 py-1.5 text-xs text-white">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-xs font-semibold text-white">
                                            Rectify
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('projects.handover.punch.verify', [$project, $item]) }}" class="flex gap-2">
                                        @csrf
                                        <input type="text" name="verification_notes" placeholder="PM/Inspector sign-off notes..."
                                               class="flex-1 rounded-lg bg-slate-900 border border-slate-700 px-2.5 py-1.5 text-xs text-white">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-xs font-semibold text-white">
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
            <div class="rounded-2xl bg-slate-900/90 border border-slate-800 p-6">
                <h2 class="text-lg font-semibold text-white">📜 Issue Handover Certificate (8B)</h2>
                <p class="mt-1 text-xs text-slate-400">
                    Issue Taking-Over Certificate (TOC) or Defects Liability Certificate (DLC) and specify retention release amount.
                </p>

                <form method="POST" action="{{ route('projects.handover.certificates.issue', $project) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-300">Certificate Type</label>
                        <select name="certificate_type" class="mt-1 w-full rounded-lg bg-slate-950 border border-slate-800 px-3 py-2 text-sm text-white">
                            @foreach ($certificateTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-300">Handover Date</label>
                            <input type="date" name="handover_date" value="{{ now()->toDateString() }}" required
                                   class="mt-1 w-full rounded-lg bg-slate-950 border border-slate-800 px-3 py-2 text-sm text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300">DLP Period (Months)</label>
                            <input type="number" name="dlp_months" value="12" min="0" max="120" required
                                   class="mt-1 w-full rounded-lg bg-slate-950 border border-slate-800 px-3 py-2 text-sm text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300">
                            Retention Release Amount (Max: ฿{{ number_format($fa['available_retention_balance'], 2) }})
                        </label>
                        <input type="number" step="0.01" name="retention_release_amount" value="{{ $fa['available_retention_balance'] }}" required
                               class="mt-1 w-full rounded-lg bg-slate-950 border border-slate-800 px-3 py-2 text-sm text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300">Scope & Final Account Remarks</label>
                        <textarea name="scope_remarks" rows="2" placeholder="Practical completion and retention release remarks..."
                                  class="mt-1 w-full rounded-lg bg-slate-950 border border-slate-800 px-3 py-2 text-sm text-white"></textarea>
                    </div>

                    <button type="submit"
                            class="w-full rounded-lg bg-emerald-600 hover:bg-emerald-500 px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-white transition">
                        Issue Handover Certificate
                    </button>
                </form>
            </div>

            <div class="lg:col-span-2 rounded-2xl bg-slate-900/90 border border-slate-800 p-6">
                <h2 class="text-lg font-semibold text-white">🏦 Handover Certificates & Retention Money Release Ledger</h2>
                <div class="mt-4 space-y-4">
                    @forelse ($certificates as $cert)
                        <div class="rounded-xl bg-slate-950/80 border border-slate-800 p-4 space-y-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-sm font-bold text-emerald-400">{{ $cert->certificate_number }}</span>
                                    <span class="px-2.5 py-0.5 text-xs rounded bg-slate-800 text-slate-200">{{ $cert->certificate_type }}</span>
                                    <span class="text-xs text-slate-400">Handover: {{ $cert->handover_date->toDateString() }} · DLP Expiry: {{ $cert->dlp_expiry_date->toDateString() }}</span>
                                </div>
                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $cert->status === 'RETENTION_RELEASED' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-amber-500/20 text-amber-300' }}">
                                    {{ $cert->status }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs bg-slate-900/70 rounded-lg p-3">
                                <div>
                                    <span class="text-slate-400 block">Final Contract Sum</span>
                                    <span class="font-semibold text-white">฿{{ number_format($cert->final_contract_sum, 2) }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block">Approved VOs</span>
                                    <span class="font-semibold text-cyan-300">฿{{ number_format($cert->total_approved_vo_amount, 2) }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block">Total Retention Held</span>
                                    <span class="font-semibold text-amber-300">฿{{ number_format($cert->retention_held_total, 2) }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block">Retention Release</span>
                                    <span class="font-semibold text-emerald-400">฿{{ number_format($cert->retention_release_amount, 2) }}</span>
                                </div>
                            </div>

                            @if ($cert->status !== 'RETENTION_RELEASED')
                                <form method="POST" action="{{ route('projects.handover.certificates.release-retention', [$project, $cert]) }}"
                                      class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-slate-800">
                                    @csrf
                                    <label class="inline-flex items-center gap-2 text-xs text-slate-300">
                                        <input type="checkbox" name="mark_project_completed" value="1" checked
                                               class="rounded bg-slate-900 border-slate-700 text-emerald-500">
                                        Transition Project to COMPLETED (if all WBS tasks finished)
                                    </label>
                                    <button type="submit"
                                            class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-xs font-semibold text-white">
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
</body>
</html>
