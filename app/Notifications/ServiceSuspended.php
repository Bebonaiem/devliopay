<?php

namespace App\Notifications;

use App\Models\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceSuspended extends Notification
{
    use Queueable;
    use Traits\UsesEmailTemplate;

    public array $via = ['mail', 'database'];

    public function __construct(
        public Service $service,
        public string $reason = 'Payment overdue',
    ) {}

    public function via(object $notifiable): array
    {
        return $this->via;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->buildMailMessage(
            data: [
                'name' => $notifiable->name,
                'product_name' => $this->service->product->name ?? 'N/A',
                'reason' => $this->reason,
                'url' => route('client.services.show', $this->service),
            ],
            fallbackSubject: 'Service Suspended',
            fallbackGreeting: 'Hello '.$notifiable->name.'!',
            fallbackLines: [
                'Your service has been suspended.',
                'Service: '.($this->service->product->name ?? 'N/A'),
                'Reason: '.$this->reason,
                'To reactivate your service, please pay the outstanding invoice.',
            ],
            fallbackActionUrl: route('client.services.show', $this->service),
            fallbackActionText: 'View Service',
        );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Service Suspended',
            'message' => 'Your '.($this->service->product->name ?? 'service').' has been suspended. Reason: '.$this->reason,
            'service_id' => $this->service->id,
            'reason' => $this->reason,
            'url' => route('client.services.show', $this->service),
        ];
    }
}
