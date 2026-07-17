<?php

namespace App\Notifications;

use App\Models\Currency;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminPaymentReceived extends Notification
{
    use Queueable;
    use Traits\UsesEmailTemplate;

    public array $via = ['mail'];

    public function __construct(
        public Transaction $transaction,
    ) {}

    public function via(object $notifiable): array
    {
        return $this->via;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $invoice = $this->transaction->invoice;
        $user = $this->transaction->user;

        $symbol = Currency::defaultSymbol();

        return $this->buildMailMessage(
            data: [
                'admin_name' => $notifiable->name,
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'currency_symbol' => $symbol,
                'amount' => number_format($this->transaction->amount, 2),
                'invoice_number' => $invoice ? $invoice->number : 'N/A',
                'gateway' => $this->transaction->gateway,
            ],
            fallbackSubject: 'Payment Received - '.$symbol.number_format($this->transaction->amount, 2),
            fallbackGreeting: 'Payment Received',
            fallbackLines: [
                'A new payment has been received.',
                'Amount: '.$symbol.number_format($this->transaction->amount, 2),
                "Customer: {$user->name} ({$user->email})",
                'Invoice: #'.($invoice ? $invoice->number : 'N/A'),
                'Method: '.ucfirst($this->transaction->gateway),
            ],
            fallbackActionUrl: $invoice ? url("/admin/invoices/{$invoice->id}/edit") : '',
            fallbackActionText: 'View Transaction',
        );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Payment Received',
            'message' => Currency::defaultSymbol().number_format($this->transaction->amount, 2).' payment received from '.$this->transaction->user->name,
            'transaction_id' => $this->transaction->id,
            'amount' => $this->transaction->amount,
        ];
    }
}
