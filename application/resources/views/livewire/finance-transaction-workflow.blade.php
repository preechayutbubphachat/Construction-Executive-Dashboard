<section class="space-y-6" aria-labelledby="finance-title">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a class="text-xs font-bold text-cyan-700 hover:underline" href="{{ route('projects.portfolio') }}">← Back to Portfolio</a>
                <span class="text-xs text-slate-400">·</span>
                <a class="text-xs font-bold text-cyan-700 hover:underline" href="{{ route('projects.executive', $project) }}">Executive Health</a>
            </div>
            <p class="mt-1 text-sm font-semibold uppercase tracking-wide text-slate-500">{{ $project->code }}</p>
            <h1 id="finance-title" class="text-2xl font-black">Finance workflow</h1>
            <p class="text-sm text-slate-600">{{ $project->name }} · authenticated project scope</p>
        </div>
        <div>
            @if (\Illuminate\Support\Facades\Route::has('projects.report'))
                <a href="{{ route('projects.report', $project) }}" class="rounded-lg bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-slate-800">Executive Report</a>
            @endif
        </div>
    </header>

    @if ($successMessage)
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800" role="status">{{ $successMessage }}</div>
    @endif
    @error('workflow')
        <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800" role="alert">{{ $message }}</div>
    @enderror

    <section class="grid gap-4 sm:grid-cols-3" aria-label="Posted finance totals">
        <div class="rounded-xl bg-white p-4 shadow"><p class="text-xs font-semibold uppercase text-slate-500">Posted income</p><p class="mt-1 text-xl font-black">{{ $postedTotals['income'] }}</p></div>
        <div class="rounded-xl bg-white p-4 shadow"><p class="text-xs font-semibold uppercase text-slate-500">Posted expense</p><p class="mt-1 text-xl font-black">{{ $postedTotals['expense'] }}</p></div>
        <div class="rounded-xl bg-white p-4 shadow"><p class="text-xs font-semibold uppercase text-slate-500">Posted net</p><p class="mt-1 text-xl font-black">{{ $postedTotals['net'] }}</p></div>
    </section>

    @php
        $plannedBudget = (float) ($project->budget_amount ?? 0);
        $postedExpense = (float) ($postedTotals['expense'] ?? 0);
        $utilizationRate = $plannedBudget > 0 ? min(100.0, max(0.0, ($postedExpense / $plannedBudget) * 100)) : 0.0;
    @endphp
    @if ($plannedBudget > 0)
        <section class="rounded-xl bg-white p-5 shadow space-y-2" aria-label="Budget Utilization">
            <div class="flex justify-between text-xs font-bold">
                <span class="text-slate-600">Budget Utilization (Spent: {{ number_format($postedExpense, 2) }} / Planned: {{ number_format($plannedBudget, 2) }})</span>
                <span class="{{ $utilizationRate > 90 ? 'text-red-700' : 'text-slate-900' }}">{{ number_format($utilizationRate, 1) }}%</span>
            </div>
            <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                <div
                    class="h-full rounded-full transition-all duration-300 {{ $utilizationRate > 90 ? 'bg-red-500' : ($utilizationRate > 75 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                    style="width: {{ $utilizationRate }}%"
                ></div>
            </div>
        </section>
    @endif
    <aside class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700" aria-label="Finance calculation provenance">
        <p><span class="font-semibold">Rule:</span> {{ $postedTotals['rule_version'] }}</p>
        <p><span class="font-semibold">Calculated at:</span> {{ $postedTotals['calculated_at'] }}</p>
        <p><span class="font-semibold">Source scope:</span> project {{ $postedTotals['contributing_source_record_scope']['project_id'] }}, status {{ $postedTotals['contributing_source_record_scope']['status'] }}, records {{ implode(', ', $postedTotals['contributing_source_record_scope']['included_record_ids']) ?: 'none' }}</p>
        <p><span class="font-semibold">Explanation:</span> {{ $postedTotals['explanation'] }}</p>
    </aside>

    <form wire:submit="createDraft" class="grid gap-4 rounded-xl bg-white p-6 shadow sm:grid-cols-4">
        <h2 class="sm:col-span-4 text-lg font-bold">Create draft</h2>
        <label class="text-sm font-semibold">Direction
            <select wire:model="direction" class="mt-1 block w-full rounded border-slate-300">
                <option value="">Select direction</option>
                <option value="INCOME">Income</option>
                <option value="EXPENSE">Expense</option>
            </select>
            @error('direction') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="text-sm font-semibold sm:col-span-2">Description
            <input wire:model="description" type="text" class="mt-1 block w-full rounded border-slate-300">
            @error('description') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <label class="text-sm font-semibold">Amount
            <input wire:model="amount" type="number" min="0.01" step="0.01" class="mt-1 block w-full rounded border-slate-300">
            @error('amount') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
        </label>
        <button type="submit" class="rounded bg-slate-900 px-4 py-2 font-bold text-white sm:col-span-4">Create draft</button>
    </form>

    <div class="overflow-hidden rounded-xl bg-white shadow">
        <div class="border-b border-slate-200 p-6">
            <h2 class="text-lg font-bold">Transactions</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr><th class="px-6 py-3">Transaction</th><th class="px-6 py-3">Details</th><th class="px-6 py-3">Status</th><th class="px-6 py-3">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                @forelse ($transactions as $transaction)
                    <tr wire:key="finance-{{ $transaction->id }}">
                        <td class="px-6 py-4 font-mono text-xs">{{ $transaction->id }}</td>
                        <td class="px-6 py-4">{{ $transaction->description }}<br><span class="text-slate-500">{{ $transaction->direction }} · {{ number_format((float) $transaction->amount, 2) }}</span></td>
                        <td class="px-6 py-4 font-semibold">{{ $transaction->status->value }}</td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-2">
                                @if ($transaction->status === \App\Domain\Finance\FinanceStatus::Draft && (int) $transaction->created_by === (int) auth()->id())
                                    <button wire:click="submit('{{ $transaction->id }}')" type="button" class="rounded bg-blue-700 px-3 py-1 text-xs font-bold text-white">Submit</button>
                                @endif
                                @if ($transaction->status === \App\Domain\Finance\FinanceStatus::Submitted)
                                    <button wire:click="approve('{{ $transaction->id }}')" type="button" class="rounded bg-emerald-700 px-3 py-1 text-xs font-bold text-white">Approve</button>
                                @endif
                                @if ($transaction->status === \App\Domain\Finance\FinanceStatus::Approved)
                                    <button wire:click="post('{{ $transaction->id }}')" type="button" class="rounded bg-slate-900 px-3 py-1 text-xs font-bold text-white">Post</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-6 text-slate-500">No finance transactions.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
