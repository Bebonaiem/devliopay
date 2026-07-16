<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmail extends Notification
{
    use Queueable;
    use Traits\UsesEmailTemplate;

    public array $via = ['mail'];

    public function __construct(
        public string $url,
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
                'url' => $this->url,
            ],
            fallbackSubject: 'Verify your email address',
            fallbackGreeting: 'Verify Your Email',
            fallbackLines: [
                'Please verify your email address by clicking the button below.',
                'This link will expire in 24 hours.',
                'If you did not create an account, no further action is required.',
            ],
            fallbackActionUrl: $this->url,
            fallbackActionText: 'Verify Email',
        );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Verify Email',
            'message' => 'Please verify your email address.',
            'url' => $this->url,
        ];
    }
}
