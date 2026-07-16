<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceOverdue extends Notification
{
    use Queueable;
    use Traits\UsesEmailTemplate;

    public array $via = ['mail', 'database'];

    public function __construct(public Invoice $invoice) {}

    public function via(object $notifiable): array
    {
        return $this->via;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $daysOverdue = $this->invoice->due_at ? $this->invoice->due_at->diffInDays(now()) : 0;

        return $this->buildMailMessage(
            data: [
                'name' => $notifiable->name,
                'invoice_number' => $this->invoice->number,
                'amount' => number_format($this->invoice->total, 2),
                'due_date' => $this->invoice->due_at->format('M d, Y'),
                'days_overdue' => $daysOverdue,
                'url' => route('client.invoices.show', $this->invoice),
            ],
            fallbackSubject: "Overdue Invoice #{$this->invoice->number}",
            fallbackGreeting: 'Payment Overdue',
            fallbackLines: [
                "Your invoice #{$this->invoice->number} is {$daysOverdue} day(s) overdue.",
                'Amount Due: $'.number_format($this->invoice->total, 2),
                'Due Date: '.$this->invoice->due_at->format('M d, Y'),
                'Please pay as soon as possible to avoid service suspension.',
            ],
            fallbackActionUrl: route('client.invoices.show', $this->invoice),
            fallbackActionText: 'Pay Now',
        );
    }

    public function toArray(object $notifiable): array
    {
        $daysOverdue = $this->invoice->due_at ? $this->invoice->due_at->diffInDays(now()) : 0;

        return [
            'title' => 'Invoice Overdue',
            'message' => 'Invoice #'.$this->invoice->number.' is '.$daysOverdue.' day(s) overdue. Amount due: $'.number_format($this->invoice->total, 2),
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->number,
            'amount' => $this->invoice->total,
            'days_overdue' => $daysOverdue,
            'url' => route('client.invoices.show', $this->invoice),
        ];
    }
}
