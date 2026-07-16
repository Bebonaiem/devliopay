<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromoCodeResource\Pages;
use App\Models\PromoCode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PromoCodeResource extends Resource
{
    protected static ?string $model = PromoCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Store';

    protected static ?string $navigationLabel = 'Promo Codes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Promo Code Details')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->toUpperCase(),
                        Forms\Components\TextInput::make('description')
                            ->maxLength(255),
                        Forms\Components\Select::make('type')
                            ->options([
                                'percentage' => 'Percentage',
                                'fixed' => 'Fixed Amount',
                            ])
                            ->default('percentage')
                            ->required(),
                        Forms\Components\TextInput::make('value')
                            ->numeric()
                            ->required()
                            ->suffix(fn ($get) => $get('type') === 'percentage' ? '%' : '$'),
                        Forms\Components\TextInput::make('min_amount')
                            ->numeric()
                            ->default(0)
                            ->prefix('$'),
                    ])->columns(2),

                Forms\Components\Section::make('Usage Limits')
                    ->schema([
                        Forms\Components\TextInput::make('max_uses')
                            ->numeric()
                            ->nullable()
                            ->label('Max Uses (null = unlimited)'),
                        Forms\Components\TextInput::make('used_count')
                            ->numeric()
                            ->default(0)
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('starts_at'),
                        Forms\Components\DateTimePicker::make('expires_at'),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'percentage' => 'info',
                        'fixed' => 'success',
                    }),
                Tables\Columns\TextColumn::make('value')
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => $record->type === 'percentage' ? $state.'%' : '$'.number_format($state, 2)),
                Tables\Columns\TextColumn::make('used_count')
                    ->label('Uses')
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_uses')
                    ->label('Limit')
                    ->formatStateUsing(fn ($state) => $state ?? '∞'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Record')
                    ->modalDescription('Are you sure you want to delete this record? This action cannot be undone.'),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPromoCodes::route('/'),
            'create' => Pages\CreatePromoCode::route('/create'),
            'edit' => Pages\EditPromoCode::route('/{record}/edit'),
        ];
    }
}
