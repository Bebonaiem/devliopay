<?php

namespace App\Services\Gateways;

use App\Models\Invoice;
use App\Models\User;

interface GatewayInterface
{
    public function getName(): string;

    public function getDisplayName(): string;

    public function isConfigured(): bool;

    public function charge(Invoice $invoice, array $params = []): array;

    public function refund(string $transactionId, float $amount): array;

    public function getTransactionStatus(string $transactionId): string;

    public function createCheckoutUrl(Invoice $invoice, array $params = []): ?string;

    public function createCreditDepositCheckoutUrl(User $user, float $amount): ?string;

    public function handleWebhook(array $payload): void;
}
