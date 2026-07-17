<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AddonResource\Pages;
use App\Filament\Resources\AddonResource\RelationManagers;
use App\Models\Addon;
use App\Models\Currency;
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
                            ->prefix(fn () => Currency::defaultSymbol())
                            ->minValue(0),
                        Forms\Components\Select::make('billing_interval')
                            ->options([
                                'month' => 'Monthly',
                                'quarter' => 'Quarterly',
                                'semi_annual' => 'Semi-Annually',
                                'year' => 'Annually',
                                'one_time' => 'One-Time',
                            ])
                            ->default('month')
                            ->live(),
                        Forms\Components\TextInput::make('billing_period')
                            ->numeric()
                            ->default(1)
                            ->helperText('Number of intervals between billings')
                            ->visible(fn (Forms\Get $get) => $get('billing_interval') !== 'one_time'),
                    ])->columns(3),

                Forms\Components\Section::make('Pterodactyl Resources')
                    ->description('Extra resources this addon adds to a server when purchased')
                    ->schema([
                        Forms\Components\TextInput::make('extra_ram')
                            ->numeric()
                            ->default(0)
                            ->suffix('MB')
                            ->minValue(0),
                        Forms\Components\TextInput::make('extra_disk')
                            ->numeric()
                            ->default(0)
                            ->suffix('MB')
                            ->minValue(0),
                        Forms\Components\TextInput::make('extra_cpu')
                            ->numeric()
                            ->default(0)
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(1000),
                        Forms\Components\TextInput::make('extra_databases')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Forms\Components\TextInput::make('extra_allocations')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Forms\Components\TextInput::make('extra_backups')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ])->columns(3),

                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\Select::make('server_extension')
                            ->options([
                                '' => 'All Extensions',
                                'pterodactyl' => 'Pterodactyl',
                            ])
                            ->nullable()
                            ->helperText('Limit this addon to a specific server extension'),
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
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->money(Currency::defaultCode())
                    ->sortable(),
                Tables\Columns\TextColumn::make('billing_interval')
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state))),
                Tables\Columns\TextColumn::make('billing_period')
                    ->suffix('x')
                    ->sortable(),
                Tables\Columns\TextColumn::make('extra_ram')
                    ->label('RAM')
                    ->suffix('MB')
                    ->sortable(),
                Tables\Columns\TextColumn::make('extra_disk')
                    ->label('Disk')
                    ->suffix('MB')
                    ->sortable(),
                Tables\Columns\TextColumn::make('extra_cpu')
                    ->label('CPU')
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_required')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('service_addons_count')
                    ->counts('serviceAddons')
                    ->label('Installed')
                    ->sortable(),
                Tables\Columns\TextColumn::make('server_extension')
                    ->badge(fn ($state) => match ($state) {
                        'pterodactyl' => 'success',
                        null => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => $state ? ucfirst($state) : 'All'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category'),
                Tables\Filters\SelectFilter::make('billing_interval')
                    ->options([
                        'month' => 'Monthly',
                        'quarter' => 'Quarterly',
                        'semi_annual' => 'Semi-Annually',
                        'year' => 'Annually',
                        'one_time' => 'One-Time',
                    ])
                    ->label('Billing'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
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
        return [
            RelationManagers\ServiceAddonsRelationManager::class,
        ];
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
