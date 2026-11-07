<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Multi-Factor Authentication (MFA) Setup · Construction Executive Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 flex items-center justify-center p-4 selection:bg-amber-500 selection:text-slate-950">
    <div class="max-w-xl w-full bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl overflow-hidden p-6 sm:p-8 space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
            <div class="flex items-center gap-3">
                <span class="p-2.5 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20 text-2xl">
                    🔐
                </span>
                <div>
                    <h1 class="text-lg font-bold text-white">Two-Factor Authentication (MFA / TOTP)</h1>
                    <p class="text-xs text-slate-400">Enterprise security protection for {{ $user->email }}</p>
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

        {{-- Step 1: Add to Authenticator App --}}
        <div class="space-y-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-amber-400">Step 1: Link Authenticator App</h2>
            <p class="text-xs text-slate-300 leading-relaxed">
                Open <strong>Google Authenticator</strong>, <strong>Microsoft Authenticator</strong>, or <strong>1Password</strong> on your mobile device. Enter the setup key below or click the direct link:
            </p>

            {{-- Secret Display Box --}}
            <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between gap-3">
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-500 block">Manual Entry Secret Key:</span>
                    <span class="font-mono text-sm font-bold text-amber-400 tracking-widest select-all">{{ chunk_split($secret, 4, ' ') }}</span>
                </div>
                <a href="{{ $otpAuthUri }}" class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs transition shrink-0">
                    Open in App &nearr;
                </a>
            </div>
        </div>

        {{-- Step 2: Emergency Recovery Codes --}}
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-xs font-bold uppercase tracking-wider text-amber-400">Step 2: Save Emergency Recovery Codes</h2>
                <span class="text-[10px] text-rose-400 font-semibold">Single-use backup</span>
            </div>
            <p class="text-xs text-slate-300 leading-relaxed">
                If you lose access to your device, these emergency backup codes are the only way to recover your account:
            </p>

            <div class="grid grid-cols-2 gap-2 p-3.5 rounded-xl bg-slate-950 border border-slate-800 font-mono text-xs text-slate-300 select-all">
                @foreach ($recoveryCodes as $rc)
                    <div class="p-1 rounded bg-slate-900/60 border border-slate-800/80 text-center text-cyan-300 font-bold">
                        {{ $rc }}
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Step 3: Verification & Activation --}}
        <form method="POST" action="{{ route('mfa.enable') }}" class="space-y-4 pt-2 border-t border-slate-800">
            @csrf
            <div>
                <label for="code" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Step 3: Enter the 6-digit code shown in your Authenticator App
                </label>
                <input
                    type="text"
                    id="code"
                    name="code"
                    maxlength="6"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    autocomplete="one-time-code"
                    required
                    placeholder="123456"
                    class="w-full text-center font-mono text-xl tracking-[0.5em] py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-600 focus:outline-none focus:border-amber-400 transition"
                >
            </div>

            <button type="submit" class="w-full py-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-sm shadow-lg shadow-amber-950/40 transition active:scale-[0.99]">
                Activate Two-Factor Authentication 🛡️
            </button>
        </form>

    </div>
</body>
</html>
