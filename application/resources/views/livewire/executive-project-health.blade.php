<section class="grid gap-6" data-health-version="{{ $health['version'] }}">
    <header class="rounded-2xl bg-slate-950 p-8 text-white shadow-xl">
        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-cyan-300">Construction Executive Dashboard</p>
        <div class="mt-3 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold">{{ $projectName }}</h1>
                <p class="mt-1 font-mono text-sm text-slate-300">{{ $projectId }}</p>
            </div>
            <div class="rounded-xl border border-white/20 px-5 py-3 text-right">
                <p class="text-xs uppercase text-slate-300">Overall health</p>
                <p class="text-2xl font-black">{{ $health['overall_status'] }}</p>
            </div>
        </div>
    </header>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <article class="rounded-xl bg-white p-5 shadow">
            <p class="text-xs font-bold uppercase text-slate-500">Actual</p>
            <p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($actual['value'], 2) }}%</p>
            <p class="mt-2 text-xs text-slate-500">{{ $actual['version'] }}</p>
        </article>
        <article class="rounded-xl bg-white p-5 shadow">
            <p class="text-xs font-bold uppercase text-slate-500">Planned</p>
            <p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($planned['value'], 2) }}%</p>
            <p class="mt-2 text-xs text-slate-500">{{ $planned['version'] }}</p>
        </article>
        @foreach ($health['dimensions'] as $name => $dimension)
            <article class="rounded-xl bg-white p-5 shadow">
                <p class="text-xs font-bold uppercase text-slate-500">{{ $name }}</p>
                <p class="mt-2 text-xl font-black text-slate-900">{{ $dimension['status'] }}</p>
                <p class="mt-2 text-xs leading-5 text-slate-600">{{ $dimension['reason'] }}</p>
            </article>
        @endforeach
    </div>

    <footer class="text-xs text-slate-500">
        Evaluated {{ $health['evaluated_at'] }} · {{ $health['version'] }}
    </footer>
</section>
