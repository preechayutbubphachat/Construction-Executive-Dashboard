<div class="space-y-6">
    {{-- Header & Breadcrumbs --}}
    <header class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
                <a class="text-cyan-700 hover:underline" href="{{ route('projects.portfolio') }}">&larr; Portfolio</a>
                <span class="text-slate-400">&bull;</span>
                <a class="text-cyan-700 hover:underline" href="{{ route('projects.executive', $project) }}">Health</a>
                <span class="text-slate-400">&bull;</span>
                <a class="text-cyan-700 hover:underline" href="{{ route('projects.schedule.show', $project) }}">Schedule</a>
                <span class="text-slate-400">&bull;</span>
                <a class="text-cyan-700 hover:underline" href="{{ route('projects.lookahead', $project) }}">Lookahead</a>
                <span class="text-slate-400">&bull;</span>
                <a class="text-cyan-700 hover:underline" href="{{ route('projects.constraints', $project) }}">Constraints</a>
                <span class="text-slate-400">&bull;</span>
                <a class="text-indigo-700 hover:underline" href="{{ route('projects.3d-twin', $project) }}">3D Twin 🌐</a>
                <span class="text-slate-400">&bull;</span>
                <a class="text-amber-700 hover:underline" href="{{ route('projects.site', $project) }}">Site 📱</a>
            </div>
            <div class="flex items-center gap-2.5 mt-2">
                <span class="px-2.5 py-0.5 rounded text-xs font-mono font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                    {{ $project->code }}
                </span>
                <h1 class="text-2xl font-black text-slate-900">
                    Earned Value Management (EVM) & S-Curve Forecasting
                </h1>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                {{ $project->name }} &middot; International Construction EVM Standard &middot; Pure Projection
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('projects.report', $project) }}" class="rounded-lg bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow hover:bg-slate-800 transition">
                Executive PDF Report
            </a>
            <a href="{{ route('projects.3d-twin', $project) }}" class="rounded-lg bg-indigo-700 px-4 py-2 text-xs font-bold text-white shadow hover:bg-indigo-800 transition">
                3D BIM Model &rarr;
            </a>
        </div>
    </header>

    {{-- 4 Core EVM Financial Metrics --}}
    <section class="grid grid-cols-2 lg:grid-cols-4 gap-4" aria-label="EVM Core Values">
        <div class="p-5 rounded-xl bg-white border border-slate-200 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">BAC (Total Budget)</span>
            <p class="text-2xl font-black text-slate-900 mt-1">฿{{ number_format($metrics['bac'], 2) }}</p>
            <p class="text-[11px] text-slate-500 mt-1">Budget at Completion baseline</p>
        </div>

        <div class="p-5 rounded-xl bg-white border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-cyan-700">PV (Planned Value)</span>
                <span class="text-xs font-mono text-cyan-600 font-bold">{{ number_format($evmData['progress']['planned_percent'], 1) }}%</span>
            </div>
            <p class="text-2xl font-black text-cyan-700 mt-1">฿{{ number_format($metrics['pv'], 2) }}</p>
            <p class="text-[11px] text-slate-500 mt-1">Budgeted cost of work scheduled</p>
        </div>

        <div class="p-5 rounded-xl bg-white border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">EV (Earned Value)</span>
                <span class="text-xs font-mono text-emerald-600 font-bold">{{ number_format($evmData['progress']['actual_percent'], 1) }}%</span>
            </div>
            <p class="text-2xl font-black text-emerald-700 mt-1">฿{{ number_format($metrics['ev'], 2) }}</p>
            <p class="text-[11px] text-slate-500 mt-1">Budgeted value of completed work</p>
        </div>

        <div class="p-5 rounded-xl bg-white border border-slate-200 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-700">AC (Actual Cost)</span>
            <p class="text-2xl font-black text-slate-800 mt-1">฿{{ number_format($metrics['ac'], 2) }}</p>
            <p class="text-[11px] text-slate-500 mt-1">Total posted actual expenses</p>
        </div>
    </section>

    {{-- Performance Indices & Variances --}}
    <section class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- CPI (Cost Performance Index) --}}
        <div class="p-5 rounded-xl bg-white border {{ $indices['cpi'] >= 1.0 ? 'border-emerald-200 bg-emerald-50/20' : 'border-rose-200 bg-rose-50/20' }} shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-600">Cost Performance</span>
                    <h3 class="text-xl font-black text-slate-900 mt-0.5">
                        CPI: <span class="{{ $indices['cpi'] >= 1.0 ? 'text-emerald-700' : 'text-rose-700' }}">{{ number_format($indices['cpi'], 2) }}</span>
                    </h3>
                </div>
                <span class="px-2.5 py-1 rounded-md text-xs font-bold uppercase {{ $indices['cpi'] >= 1.0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                    {{ $indices['cpi'] >= 1.0 ? 'Cost Efficient' : 'Cost Overrun' }}
                </span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-200/80 flex items-center justify-between text-xs">
                <span class="text-slate-600">Cost Variance (CV = EV - AC):</span>
                <span class="font-mono font-bold {{ $variances['cv'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                    {{ $variances['cv'] >= 0 ? '+' : '' }}฿{{ number_format($variances['cv'], 2) }}
                </span>
            </div>
            <p class="text-[11px] text-slate-500 mt-1">
                {{ $indices['cpi'] >= 1.0 ? 'Earning more value than expenditure.' : 'Generating less than ฿1.00 of physical value per ฿1.00 spent.' }}
            </p>
        </div>

        {{-- SPI (Schedule Performance Index) --}}
        <div class="p-5 rounded-xl bg-white border {{ $indices['spi'] >= 1.0 ? 'border-emerald-200 bg-emerald-50/20' : 'border-rose-200 bg-rose-50/20' }} shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-600">Schedule Performance</span>
                    <h3 class="text-xl font-black text-slate-900 mt-0.5">
                        SPI: <span class="{{ $indices['spi'] >= 1.0 ? 'text-emerald-700' : 'text-rose-700' }}">{{ number_format($indices['spi'], 2) }}</span>
                    </h3>
                </div>
                <span class="px-2.5 py-1 rounded-md text-xs font-bold uppercase {{ $indices['spi'] >= 1.0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                    {{ $indices['spi'] >= 1.0 ? 'Ahead of Schedule' : 'Schedule Delayed' }}
                </span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-200/80 flex items-center justify-between text-xs">
                <span class="text-slate-600">Schedule Variance (SV = EV - PV):</span>
                <span class="font-mono font-bold {{ $variances['sv'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                    {{ $variances['sv'] >= 0 ? '+' : '' }}฿{{ number_format($variances['sv'], 2) }}
                </span>
            </div>
            <p class="text-[11px] text-slate-500 mt-1">
                {{ $indices['spi'] >= 1.0 ? 'Project progressing ahead of planned velocity.' : 'Physical progress is lagging behind baseline timeline.' }}
            </p>
        </div>
    </section>

    {{-- Interactive SVG S-Curve Chart --}}
    <section class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-lg font-black text-slate-900">Project S-Curve Cumulative Progression</h2>
                <p class="text-xs text-slate-500">Planned vs Earned vs Actual Cost trajectory over project duration</p>
            </div>

            {{-- Legend --}}
            <div class="flex items-center gap-4 text-xs font-medium">
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-0.5 bg-cyan-600 inline-block"></span>
                    <span class="text-slate-600">Planned (PV)</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-0.5 bg-emerald-600 inline-block"></span>
                    <span class="text-slate-600">Earned (EV)</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-0.5 bg-rose-600 inline-block"></span>
                    <span class="text-slate-600">Actual Cost (AC)</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-0.5 border-t border-dashed border-emerald-400 inline-block"></span>
                    <span class="text-slate-400">Forecast</span>
                </span>
            </div>
        </div>

        {{-- SVG Chart Representation --}}
        @php
            $count = count($series);
            $width = 900;
            $height = 280;
            $padding = 50;
            $plotW = $width - ($padding * 2);
            $plotH = $height - ($padding * 2);
            $maxVal = max($metrics['bac'] * 1.15, 1000);

            // Compute SVG Points
            $pvPoints = [];
            $evPoints = [];
            $acPoints = [];
            $forecastPoints = [];

            foreach ($series as $idx => $pt) {
                $x = $padding + ($count > 1 ? ($idx / ($count - 1)) * $plotW : 0);
                
                $yPv = $height - $padding - (($pt['pv'] / $maxVal) * $plotH);
                $pvPoints[] = "{$x},{$yPv}";

                if ($pt['ev'] !== null) {
                    $yEv = $height - $padding - (($pt['ev'] / $maxVal) * $plotH);
                    $evPoints[] = "{$x},{$yEv}";
                }

                if ($pt['ac'] !== null) {
                    $yAc = $height - $padding - (($pt['ac'] / $maxVal) * $plotH);
                    $acPoints[] = "{$x},{$yAc}";
                }

                if ($pt['forecast_ev'] !== null) {
                    $yFc = $height - $padding - (($pt['forecast_ev'] / $maxVal) * $plotH);
                    $forecastPoints[] = "{$x},{$yFc}";
                }
            }

            $pvPoly = implode(' ', $pvPoints);
            $evPoly = implode(' ', $evPoints);
            $acPoly = implode(' ', $acPoints);
            $fcPoly = implode(' ', $forecastPoints);
        @endphp

        <div class="relative w-full overflow-x-auto">
            <svg viewBox="0 0 {{ $width }} {{ $height }}" class="w-full h-auto max-h-72 select-none">
                {{-- Grid Lines --}}
                @for ($g = 0; $g <= 4; $g++)
                    @php
                        $gridY = $padding + ($g * ($plotH / 4));
                        $valLabel = $maxVal * (1 - ($g / 4));
                    @endphp
                    <line x1="{{ $padding }}" y1="{{ $gridY }}" x2="{{ $width - $padding }}" y2="{{ $gridY }}" stroke="#e2e8f0" stroke-width="1" />
                    <text x="{{ $padding - 8 }}" y="{{ $gridY + 4 }}" font-size="10" fill="#94a3b8" text-anchor="end">฿{{ number_format($valLabel / 1000, 0) }}k</text>
                @endfor

                {{-- Baseline Line (PV) --}}
                <polyline fill="none" stroke="#0891b2" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" points="{{ $pvPoly }}" />

                {{-- Forecast Dotted Line --}}
                <polyline fill="none" stroke="#34d399" stroke-width="2" stroke-dasharray="4,4" points="{{ $fcPoly }}" />

                {{-- Actual Cost Line (AC) --}}
                @if (!empty($acPoints))
                    <polyline fill="none" stroke="#e11d48" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" points="{{ $acPoly }}" />
                @endif

                {{-- Earned Value Line (EV) --}}
                @if (!empty($evPoints))
                    <polyline fill="none" stroke="#059669" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" points="{{ $evPoly }}" />
                @endif
            </svg>
        </div>
    </section>

    {{-- Predictive Forecasting at Completion (EAC, VAC, ETC, Date) --}}
    <section class="p-6 rounded-2xl bg-slate-900 text-white shadow-xl space-y-4">
        <div>
            <span class="text-xs font-mono font-bold uppercase tracking-wider text-amber-400">Predictive Forecasting</span>
            <h2 class="text-lg font-black text-white mt-0.5">Estimate at Completion (EAC Forecasts)</h2>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-4 rounded-xl bg-slate-800/80 border border-slate-700">
                <span class="text-xs text-slate-400">EAC (Projected Total Cost)</span>
                <p class="text-xl font-bold text-white mt-1">฿{{ number_format($forecasts['eac'], 2) }}</p>
                <p class="text-[11px] text-slate-400 mt-1">Expected final spend at CPI velocity</p>
            </div>

            <div class="p-4 rounded-xl bg-slate-800/80 border border-slate-700">
                <span class="text-xs text-slate-400">VAC (Projected Over/Under)</span>
                <p class="text-xl font-bold {{ $forecasts['vac'] >= 0 ? 'text-emerald-400' : 'text-rose-400' }} mt-1">
                    {{ $forecasts['vac'] >= 0 ? '+' : '' }}฿{{ number_format($forecasts['vac'], 2) }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">{{ $forecasts['vac'] >= 0 ? 'Projected budget savings' : 'Expected budget overrun' }}</p>
            </div>

            <div class="p-4 rounded-xl bg-slate-800/80 border border-slate-700">
                <span class="text-xs text-slate-400">ETC (Remaining Cost)</span>
                <p class="text-xl font-bold text-amber-400 mt-1">฿{{ number_format($forecasts['etc'], 2) }}</p>
                <p class="text-[11px] text-slate-400 mt-1">Funds needed to finish project</p>
            </div>

            <div class="p-4 rounded-xl bg-slate-800/80 border border-slate-700">
                <span class="text-xs text-slate-400">Projected Completion</span>
                <p class="text-base font-bold text-white mt-1.5">{{ $forecasts['estimated_finish_date'] }}</p>
                <p class="text-[11px] text-slate-400 mt-1">
                    @if ($forecasts['delay_days'] > 0)
                        <span class="text-rose-400 font-bold">+{{ $forecasts['delay_days'] }} days delay</span> vs baseline
                    @else
                        <span class="text-emerald-400 font-bold">On Schedule</span>
                    @endif
                </p>
            </div>
        </div>
    </section>
</div>
