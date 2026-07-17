<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class GeneralSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Configuration';

    protected static ?string $navigationLabel = 'General Settings';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'General Settings';

    protected static string $view = 'filament.pages.settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'company_name' => Setting::get('company_name', config('app.name', 'DevlioPay')),
            'company_email' => Setting::get('company_email', ''),
            'company_url' => Setting::get('company_url') ?: url('/'),
            'company_phone' => Setting::get('company_phone', ''),
            'company_address' => Setting::get('company_address', ''),
            'company_logo' => Setting::get('company_logo', ''),
            'company_logo_display' => Setting::get('company_logo_display', 'name_only'),
            'company_favicon' => Setting::get('company_favicon', ''),
            'company_og_image' => Setting::get('company_og_image', ''),
            'company_footer_text' => Setting::get('company_footer_text', ''),
            'default_currency' => Setting::get('default_currency', 'USD'),
            'default_currency_symbol' => Setting::get('default_currency_symbol', '$'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Company Information')
                    ->schema([
                        Forms\Components\TextInput::make('company_name')
                            ->label('Company Name')
                            ->required(),
                        Forms\Components\TextInput::make('company_email')
                            ->label('Support Email')
                            ->email()
                            ->required(),
                        Forms\Components\TextInput::make('company_url')
                            ->label('Website URL')
                            ->url()
                            ->default(url('/')),
                        Forms\Components\TextInput::make('company_phone')
                            ->label('Phone Number'),
                        Forms\Components\Textarea::make('company_address')
                            ->label('Company Address'),
                    ])->columns(2),

                Forms\Components\Section::make('Branding')
                    ->schema([
                        Forms\Components\FileUpload::make('company_logo')
                            ->label('Logo')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('company')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('company_logo_display')
                            ->label('Logo Display')
                            ->options([
                                'logo_only' => 'Logo Only',
                                'logo_and_name' => 'Logo + Company Name',
                                'name_only' => 'Company Name Only',
                            ])
                            ->default('name_only'),
                        Forms\Components\FileUpload::make('company_favicon')
                            ->label('Favicon')
                            ->image()
                            ->disk('public')
                            ->directory('company')
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('company_og_image')
                            ->label('OG Image (1200x630 recommended)')
                            ->image()
                            ->disk('public')
                            ->directory('company')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('company_footer_text')
                            ->label('Footer text shown to clients'),
                    ])->columns(2),

                Forms\Components\Section::make('Currency')
                    ->schema([
                        Forms\Components\Select::make('default_currency')
                            ->label('Currency')
                            ->options([
                                'USD' => 'USD ($)',
                                'EUR' => 'EUR (€)',
                                'GBP' => 'GBP (£)',
                                'CAD' => 'CAD (C$)',
                                'AUD' => 'AUD (A$)',
                                'BRL' => 'BRL (R$)',
                                'INR' => 'INR (₹)',
                                'JPY' => 'JPY (¥)',
                                'CNY' => 'CNY (¥)',
                                'KRW' => 'KRW (₩)',
                                'RUB' => 'RUB (₽)',
                                'TRY' => 'TRY (₺)',
                                'MXN' => 'MXN (MX$)',
                                'SEK' => 'SEK (kr)',
                                'NOK' => 'NOK (kr)',
                                'DKK' => 'DKK (kr)',
                                'PLN' => 'PLN (zł)',
                                'CHF' => 'CHF (CHF)',
                                'ZAR' => 'ZAR (R)',
                                'NGN' => 'NGN (₦)',
                                'PHP' => 'PHP (₱)',
                                'IDR' => 'IDR (Rp)',
                                'MYR' => 'MYR (RM)',
                                'THB' => 'THB (฿)',
                                'AED' => 'AED (د.إ)',
                                'SAR' => 'SAR (﷼)',
                                'QAR' => 'QAR (﷼)',
                                'PKR' => 'PKR (₨)',
                                'BDT' => 'BDT (৳)',
                                'EGP' => 'EGP (E£)',
                                'VND' => 'VND (₫)',
                                'TWD' => 'TWD (NT$)',
                                'HKD' => 'HKD (HK$)',
                                'SGD' => 'SGD (S$)',
                                'NZD' => 'NZD (NZ$)',
                                'CLP' => 'CLP (CL$)',
                                'COP' => 'COP (COL$)',
                                'ARS' => 'ARS (AR$)',
                                'PEN' => 'PEN (S/)',
                                'UAH' => 'UAH (₴)',
                                'CZK' => 'CZK (Kč)',
                                'HUF' => 'HUF (Ft)',
                                'RON' => 'RON (lei)',
                                'BGN' => 'BGN (лв)',
                                'HRK' => 'HRK (kn)',
                                'ISK' => 'ISK (kr)',
                                'JPY' => 'JPY (¥)',
                            ])
                            ->default('USD')
                            ->reactive()
                            ->afterStateUpdated(fn ($state, \Filament\Forms\Set $set) => $set('default_currency_symbol', self::symbols()[$state] ?? '$')),
                        Forms\Components\TextInput::make('default_currency_symbol')
                            ->label('Symbol')
                            ->default('$')
                            ->maxLength(5)
                            ->disabled(),
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

        Setting::flushCache();

        if (!empty($data['company_url'])) {
            $this->syncUrlToEnv($data['company_url']);
        }

        Notification::make()
            ->title('General settings saved')
            ->success()
            ->send();
    }

    private function syncUrlToEnv(string $url): void
    {
        $url = rtrim($url, '/');
        $envFile = base_path('.env');

        if (!file_exists($envFile)) {
            return;
        }

        $content = file_get_contents($envFile);

        if (str_contains($content, 'APP_URL=')) {
            $content = preg_replace("/^APP_URL=.*/m", "APP_URL={$url}", $content);
        } else {
            $content .= "\nAPP_URL={$url}";
        }

        if (str_contains($content, 'APP_DOMAIN=')) {
            $domain = parse_url($url, PHP_URL_HOST) ?? '';
            $content = preg_replace("/^APP_DOMAIN=.*/m", "APP_DOMAIN={$domain}", $content);
        } else {
            $domain = parse_url($url, PHP_URL_HOST) ?? '';
            $content .= "\nAPP_DOMAIN={$domain}";
        }

        file_put_contents($envFile, $content);
    }

    private static function symbols(): array
    {
        return [
            'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'CAD' => 'C$', 'AUD' => 'A$',
            'BRL' => 'R$', 'INR' => '₹', 'JPY' => '¥', 'CNY' => '¥', 'KRW' => '₩',
            'RUB' => '₽', 'TRY' => '₺', 'MXN' => 'MX$', 'SEK' => 'kr', 'NOK' => 'kr',
            'DKK' => 'kr', 'PLN' => 'zł', 'CHF' => 'CHF', 'ZAR' => 'R', 'NGN' => '₦',
            'PHP' => '₱', 'IDR' => 'Rp', 'MYR' => 'RM', 'THB' => '฿', 'AED' => 'د.إ',
            'SAR' => '﷼', 'QAR' => '﷼', 'PKR' => '₨', 'BDT' => '৳', 'EGP' => 'E£',
            'VND' => '₫', 'TWD' => 'NT$', 'HKD' => 'HK$', 'SGD' => 'S$', 'NZD' => 'NZ$',
            'CLP' => 'CL$', 'COP' => 'COL$', 'ARS' => 'AR$', 'PEN' => 'S/', 'UAH' => '₴',
            'CZK' => 'Kč', 'HUF' => 'Ft', 'RON' => 'lei', 'BGN' => 'лв', 'HRK' => 'kn',
            'ISK' => 'kr',
        ];
    }
}
