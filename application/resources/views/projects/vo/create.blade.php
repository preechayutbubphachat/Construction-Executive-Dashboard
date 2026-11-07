<x-layouts.app :project="$project" title="{{ $project->code }} · New Variation Request">
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center gap-2 text-xs font-bold">
            <a class="text-safety-600 hover:text-safety-700 hover:underline transition" href="{{ route('projects.vo.index', $project) }}">&larr; Back to VO Register</a>
            <span class="text-slate-300">&bull;</span>
            <span class="text-slate-600">New Variation Request</span>
        </div>

        @if($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800 shadow-sm">
                @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif

        <form action="{{ route('projects.vo.store', $project) }}" method="POST" class="space-y-5 rounded-2xl bg-white p-6 shadow-card border border-slate-200/80">
            @csrf
            <div class="border-b border-slate-100 pb-4">
                <span class="text-xs font-mono font-bold uppercase tracking-wider text-safety-600 bg-orange-100 border border-orange-200/70 px-2.5 py-0.5 rounded-full inline-block">
                    Request for Variation
                </span>
                <h1 class="text-xl font-black text-slate-900 mt-1">ขอเปลี่ยนแปลงงาน / งานเพิ่ม-งานลด</h1>
                <div class="font-mono text-xs text-slate-500 mt-0.5">Reference: {{ $nextRef }}</div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700">Title <span class="text-rose-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. Extra retaining wall along foundation grid C" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-900 focus:border-safety-500 focus:ring-safety-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700">Description</label>
                <textarea name="description" rows="3" placeholder="Provide technical justification, drawing revisions, or site inspection notes..." class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-900 focus:border-safety-500 focus:ring-safety-500">{{ old('description') }}</textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700">Type</label>
                    <select name="type" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-900 focus:border-safety-500 focus:ring-safety-500">
                        <option value="ADDITION" @selected(old('type') === 'ADDITION')>ADDITION (งานเพิ่ม)</option>
                        <option value="OMISSION" @selected(old('type') === 'OMISSION')>OMISSION (งานลด)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Reason</label>
                    <select name="reason" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-900 focus:border-safety-500 focus:ring-safety-500">
                        @foreach(\App\Models\VariationOrder::REASONS as $reason)
                            <option value="{{ $reason }}" @selected(old('reason') === $reason)>{{ str_replace('_', ' ', $reason) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Cost Impact (THB, magnitude) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0" name="cost_amount" value="{{ old('cost_amount', '0.00') }}" required class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-mono text-slate-900 focus:border-safety-500 focus:ring-safety-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700">Time Impact / EOT (days, magnitude)</label>
                    <input type="number" step="1" min="0" name="time_impact_days" value="{{ old('time_impact_days', 0) }}" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-mono text-slate-900 focus:border-safety-500 focus:ring-safety-500">
                </div>
            </div>
            <p class="text-[11px] text-slate-500">เครื่องหมายบวก/ลบถูกกำหนดจาก Type โดยอัตโนมัติ (OMISSION = ลดงบ/ลดเวลา)</p>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('projects.vo.index', $project) }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">Cancel</a>
                <button type="submit" class="rounded-xl bg-safety-600 hover:bg-safety-700 px-6 py-2 text-xs font-bold text-white shadow-sm transition">Submit Request</button>
            </div>
        </form>
    </div>
</x-layouts.app>
