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

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'General Settings';

    protected static ?string $title = 'General Settings';

    protected static string $view = 'filament.pages.settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'company_name' => Setting::get('company_name', config('app.name', 'DevlioPay')),
            'company_email' => Setting::get('company_email', ''),
            'company_url' => Setting::get('company_url', ''),
            'company_phone' => Setting::get('company_phone', ''),
            'company_address' => Setting::get('company_address', ''),
            'company_logo' => Setting::get('company_logo', ''),
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
                            ->url(),
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
                        Forms\Components\Textarea::make('company_footer_text')
                            ->label('Footer text shown to clients'),
                    ])->columns(2),

                Forms\Components\Section::make('Currency')
                    ->schema([
                        Forms\Components\TextInput::make('default_currency')
                            ->label('Currency Code')
                            ->default('USD')
                            ->maxLength(3),
                        Forms\Components\TextInput::make('default_currency_symbol')
                            ->label('Currency Symbol')
                            ->default('$')
                            ->maxLength(5),
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

        Notification::make()
            ->title('General settings saved')
            ->success()
            ->send();
    }
}
