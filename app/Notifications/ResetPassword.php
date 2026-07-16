<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPassword extends Notification
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
            fallbackSubject: 'Reset Your Password',
            fallbackGreeting: 'Password Reset Request',
            fallbackLines: [
                'We received a request to reset your password.',
                'Click the button below to set a new password.',
                'This link will expire in 60 minutes.',
                'If you did not request a password reset, you can safely ignore this email.',
            ],
            fallbackActionUrl: $this->url,
            fallbackActionText: 'Reset Password',
        );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Password Reset',
            'message' => 'A password reset was requested for your account.',
            'url' => $this->url,
        ];
    }
}
