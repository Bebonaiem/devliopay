<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CreditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditTest extends TestCase
{
    use RefreshDatabase;

    private CreditService $creditService;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->creditService = new CreditService;
        $this->user = User::factory()->create(['balance' => 0]);
    }

    public function test_deposit_adds_credit(): void
    {
        $result = $this->creditService->deposit($this->user, 100.00, 'Test deposit');

        $this->assertTrue($result);
        $this->assertEquals(100.00, $this->user->fresh()->balance);
    }

    public function test_withdraw_deducts_credit(): void
    {
        $this->creditService->deposit($this->user, 100.00);
        $result = $this->creditService->withdraw($this->user, 50.00, 'Test withdrawal');

        $this->assertTrue($result);
        $this->assertEquals(50.00, $this->user->fresh()->balance);
    }

    public function test_withdraw_fails_if_insufficient(): void
    {
        $result = $this->creditService->withdraw($this->user, 50.00);

        $this->assertFalse($result);
        $this->assertEquals(0, $this->user->fresh()->balance);
    }

    public function test_refund_adds_credit(): void
    {
        $result = $this->creditService->refund($this->user, 25.00);

        $this->assertTrue($result);
        $this->assertEquals(25.00, $this->user->fresh()->balance);
    }

    public function test_returns_correct_balance(): void
    {
        $this->creditService->deposit($this->user, 200.00);
        $this->creditService->withdraw($this->user, 50.00);

        $balance = $this->creditService->getBalance($this->user);
        $this->assertEquals(150.00, $balance);
    }
}
