<?php

namespace App\Notifications;

use App\Models\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;
    use Traits\UsesEmailTemplate;

    public array $via = ['mail', 'database'];

    public function __construct(
        public Service $service,
        public string $oldStatus,
        public string $newStatus,
    ) {}

    public function via(object $notifiable): array
    {
        return $this->via;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = match ($this->newStatus) {
            'active' => 'Your service has been activated',
            'suspended' => 'Your service has been suspended',
            'terminated' => 'Your service has been terminated',
            default => 'Your service status has been updated',
        };

        $body = match ($this->newStatus) {
            'active' => 'Your '.($this->service->product->name ?? 'service').' is now active and ready to use.',
            'suspended' => 'Your '.($this->service->product->name ?? 'service').' has been suspended due to non-payment. Please update your payment method to reactivate.',
            'terminated' => 'Your '.($this->service->product->name ?? 'service').' has been permanently terminated.',
            default => 'Your service status has been changed from '.$this->oldStatus.' to '.$this->newStatus.'.',
        };

        return $this->buildMailMessage(
            data: [
                'name' => $notifiable->name,
                'product_name' => $this->service->product->name ?? 'service',
                'old_status' => ucfirst($this->oldStatus),
                'new_status' => ucfirst($this->newStatus),
                'new_status_label' => $subject,
                'url' => route('client.services.show', $this->service->id),
            ],
            fallbackSubject: $subject,
            fallbackGreeting: 'Hello '.$notifiable->name,
            fallbackLines: [
                $body,
                'If you have any questions, please open a support ticket.',
            ],
            fallbackActionUrl: route('client.services.show', $this->service->id),
            fallbackActionText: 'View Service',
        );
    }

    public function toArray(object $notifiable): array
    {
        $productName = $this->service->product->name ?? 'service';

        $message = match ($this->newStatus) {
            'active' => "Your {$productName} is now active and ready to use.",
            'suspended' => "Your {$productName} has been suspended due to non-payment.",
            'terminated' => "Your {$productName} has been permanently terminated.",
            default => "Your service status changed from {$this->oldStatus} to {$this->newStatus}.",
        };

        return [
            'title' => 'Service '.ucfirst($this->newStatus),
            'message' => $message,
            'service_id' => $this->service->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'url' => route('client.services.show', $this->service->id),
        ];
    }
}
