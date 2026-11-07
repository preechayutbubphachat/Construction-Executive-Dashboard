<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use RuntimeException;

class TotpMfaService
{
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate a cryptographically secure Base32 TOTP secret (16-32 chars).
     */
    public function generateSecret(int $length = 16): string
    {
        $secret = '';
        $alphabetLength = strlen(self::BASE32_ALPHABET);
        for ($i = 0; $i < $length; $i++) {
            $secret .= self::BASE32_ALPHABET[random_int(0, $alphabetLength - 1)];
        }

        return $secret;
    }

    /**
     * Generate a set of random one-time backup recovery codes.
     *
     * @return list<string>
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = sprintf(
                'CED-%s-%s',
                strtoupper(Str::random(4)),
                strtoupper(Str::random(4))
            );
        }

        return $codes;
    }

    /**
     * Generate standard otpauth:// URI compatible with Google Authenticator, Microsoft Authenticator, 1Password.
     */
    public function getOtpAuthUri(User $user, string $secret, string $issuer = 'Construction Executive Dashboard'): string
    {
        $label = rawurlencode($issuer).':'.rawurlencode($user->email);

        return sprintf(
            'otpauth://totp/%s?secret=%s&issuer=%s&algorithm=SHA1&digits=6&period=30',
            $label,
            $secret,
            rawurlencode($issuer)
        );
    }

    /**
     * Calculate 6-digit TOTP code for a secret at a specific Unix timestamp (RFC 6238).
     */
    public function calculateOtp(string $secret, ?int $timestamp = null): string
    {
        $timestamp = $timestamp ?? time();
        $counter = (int) floor($timestamp / 30);

        $binarySecret = $this->base32Decode($secret);

        // Pack counter into 8-byte big-endian binary string
        $binaryCounter = pack('N*', 0).pack('N*', $counter);

        // Compute HMAC-SHA1
        $hash = hash_hmac('sha1', $binaryCounter, $binarySecret, true);

        // Dynamic truncation (RFC 4226 / 6238)
        $offset = ord($hash[19]) & 0x0F;
        $binaryCode = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        );

        $otp = $binaryCode % 1000000;

        return str_pad((string) $otp, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Verify a 6-digit code against a secret within a drift window (+- 1 time step = 30s).
     */
    public function verifyCode(string $secret, string $code, int $discrepancy = 1): bool
    {
        $code = trim($code);
        if (strlen($code) !== 6 || ! ctype_digit($code)) {
            return false;
        }

        $currentTime = time();
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $timestamp = $currentTime + ($i * 30);
            if (hash_equals($this->calculateOtp($secret, $timestamp), $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verify and consume a one-time recovery code for a user.
     */
    public function verifyAndConsumeRecoveryCode(User $user, string $inputCode): bool
    {
        $cleanInput = strtoupper(trim($inputCode));
        $recoveryCodes = $user->mfa_recovery_codes ?? [];

        if (! is_array($recoveryCodes) || empty($recoveryCodes)) {
            return false;
        }

        foreach ($recoveryCodes as $index => $storedCode) {
            if (hash_equals(strtoupper(trim($storedCode)), $cleanInput)) {
                // Consume the code
                unset($recoveryCodes[$index]);
                $user->mfa_recovery_codes = array_values($recoveryCodes);
                $user->save();

                return true;
            }
        }

        return false;
    }

    /**
     * Base32 decoding implementation.
     */
    public function base32Decode(string $base32): string
    {
        $clean = strtoupper(preg_replace('/[^A-Z2-7]/', '', $base32) ?? '');
        if ($clean === '') {
            throw new RuntimeException('Invalid Base32 secret string provided.');
        }

        $buffer = 0;
        $bitsLeft = 0;
        $binary = '';

        $length = strlen($clean);
        for ($i = 0; $i < $length; $i++) {
            $char = $clean[$i];
            $val = strpos(self::BASE32_ALPHABET, $char);
            if ($val === false) {
                continue;
            }

            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $binary .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $binary;
    }
}
