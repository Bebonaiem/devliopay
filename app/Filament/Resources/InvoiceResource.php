<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Notifications\InvoiceCreated;
use App\Services\NotificationService;
use App\Services\PaymentService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Billing';

    protected static ?string $navigationLabel = 'Invoices';

    private static function getCurrencySymbol(): string
    {
        $default = Currency::where('is_default', true)->first();

        return $default?->symbol ?? '$';
    }

    public static function form(Form $form): Form
    {
        $symbol = self::getCurrencySymbol();

        return $form
            ->schema([
                Forms\Components\Section::make('Invoice Details')
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable(),
                        Forms\Components\Select::make('service_id')
                            ->relationship('service', 'uuid')
                            ->nullable()
                            ->searchable(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'overdue' => 'Overdue',
                                'cancelled' => 'Cancelled',
                                'refunded' => 'Refunded',
                            ])
                            ->default('pending'),
                        Forms\Components\TextInput::make('subtotal')
                            ->numeric()
                            ->prefix($symbol),
                        Forms\Components\TextInput::make('tax')
                            ->numeric()
                            ->prefix($symbol),
                        Forms\Components\TextInput::make('total')
                            ->numeric()
                            ->prefix($symbol),
                        Forms\Components\TextInput::make('credit')
                            ->numeric()
                            ->prefix($symbol)
                            ->default(0),
                        Forms\Components\Select::make('currency_id')
                            ->relationship('currency', 'name')
                            ->nullable(),
                        Forms\Components\Textarea::make('notes'),
                        Forms\Components\DateTimePicker::make('due_at'),
                        Forms\Components\DateTimePicker::make('paid_at'),
                    ])->columns(3),

                Forms\Components\Section::make('Invoice Items')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('description')
                                    ->required(),
                                Forms\Components\TextInput::make('amount')
                                    ->numeric()
                                    ->required()
                                    ->prefix($symbol),
                                Forms\Components\TextInput::make('tax')
                                    ->numeric()
                                    ->prefix($symbol)
                                    ->default(0),
                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->default(1),
                            ])->columns(4),
                    ]),
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
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'pending' => 'warning',
                        'paid' => 'success',
                        'overdue' => 'danger',
                        'cancelled' => 'danger',
                        'refunded' => 'info',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->money(fn ($record) => $record->currency?->code ?? 'USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tax')
                    ->money(fn ($record) => $record->currency?->code ?? 'USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'overdue' => 'Overdue',
                        'cancelled' => 'Cancelled',
                        'refunded' => 'Refunded',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('markAsPaid')
                    ->label('Mark as Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Invoice $record): bool => in_array($record->status, ['pending', 'overdue']))
                    ->requiresConfirmation()
                    ->modalHeading('Mark as Paid')
                    ->modalDescription('Mark this invoice as paid?')
                    ->action(function (Invoice $record): void {
                        $record->update([
                            'status' => 'paid',
                            'paid_at' => now(),
                        ]);

                        Transaction::create([
                            'user_id' => $record->user_id,
                            'invoice_id' => $record->id,
                            'status' => 'completed',
                            'amount' => $record->total,
                            'currency_id' => $record->currency_id,
                            'gateway' => 'admin',
                            'gateway_id' => 'manual-' . $record->number,
                            'completed_at' => now(),
                        ]);

                        app(PaymentService::class)->updateOrderStatus($record);

                        Notification::make()
                            ->title('Invoice marked as paid')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('sendInvoice')
                    ->label('Send Invoice')
                    ->icon('heroicon-o-envelope')
                    ->color('info')
                    ->action(function (Invoice $record): void {
                        app(NotificationService::class)->notify(
                            $record->user,
                            new InvoiceCreated($record),
                        );

                        Notification::make()
                            ->title('Invoice sent to ' . $record->user->email)
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('markAsPaid')
                        ->label('Mark as Paid')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Mark as Paid')
                        ->modalDescription('Mark selected invoices as paid?')
                        ->action(function ($records): void {
                            $records->each(function (Invoice $record): void {
                                if (in_array($record->status, ['pending', 'overdue'])) {
                                    $record->update([
                                        'status' => 'paid',
                                        'paid_at' => now(),
                                    ]);

                                    Transaction::create([
                                        'user_id' => $record->user_id,
                                        'invoice_id' => $record->id,
                                        'status' => 'completed',
                                        'amount' => $record->total,
                                        'currency_id' => $record->currency_id,
                                        'gateway' => 'admin',
                                        'gateway_id' => 'manual-' . $record->number,
                                        'completed_at' => now(),
                                    ]);

                                    app(PaymentService::class)->updateOrderStatus($record);
                                }
                            });

                            Notification::make()
                                ->title('Selected invoices marked as paid')
                                ->success()
                                ->send();
                        }),
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
