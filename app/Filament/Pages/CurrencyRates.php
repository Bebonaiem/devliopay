<?php

namespace App\Filament\Pages;

use App\Models\Currency;
use App\Models\CurrencyRate;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class CurrencyRates extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Exchange Rates';

    protected static ?string $title = 'Currency Exchange Rates';

    protected static string $view = 'filament.pages.settings';

    public ?array $data = [];

    public function mount(): void
    {
        $rates = CurrencyRate::all()->map(fn ($rate) => [
            'from_currency' => $rate->from_currency,
            'rate' => $rate->rate,
            'last_updated' => $rate->fetched_at?->format('Y-m-d\TH:i'),
        ])->toArray();

        $this->form->fill([
            'base_currency_code' => Setting::get('base_currency_code', 'USD'),
            'rates' => $rates,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Base Currency')
                    ->schema([
                        Forms\Components\TextInput::make('base_currency_code')
                            ->label('Base Currency Code')
                            ->default('USD')
                            ->required()
                            ->maxLength(3),
                    ])->columns(2),

                Forms\Components\Section::make('Exchange Rates')
                    ->description('Define exchange rates relative to the base currency. Each rate represents how many units of the target currency equal one unit of the base currency.')
                    ->schema([
                        Forms\Components\Repeater::make('rates')
                            ->label('Rates')
                            ->schema([
                                Forms\Components\Select::make('from_currency')
                                    ->label('Currency')
                                    ->options(fn () => Currency::where('is_active', true)->pluck('name', 'code'))
                                    ->searchable()
                                    ->required(),
                                Forms\Components\TextInput::make('rate')
                                    ->label('Rate')
                                    ->numeric()
                                    ->suffix('x')
                                    ->required(),
                                Forms\Components\DateTimePicker::make('last_updated')
                                    ->label('Last Updated')
                                    ->disabled(),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->addActionLabel('Add Rate')
                            ->itemLabel(fn (array $state): ?string => $state['from_currency'] ?? null),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::set('base_currency_code', $data['base_currency_code']);

        CurrencyRate::truncate();

        foreach ($data['rates'] as $rate) {
            CurrencyRate::create([
                'from_currency' => $rate['from_currency'],
                'to_currency' => $data['base_currency_code'],
                'rate' => $rate['rate'],
                'fetched_at' => $rate['last_updated'] ?? now(),
            ]);
        }

        Notification::make()
            ->title('Exchange rates saved')
            ->success()
            ->send();
    }
}
