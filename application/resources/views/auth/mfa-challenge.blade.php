<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Two-Factor Verification · Construction Executive Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 flex items-center justify-center p-4 selection:bg-amber-500 selection:text-slate-950">
    <div class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl p-6 sm:p-8 space-y-6" x-data="{ useRecovery: false }">

        {{-- Header --}}
        <div class="text-center space-y-2">
            <span class="inline-flex p-3 rounded-2xl bg-amber-500/10 text-amber-400 border border-amber-500/20 text-3xl">
                🛡️
            </span>
            <h1 class="text-xl font-bold text-white">Two-Factor Authentication</h1>
            <p class="text-xs text-slate-400">Verifying session for <strong>{{ $user->email }}</strong></p>
        </div>

        @if ($errors->any())
            <div class="p-3.5 rounded-xl bg-rose-950/80 border border-rose-500/50 text-rose-200 text-xs font-semibold text-center">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Verification Form --}}
        <form method="POST" action="{{ route('mfa.verify') }}" class="space-y-4">
            @csrf

            <template x-if="!useRecovery">
                <div class="space-y-3">
                    <label for="code" class="block text-xs font-semibold text-slate-300 text-center">
                        Enter the 6-digit code from your Authenticator app:
                    </label>
                    <input
                        type="text"
                        id="code"
                        name="code"
                        maxlength="6"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        autocomplete="one-time-code"
                        autofocus
                        placeholder="000000"
                        class="w-full text-center font-mono text-2xl tracking-[0.5em] py-3 rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-600 focus:outline-none focus:border-amber-400 transition"
                    >
                </div>
            </template>

            <template x-if="useRecovery">
                <div class="space-y-3">
                    <label for="recovery_code" class="block text-xs font-semibold text-rose-300 text-center">
                        Enter a one-time emergency backup recovery code:
                    </label>
                    <input
                        type="text"
                        id="recovery_code"
                        name="recovery_code"
                        placeholder="CED-ABCD-1234"
                        class="w-full text-center font-mono text-base font-bold py-3 rounded-xl bg-slate-950 border border-rose-900/60 text-cyan-300 placeholder-slate-600 focus:outline-none focus:border-rose-400 transition"
                    >
                </div>
            </template>

            <button type="submit" class="w-full py-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-sm shadow-lg shadow-amber-950/40 transition active:scale-[0.99]">
                Verify &amp; Continue &rarr;
            </button>
        </form>

        {{-- Switch to Recovery Code Link --}}
        <div class="text-center pt-2 border-t border-slate-800">
            <button
                type="button"
                @click="useRecovery = !useRecovery"
                class="text-xs text-slate-400 hover:text-amber-400 transition"
                x-text="useRecovery ? '← Use Authenticator app instead' : 'Lost your device? Use an emergency recovery code'"
            >
            </button>
        </div>

    </div>
</body>
</html>
