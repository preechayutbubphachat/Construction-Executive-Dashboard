<x-layouts.app :project="$project">
    <div class="mx-auto max-w-4xl space-y-6">
        {{-- Breadcrumb & Back button --}}
        <div class="flex items-center justify-between">
            <a class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-safety-orange transition" href="{{ route('projects.procurement.index', $project) }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Procurement Register
            </a>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-safety-orange/10 text-safety-orange border border-safety-orange/20">
                Material Requisition (MR)
            </span>
        </div>

        @if($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50/80 p-4 text-xs font-semibold text-rose-800 shadow-card">
                <div class="flex items-center gap-2 mb-1 font-bold text-rose-900">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Please correct the following errors:
                </div>
                @foreach($errors->all() as $error)<div>• {{ $error }}</div>@endforeach
            </div>
        @endif

        <form action="{{ route('projects.procurement.store', $project) }}" method="POST" class="rounded-2xl bg-white p-6 sm:p-8 shadow-card border border-slate-200/80 space-y-6">
            @csrf
            <div class="border-b border-slate-100 pb-5">
                <span class="text-xs font-bold uppercase tracking-wider text-safety-orange">Material Requisition (MR)</span>
                <h1 class="text-2xl font-black text-slate-900 mt-1">เปิดใบขอซื้อวัสดุก่อสร้างเข้าหน้างาน</h1>
                <div class="font-mono text-xs text-slate-500 mt-1">Reference: {{ $nextRef }}</div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Material Name & Specification <span class="text-rose-500">*</span></label>
                <input type="text" name="material_name" value="{{ old('material_name') }}" required placeholder="e.g. Deformed Bar SD50 DB25 (TIS 24-2559)" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-xs text-slate-900 focus:bg-white focus:border-safety-orange focus:ring-1 focus:ring-safety-orange transition">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Category</label>
                    <select name="category" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-xs text-slate-900 focus:bg-white focus:border-safety-orange focus:ring-1 focus:ring-safety-orange transition font-bold">
                        @foreach(\App\Models\MaterialRequisition::CATEGORIES as $cat)
                            <option value="{{ $cat }}" @selected(old('category') === $cat)>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Required Quantity <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="required_qty" value="{{ old('required_qty', '10.00') }}" required class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-xs font-mono text-slate-900 focus:bg-white focus:border-safety-orange focus:ring-1 focus:ring-safety-orange transition">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Unit <span class="text-rose-500">*</span></label>
                    <input type="text" name="unit" value="{{ old('unit', 'TON') }}" required class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-xs font-mono text-slate-900 focus:bg-white focus:border-safety-orange focus:ring-1 focus:ring-safety-orange transition">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Estimated Unit Cost (THB)</label>
                    <input type="number" step="0.01" min="0" name="unit_cost" value="{{ old('unit_cost', '0.00') }}" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-xs font-mono text-slate-900 focus:bg-white focus:border-safety-orange focus:ring-1 focus:ring-safety-orange transition">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Preferred Supplier</label>
                    <input type="text" name="supplier_name" value="{{ old('supplier_name') }}" placeholder="e.g. Siam Yamato Steel" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-xs text-slate-900 focus:bg-white focus:border-safety-orange focus:ring-1 focus:ring-safety-orange transition">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Required On-Site Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="required_on_site_date" value="{{ old('required_on_site_date', now()->addDays(5)->toDateString()) }}" required class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-xs text-slate-900 focus:bg-white focus:border-safety-orange focus:ring-1 focus:ring-safety-orange transition">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Linked WBS Task (Auto-resolves task's MATERIAL constraints on full delivery)</label>
                    <select name="wbs_task_id" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-xs text-slate-900 focus:bg-white focus:border-safety-orange focus:ring-1 focus:ring-safety-orange transition">
                        <option value="">— Optional —</option>
                        @foreach($tasks as $task)
                            <option value="{{ $task->id }}" @selected(old('wbs_task_id') === $task->id)>{{ $task->wbs_code }} · {{ $task->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Linked Open MATERIAL Constraint</label>
                    <select name="constraint_id" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-xs text-slate-900 focus:bg-white focus:border-safety-orange focus:ring-1 focus:ring-safety-orange transition">
                        <option value="">— Optional —</option>
                        @foreach($materialConstraints as $c)
                            <option value="{{ $c->id }}" @selected(old('constraint_id') === $c->id)>{{ $c->title }} ({{ $c->severity }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('projects.procurement.index', $project) }}" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">Cancel</a>
                <button type="submit" class="rounded-xl bg-safety-orange px-6 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-orange-600 transition">Submit Requisition</button>
            </div>
        </form>
    </div>
</x-layouts.app>
