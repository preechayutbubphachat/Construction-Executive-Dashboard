<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $task->id }} · WBS Task</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
<main class="mx-auto max-w-3xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
    <header class="rounded-xl bg-white p-6 shadow">
        <p class="text-sm font-bold uppercase tracking-wide text-cyan-700">{{ $project->code }} · Schedule source</p>
        <h1 class="mt-1 text-2xl font-black">{{ $task->id }}</h1>
        <p class="mt-2 text-lg font-semibold">{{ $task->wbs_code }} · {{ $task->name }}</p>
        <p class="mt-2 text-sm text-slate-600">Project {{ $project->id }} · {{ $task->status }} · baseline {{ $task->baseline_start_date->toDateString() }} → {{ $task->baseline_finish_date->toDateString() }}</p>
    </header>
    <section class="rounded-xl bg-white p-6 shadow" aria-label="Progress records">
        <h2 class="text-lg font-black">Progress records</h2>
        <ul class="mt-4 divide-y divide-slate-200">
            @forelse ($task->progressEntries as $entry)
                <li class="py-3" data-progress-id="{{ $entry->id }}">
                    <span class="font-mono text-xs text-slate-500">{{ $entry->id }}</span>
                    <p class="font-semibold">{{ $entry->progress_percent }}% on {{ $entry->progress_date->toDateString() }}</p>
                    <p class="text-sm text-slate-600">Recorded {{ $entry->recorded_at->utc()->format('Y-m-d\TH:i:s\Z') }} · {{ $entry->note }}</p>
                </li>
            @empty
                <li class="py-3 text-sm text-slate-500">No progress records.</li>
            @endforelse
        </ul>
    </section>
</main>
</body>
</html>
