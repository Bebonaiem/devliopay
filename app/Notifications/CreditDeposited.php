<?php

namespace App\Notifications;

use App\Models\CreditTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CreditDeposited extends Notification
{
    use Queueable;
    use Traits\UsesEmailTemplate;

    public array $via = ['mail', 'database'];

    public function __construct(
        public CreditTransaction $transaction,
    ) {}

    public function via(object $notifiable): array
    {
        return $this->via;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $balance = number_format((float) $notifiable->balance, 2);

        return $this->buildMailMessage(
            data: [
                'name' => $notifiable->name,
                'amount' => number_format($this->transaction->amount, 2),
                'type' => ucfirst($this->transaction->type),
                'type_label' => ucfirst($this->transaction->type),
                'old_balance' => number_format((float) $notifiable->balance - $this->transaction->amount, 2),
                'new_balance' => $balance,
                'url' => route('client.credits.index'),
            ],
            fallbackSubject: ucfirst($this->transaction->type).' - $'.number_format($this->transaction->amount, 2),
            fallbackGreeting: 'Hello '.$notifiable->name.'!',
            fallbackLines: [
                'Your credit balance has been updated.',
                'Type: '.ucfirst($this->transaction->type),
                'Amount: $'.number_format($this->transaction->amount, 2),
                'New Balance: $'.$balance,
            ],
            fallbackActionUrl: route('client.credits.index'),
            fallbackActionText: 'View Credits',
        );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Credit '.ucfirst($this->transaction->type),
            'message' => '$'.number_format($this->transaction->amount, 2).' '.$this->transaction->type.'. New balance: $'.number_format($this->transaction->balance_after ?? 0, 2),
            'transaction_id' => $this->transaction->id,
            'amount' => $this->transaction->amount,
            'type' => $this->transaction->type,
            'url' => route('client.credits.index'),
        ];
    }
}
