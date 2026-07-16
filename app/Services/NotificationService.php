<?php

namespace App\Services;

use App\Models\ClientNotification;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    private const TYPE_MAP = [
        \App\Notifications\InvoiceCreated::class => 'invoice_created',
        \App\Notifications\PaymentReceived::class => 'payment_received',
        \App\Notifications\InvoiceOverdue::class => 'invoice_overdue',
        \App\Notifications\ServiceActivated::class => 'service_activated',
        \App\Notifications\ServiceSuspended::class => 'service_suspended',
        \App\Notifications\ServiceStatusChanged::class => 'service_status_changed',
        \App\Notifications\TicketReply::class => 'ticket_reply',
        \App\Notifications\WelcomeUser::class => 'welcome_user',
        \App\Notifications\OrderCompleted::class => 'order_completed',
        \App\Notifications\CreditDeposited::class => 'credit_deposited',
        \App\Notifications\VerifyEmail::class => 'verify_email',
        \App\Notifications\ResetPassword::class => 'reset_password',
    ];

    public function notify(User $user, $notification): void
    {
        $type = self::TYPE_MAP[get_class($notification)] ?? null;

        if ($type) {
            $prefs = ClientNotification::getForUser($user->id, $type);

            $channels = [];
            if ($prefs->email_enabled) {
                $channels[] = 'mail';
            }
            if ($prefs->dashboard_enabled) {
                $channels[] = 'database';
            }

            if (empty($channels)) {
                return;
            }

            $notification->via = $channels;
            $user->notify($notification);
        } else {
            $user->notify($notification);
        }
    }

    public function notifyAdmins($notification): void
    {
        $admins = User::where('is_admin', true)->get();
        Notification::send($admins, $notification);
    }
}
