<x-layouts.app :project="$project" title="{{ $project->code }} · Issue Non-Conformance Report (NCR)">
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center gap-2 text-xs font-bold">
            <a class="text-safety-600 hover:text-safety-700 hover:underline transition" href="{{ route('projects.ncr.index', $project) }}">&larr; Back to NCR Register</a>
            <span class="text-slate-300">&bull;</span>
            <span class="text-slate-600">Issue Non-Conformance Report</span>
        </div>

        @if($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800 shadow-sm">
                @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif

        <form action="{{ route('projects.ncr.store', $project) }}" method="POST" class="space-y-5 rounded-2xl bg-white p-6 shadow-card border border-slate-200/80">
            @csrf
            <div class="border-b border-slate-100 pb-4">
                <span class="text-xs font-mono font-bold uppercase tracking-wider text-rose-600 bg-rose-100 border border-rose-200 px-2.5 py-0.5 rounded-full inline-block">
                    QA/QC Field Inspection
                </span>
                <h1 class="text-xl font-black text-slate-900 mt-1">เปิดใบแจ้งงานไม่เป็นไปตามข้อกำหนด (Issue NCR)</h1>
                <div class="font-mono text-xs text-slate-500 mt-0.5">Reference: {{ $nextRef }}</div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700">Title / Defect Summary <span class="text-rose-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. Honeycombing on Pier Column P4 concrete pour" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-900 focus:border-safety-500 focus:ring-safety-500">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700">Linked WBS Task (Optional — required to block IPC claim)</label>
                    <select name="wbs_task_id" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-900 focus:border-safety-500 focus:ring-safety-500">
                        <option value="">— General Project / Unassigned —</option>
                        @foreach($tasks as $task)
                            <option value="{{ $task->id }}" @selected(old('wbs_task_id') === $task->id)>{{ $task->wbs_code }} · {{ $task->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Location / Zone / 3D Grid Reference</label>
                    <input type="text" name="location_zone" value="{{ old('location_zone') }}" placeholder="e.g. Level 2 · Grid C-5" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-900 focus:border-safety-500 focus:ring-safety-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Category</label>
                    <select name="category" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-900 focus:border-safety-500 focus:ring-safety-500">
                        @foreach(\App\Models\NonConformanceReport::CATEGORIES as $cat)
                            <option value="{{ $cat }}" @selected(old('category', 'WORKMANSHIP') === $cat)>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Severity</label>
                    <select name="severity" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-900 focus:border-safety-500 focus:ring-safety-500">
                        @foreach(\App\Models\NonConformanceReport::SEVERITIES as $sev)
                            <option value="{{ $sev }}" @selected(old('severity', 'MAJOR') === $sev)>{{ $sev }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700">Detailed Non-Conformance Description & Spec Reference</label>
                <textarea name="description" rows="3" placeholder="Describe the defect, ASTM / BS standard non-compliance, test lab results..." class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-900 focus:border-safety-500 focus:ring-safety-500">{{ old('description') }}</textarea>
            </div>

            <label class="flex items-center gap-3 rounded-xl border border-rose-200 bg-rose-50/60 p-4 text-xs font-bold text-rose-900">
                <input type="checkbox" name="hold_payment" value="1" checked class="rounded border-rose-300 text-rose-600 focus:ring-rose-500">
                <span>🚫 Hold IPC Payment for this WBS Task until NCR is verified & closed (ระงับการเบิกเงินค่างวดของ WBS Task นี้จนกว่าจะแก้ไขผ่าน)</span>
            </label>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('projects.ncr.index', $project) }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">Cancel</a>
                <button type="submit" class="rounded-xl bg-rose-600 hover:bg-rose-700 px-6 py-2 text-xs font-bold text-white shadow-sm transition">Issue NCR</button>
            </div>
        </form>
    </div>
</x-layouts.app>
