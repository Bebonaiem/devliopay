<?php

namespace App\Notifications;

use App\Models\Currency;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceCreated extends Notification
{
    use Queueable;
    use Traits\UsesEmailTemplate;

    public array $via = ['mail', 'database'];

    public function __construct(
        public Invoice $invoice,
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
                'amount' => number_format($this->invoice->total, 2),
                'due_date' => $this->invoice->due_at->format('M d, Y'),
                'url' => route('client.invoices.show', $this->invoice),
            ],
            fallbackSubject: 'Invoice #'.$this->invoice->number,
            fallbackGreeting: 'Hello '.$notifiable->name.'!',
            fallbackLines: [
                'A new invoice has been generated for your account.',
                'Invoice Number: '.$this->invoice->number,
                'Amount Due: '.$symbol.number_format($this->invoice->total, 2),
                'Due Date: '.$this->invoice->due_at->format('M d, Y'),
                'Please make payment before the due date to avoid service interruption.',
            ],
            fallbackActionUrl: route('client.invoices.show', $this->invoice),
            fallbackActionText: 'View Invoice',
        );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Invoice Created',
            'message' => 'Invoice #'.$this->invoice->number.' for '.Currency::defaultSymbol().number_format($this->invoice->total, 2).' has been generated.',
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->number,
            'amount' => $this->invoice->total,
            'url' => route('client.invoices.show', $this->invoice),
        ];
    }
}
