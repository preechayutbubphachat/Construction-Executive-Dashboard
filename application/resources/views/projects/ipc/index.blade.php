<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $project->code }} · Interim Payment Certificates (IPC Register)</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
<main class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">

    <!-- Top Navigation Bar -->
    <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl bg-white p-4 shadow-sm border border-slate-200">
        <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
            <a class="text-cyan-700 hover:underline" href="{{ route('projects.portfolio') }}">← Back to Portfolio</a>
            <span class="text-slate-400">·</span>
            <a class="text-cyan-700 hover:underline" href="{{ route('projects.executive', $project) }}">Executive Health</a>
            <span class="text-slate-400">·</span>
            <a class="text-cyan-700 hover:underline" href="{{ route('projects.finance', $project) }}">Finance</a>
            <span class="text-slate-400">·</span>
            <a class="text-emerald-700 hover:underline" href="{{ route('projects.evm', $project) }}">EVM S-Curve 📈</a>
            <span class="text-slate-400">·</span>
            <a class="text-amber-700 hover:underline" href="{{ route('projects.site', $project) }}">Field Capture 📱</a>
            <span class="text-slate-400">·</span>
            <a class="text-blue-700 hover:underline" href="{{ route('projects.reports.daily', $project) }}">📅 Daily Report</a>
            <span class="text-slate-400">·</span>
            <a class="text-indigo-700 hover:underline" href="{{ route('projects.reports.weekly', $project) }}">📊 Weekly Report</a>
            <span class="text-slate-400">·</span>
            <a class="text-violet-700 hover:underline" href="{{ route('projects.vo.index', $project) }}">📑 Variation Orders</a>
            <span class="text-slate-400">·</span>
            <span class="text-emerald-800 font-bold underline">💰 IPC Commercial Register</span>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('projects.ipc.create', $project) }}" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-emerald-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                + New Payment Application (ยื่นเบิกค่างวด)
            </a>
        </div>
    </div>

    <!-- Status Messages -->
    @if(session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-900 shadow-sm">
            {{ session('status') }}
        </div>
    @endif

    <!-- Header Section -->
    <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="rounded bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-800">Commercial & QS</span>
                    <span class="text-xs text-slate-500 font-mono">{{ $project->code }}</span>
                </div>
                <h1 class="mt-1 text-2xl font-black text-slate-900">Interim Payment Certificates (IPC Register)</h1>
                <p class="text-xs text-slate-500">ระบบบริหารจัดการการตรวจรับ ตรวจสอบปริมาณงาน (QS) และเบิกจ่ายเงินค่างวดงานก่อสร้าง 3 ระดับ</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-400">Active Stage:</span>
                <span class="rounded-md bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ $project->status ?? 'ACTIVE' }}</span>
            </div>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total IPCs</span>
            <div class="mt-1 text-xl font-black text-slate-900">{{ number_format($stats['total_ipcs']) }} <span class="text-xs font-normal text-slate-500">งวด</span></div>
            <span class="text-[10px] text-slate-400">Applications filed</span>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Gross Certified</span>
            <div class="mt-1 text-xl font-black text-emerald-700">฿{{ number_format($stats['total_gross_certified'], 2) }}</div>
            <span class="text-[10px] text-slate-400">Approved gross work</span>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Retention Held</span>
            <div class="mt-1 text-xl font-black text-amber-700">฿{{ number_format($stats['total_retention_held'], 2) }}</div>
            <span class="text-[10px] text-slate-400">เงินประกันผลงานสะสม</span>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Advance Recovered</span>
            <div class="mt-1 text-xl font-black text-indigo-700">฿{{ number_format($stats['total_advance_recovered'], 2) }}</div>
            <span class="text-[10px] text-slate-400">หักเงินล่วงหน้าสะสม</span>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Net Disbursed</span>
            <div class="mt-1 text-xl font-black text-cyan-800">฿{{ number_format($stats['total_net_disbursed'], 2) }}</div>
            <span class="text-[10px] text-slate-400">จ่ายจริงกระทบยอดบัญชี</span>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">In Progress / Pending</span>
            <div class="mt-1 text-xl font-black {{ $stats['pending_approval'] > 0 ? 'text-amber-600' : 'text-slate-400' }}">
                {{ number_format($stats['pending_approval']) }} <span class="text-xs font-normal text-slate-500">งวด</span>
            </div>
            <span class="text-[10px] text-slate-400">Awaiting approval</span>
        </div>
    </div>

    <!-- IPC Applications Register Table -->
    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900">Interim Payment Certificates List (ทะเบียนใบรับรองค่างวดงาน)</h2>
            <span class="text-xs text-slate-500 font-mono">{{ $ipcs->count() }} Records</span>
        </div>

        @if($ipcs->isEmpty())
            <div class="p-12 text-center">
                <div class="mx-auto w-12 h-12 text-slate-300">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <h3 class="mt-2 text-sm font-bold text-slate-800">ยังไม่มีประวัติการเบิกจ่ายเงินค่างวดงาน (No IPCs recorded)</h3>
                <p class="mt-1 text-xs text-slate-500">เริ่มต้นยื่นคำขอเบิกเงินค่างวดงานฉบับแรกโดยคลิกปุ่มด้านล่าง</p>
                <div class="mt-4">
                    <a href="{{ route('projects.ipc.create', $project) }}" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-emerald-700">
                        + ยื่นเบิกค่างวดงวดที่ 1 (Create First IPC)
                    </a>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 font-bold border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3">Cert No. / Ref</th>
                            <th class="px-6 py-3">Contractor / Period</th>
                            <th class="px-6 py-3 text-right">Current Work</th>
                            <th class="px-6 py-3 text-right">Retention ({{ 5 }}%)</th>
                            <th class="px-6 py-3 text-right">Advance Recovery</th>
                            <th class="px-6 py-3 text-right">Net Payable (Inc VAT)</th>
                            <th class="px-6 py-3 text-center">Workflow Status</th>
                            <th class="px-6 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($ipcs as $ipc)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">#{{ str_pad($ipc->certificate_number, 2, '0', STR_PAD_LEFT) }}</div>
                                    <div class="font-mono text-[11px] text-slate-500">{{ $ipc->reference_no }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-800">{{ $ipc->contractor_name }}</div>
                                    <div class="text-[11px] text-slate-500">
                                        {{ \Carbon\Carbon::parse($ipc->period_start)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($ipc->period_end)->format('d/m/Y') }}
                                    </div>
                                    @if($ipc->contractor_invoice_no)
                                        <div class="text-[10px] text-slate-400">Inv: {{ $ipc->contractor_invoice_no }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-mono font-bold text-slate-900">
                                    ฿{{ number_format($ipc->current_work_amount, 2) }}
                                </td>
                                <td class="px-6 py-4 text-right font-mono text-amber-700">
                                    -฿{{ number_format($ipc->retention_deduction, 2) }}
                                </td>
                                <td class="px-6 py-4 text-right font-mono text-indigo-700">
                                    -฿{{ number_format($ipc->advance_recovery_deduction, 2) }}
                                </td>
                                <td class="px-6 py-4 text-right font-mono font-bold text-emerald-700 text-sm">
                                    ฿{{ number_format($ipc->net_payable_amount, 2) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $badgeClasses = match($ipc->status) {
                                            'SUBMITTED' => 'bg-amber-100 text-amber-800 border-amber-200',
                                            'VERIFIED_SITE' => 'bg-blue-100 text-blue-800 border-blue-200',
                                            'VERIFIED_QS' => 'bg-purple-100 text-purple-800 border-purple-200',
                                            'APPROVED' => 'bg-teal-100 text-teal-800 border-teal-200',
                                            'POSTED' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                            'REJECTED' => 'bg-rose-100 text-rose-800 border-rose-200',
                                            default => 'bg-slate-100 text-slate-800 border-slate-200',
                                        };
                                        $label = match($ipc->status) {
                                            'SUBMITTED' => 'รอตรวจหน้างาน (L1)',
                                            'VERIFIED_SITE' => 'รอตรวจปริมาณงาน (L2 QS)',
                                            'VERIFIED_QS' => 'รออนุมัติจ่าย (L3 Director)',
                                            'APPROVED' => 'อนุมัติแล้ว',
                                            'POSTED' => 'บันทึกบัญชีแล้ว (POSTED)',
                                            'REJECTED' => 'ถูกปฏิเสธ (REJECTED)',
                                            default => $ipc->status,
                                        };
                                    @endphp
                                    <span class="inline-block rounded-full border px-2.5 py-1 text-[11px] font-bold {{ $badgeClasses }}">
                                        {{ $label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('projects.ipc.show', [$project, $ipc]) }}" class="inline-flex items-center gap-1 rounded bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-200 transition">
                                        <span>View Sheet</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</main>
</body>
</html>
