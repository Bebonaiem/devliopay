<?php

namespace App\Notifications;

use App\Models\TicketMessage;
use App\Models\TicketThread;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketReply extends Notification
{
    use Queueable;
    use Traits\UsesEmailTemplate;

    public array $via = ['mail', 'database'];

    public function __construct(
        public TicketThread $ticket,
        public TicketMessage $message,
    ) {}

    public function via(object $notifiable): array
    {
        return $this->via;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = $notifiable->is_admin
            ? route('filament.admin.resources.ticket-threads.edit', $this->ticket)
            : route('client.tickets.show', $this->ticket);

        return $this->buildMailMessage(
            data: [
                'name' => $notifiable->name,
                'ticket_id' => $this->ticket->number,
                'subject' => $this->ticket->subject,
                'message' => $this->message->message,
                'url' => $url,
            ],
            fallbackSubject: 'Re: '.$this->ticket->subject,
            fallbackGreeting: 'Hello '.$notifiable->name.'!',
            fallbackLines: [
                'You have received a reply to your support ticket.',
                'Ticket: '.$this->ticket->number,
                'Subject: '.$this->ticket->subject,
                'Message:',
                $this->message->message,
            ],
            fallbackActionUrl: $url,
            fallbackActionText: 'View Ticket',
        );
    }

    public function toArray(object $notifiable): array
    {
        $url = $notifiable->is_admin
            ? route('filament.admin.resources.ticket-threads.edit', $this->ticket)
            : route('client.tickets.show', $this->ticket);

        return [
            'title' => 'Ticket Reply',
            'message' => 'New reply on ticket #'.$this->ticket->number.': '.$this->ticket->subject,
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->number,
            'subject' => $this->ticket->subject,
            'message_body' => $this->message->message,
            'from_staff' => $this->message->is_staff,
            'url' => $url,
        ];
    }
}
