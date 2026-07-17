<?php

namespace App\Filament\Resources\AddonResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceAddonsRelationManager extends RelationManager
{
    protected static string $relationship = 'serviceAddons';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('service.user.name')->searchable()->label('User'),
                Tables\Columns\TextColumn::make('service_id')->sortable()->label('Service ID'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(fn ($state) => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'cancelled' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('activated_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('next_billing_at')->dateTime()->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }
}
