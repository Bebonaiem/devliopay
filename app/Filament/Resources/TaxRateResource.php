<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaxRateResource\Pages;
use App\Models\TaxRate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TaxRateResource extends Resource
{
    protected static ?string $model = TaxRate::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Tax Rate Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., VAT, GST, Sales Tax'),
                        Forms\Components\TextInput::make('rate')
                            ->required()
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01),
                        Forms\Components\Toggle::make('is_inclusive')
                            ->default(false)
                            ->helperText('If enabled, tax is included in the price instead of added on top'),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Location')
                    ->schema([
                        Forms\Components\TextInput::make('country_code')
                            ->label('Country Code')
                            ->maxLength(2)
                            ->placeholder('US')
                            ->helperText('2-letter ISO country code. Leave blank for all countries.'),
                        Forms\Components\TextInput::make('state_code')
                            ->label('State/Province Code')
                            ->maxLength(2)
                            ->placeholder('NY')
                            ->helperText('2-letter state code. Leave blank for all states.'),
                        Forms\Components\TextInput::make('zip_code')
                            ->label('ZIP/Postal Code')
                            ->maxLength(20)
                            ->placeholder('10001')
                            ->helperText('Leave blank for all ZIP codes in this region.'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rate')
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('country_code')
                    ->label('Country')
                    ->formatStateUsing(fn ($state) => $state ?: 'All'),
                Tables\Columns\TextColumn::make('state_code')
                    ->label('State')
                    ->formatStateUsing(fn ($state) => $state ?: 'All'),
                Tables\Columns\TextColumn::make('zip_code')
                    ->label('ZIP')
                    ->formatStateUsing(fn ($state) => $state ?: 'All'),
                Tables\Columns\IconColumn::make('is_inclusive')
                    ->boolean()
                    ->label('Inclusive'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->options([true => 'Active', false => 'Inactive']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete Records')
                        ->modalDescription('Are you sure you want to delete the selected records? This action cannot be undone.'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTaxRates::route('/'),
            'create' => Pages\CreateTaxRate::route('/create'),
            'edit' => Pages\EditTaxRate::route('/{record}/edit'),
        ];
    }
}
