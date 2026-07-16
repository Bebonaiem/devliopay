<?php

namespace App\Services;

use App\Models\CreditTransaction;
use App\Models\User;
use App\Notifications\CreditDeposited;
use Illuminate\Support\Facades\DB;

class CreditService
{
    public function getBalance(User $user): float
    {
        return (float) $user->balance;
    }

    public function deposit(User $user, float $amount, ?string $description = null): bool
    {
        if ($amount <= 0) {
            return false;
        }

        $transaction = DB::transaction(function () use ($user, $amount, $description) {
            $freshUser = User::lockForUpdate()->find($user->id);
            $freshUser->increment('balance', $amount);

            return CreditTransaction::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'type' => 'deposit',
                'description' => $description ?? 'Credit deposit',
            ]);
        });

        if ($transaction) {
            try {
                $user->notify(new CreditDeposited($transaction));
            } catch (\Exception $e) {
                // notification failure shouldn't block deposit
            }
        }

        return $transaction ? true : false;
    }

    public function withdraw(User $user, float $amount, ?string $description = null): bool
    {
        if ($amount <= 0) {
            return false;
        }

        return DB::transaction(function () use ($user, $amount, $description) {
            $freshUser = User::lockForUpdate()->find($user->id);
            if ($freshUser->balance < $amount) {
                return false;
            }

            $freshUser->decrement('balance', $amount);

            CreditTransaction::create([
                'user_id' => $user->id,
                'amount' => -$amount,
                'type' => 'withdrawal',
                'description' => $description ?? 'Credit withdrawal',
            ]);

            return true;
        }) ?? false;
    }

    public function refund(User $user, float $amount, ?string $description = null): bool
    {
        if ($amount <= 0) {
            return false;
        }

        return DB::transaction(function () use ($user, $amount, $description) {
            $freshUser = User::lockForUpdate()->find($user->id);
            $freshUser->increment('balance', $amount);

            CreditTransaction::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'type' => 'refund',
                'description' => $description ?? 'Credit refund',
            ]);

            return true;
        }) ?? false;
    }

    public function adjust(User $user, float $amount, ?string $description = null): bool
    {
        return DB::transaction(function () use ($user, $amount, $description) {
            $freshUser = User::lockForUpdate()->find($user->id);
            $freshUser->increment('balance', $amount);

            CreditTransaction::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'type' => 'adjustment',
                'description' => $description ?? 'Balance adjustment',
            ]);

            return true;
        }) ?? false;
    }

    public function getHistory(User $user, int $limit = 50)
    {
        return CreditTransaction::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
