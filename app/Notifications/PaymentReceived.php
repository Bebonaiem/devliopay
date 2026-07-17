<?php

namespace App\Notifications;

use App\Models\Currency;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceived extends Notification
{
    use Queueable;
    use Traits\UsesEmailTemplate;

    public array $via = ['mail', 'database'];

    public function __construct(
        public Invoice $invoice,
        public float $amount,
    ) {}

    public function via(object $notifiable): array
    {
        return $this->via;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $symbol = Currency::defaultSymbol();

        return $this->buildMailMessage(
            data: [
                'name' => $notifiable->name,
                'invoice_number' => $this->invoice->number,
                'currency_symbol' => $symbol,
                'amount' => number_format($this->amount, 2),
                'date' => now()->format('M d, Y H:i'),
                'url' => route('client.invoices.show', $this->invoice),
            ],
            fallbackSubject: 'Payment Received - Invoice #'.$this->invoice->number,
            fallbackGreeting: 'Hello '.$notifiable->name.'!',
            fallbackLines: [
                'We have received your payment for Invoice #'.$this->invoice->number,
                'Amount Paid: '.$symbol.number_format($this->amount, 2),
                'Payment Date: '.now()->format('M d, Y H:i'),
                'Thank you for your payment!',
            ],
            fallbackActionUrl: route('client.invoices.show', $this->invoice),
            fallbackActionText: 'View Invoice',
        );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Payment Received',
            'message' => Currency::defaultSymbol().number_format($this->amount, 2).' payment received for Invoice #'.$this->invoice->number,
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->number,
            'amount' => $this->amount,
            'url' => route('client.invoices.show', $this->invoice),
        ];
    }
}
