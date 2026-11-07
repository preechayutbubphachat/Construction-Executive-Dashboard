<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $project->code }} · New Material Requisition</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
<main class="mx-auto max-w-3xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
    <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200 text-xs font-bold">
        <a class="text-cyan-700 hover:underline" href="{{ route('projects.procurement.index', $project) }}">← Back to Procurement Register</a>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <form action="{{ route('projects.procurement.store', $project) }}" method="POST" class="space-y-4 rounded-xl bg-white p-6 shadow-sm border border-slate-200">
        @csrf
        <div>
            <span class="text-xs font-bold uppercase text-amber-700">Material Requisition (MR)</span>
            <h1 class="text-xl font-black">เปิดใบขอซื้อวัสดุก่อสร้างเข้าหน้างาน</h1>
            <div class="font-mono text-xs text-slate-500">Reference: {{ $nextRef }}</div>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700">Material Name & Specification <span class="text-rose-500">*</span></label>
            <input type="text" name="material_name" value="{{ old('material_name') }}" required placeholder="e.g. Deformed Bar SD50 DB25 (TIS 24-2559)" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700">Category</label>
                <select name="category" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
                    @foreach(\App\Models\MaterialRequisition::CATEGORIES as $cat)
                        <option value="{{ $cat }}" @selected(old('category') === $cat)>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700">Required Quantity <span class="text-rose-500">*</span></label>
                <input type="number" step="0.01" min="0.01" name="required_qty" value="{{ old('required_qty', '10.00') }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-xs font-mono">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700">Unit <span class="text-rose-500">*</span></label>
                <input type="text" name="unit" value="{{ old('unit', 'TON') }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-xs font-mono">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700">Estimated Unit Cost (THB)</label>
                <input type="number" step="0.01" min="0" name="unit_cost" value="{{ old('unit_cost', '0.00') }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-xs font-mono">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700">Preferred Supplier</label>
                <input type="text" name="supplier_name" value="{{ old('supplier_name') }}" placeholder="e.g. Siam Yamato Steel" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700">Required On-Site Date <span class="text-rose-500">*</span></label>
                <input type="date" name="required_on_site_date" value="{{ old('required_on_site_date', now()->addDays(5)->toDateString()) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700">Linked WBS Task (Auto-resolves task's MATERIAL constraints on full delivery)</label>
                <select name="wbs_task_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
                    <option value="">— Optional —</option>
                    @foreach($tasks as $task)
                        <option value="{{ $task->id }}" @selected(old('wbs_task_id') === $task->id)>{{ $task->wbs_code }} · {{ $task->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700">Linked Open MATERIAL Constraint</label>
                <select name="constraint_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
                    <option value="">— Optional —</option>
                    @foreach($materialConstraints as $c)
                        <option value="{{ $c->id }}" @selected(old('constraint_id') === $c->id)>{{ $c->title }} ({{ $c->severity }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('projects.procurement.index', $project) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-xs font-bold text-slate-700">Cancel</a>
            <button type="submit" class="rounded-lg bg-amber-600 px-6 py-2 text-xs font-bold text-white hover:bg-amber-700">Submit Requisition</button>
        </div>
    </form>
</main>
</body>
</html>
