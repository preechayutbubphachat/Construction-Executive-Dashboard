<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $project->code }} · Variation Orders</title>
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
            <a class="text-rose-700 hover:underline" href="{{ route('projects.ncr.index', $project) }}">🛡️ QC & NCR</a>
            <span class="text-slate-400">·</span>
            <span class="text-violet-800 underline">📑 Variation Orders</span>
        </div>
        <a href="{{ route('projects.vo.create', $project) }}" class="rounded-lg bg-violet-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-violet-700">+ New Variation Request (ขอเปลี่ยนแปลงงาน)</a>
    </div>

    @if(session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-900">{{ session('status') }}</div>
    @endif

    <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200">
        <span class="rounded bg-violet-100 px-2.5 py-0.5 text-xs font-bold text-violet-800">Change Management</span>
        <h1 class="mt-1 text-2xl font-black">Variation Orders (VO Register)</h1>
        <p class="text-xs text-slate-500">ทะเบียนงานเพิ่ม-งานลด ประเมินผลกระทบด้านต้นทุนและเวลา (EOT) พร้อมปรับ BAC และกำหนดเสร็จอัตโนมัติเมื่ออนุมัติ</p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
            <div class="text-[11px] font-bold uppercase text-slate-400">Total VOs</div>
            <div class="text-xl font-black">{{ $stats['total'] }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
            <div class="text-[11px] font-bold uppercase text-slate-400">Pending</div>
            <div class="text-xl font-black text-amber-600">{{ $stats['pending'] }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
            <div class="text-[11px] font-bold uppercase text-slate-400">Approved Cost Impact</div>
            <div class="text-xl font-black {{ $stats['approved_cost'] >= 0 ? 'text-rose-700' : 'text-emerald-700' }}">{{ $stats['approved_cost'] >= 0 ? '+' : '-' }}฿{{ number_format(abs($stats['approved_cost']), 2) }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
            <div class="text-[11px] font-bold uppercase text-slate-400">Approved EOT</div>
            <div class="text-xl font-black">{{ $stats['approved_days'] >= 0 ? '+' : '' }}{{ $stats['approved_days'] }} <span class="text-xs font-normal">days</span></div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
            <div class="text-[11px] font-bold uppercase text-slate-400">Current Contract (BAC)</div>
            <div class="text-xl font-black text-cyan-800">฿{{ number_format($project->budget_amount, 2) }}</div>
            <div class="text-[10px] text-slate-400">Finish {{ $project->baseline_finish_date->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden">
        @if($vos->isEmpty())
            <div class="p-10 text-center text-sm text-slate-500">ยังไม่มีรายการขอเปลี่ยนแปลงงาน (No variation orders yet)</div>
        @else
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Ref / Title</th>
                        <th class="px-6 py-3">Type / Reason</th>
                        <th class="px-6 py-3 text-right">Cost Impact</th>
                        <th class="px-6 py-3 text-right">Time (EOT)</th>
                        <th class="px-6 py-3 text-center">Status</th>
                        <th class="px-6 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($vos as $vo)
                        @php
                            $badge = match($vo->status) {
                                'SUBMITTED' => 'bg-amber-100 text-amber-800',
                                'VERIFIED_SITE' => 'bg-blue-100 text-blue-800',
                                'VERIFIED_QS' => 'bg-purple-100 text-purple-800',
                                'APPROVED' => 'bg-emerald-100 text-emerald-800',
                                'REJECTED' => 'bg-rose-100 text-rose-800',
                                default => 'bg-slate-100 text-slate-800',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <div class="font-mono text-[11px] text-slate-500">{{ $vo->reference_no }}</div>
                                <div class="font-bold text-slate-900">{{ $vo->title }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold">{{ $vo->type }}</div>
                                <div class="text-[11px] text-slate-500">{{ str_replace('_', ' ', $vo->reason) }}</div>
                            </td>
                            <td class="px-6 py-4 text-right font-mono font-bold {{ $vo->cost_impact >= 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                                {{ $vo->cost_impact >= 0 ? '+' : '-' }}฿{{ number_format(abs((float) $vo->cost_impact), 2) }}
                            </td>
                            <td class="px-6 py-4 text-right font-mono">{{ $vo->time_impact_days >= 0 ? '+' : '' }}{{ $vo->time_impact_days }} d</td>
                            <td class="px-6 py-4 text-center"><span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $badge }}">{{ $vo->status }}</span></td>
                            <td class="px-6 py-4 text-center"><a href="{{ route('projects.vo.show', [$project, $vo]) }}" class="rounded bg-slate-100 px-3 py-1.5 font-bold text-slate-700 hover:bg-slate-200">View</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</main>
</body>
</html>
