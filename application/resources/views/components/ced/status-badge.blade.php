@props([
    'status',
])

@php
    $normalized = strtoupper(trim((string) $status));

    $theme = match(true) {
        in_array($normalized, ['APPROVED', 'VERIFIED', 'CLOSED', 'VERIFIED_CLOSED', 'POSTED', 'DELIVERED', 'COMPLETED', 'RESOLVED'])
            => 'bg-emerald-50 text-emerald-700 border-emerald-200',

        in_array($normalized, ['APPROVED_AS_NOTED', 'SUBMITTED', 'VERIFIED_SITE', 'VERIFIED_QS', 'PO_ISSUED', 'RECTIFIED_PENDING_INSPECTION', 'ACTIVE_APPROVED'])
            => 'bg-sky-50 text-sky-700 border-sky-200',

        in_array($normalized, ['PENDING_REVIEW', 'REQUESTED', 'OPEN', 'IN_PROGRESS', 'STANDBY', 'MAINTENANCE', 'MINOR'])
            => 'bg-amber-50 text-amber-700 border-amber-200',

        in_array($normalized, ['REJECTED', 'HOLD_PAYMENT', 'REVOKED', 'BREAKDOWN', 'MAJOR', 'CRITICAL', 'CRITICAL_HANDOVER_BLOCKER', 'LTI'])
            => 'bg-rose-50 text-rose-700 border-rose-200',

        default => 'bg-slate-100 text-slate-700 border-slate-200',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider border ' . $theme]) }}>
    {{ $status }}
</span>
