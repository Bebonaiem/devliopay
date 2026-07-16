<?php

namespace App\Notifications;

use App\Models\TicketThread;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminNewTicket extends Notification
{
    use Queueable;
    use Traits\UsesEmailTemplate;

    public array $via = ['mail'];

    public function __construct(
        public TicketThread $ticket,
    ) {}

    public function via(object $notifiable): array
    {
        return $this->via;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $user = $this->ticket->user;

        return $this->buildMailMessage(
            data: [
                'admin_name' => $notifiable->name,
                'ticket_number' => $this->ticket->number,
                'subject' => $this->ticket->subject,
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'priority' => $this->ticket->priority,
            ],
            fallbackSubject: "New Ticket #{$this->ticket->number}",
            fallbackGreeting: 'New Support Ticket',
            fallbackLines: [
                'A new support ticket has been opened.',
                "Ticket: #{$this->ticket->number} - {$this->ticket->subject}",
                "Customer: {$user->name} ({$user->email})",
                'Priority: '.ucfirst($this->ticket->priority),
            ],
            fallbackActionUrl: url("/admin/ticket-threads/{$this->ticket->id}/edit"),
            fallbackActionText: 'View Ticket',
        );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Ticket',
            'message' => 'Ticket #'.$this->ticket->number.': '.$this->ticket->subject,
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->number,
        ];
    }
}
