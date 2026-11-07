<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MFA Status & Settings · Construction Executive Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 flex items-center justify-center p-4 selection:bg-amber-500 selection:text-slate-950">
    <div class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl p-6 sm:p-8 space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
            <div class="flex items-center gap-3">
                <span class="p-2.5 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-2xl">
                    🛡️
                </span>
                <div>
                    <h1 class="text-lg font-bold text-white">Security Settings</h1>
                    <p class="text-xs text-slate-400">{{ $user->email }}</p>
                </div>
            </div>
            <a href="{{ route('projects.portfolio') }}" class="text-xs text-slate-400 hover:text-white transition">
                &larr; Portfolio
            </a>
        </div>

        @if (session('status'))
            <div class="p-3.5 rounded-xl bg-emerald-950/80 border border-emerald-500/50 text-emerald-200 text-xs font-semibold">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-3.5 rounded-xl bg-rose-950/80 border border-rose-500/50 text-rose-200 text-xs font-semibold">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Status Box --}}
        <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs text-slate-400">Two-Factor Status:</span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    ACTIVE &amp; ENFORCED
                </span>
            </div>
            <div class="flex items-center justify-between text-xs text-slate-400 pt-1 border-t border-slate-900">
                <span>Backup Recovery Codes:</span>
                <span class="font-bold text-slate-200">{{ $recoveryCodesRemaining }} remaining</span>
            </div>
            <div class="flex items-center justify-between text-xs text-slate-400">
                <span>Activated On:</span>
                <span class="font-mono text-slate-300">{{ $user->mfa_confirmed_at?->format('d M Y H:i') ?? 'Confirmed' }}</span>
            </div>
        </div>

        {{-- Deactivate MFA Section --}}
        <form method="POST" action="{{ route('mfa.disable') }}" class="p-4 rounded-xl bg-rose-950/20 border border-rose-900/40 space-y-3">
            @csrf
            <div>
                <h3 class="text-xs font-bold text-rose-300">Deactivate Two-Factor Authentication</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Enter your account password to confirm removal of 2FA protection:</p>
            </div>
            <div>
                <input
                    type="password"
                    name="password"
                    required
                    placeholder="Current Password"
                    class="w-full py-2 px-3 rounded-lg bg-slate-950 border border-slate-700 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-400"
                >
            </div>
            <button type="submit" class="w-full py-2 rounded-lg bg-rose-900 hover:bg-rose-800 text-rose-100 font-bold text-xs transition">
                Disable Two-Factor Authentication
            </button>
        </form>

    </div>
</body>
</html>
