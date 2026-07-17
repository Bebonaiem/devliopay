<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceUpgradeResource\Pages;
use App\Models\Currency;
use App\Models\ServiceUpgrade;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceUpgradeResource extends Resource
{
    protected static ?string $model = ServiceUpgrade::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up';

    protected static ?string $navigationGroup = 'Services';

    protected static ?string $navigationLabel = 'Upgrade Log';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Service Upgrade';

    protected static ?string $pluralModelLabel = 'Service Upgrades';

    protected static bool $shouldRegisterNavigation = true;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('service.product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fromPricing.name')
                    ->label('From Plan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('toPricing.name')
                    ->label('To Plan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'upgrade' => 'success',
                        'downgrade' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('price_difference')
                    ->label('Price Diff')
                    ->money(Currency::defaultCode())
                    ->sortable(),
                Tables\Columns\TextColumn::make('credit_applied')
                    ->label('Credit')
                    ->money(Currency::defaultCode())
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_due')
                    ->label('Amount Due')
                    ->money(Currency::defaultCode())
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'failed' => 'danger',
                        'cancelled' => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'upgrade' => 'Upgrade',
                        'downgrade' => 'Downgrade',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceUpgrades::route('/'),
        ];
    }
}
