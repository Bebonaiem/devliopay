<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ServerSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-server';

    protected static ?string $navigationGroup = 'Configuration';

    protected static ?string $navigationLabel = 'Server Settings';

    protected static ?int $navigationSort = 7;

    protected static ?string $title = 'Server Provisioning Settings';

    protected static string $view = 'filament.pages.settings';

    public ?array $data = [];

    public function mount(): void
    {
        $configs = json_decode(Setting::get('server_configurations', '[]'), true);

        $this->form->fill([
            'servers' => ! empty($configs) ? $configs : [
                ['name' => '', 'type' => 'pterodactyl', 'is_active' => false, 'host' => '', 'api_key' => ''],
            ],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Repeater::make('servers')
                    ->label('Server Configurations')
                    ->addActionLabel('Add Server')
                    ->collapsible()
                    ->itemLabel(fn (array $state): string => $state['name'] ?: 'Pterodactyl Server')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Server Name')
                                    ->placeholder('e.g. Game Server 1')
                                    ->required(),
                                Forms\Components\Hidden::make('type')
                                    ->default('pterodactyl'),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('host')
                                    ->label('Panel URL')
                                    ->placeholder('https://panel.example.com')
                                    ->required(),
                                Forms\Components\TextInput::make('api_key')
                                    ->label('Application API Key (ptla_)')
                                    ->placeholder('ptla_...')
                                    ->password()
                                    ->revealable()
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->required(),
                            ]),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->inline(),
                    ])
                    ->defaultItems(1)
                    ->minItems(0),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $servers = $data['servers'] ?? [];

        Setting::set('server_configurations', json_encode($servers));

        // Save first active server to individual settings
        $active = collect($servers)->firstWhere('is_active', true);
        if ($active) {
            Setting::set('pterodactyl_host', $active['host'] ?? '');
            Setting::set('pterodactyl_api_key', $active['api_key'] ?? '');
        }

        Notification::make()
            ->title('Server settings saved')
            ->success()
            ->send();
    }
}
