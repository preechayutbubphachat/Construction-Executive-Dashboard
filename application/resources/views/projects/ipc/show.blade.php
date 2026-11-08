<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $ipc->reference_no }} · Interim Payment Certificate (IPC)</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: #ffffff !important;
                color: #000000 !important;
                font-size: 10pt !important;
            }
            main {
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .certificate-container {
                box-shadow: none !important;
                border: none !important;
            }
            .print-border {
                border: 1px solid #94a3b8 !important;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased print:bg-white print:text-black">
<main class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6 lg:px-8 print:max-w-none print:p-0">

    <!-- Top Action Bar (Hidden in Print) -->
    <div class="no-print flex flex-wrap items-center justify-between gap-4 rounded-xl bg-white p-4 shadow-sm border border-slate-200">
        <div class="flex items-center gap-2 text-xs font-bold">
            <a class="text-cyan-700 hover:underline" href="{{ route('projects.ipc.index', $project) }}">← Back to IPC Register</a>
            <span class="text-slate-400">·</span>
            <span class="text-slate-600">Certificate: <strong class="text-slate-900 font-mono">{{ $ipc->reference_no }}</strong></span>
            <span class="text-slate-400">·</span>
            @php
                $statusColors = match($ipc->status) {
                    'SUBMITTED' => 'bg-amber-100 text-amber-800',
                    'VERIFIED_SITE' => 'bg-blue-100 text-blue-800',
                    'VERIFIED_QS' => 'bg-purple-100 text-purple-800',
                    'APPROVED' => 'bg-teal-100 text-teal-800',
                    'POSTED' => 'bg-emerald-100 text-emerald-800',
                    'REJECTED' => 'bg-rose-100 text-rose-800',
                    default => 'bg-slate-100 text-slate-800',
                };
            @endphp
            <span class="rounded px-2.5 py-0.5 text-[11px] font-bold {{ $statusColors }}">
                {{ $ipc->status }}
            </span>
        </div>

        <div class="flex items-center gap-2">
            <button
                type="button"
                onclick="window.print()"
                class="inline-flex items-center gap-1.5 rounded-lg bg-slate-800 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-slate-900 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print / Export PDF
            </button>
        </div>
    </div>

    <!-- Status Messages & Errors (Hidden in Print) -->
    @if(session('status'))
        <div class="no-print rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-900 shadow-sm">
            {{ session('status') }}
        </div>
    @endif
    @if($errors->any())
        <div class="no-print rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800 shadow-sm">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <!-- Workflow Progression Banner (Hidden in Print) -->
    <div class="no-print rounded-xl bg-white p-5 shadow-sm border border-slate-200">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">3-Level Approval & Certification Process</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            <!-- Step 1: Site Engineer -->
            <div class="rounded-lg border p-4 {{ in_array($ipc->status, ['VERIFIED_SITE', 'VERIFIED_QS', 'APPROVED', 'POSTED']) ? 'border-emerald-200 bg-emerald-50/50' : ($ipc->status === 'SUBMITTED' ? 'border-amber-300 bg-amber-50/40 ring-2 ring-amber-300' : 'border-slate-200 bg-slate-50') }}">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase text-slate-500">Level 1</span>
                    @if($ipc->site_verified_at)
                        <span class="text-[10px] font-bold text-emerald-700">✓ Verified</span>
                    @elseif($ipc->status === 'SUBMITTED')
                        <span class="text-[10px] font-bold text-amber-700 animate-pulse">Action Required</span>
                    @else
                        <span class="text-[10px] text-slate-400">Pending</span>
                    @endif
                </div>
                <div class="mt-1 font-bold text-xs text-slate-800">Site Inspection & Progress</div>
                <div class="text-[11px] text-slate-500">
                    {{ $ipc->siteEngineer?->name ?? 'Site Engineer' }}
                </div>
                @if($ipc->site_verified_at)
                    <div class="mt-2 text-[10px] font-mono text-slate-400">
                        {{ \Carbon\Carbon::parse($ipc->site_verified_at)->format('d/m/Y H:i') }}
                    </div>
                @elseif($ipc->status === 'SUBMITTED')
                    <form action="{{ route('projects.ipc.verify-site', [$project, $ipc]) }}" method="POST" class="mt-3">
                        @csrf
                        <input type="text" name="notes" placeholder="Site verification notes..." class="w-full text-[11px] border rounded px-2 py-1 mb-2">
                        <button type="submit" class="w-full rounded bg-amber-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-amber-700 transition">
                            Sign Level 1: Site Verify
                        </button>
                    </form>
                @endif
            </div>

            <!-- Step 2: Quantity Surveyor (QS) -->
            <div class="rounded-lg border p-4 {{ in_array($ipc->status, ['VERIFIED_QS', 'APPROVED', 'POSTED']) ? 'border-emerald-200 bg-emerald-50/50' : ($ipc->status === 'VERIFIED_SITE' ? 'border-purple-300 bg-purple-50/40 ring-2 ring-purple-300' : 'border-slate-200 bg-slate-50') }}">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase text-slate-500">Level 2</span>
                    @if($ipc->qs_verified_at)
                        <span class="text-[10px] font-bold text-emerald-700">✓ Certified</span>
                    @elseif($ipc->status === 'VERIFIED_SITE')
                        <span class="text-[10px] font-bold text-purple-700 animate-pulse">Action Required</span>
                    @else
                        <span class="text-[10px] text-slate-400">Waiting Level 1</span>
                    @endif
                </div>
                <div class="mt-1 font-bold text-xs text-slate-800">QS Measurement & Valuation</div>
                <div class="text-[11px] text-slate-500">
                    {{ $ipc->quantitySurveyor?->name ?? 'Quantity Surveyor' }}
                </div>
                @if($ipc->qs_verified_at)
                    <div class="mt-2 text-[10px] font-mono text-slate-400">
                        {{ \Carbon\Carbon::parse($ipc->qs_verified_at)->format('d/m/Y H:i') }}
                    </div>
                @elseif($ipc->status === 'VERIFIED_SITE')
                    <form action="{{ route('projects.ipc.verify-qs', [$project, $ipc]) }}" method="POST" class="mt-3">
                        @csrf
                        <input type="text" name="notes" placeholder="QS measurement notes..." class="w-full text-[11px] border rounded px-2 py-1 mb-2">
                        <button type="submit" class="w-full rounded bg-purple-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-purple-700 transition">
                            Sign Level 2: Certify Valuation
                        </button>
                    </form>
                @endif
            </div>

            <!-- Step 3: Project Director -->
            <div class="rounded-lg border p-4 {{ in_array($ipc->status, ['APPROVED', 'POSTED']) ? 'border-emerald-200 bg-emerald-50/50' : ($ipc->status === 'VERIFIED_QS' ? 'border-teal-300 bg-teal-50/40 ring-2 ring-teal-300' : 'border-slate-200 bg-slate-50') }}">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase text-slate-500">Level 3</span>
                    @if($ipc->director_approved_at)
                        <span class="text-[10px] font-bold text-emerald-700">✓ Approved & Posted</span>
                    @elseif($ipc->status === 'VERIFIED_QS')
                        <span class="text-[10px] font-bold text-teal-700 animate-pulse">Action Required</span>
                    @else
                        <span class="text-[10px] text-slate-400">Waiting Level 2</span>
                    @endif
                </div>
                <div class="mt-1 font-bold text-xs text-slate-800">Executive Final Approval</div>
                <div class="text-[11px] text-slate-500">
                    {{ $ipc->projectDirector?->name ?? 'Project Director' }}
                </div>
                @if($ipc->director_approved_at)
                    <div class="mt-2 text-[10px] font-mono text-slate-400">
                        {{ \Carbon\Carbon::parse($ipc->director_approved_at)->format('d/m/Y H:i') }}
                        @if($ipc->finance_transaction_id)
                            <div class="text-emerald-700 font-bold">Ledger Tx #{{ $ipc->finance_transaction_id }}</div>
                        @endif
                    </div>
                @elseif($ipc->status === 'VERIFIED_QS')
                    @php
                        $userMembership = $project->members()->where('user_id', auth()->id())->first();
                        $userRole = $userMembership?->pivot?->role ?? null;
                        $isExecutive = $userRole === 'EXECUTIVE';
                    @endphp

                    @if($isExecutive)
                        <form action="{{ route('projects.ipc.approve-director', [$project, $ipc]) }}" method="POST" class="mt-3">
                            @csrf
                            <input type="text" name="notes" placeholder="Director approval remarks..." class="w-full text-[11px] border rounded px-2 py-1 mb-2">
                            <button type="submit" class="w-full rounded bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-700 transition">
                                Sign Level 3: Approve & Post Ledger
                            </button>
                        </form>
                    @else
                        <div class="mt-3 rounded-lg bg-amber-50 border border-amber-200 p-3 text-xs text-amber-900 space-y-1.5">
                            <p class="font-bold flex items-center gap-1.5 text-xs text-amber-900">
                                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span>ต้องใช้สิทธิ์ Executive Director</span>
                            </p>
                            <p class="text-[11px] text-amber-800">
                                บัญชีปัจจุบันคือ <strong>{{ auth()->user()->name }}</strong> (สิทธิ์: {{ $userRole ?? 'General' }}) การสั่งจ่ายเงินและบันทึกบัญชีต้องลงนามโดยผู้อำนวยการโครงการ
                            </p>
                            @php
                                $directorUser = $project->members()->wherePivot('role', 'EXECUTIVE')->first() ?? \App\Models\User::where('email', 'director@ced.local')->first();
                            @endphp
                            @if($directorUser)
                                <a href="{{ route('quick-login', $directorUser) }}" class="mt-2 inline-flex items-center justify-center gap-1.5 w-full rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs py-2 shadow-xs transition">
                                    <span>🔄 สลับเป็นบทบาท {{ $directorUser->name }} เพื่อลงนาม</span>
                                </a>
                            @endif
                        </div>
                    @endif
                @endif
            </div>

        </div>
    </div>

    <!-- Official Printable Certificate Document -->
    <div class="certificate-container rounded-xl bg-white p-8 sm:p-12 shadow-sm border border-slate-200 space-y-6">

        <!-- Certificate Header / Letterhead -->
        <div class="border-b-2 border-slate-900 pb-6">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                <div>
                    <div class="text-[11px] font-black uppercase tracking-wider text-slate-500">CONSTRUCTION COMMERCIAL MANAGEMENT</div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">INTERIM PAYMENT CERTIFICATE</h1>
                    <div class="text-sm font-semibold text-slate-700 mt-1">ใบรับรองการจ่ายเงินค่างวดงานก่อสร้าง</div>
                </div>
                <div class="text-right">
                    <div class="inline-block border border-slate-300 rounded px-3 py-1 bg-slate-50">
                        <span class="text-[10px] font-bold text-slate-400 block uppercase">Certificate No.</span>
                        <span class="text-lg font-black text-slate-900 font-mono">No. {{ str_pad($ipc->certificate_number, 2, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="mt-1 text-xs font-mono font-bold text-slate-600">{{ $ipc->reference_no }}</div>
                </div>
            </div>
        </div>

        <!-- Project & Contractor Metadata Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 rounded-lg bg-slate-50 p-4 border border-slate-200 text-xs">
            <div>
                <span class="font-bold text-slate-500 uppercase text-[10px]">Project Name & Code</span>
                <div class="font-bold text-slate-900">{{ $project->name }}</div>
                <div class="font-mono text-slate-500">{{ $project->code }}</div>
            </div>
            <div>
                <span class="font-bold text-slate-500 uppercase text-[10px]">Contractor / Vendor</span>
                <div class="font-bold text-slate-900">{{ $ipc->contractor_name }}</div>
                @if($ipc->contractor_invoice_no)
                    <div class="text-slate-500">Invoice: {{ $ipc->contractor_invoice_no }}</div>
                @endif
            </div>
            <div>
                <span class="font-bold text-slate-500 uppercase text-[10px]">Valuation Period</span>
                <div class="font-bold text-slate-900">
                    {{ \Carbon\Carbon::parse($ipc->period_start)->format('d M Y') }} – {{ \Carbon\Carbon::parse($ipc->period_end)->format('d M Y') }}
                </div>
                <div class="text-slate-500">Billing Cycle</div>
            </div>
            <div>
                <span class="font-bold text-slate-500 uppercase text-[10px]">Valuation Date</span>
                <div class="font-bold text-slate-900">
                    {{ \Carbon\Carbon::parse($ipc->valuation_date)->format('d M Y') }}
                </div>
                <div class="text-slate-500">Created: {{ $ipc->created_at->format('d/m/Y') }}</div>
            </div>
        </div>

        <!-- Valuation Statement Summary (The Core IPC Financial Statement) -->
        <div class="space-y-2">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-600">Financial Valuation & Deduction Statement (การประเมินราคาและรายการหัก)</h2>
            <div class="border border-slate-200 rounded-lg overflow-hidden">
                <table class="w-full text-xs text-slate-700">
                    <tbody class="divide-y divide-slate-200">
                        <tr class="bg-slate-50/50">
                            <td class="px-4 py-2.5 font-bold text-slate-800">1. Cumulative Gross Work Certified to Date (มูลค่างานสะสมถึงงวดนี้)</td>
                            <td class="px-4 py-2.5 text-right font-mono font-bold text-slate-900 w-48">
                                ฿{{ number_format($ipc->cumulative_gross_amount, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2.5 text-slate-600">2. Less: Previous Cumulative Gross Certified (หัก มูลค่างานสะสมงวดก่อนหน้า)</td>
                            <td class="px-4 py-2.5 text-right font-mono text-slate-600">
                                ฿{{ number_format($ipc->previous_gross_amount, 2) }}
                            </td>
                        </tr>
                        <tr class="bg-emerald-50/40">
                            <td class="px-4 py-2.5 font-bold text-emerald-950">3. Gross Work Executed This Period (มูลค่างานงวดปัจจุบัน A)</td>
                            <td class="px-4 py-2.5 text-right font-mono font-black text-emerald-900 text-sm">
                                ฿{{ number_format($ipc->current_work_amount, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2.5 text-slate-600 pl-8">4. Less: Retention Withheld @ {{ number_format($ipc->retention_rate_percent, 1) }}% (หัก เงินประกันผลงาน)</td>
                            <td class="px-4 py-2.5 text-right font-mono text-amber-700">
                                -฿{{ number_format($ipc->retention_deduction, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2.5 text-slate-600 pl-8">5. Less: Advance Payment Recovery (หัก คืนเงินจ่ายล่วงหน้า)</td>
                            <td class="px-4 py-2.5 text-right font-mono text-indigo-700">
                                -฿{{ number_format($ipc->advance_recovery_deduction, 2) }}
                            </td>
                        </tr>
                        @if($ipc->other_deductions > 0)
                        <tr>
                            <td class="px-4 py-2.5 text-slate-600 pl-8">6. Less: Other Deductions / Penalties (หัก รายการหักอื่นๆ)</td>
                            <td class="px-4 py-2.5 text-right font-mono text-rose-700">
                                -฿{{ number_format($ipc->other_deductions, 2) }}
                            </td>
                        </tr>
                        @endif
                        <tr class="bg-slate-50 font-bold">
                            <td class="px-4 py-2.5 text-slate-800">7. Net Amount Before VAT (มูลค่าสุทธิก่อนคิดภาษีมูลค่าเพิ่ม)</td>
                            <td class="px-4 py-2.5 text-right font-mono text-slate-900">
                                ฿{{ number_format($ipc->net_amount_before_vat, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2.5 text-slate-600 pl-8">8. Add: Value Added Tax (VAT @ {{ number_format($ipc->vat_rate_percent, 1) }}%)</td>
                            <td class="px-4 py-2.5 text-right font-mono text-cyan-800">
                                +฿{{ number_format($ipc->vat_amount, 2) }}
                            </td>
                        </tr>
                        <tr class="bg-emerald-600 text-white font-black text-sm">
                            <td class="px-4 py-3">9. TOTAL NET CERTIFIED PAYABLE (จำนวนเงินที่สั่งจ่ายสุทธิงวดนี้)</td>
                            <td class="px-4 py-3 text-right font-mono text-base tracking-wide">
                                ฿{{ number_format($ipc->net_payable_amount, 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Breakdown by Work Package / Task -->
        @if($ipc->items->isNotEmpty())
        <div class="space-y-2">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-600">Breakdown of Work Items Claimed (รายละเอียดงานที่ขอเบิก)</h2>
            <div class="border border-slate-200 rounded-lg overflow-hidden">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-2">Item / WBS Task</th>
                            <th class="px-4 py-2 text-right">Planned (BAC)</th>
                            <th class="px-4 py-2 text-center">Claim %</th>
                            <th class="px-4 py-2 text-right">Claim Amount</th>
                            <th class="px-4 py-2">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($ipc->items as $item)
                        <tr>
                            <td class="px-4 py-2">
                                <span class="font-bold text-slate-800">{{ $item->task?->wbs_code ?? '-' }}</span>
                                <span class="text-slate-600">· {{ $item->task?->name ?? ($item->description ?? 'Work Item') }}</span>
                            </td>
                            <td class="px-4 py-2 text-right font-mono text-slate-600">
                                ฿{{ number_format((float) ($item->task?->planned_cost ?? 0), 2) }}
                            </td>
                            <td class="px-4 py-2 text-center font-bold text-slate-800">
                                {{ number_format((float) ($item->claimed_progress_percent ?? 0), 0) }}%
                            </td>
                            <td class="px-4 py-2 text-right font-mono font-bold text-slate-900">
                                ฿{{ number_format((float) $item->claimed_amount, 2) }}
                            </td>
                            <td class="px-4 py-2 text-slate-500 text-[11px]">
                                {{ $item->remarks ?? '-' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- 3-Level Formal Signature Box Grid -->
        <div class="pt-6 border-t border-slate-200">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-4 text-center">Formal Multi-Tier Certification & Sign-off</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs">

                <!-- Site Engineer Box -->
                <div class="border rounded-lg p-4 bg-slate-50/50 flex flex-col justify-between h-48 print-border">
                    <div>
                        <div class="text-[10px] font-bold uppercase text-slate-400">Level 1: Field Verification</div>
                        <div class="font-bold text-slate-800 mt-1">Site Engineer / Inspector</div>
                        <p class="text-[10px] text-slate-500 mt-0.5">ตรวจสอบความถูกต้องของเนื้องานจริงและภาพถ่ายหน้างาน</p>
                    </div>
                    <div>
                        @if($ipc->site_verified_at)
                            <div class="border-b border-slate-300 pb-1 mb-1 text-center font-serif text-slate-800 italic">
                                / {{ $ipc->siteEngineer?->name }} /
                            </div>
                            <div class="text-[10px] text-center text-slate-500">
                                Verified: {{ \Carbon\Carbon::parse($ipc->site_verified_at)->format('d/m/Y H:i') }}
                            </div>
                            @if($ipc->site_notes)
                                <div class="text-[10px] text-slate-500 mt-1 italic text-center">"{{ $ipc->site_notes }}"</div>
                            @endif
                        @else
                            <div class="border-b border-dashed border-slate-300 pb-4 mb-1 text-center text-slate-400 italic">
                                [ Pending Field Inspection ]
                            </div>
                            <div class="text-[10px] text-center text-slate-400">Date: _______________</div>
                        @endif
                    </div>
                </div>

                <!-- Quantity Surveyor Box -->
                <div class="border rounded-lg p-4 bg-slate-50/50 flex flex-col justify-between h-48 print-border">
                    <div>
                        <div class="text-[10px] font-bold uppercase text-slate-400">Level 2: Cost & Measurement</div>
                        <div class="font-bold text-slate-800 mt-1">Quantity Surveyor (QS)</div>
                        <p class="text-[10px] text-slate-500 mt-0.5">ตรวจสอบปริมาณงาน สัญญา การคิดเงิน และยอดหักภาษี</p>
                    </div>
                    <div>
                        @if($ipc->qs_verified_at)
                            <div class="border-b border-slate-300 pb-1 mb-1 text-center font-serif text-slate-800 italic">
                                / {{ $ipc->quantitySurveyor?->name }} /
                            </div>
                            <div class="text-[10px] text-center text-slate-500">
                                Certified: {{ \Carbon\Carbon::parse($ipc->qs_verified_at)->format('d/m/Y H:i') }}
                            </div>
                            @if($ipc->qs_notes)
                                <div class="text-[10px] text-slate-500 mt-1 italic text-center">"{{ $ipc->qs_notes }}"</div>
                            @endif
                        @else
                            <div class="border-b border-dashed border-slate-300 pb-4 mb-1 text-center text-slate-400 italic">
                                [ Pending QS Certification ]
                            </div>
                            <div class="text-[10px] text-center text-slate-400">Date: _______________</div>
                        @endif
                    </div>
                </div>

                <!-- Project Director Box -->
                <div class="border rounded-lg p-4 bg-slate-50/50 flex flex-col justify-between h-48 print-border">
                    <div>
                        <div class="text-[10px] font-bold uppercase text-slate-400">Level 3: Executive Approval</div>
                        <div class="font-bold text-slate-800 mt-1">Project Director / Client Rep</div>
                        <p class="text-[10px] text-slate-500 mt-0.5">อนุมัติการสั่งจ่ายเงินและบันทึกกระทบยอดบัญชี</p>
                    </div>
                    <div>
                        @if($ipc->director_approved_at)
                            <div class="border-b border-slate-300 pb-1 mb-1 text-center font-serif text-slate-800 italic">
                                / {{ $ipc->projectDirector?->name }} /
                            </div>
                            <div class="text-[10px] text-center text-slate-500">
                                Approved: {{ \Carbon\Carbon::parse($ipc->director_approved_at)->format('d/m/Y H:i') }}
                            </div>
                            @if($ipc->director_notes)
                                <div class="text-[10px] text-slate-500 mt-1 italic text-center">"{{ $ipc->director_notes }}"</div>
                            @endif
                        @else
                            <div class="border-b border-dashed border-slate-300 pb-4 mb-1 text-center text-slate-400 italic">
                                [ Pending Executive Sign-off ]
                            </div>
                            <div class="text-[10px] text-center text-slate-400">Date: _______________</div>
                        @endif
                    </div>
                </div>

            </div>
        </div>

        <!-- Footnote / System Attestation -->
        <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-400">
            <div>Document generated by Construction Executive Dashboard (CED) · Commercial Module</div>
            <div class="font-mono">Audit Token: {{ hash('sha256', $ipc->id . ':' . $ipc->reference_no . ':' . $ipc->net_payable_amount) }}</div>
        </div>

    </div>

</main>
</body>
</html>
