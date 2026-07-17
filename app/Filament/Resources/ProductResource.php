<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Currency;
use App\Models\Product;
use App\Services\Servers\PterodactylServer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Store';

    protected static ?string $navigationLabel = 'Products';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('General')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', \Str::slug($state))),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable(),
                        Forms\Components\Textarea::make('description')
                            ->rows(3),
                        Forms\Components\Select::make('server_extension')
                            ->options([
                                'pterodactyl' => 'Pterodactyl',
                            ])
                            ->nullable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state !== 'pterodactyl') {
                                    $set('config_options', []);
                                }
                            }),
                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('products')
                            ->visibility('public'),
                    ])->columns(2),

                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\TextInput::make('stock')
                            ->numeric()
                            ->nullable()
                            ->label('Stock (-1 for unlimited)'),
                        Forms\Components\TextInput::make('per_user_limit')
                            ->numeric()
                            ->nullable()
                            ->label('Per User Limit (-1 for unlimited)'),
                        Forms\Components\Select::make('allow_quantity')
                            ->options([
                                'no' => 'No',
                                'separated' => 'Separated',
                                'combined' => 'Combined',
                            ])
                            ->default('no'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        Forms\Components\Toggle::make('is_hidden')
                            ->label('Hidden from Store'),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ])->columns(3),

                Forms\Components\Section::make('Server Configuration')
                    ->description('Pterodactyl server settings for this product')
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\Select::make('config_options.nest_id')
                                ->label('Nest')
                                ->options(fn () => (new PterodactylServer)->getNests())
                                ->searchable()
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('config_options.egg_id', null))
                                ->helperText('Pterodactyl nest (game category)'),
                            Forms\Components\Select::make('config_options.egg_id')
                                ->label('Egg')
                                ->options(fn (Forms\Get $get) => (new PterodactylServer)->getEggs((int) ($get('config_options.nest_id') ?? 0)))
                                ->searchable()
                                ->required()
                                ->helperText('Pterodactyl egg for this server type'),
                            Forms\Components\Select::make('config_options.node_id')
                                ->label('Node')
                                ->options(fn () => (new PterodactylServer)->getNodes())
                                ->searchable()
                                ->required()
                                ->helperText('Pterodactyl node to deploy to'),
                        ]),
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('config_options.ram')
                                ->label('RAM (MB)')
                                ->numeric()
                                ->required()
                                ->default(1024),
                            Forms\Components\TextInput::make('config_options.disk')
                                ->label('Disk (MB)')
                                ->numeric()
                                ->required()
                                ->default(10240),
                            Forms\Components\TextInput::make('config_options.cpu')
                                ->label('CPU (%)')
                                ->numeric()
                                ->required()
                                ->default(100),
                        ]),
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('config_options.databases')
                                ->label('Databases')
                                ->numeric()
                                ->default(2),
                            Forms\Components\TextInput::make('config_options.allocations')
                                ->label('Allocations')
                                ->numeric()
                                ->default(1),
                        ]),
                        Forms\Components\TextInput::make('config_options.docker_image')
                            ->label('Docker Image')
                            ->placeholder('ghcr.io/ptero-eggs/yolks:java_17')
                            ->helperText('Leave empty to use the egg default image'),
                    ])
                    ->visible(fn (Forms\Get $get) => $get('server_extension') === 'pterodactyl'),

                Forms\Components\Section::make('Pricing')
                    ->schema([
                        Forms\Components\Repeater::make('pricing')
                            ->schema([
                                Forms\Components\Hidden::make('pricing_id'),
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
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
                                Forms\Components\TextInput::make('billing_period')
                                    ->numeric()
                                    ->default(1)
                                    ->required(),
                                Forms\Components\Repeater::make('currencies_data')
                                    ->schema([
                                        Forms\Components\Select::make('currency_id')
                                            ->options(fn () => Currency::where('is_active', true)->pluck('name', 'id'))
                                            ->required()
                                            ->label('Currency'),
                                        Forms\Components\TextInput::make('amount')
                                            ->numeric()
                                            ->required()
                                            ->default(0),
                                        Forms\Components\TextInput::make('setup_fee')
                                            ->numeric()
                                            ->default(0),
                                    ])
                                    ->columns(3)
                                    ->collapsible(),
                            ])
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                            ->afterStateHydrated(function (Forms\Components\Repeater $component, ?Product $record): void {
                                if (! $record) {
                                    return;
                                }

                                $component->state(
                                    $record->pricing->map(fn ($pricing) => [
                                        'pricing_id' => $pricing->id,
                                        'name' => $pricing->name,
                                        'type' => $pricing->type,
                                        'interval' => $pricing->interval,
                                        'billing_period' => $pricing->billing_period,
                                        'currencies_data' => $pricing->currencies->map(fn ($currency) => [
                                            'currency_id' => $currency->id,
                                            'amount' => $currency->pivot->amount,
                                            'setup_fee' => $currency->pivot->setup_fee,
                                        ])->toArray(),
                                    ])->toArray()
                                );
                            }),
                    ]),

                Forms\Components\Section::make('Email Template')
                    ->schema([
                        Forms\Components\RichEditor::make('email_template')
                            ->label('Post-Purchase Email Content'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->width(48)
                    ->height(48)
                    ->square(),
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pricing.count')
                    ->counts('pricing')
                    ->label('Plans'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_hidden')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
                Tables\Filters\SelectFilter::make('category_id')
                    ->relationship('category', 'name'),
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
        return [
            RelationManagers\PricingRelationManager::class,
            RelationManagers\ServicesRelationManager::class,
            RelationManagers\OrderItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
