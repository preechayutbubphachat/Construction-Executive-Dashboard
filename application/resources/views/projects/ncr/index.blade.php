<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $project->code }} · Quality Control & NCR Register</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
<main class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">

    <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl bg-white p-4 shadow-sm border border-slate-200">
        <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
            <a class="text-cyan-700 hover:underline" href="{{ route('projects.portfolio') }}">← Portfolio</a>
            <span class="text-slate-400">·</span>
            <a class="text-cyan-700 hover:underline" href="{{ route('projects.executive', $project) }}">Executive Health</a>
            <span class="text-slate-400">·</span>
            <a class="text-emerald-700 hover:underline" href="{{ route('projects.evm', $project) }}">EVM S-Curve 📈</a>
            <span class="text-slate-400">·</span>
            <a class="text-emerald-800 hover:underline" href="{{ route('projects.ipc.index', $project) }}">💰 IPC & Payment</a>
            <span class="text-slate-400">·</span>
            <a class="text-violet-700 hover:underline" href="{{ route('projects.vo.index', $project) }}">📑 Variation Orders</a>
            <span class="text-slate-400">·</span>
            <span class="text-rose-800 font-bold underline">🛡️ QC & NCR Register</span>
        </div>
        <a href="{{ route('projects.ncr.create', $project) }}" class="rounded-lg bg-rose-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-rose-700">+ Issue New NCR (เปิดใบแจ้งงานบกพร่อง)</a>
    </div>

    @if(session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-900">{{ session('status') }}</div>
    @endif

    <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200">
        <span class="rounded bg-rose-100 px-2.5 py-0.5 text-xs font-bold text-rose-800">QA/QC & Snag Management</span>
        <h1 class="mt-1 text-2xl font-black">Non-Conformance Reports (NCR Register)</h1>
        <p class="text-xs text-slate-500">ระบบควบคุมคุณภาพ ตรวจจับข้อบกพร่องหน้างาน (CAPA Workflow) และเชื่อมโยงการระงับเบิกจ่ายเงินค่างวด (IPC Payment Hold) อัตโนมัติ</p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
            <div class="text-[11px] font-bold uppercase text-slate-400">Total NCRs</div>
            <div class="text-xl font-black">{{ $stats['total'] }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
            <div class="text-[11px] font-bold uppercase text-slate-400">Open / Rejected</div>
            <div class="text-xl font-black text-rose-600">{{ $stats['open'] }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
            <div class="text-[11px] font-bold uppercase text-slate-400">Awaiting QC Sign-off</div>
            <div class="text-xl font-black text-amber-600">{{ $stats['awaiting_verification'] }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
            <div class="text-[11px] font-bold uppercase text-slate-400">Closed (Compliant)</div>
            <div class="text-xl font-black text-emerald-700">{{ $stats['closed'] }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm border border-rose-200 bg-rose-50/40">
            <div class="text-[11px] font-bold uppercase text-rose-700">Active IPC Payment Holds</div>
            <div class="text-xl font-black text-rose-800">{{ $stats['blocking_payment'] }} <span class="text-xs font-normal">NCRs</span></div>
            <div class="text-[10px] text-rose-600">Blocks WBS claim in IPC</div>
        </div>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden">
        @if($ncrs->isEmpty())
            <div class="p-10 text-center text-sm text-slate-500">ยังไม่มีรายการแจ้งข้อบกพร่องหน้างาน (No NCRs recorded)</div>
        @else
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Ref / Title</th>
                        <th class="px-6 py-3">WBS Task / Zone</th>
                        <th class="px-6 py-3 text-center">Category / Severity</th>
                        <th class="px-6 py-3 text-center">IPC Payment Gate</th>
                        <th class="px-6 py-3 text-center">Status</th>
                        <th class="px-6 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($ncrs as $ncr)
                        @php
                            $sevBadge = match($ncr->severity) {
                                'CRITICAL' => 'bg-rose-600 text-white',
                                'MAJOR' => 'bg-amber-100 text-amber-900',
                                default => 'bg-slate-100 text-slate-700',
                            };
                            $statusBadge = match($ncr->status) {
                                'OPEN' => 'bg-rose-100 text-rose-800',
                                'RECTIFICATION_SUBMITTED' => 'bg-amber-100 text-amber-800',
                                'REJECTED_RECTIFICATION' => 'bg-rose-200 text-rose-900',
                                'CLOSED' => 'bg-emerald-100 text-emerald-800',
                                default => 'bg-slate-100 text-slate-800',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <div class="font-mono text-[11px] text-slate-500">{{ $ncr->reference_no }}</div>
                                <div class="font-bold text-slate-900">{{ $ncr->title }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($ncr->task)
                                    <div class="font-bold text-slate-800">{{ $ncr->task->wbs_code }} · {{ $ncr->task->name }}</div>
                                @else
                                    <div class="text-slate-400">General Site</div>
                                @endif
                                @if($ncr->location_zone)
                                    <div class="text-[11px] text-slate-500">📍 {{ $ncr->location_zone }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="rounded px-2 py-0.5 text-[10px] font-bold {{ $sevBadge }}">{{ $ncr->severity }}</span>
                                <div class="mt-1 text-[10px] text-slate-500">{{ $ncr->category }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($ncr->blocksPayment())
                                    <span class="rounded-full bg-rose-100 border border-rose-300 px-2.5 py-0.5 text-[10px] font-bold text-rose-800">🚫 HOLD PAYMENT</span>
                                @elseif($ncr->hold_payment && $ncr->status === 'CLOSED')
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold text-emerald-700">✓ Hold Released</span>
                                @else
                                    <span class="text-[10px] text-slate-400">Advisory</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $statusBadge }}">{{ $ncr->status }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('projects.ncr.show', [$project, $ncr]) }}" class="rounded bg-slate-100 px-3 py-1.5 font-bold text-slate-700 hover:bg-slate-200">Inspect</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</main>
</body>
</html>
