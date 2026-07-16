<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\Gateways\StripeGateway;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class PaymentGatewaySettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Payment Gateways';

    protected static ?string $title = 'Payment Gateway Settings';

    protected static string $view = 'filament.pages.settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'stripe_enabled' => Setting::get('stripe_enabled', false),
            'stripe_restricted_key' => Setting::get('stripe_restricted_key', ''),
            'stripe_secret_key' => Setting::get('stripe_secret_key', ''),
            'stripe_publishable_key' => Setting::get('stripe_publishable_key', ''),
            'stripe_webhook_secret' => Setting::get('stripe_webhook_secret', ''),
            'paypal_enabled' => Setting::get('paypal_enabled', false),
            'paypal_client_id' => Setting::get('paypal_client_id', ''),
            'paypal_client_secret' => Setting::get('paypal_client_secret', ''),
            'paypal_mode' => Setting::get('paypal_mode', 'sandbox'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Stripe')
                    ->description('Accept credit/debit card payments via Stripe. Use a Restricted API Key for best security.')
                    ->schema([
                        Forms\Components\Toggle::make('stripe_enabled')
                            ->label('Enable Stripe')
                            ->default(false),
                        Forms\Components\TextInput::make('stripe_restricted_key')
                            ->label('Restricted API Key')
                            ->helperText('Recommended: Use a restricted key (rk_test_... or rk_live_...) with permissions: Checkout Sessions (Write), Payment Links (Write), Customers (Read/Write), Webhooks (Read/Write).')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn ($state) => filled($state))
                            ->visible(fn ($get) => $get('stripe_enabled')),
                        Forms\Components\TextInput::make('stripe_secret_key')
                            ->label('Secret Key (Fallback)')
                            ->helperText('Only used if Restricted API Key is empty. Prefer using a Restricted Key above.')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn ($state) => filled($state))
                            ->visible(fn ($get) => $get('stripe_enabled')),
                        Forms\Components\TextInput::make('stripe_publishable_key')
                            ->label('Publishable Key')
                            ->helperText('Your publishable key (pk_test_... or pk_live_...). Safe to use in frontend code.')
                            ->visible(fn ($get) => $get('stripe_enabled')),
                        Forms\Components\TextInput::make('stripe_webhook_secret')
                            ->label('Webhook Signing Secret')
                            ->helperText('Used to verify webhook signatures. Click "Generate" to create one, then paste it into your Stripe Dashboard webhook endpoint settings.')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn ($state) => filled($state))
                            ->visible(fn ($get) => $get('stripe_enabled')),
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('generateWebhookSecret')
                                ->label('Generate Webhook Secret')
                                ->icon('heroicon-m-arrow-path')
                                ->color('success')
                                ->requiresConfirmation()
                                ->modalHeading('Generate Webhook Secret')
                                ->modalDescription('This will generate a new webhook signing secret and save it. Use this value in your Stripe Dashboard webhook endpoint settings.')
                                ->action(function (Forms\Get $get, Forms\Set $set) {
                                    $secret = StripeGateway::generateWebhookSecret();
                                    $set('stripe_webhook_secret', $secret);
                                    Notification::make()
                                        ->title('Webhook secret generated')
                                        ->body('Copy this value to your Stripe Dashboard webhook endpoint settings as the "Signing secret".')
                                        ->success()
                                        ->duration(10000)
                                        ->send();
                                }),
                        ])
                            ->visible(fn ($get) => $get('stripe_enabled')),
                    ])->columns(2),

                Forms\Components\Section::make('PayPal')
                    ->schema([
                        Forms\Components\Toggle::make('paypal_enabled')
                            ->label('Enable PayPal')
                            ->default(false),
                        Forms\Components\TextInput::make('paypal_client_id')
                            ->label('Client ID')
                            ->visible(fn ($get) => $get('paypal_enabled')),
                        Forms\Components\TextInput::make('paypal_client_secret')
                            ->label('Client Secret')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn ($state) => filled($state))
                            ->visible(fn ($get) => $get('paypal_enabled')),
                        Forms\Components\Select::make('paypal_mode')
                            ->label('Mode')
                            ->options([
                                'sandbox' => 'Sandbox (Testing)',
                                'live' => 'Live (Production)',
                            ])
                            ->default('sandbox')
                            ->visible(fn ($get) => $get('paypal_enabled')),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        Notification::make()
            ->title('Payment gateway settings saved')
            ->success()
            ->send();
    }
}
