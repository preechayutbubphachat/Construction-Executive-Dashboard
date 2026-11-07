<section class="space-y-6" aria-labelledby="finance-title">
    <header class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200/80 pb-5">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold">
                <a class="text-safety-600 hover:text-safety-700 hover:underline transition" href="{{ route('projects.portfolio') }}">&larr; Back to Portfolio</a>
                <span class="text-slate-300">&bull;</span>
                <a class="text-slate-600 hover:text-slate-900 hover:underline transition" href="{{ route('projects.executive', $project) }}">Executive Health</a>
                <span class="text-slate-300">&bull;</span>
                <a class="text-emerald-700 hover:underline transition" href="{{ route('projects.evm', $project) }}">EVM S-Curve 📈</a>
                <span class="text-slate-300">&bull;</span>
                <a class="text-slate-600 hover:text-slate-900 hover:underline transition" href="{{ route('projects.ipc.index', $project) }}">IPC Register 💰</a>
            </div>
            <div class="flex items-center gap-2.5 mt-2">
                <span class="px-2.5 py-0.5 rounded text-xs font-mono font-bold bg-orange-100 text-safety-600 border border-orange-200/70">
                    {{ $project->code }}
                </span>
                <h1 id="finance-title" class="text-2xl font-black text-slate-900 tracking-tight">Finance workflow</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1">{{ $project->name }} &middot; authenticated project scope &middot; CED Ledger V1</p>
        </div>
        <div>
            @if (\Illuminate\Support\Facades\Route::has('projects.report'))
                <a href="{{ route('projects.report', $project) }}" class="rounded-xl border border-slate-200/80 bg-white px-4 py-2 text-xs font-bold text-slate-800 shadow-card hover:bg-slate-50 transition">
                    Executive Report
                </a>
            @endif
        </div>
    </header>

    @if ($successMessage)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800 shadow-sm" role="status">{{ $successMessage }}</div>
    @endif
    @error('workflow')
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800 shadow-sm" role="alert">{{ $message }}</div>
    @enderror

    <section class="grid gap-4 sm:grid-cols-3" aria-label="Posted finance totals">
        <div class="rounded-2xl bg-white p-5 border border-slate-200/80 shadow-card">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Posted income</p>
            <p class="mt-2 text-2xl font-black text-emerald-700">฿{{ number_format((float) ($postedTotals['income'] ?? 0), 2) }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Confirmed cash inflow from client</p>
        </div>
        <div class="rounded-2xl bg-white p-5 border border-slate-200/80 shadow-card">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Posted expense</p>
            <p class="mt-2 text-2xl font-black text-rose-700">฿{{ number_format((float) ($postedTotals['expense'] ?? 0), 2) }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Actual posted subcontractor / material costs</p>
        </div>
        <div class="rounded-2xl bg-white p-5 border border-slate-200/80 shadow-card">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Posted net</p>
            <p class="mt-2 text-2xl font-black text-slate-900">฿{{ number_format((float) ($postedTotals['net'] ?? 0), 2) }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Realized project operating margin</p>
        </div>
    </section>

    @php
        $plannedBudget = (float) ($project->budget_amount ?? 0);
        $postedExpense = (float) ($postedTotals['expense'] ?? 0);
        $utilizationRate = $plannedBudget > 0 ? min(100.0, max(0.0, ($postedExpense / $plannedBudget) * 100)) : 0.0;
    @endphp
    @if ($plannedBudget > 0)
        <section class="rounded-2xl bg-white p-5 border border-slate-200/80 shadow-card space-y-3" aria-label="Budget Utilization">
            <div class="flex justify-between items-center text-xs font-bold">
                <span class="text-slate-700">Budget Utilization (Spent: ฿{{ number_format($postedExpense, 2) }} / Planned: ฿{{ number_format($plannedBudget, 2) }})</span>
                <span class="{{ $utilizationRate > 90 ? 'text-rose-700' : 'text-slate-900' }} font-mono font-bold">{{ number_format($utilizationRate, 1) }}%</span>
            </div>
            <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                <div
                    class="h-full rounded-full transition-all duration-300 {{ $utilizationRate > 90 ? 'bg-rose-500' : ($utilizationRate > 75 ? 'bg-amber-500' : 'bg-safety-500') }}"
                    style="width: {{ $utilizationRate }}%"
                ></div>
            </div>
        </section>
    @endif

    <aside class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-5 text-xs text-slate-600 space-y-1.5 shadow-sm" aria-label="Finance calculation provenance">
        <p><span class="font-bold text-slate-800">Rule:</span> {{ $postedTotals['rule_version'] }}</p>
        <p><span class="font-bold text-slate-800">Calculated at:</span> {{ $postedTotals['calculated_at'] }}</p>
        <p><span class="font-bold text-slate-800">Source scope:</span> project {{ $postedTotals['contributing_source_record_scope']['project_id'] }}, status {{ $postedTotals['contributing_source_record_scope']['status'] }}, records {{ implode(', ', $postedTotals['contributing_source_record_scope']['included_record_ids']) ?: 'none' }}</p>
        <p><span class="font-bold text-slate-800">Explanation:</span> {{ $postedTotals['explanation'] }}</p>
    </aside>

    <form wire:submit="createDraft" class="grid gap-4 rounded-2xl bg-white p-6 border border-slate-200/80 shadow-card sm:grid-cols-4">
        <div class="sm:col-span-4 border-b border-slate-100 pb-3">
            <span class="text-xs font-mono font-bold uppercase tracking-wider text-safety-600 bg-orange-100 border border-orange-200/70 px-2.5 py-0.5 rounded-full inline-block">
                Maker-Checker Entry
            </span>
            <h2 class="text-lg font-black text-slate-900 mt-1">Create draft</h2>
            <p class="text-xs text-slate-500">Record a new incoming or outgoing transaction draft awaiting approval</p>
        </div>

        <label class="text-xs font-bold text-slate-700">Direction
            <select wire:model="direction" class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-900 focus:border-safety-500 focus:ring-safety-500">
                <option value="">Select direction</option>
                <option value="INCOME">Income (รายรับ)</option>
                <option value="EXPENSE">Expense (รายจ่าย)</option>
            </select>
            @error('direction') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
        </label>
        <label class="text-xs font-bold text-slate-700 sm:col-span-2">Description
            <input wire:model="description" type="text" placeholder="e.g. Concrete supplier payment batch #4" class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-900 focus:border-safety-500 focus:ring-safety-500">
            @error('description') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
        </label>
        <label class="text-xs font-bold text-slate-700">Amount (฿)
            <input wire:model="amount" type="number" min="0.01" step="0.01" placeholder="0.00" class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-mono text-slate-900 focus:border-safety-500 focus:ring-safety-500">
            @error('amount') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
        </label>
        <button type="submit" class="rounded-xl bg-safety-600 hover:bg-safety-700 px-5 py-2.5 text-xs font-bold text-white shadow-sm transition sm:col-span-4 justify-self-start">Create draft</button>
    </form>

    <div class="overflow-hidden rounded-2xl bg-white border border-slate-200/80 shadow-card">
        <div class="border-b border-slate-200/80 bg-slate-50/50 p-5 flex items-center justify-between">
            <div>
                <h2 class="text-base font-black text-slate-900">Transactions</h2>
                <p class="text-xs text-slate-500">Maker-checker financial ledger records with dual authorization</p>
            </div>
            <span class="text-xs font-mono text-slate-400">{{ count($transactions) }} records</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-xs">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 font-bold border-b border-slate-200/80">
                    <tr>
                        <th class="px-6 py-3.5">Transaction</th>
                        <th class="px-6 py-3.5">Details</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse ($transactions as $transaction)
                    <tr wire:key="finance-{{ $transaction->id }}" class="hover:bg-slate-50/80 transition">
                        <td class="px-6 py-4 font-mono text-xs font-bold text-slate-800">{{ $transaction->id }}</td>
                        <td class="px-6 py-4">
                            <span class="font-bold text-slate-900">{{ $transaction->description }}</span>
                            <br>
                            <span class="text-slate-500 font-mono text-[11px]">{{ $transaction->direction }} &middot; ฿{{ number_format((float) $transaction->amount, 2) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusBadge = match($transaction->status->value) {
                                    'DRAFT' => 'bg-slate-100 text-slate-700 border-slate-200',
                                    'SUBMITTED' => 'bg-amber-100 text-amber-800 border-amber-200',
                                    'APPROVED' => 'bg-teal-100 text-teal-800 border-teal-200',
                                    'POSTED' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200',
                                };
                            @endphp
                            <span class="inline-block rounded-full border px-2.5 py-0.5 text-[11px] font-bold {{ $statusBadge }}">
                                {{ $transaction->status->value }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-2">
                                @if ($transaction->status === \App\Domain\Finance\FinanceStatus::Draft && (int) $transaction->created_by === (int) auth()->id())
                                    <button wire:click="submit('{{ $transaction->id }}')" type="button" class="rounded-lg bg-sky-600 hover:bg-sky-700 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition">Submit</button>
                                @endif
                                @if ($transaction->status === \App\Domain\Finance\FinanceStatus::Submitted)
                                    <button wire:click="approve('{{ $transaction->id }}')" type="button" class="rounded-lg bg-emerald-600 hover:bg-emerald-700 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition">Approve</button>
                                @endif
                                @if ($transaction->status === \App\Domain\Finance\FinanceStatus::Approved)
                                    <button wire:click="post('{{ $transaction->id }}')" type="button" class="rounded-lg bg-slate-900 hover:bg-slate-800 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition">Post</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-8 text-center text-slate-500">No finance transactions recorded yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
