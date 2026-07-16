<?php

namespace App\Notifications;

use App\Models\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceActivated extends Notification
{
    use Queueable;
    use Traits\UsesEmailTemplate;

    public array $via = ['mail', 'database'];

    public function __construct(
        public Service $service,
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
                'ip_address' => $this->service->server_properties['ip_address'] ?? 'Pending',
                'url' => route('client.services.show', $this->service),
            ],
            fallbackSubject: 'Service Activated',
            fallbackGreeting: 'Hello '.$notifiable->name.'!',
            fallbackLines: [
                'Your service has been activated and is ready to use!',
                'Service: '.($this->service->product->name ?? 'N/A'),
                'IP: '.($this->service->server_properties['ip_address'] ?? 'Pending'),
            ],
            fallbackActionUrl: route('client.services.show', $this->service),
            fallbackActionText: 'View Service',
        );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Service Activated',
            'message' => 'Your '.($this->service->product->name ?? 'service').' is now active and ready to use.',
            'service_id' => $this->service->id,
            'url' => route('client.services.show', $this->service),
        ];
    }
}
