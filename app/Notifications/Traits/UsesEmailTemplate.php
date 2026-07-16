<?php

namespace App\Notifications\Traits;

use App\Models\EmailTemplate;
use App\Models\Setting;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Str;

trait UsesEmailTemplate
{
    private static array $slugMap = [
        'InvoiceCreated' => 'invoice-created',
        'PaymentReceived' => 'payment-received',
        'ServiceActivated' => 'service-activated',
        'ServiceSuspended' => 'service-suspended',
        'ServiceStatusChanged' => 'service-status-changed',
        'TicketReply' => 'ticket-reply',
        'InvoiceOverdue' => 'invoice-overdue',
        'WelcomeUser' => 'welcome',
        'OrderCompleted' => 'order-completed',
        'CreditDeposited' => 'credit-deposit',
        'VerifyEmail' => 'verify-email',
        'ResetPassword' => 'password-reset',
        'AdminNewOrder' => 'admin-new-order',
        'AdminNewTicket' => 'admin-new-ticket',
        'AdminPaymentReceived' => 'admin-payment-received',
    ];

    public function getTemplateSlug(): string
    {
        $class = class_basename(static::class);

        return self::$slugMap[$class] ?? Str::slug($class);
    }

    public function buildMailMessage(array $data, string $fallbackSubject = '', string $fallbackGreeting = '', array $fallbackLines = [], string $fallbackActionUrl = '', string $fallbackActionText = ''): MailMessage
    {
        $template = EmailTemplate::enabled()->where('slug', $this->getTemplateSlug())->first();

        if ($template) {
            $renderedBody = $template->renderBodyOnly($data);
            $renderedSubject = $template->renderSubject($data);

            $companyName = Setting::get('company_name', config('app.name', 'DevlioPay'));
            $companyAddress = Setting::get('company_address', '');

            $mailData = array_merge($data, [
                'company_name' => $companyName,
                'company_address' => $companyAddress,
                'subject' => $renderedSubject,
                'title' => $template->name,
                'actionUrl' => $fallbackActionUrl ?: ($data['url'] ?? ''),
                'actionText' => $fallbackActionText ?: 'View',
                'slot' => $renderedBody,
            ]);

            $mail = (new MailMessage)
                ->subject($renderedSubject)
                ->view('emails.layout', $mailData);

            return $mail;
        }

        $mail = (new MailMessage)
            ->subject($fallbackSubject)
            ->greeting($fallbackGreeting);

        foreach ($fallbackLines as $line) {
            $mail->line($line);
        }

        if ($fallbackActionUrl) {
            $mail->action($fallbackActionText ?: 'View', $fallbackActionUrl);
        }

        return $mail;
    }
}
