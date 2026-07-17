<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mailer;

class MailSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Configuration';

    protected static ?string $navigationLabel = 'Mail Settings';

    protected static ?int $navigationSort = 6;

    protected static ?string $title = 'Mail Settings';

    protected static string $view = 'filament.pages.settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'mail_driver' => env('MAIL_DRIVER', env('MAIL_MAILER', 'smtp')),
            'mail_host' => env('MAIL_HOST', 'smtp.mailgun.org'),
            'mail_port' => env('MAIL_PORT', '587'),
            'mail_username' => env('MAIL_USERNAME', ''),
            'mail_password' => env('MAIL_PASSWORD', ''),
            'mail_encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'mail_from_address' => env('MAIL_FROM_ADDRESS', 'noreply@example.com'),
            'mail_from_name' => env('MAIL_FROM_NAME', Setting::get('company_name', config('app.name', 'DevlioPay'))),
            'test_email_to' => '',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Mail Configuration')
                    ->schema([
                        Forms\Components\Select::make('mail_driver')
                            ->label('Mail Driver')
                            ->options([
                                'smtp' => 'SMTP',
                                'sendmail' => 'Sendmail',
                                'mailgun' => 'Mailgun',
                                'ses' => 'SES',
                                'log' => 'Log',
                                'array' => 'Array',
                            ])
                            ->default('smtp'),
                        Forms\Components\TextInput::make('mail_host')
                            ->label('Mail Host')
                            ->default('smtp.mailgun.org'),
                        Forms\Components\TextInput::make('mail_port')
                            ->label('Mail Port')
                            ->numeric()
                            ->default('587'),
                        Forms\Components\TextInput::make('mail_username')
                            ->label('Mail Username'),
                        Forms\Components\TextInput::make('mail_password')
                            ->label('Mail Password')
                            ->password()
                            ->revealable(),
                        Forms\Components\Select::make('mail_encryption')
                            ->label('Mail Encryption')
                            ->options([
                                'tls' => 'TLS',
                                'ssl' => 'SSL',
                                '' => 'None',
                            ])
                            ->default('tls'),
                        Forms\Components\TextInput::make('mail_from_address')
                            ->label('From Address')
                            ->email()
                            ->default('noreply@example.com'),
                        Forms\Components\TextInput::make('mail_from_name')
                            ->label('From Name')
                            ->default(Setting::get('company_name', config('app.name', 'DevlioPay'))),
                    ])->columns(2),

                Forms\Components\Section::make('Test Email')
                    ->schema([
                        Forms\Components\TextInput::make('test_email_to')
                            ->label('Send test email to')
                            ->email(),
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('sendTestEmail')
                                ->label('Send Test Email')
                                ->icon('heroicon-m-paper-airplane')
                                ->color('primary')
                                ->requiresConfirmation()
                                ->modalHeading('Send Test Email')
                                ->modalDescription('This will send a test email to the address specified above using the current mail configuration.')
                                ->action(function (Forms\Get $get) {
                                    $to = $get('test_email_to');

                                    if (empty($to)) {
                                        Notification::make()
                                            ->title('Please enter an email address')
                                            ->warning()
                                            ->send();
                                        return;
                                    }

                                    $fromAddress = $get('mail_from_address');
                                    $fromName = $get('mail_from_name');

                                    $companyName = Setting::get('company_name', config('app.name', 'DevlioPay'));
                                    $testHtml = view('emails.layout', [
                                        'company_name' => $companyName,
                                        'company_address' => Setting::get('company_address', ''),
                                        'subject' => $companyName . ' Test Email',
                                        'title' => 'Test Email',
                                        'actionUrl' => '',
                                        'actionText' => '',
                                        'slot' => '<p style="margin:0 0 20px;font-size:16px;color:#e2e8f0;">Hello!</p><p style="margin:0 0 20px;font-size:15px;color:#94a3b8;">This is a test email from your '.$companyName.' mail system.</p><table width="100%" cellpadding="0" cellspacing="0" style="background:#0f172a;border-radius:12px;border:1px solid #334155;"><tr><td style="padding:24px;text-align:center;"><span style="color:#22c55e;font-size:18px;font-weight:700;">✅ Mail Configuration Working</span></td></tr></table><p style="margin:20px 0 0;font-size:14px;color:#94a3b8;">If you received this email, your mail settings are configured correctly.</p>',
                                    ])->render();

                                    Mail::send([], [], function ($message) use ($testHtml, $to, $fromAddress, $fromName, $companyName) {
                                        $message->to($to)
                                            ->from($fromAddress, $fromName)
                                            ->subject($companyName . ' Test Email')
                                            ->html($testHtml);
                                    });

                                    Notification::make()
                                        ->title('Test email sent')
                                        ->body('A test email has been sent to ' . $to)
                                        ->success()
                                        ->send();
                                }),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $envMap = [
            'mail_driver' => ['MAIL_MAILER', $data['mail_driver']],
            'mail_host' => ['MAIL_HOST', $data['mail_host']],
            'mail_port' => ['MAIL_PORT', $data['mail_port']],
            'mail_username' => ['MAIL_USERNAME', $data['mail_username']],
            'mail_password' => ['MAIL_PASSWORD', $data['mail_password']],
            'mail_encryption' => ['MAIL_ENCRYPTION', $data['mail_encryption']],
            'mail_from_address' => ['MAIL_FROM_ADDRESS', $data['mail_from_address']],
            'mail_from_name' => ['MAIL_FROM_NAME', $data['mail_from_name']],
        ];

        foreach ($envMap as $key => [$envKey, $value]) {
            $this->setEnvValue($envKey, $value);
        }

        config([
            'mail.mailers.smtp' => [
                'transport' => $data['mail_driver'],
                'host' => $data['mail_host'],
                'port' => $data['mail_port'],
                'encryption' => $data['mail_encryption'] ?: null,
                'username' => $data['mail_username'],
                'password' => $data['mail_password'],
            ],
            'mail.default' => $data['mail_driver'],
            'mail.from.address' => $data['mail_from_address'],
            'mail.from.name' => $data['mail_from_name'],
        ]);

        Notification::make()
            ->title('Mail settings saved')
            ->body('Configuration updated in .env and applied for this session. Restart the server to persist across reboots.')
            ->success()
            ->send();
    }

    private function setEnvValue(string $key, ?string $value): void
    {
        $envFile = base_path('.env');

        if (! file_exists($envFile)) {
            return;
        }

        $content = file_get_contents($envFile);

        if (str_contains($content, "{$key}=")) {
            $content = preg_replace(
                "/^{$key}=.*/m",
                "{$key}=" . addslashes($value),
                $content
            );
        } else {
            $content .= "\n{$key}=" . addslashes($value);
        }

        file_put_contents($envFile, $content);

        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
    }
}
