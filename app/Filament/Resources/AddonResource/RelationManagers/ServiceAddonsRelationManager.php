<?php

namespace App\Filament\Resources\AddonResource\RelationManagers;

use App\Models\Currency;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceAddonsRelationManager extends RelationManager
{
    protected static string $relationship = 'serviceAddons';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'pending' => 'Pending',
                        'cancelled' => 'Cancelled',
                        'suspended' => 'Suspended',
                    ])
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('service.user.name')
                    ->searchable()
                    ->label('User'),
                Tables\Columns\TextColumn::make('service.product.name')
                    ->searchable()
                    ->label('Product'),
                Tables\Columns\TextColumn::make('addon.name')
                    ->searchable()
                    ->label('Addon'),
                Tables\Columns\TextColumn::make('price')
                    ->money(Currency::defaultCode())
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(fn ($state) => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'cancelled' => 'gray',
                        'suspended' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('activated_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('next_billing_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'pending' => 'Pending',
                        'cancelled' => 'Cancelled',
                        'suspended' => 'Suspended',
                    ])
                    ->label('Status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cancel Addon')
                    ->modalDescription('This will cancel this addon for the service. The extra resources will be removed.')
                    ->action(fn ($record) => $record->update(['status' => 'cancelled'])),
                Tables\Actions\Action::make('activate')
                    ->label('Activate')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => $record->status !== 'active')
                    ->action(fn ($record) => $record->update([
                        'status' => 'active',
                        'activated_at' => $record->activated_at ?? now(),
                    ])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete Records')
                        ->modalDescription('Are you sure you want to delete the selected records?'),
                ]),
            ]);
    }
}
