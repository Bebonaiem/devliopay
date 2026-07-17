<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderItems';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('order.user.name')->searchable(),
                Tables\Columns\TextColumn::make('pricing.name'),
                Tables\Columns\TextColumn::make('quantity')->sortable(),
                Tables\Columns\TextColumn::make('price')->money(fn ($state) => 'USD')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->actions([])
            ->bulkActions([]);
    }
}
