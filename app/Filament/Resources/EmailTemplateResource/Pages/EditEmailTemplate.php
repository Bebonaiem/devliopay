<?php

namespace App\Filament\Resources\EmailTemplateResource\Pages;

use App\Filament\Resources\EmailTemplateResource;
use App\Models\Setting;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;

class EditEmailTemplate extends EditRecord
{
    protected static string $resource = EmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('sendPreview')
                ->label('Send Preview')
                ->icon('heroicon-m-paper-airplane')
                ->color('success')
                ->form([
                    Forms\Components\TextInput::make('preview_email')
                        ->label('Send to')
                        ->email()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $template = $this->record;
                    $testData = [
                        'name' => 'John Doe',
                        'email' => 'john@example.com',
                        'invoice_number' => 'INV-001',
                        'amount' => '49.99',
                        'due_date' => now()->addDays(7)->format('M d, Y'),
                        'date' => now()->format('M d, Y H:i'),
                        'product_name' => 'VPS Hosting Basic',
                        'ip_address' => '192.168.1.100',
                        'reason' => 'Payment overdue',
                        'old_status' => 'active',
                        'new_status' => 'suspended',
                        'ticket_id' => '1234',
                        'subject' => 'Server is down',
                        'message' => 'Hello, my server appears to be offline. Can you help?',
                        'order_number' => 'ORD-001',
                        'status' => 'completed',
                        'type' => 'deposit',
                        'old_balance' => '10.00',
                        'new_balance' => '59.99',
                        'days_overdue' => '5',
                        'admin_name' => 'Admin',
                        'customer_name' => 'John Doe',
                        'customer_email' => 'john@example.com',
                        'gateway' => 'Stripe',
                        'ticket_number' => '1234',
                        'priority' => 'high',
                        'url' => url('/client'),
                    ];

                    $renderedBody = $template->renderBodyOnly($testData);
                    $renderedSubject = $template->renderSubject($testData);

                    $companyName = Setting::get('company_name', config('app.name', 'DevlioPay'));

                    $fullHtml = view('emails.layout', [
                        'company_name' => $companyName,
                        'company_address' => Setting::get('company_address', ''),
                        'subject' => $renderedSubject,
                        'title' => $template->name,
                        'actionUrl' => url('/client'),
                        'actionText' => 'View Dashboard',
                        'slot' => $renderedBody,
                    ])->render();

                    Mail::raw($fullHtml, function ($message) use ($data, $renderedSubject, $fromAddress, $fromName) {
                        $message->to($data['preview_email'])
                            ->from(Setting::get('mail_from_address', config('mail.from.address')), Setting::get('mail_from_name', config('mail.from.name')))
                            ->subject('[Preview] '.$renderedSubject);
                    });

                    Notification::make()
                        ->title('Preview sent')
                        ->body('Template preview sent to '.$data['preview_email'])
                        ->success()
                        ->send();
                }),
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Delete Record')
                ->modalDescription('Are you sure you want to delete this record? This action cannot be undone.'),
        ];
    }
}
