<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $project->code }} · Issue Non-Conformance Report (NCR)</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
<main class="mx-auto max-w-3xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
    <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200 text-xs font-bold">
        <a class="text-cyan-700 hover:underline" href="{{ route('projects.ncr.index', $project) }}">← Back to NCR Register</a>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <form action="{{ route('projects.ncr.store', $project) }}" method="POST" class="space-y-4 rounded-xl bg-white p-6 shadow-sm border border-slate-200">
        @csrf
        <div>
            <span class="text-xs font-bold uppercase text-rose-700">QA/QC Field Inspection</span>
            <h1 class="text-xl font-black">เปิดใบแจ้งงานไม่เป็นไปตามข้อกำหนด (Issue NCR)</h1>
            <div class="font-mono text-xs text-slate-500">Reference: {{ $nextRef }}</div>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700">Title / Defect Summary <span class="text-rose-500">*</span></label>
            <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. Honeycombing on Pier Column P4 concrete pour" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700">Linked WBS Task (Optional — required to block IPC claim)</label>
                <select name="wbs_task_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
                    <option value="">— General Project / Unassigned —</option>
                    @foreach($tasks as $task)
                        <option value="{{ $task->id }}" @selected(old('wbs_task_id') === $task->id)>{{ $task->wbs_code }} · {{ $task->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700">Location / Zone / 3D Grid Reference</label>
                <input type="text" name="location_zone" value="{{ old('location_zone') }}" placeholder="e.g. Level 2 · Grid C-5" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700">Category</label>
                <select name="category" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
                    @foreach(\App\Models\NonConformanceReport::CATEGORIES as $cat)
                        <option value="{{ $cat }}" @selected(old('category', 'WORKMANSHIP') === $cat)>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700">Severity</label>
                <select name="severity" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
                    @foreach(\App\Models\NonConformanceReport::SEVERITIES as $sev)
                        <option value="{{ $sev }}" @selected(old('severity', 'MAJOR') === $sev)>{{ $sev }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700">Detailed Non-Conformance Description & Spec Reference</label>
            <textarea name="description" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">{{ old('description') }}</textarea>
        </div>

        <label class="flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50/60 p-3 text-xs font-bold text-rose-900">
            <input type="checkbox" name="hold_payment" value="1" checked class="rounded border-rose-300 text-rose-600">
            <span>🚫 Hold IPC Payment for this WBS Task until NCR is verified & closed (ระงับการเบิกเงินค่างวดของ WBS Task นี้จนกว่าจะแก้ไขผ่าน)</span>
        </label>

        <div class="flex justify-end gap-3">
            <a href="{{ route('projects.ncr.index', $project) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-xs font-bold text-slate-700">Cancel</a>
            <button type="submit" class="rounded-lg bg-rose-600 px-6 py-2 text-xs font-bold text-white hover:bg-rose-700">Issue NCR</button>
        </div>
    </form>
</main>
</body>
</html>
