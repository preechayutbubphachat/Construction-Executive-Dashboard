<?php

namespace App\Http\Controllers;

use App\Services\TotpMfaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class MfaSecurityController extends Controller
{
    public function setup(Request $request, TotpMfaService $totp): View
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        if ($user->hasMfaEnabled()) {
            return view('auth.mfa-manage', [
                'user' => $user,
                'recoveryCodesRemaining' => count($user->mfa_recovery_codes ?? []),
            ]);
        }

        // Stash setup secret and recovery codes in session during enrollment
        $secret = $request->session()->get('mfa_setup_secret');
        $recoveryCodes = $request->session()->get('mfa_setup_recovery_codes');

        if (! $secret || ! $recoveryCodes) {
            $secret = $totp->generateSecret(16);
            $recoveryCodes = $totp->generateRecoveryCodes(8);
            $request->session()->put('mfa_setup_secret', $secret);
            $request->session()->put('mfa_setup_recovery_codes', $recoveryCodes);
        }

        $otpAuthUri = $totp->getOtpAuthUri($user, $secret);

        return view('auth.mfa-setup', [
            'user' => $user,
            'secret' => $secret,
            'recoveryCodes' => $recoveryCodes,
            'otpAuthUri' => $otpAuthUri,
        ]);
    }

    public function enable(Request $request, TotpMfaService $totp): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $secret = (string) $request->session()->get('mfa_setup_secret');
        $recoveryCodes = (array) $request->session()->get('mfa_setup_recovery_codes', []);

        if (! $secret || empty($recoveryCodes)) {
            return redirect()->route('mfa.setup')
                ->withErrors(['code' => 'MFA enrollment session expired. Please retry.']);
        }

        if (! $totp->verifyCode($secret, $request->input('code'))) {
            return redirect()->route('mfa.setup')
                ->withErrors(['code' => 'Invalid 6-digit TOTP verification code. Ensure your device clock is synchronized.']);
        }

        $user->enableMfa($secret, $recoveryCodes);

        $request->session()->forget(['mfa_setup_secret', 'mfa_setup_recovery_codes']);
        $request->session()->put('mfa_verified', true);

        return redirect()->route('mfa.setup')
            ->with('status', 'Multi-Factor Authentication (MFA) successfully activated for your account.');
    }

    public function challenge(Request $request): View
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        return view('auth.mfa-challenge', [
            'user' => $user,
        ]);
    }

    public function verify(Request $request, TotpMfaService $totp): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $request->validate([
            'code' => ['nullable', 'string'],
            'recovery_code' => ['nullable', 'string'],
        ]);

        $code = $request->input('code');
        $recoveryCode = $request->input('recovery_code');

        $verified = false;

        if (! empty($code)) {
            if ($user->mfa_secret && $totp->verifyCode($user->mfa_secret, $code)) {
                $verified = true;
            }
        } elseif (! empty($recoveryCode)) {
            if ($totp->verifyAndConsumeRecoveryCode($user, $recoveryCode)) {
                $verified = true;
            }
        }

        if (! $verified) {
            return back()->withErrors([
                'code' => 'Authentication failed. Invalid 6-digit code or one-time recovery code.',
            ]);
        }

        $request->session()->put('mfa_verified', true);
        $intended = $request->session()->pull('url.intended', route('projects.portfolio'));

        return redirect()->to($intended);
    }

    public function disable(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Hash::check($request->input('password'), $user->password)) {
            return back()->withErrors([
                'password' => 'Incorrect account password. Multi-Factor Authentication was not disabled.',
            ]);
        }

        $user->disableMfa();
        $request->session()->forget('mfa_verified');

        return redirect()->route('mfa.setup')
            ->with('status', 'Multi-Factor Authentication has been successfully deactivated.');
    }
}
