<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AddonResource\Pages;
use App\Models\Addon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class AddonResource extends Resource
{
    protected static ?string $model = Addon::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $navigationGroup = 'Store';

    protected static ?string $navigationLabel = 'Addons';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Addon Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->rows(3),
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])->columns(2),

                Forms\Components\Section::make('Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0),
                        Forms\Components\Select::make('billing_interval')
                            ->options([
                                'month' => 'Monthly',
                                'quarter' => 'Quarterly',
                                'semi_annual' => 'Semi-Annually',
                                'year' => 'Annually',
                                'one_time' => 'One-Time',
                            ])
                            ->default('month'),
                        Forms\Components\TextInput::make('billing_period')
                            ->numeric()
                            ->default(1)
                            ->helperText('Number of intervals between billings'),
                    ])->columns(3),

                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                        Forms\Components\Toggle::make('is_required')
                            ->default(false)
                            ->helperText('Required addons are automatically added to all services'),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
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
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('billing_interval')
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state))),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_required')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('service_addons_count')
                    ->counts('serviceAddons')
                    ->label('Active')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category'),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAddons::route('/'),
            'create' => Pages\CreateAddon::route('/create'),
            'edit' => Pages\EditAddon::route('/{record}/edit'),
        ];
    }
}
