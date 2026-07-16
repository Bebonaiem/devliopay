<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class LegalPages extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Legal Pages';

    protected static ?string $title = 'Legal Pages';

    protected static string $view = 'filament.pages.settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'legal_terms_title' => Setting::get('legal_terms_title', 'Terms of Service'),
            'legal_terms_content' => Setting::get('legal_terms_content', ''),
            'legal_privacy_title' => Setting::get('legal_privacy_title', 'Privacy Policy'),
            'legal_privacy_content' => Setting::get('legal_privacy_content', ''),
            'legal_sla_title' => Setting::get('legal_sla_title', 'Service Level Agreement'),
            'legal_sla_content' => Setting::get('legal_sla_content', ''),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Terms of Service')
                    ->schema([
                        Forms\Components\TextInput::make('legal_terms_title')
                            ->label('Title')
                            ->default('Terms of Service'),
                        Forms\Components\Textarea::make('legal_terms_content')
                            ->label('Content')
                            ->rows(15)
                            ->default('')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Privacy Policy')
                    ->schema([
                        Forms\Components\TextInput::make('legal_privacy_title')
                            ->label('Title')
                            ->default('Privacy Policy'),
                        Forms\Components\Textarea::make('legal_privacy_content')
                            ->label('Content')
                            ->rows(15)
                            ->default('')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('SLA / Service Level Agreement')
                    ->schema([
                        Forms\Components\TextInput::make('legal_sla_title')
                            ->label('Title')
                            ->default('Service Level Agreement'),
                        Forms\Components\Textarea::make('legal_sla_content')
                            ->label('Content')
                            ->rows(15)
                            ->default('')
                            ->columnSpanFull(),
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
            ->title('Legal pages saved')
            ->success()
            ->send();
    }
}
