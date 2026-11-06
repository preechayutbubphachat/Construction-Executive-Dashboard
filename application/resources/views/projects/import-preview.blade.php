<!doctype html><html><head><meta charset="utf-8"><title>Excel import preview</title></head><body class="bg-slate-100">
<div class="mx-auto max-w-6xl p-6">
<h1 class="text-2xl font-bold">Excel import preview · {{ $project->code }}</h1>
<form class="mt-6 rounded bg-white p-6 shadow" method="POST" action="{{ route('projects.import-preview.store', $project) }}" enctype="multipart/form-data">
@csrf <label class="block font-semibold" for="workbook">XLSX workbook (maximum 10 MiB)</label>
<input class="mt-2 block" id="workbook" name="workbook" type="file" accept=".xlsx" required>
<button class="mt-4 rounded bg-cyan-700 px-4 py-2 text-white" type="submit">Validate and preview</button>
@if(isset($errors) && $errors->has('workbook'))<p class="mt-2 text-red-700">{{ $errors->first('workbook') }}</p>@endif
</form>
@if(isset($report))
<section class="mt-6 rounded bg-white p-6 shadow" data-preview-ready="{{ $report['ready'] ? 'true' : 'false' }}">
<h2 class="text-xl font-bold">{{ $report['ready'] ? 'Ready for review' : 'Validation issues' }}</h2>
@if(!empty($report['summary']))<p class="mt-2">Rows: {{ $report['summary']['total_rows'] }} · Valid: {{ $report['summary']['valid_rows'] }} · Invalid: {{ $report['summary']['invalid_rows'] }}</p>@endif
<ul class="mt-4 list-disc pl-6">@foreach($report['issues'] ?? [] as $issue)<li>{{ $issue['code'] }}: {{ $issue['message'] }}</li>@endforeach</ul>
@foreach($report['sheets'] ?? [] as $name => $sheet)<h3 class="mt-5 font-bold">{{ $name }}</h3><p>{{ count($sheet['rows']) }} candidate rows; mapping is explicit and read-only.</p>@endforeach
</section>
@endif
</div></body></html>
