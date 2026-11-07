<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Construction Executive Dashboard — Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4 antialiased">
    <div class="w-full max-w-md space-y-6">
        <!-- Logo & Header -->
        <div class="text-center space-y-2">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-cyan-600 to-emerald-500 shadow-lg shadow-cyan-500/20 text-2xl font-black text-white">
                🏗️
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white">
                Construction Executive Dashboard
            </h1>
            <p class="text-xs text-slate-400">
                Enterprise Construction Intelligence & Commercial Execution System
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-xl bg-emerald-950/60 border border-emerald-500/40 p-3 text-xs text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl bg-rose-950/60 border border-rose-500/40 p-3 text-xs text-rose-200">
                @foreach ($errors->all() as $error)
                    <div>• {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <!-- Quick 1-Click Role Login for Demo -->
        <div class="rounded-2xl bg-slate-900/90 border border-slate-800 p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-cyan-400">⚡ 1-Click Demo Personas</span>
                <span class="text-[11px] text-slate-500">Maker-Checker Ready</span>
            </div>

            <div class="space-y-2.5">
                @forelse ($users as $u)
                    <form method="POST" action="{{ route('quick-login', $u) }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center justify-between p-3 rounded-xl bg-slate-950 hover:bg-slate-800 border border-slate-800 hover:border-cyan-500/40 transition group text-left">
                            <div>
                                <div class="text-sm font-semibold text-white group-hover:text-cyan-300">
                                    {{ $u->name }}
                                </div>
                                <div class="text-xs text-slate-400">
                                    {{ $u->email }}
                                </div>
                            </div>
                            <span class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-cyan-500/10 text-cyan-300 border border-cyan-500/20 group-hover:bg-cyan-500 group-hover:text-slate-950 transition">
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

        <div class="text-center text-[11px] text-slate-500">
            CED v8.0.0 · Comprehensive Enterprise Construction Suite
        </div>
    </div>
</body>
</html>
