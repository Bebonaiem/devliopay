<?php

namespace Tests\Unit;

use App\Services\TwoFactorService;
use PHPUnit\Framework\TestCase;

class TwoFactorTest extends TestCase
{
    private TwoFactorService $twoFactorService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->twoFactorService = new TwoFactorService;
    }

    public function test_generates_secret_of_correct_length(): void
    {
        $secret = $this->twoFactorService->generateSecret();

        $this->assertEquals(20, strlen($secret));
        $this->assertMatchesRegularExpression('/^[A-Z2-7]+$/', $secret);
    }

    public function test_generates_unique_secrets(): void
    {
        $secret1 = $this->twoFactorService->generateSecret();
        $secret2 = $this->twoFactorService->generateSecret();

        $this->assertNotEquals($secret1, $secret2);
    }

    public function test_generates_qr_code_url(): void
    {
        $secret = $this->twoFactorService->generateSecret();
        $qrOutput = $this->twoFactorService->getQrCodeUrl($secret, 'test@example.com', 'DevlioPay');

        $this->assertNotEmpty($qrOutput);
        $this->assertStringContainsString('svg', $qrOutput);
    }

    public function test_verify_code_returns_false_for_invalid_code(): void
    {
        $secret = $this->twoFactorService->generateSecret();

        $result = $this->twoFactorService->verifyCode($secret, '000000');

        $this->assertFalse($result);
    }
}
