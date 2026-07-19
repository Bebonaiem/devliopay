<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Currency;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Billing';

    protected static ?string $navigationLabel = 'Orders';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return number_format(static::getModel()::where('status', 'pending')->count());
    }

    protected static ?string $recordTitleAttribute = 'number';

    protected static ?string $modelLabel = 'Order';

    protected static ?string $pluralModelLabel = 'Orders';

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Order Information')
                    ->schema([
                        TextEntry::make('number')
                            ->label('Order Number'),
                        TextEntry::make('user.name')
                            ->label('Customer')
                            ->url(fn ($record) => route('filament.admin.resources.users.edit', $record->user_id)),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'processing' => 'info',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                'failed' => 'danger',
                            }),
                        TextEntry::make('total')
                            ->money(Currency::defaultCode()),
                        TextEntry::make('setup_fee')
                            ->money(Currency::defaultCode())
                            ->label('Setup Fee'),
                        TextEntry::make('currency.code')
                            ->label('Currency'),
                        TextEntry::make('created_at')
                            ->dateTime(),
                        TextEntry::make('paid_at')
                            ->dateTime()
                            ->placeholder('Not paid'),
                    ])->columns(3),

                Section::make('Order Items')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->schema([
                                TextEntry::make('product.name')
                                    ->label('Product'),
                                TextEntry::make('pricing.name')
                                    ->label('Plan'),
                                TextEntry::make('quantity')
                                    ->label('Qty'),
                                TextEntry::make('price')
                                    ->money(Currency::defaultCode()),
                                TextEntry::make('setup_fee')
                                    ->money(Currency::defaultCode())
                                    ->label('Setup Fee'),
                            ])->columns(5),
                    ]),

                Section::make('Services')
                    ->schema([
                        RepeatableEntry::make('services')
                            ->schema([
                                TextEntry::make('uuid')
                                    ->label('Service ID')
                                    ->copyable(),
                                TextEntry::make('product.name')
                                    ->label('Product'),
                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'active' => 'success',
                                        'suspended' => 'danger',
                                        'cancelled' => 'gray',
                                        'terminated' => 'danger',
                                    }),
                                TextEntry::make('server_properties.ip_address')
                                    ->label('IP'),
                                TextEntry::make('next_billing_at')
                                    ->dateTime()
                                    ->label('Next Billing'),
                            ])->columns(5),
                    ]),

                Section::make('Invoices')
                    ->schema([
                        RepeatableEntry::make('invoices')
                            ->schema([
                                TextEntry::make('number')
                                    ->label('Invoice')
                                    ->url(fn ($record) => route('filament.admin.resources.invoices.edit', $record->id)),
                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'draft' => 'gray',
                                        'pending' => 'warning',
                                        'paid' => 'success',
                                        'overdue' => 'danger',
                                        'cancelled' => 'danger',
                                        'refunded' => 'info',
                                    }),
                                TextEntry::make('total')
                                    ->money(Currency::defaultCode()),
                                TextEntry::make('due_at')
                                    ->dateTime()
                                    ->label('Due Date'),
                                TextEntry::make('paid_at')
                                    ->dateTime()
                                    ->placeholder('Not paid'),
                            ])->columns(5),
                    ]),

                Section::make('Notes')
                    ->schema([
                        TextEntry::make('notes')
                            ->placeholder('No notes'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Order Details')
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->disabled(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                                'failed' => 'Failed',
                            ])
                            ->default('pending'),
                        Forms\Components\TextInput::make('total')
                            ->numeric()
                            ->prefix(fn ($record) => $record?->currency?->symbol ?? Currency::defaultSymbol())
                            ->disabled(),
                        Forms\Components\TextInput::make('setup_fee')
                            ->numeric()
                            ->prefix(fn ($record) => $record?->currency?->symbol ?? Currency::defaultSymbol())
                            ->disabled(),
                        Forms\Components\Select::make('currency_id')
                            ->relationship('currency', 'name')
                            ->nullable()
                            ->disabled(),
                        Forms\Components\Textarea::make('notes'),
                        Forms\Components\DateTimePicker::make('paid_at'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => route('filament.admin.resources.users.edit', $record->user_id)),
                Tables\Columns\TextColumn::make('items.product.name')
                    ->label('Products')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->items->pluck('product.name')->implode(', ')),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'failed' => 'danger',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->money(Currency::defaultCode())
                    ->sortable(),
                Tables\Columns\TextColumn::make('services_count')
                    ->counts('services')
                    ->label('Services')
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoices.number')
                    ->label('Invoice')
                    ->limit(15),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'failed' => 'Failed',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Order $record) => route('filament.admin.resources.orders.view', $record)),
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
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
