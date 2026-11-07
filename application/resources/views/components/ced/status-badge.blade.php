@props([
    'status',
])

@php
    $normalized = strtoupper(trim((string) $status));

    $theme = match(true) {
        in_array($normalized, ['APPROVED', 'VERIFIED', 'CLOSED', 'VERIFIED_CLOSED', 'POSTED', 'DELIVERED', 'COMPLETED', 'RESOLVED'])
            => 'bg-qc-500/15 text-qc-400 border-qc-500/30',

        in_array($normalized, ['APPROVED_AS_NOTED', 'SUBMITTED', 'VERIFIED_SITE', 'VERIFIED_QS', 'PO_ISSUED', 'RECTIFIED_PENDING_INSPECTION', 'ACTIVE_APPROVED'])
            => 'bg-blueprint-500/15 text-blueprint-400 border-blueprint-500/30',

        in_array($normalized, ['PENDING_REVIEW', 'REQUESTED', 'OPEN', 'IN_PROGRESS', 'STANDBY', 'MAINTENANCE', 'MINOR'])
            => 'bg-hazard-500/15 text-hazard-400 border-hazard-500/30',

        in_array($normalized, ['REJECTED', 'HOLD_PAYMENT', 'REVOKED', 'BREAKDOWN', 'MAJOR', 'CRITICAL', 'CRITICAL_HANDOVER_BLOCKER', 'LTI'])
            => 'bg-ncr-500/15 text-ncr-400 border-ncr-500/30',

        default => 'bg-obsidian-750 text-slate-300 border-obsidian-700',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider border ' . $theme]) }}>
    {{ $status }}
</span>
