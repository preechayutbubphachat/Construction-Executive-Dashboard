<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Construction Executive Dashboard — Login</title>

    <!-- Google Fonts for Modern Clean Readability -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex items-center justify-center p-4 antialiased selection:bg-safety-500 selection:text-white">
    <div class="w-full max-w-md space-y-6">
        <!-- Logo & Header (MindInventory Safety Orange Branding) -->
        <div class="text-center space-y-2">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-safety-500 to-amber-600 shadow-safety-glow text-2xl font-black text-white">
                🏗️
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">
                Construction Executive Dashboard
            </h1>
            <p class="text-xs text-slate-500 font-medium">
                Enterprise Construction Project Management & Commercial Execution
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-3.5 text-xs text-emerald-800 shadow-xs">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl bg-rose-50 border border-rose-200 p-3.5 text-xs text-rose-800 shadow-xs space-y-1">
                @foreach ($errors->all() as $error)
                    <div>• {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <!-- Quick 1-Click Role Login for Demo (Clean White Card) -->
        <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-7 shadow-card space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-orange-600">⚡ 1-Click Demo Personas</span>
                <span class="text-[11px] text-slate-400 font-medium">Maker-Checker Ready</span>
            </div>

            <div class="space-y-3">
                @forelse ($users as $u)
                    <form method="POST" action="{{ route('quick-login', $u) }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 hover:bg-orange-50/70 border border-slate-200 hover:border-orange-300 transition-all group text-left shadow-2xs">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-sky-600 to-blue-600 flex items-center justify-center text-xs font-bold text-white uppercase shadow-xs">
                                    {{ substr($u->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-slate-800 group-hover:text-orange-600 transition-colors">
                                        {{ $u->name }}
                                    </div>
                                    <div class="text-xs text-slate-500 font-mono">
                                        {{ $u->email }}
                                    </div>
                                </div>
                            </div>
                            <span class="px-3 py-1.5 text-xs font-bold rounded-xl bg-orange-100 text-orange-700 border border-orange-200 group-hover:bg-safety-500 group-hover:text-white transition shadow-2xs">
                                เข้าใช้งาน →
                            </span>
                        </button>
                    </form>
                @empty
                    <div class="text-center py-4 text-xs text-slate-500">
                        No seed users found. Please run seeders.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="text-center text-[11px] text-slate-400 font-medium">
            CED PRO · MindInventory Industrial Architecture · v8.0.0
        </div>
    </div>
</body>
</html>
