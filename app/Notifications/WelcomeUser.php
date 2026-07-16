<?php

namespace App\Notifications;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeUser extends Notification
{
    use Queueable;
    use Traits\UsesEmailTemplate;

    public array $via = ['mail', 'database'];

    public function __construct(
        public User $user,
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
                'email' => $notifiable->email,
                'url' => route('client.dashboard'),
            ],
            fallbackSubject: 'Welcome to '.Setting::get('company_name', config('app.name', 'DevlioPay')),
            fallbackGreeting: 'Welcome, '.$notifiable->name.'!',
            fallbackLines: [
                'Your account has been created successfully.',
                'From your dashboard you can order services, manage invoices, and open support tickets.',
            ],
            fallbackActionUrl: route('client.dashboard'),
            fallbackActionText: 'Go to Dashboard',
        );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Welcome!',
            'message' => 'Your account has been created. Welcome to '.Setting::get('company_name', config('app.name', 'DevlioPay')).'!',
            'url' => route('client.dashboard'),
        ];
    }
}
