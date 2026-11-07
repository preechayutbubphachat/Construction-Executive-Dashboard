<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Services\ProjectAccess;
use App\Services\TotpMfaService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnterpriseMfaAndRbacTest extends TestCase
{
    use RefreshDatabase;

    private User $executive;

    private User $supervisor;

    private User $auditor;

    private Project $project;

    private TotpMfaService $totpService;

    private ProjectAccess $projectAccess;

    protected function setUp(): void
    {
        parent::setUp();

        $this->totpService = app(TotpMfaService::class);
        $this->projectAccess = app(ProjectAccess::class);

        $this->executive = User::query()->create([
            'name' => 'Somchai Executive',
            'email' => 'somchai.exec@construction.local',
            'password' => Hash::make('Secret123!'),
        ]);

        $this->supervisor = User::query()->create([
            'name' => 'Wichai Supervisor',
            'email' => 'wichai.site@construction.local',
            'password' => Hash::make('Secret123!'),
        ]);

        $this->auditor = User::query()->create([
            'name' => 'Kanya Auditor',
            'email' => 'kanya.audit@construction.local',
            'password' => Hash::make('Secret123!'),
        ]);

        $this->project = Project::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'CED-MFA-01',
            'name' => 'High-Speed Rail Viaduct C4',
            'status' => 'ACTIVE',
            'budget_amount' => 85000000.00,
            'baseline_start_date' => CarbonImmutable::now()->startOfWeek()->toDateString(),
            'baseline_finish_date' => CarbonImmutable::now()->addMonths(12)->toDateString(),
        ]);

        $this->project->members()->attach($this->executive, ['role' => 'EXECUTIVE']);
        $this->project->members()->attach($this->supervisor, ['role' => 'SITE_SUPERVISOR']);
        $this->project->members()->attach($this->auditor, ['role' => 'AUDITOR']);
    }

    public function test_totp_service_generates_valid_secret_and_recovery_codes(): void
    {
        $secret = $this->totpService->generateSecret(16);
        $this->assertEquals(16, strlen($secret));
        $this->assertMatchesRegularExpression('/^[A-Z2-7]+$/', $secret);

        $codes = $this->totpService->generateRecoveryCodes(8);
        $this->assertCount(8, $codes);
        $this->assertStringStartsWith('CED-', $codes[0]);

        $uri = $this->totpService->getOtpAuthUri($this->executive, $secret);
        $this->assertStringContainsString('otpauth://totp/', $uri);
        $this->assertStringContainsString('secret='.$secret, $uri);
        $this->assertStringContainsString(rawurlencode($this->executive->email), $uri);
    }

    public function test_totp_service_calculates_and_verifies_correct_code(): void
    {
        $secret = $this->totpService->generateSecret(16);
        $code = $this->totpService->calculateOtp($secret);

        $this->assertEquals(6, strlen($code));
        $this->assertTrue(ctype_digit($code));

        $this->assertTrue($this->totpService->verifyCode($secret, $code));
        $this->assertFalse($this->totpService->verifyCode($secret, '000000'));
    }

    public function test_totp_service_verifies_and_consumes_recovery_code_once(): void
    {
        $codes = ['CED-AAAA-1111', 'CED-BBBB-2222', 'CED-CCCC-3333'];
        $this->executive->mfa_recovery_codes = $codes;
        $this->executive->save();

        // 1. Valid recovery code succeeds
        $this->assertTrue($this->totpService->verifyAndConsumeRecoveryCode($this->executive, 'CED-AAAA-1111'));

        // 2. Recovery code array is reduced
        $this->executive->refresh();
        $this->assertCount(2, $this->executive->mfa_recovery_codes);
        $this->assertNotContains('CED-AAAA-1111', $this->executive->mfa_recovery_codes);

        // 3. Re-using the same consumed code fails
        $this->assertFalse($this->totpService->verifyAndConsumeRecoveryCode($this->executive, 'CED-AAAA-1111'));
    }

    public function test_user_can_view_mfa_setup_screen(): void
    {
        $response = $this->actingAs($this->executive)->get(route('mfa.setup'));

        $response->assertOk()
            ->assertSee('Two-Factor Authentication (MFA / TOTP)')
            ->assertSee('Manual Entry Secret Key')
            ->assertSee('Save Emergency Recovery Codes')
            ->assertSee('CED-');
    }

    public function test_user_cannot_enable_mfa_with_invalid_code(): void
    {
        $this->actingAs($this->executive)->get(route('mfa.setup'));

        $response = $this->actingAs($this->executive)->post(route('mfa.enable'), [
            'code' => '999999',
        ]);

        $response->assertRedirect(route('mfa.setup'))
            ->assertSessionHasErrors('code');

        $this->assertFalse($this->executive->fresh()->hasMfaEnabled());
    }

    public function test_user_can_enable_mfa_with_valid_totp_code(): void
    {
        // 1. Visit setup to generate secret in session
        $this->actingAs($this->executive)->get(route('mfa.setup'));
        $secret = session('mfa_setup_secret');
        $validCode = $this->totpService->calculateOtp($secret);

        // 2. Submit valid 6-digit code
        $response = $this->actingAs($this->executive)->post(route('mfa.enable'), [
            'code' => $validCode,
        ]);

        $response->assertRedirect(route('mfa.setup'))
            ->assertSessionHas('status');

        $freshUser = $this->executive->fresh();
        $this->assertTrue($freshUser->hasMfaEnabled());
        $this->assertNotNull($freshUser->mfa_confirmed_at);
        $this->assertCount(8, $freshUser->mfa_recovery_codes);
        $this->assertTrue(session('mfa_verified'));
    }

    public function test_mfa_middleware_redirects_unverified_session_to_challenge(): void
    {
        // Setup user with MFA already enabled
        $secret = $this->totpService->generateSecret(16);
        $codes = $this->totpService->generateRecoveryCodes(8);
        $this->executive->enableMfa($secret, $codes);

        // Access protected portfolio without 'mfa_verified' in session
        $response = $this->actingAs($this->executive)
            ->withSession(['mfa_verified' => false])
            ->get(route('projects.portfolio'));

        $response->assertRedirect(route('mfa.challenge'));
    }

    public function test_mfa_challenge_succeeds_with_totp_code_and_unlocks_session(): void
    {
        $secret = $this->totpService->generateSecret(16);
        $codes = $this->totpService->generateRecoveryCodes(8);
        $this->executive->enableMfa($secret, $codes);

        $validOtp = $this->totpService->calculateOtp($secret);

        $response = $this->actingAs($this->executive)
            ->withSession(['mfa_verified' => false])
            ->post(route('mfa.verify'), [
                'code' => $validOtp,
            ]);

        $response->assertRedirect(route('projects.portfolio'));
        $this->assertTrue(session('mfa_verified'));
    }

    public function test_mfa_challenge_succeeds_with_recovery_code(): void
    {
        $secret = $this->totpService->generateSecret(16);
        $codes = $this->totpService->generateRecoveryCodes(8);
        $recoveryToUse = $codes[0];
        $this->executive->enableMfa($secret, $codes);

        $response = $this->actingAs($this->executive)
            ->withSession(['mfa_verified' => false])
            ->post(route('mfa.verify'), [
                'recovery_code' => $recoveryToUse,
            ]);

        $response->assertRedirect(route('projects.portfolio'));
        $this->assertTrue(session('mfa_verified'));

        $this->assertCount(7, $this->executive->fresh()->mfa_recovery_codes);
    }

    public function test_user_can_disable_mfa_with_password(): void
    {
        $secret = $this->totpService->generateSecret(16);
        $codes = $this->totpService->generateRecoveryCodes(8);
        $this->executive->enableMfa($secret, $codes);

        $this->assertTrue($this->executive->fresh()->hasMfaEnabled());

        // Incorrect password fails
        $this->actingAs($this->executive)
            ->withSession(['mfa_verified' => true])
            ->post(route('mfa.disable'), [
                'password' => 'WrongPassword',
            ])
            ->assertSessionHasErrors('password');

        $this->assertTrue($this->executive->fresh()->hasMfaEnabled());

        // Correct password succeeds
        $this->actingAs($this->executive)
            ->withSession(['mfa_verified' => true])
            ->post(route('mfa.disable'), [
                'password' => 'Secret123!',
            ])
            ->assertRedirect(route('mfa.setup'));

        $this->assertFalse($this->executive->fresh()->hasMfaEnabled());
    }

    public function test_rbac_access_matrix(): void
    {
        // 1. EXECUTIVE has full permissions
        $this->assertTrue($this->projectAccess->isExecutive($this->executive, $this->project));
        $this->assertTrue($this->projectAccess->canManageOperations($this->executive, $this->project));
        $this->assertTrue($this->projectAccess->canCaptureSite($this->executive, $this->project));
        $this->assertTrue($this->projectAccess->canApproveFinance($this->executive, $this->project));
        $this->assertTrue($this->projectAccess->canArchive($this->executive, $this->project));

        // 2. SITE_SUPERVISOR has site capture & operations, but cannot approve finance or archive
        $this->assertFalse($this->projectAccess->isExecutive($this->supervisor, $this->project));
        $this->assertTrue($this->projectAccess->canCaptureSite($this->supervisor, $this->project));
        $this->assertFalse($this->projectAccess->canApproveFinance($this->supervisor, $this->project));
        $this->assertFalse($this->projectAccess->canArchive($this->supervisor, $this->project));

        // 3. AUDITOR has read-only access (allows view, but no write operations)
        $this->assertTrue($this->projectAccess->allows($this->auditor, $this->project));
        $this->assertFalse($this->projectAccess->isExecutive($this->auditor, $this->project));
        $this->assertFalse($this->projectAccess->canManageOperations($this->auditor, $this->project));
        $this->assertFalse($this->projectAccess->canCaptureSite($this->auditor, $this->project));
        $this->assertFalse($this->projectAccess->canApproveFinance($this->auditor, $this->project));
    }
}
