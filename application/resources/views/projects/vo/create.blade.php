<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $project->code }} · New Variation Request</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
<main class="mx-auto max-w-3xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
    <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200 text-xs font-bold">
        <a class="text-cyan-700 hover:underline" href="{{ route('projects.vo.index', $project) }}">← Back to VO Register</a>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-800">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <form action="{{ route('projects.vo.store', $project) }}" method="POST" class="space-y-4 rounded-xl bg-white p-6 shadow-sm border border-slate-200">
        @csrf
        <div>
            <span class="text-xs font-bold uppercase text-violet-700">Request for Variation</span>
            <h1 class="text-xl font-black">ขอเปลี่ยนแปลงงาน / งานเพิ่ม-งานลด</h1>
            <div class="font-mono text-xs text-slate-500">Reference: {{ $nextRef }}</div>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700">Title <span class="text-rose-500">*</span></label>
            <input type="text" name="title" value="{{ old('title') }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-700">Description</label>
            <textarea name="description" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">{{ old('description') }}</textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700">Type</label>
                <select name="type" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
                    <option value="ADDITION" @selected(old('type') === 'ADDITION')>ADDITION (งานเพิ่ม)</option>
                    <option value="OMISSION" @selected(old('type') === 'OMISSION')>OMISSION (งานลด)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700">Reason</label>
                <select name="reason" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
                    @foreach(\App\Models\VariationOrder::REASONS as $reason)
                        <option value="{{ $reason }}" @selected(old('reason') === $reason)>{{ str_replace('_', ' ', $reason) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700">Cost Impact (THB, magnitude) <span class="text-rose-500">*</span></label>
                <input type="number" step="0.01" min="0" name="cost_amount" value="{{ old('cost_amount', '0.00') }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-xs font-mono">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700">Time Impact / EOT (days, magnitude)</label>
                <input type="number" step="1" min="0" name="time_impact_days" value="{{ old('time_impact_days', 0) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-xs font-mono">
            </div>
        </div>
        <p class="text-[11px] text-slate-500">เครื่องหมายบวก/ลบถูกกำหนดจาก Type โดยอัตโนมัติ (OMISSION = ลดงบ/ลดเวลา)</p>

        <div class="flex justify-end gap-3">
            <a href="{{ route('projects.vo.index', $project) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-xs font-bold text-slate-700">Cancel</a>
            <button type="submit" class="rounded-lg bg-violet-600 px-6 py-2 text-xs font-bold text-white hover:bg-violet-700">Submit Request</button>
        </div>
    </form>
</main>
</body>
</html>
