<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PricingRelationManager extends RelationManager
{
    protected static string $relationship = 'pricing';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\Select::make('type')
                ->options([
                    'free' => 'Free',
                    'one_time' => 'One Time',
                    'recurring' => 'Recurring',
                ])
                ->default('recurring')
                ->required(),
            Forms\Components\Select::make('interval')
                ->options([
                    'day' => 'Day',
                    'week' => 'Week',
                    'month' => 'Month',
                    'year' => 'Year',
                ])
                ->default('month')
                ->required(),
            Forms\Components\TextInput::make('billing_period')->numeric()->default(1)->required(),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')->sortable(),
                Tables\Columns\TextColumn::make('interval')->sortable(),
                Tables\Columns\TextColumn::make('billing_period')->sortable(),
                Tables\Columns\TextColumn::make('currencies.pivot.amount')
                    ->money(fn ($state) => 'USD')
                    ->label('Price'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ]);
    }
}
