<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Currency;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Billing';

    protected static ?string $navigationLabel = 'Transactions';

    protected static ?int $navigationSort = 3;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoice.number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money(fn ($record) => $record->currency?->code ?? Currency::defaultCode())
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        'refunded' => 'info',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('gateway')
                    ->sortable(),
                Tables\Columns\TextColumn::make('gateway_id')
                    ->limit(20)
                    ->tooltip(fn ($record) => $record->gateway_id),
                Tables\Columns\TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'completed' => 'Completed',
                        'pending' => 'Pending',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),
                Tables\Filters\SelectFilter::make('gateway')
                    ->options([
                        'stripe' => 'Stripe',
                        'paypal' => 'PayPal',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->form(fn (Transaction $record): array => [
                        Forms\Components\Section::make('Transaction Details')
                            ->schema([
                                Forms\Components\TextInput::make('id')->disabled(),
                                Forms\Components\TextInput::make('user.name')->label('Customer')->disabled(),
                                Forms\Components\TextInput::make('invoice.number')->label('Invoice')->disabled(),
                                Forms\Components\TextInput::make('amount')
                                    ->money(fn ($record) => $record->currency?->code ?? Currency::defaultCode())
                                    ->disabled(),
                                Forms\Components\TextInput::make('status')->disabled(),
                                Forms\Components\TextInput::make('gateway')->disabled(),
                                Forms\Components\TextInput::make('gateway_id')->label('Gateway Transaction ID')->disabled(),
                                Forms\Components\TextInput::make('completed_at')->dateTime()->disabled(),
                                Forms\Components\TextInput::make('created_at')->dateTime()->disabled(),
                                Forms\Components\Textarea::make('description')->disabled(),
                            ])->columns(2),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
        ];
    }
}
